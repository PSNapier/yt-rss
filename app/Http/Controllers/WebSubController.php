<?php

namespace App\Http\Controllers;

use App\Enums\WebSubSubscriptionStatus;
use App\Models\ChannelSubscription;
use App\Services\RssFetcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebSubController extends Controller
{
    /**
     * Hub verification challenge (GET). Echo hub.challenge and mark subscription active.
     */
    public function verify(Request $request, string $token): Response
    {
        $subscription = ChannelSubscription::query()
            ->where('callback_token', $token)
            ->firstOrFail();

        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $topic = $request->query('hub_topic') ?? $request->query('hub.topic');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');
        $leaseSeconds = $request->query('hub_lease_seconds') ?? $request->query('hub.lease_seconds');

        if (! is_string($challenge) || $challenge === '') {
            abort(400, 'Missing hub.challenge');
        }

        if (is_string($mode) && $mode === 'unsubscribe') {
            $subscription->forceFill([
                'status' => WebSubSubscriptionStatus::Failed,
                'last_verified_at' => now(),
            ])->save();

            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        if (is_string($topic) && $topic !== '' && $topic !== $subscription->topic_url) {
            Log::warning('WebSub challenge topic mismatch', [
                'subscription_id' => $subscription->id,
                'expected' => $subscription->topic_url,
                'got' => $topic,
            ]);
            abort(404);
        }

        $lease = is_numeric($leaseSeconds) ? (int) $leaseSeconds : null;

        $subscription->forceFill([
            'status' => WebSubSubscriptionStatus::Active,
            'lease_seconds' => $lease,
            'expires_at' => $lease !== null ? now()->addSeconds($lease) : null,
            'last_verified_at' => now(),
        ])->save();

        return response($challenge, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Hub content notification (POST). HMAC-verify then ingest Atom fragment.
     */
    public function receive(Request $request, string $token, RssFetcher $fetcher): Response
    {
        $subscription = ChannelSubscription::query()
            ->where('callback_token', $token)
            ->with('channel')
            ->firstOrFail();

        $rawBody = $request->getContent();
        $signatureHeader = $request->header('X-Hub-Signature', '');

        if (! $this->signatureIsValid($rawBody, $signatureHeader, $subscription->secret)) {
            Log::warning('WebSub invalid signature', [
                'subscription_id' => $subscription->id,
                'channel_id' => $subscription->channel_id,
            ]);

            return response('Invalid signature', 403);
        }

        try {
            $fetcher->ingest($subscription->channel, $rawBody);
            $subscription->channel->forceFill(['last_fetched_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::warning('WebSub ingest failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return response('Ingest failed', 500);
        }

        return response('OK', 200);
    }

    protected function signatureIsValid(string $rawBody, string $signatureHeader, string $secret): bool
    {
        if ($signatureHeader === '' || ! str_contains($signatureHeader, '=')) {
            return false;
        }

        [$algo, $hash] = explode('=', $signatureHeader, 2);

        if (strtolower($algo) !== 'sha1' || $hash === '') {
            return false;
        }

        $expected = hash_hmac('sha1', $rawBody, $secret);

        return hash_equals($expected, $hash);
    }
}
