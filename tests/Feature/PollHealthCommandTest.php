<?php

use App\Models\Channel;
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
