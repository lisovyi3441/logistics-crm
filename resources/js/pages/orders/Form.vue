<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    companies: Array<{ id: number; name: string }>;
    trucks: Array<{ id: number; name: string; max_weight_kg: number; max_volume_cbm: number }>;
    is_admin: boolean;
    default_company_id?: number;
}>();

const form = useForm({
    company_id: props.default_company_id || '',
    truck_id: '',
    notes: '',
    items: [
        { name: '', quantity: 1, weight_kg: 0, declared_value_cents: 0, cbm: 0, length_cm: '', width_cm: '', height_cm: '', is_dangerous: false }
    ]
});

const addItem = () => {
    form.items.push({ name: '', quantity: 1, weight_kg: 0, declared_value_cents: 0, cbm: 0, length_cm: '', width_cm: '', height_cm: '', is_dangerous: false });
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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 max-w-4xl mx-auto w-full">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-2xl font-bold dark:text-white">Create New Order</h1>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Order Info -->
                <div class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Order Information</h3>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div v-if="is_admin">
                            <label for="company_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Company *</label>
                            <select id="company_id" v-model="form.company_id" required class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="" disabled>Select a company</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.company_id" class="mt-1 text-sm text-red-600">{{ form.errors.company_id }}</p>
                        </div>
                        <div v-else>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Company</label>
                            <div class="mt-1 block w-full px-3 py-2 bg-zinc-100 dark:bg-zinc-800 rounded-md border border-transparent text-sm text-zinc-500">
                                (Your company will be assigned automatically)
                            </div>
                        </div>

                        <div>
                            <label for="truck_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Assign Truck (Optional)</label>
                            <select id="truck_id" v-model="form.truck_id" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="">No truck assigned</option>
                                <option v-for="truck in trucks" :key="truck.id" :value="truck.id">
                                    {{ truck.name }} (Max: {{ truck.max_weight_kg }}kg / {{ truck.max_volume_cbm }} cbm)
                                </option>
                            </select>
                            <p v-if="form.errors.truck_id" class="mt-1 text-sm text-red-600">{{ form.errors.truck_id }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Notes (Optional)</label>
                            <textarea id="notes" v-model="form.notes" rows="1" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <h3 class="text-lg font-semibold">Order Items (Cargo)</h3>
                        <Button type="button" @click="addItem" variant="outline" size="sm" class="gap-1">
                            <Plus class="w-4 h-4" /> Add Item
                        </Button>
                    </div>

                    <div v-if="form.errors.items" class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-md border border-red-200">
                        {{ form.errors.items }}
                    </div>

                    <div class="space-y-4">
                        <div v-for="(item, index) in form.items" :key="index" class="p-4 border rounded-lg relative bg-zinc-50 dark:bg-zinc-950/50">
                            <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="absolute top-2 right-2 text-zinc-400 hover:text-red-500 transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1">Item Name / Description *</label>
                                    <input type="text" v-model="item.name" placeholder="e.g. Pallets of electronics" required class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <p v-if="form.errors[`items.${index}.name`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.name`] }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1">Quantity *</label>
                                    <input type="number" v-model="item.quantity" min="1" required class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.quantity`] }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1" title="For Insurance purposes">Declared Value (cents) *</label>
                                    <input type="number" v-model="item.declared_value_cents" min="0" required class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <p v-if="form.errors[`items.${index}.declared_value_cents`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.declared_value_cents`] }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1">Weight (kg) *</label>
                                    <input type="number" step="0.01" v-model="item.weight_kg" min="0.01" required class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <p v-if="form.errors[`items.${index}.weight_kg`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.weight_kg`] }}</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1">Volume (CBM)</label>
                                    <input type="number" step="0.001" v-model="item.cbm" min="0" class="block w-full rounded-md border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <p v-if="form.errors[`items.${index}.cbm`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.cbm`] }}</p>
                                </div>
                                <div class="col-span-1 border border-zinc-200 dark:border-zinc-800 rounded p-2 flex gap-2">
                                    <div class="flex-1">
                                        <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1" title="Length (cm)">L(cm)</label>
                                        <input type="number" v-model="item.length_cm" min="0" class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1" title="Width (cm)">W(cm)</label>
                                        <input type="number" v-model="item.width_cm" min="0" class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] uppercase font-bold text-zinc-500 mb-1" title="Height (cm)">H(cm)</label>
                                        <input type="number" v-model="item.height_cm" min="0" class="block w-full rounded-md border border-zinc-300 px-1 py-1 text-xs shadow-sm focus:border-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800">
                                    </div>
                                </div>
                                <div class="flex items-center justify-start mt-4">
                                    <label class="flex items-center space-x-2 cursor-pointer text-sm">
                                        <input type="checkbox" v-model="item.is_dangerous" class="rounded border-zinc-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="text-zinc-700 dark:text-zinc-300 font-medium text-red-600">Dangerous Goods (ADR)</span>
                                    </label>
                                    <p v-if="form.errors[`items.${index}.is_dangerous`]" class="mt-1 text-xs text-red-600">{{ form.errors[`items.${index}.is_dangerous`] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <Link href="/orders" class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-md shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-700">
                        Cancel
                    </Link>
                    <button type="submit" :disabled="form.processing" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                        {{ form.processing ? 'Saving...' : 'Create Order' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
