<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search, Plus, MoreHorizontal, Eye, Pencil } from 'lucide-vue-next';

const templates = [
    { id: 1, name: 'Modern Professional', category: 'Professional', downloads: 1234, status: 'active', premium: true },
    { id: 2, name: 'Creative Designer', category: 'Creative', downloads: 892, status: 'active', premium: true },
    { id: 3, name: 'Simple Clean', category: 'Minimal', downloads: 2341, status: 'active', premium: false },
    { id: 4, name: 'Executive Suite', category: 'Executive', downloads: 567, status: 'active', premium: true },
    { id: 5, name: 'Tech Starter', category: 'Technology', downloads: 1567, status: 'draft', premium: false },
];
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Templates' }]">
        <Head title="Manage Templates" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Templates</h1>
                    <p class="text-muted-foreground">Manage CV templates available to users</p>
                </div>
                <Button>
                    <Plus class="h-4 w-4 mr-2" />
                    Add Template
                </Button>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input placeholder="Search templates..." class="pl-9" />
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Card v-for="template in templates" :key="template.id" class="group hover:shadow-lg transition-shadow">
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <CardTitle class="text-lg">{{ template.name }}</CardTitle>
                                <CardDescription>{{ template.category }}</CardDescription>
                            </div>
                            <div class="flex gap-2">
                                <Badge v-if="template.premium" variant="default">Premium</Badge>
                                <Badge :variant="template.status === 'active' ? 'outline' : 'secondary'">
                                    {{ template.status }}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Template Preview Placeholder -->
                        <div class="aspect-[3/4] bg-muted rounded-lg mb-4 flex items-center justify-center text-muted-foreground">
                            Preview
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-muted-foreground">
                                {{ template.downloads.toLocaleString() }} downloads
                            </p>
                            <div class="flex gap-2">
                                <Button variant="ghost" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="sm">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
