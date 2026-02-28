<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    order: any;
}>();

const orderData = computed(() => props.order.data ?? props.order);

const breadcrumbs = computed(() => [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
    { title: `Order #${orderData.value.order_number}`, href: `/orders/${orderData.value.order_number}` },
]);

const formatMoney = (cents: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(cents / 100);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const resolvingStatus = ref(false);

const updateStatus = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const newStatus = select.value;

    resolvingStatus.value = true;
    router.put(`/orders/${orderData.value.order_number}/status`, {
        status: newStatus,
    }, {
        preserveScroll: true,
        onFinish: () => resolvingStatus.value = false,
    });
};
</script>

<template>
    <Head :title="`Order #${orderData.order_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">
                        Order <span class="text-zinc-500">#{{ orderData.order_number }}</span>
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Placed on {{ formatDate(orderData.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative" v-if="orderData.can?.updateStatus && orderData.status.allowed_transitions && orderData.status.allowed_transitions.length > 0">
                        <select 
                            @change="updateStatus" 
                            :value="orderData.status.value"
                            :disabled="resolvingStatus"
                            class="block w-full appearance-none rounded-md border border-sidebar-border bg-white px-4 py-2 pr-8 text-sm font-medium shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-white dark:border-zinc-700 disabled:opacity-50"
                        >
                            <option :value="orderData.status.value" disabled>
                                Current: {{ orderData.status.label }}
                            </option>
                            <option v-for="st in orderData.status.allowed_transitions" :key="st.value" :value="st.value">
                                Change to: {{ st.label }}
                            </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <div v-else>
                        <Badge :class="orderData.status.color">{{ orderData.status.label }}</Badge>
                    </div>
                </div>
            </div>

            <!-- Content grid -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Customer Details -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Customer Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Company</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-white">{{ orderData.company.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Manager (User)</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-white">{{ orderData.user.name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Order Summary</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Current Status</dt>
                                <dd class="mt-2">
                                    <Badge :class="orderData.status.color">
                                        {{ orderData.status.label }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Amount</dt>
                                <dd class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ formatMoney(orderData.total_price_cents) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Assigned Truck</dt>
                                <dd class="mt-1 text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ orderData.truck?.name || 'Not assigned' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- Items Grid -->
            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Order Items (Товари/Вантажі)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-zinc-600 dark:text-zinc-400">
                        <thead class="bg-zinc-50/50 text-xs uppercase text-zinc-500 dark:bg-zinc-800/30 dark:text-zinc-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-medium">Name</th>
                                <th scope="col" class="px-6 py-3 font-medium">Quantity</th>
                                <th scope="col" class="px-6 py-3 font-medium">Weight (kg)</th>
                                <th scope="col" class="px-6 py-3 font-medium">Volume (CBM)</th>
                                <th scope="col" class="px-6 py-3 font-medium">ADR</th>
                                <th scope="col" class="px-6 py-3 font-medium text-right" title="Declared Value (Insurance)">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in orderData.items" :key="item.id" class="border-b border-sidebar-border/70 last:border-0 hover:bg-zinc-50 dark:border-sidebar-border dark:hover:bg-zinc-800/50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                    {{ item.name }}
                                    <div class="text-[10px] text-zinc-500 mt-1" v-if="item.length_cm || item.width_cm || item.height_cm">
                                        Dims: {{ item.length_cm || '-' }}x{{ item.width_cm || '-' }}x{{ item.height_cm || '-' }} cm
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ item.quantity }}</td>
                                <td class="px-6 py-4">{{ item.weight_kg ?? '-' }}</td>
                                <td class="px-6 py-4">{{ item.cbm ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <Badge v-if="item.is_dangerous" class="bg-red-500/10 text-red-500 border-red-500/20 hover:bg-red-500/20">Yes</Badge>
                                    <span v-else class="text-zinc-500">-</span>
                                </td>
                                <td class="px-6 py-4 text-right">{{ item.declared_value_cents ? formatMoney(item.declared_value_cents) : '-' }}</td>
                            </tr>
                            <tr v-if="!orderData.items || orderData.items.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-zinc-500">No items found for this order.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status History Timeline -->
            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">Status History (Audit Trail)</h3>
                </div>
                <div class="p-6">
                    <div class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li v-for="(history, historyIdx) in orderData.status_histories" :key="history.id">
                                <div class="relative pb-8">
                                    <span v-if="historyIdx !== orderData.status_histories.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-zinc-900" :class="history.new_status_color">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" check-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                                    Status changed to <Badge :class="history.new_status_color">{{ history.new_status }}</Badge> by <span class="font-medium text-zinc-900 dark:text-white">{{ history.user.name }}</span>
                                                </p>
                                                <p v-if="history.comment" class="mt-1 text-sm text-zinc-500 italic">{{ history.comment }}</p>
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-zinc-500 dark:text-zinc-400">
                                                <time :datetime="history.created_at">{{ history.created_at }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <li v-if="!orderData.status_histories || orderData.status_histories.length === 0">
                                <p class="text-sm text-zinc-500 text-center py-4">No history recorded yet.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <Link
                    href="/orders"
                    class="rounded-md border border-sidebar-border bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 focus:outline-none dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800"
                >
                    &larr; Back to Orders
                </Link>
            </div>

        </div>
    </AppLayout>
</template>
