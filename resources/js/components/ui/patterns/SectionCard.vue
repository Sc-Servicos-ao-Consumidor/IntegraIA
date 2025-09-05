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
  default: 'bg-white',
  gray: 'bg-gray-50',
  colored: ''
}

const colorVariants = {
  orange: 'bg-orange-50 border-orange-200',
  blue: 'bg-blue-50 border-blue-200',
  green: 'bg-green-50 border-green-200',
  purple: 'bg-purple-50 border-purple-200'
}

const headerColorVariants = {
  orange: 'bg-orange-100 text-orange-900',
  blue: 'bg-blue-100 text-blue-900',
  green: 'bg-green-100 text-green-900',
  purple: 'bg-purple-100 text-purple-900'
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
          <h3 v-if="title" class="text-base font-semibold text-gray-900">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-sm text-gray-600 mt-1">
            {{ subtitle }}
          </p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <slot />
  </div>
</template>
