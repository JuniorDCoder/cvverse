<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit,
    Trash2,
    Mail,
    Sparkles,
    Copy,
    Download,
    Loader2,
    RefreshCcw,
    Briefcase,
    Building2,
    Calendar,
    CheckCircle,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { ref } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
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
    content: string;
    job_application?: JobApplication | null;
    ai_improvements?: any | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    coverLetter: CoverLetter;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cover Letters', href: '/cover-letters' },
    { title: props.coverLetter.name, href: '#' },
];

const isImproving = ref(false);
const improvements = ref(props.coverLetter.ai_improvements);
const copied = ref(false);

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

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const copyToClipboard = async () => {
    await navigator.clipboard.writeText(props.coverLetter.content);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const getImprovements = async () => {
    isImproving.value = true;
    try {
        const response = await fetch(`/cover-letters/${props.coverLetter.id}/improve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
        });
        const data = await response.json();
        if (data.success && data.result) {
            improvements.value = data.result;
        }
    } finally {
        isImproving.value = false;
    }
};

const isDeleteModalOpen = ref(false);

const confirmDelete = () => {
    isDeleteModalOpen.value = true;
};

const deleteCoverLetter = () => {
    router.delete(`/cover-letters/${props.coverLetter.id}`);
    isDeleteModalOpen.value = false;
};
</script>

<template>
    <Head :title="coverLetter.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <Button variant="ghost" size="icon" as-child class="shrink-0 mt-1">
                        <Link href="/cover-letters">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl md:text-3xl font-bold tracking-tight">{{ coverLetter.name }}</h1>
                            <Badge :class="toneColors[coverLetter.tone]">
                                {{ toneLabels[coverLetter.tone] }}
                            </Badge>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-2 text-muted-foreground">
                            <div v-if="coverLetter.job_application" class="flex items-center gap-1">
                                <Briefcase class="h-4 w-4" />
                                <span>{{ coverLetter.job_application.title }}</span>
                            </div>
                            <div v-if="coverLetter.job_application?.company" class="flex items-center gap-1">
                                <Building2 class="h-4 w-4" />
                                <span>{{ coverLetter.job_application.company.name }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Calendar class="h-4 w-4" />
                                <span>Updated {{ formatDate(coverLetter.updated_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-12 lg:ml-0">
                    <Button variant="outline" size="sm" @click="copyToClipboard">
                        <CheckCircle v-if="copied" class="h-4 w-4 mr-2 text-green-500" />
                        <Copy v-else class="h-4 w-4 mr-2" />
                        {{ copied ? 'Copied!' : 'Copy' }}
                    </Button>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/cover-letters/${coverLetter.id}/edit`">
                            <Edit class="h-4 w-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="confirmDelete">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Mail class="h-5 w-5" />
                                Content
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                <p class="whitespace-pre-wrap">{{ coverLetter.content }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- AI Improvements -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Sparkles class="h-5 w-5 text-primary" />
                                    AI Suggestions
                                </CardTitle>
                                <CardDescription>Get tips to improve your letter</CardDescription>
                            </div>
                            <Button
                                size="icon"
                                variant="ghost"
                                @click="getImprovements"
                                :disabled="isImproving"
                            >
                                <Loader2 v-if="isImproving" class="h-4 w-4 animate-spin" />
                                <RefreshCcw v-else class="h-4 w-4" />
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="isImproving" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="animate-pulse">
                                        <Sparkles class="h-5 w-5 text-primary" />
                                    </div>
                                    <span class="text-sm">Analyzing your cover letter...</span>
                                </div>
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-4/5" />
                                <Skeleton class="h-4 w-3/5" />
                            </div>
                            <div v-else-if="improvements" class="space-y-4">
                <!-- Strength Score -->
                <div v-if="improvements.strength_score !== undefined" class="text-center py-4">
                    <div
                        class="inline-flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold"
                        :class="{
                            'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': improvements.strength_score >= 70,
                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300': improvements.strength_score >= 40 && improvements.strength_score < 70,
                            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': improvements.strength_score < 40,
                        }"
                    >
                        {{ improvements.strength_score }}
                    </div>
                    <p class="text-sm text-muted-foreground mt-2">Strength Score</p>
                </div>

                <Separator v-if="improvements.strength_score !== undefined" />

                <!-- Changes Made -->
                <div v-if="improvements.changes_made?.length" class="space-y-4 pt-2">
                    <h4 class="font-semibold text-sm text-blue-600 dark:text-blue-400 flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        Changes Made
                    </h4>
                    <div class="space-y-4">
                        <div v-for="(change, index) in improvements.changes_made" :key="index" class="p-3 rounded-lg bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30">
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">{{ typeof change === 'string' ? change : change.change }}</p>
                            <div v-if="typeof change !== 'string' && change.why" 
                                class="text-xs text-blue-700/80 dark:text-blue-300/80 mt-1.5 prose prose-xs dark:prose-invert max-w-none"
                                v-html="marked(typeof (change.why || change) === 'string' ? (change.why || change) : JSON.stringify(change.why || change))"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Suggestions -->
                <div v-if="improvements.suggestions?.length" class="space-y-4 pt-4 border-t">
                    <h4 class="font-semibold text-sm text-primary flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        Additional Suggestions
                    </h4>
                    <div class="space-y-3">
                        <div v-for="(suggestion, index) in improvements.suggestions" :key="index" 
                            class="p-3 rounded-lg bg-primary/5 border border-primary/10 prose prose-sm dark:prose-invert max-w-none"
                            v-html="marked(typeof suggestion === 'string' ? suggestion : JSON.stringify(suggestion))"
                        ></div>
                    </div>
                </div>

                <!-- Improved Content Available -->
                <div v-if="improvements.improved_content" class="space-y-4 pt-4 border-t">
                    <div class="flex items-center justify-between">
                        <h4 class="font-semibold text-sm text-green-600 dark:text-green-400">Improved Version Available</h4>
                        <Button size="sm" variant="outline" as-child>
                            <Link :href="`/cover-letters/${coverLetter.id}/edit`">
                                <Edit class="h-3 w-3 mr-1" />
                                Apply
                            </Link>
                        </Button>
                    </div>
                    <p class="text-xs text-muted-foreground">Click "Apply" to view and use the improved version</p>
                </div>
            </div>
                            <div v-else class="text-center py-6">
                                <Sparkles class="h-10 w-10 mx-auto mb-3 text-muted-foreground/50" />
                                <p class="text-sm text-muted-foreground">
                                    Click the refresh button to get AI suggestions for improving your cover letter.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Linked Job -->
                    <Card v-if="coverLetter.job_application">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Briefcase class="h-5 w-5" />
                                Linked Job
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Link
                                :href="`/jobs/${coverLetter.job_application.id}`"
                                class="flex items-start gap-3 p-3 rounded-lg border hover:bg-muted/50 transition-colors"
                            >
                                <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <Briefcase class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ coverLetter.job_application.title }}</p>
                                    <p v-if="coverLetter.job_application.company" class="text-sm text-muted-foreground">
                                        {{ coverLetter.job_application.company.name }}
                                    </p>
                                </div>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
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
