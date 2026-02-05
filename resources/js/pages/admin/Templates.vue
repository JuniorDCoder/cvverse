<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Eye, Pencil, Trash2, Copy, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { useToast } from '@/composables/useToast';
import { templates as adminTemplatesRoute } from '@/routes/admin';
import { create as templateCreate, edit as templateEdit, destroy as templateDestroy, toggleStatus as templateToggleStatus, duplicate as templateDuplicate } from '@/routes/admin/template-builder';

interface Template {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category: string;
    is_premium: boolean;
    is_active: boolean;
    downloads_count: number;
    views_count: number;
    image: string | null;
    thumbnail: string | null;
}

interface PaginatedTemplates {
    data: Template[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const props = withDefaults(defineProps<{
    templates?: PaginatedTemplates;
    categories?: Record<string, string>;
    filters?: {
        search?: string;
        category?: string;
        status?: string;
    };
}>(), {
    templates: () => ({ data: [], current_page: 1, last_page: 1, per_page: 12, total: 0 }),
    categories: () => ({}),
    filters: () => ({})
});

const { addToast } = useToast();
const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const status = ref(props.filters?.status || '');
const deleteModalOpen = ref(false);
const templateToDelete = ref<Template | null>(null);

const applyFilters = () => {
    router.get(adminTemplatesRoute.url({
        query: {
            search: search.value || undefined,
            category: category.value || undefined,
            status: status.value || undefined,
        }
    }), {}, { preserveState: true });
};

const confirmDelete = (template: Template) => {
    templateToDelete.value = template;
    deleteModalOpen.value = true;
};

const deleteTemplate = async () => {
    if (!templateToDelete.value) return;
    
    try {
        const response = await fetch(templateDestroy.url(templateToDelete.value.id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
        });
        
        if (response.ok) {
            addToast({ type: 'success', title: 'Deleted', message: 'Template deleted successfully.' });
            router.reload();
        }
    } catch (e) {
        addToast({ type: 'error', title: 'Error', message: 'Failed to delete template.' });
    } finally {
        deleteModalOpen.value = false;
        templateToDelete.value = null;
    }
};

const toggleStatus = async (template: Template) => {
    try {
        const response = await fetch(templateToggleStatus.url(template.id), {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
        });
        
        if (response.ok) {
            addToast({ type: 'success', title: 'Updated', message: 'Template status updated.' });
            router.reload();
        }
    } catch (e) {
        addToast({ type: 'error', title: 'Error', message: 'Failed to update status.' });
    }
};

const duplicateTemplate = async (template: Template) => {
    try {
        const response = await fetch(templateDuplicate.url(template.id), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
        });
        
        if (response.ok) {
            addToast({ type: 'success', title: 'Duplicated', message: 'Template duplicated successfully.' });
            router.reload();
        }
    } catch (e) {
        addToast({ type: 'error', title: 'Error', message: 'Failed to duplicate template.' });
    }
};
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
                <Button as-child>
                    <Link :href="templateCreate.url()">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Template
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search templates..." 
                        class="pl-9"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <Select v-model="category" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Category" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Categories</SelectItem>
                        <SelectItem 
                            v-for="(label, value) in categories" 
                            :key="value" 
                            :value="value"
                        >
                            {{ label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="status" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-32">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All</SelectItem>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Templates Grid -->
            <div v-if="templates?.data?.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="template in templates.data" :key="template.id" class="group hover:shadow-lg transition-shadow overflow-hidden">
                    <div class="aspect-[3/4] bg-muted relative overflow-hidden">
                        <img 
                            v-if="template.image" 
                            :src="`/storage/${template.image}`" 
                            :alt="template.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-muted-foreground">
                            Preview
                        </div>
                        
                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <Button size="sm" variant="secondary" as-child>
                                <Link :href="templateEdit.url(template.id)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button size="sm" variant="secondary" @click="duplicateTemplate(template)">
                                <Copy class="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="destructive" @click="confirmDelete(template)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    
                    <CardHeader class="pb-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <CardTitle class="text-base truncate">{{ template.name }}</CardTitle>
                                <CardDescription class="capitalize">{{ categories[template.category] || template.category }}</CardDescription>
                            </div>
                            <div class="flex flex-col gap-1 shrink-0">
                                <Badge v-if="template.is_premium" variant="default" class="text-xs">Premium</Badge>
                                <Badge :variant="template.is_active ? 'outline' : 'secondary'" class="text-xs">
                                    {{ template.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent class="pt-0">
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ template.downloads_count.toLocaleString() }} downloads</span>
                            <span>{{ template.views_count.toLocaleString() }} views</span>
                        </div>
                        
                        <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t">
                            <Button 
                                variant="ghost" 
                                size="sm"
                                @click="toggleStatus(template)"
                                :title="template.is_active ? 'Deactivate' : 'Activate'"
                            >
                                <ToggleRight v-if="template.is_active" class="h-4 w-4 text-green-500" />
                                <ToggleLeft v-else class="h-4 w-4 text-muted-foreground" />
                            </Button>
                            <Button variant="ghost" size="sm" as-child>
                                <Link :href="templateEdit.url(template.id)">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                    <Plus class="h-8 w-8 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold mb-2">No templates yet</h3>
                <p class="text-muted-foreground mb-4">Create your first CV template to get started.</p>
                <Button as-child>
                    <Link :href="templateCreate.url()">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Template
                    </Link>
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="templates && templates.last_page > 1" class="flex items-center justify-center gap-2">
                <Button 
                    variant="outline" 
                    size="sm"
                    :disabled="templates.current_page === 1"
                    @click="router.get(adminTemplatesRoute.url({ query: { ...filters, page: templates.current_page - 1 } }))"
                >
                    Previous
                </Button>
                <span class="text-sm text-muted-foreground">
                    Page {{ templates.current_page }} of {{ templates.last_page }}
                </span>
                <Button 
                    variant="outline" 
                    size="sm"
                    :disabled="templates.current_page === templates.last_page"
                    @click="router.get(adminTemplatesRoute.url({ query: { ...filters, page: templates.current_page + 1 } }))"
                >
                    Next
                </Button>
            </div>
        </div>

        <ConfirmDeleteModal
            v-model:open="deleteModalOpen"
            title="Delete Template"
            :description="`Are you sure you want to delete '${templateToDelete?.name}'? This action cannot be undone.`"
            @confirm="deleteTemplate"
        />
    </AppLayout>
</template>