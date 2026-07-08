<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeedCapController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->feed_cap_enabled = $validated['enabled'];
        $user->save();

        return back();
    }
}
