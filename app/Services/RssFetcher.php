<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\Video;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class RssFetcher
{
    public function __construct(
        protected int $ttlMinutes = 30,
        protected int $poolChunkSize = 20,
        protected float $connectTimeoutSeconds = 2.0,
        protected float $timeoutSeconds = 3.0,
        protected int $interChunkDelayMs = 0,
    ) {
        if ($this->poolChunkSize < 1) {
            $this->poolChunkSize = 1;
        }

        if ($this->interChunkDelayMs < 0) {
            $this->interChunkDelayMs = 0;
        }
    }

    /**
     * A copy of this fetcher on the sweep budget.
     *
     * A feed load has a user waiting on it, so it stays impatient. A sweep has
     * nobody waiting and 193 channels to get through, so it trades wall-clock for
     * arriving under whatever rate the endpoint answers: smaller batches, a gap
     * between them, and timeouts wide enough that pacing is what does the work.
     */
    public function forSweep(): self
    {
        return new self(
            ttlMinutes: $this->ttlMinutes,
            poolChunkSize: max(1, (int) config('services.polling.pool_chunk', 5)),
            connectTimeoutSeconds: (float) config('services.polling.connect_timeout', 5.0),
            timeoutSeconds: (float) config('services.polling.timeout', 10.0),
            interChunkDelayMs: max(0, (int) config('services.polling.inter_chunk_delay_ms', 500)),
        );
    }

    /**
     * Fetch RSS for all channels in a group, refreshing stale ones.
     *
     * @return array{fetched: int, failed: int, skipped: int, not_modified: int, blocked: int, failures: array<string, int>, shorts_flagged: int}
     */
    public function fetchForGroup(ChannelGroup $group, bool $force = false): array
    {
        return $this->fetchForChannels($group->channels()->get(), $force);
    }

    /**
     * Fetch RSS for a collection of channels, refreshing stale ones.
     *
     * @param  Collection<int, Channel>  $channels
     * @return array{fetched: int, failed: int, skipped: int, not_modified: int, blocked: int, failures: array<string, int>, shorts_flagged: int}
     */
    public function fetchForChannels(Collection $channels, bool $force = false): array
    {
        $stale = $channels->filter(
            fn (Channel $c) => $force || $this->isStale($c)
        )->values();

        if ($stale->isEmpty()) {
            return [
                'fetched' => 0,
                'failed' => 0,
                'skipped' => $channels->count(),
                'not_modified' => 0,
                'blocked' => 0,
                'failures' => [],
                'shorts_flagged' => 0,
            ];
        }

        $fetched = 0;
        $failed = 0;
        $notModified = 0;
        $blocked = 0;

        $shortsFlagged = 0;

        /** @var array<string, int> $failures */
        $failures = [];

        foreach ($stale->chunk($this->poolChunkSize)->values() as $index => $batch) {
            // Gap between batches, never after the last one: the pause is pacing for
            // the next batch, not a tax on finishing.
            if ($index > 0 && $this->interChunkDelayMs > 0) {
                Sleep::usleep($this->interChunkDelayMs * 1000);
            }

            $responses = Http::pool(fn (Pool $pool) => $batch->map(
                fn (Channel $c) => $pool
                    ->as((string) $c->id)
                    ->withHeaders($this->pollHeaders($c))
                    ->connectTimeout($this->connectTimeoutSeconds)
                    ->timeout($this->timeoutSeconds)
                    ->get($c->rssUrl())
            )->all());

            foreach ($batch as $channel) {
                $resp = $responses[(string) $channel->id] ?? null;

                if ($resp instanceof Response && $resp->status() === 304) {
                    $notModified++;
                    $channel->forceFill(array_filter([
                        'last_fetched_at' => now(),
                        // Some origins rotate validators on a 304; keep ours current.
                        'rss_etag' => $this->validator($resp->header('ETag')),
                        'rss_last_modified' => $this->validator($resp->header('Last-Modified')),
                    ], fn ($value) => $value !== null))->save();

                    continue;
                }

                $blockSignal = $resp instanceof Response ? $this->blockSignal($resp) : null;

                if ($blockSignal !== null) {
                    $blocked++;
                    Log::warning('RSS block signal', [
                        'channel_id' => $channel->channel_id,
                        'signal' => $blockSignal,
                        'status' => $resp->status(),
                    ]);

                    continue;
                }

                if (! $resp instanceof Response || ! $resp->successful()) {
                    $failed++;

                    // `Http::pool` hands back the transport exception in the response
                    // slot rather than throwing it, so without this every DNS, TLS,
                    // connect and read failure logged as the same opaque string.
                    $category = $resp instanceof \Throwable
                        ? $this->transportFailureCategory($resp)
                        : 'http_error';

                    $failures[$category] = ($failures[$category] ?? 0) + 1;

                    Log::warning('RSS fetch failed', [
                        'channel_id' => $channel->channel_id,
                        'status' => $resp instanceof Response ? $resp->status() : 'no_response',
                        'category' => $category,
                        'exception' => $resp instanceof \Throwable ? $resp::class : null,
                        'error' => $resp instanceof \Throwable ? $resp->getMessage() : null,
                    ]);

                    continue;
                }

                try {
                    $shortsFlagged += $this->ingest($channel, $resp->body())['shorts'];
                    $channel->forceFill([
                        'last_fetched_at' => now(),
                        'rss_etag' => $this->validator($resp->header('ETag')),
                        'rss_last_modified' => $this->validator($resp->header('Last-Modified')),
                    ])->save();
                    $fetched++;
                } catch (\Throwable $e) {
                    $failed++;
                    $failures['parse_error'] = ($failures['parse_error'] ?? 0) + 1;
                    Log::warning('RSS parse failed', [
                        'channel_id' => $channel->channel_id,
                        'category' => 'parse_error',
                        'exception' => $e::class,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        arsort($failures);

        return [
            'fetched' => $fetched,
            'failed' => $failed,
            'skipped' => $channels->count() - $stale->count(),
            'not_modified' => $notModified,
            'blocked' => $blocked,
            'failures' => $failures,
            'shorts_flagged' => $shortsFlagged,
        ];
    }

    /**
     * Coarse category for a transport failure, from the cURL errno Guzzle puts in
     * the message.
     *
     * Guzzle raises `ConnectException` for the whole connect phase, DNS and TLS
     * included, so the class alone separates nothing. Errno 28 covers both timeout
     * kinds and only the message text tells them apart: a connect-phase timeout
     * reads "Connection timed out", while a total timeout on an accepted connection
     * reads "Operation timed out ... with 0 bytes received", which is the signature
     * of the production failure storm.
     */
    protected function transportFailureCategory(\Throwable $e): string
    {
        $message = $e->getMessage();

        $errno = preg_match('/cURL error (\d+)/i', $message, $matches)
            ? (int) $matches[1]
            : null;

        return match (true) {
            // CURLE_COULDNT_RESOLVE_PROXY, CURLE_COULDNT_RESOLVE_HOST
            $errno === 5 || $errno === 6 => 'dns',
            // CURLE_SSL_CONNECT_ERROR, CURLE_PEER_FAILED_VERIFICATION, CURLE_SSL_CACERT
            $errno === 35 || $errno === 60 || $errno === 58 || $errno === 83 => 'tls',
            // CURLE_COULDNT_CONNECT
            $errno === 7 => 'connect_failed',
            // CURLE_OPERATION_TIMEDOUT
            $errno === 28 => str_contains($message, 'Connection timed out')
                || str_contains($message, 'Connection timeout')
                    ? 'connect_timeout'
                    : 'read_timeout',
            // CURLE_GOT_NOTHING
            $errno === 52 => 'empty_response',
            default => 'other',
        };
    }

    /**
     * Why this response looks like a block rather than an ordinary failure, or null.
     *
     * A YouTube RSS block does not arrive as a 429. Across every documented case it
     * is an HTTP 403, or an HTTP 200 carrying Google's "automated queries" HTML
     * interstitial in place of Atom, so a metric counting only 429s reads zero
     * through the whole outage.
     */
    protected function blockSignal(Response $response): ?string
    {
        if ($response->status() === 403) {
            return 'http_403';
        }

        if ($response->status() === 429) {
            return 'http_429';
        }

        if ($response->status() === 200 && ! $this->looksLikeAtom($response->body())) {
            return 'non_atom_200';
        }

        return null;
    }

    /**
     * True when the body opens an Atom feed, rather than an HTML interstitial.
     */
    protected function looksLikeAtom(string $body): bool
    {
        $head = ltrim(substr($body, 0, 2048));

        if ($head === '') {
            return false;
        }

        return (bool) preg_match('/<(?:[A-Za-z0-9_-]+:)?feed[\s>]/', $head);
    }

    /**
     * A cache validator we are willing to store and replay.
     *
     * The origin controls this value, so an oversized or control-character-laden
     * header must not be written to a fixed-width column (a failed write would
     * also lose `last_fetched_at` and pin the channel as permanently stale).
     */
    protected function validator(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/[^ -~]/', '', $value) ?? '';

        if ($clean === '' || strlen($clean) > 200) {
            return null;
        }

        return $clean;
    }

    /**
     * Polite polling headers: browser UA, gzip, and conditional GET validators.
     *
     * @return array<string, string>
     */
    protected function pollHeaders(Channel $channel): array
    {
        $headers = [
            'User-Agent' => (string) config('services.websub.user_agent'),
            'Accept-Encoding' => 'gzip, deflate',
            'Accept' => 'application/atom+xml, application/xml, text/xml;q=0.9, */*;q=0.8',
        ];

        if (filled($channel->rss_etag)) {
            $headers['If-None-Match'] = (string) $channel->rss_etag;
        }

        if (filled($channel->rss_last_modified)) {
            $headers['If-Modified-Since'] = (string) $channel->rss_last_modified;
        }

        return $headers;
    }

    protected function isStale(Channel $channel): bool
    {
        if ($channel->last_fetched_at === null) {
            return true;
        }

        return $channel->last_fetched_at->lt(
            CarbonImmutable::now()->subMinutes($this->ttlMinutes)
        );
    }

    /**
     * Parse YouTube RSS XML and upsert videos for the channel.
     *
     * @return array{stored: int, shorts: int}
     */
    public function ingest(Channel $channel, string $xml): array
    {
        $previous = libxml_use_internal_errors(true);

        $feed = simplexml_load_string($xml);

        if ($feed === false) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            throw new \RuntimeException('Invalid RSS XML');
        }

        libxml_use_internal_errors($previous);

        $namespaces = $feed->getNamespaces(true);
        $count = 0;
        $shorts = 0;

        if (isset($feed->title)) {
            $title = trim((string) $feed->title);

            if ($title !== '' && $channel->name !== $title) {
                $channel->name = $title;
            }
        }

        foreach ($feed->entry ?? [] as $entry) {
            $yt = isset($namespaces['yt']) ? $entry->children($namespaces['yt']) : null;
            $media = isset($namespaces['media']) ? $entry->children($namespaces['media']) : null;

            $videoId = $yt && isset($yt->videoId) ? (string) $yt->videoId : null;

            if (! $videoId) {
                continue;
            }

            // Flag, never delete. YouTube's Shorts ceiling is three minutes, so a 2:50
            // vertical upload is classified here too, and a flag makes that a filter the
            // user can disagree with rather than data that quietly disappeared.
            $isShort = $this->alternateHrefIsShort($this->entryAlternateHref($entry));

            if ($isShort) {
                $shorts++;
            }

            $title = (string) ($entry->title ?? '');
            $publishedAt = isset($entry->published)
                ? CarbonImmutable::parse((string) $entry->published)
                : CarbonImmutable::now();

            $thumbnail = null;
            if ($media && isset($media->group->thumbnail)) {
                $thumb = $media->group->thumbnail->attributes();
                $thumbnail = isset($thumb['url']) ? (string) $thumb['url'] : null;
            }

            $attributes = [
                'channel_id' => $channel->id,
                'title' => $title,
                'thumbnail_url' => $thumbnail,
                'published_at' => $publishedAt,
            ];

            // The flag only ever goes on. `videos:prune-shorts` classifies off the watch
            // page, which sees Shorts this href does not, and a sweep re-ingesting the
            // same entry every 30 minutes must not argue with the stronger signal.
            if ($isShort) {
                $attributes['is_short'] = true;
            }

            Video::updateOrCreate(['youtube_video_id' => $videoId], $attributes);

            $count++;
        }

        return ['stored' => $count, 'shorts' => $shorts];
    }

    /**
     * Atom alternate link for the video (watch or shorts URL).
     */
    protected function entryAlternateHref(\SimpleXMLElement $entry): ?string
    {
        if (! isset($entry->link)) {
            return null;
        }

        foreach ($entry->link as $link) {
            $attrs = $link->attributes();
            if (! isset($attrs['rel'], $attrs['href'])) {
                continue;
            }

            if ((string) $attrs['rel'] !== 'alternate') {
                continue;
            }

            $href = trim((string) $attrs['href']);

            return $href !== '' ? $href : null;
        }

        return null;
    }

    /**
     * True when alternate link path contains `/shorts/` (YouTube Shorts feed entries).
     */
    protected function alternateHrefIsShort(?string $href): bool
    {
        if ($href === null || $href === '') {
            return false;
        }

        $path = parse_url($href, PHP_URL_PATH) ?? '';

        return str_contains($path, '/shorts/');
    }
}
