<?php

namespace App\Services;

use App\Models\ChannelSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Surfaces WebSub failures loudly: silent push loss is invisible data loss.
 */
class WebSubAlerter
{
    /**
     * Report every renewal failure from one sweep as a single alert.
     *
     * @param  Collection<int, ChannelSubscription>  $failures
     */
    public function renewalsFailed(Collection $failures): void
    {
        if ($failures->isEmpty()) {
            return;
        }

        $channelIds = $failures
            ->map(fn (ChannelSubscription $s) => $this->safeChannelId($s))
            ->all();

        Log::error('WebSub renewal failed', [
            'failed_count' => $failures->count(),
            'subscription_ids' => $failures->pluck('id')->all(),
            'channel_ids' => array_slice($channelIds, 0, 25),
        ]);

        $sample = implode(', ', array_slice($channelIds, 0, 5));

        $this->notify(sprintf(
            'WebSub renewal failed for %d subscription(s). Sample channels: %s',
            $failures->count(),
            $sample,
        ));
    }

    /**
     * Report every failed first-time subscribe from one sweep as a single alert.
     *
     * @param  Collection<int, ChannelSubscription>  $failures
     */
    public function subscribesFailed(Collection $failures): void
    {
        if ($failures->isEmpty()) {
            return;
        }

        $channelIds = $failures
            ->map(fn (ChannelSubscription $s) => $this->safeChannelId($s))
            ->all();

        Log::error('WebSub subscribe failed', [
            'failed_count' => $failures->count(),
            'subscription_ids' => $failures->pluck('id')->all(),
            'channel_ids' => array_slice($channelIds, 0, 25),
        ]);

        $sample = implode(', ', array_slice($channelIds, 0, 5));

        $this->notify(sprintf(
            'WebSub subscribe failed for %d channel(s). Sample channels: %s',
            $failures->count(),
            $sample,
        ));
    }

    protected function notify(string $message): void
    {
        $webhook = (string) config('services.websub.alert_webhook');

        if ($webhook === '') {
            return;
        }

        try {
            Http::timeout(5)->post($webhook, ['text' => $message]);
        } catch (\Throwable $e) {
            Log::warning('WebSub alert webhook failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Channel ids reach an operator's alert channel, and users choose them:
     * strip control characters and cap the length before they leave the app.
     */
    protected function safeChannelId(ChannelSubscription $subscription): string
    {
        $raw = $subscription->channel?->channel_id ?? (string) $subscription->channel_id;

        return mb_substr(preg_replace('/[^\w\-]/u', '', $raw) ?? '', 0, 64);
    }
}
