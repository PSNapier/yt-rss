<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { ChevronDoubleLeftIcon, ChevronDoubleRightIcon } from "@heroicons/vue/24/outline"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
import { useSidebar } from "./utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const { isMobile, state, toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-7 w-7', props.class)"
    @click="toggleSidebar"
  >
    <ChevronDoubleRightIcon v-if="isMobile || state === 'collapsed'" />
    <ChevronDoubleLeftIcon v-else />
    <span class="sr-only">Toggle sidebar</span>
  </Button>
</template>
