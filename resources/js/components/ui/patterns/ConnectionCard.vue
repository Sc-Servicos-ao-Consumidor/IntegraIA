<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  color?: 'orange' | 'blue' | 'green' | 'purple'
  type?: 'recipe' | 'product' | 'content' | 'ingredient'
}

const props = withDefaults(defineProps<Props>(), {
  color: 'orange',
  type: 'recipe'
})

const typeConfig = {
  recipe: { icon: '🍳', color: 'orange' },
  product: { icon: '🛒', color: 'blue' },
  content: { icon: '📄', color: 'blue' },
  ingredient: { icon: '🥕', color: 'green' }
}

const colorVariants = {
  orange: {
    bg: 'bg-orange-100',
    text: 'text-orange-900',
    border: 'border-orange-200',
    icon: 'bg-orange-100',
    iconText: 'text-orange-600'
  },
  blue: {
    bg: 'bg-blue-100',
    text: 'text-blue-900',
    border: 'border-blue-200',
    icon: 'bg-blue-100',
    iconText: 'text-blue-600'
  },
  green: {
    bg: 'bg-green-100',
    text: 'text-green-900',
    border: 'border-green-200',
    icon: 'bg-green-100',
    iconText: 'text-green-600'
  },
  purple: {
    bg: 'bg-purple-100',
    text: 'text-purple-900',
    border: 'border-purple-200',
    icon: 'bg-purple-100',
    iconText: 'text-purple-600'
  }
}

const config = typeConfig[props.type]
const colors = colorVariants[config.color]
</script>

<template>
  <div
    :class="
      cn(
        'bg-gray-50 p-4 rounded-lg',
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
        <h4 class="text-sm font-semibold">{{ title || `${type} Connections` }}</h4>
      </div>
      <p v-if="subtitle" class="text-xs mt-1 opacity-80">{{ subtitle }}</p>
    </div>

    <!-- Content -->
    <div class="space-y-3">
      <p class="text-sm font-medium text-gray-700">
        {{ title || `${type}s` }} Vinculados
      </p>
      <div class="space-y-2">
        <slot />
      </div>
    </div>
  </div>
</template>
