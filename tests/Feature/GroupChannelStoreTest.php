<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(fn () => Config::set('services.youtube.api_key', null));

test('user can add channel to group by youtube channel id without api key', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $ucId = 'UC'.str_repeat('B', 22);

    $response = $this->actingAs($user)->post(route('groups.channels.store', $group), [
        'mode' => 'id',
        'value' => $ucId,
    ]);

    $response->assertRedirect();
    $channel = Channel::query()->where('channel_id', $ucId)->first();
    expect($channel)->not->toBeNull()
        ->and($channel->name)->toBe($ucId);
    expect($group->fresh()->channels()->where('channels.id', $channel->id)->exists())->toBeTrue();
});

test('user gets validation error when channel id is invalid', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('groups.channels.store', $group), [
        'mode' => 'id',
        'value' => 'not-a-channel-id',
    ]);

    $response->assertSessionHasErrors('value');
});

test('user can set optional custom display name when adding by channel id', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $ucId = 'UC'.str_repeat('D', 22);

    $this->actingAs($user)->post(route('groups.channels.store', $group), [
        'mode' => 'id',
        'value' => $ucId,
        'custom_name' => '  My Label  ',
    ])->assertRedirect();

    $channel = Channel::query()->where('channel_id', $ucId)->first();
    expect($channel->name)->toBe('My Label');
});

test('user cannot add channel to another users group', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();
    $ucId = 'UC'.str_repeat('C', 22);

    $this->actingAs($other)->post(route('groups.channels.store', $group), [
        'mode' => 'id',
        'value' => $ucId,
    ])->assertForbidden();
});
