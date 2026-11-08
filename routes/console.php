<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
