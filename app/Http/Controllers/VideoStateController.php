<?php

namespace App\Http\Controllers;

use App\Models\UserVideoState;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoStateController extends Controller
{
    public function store(Request $request, string $youtubeVideoId): Response
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

        // The feed writes state over XHR and updates itself optimistically, so
        // there is nothing to send back for those callers.
        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return back();
    }
}
