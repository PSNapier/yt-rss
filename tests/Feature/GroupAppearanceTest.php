<?php

use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('a group icon and color can be set without sending a name', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['icon' => 'RocketLaunchIcon', 'color' => 'ocean'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('subscriptions.index'));

    expect($group->fresh())
        ->name->toBe('Tech')
        ->icon->toBe('RocketLaunchIcon')
        ->color->toBe('ocean');
});

test('icon and color can be cleared back to null', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['icon' => 'FilmIcon', 'color' => 'ocean']);

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['icon' => null, 'color' => null])
        ->assertSessionHasNoErrors();

    expect($group->fresh())->icon->toBeNull()->color->toBeNull();
});

test('an unknown color preset is rejected', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['color' => '#ff0000'])
        ->assertSessionHasErrors('color');

    expect($group->fresh()->color)->toBeNull();
});

test('a malformed icon name is rejected', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['icon' => '<script>'])
        ->assertSessionHasErrors('icon');

    expect($group->fresh()->icon)->toBeNull();
});

test('legacy curated icon keys remain accepted', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['icon' => 'Braces'])
        ->assertSessionHasNoErrors();

    expect($group->fresh()->icon)->toBe('Braces');
});

test('another user cannot change a group appearance', function () {
    $group = ChannelGroup::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('groups.update', $group), ['color' => 'ocean'])
        ->assertForbidden();
});

test('an empty update is still rejected', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('groups.update', $group), [])
        ->assertSessionHasErrors('name');
});

test('icon and color are shared to the sidebar, group page and subscriptions', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['icon' => 'RocketLaunchIcon', 'color' => 'ocean']);

    $this->actingAs($user)->get(route('groups.show', $group))
        ->assertInertia(fn (Assert $page) => $page
            ->where('group.icon', 'RocketLaunchIcon')
            ->where('group.color', 'ocean')
            ->where('channelGroups.0.color', 'ocean'));

    $this->actingAs($user)->get(route('subscriptions.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('groups.0.icon', 'RocketLaunchIcon')
            ->where('groups.0.color', 'ocean'));
});
