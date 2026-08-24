<?php

use App\Models\Channel;
use App\Models\PollSweep;
use App\Services\PollCooldown;
use App\Services\RssFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const INTERSTITIAL = '<html><head><title>Error 200 (Server Error)</title></head><body>Our systems have detected unusual traffic from your computer network. Please try your request again later.</body></html>';

test('it counts a 403 as a block signal', function () {
    Http::fake(['*' => Http::response('forbidden', 403)]);

    $channel = Channel::factory()->create(['last_fetched_at' => null]);

    $result = (new RssFetcher)->fetchForChannels(collect([$channel]), force: true);

    expect($result['blocked'])->toBe(1)
        ->and($result['fetched'])->toBe(0);
});

test('it counts a 200 with a non atom body as a block signal', function () {
    Http::fake(['*' => Http::response(INTERSTITIAL, 200)]);

    $channel = Channel::factory()->create(['last_fetched_at' => null]);

    $result = (new RssFetcher)->fetchForChannels(collect([$channel]), force: true);

    expect($result['blocked'])->toBe(1)
        ->and($result['fetched'])->toBe(0);

    $channel->refresh();
    expect($channel->last_fetched_at)->toBeNull();
});

test('it enters cooldown when the sweep failure ratio crosses the threshold', function () {
    Http::fake(['*' => Http::response('forbidden', 403)]);
    config()->set('services.polling.block_failure_ratio', 0.5);
    config()->set('services.polling.block_min_sample', 2);

    Channel::factory()->count(4)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    expect(app(PollCooldown::class)->isActive())->toBeTrue()
        ->and(PollSweep::latest('id')->first()->cooldown_triggered)->toBeTrue();
});

test('it skips the sweep while cooldown is active', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    Channel::factory()->count(3)->create(['last_fetched_at' => null]);

    app(PollCooldown::class)->start('test');

    $this->artisan('channels:poll')
        ->expectsOutputToContain('cooldown')
        ->assertSuccessful();

    Http::assertNothingSent();
});

test('it skips the websub backstop while cooldown is active', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $channel = Channel::factory()->create(['last_fetched_at' => now()->subDays(30)]);

    app(PollCooldown::class)->start('test');

    $this->artisan('websub:backstop')
        ->expectsOutputToContain('cooldown')
        ->assertSuccessful();

    Http::assertNothingSent();
});

test('a sweep that only fails without block signals still enters cooldown', function () {
    Http::fake(['*' => Http::response('', 500)]);
    config()->set('services.polling.block_failure_ratio', 0.5);
    config()->set('services.polling.block_min_sample', 2);

    Channel::factory()->count(4)->create(['last_fetched_at' => null]);

    $this->artisan('channels:poll')->assertSuccessful();

    $sweep = PollSweep::latest('id')->first();

    expect($sweep->blocked)->toBe(0)
        ->and($sweep->failed)->toBe(4)
        ->and($sweep->cooldown_triggered)->toBeTrue()
        ->and(app(PollCooldown::class)->isActive())->toBeTrue();
});
