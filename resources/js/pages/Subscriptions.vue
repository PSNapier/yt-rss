<script setup lang="ts">
import { EyeIcon, EyeSlashIcon, StarIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, useTemplateRef } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
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
import { clearFeedCache } from '@/composables/useFeedCache';
import groupRoutes from '@/routes/groups';
import shorts from '@/routes/shorts';
import subscriptions from '@/routes/subscriptions';

interface Group {
    id: number;
    name: string;
    channels_count: number;
    hidden: boolean;
}

interface Channel {
    id: number;
    channel_id: string;
    name: string;
    last_fetched_at: string | null;
    is_favorite: boolean;
    unwatched_cap: number | null;
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

// --- Group management ---
const createGroupForm = useForm({ name: '' });

const createGroup = () => {
    createGroupForm.post(groupRoutes.store().url, {
        preserveScroll: true,
        onSuccess: () => createGroupForm.reset('name'),
    });
};

const renamingGroupId = ref<number | null>(null);
const renameGroupForm = useForm({ name: '' });

const startRename = (group: Group) => {
    renameGroupForm.clearErrors();
    renameGroupForm.name = group.name;
    renamingGroupId.value = group.id;
};

const cancelRename = () => {
    renamingGroupId.value = null;
    renameGroupForm.clearErrors();
};

const submitRename = (group: Group) => {
    if (renameGroupForm.name.trim() === '' || renameGroupForm.name === group.name) {
        cancelRename();

        return;
    }

    renameGroupForm.patch(groupRoutes.update(group.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            renamingGroupId.value = null;
        },
    });
};

const confirmDeleteGroup = ref<Group | null>(null);

const confirmDelete = () => {
    const group = confirmDeleteGroup.value;

    if (!group) {
        return;
    }

    confirmDeleteGroup.value = null;
    addGroupIds.value = addGroupIds.value.filter((id) => id !== group.id);
    router.delete(groupRoutes.destroy(group.id).url, {
        preserveScroll: true,
    });
};

// --- Hide groups and the Shorts page ---
// Hiding changes what All Videos and Shorts return, so cached feed state goes.
const togglingHiddenGroupId = ref<number | null>(null);

const toggleGroupHidden = (group: Group) => {
    togglingHiddenGroupId.value = group.id;
    router.patch(
        groupRoutes.update(group.id).url,
        { hidden: !group.hidden },
        {
            preserveScroll: true,
            onSuccess: () => clearFeedCache(),
            onFinish: () => {
                togglingHiddenGroupId.value = null;
            },
        },
    );
};

const page = usePage();
const shortsHidden = computed(() => page.props.shortsHidden);
const togglingShorts = ref(false);

const toggleShortsHidden = () => {
    togglingShorts.value = true;
    router.patch(
        shorts.visibility().url,
        { hidden: !shortsHidden.value },
        {
            preserveScroll: true,
            onSuccess: () => clearFeedCache(),
            onFinish: () => {
                togglingShorts.value = false;
            },
        },
    );
};

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

    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    const q = searchQuery.value.trim();

    if (q.length < 2) {
        searchResults.value = [];
        showDropdown.value = false;

        return;
    }

    searchTimer = setTimeout(async () => {
        searchLoading.value = true;

        try {
            const res = await fetch(
                `/channels/search?q=${encodeURIComponent(q)}`,
                {
                    headers: { Accept: 'application/json' },
                },
            );

            if (!res.ok) {
                throw new Error('Search failed');
            }

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
        {
            mode: 'existing',
            channel_id: result.channel_id,
            group_ids: addGroupIds.value,
        },
        { preserveScroll: true },
    );
};

const onSearchBlur = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 150);
};

// --- Add by URL, @handle, or channel ID ---
const idForm = useForm({
    mode: 'auto' as const,
    value: '',
    group_ids: [] as number[],
});

const submitIdForm = () => {
    if (addGroupIds.value.length === 0) {
        addGroupError.value = 'Select at least one group before adding.';

        return;
    }

    idForm.group_ids = addGroupIds.value;
    idForm.post(subscriptions.store().url, {
        preserveScroll: true,
        onSuccess: () => idForm.reset('value'),
    });
};

// --- Inline group pill toggle (per channel) ---
const togglingPill = ref<{ channelId: number; groupId: number } | null>(null);

const toggleChannelGroup = (channel: Channel, groupId: number) => {
    if (togglingPill.value !== null) {
        return;
    }

    // Prevent removing the last group — use Remove button for that
    if (channel.group_ids.includes(groupId) && channel.group_ids.length === 1) {
        return;
    }

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
    togglingPill.value?.channelId === channelId &&
    togglingPill.value?.groupId === groupId;

// --- Favorites ---
const togglingFavoriteId = ref<number | null>(null);

const toggleFavorite = (channel: Channel) => {
    if (togglingFavoriteId.value !== null) {
        return;
    }

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

// --- Per-channel unwatched cap ---
// value null = default cap, 0 = unlimited, N = that many unwatched.
const CAP_OPTIONS: { label: string; value: number | null }[] = [
    { label: 'Default', value: null },
    { label: '1', value: 1 },
    { label: '2', value: 2 },
    { label: '3', value: 3 },
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: 'Unlimited', value: 0 },
];

const capSelectValue = (cap: number | null) =>
    cap === null ? '' : String(cap);

const updatingCapId = ref<number | null>(null);

const updateCap = (channel: Channel, raw: string) => {
    const value = raw === '' ? null : Number(raw);
    updatingCapId.value = channel.id;
    router.patch(
        subscriptions.updateCap(channel.id).url,
        { cap: value },
        {
            preserveScroll: true,
            // Feed output depends on caps; drop cached feed state so the next
            // feed render reflects the change.
            onSuccess: () => clearFeedCache(),
            onFinish: () => {
                updatingCapId.value = null;
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

    if (!channel) {
        return;
    }

    confirmRemoveChannel.value = null;
    router.delete(subscriptions.destroy(channel.id).url, {
        preserveScroll: true,
    });
};

const cancelRemove = () => {
    confirmRemoveChannel.value = null;
};

const groupName = (groupId: number) =>
    props.groups.find((g) => g.id === groupId)?.name ?? 'Unknown';

// --- Sort mode ---
type SortMode = 'alpha' | 'by-group';
const sortMode = ref<SortMode>('by-group');

const sortedChannels = computed(() =>
    [...props.channels].sort((a, b) => a.name.localeCompare(b.name)),
);

const groupedChannels = computed(() => {
    const sortedGroups = [...props.groups].sort((a, b) =>
        a.name.localeCompare(b.name),
    );

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

    if (!file) {
        return;
    }

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
        <Heading
            title="Subscriptions"
            description="Your channel groups and every channel in them."
        />

        <!-- Group management -->
        <section
            id="group-management"
            class="flex max-w-2xl scroll-mt-6 flex-col gap-4 rounded-xl border p-4"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <h2 class="text-base font-semibold">Groups</h2>
                <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
                    <Button variant="outline" size="sm" as-child>
                        <a :href="groupRoutes.exportAll().url" download
                            >Export JSON</a
                        >
                    </Button>
                    <input
                        ref="importFileInput"
                        type="file"
                        accept=".json,application/json,text/plain"
                        class="sr-only"
                        aria-label="Import all groups JSON file"
                        @change="onImportFile"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="openImportDialog"
                        >Import JSON</Button
                    >
                </div>
            </div>
            <p v-if="importFileError" class="text-sm text-destructive">
                {{ importFileError }}
            </p>

            <form class="grid gap-2" @submit.prevent="createGroup">
                <Label for="new-group-input">New group</Label>
                <div class="flex gap-2">
                    <Input
                        id="new-group-input"
                        v-model="createGroupForm.name"
                        placeholder="Group name"
                        autocomplete="off"
                    />
                    <Button
                        type="submit"
                        class="shrink-0"
                        :disabled="
                            createGroupForm.processing || !createGroupForm.name
                        "
                        >Create group</Button
                    >
                </div>
                <p
                    v-if="createGroupForm.errors.name"
                    class="text-sm text-destructive"
                >
                    {{ createGroupForm.errors.name }}
                </p>
            </form>

            <p
                v-if="groups.length === 0"
                class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground"
            >
                No channel groups yet. Create one above, then add channels to
                it.
            </p>

            <ul v-else class="divide-y rounded-xl border">
                <li
                    v-for="group in groups"
                    :key="group.id"
                    class="flex flex-col gap-1 p-3"
                >
                    <form
                        v-if="renamingGroupId === group.id"
                        class="flex gap-2"
                        @submit.prevent="submitRename(group)"
                        @keydown.esc="cancelRename"
                    >
                        <Input
                            v-model="renameGroupForm.name"
                            :aria-label="`Rename ${group.name}`"
                            autocomplete="off"
                            autofocus
                        />
                        <Button
                            type="submit"
                            size="sm"
                            class="shrink-0 self-center"
                            :disabled="renameGroupForm.processing"
                            >Save</Button
                        >
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="shrink-0 self-center"
                            @click="cancelRename"
                            >Cancel</Button
                        >
                    </form>
                    <div v-else class="flex items-center gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            class="shrink-0 text-muted-foreground"
                            :disabled="togglingHiddenGroupId === group.id"
                            :aria-pressed="group.hidden"
                            :aria-label="
                                group.hidden
                                    ? `Show ${group.name}`
                                    : `Hide ${group.name}`
                            "
                            :title="group.hidden ? 'Hidden' : 'Visible'"
                            @click="toggleGroupHidden(group)"
                        >
                            <EyeSlashIcon v-if="group.hidden" class="size-5" />
                            <EyeIcon v-else class="size-5" />
                        </Button>
                        <div
                            class="min-w-0 flex-1 truncate"
                            :class="{ 'opacity-50': group.hidden }"
                        >
                            <span v-if="group.hidden" class="font-medium">{{
                                group.name
                            }}</span>
                            <Link
                                v-else
                                :href="groupRoutes.show(group.id).url"
                                class="font-medium hover:underline"
                            >
                                {{ group.name }}
                            </Link>
                            <span class="ml-2 text-xs text-muted-foreground"
                                >{{ group.channels_count }} channel{{
                                    group.channels_count === 1 ? '' : 's'
                                }}</span
                            >
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="startRename(group)"
                            >Rename</Button
                        >
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            class="text-destructive"
                            @click="confirmDeleteGroup = group"
                            >Delete</Button
                        >
                    </div>
                    <p
                        v-if="
                            renamingGroupId === group.id &&
                            renameGroupForm.errors.name
                        "
                        class="text-sm text-destructive"
                    >
                        {{ renameGroupForm.errors.name }}
                    </p>
                </li>
            </ul>

            <!-- Shorts page visibility -->
            <div class="flex items-center gap-2 rounded-xl border p-3">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="shrink-0 text-muted-foreground"
                    :disabled="togglingShorts"
                    :aria-pressed="shortsHidden"
                    :aria-label="
                        shortsHidden ? 'Show Shorts page' : 'Hide Shorts page'
                    "
                    :title="shortsHidden ? 'Hidden' : 'Visible'"
                    @click="toggleShortsHidden"
                >
                    <EyeSlashIcon v-if="shortsHidden" class="size-5" />
                    <EyeIcon v-else class="size-5" />
                </Button>
                <div
                    class="min-w-0 flex-1"
                    :class="{ 'opacity-50': shortsHidden }"
                >
                    <span class="font-medium">Shorts page</span>
                    <span class="ml-2 text-xs text-muted-foreground">{{
                        shortsHidden ? 'Hidden from the sidebar' : 'In the sidebar'
                    }}</span>
                </div>
            </div>
        </section>

        <template v-if="groups.length > 0">
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
                            class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            :class="[
                                addGroupIds.includes(group.id)
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background text-foreground hover:bg-accent',
                                { 'opacity-50': group.hidden },
                            ]"
                            @click="toggleAddGroup(group.id)"
                        >
                            <EyeSlashIcon
                                v-if="group.hidden"
                                class="size-3.5"
                                aria-label="Hidden"
                            />
                            {{ group.name }}
                        </button>
                    </div>
                    <p v-if="addGroupError" class="text-sm text-destructive">
                        {{ addGroupError }}
                    </p>
                </div>

                <!-- Search -->
                <div class="grid gap-2">
                    <Label for="sub-search-input"
                        >Search channel directory</Label
                    >
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
                                    <span class="truncate font-medium">{{
                                        result.name
                                    }}</span>
                                    <span
                                        class="ml-2 shrink-0 font-mono text-xs text-muted-foreground"
                                        >{{ result.channel_id }}</span
                                    >
                                </li>
                            </ul>
                        </div>
                        <div
                            v-else-if="
                                showDropdown &&
                                !searchLoading &&
                                searchQuery.trim().length >= 2
                            "
                            class="absolute z-10 mt-1 w-full rounded-lg border bg-popover px-3 py-2 text-sm text-muted-foreground shadow-md"
                        >
                            No channels found.
                        </div>
                    </div>
                    <p v-if="searchError" class="text-sm text-destructive">
                        {{ searchError }}
                    </p>
                </div>

                <!-- Add by URL, @handle, or channel ID -->
                <form
                    class="grid gap-2"
                    @submit.prevent="submitIdForm"
                >
                    <Label for="sub-channel-id-input"
                        >Add by URL, @handle, or channel ID</Label
                    >
                    <p
                        id="sub-channel-id-hint"
                        class="text-xs text-muted-foreground"
                    >
                        Paste a channel URL, an
                        <code>@handle</code>, or a
                        <code>UCxxxxxxxxxxxxxxxxxxxxxx</code> id. Custom
                        <code>/c/</code> URLs are not supported — use the
                        @handle.
                    </p>
                    <div class="flex gap-2">
                        <Input
                            id="sub-channel-id-input"
                            v-model="idForm.value"
                            placeholder="https://youtube.com/@handle"
                            autocomplete="off"
                            aria-describedby="sub-channel-id-hint"
                        />
                        <Button
                            type="submit"
                            :disabled="idForm.processing || !idForm.value"
                            class="shrink-0"
                        >
                            Add channel
                        </Button>
                    </div>
                    <p
                        v-if="idForm.errors.value"
                        class="text-sm text-destructive"
                    >
                        {{ idForm.errors.value }}
                    </p>
                    <p
                        v-if="idForm.errors.group_ids"
                        class="text-sm text-destructive"
                    >
                        {{ idForm.errors.group_ids }}
                    </p>
                </form>
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
                            :class="
                                sortMode === 'alpha'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="sortMode = 'alpha'"
                        >
                            A–Z
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 font-medium transition-colors"
                            :class="
                                sortMode === 'by-group'
                                    ? 'bg-primary text-primary-foreground'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
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
                <ul
                    v-else-if="sortMode === 'alpha'"
                    class="divide-y rounded-xl border"
                >
                    <li
                        v-for="channel in sortedChannels"
                        :key="channel.id"
                        class="flex flex-col gap-2 p-3 sm:flex-row sm:items-start"
                    >
                        <div class="flex min-w-0 flex-1 gap-2">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                class="mt-0.5 shrink-0 text-muted-foreground hover:text-amber-500"
                                :disabled="togglingFavoriteId === channel.id"
                                :aria-pressed="channel.is_favorite"
                                :aria-label="
                                    channel.is_favorite
                                        ? 'Remove from favorites'
                                        : 'Mark as favorite'
                                "
                                @click="toggleFavorite(channel)"
                            >
                                <StarIconSolid
                                    v-if="channel.is_favorite"
                                    class="size-5 text-amber-400"
                                />
                                <StarIcon v-else class="size-5" />
                            </Button>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium">
                                    {{ channel.name }}
                                </p>
                                <p
                                    class="mt-0.5 truncate font-mono text-xs text-muted-foreground"
                                >
                                    {{ channel.channel_id }}
                                </p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <button
                                        v-for="group in groups"
                                        :key="group.id"
                                        type="button"
                                        class="rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            channel.group_ids.includes(group.id)
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground'
                                        "
                                        :disabled="
                                            isPillToggling(
                                                channel.id,
                                                group.id,
                                            ) ||
                                            (channel.group_ids.includes(
                                                group.id,
                                            ) &&
                                                channel.group_ids.length === 1)
                                        "
                                        :title="
                                            channel.group_ids.includes(
                                                group.id,
                                            ) && channel.group_ids.length === 1
                                                ? 'Use Remove to unsubscribe from all groups'
                                                : undefined
                                        "
                                        @click="
                                            toggleChannelGroup(
                                                channel,
                                                group.id,
                                            )
                                        "
                                    >
                                        {{ group.name }}
                                    </button>
                                </div>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <label
                                        :for="`cap-alpha-${channel.id}`"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Unwatched cap
                                    </label>
                                    <select
                                        :id="`cap-alpha-${channel.id}`"
                                        class="rounded-md border border-border bg-background px-2 py-0.5 text-xs text-foreground disabled:opacity-50"
                                        :disabled="updatingCapId === channel.id"
                                        :value="
                                            capSelectValue(
                                                channel.unwatched_cap,
                                            )
                                        "
                                        @change="
                                            updateCap(
                                                channel,
                                                (
                                                    $event.target as HTMLSelectElement
                                                ).value,
                                            )
                                        "
                                    >
                                        <option
                                            v-for="opt in CAP_OPTIONS"
                                            :key="opt.label"
                                            :value="capSelectValue(opt.value)"
                                        >
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="shrink-0 self-start text-destructive sm:mt-0.5"
                            @click="startRemove(channel)"
                        >
                            Remove
                        </Button>
                    </li>
                </ul>

                <!-- By-group view -->
                <div v-else class="flex flex-col gap-4">
                    <div
                        v-for="{
                            group,
                            channels: groupChannels,
                        } in groupedChannels"
                        :key="group.id"
                    >
                        <h4
                            :id="`subscription-group-${group.id}`"
                            class="mb-1.5 flex scroll-mt-6 items-center gap-1 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                            :class="{ 'opacity-50': group.hidden }"
                        >
                            <EyeSlashIcon
                                v-if="group.hidden"
                                class="size-3.5"
                                aria-label="Hidden"
                            />
                            {{ group.name }} ({{ groupChannels.length }})
                        </h4>
                        <ul
                            v-if="groupChannels.length > 0"
                            class="divide-y rounded-xl border"
                        >
                            <li
                                v-for="channel in groupChannels"
                                :key="channel.id"
                                class="flex flex-col gap-2 p-3 sm:flex-row sm:items-start"
                            >
                                <div class="flex min-w-0 flex-1 gap-2">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon-sm"
                                        class="mt-0.5 shrink-0 text-muted-foreground hover:text-amber-500"
                                        :disabled="
                                            togglingFavoriteId === channel.id
                                        "
                                        :aria-pressed="channel.is_favorite"
                                        :aria-label="
                                            channel.is_favorite
                                                ? 'Remove from favorites'
                                                : 'Mark as favorite'
                                        "
                                        @click="toggleFavorite(channel)"
                                    >
                                        <StarIconSolid
                                            v-if="channel.is_favorite"
                                            class="size-5 text-amber-400"
                                        />
                                        <StarIcon v-else class="size-5" />
                                    </Button>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-medium">
                                            {{ channel.name }}
                                        </p>
                                        <p
                                            class="mt-0.5 truncate font-mono text-xs text-muted-foreground"
                                        >
                                            {{ channel.channel_id }}
                                        </p>
                                        <div
                                            class="mt-2 flex flex-wrap gap-1.5"
                                        >
                                            <button
                                                v-for="g in groups"
                                                :key="g.id"
                                                type="button"
                                                class="rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                                :class="
                                                    channel.group_ids.includes(
                                                        g.id,
                                                    )
                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                        : 'border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground'
                                                "
                                                :disabled="
                                                    isPillToggling(
                                                        channel.id,
                                                        g.id,
                                                    ) ||
                                                    (channel.group_ids.includes(
                                                        g.id,
                                                    ) &&
                                                        channel.group_ids
                                                            .length === 1)
                                                "
                                                :title="
                                                    channel.group_ids.includes(
                                                        g.id,
                                                    ) &&
                                                    channel.group_ids.length ===
                                                        1
                                                        ? 'Use Remove to unsubscribe from all groups'
                                                        : undefined
                                                "
                                                @click="
                                                    toggleChannelGroup(
                                                        channel,
                                                        g.id,
                                                    )
                                                "
                                            >
                                                {{ g.name }}
                                            </button>
                                        </div>
                                        <div
                                            class="mt-2 flex items-center gap-1.5"
                                        >
                                            <label
                                                :for="`cap-group-${group.id}-${channel.id}`"
                                                class="text-xs text-muted-foreground"
                                            >
                                                Unwatched cap
                                            </label>
                                            <select
                                                :id="`cap-group-${group.id}-${channel.id}`"
                                                class="rounded-md border border-border bg-background px-2 py-0.5 text-xs text-foreground disabled:opacity-50"
                                                :disabled="
                                                    updatingCapId === channel.id
                                                "
                                                :value="
                                                    capSelectValue(
                                                        channel.unwatched_cap,
                                                    )
                                                "
                                                @change="
                                                    updateCap(
                                                        channel,
                                                        (
                                                            $event.target as HTMLSelectElement
                                                        ).value,
                                                    )
                                                "
                                            >
                                                <option
                                                    v-for="opt in CAP_OPTIONS"
                                                    :key="opt.label"
                                                    :value="
                                                        capSelectValue(
                                                            opt.value,
                                                        )
                                                    "
                                                >
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="shrink-0 self-start text-destructive sm:mt-0.5"
                                    @click="startRemove(channel)"
                                >
                                    Remove
                                </Button>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="rounded-xl border border-dashed p-4 text-center text-sm text-muted-foreground"
                        >
                            No channels in this group.
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Confirm delete group dialog -->
    <Dialog
        :open="!!confirmDeleteGroup"
        @update:open="
            (v) => {
                if (!v) confirmDeleteGroup = null;
            }
        "
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Delete group?</DialogTitle>
                <DialogDescription>
                    <span class="font-medium text-foreground">{{
                        confirmDeleteGroup?.name
                    }}</span>
                    will be deleted. Its channels stay subscribed through any
                    other groups they belong to.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="confirmDeleteGroup = null"
                    >Cancel</Button
                >
                <Button variant="destructive" @click="confirmDelete"
                    >Delete</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Confirm remove dialog -->
    <Dialog
        :open="!!confirmRemoveChannel"
        @update:open="
            (v) => {
                if (!v) cancelRemove();
            }
        "
    >
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Remove subscription?</DialogTitle>
                <DialogDescription as="div">
                    <p>
                        <span class="font-medium text-foreground">{{
                            confirmRemoveChannel?.name
                        }}</span>
                        will be removed from
                        {{
                            confirmRemoveChannel &&
                            confirmRemoveChannel.group_ids.length === 1
                                ? 'this group'
                                : 'all groups'
                        }}:
                    </p>
                    <ul class="mt-2 list-disc pl-5 text-sm">
                        <li
                            v-for="gid in confirmRemoveChannel?.group_ids"
                            :key="gid"
                        >
                            {{ groupName(gid) }}
                        </li>
                    </ul>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="cancelRemove">Cancel</Button>
                <Button variant="destructive" @click="confirmRemove"
                    >Remove</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
