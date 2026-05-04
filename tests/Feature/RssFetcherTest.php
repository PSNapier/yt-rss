<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserVideoState;
use App\Services\RssFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function sampleRss(
    string $videoId = 'dQw4w9WgXcQ',
    string $channelId = 'UCuAXFkgsw1L7xaCfnd5JJOw',
    ?string $alternateHref = null,
): string {
    $href = $alternateHref ?? 'https://www.youtube.com/watch?v='.$videoId;

    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <yt:channelId>{$channelId}</yt:channelId>
  <title>Sample Channel</title>
  <entry>
    <id>yt:video:{$videoId}</id>
    <yt:videoId>{$videoId}</yt:videoId>
    <yt:channelId>{$channelId}</yt:channelId>
    <title>Sample Video Title</title>
    <link rel="alternate" href="{$href}"/>
    <published>2026-05-01T10:00:00+00:00</published>
    <media:group>
      <media:thumbnail url="https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg" />
    </media:group>
  </entry>
</feed>
XML;
}

test('ingests RSS feed and creates videos', function () {
    $channel = Channel::factory()->create(['channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw']);

    $count = (new RssFetcher)->ingest($channel, sampleRss());

    expect($count)->toBe(1);
    $this->assertDatabaseHas('videos', [
        'youtube_video_id' => 'dQw4w9WgXcQ',
        'channel_id' => $channel->id,
        'title' => 'Sample Video Title',
    ]);
});

test('upserts videos on re-ingest (no duplicates)', function () {
    $channel = Channel::factory()->create();

    $fetcher = new RssFetcher;
    $fetcher->ingest($channel, sampleRss());
    $fetcher->ingest($channel, sampleRss());

    expect($channel->videos()->count())->toBe(1);
});

test('skips shorts and does not store them', function () {
    $channel = Channel::factory()->create();

    $count = (new RssFetcher)->ingest(
        $channel,
        sampleRss('ssDbeb9vB6g', 'UCuAXFkgsw1L7xaCfnd5JJOw', 'https://www.youtube.com/shorts/ssDbeb9vB6g')
    );

    expect($count)->toBe(0);
    $this->assertDatabaseCount('videos', 0);
});

test('deletes existing row when feed entry is a short', function () {
    $channel = Channel::factory()->create();

    $fetcher = new RssFetcher;
    $fetcher->ingest($channel, sampleRss('ssDbeb9vB6g'));
    expect($channel->videos()->count())->toBe(1);

    $fetcher->ingest(
        $channel,
        sampleRss('ssDbeb9vB6g', 'UCuAXFkgsw1L7xaCfnd5JJOw', 'https://www.youtube.com/shorts/ssDbeb9vB6g')
    );

    expect($channel->fresh()->videos()->count())->toBe(0);
});

test('deletes user video state when removing a short from RSS', function () {
    $user = User::factory()->create();
    $channel = Channel::factory()->create();

    (new RssFetcher)->ingest($channel, sampleRss('ssDbeb9vB6g'));
    UserVideoState::factory()->for($user)->create([
        'youtube_video_id' => 'ssDbeb9vB6g',
        'state' => UserVideoState::STATE_WATCHED,
    ]);

    (new RssFetcher)->ingest(
        $channel,
        sampleRss('ssDbeb9vB6g', 'UCuAXFkgsw1L7xaCfnd5JJOw', 'https://www.youtube.com/shorts/ssDbeb9vB6g')
    );

    expect(UserVideoState::query()->where('youtube_video_id', 'ssDbeb9vB6g')->count())->toBe(0);
});

test('fetchForGroup updates last_fetched_at on success', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    Http::fake([
        '*' => Http::response(sampleRss(), 200),
    ]);

    $result = (new RssFetcher)->fetchForGroup($group);

    expect($result['fetched'])->toBe(1);
    expect($channel->fresh()->last_fetched_at)->not->toBeNull();
});

test('fetchForGroup skips fresh channels', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()->subMinutes(5)]);
    $group->channels()->attach($channel);

    Http::fake();

    $result = (new RssFetcher)->fetchForGroup($group);

    expect($result['skipped'])->toBe(1);
    expect($result['fetched'])->toBe(0);
    Http::assertNothingSent();
});

test('fetchForGroup refetches fresh channels when forced', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create([
        'channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw',
        'last_fetched_at' => now()->subMinutes(5),
    ]);
    $group->channels()->attach($channel);

    Http::fake(['*' => Http::response(sampleRss(), 200)]);

    $result = (new RssFetcher)->fetchForGroup($group, force: true);

    expect($result['fetched'])->toBe(1);
    Http::assertSentCount(1);
});

test('fetchForGroup logs failure and leaves last_fetched_at unchanged on http error', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    Http::fake(['*' => Http::response('', 500)]);

    $result = (new RssFetcher)->fetchForGroup($group);

    expect($result['failed'])->toBe(1);
    expect($channel->fresh()->last_fetched_at)->toBeNull();
});
