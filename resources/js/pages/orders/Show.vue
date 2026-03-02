<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import L from 'leaflet';
import { MapPin } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import 'leaflet/dist/leaflet.css';

const props = defineProps<{
    order: any;
    trucks?: Array<{ id: number; name: string }>;
    is_admin?: boolean;
}>();

const orderData = computed(() => props.order.data ?? props.order);
const trucksList = computed(() => props.trucks ?? []);
const isAdmin = computed(() => props.is_admin ?? false);

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

const assigningTruck = ref(false);

const assignTruck = (event: Event) => {
    const select = event.target as HTMLSelectElement;
    const currentTruckId = orderData.value.truck?.id;
    const newTruckId = select.value;
    
    if (newTruckId == currentTruckId) return;

    assigningTruck.value = true;
    router.put(`/orders/${orderData.value.order_number}/assign-truck`, {
        truck_id: newTruckId,
    }, {
        preserveScroll: true,
        onFinish: () => assigningTruck.value = false,
        onError: (err) => {
            alert(err.truck_id || 'Failed to assign truck.');
            select.value = currentTruckId || '';
        }
    });
};

const mapContainer = ref<HTMLElement | null>(null);

onMounted(() => {
    // Only initialize map if we have coordinates
    if (orderData.value.pickup_lat && orderData.value.delivery_lat && mapContainer.value) {
        
        // Fix for Leaflet default icon paths in Vite/Vue
        delete (L.Icon.Default.prototype as any)._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        const map = L.map(mapContainer.value);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const pickup = [orderData.value.pickup_lat, orderData.value.pickup_lng] as [number, number];
        const delivery = [orderData.value.delivery_lat, orderData.value.delivery_lng] as [number, number];

        // Add markers
        L.marker(pickup).addTo(map).bindPopup('<b>Pickup</b><br>' + orderData.value.pickup_address);
        L.marker(delivery).addTo(map).bindPopup('<b>Delivery</b><br>' + orderData.value.delivery_address);

        // Fetch route geometry to draw the polyline smoothly instead of a straight line
        // Since OSRM Service calculates it on the backend, we can just request it from public OSRM for frontend display
        // or just draw a straight line if it's too complex. For simplicity and instant rendering, we'll ask OSRM public API directly.
        fetch(`https://router.project-osrm.org/route/v1/driving/${orderData.value.pickup_lng},${orderData.value.pickup_lat};${orderData.value.delivery_lng},${orderData.value.delivery_lat}?overview=full&geometries=geojson`)
            .then(res => res.json())
            .then(data => {
                if (data.routes && data.routes[0]) {
                    const geojson = data.routes[0].geometry;
                    const routeLine = L.geoJSON(geojson, {
                        style: { color: '#4f46e5', weight: 4, opacity: 0.7 }
                    }).addTo(map);
                    map.fitBounds(routeLine.getBounds(), { padding: [50, 50] });
                } else {
                    // Fallback to straight line
                    const line = L.polyline([pickup, delivery], { color: '#4f46e5', weight: 4, dashArray: '10, 10' }).addTo(map);
                    map.fitBounds(line.getBounds(), { padding: [50, 50] });
                }
            })
            .catch(() => {
                const line = L.polyline([pickup, delivery], { color: '#4f46e5', weight: 4, dashArray: '10, 10' }).addTo(map);
                map.fitBounds(line.getBounds(), { padding: [50, 50] });
            });
    }
});
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
                <!-- Observer Details -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Observer Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Company</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-white">{{ orderData.company.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Manager (User)</dt>
                                <dd class="mt-1 font-medium text-zinc-900 dark:text-white">{{ orderData.user.name }}</dd>
                            </div>
                        </dl>
                        <div class="border-t border-sidebar-border/70 dark:border-sidebar-border pt-4">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Logistics Route</dt>
                            <dd class="mt-1 flex items-center gap-2 font-medium text-zinc-900 dark:text-white">
                                <span class="truncate" :title="orderData.pickup_address">{{ orderData.pickup_address ? orderData.pickup_address.split(',')[0] : 'TBD' }}</span>
                                <svg class="w-4 h-4 text-zinc-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                <span class="truncate" :title="orderData.delivery_address">{{ orderData.delivery_address ? orderData.delivery_address.split(',')[0] : 'TBD' }}</span>
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 flex justify-between items-center dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Order Summary (Receipt)</h3>
                        <Badge :class="orderData.status.color">
                            {{ orderData.status.label }}
                        </Badge>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Requested Vehicle</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ orderData.vehicle_type?.name || 'Any suitable' }}</dd>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Assigned Truck (License Plate)</dt>
                                <dd v-if="isAdmin && ['new', 'pending'].includes(orderData.status.value)">
                                    <select 
                                        @change="assignTruck" 
                                        :value="orderData.truck?.id || ''"
                                        :disabled="assigningTruck"
                                        class="block appearance-none rounded-md border border-sidebar-border bg-white px-3 py-1 text-sm font-medium shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:bg-zinc-900 dark:text-white dark:border-zinc-700 disabled:opacity-50"
                                    >
                                        <option value="" disabled>{{ orderData.truck ? 'Change Truck' : 'Assign a Truck' }}</option>
                                        <option v-for="t in trucksList" :key="t.id" :value="t.id">
                                            {{ t.name }}
                                        </option>
                                    </select>
                                </dd>
                                <dd v-else class="font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ orderData.truck?.name || 'Waiting for assignment' }}
                                </dd>
                            </div>
                            
                            <hr class="border-sidebar-border/70 dark:border-sidebar-border my-3" />
                            
                            <!-- Pricing Breakdown -->
                            <div class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">Base Freight Rate (Distance/Weight)</dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ formatMoney(orderData.base_price_cents) }}</dd>
                            </div>
                            
                            <div v-if="orderData.insurance_fee_cents > 0" class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">
                                    Insurance Fee (1% Declared Value)
                                </dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ formatMoney(orderData.insurance_fee_cents) }}</dd>
                            </div>
                            
                            <div v-if="orderData.surcharge_cents > 0" class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">
                                    ADR / Dangerous Goods Surcharge
                                </dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ formatMoney(orderData.surcharge_cents) }}</dd>
                            </div>

                            <div v-if="orderData.tax_cents > 0" class="flex items-center justify-between">
                                <dt class="text-zinc-500 dark:text-zinc-400">
                                    Taxes & Fees (VAT)
                                </dt>
                                <dd class="font-medium text-zinc-900 dark:text-white">{{ formatMoney(orderData.tax_cents) }}</dd>
                            </div>
                            
                            <hr class="border-sidebar-border/70 dark:border-sidebar-border my-3" />

                            <div class="flex items-center justify-between pt-1">
                                <dt class="text-base font-bold text-zinc-900 dark:text-white">Total Amount</dt>
                                <dd class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ formatMoney(orderData.total_price_cents) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- Interactive Map Section -->
            <div v-if="orderData.pickup_lat && orderData.delivery_lat" class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 flex justify-between items-center dark:border-sidebar-border dark:bg-zinc-800/50">
                    <h3 class="font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                        <MapPin class="w-5 h-5 text-indigo-500" />
                        Logistics Route
                    </h3>
                    <div class="text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 px-3 py-1 rounded-full">
                        {{ orderData.distance_km }} km (~ {{ orderData.transit_time_minutes > 60 ? Math.floor(orderData.transit_time_minutes/60) + 'h ' + (orderData.transit_time_minutes%60) + 'm' : orderData.transit_time_minutes + ' min' }})
                    </div>
                </div>
                
                <div class="grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x border-b border-sidebar-border/70">
                    <div class="p-4 text-sm">
                        <span class="block text-zinc-500 text-xs uppercase font-bold mb-1 pt-1">Origin</span>
                        <div class="text-zinc-900 dark:text-zinc-200 font-medium">{{ orderData.pickup_address }}</div>
                    </div>
                    <div class="p-4 text-sm md:col-span-2">
                        <span class="block text-zinc-500 text-xs uppercase font-bold mb-1 pt-1">Destination</span>
                        <div class="text-zinc-900 dark:text-zinc-200 font-medium">{{ orderData.delivery_address }}</div>
                    </div>
                </div>

                <!-- Leaflet container -->
                <div class="w-full h-96 z-0 bg-zinc-100 dark:bg-zinc-800" ref="mapContainer"></div>
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
                                                    <template v-if="history.old_status && history.old_status === history.new_status">
                                                        <span class="font-medium text-zinc-900 dark:text-white">{{ history.user.name }}</span> updated the order
                                                    </template>
                                                    <template v-else>
                                                        Status changed to <Badge :class="history.new_status_color">{{ history.new_status }}</Badge> by <span class="font-medium text-zinc-900 dark:text-white">{{ history.user.name }}</span>
                                                    </template>
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
