<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVideoState;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The numbers behind the feed stats card: channel count, starred count, and the
 * most watched channel by three measures. Each measure is a single query.
 */
class FeedStats
{
    public const RATIO_MIN_WATCHED = 5;

    public const RECENT_DAYS = 30;

    /**
     * @param  iterable<int>  $channelIds  channels (by `channels.id`) in the feed
     * @param  bool  $shorts  count Shorts only (true) or regular videos only (false)
     * @return array{channel_count: int, starred_count: int, most_watched: array{all_time: ?array, last_30_days: ?array, ratio: ?array}}
     */
    public function for(User $user, iterable $channelIds, bool $shorts = false): array
    {
        $ids = collect($channelIds)->unique()->values()->all();

        if ($ids === []) {
            return [
                'channel_count' => 0,
                'starred_count' => 0,
                'most_watched' => ['all_time' => null, 'last_30_days' => null, 'ratio' => null],
            ];
        }

        $starred = DB::table('user_channel_favorites')
            ->where('user_id', $user->id)
            ->whereIn('channel_id', $ids)
            ->count();

        return [
            'channel_count' => count($ids),
            'starred_count' => $starred,
            'most_watched' => [
                'all_time' => $this->mostWatched($user, $ids, $shorts),
                'last_30_days' => $this->mostWatched($user, $ids, $shorts, now()->subDays(self::RECENT_DAYS)),
                'ratio' => $this->highestRatio($user, $ids, $shorts),
            ],
        ];
    }

    /**
     * @param  array<int>  $ids
     */
    private function base(User $user, array $ids, bool $shorts): Builder
    {
        return DB::table('videos')
            ->join('channels', 'channels.id', '=', 'videos.channel_id')
            ->join('user_video_states as uvs', function ($join) use ($user) {
                $join->on('uvs.youtube_video_id', '=', 'videos.youtube_video_id')
                    ->where('uvs.user_id', $user->id)
                    ->where('uvs.state', UserVideoState::STATE_WATCHED);
            })
            ->whereIn('videos.channel_id', $ids)
            ->where('videos.is_short', $shorts)
            ->groupBy('channels.id', 'channels.channel_id', 'channels.name');
    }

    /**
     * @param  array<int>  $ids
     */
    private function mostWatched(User $user, array $ids, bool $shorts, $since = null): ?array
    {
        $row = $this->base($user, $ids, $shorts)
            ->when($since, fn ($q) => $q->where('uvs.updated_at', '>=', $since))
            ->select('channels.channel_id', 'channels.name')
            ->selectRaw('count(*) as watched')
            ->selectRaw('max(uvs.updated_at) as last_watched_at')
            ->orderByDesc('watched')
            ->orderByDesc('last_watched_at')
            ->first();

        return $row ? $this->format($row, (int) $row->watched) : null;
    }

    /**
     * @param  array<int>  $ids
     */
    private function highestRatio(User $user, array $ids, bool $shorts): ?array
    {
        $row = $this->base($user, $ids, $shorts)
            ->select('channels.id', 'channels.channel_id', 'channels.name')
            ->selectRaw('count(*) as watched')
            ->selectRaw('max(uvs.updated_at) as last_watched_at')
            ->selectSub(
                DB::table('videos as v2')
                    ->selectRaw('count(*)')
                    ->whereColumn('v2.channel_id', 'channels.id')
                    ->where('v2.is_short', $shorts),
                'ingested'
            )
            ->havingRaw('count(*) >= ?', [self::RATIO_MIN_WATCHED])
            ->orderByRaw('count(*) * 1.0 / ingested desc')
            ->orderByDesc('watched')
            ->orderByDesc('last_watched_at')
            ->first();

        if (! $row || (int) $row->ingested === 0) {
            return null;
        }

        return $this->format($row, (int) round($row->watched / $row->ingested * 100));
    }

    private function format(object $row, int $count): array
    {
        return [
            'channel_id' => $row->channel_id,
            'name' => $row->name,
            'count' => $count,
        ];
    }
}
