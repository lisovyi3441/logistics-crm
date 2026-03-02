<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    truck?: any;
    vehicleTypes: Array<{ id: number; name: string }>;
}>();

const isEditing = !!props.truck;

const form = useForm({
    name: props.truck?.name ?? '',
    license_plate: props.truck?.license_plate ?? '',
    vehicle_type_id: props.truck?.vehicle_type_id ?? '',
});

const submit = () => {
    if (isEditing) {
        form.put(`/trucks/${props.truck.id}`);
    } else {
        form.post('/trucks');
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Truck' : 'Add Truck'" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }, { title: 'Fleet Management', href: '/trucks' }, { title: isEditing ? 'Edit Truck' : 'Add Truck', href: '#' }]">
        <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 md:p-6">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ isEditing ? 'Edit Truck' : 'Add Truck' }}</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Configure physical parameters and categorization constraint for fleet assignment.</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Truck Alias (Name)</label>
                        <p class="text-xs text-zinc-500 mb-2">Internal recognizable name (e.g. "White Sprinter #2").</p>
                        <input
                            v-model="form.name"
                            type="text"
                            class="block w-full rounded-md border-zinc-300 py-2 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">License Plate</label>
                        <p class="text-xs text-zinc-500 mb-2">Official registration number used for assignment visuality.</p>
                        <input
                            v-model="form.license_plate"
                            type="text"
                            class="block w-full uppercase font-mono rounded-md border-zinc-300 py-2 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm"
                            required
                            pattern="^[ABCEHIKMOPTXАВЕСНКІМОРТХabcehikmoptxавеснкімортх]{2}\s?\d{4}\s?[ABCEHIKMOPTXАВЕСНКІМОРТХabcehikmoptxавеснкімортх]{2}$"
                            title="Format: AA 1234 BC or AA1234BC (Using valid Latin/Cyrillic characters: A,B,C,E,H,I,K,M,O,P,T,X / А,В,Е,К,М,Н,О,Р,С,Т,Х,І)"
                        />
                        <InputError class="mt-2" :message="form.errors.license_plate" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Vehicle Type Grouping</label>
                        <p class="text-xs text-zinc-500 mb-2">Matches truck to specific capacity constraints and client requests.</p>
                        <select
                            v-model="form.vehicle_type_id"
                            class="block w-full rounded-md border-zinc-300 py-2 pl-3 pr-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 disabled:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm dark:disabled:bg-zinc-800"
                            :disabled="isEditing"
                            required
                        >
                            <option value="" disabled>Select a vehicle type</option>
                            <option v-for="vt in vehicleTypes" :key="vt.id" :value="vt.id">
                                {{ vt.name }}
                            </option>
                        </select>
                        <p v-if="isEditing" class="text-xs text-amber-600 dark:text-amber-500 mt-2 flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Vehicle type cannot be changed after creation to preserve historical constraints.
                        </p>
                        <InputError class="mt-2" :message="form.errors.vehicle_type_id" />
                    </div>

                    <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-700 pt-6">
                        <Link
                            href="/trucks"
                            class="text-sm font-medium text-zinc-600 hover:text-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-300"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            {{ isEditing ? 'Update Truck' : 'Add Truck' }}
                        </button>
                    </div>
                </form>
            </div>
            
            <div v-if="isEditing" class="mt-4 p-4 border border-red-300 bg-red-50 rounded-xl dark:border-red-900/50 dark:bg-red-900/10">
                 <h3 class="text-red-800 dark:text-red-400 font-semibold mb-2">Danger Zone</h3>
                 <p class="text-sm text-red-700 dark:text-red-300 mb-4">Deleting this truck will permanently remove it from the fleet. This action cannot be undone and may cause issues if the truck is linked to historical orders without constraints.</p>
                 <Link
                    :href="`/trucks/${truck.id}`"
                    method="delete"
                    as="button"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
                 >
                    Delete Truck
                 </Link>
            </div>
        </div>
    </AppLayout>
</template>
