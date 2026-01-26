<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search, MoreHorizontal, Mail, Shield, User, FileText, Briefcase } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    onboarding_completed: boolean;
    cvs_count: number;
    applications_count: number;
    cover_letters_count: number;
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');

const performSearch = useDebounceFn(() => {
    router.get('/admin/users', {
        search: search.value || undefined,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const handleSearch = () => {
    performSearch();
};

const handleRoleChange = (value: string) => {
    roleFilter.value = value;
    performSearch();
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Users' }]">
        <Head title="Manage Users" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Users</h1>
                    <p class="text-muted-foreground">Manage all users on the platform</p>
                </div>
                <Button>
                    <Mail class="h-4 w-4 mr-2" />
                    Invite User
                </Button>
            </div>

            <!-- Search & Filters -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search users..." 
                        class="pl-9"
                        @input="handleSearch"
                    />
                </div>
                <Select :model-value="roleFilter" @update:model-value="handleRoleChange">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Roles</SelectItem>
                        <SelectItem value="admin">Admin</SelectItem>
                        <SelectItem value="user">User</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">User</th>
                                    <th class="text-left p-4 font-medium text-sm">Role</th>
                                    <th class="text-left p-4 font-medium text-sm">Onboarding</th>
                                    <th class="text-left p-4 font-medium text-sm">Activity</th>
                                    <th class="text-left p-4 font-medium text-sm">Joined</th>
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                                {{ user.name.split(' ').map((n: string) => n[0]).join('') }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ user.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <component :is="user.role === 'admin' ? Shield : User" class="h-4 w-4 text-muted-foreground" />
                                            <Badge :variant="user.role === 'admin' ? 'default' : 'secondary'">
                                                {{ user.role }}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="user.onboarding_completed ? 'default' : 'secondary'" 
                                               :class="user.onboarding_completed ? 'bg-green-500/10 text-green-500 hover:bg-green-500/20' : ''">
                                            {{ user.onboarding_completed ? 'Completed' : 'Pending' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                            <div class="flex items-center gap-1">
                                                <FileText class="h-3 w-3" />
                                                {{ user.cvs_count }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <Briefcase class="h-3 w-3" />
                                                {{ user.applications_count }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ user.created_at }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <Button variant="ghost" size="sm">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-muted-foreground">
                                        No users found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in users.links" :key="link.label">
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
