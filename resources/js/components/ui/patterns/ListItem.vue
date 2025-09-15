<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  description?: string
  icon?: string
  status?: 'active' | 'inactive' | 'discontinued'
  badges?: Array<{ text: string; color: 'gray' | 'blue' | 'green' | 'red' | 'yellow' | 'purple' | 'orange' }>
  actions?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  status: 'active',
  actions: true
})

const statusVariants = {
  active: '',
  inactive: 'bg-red-50 text-red-700 px-2 py-1 rounded text-xs border border-red-200',
  discontinued: 'bg-yellow-50 text-yellow-700 px-2 py-1 rounded text-xs border border-yellow-200'
}

const badgeVariants = {
  gray: 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-gray-700',
  blue: 'bg-orange-50 dark:bg-slate-800 text-orange-700 dark:text-slate-300 border-orange-200 dark:border-gray-700',
  green: 'bg-orange-50 dark:bg-slate-800 text-orange-700 dark:text-slate-300 border-orange-200 dark:border-gray-700',
  red: 'bg-red-50 dark:bg-slate-800 text-red-700 dark:text-slate-300 border-red-200 dark:border-gray-700',
  yellow: 'bg-orange-50 dark:bg-slate-800 text-orange-700 dark:text-slate-300 border-orange-200 dark:border-gray-700',
  purple: 'bg-orange-50 dark:bg-slate-800 text-orange-700 dark:text-slate-300 border-orange-200 dark:border-gray-700',
  orange: 'bg-orange-50 dark:bg-slate-800 text-orange-700 dark:text-slate-300 border-orange-200 dark:border-gray-700'
}
</script>

<template>
  <div
    :class="
      cn(
        'border border-gray-200 dark:border-gray-700 rounded-md p-4 bg-white dark:bg-card shadow-sm',
        props.class
      )
    "
  >
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <!-- Header with title and badges -->
        <div class="flex items-center gap-2 mb-2">
          <h3 class="font-medium text-slate-900 dark:text-slate-100">{{ title }}</h3>
          
          <!-- Status Badge -->
          <span 
            v-if="status !== 'active'"
            :class="statusVariants[status]"
          >
            {{ status === 'inactive' ? 'INATIVO' : 'DESCONTINUADO' }}
          </span>
          
          <!-- Custom Badges -->
          <span 
            v-for="badge in badges"
            :key="badge.text"
            :class="
              cn(
                'px-2 py-1 rounded text-xs border',
                badgeVariants[badge.color]
              )
            "
          >
            {{ badge.text }}
          </span>
        </div>
        
        <!-- Subtitle -->
        <div v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 space-y-1 mb-2">
          {{ subtitle }}
        </div>
        
        <!-- Description -->
        <p v-if="description" class="text-sm text-slate-600 dark:text-slate-400 mt-2 line-clamp-2">
          {{ description }}
        </p>
        
        <!-- Associations Info -->
        <div v-if="$slots.associations" class="mt-3 space-y-2">
          <slot name="associations" />
        </div>
      </div>
      
      <!-- Actions -->
      <div v-if="actions" class="flex gap-2 ml-4">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
