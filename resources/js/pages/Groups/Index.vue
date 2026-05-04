<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import groupRoutes from '@/routes/groups';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

interface Group {
    id: number;
    name: string;
    channels_count: number;
}

defineProps<{ groups: Group[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Groups', href: groupRoutes.index().url }],
    },
});

const form = useForm({ name: '' });

const submit = () => {
    form.post(groupRoutes.store().url, {
        preserveScroll: true,
        onSuccess: () => form.reset('name'),
    });
};

const rename = (group: Group) => {
    const next = window.prompt('Rename group', group.name);
    if (!next || next === group.name) return;
    router.patch(groupRoutes.update(group.id).url, { name: next }, { preserveScroll: true });
};

const destroy = (group: Group) => {
    if (!window.confirm(`Delete group "${group.name}"?`)) return;
    router.delete(groupRoutes.destroy(group.id).url);
};
</script>

<template>
    <Head title="Groups" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <Heading title="Channel Groups" description="Organize YouTube channels into custom feeds." />

        <form class="flex gap-2" @submit.prevent="submit">
            <Input v-model="form.name" placeholder="New group name" class="max-w-sm" />
            <Button type="submit" :disabled="form.processing || !form.name">Create group</Button>
        </form>
        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>

        <div v-if="groups.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            No groups yet. Create your first one above.
        </div>

        <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="group in groups"
                :key="group.id"
                class="flex flex-col gap-3 rounded-xl border p-4"
            >
                <div class="flex items-start justify-between gap-2">
                    <Link :href="groupRoutes.show(group.id).url" class="text-lg font-semibold hover:underline">
                        {{ group.name }}
                    </Link>
                    <div class="flex gap-1">
                        <Button variant="ghost" size="sm" @click="rename(group)">Rename</Button>
                        <Button variant="ghost" size="sm" class="text-destructive" @click="destroy(group)">Delete</Button>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground">{{ group.channels_count }} channel{{ group.channels_count === 1 ? '' : 's' }}</p>
                <div class="flex gap-2">
                    <Link :href="groupRoutes.show(group.id).url">
                        <Button variant="outline" size="sm">View feed</Button>
                    </Link>
                    <Link :href="groupRoutes.channels.index(group.id).url">
                        <Button variant="outline" size="sm">Manage channels</Button>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
