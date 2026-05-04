<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view channels for their group with pivot join', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $alpha = Channel::factory()->create(['name' => 'Alpha Channel']);
    $beta = Channel::factory()->create(['name' => 'Beta Channel']);
    $group->channels()->attach($alpha->id, ['is_favorite' => false]);
    $group->channels()->attach($beta->id, ['is_favorite' => true]);

    $response = $this->actingAs($user)->get(route('groups.channels.index', $group));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->component('Groups/Channels')
            ->has('channels', 2)
            ->where('channels.0.name', 'Beta Channel')
            ->where('channels.0.is_favorite', true)
            ->where('channels.1.name', 'Alpha Channel')
            ->where('channels.1.is_favorite', false)
    );
});

test('user cannot view channels for another users group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('groups.channels.index', $group))->assertForbidden();
});
