<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    tariff: any;
    vehicleTypes: Array<{ id: number; name: string; base_price_per_km_cents: number }>;
}>();

const form = useForm({
    insurance_rate_percent: props.tariff.insurance_rate_percent ?? 1.00,
    tax_rate_percent: props.tariff.tax_rate_percent ?? 20.00,
    adr_surcharge_percent: props.tariff.adr_surcharge_percent ?? 25.00,
    vehicle_types: props.vehicleTypes.map(vt => ({
        id: vt.id,
        name: vt.name,
        base_price_per_km_cents: vt.base_price_per_km_cents ?? 0
    })),
});

const submit = () => {
    form.put('/tariffs', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tariff Settings" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Tariff Settings', href: '/tariffs' }]">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">Global Tariffs</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manage default base pricing and surcharges for the Logistics CRM.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">General Defaults</h3>
                        <p class="text-sm text-zinc-500">Fallback rates and global percentages.</p>
                    </div>
                    <div class="p-6 space-y-6">

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Insurance Rate (%)</label>
                            <p class="text-xs text-zinc-500 mb-2">Percentage charged on the declared value of the cargo.</p>
                            <input
                                v-model="form.insurance_rate_percent"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="block w-full rounded-md border-zinc-300 py-2 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.insurance_rate_percent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tax Rate (VAT) (%)</label>
                            <input
                                v-model="form.tax_rate_percent"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="block w-full rounded-md border-zinc-300 py-2 mt-1 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.tax_rate_percent" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Dangerous Goods (ADR) Surcharge (%)</label>
                            <input
                                v-model="form.adr_surcharge_percent"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                class="block w-full rounded-md border-zinc-300 py-2 mt-1 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.adr_surcharge_percent" />
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <div class="border-b border-sidebar-border/70 bg-zinc-50 px-6 py-4 dark:border-sidebar-border dark:bg-zinc-800/50">
                        <h3 class="font-semibold text-zinc-900 dark:text-white">Vehicle Type Rates</h3>
                        <p class="text-sm text-zinc-500">Specific base rate per KM assigned dynamically when order is placed or physical truck assigned.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="(vt, index) in form.vehicle_types" :key="vt.id" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center pb-4 border-b border-zinc-100 last:border-0 last:pb-0 dark:border-zinc-800">
                            <div>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ vt.name }}</span>
                            </div>
                            <div>
                                <label class="sr-only">Rate (Cents/KM)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-zinc-500 sm:text-sm">¢</span>
                                    </div>
                                    <input
                                        v-model="form.vehicle_types[index].base_price_per_km_cents"
                                        type="number"
                                        min="0"
                                        class="block w-full rounded-md border-zinc-300 py-2 pl-7 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
