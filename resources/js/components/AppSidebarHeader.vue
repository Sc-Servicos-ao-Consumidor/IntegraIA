<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Search, Plus } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 bg-gradient-to-r from-orange-50 to-amber-50"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- Header Actions -->
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-1 text-sm text-muted-foreground">
                <span class="font-medium">🍳</span>
                <span>Sistema Inteligente de Receitas</span>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <button 
                    class="p-2 hover:bg-orange-100 rounded-lg transition-colors"
                    title="Busca Rápida"
                >
                    <Search class="h-4 w-4" />
                </button>
                <button 
                    class="p-2 hover:bg-orange-100 rounded-lg transition-colors"
                    title="Nova Receita"
                >
                    <Plus class="h-4 w-4" />
                </button>
            </div>
        </div>
    </header>
</template>
