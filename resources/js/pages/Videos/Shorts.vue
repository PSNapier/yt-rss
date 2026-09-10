<script setup lang="ts">
import {
    BoltIcon,
    EyeIcon,
    EyeSlashIcon,
    FunnelIcon,
} from '@heroicons/vue/24/outline';
import { Deferred, Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import FeedGridSkeleton from '@/components/FeedGridSkeleton.vue';
import ShortVideoCard from '@/components/ShortVideoCard.vue';
import { getFeedCache, saveFeedCache } from '@/composables/useFeedCache';
import { postVideoState } from '@/composables/useVideoState';
import { resolveGroupIcon } from '@/lib/groupIcons';
import feed from '@/routes/feed';
import shorts from '@/routes/shorts';
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

interface Category {
    id: number;
    name: string;
    icon: string | null;
    hidden: boolean;
}

interface CursorPaginator<T> {
    data: T[];
    next_cursor: string | null;
    prev_cursor: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    videos?: CursorPaginator<Video>;
    categories?: Category[];
    capEnabled?: boolean;
}>();

const items = reactive<Video[]>([]);
const nextUrl = ref<string | null>(null);
const loadingMore = ref(false);
const showWatched = ref(true);
const capOn = ref(props.capEnabled ?? false);

// The hidden set is server state: filtering happens in the query, so a chip
// toggle refetches rather than hiding rows the paginator already skipped.
const hiddenIds = ref<number[]>(
    (props.categories ?? []).filter((c) => c.hidden).map((c) => c.id),
);

watch(
    () => props.categories,
    (categories) => {
        if (categories) {
            hiddenIds.value = categories
                .filter((c) => c.hidden)
                .map((c) => c.id);
        }
    },
);

const cacheKey = 'shorts';
const hydratedFromCache = ref(false);

const applyItems = (data: Video[], next: string | null) => {
    items.splice(0, items.length, ...data);
    nextUrl.value = next;
};

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

        if (hydratedFromCache.value) {
            hydratedFromCache.value = false;

            return;
        }

        applyItems(v.data, v.next_page_url);
    },
    { immediate: true },
);

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

    // Plain XHR, not an Inertia visit: `videos` is deferred, so a visit would
    // blank it and flash the grid back to the skeleton.
    postVideoState(videoRoutes.state.store(youtubeVideoId).url, state).then(
        () => {
            if (capOn.value) {
                router.reload({ only: ['videos'] });
            }
        },
    );
};

// Shared with the other feeds: this is the same user-level cap flag.
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

const toggleCategory = (category: Category) => {
    const next = hiddenIds.value.includes(category.id)
        ? hiddenIds.value.filter((id) => id !== category.id)
        : [...hiddenIds.value, category.id];

    hiddenIds.value = next;

    router.post(
        shorts.categories().url,
        { hidden_group_ids: next },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                // The filter narrows the query, so the loaded pages and cursor
                // are stale: start the feed over under the new category set.
                items.splice(0, items.length);
                nextUrl.value = null;
                router.reload({ only: ['videos'] });
            },
        },
    );
};

const isVisible = (category: Category) =>
    !hiddenIds.value.includes(category.id);

const onToggleWatched = (video: Video) => {
    setState(
        video.youtube_video_id,
        video.user_state === 'watched' ? null : 'watched',
    );
};

const onCardClick = (video: Video) => {
    if (video.user_state !== 'watched') {
        setState(video.youtube_video_id, 'watched');
    }
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
                'X-Inertia-Partial-Component': 'Videos/Shorts',
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
    <Head title="Shorts" />

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
                    <BoltIcon class="size-11" />
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
                        Shorts
                    </h1>
                </div>

                <div class="flex items-center gap-[10px]">
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
                                ? 'Showing latest unwatched Short per channel'
                                : 'Showing all Shorts'
                        "
                        @click="toggleCap"
                    >
                        <FunnelIcon class="size-[15px]" />
                        <span>{{ capOn ? 'Latest only' : 'All Shorts' }}</span>
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

            <!-- Category chips: click to hide or show a category -->
            <div
                v-if="(props.categories?.length ?? 0) > 0"
                class="flex flex-wrap items-center gap-2"
            >
                <button
                    v-for="category in props.categories"
                    :key="category.id"
                    type="button"
                    :aria-pressed="isVisible(category)"
                    :title="
                        isVisible(category)
                            ? `Hide ${category.name}`
                            : `Show ${category.name}`
                    "
                    :class="[
                        'flex items-center gap-1.5 rounded-full border px-3 py-1 text-[12px] font-medium transition-colors',
                        isVisible(category)
                            ? 'border-transparent bg-cherry text-white'
                            : 'border-border bg-transparent text-muted-foreground hover:text-foreground',
                    ]"
                    @click="toggleCategory(category)"
                >
                    <component
                        :is="resolveGroupIcon(category.icon, category.name)"
                        class="size-3.5"
                    />
                    <span>{{ category.name }}</span>
                </button>
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
                    No Shorts yet.
                </div>

                <template v-else>
                    <template v-if="buckets.length === 0">
                        <div
                            class="rounded-xl border border-dashed p-8 text-center text-muted-foreground"
                        >
                            No unwatched Shorts.
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
                                {{ bucket.items.length }} Short{{
                                    bucket.items.length === 1 ? '' : 's'
                                }}
                            </span>
                            <div class="h-px flex-1 bg-border" />
                        </div>

                        <div
                            class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8"
                        >
                            <ShortVideoCard
                                v-for="video in bucket.items"
                                :key="video.youtube_video_id"
                                :video="video"
                                @card-click="onCardClick"
                                @toggle-watched="onToggleWatched"
                            />
                        </div>
                    </section>
                </template>
            </Deferred>

            <div ref="sentinel" class="h-10" />
            <FeedGridSkeleton v-if="loadingMore" :count="4" class="mt-4" />
        </div>
    </div>
</template>
