<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{ count?: number }>(), { count: 8 });
</script>

<template>
    <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div
            v-for="n in count"
            :key="n"
            class="overflow-hidden rounded-[5px] border-2 border-border bg-card"
            aria-hidden="true"
        >
            <Skeleton class="aspect-video w-full rounded-none" />
            <div class="p-[10px_11px_12px]">
                <Skeleton class="mb-2 h-3.5 w-full" />
                <Skeleton class="mb-3 h-3.5 w-3/4" />
                <div class="flex items-center gap-2">
                    <Skeleton class="size-6 shrink-0 rounded-full" />
                    <Skeleton class="h-3 w-24" />
                </div>
            </div>
        </div>
    </div>
</template>
