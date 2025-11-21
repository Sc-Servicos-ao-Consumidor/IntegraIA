<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuBadge, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

const currentPath = computed(() => page.url.split('?')[0]);
const isActive = (href: string) => currentPath.value === href || currentPath.value.startsWith(href + '/');
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>SmartChef AI</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuBadge
                    v-if="item.badge"
                    :class="[
                        item.badgeColor === 'orange' || !item.badgeColor ? 'bg-orange-200 text-orange-900 dark:bg-orange-900 dark:text-orange-100' : '',
                        item.badgeColor === 'blue' ? 'bg-blue-200 text-blue-900 dark:bg-blue-900 dark:text-blue-100' : '',
                        item.badgeColor === 'green' ? 'bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-100' : '',
                        item.badgeColor === 'red' ? 'bg-red-200 text-red-900 dark:bg-red-900 dark:text-red-100' : '',
                        item.badgeColor === 'purple' ? 'bg-purple-200 text-purple-900 dark:bg-purple-900 dark:text-purple-100' : '',
                        item.badgeColor === 'gray' ? 'bg-gray-200 text-gray-900 dark:bg-gray-800 dark:text-gray-100' : ''
                    ]"
                >
                    {{ item.badge }}
                </SidebarMenuBadge>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
