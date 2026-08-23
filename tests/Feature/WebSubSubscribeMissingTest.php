<?php

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use Illuminate\Support\Facades\Http;

function missingSweepRss(string $channelId): string
{
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <yt:channelId>{$channelId}</yt:channelId>
  <title>Sweep Channel</title>
  <entry>
    <id>yt:video:sweepVideo001</id>
    <yt:videoId>sweepVideo001</yt:videoId>
    <yt:channelId>{$channelId}</yt:channelId>
    <title>Swept Video</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v=sweepVideo001"/>
    <published>2026-07-01T10:00:00+00:00</published>
    <media:group>
      <media:thumbnail url="https://i.ytimg.com/vi/sweepVideo001/hqdefault.jpg" />
    </media:group>
  </entry>
</feed>
XML;
}

/**
 * Http::fake() stacks stubs and the first match wins, so each test registers
 * its own single set rather than layering one over a shared default.
 */
function fakeWebSub(int $hubStatus = 202): void
{
    Http::fake([
        'youtube.com/feeds/videos.xml*' => Http::response(missingSweepRss('UCsweepAAAAAAAAAAAAAAAA'), 200),
        'pubsubhubbub.appspot.com/*' => Http::response('Accepted', $hubStatus),
        'hooks.example.com/*' => Http::response('', 200),
    ]);
}

test('it subscribes and backfills a channel that has no subscription row', function () {
    fakeWebSub();

    $channel = Channel::factory()->create();

    $this->artisan('websub:subscribe-missing')->assertSuccessful();

    $subscription = $channel->fresh()->webSubSubscription;

    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(WebSubSubscriptionStatus::Pending)
        ->and($subscription->topic_url)->toBe($channel->rssUrl())
        ->and($subscription->callback_token)->not->toBeEmpty()
        ->and($subscription->secret)->not->toBeEmpty();

    Http::assertSent(fn ($request) => $request->url() === config('services.websub.hub_url')
        && $request['hub.mode'] === 'subscribe'
        && $request['hub.callback'] === $subscription->callbackUrl());

    expect($channel->fresh()->videos()->count())->toBe(1);
});

test('it leaves channels that already have a subscription alone', function () {
    fakeWebSub();

    $channel = Channel::factory()->create();
    $existing = ChannelSubscription::factory()->active()->create(['channel_id' => $channel->id]);

    $this->artisan('websub:subscribe-missing')
        ->expectsOutputToContain('Every channel already has a WebSub subscription.')
        ->assertSuccessful();

    expect($channel->fresh()->webSubSubscription->callback_token)
        ->toBe($existing->callback_token);

    Http::assertNothingSent();
});

test('it also leaves a failed subscription alone, since renewal owns that retry', function () {
    fakeWebSub();

    $channel = Channel::factory()->create();
    ChannelSubscription::factory()->create([
        'channel_id' => $channel->id,
        'status' => WebSubSubscriptionStatus::Failed,
    ]);

    $this->artisan('websub:subscribe-missing')->assertSuccessful();

    Http::assertNothingSent();
});

test('an optional limit paces the run and warns when it is hit', function () {
    fakeWebSub();

    Channel::factory()->count(3)->create();

    $this->artisan('websub:subscribe-missing', ['--limit' => 2])
        ->expectsOutputToContain('Run hit its limit of 2')
        ->assertSuccessful();

    expect(ChannelSubscription::query()->count())->toBe(2);
});

test('dry run reports candidates without touching the hub', function () {
    fakeWebSub();

    Channel::factory()->count(2)->create();

    $this->artisan('websub:subscribe-missing', ['--dry-run' => true])
        ->expectsOutputToContain('[dry-run] 2 channels would be subscribed.')
        ->assertSuccessful();

    expect(ChannelSubscription::query()->count())->toBe(0);
    Http::assertNothingSent();
});

test('a hub rejection is recorded as failed and reported', function () {
    fakeWebSub(hubStatus: 500);

    config()->set('services.websub.alert_webhook', 'https://hooks.example.com/websub-alerts');

    $channel = Channel::factory()->create();

    $this->artisan('websub:subscribe-missing')
        ->expectsOutputToContain('failed: 1')
        ->assertSuccessful();

    expect($channel->fresh()->webSubSubscription->status)
        ->toBe(WebSubSubscriptionStatus::Failed);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'hooks.example.com')
        && str_contains($request['text'], 'WebSub subscribe failed for 1 channel(s)'));
});
