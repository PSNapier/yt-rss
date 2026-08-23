<?php

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.websub.hub_url', 'https://hub.test/subscribe');
    config()->set('services.websub.renew_within_hours', 48);
    config()->set('services.websub.alert_webhook', null);
});

test('a lease expiring inside the renewal window is re-subscribed with the same token and secret', function () {
    Http::fake(['https://hub.test/*' => Http::response('', 202)]);

    $channel = Channel::factory()->create();
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addHours(12),
        'callback_token' => 'renewal-token-value',
        'secret' => 'renewal-secret-value',
    ]);

    $this->artisan('websub:renew')->assertSuccessful();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://hub.test/subscribe'
            && $request['hub.mode'] === 'subscribe'
            && $request['hub.secret'] === 'renewal-secret-value'
            && str_contains($request['hub.callback'], 'renewal-token-value');
    });

    $subscription->refresh();

    expect($subscription->last_renewal_attempt_at)->not->toBeNull()
        ->and($subscription->status)->toBe(WebSubSubscriptionStatus::Active)
        ->and($subscription->renewal_failures)->toBe(0);
});

test('a lease outside the renewal window is left alone', function () {
    Http::fake();

    $channel = Channel::factory()->create();
    ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addDays(9),
    ]);

    $this->artisan('websub:renew')->assertSuccessful();

    Http::assertNothingSent();
});

test('a hub rejection increments renewal_failures and raises an alert', function () {
    config()->set('services.websub.alert_webhook', 'https://alerts.test/hook');

    Http::fake([
        'https://hub.test/*' => Http::response('nope', 500),
        'https://alerts.test/*' => Http::response('', 200),
    ]);

    Log::spy();

    $channel = Channel::factory()->create();
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addHours(6),
    ]);

    $this->artisan('websub:renew')->assertSuccessful();

    $subscription->refresh();

    expect($subscription->renewal_failures)->toBe(1);

    Http::assertSent(fn ($request) => $request->url() === 'https://alerts.test/hook');

    Log::shouldHaveReceived('error')
        ->withArgs(fn (string $message) => str_contains($message, 'WebSub renewal failed'));
});

test('a successful hub verification clears the renewal failure counter', function () {
    $channel = Channel::factory()->create();
    $subscription = ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'renewal_failures' => 3,
    ]);

    $this->get('/websub/'.$subscription->callback_token.'?'.http_build_query([
        'hub.mode' => 'subscribe',
        'hub.topic' => $subscription->topic_url,
        'hub.challenge' => 'challenge-value',
        'hub.lease_seconds' => 432000,
    ]))->assertOk()->assertSee('challenge-value');

    expect($subscription->fresh()->renewal_failures)->toBe(0);
});

test('a failed subscription is retried by the renewal sweep', function () {
    Http::fake(['https://hub.test/*' => Http::response('', 202)]);

    $channel = Channel::factory()->create();
    $subscription = ChannelSubscription::factory()->forChannel($channel)->create([
        'status' => WebSubSubscriptionStatus::Failed,
        'expires_at' => null,
        'updated_at' => now()->subDay(),
    ]);

    $this->artisan('websub:renew')->assertSuccessful();

    Http::assertSent(fn ($request) => $request['hub.mode'] === 'subscribe');

    expect($subscription->fresh()->last_renewal_attempt_at)->not->toBeNull();
});

test('a subscription re-subscribed recently is not re-posted while the hub verifies', function () {
    Http::fake();

    $channel = Channel::factory()->create();
    ChannelSubscription::factory()->forChannel($channel)->active()->create([
        'expires_at' => now()->addHours(6),
        'last_renewal_attempt_at' => now()->subMinutes(30),
    ]);

    $this->artisan('websub:renew')->assertSuccessful();

    Http::assertNothingSent();
});

test('a hub outage raises one aggregated alert, not one per subscription', function () {
    config()->set('services.websub.alert_webhook', 'https://alerts.test/hook');

    Http::fake([
        'https://hub.test/*' => Http::response('down', 503),
        'https://alerts.test/*' => Http::response('', 200),
    ]);

    foreach (range(1, 3) as $ignored) {
        $channel = Channel::factory()->create();
        ChannelSubscription::factory()->forChannel($channel)->active()->create([
            'expires_at' => now()->addHours(6),
        ]);
    }

    $this->artisan('websub:renew')->assertSuccessful();

    $alerts = collect(Http::recorded())
        ->filter(fn ($pair) => str_contains($pair[0]->url(), 'alerts.test'));

    expect($alerts)->toHaveCount(1);
});
