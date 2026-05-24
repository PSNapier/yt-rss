<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\ChannelResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ChannelController extends Controller
{
    public function index(Request $request, ChannelGroup $group): Response
    {
        $this->authorize('view', $group);

        $channels = $group->channels()
            ->orderBy('channels.name')
            ->get([
                'channels.id',
                'channels.channel_id',
                'channels.name',
                'channels.last_fetched_at',
            ]);

        $favoriteIds = array_flip(
            $request->user()->favoritedChannels()
                ->whereIn('channels.id', $channels->pluck('id')->all())
                ->pluck('channels.id')
                ->all()
        );

        $mapped = $channels->map(fn (Channel $channel) => [
            'id' => $channel->id,
            'channel_id' => $channel->channel_id,
            'name' => $channel->name,
            'last_fetched_at' => $channel->last_fetched_at,
            'is_favorite' => isset($favoriteIds[$channel->id]),
        ])
            ->sortByDesc('is_favorite')
            ->values()
            ->all();

        return Inertia::render('Groups/Channels', [
            'group' => $group->only(['id', 'name']),
            'channels' => $mapped,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $results = Channel::where('name', 'like', '%' . $validated['q'] . '%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'channel_id', 'name']);

        return response()->json($results);
    }

    public function store(Request $request, ChannelGroup $group, ChannelResolver $resolver): RedirectResponse
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'mode' => 'required|in:handle,id,existing',
            'value' => 'required_unless:mode,existing|string|max:255|nullable',
            'channel_id' => 'required_if:mode,existing|string|max:255|nullable',
        ]);

        if ($validated['mode'] === 'existing') {
            $channel = Channel::where('channel_id', $validated['channel_id'])->firstOrFail();
            $group->channels()->syncWithoutDetaching([$channel->id]);

            return back();
        }

        try {
            $info = $validated['mode'] === 'handle'
                ? $resolver->fromHandle($validated['value'])
                : $resolver->fromChannelId($validated['value']);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['value' => $e->getMessage()]);
        }

        $channel = Channel::firstOrCreate(
            ['channel_id' => $info['channel_id']],
            ['name' => $info['name'], 'rss_url' => $info['rss_url']]
        );

        $group->channels()->syncWithoutDetaching([$channel->id]);

        return back();
    }

    public function update(Request $request, ChannelGroup $group, Channel $channel): RedirectResponse
    {
        $this->authorize('update', $group);

        if (! $group->channels()->where('channels.id', $channel->id)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'is_favorite' => 'required|boolean',
        ]);

        $user = $request->user();
        if ($validated['is_favorite']) {
            $user->favoritedChannels()->syncWithoutDetaching([$channel->id]);
        } else {
            $user->favoritedChannels()->detach($channel->id);
        }

        return back();
    }

    public function destroy(Request $request, ChannelGroup $group, Channel $channel): RedirectResponse
    {
        $this->authorize('update', $group);

        $group->channels()->detach($channel->id);

        return back();
    }
}
