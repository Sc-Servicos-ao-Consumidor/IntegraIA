<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

const page = usePage();
const tenants = computed(() => page.props.auth?.tenant?.list ?? []);
const currentTenantId = computed(() => page.props.auth?.tenant?.current_id ?? null);

const switchTenant = (tenantId: number) => {
    if (!tenantId || tenantId === currentTenantId.value) return;
    router.post(route('tenant.switch'), { tenant_id: tenantId }, { preserveScroll: true });
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup v-if="tenants.length > 0">
        <DropdownMenuLabel>Tenants</DropdownMenuLabel>
        <DropdownMenuItem
            v-for="t in tenants"
            :key="t.id"
            as-child
        >
            <button class="flex w-full items-center justify-between" @click="switchTenant(t.id)">
                <span>{{ t.name }}</span>
                <span v-if="currentTenantId === t.id" class="ml-2 text-xs text-muted-foreground">(Ativo)</span>
            </button>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator v-if="tenants.length > 0" />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
