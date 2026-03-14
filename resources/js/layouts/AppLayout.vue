<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

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

    const isDebug = document.querySelector('meta[name="app-debug"]')?.getAttribute('content') === 'true';

    // Listen for personal notifications (PDF generation finished)
    // We use .listen('.DocumentGenerated') because broadcastAs adds a dot prefix in Echo unless specified
    window.Echo.private(`user.${user.id}`)
        .listen('.DocumentGenerated', (data: any) => {
            if (isDebug) console.log('PDF Generation Finished:', data);
            
            // Perform a partial reload to refresh the order data (which contains documents)
            router.reload({ 
                only: ['order', 'orders'],
                onSuccess: () => {
                    if (isDebug) console.log('Order data refreshed via WebSockets');
                }
            });
        });

    // Listen for company-wide updates (Status changes, Truck assignments)
    if (user.company_id) {
        window.Echo.private(`company.${user.company_id}`)
            .listen('.OrderUpdated', (data: any) => {
                if (isDebug) console.log('Order Updated (Company Channel):', data);
                router.reload({ only: ['order', 'orders', 'recentOrders', 'stats'] });
            });
    }

    // Admins listen to global order updates safely
    if (user.roles && Array.isArray(user.roles) && user.roles.includes('admin')) {
        window.Echo.private('admin.orders')
            .listen('.OrderUpdated', (data: any) => {
                if (isDebug) console.log('Order Updated (Admin Channel):', data);
                router.reload({ only: ['order', 'orders', 'recentOrders', 'stats'] });
            });
    }
});

onUnmounted(() => {
    const user = page.props.auth?.user;
    if (user) {
        window.Echo.leave(`user.${user.id}`);
        if (user.company_id) window.Echo.leave(`company.${user.company_id}`);
        if (user.roles && Array.isArray(user.roles) && user.roles.includes('admin')) window.Echo.leave('admin.orders');
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
