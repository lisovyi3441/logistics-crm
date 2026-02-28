<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

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

const formatMoney = (val: string | number) => {
    // total_price_cents is probably formatted as string from controller without cents conversion here, or is it already string?
    // Wait, let's look at the original code. It was `<td class="px-6 py-4">${{ order.total_price }}</td>`.
    // And let's not break how the user sees it, just output `$${val}` or what was there.
};

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

            <div class="mt-4 flex-1 min-h-0 flex flex-col rounded-xl border border-sidebar-border/70 bg-card text-card-foreground shadow-sm dark:border-sidebar-border overflow-hidden">
                <div class="border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Recent Orders</h3>
                </div>
                <div class="flex-1 overflow-y-auto min-h-0">
                    <Table class="whitespace-nowrap md:whitespace-normal h-full">
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="font-medium text-center">Order Number</TableHead>
                                <TableHead class="font-medium text-center">Company</TableHead>
                                <TableHead class="font-medium text-center">Status</TableHead>
                                <TableHead class="font-medium text-center">Total Price</TableHead>
                                <TableHead class="font-medium text-center">Date</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="recentOrders.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    No recent orders found.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="order in recentOrders" :key="order.id"
                                @click="router.visit(`/orders/${order.order_number}`)"
                                class="cursor-pointer hover:bg-muted/50">
                                <TableCell class="font-medium font-mono text-zinc-900 dark:text-white text-center">
                                    {{ order.order_number }}
                                </TableCell>
                                <TableCell class="text-center">{{ order.company_name }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline" :class="[order.status_color, 'whitespace-nowrap flex-shrink-0 min-w-max']">
                                        {{ order.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center font-mono font-medium">${{ order.total_price }}</TableCell>
                                <TableCell class="text-xs text-muted-foreground text-center">{{ order.created_at }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
