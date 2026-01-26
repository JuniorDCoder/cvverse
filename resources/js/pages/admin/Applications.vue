<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Briefcase, User, Building2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { useDebounceFn } from '@vueuse/core';

interface Application {
    id: number;
    title: string;
    status: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    company: {
        id: number;
        name: string;
    } | null;
    created_at: string;
}

interface Props {
    applications: {
        data: Application[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
    statuses: string[];
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

const performSearch = useDebounceFn(() => {
    router.get('/admin/applications', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const handleSearch = () => {
    performSearch();
};

const handleStatusChange = (value: string) => {
    statusFilter.value = value;
    performSearch();
};

const statusColors: Record<string, string> = {
    saved: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    applied: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    interviewing: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    offered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    withdrawn: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Job Applications' }]">
        <Head title="Manage Applications" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Job Applications</h1>
                    <p class="text-muted-foreground">Manage all job applications on the platform</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search applications..." 
                        class="pl-9"
                        @input="handleSearch"
                    />
                </div>
                <Select :model-value="statusFilter" @update:model-value="handleStatusChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        <SelectItem
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Applications Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">Title</th>
                                    <th class="text-left p-4 font-medium text-sm">User</th>
                                    <th class="text-left p-4 font-medium text-sm">Company</th>
                                    <th class="text-left p-4 font-medium text-sm">Status</th>
                                    <th class="text-left p-4 font-medium text-sm">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="app in applications.data" :key="app.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <Briefcase class="h-4 w-4 text-muted-foreground" />
                                            <span class="font-medium">{{ app.title }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="font-medium text-sm">{{ app.user.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ app.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div v-if="app.company" class="flex items-center gap-2">
                                            <Building2 class="h-4 w-4 text-muted-foreground" />
                                            <span class="text-sm">{{ app.company.name }}</span>
                                        </div>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-4">
                                        <Badge :class="statusColors[app.status] || ''" class="capitalize">
                                            {{ app.status }}
                                        </Badge>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ app.created_at }}
                                    </td>
                                </tr>
                                <tr v-if="applications.data.length === 0">
                                    <td colspan="5" class="p-8 text-center text-muted-foreground">
                                        No applications found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="applications.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in applications.links" :key="link.label">
                    <Button
                        v-if="link.url"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        as-child
                    >
                        <Link :href="link.url" v-html="link.label" />
                    </Button>
                    <Button
                        v-else
                        variant="outline"
                        size="sm"
                        disabled
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
