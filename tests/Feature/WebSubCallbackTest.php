<?php

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function webSubAtomPayload(
    string $videoId = 'dQw4w9WgXcQ',
    string $channelId = 'UCuAXFkgsw1L7xaCfnd5JJOw',
): string {
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
    <title>Pushed Video</title>
    <link rel="alternate" href="https://www.youtube.com/watch?v={$videoId}"/>
    <published>2026-07-23T12:00:00+00:00</published>
    <media:group>
      <media:thumbnail url="https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg" />
    </media:group>
  </entry>
</feed>
XML;
}

test('hub challenge echoes and marks subscription active', function () {
    $channel = Channel::factory()->create(['channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw']);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->create([
        'status' => WebSubSubscriptionStatus::Pending,
    ]);

    $challenge = 'challenge-token-abc';

    $response = $this->get(route('websub.verify', $subscription->callback_token).'?'.http_build_query([
        'hub.mode' => 'subscribe',
        'hub.topic' => $subscription->topic_url,
        'hub.challenge' => $challenge,
        'hub.lease_seconds' => 864000,
    ]));

    $response->assertOk();
    expect($response->getContent())->toBe($challenge);

    $subscription->refresh();
    expect($subscription->status)->toBe(WebSubSubscriptionStatus::Active)
        ->and($subscription->lease_seconds)->toBe(864000)
        ->and($subscription->expires_at)->not->toBeNull()
        ->and($subscription->last_verified_at)->not->toBeNull();
});

test('unknown callback token returns 404 on verify', function () {
    $this->get(route('websub.verify', 'missing-token-xxxxxxxxxxxxxxxxxxxx'))
        ->assertNotFound();
});

test('valid signed push ingests video via RssFetcher', function () {
    $channel = Channel::factory()->create(['channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw']);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create();
    $body = webSubAtomPayload();
    $signature = 'sha1='.hash_hmac('sha1', $body, $subscription->secret);

    $response = $this->call(
        'POST',
        route('websub.receive', $subscription->callback_token),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/atom+xml',
            'HTTP_X_HUB_SIGNATURE' => $signature,
        ],
        $body,
    );

    $response->assertOk();
    $this->assertDatabaseHas('videos', [
        'youtube_video_id' => 'dQw4w9WgXcQ',
        'channel_id' => $channel->id,
        'title' => 'Pushed Video',
    ]);
});

test('invalid signature rejects push and writes nothing', function () {
    $channel = Channel::factory()->create(['channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw']);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create();
    $body = webSubAtomPayload('badSigVideoId1');

    $response = $this->call(
        'POST',
        route('websub.receive', $subscription->callback_token),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/atom+xml',
            'HTTP_X_HUB_SIGNATURE' => 'sha1=deadbeef',
        ],
        $body,
    );

    $response->assertForbidden();
    expect(Video::query()->where('youtube_video_id', 'badSigVideoId1')->exists())->toBeFalse();
});

test('missing signature rejects push and writes nothing', function () {
    $channel = Channel::factory()->create(['channel_id' => 'UCuAXFkgsw1L7xaCfnd5JJOw']);
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create();
    $body = webSubAtomPayload('noSigVideoId12');

    $response = $this->call(
        'POST',
        route('websub.receive', $subscription->callback_token),
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/atom+xml'],
        $body,
    );

    $response->assertForbidden();
    expect(Video::query()->where('youtube_video_id', 'noSigVideoId12')->exists())->toBeFalse();
});
