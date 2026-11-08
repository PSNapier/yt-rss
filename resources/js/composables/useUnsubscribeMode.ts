// Unsubscribe-select mode shared by the All Videos, group, and Shorts feeds.
// In the mode, clicking a card selects its channel instead of opening the
// video. The selection lives in the page component, so leaving the page or
// turning the mode off drops it.

import { router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { forgetFeedCache } from '@/composables/useFeedCache';
import subscriptions from '@/routes/subscriptions';

export interface UnsubscribeChannel {
    id: number;
    name: string;
}

export interface ChannelGroupRef {
    id: number;
    name: string;
}

/** Channel ID => the user's groups that contain it. */
export type ChannelGroupMap = Record<string, ChannelGroupRef[]>;

export type UnsubscribeScope = 'everywhere' | 'group';

export type UnsubscribeItem = {
    channel_id: number;
    scope: UnsubscribeScope;
    group_id?: number;
};

export function useUnsubscribeMode(options: {
    /** The group feed's ID. Null on All Videos and Shorts. */
    groupId?: () => number | null;
    channelGroups: () => ChannelGroupMap | undefined;
    /** Called after a successful request with the channels removed from this feed. */
    onRemoved: (channelIds: number[]) => void;
}) {
    const active = ref(false);
    const selected = reactive(new Map<number, UnsubscribeChannel>());
    const dialogOpen = ref(false);
    const processing = ref(false);

    const selectedChannels = computed(() => [...selected.values()]);
    const count = computed(() => selected.size);

    const clear = () => {
        selected.clear();
        dialogOpen.value = false;
    };

    const toggleMode = () => {
        active.value = !active.value;

        if (!active.value) {
            clear();
        }
    };

    const exit = () => {
        active.value = false;
        clear();
    };

    const isSelected = (channelId: number) => selected.has(channelId);

    const toggleChannel = (channel: UnsubscribeChannel) => {
        if (selected.has(channel.id)) {
            selected.delete(channel.id);
        } else {
            selected.set(channel.id, { id: channel.id, name: channel.name });
        }
    };

    const deselect = (channelId: number) => {
        selected.delete(channelId);
    };

    const openDialog = () => {
        if (selected.size > 0) {
            dialogOpen.value = true;
        }
    };

    // Feeds whose cached state goes stale: All Videos and Shorts always, plus
    // every group that loses at least one channel.
    const affectedCacheKeys = (items: UnsubscribeItem[]): string[] => {
        const keys = new Set(['all', 'shorts']);
        const map = options.channelGroups() ?? {};

        for (const item of items) {
            if (item.scope === 'group' && item.group_id !== undefined) {
                keys.add(`group:${item.group_id}`);
            } else {
                for (const group of map[item.channel_id] ?? []) {
                    keys.add(`group:${group.id}`);
                }
            }
        }

        return [...keys];
    };

    const confirm = (items: UnsubscribeItem[]) => {
        if (items.length === 0 || processing.value) {
            return;
        }

        router.delete(subscriptions.destroyMany().url, {
            data: { items },
            preserveScroll: true,
            preserveState: true,
            onStart: () => (processing.value = true),
            onFinish: () => (processing.value = false),
            onSuccess: () => {
                forgetFeedCache(affectedCacheKeys(items));
                // Every item leaves the current feed: "everywhere" removes it
                // from all feeds, and "group" scope only exists on this group.
                options.onRemoved(items.map((i) => i.channel_id));
                exit();
            },
        });
    };

    // Inertia reuses Groups/Show across groups, so switching group is leaving
    // the page.
    if (options.groupId) {
        watch(options.groupId, exit);
    }

    return {
        active,
        count,
        selectedChannels,
        dialogOpen,
        processing,
        toggleMode,
        exit,
        clear,
        isSelected,
        toggleChannel,
        deselect,
        openDialog,
        confirm,
    };
}
