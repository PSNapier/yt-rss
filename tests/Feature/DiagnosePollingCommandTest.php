<?php

use App\Models\Channel;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function diagnoseFeed(string $videoId, string $href): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns="http://www.w3.org/2005/Atom">
  <title>Diagnose Channel</title>
  <entry>
    <yt:videoId>{$videoId}</yt:videoId>
    <title>Diagnose Video</title>
    <link rel="alternate" href="{$href}"/>
    <published>2026-08-01T10:00:00+00:00</published>
  </entry>
</feed>
XML;
}

test('it names a video that is in the live feed but not in the database', function () {
    Http::fake(['*' => Http::response(diagnoseFeed('absentVid01', 'https://www.youtube.com/watch?v=absentVid01'), 200)]);

    Channel::factory()->create();

    $this->artisan('poll:diagnose --timing=1 --pool=1 --sleep=0')
        ->expectsOutputToContain('absentVid01')
        ->expectsOutputToContain('missing from db=1')
        ->assertSuccessful();
});

test('it lists a stored video the shorts rule will delete on the next poll', function () {
    Http::fake(['*' => Http::response(diagnoseFeed('doomedVid01', 'https://www.youtube.com/shorts/doomedVid01'), 200)]);

    $channel = Channel::factory()->create();
    Video::factory()->create(['channel_id' => $channel->id, 'youtube_video_id' => 'doomedVid01']);

    $this->artisan('poll:diagnose --timing=1 --pool=1 --sleep=0')
        ->expectsOutputToContain('will delete on the next successful poll: 1')
        ->expectsOutputToContain('doomedVid01')
        ->assertSuccessful();
});

test('it changes nothing it reads', function () {
    Http::fake(['*' => Http::response(diagnoseFeed('absentVid02', 'https://www.youtube.com/watch?v=absentVid02'), 200)]);

    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $video = Video::factory()->create(['channel_id' => $channel->id, 'youtube_video_id' => 'keptVid0001']);
    UserVideoState::factory()->create(['youtube_video_id' => $video->youtube_video_id]);

    $this->artisan('poll:diagnose --timing=1 --pool=1 --sleep=0')->assertSuccessful();

    expect(Video::query()->count())->toBe(1)
        ->and(UserVideoState::query()->count())->toBe(1)
        ->and($channel->fresh()->last_fetched_at)->toBeNull();
});

test('it skips the diff on request', function () {
    Http::fake(['*' => Http::response(diagnoseFeed('absentVid03', 'https://www.youtube.com/watch?v=absentVid03'), 200)]);

    Channel::factory()->count(3)->create();

    $this->artisan('poll:diagnose --timing=1 --pool=1 --skip-diff')
        ->doesntExpectOutputToContain('missing from db')
        ->assertSuccessful();
});
