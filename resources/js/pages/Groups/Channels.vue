<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { IconChevronDown, IconChevronUp, IconStar, IconStarFilled } from '@tabler/icons-vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { ref, useTemplateRef } from 'vue';
import GroupPagesNav from '@/components/GroupPagesNav.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import groups from '@/routes/groups';

interface Channel {
    id: number;
    channel_id: string;
    name: string;
    last_fetched_at: string | null;
    is_favorite: boolean;
}

interface SearchResult {
    id: number;
    channel_id: string;
    name: string;
}

const props = defineProps<{
    group: { id: number; name: string };
    channels: Channel[];
}>();

defineOptions({});

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
    showDropdown.value = false;
    searchQuery.value = '';
    searchResults.value = [];
    router.post(
        groups.channels.store(props.group.id).url,
        { mode: 'existing', channel_id: result.channel_id },
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
const idForm = useForm({ mode: 'id' as const, value: '' });

const submitId = () => {
    idForm.post(groups.channels.store(props.group.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            idForm.reset('value');
            idFallbackOpen.value = false;
        },
    });
};

// --- Remove ---
const remove = (channel: Channel) => {
    if (!window.confirm(`Remove ${channel.name} from this group?`)) {
        return;
    }
    router.delete(groups.channels.destroy([props.group.id, channel.id]).url, { preserveScroll: true });
};

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
    router.post(groups.channels.import(props.group.id).url, data, {
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

// --- Favorites ---
const togglingFavoriteId = ref<number | null>(null);

const toggleFavorite = (channel: Channel) => {
    if (togglingFavoriteId.value !== null) return;
    togglingFavoriteId.value = channel.id;
    router.patch(
        groups.channels.update([props.group.id, channel.id]).url,
        { is_favorite: !channel.is_favorite },
        {
            preserveScroll: true,
            onFinish: () => {
                togglingFavoriteId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head :title="`Channels - ${group.name}`" />

    <div class="flex h-full flex-1 flex-col">
        <!-- Inline page header: sidebar toggle + tab nav -->
        <div class="flex h-12 shrink-0 items-center gap-3 px-4 transition-[height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-10">
            <SidebarTrigger class="-ml-1" />
            <GroupPagesNav current="channels" :group-id="group.id" />
        </div>

        <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <Heading
                :title="`${group.name} channels`"
                description="Search the channel directory, or paste a channel ID if it's not yet listed."
            />
            <div class="flex flex-wrap items-center gap-2 sm:shrink-0">
                <Button variant="outline" as-child>
                    <a :href="groups.channels.export(group.id).url" download>Export JSON</a>
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

        <div class="flex max-w-xl flex-col gap-3 rounded-xl border p-4">
            <!-- Search box -->
            <div class="grid gap-2">
                <Label for="channel-search-input">Search channel directory</Label>
                <div class="relative">
                    <Input
                        id="channel-search-input"
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
                                <span class="ml-2 shrink-0 font-mono text-xs text-muted-foreground">{{ result.channel_id }}</span>
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

            <!-- Collapsible fallback -->
            <Collapsible v-model:open="idFallbackOpen">
                <CollapsibleTrigger as-child>
                    <Button type="button" variant="ghost" size="sm" class="-mx-1 gap-1 text-muted-foreground">
                        Add by channel ID
                        <IconChevronDown v-if="!idFallbackOpen" class="size-4" />
                        <IconChevronUp v-else class="size-4" />
                    </Button>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <form class="mt-3 flex flex-col gap-3" @submit.prevent="submitId">
                        <div class="grid gap-2">
                            <p id="channel-id-hint" class="text-xs text-muted-foreground">
                                Looks like <code>UCxxxxxxxxxxxxxxxxxxxxxx</code>. Found in YouTube channel URL or page source.
                            </p>
                            <Input
                                id="channel-id-input"
                                v-model="idForm.value"
                                placeholder="UC..."
                                autocomplete="off"
                                aria-describedby="channel-id-hint"
                            />
                            <p v-if="idForm.errors.value" class="text-sm text-destructive">{{ idForm.errors.value }}</p>
                        </div>
                        <Button type="submit" :disabled="idForm.processing || !idForm.value" class="self-start">
                            Add channel
                        </Button>
                    </form>
                </CollapsibleContent>
            </Collapsible>
        </div>

        <div>
            <h3 class="mb-2 text-sm font-medium text-muted-foreground">Channels in this group ({{ channels.length }})</h3>
            <div v-if="channels.length === 0" class="rounded-xl border border-dashed p-6 text-center text-muted-foreground">
                No channels yet.
            </div>
            <ul v-else class="divide-y rounded-xl border">
                <li v-for="channel in channels" :key="channel.id" class="flex items-start justify-between gap-2 p-3">
                    <div class="flex min-w-0 flex-1 gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            class="mt-0.5 shrink-0 text-muted-foreground hover:text-amber-500"
                            :disabled="togglingFavoriteId === channel.id"
                            :aria-pressed="channel.is_favorite"
                            :aria-label="channel.is_favorite ? 'Remove channel from favorites' : 'Favorite channel'"
                            @click="toggleFavorite(channel)"
                        >
                            <IconStarFilled v-if="channel.is_favorite" class="size-5 text-amber-400" />
                            <IconStar v-else class="size-5" />
                        </Button>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ channel.name }}</p>
                            <p class="mt-1 truncate font-mono text-xs text-muted-foreground">{{ channel.channel_id }}</p>
                        </div>
                    </div>
                    <Button variant="ghost" size="sm" class="shrink-0 text-destructive" @click="remove(channel)">
                        Remove
                    </Button>
                </li>
            </ul>
        </div>
        </div>
    </div>
</template>
