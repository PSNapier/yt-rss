<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { MoreVertical, Star } from 'lucide-vue-next';
import { onMounted, onUnmounted, reactive, ref } from 'vue';
import GroupPagesNav from '@/components/GroupPagesNav.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import groups from '@/routes/groups';
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
    group: { id: number; name: string };
    videos: CursorPaginator<Video>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Groups', href: groups.index().url },
        ],
    },
});

// Local state mirrors server video list so we can update watched/hidden client-side
const items = reactive<Video[]>([...props.videos.data]);
const nextUrl = ref<string | null>(props.videos.next_page_url);
const loadingMore = ref(false);

// Right-click context menu state
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

const setState = (youtubeVideoId: string, state: 'watched' | 'hidden' | null) => {
    const item = items.find((v) => v.youtube_video_id === youtubeVideoId);

    if (item) {
item.user_state = state;
}

    if (state === 'hidden') {
        const idx = items.findIndex((v) => v.youtube_video_id === youtubeVideoId);

        if (idx !== -1) {
items.splice(idx, 1);
}
    }

    router.post(
        videoRoutes.state.store(youtubeVideoId).url,
        { state },
        { preserveScroll: true, preserveState: true },
    );
};

const onCardClick = (video: Video) => {
    if (video.user_state !== 'watched') {
        setState(video.youtube_video_id, 'watched');
    }

    window.open(`https://www.youtube.com/watch?v=${video.youtube_video_id}`, '_blank', 'noopener');
};

const refreshFeed = () => {
    router.post(groups.refresh(props.group.id).url, {}, { preserveScroll: true });
};

// Infinite scroll
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
                'X-Inertia-Partial-Component': 'Groups/Show',
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

const formatDate = (iso: string) => {
    const d = new Date(iso);

    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

</script>

<template>
    <Head :title="group.name" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <GroupPagesNav current="feed" :group-id="group.id" />
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <Heading :title="group.name" :description="`Latest videos from channels in this group.`" />
            <div class="flex shrink-0 gap-2 sm:pb-0.5">
                <Button @click="refreshFeed">Refresh feed</Button>
            </div>
        </div>

        <div v-if="items.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            No videos yet. Add channels to this group, then refresh.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="video in items"
                :key="video.youtube_video_id"
                class="group relative cursor-pointer overflow-hidden rounded-xl border-2 transition-opacity"
                :class="
                    video.channel_is_favorite
                        ? video.user_state === 'watched'
                            ? 'border-yellow-500 opacity-40 hover:opacity-70'
                            : 'border-yellow-500 hover:border-yellow-600'
                        : video.user_state === 'watched'
                          ? 'border-border opacity-40 hover:opacity-70'
                          : 'border-border hover:border-foreground/30'
                "
                @click="onCardClick(video)"
                @contextmenu="openCtx($event, video)"
            >
                <div
                    v-if="video.channel_is_favorite"
                    class="pointer-events-none absolute left-2 top-2 z-10 rounded-full bg-background/90 p-1 text-yellow-500 shadow-sm ring-1 ring-yellow-500/30"
                    aria-hidden="true"
                >
                    <Star class="h-4 w-4 fill-yellow-500" />
                </div>
                <div class="aspect-video bg-muted">
                    <img
                        v-if="video.thumbnail_url"
                        :src="video.thumbnail_url"
                        :alt="video.title"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                </div>
                <div class="flex flex-col gap-1 p-3">
                    <p class="line-clamp-2 text-sm font-medium">{{ video.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ video.channel.name }} &middot; {{ formatDate(video.published_at) }}</p>
                </div>

                <div class="absolute right-2 top-2" @click.stop>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="secondary" size="icon" class="h-7 w-7">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem v-if="video.user_state !== 'watched'" @select="setState(video.youtube_video_id, 'watched')">
                                Mark watched
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="video.user_state === 'watched'" @select="setState(video.youtube_video_id, null)">
                                Mark unwatched
                            </DropdownMenuItem>
                            <DropdownMenuItem class="text-destructive" @select="setState(video.youtube_video_id, 'hidden')">
                                Hide
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div ref="sentinel" class="h-10" />
        <div v-if="loadingMore" class="text-center text-sm text-muted-foreground">Loading more...</div>

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
</template>
