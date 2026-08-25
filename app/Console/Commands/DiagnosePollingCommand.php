<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\PollSweep;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Throwable;

/**
 * Read-only. `poll:health` answers "is polling healthy"; this answers "why is it
 * not", which needs readings the sweep never records: the transport exception
 * behind a `no_response` failure, whether generous timeouts succeed where the
 * sweep's 2.0s/3.0s budget fails, and which uploads are actually absent from the
 * database. Written for `[032]` and kept because that gap recurs.
 */
#[Signature('poll:diagnose
    {--sweeps=20 : Rows of sweep history to print}
    {--timing=5 : Channels to time on the sweep budget against generous timeouts}
    {--pool= : Channels in the concurrent pool probe; defaults to the configured sweep batch}
    {--skip-diff : Skip the live-RSS-against-videos diff, which fetches every channel}
    {--limit= : Cap the channels included in the diff}
    {--list=80 : Missing entries to print}
    {--shorts-probe=0 : Missing Shorts entries to verify against the watch page}
    {--sleep=150 : Milliseconds between the diff\'s sequential requests}')]
#[Description('Diagnose why polling is losing videos: transport failures, timeout headroom, and a live RSS diff')]
class DiagnosePollingCommand extends Command
{
    /** Timeouts with enough headroom that a failure means something other than slowness. */
    protected const GENEROUS = [10.0, 30.0];

    /**
     * The budget a real sweep runs on, read from config rather than copied, so a
     * change to the pacing shows up in the diagnosis instead of quietly diverging.
     *
     * @return array{0: float, 1: float}
     */
    protected function sweepBudget(): array
    {
        return [
            (float) config('services.polling.connect_timeout', 5.0),
            (float) config('services.polling.timeout', 10.0),
        ];
    }

    public function handle(): int
    {
        $this->line('poll:diagnose '.now()->toIso8601String().' php='.PHP_VERSION);
        $this->line('curl='.(curl_version()['version'] ?? '?').' ssl='.(curl_version()['ssl_version'] ?? '?'));

        $this->reportSweepHistory();
        $this->reportSingleFetchTiming();
        $this->reportPoolTiming();

        if (! $this->option('skip-diff')) {
            $this->reportGroundTruthDiff();
        }

        $this->reportDeletionTells();

        return self::SUCCESS;
    }

    /**
     * Section 1. Sweep shape separates the candidates: `blocked` near `channels_polled`
     * is a block-detection false positive, `not_modified` near it with `fetched` at zero
     * is wedged conditional-GET validators, and `failed` near it with `blocked` at zero
     * is a transport failure the block detector cannot see.
     */
    protected function reportSweepHistory(): void
    {
        $this->newLine();
        $this->components->info('Sweep history');

        $sweeps = PollSweep::query()
            ->orderByDesc('started_at')
            ->limit(max(1, (int) $this->option('sweeps')))
            ->get();

        if ($sweeps->isEmpty()) {
            $this->line('No sweeps recorded.');

            return;
        }

        foreach ($sweeps as $sweep) {
            $this->line(sprintf(
                '%s polled=%d fetched=%d notmod=%d failed=%d blocked=%d cap=%d cooldown=%d secs=%d',
                $sweep->started_at?->toDateTimeString() ?? 'unknown',
                $sweep->channels_polled,
                $sweep->fetched,
                $sweep->not_modified,
                $sweep->failed,
                $sweep->blocked,
                (int) $sweep->cap_hit,
                (int) $sweep->cooldown_triggered,
                $sweep->finished_at && $sweep->started_at
                    ? (int) abs($sweep->finished_at->diffInSeconds($sweep->started_at))
                    : -1,
            ));
        }
    }

    /**
     * Section 2. The sweep logs a `Response`-less failure as the bare string
     * `no_response`, which cannot distinguish DNS, TLS, a connect timeout, and a
     * read timeout. Catching the exception here is the only way to read that back.
     */
    protected function reportSingleFetchTiming(): void
    {
        $channels = $this->channels(max(1, (int) $this->option('timing')));

        if ($channels->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->components->info('Single-fetch timing, the sweep budget against generous');

        foreach ($channels as $channel) {
            foreach (['sweep' => $this->sweepBudget(), 'generous' => self::GENEROUS] as $label => [$connect, $total]) {
                $startedAt = microtime(true);

                try {
                    $response = Http::withHeaders($this->pollHeaders())
                        ->connectTimeout($connect)
                        ->timeout($total)
                        ->get($channel->rssUrl());

                    $this->line(sprintf(
                        '%s %-8s status=%d bytes=%d ms=%d atom=%s',
                        $channel->channel_id,
                        $label,
                        $response->status(),
                        strlen($response->body()),
                        $this->elapsedMs($startedAt),
                        str_contains(substr($response->body(), 0, 2048), '<feed') ? 'yes' : 'NO',
                    ));
                } catch (Throwable $e) {
                    $this->line(sprintf(
                        '%s %-8s EXCEPTION ms=%d %s: %s',
                        $channel->channel_id,
                        $label,
                        $this->elapsedMs($startedAt),
                        $e::class,
                        $e->getMessage(),
                    ));
                }
            }
        }
    }

    /**
     * Section 3. A sweep fetches in concurrent pools, so a per-request timing that
     * looks comfortable can still fail in a batch. This is the shape that matters.
     */
    protected function reportPoolTiming(): void
    {
        $channels = $this->channels(max(1, (int) ($this->option('pool') ?: config('services.polling.pool_chunk', 5))));

        if ($channels->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->components->info('Pool timing, the real sweep shape');

        foreach (['sweep' => $this->sweepBudget(), 'generous' => self::GENEROUS] as $label => [$connect, $total]) {
            $startedAt = microtime(true);

            $responses = Http::pool(fn (Pool $pool) => $channels->map(
                fn (Channel $channel) => $pool
                    ->as((string) $channel->id)
                    ->withHeaders($this->pollHeaders())
                    ->connectTimeout($connect)
                    ->timeout($total)
                    ->get($channel->rssUrl())
            )->all());

            $tally = [];

            foreach ($channels as $channel) {
                $result = $responses[(string) $channel->id] ?? null;

                $key = match (true) {
                    $result instanceof Response => 'http_'.$result->status(),
                    $result instanceof Throwable => $result::class.': '.substr($result->getMessage(), 0, 120),
                    default => 'no_result_'.get_debug_type($result),
                };

                $tally[$key] = ($tally[$key] ?? 0) + 1;
            }

            $this->line(sprintf('pool %-8s channels=%d total_ms=%d', $label, $channels->count(), $this->elapsedMs($startedAt)));

            foreach ($tally as $key => $count) {
                $this->line(sprintf('   %3d x %s', $count, $key));
            }
        }
    }

    /**
     * Section 4. Ground truth. Every health signal can read green while uploads are
     * absent, so the only proof is the live feed diffed against what we stored.
     * Generous timeouts and a sequential pace, because this is a diagnosis and not
     * a sweep: a failure here is a real failure, not a budget.
     */
    protected function reportGroundTruthDiff(): void
    {
        $this->newLine();
        $this->components->info('Live RSS against the videos table');

        $sleepMicroseconds = max(0, (int) $this->option('sleep')) * 1000;
        $missing = [];
        $channelsOk = 0;
        $channelsFailed = 0;
        $entries = 0;
        $shortsEntries = 0;
        $storedShorts = [];

        foreach ($this->channels($this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null) as $channel) {
            $feed = $this->fetchFeed($channel);

            if ($feed === null) {
                $channelsFailed++;
                $this->maybeSleep($sleepMicroseconds);

                continue;
            }

            $channelsOk++;
            $namespaces = $feed->getNamespaces(true);

            foreach ($feed->entry ?? [] as $entry) {
                $yt = isset($namespaces['yt']) ? $entry->children($namespaces['yt']) : null;
                $videoId = $yt && isset($yt->videoId) ? (string) $yt->videoId : null;

                if ($videoId === null || $videoId === '') {
                    continue;
                }

                $href = $this->alternateHref($entry);
                $isShort = str_contains((string) parse_url((string) $href, PHP_URL_PATH), '/shorts/');

                $entries++;
                $shortsEntries += $isShort ? 1 : 0;

                $stored = Video::query()->where('youtube_video_id', $videoId)->exists();

                // A stored row whose href is now under /shorts/ is flagged short-form by
                // `RssFetcher::ingest` on the next successful poll and drops out of the
                // feed. The row and its watched state stay, so this is reversible.
                if ($stored && $isShort) {
                    $storedShorts[] = sprintf(
                        '%s | %s | states=%d | %s',
                        $channel->channel_id,
                        $videoId,
                        UserVideoState::query()->where('youtube_video_id', $videoId)->count(),
                        $this->trimTitle($entry),
                    );
                }

                if ($stored) {
                    continue;
                }

                $missing[] = [
                    'channel' => $channel->channel_id,
                    'video' => $videoId,
                    'published' => isset($entry->published) ? (string) $entry->published : 'unknown',
                    'short' => $isShort,
                    'href' => (string) $href,
                    'states' => UserVideoState::query()->where('youtube_video_id', $videoId)->count(),
                    'title' => $this->trimTitle($entry),
                ];
            }

            $this->maybeSleep($sleepMicroseconds);
        }

        $missingShorts = count(array_filter($missing, fn (array $row) => $row['short']));

        $this->newLine();
        $this->line(sprintf(
            'channels ok=%d failed=%d | rss entries=%d (shorts=%d) | missing from db=%d (of which shorts=%d)',
            $channelsOk,
            $channelsFailed,
            $entries,
            $shortsEntries,
            count($missing),
            $missingShorts,
        ));

        $this->newLine();
        $this->line('Stored rows a /shorts/ href will flag on the next successful poll: '.count($storedShorts));

        foreach ($storedShorts as $line) {
            $this->line('  '.$line);
        }

        $this->newLine();
        $this->line('Missing entries:');

        foreach (array_slice($missing, 0, max(0, (int) $this->option('list'))) as $row) {
            $this->line(sprintf(
                '%s | %s | %s | %s | states=%d | %s | %s',
                $row['channel'],
                $row['video'],
                $row['published'],
                $row['short'] ? 'SHORT' : 'watch',
                $row['states'],
                $row['title'],
                $row['href'],
            ));
        }

        $this->probeShorts(array_values(array_filter($missing, fn (array $row) => $row['short'])));
    }

    /**
     * Whether the /shorts/ href we flag on actually agrees with YouTube. A landscape
     * or long video canonicalised under /shorts/ would be a false positive, and the
     * flag is invisible in the feed, so this is the way to catch one.
     *
     * From a datacenter IP YouTube serves an interstitial with no player payload:
     * canonical reads `watch`, duration is absent, and dimensions come back 140x100.
     * Printing that as `watch` would be a confident wrong answer, so a page with no
     * player payload is reported `unclassified` instead.
     *
     * @param  list<array{video: string, title: string}>  $shorts
     */
    protected function probeShorts(array $shorts): void
    {
        $limit = max(0, (int) $this->option('shorts-probe'));

        if ($limit === 0 || $shorts === []) {
            return;
        }

        $this->newLine();
        $this->components->info('Shorts probe: RSS href against the watch page');
        $this->line(sprintf('%-13s %-13s %-8s %s', 'video', 'canonical', 'seconds', 'ratio'));

        $probed = 0;
        $unclassified = 0;
        $sleepMicroseconds = max(0, (int) $this->option('sleep')) * 1000;

        foreach (array_slice($shorts, 0, $limit) as $row) {
            $probed++;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => (string) config('services.websub.user_agent'),
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                    ->connectTimeout(self::GENEROUS[0])
                    ->timeout(self::GENEROUS[1])
                    ->get('https://www.youtube.com/watch?v='.$row['video']);
            } catch (Throwable $e) {
                $this->line(sprintf('%-13s %s: %s', $row['video'], $e::class, substr($e->getMessage(), 0, 80)));

                continue;
            }

            if (! $response->successful()) {
                $this->line(sprintf('%-13s http_%d', $row['video'], $response->status()));

                continue;
            }

            $html = $response->body();

            $canonical = preg_match('/<link\s[^>]*rel="canonical"[^>]*href="([^"]+)"/i', $html, $match)
                ? $match[1]
                : '';

            $hasPlayerPayload = preg_match('/"approxDurationMs":"(\d+)"/', $html, $duration) === 1;

            $seconds = $hasPlayerPayload
                ? (string) (int) round(((int) $duration[1]) / 1000)
                : '?';

            $ratio = preg_match('/"width":(\d+),"height":(\d+)/', $html, $size) && (int) $size[2] > 0
                ? ((int) $size[1] < (int) $size[2] ? 'portrait ' : 'landscape ').$size[1].'x'.$size[2]
                : '?';

            if (! $hasPlayerPayload) {
                $unclassified++;
                $this->line(sprintf(
                    '%-13s %-13s no player payload: this IP is being served an interstitial',
                    $row['video'],
                    'unclassified',
                ));

                $this->maybeSleep($sleepMicroseconds);

                continue;
            }

            $this->line(sprintf(
                '%-13s %-13s %-8s %s',
                $row['video'],
                str_contains((string) parse_url($canonical, PHP_URL_PATH), '/shorts/') ? 'shorts' : 'watch',
                $seconds,
                $ratio,
            ));

            $this->maybeSleep($sleepMicroseconds);
        }

        if ($probed > 0 && $unclassified === $probed) {
            $this->components->warn(
                'Shorts probe unusable from this IP: every page came back without a player payload. '
                .'Run it from a residential connection, or trust nothing it printed.'
            );
        }
    }

    /**
     * Section 5. Nothing in the poll path deletes a video any more (a Short is flagged
     * in place), so any orphaned `user_video_states` row is evidence of a deletion path
     * from before [036], or of one we do not know about.
     */
    protected function reportDeletionTells(): void
    {
        $this->newLine();
        $this->components->info('Deletion tells');

        $orphans = UserVideoState::query()
            ->whereNotIn('youtube_video_id', Video::query()->select('youtube_video_id'))
            ->count();

        $this->line('videos: '.Video::query()->count());
        $this->line('user_video_states: '.UserVideoState::query()->count());
        $this->line('user_video_states with no videos row: '.$orphans);
        $this->line('newest published_at: '.(string) (Video::query()->max('published_at') ?? 'none'));
        $this->line('newest created_at: '.(string) (Video::query()->max('created_at') ?? 'none'));
        $this->line('videos created in the last 24h: '.Video::query()->where('created_at', '>=', now()->subDay())->count());
    }

    /**
     * @return Collection<int, Channel>
     */
    protected function channels(?int $limit = null): Collection
    {
        $query = Channel::query()->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    protected function fetchFeed(Channel $channel): ?SimpleXMLElement
    {
        try {
            $response = Http::withHeaders($this->pollHeaders())
                ->connectTimeout(self::GENEROUS[0])
                ->timeout(self::GENEROUS[1])
                ->get($channel->rssUrl());
        } catch (Throwable $e) {
            $this->line(sprintf('FETCH-ERR %s %s: %s', $channel->channel_id, $e::class, substr($e->getMessage(), 0, 120)));

            return null;
        }

        if (! $response->successful()) {
            $this->line(sprintf('FETCH-ERR %s http_%d', $channel->channel_id, $response->status()));

            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($response->body());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($feed === false) {
            $this->line(sprintf(
                'PARSE-ERR %s bytes=%d head=%s',
                $channel->channel_id,
                strlen($response->body()),
                substr((string) preg_replace('/\s+/', ' ', $response->body()), 0, 120),
            ));

            return null;
        }

        return $feed;
    }

    /**
     * The same headers the sweep sends, so a difference here is never the headers.
     *
     * @return array<string, string>
     */
    protected function pollHeaders(): array
    {
        return [
            'User-Agent' => (string) config('services.websub.user_agent'),
            'Accept-Encoding' => 'gzip, deflate',
            'Accept' => 'application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
        ];
    }

    protected function alternateHref(SimpleXMLElement $entry): ?string
    {
        foreach ($entry->link ?? [] as $link) {
            $attributes = $link->attributes();

            if (isset($attributes['rel'], $attributes['href']) && (string) $attributes['rel'] === 'alternate') {
                $href = trim((string) $attributes['href']);

                return $href !== '' ? $href : null;
            }
        }

        return null;
    }

    protected function trimTitle(SimpleXMLElement $entry): string
    {
        return substr(trim((string) ($entry->title ?? '')), 0, 60);
    }

    protected function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    protected function maybeSleep(int $microseconds): void
    {
        if ($microseconds > 0) {
            usleep($microseconds);
        }
    }
}
