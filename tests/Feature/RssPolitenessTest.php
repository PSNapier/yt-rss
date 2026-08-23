<?php

use App\Models\Channel;
use App\Services\ChannelResolver;
use App\Services\RssFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function politeRss(string $videoId = 'politeVid01'): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <title>Polite Channel</title>
  <entry>
    <yt:videoId>{$videoId}</yt:videoId>
    <title>Polite Video</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v={$videoId}"/>
    <published>2026-05-01T10:00:00+00:00</published>
  </entry>
</feed>
XML;
}

test('poll sends browser user agent, gzip, and conditional headers from stored validators', function () {
    Http::fake(['*' => Http::response(politeRss(), 200, [
        'ETag' => 'W/"fresh-etag"',
        'Last-Modified' => 'Fri, 21 Aug 2026 09:00:00 GMT',
    ])]);

    $channel = Channel::factory()->create([
        'last_fetched_at' => null,
        'rss_etag' => 'W/"stored-etag"',
        'rss_last_modified' => 'Wed, 21 Oct 2015 07:28:00 GMT',
    ]);

    (new RssFetcher)->fetchForChannels(collect([$channel]), force: true);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'feeds/videos.xml')
            && str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0')
            && str_contains($request->header('Accept-Encoding')[0] ?? '', 'gzip')
            && ($request->header('If-None-Match')[0] ?? null) === 'W/"stored-etag"'
            && ($request->header('If-Modified-Since')[0] ?? null) === 'Wed, 21 Oct 2015 07:28:00 GMT';
    });

    $channel->refresh();

    expect($channel->rss_etag)->toBe('W/"fresh-etag"')
        ->and($channel->rss_last_modified)->toBe('Fri, 21 Aug 2026 09:00:00 GMT')
        ->and($channel->last_fetched_at)->not->toBeNull();
});

test('a 304 response writes no videos, is not a failure, and refreshes last_fetched_at', function () {
    Http::fake(['*' => Http::response('', 304)]);

    $channel = Channel::factory()->create([
        'last_fetched_at' => now()->subDay(),
        'rss_etag' => 'W/"stored-etag"',
    ]);

    $result = (new RssFetcher)->fetchForChannels(collect([$channel]), force: true);

    expect($result['failed'])->toBe(0)
        ->and($result['fetched'])->toBe(0)
        ->and($result['not_modified'])->toBe(1);

    $this->assertDatabaseCount('videos', 0);

    $channel->refresh();

    expect($channel->last_fetched_at->isToday())->toBeTrue()
        ->and($channel->rss_etag)->toBe('W/"stored-etag"');
});

test('the channel-name RSS lookup also sends the browser user agent and gzip', function () {
    Http::fake(['*' => Http::response(politeRss(), 200)]);

    $name = (new ChannelResolver(apiKey: null))
        ->lookupChannelNameFromRss('UC'.str_repeat('A', 22));

    expect($name)->toBe('Polite Channel');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'feeds/videos.xml')
            && str_contains($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0')
            && str_contains($request->header('Accept-Encoding')[0] ?? '', 'gzip');
    });
});
