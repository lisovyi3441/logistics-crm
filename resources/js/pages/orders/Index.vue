<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
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

// Приймаємо дані
defineProps<{
    orders: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
];

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('uk-UA', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 flex-1 min-h-0">

            <div class="flex-none flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Orders List</h2>
                <div class="flex items-center gap-2">
                    <Link href="/orders/create" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                        Create Order
                    </Link>
                    <div class="rounded-md bg-secondary px-3 py-1 text-sm font-medium border">
                        Total: {{ orders.meta.total }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col flex-1 min-h-0 border rounded-xl bg-card text-card-foreground shadow-sm">
                <div class="flex-1 overflow-y-auto min-h-0 rounded-t-xl">
                    <Table class="whitespace-nowrap h-full">
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center">Number</TableHead>
                                <TableHead class="text-center">Date</TableHead>
                                <TableHead class="text-center">Company</TableHead>
                                <TableHead class="text-center">Manager</TableHead>
                                <TableHead class="text-center">Status</TableHead>
                                <TableHead class="text-center">Amount</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="orders.data.length === 0">
                                <TableCell colspan="6" class="text-center py-10 text-muted-foreground">
                                    No orders found.
                                </TableCell>
                            </TableRow>

                            <TableRow v-for="order in orders.data" :key="order.id" 
                                @click="router.visit(`/orders/${order.order_number}`)"
                                class="cursor-pointer hover:bg-muted/50">
                                <TableCell class="font-medium font-mono text-zinc-600 dark:text-zinc-300 text-center">
                                    {{ order.order_number }}
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground whitespace-nowrap text-center">
                                    {{ formatDate(order.created_at) }}
                                </TableCell>
                                <TableCell class="font-medium text-center">{{ order.company.name }}</TableCell>
                                <TableCell class="text-muted-foreground text-center">{{ order.user.name }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline" :class="[order.status.color, 'whitespace-nowrap flex-shrink-0 min-w-max']">
                                        {{ order.status.label }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center font-mono font-medium">
                                    {{ formatMoney(order.total_price_cents) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex-none flex flex-wrap gap-2 justify-center p-4 border-t border-sidebar-border bg-muted/10 rounded-b-xl" v-if="orders.meta && orders.meta.last_page > 1">
                    <template v-for="(link, i) in orders.meta.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1 rounded-md border text-sm transition-colors"
                            :class="link.active
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1 rounded-md border text-sm transition-colors opacity-50 cursor-not-allowed bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
