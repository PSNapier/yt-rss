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

test('prune-shorts walks one id window at a time so a long backlog can be chunked', function () {
    $channel = Channel::factory()->create();

    $first = Video::factory()->for($channel)->create(['youtube_video_id' => 'ssDbeb9vB6g']);
    $second = Video::factory()->for($channel)->create(['youtube_video_id' => '1PZKfIyrYOc']);
    $third = Video::factory()->for($channel)->create(['youtube_video_id' => 'aaaaaaaaaaa']);

    Http::fake([
        '*' => Http::response('<link rel="canonical" href="https://www.youtube.com/shorts/x">', 200),
    ]);

    Artisan::call('videos:prune-shorts', ['--after-id' => $first->id, '--limit' => 1, '--sleep' => 0]);

    // The window starts after `first` and holds one row, so only `second` is touched.
    expect($first->fresh()->is_short)->toBeFalse();
    expect($second->fresh()->is_short)->toBeTrue();
    expect($third->fresh()->is_short)->toBeFalse();

    // The command reports where the next window should start.
    expect(Artisan::output())->toContain("--after-id={$second->id}");
});
