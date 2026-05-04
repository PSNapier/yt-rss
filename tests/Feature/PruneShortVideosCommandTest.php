<?php

use App\Models\Channel;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('prune-shorts removes Shorts and related user video states', function () {
    $channel = Channel::factory()->create();
    $user = User::factory()->create();
    $short = Video::factory()->for($channel)->create(['youtube_video_id' => 'ssDbeb9vB6g']);
    $long = Video::factory()->for($channel)->create(['youtube_video_id' => '1PZKfIyrYOc']);
    UserVideoState::factory()->for($user)->create(['youtube_video_id' => 'ssDbeb9vB6g']);
    UserVideoState::factory()->for($user)->create(['youtube_video_id' => '1PZKfIyrYOc']);

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

    expect(Video::query()->whereKey($short->id)->exists())->toBeFalse();
    expect(Video::query()->whereKey($long->id)->exists())->toBeTrue();
    expect(UserVideoState::query()->where('youtube_video_id', 'ssDbeb9vB6g')->exists())->toBeFalse();
    expect(UserVideoState::query()->where('youtube_video_id', '1PZKfIyrYOc')->exists())->toBeTrue();
});
