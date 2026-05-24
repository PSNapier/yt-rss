<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IconPlaylist, IconRss } from '@tabler/icons-vue';
import { cn } from '@/lib/utils';
import groups from '@/routes/groups';

type GroupPageNavCurrent = 'feed' | 'channels';

const props = defineProps<{
    current: GroupPageNavCurrent;
    groupId: number;
}>();

const tabClass = (active: boolean) =>
    cn(
        'inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
        active
            ? 'bg-cherry text-white'
            : 'text-muted-foreground hover:text-foreground',
    );
</script>

<template>
    <nav class="flex items-center gap-1" aria-label="Group pages">
        <Link
            :href="groups.show(groupId).url"
            :class="tabClass(current === 'feed')"
            :aria-current="current === 'feed' ? 'page' : undefined"
        >
            <IconRss class="size-3.5 shrink-0" aria-hidden="true" />
            Feed
        </Link>
        <Link
            :href="groups.channels.index(groupId).url"
            :class="tabClass(current === 'channels')"
            :aria-current="current === 'channels' ? 'page' : undefined"
        >
            <IconPlaylist class="size-3.5 shrink-0" aria-hidden="true" />
            Channels
        </Link>
    </nav>
</template>
