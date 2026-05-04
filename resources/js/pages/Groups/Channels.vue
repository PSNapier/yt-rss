<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import groups from '@/routes/groups';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Channel {
    id: number;
    channel_id: string;
    name: string;
    last_fetched_at: string | null;
    is_favorite: boolean;
}

const props = defineProps<{
    group: { id: number; name: string };
    channels: Channel[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Groups', href: groups.index().url }],
    },
});

const idForm = useForm({ mode: 'id' as const, value: '', custom_name: '' });

const submitId = () => {
    idForm.post(groups.channels.store(props.group.id).url, {
        preserveScroll: true,
        onSuccess: () => idForm.reset('value', 'custom_name'),
    });
};

const remove = (channel: Channel) => {
    if (!window.confirm(`Remove ${channel.name} from this group?`)) return;
    router.delete(groups.channels.destroy([props.group.id, channel.id]).url, { preserveScroll: true });
};

const draftNames = ref<Record<number, string>>({});
const savingChannelId = ref<number | null>(null);
const nameError = ref<string | null>(null);
const nameErrorChannelId = ref<number | null>(null);

watch(
    () => props.channels,
    (channels) => {
        const next: Record<number, string> = { ...draftNames.value };
        for (const c of channels) {
            next[c.id] = c.name;
        }
        for (const id of Object.keys(next)) {
            const numId = Number(id);
            if (!channels.some((c) => c.id === numId)) {
                delete next[numId];
            }
        }
        draftNames.value = next;
        nameError.value = null;
        nameErrorChannelId.value = null;
    },
    { immediate: true, deep: true },
);

const togglingFavoriteId = ref<number | null>(null);

const toggleFavorite = (channel: Channel) => {
    if (togglingFavoriteId.value !== null) {
        return;
    }
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

const saveChannelName = (channel: Channel) => {
    const raw = draftNames.value[channel.id] ?? '';
    const name = raw.trim();
    if (name === channel.name) {
        return;
    }
    if (!name) {
        draftNames.value[channel.id] = channel.name;
        return;
    }
    nameError.value = null;
    nameErrorChannelId.value = null;
    savingChannelId.value = channel.id;
    router.patch(
        groups.channels.update([props.group.id, channel.id]).url,
        { name },
        {
            preserveScroll: true,
            onFinish: () => {
                savingChannelId.value = null;
            },
            onError: (errors) => {
                nameError.value = (errors.name as string) ?? null;
                nameErrorChannelId.value = channel.id;
            },
        },
    );
};
</script>

<template>
    <Head :title="`Channels - ${group.name}`" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <div class="flex items-end justify-between">
            <Heading
                :title="`${group.name} channels`"
                description="Add channels with a YouTube channel ID (UC…). Rename, favorite, or remove below."
            />
            <Link :href="groups.show(group.id).url">
                <Button variant="outline">Back to feed</Button>
            </Link>
        </div>

        <form class="flex max-w-xl flex-col gap-4 rounded-xl border p-4" @submit.prevent="submitId">
            <div class="grid gap-2">
                <Label for="channel-custom-name">Display name <span class="font-normal text-muted-foreground">(optional)</span></Label>
                <p id="channel-custom-name-hint" class="text-xs text-muted-foreground">
                    Shown in your feed. Leave blank to use YouTube title when API key set, otherwise the channel ID.
                </p>
                <Input
                    id="channel-custom-name"
                    v-model="idForm.custom_name"
                    placeholder="e.g. My favorite tech channel"
                    autocomplete="off"
                    aria-describedby="channel-custom-name-hint"
                />
                <p v-if="idForm.errors.custom_name" class="text-sm text-destructive">{{ idForm.errors.custom_name }}</p>
            </div>
            <div class="grid gap-2">
                <Label for="channel-id-input">Channel ID</Label>
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
            <Button type="submit" :disabled="idForm.processing || !idForm.value">Add channel</Button>
        </form>

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
                            <Star class="size-5" :class="channel.is_favorite ? 'fill-amber-400 text-amber-500' : ''" />
                        </Button>
                        <div class="min-w-0 flex-1">
                            <Label :for="`channel-name-${channel.id}`" class="sr-only">Channel display name</Label>
                            <Input
                                :id="`channel-name-${channel.id}`"
                                v-model="draftNames[channel.id]"
                                class="font-medium"
                                :disabled="savingChannelId === channel.id"
                                @blur="saveChannelName(channel)"
                                @keydown.enter.prevent="saveChannelName(channel)"
                            />
                            <p v-if="nameError && nameErrorChannelId === channel.id" class="mt-1 text-sm text-destructive">
                                {{ nameError }}
                            </p>
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
</template>
