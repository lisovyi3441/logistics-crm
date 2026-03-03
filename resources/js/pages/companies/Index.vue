<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
const hasPermission = (permission: string) => {
    return (page.props.auth.user as any)?.permissions?.includes(permission);
};
</script>

<template>
    <Head title="Companies" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Companies', href: '/companies' },
        ]"
    >
        <div class="flex flex-col gap-4 p-4 flex-1 min-h-0">
            <div class="flex-none flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">Companies</h1>
                <Link v-if="hasPermission('create companies')" href="/companies/create" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-indigo-600 text-white shadow hover:bg-indigo-700 h-9 px-4 py-2">
                    Create Company
                </Link>
            </div>

            <div v-if="page.props.errors.message" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                {{ page.props.errors.message }}
            </div>

            <div class="flex flex-col flex-1 min-h-0 border rounded-xl bg-card text-card-foreground shadow-sm">
                <div class="flex-1 overflow-y-auto min-h-0 rounded-t-xl">
                    <Table class="whitespace-nowrap" :class="{ 'h-full': companies.data.length >= companies.meta.per_page }">
                    <TableHeader class="sticky top-0 z-10 bg-card">
                        <TableRow class="bg-muted/50 whitespace-nowrap">
                            <TableHead class="text-center">ID</TableHead>
                            <TableHead class="text-center">Name</TableHead>
                            <TableHead class="text-center">VAT/Tax ID</TableHead>
                            <TableHead class="text-center">Contacts</TableHead>
                            <TableHead class="text-center">Users</TableHead>
                            <TableHead class="text-center">Orders</TableHead>
                            <TableHead class="text-center">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="companies.data.length === 0">
                            <TableCell colspan="7" class="text-center py-10 text-muted-foreground">
                                No companies found.
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="company in companies.data" :key="company.id" class="hover:bg-muted/50">
                            <TableCell class="font-medium font-mono text-zinc-600 dark:text-zinc-300 text-center">
                                {{ company.id }}
                            </TableCell>
                            <TableCell class="font-medium text-center">
                                <Link :href="`/companies/${company.id}`" class="hover:underline">
                                    {{ company.name }}
                                </Link>
                            </TableCell>
                            <TableCell class="text-muted-foreground text-center">{{ company.vat_number || '-' }}</TableCell>
                            <TableCell class="text-center">
                                <div>{{ company.contact_phone || '-' }}</div>
                                <div class="text-xs text-muted-foreground">{{ company.contact_email || '' }}</div>
                            </TableCell>
                            <TableCell class="text-center">{{ company.users_count }}</TableCell>
                            <TableCell class="text-center">{{ company.orders_count }}</TableCell>
                            <TableCell class="text-center space-x-3 whitespace-nowrap">
                                <Link :href="`/companies/${company.id}/edit`" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</Link>
                                <button v-if="hasPermission('delete companies')" @click="deleteCompany(company.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex-none flex flex-wrap gap-2 justify-center p-4 border-t border-sidebar-border bg-muted/10 rounded-b-xl" v-if="companies.meta && companies.meta.last_page > 1">
                <template v-for="(link, i) in companies.meta.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3 py-1 rounded-md border text-sm"
                        :class="link.active
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                    <span
                        v-else
                        class="px-3 py-1 rounded-md border text-sm opacity-50 cursor-not-allowed bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700"
                        v-html="link.label"
                    ></span>
                </template>
            </div>
            </div>
        </div>
    </AppLayout>
</template>
