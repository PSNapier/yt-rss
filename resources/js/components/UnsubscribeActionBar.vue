<script setup lang="ts">
defineProps<{ count: number }>();

defineEmits<{ clear: []; remove: []; exit: [] }>();
</script>

<template>
    <!-- Spacer so the fixed bar never covers the last row of cards. -->
    <div class="h-16" aria-hidden="true" />

    <!-- Fixed, not sticky: the layout's <main> clips overflow, which breaks
         sticky positioning against the window scroll. -->
    <div
        class="pointer-events-none fixed inset-x-0 bottom-4 z-30 flex justify-center px-4"
    >
        <div
            class="pointer-events-auto flex w-full max-w-xl items-center gap-3 rounded-xl border bg-popover p-3 text-popover-foreground shadow-lg"
            role="region"
            aria-label="Unsubscribe mode"
        >
            <span class="min-w-0 flex-1 text-sm">
                <span class="font-semibold">{{ count }}</span>
                channel{{ count === 1 ? '' : 's' }} selected
                <span v-if="count === 0" class="text-muted-foreground">
                    (click a video to select its channel)
                </span>
            </span>
            <button
                type="button"
                class="rounded-md px-3 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground disabled:opacity-50"
                :disabled="count === 0"
                @click="$emit('clear')"
            >
                Clear
            </button>
            <button
                type="button"
                class="rounded-md bg-cherry px-3 py-1.5 text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                :disabled="count === 0"
                @click="$emit('remove')"
            >
                Remove subscriptions
            </button>
            <button
                type="button"
                class="rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                @click="$emit('exit')"
            >
                Done
            </button>
        </div>
    </div>
</template>
