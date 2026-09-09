<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Http;

class ChannelResolver
{
    protected YoutubeApiClient $api;

    public function __construct(
        protected ?string $apiKey = null,
        ?YoutubeApiClient $api = null,
    ) {
        $this->apiKey ??= config('services.youtube.api_key');
        $this->api = $api ?? new YoutubeApiClient($this->apiKey);
    }

    /**
     * Sniff whatever the user pasted and route it to the cheapest path that can
     * resolve it. Channel IDs and /channel/ URLs never touch the Data API.
     *
     * @return array{channel_id: string, name: string, rss_url: string, handle: ?string}
     */
    public function resolve(string $input): array
    {
        $input = trim($input);

        if ($input === '') {
            throw new \InvalidArgumentException('Paste a YouTube channel URL, @handle, or channel ID.');
        }

        // Free: a raw UC id.
        if ($this->isValidChannelId($input)) {
            return $this->fromChannelId($input);
        }

        // Free: a /channel/UC… URL carries the id already.
        if (preg_match('#/channel/(UC[A-Za-z0-9_-]{22})#i', $input, $m)) {
            return $this->fromChannelId($m[1]);
        }

        if (preg_match('#/channel/#i', $input)) {
            throw new \InvalidArgumentException('That /channel/ URL does not contain a valid channel ID.');
        }

        // Rejected: resolving a /c/ vanity URL would cost a 100-unit search.list.
        if (preg_match('#/c/#i', $input)) {
            throw new \InvalidArgumentException(
                'Custom /c/ URLs cannot be resolved. Open the channel and paste its @handle instead.'
            );
        }

        // Counted: a legacy /user/ URL resolves via forUsername.
        if (preg_match('#/user/([A-Za-z0-9._-]+)#i', $input, $m)) {
            return $this->fromUsername($m[1]);
        }

        // Counted: everything else is treated as a handle.
        return $this->fromHandle($input);
    }

    /**
     * Resolve channel info from a manually-supplied channel ID (UCxxxx).
     * Tries to enrich name via RSS, then the Data API; otherwise uses the id.
     *
     * @return array{channel_id: string, name: string, rss_url: string, handle: ?string}
     */
    public function fromChannelId(string $channelId): array
    {
        $channelId = trim($channelId);

        if (! $this->isValidChannelId($channelId)) {
            throw new \InvalidArgumentException('Channel ID must look like UC followed by 22 chars.');
        }

        $name = $this->lookupChannelNameFromRss($channelId)
            ?? $this->lookupChannelName($channelId)
            ?? $channelId;

        return [
            'channel_id' => $channelId,
            'name' => $name,
            'rss_url' => $this->rssUrl($channelId),
            'handle' => null,
        ];
    }

    /**
     * Resolve a YouTube @handle (or full URL containing one) to channel info.
     * A handle already cached on a channel row resolves for free.
     *
     * @return array{channel_id: string, name: string, rss_url: string, handle: ?string}
     */
    public function fromHandle(string $input): array
    {
        $handle = $this->extractHandle($input);
        $normalized = $this->normalizeHandle($handle);

        if ($cached = Channel::query()->where('handle', $normalized)->first()) {
            return [
                'channel_id' => $cached->channel_id,
                'name' => $cached->name,
                'rss_url' => $cached->rssUrl(),
                'handle' => $normalized,
            ];
        }

        $items = $this->api->channelsList([
            'part' => 'id,snippet',
            'forHandle' => $handle,
        ]);

        if ($items === []) {
            throw new \RuntimeException("No channel found for handle: {$handle}");
        }

        return $this->fromApiItem($items[0], $normalized);
    }

    /**
     * Resolve a legacy /user/ name via forUsername.
     *
     * @return array{channel_id: string, name: string, rss_url: string, handle: ?string}
     */
    public function fromUsername(string $username): array
    {
        $items = $this->api->channelsList([
            'part' => 'id,snippet',
            'forUsername' => $username,
        ]);

        if ($items === []) {
            throw new \RuntimeException("No channel found for user: {$username}");
        }

        return $this->fromApiItem($items[0], null);
    }

    /**
     * Build channel info from a channels.list item. The id+snippet the lookup
     * already returned covers the name, so enrichment costs no extra unit.
     *
     * @param  array<string, mixed>  $item
     * @return array{channel_id: string, name: string, rss_url: string, handle: ?string}
     */
    protected function fromApiItem(array $item, ?string $fallbackHandle): array
    {
        $channelId = $item['id'] ?? null;

        if (! $channelId) {
            throw new \RuntimeException('YouTube API response missing channel id.');
        }

        $customUrl = $item['snippet']['customUrl'] ?? null;

        $handle = is_string($customUrl) && str_starts_with($customUrl, '@')
            ? $this->normalizeHandle($customUrl)
            : $fallbackHandle;

        return [
            'channel_id' => $channelId,
            'name' => $item['snippet']['title'] ?? $handle ?? $channelId,
            'rss_url' => $this->rssUrl($channelId),
            'handle' => $handle,
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

    /** Handles are case-insensitive on YouTube; store one canonical form. */
    protected function normalizeHandle(string $handle): string
    {
        return '@'.mb_strtolower(ltrim(trim($handle), '@'));
    }

    public function lookupChannelNameFromRss(string $channelId): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => (string) config('services.websub.user_agent'),
                    'Accept-Encoding' => 'gzip, deflate',
                ])
                ->get($this->rssUrl($channelId));

            if (! $response->successful()) {
                return null;
            }

            $xml = $response->body();

            if ($xml === '') {
                return null;
            }

            $previous = libxml_use_internal_errors(true);
            $feed = simplexml_load_string($xml);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            if ($feed === false || ! isset($feed->title)) {
                return null;
            }

            $title = trim((string) $feed->title);

            return $title !== '' ? $title : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Last-resort name enrichment. Counted like any other Data API call, and
     * skipped when the daily budget is spent: a missing name is not worth a
     * unit that a real resolution needs.
     */
    protected function lookupChannelName(string $channelId): ?string
    {
        if (! $this->api->hasKey() || ! $this->api->hasBudget()) {
            return null;
        }

        try {
            $items = $this->api->channelsList([
                'part' => 'snippet',
                'id' => $channelId,
            ]);

            return $items[0]['snippet']['title'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }
}
