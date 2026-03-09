<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

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
        total_price_cents: number;
        currency: string;
        created_at: string;
    }>;
}>();

const formatMoney = (cents: number, currency: string = 'UAH') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(cents / 100);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6"
        >
            <div class="grid auto-rows-min gap-6 md:grid-cols-3">
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900"
                >
                    <p
                        class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        Total Orders
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white"
                    >
                        {{ stats.totalOrders }}
                    </p>
                </div>
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900"
                >
                    <p
                        class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        Total Revenue
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white"
                    >
                        ₴{{ stats.totalRevenue }}
                    </p>
                </div>
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900"
                >
                    <p
                        class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        Active Companies
                    </p>
                    <p
                        class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white"
                    >
                        {{ stats.activeCompanies }}
                    </p>
                </div>
            </div>

            <div
                class="mt-4 flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card text-card-foreground shadow-sm dark:border-sidebar-border"
            >
                <div
                    class="border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border"
                >
                    <h3
                        class="text-lg font-medium text-zinc-900 dark:text-white"
                    >
                        Recent Orders
                    </h3>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <Table
                        class="whitespace-nowrap md:whitespace-normal"
                        :class="{ 'h-full': recentOrders.length >= 5 }"
                    >
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center font-medium"
                                    >Order Number</TableHead
                                >
                                <TableHead class="text-center font-medium"
                                    >Company</TableHead
                                >
                                <TableHead class="text-center font-medium"
                                    >Status</TableHead
                                >
                                <TableHead class="text-center font-medium"
                                    >Total Price</TableHead
                                >
                                <TableHead class="text-center font-medium"
                                    >Date</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="recentOrders.length === 0">
                                <TableCell
                                    colspan="5"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No recent orders found.
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="order in recentOrders"
                                :key="order.id"
                                @click="
                                    router.visit(
                                        `/orders/${order.order_number}`,
                                    )
                                "
                                class="cursor-pointer hover:bg-muted/50"
                            >
                                <TableCell
                                    class="text-center font-mono font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ order.order_number }}
                                </TableCell>
                                <TableCell class="text-center">{{
                                    order.company_name
                                }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            order.status_color,
                                            'min-w-max flex-shrink-0 whitespace-nowrap',
                                        ]"
                                    >
                                        {{ order.status_label }}
                                    </Badge>
                                </TableCell>
                                <TableCell
                                    class="text-center font-mono font-medium"
                                >
                                    {{
                                        formatMoney(
                                            order.total_price_cents,
                                            order.currency,
                                        )
                                    }}
                                </TableCell>
                                <TableCell
                                    class="text-center text-xs text-muted-foreground"
                                    >{{
                                        formatDate(order.created_at)
                                    }}</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
