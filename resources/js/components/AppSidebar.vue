<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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
    useSidebar,
} from '@/components/ui/sidebar';
import { useMainNavItems } from '@/composables/useMainNavItems';
import { landing } from '@/routes';

const mainNavItems = useMainNavItems();

// Below 768px the nav lives in MobileTopBar's drop-down drawer instead.
const { isMobile } = useSidebar();
</script>

<template>
    <Sidebar v-if="!isMobile" collapsible="icon">
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
