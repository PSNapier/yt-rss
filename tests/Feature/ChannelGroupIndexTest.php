<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot manage groups', function () {
    $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
    $this->post(route('groups.store'), ['name' => 'Tech'])->assertRedirect(route('login'));
});

test('subscriptions page lists only the user groups with channel counts', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $tech = ChannelGroup::factory()->for($alice)->create(['name' => 'Tech']);
    $tech->channels()->attach(Channel::factory()->count(2)->create());
    ChannelGroup::factory()->for($bob)->create(['name' => 'Music']);

    $this->actingAs($alice)
        ->get(route('subscriptions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Subscriptions')
            ->has('groups', 1)
            ->where('groups.0.name', 'Tech')
            ->where('groups.0.channels_count', 2)
        );
});

test('user can create a group from the subscriptions page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('groups.store'), ['name' => 'Tech'])
        ->assertRedirect(route('subscriptions.index'));

    $this->assertDatabaseHas('channel_groups', [
        'user_id' => $user->id,
        'name' => 'Tech',
    ]);
});

test('user can rename a group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['name' => 'Science'])
        ->assertRedirect(route('subscriptions.index'));

    expect($group->fresh()->name)->toBe('Science');
});

test('user can delete a group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete(route('groups.destroy', $group))
        ->assertRedirect(route('subscriptions.index'));

    $this->assertDatabaseMissing('channel_groups', ['id' => $group->id]);
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

test('user cannot rename another user group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create(['name' => 'Tech']);

    $this->actingAs($other)->patch(route('groups.update', $group), ['name' => 'Mine'])->assertForbidden();
    expect($group->fresh()->name)->toBe('Tech');
});

test('user cannot delete another user group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();

    $this->actingAs($other)->delete(route('groups.destroy', $group))->assertForbidden();
    $this->assertDatabaseHas('channel_groups', ['id' => $group->id]);
});
