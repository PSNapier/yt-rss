<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function groupIdsFor(Channel $channel): array
{
    return $channel->channelGroups()->pluck('channel_groups.id')->sort()->values()->all();
}

it('removes channels by scope', function () {
    $user = User::factory()->create();
    $groupA = ChannelGroup::factory()->for($user)->create();
    $groupB = ChannelGroup::factory()->for($user)->create();

    $everywhere = Channel::factory()->create();
    $narrowed = Channel::factory()->create();
    $untouched = Channel::factory()->create();

    $groupA->channels()->attach([$everywhere->id, $narrowed->id, $untouched->id]);
    $groupB->channels()->attach([$everywhere->id, $narrowed->id, $untouched->id]);

    $this->actingAs($user)
        ->delete(route('subscriptions.destroy-many'), [
            'items' => [
                ['channel_id' => $everywhere->id, 'scope' => 'everywhere'],
                ['channel_id' => $narrowed->id, 'scope' => 'group', 'group_id' => $groupA->id],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(groupIdsFor($everywhere))->toBe([]);
    expect(groupIdsFor($narrowed))->toBe([$groupB->id]);
    expect(groupIdsFor($untouched))->toBe(collect([$groupA->id, $groupB->id])->sort()->values()->all());
});

it('rejects foreign channels and groups', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $mine = Channel::factory()->create();
    $group->channels()->attach($mine);

    $other = User::factory()->create();
    $foreignGroup = ChannelGroup::factory()->for($other)->create();
    $foreign = Channel::factory()->create();
    $foreignGroup->channels()->attach([$foreign->id, $mine->id]);

    // A channel the user is not subscribed to.
    $this->actingAs($user)
        ->delete(route('subscriptions.destroy-many'), [
            'items' => [
                ['channel_id' => $mine->id, 'scope' => 'everywhere'],
                ['channel_id' => $foreign->id, 'scope' => 'everywhere'],
            ],
        ])
        ->assertSessionHasErrors('items.1.channel_id');

    // A group the user does not own.
    $this->actingAs($user)
        ->delete(route('subscriptions.destroy-many'), [
            'items' => [
                ['channel_id' => $mine->id, 'scope' => 'group', 'group_id' => $foreignGroup->id],
            ],
        ])
        ->assertSessionHasErrors('items.0.group_id');

    // Group scope without a group.
    $this->actingAs($user)
        ->delete(route('subscriptions.destroy-many'), [
            'items' => [['channel_id' => $mine->id, 'scope' => 'group']],
        ])
        ->assertSessionHasErrors('items.0.group_id');

    // Nothing was detached by any rejected request.
    expect(groupIdsFor($mine))->toBe(collect([$group->id, $foreignGroup->id])->sort()->values()->all());
    expect(groupIdsFor($foreign))->toBe([$foreignGroup->id]);
});

it('keeps single destroy behavior', function () {
    $user = User::factory()->create();
    $groupA = ChannelGroup::factory()->for($user)->create();
    $groupB = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();
    $groupA->channels()->attach($channel);
    $groupB->channels()->attach($channel);

    $other = User::factory()->create();
    $foreignGroup = ChannelGroup::factory()->for($other)->create();
    $foreignGroup->channels()->attach($channel);

    $this->actingAs($user)
        ->delete(route('subscriptions.destroy', $channel))
        ->assertRedirect();

    // Detached from every group of this user, untouched in other users' groups.
    expect(groupIdsFor($channel))->toBe([$foreignGroup->id]);

    // Not subscribed anymore, so a second call is a 404.
    $this->actingAs($user)
        ->delete(route('subscriptions.destroy', $channel))
        ->assertNotFound();
});

it('ships a channel to groups map on each feed page', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel);

    $response = $this->actingAs($user)
        ->get(route('feed.index'), inertiaPartial('Videos/Feed', ['channelGroupMap']));

    expect($response->json('props.channelGroupMap'))->toBe([
        (string) $channel->id => [['id' => $group->id, 'name' => 'Tech']],
    ]);
});

it('keeps the shared sidebar group list intact on feed pages', function (string $route) {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);
    $channel = Channel::factory()->create();
    $group->channels()->attach($channel);

    $params = $route === 'groups.show' ? [$group] : [];

    $response = $this->actingAs($user)
        ->get(route($route, $params), inertiaPartial(
            $route === 'groups.show' ? 'Groups/Show' : ($route === 'shorts.index' ? 'Videos/Shorts' : 'Videos/Feed'),
            ['channelGroups', 'channelGroupMap'],
        ));

    expect($response->json('props.channelGroups.0.id'))->toBe($group->id)
        ->and($response->json('props.channelGroupMap'))->toHaveKey((string) $channel->id);
})->with(['feed.index', 'groups.show', 'shorts.index']);
