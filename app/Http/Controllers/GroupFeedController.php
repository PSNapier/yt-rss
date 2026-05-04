<?php

namespace App\Http\Controllers;

use App\Models\ChannelGroup;
use App\Models\Video;
use App\Services\RssFetcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupFeedController extends Controller
{
    public function show(Request $request, ChannelGroup $group, RssFetcher $fetcher): Response
    {
        $this->authorize('view', $group);

        $fetcher->fetchForGroup($group);

        $userId = $request->user()->id;

        $videos = Video::query()
            ->select([
                'videos.id',
                'videos.youtube_video_id',
                'videos.title',
                'videos.thumbnail_url',
                'videos.published_at',
                'videos.channel_id',
                'user_video_states.state as user_state',
                'cgc_fav.is_favorite as channel_is_favorite',
            ])
            ->join('channel_group_channel as cgc_fav', function ($join) use ($group) {
                $join->on('cgc_fav.channel_id', '=', 'videos.channel_id')
                    ->where('cgc_fav.channel_group_id', $group->id);
            })
            ->leftJoin('user_video_states', function ($join) use ($userId) {
                $join->on('user_video_states.youtube_video_id', '=', 'videos.youtube_video_id')
                    ->where('user_video_states.user_id', $userId);
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

        return Inertia::render('Groups/Show', [
            'group' => $group->only(['id', 'name']),
            'videos' => $videos,
        ]);
    }

    public function refresh(Request $request, ChannelGroup $group, RssFetcher $fetcher): RedirectResponse
    {
        $this->authorize('view', $group);

        $result = $fetcher->fetchForGroup($group, force: true);

        return back()->with('toast', [
            'type' => $result['failed'] > 0 ? 'warning' : 'success',
            'message' => "Refreshed {$result['fetched']} channels (failed: {$result['failed']}).",
        ]);
    }
}
