<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Mail,
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Copy,
    Calendar,
    Briefcase,
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
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

interface JobApplication {
    id: number;
    title: string;
    company?: {
        name: string;
    };
}

interface CoverLetter {
    id: number;
    name: string;
    tone: string;
    content?: string;
    job_application?: JobApplication | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    coverLetters: {
        data: CoverLetter[];
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
    { title: 'Cover Letters', href: '#' },
];

const search = ref('');
const isLoading = ref(true);

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 500);
});

const toneLabels: Record<string, string> = {
    professional: 'Professional',
    enthusiastic: 'Enthusiastic',
    confident: 'Confident',
    conversational: 'Conversational',
    formal: 'Formal',
};

const toneColors: Record<string, string> = {
    professional: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    enthusiastic: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    confident: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    conversational: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    formal: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

const filteredCoverLetters = computed(() => {
    if (!props.coverLetters?.data) return [];
    if (!search.value) return props.coverLetters.data;
    const searchLower = search.value.toLowerCase();
    return props.coverLetters.data.filter(letter =>
        letter.name.toLowerCase().includes(searchLower) ||
        letter.tone.toLowerCase().includes(searchLower) ||
        letter.job_application?.title?.toLowerCase().includes(searchLower)
    );
});

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const getPreview = (content?: string) => {
    if (!content) return 'No content yet...';
    return content.length > 100 ? content.substring(0, 100) + '...' : content;
};

const letterToDelete = ref<number | null>(null);
const isDeleteModalOpen = ref(false);

const confirmDelete = (id: number) => {
    letterToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const deleteCoverLetter = () => {
    if (letterToDelete.value) {
        router.delete(`/cover-letters/${letterToDelete.value}`);
        isDeleteModalOpen.value = false;
        letterToDelete.value = null;
    }
};

const copyToClipboard = async (content?: string) => {
    if (content) {
        await navigator.clipboard.writeText(content);
    }
};
</script>

<template>
    <Head title="Cover Letters" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Cover Letters</h1>
                    <p class="text-muted-foreground mt-1">Create and manage AI-powered cover letters</p>
                </div>
                <Button as-child>
                    <Link href="/cover-letters/create">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Cover Letter
                    </Link>
                </Button>
            </div>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search cover letters..."
                        class="pl-10"
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <ShimmerCard v-for="i in 6" :key="i" />
            </div>

            <!-- Cover Letters Grid -->
            <div v-else-if="filteredCoverLetters.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card
                    v-for="letter in filteredCoverLetters"
                    :key="letter.id"
                    class="group hover:shadow-md transition-all duration-200"
                >
                    <CardHeader class="pb-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <Mail class="h-6 w-6 text-primary" />
                                </div>
                                <div>
                                    <CardTitle class="text-lg">
                                        <Link :href="`/cover-letters/${letter.id}`" class="hover:text-primary transition-colors">
                                            {{ letter.name }}
                                        </Link>
                                    </CardTitle>
                                    <Badge :class="toneColors[letter.tone]" class="mt-1">
                                        {{ toneLabels[letter.tone] }}
                                    </Badge>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="md:opacity-0 group-hover:opacity-100 transition-opacity text-foreground">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/cover-letters/${letter.id}`" class="flex items-center">
                                            <Eye class="h-4 w-4 mr-2" />
                                            View
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link :href="`/cover-letters/${letter.id}/edit`" class="flex items-center">
                                            <Edit class="h-4 w-4 mr-2" />
                                            Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="copyToClipboard(letter.content)" class="flex items-center">
                                        <Copy class="h-4 w-4 mr-2" />
                                        Copy
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        class="text-red-600 dark:text-red-400 flex items-center"
                                        @click="confirmDelete(letter.id)"
                                    >
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Delete
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <p class="text-sm text-muted-foreground line-clamp-2">
                            {{ getPreview(letter.content) }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <div v-if="letter.job_application" class="flex items-center gap-1">
                                <Briefcase class="h-3 w-3" />
                                <span>{{ letter.job_application.title }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Calendar class="h-3 w-3" />
                                <span>{{ formatDate(letter.updated_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="py-16">
                <CardContent class="flex flex-col items-center justify-center text-center">
                    <Mail class="h-12 w-12 text-muted-foreground/50 mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No cover letters yet</h3>
                    <p class="text-muted-foreground mb-4">
                        Create your first cover letter with AI assistance
                    </p>
                    <Button as-child>
                        <Link href="/cover-letters/create">
                            <Plus class="h-4 w-4 mr-2" />
                            Create Your First Cover Letter
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div v-if="props.coverLetters.last_page > 1" class="flex items-center justify-center gap-2">
                <template v-for="link in props.coverLetters.links" :key="link.label">
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
        title="Delete Cover Letter"
        description="Are you sure you want to delete this cover letter? This action cannot be undone."
        @confirm="deleteCoverLetter"
    />
</template>
