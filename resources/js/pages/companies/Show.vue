<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    company: any;
}>();

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to remove this user?')) {
        router.delete(`/users/${id}`);
    }
};

const page = usePage();
</script>

<template>
    <Head :title="company.data.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Companies', href: '/companies' },
            { title: company.data.name, href: `/companies/${company.data.id}` },
        ]"
    >
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 max-w-5xl mx-auto w-full">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">{{ company.data.name }}</h1>
                <Link :href="`/companies/${company.data.id}/edit`" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-zinc-200 bg-white hover:bg-zinc-100 hover:text-zinc-900 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:bg-zinc-800 dark:hover:text-zinc-50 h-9 px-4 py-2 shadow-sm">
                    Edit Company
                </Link>
            </div>

            <div v-if="page.props.errors.message" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                {{ page.props.errors.message }}
            </div>

            <!-- Details Card -->
            <div class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:bg-zinc-900 dark:border-zinc-800">
                <h2 class="text-lg font-semibold mb-4 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-2">Company Information</h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">VAT / Tax ID</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ company.data.vat_number || '-' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ company.data.address || '-' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Phone</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ company.data.contact_phone || '-' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Email</dt>
                        <dd class="mt-1 text-sm text-zinc-900 dark:text-zinc-100">{{ company.data.contact_email || '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Associated Users Card -->
            <div class="rounded-xl border border-sidebar-border bg-white shadow-sm dark:bg-zinc-900 dark:border-zinc-800 overflow-hidden">
                <div class="p-6 border-b border-sidebar-border dark:border-zinc-800 flex justify-between items-center">
                    <h2 class="text-lg font-semibold dark:text-white">Associated Users</h2>
                    <Link :href="`/users/create?company_id=${company.data.id}&redirect_to=/companies/${company.data.id}`" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        + Add User
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-zinc-500 dark:text-zinc-400">
                        <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-sidebar-border/70 dark:border-sidebar-border">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Roles</th>
                                <th scope="col" class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in company.data.users" :key="user.id" class="bg-white border-b border-sidebar-border/70 dark:bg-zinc-900 dark:border-sidebar-border hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-6 py-4 font-medium text-zinc-900 dark:text-white">{{ user.name }}</td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span v-for="role in user.roles" :key="role" class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 mr-1">
                                        {{ role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="`/users/${user.id}/edit?redirect_to=/companies/${company.data.id}`" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</Link>
                                    <button @click="deleteUser(user.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Remove</button>
                                </td>
                            </tr>
                            <tr v-if="!company.data.users || company.data.users.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
                                    No users associated with this company.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </AppLayout>
</template>
