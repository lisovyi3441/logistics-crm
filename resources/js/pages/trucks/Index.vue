<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

// Приймаємо дані
defineProps<{
    trucks: any; // length aware paginator
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Fleet Management', href: '/trucks' },
];

</script>

<template>
    <Head title="Fleet Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4 flex-1 min-h-0">

            <div class="flex-none flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Fleet (Vans & Trucks)</h2>
                <div class="flex items-center gap-2">
                    <Link href="/trucks/create" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors">
                        Add Truck
                    </Link>
                    <div class="rounded-md bg-secondary px-3 py-1 text-sm font-medium border text-zinc-600 dark:text-zinc-400">
                        Total: {{ trucks.total }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col flex-1 min-h-0 border rounded-xl bg-card text-card-foreground shadow-sm overflow-hidden">
                <!-- No internal scrollbar on desktop was requested: "вмістити на одному екрані" -->
                <!-- We use overflow-y-auto only for mobile if needed, or better: md:overflow-visible -->
                <div class="flex-1 min-h-0 rounded-t-xl overflow-x-auto md:overflow-y-hidden">
                    <Table class="whitespace-nowrap" :class="{ 'h-full': trucks.data.length >= trucks.per_page }">
                        <TableHeader class="sticky top-0 z-10 bg-card">
                            <TableRow class="bg-muted/50 whitespace-nowrap">
                                <TableHead class="text-center">Name (Alias)</TableHead>
                                <TableHead class="text-center">Status</TableHead>
                                <TableHead class="text-center">License Plate</TableHead>
                                <TableHead class="text-center">Vehicle Type</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="trucks.data.length === 0">
                                <TableCell colspan="5" class="text-center py-10 text-muted-foreground">
                                    No trucks found in the fleet.
                                </TableCell>
                            </TableRow>

                            <TableRow v-for="truck in trucks.data" :key="truck.id"
                                class="hover:bg-muted/50 transition-colors">
                                <TableCell class="font-medium text-zinc-900 dark:text-white text-center">
                                    {{ truck.name }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge v-if="truck.active_orders_exists" variant="outline" class="bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-400 dark:border-red-900">Busy (In Transit)</Badge>
                                    <Badge v-else variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-400 dark:border-emerald-900">Available</Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge variant="outline" class="font-mono bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-400 dark:border-blue-900">{{ truck.license_plate }}</Badge>
                                </TableCell>
                                <TableCell class="text-center text-muted-foreground italic">
                                    {{ truck.vehicle_type?.name }}
                                </TableCell>
                                <TableCell class="text-center whitespace-nowrap">
                                    <Link 
                                        v-if="!truck.active_orders_exists"
                                        :href="`/trucks/${truck.id}/edit`" 
                                        class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >
                                        Edit
                                    </Link>
                                    <span v-else class="text-xs text-zinc-400 flex items-center justify-center gap-1 cursor-not-allowed" title="Cannot edit a busy truck">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        Locked
                                    </span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex-none flex flex-wrap gap-2 justify-center p-4 border-t border-sidebar-border bg-muted/10 rounded-b-xl" v-if="trucks.links && trucks.links.length > 3">
                    <template v-for="(link, i) in trucks.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1 rounded-md border text-sm transition-colors"
                            :class="link.active
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700'"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                        <span
                            v-else
                            class="px-3 py-1 rounded-md border text-sm transition-colors opacity-50 cursor-not-allowed bg-white text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700"
                            v-html="link.label"
                        ></span>
                    </template>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
