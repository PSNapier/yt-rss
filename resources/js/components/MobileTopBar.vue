<script setup lang="ts">
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavGroupFeeds from '@/components/NavGroupFeeds.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { useSidebar } from '@/components/ui/sidebar';
import { useMainNavItems } from '@/composables/useMainNavItems';
import { landing } from '@/routes';

const mainNavItems = useMainNavItems();
const open = ref(false);
const page = usePage();

// CSS (md:hidden) gates the bar so SSR markup matches the client. The
// sidebar's breakpoint only closes the drawer when the window grows past it.
const { isMobile } = useSidebar();

watch(isMobile, (mobile) => {
    if (!mobile) {
        open.value = false;
    }
});

const close = () => {
    open.value = false;
};

// The layout is persistent, so close the drawer on every page change.
watch(() => page.url, close);

// Picking any link closes the drawer, including the current page and the
// non-Inertia "Manage groups" anchor that only changes the hash.
const onDrawerClick = (event: MouseEvent) => {
    if ((event.target as HTMLElement).closest('a')) {
        close();
    }
};
</script>

<template>
    <header
        class="relative z-40 border-b border-sidebar-border bg-sidebar md:hidden"
    >
        <div class="flex h-14 items-center justify-between gap-2 pr-2 pl-3">
            <Link :href="landing().url" class="min-w-0">
                <AppLogo />
            </Link>
            <button
                type="button"
                class="flex size-11 items-center justify-center rounded-md text-sidebar-foreground hover:bg-sidebar-accent"
                :aria-expanded="open"
                aria-controls="mobile-nav-drawer"
                :aria-label="open ? 'Close menu' : 'Open menu'"
                @click="open = !open"
            >
                <XMarkIcon v-if="open" class="size-6" />
                <Bars3Icon v-else class="size-6" />
            </button>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <nav
                v-if="open"
                id="mobile-nav-drawer"
                aria-label="Main"
                class="absolute inset-x-0 top-full max-h-[calc(100svh-3.5rem)] overflow-y-auto border-b border-sidebar-border bg-sidebar pt-2 pb-3 text-sidebar-foreground shadow-lg [&_.text-left]:text-right [&_[data-sidebar=menu-button]]:h-11 [&_[data-sidebar=menu-button]]:flex-row-reverse [&_[data-sidebar=menu-button]]:text-base [&_[data-sidebar=menu-button]_span]:text-right [&_[data-sidebar=menu-button]_span.flex-1]:flex-none"
                @click="onDrawerClick"
            >
                <NavMain :items="mainNavItems" />
                <NavGroupFeeds />
                <div class="mt-2 border-t border-sidebar-border px-2 pt-2">
                    <NavUser />
                </div>
            </nav>
        </Transition>
    </header>

    <!-- Tap outside the drawer to close it. -->
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-150"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-30 bg-black/30 md:hidden"
            aria-hidden="true"
            @click="close"
        />
    </Transition>
</template>
