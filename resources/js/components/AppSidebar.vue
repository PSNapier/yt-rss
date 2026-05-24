<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { IconBrandGithub, IconBook, IconHome, IconPlaylist, IconStar } from '@tabler/icons-vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavGroupFeeds from '@/components/NavGroupFeeds.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import groups from '@/routes/groups';
import subscriptions from '@/routes/subscriptions';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Groups',
        href: groups.index(),
        icon: IconHome,
    },
    {
        title: 'Subscriptions',
        href: subscriptions.index(),
        icon: IconPlaylist,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: IconBrandGithub,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: IconBook,
    },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="groups.index().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavGroupFeeds />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
