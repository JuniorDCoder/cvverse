<script setup lang="ts">
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
    MessageSquare,
    CreditCard,
    Sparkles,
    Quote,
    Palette,
} from 'lucide-vue-next';
import { computed } from 'vue';
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
import { dashboard, subscription } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const isAdminRoute = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.pathname.startsWith('/admin');
    }
    // SSR fallback: try Inertia page.url
    return page.url?.startsWith('/admin');
});

// User navigation items
const userNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Generate CV with AI',
        href: '/ai-cv-generator',
        icon: Sparkles,
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
    {
        title: 'Subscription',
        href: subscription(),
        icon: CreditCard,
    },
    {
        title: 'AI Chat',
        href: '/ai-chat',
        icon: MessageSquare,
    },
];

// Admin navigation items - grouped
const adminOverviewItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/admin',
        icon: Shield,
    },
    {
        title: 'Analytics',
        href: '/admin/analytics',
        icon: BarChart3,
    },
];

const adminUsersItems: NavItem[] = [
    {
        title: 'Users',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Chat Sessions',
        href: '/admin/chat-sessions',
        icon: MessageSquare,
    },
];

const adminContentItems: NavItem[] = [
    {
        title: 'CVs',
        href: '/admin/cvs',
        icon: FileText,
    },
    {
        title: 'Cover Letters',
        href: '/admin/cover-letters',
        icon: Mail,
    },
    {
        title: 'Applications',
        href: '/admin/applications',
        icon: Briefcase,
    },
    {
        title: 'Templates',
        href: '/admin/templates',
        icon: LayoutTemplate,
    },
];

const adminPlatformItems: NavItem[] = [
    {
        title: 'Testimonials',
        href: '/admin/testimonials',
        icon: Quote,
    },
    {
        title: 'Pricing Plans',
        href: '/admin/pricing-plans',
        icon: CreditCard,
    },
    {
        title: 'Settings',
        href: '/admin/settings',
        icon: Settings,
    },
];

// Combined admin items for non-admin route view (flat list)
const adminNavItems: NavItem[] = [
    ...adminOverviewItems,
    ...adminUsersItems,
    ...adminContentItems,
    ...adminPlatformItems,
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
            <!-- Show grouped admin items on /admin routes -->
            <template v-if="isAdmin && isAdminRoute">
                <NavMain :items="adminOverviewItems" label="Overview" />
                <NavMain :items="adminUsersItems" label="Users" />
                <NavMain :items="adminContentItems" label="Content" />
                <NavMain :items="adminPlatformItems" label="Platform" />
            </template>
            <!-- Show user items (and admin section if admin) on non-admin routes -->
            <template v-else>
                <NavMain :items="userNavItems" label="Main" />
                <template v-if="isAdmin">
                    <SidebarSeparator />
                    <NavMain :items="adminNavItems" label="Administration" />
                </template>
            </template>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
