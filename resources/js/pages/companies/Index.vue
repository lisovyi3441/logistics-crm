<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    companies: any;
}>();

const deleteCompany = (id: number) => {
    if (confirm('Are you sure you want to delete this company?')) {
        router.delete(`/companies/${id}`);
    }
};

const page = usePage();
</script>

<template>
    <Head title="Companies" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Companies', href: '/companies' },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">Companies</h1>
                <Link href="/companies/create" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-indigo-600 text-white shadow hover:bg-indigo-700 h-9 px-4 py-2">
                    Create Company
                </Link>
            </div>

            <div v-if="page.props.errors.message" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                {{ page.props.errors.message }}
            </div>

            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900">
                <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-sidebar-border/70 dark:border-sidebar-border">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Name</th>
                            <th scope="col" class="px-6 py-3">VAT/Tax ID</th>
                            <th scope="col" class="px-6 py-3">Contacts</th>
                            <th scope="col" class="px-6 py-3 text-center">Users</th>
                            <th scope="col" class="px-6 py-3 text-center">Orders</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="company in companies.data" :key="company.id" class="bg-white border-b border-sidebar-border/70 dark:bg-zinc-900 dark:border-sidebar-border hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-4">{{ company.id }}</td>
                            <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">
                                <Link :href="`/companies/${company.id}`" class="hover:underline">
                                    {{ company.name }}
                                </Link>
                            </td>
                            <td class="px-6 py-4">{{ company.vat_number || '-' }}</td>
                            <td class="px-6 py-4">
                                <div>{{ company.contact_phone || '-' }}</div>
                                <div class="text-xs text-zinc-400">{{ company.contact_email || '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">{{ company.users_count }}</td>
                            <td class="px-6 py-4 text-center">{{ company.orders_count }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Link :href="`/companies/${company.id}/edit`" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</Link>
                                <button @click="deleteCompany(company.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="companies.data.length === 0">
                            <td colspan="7" class="px-6 py-10 text-center text-zinc-500">
                                No companies found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination logic here if needed: companies.meta.links -->
            <div class="flex gap-2 justify-center mt-4" v-if="companies.meta && companies.meta.last_page > 1">
                <Link 
                    v-for="(link, i) in companies.meta.links" 
                    :key="i" 
                    :href="link.url || '#'" 
                    class="px-3 py-1 rounded-md border text-sm"
                    :class="[
                        link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                    ]"
                    v-html="link.label"
                ></Link>
            </div>
        </div>
    </AppLayout>
</template>
