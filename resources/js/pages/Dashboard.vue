<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

defineProps<{
    stats: {
        totalOrders: number;
        totalRevenue: string;
        activeCompanies: number;
    };
    recentOrders: Array<{
        id: number;
        order_number: string;
        company_name: string;
        status: string;
        status_label: string;
        status_color: string;
        total_price: string;
        currency: string;
        created_at: string;
    }>;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="grid auto-rows-min gap-6 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Orders</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ stats.totalOrders }}</p>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Revenue</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">${{ stats.totalRevenue }}</p>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active Companies</p>
                    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ stats.activeCompanies }}</p>
                </div>
            </div>
            
            <div class="mt-4 flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                <div class="border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                        <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Order Number</th>
                                <th scope="col" class="px-6 py-3 font-medium">Company</th>
                                <th scope="col" class="px-6 py-3 font-medium">Status</th>
                                <th scope="col" class="px-6 py-3 font-medium">Total Price</th>
                                <th scope="col" class="px-6 py-3 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in recentOrders" :key="order.id" 
                                @click="router.visit(`/orders/${order.order_number}`)"
                                class="cursor-pointer border-b border-sidebar-border/70 last:border-0 hover:bg-zinc-50 dark:border-sidebar-border dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-6 py-4">{{ order.company_name }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', order.status_color]">
                                        {{ order.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">${{ order.total_price }}</td>
                                <td class="px-6 py-4">{{ order.created_at }}</td>
                            </tr>
                            <tr v-if="recentOrders.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-zinc-500">No recent orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
