<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDownIcon, ChevronUpIcon, StarIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import { computed, ref, useTemplateRef } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import groupRoutes from '@/routes/groups';
import subscriptions from '@/routes/subscriptions';

interface Group {
    id: number;
    name: string;
}

interface Channel {
    id: number;
    channel_id: string;
    name: string;
    last_fetched_at: string | null;
    is_favorite: boolean;
    group_ids: number[];
}

interface SearchResult {
    id: number;
    channel_id: string;
    name: string;
}

const props = defineProps<{
    channels: Channel[];
    groups: Group[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [],
    },
});

// --- Add form group selection ---
const addGroupIds = ref<number[]>([]);
const addGroupError = ref<string | null>(null);

const toggleAddGroup = (groupId: number) => {
    addGroupError.value = null;
    const idx = addGroupIds.value.indexOf(groupId);
    if (idx === -1) {
        addGroupIds.value.push(groupId);
    } else {
        addGroupIds.value.splice(idx, 1);
    }
};

// --- Search ---
const searchQuery = ref('');
const searchResults = ref<SearchResult[]>([]);
const searchLoading = ref(false);
const searchError = ref<string | null>(null);
const showDropdown = ref(false);
let searchTimer: ReturnType<typeof setTimeout> | null = null;

const onSearchInput = () => {
    searchError.value = null;
    if (searchTimer) clearTimeout(searchTimer);
    const q = searchQuery.value.trim();
    if (q.length < 2) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }
    searchTimer = setTimeout(async () => {
        searchLoading.value = true;
        try {
            const res = await fetch(`/channels/search?q=${encodeURIComponent(q)}`, {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error('Search failed');
            searchResults.value = await res.json();
            showDropdown.value = true;
        } catch {
            searchError.value = 'Search failed. Please try again.';
            showDropdown.value = false;
        } finally {
            searchLoading.value = false;
        }
    }, 250);
};

const attachExisting = (result: SearchResult) => {
    if (addGroupIds.value.length === 0) {
        addGroupError.value = 'Select at least one group before adding.';
        return;
    }
    showDropdown.value = false;
    searchQuery.value = '';
    searchResults.value = [];
    router.post(
        subscriptions.store().url,
        { mode: 'existing', channel_id: result.channel_id, group_ids: addGroupIds.value },
        { preserveScroll: true },
    );
};

const onSearchBlur = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 150);
};

// --- Manual ID fallback ---
const idFallbackOpen = ref(false);
const idForm = useForm({ mode: 'id' as const, value: '', group_ids: [] as number[] });

const submitIdForm = () => {
    if (addGroupIds.value.length === 0) {
        addGroupError.value = 'Select at least one group before adding.';
        return;
    }
    idForm.group_ids = addGroupIds.value;
    idForm.post(subscriptions.store().url, {
        preserveScroll: true,
        onSuccess: () => {
            idForm.reset('value');
            idFallbackOpen.value = false;
        },
    });
};

// --- Inline group pill toggle (per channel) ---
const togglingPill = ref<{ channelId: number; groupId: number } | null>(null);

const toggleChannelGroup = (channel: Channel, groupId: number) => {
    if (togglingPill.value !== null) return;
    // Prevent removing the last group — use Remove button for that
    if (channel.group_ids.includes(groupId) && channel.group_ids.length === 1) return;

    togglingPill.value = { channelId: channel.id, groupId };

    const newGroupIds = channel.group_ids.includes(groupId)
        ? channel.group_ids.filter((id) => id !== groupId)
        : [...channel.group_ids, groupId];

    router.patch(
        subscriptions.updateGroups(channel.id).url,
        { group_ids: newGroupIds },
        {
            preserveScroll: true,
            onFinish: () => {
                togglingPill.value = null;
            },
        },
    );
};

const isPillToggling = (channelId: number, groupId: number) =>
    togglingPill.value?.channelId === channelId && togglingPill.value?.groupId === groupId;

// --- Favorites ---
const togglingFavoriteId = ref<number | null>(null);

const toggleFavorite = (channel: Channel) => {
    if (togglingFavoriteId.value !== null) return;
    togglingFavoriteId.value = channel.id;
    router.patch(
        subscriptions.toggleFavorite(channel.id).url,
        { is_favorite: !channel.is_favorite },
        {
            preserveScroll: true,
            onFinish: () => {
                togglingFavoriteId.value = null;
            },
        },
    );
};

// --- Remove ---
const confirmRemoveChannel = ref<Channel | null>(null);

const startRemove = (channel: Channel) => {
    confirmRemoveChannel.value = channel;
};

const confirmRemove = () => {
    const channel = confirmRemoveChannel.value;
    if (!channel) return;
    confirmRemoveChannel.value = null;
    router.delete(subscriptions.destroy(channel.id).url, { preserveScroll: true });
};

const cancelRemove = () => {
    confirmRemoveChannel.value = null;
};

const groupName = (groupId: number) => props.groups.find((g) => g.id === groupId)?.name ?? 'Unknown';

// --- Sort mode ---
type SortMode = 'alpha' | 'by-group';
const sortMode = ref<SortMode>('by-group');

const sortedChannels = computed(() =>
    [...props.channels].sort((a, b) => a.name.localeCompare(b.name)),
);

const groupedChannels = computed(() => {
    const sortedGroups = [...props.groups].sort((a, b) => a.name.localeCompare(b.name));
    return sortedGroups.map((group) => ({
        group,
        channels: props.channels
            .filter((c) => c.group_ids.includes(group.id))
            .sort((a, b) => a.name.localeCompare(b.name)),
    }));
});

// --- Import/Export ---
const importFileInput = useTemplateRef<HTMLInputElement>('importFileInput');
const importFileError = ref<string | null>(null);

const openImportDialog = () => {
    importFileError.value = null;
    importFileInput.value?.click();
};

const onImportFile = (e: Event) => {
    const input = e.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    importFileError.value = null;
    const data = new FormData();
    data.append('file', file);
    router.post(groupRoutes.importAll().url, data, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            importFileError.value = (errors.file as string) ?? 'Import failed.';
        },
        onFinish: () => {
            input.value = '';
        },
    });
};
</script>

<template>
    <Head title="Subscriptions" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <Heading title="Subscriptions" description="All your subscribed channels across all groups." />
            <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
                <Button variant="outline" as-child>
                    <a :href="groupRoutes.exportAll().url" download>Export JSON</a>
                </Button>
                <input
                    ref="importFileInput"
                    type="file"
                    accept=".json,application/json,text/plain"
                    class="sr-only"
                    aria-label="Import channels JSON file"
                    @change="onImportFile"
                />
                <Button type="button" variant="outline" @click="openImportDialog">Import JSON</Button>
            </div>
        </div>
        <p v-if="importFileError" class="text-sm text-destructive">{{ importFileError }}</p>

        <!-- No groups empty state -->
        <div v-if="groups.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            <p class="mb-1 font-medium">No channel groups yet.</p>
            <p class="text-sm">
                <Link :href="groupRoutes.index().url" class="underline underline-offset-2">Create a group</Link>
                before adding subscriptions.
            </p>
        </div>

        <template v-else>
            <!-- Add channel form -->
            <div class="flex max-w-2xl flex-col gap-4 rounded-xl border p-4">
                <!-- Group pill-select for add form -->
                <div class="flex flex-col gap-2">
                    <Label>Add to groups</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="group in groups"
                            :key="group.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            :class="
                                addGroupIds.includes(group.id)
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background text-foreground hover:bg-accent'
                            "
                            @click="toggleAddGroup(group.id)"
                        >
                            {{ group.name }}
                        </button>
                    </div>
                    <p v-if="addGroupError" class="text-sm text-destructive">{{ addGroupError }}</p>
                </div>

                <!-- Search -->
                <div class="grid gap-2">
                    <Label for="sub-search-input">Search channel directory</Label>
                    <div class="relative">
                        <Input
                            id="sub-search-input"
                            v-model="searchQuery"
                            placeholder="Type a channel name…"
                            autocomplete="off"
                            @input="onSearchInput"
                            @blur="onSearchBlur"
                        />
                        <div
                            v-if="showDropdown && searchResults.length > 0"
                            class="absolute z-10 mt-1 w-full rounded-lg border bg-popover shadow-md"
                        >
                            <ul>
                                <li
                                    v-for="result in searchResults"
                                    :key="result.id"
                                    class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm hover:bg-accent"
                                    @mousedown.prevent="attachExisting(result)"
                                >
                                    <span class="truncate font-medium">{{ result.name }}</span>
                                    <span class="ml-2 shrink-0 font-mono text-xs text-muted-foreground">{{
                                        result.channel_id
                                    }}</span>
                                </li>
                            </ul>
                        </div>
                        <div
                            v-else-if="showDropdown && !searchLoading && searchQuery.trim().length >= 2"
                            class="absolute z-10 mt-1 w-full rounded-lg border bg-popover px-3 py-2 text-sm text-muted-foreground shadow-md"
                        >
                            No channels found.
                        </div>
                    </div>
                    <p v-if="searchError" class="text-sm text-destructive">{{ searchError }}</p>
                </div>

                <!-- Manual channel ID fallback -->
                <Collapsible v-model:open="idFallbackOpen">
                    <CollapsibleTrigger as-child>
                        <Button type="button" variant="ghost" size="sm" class="-mx-1 gap-1 text-muted-foreground">
                            Add by channel ID
                            <ChevronDownIcon v-if="!idFallbackOpen" class="size-4" />
                            <ChevronUpIcon v-else class="size-4" />
                        </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <form class="mt-3 flex flex-col gap-3" @submit.prevent="submitIdForm">
                            <div class="grid gap-2">
                                <p id="sub-channel-id-hint" class="text-xs text-muted-foreground">
                                    Looks like <code>UCxxxxxxxxxxxxxxxxxxxxxx</code>. Found in YouTube channel URL or
                                    page source.
                                </p>
                                <Input
                                    id="sub-channel-id-input"
                                    v-model="idForm.value"
                                    placeholder="UC..."
                                    autocomplete="off"
                                    aria-describedby="sub-channel-id-hint"
                                />
                                <p v-if="idForm.errors.value" class="text-sm text-destructive">{{
                                    idForm.errors.value
                                }}</p>
                                <p v-if="idForm.errors.group_ids" class="text-sm text-destructive">{{
                                    idForm.errors.group_ids
                                }}</p>
                            </div>
                            <Button
                                type="submit"
                                :disabled="idForm.processing || !idForm.value"
                                class="self-start"
                            >
                                Add channel
                            </Button>
                        </form>
                    </CollapsibleContent>
                </Collapsible>
            </div>

            <!-- Channel list -->
            <div>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-medium text-muted-foreground">
                        Subscriptions ({{ channels.length }})
                    </h3>
                    <div class="flex rounded-lg border p-0.5 text-sm">
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 font-medium transition-colors"
                            :class="sortMode === 'alpha' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                            @click="sortMode = 'alpha'"
                        >
                            A–Z
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 font-medium transition-colors"
                            :class="sortMode === 'by-group' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                            @click="sortMode = 'by-group'"
                        >
                            By Group
                        </button>
                    </div>
                </div>

                <div
                    v-if="channels.length === 0"
                    class="rounded-xl border border-dashed p-6 text-center text-muted-foreground"
                >
                    No subscriptions yet. Add a channel above.
                </div>

                <!-- Alphabetical view -->
                <ul v-else-if="sortMode === 'alpha'" class="divide-y rounded-xl border">
                    <li v-for="channel in sortedChannels" :key="channel.id" class="flex flex-col gap-2 p-3 sm:flex-row sm:items-start">
                        <div class="flex min-w-0 flex-1 gap-2">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                class="mt-0.5 shrink-0 text-muted-foreground hover:text-amber-500"
                                :disabled="togglingFavoriteId === channel.id"
                                :aria-pressed="channel.is_favorite"
                                :aria-label="channel.is_favorite ? 'Remove from favorites' : 'Mark as favorite'"
                                @click="toggleFavorite(channel)"
                            >
                                <StarIconSolid v-if="channel.is_favorite" class="size-5 text-amber-400" />
                                <StarIcon v-else class="size-5" />
                            </Button>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium">{{ channel.name }}</p>
                                <p class="mt-0.5 truncate font-mono text-xs text-muted-foreground">{{ channel.channel_id }}</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <button
                                        v-for="group in groups"
                                        :key="group.id"
                                        type="button"
                                        class="rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="channel.group_ids.includes(group.id) ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground'"
                                        :disabled="isPillToggling(channel.id, group.id) || (channel.group_ids.includes(group.id) && channel.group_ids.length === 1)"
                                        :title="channel.group_ids.includes(group.id) && channel.group_ids.length === 1 ? 'Use Remove to unsubscribe from all groups' : undefined"
                                        @click="toggleChannelGroup(channel, group.id)"
                                    >
                                        {{ group.name }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <Button variant="ghost" size="sm" class="shrink-0 self-start text-destructive sm:mt-0.5" @click="startRemove(channel)">
                            Remove
                        </Button>
                    </li>
                </ul>

                <!-- By-group view -->
                <div v-else class="flex flex-col gap-4">
                    <div v-for="{ group, channels: groupChannels } in groupedChannels" :key="group.id">
                        <h4
                            :id="`subscription-group-${group.id}`"
                            class="scroll-mt-6 mb-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                        >
                            {{ group.name }} ({{ groupChannels.length }})
                        </h4>
                        <ul v-if="groupChannels.length > 0" class="divide-y rounded-xl border">
                            <li v-for="channel in groupChannels" :key="channel.id" class="flex flex-col gap-2 p-3 sm:flex-row sm:items-start">
                                <div class="flex min-w-0 flex-1 gap-2">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon-sm"
                                        class="mt-0.5 shrink-0 text-muted-foreground hover:text-amber-500"
                                        :disabled="togglingFavoriteId === channel.id"
                                        :aria-pressed="channel.is_favorite"
                                        :aria-label="channel.is_favorite ? 'Remove from favorites' : 'Mark as favorite'"
                                        @click="toggleFavorite(channel)"
                                    >
                                        <StarIconSolid v-if="channel.is_favorite" class="size-5 text-amber-400" />
                                <StarIcon v-else class="size-5" />
                                    </Button>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium">{{ channel.name }}</p>
                                        <p class="mt-0.5 truncate font-mono text-xs text-muted-foreground">{{ channel.channel_id }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            <button
                                                v-for="g in groups"
                                                :key="g.id"
                                                type="button"
                                                class="rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                                :class="channel.group_ids.includes(g.id) ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground'"
                                                :disabled="isPillToggling(channel.id, g.id) || (channel.group_ids.includes(g.id) && channel.group_ids.length === 1)"
                                                :title="channel.group_ids.includes(g.id) && channel.group_ids.length === 1 ? 'Use Remove to unsubscribe from all groups' : undefined"
                                                @click="toggleChannelGroup(channel, g.id)"
                                            >
                                                {{ g.name }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <Button variant="ghost" size="sm" class="shrink-0 self-start text-destructive sm:mt-0.5" @click="startRemove(channel)">
                                    Remove
                                </Button>
                            </li>
                        </ul>
                        <p v-else class="rounded-xl border border-dashed p-4 text-center text-sm text-muted-foreground">
                            No channels in this group.
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Confirm remove dialog -->
    <Dialog :open="!!confirmRemoveChannel" @update:open="(v) => { if (!v) cancelRemove() }">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Remove subscription?</DialogTitle>
                <DialogDescription as="div">
                    <p>
                        <span class="font-medium text-foreground">{{ confirmRemoveChannel?.name }}</span> will be
                        removed from {{ confirmRemoveChannel && confirmRemoveChannel.group_ids.length === 1 ? 'this group' : 'all groups' }}:
                    </p>
                    <ul class="mt-2 list-disc pl-5 text-sm">
                        <li v-for="gid in confirmRemoveChannel?.group_ids" :key="gid">
                            {{ groupName(gid) }}
                        </li>
                    </ul>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="cancelRemove">Cancel</Button>
                <Button variant="destructive" @click="confirmRemove">Remove</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
