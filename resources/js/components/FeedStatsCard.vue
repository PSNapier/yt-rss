<script setup lang="ts">
import {
    CalendarDaysIcon,
    ChartPieIcon,
    EyeIcon,
    RectangleStackIcon,
} from '@heroicons/vue/24/outline';
import { computed } from 'vue';

export interface MostWatched {
    channel_id: string;
    name: string;
    count: number;
}

export interface FeedStats {
    channel_count: number;
    starred_count: number;
    most_watched: {
        all_time: MostWatched | null;
        last_30_days: MostWatched | null;
        ratio: MostWatched | null;
    };
}

const props = defineProps<{
    /** Undefined while the deferred prop is still loading. */
    stats?: FeedStats | null;
}>();

const columns = computed(() => [
    {
        key: 'all_time',
        label: 'All time',
        icon: EyeIcon,
        suffix: '',
        unit: 'watched',
        entry: props.stats?.most_watched.all_time ?? null,
    },
    {
        key: 'last_30_days',
        label: '30 days',
        icon: CalendarDaysIcon,
        suffix: '',
        unit: 'watched',
        entry: props.stats?.most_watched.last_30_days ?? null,
    },
    {
        key: 'ratio',
        label: 'Ratio',
        icon: ChartPieIcon,
        suffix: '%',
        unit: 'of videos',
        entry: props.stats?.most_watched.ratio ?? null,
    },
]);
</script>

<template>
    <div
        class="flex overflow-hidden rounded-t-none rounded-b-xl border border-t-0 border-border bg-card"
        data-testid="feed-stats-card"
    >
        <!-- Most watched (2/3) -->
        <div class="flex min-w-0 basis-2/3 border-r border-border">
            <div
                v-for="(column, index) in columns"
                :key="column.key"
                :class="[
                    'flex min-w-0 flex-1 flex-col gap-[10px] px-[22px] py-[18px]',
                    index > 0 ? 'border-l border-border' : '',
                ]"
            >
                <span
                    class="flex items-center gap-1.5 text-[12px] font-bold tracking-[0.12em] text-muted-foreground/70 uppercase"
                >
                    <component :is="column.icon" class="size-3.5 shrink-0" />
                    <span class="truncate">{{ column.label }}</span>
                </span>

                <div
                    v-if="stats === undefined || stats === null"
                    class="h-[42px] animate-pulse rounded-md bg-muted"
                />
                <span
                    v-else-if="!column.entry"
                    class="text-[13px] text-muted-foreground"
                    >No watches yet</span
                >
                <template v-else>
                    <div class="flex min-w-0 items-center gap-2">
                        <span
                            class="flex size-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white"
                            style="background: var(--cherry)"
                            aria-hidden="true"
                        >
                            {{ column.entry.name[0] }}
                        </span>
                        <span
                            class="min-w-0 flex-1 truncate text-[14px] font-medium text-foreground"
                            :title="column.entry.name"
                            >{{ column.entry.name }}</span
                        >
                    </div>
                    <span
                        class="text-[22px] leading-none font-semibold tracking-[-0.01em] text-foreground"
                    >
                        {{ column.entry.count }}{{ column.suffix }}
                        <span
                            class="text-[12px] font-normal text-muted-foreground"
                            >{{ column.unit }}</span
                        >
                    </span>
                </template>
            </div>
        </div>

        <!-- Channels (1/3) -->
        <div
            class="flex min-w-0 basis-1/3 flex-col gap-[10px] px-[22px] py-[18px]"
        >
            <span
                class="flex items-center gap-1.5 text-[12px] font-bold tracking-[0.12em] text-muted-foreground/70 uppercase"
            >
                <RectangleStackIcon class="size-3.5 shrink-0" />
                Channels
            </span>
            <div
                v-if="stats === undefined || stats === null"
                class="h-[42px] animate-pulse rounded-md bg-muted"
            />
            <template v-else>
                <span
                    class="text-[22px] leading-none font-semibold tracking-[-0.01em] text-foreground"
                    >{{ stats.channel_count }}</span
                >
                <span class="text-[12px] text-muted-foreground"
                    >{{ stats.starred_count }} starred</span
                >
            </template>
        </div>
    </div>
</template>
