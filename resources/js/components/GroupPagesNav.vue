<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FolderKanban, ListVideo, Rss } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import groups from '@/routes/groups';

type GroupPageNavCurrent = 'index' | 'feed' | 'channels';

type Props = {
    current: GroupPageNavCurrent;
    /** Required on feed and channels pages so Feed / Channels links resolve. */
    groupId?: number;
};

defineProps<Props>();

const linkClass = (active: boolean) =>
    cn(
        'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
        active
            ? 'border-primary bg-primary/10 text-foreground'
            : 'border-transparent bg-muted/50 text-muted-foreground hover:bg-muted hover:text-foreground',
    );
</script>

<template>
    <nav class="flex flex-wrap gap-2" aria-label="Group pages">
        <Link
            :href="groups.index().url"
            :class="linkClass(current === 'index')"
            :aria-current="current === 'index' ? 'page' : undefined"
        >
            <FolderKanban class="size-4 shrink-0" aria-hidden="true" />
            All groups
        </Link>

        <template v-if="groupId !== undefined">
            <Link
                :href="groups.show(groupId).url"
                :class="linkClass(current === 'feed')"
                :aria-current="current === 'feed' ? 'page' : undefined"
            >
                <Rss class="size-4 shrink-0" aria-hidden="true" />
                Feed
            </Link>
            <Link
                :href="groups.channels.index(groupId).url"
                :class="linkClass(current === 'channels')"
                :aria-current="current === 'channels' ? 'page' : undefined"
            >
                <ListVideo class="size-4 shrink-0" aria-hidden="true" />
                Channels
            </Link>
        </template>
    </nav>
</template>
