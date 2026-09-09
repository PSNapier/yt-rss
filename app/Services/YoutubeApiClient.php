<?php

namespace App\Services;

use App\Models\YoutubeApiUsage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

/**
 * The single door to the YouTube Data API.
 *
 * Every request routed through here ticks a durable per-day counter before it
 * leaves, so nothing we spend escapes the budget. The cap sits below Google's
 * free 10,000 units/day to leave headroom for anything that slips.
 */
class YoutubeApiClient
{
    /** Self-imposed daily ceiling, below Google's free 10,000 units/day. */
    public const DAILY_CAP = 9500;

    /** Timezone Google's quota resets against. */
    public const RESET_TIMEZONE = 'America/Los_Angeles';

    public const CAP_MESSAGE = 'Daily new-channel cap reached, try again soon.';

    public function __construct(
        protected ?string $apiKey = null,
    ) {
        $this->apiKey ??= config('services.youtube.api_key');
    }

    public function hasKey(): bool
    {
        return (bool) $this->apiKey;
    }

    /** The Pacific date the quota is currently keyed to. */
    public function currentDate(): string
    {
        return CarbonImmutable::now(self::RESET_TIMEZONE)->toDateString();
    }

    public function unitsUsedToday(): int
    {
        return (int) YoutubeApiUsage::query()
            ->where('date_pt', $this->currentDate())
            ->value('units_used');
    }

    public function remaining(): int
    {
        return max(0, self::DAILY_CAP - $this->unitsUsedToday());
    }

    public function hasBudget(int $units = 1): bool
    {
        return $this->remaining() >= $units;
    }

    /**
     * @throws \RuntimeException when the daily cap leaves no room for the spend.
     */
    public function assertBudget(int $units = 1): void
    {
        if (! $this->hasBudget($units)) {
            throw new \RuntimeException(self::CAP_MESSAGE);
        }
    }

    /**
     * Call `channels.list` (1 unit), ticking the counter for the attempt.
     *
     * @param  array<string, string>  $params
     * @return array<int, array<string, mixed>> the `items` array, possibly empty
     *
     * @throws \RuntimeException on a missing key, an exhausted budget, or an API error
     */
    public function channelsList(array $params, int $cost = 1): array
    {
        if (! $this->hasKey()) {
            throw new \RuntimeException(
                'YOUTUBE_API_KEY not configured. Paste a channel ID (UC…) instead.'
            );
        }

        $this->assertBudget($cost);

        // Tick before the call: Google charges for requests that return nothing
        // and for ones that error, so an untracked attempt is an untracked spend.
        $this->consume($cost);

        $response = Http::timeout(5)->get('https://www.googleapis.com/youtube/v3/channels', [
            ...$params,
            'key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            // Google's reason ("accessNotConfigured", "quotaExceeded") is the
            // only part of a 403 that says what to actually fix.
            $reason = $response->json('error.errors.0.reason')
                ?? $response->json('error.status');
            $message = $response->json('error.message');

            throw new \RuntimeException(trim(
                'YouTube API error '.$response->status()
                .($reason ? ' ('.$reason.')' : '')
                .($message ? ': '.$message : '')
            ));
        }

        return $response->json('items') ?? [];
    }

    /** Record a spend against today's Pacific date. */
    public function consume(int $units = 1): void
    {
        $date = $this->currentDate();

        $row = YoutubeApiUsage::query()->firstOrCreate(
            ['date_pt' => $date],
            ['units_used' => 0],
        );

        YoutubeApiUsage::query()->whereKey($row->getKey())->increment('units_used', $units);
    }
}
