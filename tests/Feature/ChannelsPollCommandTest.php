<?php

use App\Models\Channel;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function pollFeed(string $videoId = 'sweepVid001'): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <title>Sweep Channel</title>
  <entry>
    <yt:videoId>{$videoId}</yt:videoId>
    <title>Sweep Video</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v={$videoId}"/>
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
