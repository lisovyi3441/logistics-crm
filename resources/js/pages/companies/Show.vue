<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
const hasPermission = (permission: string) => {
    return (page.props.auth.user as any)?.permissions?.includes(permission);
};

const companiesBreadcrumbHref = computed(() => {
    return hasPermission('view companies') ? '/companies' : '/dashboard';
});
</script>

<template>
    <Head :title="company.data.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            {
                title: hasPermission('view companies')
                    ? 'Companies'
                    : 'My Company',
                href: companiesBreadcrumbHref,
            },
            { title: company.data.name, href: `/companies/${company.data.id}` },
        ]"
    >
        <div
            class="mx-auto flex h-full w-full max-w-5xl flex-1 flex-col gap-6 rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">
                    {{ company.data.name }}
                </h1>
                <Link
                    v-if="
                        hasPermission('edit companies') ||
                        (page.props.auth.user as any)?.company_id ===
                            company.data.id
                    "
                    :href="`/companies/${company.data.id}/edit`"
                    class="inline-flex h-9 items-center justify-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 dark:border-zinc-800 dark:bg-zinc-950 dark:hover:bg-zinc-800 dark:hover:text-zinc-50"
                >
                    Edit Company
                </Link>
            </div>

            <div
                v-if="page.props.errors.message"
                class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400"
            >
                {{ page.props.errors.message }}
            </div>

            <!-- Details Card -->
            <div
                class="rounded-xl border border-sidebar-border bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <h2
                    class="mb-4 border-b border-zinc-100 pb-2 text-lg font-semibold dark:border-zinc-800 dark:text-white"
                >
                    Company Information
                </h2>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt
                            class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                        >
                            VAT / Tax ID
                        </dt>
                        <dd
                            class="mt-1 text-sm text-zinc-900 dark:text-zinc-100"
                        >
                            {{ company.data.vat_number || '-' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt
                            class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                        >
                            Address
                        </dt>
                        <dd
                            class="mt-1 text-sm text-zinc-900 dark:text-zinc-100"
                        >
                            {{ company.data.address || '-' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt
                            class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                        >
                            Contact Phone
                        </dt>
                        <dd
                            class="mt-1 text-sm text-zinc-900 dark:text-zinc-100"
                        >
                            {{ company.data.contact_phone || '-' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt
                            class="text-sm font-medium text-zinc-500 dark:text-zinc-400"
                        >
                            Contact Email
                        </dt>
                        <dd
                            class="mt-1 text-sm text-zinc-900 dark:text-zinc-100"
                        >
                            {{ company.data.contact_email || '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Associated Users Card -->
            <div
                class="overflow-hidden rounded-xl border border-sidebar-border bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-sidebar-border p-6 dark:border-zinc-800"
                >
                    <h2 class="text-lg font-semibold dark:text-white">
                        Associated Users
                    </h2>
                    <Link
                        v-if="hasPermission('create users')"
                        :href="`/users/create?company_id=${company.data.id}&redirect_to=/companies/${company.data.id}`"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                        + Add User
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table
                        class="w-full text-left text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        <thead
                            class="border-b border-sidebar-border/70 bg-zinc-50 text-xs text-zinc-700 uppercase dark:border-sidebar-border dark:bg-zinc-800/50 dark:text-zinc-300"
                        >
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Roles</th>
                                <th
                                    v-if="
                                        hasPermission('edit users') ||
                                        hasPermission('delete users')
                                    "
                                    scope="col"
                                    class="px-6 py-3 text-right"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in company.data.users"
                                :key="user.id"
                                class="border-b border-sidebar-border/70 bg-white hover:bg-zinc-50 dark:border-sidebar-border dark:bg-zinc-900 dark:hover:bg-zinc-800/50"
                            >
                                <td
                                    class="px-6 py-4 font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role"
                                        class="mr-1 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                                    >
                                        {{ role }}
                                    </span>
                                </td>
                                <td
                                    v-if="
                                        hasPermission('edit users') ||
                                        hasPermission('delete users')
                                    "
                                    class="space-x-2 px-6 py-4 text-right"
                                >
                                    <Link
                                        v-if="hasPermission('edit users')"
                                        :href="`/users/${user.id}/edit?redirect_to=/companies/${company.data.id}`"
                                        class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                        >Edit</Link
                                    >
                                    <button
                                        v-if="hasPermission('delete users')"
                                        @click="deleteUser(user.id)"
                                        class="font-medium text-red-600 hover:underline dark:text-red-500"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !company.data.users ||
                                    company.data.users.length === 0
                                "
                            >
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-zinc-500"
                                >
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
