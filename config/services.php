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
    ],

    'websub' => [
        'hub_url' => env('WEBSUB_HUB_URL', 'https://pubsubhubbub.appspot.com/subscribe'),
        /** Public HTTPS base for hub callbacks; falls back to APP_URL when null. */
        'callback_base' => env('WEBSUB_CALLBACK_BASE'),
        'user_agent' => env(
            'WEBSUB_USER_AGENT',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ),
    ],

];
