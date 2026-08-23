<script setup lang="ts">
import {
    EyeIcon as EyeIconSolid,
    EyeSlashIcon as EyeSlashIconSolid,
    StarIcon as StarIconSolid,
} from '@heroicons/vue/24/solid';
import { computed } from 'vue';

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

const props = defineProps<{ video: Video }>();

const videoUrl = computed(
    () => `https://www.youtube.com/watch?v=${props.video.youtube_video_id}`,
);

defineEmits<{
    (e: 'card-click', video: Video): void;
    (e: 'context-menu', event: MouseEvent, video: Video): void;
    (e: 'toggle-watched', video: Video): void;
}>();

// Temporarily disabled: set back to true to restore the custom right-click menu
// and let the native browser context menu go away again.
const CONTEXT_MENU_ENABLED = false;
</script>

<template>
    <a
        :href="videoUrl"
        target="_blank"
        rel="noopener"
        class="group relative block cursor-pointer overflow-hidden rounded-[5px] border-2 bg-card transition-opacity"
        :class="
            video.channel_is_favorite
                ? video.user_state === 'watched'
                    ? 'border-[#d4a824] opacity-40 hover:opacity-70'
                    : 'border-[#d4a824]'
                : video.user_state === 'watched'
                  ? 'border-border opacity-40 hover:opacity-70'
                  : 'border-border hover:border-foreground/30'
        "
        @click="$emit('card-click', video)"
        @contextmenu="
            CONTEXT_MENU_ENABLED && $emit('context-menu', $event, video)
        "
    >
        <!-- Thumbnail -->
        <div class="relative aspect-video bg-muted">
            <img
                v-if="video.thumbnail_url"
                :src="video.thumbnail_url"
                :alt="video.title"
                class="pointer-events-none h-full w-full object-cover"
                loading="lazy"
            />
            <!-- Watched toggle — top left -->
            <button
                type="button"
                class="absolute top-2 left-2 z-10 rounded-full bg-black/55 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-black/75 focus-visible:opacity-100"
                :class="{ 'opacity-100': video.user_state === 'watched' }"
                :title="
                    video.user_state === 'watched'
                        ? 'Mark as unwatched'
                        : 'Mark as watched'
                "
                :aria-label="
                    video.user_state === 'watched'
                        ? 'Mark as unwatched'
                        : 'Mark as watched'
                "
                :aria-pressed="video.user_state === 'watched'"
                @click.prevent.stop="$emit('toggle-watched', video)"
            >
                <EyeSlashIconSolid
                    v-if="video.user_state === 'watched'"
                    class="size-4"
                />
                <EyeIconSolid v-else class="size-4" />
            </button>

            <!-- Favorite star — top right -->
            <div
                v-if="video.channel_is_favorite"
                class="pointer-events-none absolute top-2 right-2 z-10"
                aria-hidden="true"
            >
                <StarIconSolid
                    class="size-5 drop-shadow-md"
                    style="color: #ecc94b"
                />
            </div>
        </div>

        <!-- Card body -->
        <div class="p-[10px_11px_12px]">
            <p
                class="mb-2 line-clamp-2 h-[2.7em] overflow-hidden text-[13px] leading-[1.35] font-semibold tracking-[-0.005em] text-foreground"
            >
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
    </a>
</template>
