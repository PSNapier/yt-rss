<?php

use App\Models\Channel;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('prune-shorts flags Shorts instead of deleting them', function () {
    $channel = Channel::factory()->create();
    $user = User::factory()->create();
    $short = Video::factory()->for($channel)->create(['youtube_video_id' => 'ssDbeb9vB6g']);
    $long = Video::factory()->for($channel)->create(['youtube_video_id' => '1PZKfIyrYOc']);
    UserVideoState::factory()->for($user)->create(['youtube_video_id' => 'ssDbeb9vB6g']);

    Http::fake([
        '*youtube.com/watch?v=ssDbeb9vB6g*' => Http::response(
            '<head><link rel="canonical" href="https://www.youtube.com/shorts/ssDbeb9vB6g"></head>',
            200
        ),
        '*youtube.com/watch?v=1PZKfIyrYOc*' => Http::response(
            '<head><link rel="canonical" href="https://www.youtube.com/watch?v=1PZKfIyrYOc"></head>',
            200
        ),
    ]);

    Artisan::call('videos:prune-shorts', ['--sleep' => 0]);

    expect($short->fresh()->is_short)->toBeTrue()
        ->and($long->fresh()->is_short)->toBeFalse()
        ->and(UserVideoState::query()->where('youtube_video_id', 'ssDbeb9vB6g')->exists())->toBeTrue();
});

test('prune-shorts skips videos already flagged', function () {
    $channel = Channel::factory()->create();
    Video::factory()->for($channel)->create([
        'youtube_video_id' => 'ssDbeb9vB6g',
        'is_short' => true,
    ]);

    Http::fake(['*' => Http::response('<head></head>', 200)]);

    Artisan::call('videos:prune-shorts', ['--sleep' => 0]);

    Http::assertNothingSent();
});
