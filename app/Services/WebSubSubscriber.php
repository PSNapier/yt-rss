<?php

namespace App\Services;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\Channel;
use App\Models\ChannelSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebSubSubscriber
{
    public function __construct(
        protected RssFetcher $fetcher,
    ) {}

    /**
     * Ensure the channel has a WebSub subscription and is backfilled once.
     * Idempotent: skips hub subscribe when a pending/active row already exists.
     */
    public function ensureSubscribed(Channel $channel): ChannelSubscription
    {
        $existing = $channel->webSubSubscription;

        if ($existing !== null
            && in_array($existing->status, [
                WebSubSubscriptionStatus::Pending,
                WebSubSubscriptionStatus::Active,
            ], true)
        ) {
            return $existing;
        }

        $subscription = $existing ?? new ChannelSubscription(['channel_id' => $channel->id]);

        $subscription->fill([
            'topic_url' => $channel->rssUrl(),
            'callback_token' => $subscription->callback_token ?: Str::random(40),
            'secret' => $subscription->secret ?: Str::random(32),
            'status' => WebSubSubscriptionStatus::Pending,
            'lease_seconds' => null,
            'expires_at' => null,
            'last_verified_at' => null,
        ]);
        $subscription->save();

        $this->backfill($channel);
        $this->requestSubscribe($subscription);

        return $subscription->fresh();
    }

    /**
     * Re-subscribe an existing lease before it expires, reusing its token and secret.
     *
     * The lease only really extends once the hub re-verifies the callback, so a
     * successful POST is recorded as an attempt, not as a renewal.
     */
    public function renew(ChannelSubscription $subscription): bool
    {
        $accepted = $this->postSubscribe($subscription);

        if ($accepted) {
            $subscription->forceFill([
                'last_renewal_attempt_at' => now(),
            ])->save();

            return true;
        }

        $subscription->forceFill([
            'last_renewal_attempt_at' => now(),
            'renewal_failures' => $subscription->renewal_failures + 1,
        ])->save();

        return false;
    }

    protected function backfill(Channel $channel): void
    {
        $result = $this->fetcher->fetchForChannels(collect([$channel]), force: true);

        if ($result['failed'] + $result['blocked'] > 0) {
            Log::warning('WebSub backfill poll failed', [
                'channel_id' => $channel->channel_id,
                'result' => $result,
            ]);
        }
    }

    protected function requestSubscribe(ChannelSubscription $subscription): void
    {
        if ($this->postSubscribe($subscription)) {
            return;
        }

        $subscription->forceFill([
            'status' => WebSubSubscriptionStatus::Failed,
        ])->save();
    }

    /**
     * POST a subscribe request to the hub. Returns whether the hub accepted it.
     */
    protected function postSubscribe(ChannelSubscription $subscription): bool
    {
        $hubUrl = (string) config('services.websub.hub_url');

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => (string) config('services.websub.user_agent'),
                ])
                ->post($hubUrl, [
                    'hub.mode' => 'subscribe',
                    'hub.topic' => $subscription->topic_url,
                    'hub.callback' => $subscription->callbackUrl(),
                    'hub.secret' => $subscription->secret,
                    'hub.verify' => 'async',
                ]);
        } catch (\Throwable $e) {
            Log::warning('WebSub subscribe request errored', [
                'channel_id' => $subscription->channel_id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if ($response->successful()) {
            return true;
        }

        Log::warning('WebSub subscribe request failed', [
            'channel_id' => $subscription->channel_id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }
}
