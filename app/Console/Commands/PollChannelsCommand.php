<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\PollSweep;
use App\Services\PollCooldown;
use App\Services\RssFetcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('channels:poll {--limit= : Override the per-sweep safety cap} {--ignore-cooldown : Poll even while a block cooldown is active}')]
#[Description('Poll every channel\'s RSS feed in one sweep: the sole automatic ingestion path')]
class PollChannelsCommand extends Command
{
    public function handle(RssFetcher $fetcher, PollCooldown $cooldown): int
    {
        if ($cooldown->isActive() && ! $this->option('ignore-cooldown')) {
            $until = $cooldown->activeUntil();
            $this->components->warn(sprintf(
                'Block cooldown active until %s (%s); skipping sweep.',
                $until?->toIso8601String() ?? 'unknown',
                $cooldown->reason() ?? 'no reason recorded',
            ));

            return self::SUCCESS;
        }

        $cap = max(1, (int) ($this->option('limit') ?: config('services.polling.max_per_sweep', 1000)));
        $total = Channel::query()->count();

        if ($total === 0) {
            $this->components->info('No channels to poll.');

            return self::SUCCESS;
        }

        $capHit = $total > $cap;

        if ($capHit) {
            // Not a rotation mechanism: hitting it means the outbound request rate
            // grew past what one server IP should sustain, and sharding is due.
            $message = "Poll cap reached: {$total} channels, capped at {$cap} this sweep.";
            $this->components->error($message);
            Log::error('Poll sweep hit POLL_MAX_PER_SWEEP', ['channels' => $total, 'cap' => $cap]);
        }

        $startedAt = CarbonImmutable::now();

        // Stalest first. The cap is a tripwire, not a rotation mechanism, but ordering
        // this way means a sweep that ever hits it degrades into slower coverage of the
        // whole table rather than a permanent blackout for everything past the cap.
        $channels = Channel::query()
            ->orderByRaw('last_fetched_at is null desc')
            ->orderBy('last_fetched_at')
            ->orderBy('id')
            ->limit($cap)
            ->get();

        // force: true — the schedule is the only interval control. Sweep drift lands
        // channels at 29-point-something minutes old, and the TTL would skip those.
        $result = $fetcher->fetchForChannels($channels, force: true);

        $attempted = $channels->count();
        $bad = $result['failed'] + $result['blocked'];
        $ratio = $attempted > 0 ? $bad / $attempted : 0.0;

        $triggered = $this->shouldCooldown($attempted, $ratio);

        if ($triggered) {
            $cooldown->start(sprintf(
                '%d of %d requests failed (%d of them block signals)',
                $bad,
                $attempted,
                $result['blocked'],
            ));
        }

        PollSweep::create([
            'started_at' => $startedAt,
            'finished_at' => CarbonImmutable::now(),
            'channels_polled' => $attempted,
            'fetched' => $result['fetched'],
            'not_modified' => $result['not_modified'],
            'failed' => $result['failed'],
            'blocked' => $result['blocked'],
            'cap_hit' => $capHit,
            'cooldown_triggered' => $triggered,
        ]);

        $this->pruneSweepHistory();

        $this->components->info(sprintf(
            'Polled %d channels (fetched: %d, not modified: %d, failed: %d, blocked: %d).',
            $attempted,
            $result['fetched'],
            $result['not_modified'],
            $result['failed'],
            $result['blocked'],
        ));

        return self::SUCCESS;
    }

    /**
     * One row per sweep at a 30-minute cadence is 48 a day forever; keep a window
     * wide enough for the health report and drop the rest.
     */
    protected function pruneSweepHistory(): void
    {
        $keepDays = max(1, (int) config('services.polling.sweep_history_days', 30));

        PollSweep::query()
            ->where('started_at', '<', CarbonImmutable::now()->subDays($keepDays))
            ->delete();
    }

    /**
     * Blocks are all-or-nothing, so a sweep-wide bad-response ratio is the most
     * reliable tell. The minimum sample keeps a tiny sweep from tripping it.
     */
    protected function shouldCooldown(int $attempted, float $ratio): bool
    {
        $minSample = max(1, (int) config('services.polling.block_min_sample', 5));

        if ($attempted < $minSample) {
            return false;
        }

        return $ratio >= (float) config('services.polling.block_failure_ratio', 0.5);
    }
}
