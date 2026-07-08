<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserChannelCap;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);
});

/**
 * Create `count` unwatched videos for a channel, oldest first, and return them.
 *
 * @return Collection<int, Video>
 */
function makeVideos(Channel $channel, int $count)
{
    return collect(range(1, $count))->map(fn ($i) => Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now()->subDays($count - $i + 1),
    ]));
}

function capFeedIds($response): array
{
    return collect($response->json('props.videos.data'))
        ->pluck('youtube_video_id')
        ->all();
}

test('a per-channel cap of M shows the M newest unwatched videos of that channel', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $videos = makeVideos($channel, 5); // oldest..newest
    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channel->id, 'cap' => 2]);

    $ids = capFeedIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );

    expect($ids)->toHaveCount(2);
    expect($ids)->toContain($videos[4]->youtube_video_id); // newest
    expect($ids)->toContain($videos[3]->youtube_video_id); // 2nd newest
    expect($ids)->not->toContain($videos[2]->youtube_video_id);
});

test('caps on different channels are enforced simultaneously', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();

    $channelA = Channel::factory()->create(['last_fetched_at' => now()]);
    $channelB = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach([$channelA->id, $channelB->id]);

    makeVideos($channelA, 6);
    makeVideos($channelB, 8);

    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channelA->id, 'cap' => 2]);
    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channelB->id, 'cap' => 5]);

    $rows = collect(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))->json('props.videos.data')
    );

    expect($rows->where('channel.id', $channelA->id))->toHaveCount(2);
    expect($rows->where('channel.id', $channelB->id))->toHaveCount(5);
});

test('a channel with no custom cap uses the default of one', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $videos = makeVideos($channel, 4);

    $ids = capFeedIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );

    expect($ids)->toEqual([$videos[3]->youtube_video_id]);
    expect(UserChannelCap::DEFAULT_CAP)->toBe(1);
});

test('an unlimited cap removes the limit for that channel', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    makeVideos($channel, 6);
    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channel->id, 'cap' => UserChannelCap::UNLIMITED]);

    $ids = capFeedIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );

    expect($ids)->toHaveCount(6);
});

test('the cap toggle off shows all videos regardless of per-channel caps', function () {
    $user = User::factory()->create(['feed_cap_enabled' => false]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    makeVideos($channel, 5);
    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channel->id, 'cap' => 2]);

    $ids = capFeedIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );

    expect($ids)->toHaveCount(5);
});

test('updateCap persists a per-channel cap and clearing it reverts to default', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $this->actingAs($user)
        ->patch(route('subscriptions.update-cap', $channel), ['cap' => 3])
        ->assertRedirect();

    $this->assertDatabaseHas('user_channel_caps', [
        'user_id' => $user->id,
        'channel_id' => $channel->id,
        'cap' => 3,
    ]);

    // Null clears the row (revert to default).
    $this->actingAs($user)
        ->patch(route('subscriptions.update-cap', $channel), ['cap' => null])
        ->assertRedirect();

    $this->assertDatabaseMissing('user_channel_caps', [
        'user_id' => $user->id,
        'channel_id' => $channel->id,
    ]);
});

test('updateCap rejects a non-subscribed channel', function () {
    $user = User::factory()->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);

    $this->actingAs($user)
        ->patch(route('subscriptions.update-cap', $channel), ['cap' => 2])
        ->assertNotFound();
});

test('the subscriptions index exposes each channel unwatched_cap', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    UserChannelCap::create(['user_id' => $user->id, 'channel_id' => $channel->id, 'cap' => 5]);

    $response = $this->actingAs($user)->get(route('subscriptions.index'));

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->component('Subscriptions')
        ->where('channels.0.unwatched_cap', 5)
    );
});
