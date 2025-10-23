<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  icon?: string
  color?: 'orange' | 'blue' | 'green' | 'purple' | 'yellow'
  removable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  color: 'orange',
  removable: true
})

const colorVariants = {
  orange: {
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400',
    button: 'text-orange-600 dark:text-slate-400 hover:text-orange-800 dark:hover:text-slate-300',
    border: 'border-orange-300 dark:border-gray-600',
    hover: 'hover:bg-orange-50 dark:hover:bg-slate-800'
  },
  blue: {
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400',
    button: 'text-orange-600 dark:text-slate-400 hover:text-orange-800 dark:hover:text-slate-300',
    border: 'border-orange-300 dark:border-gray-600',
    hover: 'hover:bg-orange-50 dark:hover:bg-slate-800'
  },
  green: {
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400',
    button: 'text-orange-600 dark:text-slate-400 hover:text-orange-800 dark:hover:text-slate-300',
    border: 'border-orange-300 dark:border-gray-600',
    hover: 'hover:bg-orange-50 dark:hover:bg-slate-800'
  },
  purple: {
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400',
    button: 'text-orange-600 dark:text-slate-400 hover:text-orange-800 dark:hover:text-slate-300',
    border: 'border-orange-300 dark:border-gray-600',
    hover: 'hover:bg-orange-50 dark:hover:bg-slate-800'
  },
  yellow: {
    icon: 'bg-orange-100 dark:bg-slate-800',
    iconText: 'text-orange-600 dark:text-slate-400',
    button: 'text-orange-600 dark:text-slate-400 hover:text-orange-800 dark:hover:text-slate-300',
    border: 'border-orange-300 dark:border-gray-600',
    hover: 'hover:bg-orange-50 dark:hover:bg-slate-800'
  }
}

const colors = colorVariants[props.color]
</script>

<template>
  <div
    :class="
      cn(
        'flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-card shadow-sm',
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
          'w-10 h-10 rounded-lg flex items-center justify-center',
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
          'text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium px-3 py-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900 transition-colors',
          colors.button
        )
      "
    >
      <slot name="remove">Remover</slot>
    </button>
  </div>
</template>
