<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { HomeIcon, QueueListIcon, VideoCameraIcon } from '@heroicons/vue/24/outline';
import AppLogo from '@/components/AppLogo.vue';
import NavGroupFeeds from '@/components/NavGroupFeeds.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import feed from '@/routes/feed';
import groups from '@/routes/groups';
import subscriptions from '@/routes/subscriptions';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Groups',
        href: groups.index(),
        icon: HomeIcon,
    },
    {
        title: 'All Videos',
        href: feed.index(),
        icon: VideoCameraIcon,
    },
    {
        title: 'Subscriptions',
        href: subscriptions.index(),
        icon: QueueListIcon,
    },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <div
                class="flex items-center justify-between gap-2 group-data-[collapsible=icon]:justify-center"
            >
                <Link
                    :href="groups.index().url"
                    class="min-w-0 group-data-[collapsible=icon]:hidden"
                >
                    <AppLogo />
                </Link>
                <SidebarTrigger class="shrink-0" />
            </div>
        </SidebarHeader>

        <SidebarContent class="overflow-hidden">
            <NavMain :items="mainNavItems" />
            <NavGroupFeeds />
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
