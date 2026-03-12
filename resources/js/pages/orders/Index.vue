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

// Define props
defineProps<{
    orders: any;
    can_create: boolean;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
];

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
    <Head title="Orders" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-none items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Orders List</h2>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can_create"
                        href="/orders/create"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        Create Order
                    </Link>
                    <div
                        class="rounded-md border bg-secondary px-3 py-1 text-sm font-medium"
                    >
                        Total: {{ orders.meta.total }}
                    </div>
                </div>
            </div>

            <div
                class="flex min-h-0 flex-1 flex-col rounded-xl border bg-card text-card-foreground shadow-sm"
            >
                <div class="min-h-0 flex-1 overflow-y-auto rounded-t-xl">
                    <Table
                        class="whitespace-nowrap"
                        :class="{
                            'h-full':
                                orders.data.length >= orders.meta.per_page,
                        }"
                    >
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center"
                                    >Number</TableHead
                                >
                                <TableHead class="text-center">Date</TableHead>
                                <TableHead class="text-center"
                                    >Company</TableHead
                                >
                                <TableHead class="text-center"
                                    >Manager</TableHead
                                >
                                <TableHead class="text-center">Route</TableHead>
                                <TableHead class="text-center"
                                    >Status</TableHead
                                >
                                <TableHead class="text-center"
                                    >Amount</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="orders.data.length === 0">
                                <TableCell
                                    colspan="6"
                                    class="py-10 text-center text-muted-foreground"
                                >
                                    No orders found.
                                </TableCell>
                            </TableRow>

                            <TableRow
                                v-for="order in orders.data"
                                :key="order.id"
                                @click="
                                    router.visit(
                                        `/orders/${order.order_number}`,
                                    )
                                "
                                class="cursor-pointer hover:bg-muted/50"
                            >
                                <TableCell
                                    class="text-center font-mono font-medium text-zinc-600 dark:text-zinc-300"
                                >
                                    {{ order.order_number }}
                                </TableCell>
                                <TableCell
                                    class="text-center text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ formatDate(order.created_at) }}
                                </TableCell>
                                <TableCell class="text-center font-medium">{{
                                    order.company.name
                                }}</TableCell>
                                <TableCell
                                    class="text-center text-muted-foreground"
                                    >{{ order.user.name }}</TableCell
                                >
                                <TableCell
                                    class="text-center text-xs text-zinc-600 dark:text-zinc-400"
                                >
                                    <div
                                        class="mx-auto flex max-w-[200px] items-center justify-center gap-1 truncate"
                                        :title="`${order.pickup_address} -> ${order.delivery_address}`"
                                    >
                                        <span class="truncate">{{
                                            order.pickup_address
                                                ? order.pickup_address.split(
                                                      ',',
                                                  )[0]
                                                : 'TBD'
                                        }}</span>
                                        <svg
                                            class="h-3 w-3 flex-shrink-0 text-zinc-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 8l4 4m0 0l-4 4m4-4H3"
                                            ></path>
                                        </svg>
                                        <span class="truncate">{{
                                            order.delivery_address
                                                ? order.delivery_address.split(
                                                      ',',
                                                  )[0]
                                                : 'TBD'
                                        }}</span>
                                    </div>
                                    <div
                                        class="mt-0.5 text-[10px] text-zinc-400"
                                        v-if="order.distance_km"
                                    >
                                        {{ order.distance_km }} km
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            order.status.color,
                                            'min-w-max flex-shrink-0 whitespace-nowrap',
                                        ]"
                                    >
                                        {{ order.status.label }}
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
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    class="flex flex-none flex-wrap justify-center gap-2 rounded-b-xl border-t border-sidebar-border bg-muted/10 p-4"
                    v-if="orders.meta && orders.meta.last_page > 1"
                >
                    <template v-for="(link, i) in orders.meta.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-md border px-3 py-1 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'
                            "
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else
                            class="cursor-not-allowed rounded-md border border-zinc-200 bg-white px-3 py-1 text-sm text-zinc-700 opacity-50 transition-colors dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
