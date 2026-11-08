<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\UserVideoState;
use App\Models\Video;
use App\Services\FeedStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::fake(['*' => Http::response('<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>', 200)]);
});

/**
 * Create $count videos on the channel and mark the first $watched as watched.
 */
function seedWatches(User $user, Channel $channel, int $count, int $watched, $watchedAt = null, bool $short = false): void
{
    Video::factory()->count($count)->create(['channel_id' => $channel->id, 'is_short' => $short])
        ->take($watched)
        ->each(function (Video $video) use ($user, $watchedAt) {
            $state = UserVideoState::factory()->watched()->create([
                'user_id' => $user->id,
                'youtube_video_id' => $video->youtube_video_id,
            ]);

            if ($watchedAt) {
                $state->forceFill(['updated_at' => $watchedAt])->saveQuietly();
            }
        });
}

function statsFor(User $user, array $channelIds, bool $shorts = false): array
{
    return app(FeedStats::class)->for($user, $channelIds, $shorts);
}

it('returns stats on each feed', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $channel = Channel::factory()->create(['last_fetched_at' => now()]);
    $group->channels()->attach($channel);
    $user->favoritedChannels()->attach($channel);
    seedWatches($user, $channel, 6, 5);
    seedWatches($user, $channel, 2, 1, null, true);

    $feeds = [
        ['Groups/Show', route('groups.show', $group)],
        ['Videos/Feed', route('feed.index')],
        ['Videos/Shorts', route('shorts.index')],
    ];

    foreach ($feeds as [$component, $url]) {
        $response = $this->actingAs($user)->get($url, inertiaPartial($component, ['stats']));

        $response->assertOk();
        expect($response->json('props.stats'))->toHaveKeys(['channel_count', 'starred_count', 'most_watched'])
            ->and($response->json('props.stats.channel_count'))->toBe(1)
            ->and($response->json('props.stats.starred_count'))->toBe(1)
            ->and($response->json('props.stats.most_watched'))->toHaveKeys(['all_time', 'last_30_days', 'ratio']);
    }

    $long = $this->actingAs($user)->get(route('feed.index'), inertiaPartial('Videos/Feed', ['stats']));
    $short = $this->actingAs($user)->get(route('shorts.index'), inertiaPartial('Videos/Shorts', ['stats']));

    expect($long->json('props.stats.most_watched.all_time.count'))->toBe(5)
        ->and($short->json('props.stats.most_watched.all_time.count'))->toBe(1);
});

it('picks the all time most watched channel', function () {
    $user = User::factory()->create();
    $a = Channel::factory()->create();
    $b = Channel::factory()->create();
    $c = Channel::factory()->create();

    seedWatches($user, $a, 4, 3, now()->subDays(40));
    seedWatches($user, $b, 4, 3, now()->subDays(2));
    seedWatches($user, $c, 4, 1);

    $stats = statsFor($user, [$a->id, $b->id, $c->id]);

    // a and b tie at 3 watched, b was watched more recently.
    expect($stats['most_watched']['all_time'])->toMatchArray([
        'channel_id' => $b->channel_id,
        'name' => $b->name,
        'count' => 3,
    ]);
});

it('limits the 30 day measure', function () {
    $user = User::factory()->create();
    $old = Channel::factory()->create();
    $recent = Channel::factory()->create();

    seedWatches($user, $old, 10, 10, now()->subDays(31));
    seedWatches($user, $recent, 3, 2, now()->subDays(5));

    $stats = statsFor($user, [$old->id, $recent->id]);

    expect($stats['most_watched']['all_time']['channel_id'])->toBe($old->channel_id)
        ->and($stats['most_watched']['last_30_days'])->toMatchArray([
            'channel_id' => $recent->channel_id,
            'count' => 2,
        ]);

    $none = statsFor($user, [$old->id]);
    expect($none['most_watched']['last_30_days'])->toBeNull();
});

it('requires a minimum for ratio', function () {
    $user = User::factory()->create();
    $small = Channel::factory()->create();
    $big = Channel::factory()->create();

    // 4 of 4 is 100% but under the 5 watched minimum.
    seedWatches($user, $small, 4, 4);
    // 5 of 10 is 50%.
    seedWatches($user, $big, 10, 5);

    $stats = statsFor($user, [$small->id, $big->id]);

    expect($stats['most_watched']['ratio'])->toMatchArray([
        'channel_id' => $big->channel_id,
        'count' => 50,
    ]);

    expect(statsFor($user, [$small->id])['most_watched']['ratio'])->toBeNull();
});

it('scopes stats to the feed and user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $inFeed = Channel::factory()->create();
    $outOfFeed = Channel::factory()->create();

    seedWatches($user, $inFeed, 3, 1);
    seedWatches($user, $outOfFeed, 9, 9);

    // Another user's watches never count, even on the same channel.
    seedWatches($other, $inFeed, 5, 5);

    $stats = statsFor($user, [$inFeed->id]);

    expect($stats['channel_count'])->toBe(1)
        ->and($stats['most_watched']['all_time'])->toMatchArray([
            'channel_id' => $inFeed->channel_id,
            'count' => 1,
        ]);

    $empty = statsFor(User::factory()->create(), [$inFeed->id]);
    expect($empty['most_watched'])->toBe(['all_time' => null, 'last_30_days' => null, 'ratio' => null]);
});
