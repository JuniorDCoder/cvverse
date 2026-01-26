<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    FileText,
    Star,
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Download,
    Calendar,
} from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import ShimmerCard from '@/components/ShimmerCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as cvsIndex, create as cvCreate, show as cvShow, edit as cvEdit, destroy as cvDestroy } from '@/routes/cvs';
import { type BreadcrumbItem } from '@/types';

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    file_path?: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    cvs: {
        data: Cv[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'CVs', href: '#' },
];

const search = ref('');
const isLoading = ref(true);
const { addToast } = useToast();

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 500);
});

const templateLabels: Record<string, string> = {
    modern: 'Modern',
    classic: 'Classic',
    minimal: 'Minimal',
    creative: 'Creative',
    executive: 'Executive',
};

const templateColors: Record<string, string> = {
    modern: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    classic: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    minimal: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    creative: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    executive: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
};

const filteredCvs = computed(() => {
    if (!search.value) return props.cvs.data;
    const searchLower = search.value.toLowerCase();
    return props.cvs.data.filter(cv =>
        cv.name.toLowerCase().includes(searchLower) ||
        cv.template.toLowerCase().includes(searchLower)
    );
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const cvToDelete = ref<number | null>(null);
const isDeleteModalOpen = ref(false);

const confirmDelete = (id: number) => {
    cvToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const deleteCv = () => {
    if (cvToDelete.value) {
        router.delete(`/cvs/${cvToDelete.value}`);
        isDeleteModalOpen.value = false;
        cvToDelete.value = null;
    }
};

const setPrimary = async (id: number) => {
    try {
        const response = await fetch(`/cvs/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ is_primary: true }),
        });
        
        if (response.ok) {
            addToast({
                title: 'Success',
                message: 'Primary CV updated.',
                type: 'success'
            });
            router.reload();
        } else {
            addToast({
                title: 'Error',
                message: 'Failed to update primary CV.',
                type: 'error'
            });
        }
    } catch (error) {
        addToast({
            title: 'Error',
            message: 'A network error occurred.',
            type: 'error'
        });
    }
};
</script>

<template>
    <Head title="My CVs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">My CVs</h1>
                    <p class="text-muted-foreground mt-1">Manage your resumes and get AI-powered suggestions</p>
                </div>
                <Button as-child>
                    <Link :href="cvCreate().url">
                        <Plus class="h-4 w-4 mr-2" />
                        Create CV
                    </Link>
                </Button>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search CVs..."
                        class="pl-10"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <ShimmerCard v-for="i in 6" :key="i" />
            </div>

            <!-- CV Grid -->
            <div v-else-if="(filteredCvs?.length || 0) > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card
                    v-for="cv in filteredCvs"
                    :key="cv.id"
                    class="group hover:shadow-md transition-all duration-200 relative"
                    :class="{ 'ring-2 ring-primary': cv.is_primary }"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <FileText class="h-6 w-6 text-primary" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg flex items-center gap-2">
                                        {{ cv.name }}
                                        <Star
                                            v-if="cv.is_primary"
                                            class="h-4 w-4 text-yellow-500 fill-yellow-500"
                                        />
                                    </CardTitle>
                                    <Badge :class="templateColors[cv.template]" class="mt-1">
                                        {{ templateLabels[cv.template] }}
                                    </Badge>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/cvs/${cv.id}`" class="flex items-center">
                                            <Eye class="h-4 w-4 mr-2" />
                                            View
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/cvs/${cv.id}/edit`" class="flex items-center">
                                            <Edit class="h-4 w-4 mr-2" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem v-if="cv.file_path" class="flex items-center">
                                        <Download class="h-4 w-4 mr-2" />
                                        Download
                                    </DropdownMenuItem>
                                    <DropdownMenuItem v-if="!cv.is_primary" @click="setPrimary(cv.id)" class="flex items-center">
                                        <Star class="h-4 w-4 mr-2" />
                                        Set as Primary
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-red-600 dark:text-red-400 flex items-center"
                                        @click="confirmDelete(cv.id)"
                                    >
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Calendar class="h-4 w-4" />
                            <span>Updated {{ formatDate(cv.updated_at) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="py-16">
                <CardContent class="flex flex-col items-center justify-center text-center">
                    <FileText class="h-12 w-12 text-muted-foreground/50 mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No CVs yet</h3>
                    <p class="text-muted-foreground mb-4">
                        Create your first CV to get started with AI-powered suggestions
                    </p>
                    <Button as-child>
                        <Link :href="cvCreate().url">
                            <Plus class="h-4 w-4 mr-2" />
                            Create Your First CV
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="props.cvs.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in props.cvs.links" :key="link.label">
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

    <ConfirmDeleteModal
        v-model:open="isDeleteModalOpen"
        title="Delete CV"
        description="Are you sure you want to delete this CV? This action cannot be undone."
        @confirm="deleteCv"
    />
</template>
