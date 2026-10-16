<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShortsVisibilityController extends Controller
{
    /**
     * Hide or show the Shorts page as a whole. Distinct from the per-category
     * filter on the Shorts page itself.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hidden' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->shorts_hidden = $validated['hidden'];
        $user->save();

        return back();
    }
}
