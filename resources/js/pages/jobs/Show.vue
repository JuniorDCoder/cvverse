<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as jobsIndex, edit as jobEdit, destroy as jobDestroy } from '@/routes/jobs';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    ArrowLeft,
    Building2,
    MapPin,
    Briefcase,
    Calendar,
    ExternalLink,
    Edit,
    Trash2,
    Sparkles,
    FileText,
    Mail,
    Target,
    Clock,
    DollarSign,
    GraduationCap,
    CheckCircle,
    XCircle,
    Loader2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Company {
    id: number;
    name: string;
    website?: string;
    industry?: string;
    description?: string;
    ai_insights?: any;
}

interface Cv {
    id: number;
    name: string;
}

interface CoverLetter {
    id: number;
    name: string;
}

interface CvVersion {
    id: number;
    version_number: number;
    change_summary?: string;
    created_at: string;
}

interface JobApplication {
    id: number;
    title: string;
    status: string;
    company?: Company | null;
    cv?: Cv | null;
    cover_letter?: CoverLetter | null;
    cv_versions: CvVersion[];
    description?: string | null;
    requirements?: string[] | null;
    skills?: string[] | null;
    salary_range?: string | null;
    location?: string | null;
    work_type?: string | null;
    experience_level?: string | null;
    source_url?: string | null;
    applied_at?: string | null;
    deadline?: string | null;
    notes?: string | null;
    ai_analysis?: any | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    application: JobApplication;
    cvs: Cv[];
    coverLetters: CoverLetter[];
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Job Applications', href: jobsIndex().url },
    { title: props.application.title, href: '#' },
];

const isAnalyzing = ref(false);
const analysis = ref(props.application.ai_analysis);
const isUpdatingStatus = ref(false);

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
    return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const updateStatus = async (newStatus: string) => {
    isUpdatingStatus.value = true;
    try {
        await fetch(`/jobs/${props.application.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ status: newStatus }),
        });
        router.reload({ only: ['application'] });
    } finally {
        isUpdatingStatus.value = false;
    }
};

const analyzeWithAI = async () => {
    isAnalyzing.value = true;
    try {
        const response = await fetch(`/jobs/${props.application.id}/analyze`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
        });
        const data = await response.json();
        if (data.success) {
            analysis.value = data.analysis;
        }
    } finally {
        isAnalyzing.value = false;
    }
};

const deleteApplication = () => {
    if (confirm('Are you sure you want to delete this application?')) {
        router.delete(`/jobs/${props.application.id}`);
    }
};

const formatMarkdown = (text: string) => {
    // Basic bold formatting
    let formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold text-foreground">$1</strong>');
    return formatted;
};
</script>

<template>
    <Head :title="application.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <Button variant="ghost" size="icon" as-child class="shrink-0 mt-1">
                        <Link :href="jobsIndex().url">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl md:text-3xl font-bold tracking-tight">
                                {{ application.title }}
                            </h1>
                            <Badge :class="statusColors[application.status]" class="text-sm">
                                {{ statusLabels[application.status] }}
                            </Badge>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-muted-foreground">
                            <div v-if="application.company" class="flex items-center gap-1">
                                <Building2 class="h-4 w-4" />
                                <span>{{ application.company.name }}</span>
                            </div>
                            <div v-if="application.location" class="flex items-center gap-1">
                                <MapPin class="h-4 w-4" />
                                <span>{{ application.location }}</span>
                            </div>
                            <div v-if="application.work_type" class="flex items-center gap-1">
                                <Briefcase class="h-4 w-4" />
                                <span class="capitalize">{{ application.work_type }}</span>
                            </div>
                            <div v-if="application.salary_range" class="flex items-center gap-1">
                                <DollarSign class="h-4 w-4" />
                                <span>{{ application.salary_range }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-12 lg:ml-0">
                    <a
                        v-if="application.source_url"
                        :href="application.source_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex"
                    >
                        <Button variant="outline" size="sm">
                            <ExternalLink class="h-4 w-4 mr-2" />
                            View Posting
                        </Button>
                    </a>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/jobs/${application.id}/edit`">
                            <Edit class="h-4 w-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteApplication">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Status Update -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Update Status</CardTitle>
                            <CardDescription>Track your application progress</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-for="status in statuses"
                                    :key="status"
                                    :variant="application.status === status ? 'default' : 'outline'"
                                    size="sm"
                                    :disabled="isUpdatingStatus"
                                    @click="updateStatus(status)"
                                >
                                    <Loader2 v-if="isUpdatingStatus && application.status !== status" class="h-3 w-3 mr-1 animate-spin" />
                                    {{ statusLabels[status] }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Description -->
                    <Card v-if="application.description">
                        <CardHeader>
                            <CardTitle>Job Description</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="whitespace-pre-wrap text-muted-foreground">{{ application.description }}</p>
                        </CardContent>
                    </Card>

                    <!-- Skills -->
                    <Card v-if="application.skills && application.skills.length > 0">
                        <CardHeader>
                            <CardTitle>Required Skills</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="skill in application.skills" :key="skill" variant="secondary">
                                    {{ skill }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Requirements -->
                    <Card v-if="application.requirements && application.requirements.length > 0">
                        <CardHeader>
                            <CardTitle>Requirements</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-2">
                                <li v-for="(req, index) in application.requirements" :key="index" class="flex items-start gap-2">
                                    <CheckCircle class="h-5 w-5 text-green-500 shrink-0 mt-0.5" />
                                    <span>{{ req }}</span>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>

                    <!-- AI Analysis -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Sparkles class="h-5 w-5 text-primary" />
                                    AI Analysis
                                </CardTitle>
                                <CardDescription>Get AI-powered insights on your fit for this role</CardDescription>
                            </div>
                            <Button 
                                @click="analyzeWithAI" 
                                :disabled="isAnalyzing"
                                size="sm"
                            >
                                <Loader2 v-if="isAnalyzing" class="h-4 w-4 mr-2 animate-spin" />
                                <Sparkles v-else class="h-4 w-4 mr-2" />
                                {{ analysis ? 'Re-analyze' : 'Analyze' }}
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="isAnalyzing" class="space-y-4">
                                <div class="flex items-center gap-3 p-4 rounded-lg bg-primary/5 border border-primary/20">
                                    <div class="animate-pulse">
                                        <Sparkles class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium">Analyzing your profile against this job...</p>
                                        <p class="text-sm text-muted-foreground">This may take a moment.</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <Skeleton class="h-8 w-32" />
                                    <Skeleton class="h-4 w-full" />
                                    <Skeleton class="h-4 w-4/5" />
                                    <Skeleton class="h-4 w-3/5" />
                                </div>
                            </div>
                            <div v-else-if="analysis" class="space-y-6">
                                <!-- Match Score -->
                                <div class="flex items-center gap-4">
                                    <div 
                                        class="h-20 w-20 rounded-full flex items-center justify-center text-2xl font-bold"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': analysis.match_score >= 70,
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300': analysis.match_score >= 40 && analysis.match_score < 70,
                                            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': analysis.match_score < 40,
                                        }"
                                    >
                                        {{ analysis.match_score }}%
                                    </div>
                                    <div>
                                        <p class="font-semibold text-lg">Match Score</p>
                                        <p class="text-muted-foreground">
                                            {{ analysis.match_score >= 70 ? 'Great match!' : analysis.match_score >= 40 ? 'Good potential' : 'Consider improving your CV' }}
                                        </p>
                                    </div>
                                </div>

                                <Separator />

                                <!-- Skills to Add -->
                                <div v-if="analysis.skills_to_add?.length">
                                    <h4 class="font-semibold mb-2">Skills to Highlight</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-for="skill in analysis.skills_to_add" :key="skill" variant="outline" class="border-primary text-primary">
                                            + {{ skill }}
                                        </Badge>
                                    </div>
                                </div>

                                <!-- Recommendations -->
                                <div v-if="analysis.overall_recommendations?.length">
                                    <h4 class="font-semibold mb-2">Recommendations</h4>
                                    <ul class="space-y-2">
                                        <li v-for="(rec, index) in analysis.overall_recommendations" :key="index" class="flex items-start gap-2 text-sm">
                                            <Target class="h-4 w-4 text-primary shrink-0 mt-0.5" />
                                            <span v-html="formatMarkdown(rec)"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <Sparkles class="h-12 w-12 mx-auto mb-4 text-muted-foreground/50" />
                                <p class="text-muted-foreground">
                                    Click "Analyze" to get AI-powered insights on how well your profile matches this job.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Notes -->
                    <Card v-if="application.notes">
                        <CardHeader>
                            <CardTitle>Notes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="whitespace-pre-wrap text-muted-foreground">{{ application.notes }}</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    Added
                                </span>
                                <span class="font-medium">{{ formatDate(application.created_at) }}</span>
                            </div>
                            <div v-if="application.applied_at" class="flex items-center justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4" />
                                    Applied
                                </span>
                                <span class="font-medium">{{ formatDate(application.applied_at) }}</span>
                            </div>
                            <div v-if="application.deadline" class="flex items-center justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <Clock class="h-4 w-4" />
                                    Deadline
                                </span>
                                <span class="font-medium">{{ formatDate(application.deadline) }}</span>
                            </div>
                            <div v-if="application.experience_level" class="flex items-center justify-between">
                                <span class="text-muted-foreground flex items-center gap-2">
                                    <GraduationCap class="h-4 w-4" />
                                    Level
                                </span>
                                <span class="font-medium capitalize">{{ application.experience_level }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attached Documents -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Documents</CardTitle>
                            <CardDescription>CV and cover letter for this application</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Link
                                v-if="application.cv"
                                :href="`/cvs/${application.cv.id}`"
                                class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                            >
                                <FileText class="h-5 w-5 text-primary" />
                                <div class="flex-1">
                                    <p class="font-medium">{{ application.cv.name }}</p>
                                    <p class="text-xs text-muted-foreground">CV</p>
                                </div>
                            </Link>
                            <Link
                                v-if="application.cover_letter"
                                :href="`/cover-letters/${application.cover_letter.id}`"
                                class="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                            >
                                <Mail class="h-5 w-5 text-primary" />
                                <div class="flex-1">
                                    <p class="font-medium">{{ application.cover_letter.name }}</p>
                                    <p class="text-xs text-muted-foreground">Cover Letter</p>
                                </div>
                            </Link>
                            <div v-if="!application.cv && !application.cover_letter" class="text-center py-4 text-muted-foreground">
                                <p class="text-sm">No documents attached</p>
                                <Button variant="link" size="sm" as-child class="mt-1">
                                    <Link :href="`/jobs/${application.id}/edit`">Add documents</Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Company Info -->
                    <Card v-if="application.company">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Building2 class="h-5 w-5" />
                                {{ application.company.name }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <p v-if="application.company.description" class="text-sm text-muted-foreground">
                                    {{ application.company.description }}
                                </p>
                                <div v-if="application.company.industry" class="flex items-center gap-2 text-sm">
                                    <Badge variant="outline">{{ application.company.industry }}</Badge>
                                </div>
                                <a
                                    v-if="application.company.website"
                                    :href="application.company.website"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-1 text-sm text-primary hover:underline"
                                >
                                    <ExternalLink class="h-3 w-3" />
                                    Visit website
                                </a>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
