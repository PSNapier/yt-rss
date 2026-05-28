<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AllVideosFeedController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user->id;

        $subscribedChannelIds = $user->channelGroups()
            ->join('channel_group_channel as cgc', 'cgc.channel_group_id', '=', 'channel_groups.id')
            ->distinct()
            ->pluck('cgc.channel_id');

        $videos = Video::query()
            ->select([
                'videos.id',
                'videos.youtube_video_id',
                'videos.title',
                'videos.thumbnail_url',
                'videos.published_at',
                'videos.channel_id',
                'user_video_states.state as user_state',
                \DB::raw('ucf.channel_id IS NOT NULL as channel_is_favorite'),
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
            ->where(function ($q) {
                $q->whereNull('user_video_states.state')
                    ->orWhere('user_video_states.state', '!=', 'hidden');
            })
            ->with(['channel:id,channel_id,name'])
            ->orderByDesc('videos.published_at')
            ->orderByDesc('videos.id')
            ->cursorPaginate(60)
            ->withQueryString();

        return Inertia::render('Videos/Feed', [
            'videos' => $videos,
        ]);
    }
}
