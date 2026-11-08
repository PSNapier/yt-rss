<script setup lang="ts">
import type { Component } from 'vue';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

const props = defineProps<{
    icon: Component;
    /** Current state and what a click does. Also used as the aria-label. */
    tooltip: string;
    /** Omit for a plain action or link (no aria-pressed). */
    pressed?: boolean;
    href?: string;
}>();

defineEmits<{ click: [] }>();

const classes = (pressed: boolean | undefined) => [
    'flex size-[34px] shrink-0 items-center justify-center rounded-[4px] border border-white/40 transition-colors',
    pressed ? 'bg-white' : 'bg-white/10 text-white/90 hover:text-white',
];
</script>

<template>
    <TooltipProvider :delay-duration="150">
        <Tooltip>
            <TooltipTrigger as-child>
                <a
                    v-if="props.href"
                    :href="props.href"
                    data-inertia-link="false"
                    :aria-label="props.tooltip"
                    :class="classes(undefined)"
                >
                    <component :is="props.icon" class="size-[17px]" />
                </a>
                <button
                    v-else
                    type="button"
                    :aria-label="props.tooltip"
                    :aria-pressed="props.pressed"
                    :class="classes(props.pressed)"
                    :style="props.pressed ? 'color: var(--cherry)' : ''"
                    @click="$emit('click')"
                >
                    <component :is="props.icon" class="size-[17px]" />
                </button>
            </TooltipTrigger>
            <TooltipContent>{{ props.tooltip }}</TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
