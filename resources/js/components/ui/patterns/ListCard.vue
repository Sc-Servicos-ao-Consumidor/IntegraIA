<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  title?: string
  subtitle?: string
  icon?: string
  badge?: string
  emptyMessage?: string
  emptyIcon?: string
  searchable?: boolean
  search?: string
  perPage?: number
  perPageOptions?: number[]
  searchPlaceholder?: string
}

const props = withDefaults(defineProps<Props>(), {
  emptyMessage: 'Nenhum item cadastrado',
  emptyIcon: '📋',
  searchable: false,
  search: '',
  perPage: 10,
  perPageOptions: () => [5, 10, 15, 25, 50],
  searchPlaceholder: 'Buscar...'
})

const emit = defineEmits<{
  (e: 'update:search', value: string): void
  (e: 'update:perPage', value: number): void
  (e: 'submit'): void
  (e: 'clear'): void
}>()
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
    <div v-if="title || subtitle || icon || $slots.toolbar || props.searchable" class="mb-6">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
          <span v-if="icon" class="text-2xl">{{ icon }}</span>
          <div>
            <h2 v-if="title" class="text-xl font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span>{{ title }}</span>
              <span
                v-if="props.badge"
                class="text-xs font-medium rounded px-2 py-0.5 border bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900 dark:text-orange-100 dark:border-orange-800"
              >
                {{ props.badge }}
              </span>
            </h2>
            <p v-if="subtitle" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
              {{ subtitle }}
            </p>
          </div>
        </div>
        <div v-if="$slots.toolbar" class="mt-1 w-full md:w-auto">
          <slot name="toolbar" />
        </div>
        <div v-else-if="props.searchable" class="mt-1 w-full md:w-auto">
          <form class="flex flex-col md:flex-row items-stretch md:items-center gap-3" @submit.prevent="emit('submit')">
            <div class="w-full md:w-80">
              <input
                :value="props.search"
                @input="emit('update:search', ($event.target as HTMLInputElement).value)"
                type="text"
                :placeholder="props.searchPlaceholder"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              />
            </div>
            <div>
              <select
                :value="props.perPage"
                @change="emit('update:perPage', Number(($event.target as HTMLSelectElement).value))"
                class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
              >
                <option v-for="opt in props.perPageOptions" :key="opt" :value="opt">{{ opt }} por página</option>
              </select>
            </div>
            <div class="flex gap-2">
              <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                Buscar
              </button>
              <button
                type="button"
                @click="emit('clear')"
                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-secondary border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-accent"
              >
                Limpar
              </button>
            </div>
          </form>
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
