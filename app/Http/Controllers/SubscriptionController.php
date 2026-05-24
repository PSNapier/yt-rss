<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\ChannelResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $groups = $user->channelGroups()
            ->orderBy('name')
            ->get(['id', 'name']);

        $userGroupIds = $groups->pluck('id')->all();

        $favoriteIds = array_flip(
            $user->favoritedChannels()->pluck('channels.id')->all()
        );

        $channels = Channel::query()
            ->whereHas('channelGroups', fn ($q) => $q->whereIn('channel_groups.id', $userGroupIds))
            ->with(['channelGroups' => fn ($q) => $q->whereIn('channel_groups.id', $userGroupIds)->select('channel_groups.id')])
            ->orderBy('channels.name')
            ->get(['channels.id', 'channels.channel_id', 'channels.name', 'channels.last_fetched_at'])
            ->map(fn (Channel $channel) => [
                'id' => $channel->id,
                'channel_id' => $channel->channel_id,
                'name' => $channel->name,
                'last_fetched_at' => $channel->last_fetched_at,
                'is_favorite' => isset($favoriteIds[$channel->id]),
                'group_ids' => $channel->channelGroups->pluck('id')->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Subscriptions', [
            'channels' => $channels,
            'groups' => $groups->map(fn ($g) => ['id' => $g->id, 'name' => $g->name])->values()->all(),
        ]);
    }

    public function store(Request $request, ChannelResolver $resolver): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'mode' => 'required|in:handle,id,existing',
            'value' => 'required_unless:mode,existing|string|max:255|nullable',
            'channel_id' => 'required_if:mode,existing|string|max:255|nullable',
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'integer',
        ]);

        $userGroupIds = $user->channelGroups()->pluck('id')->all();
        $validGroupIds = array_values(array_intersect($validated['group_ids'], $userGroupIds));

        if ($validGroupIds === []) {
            throw ValidationException::withMessages(['group_ids' => 'Please select at least one of your groups.']);
        }

        if ($validated['mode'] === 'existing') {
            $channel = Channel::where('channel_id', $validated['channel_id'])->firstOrFail();
        } else {
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
        }

        $groups = ChannelGroup::whereIn('id', $validGroupIds)->get();
        foreach ($groups as $group) {
            $group->channels()->syncWithoutDetaching([$channel->id]);
        }

        return back();
    }

    public function updateGroups(Request $request, Channel $channel): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => 'integer',
        ]);

        $userGroupIds = $user->channelGroups()->pluck('id')->all();

        $isSubscribed = $channel->channelGroups()
            ->whereIn('channel_groups.id', $userGroupIds)
            ->exists();

        if (! $isSubscribed) {
            abort(404);
        }

        $newGroupIds = array_values(array_intersect($validated['group_ids'], $userGroupIds));

        if ($newGroupIds === []) {
            throw ValidationException::withMessages(['group_ids' => 'Please select at least one of your groups.']);
        }

        $groups = ChannelGroup::whereIn('id', $userGroupIds)->get();
        foreach ($groups as $group) {
            if (in_array($group->id, $newGroupIds)) {
                $group->channels()->syncWithoutDetaching([$channel->id]);
            } else {
                $group->channels()->detach($channel->id);
            }
        }

        return back();
    }

    public function toggleFavorite(Request $request, Channel $channel): RedirectResponse
    {
        $user = $request->user();

        $userGroupIds = $user->channelGroups()->pluck('id')->all();
        $isSubscribed = $channel->channelGroups()
            ->whereIn('channel_groups.id', $userGroupIds)
            ->exists();

        if (! $isSubscribed) {
            abort(404);
        }

        $validated = $request->validate([
            'is_favorite' => 'required|boolean',
        ]);

        if ($validated['is_favorite']) {
            $user->favoritedChannels()->syncWithoutDetaching([$channel->id]);
        } else {
            $user->favoritedChannels()->detach($channel->id);
        }

        return back();
    }

    public function destroy(Request $request, Channel $channel): RedirectResponse
    {
        $user = $request->user();

        $userGroupIds = $user->channelGroups()->pluck('id')->all();

        $channelGroupIds = $channel->channelGroups()
            ->whereIn('channel_groups.id', $userGroupIds)
            ->pluck('channel_groups.id')
            ->all();

        if ($channelGroupIds === []) {
            abort(404);
        }

        $groups = ChannelGroup::whereIn('id', $channelGroupIds)->get();
        foreach ($groups as $group) {
            $group->channels()->detach($channel->id);
        }

        return back();
    }
}
