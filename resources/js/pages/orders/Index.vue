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
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <div class="flex items-center justify-between">
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

            <div class="rounded-xl border bg-card text-card-foreground shadow-sm overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-muted/50">
                            <TableHead class="w-[100px]">Number</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Company</TableHead>
                            <TableHead>Manager</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Amount</TableHead>
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
                            <TableCell class="font-medium font-mono text-zinc-600 dark:text-zinc-300">
                                {{ order.order_number }}
                            </TableCell>
                            <TableCell class="text-xs text-muted-foreground">
                                {{ formatDate(order.created_at) }}
                            </TableCell>
                            <TableCell class="font-medium">{{ order.company.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ order.user.name }}</TableCell>
                            <TableCell>
                                <Badge variant="outline" :class="order.status.color">
                                    {{ order.status.label }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right font-mono font-medium">
                                {{ formatMoney(order.total_price_cents) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="mt-4 flex gap-1 justify-end">
                <Link
                    v-for="(link, k) in orders.meta.links"
                    :key="k"
                    :href="link.url ?? '#'"
                    class="px-3 py-2 border rounded-md text-sm font-medium transition-colors"
                    :class="{
                        'bg-primary text-primary-foreground border-primary': link.active,
                        'bg-background text-muted-foreground hover:bg-accent hover:text-accent-foreground': !link.active && link.url,
                        'opacity-50 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                />
            </div>

        </div>
    </AppLayout>
</template>
