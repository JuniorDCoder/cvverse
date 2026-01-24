<script setup lang="ts">
import ShimmerCard from '@/components/ShimmerCard.vue';
import ShimmerStats from '@/components/ShimmerStats.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as jobsIndex, create as jobCreate } from '@/routes/jobs';
import { index as cvsIndex, create as cvCreate } from '@/routes/cvs';
import { index as coverLettersIndex } from '@/routes/cover-letters';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    Briefcase, 
    FileText, 
    Mail, 
    Plus, 
    TrendingUp, 
    Calendar,
    Target,
    Award,
    Sparkles,
    ArrowRight,
    Building2,
    Clock
} from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';

interface JobApplication {
    id: number;
    title: string;
    status: string;
    company?: { name: string } | null;
    created_at: string;
}

interface Stats {
    total_applications: number;
    active_applications: number;
    interviews: number;
    offers: number;
    cvs_count: number;
    cover_letters_count: number;
}

interface Props {
    recentApplications: JobApplication[];
    stats: Stats;
    statusBreakdown: Record<string, number>;
    weeklyActivity: Record<string, number>;
}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const isLoading = ref(true);

onMounted(() => {
    // Simulate initial load for shimmer effect demo
    setTimeout(() => {
        isLoading.value = false;
    }, 800);
});

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

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Welcome Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">
                        {{ greeting }}, {{ user?.name?.split(' ')[0] }}! 👋
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        Here's what's happening with your job search today.
                    </p>
                </div>
                <div class="flex gap-3">
                    <Button as-child>
                        <Link :href="jobCreate().url">
                            <Plus class="h-4 w-4 mr-2" />
                            Track New Job
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="cvCreate().url">
                            <FileText class="h-4 w-4 mr-2" />
                            Create CV
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div v-if="isLoading">
                <ShimmerStats />
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Total Applications
                        </CardTitle>
                        <div class="p-2 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                            <Briefcase class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.total_applications }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            <span class="text-green-500 font-medium">+{{ stats.active_applications }}</span> active
                        </p>
                    </CardContent>
                </Card>

                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Interviews
                        </CardTitle>
                        <div class="p-2 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <Target class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.interviews }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Scheduled or in progress
                        </p>
                    </CardContent>
                </Card>

                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Offers Received
                        </CardTitle>
                        <div class="p-2 rounded-lg bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400 group-hover:bg-green-600 group-hover:text-white transition-colors">
                            <Award class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.offers }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            Congratulations! 🎉
                        </p>
                    </CardContent>
                </Card>

                <Card class="group hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <CardHeader class="flex flex-row items-center justify-between pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Documents
                        </CardTitle>
                        <div class="p-2 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <FileText class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stats.cvs_count + stats.cover_letters_count }}</div>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ stats.cvs_count }} CVs, {{ stats.cover_letters_count }} letters
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Applications -->
                <Card class="lg:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Applications</CardTitle>
                            <CardDescription>Your latest job applications</CardDescription>
                        </div>
                        <Button variant="ghost" size="sm" as-child>
                            <Link :href="jobsIndex().url">
                                View all
                                <ArrowRight class="h-4 w-4 ml-1" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <div v-if="isLoading" class="space-y-4">
                            <div v-for="i in 5" :key="i" class="flex items-center gap-4 p-3 rounded-lg">
                                <Skeleton class="h-10 w-10 rounded-lg" />
                                <div class="flex-1 space-y-2">
                                    <Skeleton class="h-4 w-3/4" />
                                    <Skeleton class="h-3 w-1/2" />
                                </div>
                                <Skeleton class="h-6 w-20 rounded-full" />
                            </div>
                        </div>
                        <div v-else-if="recentApplications.length === 0" class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
                                <Briefcase class="h-8 w-8 text-muted-foreground" />
                            </div>
                            <h3 class="font-semibold text-lg mb-2">No applications yet</h3>
                            <p class="text-muted-foreground mb-4">Start tracking your job applications today!</p>
                            <Button as-child>
                                <Link :href="jobCreate().url">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Track Your First Job
                                </Link>
                            </Button>
                        </div>
                        <div v-else class="space-y-2">
                            <Link
                                v-for="app in recentApplications"
                                :key="app.id"
                                :href="`/jobs/${app.id}`"
                                class="flex items-center gap-4 p-3 rounded-lg hover:bg-muted/50 transition-colors group"
                            >
                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center text-primary font-semibold">
                                    {{ app.company?.name?.charAt(0) || app.title.charAt(0) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate group-hover:text-primary transition-colors">
                                        {{ app.title }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Building2 class="h-3 w-3" />
                                        <span class="truncate">{{ app.company?.name || 'Unknown Company' }}</span>
                                        <span>•</span>
                                        <Clock class="h-3 w-3" />
                                        <span>{{ formatDate(app.created_at) }}</span>
                                    </div>
                                </div>
                                <Badge :class="statusColors[app.status]">
                                    {{ statusLabels[app.status] }}
                                </Badge>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions & AI Assistant -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-primary" />
                                Quick Actions
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link
                                :href="jobCreate().url"
                                class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 hover:border-primary/50 transition-all group"
                            >
                                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                    <Plus class="h-4 w-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium group-hover:text-primary transition-colors">Track New Job</p>
                                    <p class="text-xs text-muted-foreground">Add application manually or via URL</p>
                                </div>
                                <ArrowRight class="h-4 w-4 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all" />
                            </Link>

                            <Link
                                :href="cvCreate().url"
                                class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 hover:border-primary/50 transition-all group"
                            >
                                <div class="p-2 rounded-lg bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400">
                                    <FileText class="h-4 w-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium group-hover:text-primary transition-colors">Create CV</p>
                                    <p class="text-xs text-muted-foreground">Build an AI-optimized CV</p>
                                </div>
                                <ArrowRight class="h-4 w-4 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all" />
                            </Link>

                            <Link
                                :href="coverLettersIndex().url"
                                class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 hover:border-primary/50 transition-all group"
                            >
                                <div class="p-2 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400">
                                    <Mail class="h-4 w-4" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium group-hover:text-primary transition-colors">Cover Letters</p>
                                    <p class="text-xs text-muted-foreground">Generate with AI</p>
                                </div>
                                <ArrowRight class="h-4 w-4 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all" />
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Status Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <TrendingUp class="h-5 w-5 text-primary" />
                                Application Status
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="isLoading" class="space-y-3">
                                <div v-for="i in 4" :key="i" class="flex items-center gap-3">
                                    <Skeleton class="h-2 flex-1 rounded-full" />
                                    <Skeleton class="h-4 w-8" />
                                </div>
                            </div>
                            <div v-else-if="Object.keys(statusBreakdown).length === 0" class="text-center py-6 text-muted-foreground">
                                <Calendar class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No data yet</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="(count, status) in statusBreakdown" :key="status" class="space-y-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="capitalize">{{ statusLabels[status] || status }}</span>
                                        <span class="font-medium">{{ count }}</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="{
                                                'bg-gray-500': status === 'saved',
                                                'bg-blue-500': status === 'applied',
                                                'bg-purple-500': status === 'interviewing',
                                                'bg-green-500': status === 'offered',
                                                'bg-red-500': status === 'rejected',
                                                'bg-orange-500': status === 'withdrawn',
                                            }"
                                            :style="{ width: `${(count / stats.total_applications) * 100}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
