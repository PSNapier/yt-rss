<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeedPreferencesController extends Controller
{
    /**
     * Persist the feed display toggles. Returns 204 so the client can write
     * with a plain fetch: an Inertia visit would reset the deferred `videos`
     * prop and flash the feed back to the loading skeleton.
     */
    public function update(Request $request): Response
    {
        $validated = $request->validate([
            'show_watched' => 'sometimes|boolean',
            'starred_only' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        if (array_key_exists('show_watched', $validated)) {
            $user->feed_show_watched = $validated['show_watched'];
        }

        if (array_key_exists('starred_only', $validated)) {
            $user->feed_starred_only = $validated['starred_only'];
        }

        $user->save();

        return response()->noContent();
    }
}
