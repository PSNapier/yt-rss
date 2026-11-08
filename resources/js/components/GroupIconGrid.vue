<script setup lang="ts">
// Loaded lazily (defineAsyncComponent) so the full Heroicons set stays out of the main bundle.
import type { Component } from 'vue';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';

defineProps<{ modelValue: string | null }>();
const emit = defineEmits<{ 'update:modelValue': [name: string] }>();

const query = ref('');
// Eager glob of individual icon modules (not the barrel), so only this lazy chunk carries the full set.
const modules = import.meta.glob<Component>(
    '/node_modules/@heroicons/vue/24/outline/esm/*Icon.js',
    { eager: true, import: 'default' },
);
const all = Object.entries(modules).map(([path, component]) => {
    const name = path.split('/').pop()!.replace(/\.js$/, '');

    return {
        name,
        component,
        label: name.replace(/Icon$/, '').replace(/([a-z0-9])([A-Z])/g, '$1 $2'),
    };
});

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();

    return q ? all.filter((i) => i.label.toLowerCase().includes(q)) : all;
});
</script>

<template>
    <div class="grid gap-2">
        <Input
            v-model="query"
            placeholder="Search icons"
            aria-label="Search icons"
            autocomplete="off"
        />
        <div
            class="grid max-h-56 grid-cols-6 gap-1 overflow-y-auto rounded-md border p-1.5 sm:grid-cols-8"
        >
            <button
                v-for="icon in filtered"
                :key="icon.name"
                type="button"
                :title="icon.label"
                :aria-label="icon.label"
                :aria-pressed="modelValue === icon.name"
                class="flex size-9 items-center justify-center rounded-md transition-colors hover:bg-accent"
                :class="modelValue === icon.name ? 'bg-accent ring-2 ring-ring' : ''"
                @click="emit('update:modelValue', icon.name)"
            >
                <component :is="icon.component" class="size-5" />
            </button>
            <p
                v-if="filtered.length === 0"
                class="col-span-full p-3 text-center text-sm text-muted-foreground"
            >
                No icons match.
            </p>
        </div>
    </div>
</template>
