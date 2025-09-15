<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  color?: 'orange' | 'blue' | 'green' | 'purple' | 'yellow'
  type?: 'recipe' | 'product' | 'content' | 'ingredient'
}

const props = withDefaults(defineProps<Props>(), {
  color: 'orange',
  type: 'recipe'
})

const typeConfig = {
  recipe: { icon: '🍳', color: 'orange' },
  product: { icon: '🛒', color: 'yellow' },
  content: { icon: '📄', color: 'blue' },
  ingredient: { icon: '🥕', color: 'green' }
}

const colorVariants = {
  orange: {
    bg: 'bg-orange-100 dark:bg-slate-800',
    text: 'text-orange-900 dark:text-slate-100',
    border: 'border-orange-200 dark:border-gray-700',
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400'
  },
  blue: {
    bg: 'bg-orange-100 dark:bg-slate-800',
    text: 'text-orange-900 dark:text-slate-100',
    border: 'border-orange-200 dark:border-gray-700',
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400'
  },
  green: {
    bg: 'bg-orange-100 dark:bg-slate-800',
    text: 'text-orange-900 dark:text-slate-100',
    border: 'border-orange-200 dark:border-gray-700',
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400'
  },
  purple: {
    bg: 'bg-orange-100 dark:bg-slate-800',
    text: 'text-orange-900 dark:text-slate-100',
    border: 'border-orange-200 dark:border-gray-700',
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400'
  },
  yellow: {
    bg: 'bg-orange-100 dark:bg-slate-800',
    text: 'text-orange-900 dark:text-slate-100',
    border: 'border-orange-200 dark:border-gray-700',
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400'
  }
}

const config = typeConfig[props.type]
const colors = colorVariants[config.color]
</script>

<template>
  <div
    :class="
      cn(
        'bg-white dark:bg-card p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700',
        props.class
      )
    "
  >
    <!-- Header Section -->
    <div 
      :class="
        cn(
          'px-4 py-2 rounded-md mb-4',
          colors.bg,
          colors.text
        )
      "
    >
      <div class="flex items-center gap-2">
        <span class="text-lg">{{ config.icon }}</span>
        <h4 class="text-base font-bold">{{ title || `${type} Connections` }}</h4>
      </div>
      <p v-if="subtitle" class="text-xs mt-1 opacity-80">{{ subtitle }}</p>
    </div>

    <!-- Content -->
    <div class="space-y-3">
      <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
        {{ title || `${type}s` }} Vinculados
      </p>
      <div class="space-y-2">
        <slot />
      </div>
    </div>
  </div>
</template>
