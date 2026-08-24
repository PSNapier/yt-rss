<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| WebSub upkeep
|--------------------------------------------------------------------------
|
| Google grants ~5-day leases, so the renewal sweep runs hourly and picks up
| anything expiring inside the configured window: several retry opportunities
| before a lease can lapse. The backstop is failure-driven, not freshness-driven,
| and only re-polls channels whose push path looks broken or anomalously quiet.
|
*/

Schedule::command('websub:renew')
    ->hourly()
    ->withoutOverlapping(50)
    ->runInBackground();

Schedule::command('websub:backstop')
    ->hourly()
    ->withoutOverlapping(50)
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| RSS polling
|--------------------------------------------------------------------------
|
| Polling is the guaranteed ingestion path: YouTube's publisher does not reliably
| ping its own hub for our feeds, so push cannot be relied on. The sweep polls
| every channel and passes force, which makes this schedule the only interval
| control. `withoutOverlapping` keeps a slow sweep from stacking on the next one.
|
*/

Schedule::command('channels:poll')
    ->everyThirtyMinutes()
    ->withoutOverlapping(29)
    ->runInBackground();
