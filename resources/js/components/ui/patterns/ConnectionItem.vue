<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  icon?: string
  color?: 'orange' | 'blue' | 'green' | 'purple'
  removable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  color: 'orange',
  removable: true
})

const colorVariants = {
  orange: {
    icon: 'bg-orange-100',
    iconText: 'text-orange-600',
    button: 'text-orange-600 hover:text-orange-800',
    border: 'border-orange-300',
    hover: 'hover:bg-orange-50'
  },
  blue: {
    icon: 'bg-blue-100',
    iconText: 'text-blue-600',
    button: 'text-blue-600 hover:text-blue-800',
    border: 'border-blue-300',
    hover: 'hover:bg-blue-50'
  },
  green: {
    icon: 'bg-green-100',
    iconText: 'text-green-600',
    button: 'text-green-600 hover:text-green-800',
    border: 'border-green-300',
    hover: 'hover:bg-green-50'
  },
  purple: {
    icon: 'bg-purple-100',
    iconText: 'text-purple-600',
    button: 'text-purple-600 hover:text-purple-800',
    border: 'border-purple-300',
    hover: 'hover:bg-purple-50'
  }
}

const colors = colorVariants[props.color]
</script>

<template>
  <div
    :class="
      cn(
        'flex items-center gap-3 p-3 border border-gray-200 rounded bg-white',
        colors.hover,
        'transition-colors',
        props.class
      )
    "
  >
    <!-- Icon -->
    <div 
      :class="
        cn(
          'w-8 h-8 rounded flex items-center justify-center',
          colors.icon
        )
      "
    >
      <svg 
        :class="cn('w-4 h-4', colors.iconText)" 
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <slot name="icon">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </slot>
      </svg>
    </div>
    
    <!-- Content -->
    <div class="flex-1">
      <slot />
    </div>
    
    <!-- Actions -->
    <div v-if="$slots.actions" class="flex items-center gap-2">
      <slot name="actions" />
    </div>
    
    <!-- Remove Button -->
    <button 
      v-if="removable && $slots.remove"
      type="button"
      :class="
        cn(
          'text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors',
          colors.button
        )
      "
    >
      <slot name="remove">Remover</slot>
    </button>
  </div>
</template>
