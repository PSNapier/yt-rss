<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { resolveGroupIcon } from '@/lib/groupIcons';
import groups from '@/routes/groups';

const page = usePage();
const channelGroups = computed(() => (page.props.channelGroups as any[]) ?? []);
const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="min-h-0 flex-1 px-0 py-0">
        <div class="mb-1 mt-4 flex shrink-0 items-center justify-between px-4 group-data-[collapsible=icon]:invisible">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-sidebar-foreground/40">
                Groups
            </span>
            <Link
                :href="groups.index().url"
                class="flex size-5 items-center justify-center rounded text-sidebar-foreground/40 transition-colors hover:bg-sidebar-accent hover:text-sidebar-foreground"
                title="Manage groups"
            >
                <PlusIcon class="size-3.5" />
            </Link>
        </div>

        <SidebarMenu class="min-h-0 flex-1 gap-0.5 overflow-y-auto px-2 group-data-[collapsible=icon]:overflow-hidden">
            <SidebarMenuItem
                v-for="group in channelGroups"
                :key="group.id"
            >
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(groups.show(group.id))"
                    :tooltip="group.name"
                    :class="
                        isCurrentUrl(groups.show(group.id))
                            ? 'bg-cherry font-semibold text-white hover:bg-cherry hover:text-white data-[active=true]:bg-cherry data-[active=true]:text-white'
                            : 'text-foreground'
                    "
                >
                    <Link :href="groups.show(group.id).url">
                        <component :is="resolveGroupIcon(group.icon, group.name)" />
                        <span class="flex-1 truncate">{{ group.name }}</span>
                        <span
                            v-if="group.channels_count !== undefined && group.channels_count > 0"
                            :class="[
                                'rounded-full px-1.5 py-0.5 text-[11px] leading-none group-data-[collapsible=icon]:hidden',
                                isCurrentUrl(groups.show(group.id)) ? 'text-white/85' : 'text-sidebar-foreground/45',
                            ]"
                        >
                            {{ group.channels_count }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
