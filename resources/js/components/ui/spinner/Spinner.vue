<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { ArrowPathIcon } from "@heroicons/vue/24/outline"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <ArrowPathIcon
    role="status"
    aria-label="Loading"
    :class="cn('size-4 animate-spin', props.class)"
  />

</template>
