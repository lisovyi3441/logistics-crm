import type { LucideIcon } from 'lucide-vue-next';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    permission?: string;
    component?: any;
};
