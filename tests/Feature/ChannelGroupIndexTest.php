<?php

use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view groups index', function () {
    $this->get(route('groups.index'))->assertRedirect(route('login'));
});

test('user sees only their own groups', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    ChannelGroup::factory()->for($alice)->create(['name' => 'Tech']);
    ChannelGroup::factory()->for($bob)->create(['name' => 'Music']);

    $response = $this->actingAs($alice)->get(route('groups.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->component('Groups/Index')
            ->has('groups', 1)
            ->where('groups.0.name', 'Tech')
            ->has('channelGroups', 1)
            ->where('channelGroups.0.name', 'Tech')
    );
});

test('user can create a group', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('groups.store'), ['name' => 'Tech'])
        ->assertRedirect();

    $this->assertDatabaseHas('channel_groups', [
        'user_id' => $user->id,
        'name' => 'Tech',
    ]);
});

test('user cannot have two groups with the same name', function () {
    $user = User::factory()->create();
    ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);

    $this->actingAs($user)
        ->post(route('groups.store'), ['name' => 'Tech'])
        ->assertSessionHasErrors('name');
});

test('user cannot view another user group feed', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('groups.show', $group))->assertForbidden();
});

test('user cannot delete another user group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();

    $this->actingAs($other)->delete(route('groups.destroy', $group))->assertForbidden();
    $this->assertDatabaseHas('channel_groups', ['id' => $group->id]);
});
