<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\UserVideoState;
use App\Models\Video;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RssFetcher
{
    public function __construct(
        protected int $ttlMinutes = 30,
        protected int $poolChunkSize = 20,
        protected float $connectTimeoutSeconds = 2.0,
        protected float $timeoutSeconds = 3.0,
    ) {
        if ($this->poolChunkSize < 1) {
            $this->poolChunkSize = 1;
        }
    }

    /**
     * Fetch RSS for all channels in a group, refreshing stale ones.
     *
     * @return array{fetched: int, failed: int, skipped: int}
     */
    public function fetchForGroup(ChannelGroup $group, bool $force = false): array
    {
        $channels = $group->channels()->get();

        $stale = $channels->filter(
            fn (Channel $c) => $force || $this->isStale($c)
        )->values();

        if ($stale->isEmpty()) {
            return ['fetched' => 0, 'failed' => 0, 'skipped' => $channels->count()];
        }

        $fetched = 0;
        $failed = 0;

        foreach ($stale->chunk($this->poolChunkSize) as $batch) {
            $responses = Http::pool(fn (Pool $pool) => $batch->map(
                fn (Channel $c) => $pool
                    ->as((string) $c->id)
                    ->connectTimeout($this->connectTimeoutSeconds)
                    ->timeout($this->timeoutSeconds)
                    ->get($c->rssUrl())
            )->all());

            foreach ($batch as $channel) {
                $resp = $responses[(string) $channel->id] ?? null;

                if (! $resp instanceof Response || ! $resp->successful()) {
                    $failed++;
                    Log::warning('RSS fetch failed', [
                        'channel_id' => $channel->channel_id,
                        'status' => $resp instanceof Response ? $resp->status() : 'no_response',
                    ]);

                    continue;
                }

                try {
                    $this->ingest($channel, $resp->body());
                    $channel->forceFill(['last_fetched_at' => now()])->save();
                    $fetched++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('RSS parse failed', [
                        'channel_id' => $channel->channel_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return [
            'fetched' => $fetched,
            'failed' => $failed,
            'skipped' => $channels->count() - $stale->count(),
        ];
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
     */
    public function ingest(Channel $channel, string $xml): int
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

        if (! isset($feed->title) || trim((string) $feed->title) === '') {
            // skip
        } elseif ($channel->name === '' || $channel->name === null) {
            $channel->name = (string) $feed->title;
        }

        foreach ($feed->entry ?? [] as $entry) {
            $yt = isset($namespaces['yt']) ? $entry->children($namespaces['yt']) : null;
            $media = isset($namespaces['media']) ? $entry->children($namespaces['media']) : null;

            $videoId = $yt && isset($yt->videoId) ? (string) $yt->videoId : null;

            if (! $videoId) {
                continue;
            }

            $alternateHref = $this->entryAlternateHref($entry);
            if ($this->alternateHrefIsShort($alternateHref)) {
                UserVideoState::query()->where('youtube_video_id', $videoId)->delete();
                Video::query()->where('youtube_video_id', $videoId)->delete();

                continue;
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

            Video::updateOrCreate(
                ['youtube_video_id' => $videoId],
                [
                    'channel_id' => $channel->id,
                    'title' => $title,
                    'thumbnail_url' => $thumbnail,
                    'published_at' => $publishedAt,
                ]
            );

            $count++;
        }

        return $count;
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
