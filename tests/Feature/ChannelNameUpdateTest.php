<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can rename channel in their group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['name' => 'Old']);
    $group->channels()->attach($channel->id);

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), ['name' => 'My Label'])
        ->assertRedirect();

    expect($channel->fresh()->name)->toBe('My Label');
});

test('user cannot rename channel not attached to group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), ['name' => 'X'])
        ->assertNotFound();
});

test('user cannot rename channel in another users group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel->id);

    $this->actingAs($other)
        ->patch(route('groups.channels.update', [$group, $channel]), ['name' => 'X'])
        ->assertForbidden();
});

test('rename validates name is present', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel->id);

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), ['name' => ''])
        ->assertSessionHasErrors('name');
});
