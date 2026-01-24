<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search, MoreHorizontal, Mail, Shield, User } from 'lucide-vue-next';

const users = [
    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'user', plan: 'Pro', status: 'active', joined: 'Jan 15, 2026' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'user', plan: 'Free', status: 'active', joined: 'Jan 12, 2026' },
    { id: 3, name: 'Mike Johnson', email: 'mike@example.com', role: 'admin', plan: 'Enterprise', status: 'active', joined: 'Dec 28, 2025' },
    { id: 4, name: 'Sarah Wilson', email: 'sarah@example.com', role: 'user', plan: 'Pro', status: 'inactive', joined: 'Dec 15, 2025' },
    { id: 5, name: 'Alex Brown', email: 'alex@example.com', role: 'user', plan: 'Free', status: 'active', joined: 'Nov 30, 2025' },
];
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
                    <Input placeholder="Search users..." class="pl-9" />
                </div>
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
                                    <th class="text-left p-4 font-medium text-sm">Plan</th>
                                    <th class="text-left p-4 font-medium text-sm">Status</th>
                                    <th class="text-left p-4 font-medium text-sm">Joined</th>
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                                {{ user.name.split(' ').map(n => n[0]).join('') }}
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
                                            <span class="capitalize">{{ user.role }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="user.plan === 'Free' ? 'secondary' : user.plan === 'Enterprise' ? 'default' : 'outline'">
                                            {{ user.plan }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="user.status === 'active' ? 'default' : 'secondary'" :class="user.status === 'active' ? 'bg-green-500/10 text-green-500 hover:bg-green-500/20' : ''">
                                            {{ user.status }}
                                        </Badge>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ user.joined }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <Button variant="ghost" size="sm">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
