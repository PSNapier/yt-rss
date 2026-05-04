<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChannelResolver
{
    public function __construct(
        protected ?string $apiKey = null,
    ) {
        $this->apiKey ??= config('services.youtube.api_key');
    }

    /**
     * Resolve channel info from a manually-supplied channel ID (UCxxxx).
     * Tries to enrich name via YouTube API if key is set; otherwise uses placeholder.
     *
     * @return array{channel_id: string, name: string, rss_url: string}
     */
    public function fromChannelId(string $channelId): array
    {
        $channelId = trim($channelId);

        if (! $this->isValidChannelId($channelId)) {
            throw new \InvalidArgumentException('Channel ID must look like UC followed by 22 chars.');
        }

        $name = $this->lookupChannelName($channelId) ?? $channelId;

        return [
            'channel_id' => $channelId,
            'name' => $name,
            'rss_url' => $this->rssUrl($channelId),
        ];
    }

    /**
     * Resolve a YouTube @handle (or full URL containing one) to channel info.
     *
     * @return array{channel_id: string, name: string, rss_url: string}
     */
    public function fromHandle(string $input): array
    {
        if (! $this->apiKey) {
            throw new \RuntimeException(
                'YOUTUBE_API_KEY not configured. Use the channel ID input instead.'
            );
        }

        $handle = $this->extractHandle($input);

        $resp = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'id,snippet',
            'forHandle' => $handle,
            'key' => $this->apiKey,
        ]);

        if (! $resp->successful()) {
            throw new \RuntimeException('YouTube API error: '.$resp->status());
        }

        $items = $resp->json('items') ?? [];

        if ($items === []) {
            throw new \RuntimeException("No channel found for handle: {$handle}");
        }

        $item = $items[0];
        $channelId = $item['id'] ?? null;
        $name = $item['snippet']['title'] ?? $handle;

        if (! $channelId) {
            throw new \RuntimeException('YouTube API response missing channel id.');
        }

        return [
            'channel_id' => $channelId,
            'name' => $name,
            'rss_url' => $this->rssUrl($channelId),
        ];
    }

    protected function isValidChannelId(string $id): bool
    {
        return (bool) preg_match('/^UC[A-Za-z0-9_-]{22}$/', $id);
    }

    protected function rssUrl(string $channelId): string
    {
        return 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId;
    }

    protected function extractHandle(string $input): string
    {
        $input = trim($input);

        if (preg_match('#youtube\.com/@([A-Za-z0-9._-]+)#', $input, $m)) {
            return '@'.$m[1];
        }

        if (str_starts_with($input, '@')) {
            return $input;
        }

        return '@'.ltrim($input, '@');
    }

    protected function lookupChannelName(string $channelId): ?string
    {
        if (! $this->apiKey) {
            return null;
        }

        try {
            $resp = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet',
                'id' => $channelId,
                'key' => $this->apiKey,
            ]);

            return $resp->json('items.0.snippet.title');
        } catch (\Throwable) {
            return null;
        }
    }
}
