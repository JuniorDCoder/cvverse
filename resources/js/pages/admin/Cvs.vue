<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Search,
    FileText,
    User,
    Plus,
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
    Download,
    Copy,
    Star,
    StarOff,
    Sparkles,
    X,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    user: UserType;
    created_at: string;
    updated_at: string;
}

interface Props {
    cvs: {
        data: Cv[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        user_id?: number | string;
        template?: string;
    };
    stats: {
        total_cvs: number;
        primary_cvs: number;
        cvs_today: number;
    };
    templates: string[];
    users: UserType[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const search = ref(props.filters.search || '');
const selectedUserId = ref(props.filters.user_id?.toString() || '');
const selectedTemplate = ref(props.filters.template || '');

const showDeleteDialog = ref(false);
const cvToDelete = ref<Cv | null>(null);
const showDuplicateDialog = ref(false);
const cvToDuplicate = ref<Cv | null>(null);

// Delete form
const deleteForm = useForm({});

// Duplicate form
const duplicateForm = useForm({
    user_id: '' as string,
    name: '',
});

const hasActiveFilters = computed(() => {
    return search.value || selectedUserId.value || selectedTemplate.value;
});

const applyFilters = () => {
    router.get('/admin/cvs', {
        search: search.value || undefined,
        user_id: selectedUserId.value || undefined,
        template: selectedTemplate.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const performSearch = useDebounceFn(applyFilters, 300);

const clearFilters = () => {
    search.value = '';
    selectedUserId.value = '';
    selectedTemplate.value = '';
    router.get('/admin/cvs', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const confirmDelete = (cv: Cv) => {
    cvToDelete.value = cv;
    showDeleteDialog.value = true;
};

const deleteCv = () => {
    if (!cvToDelete.value) return;
    
    deleteForm.delete(`/admin/cvs/${cvToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            cvToDelete.value = null;
            toast({
                title: 'CV Deleted',
                description: 'The CV has been deleted successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to delete the CV.',
                variant: 'destructive',
            });
        },
    });
};

const togglePrimary = (cv: Cv) => {
    router.patch(`/admin/cvs/${cv.id}/toggle-primary`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: cv.is_primary ? 'Primary Removed' : 'Set as Primary',
                description: cv.is_primary 
                    ? 'CV is no longer the primary CV.'
                    : 'CV has been set as primary.',
            });
        },
    });
};

const openDuplicateDialog = (cv: Cv) => {
    cvToDuplicate.value = cv;
    duplicateForm.user_id = cv.user.id.toString();
    duplicateForm.name = `${cv.name} (Copy)`;
    showDuplicateDialog.value = true;
};

const duplicateCv = () => {
    if (!cvToDuplicate.value) return;
    
    duplicateForm.post(`/admin/cvs/${cvToDuplicate.value.id}/duplicate`, {
        preserveScroll: true,
        onSuccess: () => {
            showDuplicateDialog.value = false;
            cvToDuplicate.value = null;
            duplicateForm.reset();
            toast({
                title: 'CV Duplicated',
                description: 'The CV has been duplicated successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to duplicate the CV.',
                variant: 'destructive',
            });
        },
    });
};

const downloadPdf = (cv: Cv) => {
    window.open(`/admin/cvs/${cv.id}/download-pdf`, '_blank');
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'CVs' }]">
        <Head title="Manage CVs" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">CV Management</h1>
                    <p class="text-muted-foreground">Manage all CVs on the platform</p>
                </div>
                <Button as-child>
                    <Link href="/admin/cvs/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Create CV
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-blue-500/10 p-3">
                            <FileText class="h-6 w-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total CVs</p>
                            <p class="text-2xl font-bold">{{ stats.total_cvs }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-amber-500/10 p-3">
                            <Star class="h-6 w-6 text-amber-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Primary CVs</p>
                            <p class="text-2xl font-bold">{{ stats.primary_cvs }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-green-500/10 p-3">
                            <Sparkles class="h-6 w-6 text-green-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Created Today</p>
                            <p class="text-2xl font-bold">{{ stats.cvs_today }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search CVs or users..." 
                        class="pl-9"
                        @input="performSearch"
                    />
                </div>
                <Select v-model="selectedUserId" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filter by user" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Users</SelectItem>
                        <SelectItem 
                            v-for="user in users" 
                            :key="user.id" 
                            :value="user.id.toString()"
                        >
                            {{ user.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Select v-model="selectedTemplate" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by template" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Templates</SelectItem>
                        <SelectItem 
                            v-for="template in templates" 
                            :key="template" 
                            :value="template"
                        >
                            {{ template }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button
                    v-if="hasActiveFilters"
                    variant="ghost"
                    size="sm"
                    @click="clearFilters"
                >
                    <X class="mr-2 h-4 w-4" />
                    Clear Filters
                </Button>
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
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="cv in cvs.data" 
                                    :key="cv.id" 
                                    class="border-b last:border-0 hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-4 w-4 text-muted-foreground" />
                                            <Link 
                                                :href="`/admin/cvs/${cv.id}`"
                                                class="font-medium hover:underline"
                                            >
                                                {{ cv.name }}
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge variant="outline" class="capitalize">
                                            {{ cv.template }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <User class="h-4 w-4 text-primary" />
                                            </div>
                                            <div>
                                                <Link 
                                                    :href="`/admin/users/${cv.user.id}`"
                                                    class="font-medium text-sm hover:underline"
                                                >
                                                    {{ cv.user.name }}
                                                </Link>
                                                <p class="text-xs text-muted-foreground">{{ cv.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge v-if="cv.is_primary" variant="default">
                                            <Star class="mr-1 h-3 w-3" />
                                            Primary
                                        </Badge>
                                        <span v-else class="text-sm text-muted-foreground">-</span>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ cv.created_at }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon">
                                                        <MoreHorizontal class="h-4 w-4" />
                                                        <span class="sr-only">Actions</span>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="`/admin/cvs/${cv.id}`">
                                                            <Eye class="mr-2 h-4 w-4" />
                                                            View Details
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="`/admin/cvs/${cv.id}/edit`">
                                                            <Pencil class="mr-2 h-4 w-4" />
                                                            Edit CV
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="downloadPdf(cv)">
                                                        <Download class="mr-2 h-4 w-4" />
                                                        Download PDF
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="togglePrimary(cv)">
                                                        <template v-if="cv.is_primary">
                                                            <StarOff class="mr-2 h-4 w-4" />
                                                            Remove Primary
                                                        </template>
                                                        <template v-else>
                                                            <Star class="mr-2 h-4 w-4" />
                                                            Set as Primary
                                                        </template>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="openDuplicateDialog(cv)">
                                                        <Copy class="mr-2 h-4 w-4" />
                                                        Duplicate
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem 
                                                        class="text-destructive focus:text-destructive"
                                                        @click="confirmDelete(cv)"
                                                    >
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Delete CV
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="cvs.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-muted-foreground">
                                        <FileText class="mx-auto h-12 w-12 text-muted-foreground/50 mb-4" />
                                        <p class="text-lg font-medium">No CVs found</p>
                                        <p class="text-sm">
                                            {{ hasActiveFilters ? 'Try adjusting your filters.' : 'Create a new CV to get started.' }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="cvs.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ cvs.data.length }} of {{ cvs.total }} CVs
                </p>
                <div class="flex items-center gap-2">
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
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete CV</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this CV? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="cvToDelete" class="py-4">
                    <Card class="bg-destructive/5 border-destructive/20">
                        <CardContent class="p-4">
                            <div class="flex items-center gap-3">
                                <FileText class="h-8 w-8 text-destructive" />
                                <div>
                                    <p class="font-semibold">{{ cvToDelete.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        Owner: {{ cvToDelete.user.name }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 text-sm text-muted-foreground">
                                <p class="font-medium text-destructive mb-2">The following will be deleted:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>All CV versions</li>
                                    <li>Associated shares and comments</li>
                                    <li>Generated PDF files</li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        :disabled="deleteForm.processing"
                        @click="deleteCv"
                    >
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete CV' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Duplicate Dialog -->
        <Dialog v-model:open="showDuplicateDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Duplicate CV</DialogTitle>
                    <DialogDescription>
                        Create a copy of this CV for the same or a different user.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>CV Name</Label>
                        <Input v-model="duplicateForm.name" placeholder="Enter name for the copy" />
                    </div>
                    <div class="space-y-2">
                        <Label>Copy to User</Label>
                        <Select v-model="duplicateForm.user_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select user" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="user in users" 
                                    :key="user.id" 
                                    :value="user.id.toString()"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDuplicateDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        :disabled="duplicateForm.processing || !duplicateForm.name || !duplicateForm.user_id"
                        @click="duplicateCv"
                    >
                        <Copy class="mr-2 h-4 w-4" />
                        {{ duplicateForm.processing ? 'Duplicating...' : 'Duplicate CV' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
