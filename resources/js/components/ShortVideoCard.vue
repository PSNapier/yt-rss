<script setup lang="ts">
import {
    EyeIcon as EyeIconSolid,
    EyeSlashIcon as EyeSlashIconSolid,
    CheckIcon as CheckIconSolid,
    StarIcon as StarIconSolid,
} from '@heroicons/vue/24/solid';
import { computed, ref, watch } from 'vue';

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

const props = defineProps<{
    video: Video;
    /** Unsubscribe mode: a click selects the channel instead of opening. */
    selectable?: boolean;
    selected?: boolean;
}>();

const videoUrl = computed(
    () => `https://www.youtube.com/shorts/${props.video.youtube_video_id}`,
);

// RSS only ever gives the 16:9 hqdefault. Shorts also publish a vertical
// `oardefault.jpg` at the same video ID, so try that first and fall back to a
// centre-cropped hqdefault when it 404s.
const verticalFailed = ref(false);

watch(
    () => props.video.youtube_video_id,
    () => {
        verticalFailed.value = false;
    },
);

const thumbnailUrl = computed(() => {
    if (verticalFailed.value) {
        return props.video.thumbnail_url;
    }

    return `https://i.ytimg.com/vi/${props.video.youtube_video_id}/oardefault.jpg`;
});

const onClick = (event: MouseEvent) => {
    if (props.selectable) {
        event.preventDefault();
        emit('select', props.video);

        return;
    }

    emit('card-click', props.video);
};

const emit = defineEmits<{
    (e: 'card-click', video: Video): void;
    (e: 'select', video: Video): void;
    (e: 'toggle-watched', video: Video): void;
}>();
</script>

<template>
    <a
        :href="selectable ? undefined : videoUrl"
        :target="selectable ? undefined : '_blank'"
        :rel="selectable ? undefined : 'noopener'"
        :role="selectable ? 'button' : undefined"
        :tabindex="selectable ? 0 : undefined"
        :aria-pressed="selectable ? selected : undefined"
        class="group relative block overflow-hidden rounded-[5px] border-2 bg-card transition-opacity"
        :class="[
            selected
                ? 'ring-[3px] ring-cherry ring-offset-2 ring-offset-background'
                : '',
            video.channel_is_favorite
                ? video.user_state === 'watched'
                    ? 'border-[#d4a824] opacity-40 hover:opacity-70'
                    : 'border-[#d4a824]'
                : video.user_state === 'watched'
                  ? 'border-border opacity-40 hover:opacity-70'
                  : 'border-border hover:border-foreground/30',
        ]"
        @click="onClick"
        @keydown.enter.space.prevent="selectable && emit('select', video)"
    >
        <!-- Vertical thumbnail -->
        <div class="relative aspect-[9/16] bg-muted">
            <img
                v-if="thumbnailUrl"
                :src="thumbnailUrl"
                :alt="video.title"
                class="pointer-events-none h-full w-full object-cover"
                loading="lazy"
                @error="verticalFailed = true"
            />

            <!-- Watched toggle — top left -->
            <!-- Selected badge (unsubscribe mode) -->
            <div
                v-if="selectable && selected"
                class="pointer-events-none absolute top-2 left-2 z-10 rounded-full bg-cherry p-1 text-white shadow-md"
                aria-hidden="true"
            >
                <CheckIconSolid class="size-4" />
            </div>
            <button
                v-if="!selectable"
                type="button"
                class="absolute top-2 left-2 z-10 rounded-full bg-black/55 p-1.5 text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-black/75 focus-visible:opacity-100 pointer-coarse:p-2 pointer-coarse:opacity-100"
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
        <div class="p-[8px_9px_10px]">
            <p
                class="mb-1.5 line-clamp-2 h-[2.7em] overflow-hidden text-[12px] leading-[1.35] font-semibold tracking-[-0.005em] text-foreground"
            >
                {{ video.title }}
            </p>
            <div class="flex items-center gap-1.5">
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
