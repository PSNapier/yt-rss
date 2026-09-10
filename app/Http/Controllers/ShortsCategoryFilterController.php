<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShortsCategoryFilterController extends Controller
{
    /**
     * Replace the set of categories hidden from the Shorts feed. The payload is the
     * whole hidden set, so unchecking a chip is an absence rather than a delete call.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'hidden_group_ids' => 'present|array',
            'hidden_group_ids.*' => [
                'integer',
                Rule::exists('channel_groups', 'id')->where('user_id', $user->id),
            ],
        ]);

        $user->hiddenShortGroups()->sync($validated['hidden_group_ids']);

        return back();
    }
}
