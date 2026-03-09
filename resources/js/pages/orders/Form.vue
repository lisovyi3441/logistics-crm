<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { OpenStreetMapProvider } from 'leaflet-geosearch';
import { Plus, Trash2, MapPin } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const geocoder = new OpenStreetMapProvider();
const pickupSearch = ref('');
const deliverySearch = ref('');
const pickupResults = ref<any[]>([]);
const deliveryResults = ref<any[]>([]);
const isSearchingPickup = ref(false);
const isSearchingDelivery = ref(false);
const showPickupDropdown = ref(false);
const showDeliveryDropdown = ref(false);

const searchAddress = async (query: string, type: 'pickup' | 'delivery') => {
    if (query.length < 3) return;

    if (type === 'pickup') {
        isSearchingPickup.value = true;
        showPickupDropdown.value = true;
        const results = await geocoder.search({ query });
        pickupResults.value = results;
        isSearchingPickup.value = false;
    } else {
        isSearchingDelivery.value = true;
        showDeliveryDropdown.value = true;
        const results = await geocoder.search({ query });
        deliveryResults.value = results;
        isSearchingDelivery.value = false;
    }
};

let pickupTimeout: NodeJS.Timeout;
let deliveryTimeout: NodeJS.Timeout;

watch(pickupSearch, (newVal) => {
    if (newVal === form.pickup_address) return; // Skip if changed by selection
    clearTimeout(pickupTimeout);
    pickupTimeout = setTimeout(() => searchAddress(newVal, 'pickup'), 300);
});

watch(deliverySearch, (newVal) => {
    if (newVal === form.delivery_address) return; // Skip if changed by selection
    clearTimeout(deliveryTimeout);
    deliveryTimeout = setTimeout(() => searchAddress(newVal, 'delivery'), 300);
});

const selectAddress = (result: any, type: 'pickup' | 'delivery') => {
    if (type === 'pickup') {
        form.pickup_address = result.label;
        form.pickup_lat = result.y;
        form.pickup_lng = result.x;
        pickupSearch.value = result.label;
        pickupResults.value = [];
        showPickupDropdown.value = false;
    } else {
        form.delivery_address = result.label;
        form.delivery_lat = result.y;
        form.delivery_lng = result.x;
        deliverySearch.value = result.label;
        deliveryResults.value = [];
        showDeliveryDropdown.value = false;
    }
};

const hideDropdown = (type: 'pickup' | 'delivery') => {
    setTimeout(() => {
        if (type === 'pickup') showPickupDropdown.value = false;
        if (type === 'delivery') showDeliveryDropdown.value = false;
    }, 200);
};

const handleEnter = (type: 'pickup' | 'delivery', event: Event) => {
    event.preventDefault();
    if (type === 'pickup' && pickupResults.value.length > 0) {
        selectAddress(pickupResults.value[0], 'pickup');
    } else if (type === 'delivery' && deliveryResults.value.length > 0) {
        selectAddress(deliveryResults.value[0], 'delivery');
    }
    hideDropdown(type);
};

const props = defineProps<{
    companies: Array<{ id: number; name: string }>;
    vehicleTypes: Array<{
        id: number;
        name: string;
        max_weight_kg: number;
        max_volume_cbm: number;
    }>;
    is_admin: boolean;
    default_company_id?: number;
}>();

const form = useForm({
    company_id: props.default_company_id || '',
    vehicle_type_id: '',
    notes: '',
    pickup_address: '',
    pickup_lat: null as number | null,
    pickup_lng: null as number | null,
    delivery_address: '',
    delivery_lat: null as number | null,
    delivery_lng: null as number | null,
    items: [
        {
            name: '',
            quantity: 1,
            weight_kg: 0,
            declared_value_cents: 0,
            cbm: 0,
            length_cm: '',
            width_cm: '',
            height_cm: '',
            is_dangerous: false,
        },
    ],
});

const addItem = () => {
    form.items.push({
        name: '',
        quantity: 1,
        weight_kg: 0,
        declared_value_cents: 0,
        cbm: 0,
        length_cm: '',
        width_cm: '',
        height_cm: '',
        is_dangerous: false,
    });
};

const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    form.post('/orders');
};
</script>

<template>
    <Head title="Create Order" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Orders', href: '/orders' },
            { title: 'Create', href: '#' },
        ]"
    >
        <div
            class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-4 p-4"
        >
            <div class="mb-2 flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">
                    Create New Order
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Order Info -->
                <div
                    class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <h3 class="mb-4 border-b pb-2 text-lg font-semibold">
                        Order Information
                    </h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div v-if="is_admin">
                            <label
                                for="company_id"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Company *</label
                            >
                            <select
                                id="company_id"
                                v-model="form.company_id"
                                required
                                class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                                <option value="" disabled>
                                    Select a company
                                </option>
                                <option
                                    v-for="company in companies"
                                    :key="company.id"
                                    :value="company.id"
                                >
                                    {{ company.name }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.company_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.company_id }}
                            </p>
                        </div>
                        <div v-else>
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Company</label
                            >
                            <div
                                class="mt-1 block w-full rounded-md border border-transparent bg-zinc-100 px-3 py-2 text-sm text-zinc-500 dark:bg-zinc-800"
                            >
                                (Your company will be assigned automatically)
                            </div>
                        </div>

                        <div>
                            <label
                                for="vehicle_type_id"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Requested Vehicle Type (Optional)</label
                            >
                            <select
                                id="vehicle_type_id"
                                v-model="form.vehicle_type_id"
                                class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                                <option value="">Any vehicle format</option>
                                <option
                                    v-for="vt in vehicleTypes"
                                    :key="vt.id"
                                    :value="vt.id"
                                >
                                    {{ vt.name }} (Max: {{ vt.max_weight_kg }}kg
                                    /
                                    {{ vt.max_volume_cbm || 'Unlimited' }} cbm)
                                </option>
                            </select>
                            <p
                                v-if="form.errors.vehicle_type_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.vehicle_type_id }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label
                                for="notes"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Notes (Optional)</label
                            >
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="1"
                                class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            ></textarea>
                            <p
                                v-if="form.errors.notes"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Locational Data -->
                <div
                    class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <h3
                        class="mb-4 flex items-center gap-2 border-b pb-2 text-lg font-semibold"
                    >
                        <MapPin class="h-5 w-5 text-indigo-500" />
                        Route Details
                    </h3>
                    <div class="relative grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Origin Input -->
                        <div class="relative">
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Pickup Address *</label
                            >
                            <input
                                type="text"
                                v-model="pickupSearch"
                                @focus="showPickupDropdown = true"
                                @blur="hideDropdown('pickup')"
                                @keydown.esc="showPickupDropdown = false"
                                @keydown.enter="(e) => handleEnter('pickup', e)"
                                placeholder="Search for origin..."
                                class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <input
                                type="hidden"
                                v-model="form.pickup_address"
                            />

                            <!-- Loading Indicator -->
                            <div
                                v-if="isSearchingPickup"
                                class="absolute z-10 mt-1 w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                Searching...
                            </div>

                            <!-- Origin Autocomplete Results -->
                            <div
                                v-if="
                                    !isSearchingPickup &&
                                    showPickupDropdown &&
                                    pickupResults.length > 0
                                "
                                class="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-md border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <ul class="py-1">
                                    <li
                                        v-for="result in pickupResults"
                                        :key="result.x"
                                        @click="selectAddress(result, 'pickup')"
                                        class="cursor-pointer border-b px-3 py-2 text-sm text-zinc-700 last:border-0 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                    >
                                        {{ result.label }}
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="
                                    !isSearchingPickup &&
                                    showPickupDropdown &&
                                    pickupSearch.length >= 3 &&
                                    pickupResults.length === 0
                                "
                                class="absolute z-10 mt-1 w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                No results found
                            </div>
                            <!-- Error display -->
                            <p
                                v-if="form.errors.pickup_address"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.pickup_address }}
                            </p>
                        </div>

                        <!-- Destination Input -->
                        <div class="relative">
                            <label
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >Delivery Address *</label
                            >
                            <input
                                type="text"
                                v-model="deliverySearch"
                                @focus="showDeliveryDropdown = true"
                                @blur="hideDropdown('delivery')"
                                @keydown.esc="showDeliveryDropdown = false"
                                @keydown.enter="
                                    (e) => handleEnter('delivery', e)
                                "
                                placeholder="Search for destination..."
                                class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            />
                            <input
                                type="hidden"
                                v-model="form.delivery_address"
                            />

                            <!-- Loading Indicator -->
                            <div
                                v-if="isSearchingDelivery"
                                class="absolute z-10 mt-1 w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                Searching...
                            </div>

                            <!-- Destination Autocomplete Results -->
                            <div
                                v-if="
                                    !isSearchingDelivery &&
                                    showDeliveryDropdown &&
                                    deliveryResults.length > 0
                                "
                                class="absolute z-10 mt-1 max-h-48 w-full overflow-auto rounded-md border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <ul class="py-1">
                                    <li
                                        v-for="result in deliveryResults"
                                        :key="result.x"
                                        @click="
                                            selectAddress(result, 'delivery')
                                        "
                                        class="cursor-pointer border-b px-3 py-2 text-sm text-zinc-700 last:border-0 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                    >
                                        {{ result.label }}
                                    </li>
                                </ul>
                            </div>
                            <div
                                v-if="
                                    !isSearchingDelivery &&
                                    showDeliveryDropdown &&
                                    deliverySearch.length >= 3 &&
                                    deliveryResults.length === 0
                                "
                                class="absolute z-10 mt-1 w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-500 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                No results found
                            </div>
                            <!-- Error display -->
                            <p
                                v-if="form.errors.delivery_address"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.delivery_address }}
                            </p>
                        </div>
                    </div>
                    <!-- Coordinates confirmation -->
                    <div
                        v-if="form.pickup_lat && form.delivery_lat"
                        class="mt-4 flex justify-between rounded border border-indigo-100 bg-indigo-50 p-3 text-xs text-indigo-700 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300"
                    >
                        <div>
                            <strong>Origin:</strong> {{ form.pickup_lat }},
                            {{ form.pickup_lng }}
                        </div>
                        <div>
                            <strong>Dest:</strong> {{ form.delivery_lat }},
                            {{ form.delivery_lng }}
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div
                    class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="mb-4 flex items-center justify-between border-b pb-2"
                    >
                        <h3 class="text-lg font-semibold">
                            Order Items (Cargo)
                        </h3>
                        <Button
                            type="button"
                            @click="addItem"
                            variant="outline"
                            size="sm"
                            class="gap-1"
                        >
                            <Plus class="h-4 w-4" /> Add Item
                        </Button>
                    </div>

                    <div
                        v-if="form.errors.items"
                        class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"
                    >
                        {{ form.errors.items }}
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="relative rounded-lg border bg-zinc-50 p-4 dark:bg-zinc-950/50"
                        >
                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                @click="removeItem(index)"
                                class="absolute top-2 right-2 text-zinc-400 transition-colors hover:text-red-500"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div class="md:col-span-2">
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                        >Item Name / Description *</label
                                    >
                                    <input
                                        type="text"
                                        v-model="item.name"
                                        placeholder="e.g. Pallets of electronics"
                                        required
                                        class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <p
                                        v-if="
                                            form.errors[`items.${index}.name`]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors[`items.${index}.name`] }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                        >Quantity *</label
                                    >
                                    <input
                                        type="number"
                                        v-model="item.quantity"
                                        min="1"
                                        required
                                        class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.quantity`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.quantity`
                                            ]
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                        title="For Insurance purposes"
                                        >Declared Value (cents) *</label
                                    >
                                    <input
                                        type="number"
                                        v-model="item.declared_value_cents"
                                        min="0"
                                        required
                                        class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.declared_value_cents`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.declared_value_cents`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                        >Weight (kg) *</label
                                    >
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="item.weight_kg"
                                        min="0.01"
                                        required
                                        class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.weight_kg`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.weight_kg`
                                            ]
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                        >Volume (CBM)</label
                                    >
                                    <input
                                        type="number"
                                        step="0.001"
                                        v-model="item.cbm"
                                        min="0"
                                        class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                                    />
                                    <p
                                        v-if="form.errors[`items.${index}.cbm`]"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors[`items.${index}.cbm`] }}
                                    </p>
                                </div>
                                <div
                                    class="col-span-1 flex gap-2 rounded border border-zinc-200 p-2 dark:border-zinc-800"
                                >
                                    <div class="flex-1">
                                        <label
                                            class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                            title="Length (cm)"
                                            >L(cm)</label
                                        >
                                        <input
                                            type="number"
                                            v-model="item.length_cm"
                                            min="0"
                                            class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <label
                                            class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                            title="Width (cm)"
                                            >W(cm)</label
                                        >
                                        <input
                                            type="number"
                                            v-model="item.width_cm"
                                            min="0"
                                            class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <label
                                            class="mb-1 block text-[10px] font-bold text-zinc-500 uppercase"
                                            title="Height (cm)"
                                            >H(cm)</label
                                        >
                                        <input
                                            type="number"
                                            v-model="item.height_cm"
                                            min="0"
                                            class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="mt-4 flex items-center justify-start"
                                >
                                    <label
                                        class="flex cursor-pointer items-center space-x-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="item.is_dangerous"
                                            class="focus:ring-opacity-50 rounded border-zinc-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200"
                                        />
                                        <span
                                            class="font-medium text-red-600 text-zinc-700 dark:text-zinc-300"
                                            >Dangerous Goods (ADR)</span
                                        >
                                    </label>
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.is_dangerous`
                                            ]
                                        "
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.is_dangerous`
                                            ]
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <Link
                        href="/orders"
                        class="rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Create Order' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
