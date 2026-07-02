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

    $response = $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $response->assertOk();
    expect($response->json('component'))->toBe('Groups/Show');

    $data = $response->json('props.videos.data');
    expect($data)->toHaveCount(2);
    expect($data[0]['youtube_video_id'])->toBe($newer->youtube_video_id);
    expect($data[1]['youtube_video_id'])->toBe($older->youtube_video_id);
    expect($data[0]['channel_is_favorite'])->toBeFalse();
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

    $response = $this->actingAs($user)
        ->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $response->assertOk();
    $data = $response->json('props.videos.data');
    expect($data)->toHaveCount(1);
    expect($data[0]['youtube_video_id'])->toBe($video->youtube_video_id);
    expect($data[0]['channel_is_favorite'])->toBeTrue();
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

    $response = $this->actingAs($user)
        ->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $response->assertOk();
    $data = $response->json('props.videos.data');
    expect($data)->toHaveCount(1);
    expect($data[0]['channel_is_favorite'])->toBeTrue();
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

    $response = $this->actingAs($user)
        ->get(route('groups.show', $group), inertiaPartial('Groups/Show'));

    $response->assertOk();
    expect($response->json('props.videos.data'))->toHaveCount(15);
    expect($response->json('props.videos.next_page_url'))->not->toBeNull();
});

test('group feed defers videos and skips the RSS fetch on the initial shell render', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    // Full page render (no partial headers): the shell returns immediately with
    // `videos` deferred and no blocking YouTube fetch.
    $this->actingAs($user)
        ->get(route('groups.show', $group))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Groups/Show')
            ->missing('videos')
        );

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(0);
});

test('visiting feed auto-fetches stale channels', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))->assertOk();

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

    $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show'))->assertOk();

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
