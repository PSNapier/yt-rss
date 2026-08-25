<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'youtube' => [
        'api_key' => env('YOUTUBE_API_KEY'),
        'rss_cache_ttl' => env('RSS_CACHE_TTL', 30),
        /** Concurrent RSS URLs per Http::pool batch (whole group can still be large; lowers timeouts / 429s). */
        'rss_pool_chunk' => (int) env('RSS_POOL_CHUNK', 20),
        /** Connect timeout for a feed load, where a user is waiting on the response. */
        'rss_connect_timeout' => (float) env('RSS_CONNECT_TIMEOUT', 2.0),
        /** Total timeout for a feed load. [032] measured single requests succeeding well inside this. */
        'rss_timeout' => (float) env('RSS_TIMEOUT', 3.0),
    ],

    'polling' => [
        /** Concurrent requests per pool batch in a sweep. [032] measured one batch of 20 already losing a request to silence. */
        'pool_chunk' => (int) env('POLL_POOL_CHUNK', 5),
        /** Pause between pool batches. 193 channels fetched with a 150ms gap lost nothing; 10 ungapped batches of 20 lost 92%. */
        'inter_chunk_delay_ms' => (int) env('POLL_INTER_CHUNK_DELAY_MS', 500),
        /** Connect timeout for sweep requests: margin behind the pacing, not the fix. */
        'connect_timeout' => (float) env('POLL_CONNECT_TIMEOUT', 5.0),
        /** Total timeout for sweep requests. Nobody is waiting on a sweep, so it can afford to be patient. */
        'timeout' => (float) env('POLL_TIMEOUT', 10.0),
        /** Safety cap on channels polled per sweep; a no-op at current scale, and a signal that sharding is due if ever hit. */
        'max_per_sweep' => (int) env('POLL_MAX_PER_SWEEP', 1000),
        /** Share of a sweep's requests that must fail for non-block reasons before a failure storm is declared. Never pauses polling. */
        'failure_alert_ratio' => (float) env('POLL_FAILURE_ALERT_RATIO', 0.5),
        /** Share of a sweep's requests that must look blocked before polling pauses. */
        'block_failure_ratio' => (float) env('POLL_BLOCK_FAILURE_RATIO', 0.5),
        /** Below this many requests a sweep is too small for the ratio to mean anything. */
        'block_min_sample' => (int) env('POLL_BLOCK_MIN_SAMPLE', 5),
        /** How long polling pauses after a block is detected (YouTube blocks last hours to about a day). */
        'cooldown_hours' => (int) env('POLL_COOLDOWN_HOURS', 6),
        /** Window the health report aggregates sweep failures and block signals over. */
        'health_window_hours' => (int) env('POLL_HEALTH_WINDOW_HOURS', 24),
        /** How long sweep history is kept for the health report. */
        'sweep_history_days' => (int) env('POLL_SWEEP_HISTORY_DAYS', 30),
        /** Webhook posted to on a cooldown or a failure storm; null disables webhook alerting (logs still fire). */
        'alert_webhook' => env('POLL_ALERT_WEBHOOK'),
    ],

    'websub' => [
        'hub_url' => env('WEBSUB_HUB_URL', 'https://pubsubhubbub.appspot.com/subscribe'),
        /** Public HTTPS base for hub callbacks; falls back to APP_URL when null. */
        'callback_base' => env('WEBSUB_CALLBACK_BASE'),
        'user_agent' => env(
            'WEBSUB_USER_AGENT',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ),
        /** Re-subscribe a lease this many hours before it expires (Google grants 5-day leases). */
        'renew_within_hours' => (int) env('WEBSUB_RENEW_WITHIN_HOURS', 48),
        /** Max subscriptions renewed per sweep (steady state at 100k channels is ~850/hour). */
        'renew_limit' => (int) env('WEBSUB_RENEW_LIMIT', 1000),
        /** Wait this long before re-POSTing a subscribe the hub has not verified yet. */
        'renew_retry_hours' => (int) env('WEBSUB_RENEW_RETRY_HOURS', 6),
        /** Webhook posted to when a renewal fails; null disables webhook alerting (logs still fire). */
        'alert_webhook' => env('WEBSUB_ALERT_WEBHOOK'),
        /** A channel is anomalously silent past this multiple of its own median upload gap. */
        'backstop_silence_multiplier' => (float) env('WEBSUB_BACKSTOP_SILENCE_MULTIPLIER', 3.0),
        /** Floor on the silence threshold, so fast-cadence channels are not polled constantly. */
        'backstop_min_silence_hours' => (int) env('WEBSUB_BACKSTOP_MIN_SILENCE_HOURS', 48),
        /** Never re-poll a channel polled more recently than this. */
        'backstop_min_repoll_hours' => (int) env('WEBSUB_BACKSTOP_MIN_REPOLL_HOURS', 6),
        /** A gap this long between backstop sweeps means the callback was offline. */
        'backstop_downtime_gap_hours' => (int) env('WEBSUB_BACKSTOP_DOWNTIME_GAP_HOURS', 3),
        /** Max channels re-polled per backstop sweep. */
        'backstop_limit' => (int) env('WEBSUB_BACKSTOP_LIMIT', 200),
    ],

];
