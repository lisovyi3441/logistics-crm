<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

onMounted(() => {
    const user = page.props.auth?.user;
    if (!user) return;

    // Listen for personal notifications (e.g. PDF ready)
    window.Echo.private(`user.${user.id}`)
        .listen('DocumentGenerated', (e: any) => {
            console.log('Document ready:', e);
            router.reload({ only: ['order', 'orders'] });
        });

    // Listen for company-wide updates
    if (user.company_id) {
        window.Echo.private(`company.${user.company_id}`)
            .listen('OrderUpdated', (e: any) => {
                console.log('Order updated:', e);
                router.reload({ only: ['order', 'orders', 'recentOrders', 'stats'] });
            });
    }

    // Admins listen to global order updates
    if (user.roles.includes('admin')) {
        window.Echo.private('admin.orders')
            .listen('OrderUpdated', (e: any) => {
                router.reload({ only: ['order', 'orders', 'recentOrders', 'stats'] });
            });
    }
});

onUnmounted(() => {
    const user = page.props.auth?.user;
    if (user) {
        window.Echo.leave(`user.${user.id}`);
        if (user.company_id) window.Echo.leave(`company.${user.company_id}`);
        if (user.roles.includes('admin')) window.Echo.leave('admin.orders');
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
