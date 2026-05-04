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

test('feed exposes channel_is_favorite when channel is favorited in group', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel->id, ['is_favorite' => true]);

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
