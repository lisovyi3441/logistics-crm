<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Package,
    Building,
    Users,
    Truck,
    Settings,
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user as any);

const can = (permission: string) => {
    return user.value?.permissions?.includes(permission);
};

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },
        {
            title: 'Orders',
            href: '/orders',
            icon: Package,
        },
    ];

    if (can('view trucks')) {
        items.push({
            title: 'Fleet',
            href: '/trucks',
            icon: Truck,
        });
    }

    if (can('edit tariffs')) {
        items.push({
            title: 'Tariffs / Pricing',
            href: '/tariffs',
            icon: Settings,
        });
    }

    // "Companies" for Admin, "My Company" for Manager/Observer
    if (can('view companies') || user.value?.company_id) {
        const isGlobal = can('view companies');
        items.push({
            title: isGlobal ? 'Companies' : 'My Company',
            href: isGlobal
                ? '/companies'
                : `/companies/${user.value.company_id}`,
            icon: Building,
        });
    }

    if (can('view users')) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: Users,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
