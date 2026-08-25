<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use App\Services\RssFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

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

    $result = (new RssFetcher)->ingest($channel, sampleRss());

    expect($result['stored'])->toBe(1);
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

test('stores a shorts entry with the flag set', function () {
    $channel = Channel::factory()->create();

    $result = (new RssFetcher)->ingest(
        $channel,
        sampleRss('ssDbeb9vB6g', 'UCuAXFkgsw1L7xaCfnd5JJOw', 'https://www.youtube.com/shorts/ssDbeb9vB6g')
    );

    expect($result['stored'])->toBe(1)
        ->and($result['shorts'])->toBe(1);

    $this->assertDatabaseHas('videos', [
        'youtube_video_id' => 'ssDbeb9vB6g',
        'is_short' => true,
    ]);
});

test('flags an existing row when the feed entry becomes a short', function () {
    $channel = Channel::factory()->create();

    $fetcher = new RssFetcher;
    $fetcher->ingest($channel, sampleRss('ssDbeb9vB6g'));
    expect($channel->videos()->count())->toBe(1);

    $fetcher->ingest(
        $channel,
        sampleRss('ssDbeb9vB6g', 'UCuAXFkgsw1L7xaCfnd5JJOw', 'https://www.youtube.com/shorts/ssDbeb9vB6g')
    );

    $video = $channel->fresh()->videos()->first();

    expect($channel->fresh()->videos()->count())->toBe(1)
        ->and($video->is_short)->toBeTrue();
});

test('keeps user video state when an entry is reclassified as short-form', function () {
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

    expect(UserVideoState::query()->where('youtube_video_id', 'ssDbeb9vB6g')->count())->toBe(1)
        ->and(UserVideoState::query()->where('youtube_video_id', 'ssDbeb9vB6g')->first()->state)
        ->toBe(UserVideoState::STATE_WATCHED);
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

test('fetchForGroup processes many channels in pool chunks', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channels = Channel::factory()->count(25)->create(['last_fetched_at' => null]);
    $group->channels()->attach($channels->pluck('id')->all());

    Http::fake(['*' => Http::response(sampleRss(), 200)]);

    $result = (new RssFetcher(poolChunkSize: 5))->fetchForGroup($group);

    expect($result['fetched'])->toBe(25);
    expect($result['failed'])->toBe(0);
    Http::assertSentCount(25);
});

test('a poll that fails without a response logs the exception class and message', function () {
    Log::spy();

    Http::fake(['*' => Http::failedConnection(
        'cURL error 6: Could not resolve host: www.youtube.com (see https://curl.se/libcurl/c/libcurl-errors.html)'
    )]);

    $channel = Channel::factory()->create([
        'channel_id' => 'UCtransport00000000000001',
        'last_fetched_at' => null,
    ]);

    $result = (new RssFetcher)->fetchForChannels(collect([$channel]), force: true);

    expect($result['failed'])->toBe(1)
        ->and($result['failures'])->toBe(['dns' => 1]);

    Log::shouldHaveReceived('warning')->withArgs(
        fn (string $message, array $context) => $message === 'RSS fetch failed'
            && $context['category'] === 'dns'
            && $context['exception'] === ConnectionException::class
            && str_contains($context['error'], 'Could not resolve host')
    );
});

test('a read timeout and a connect timeout land in different categories', function () {
    Http::fake([
        '*channel_id=UCread0000000000000000001*' => Http::failedConnection(
            'cURL error 28: Operation timed out after 3002 milliseconds with 0 bytes received'
        ),
        '*channel_id=UCconnect00000000000000001*' => Http::failedConnection(
            'cURL error 28: Connection timed out after 2001 milliseconds'
        ),
    ]);

    $channels = collect([
        Channel::factory()->create([
            'channel_id' => 'UCread0000000000000000001',
            'rss_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCread0000000000000000001',
            'last_fetched_at' => null,
        ]),
        Channel::factory()->create([
            'channel_id' => 'UCconnect00000000000000001',
            'rss_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCconnect00000000000000001',
            'last_fetched_at' => null,
        ]),
    ]);

    $result = (new RssFetcher)->fetchForChannels($channels, force: true);

    expect($result['failed'])->toBe(2)
        ->and($result['failures'])->toHaveKey('read_timeout', 1)
        ->and($result['failures'])->toHaveKey('connect_timeout', 1);
});

test('a tls failure and an http error status are counted in separate categories', function () {
    Http::fake([
        '*channel_id=UCtls00000000000000000001*' => Http::failedConnection(
            'cURL error 35: OpenSSL SSL_connect: SSL_ERROR_SYSCALL'
        ),
        '*channel_id=UChttp0000000000000000001*' => Http::response('', 500),
    ]);

    $channels = collect([
        Channel::factory()->create([
            'channel_id' => 'UCtls00000000000000000001',
            'rss_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UCtls00000000000000000001',
            'last_fetched_at' => null,
        ]),
        Channel::factory()->create([
            'channel_id' => 'UChttp0000000000000000001',
            'rss_url' => 'https://www.youtube.com/feeds/videos.xml?channel_id=UChttp0000000000000000001',
            'last_fetched_at' => null,
        ]),
    ]);

    $result = (new RssFetcher)->fetchForChannels($channels, force: true);

    expect($result['failures'])->toHaveKey('tls', 1)
        ->and($result['failures'])->toHaveKey('http_error', 1);
});

test('the configured chunk size and inter-chunk delay pace the pool batches', function () {
    Sleep::fake();
    Http::fake(['*' => Http::response(sampleRss(), 200)]);

    $channels = Channel::factory()->count(12)->create(['last_fetched_at' => null]);

    $result = (new RssFetcher(poolChunkSize: 5, interChunkDelayMs: 250))
        ->fetchForChannels($channels, force: true);

    expect($result['fetched'])->toBe(12);
    Http::assertSentCount(12);

    // 12 channels in batches of 5 is three batches, so two gaps: no trailing pause.
    Sleep::assertSleptTimes(2);
    Sleep::assertSequence([
        Sleep::usleep(250 * 1000),
        Sleep::usleep(250 * 1000),
    ]);
});

test('a single batch is not followed by a pause', function () {
    Sleep::fake();
    Http::fake(['*' => Http::response(sampleRss(), 200)]);

    $channels = Channel::factory()->count(3)->create(['last_fetched_at' => null]);

    (new RssFetcher(poolChunkSize: 5, interChunkDelayMs: 250))->fetchForChannels($channels, force: true);

    Sleep::assertNeverSlept();
});

test('configured timeouts reach the outbound request', function () {
    $captured = [];

    Http::fake(function ($request, $options) use (&$captured) {
        $captured[] = [
            'connect_timeout' => $options['connect_timeout'] ?? null,
            'timeout' => $options['timeout'] ?? null,
        ];

        return Http::response(sampleRss(), 200);
    });

    $channel = Channel::factory()->create(['last_fetched_at' => null]);

    (new RssFetcher(connectTimeoutSeconds: 4.5, timeoutSeconds: 9.5))
        ->fetchForChannels(collect([$channel]), force: true);

    expect($captured)->toHaveCount(1)
        ->and($captured[0]['connect_timeout'])->toBe(4.5)
        ->and($captured[0]['timeout'])->toBe(9.5);
});

test('the sweep budget is read from polling config', function () {
    config()->set('services.polling.pool_chunk', 7);
    config()->set('services.polling.inter_chunk_delay_ms', 321);
    config()->set('services.polling.connect_timeout', 6.5);
    config()->set('services.polling.timeout', 12.5);

    $captured = [];

    Http::fake(function ($request, $options) use (&$captured) {
        $captured[] = $options;

        return Http::response(sampleRss(), 200);
    });

    Sleep::fake();

    $channels = Channel::factory()->count(8)->create(['last_fetched_at' => null]);

    (new RssFetcher)->forSweep()->fetchForChannels($channels, force: true);

    expect($captured[0]['connect_timeout'])->toBe(6.5)
        ->and($captured[0]['timeout'])->toBe(12.5);

    // 8 channels at a chunk of 7 is two batches, so exactly one configured gap.
    Sleep::assertSequence([Sleep::usleep(321 * 1000)]);
});

test('a re-ingest never clears a short-form flag set by the watch-page detector', function () {
    $channel = Channel::factory()->create();

    $fetcher = new RssFetcher;
    $fetcher->ingest($channel, sampleRss('ssDbeb9vB6g'));

    // `videos:prune-shorts` classifies off the watch page, which sees Shorts the RSS
    // alternate href does not. A later sweep must not argue with that.
    Video::query()->where('youtube_video_id', 'ssDbeb9vB6g')->update(['is_short' => true]);

    $fetcher->ingest($channel, sampleRss('ssDbeb9vB6g'));

    expect(Video::query()->where('youtube_video_id', 'ssDbeb9vB6g')->first()->is_short)->toBeTrue();
});
