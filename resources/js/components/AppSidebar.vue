<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
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
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    FileText, 
    Briefcase,
    Mail,
    Settings,
    HelpCircle,
    BookOpen,
    Users,
    BarChart3,
    LayoutTemplate,
    Shield,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

// User navigation items
const userNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Job Applications',
        href: '/jobs',
        icon: Briefcase,
    },
    {
        title: 'My CVs',
        href: '/cvs',
        icon: FileText,
    },
    {
        title: 'Cover Letters',
        href: '/cover-letters',
        icon: Mail,
    },
];

// Admin navigation items
const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
        icon: Shield,
    },
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Templates',
        href: '/admin/templates',
        icon: LayoutTemplate,
    },
    {
        title: 'Analytics',
        href: '/admin/analytics',
        icon: BarChart3,
    },
    {
        title: 'Settings',
        href: '/admin/settings',
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Help Center',
        href: '/contact',
        icon: HelpCircle,
    },
    {
        title: 'Documentation',
        href: '/services',
        icon: BookOpen,
    },
];
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
            <!-- User Navigation -->
            <NavMain :items="userNavItems" label="Main" />
            
            <!-- Admin Navigation (only for admins) -->
            <template v-if="isAdmin">
                <SidebarSeparator />
                <NavMain :items="adminNavItems" label="Administration" />
            </template>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
