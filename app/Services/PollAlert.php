<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The one way polling shouts.
 *
 * Both of the conditions worth waking someone for, a block cooldown and a failure
 * storm that is not a block, go out here, so configuring `POLL_ALERT_WEBHOOK` once
 * covers both and neither ends up visible only in `laravel.log`.
 */
class PollAlert
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function raise(string $message, array $context = []): void
    {
        Log::error($message, $context);

        $webhook = config('services.polling.alert_webhook');

        if (! is_string($webhook) || $webhook === '') {
            return;
        }

        try {
            Http::timeout(5)->post($webhook, [
                'text' => $message.($context === [] ? '' : ' '.json_encode($context)),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Poll alert delivery failed', ['error' => $e->getMessage()]);
        }
    }
}
