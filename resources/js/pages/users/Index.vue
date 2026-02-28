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
        <div class="flex flex-col gap-4 p-4 flex-1 min-h-0">
            <div class="flex-none flex items-center justify-between">
                <h1 class="text-2xl font-bold dark:text-white">Users</h1>
                <Link href="/users/create" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 bg-indigo-600 text-white shadow hover:bg-indigo-700 h-9 px-4 py-2">
                    Create User
                </Link>
            </div>

            <div v-if="page.props.errors.message" class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                {{ page.props.errors.message }}
            </div>

            <div class="flex flex-col flex-1 min-h-0 border rounded-xl bg-card text-card-foreground shadow-sm">
                <div class="flex-1 overflow-y-auto min-h-0 rounded-t-xl">
                    <Table class="whitespace-nowrap h-full">
                    <TableHeader class="sticky top-0 z-10 bg-card">
                        <TableRow class="bg-muted/50 whitespace-nowrap">
                            <TableHead class="text-center">ID</TableHead>
                            <TableHead class="text-center">Name</TableHead>
                            <TableHead class="text-center">Email</TableHead>
                            <TableHead class="text-center">Company</TableHead>
                            <TableHead class="text-center">Roles</TableHead>
                            <TableHead class="text-center">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="6" class="text-center py-10 text-muted-foreground">
                                No users found.
                            </TableCell>
                        </TableRow>

                        <TableRow v-for="user in users.data" :key="user.id" class="hover:bg-muted/50">
                            <TableCell class="font-medium font-mono text-zinc-600 dark:text-zinc-300 text-center">
                                {{ user.id }}
                            </TableCell>
                            <TableCell class="font-medium text-center">
                                {{ user.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground text-center">{{ user.email }}</TableCell>
                            <TableCell class="text-center">
                                <span v-if="user.company">
                                    <Link :href="`/companies/${user.company.id}`" class="hover:underline text-indigo-600 dark:text-indigo-400">
                                        {{ user.company.name }}
                                    </Link>
                                </span>
                                <span v-else class="text-muted-foreground italic">Global</span>
                            </TableCell>
                            <TableCell class="text-center">
                                <div class="flex flex-wrap gap-1 justify-center">
                                    <Badge v-for="role in user.roles" :key="role" variant="outline" class="whitespace-nowrap flex-shrink-0 bg-blue-50 text-blue-700 hover:bg-blue-50 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                        {{ role }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell class="text-center space-x-3 whitespace-nowrap">
                                <template v-if="user.id !== (page.props.auth.user as any)?.id">
                                    <Link :href="`/users/${user.id}/edit`" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</Link>
                                    <button @click="deleteUser(user.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                                </template>
                                <span v-else class="text-muted-foreground italic text-xs">Current User</span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex-none flex flex-wrap gap-2 justify-center p-4 border-t border-sidebar-border bg-muted/10 rounded-b-xl" v-if="users.meta && users.meta.last_page > 1">
                <template v-for="(link, i) in users.meta.links" :key="i">
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
