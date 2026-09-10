<?php

namespace App\Http\Controllers;

use App\Models\ChannelGroup;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ShortsFeedController extends Controller
{
    /**
     * The Shorts feed is the mirror of `AllVideosFeedController::index`: same read,
     * `is_short` inverted, narrowed to the categories the user has not hidden.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user->id;

        $hiddenGroupIds = $user->hiddenShortGroups()->pluck('channel_groups.id');

        // A channel reached through any visible group stays in, even when one of
        // its other groups is hidden.
        $subscribedChannelIds = $user->channelGroups()
            ->whereNotIn('channel_groups.id', $hiddenGroupIds)
            ->join('channel_group_channel as cgc', 'cgc.channel_group_id', '=', 'channel_groups.id')
            ->distinct()
            ->pluck('cgc.channel_id');

        $capEnabled = (bool) $user->feed_cap_enabled;

        return Inertia::render('Videos/Shorts', [
            'capEnabled' => $capEnabled,
            'categories' => $user->channelGroups()
                ->orderBy('name')
                ->get(['id', 'name', 'icon'])
                ->map(fn (ChannelGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'icon' => $group->icon,
                    'hidden' => $hiddenGroupIds->contains($group->id),
                ])
                ->values()
                ->all(),
            'videos' => Inertia::defer(function () use ($subscribedChannelIds, $userId, $capEnabled) {
                return Video::query()
                    ->select([
                        'videos.id',
                        'videos.youtube_video_id',
                        'videos.title',
                        'videos.thumbnail_url',
                        'videos.published_at',
                        'videos.channel_id',
                        'user_video_states.state as user_state',
                        DB::raw('ucf.channel_id IS NOT NULL as channel_is_favorite'),
                    ])
                    ->whereIn('videos.channel_id', $subscribedChannelIds)
                    ->leftJoin('user_video_states', function ($join) use ($userId) {
                        $join->on('user_video_states.youtube_video_id', '=', 'videos.youtube_video_id')
                            ->where('user_video_states.user_id', $userId);
                    })
                    ->leftJoin('user_channel_favorites as ucf', function ($join) use ($userId) {
                        $join->on('ucf.channel_id', '=', 'videos.channel_id')
                            ->where('ucf.user_id', $userId);
                    })
                    ->where('videos.is_short', true)
                    ->where(function ($q) {
                        $q->whereNull('user_video_states.state')
                            ->orWhere('user_video_states.state', '!=', 'hidden');
                    })
                    ->when($capEnabled, fn ($q) => $q->unwatchedCappedPerChannel($userId, shorts: true))
                    ->with(['channel:id,channel_id,name'])
                    ->orderByDesc('videos.published_at')
                    ->orderByDesc('videos.id')
                    ->cursorPaginate(24)
                    ->withQueryString();
            }),
        ]);
    }
}
