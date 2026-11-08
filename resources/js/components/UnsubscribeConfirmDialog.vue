<script setup lang="ts">
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { computed, reactive, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type {
    ChannelGroupMap,
    ChannelGroupRef,
    UnsubscribeChannel,
    UnsubscribeItem,
    UnsubscribeScope,
} from '@/composables/useUnsubscribeMode';

const props = defineProps<{
    open: boolean;
    channels: UnsubscribeChannel[];
    channelGroups?: ChannelGroupMap;
    /** The current group feed. Null hides the per-row scope control. */
    group?: ChannelGroupRef | null;
    processing?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [open: boolean];
    deselect: [channelId: number];
    confirm: [items: UnsubscribeItem[]];
}>();

// Per-row scope. Rows not in the map use "everywhere".
const scopes = reactive<Record<number, UnsubscribeScope>>({});

watch(
    () => props.open,
    (open) => {
        if (open) {
            for (const key of Object.keys(scopes)) {
                delete scopes[Number(key)];
            }
        }
    },
);

const groupsOf = (channelId: number): ChannelGroupRef[] =>
    props.channelGroups?.[channelId] ?? [];

const scopeOf = (channelId: number): UnsubscribeScope =>
    props.group ? (scopes[channelId] ?? 'everywhere') : 'everywhere';

const items = computed<UnsubscribeItem[]>(() =>
    props.channels.map((channel) =>
        scopeOf(channel.id) === 'group' && props.group
            ? {
                  channel_id: channel.id,
                  scope: 'group',
                  group_id: props.group.id,
              }
            : { channel_id: channel.id, scope: 'everywhere' },
    ),
);

// Each group that loses at least one channel, in name order.
const affectedFeeds = computed(() => {
    const byId = new Map<number, string>();

    for (const item of items.value) {
        const groups =
            item.scope === 'group' && props.group
                ? [props.group]
                : groupsOf(item.channel_id);

        for (const group of groups) {
            byId.set(group.id, group.name);
        }
    }

    return [...byId.values()].sort((a, b) => a.localeCompare(b));
});

const onConfirm = () => {
    if (items.value.length > 0 && !props.processing) {
        emit('confirm', items.value);
    }
};
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Remove subscriptions</DialogTitle>
                <DialogDescription>
                    {{ channels.length }} channel{{
                        channels.length === 1 ? '' : 's'
                    }}
                    selected. Their videos leave the affected feeds.
                </DialogDescription>
            </DialogHeader>

            <p
                v-if="channels.length === 0"
                class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground"
            >
                No channels left in this batch.
            </p>

            <ul
                v-else
                class="-mx-1 grid max-h-[50vh] gap-1 overflow-y-auto px-1"
            >
                <li
                    v-for="channel in channels"
                    :key="channel.id"
                    class="flex items-center gap-3 rounded-lg border p-2.5"
                    data-test="unsubscribe-row"
                >
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                        style="background: var(--cherry)"
                        aria-hidden="true"
                    >
                        {{ channel.name[0] }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">
                            {{ channel.name }}
                        </p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{
                                groupsOf(channel.id)
                                    .map((g) => g.name)
                                    .join(', ') || 'No groups'
                            }}
                        </p>
                    </div>

                    <div
                        v-if="group"
                        class="flex shrink-0 overflow-hidden rounded-md border text-xs"
                        role="group"
                        :aria-label="`Remove ${channel.name} from`"
                    >
                        <button
                            type="button"
                            class="px-2 py-1 transition-colors"
                            :class="
                                scopeOf(channel.id) === 'everywhere'
                                    ? 'bg-cherry text-white'
                                    : 'hover:bg-muted'
                            "
                            :aria-pressed="scopeOf(channel.id) === 'everywhere'"
                            @click="scopes[channel.id] = 'everywhere'"
                        >
                            Everywhere
                        </button>
                        <button
                            type="button"
                            class="border-l px-2 py-1 transition-colors"
                            :class="
                                scopeOf(channel.id) === 'group'
                                    ? 'bg-cherry text-white'
                                    : 'hover:bg-muted'
                            "
                            :aria-pressed="scopeOf(channel.id) === 'group'"
                            @click="scopes[channel.id] = 'group'"
                        >
                            This group only
                        </button>
                    </div>

                    <button
                        type="button"
                        class="shrink-0 rounded-md p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                        :aria-label="`Keep ${channel.name}`"
                        :title="`Keep ${channel.name}`"
                        @click="emit('deselect', channel.id)"
                    >
                        <XMarkIcon class="size-4" />
                    </button>
                </li>
            </ul>

            <div
                v-if="affectedFeeds.length > 0"
                class="text-sm text-muted-foreground"
            >
                <span class="font-medium text-foreground">Affected feeds:</span>
                {{ affectedFeeds.join(', ') }}
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="ghost"
                    @click="emit('update:open', false)"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="channels.length === 0 || processing"
                    @click="onConfirm"
                >
                    {{ processing ? 'Removing…' : 'Remove subscriptions' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
