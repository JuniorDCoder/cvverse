<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Users, FileText, TrendingUp, Activity, Mail, Briefcase, MessageSquare, Shield } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

interface Props {
    stats: {
        total_users: number;
        active_users: number;
        total_cvs: number;
        total_cover_letters: number;
        total_applications: number;
        total_chat_sessions: number;
        users_today: number;
        cvs_today: number;
        applications_today: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        joined: string;
        onboarding_completed: boolean;
    }>;
}

const props = defineProps<Props>();
const page = usePage();
const user = computed(() => page.props.auth.user);

const stats = computed(() => [
    {
        title: 'Total Users',
        value: props.stats.total_users.toLocaleString(),
        subtitle: `${props.stats.users_today} today`,
        icon: Users,
        href: '/admin/users',
    },
    {
        title: 'CVs Created',
        value: props.stats.total_cvs.toLocaleString(),
        subtitle: `${props.stats.cvs_today} today`,
        icon: FileText,
        href: '/admin/cvs',
    },
    {
        title: 'Cover Letters',
        value: props.stats.total_cover_letters.toLocaleString(),
        subtitle: 'Total created',
        icon: Mail,
        href: '/admin/cover-letters',
    },
    {
        title: 'Job Applications',
        value: props.stats.total_applications.toLocaleString(),
        subtitle: `${props.stats.applications_today} today`,
        icon: Briefcase,
        href: '/admin/applications',
    },
    {
        title: 'Chat Sessions',
        value: props.stats.total_chat_sessions.toLocaleString(),
        subtitle: 'Total sessions',
        icon: MessageSquare,
        href: '/admin/chat-sessions',
    },
    {
        title: 'Active Users',
        value: props.stats.active_users.toLocaleString(),
        subtitle: 'Completed onboarding',
        icon: Activity,
        href: '/admin/users',
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Dashboard' }]">
        <Head title="Admin Dashboard" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                <p class="text-muted-foreground">Welcome back, {{ user?.name }}. Here's what's happening.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link v-for="stat in stats" :key="stat.title" :href="stat.href" class="block">
                    <Card class="hover:shadow-md transition-shadow cursor-pointer">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
                            <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stat.value }}</div>
                            <p class="text-xs text-muted-foreground mt-1">
                                {{ stat.subtitle }}
                            </p>
                        </CardContent>
                    </Card>
                </Link>
            </div>

            <!-- Recent Users -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Users</CardTitle>
                    <CardDescription>Latest users who joined the platform</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <Link
                            v-for="recentUser in recentUsers"
                            :key="recentUser.id"
                            :href="`/admin/users?search=${recentUser.email}`"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors block"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ recentUser.name.split(' ').map((n: string) => n[0]).join('') }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ recentUser.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ recentUser.email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <Badge :variant="recentUser.role === 'admin' ? 'default' : 'secondary'">
                                    <Shield v-if="recentUser.role === 'admin'" class="h-3 w-3 mr-1" />
                                    {{ recentUser.role }}
                                </Badge>
                                <p class="text-xs text-muted-foreground mt-1">{{ recentUser.joined }}</p>
                            </div>
                        </Link>
                        <p v-if="recentUsers.length === 0" class="text-center py-8 text-muted-foreground">
                            No users yet
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
