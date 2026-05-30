<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('all videos feed returns videos from all subscribed channels', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    Video::factory()->count(3)->create(['channel_id' => $channel->id]);

    $this->actingAs($user)
        ->get(route('feed.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Videos/Feed')
            ->has('videos.data', 3)
        );
});

test('all videos feed auto-fetches stale channels on load', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group->channels()->attach($channel);

    $this->actingAs($user)->get(route('feed.index'))->assertOk();

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(1);
    $this->assertDatabaseHas('channels', [
        'id' => $channel->id,
        'last_fetched_at' => now()->toDateTimeString(),
    ]);
});

test('all videos feed skips fetch for recently fetched channels', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);

    $this->actingAs($user)->get(route('feed.index'))->assertOk();

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(0);
});

test('all videos feed deduplicates channels shared across groups', function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);

    $user = User::factory()->create();
    $group1 = ChannelGroup::factory()->for($user)->create();
    $group2 = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => null]);
    $group1->channels()->attach($channel);
    $group2->channels()->attach($channel);

    $this->actingAs($user)->get(route('feed.index'))->assertOk();

    $rssRequests = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'youtube.com'));

    expect($rssRequests)->toHaveCount(1);
});
