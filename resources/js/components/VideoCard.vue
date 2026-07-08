<script setup lang="ts">
import { StarIcon as StarIconSolid } from '@heroicons/vue/24/solid';

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

defineProps<{ video: Video }>();

defineEmits<{
    (e: 'card-click', video: Video): void;
    (e: 'context-menu', event: MouseEvent, video: Video): void;
}>();
</script>

<template>
    <div
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
        @click="$emit('card-click', video)"
        @contextmenu="$emit('context-menu', $event, video)"
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
    </div>
</template>
