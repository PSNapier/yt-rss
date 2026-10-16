<?php

namespace App\Http\Controllers;

use App\Models\ChannelGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        // Rename and hide are separate controls, so either field may come alone.
        $validated = $request->validate([
            'name' => [
                'required_without:hidden', 'string', 'max:100',
                'unique:channel_groups,name,'.$group->id.',id,user_id,'.$request->user()->id,
            ],
            'hidden' => ['sometimes', 'boolean'],
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
