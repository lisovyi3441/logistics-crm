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
        <div class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-none items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">Companies</h1>
                <Link
                    v-if="hasPermission('create companies')"
                    href="/companies/create"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-indigo-700 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    Create Company
                </Link>
            </div>

            <div
                v-if="page.props.errors.message"
                class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400"
            >
                {{ page.props.errors.message }}
            </div>

            <div
                class="flex min-h-0 flex-1 flex-col rounded-xl border bg-card text-card-foreground shadow-sm"
            >
                <div class="min-h-0 flex-1 overflow-y-auto rounded-t-xl">
                    <Table
                        class="whitespace-nowrap"
                        :class="{
                            'h-full':
                                companies.data.length >=
                                companies.meta.per_page,
                        }"
                    >
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center">ID</TableHead>
                                <TableHead class="text-center">Name</TableHead>
                                <TableHead class="text-center"
                                    >VAT/Tax ID</TableHead
                                >
                                <TableHead class="text-center"
                                    >Contacts</TableHead
                                >
                                <TableHead class="text-center">Users</TableHead>
                                <TableHead class="text-center"
                                    >Orders</TableHead
                                >
                                <TableHead class="text-center"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="companies.data.length === 0">
                                <TableCell
                                    colspan="7"
                                    class="py-10 text-center text-muted-foreground"
                                >
                                    No companies found.
                                </TableCell>
                            </TableRow>

                            <TableRow
                                v-for="company in companies.data"
                                :key="company.id"
                                class="hover:bg-muted/50"
                            >
                                <TableCell
                                    class="text-center font-mono font-medium text-zinc-600 dark:text-zinc-300"
                                >
                                    {{ company.id }}
                                </TableCell>
                                <TableCell class="text-center font-medium">
                                    <Link
                                        :href="`/companies/${company.id}`"
                                        class="hover:underline"
                                    >
                                        {{ company.name }}
                                    </Link>
                                </TableCell>
                                <TableCell
                                    class="text-center text-muted-foreground"
                                    >{{ company.vat_number || '-' }}</TableCell
                                >
                                <TableCell class="text-center">
                                    <div>
                                        {{ company.contact_phone || '-' }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ company.contact_email || '' }}
                                    </div>
                                </TableCell>
                                <TableCell class="text-center">{{
                                    company.users_count
                                }}</TableCell>
                                <TableCell class="text-center">{{
                                    company.orders_count
                                }}</TableCell>
                                <TableCell
                                    class="space-x-3 text-center whitespace-nowrap"
                                >
                                    <Link
                                        :href="`/companies/${company.id}/edit`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                        >Edit</Link
                                    >
                                    <button
                                        v-if="hasPermission('delete companies')"
                                        @click="deleteCompany(company.id)"
                                        class="font-medium text-red-600 hover:underline dark:text-red-500"
                                    >
                                        Delete
                                    </button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    class="flex flex-none flex-wrap justify-center gap-2 rounded-b-xl border-t border-sidebar-border bg-muted/10 p-4"
                    v-if="companies.meta && companies.meta.last_page > 1"
                >
                    <template
                        v-for="(link, i) in companies.meta.links"
                        :key="i"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-md border px-3 py-1 text-sm"
                            :class="
                                link.active
                                    ? 'border-indigo-600 bg-indigo-600 text-white'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'
                            "
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else
                            class="cursor-not-allowed rounded-md border border-zinc-200 bg-white px-3 py-1 text-sm text-zinc-700 opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
