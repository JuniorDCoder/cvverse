<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search, FileText, User } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    };
    created_at: string;
}

interface Props {
    cvs: {
        data: Cv[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

const performSearch = useDebounceFn(() => {
    router.get('/admin/cvs', {
        search: search.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const handleSearch = () => {
    performSearch();
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'CVs' }]">
        <Head title="Manage CVs" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">CVs</h1>
                    <p class="text-muted-foreground">Manage all CVs on the platform</p>
                </div>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search CVs..." 
                        class="pl-9"
                        @input="handleSearch"
                    />
                </div>
            </div>

            <!-- CVs Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">CV Name</th>
                                    <th class="text-left p-4 font-medium text-sm">Template</th>
                                    <th class="text-left p-4 font-medium text-sm">User</th>
                                    <th class="text-left p-4 font-medium text-sm">Status</th>
                                    <th class="text-left p-4 font-medium text-sm">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cv in cvs.data" :key="cv.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-4 w-4 text-muted-foreground" />
                                            <span class="font-medium">{{ cv.name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge variant="outline" class="capitalize">
                                            {{ cv.template }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <p class="font-medium text-sm">{{ cv.user.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ cv.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge v-if="cv.is_primary" variant="default">
                                            Primary
                                        </Badge>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ cv.created_at }}
                                    </td>
                                </tr>
                                <tr v-if="cvs.data.length === 0">
                                    <td colspan="5" class="p-8 text-center text-muted-foreground">
                                        No CVs found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="cvs.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in cvs.links" :key="link.label">
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
