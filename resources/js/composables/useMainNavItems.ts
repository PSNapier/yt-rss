import {
    BoltIcon,
    QueueListIcon,
    VideoCameraIcon,
} from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import feed from '@/routes/feed';
import shorts from '@/routes/shorts';
import subscriptions from '@/routes/subscriptions';
import type { NavItem } from '@/types';

/** Main nav entries shared by the desktop sidebar and the mobile drawer. */
export function useMainNavItems() {
    const page = usePage();

    return computed<NavItem[]>(() => [
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
}
