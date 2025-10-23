<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  variant?: 'default' | 'gray' | 'colored'
  color?: 'orange' | 'blue' | 'green' | 'purple'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  color: 'orange'
})

const sectionVariants = {
  default: 'bg-white dark:bg-card',
  gray: 'bg-gray-50 dark:bg-slate-800',
  colored: ''
}

const colorVariants = {
  orange: 'bg-orange-50 dark:bg-slate-800 border-orange-200 dark:border-gray-700',
  blue: 'bg-orange-50 dark:bg-slate-800 border-orange-200 dark:border-gray-700',
  green: 'bg-orange-50 dark:bg-slate-800 border-orange-200 dark:border-gray-700',
  purple: 'bg-orange-50 dark:bg-slate-800 border-orange-200 dark:border-gray-700'
}

const headerColorVariants = {
  orange: 'bg-orange-100 dark:bg-slate-800 text-orange-900 dark:text-slate-100',
  blue: 'bg-orange-100 dark:bg-slate-800 text-orange-900 dark:text-slate-100',
  green: 'bg-orange-100 dark:bg-slate-800 text-orange-900 dark:text-slate-100',
  purple: 'bg-orange-100 dark:bg-slate-800 text-orange-900 dark:text-slate-100'
}
</script>

<template>
  <div
    :class="
      cn(
        'rounded-lg p-4',
        sectionVariants[variant],
        variant === 'colored' ? colorVariants[color] : '',
        props.class
      )
    "
  >
    <!-- Header Section -->
    <div v-if="title || subtitle || icon" class="mb-4">
      <div 
        v-if="variant === 'colored'"
        :class="
          cn(
            'px-4 py-2 rounded-md mb-4',
            headerColorVariants[color]
          )
        "
      >
        <div class="flex items-center gap-2">
          <span v-if="icon" class="text-lg">{{ icon }}</span>
          <h4 class="text-sm font-semibold">{{ title }}</h4>
        </div>
        <p v-if="subtitle" class="text-xs mt-1 opacity-80">{{ subtitle }}</p>
      </div>
      
      <div v-else class="flex items-center gap-3">
        <span v-if="icon" class="text-xl">{{ icon }}</span>
        <div>
          <h3 v-if="title" class="text-base font-semibold text-slate-900 dark:text-slate-100">
            {{ title }}
          </h3>
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
