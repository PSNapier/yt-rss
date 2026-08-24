<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\PollSweep;
use App\Services\PollCooldown;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('poll:health {--hours= : Window for sweep failure and block counts}')]
#[Description('Report polling staleness, fetch failures, block signals, and rss_url deviations')]
class PollHealthCommand extends Command
{
    public function handle(PollCooldown $cooldown): int
    {
        $hours = max(1, (int) ($this->option('hours') ?: config('services.polling.health_window_hours', 24)));
        $since = CarbonImmutable::now()->subHours($hours);

        $this->reportCooldown($cooldown);
        $this->reportStaleness();
        $this->reportSweeps($since, $hours);
        $this->reportRssUrlDeviations();

        return self::SUCCESS;
    }

    protected function reportCooldown(PollCooldown $cooldown): void
    {
        $until = $cooldown->activeUntil();

        if ($until === null) {
            $this->line('Cooldown: none active.');

            return;
        }

        $this->line(sprintf(
            'Cooldown: ACTIVE until %s (%s).',
            $until->toIso8601String(),
            $cooldown->reason() ?? 'no reason recorded',
        ));
    }

    protected function reportStaleness(): void
    {
        $total = Channel::query()->count();

        if ($total === 0) {
            $this->line('Staleness: no channels.');

            return;
        }

        $never = Channel::query()->whereNull('last_fetched_at')->count();

        $ages = Channel::query()
            ->whereNotNull('last_fetched_at')
            ->pluck('last_fetched_at')
            ->map(fn ($at) => abs(CarbonImmutable::parse($at)->diffInMinutes(CarbonImmutable::now())))
            ->values();

        // A channel whose fetch fails every sweep never sets `last_fetched_at`, so it
        // would otherwise hide from the staleness numbers entirely.
        if ($never > 0) {
            $oldestNever = Channel::query()
                ->whereNull('last_fetched_at')
                ->min('created_at');

            $this->line(sprintf(
                'Never fetched: %d channels, oldest added %s.',
                $never,
                $oldestNever !== null ? CarbonImmutable::parse($oldestNever)->toIso8601String() : 'unknown',
            ));
        }

        if ($ages->isEmpty()) {
            $this->line("Staleness: {$total} channels, none ever fetched.");

            return;
        }

        $sorted = $ages->sort()->values();
        $middle = intdiv($sorted->count(), 2);
        $medianMinutes = $sorted->count() % 2 === 1
            ? $sorted[$middle]
            : ($sorted[$middle - 1] + $sorted[$middle]) / 2;

        $this->line(sprintf(
            'Staleness: %d channels, max %d min, median %d min, never fetched %d.',
            $total,
            (int) $ages->max(),
            (int) $medianMinutes,
            $never,
        ));
    }

    protected function reportSweeps(CarbonImmutable $since, int $hours): void
    {
        $row = PollSweep::query()
            ->where('started_at', '>=', $since)
            ->select([
                DB::raw('count(*) as sweeps'),
                DB::raw('coalesce(sum(channels_polled), 0) as polled'),
                DB::raw('coalesce(sum(failed), 0) as failed'),
                DB::raw('coalesce(sum(blocked), 0) as blocked'),
                DB::raw('coalesce(sum(cap_hit), 0) as cap_hits'),
                DB::raw('coalesce(sum(cooldown_triggered), 0) as cooldowns'),
            ])
            ->first();

        $this->line(sprintf(
            'Last %dh: %d sweeps, %d polls, %d fetch failures, %d block signals, %d cap hits, %d cooldowns.',
            $hours,
            (int) $row->sweeps,
            (int) $row->polled,
            (int) $row->failed,
            (int) $row->blocked,
            (int) $row->cap_hits,
            (int) $row->cooldowns,
        ));
    }

    /**
     * `Channel::rssUrl()` prefers the stored column, so a legacy odd row silently
     * polls the wrong feed forever. Surface any that differ from the canonical form.
     */
    protected function reportRssUrlDeviations(): void
    {
        $deviant = [];

        Channel::query()->orderBy('id')->chunkById(500, function ($channels) use (&$deviant): void {
            foreach ($channels as $channel) {
                $canonical = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channel->channel_id;

                if (filled($channel->rss_url) && $channel->rss_url !== $canonical) {
                    $deviant[] = "{$channel->channel_id}: {$channel->rss_url}";
                }
            }
        });

        if ($deviant === []) {
            $this->line('All rss_url values are canonical.');

            return;
        }

        $this->line('rss_url deviations ('.count($deviant).'):');

        foreach ($deviant as $line) {
            $this->line('  '.$line);
        }
    }
}
