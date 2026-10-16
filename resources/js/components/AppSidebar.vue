<script setup lang="ts">
import {
    BoltIcon,
    QueueListIcon,
    VideoCameraIcon,
} from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
import { landing } from '@/routes';
import feed from '@/routes/feed';
import shorts from '@/routes/shorts';
import subscriptions from '@/routes/subscriptions';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'All Videos',
        href: feed.index(),
        icon: VideoCameraIcon,
    },
    ...(page.props.shortsHidden
        ? []
        : [
              {
                  title: 'Shorts',
                  href: shorts.index(),
                  icon: BoltIcon,
              },
          ]),
    {
        title: 'Subscriptions',
        href: subscriptions.index(),
        icon: QueueListIcon,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <div
                class="flex items-center justify-between gap-2 group-data-[collapsible=icon]:justify-center"
            >
                <Link
                    :href="landing().url"
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
