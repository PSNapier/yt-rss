<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can favorite channel in their group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel->id, ['is_favorite' => false]);

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), ['is_favorite' => true])
        ->assertRedirect();

    expect((bool) $group->channels()->where('channels.id', $channel->id)->first()->pivot->is_favorite)->toBeTrue();
});

test('user can unfavorite channel in their group', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel->id, ['is_favorite' => true]);

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), ['is_favorite' => false])
        ->assertRedirect();

    expect((bool) $group->channels()->where('channels.id', $channel->id)->first()->pivot->is_favorite)->toBeFalse();
});

test('patch without name or favorite returns validation error', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel->id);

    $this->actingAs($user)
        ->patch(route('groups.channels.update', [$group, $channel]), [])
        ->assertSessionHasErrors('name');
});
