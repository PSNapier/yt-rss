<script setup lang="ts">
import { EyeIcon, EyeSlashIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { Deferred, Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import FeedGridSkeleton from '@/components/FeedGridSkeleton.vue';
import VideoCard from '@/components/VideoCard.vue';
import { getFeedCache, saveFeedCache } from '@/composables/useFeedCache';
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
    capEnabled?: boolean;
}>();

const items = reactive<Video[]>([]);
const nextUrl = ref<string | null>(null);
const loadingMore = ref(false);
const showWatched = ref(true);
const capOn = ref(props.capEnabled ?? false);

const cacheKey = 'all';
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
    ctx.x = event.clientX;
    ctx.y = event.clientY;
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

    router.post(
        videoRoutes.state.store(youtubeVideoId).url,
        { state },
        {
            preserveScroll: true,
            preserveState: true,
            // With the cap on, a state change alters which unwatched video
            // each channel surfaces, so refetch the capped feed.
            onSuccess: () => {
                if (capOn.value) {
                    router.reload({ only: ['videos'] });
                }
            },
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

const onCardClick = (video: Video) => {
    if (video.user_state !== 'watched') {
        setState(video.youtube_video_id, 'watched');
    }

    window.open(
        `https://www.youtube.com/watch?v=${video.youtube_video_id}`,
        '_blank',
        'noopener',
    );
};

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;
const page = usePage();

const loadMore = async () => {
    if (loadingMore.value || !nextUrl.value) {
        return;
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
            return;
        }

        const json = await res.json();
        const data = json?.props?.videos as CursorPaginator<Video> | undefined;

        if (!data) {
            return;
        }

        items.push(...data.data);
        nextUrl.value = data.next_page_url;
    } finally {
        loadingMore.value = false;
    }
};

onMounted(() => {
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

    const visible = showWatched.value
        ? items
        : items.filter((v) => v.user_state !== 'watched');

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
</script>

<template>
    <Head title="All Videos" />

    <div class="flex h-full flex-1 flex-col">
        <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
            <!-- Hero banner -->
            <div
                class="flex items-end gap-[22px] rounded-xl p-[26px_28px] text-white"
                style="
                    background: linear-gradient(
                        180deg,
                        var(--cherry) 0%,
                        var(--cherry-deep) 100%
                    );
                "
            >
                <div
                    class="flex size-[92px] shrink-0 items-center justify-center rounded-xl bg-white"
                    style="color: var(--cherry)"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-11"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path
                            d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.32 6.32 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.24 8.24 0 0 0 4.83 1.56V6.8a4.85 4.85 0 0 1-1.06-.11z"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p
                        class="mb-[7px] text-[12px] font-bold tracking-[0.16em] text-white/70 uppercase"
                    >
                        Feed
                    </p>
                    <h1
                        class="text-[40px] leading-none font-bold tracking-[-0.03em] text-white"
                    >
                        All Videos
                    </h1>
                </div>

                <div class="flex items-center gap-[10px]">
                    <!-- Latest-unwatched-per-channel cap toggle -->
                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-[4px] border border-white/40 p-[6px_10px] text-[13px] font-medium transition-colors',
                            capOn
                                ? 'bg-white'
                                : 'bg-white/10 text-white/90 hover:text-white',
                        ]"
                        :style="capOn ? 'color: var(--cherry)' : ''"
                        :aria-pressed="capOn"
                        :title="
                            capOn
                                ? 'Showing latest unwatched video per channel'
                                : 'Showing all videos'
                        "
                        @click="toggleCap"
                    >
                        <FunnelIcon class="size-[15px]" />
                        <span>{{ capOn ? 'Latest only' : 'All videos' }}</span>
                    </button>

                    <button
                        type="button"
                        :class="[
                            'flex items-center gap-2 rounded-[4px] border border-white/40 p-[6px_10px] text-[13px] font-medium transition-colors',
                            showWatched
                                ? 'bg-white'
                                : 'bg-white/10 text-white/90 hover:text-white',
                        ]"
                        :style="showWatched ? 'color: var(--cherry)' : ''"
                        :aria-pressed="showWatched"
                        @click="showWatched = !showWatched"
                    >
                        <component
                            :is="showWatched ? EyeIcon : EyeSlashIcon"
                            class="size-[15px]"
                        />
                        <span>{{
                            showWatched ? 'Showing watched' : 'Hiding watched'
                        }}</span>
                    </button>
                </div>
            </div>

            <Deferred data="videos">
                <template #fallback>
                    <FeedGridSkeleton :count="8" />
                </template>

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
                                @card-click="onCardClick"
                                @context-menu="openCtx"
                            />
                        </div>
                    </section>
                </template>
            </Deferred>

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
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, 'watched');
                        closeCtx();
                    "
                >
                    Mark watched
                </button>
                <button
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, null);
                        closeCtx();
                    "
                >
                    Mark unwatched
                </button>
                <button
                    class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm text-destructive hover:bg-accent"
                    @click="
                        setState(ctx.videoId!, 'hidden');
                        closeCtx();
                    "
                >
                    Hide
                </button>
            </div>
        </div>
    </div>
</template>
