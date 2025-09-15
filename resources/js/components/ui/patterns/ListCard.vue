<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  emptyMessage?: string
  emptyIcon?: string
}

const props = withDefaults(defineProps<Props>(), {
  emptyMessage: 'Nenhum item cadastrado',
  emptyIcon: '📋'
})
</script>

<template>
  <div
    :class="
      cn(
        'bg-white dark:bg-card rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 min-h-[400px]',
        props.class
      )
    "
  >
    <!-- Header Section -->
    <div v-if="title || subtitle || icon" class="mb-6">
      <div class="flex items-center gap-3">
        <span v-if="icon" class="text-2xl">{{ icon }}</span>
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
    <div v-if="$slots.default" class="space-y-3">
      <slot />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 text-slate-500 dark:text-slate-400">
      <div class="text-5xl mb-4">{{ emptyIcon }}</div>
      <p>{{ emptyMessage }}</p>
    </div>
  </div>
</template>
