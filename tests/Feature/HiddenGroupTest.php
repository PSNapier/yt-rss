<?php

use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a group can be hidden and unhidden and stays editable while hidden', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);

    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['hidden' => true])
        ->assertRedirect(route('subscriptions.index'));

    expect($group->fresh()->hidden)->toBeTrue();

    // Renaming a hidden group keeps it hidden.
    $this->actingAs($user)
        ->patch(route('groups.update', $group), ['name' => 'Science'])
        ->assertSessionHasNoErrors();

    expect($group->fresh())
        ->name->toBe('Science')
        ->hidden->toBeTrue();

    // Still listed on the Subscriptions page, flagged hidden.
    $this->actingAs($user)
        ->get(route('subscriptions.index'))
        ->assertInertia(fn ($page) => $page->component('Subscriptions')
            ->has('groups', 1)
            ->where('groups.0.hidden', true)
        );

    $this->actingAs($user)->patch(route('groups.update', $group), ['hidden' => false]);

    expect($group->fresh()->hidden)->toBeFalse();
});

test('another user cannot hide a group', function () {
    $group = ChannelGroup::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('groups.update', $group), ['hidden' => true])
        ->assertForbidden();

    expect($group->fresh()->hidden)->toBeFalse();
});

test('a hidden group leaves the sidebar and its feed redirects home', function () {
    $user = User::factory()->create();
    $visible = ChannelGroup::factory()->for($user)->create(['name' => 'Visible']);
    $hidden = ChannelGroup::factory()->for($user)->create(['name' => 'Hidden', 'hidden' => true]);

    $this->actingAs($user)
        ->get(route('groups.show', $visible))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('channelGroups', 1)
            ->where('channelGroups.0.id', $visible->id)
        );

    $this->actingAs($user)
        ->get(route('groups.show', $hidden))
        ->assertRedirect(route('landing'));
});
