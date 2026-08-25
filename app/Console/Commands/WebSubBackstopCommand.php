<?php

namespace App\Console\Commands;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use App\Models\Video;
use App\Services\PollCooldown;
use App\Services\RssFetcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Signature('websub:backstop {--limit= : Max channels to re-poll this sweep} {--dry-run : List candidates without polling}')]
#[Description('Failure-driven re-poll of channels push may have missed (lapsed lease, failed renewal, anomalous silence)')]
class WebSubBackstopCommand extends Command
{
    public function handle(RssFetcher $fetcher, PollCooldown $cooldown): int
    {
        // A block is IP-scoped, so it applies to every automatic fetch path, not just
        // the main sweep. Polling through one wastes requests and may extend it.
        if ($cooldown->isActive()) {
            $this->components->warn(sprintf(
                'Block cooldown active until %s; skipping backstop.',
                $cooldown->activeUntil()?->toIso8601String() ?? 'unknown',
            ));

            return self::SUCCESS;
        }

        $limit = (int) ($this->option('limit') ?: config('services.websub.backstop_limit', 200));

        $this->flagRecoveryAfterCallbackDowntime();

        $minRepollHours = (int) config('services.websub.backstop_min_repoll_hours', 6);
        $repollCutoff = CarbonImmutable::now()->subHours($minRepollHours);

        /** @var Collection<int, Channel> $candidates */
        $candidates = collect();
        /** @var array<int, string> $reasons */
        $reasons = [];

        Channel::query()
            ->with('webSubSubscription')
            ->orderBy('id')
            ->chunkById(200, function (Collection $channels) use (&$candidates, &$reasons, $limit, $repollCutoff): bool {
                $uploadStats = $this->uploadStatsPerChannel($channels->pluck('id')->all());

                foreach ($channels as $channel) {
                    if ($candidates->count() >= $limit) {
                        return false;
                    }

                    $reason = $this->reasonToRepoll(
                        $channel,
                        $uploadStats[$channel->id] ?? null,
                    );

                    if ($reason === null) {
                        continue;
                    }

                    // A known-missing upload skips the pacing gate; the softer signals do not.
                    $polledRecently = $channel->last_fetched_at !== null
                        && $channel->last_fetched_at->gt($repollCutoff);

                    if ($polledRecently && ! $this->isUrgent($reason)) {
                        continue;
                    }

                    $candidates->push($channel);
                    $reasons[$channel->id] = $reason;
                }

                return true;
            });

        if ($candidates->isEmpty()) {
            $this->components->info('No channels need a backstop poll.');

            return self::SUCCESS;
        }

        foreach ($candidates as $channel) {
            $this->line("{$channel->channel_id}: {$reasons[$channel->id]}");
        }

        if ($this->option('dry-run')) {
            $this->components->info('[dry-run] '.$candidates->count().' channels would be re-polled.');

            return self::SUCCESS;
        }

        $result = $fetcher->fetchForChannels($candidates, force: true);

        ChannelSubscription::query()
            ->whereIn('channel_id', $candidates->pluck('id'))
            ->update([
                'delivery_failed_at' => null,
                'recovery_due_at' => null,
            ]);

        $this->components->info(sprintf(
            'Backstop polled %d channels (fetched: %d, not modified: %d, failed: %d, blocked: %d).',
            $candidates->count(),
            $result['fetched'],
            $result['not_modified'],
            $result['failed'],
            $result['blocked'],
        ));

        return self::SUCCESS;
    }

    /**
     * Reasons that mean an upload is already known to be missing.
     */
    protected function isUrgent(string $reason): bool
    {
        return in_array($reason, ['callback downtime recovery', 'a push failed to ingest'], true);
    }

    /**
     * Why this channel needs a poll, or null when push is believed healthy.
     *
     * @param  array{latest: CarbonImmutable, earliest: CarbonImmutable, count: int}|null  $uploadStats
     */
    protected function reasonToRepoll(Channel $channel, ?array $uploadStats): ?string
    {
        $subscription = $channel->webSubSubscription;

        if ($subscription === null) {
            return 'no websub subscription';
        }

        if ($subscription->recovery_due_at !== null) {
            return 'callback downtime recovery';
        }

        if ($subscription->delivery_failed_at !== null) {
            return 'a push failed to ingest';
        }

        if ($subscription->status === WebSubSubscriptionStatus::Failed) {
            return 'subscription failed';
        }

        if ($subscription->renewal_failures > 0) {
            return "renewal failing ({$subscription->renewal_failures}x)";
        }

        if ($this->leaseHasLapsed($subscription)) {
            return 'lease lapsed';
        }

        return $this->silenceAnomaly($uploadStats);
    }

    protected function leaseHasLapsed(ChannelSubscription $subscription): bool
    {
        if ($subscription->status !== WebSubSubscriptionStatus::Active) {
            return false;
        }

        return $subscription->expires_at === null
            || $subscription->expires_at->isPast();
    }

    /**
     * A channel silent well past its own posting rhythm may have lost a push.
     *
     * The cadence is the mean gap across the channel's stored uploads, which
     * comes from the same grouped aggregate as the latest upload: no per-channel
     * query, so the sweep stays flat as the channel count grows.
     *
     * @param  array{latest: CarbonImmutable, earliest: CarbonImmutable, count: int}|null  $uploadStats
     */
    protected function silenceAnomaly(?array $uploadStats): ?string
    {
        if ($uploadStats === null || $uploadStats['count'] < 3) {
            return null;
        }

        $minSilenceHours = (int) config('services.websub.backstop_min_silence_hours', 48);
        $silentHours = abs($uploadStats['latest']->diffInHours(CarbonImmutable::now()));

        if ($silentHours < $minSilenceHours) {
            return null;
        }

        $spanHours = abs($uploadStats['earliest']->diffInHours($uploadStats['latest']));
        $meanGapHours = $spanHours / ($uploadStats['count'] - 1);

        if ($meanGapHours <= 0.0) {
            return null;
        }

        $multiplier = (float) config('services.websub.backstop_silence_multiplier', 3.0);
        $threshold = max($meanGapHours * $multiplier, (float) $minSilenceHours);

        if ($silentHours <= $threshold) {
            return null;
        }

        return sprintf(
            'silent %dh against a ~%dh cadence',
            (int) $silentHours,
            (int) $meanGapHours,
        );
    }

    /**
     * Upload span and count per channel id, in one grouped query per chunk.
     *
     * Short-form rows are excluded: before [036] they were deleted rather than stored,
     * so counting them now would shrink every cadence estimate and make the silence
     * threshold fire differently than it ever has.
     *
     * @param  array<int, int>  $channelIds
     * @return array<int, array{latest: CarbonImmutable, earliest: CarbonImmutable, count: int}>
     */
    protected function uploadStatsPerChannel(array $channelIds): array
    {
        if ($channelIds === []) {
            return [];
        }

        return Video::query()
            ->selectRaw('channel_id, max(published_at) as latest_published_at, min(published_at) as earliest_published_at, count(*) as upload_count')
            ->whereIn('channel_id', $channelIds)
            ->where('is_short', false)
            ->groupBy('channel_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->channel_id => [
                    'latest' => CarbonImmutable::parse($row->latest_published_at),
                    'earliest' => CarbonImmutable::parse($row->earliest_published_at),
                    'count' => (int) $row->upload_count,
                ],
            ])
            ->all();
    }

    /**
     * A gap between sweeps means this app (and its callback) was down, so pushes
     * delivered during the gap are simply gone. Queue every channel for one re-poll;
     * the per-sweep limit paces the recovery.
     */
    protected function flagRecoveryAfterCallbackDowntime(): void
    {
        $gapHours = (int) config('services.websub.backstop_downtime_gap_hours', 3);
        $lastRun = Cache::get('websub:backstop:last_run_at');
        $now = CarbonImmutable::now();

        if (is_string($lastRun) && CarbonImmutable::parse($lastRun)->addHours($gapHours)->isPast()) {
            $flagged = ChannelSubscription::query()
                ->whereNull('recovery_due_at')
                ->update(['recovery_due_at' => $now]);

            $this->components->warn(
                "Callback downtime detected since {$lastRun}; queued {$flagged} channels for recovery."
            );
        }

        Cache::forever('websub:backstop:last_run_at', $now->toIso8601String());
    }
}
