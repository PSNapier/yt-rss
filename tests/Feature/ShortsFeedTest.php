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

function shortsFeedIds($response): array
{
    return collect($response->json('props.videos.data'))
        ->pluck('youtube_video_id')
        ->all();
}

test('lists only shorts for subscribed channels', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $short = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);
    Video::factory()->create(['channel_id' => $channel->id, 'is_short' => false]);

    // A Short on a channel the user is not subscribed to stays out.
    Video::factory()->create(['is_short' => true]);

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    $response->assertOk();
    expect($response->json('component'))->toBe('Videos/Shorts');
    expect(shortsFeedIds($response))->toBe([$short->youtube_video_id]);
});

test('other feeds never return shorts', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $long = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => false]);
    Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);

    $all = $this->actingAs($user)
        ->get(route('feed.index'), inertiaPartial('Videos/Feed'));

    expect(shortsFeedIds($all))->toBe([$long->youtube_video_id]);

    $groupFeed = $this->actingAs($user)
        ->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    expect(shortsFeedIds($groupFeed))->toBe([$long->youtube_video_id]);
});

test('hidden categories are excluded and persist', function () {
    $user = User::factory()->create();
    $keep = ChannelGroup::factory()->for($user)->create(['name' => 'Keep']);
    $hide = ChannelGroup::factory()->for($user)->create(['name' => 'Hide']);

    $keepChannel = Channel::factory()->create(['last_fetched_at' => now()]);
    $hideChannel = Channel::factory()->create(['last_fetched_at' => now()]);
    $keep->channels()->attach($keepChannel);
    $hide->channels()->attach($hideChannel);

    $kept = Video::factory()->create(['channel_id' => $keepChannel->id, 'is_short' => true]);
    Video::factory()->create(['channel_id' => $hideChannel->id, 'is_short' => true]);

    $this->actingAs($user)
        ->post(route('shorts.categories'), ['hidden_group_ids' => [$hide->id]])
        ->assertRedirect();

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    expect(shortsFeedIds($response))->toBe([$kept->youtube_video_id]);

    // The hidden set survives the round trip and reaches the chip row.
    $shell = $this->actingAs($user)->get(route('shorts.index'));

    $shell->assertInertia(fn ($page) => $page
        ->component('Videos/Shorts')
        ->where('categories', fn ($categories) => collect($categories)
            ->firstWhere('id', $hide->id)['hidden'] === true
            && collect($categories)->firstWhere('id', $keep->id)['hidden'] === false
        )
    );
});

test('a channel in both a hidden and a visible category still shows', function () {
    $user = User::factory()->create();
    $keep = ChannelGroup::factory()->for($user)->create();
    $hide = ChannelGroup::factory()->for($user)->create();

    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $keep->channels()->attach($channel);
    $hide->channels()->attach($channel);

    $short = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);

    $this->actingAs($user)->post(route('shorts.categories'), ['hidden_group_ids' => [$hide->id]]);

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    expect(shortsFeedIds($response))->toBe([$short->youtube_video_id]);
});

test('new group is visible by default', function () {
    $user = User::factory()->create();
    $existing = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)->post(route('shorts.categories'), ['hidden_group_ids' => [$existing->id]]);

    // Created after the user hid everything they knew about.
    $fresh = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $fresh->channels()->attach($channel);

    $short = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    expect(shortsFeedIds($response))->toBe([$short->youtube_video_id]);
});

test('cap toggle limits shorts per channel', function () {
    $user = User::factory()->create(['feed_cap_enabled' => true]);
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $older = Video::factory()->create([
        'channel_id' => $channel->id,
        'is_short' => true,
        'published_at' => now()->subDays(2),
    ]);
    $newest = Video::factory()->create([
        'channel_id' => $channel->id,
        'is_short' => true,
        'published_at' => now()->subDay(),
    ]);

    // Long-form videos must not consume the Shorts cap.
    Video::factory()->count(3)->create([
        'channel_id' => $channel->id,
        'is_short' => false,
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    // Default cap is 1: the newest unwatched Short only.
    expect(shortsFeedIds($response))->toBe([$newest->youtube_video_id]);
    expect(shortsFeedIds($response))->not->toContain($older->youtube_video_id);
});

test('excludes hidden videos', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $visible = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);
    $hidden = Video::factory()->create(['channel_id' => $channel->id, 'is_short' => true]);

    UserVideoState::create([
        'user_id' => $user->id,
        'youtube_video_id' => $hidden->youtube_video_id,
        'state' => 'hidden',
    ]);

    $response = $this->actingAs($user)
        ->get(route('shorts.index'), inertiaPartial('Videos/Shorts'));

    expect(shortsFeedIds($response))->toBe([$visible->youtube_video_id]);
});

test('the shorts page requires authentication', function () {
    $this->get(route('shorts.index'))->assertRedirect(route('login'));
});
