<?php

namespace App\Http\Controllers;

use App\Models\UserVideoState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VideoStateController extends Controller
{
    public function store(Request $request, string $youtubeVideoId): RedirectResponse
    {
        $validated = $request->validate([
            'state' => 'nullable|in:watched,hidden',
        ]);

        $userId = $request->user()->id;

        if (($validated['state'] ?? null) === null) {
            UserVideoState::query()
                ->where('user_id', $userId)
                ->where('youtube_video_id', $youtubeVideoId)
                ->delete();
        } else {
            UserVideoState::updateOrCreate(
                ['user_id' => $userId, 'youtube_video_id' => $youtubeVideoId],
                ['state' => $validated['state']]
            );
        }

        return back();
    }
}
