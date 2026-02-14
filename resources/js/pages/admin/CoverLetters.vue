<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Search,
    Mail,
    User,
    Plus,
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
    Copy,
    Sparkles,
    X,
    Briefcase,
    Calendar,
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

interface CoverLetter {
    id: number;
    name: string;
    tone: string;
    content_preview: string;
    user: UserType;
    job_application: {
        id: number;
        title: string;
        company_name: string | null;
    } | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    coverLetters: {
        data: CoverLetter[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        total: number;
    };
    filters: {
        search?: string;
        user_id?: number | string;
        tone?: string;
    };
    stats: {
        total_cover_letters: number;
        cover_letters_today: number;
        with_job_applications: number;
    };
    tones: Record<string, string>;
    users: UserType[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const search = ref(props.filters.search || '');
const selectedUserId = ref(props.filters.user_id?.toString() || '');
const selectedTone = ref(props.filters.tone || '');

const showDeleteDialog = ref(false);
const letterToDelete = ref<CoverLetter | null>(null);
const showDuplicateDialog = ref(false);
const letterToDuplicate = ref<CoverLetter | null>(null);

// Delete form
const deleteForm = useForm({});

// Duplicate form
const duplicateForm = useForm({
    user_id: '' as string,
    name: '',
});

const hasActiveFilters = computed(() => {
    return search.value || selectedUserId.value || selectedTone.value;
});

const applyFilters = () => {
    router.get('/admin/cover-letters', {
        search: search.value || undefined,
        user_id: selectedUserId.value || undefined,
        tone: selectedTone.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const performSearch = useDebounceFn(applyFilters, 300);

const clearFilters = () => {
    search.value = '';
    selectedUserId.value = '';
    selectedTone.value = '';
    router.get('/admin/cover-letters', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const confirmDelete = (letter: CoverLetter) => {
    letterToDelete.value = letter;
    showDeleteDialog.value = true;
};

const deleteCoverLetter = () => {
    if (!letterToDelete.value) return;
    
    deleteForm.delete(`/admin/cover-letters/${letterToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            letterToDelete.value = null;
            toast({
                title: 'Cover Letter Deleted',
                description: 'The cover letter has been deleted successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to delete the cover letter.',
                variant: 'destructive',
            });
        },
    });
};

const openDuplicateDialog = (letter: CoverLetter) => {
    letterToDuplicate.value = letter;
    duplicateForm.user_id = letter.user.id.toString();
    duplicateForm.name = `${letter.name} (Copy)`;
    showDuplicateDialog.value = true;
};

const duplicateCoverLetter = () => {
    if (!letterToDuplicate.value) return;
    
    duplicateForm.post(`/admin/cover-letters/${letterToDuplicate.value.id}/duplicate`, {
        preserveScroll: true,
        onSuccess: () => {
            showDuplicateDialog.value = false;
            letterToDuplicate.value = null;
            duplicateForm.reset();
            toast({
                title: 'Cover Letter Duplicated',
                description: 'The cover letter has been duplicated successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to duplicate the cover letter.',
                variant: 'destructive',
            });
        },
    });
};

const getToneBadgeVariant = (tone: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        professional: 'default',
        enthusiastic: 'secondary',
        confident: 'default',
        conversational: 'outline',
        formal: 'secondary',
    };
    return variants[tone] || 'outline';
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Cover Letters' }]">
        <Head title="Manage Cover Letters" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Cover Letter Management</h1>
                    <p class="text-muted-foreground">Manage all cover letters on the platform</p>
                </div>
                <Button as-child>
                    <Link href="/admin/cover-letters/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Create Cover Letter
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-blue-500/10 p-3">
                            <Mail class="h-6 w-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Total Cover Letters</p>
                            <p class="text-2xl font-bold">{{ stats.total_cover_letters }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-amber-500/10 p-3">
                            <Briefcase class="h-6 w-6 text-amber-500" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Linked to Applications</p>
                            <p class="text-2xl font-bold">{{ stats.with_job_applications }}</p>
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
                            <p class="text-2xl font-bold">{{ stats.cover_letters_today }}</p>
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
                        placeholder="Search cover letters or users..." 
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
                <Select v-model="selectedTone" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-[180px]">
                        <SelectValue placeholder="Filter by tone" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">All Tones</SelectItem>
                        <SelectItem 
                            v-for="(label, key) in tones" 
                            :key="key" 
                            :value="key"
                        >
                            {{ label }}
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

            <!-- Cover Letters Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">Name</th>
                                    <th class="text-left p-4 font-medium text-sm">Tone</th>
                                    <th class="text-left p-4 font-medium text-sm">User</th>
                                    <th class="text-left p-4 font-medium text-sm">Job Application</th>
                                    <th class="text-left p-4 font-medium text-sm">Created</th>
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="letter in coverLetters.data" 
                                    :key="letter.id" 
                                    class="border-b last:border-0 hover:bg-muted/50 transition-colors"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <Mail class="h-4 w-4 text-muted-foreground" />
                                            <Link 
                                                :href="`/admin/cover-letters/${letter.id}`"
                                                class="font-medium hover:underline"
                                            >
                                                {{ letter.name }}
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge :variant="getToneBadgeVariant(letter.tone)" class="capitalize">
                                            {{ letter.tone }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <User class="h-4 w-4 text-muted-foreground" />
                                            <div>
                                                <Link 
                                                    :href="`/admin/users/${letter.user.id}`"
                                                    class="font-medium text-sm hover:underline"
                                                >
                                                    {{ letter.user.name }}
                                                </Link>
                                                <p class="text-xs text-muted-foreground">{{ letter.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <template v-if="letter.job_application">
                                            <div class="flex items-center gap-2">
                                                <Briefcase class="h-4 w-4 text-muted-foreground" />
                                                <div>
                                                    <p class="font-medium text-sm">{{ letter.job_application.title }}</p>
                                                    <p v-if="letter.job_application.company_name" class="text-xs text-muted-foreground">
                                                        {{ letter.job_application.company_name }}
                                                    </p>
                                                </div>
                                            </div>
                                        </template>
                                        <span v-else class="text-sm text-muted-foreground">Not linked</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                            <Calendar class="h-3 w-3" />
                                            {{ letter.created_at }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/admin/cover-letters/${letter.id}`">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/admin/cover-letters/${letter.id}/edit`">
                                                        <Pencil class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="openDuplicateDialog(letter)">
                                                    <Copy class="mr-2 h-4 w-4" />
                                                    Duplicate
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    class="text-destructive focus:text-destructive"
                                                    @click="confirmDelete(letter)"
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="coverLetters.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-muted-foreground">
                                        No cover letters found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="coverLetters.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in coverLetters.links" :key="link.label">
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

        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Cover Letter</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "{{ letterToDelete?.name }}"? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        :disabled="deleteForm.processing"
                        @click="deleteCoverLetter"
                    >
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Duplicate Dialog -->
        <Dialog v-model:open="showDuplicateDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Duplicate Cover Letter</DialogTitle>
                    <DialogDescription>
                        Create a copy of this cover letter. You can assign it to the same or a different user.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="duplicate-name">New Name</Label>
                        <Input 
                            id="duplicate-name" 
                            v-model="duplicateForm.name"
                            placeholder="Enter cover letter name"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="duplicate-user">Assign to User</Label>
                        <Select v-model="duplicateForm.user_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a user" />
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
                        :disabled="duplicateForm.processing"
                        @click="duplicateCoverLetter"
                    >
                        <Copy class="mr-2 h-4 w-4" />
                        {{ duplicateForm.processing ? 'Duplicating...' : 'Duplicate' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
