<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import groups from '@/routes/groups';

const page = usePage();
const channelGroups = computed(
    () => page.props.channelGroups ?? [],
);

const { isCurrentUrl } = useCurrentUrl();

function groupInitial(name: string): string {
    const trimmed = name.trim();
    if (trimmed === '') {
        return '?';
    }

    const first = Array.from(trimmed)[0] ?? '?';

    return first.toLocaleUpperCase();
}
</script>

<template>
    <SidebarGroup
        v-if="channelGroups.length > 0"
        class="px-2 py-0"
    >
        <SidebarGroupLabel>Feeds</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem
                v-for="group in channelGroups"
                :key="group.id"
            >
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(groups.show(group.id))"
                    :tooltip="group.name"
                >
                    <Link :href="groups.show(group.id)">
                        <span
                            class="flex size-4 shrink-0 items-center justify-center rounded-sm border border-sidebar-border bg-sidebar-accent/60 text-[10px] font-semibold leading-none text-sidebar-foreground"
                            aria-hidden="true"
                        >
                            {{ groupInitial(group.name) }}
                        </span>
                        <span class="truncate">{{ group.name }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
