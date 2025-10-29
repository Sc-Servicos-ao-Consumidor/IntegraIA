<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const tenants = computed(() => page.props.auth?.tenant?.list ?? []);
const currentTenantId = computed(() => page.props.auth?.tenant?.current_id ?? null);
const currentTenant = computed(() => tenants.value.find((t: any) => t.id === currentTenantId.value));

const switchTenant = (tenantId: number) => {
    if (!tenantId || tenantId === currentTenantId.value) return;
    router.post(route('tenant.switch'), { tenant_id: tenantId }, { preserveScroll: true });
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 bg-white dark:bg-card"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- Header Actions -->
        <div class="flex items-center gap-3">
            <!-- Tenant Selector -->
            <div class="hidden md:flex items-center">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <button class="flex items-center gap-2 rounded-lg px-2 py-1 hover:bg-orange-100">
                            <img v-if="currentTenant?.logo_url" :src="currentTenant.logo_url" alt="Logo" class="h-6 w-6 rounded object-cover" />
                            <span v-else class="h-6 w-6 rounded bg-orange-200 text-orange-800 flex items-center justify-center text-xs">{{ (currentTenant?.name || 'T').slice(0,1) }}</span>
                            <span class="max-w-[180px] truncate text-sm text-muted-foreground" :title="currentTenant?.name || 'Selecionar tenant'">
                                {{ currentTenant?.name || 'Selecionar tenant' }}
                            </span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-64">
                        <DropdownMenuItem
                            v-for="t in tenants"
                            :key="t.id"
                            as-child
                        >
                            <button class="flex w-full items-center gap-2" @click="switchTenant(t.id)">
                                <img v-if="t.logo_url" :src="t.logo_url" alt="Logo" class="h-5 w-5 rounded object-cover" />
                                <span v-else class="h-5 w-5 rounded bg-orange-200 text-orange-800 flex items-center justify-center text-[10px]">{{ t.name.slice(0,1) }}</span>
                                <span class="truncate">{{ t.name }}</span>
                                <span v-if="currentTenantId === t.id" class="ml-auto text-xs text-muted-foreground">(Ativo)</span>
                            </button>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            
        </div>
    </header>
</template>
