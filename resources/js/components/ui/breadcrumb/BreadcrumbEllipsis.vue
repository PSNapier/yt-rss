<script lang="ts" setup>
import type { HTMLAttributes } from "vue"
import { EllipsisHorizontalIcon } from "@heroicons/vue/24/outline"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <span
    data-slot="breadcrumb-ellipsis"
    role="presentation"
    aria-hidden="true"
    :class="cn('flex size-9 items-center justify-center', props.class)"
  >
    <slot>
      <EllipsisHorizontalIcon class="size-4" />
    </slot>
    <span class="sr-only">More</span>
  </span>
</template>
