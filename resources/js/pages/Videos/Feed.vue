<script setup lang="ts">
import {
    EyeIcon,
    EyeSlashIcon,
    FunnelIcon,
    UserMinusIcon,
    StarIcon,
} from '@heroicons/vue/24/outline';
import { FilmIcon } from '@heroicons/vue/24/solid';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    computed,
    nextTick,
    onMounted,
    onUnmounted,
    reactive,
    ref,
    watch,
} from 'vue';
import FeedGridSkeleton from '@/components/FeedGridSkeleton.vue';
import FeedStatsCard from '@/components/FeedStatsCard.vue';
import type { FeedStats } from '@/components/FeedStatsCard.vue';
import HeroToggleButton from '@/components/HeroToggleButton.vue';
import UnsubscribeActionBar from '@/components/UnsubscribeActionBar.vue';
import UnsubscribeConfirmDialog from '@/components/UnsubscribeConfirmDialog.vue';
import VideoCard from '@/components/VideoCard.vue';
import { getFeedCache, saveFeedCache } from '@/composables/useFeedCache';
import { postFeedPreferences } from '@/composables/useFeedPreferences';
import {
    beginFeedScroll,
    endFeedScroll,
    onFeedScroll,
} from '@/composables/useFeedScroll';
import { useUnsubscribeMode } from '@/composables/useUnsubscribeMode';
import type { ChannelGroupMap } from '@/composables/useUnsubscribeMode';
import { postVideoState } from '@/composables/useVideoState';
import feed from '@/routes/feed';
import videoRoutes from '@/routes/videos';

interface Channel {
    id: number;
    channel_id: string;
    name: string;
}

interface Video {
    id: number;
    youtube_video_id: string;
    title: string;
    thumbnail_url: string | null;
    published_at: string;
    user_state: 'watched' | 'hidden' | null;
    channel_is_favorite: boolean;
    channel: Channel;
}

interface CursorPaginator<T> {
    data: T[];
    next_cursor: string | null;
    prev_cursor: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    videos?: CursorPaginator<Video>;
    stats?: FeedStats;
    capEnabled?: boolean;
    channelGroupMap?: ChannelGroupMap;
    showWatched?: boolean;
    starredOnly?: boolean;
}>();

const items = reactive<Video[]>([]);
const nextUrl = ref<string | null>(null);
const loadingMore = ref(false);
const showWatched = ref(props.showWatched ?? true);
const starredOnly = ref(props.starredOnly ?? false);
const capOn = ref(props.capEnabled ?? false);

const cacheKey = 'all';
const MIN_VISIBLE = 15;
// True once the feed was restored from cache, so the deferred page-one
// payload that arrives on navigation is ignored instead of clobbering it.
const hydratedFromCache = ref(false);

const applyItems = (data: Video[], next: string | null) => {
    items.splice(0, items.length, ...data);
    nextUrl.value = next;
};

// Restore cached state on mount so returning keeps loaded videos + cursor.
const cached = getFeedCache<Video>(cacheKey);

if (cached) {
    applyItems(cached.items, cached.nextUrl);
    hydratedFromCache.value = true;
}

watch(
    () => props.videos,
    (v) => {
        if (!v) {
            return;
        }

        // Cache already restored richer state; ignore the deferred first page
        // so infinite-scroll progress survives the round trip.
        if (hydratedFromCache.value) {
            hydratedFromCache.value = false;

            return;
        }

        applyItems(v.data, v.next_page_url);
    },
    { immediate: true },
);

// Persist loaded videos + cursor so returning restores them.
watch(
    [items, nextUrl],
    () => {
        saveFeedCache(cacheKey, {
            items,
            nextUrl: nextUrl.value,
            olderExpanded: false,
        });
    },
    { deep: true },
);

const ctx = reactive({
    open: false,
    x: 0,
    y: 0,
    videoId: null as string | null,
});

const closeCtx = () => {
    ctx.open = false;
    ctx.videoId = null;
};

const openCtx = (event: MouseEvent, video: Video) => {
    event.preventDefault();
    // Keep the menu on screen when opened near an edge. The card's actions
    // button sits at its right edge, so on a phone the menu would spill off.
    ctx.x = Math.max(8, Math.min(event.clientX, window.innerWidth - 176));
    ctx.y = Math.max(8, Math.min(event.clientY, window.innerHeight - 136));
    ctx.videoId = video.youtube_video_id;
    ctx.open = true;
};

const setState = (
    youtubeVideoId: string,
    state: 'watched' | 'hidden' | null,
) => {
    const item = items.find((v) => v.youtube_video_id === youtubeVideoId);

    if (item) {
        item.user_state = state;
    }

    if (state === 'hidden') {
        const idx = items.findIndex(
            (v) => v.youtube_video_id === youtubeVideoId,
        );

        if (idx !== -1) {
            items.splice(idx, 1);
        }
    }

    // Plain XHR, not an Inertia visit: `videos` is a deferred prop, so a visit
    // would blank it and flash the whole grid back to the skeleton.
    postVideoState(videoRoutes.state.store(youtubeVideoId).url, state).then(
        () => {
            // With the cap on, a state change alters which unwatched video
            // each channel surfaces, so refetch the capped feed. A partial
            // reload keeps the current videos on screen until it lands.
            if (capOn.value) {
                router.reload({ only: ['videos'] });
            }
        },
    );
};

// Toggle the per-user cap, persist it, then refetch the feed with the new mode.
const toggleCap = () => {
    capOn.value = !capOn.value;
    router.post(
        feed.cap().url,
        { enabled: capOn.value },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => router.reload({ only: ['videos'] }),
        },
    );
};

// Persist a display toggle without an Inertia visit (see useFeedPreferences).
const toggleShowWatched = () => {
    showWatched.value = !showWatched.value;
    postFeedPreferences(feed.preferences().url, {
        show_watched: showWatched.value,
    });
};

const toggleStarredOnly = () => {
    starredOnly.value = !starredOnly.value;
    postFeedPreferences(feed.preferences().url, {
        starred_only: starredOnly.value,
    });
};

// Eye toggle on the card: flip watched <-> unwatched without opening the video.
const onToggleWatched = (video: Video) => {
    setState(
        video.youtube_video_id,
        video.user_state === 'watched' ? null : 'watched',
    );
};

// Unsubscribe-select mode: a card click selects its channel for removal.
const {
    active: unsubActive,
    count: unsubCount,
    selectedChannels: unsubChannels,
    dialogOpen: unsubDialogOpen,
    processing: unsubProcessing,
    toggleMode: toggleUnsubMode,
    exit: exitUnsubMode,
    clear: clearUnsub,
    isSelected: isChannelSelected,
    toggleChannel: toggleUnsubChannel,
    deselect: deselectUnsubChannel,
    openDialog: openUnsubDialog,
    confirm: confirmUnsub,
} = useUnsubscribeMode({
    channelGroups: () => props.channelGroupMap,
    onRemoved: (channelIds) => {
        const gone = new Set(channelIds);
        const kept = items.filter((v) => !gone.has(v.channel.id));
        items.splice(0, items.length, ...kept);
    },
});

const onCardClick = (video: Video) => {
    if (video.user_state !== 'watched') {
        setState(video.youtube_video_id, 'watched');
    }
};

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;
const page = usePage();

const loadMore = async (): Promise<boolean> => {
    if (loadingMore.value || !nextUrl.value) {
        return false;
    }

    loadingMore.value = true;

    try {
        const res = await fetch(nextUrl.value, {
            headers: {
                'X-Inertia': 'true',
                'X-Inertia-Version': String(page.version ?? ''),
                'X-Inertia-Partial-Component': 'Videos/Feed',
                'X-Inertia-Partial-Data': 'videos',
                Accept: 'text/html, application/xhtml+xml',
            },
        });

        if (!res.ok) {
            return false;
        }

        const json = await res.json();
        const data = json?.props?.videos as CursorPaginator<Video> | undefined;

        if (!data) {
            return false;
        }

        items.push(...data.data);
        nextUrl.value = data.next_page_url;

        return true;
    } finally {
        loadingMore.value = false;
    }
};

onMounted(() => {
    beginFeedScroll(cacheKey, nextTick);
    window.addEventListener('scroll', onFeedScroll, { passive: true });
    document.addEventListener('click', closeCtx);
    document.addEventListener('scroll', closeCtx, { passive: true });
    observer = new IntersectionObserver((entries) => {
        if (entries.some((e) => e.isIntersecting)) {
            loadMore();
        }
    });

    if (sentinel.value) {
        observer.observe(sentinel.value);
    }
});

onUnmounted(() => {
    endFeedScroll();
    window.removeEventListener('scroll', onFeedScroll);
    document.removeEventListener('click', closeCtx);
    document.removeEventListener('scroll', closeCtx);
    observer?.disconnect();
});

function startOfDay(offsetDays: number): Date {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    d.setDate(d.getDate() - offsetDays);

    return d;
}

const buckets = computed(() => {
    const today = startOfDay(0);
    const yesterday = startOfDay(2);
    const weekStart = startOfDay(7);

    const visible = items.filter(
        (v) =>
            (showWatched.value || v.user_state !== 'watched') &&
            (!starredOnly.value || v.channel_is_favorite),
    );

    const sections = [
        { id: 'today', label: 'Today', items: [] as Video[] },
        { id: 'yesterday', label: 'Yesterday', items: [] as Video[] },
        { id: 'week', label: 'Earlier this week', items: [] as Video[] },
        { id: 'older', label: 'Older', items: [] as Video[] },
    ];

    for (const v of visible) {
        const pub = new Date(v.published_at);

        if (pub >= today) {
            sections[0].items.push(v);
        } else if (pub >= yesterday) {
            sections[1].items.push(v);
        } else if (pub >= weekStart) {
            sections[2].items.push(v);
        } else {
            sections[3].items.push(v);
        }
    }

    return sections.filter((s) => s.items.length > 0);
});

// Keep pulling pages until MIN_VISIBLE cards show (after the watched/starred
// filters) or the cursor ends. A failed request stops the loop.
const visibleCount = computed(() =>
    buckets.value.reduce((sum, b) => sum + b.items.length, 0),
);
const autoFillFailed = ref(false);

watch(
    [visibleCount, nextUrl, loadingMore],
    async () => {
        if (
            autoFillFailed.value ||
            loadingMore.value ||
            !nextUrl.value ||
            visibleCount.value >= MIN_VISIBLE
        ) {
            return;
        }

        if (!(await loadMore())) {
            autoFillFailed.value = true;
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head title="All Videos" />

    <div class="flex h-full flex-1 flex-col">
        <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col">
                <!-- Hero banner -->
                <div
                    class="flex flex-wrap items-end gap-x-3.5 gap-y-4 rounded-t-xl rounded-b-none p-[18px] text-white md:flex-nowrap md:gap-[22px] md:p-[26px_28px]"
                    style="
                        background: linear-gradient(
                            180deg,
                            var(--cherry) 0%,
                            var(--cherry-deep) 100%
                        );
                    "
                >
                    <div
                        class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-white md:size-[92px]"
                        style="color: var(--cherry)"
                    >
                        <FilmIcon class="size-8 md:size-11" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p
                            class="mb-1 text-[11px] font-bold tracking-[0.16em] text-white/70 uppercase md:mb-[7px] md:text-[12px]"
                        >
                            Feed
                        </p>
                        <h1
                            class="truncate text-[26px] leading-[1.15] font-bold tracking-[-0.03em] text-white md:text-[40px]"
                        >
                            All Videos
                        </h1>
                    </div>

                    <div class="flex w-full shrink-0 items-center gap-[10px] md:w-auto">
                        <HeroToggleButton
                            :icon="UserMinusIcon"
                            :pressed="unsubActive"
                            :tooltip="
                                unsubActive
                                    ? 'Unsubscribe mode on. Click to turn it off'
                                    : 'Unsubscribe mode. Click videos to select channels to remove'
                            "
                            @click="toggleUnsubMode"
                        />
                        <HeroToggleButton
                            :icon="FunnelIcon"
                            :pressed="capOn"
                            :tooltip="
                                capOn
                                    ? 'Showing latest unwatched video per channel. Click to show all videos'
                                    : 'Showing all videos. Click to show only the latest unwatched per channel'
                            "
                            @click="toggleCap"
                        />
                        <HeroToggleButton
                            :icon="StarIcon"
                            :pressed="starredOnly"
                            :tooltip="
                                starredOnly
                                    ? 'Showing starred channels only. Click to show all channels'
                                    : 'Showing all channels. Click to show starred channels only'
                            "
                            @click="toggleStarredOnly"
                        />
                        <HeroToggleButton
                            :icon="showWatched ? EyeIcon : EyeSlashIcon"
                            :pressed="showWatched"
                            :tooltip="
                                showWatched
                                    ? 'Watched videos visible (greyed). Click to hide them'
                                    : 'Watched videos hidden. Click to show them'
                            "
                            @click="toggleShowWatched"
                        />
                    </div>
                </div>
                <FeedStatsCard :stats="props.stats" />
            </div>

            <!-- Skeleton only while nothing is loaded or cached. Cached cards
                 render immediately and the deferred payload reconciles quietly. -->
            <FeedGridSkeleton
                v-if="items.length === 0 && !props.videos"
                :count="8"
            />

            <template v-else>
                <!-- Empty state -->
                <div
                    v-if="items.length === 0"
                    class="rounded-xl border border-dashed p-8 text-center text-muted-foreground"
                >
                    No videos yet. Add channels to a group, then refresh.
                </div>

                <!-- Time-bucketed feed -->
                <template v-else>
                    <template v-if="buckets.length === 0">
                        <div
                            class="rounded-xl border border-dashed p-8 text-center text-muted-foreground"
                        >
                            No unwatched videos.
                        </div>
                    </template>

                    <section
                        v-for="bucket in buckets"
                        :key="bucket.id"
                        class="mb-8 last:mb-0"
                    >
                        <div
                            class="sticky top-0 z-10 mb-3 flex items-center gap-3 bg-background py-1"
                        >
                            <span
                                class="text-[11px] font-bold tracking-[0.14em] text-foreground uppercase"
                            >
                                {{ bucket.label }}
                            </span>
                            <span
                                class="rounded-[5px] bg-cherry px-2 py-[2px] text-[11px] text-white"
                            >
                                {{ bucket.items.length }} video{{
                                    bucket.items.length === 1 ? '' : 's'
                                }}
                            </span>
                            <div class="h-px flex-1 bg-border" />
                        </div>

                        <div
                            class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <VideoCard
                                v-for="video in bucket.items"
                                :key="video.youtube_video_id"
                                :video="video"
                                :selectable="unsubActive"
                                :selected="isChannelSelected(video.channel.id)"
                                @select="
                                    (v: Video) => toggleUnsubChannel(v.channel)
                                "
                                @card-click="onCardClick"
                                @toggle-watched="onToggleWatched"
                                @context-menu="openCtx"
                            />
                        </div>
                    </section>
                </template>
            </template>

            <div ref="sentinel" class="h-10" />
            <FeedGridSkeleton v-if="loadingMore" :count="4" class="mt-4" />

            <!-- Right-click context menu -->
            <div
                v-if="ctx.open && ctx.videoId"
                class="fixed z-50 min-w-[160px] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
                :style="{ top: `${ctx.y}px`, left: `${ctx.x}px` }"
                @click.stop
            >
                <button
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm pointer-coarse:py-2.5 hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, 'watched');
                        closeCtx();
                    "
                >
                    Mark watched
                </button>
                <button
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm pointer-coarse:py-2.5 hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, null);
                        closeCtx();
                    "
                >
                    Mark unwatched
                </button>
                <button
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm pointer-coarse:py-2.5 text-destructive hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, 'hidden');
                        closeCtx();
                    "
                >
                    Hide
                </button>
            </div>
            <UnsubscribeActionBar
                v-if="unsubActive"
                :count="unsubCount"
                @clear="clearUnsub"
                @remove="openUnsubDialog"
                @exit="exitUnsubMode"
            />

            <UnsubscribeConfirmDialog
                v-model:open="unsubDialogOpen"
                :channels="unsubChannels"
                :channel-groups="props.channelGroupMap"
                :processing="unsubProcessing"
                @deselect="deselectUnsubChannel"
                @confirm="confirmUnsub"
            />
        </div>
    </div>
</template>
