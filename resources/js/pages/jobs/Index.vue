<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Briefcase, 
    Plus, 
    Search, 
    Building2, 
    MapPin, 
    Calendar,
    ExternalLink,
    MoreHorizontal,
    Trash2,
    Edit,
    Eye
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as jobsIndex, create as jobCreate } from '@/routes/jobs';
import { type BreadcrumbItem } from '@/types';

interface Company {
    id: number;
    name: string;
}

interface JobApplication {
    id: number;
    title: string;
    status: string;
    company?: Company | null;
    location?: string | null;
    work_type?: string | null;
    salary_range?: string | null;
    source_url?: string | null;
    applied_at?: string | null;
    deadline?: string | null;
    created_at: string;
}

interface PaginatedData {
    data: JobApplication[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props {
    applications: PaginatedData;
    filters: { status?: string; search?: string };
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Job Applications', href: jobsIndex().url },
];

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const isLoading = ref(false);

const statusColors: Record<string, string> = {
    saved: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    applied: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    interviewing: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    offered: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    withdrawn: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
};

const statusLabels: Record<string, string> = {
    saved: 'Saved',
    applied: 'Applied',
    interviewing: 'Interviewing',
    offered: 'Offered',
    rejected: 'Rejected',
    withdrawn: 'Withdrawn',
};

const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const applyFilters = () => {
    isLoading.value = true;
    router.get(jobsIndex().url, {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});

watch(statusFilter, applyFilters);

const appToDelete = ref<number | null>(null);
const isDeleteModalOpen = ref(false);

const confirmDelete = (id: number) => {
    appToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const deleteApplication = () => {
    if (appToDelete.value) {
        router.delete(`/jobs/${appToDelete.value}`);
        isDeleteModalOpen.value = false;
        appToDelete.value = null;
    }
};
</script>

<template>
    <Head title="Job Applications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Job Applications</h1>
                    <p class="text-muted-foreground mt-1">
                        Track and manage all your job applications in one place.
                    </p>
                </div>
                <Button as-child size="lg">
                    <Link :href="jobCreate().url">
                        <Plus class="h-4 w-4 mr-2" />
                        Track New Job
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search by job title or company..."
                                class="pl-10"
                            />
                        </div>
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-full sm:w-[180px]">
                                <SelectValue placeholder="Filter by status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem v-for="status in statuses" :key="status" :value="status">
                                    {{ statusLabels[status] || status }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardContent>
            </Card>

            <!-- Results -->
            <div v-if="isLoading" class="space-y-4">
                <div v-for="i in 5" :key="i" class="p-4 rounded-xl border bg-card">
                    <div class="flex items-start gap-4">
                        <Skeleton class="h-12 w-12 rounded-lg" />
                        <div class="flex-1 space-y-2">
                            <Skeleton class="h-5 w-2/3" />
                            <Skeleton class="h-4 w-1/2" />
                            <div class="flex gap-4">
                                <Skeleton class="h-4 w-24" />
                                <Skeleton class="h-4 w-24" />
                            </div>
                        </div>
                        <Skeleton class="h-6 w-20 rounded-full" />
                    </div>
                </div>
            </div>

            <div v-else-if="applications.data.length === 0" class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-muted flex items-center justify-center">
                    <Briefcase class="h-10 w-10 text-muted-foreground" />
                </div>
                <h3 class="font-semibold text-xl mb-2">No applications found</h3>
                <p class="text-muted-foreground mb-6 max-w-md mx-auto">
                    {{ filters.search || filters.status 
                        ? 'Try adjusting your filters to find what you\'re looking for.'
                        : 'Start tracking your job applications to stay organized and land your dream job!' 
                    }}
                </p>
                <Button as-child size="lg" v-if="!filters.search && !filters.status">
                    <Link :href="jobCreate().url">
                        <Plus class="h-4 w-4 mr-2" />
                        Track Your First Job
                    </Link>
                </Button>
            </div>

            <div v-else class="space-y-4">
                <Link
                    v-for="app in applications.data"
                    :key="app.id"
                    :href="`/jobs/${app.id}`"
                    class="block p-4 rounded-xl border bg-card hover:shadow-lg hover:border-primary/50 transition-all group"
                >
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center text-primary font-bold text-lg shrink-0">
                            {{ app.company?.name?.charAt(0) || app.title.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-lg group-hover:text-primary transition-colors">
                                        {{ app.title }}
                                    </h3>
                                    <div class="flex items-center gap-2 text-muted-foreground mt-1">
                                        <Building2 class="h-4 w-4 shrink-0" />
                                        <span>{{ app.company?.name || 'Unknown Company' }}</span>
                                    </div>
                                </div>
                                <Badge :class="statusColors[app.status]" class="shrink-0">
                                    {{ statusLabels[app.status] }}
                                </Badge>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-sm text-muted-foreground">
                                <div v-if="app.location" class="flex items-center gap-1">
                                    <MapPin class="h-3.5 w-3.5" />
                                    <span>{{ app.location }}</span>
                                </div>
                                <div v-if="app.work_type" class="flex items-center gap-1">
                                    <Briefcase class="h-3.5 w-3.5" />
                                    <span class="capitalize">{{ app.work_type }}</span>
                                </div>
                                <div v-if="app.salary_range" class="flex items-center gap-1">
                                    <span>💰</span>
                                    <span>{{ app.salary_range }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="h-3.5 w-3.5" />
                                    <span>Added {{ formatDate(app.created_at) }}</span>
                                </div>
                                <a
                                    v-if="app.source_url"
                                    :href="app.source_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-1 hover:text-primary transition-colors"
                                    @click.stop
                                >
                                    <ExternalLink class="h-3.5 w-3.5" />
                                    <span>View Posting</span>
                                </a>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child @click.stop.prevent>
                                <Button variant="ghost" size="icon" class="shrink-0">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child>
                                    <Link :href="`/jobs/${app.id}`" class="flex items-center">
                                        <Eye class="h-4 w-4 mr-2" />
                                        View Details
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link :href="`/jobs/${app.id}/edit`" class="flex items-center">
                                        <Edit class="h-4 w-4 mr-2" />
                                        Edit
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="text-destructive focus:text-destructive"
                                    @click.stop="confirmDelete(app.id)"
                                >
                                    <Trash2 class="h-4 w-4 mr-2" />
                                    Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </Link>

                <!-- Pagination -->
                <div v-if="applications.last_page > 1" class="flex items-center justify-center gap-2 pt-4">
                    <Button
                        v-for="link in applications.links"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        :disabled="!link.url"
                        size="sm"
                        @click="link.url && router.get(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>


    <ConfirmDeleteModal
        v-model:open="isDeleteModalOpen"
        title="Delete Job Application"
        description="Are you sure you want to delete this job application? This action cannot be undone."
        @confirm="deleteApplication"
    />
</template>
