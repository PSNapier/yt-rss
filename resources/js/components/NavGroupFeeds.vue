<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { IconPlus } from '@tabler/icons-vue';
import { SidebarGroup, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { resolveGroupIcon } from '@/lib/groupIcons';
import groups from '@/routes/groups';

const page = usePage();
const channelGroups = computed(() => (page.props.channelGroups as any[]) ?? []);
const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-0 py-0">
        <div class="mb-1 mt-4 flex items-center justify-between px-4">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                Groups
            </span>
            <Link
                :href="groups.index().url"
                class="flex size-5 items-center justify-center rounded text-sidebar-foreground/40 transition-colors hover:bg-sidebar-accent hover:text-sidebar-foreground"
                title="Manage groups"
            >
                <IconPlus class="size-3.5" />
            </Link>
        </div>

        <SidebarMenu class="gap-0.5 px-2">
            <SidebarMenuItem
                v-for="group in channelGroups"
                :key="group.id"
                class="relative"
            >
                <div
                    v-if="isCurrentUrl(groups.show(group.id))"
                    class="absolute bottom-1 left-0 top-1 w-0.5 rounded-full bg-cherry"
                />
                <Link
                    :href="groups.show(group.id).url"
                    :class="[
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                        isCurrentUrl(groups.show(group.id))
                            ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                            : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/60 hover:text-sidebar-foreground',
                    ]"
                >
                    <component
                        :is="resolveGroupIcon(group.icon, group.name)"
                        class="size-4 shrink-0 opacity-60"
                    />
                    <span class="flex-1 truncate">{{ group.name }}</span>
                    <span
                        v-if="group.channels_count !== undefined && group.channels_count > 0"
                        class="rounded-full px-1.5 py-0.5 text-[11px] leading-none text-sidebar-foreground/45"
                    >
                        {{ group.channels_count }}
                    </span>
                </Link>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
