<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

test('adding a channel succeeds with no hub request and no subscription side effect', function () {
    Http::fake(['*' => Http::response('', 200)]);

    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create();

    $this->actingAs($user)->post('/subscriptions', [
        'mode' => 'existing',
        'channel_id' => $channel->channel_id,
        'group_ids' => [$group->id],
    ])->assertRedirect();

    expect($group->channels()->whereKey($channel->id)->exists())->toBeTrue();

    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'pubsubhubbub'));
    expect(Schema::hasTable('channel_subscriptions'))->toBeFalse();
});

test('no websub routes or commands remain', function () {
    expect(Route::has('websub.verify'))->toBeFalse()
        ->and(Route::has('websub.receive'))->toBeFalse()
        ->and(array_keys(Artisan::all()))->not->toContain('websub:renew', 'websub:backstop', 'websub:subscribe-missing');
});
