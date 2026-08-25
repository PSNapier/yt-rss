<?php

use App\Models\Channel;
use App\Models\PollSweep;
use App\Models\Video;
use App\Services\PollCooldown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

function pollFeed(string $videoId = 'sweepVid001', ?string $href = null): string
{
    $href ??= 'https://www.youtube.com/watch?v='.$videoId;

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <title>Sweep Channel</title>
  <entry>
    <yt:videoId>{$videoId}</yt:videoId>
    <title>Sweep Video</title>
    <link rel="alternate" href="{$href}"/>
    <published>2026-08-01T10:00:00+00:00</published>
  </entry>
</feed>
XML;
}

test('it polls every channel in one sweep', function () {
    Http::fake(['*' => Http::response(pollFeed(), 200)]);

    Channel::factory()->count(5)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    Http::assertSentCount(5);
});

test('it forces past the ttl so a recently fetched channel is not skipped', function () {
    Http::fake(['*' => Http::response(pollFeed(), 200)]);

    Channel::factory()->create(['last_fetched_at' => now()->subMinutes(2)]);

    $this->artisan('channels:poll')->assertSuccessful();

    Http::assertSentCount(1);
});

test('it caps a sweep at the configured maximum', function () {
    Http::fake(['*' => Http::response(pollFeed(), 200)]);
    config()->set('services.polling.max_per_sweep', 3);

    Channel::factory()->count(7)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')
        ->expectsOutputToContain('cap')
        ->assertSuccessful();

    Http::assertSentCount(3);
});

test('it polls everything when the channel count is under the cap', function () {
    Http::fake(['*' => Http::response(pollFeed(), 200)]);
    config()->set('services.polling.max_per_sweep', 1000);

    Channel::factory()->count(4)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    Http::assertSentCount(4);
    expect(Video::count())->toBeGreaterThan(0);
});

test('it records the failure categories of a sweep on the sweep row', function () {
    Http::fake(['*' => Http::failedConnection(
        'cURL error 28: Operation timed out after 3002 milliseconds with 0 bytes received'
    )]);

    Channel::factory()->count(3)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    expect(PollSweep::latest('id')->first()->failure_categories)->toBe(['read_timeout' => 3]);
});

test('the sweep uses the sweep budget, not the request-path budget', function () {
    config()->set('services.youtube.rss_connect_timeout', 2.0);
    config()->set('services.youtube.rss_timeout', 3.0);
    config()->set('services.polling.pool_chunk', 2);
    config()->set('services.polling.inter_chunk_delay_ms', 400);
    config()->set('services.polling.connect_timeout', 6.0);
    config()->set('services.polling.timeout', 11.0);

    $captured = [];

    Http::fake(function ($request, $options) use (&$captured) {
        $captured[] = $options;

        return Http::response(pollFeed(), 200);
    });

    Sleep::fake();

    Channel::factory()->count(6)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    expect($captured)->toHaveCount(6)
        ->and($captured[0]['connect_timeout'])->toBe(6.0)
        ->and($captured[0]['timeout'])->toBe(11.0);

    // 6 channels at a chunk of 2 is three batches, so two gaps.
    Sleep::assertSleptTimes(2);
});

test('a sweep of transport failures does not trip the cooldown', function () {
    config()->set('services.polling.block_failure_ratio', 0.5);
    config()->set('services.polling.block_min_sample', 2);

    Http::fake(['*' => Http::failedConnection(
        'cURL error 28: Operation timed out after 3002 milliseconds with 0 bytes received'
    )]);

    Channel::factory()->count(6)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    $sweep = PollSweep::latest('id')->first();

    expect(app(PollCooldown::class)->isActive())->toBeFalse()
        ->and($sweep->cooldown_triggered)->toBeFalse()
        ->and($sweep->failed)->toBe(6);
});

test('a sweep of block signals still trips the cooldown', function () {
    config()->set('services.polling.block_failure_ratio', 0.5);
    config()->set('services.polling.block_min_sample', 2);

    Http::fake(['*' => Http::response('forbidden', 403)]);

    Channel::factory()->count(6)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    $sweep = PollSweep::latest('id')->first();

    expect(app(PollCooldown::class)->isActive())->toBeTrue()
        ->and($sweep->cooldown_triggered)->toBeTrue()
        ->and($sweep->failure_alert)->toBeFalse();
});

test('a wholesale non-block failure raises the distinct signal and keeps polling', function () {
    Log::spy();

    config()->set('services.polling.failure_alert_ratio', 0.5);
    config()->set('services.polling.block_min_sample', 2);

    Http::fake(['*' => Http::failedConnection(
        'cURL error 28: Operation timed out after 3002 milliseconds with 0 bytes received'
    )]);

    Channel::factory()->count(6)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')
        ->expectsOutputToContain('read_timeout')
        ->assertSuccessful();

    expect(PollSweep::latest('id')->first()->failure_alert)->toBeTrue()
        ->and(app(PollCooldown::class)->isActive())->toBeFalse();

    Log::shouldHaveReceived('error')->withArgs(
        fn (string $message, array $context = []) => str_contains($message, 'Poll sweep failure storm')
    );
});

test('a healthy sweep raises neither signal', function () {
    Http::fake(['*' => Http::response(pollFeed(), 200)]);

    Channel::factory()->count(6)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    $sweep = PollSweep::latest('id')->first();

    expect($sweep->failure_alert)->toBeFalse()
        ->and($sweep->cooldown_triggered)->toBeFalse();
});

test('it counts the entries flagged as short-form in a sweep', function () {
    Http::fake(['*' => Http::response(
        pollFeed('shortVid001', 'https://www.youtube.com/shorts/shortVid001'),
        200
    )]);

    Channel::factory()->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')
        ->expectsOutputToContain('short-form flagged: 1')
        ->assertSuccessful();

    expect(PollSweep::latest('id')->first()->shorts_flagged)->toBe(1);
});
