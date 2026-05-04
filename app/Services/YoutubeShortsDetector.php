<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YoutubeShortsDetector
{
    public function __construct(
        protected float $connectTimeoutSeconds = 3.0,
        protected float $timeoutSeconds = 12.0,
    ) {}

    /**
     * Uses the public watch page HTML: Shorts use a canonical URL under `/shorts/`.
     *
     * @return null when the page could not be fetched or no canonical link was found
     */
    public function isShortByWatchPage(string $youtubeVideoId): ?bool
    {
        if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeVideoId)) {
            return null;
        }

        $response = Http::connectTimeout($this->connectTimeoutSeconds)
            ->timeout($this->timeoutSeconds)
            ->withHeaders([
                'Accept-Language' => 'en-US,en;q=0.9',
                'User-Agent' => 'Mozilla/5.0 (compatible; yt-rss/1.0; +https://github.com/)',
            ])
            ->get('https://www.youtube.com/watch?v='.$youtubeVideoId);

        if (! $response->successful()) {
            return null;
        }

        $canonical = $this->extractCanonicalHref($response->body());

        if ($canonical === null || $canonical === '') {
            return null;
        }

        $path = parse_url($canonical, PHP_URL_PATH) ?? '';

        return str_contains($path, '/shorts/');
    }

    protected function extractCanonicalHref(string $html): ?string
    {
        if (preg_match('/<link\s[^>]*\brel\s*=\s*"canonical"[^>]*\bhref\s*=\s*"([^"]+)"/i', $html, $matches)) {
            return $matches[1];
        }

        if (preg_match('/<link\s[^>]*\bhref\s*=\s*"([^"]+)"[^>]*\brel\s*=\s*"canonical"/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
