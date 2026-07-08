<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);
});

function capGroupWithChannel(User $user): array
{
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    return [$group, $channel];
}

function feedVideoIds($response): array
{
    return collect($response->json('props.videos.data'))
        ->pluck('youtube_video_id')
        ->all();
}

test('cap shows at most the newest unwatched video per channel', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();

    $channelA = Channel::factory()->create(['last_fetched_at' => now()]);
    $channelB = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach([$channelA->id, $channelB->id]);

    // Channel A: three unwatched, a3 newest.
    Video::factory()->create(['channel_id' => $channelA->id, 'published_at' => now()->subDays(5)]);
    Video::factory()->create(['channel_id' => $channelA->id, 'published_at' => now()->subDays(3)]);
    $a3 = Video::factory()->create(['channel_id' => $channelA->id, 'published_at' => now()->subDay()]);

    // Channel B: one unwatched.
    $b1 = Video::factory()->create(['channel_id' => $channelB->id, 'published_at' => now()->subDays(2)]);

    $response = $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $response->assertOk();
    $ids = feedVideoIds($response);

    expect($ids)->toHaveCount(2);
    expect($ids)->toContain($a3->youtube_video_id);
    expect($ids)->toContain($b1->youtube_video_id);
});

test('cap keeps watched videos and still surfaces the newest unwatched of the channel', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    [$group, $channel] = capGroupWithChannel($user);

    $unwatched = Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDays(3)]);
    $watched = Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDay()]);

    UserVideoState::create([
        'user_id' => $user->id,
        'youtube_video_id' => $watched->youtube_video_id,
        'state' => 'watched',
    ]);

    $response = $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $ids = feedVideoIds($response);
    // Watched still renders, and the older unwatched surfaces as newest unwatched.
    expect($ids)->toHaveCount(2);
    expect($ids)->toContain($watched->youtube_video_id);
    expect($ids)->toContain($unwatched->youtube_video_id);
});

test('marking the newest unwatched watched surfaces the next unwatched under cap', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    [$group, $channel] = capGroupWithChannel($user);

    $older = Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDays(3)]);
    $newer = Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDay()]);

    // Initially only the newest unwatched shows.
    $before = feedVideoIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );
    expect($before)->toEqual([$newer->youtube_video_id]);

    // Mark it watched.
    $this->actingAs($user)
        ->post(route('videos.state.store', $newer->youtube_video_id), ['state' => 'watched'])
        ->assertRedirect();

    // Now the next unwatched surfaces (and the watched one still renders).
    $after = feedVideoIds(
        $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))
    );
    expect($after)->toHaveCount(2);
    expect($after)->toContain($older->youtube_video_id);
    expect($after)->toContain($newer->youtube_video_id);
});

test('disabling the cap returns the full unfiltered feed', function () {
    $user = User::factory()->create(['feed_cap_enabled' => false]);
    [$group, $channel] = capGroupWithChannel($user);

    Video::factory()->count(4)->create(['channel_id' => $channel->id, 'published_at' => now()->subDay()]);

    $response = $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    expect(feedVideoIds($response))->toHaveCount(4);
});

test('cap applies to the all-videos feed as well', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDays(3)]);
    $newer = Video::factory()->create(['channel_id' => $channel->id, 'published_at' => now()->subDay()]);

    $response = $this->actingAs($user)->get(route('feed.index'), inertiaPartial('Videos/Feed'));

    expect(feedVideoIds($response))->toEqual([$newer->youtube_video_id]);
});

test('the cap toggle persists the per-user setting', function () {
    $user = User::factory()->create(['feed_cap_enabled' => false]);

    $this->actingAs($user)
        ->post(route('feed.cap'), ['enabled' => true])
        ->assertRedirect();

    expect($user->fresh()->feed_cap_enabled)->toBeTrue();

    $this->actingAs($user)
        ->post(route('feed.cap'), ['enabled' => false])
        ->assertRedirect();

    expect($user->fresh()->feed_cap_enabled)->toBeFalse();
});
