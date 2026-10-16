<?php

use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are sent to login', function () {
    $this->get('/home')->assertRedirect(route('login'));
});

test('home redirects to the first group by name', function () {
    $user = User::factory()->create();
    ChannelGroup::factory()->for($user)->create(['name' => 'Zeta']);
    $alpha = ChannelGroup::factory()->for($user)->create(['name' => 'Alpha']);

    // Another user's group sorting first must not leak in.
    ChannelGroup::factory()->for(User::factory())->create(['name' => 'Aaa']);

    $this->actingAs($user)
        ->get(route('landing'))
        ->assertRedirect(route('groups.show', $alpha));
});

test('home falls back to all videos with no groups', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('landing'))
        ->assertRedirect(route('feed.index'));
});

test('the old groups index redirects to home', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/groups')
        ->assertRedirect('/home');
});

test('home skips hidden groups', function () {
    $user = User::factory()->create();
    ChannelGroup::factory()->for($user)->create(['name' => 'Alpha', 'hidden' => true]);
    $beta = ChannelGroup::factory()->for($user)->create(['name' => 'Beta']);

    $this->actingAs($user)
        ->get(route('landing'))
        ->assertRedirect(route('groups.show', $beta));
});

test('home falls back to all videos when every group is hidden', function () {
    $user = User::factory()->create();
    ChannelGroup::factory()->for($user)->create(['hidden' => true]);

    $this->actingAs($user)
        ->get(route('landing'))
        ->assertRedirect(route('feed.index'));
});
