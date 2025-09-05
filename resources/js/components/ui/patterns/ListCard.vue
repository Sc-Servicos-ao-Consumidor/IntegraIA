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
        'bg-white rounded-lg shadow-sm border border-gray-200 p-8 min-h-[400px]',
        props.class
      )
    "
  >
    <!-- Header Section -->
    <div v-if="title || subtitle || icon" class="mb-6">
      <div class="flex items-center gap-3">
        <span v-if="icon" class="text-xl">{{ icon }}</span>
        <div>
          <h2 v-if="title" class="text-lg font-semibold text-gray-900">
            {{ title }}
          </h2>
          <p v-if="subtitle" class="text-sm text-gray-600 mt-1">
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
    <div v-else class="text-center py-8 text-gray-500">
      <div class="text-4xl mb-2">{{ emptyIcon }}</div>
      <p>{{ emptyMessage }}</p>
    </div>
  </div>
</template>
