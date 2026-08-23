<?php

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    config()->set('services.websub.backstop_silence_multiplier', 3.0);
    config()->set('services.websub.backstop_min_silence_hours', 48);
    config()->set('services.websub.backstop_min_repoll_hours', 6);
});

/**
 * Channel ids present in the RSS URLs the fetcher actually requested.
 *
 * @return array<int, string>
 */
function polledChannelIds(): array
{
    return collect(Http::recorded())
        ->map(fn ($pair) => $pair[0]->url())
        ->filter(fn (string $url) => str_contains($url, 'feeds/videos.xml'))
        ->map(fn (string $url) => (string) parse_url($url, PHP_URL_QUERY))
        ->map(function (string $query) {
            parse_str($query, $parsed);

            return (string) ($parsed['channel_id'] ?? '');
        })
        ->values()
        ->all();
}

/**
 * Give a channel a regular posting cadence ending $silentDays ago.
 */
function seedCadence(Channel $channel, int $gapDays, int $silentDays, int $count = 6): void
{
    for ($i = 0; $i < $count; $i++) {
        Video::factory()->create([
            'channel_id' => $channel->id,
            'published_at' => now()->subDays($silentDays + ($i * $gapDays)),
        ]);
    }
}

test('a lapsed lease is re-polled and a healthy subscription is not', function () {
    $lapsed = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
    ChannelSubscription::factory()->forChannel($lapsed)->active()->create([
        'expires_at' => now()->subHour(),
    ]);
    seedCadence($lapsed, gapDays: 3, silentDays: 1);

    $healthy = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
    ChannelSubscription::factory()->forChannel($healthy)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($healthy, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([$lapsed->channel_id]);
});

test('a failed subscription and one with renewal failures are re-polled', function () {
    $failed = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
    ChannelSubscription::factory()->forChannel($failed)->create([
        'status' => WebSubSubscriptionStatus::Failed,
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($failed, gapDays: 3, silentDays: 1);

    $shaky = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
    ChannelSubscription::factory()->forChannel($shaky)->active()->create([
        'expires_at' => now()->addDays(4),
        'renewal_failures' => 2,
    ]);
    seedCadence($shaky, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toHaveCount(2)
        ->and(polledChannelIds())->toContain($failed->channel_id, $shaky->channel_id);
});

test('a channel silent far beyond its own cadence is re-polled', function () {
    $silent = Channel::factory()->create(['last_fetched_at' => now()->subDays(1)]);
    ChannelSubscription::factory()->forChannel($silent)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($silent, gapDays: 2, silentDays: 20);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([$silent->channel_id]);
});

test('a slow-cadence channel silent within its own rhythm is not re-polled', function () {
    $slow = Channel::factory()->create(['last_fetched_at' => now()->subDays(1)]);
    ChannelSubscription::factory()->forChannel($slow)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($slow, gapDays: 30, silentDays: 20);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([]);
});

test('a channel with no subscription row at all is re-polled', function () {
    $orphan = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
    seedCadence($orphan, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([$orphan->channel_id]);
});

test('a channel polled within the minimum re-poll interval is skipped', function () {
    $recent = Channel::factory()->create(['last_fetched_at' => now()->subMinutes(30)]);
    ChannelSubscription::factory()->forChannel($recent)->active()->create([
        'expires_at' => now()->subHour(),
    ]);
    seedCadence($recent, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([]);
});

test('the cadence check does not run a query per channel', function () {
    foreach (range(1, 12) as $ignored) {
        $channel = Channel::factory()->create(['last_fetched_at' => now()->subDays(2)]);
        ChannelSubscription::factory()->forChannel($channel)->active()->create([
            'expires_at' => now()->addDays(4),
        ]);
        seedCadence($channel, gapDays: 3, silentDays: 4);
    }

    $videoQueries = 0;
    DB::listen(function ($query) use (&$videoQueries) {
        if (str_contains($query->sql, 'videos')) {
            $videoQueries++;
        }
    });

    $this->artisan('websub:backstop')->assertSuccessful();

    // One grouped aggregate per chunk, not one lookup per channel.
    expect($videoQueries)->toBeLessThanOrEqual(2);
});

test('a push that failed to ingest flags the channel for an immediate re-poll', function () {
    $channel = Channel::factory()->create(['last_fetched_at' => now()->subMinutes(5)]);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);

    $body = 'not xml at all';

    $this->call(
        'POST',
        route('websub.receive', $subscription->callback_token),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/atom+xml',
            'HTTP_X_HUB_SIGNATURE' => 'sha1='.hash_hmac('sha1', $body, $subscription->secret),
        ],
        $body,
    )->assertStatus(500);

    expect($subscription->fresh()->delivery_failed_at)->not->toBeNull();

    $this->artisan('websub:backstop')->assertSuccessful();

    // Recent poll notwithstanding: a known-missing upload is re-polled now, and the flag clears.
    expect(polledChannelIds())->toBe([$channel->channel_id])
        ->and($subscription->fresh()->delivery_failed_at)->toBeNull();
});

test('a gap between sweeps queues every channel for one recovery poll', function () {
    Cache::put('websub:backstop:last_run_at', now()->subHours(9)->toIso8601String());

    $channel = Channel::factory()->create(['last_fetched_at' => now()->subMinutes(5)]);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($channel, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([$channel->channel_id])
        ->and($subscription->fresh()->recovery_due_at)->toBeNull();
});

test('an uninterrupted sweep cadence queues no recovery', function () {
    Cache::put('websub:backstop:last_run_at', now()->subHour()->toIso8601String());

    $channel = Channel::factory()->create(['last_fetched_at' => now()->subDays(1)]);
    ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addDays(4),
    ]);
    seedCadence($channel, gapDays: 3, silentDays: 1);

    $this->artisan('websub:backstop')->assertSuccessful();

    expect(polledChannelIds())->toBe([]);
});
