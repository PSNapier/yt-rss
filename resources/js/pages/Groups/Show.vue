<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { EyeIcon, EyeSlashIcon, WrenchIcon } from '@heroicons/vue/24/outline';
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { resolveGroupIcon } from '@/lib/groupIcons';
import groups from '@/routes/groups';
import subscriptions from '@/routes/subscriptions';
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
    group: { id: number; name: string; icon: string | null };
    videos: CursorPaginator<Video>;
}>();

defineOptions({});

const items = reactive<Video[]>([...props.videos.data]);
const nextUrl = ref<string | null>(props.videos.next_page_url);
const loadingMore = ref(false);
const showWatched = ref(true);
const olderExpanded = ref(false);

// Right-click context menu
const ctx = reactive({
    open: false,
    x: 0,
    y: 0,
    videoId: null as string | null,
});

const closeCtx = () => { ctx.open = false; ctx.videoId = null; };

const openCtx = (event: MouseEvent, video: Video) => {
    event.preventDefault();
    ctx.x = event.clientX;
    ctx.y = event.clientY;
    ctx.videoId = video.youtube_video_id;
    ctx.open = true;
};

const setState = (youtubeVideoId: string, state: 'watched' | 'hidden' | null) => {
    const item = items.find((v) => v.youtube_video_id === youtubeVideoId);
    if (item) item.user_state = state;
    if (state === 'hidden') {
        const idx = items.findIndex((v) => v.youtube_video_id === youtubeVideoId);
        if (idx !== -1) items.splice(idx, 1);
    }
    router.post(videoRoutes.state.store(youtubeVideoId).url, { state }, { preserveScroll: true, preserveState: true });
};

const onCardClick = (video: Video) => {
    if (video.user_state !== 'watched') setState(video.youtube_video_id, 'watched');
    window.open(`https://www.youtube.com/watch?v=${video.youtube_video_id}`, '_blank', 'noopener');
};

const page = usePage();

const loadMore = async () => {
    if (loadingMore.value || !nextUrl.value) return;
    loadingMore.value = true;
    try {
        const res = await fetch(nextUrl.value, {
            headers: {
                'X-Inertia': 'true',
                'X-Inertia-Version': String(page.version ?? ''),
                'X-Inertia-Partial-Component': 'Groups/Show',
                'X-Inertia-Partial-Data': 'videos',
                Accept: 'text/html, application/xhtml+xml',
            },
        });
        if (!res.ok) return;
        const json = await res.json();
        const data = json?.props?.videos as CursorPaginator<Video> | undefined;
        if (!data) return;
        items.push(...data.data);
        nextUrl.value = data.next_page_url;
    } finally {
        loadingMore.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeCtx);
    document.addEventListener('scroll', closeCtx, { passive: true });
});

onUnmounted(() => {
    document.removeEventListener('click', closeCtx);
    document.removeEventListener('scroll', closeCtx);
});

// Time bucketing
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
        { id: 'today',    label: 'Today',              items: [] as Video[] },
        { id: 'yesterday',label: 'Yesterday',           items: [] as Video[] },
        { id: 'week',     label: 'Earlier this week',   items: [] as Video[] },
        { id: 'older',    label: 'Older',               items: [] as Video[] },
    ];

    for (const v of visible) {
        const pub = new Date(v.published_at);
        if (pub >= today) sections[0].items.push(v);
        else if (pub >= yesterday) sections[1].items.push(v);
        else if (pub >= weekStart) sections[2].items.push(v);
        else sections[3].items.push(v);
    }

    return sections.filter((s) => s.items.length > 0);
});

const olderBucket = computed(() => buckets.value.find((b) => b.id === 'older') ?? null);
const recentBuckets = computed(() => buckets.value.filter((b) => b.id !== 'older'));
const displayBuckets = computed(() => {
    const recent = recentBuckets.value;
    const older = olderBucket.value;
    if (!older || !olderExpanded.value) {
        return recent;
    }

    return [...recent, older];
});

const showLoadMoreButton = computed(
    () => (olderBucket.value !== null && !olderExpanded.value) || nextUrl.value !== null,
);

const loadMoreLabel = computed(() => {
    if (olderBucket.value && !olderExpanded.value) {
        const count = olderBucket.value.items.length;

        return `Show older videos (${count})`;
    }

    return loadingMore.value ? 'Loading…' : 'Load more';
});

const onLoadMoreClick = () => {
    if (olderBucket.value && !olderExpanded.value) {
        olderExpanded.value = true;

        return;
    }

    loadMore();
};

</script>

<template>
    <Head :title="group.name" />

    <div class="flex h-full flex-1 flex-col">
        <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">

        <!-- Hero + stats (single fused unit) -->
        <div class="flex flex-col">

        <!-- Hero banner -->
        <div
            class="flex items-end gap-[22px] rounded-t-xl rounded-b-none p-[26px_28px] text-white"
            style="background: linear-gradient(180deg, var(--cherry) 0%, var(--cherry-deep) 100%)"
        >
            <!-- Group icon tile -->
            <div
                class="flex size-[92px] shrink-0 items-center justify-center rounded-xl bg-white"
                style="color: var(--cherry)"
            >
                <component :is="resolveGroupIcon(group.icon, group.name)" class="size-11" />
            </div>

            <div class="min-w-0 flex-1">
                <p class="mb-[7px] text-[12px] font-bold uppercase tracking-[0.16em] text-white/70">Group</p>
                <h1 class="text-[40px] font-bold leading-none tracking-[-0.03em] text-white">
                    {{ group.name }}
                </h1>
            </div>

            <div class="flex items-center gap-[10px]">
                <!-- Show/hide watched toggle -->
                <button
                    type="button"
                    :class="[
                        'flex items-center gap-2 rounded-[4px] border border-white/40 p-[6px_10px] text-[13px] font-medium transition-colors',
                        showWatched ? 'bg-white' : 'bg-white/10 text-white/90 hover:text-white',
                    ]"
                    :style="showWatched ? 'color: var(--cherry)' : ''"
                    :aria-pressed="showWatched"
                    :title="showWatched ? 'Watched videos visible (greyed)' : 'Hide watched videos'"
                    @click="showWatched = !showWatched"
                >
                    <component :is="showWatched ? EyeIcon : EyeSlashIcon" class="size-[15px]" />
                    <span>{{ showWatched ? 'Showing watched' : 'Hiding watched' }}</span>
                </button>

                <a
                    :href="`${subscriptions.index().url}#subscription-group-${group.id}`"
                    data-inertia-link="false"
                    class="flex items-center gap-2 rounded-[4px] border border-white/40 bg-white/10 p-[6px_10px] text-[13px] font-medium text-white/90 transition-colors hover:text-white"
                >
                    <WrenchIcon class="size-[15px]" />
                    Manage channels
                </a>
            </div>
        </div>

        <!-- Stats card -->
        <div class="flex overflow-hidden rounded-b-xl rounded-t-none border border-t-0 border-border bg-card">
            <div class="flex flex-1 flex-col gap-[10px] border-r border-border px-[22px] py-[18px]">
                <span class="text-[12px] font-bold uppercase tracking-[0.12em] text-muted-foreground/70">Channels</span>
                <span class="text-[22px] font-semibold tracking-[-0.01em] text-foreground">—</span>
            </div>
            <div class="flex flex-1 flex-col gap-[10px] border-r border-border px-[22px] py-[18px]">
                <span class="text-[12px] font-bold uppercase tracking-[0.12em] text-muted-foreground/70">Starred</span>
                <span class="text-[22px] font-semibold tracking-[-0.01em] text-foreground">—</span>
            </div>
            <div class="flex flex-1 flex-col gap-[10px] px-[22px] py-[18px]">
                <span class="text-[12px] font-bold uppercase tracking-[0.12em] text-muted-foreground/70">Last sync</span>
                <span class="text-[22px] font-semibold tracking-[-0.01em] text-foreground">—</span>
            </div>
        </div>

        </div>
        <!-- /Hero + stats -->

        <!-- Empty state -->
        <div
            v-if="items.length === 0"
            class="rounded-xl border border-dashed p-8 text-center text-muted-foreground"
        >
            No videos yet. Add channels to this group, then refresh.
        </div>

        <!-- Time-bucketed feed -->
        <template v-else>
            <template v-if="buckets.length === 0">
                <div class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                    No unwatched videos.
                </div>
            </template>

            <section v-for="bucket in displayBuckets" :key="bucket.id" class="mb-8 last:mb-0">
                <!-- Sticky section header -->
                <div class="sticky top-0 z-10 mb-3 flex items-center gap-3 bg-background py-1">
                    <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-foreground">
                        {{ bucket.label }}
                    </span>
                    <span class="rounded-[5px] bg-cherry px-2 py-[2px] text-[11px] text-white">
                        {{ bucket.items.length }} video{{ bucket.items.length === 1 ? '' : 's' }}
                    </span>
                    <div class="h-px flex-1 bg-border" />
                </div>

                <!-- Video grid -->
                <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="video in bucket.items"
                        :key="video.youtube_video_id"
                        class="group relative cursor-pointer overflow-hidden rounded-[5px] border-2 bg-card transition-opacity"
                        :class="
                            video.channel_is_favorite
                                ? video.user_state === 'watched'
                                    ? 'border-[#d4a824] opacity-40 hover:opacity-70'
                                    : 'border-[#d4a824]'
                                : video.user_state === 'watched'
                                  ? 'border-border opacity-40 hover:opacity-70'
                                  : 'border-border hover:border-foreground/30'
                        "
                        @click="onCardClick(video)"
                        @contextmenu="openCtx($event, video)"
                    >
                        <!-- Thumbnail -->
                        <div class="relative aspect-video bg-muted">
                            <img
                                v-if="video.thumbnail_url"
                                :src="video.thumbnail_url"
                                :alt="video.title"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                            <!-- Favorite star — top right -->
                            <div
                                v-if="video.channel_is_favorite"
                                class="pointer-events-none absolute right-2 top-2 z-10"
                                aria-hidden="true"
                            >
                                <StarIconSolid class="size-5 drop-shadow-md" style="color: #ecc94b" />
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="p-[10px_11px_12px]">
                            <p class="mb-2 line-clamp-2 min-h-9 text-[13px] font-semibold leading-[1.35] tracking-[-0.005em] text-foreground">
                                {{ video.title }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                <!-- Channel avatar -->
                                <span
                                    class="flex size-4 shrink-0 items-center justify-center rounded-full text-[8.5px] font-bold text-white"
                                    style="background: var(--cherry)"
                                >
                                    {{ video.channel.name[0] }}
                                </span>
                                <span class="truncate text-[11px] text-muted-foreground">
                                    {{ video.channel.name }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

        </template>

        <div v-if="showLoadMoreButton" class="flex justify-center pb-4">
            <button
                type="button"
                class="rounded-lg border border-border bg-muted/50 px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted disabled:opacity-50"
                :disabled="loadingMore"
                @click="onLoadMoreClick"
            >
                {{ loadMoreLabel }}
            </button>
        </div>

        <!-- Right-click context menu -->
        <div
            v-if="ctx.open && ctx.videoId"
            class="fixed z-50 min-w-[160px] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
            :style="{ top: `${ctx.y}px`, left: `${ctx.x}px` }"
            @click.stop
        >
            <button
                class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                @click="setState(ctx.videoId!, 'watched'); closeCtx()"
            >
                Mark watched
            </button>
            <button
                class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                @click="setState(ctx.videoId!, null); closeCtx()"
            >
                Mark unwatched
            </button>
            <button
                class="flex w-full items-center rounded-sm px-2 py-1.5 text-sm text-destructive hover:bg-accent"
                @click="setState(ctx.videoId!, 'hidden'); closeCtx()"
            >
                Hide
            </button>
        </div>
        </div>
    </div>
</template>
