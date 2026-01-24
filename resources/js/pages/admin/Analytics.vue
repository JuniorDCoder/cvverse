<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, FileText, TrendingUp, DollarSign } from 'lucide-vue-next';

const stats = [
    { title: 'Total Revenue', value: '$12,345', change: '+15%', icon: DollarSign },
    { title: 'New Signups', value: '234', change: '+8%', icon: Users },
    { title: 'CVs Created', value: '1,567', change: '+23%', icon: FileText },
    { title: 'Conversion Rate', value: '12.5%', change: '+2%', icon: TrendingUp },
];

const topTemplates = [
    { name: 'Modern Professional', usage: 45 },
    { name: 'Simple Clean', usage: 32 },
    { name: 'Creative Designer', usage: 28 },
    { name: 'Tech Starter', usage: 21 },
    { name: 'Executive Suite', usage: 18 },
];

const topIndustries = [
    { name: 'Technology', percentage: 35 },
    { name: 'Finance', percentage: 22 },
    { name: 'Marketing', percentage: 18 },
    { name: 'Healthcare', percentage: 12 },
    { name: 'Other', percentage: 13 },
];
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Analytics' }]">
        <Head title="Analytics" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold">Analytics</h1>
                <p class="text-muted-foreground">Platform performance and user insights</p>
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
                        <p class="text-xs text-green-500">{{ stat.change }} from last month</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Top Templates -->
                <Card>
                    <CardHeader>
                        <CardTitle>Top Templates</CardTitle>
                        <CardDescription>Most used templates this month</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="template in topTemplates" :key="template.name" class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ template.name }}</span>
                                    <span class="text-muted-foreground">{{ template.usage }}%</span>
                                </div>
                                <div class="h-2 bg-muted rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-primary to-purple-500 rounded-full"
                                        :style="{ width: `${template.usage}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- User Industries -->
                <Card>
                    <CardHeader>
                        <CardTitle>User Industries</CardTitle>
                        <CardDescription>Distribution of users by industry</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="industry in topIndustries" :key="industry.name" class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ industry.name }}</span>
                                    <span class="text-muted-foreground">{{ industry.percentage }}%</span>
                                </div>
                                <div class="h-2 bg-muted rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full"
                                        :style="{ width: `${industry.percentage}%` }"
                                    />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
