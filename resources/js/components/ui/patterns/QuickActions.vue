<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  icon?: string
}

const props = withDefaults(defineProps<Props>(), {
  title: '⚡ Ações Rápidas',
  icon: '⚡'
})
</script>

<template>
  <div
    :class="
      cn(
        'bg-white dark:bg-card rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6',
        props.class
      )
    "
  >
    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-4">{{ title }}</h2>
    
    <div class="flex gap-3 flex-wrap">
      <slot />
    </div>
  </div>
</template>
