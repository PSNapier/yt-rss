<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\ChannelResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ChannelController extends Controller
{
    public function index(Request $request, ChannelGroup $group): Response
    {
        $this->authorize('view', $group);

        $channels = $group->channels()
            ->withPivot('is_favorite')
            ->orderByDesc('channel_group_channel.is_favorite')
            ->orderBy('channels.name')
            ->get([
                'channels.id',
                'channels.channel_id',
                'channels.name',
                'channels.last_fetched_at',
            ])
            ->map(fn (Channel $channel) => [
                'id' => $channel->id,
                'channel_id' => $channel->channel_id,
                'name' => $channel->name,
                'last_fetched_at' => $channel->last_fetched_at,
                'is_favorite' => (bool) $channel->pivot->is_favorite,
            ])
            ->values()
            ->all();

        return Inertia::render('Groups/Channels', [
            'group' => $group->only(['id', 'name']),
            'channels' => $channels,
        ]);
    }

    public function store(Request $request, ChannelGroup $group, ChannelResolver $resolver): RedirectResponse
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'mode' => 'required|in:handle,id',
            'value' => 'required|string|max:255',
            'custom_name' => 'nullable|string|max:255',
        ]);

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

        $customName = trim((string) ($validated['custom_name'] ?? ''));
        if ($customName !== '') {
            $channel->update(['name' => $customName]);
        }

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
            'name' => 'sometimes|required|string|max:255',
            'is_favorite' => 'sometimes|boolean',
        ]);

        if (! Arr::hasAny($validated, ['name', 'is_favorite'])) {
            throw ValidationException::withMessages([
                'name' => __('Provide a display name or favorite preference.'),
            ]);
        }

        if (array_key_exists('name', $validated)) {
            $channel->update(['name' => $validated['name']]);
        }

        if (array_key_exists('is_favorite', $validated)) {
            $group->channels()->updateExistingPivot($channel->id, [
                'is_favorite' => $validated['is_favorite'],
            ]);
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
