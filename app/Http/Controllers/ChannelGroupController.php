<?php

namespace App\Http\Controllers;

use App\Models\ChannelGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChannelGroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                'unique:channel_groups,name,NULL,id,user_id,'.$request->user()->id,
            ],
        ]);

        $request->user()->channelGroups()->create($validated);

        return to_route('subscriptions.index');
    }

    public function update(Request $request, ChannelGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        // Rename, hide and appearance are separate controls, so either field may come alone.
        $validated = $request->validate([
            'name' => [
                Rule::requiredIf(! $request->hasAny(['hidden', 'icon', 'color'])), 'string', 'max:100',
                'unique:channel_groups,name,'.$group->id.',id,user_id,'.$request->user()->id,
            ],
            'hidden' => ['sometimes', 'boolean'],
            // Raw Heroicons name (RocketLaunchIcon) or a legacy curated key (Braces). Null resets.
            'icon' => ['sometimes', 'nullable', 'string', 'max:64', 'regex:/^[A-Z][A-Za-z0-9]*$/'],
            'color' => ['sometimes', 'nullable', 'string', Rule::in(ChannelGroup::COLORS)],
        ]);

        $group->update($validated);

        return to_route('subscriptions.index');
    }

    public function destroy(Request $request, ChannelGroup $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $group->delete();

        return to_route('subscriptions.index');
    }
}
