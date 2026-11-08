<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('feed preferences default to show watched and not starred only', function () {
    $user = User::factory()->create();

    expect($user->fresh()->feed_show_watched)->toBeTrue();
    expect($user->fresh()->feed_starred_only)->toBeFalse();
});

test('the preferences route persists show watched and starred only', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('feed.preferences'), ['show_watched' => false])
        ->assertNoContent();

    expect($user->fresh()->feed_show_watched)->toBeFalse();
    expect($user->fresh()->feed_starred_only)->toBeFalse();

    $this->actingAs($user)
        ->post(route('feed.preferences'), ['starred_only' => true])
        ->assertNoContent();

    expect($user->fresh()->feed_show_watched)->toBeFalse();
    expect($user->fresh()->feed_starred_only)->toBeTrue();
});

test('the preferences route rejects non-boolean values', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('feed.preferences'), ['show_watched' => 'maybe'])
        ->assertUnprocessable();
});

test('the preferences are shared as props on the all-videos feed and group feeds', function () {
    $user = User::factory()->create(['feed_show_watched' => false, 'feed_starred_only' => true]);
    $group = App\Models\ChannelGroup::factory()->for($user)->create();

    $all = $this->actingAs($user)->get(route('feed.index'), inertiaPartial('Videos/Feed', ['showWatched', 'starredOnly']));
    expect($all->json('props.showWatched'))->toBeFalse();
    expect($all->json('props.starredOnly'))->toBeTrue();

    $show = $this->actingAs($user)->get(route('groups.show', $group), inertiaPartial('Groups/Show', ['showWatched', 'starredOnly']));
    expect($show->json('props.showWatched'))->toBeFalse();
    expect($show->json('props.starredOnly'))->toBeTrue();
});
