<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Users, FileText, TrendingUp, Activity } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const stats = [
    {
        title: 'Total Users',
        value: '1,234',
        change: '+12%',
        changeType: 'positive',
        icon: Users,
    },
    {
        title: 'CVs Created',
        value: '5,678',
        change: '+23%',
        changeType: 'positive',
        icon: FileText,
    },
    {
        title: 'Active Today',
        value: '89',
        change: '+5%',
        changeType: 'positive',
        icon: Activity,
    },
    {
        title: 'Conversion Rate',
        value: '12.5%',
        change: '-2%',
        changeType: 'negative',
        icon: TrendingUp,
    },
];

const recentUsers = [
    { name: 'John Doe', email: 'john@example.com', joined: '2 hours ago', plan: 'Pro' },
    { name: 'Jane Smith', email: 'jane@example.com', joined: '5 hours ago', plan: 'Free' },
    { name: 'Mike Johnson', email: 'mike@example.com', joined: '1 day ago', plan: 'Enterprise' },
    { name: 'Sarah Wilson', email: 'sarah@example.com', joined: '2 days ago', plan: 'Pro' },
];
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
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in stats" :key="stat.title">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
                        <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span :class="stat.changeType === 'positive' ? 'text-green-500' : 'text-red-500'">
                                {{ stat.change }}
                            </span>
                            from last month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Users -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Users</CardTitle>
                    <CardDescription>Latest users who joined the platform</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div 
                            v-for="user in recentUsers" 
                            :key="user.email"
                            class="flex items-center justify-between p-3 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ user.name.split(' ').map(n => n[0]).join('') }}
                                </div>
                                <div>
                                    <p class="font-medium">{{ user.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <Badge :variant="user.plan === 'Free' ? 'secondary' : user.plan === 'Enterprise' ? 'default' : 'outline'">
                                    {{ user.plan }}
                                </Badge>
                                <p class="text-xs text-muted-foreground mt-1">{{ user.joined }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
