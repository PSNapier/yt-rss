<?php

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\ChannelSubscription;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function subscribeSampleRss(
    string $videoId = 'backfillVid0001',
    string $channelId = 'UCAAAAAAAAAAAAAAAAAAAAAA',
): string {
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns:yt="http://www.youtube.com/xml/schemas/2015"
      xmlns:media="http://search.yahoo.com/mrss/"
      xmlns="http://www.w3.org/2005/Atom">
  <yt:channelId>{$channelId}</yt:channelId>
  <title>Backfill Channel</title>
  <entry>
    <id>yt:video:{$videoId}</id>
    <yt:videoId>{$videoId}</yt:videoId>
    <yt:channelId>{$channelId}</yt:channelId>
    <title>Backfilled Video</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v={$videoId}"/>
    <published>2026-07-01T10:00:00+00:00</published>
    <media:group>
      <media:thumbnail url="https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg" />
    </media:group>
  </entry>
</feed>
XML;
}

test('adding a channel creates subscription, backfills, and posts subscribe to hub', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->create(['user_id' => $user->id]);
    $channelId = 'UCAAAAAAAAAAAAAAAAAAAAAA';

    Http::fake([
        'youtube.com/feeds/videos.xml*' => Http::response(subscribeSampleRss(), 200),
        'pubsubhubbub.appspot.com/*' => Http::response('Accepted', 202),
    ]);

    $this->actingAs($user)
        ->post(route('subscriptions.store'), [
            'mode' => 'id',
            'value' => $channelId,
            'group_ids' => [$group->id],
        ])
        ->assertRedirect();

    $channel = Channel::query()->where('channel_id', $channelId)->first();
    expect($channel)->not->toBeNull();

    $subscription = ChannelSubscription::query()->where('channel_id', $channel->id)->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(WebSubSubscriptionStatus::Pending)
        ->and($subscription->topic_url)->toBe($channel->rssUrl())
        ->and($subscription->callback_token)->not->toBeEmpty()
        ->and($subscription->secret)->not->toBeEmpty();

    expect(Video::query()->where('youtube_video_id', 'backfillVid0001')->exists())->toBeTrue();

    Http::assertSent(function ($request) use ($subscription) {
        if (! str_contains($request->url(), 'pubsubhubbub.appspot.com')) {
            return false;
        }

        $data = $request->data();

        return ($data['hub.mode'] ?? null) === 'subscribe'
            && ($data['hub.topic'] ?? null) === $subscription->topic_url
            && ($data['hub.callback'] ?? null) === $subscription->callbackUrl()
            && ($data['hub.secret'] ?? null) === $subscription->secret
            && ($data['hub.verify'] ?? null) === 'async';
    });
});

test('re-adding an already subscribed channel does not re-subscribe or re-backfill', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->create(['user_id' => $user->id]);
    $channel = Channel::factory()->create(['channel_id' => 'UCBBBBBBBBBBBBBBBBBBBBBB']);
    $group->channels()->attach($channel);

    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create();

    Http::fake([
        'youtube.com/feeds/videos.xml*' => Http::response(subscribeSampleRss('shouldNotAppear', $channel->channel_id), 200),
        'pubsubhubbub.appspot.com/*' => Http::response('Accepted', 202),
    ]);

    $this->actingAs($user)
        ->post(route('subscriptions.store'), [
            'mode' => 'existing',
            'channel_id' => $channel->channel_id,
            'group_ids' => [$group->id],
        ])
        ->assertRedirect();

    expect(ChannelSubscription::query()->where('channel_id', $channel->id)->count())->toBe(1);
    expect(ChannelSubscription::query()->find($subscription->id)->callback_token)
        ->toBe($subscription->callback_token);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'pubsubhubbub.appspot.com'));
    expect(Video::query()->where('youtube_video_id', 'shouldNotAppear')->exists())->toBeFalse();
});

test('failed subscription can be retried on next add', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->create(['user_id' => $user->id]);
    $channel = Channel::factory()->create(['channel_id' => 'UCCCCCCCCCCCCCCCCCCCCCCC']);
    $group->channels()->attach($channel);

    ChannelSubscription::factory()->forChannel($channel)->create([
        'status' => WebSubSubscriptionStatus::Failed,
    ]);

    Http::fake([
        'youtube.com/feeds/videos.xml*' => Http::response(subscribeSampleRss('retryVid0000001', $channel->channel_id), 200),
        'pubsubhubbub.appspot.com/*' => Http::response('Accepted', 202),
    ]);

    $this->actingAs($user)
        ->post(route('subscriptions.store'), [
            'mode' => 'existing',
            'channel_id' => $channel->channel_id,
            'group_ids' => [$group->id],
        ])
        ->assertRedirect();

    $subscription = ChannelSubscription::query()->where('channel_id', $channel->id)->first();
    expect($subscription->status)->toBe(WebSubSubscriptionStatus::Pending);
    expect(Video::query()->where('youtube_video_id', 'retryVid0000001')->exists())->toBeTrue();
    Http::assertSent(fn ($request) => str_contains($request->url(), 'pubsubhubbub.appspot.com'));
});
