<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
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
    users: any;
}>();

const deleteUser = (id: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};

const page = usePage();
</script>

<template>
    <Head title="Users" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Users', href: '/users' },
        ]"
    >
        <div class="flex min-h-0 flex-1 flex-col gap-4 p-4">
            <div class="flex flex-none items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">Users</h1>
                <Link
                    href="/users/create"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-indigo-700 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                >
                    Create User
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
                            'h-full': users.data.length >= users.meta.per_page,
                        }"
                    >
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center">ID</TableHead>
                                <TableHead class="text-center">Name</TableHead>
                                <TableHead class="text-center">Email</TableHead>
                                <TableHead class="text-center"
                                    >Company</TableHead
                                >
                                <TableHead class="text-center">Roles</TableHead>
                                <TableHead class="text-center"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="users.data.length === 0">
                                <TableCell
                                    colspan="6"
                                    class="py-10 text-center text-muted-foreground"
                                >
                                    No users found.
                                </TableCell>
                            </TableRow>

                            <TableRow
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-muted/50"
                            >
                                <TableCell
                                    class="text-center font-mono font-medium text-zinc-600 dark:text-zinc-300"
                                >
                                    {{ user.id }}
                                </TableCell>
                                <TableCell class="text-center font-medium">
                                    {{ user.name }}
                                </TableCell>
                                <TableCell
                                    class="text-center text-muted-foreground"
                                    >{{ user.email }}</TableCell
                                >
                                <TableCell class="text-center">
                                    <span v-if="user.company">
                                        <Link
                                            :href="`/companies/${user.company.id}`"
                                            class="text-indigo-600 hover:underline dark:text-indigo-400"
                                        >
                                            {{ user.company.name }}
                                        </Link>
                                    </span>
                                    <span
                                        v-else
                                        class="text-muted-foreground italic"
                                        >Global</span
                                    >
                                </TableCell>
                                <TableCell class="text-center">
                                    <div
                                        class="flex flex-wrap justify-center gap-1"
                                    >
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role"
                                            variant="outline"
                                            class="flex-shrink-0 border-blue-200 bg-blue-50 whitespace-nowrap text-blue-700 hover:bg-blue-50 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                                        >
                                            {{ role }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="space-x-3 text-center whitespace-nowrap"
                                >
                                    <template
                                        v-if="
                                            user.id !==
                                            (page.props.auth.user as any)?.id
                                        "
                                    >
                                        <Link
                                            :href="`/users/${user.id}/edit`"
                                            class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                            >Edit</Link
                                        >
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="font-medium text-red-600 hover:underline dark:text-red-500"
                                        >
                                            Delete
                                        </button>
                                    </template>
                                    <span
                                        v-else
                                        class="text-xs text-muted-foreground italic"
                                        >Current User</span
                                    >
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    class="flex flex-none flex-wrap justify-center gap-2 rounded-b-xl border-t border-sidebar-border bg-muted/10 p-4"
                    v-if="users.meta && users.meta.last_page > 1"
                >
                    <template v-for="(link, i) in users.meta.links" :key="i">
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
