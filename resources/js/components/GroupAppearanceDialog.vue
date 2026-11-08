<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { GROUP_COLORS, resolveGroupColor } from '@/lib/groupColors';
import { resolveGroupIcon } from '@/lib/groupIcons';
import groupRoutes from '@/routes/groups';

const props = defineProps<{
    group: {
        id: number;
        name: string;
        icon: string | null;
        color: string | null;
    } | null;
}>();
const emit = defineEmits<{ close: [] }>();

const GroupIconGrid = defineAsyncComponent(
    () => import('@/components/GroupIconGrid.vue'),
);

const icon = ref<string | null>(null);
const color = ref<string | null>(null);
const saving = ref(false);
const errors = ref<Record<string, string>>({});

watch(
    () => props.group,
    (group) => {
        icon.value = group?.icon ?? null;
        color.value = group?.color ?? null;
        errors.value = {};
    },
    { immediate: true },
);

const PreviewIcon = computed(() =>
    resolveGroupIcon(icon.value, props.group?.name ?? ''),
);
const previewColor = computed(
    () => resolveGroupColor(color.value)?.icon ?? 'var(--cherry)',
);

const reset = () => {
    icon.value = null;
    color.value = null;
};

const save = () => {
    if (!props.group) {
        return;
    }

    router.patch(
        groupRoutes.update(props.group.id).url,
        { icon: icon.value, color: color.value },
        {
            preserveScroll: true,
            onStart: () => (saving.value = true),
            onFinish: () => (saving.value = false),
            onError: (e) => (errors.value = e),
            onSuccess: () => emit('close'),
        },
    );
};
</script>

<template>
    <Dialog
        :open="group !== null"
        @update:open="(open: boolean) => !open && emit('close')"
    >
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Group appearance</DialogTitle>
                <DialogDescription
                    >Icon and color for {{ group?.name }}.</DialogDescription
                >
            </DialogHeader>

            <div class="grid gap-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-12 items-center justify-center rounded-xl border"
                        :style="{ color: previewColor }"
                    >
                        <component :is="PreviewIcon" class="size-7" />
                    </div>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="reset"
                        >Reset to default</Button
                    >
                </div>

                <div class="grid gap-2">
                    <p class="text-sm font-medium">Color</p>
                    <div
                        class="flex flex-wrap gap-2"
                        role="group"
                        aria-label="Group color"
                    >
                        <button
                            type="button"
                            title="Cherry (default)"
                            aria-label="Cherry (default)"
                            :aria-pressed="color === null"
                            class="size-7 rounded-full ring-offset-2 ring-offset-background"
                            :class="color === null ? 'ring-2 ring-ring' : ''"
                            style="background: var(--cherry)"
                            @click="color = null"
                        />
                        <button
                            v-for="swatch in GROUP_COLORS"
                            :key="swatch.key"
                            type="button"
                            :title="swatch.label"
                            :aria-label="swatch.label"
                            :aria-pressed="color === swatch.key"
                            class="size-7 rounded-full ring-offset-2 ring-offset-background"
                            :class="
                                color === swatch.key ? 'ring-2 ring-ring' : ''
                            "
                            :style="{ background: swatch.icon }"
                            @click="color = swatch.key"
                        />
                    </div>
                    <p v-if="errors.color" class="text-sm text-destructive">
                        {{ errors.color }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <p class="text-sm font-medium">Icon</p>
                    <Suspense>
                        <GroupIconGrid v-model="icon" />
                        <template #fallback>
                            <p
                                class="py-6 text-center text-sm text-muted-foreground"
                            >
                                Loading icons...
                            </p>
                        </template>
                    </Suspense>
                    <p v-if="errors.icon" class="text-sm text-destructive">
                        {{ errors.icon }}
                    </p>
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="ghost" @click="emit('close')"
                    >Cancel</Button
                >
                <Button type="button" :disabled="saving" @click="save"
                    >Save</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
