<?php

namespace App\Http\Controllers;

use App\Models\ChannelGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChannelGroupController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = $request->user()
            ->channelGroups()
            ->withCount('channels')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                'unique:channel_groups,name,NULL,id,user_id,'.$request->user()->id,
            ],
        ]);

        $group = $request->user()->channelGroups()->create($validated);

        return to_route('groups.show', $group);
    }

    public function update(Request $request, ChannelGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                'unique:channel_groups,name,'.$group->id.',id,user_id,'.$request->user()->id,
            ],
        ]);

        $group->update($validated);

        return back();
    }

    public function destroy(Request $request, ChannelGroup $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $group->delete();

        return to_route('groups.index');
    }
}
