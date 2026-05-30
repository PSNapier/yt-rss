<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('feed returns videos sorted newest first and excludes hidden', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $older = Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now()->subDays(5),
    ]);
    $newer = Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now()->subDay(),
    ]);
    $hidden = Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now(),
    ]);

    $user->fresh();
    UserVideoState::create([
        'user_id' => $user->id,
        'youtube_video_id' => $hidden->youtube_video_id,
        'state' => 'hidden',
    ]);

    $response = $this->actingAs($user)->get(route('groups.show', $group));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Groups/Show')
        ->has('videos.data', 2)
        ->where('videos.data.0.youtube_video_id', $newer->youtube_video_id)
        ->where('videos.data.1.youtube_video_id', $older->youtube_video_id)
        ->where('videos.data.0.channel_is_favorite', false)
    );
});

test('feed exposes channel_is_favorite when user favorited channel via subscriptions', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel->id);
    $user->favoritedChannels()->attach($channel->id);

    $video = Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page
            ->component('Groups/Show')
            ->has('videos.data', 1)
            ->where('videos.data.0.youtube_video_id', $video->youtube_video_id)
            ->where('videos.data.0.channel_is_favorite', true)
        );
});

test('favoriting on subscriptions page shows starred videos in group feed', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel->id);

    Video::factory()->create([
        'channel_id' => $channel->id,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->patch(route('subscriptions.toggle-favorite', $channel), ['is_favorite' => true])
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page
            ->component('Groups/Show')
            ->has('videos.data', 1)
            ->where('videos.data.0.channel_is_favorite', true)
        );
});

test('feed paginates fifteen videos per page', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    Video::factory()->count(16)->create([
        'channel_id' => $channel->id,
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertInertia(fn ($page) => $page
            ->component('Groups/Show')
            ->has('videos.data', 15)
            ->where('videos.next_page_url', fn ($url) => $url !== null)
        );
});

test('visiting feed auto-fetches stale channels', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    $this->actingAs($user)->get(route('groups.show', $group))->assertOk();

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(1);
    $this->assertDatabaseHas('channels', [
        'id' => $channel->id,
        'last_fetched_at' => now()->toDateTimeString(),
    ]);
});

test('visiting feed skips fetch for recently fetched channels', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $this->actingAs($user)->get(route('groups.show', $group))->assertOk();

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(0);
});

test('refresh route forces RSS fetch', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $this->actingAs($user)
        ->post(route('groups.refresh', $group))
        ->assertRedirect();

    Http::assertSentCount(1);
});
