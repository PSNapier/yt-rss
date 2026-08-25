<?php

use App\Models\Channel;
use App\Models\PollSweep;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it reports an rss url deviation', function () {
    Channel::factory()->create([
        'channel_id' => 'UCdeviant000000000000000',
        'rss_url' => 'https://www.youtube.com/feeds/videos.xml?user=legacy',
    ]);

    $this->artisan('poll:health')
        ->expectsOutputToContain('UCdeviant000000000000000')
        ->assertSuccessful();
});

test('it reports no deviation when every rss url is canonical', function () {
    Channel::factory()->count(3)->create();

    $this->artisan('poll:health')
        ->expectsOutputToContain('All rss_url values are canonical')
        ->assertSuccessful();
});

test('it names the dominant failure category over the window', function () {
    PollSweep::create([
        'started_at' => now()->subHour(),
        'finished_at' => now()->subHour(),
        'channels_polled' => 100,
        'fetched' => 40,
        'failed' => 60,
        'failure_categories' => ['read_timeout' => 50, 'dns' => 10],
    ]);

    $this->artisan('poll:health')
        ->expectsOutputToContain('read_timeout')
        ->assertSuccessful();
});

test('it reports no failure categories when the window is clean', function () {
    PollSweep::create([
        'started_at' => now()->subHour(),
        'finished_at' => now()->subHour(),
        'channels_polled' => 10,
        'fetched' => 10,
    ]);

    $this->artisan('poll:health')
        ->expectsOutputToContain('Failure categories: none')
        ->assertSuccessful();
});

test('it distinguishes a cooldown from a failure storm in the sweep window', function () {
    PollSweep::create([
        'started_at' => now()->subHours(2),
        'finished_at' => now()->subHours(2),
        'channels_polled' => 10,
        'blocked' => 10,
        'cooldown_triggered' => true,
    ]);

    PollSweep::create([
        'started_at' => now()->subHour(),
        'finished_at' => now()->subHour(),
        'channels_polled' => 10,
        'failed' => 10,
        'failure_categories' => ['read_timeout' => 10],
        'failure_alert' => true,
    ]);

    $this->artisan('poll:health')
        ->expectsOutputToContain('1 cooldowns, 1 failure storms')
        ->assertSuccessful();
});
