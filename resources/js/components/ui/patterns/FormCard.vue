<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  variant?: 'default' | 'compact' | 'spacious'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default'
})

const cardVariants = {
  default: 'p-8',
  compact: 'p-6',
  spacious: 'p-10'
}
</script>

<template>
  <div
    :class="
      cn(
        'bg-white dark:bg-card rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 min-h-[600px]',
        cardVariants[variant],
        props.class
      )
    "
  >
    <!-- Header Section -->
    <div v-if="title || subtitle || icon" class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <span v-if="icon" class="text-3xl">{{ icon }}</span>
        <div>
          <h2 v-if="title" class="text-xl font-bold text-slate-900 dark:text-slate-100">
            {{ title }}
          </h2>
          <p v-if="subtitle" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
            {{ subtitle }}
          </p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <slot />
  </div>
</template>
