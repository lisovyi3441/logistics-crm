<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    company?: any;
}>();

const isEditing = !!props.company;

const form = useForm({
    name: props.company?.data?.name || '',
    vat_number: props.company?.data?.vat_number || '',
    address: props.company?.data?.address || '',
    contact_phone: props.company?.data?.contact_phone || '',
    contact_email: props.company?.data?.contact_email || '',
});

const submit = () => {
    if (isEditing) {
        form.put(`/companies/${props.company.data.id}`);
    } else {
        form.post('/companies');
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Company' : 'Create Company'" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Companies', href: '/companies' },
            { title: isEditing ? 'Edit' : 'Create', href: '#' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-2xl mx-auto w-full">
            <h1 class="text-2xl font-bold dark:text-white">{{ isEditing ? 'Edit Company' : 'Create Company' }}</h1>
            
            <div class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Company Name *</label>
                        <input id="name" type="text" v-model="form.name" required class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="vat_number" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">VAT / Tax ID</label>
                        <input id="vat_number" type="text" v-model="form.vat_number" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p v-if="form.errors.vat_number" class="mt-1 text-sm text-red-600">{{ form.errors.vat_number }}</p>
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Address</label>
                        <input id="address" type="text" v-model="form.address" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Phone</label>
                            <input id="contact_phone" type="text" v-model="form.contact_phone" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <p v-if="form.errors.contact_phone" class="mt-1 text-sm text-red-600">{{ form.errors.contact_phone }}</p>
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Contact Email</label>
                            <input id="contact_email" type="email" v-model="form.contact_email" class="mt-1 block w-full rounded-md border border-zinc-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            <p v-if="form.errors.contact_email" class="mt-1 text-sm text-red-600">{{ form.errors.contact_email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <Link href="/companies" class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-md shadow-sm hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 dark:hover:bg-zinc-700">
                            Cancel
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            {{ isEditing ? 'Save Changes' : 'Create Company' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
