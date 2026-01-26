<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit,
    Trash2,
    Star,
    Sparkles,
    FileText,
    User,
    Mail,
    Phone,
    MapPin,
    Globe,
    Linkedin,
    Briefcase,
    GraduationCap,
    Code,
    Award,
    Languages,
    History,
    Download,
    Loader2,
    RefreshCcw,
    Printer,
    FileType,
    MessageSquare,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import ShareDialog from '@/components/cvs/ShareDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as cvsIndex, edit as cvEdit } from '@/routes/cvs';
import { type BreadcrumbItem } from '@/types';

interface CvVersion {
    id: number;
    version_number: number;
    change_summary?: string;
    created_at: string;
}

interface Comment {
    id: number;
    content: string;
    section: string | null;
    guest_name: string | null;
    created_at: string;
    user?: {
        name: string;
    } | null;
    share?: {
        permission: string;
    } | null;
}

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    summary?: string | null;
    personal_info?: {
        full_name?: string;
        email?: string;
        phone?: string;
        location?: string;
        linkedin?: string;
        website?: string;
    } | null;
    experience?: Array<{
        company: string;
        title: string;
        location?: string;
        start_date: string;
        end_date?: string;
        current?: boolean;
        description?: string;
    }> | null;
    education?: Array<{
        institution: string;
        degree: string;
        field?: string;
        start_date?: string;
        end_date?: string;
        gpa?: string;
    }> | null;
    skills?: string[] | null;
    projects?: Array<{
        name: string;
        title?: string;
        description?: string;
        url?: string;
        technologies?: string[];
    }> | null;
    certifications?: Array<{
        name: string;
        issuer?: string;
        date?: string;
        url?: string;
    }> | null;
    languages?: Array<{
        language: string;
        proficiency: string;
    }> | null;
    ai_suggestions?: any | null;
    file_path?: string | null;
    versions: CvVersion[];
    comments: Comment[];
    created_at: string;
    updated_at: string;
}

interface Props {
    cv: Cv;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'CVs', href: cvsIndex().url },
    { title: props.cv.name, href: '#' },
];

const isGeneratingSummary = ref(false);
const isGettingSuggestions = ref(false);
const suggestions = ref(props.cv.ai_suggestions);

watch(() => props.cv.ai_suggestions, (newVal) => {
    suggestions.value = newVal;
});
const { addToast } = useToast();

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

const proficiencyLabels: Record<string, string> = {
    native: 'Native',
    fluent: 'Fluent',
    advanced: 'Advanced',
    intermediate: 'Intermediate',
    beginner: 'Beginner',
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const formatVersionDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const getSuggestions = async () => {
    isGettingSuggestions.value = true;
    try {
        const response = await fetch(`/cvs/${props.cv.id}/suggestions`, {
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
            suggestions.value = data.suggestions;
            addToast({
                title: 'Success',
                message: 'AI suggestions generated.',
                type: 'success'
            });
        } else {
            addToast({
                title: 'Error',
                message: data.message || 'Failed to generate suggestions.',
                type: 'error'
            });
        }
    } catch (error) {
        addToast({
            title: 'Error',
            message: 'A network error occurred.',
            type: 'error'
        });
    } finally {
        isGettingSuggestions.value = false;
    }
};

const generateSummary = async () => {
    isGeneratingSummary.value = true;
    try {
        const response = await fetch(`/cvs/${props.cv.id}/generate-summary`, {
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
            addToast({
                title: 'Success',
                message: 'Professional summary updated.',
                type: 'success'
            });
            router.reload({ only: ['cv'] });
        } else {
             addToast({
                title: 'Error',
                message: data.message || 'Failed to generate summary.',
                type: 'error'
            });
        }
    } catch (error) {
        addToast({
            title: 'Error',
            message: 'A network error occurred.',
            type: 'error'
        });
    } finally {
        isGeneratingSummary.value = false;
    }
};

const restoreVersion = async (versionId: number) => {
    try {
        const response = await fetch(`/cvs/${props.cv.id}/restore/${versionId}`, {
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
            addToast({
                title: 'Success',
                message: 'Version restored successfully.',
                type: 'success'
            });
            router.reload();
        } else {
            addToast({
                title: 'Error',
                message: data.message || 'Failed to restore version.',
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

const deleteCv = () => {
    router.delete(`/cvs/${props.cv.id}`);
};
</script>

<template>
    <Head :title="cv.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <Button variant="ghost" size="icon" as-child class="shrink-0 mt-1">
                        <Link :href="cvsIndex().url">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-2xl md:text-3xl font-bold tracking-tight">{{ cv.name }}</h1>
                            <Star v-if="cv.is_primary" class="h-5 w-5 text-yellow-500 fill-yellow-500" />
                            <Badge :class="templateColors[cv.template]">
                                {{ templateLabels[cv.template] }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground mt-1">
                            Last updated {{ formatVersionDate(cv.updated_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-12 lg:ml-0">
                    <Button v-if="cv.file_path" variant="outline" size="sm">
                        <Download class="h-4 w-4 mr-2" />
                        Download
                    </Button>
                    <div class="flex items-center gap-2">
                         <a :href="`/cvs/${cv.id}/export/pdf`" target="_blank">
                             <Button variant="outline" size="sm">
                                <Printer class="h-4 w-4 mr-2" />
                                PDF
                            </Button>
                        </a>
                        <a :href="`/cvs/${cv.id}/export/docx`" target="_blank">
                            <Button variant="outline" size="sm">
                                <FileText class="h-4 w-4 mr-2" />
                                DOCX
                            </Button>
                        </a>
                    </div>
                    <ShareDialog :cv-id="cv.id" />
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/cvs/${cv.id}/edit`">
                            <Edit class="h-4 w-4 mr-2" />
                            Edit
                        </Link>
                    </Button>
                    <ConfirmDeleteModal
                        title="Delete CV"
                        description="Are you sure you want to delete this CV? This action cannot be undone."
                        @confirm="deleteCv"
                    >
                        <template #trigger>
                            <Button variant="destructive" size="sm">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete
                            </Button>
                        </template>
                    </ConfirmDeleteModal>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Original Document Preview -->
                    <Card v-if="cv.file_path">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Original Document
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <iframe
                                :src="`/cvs/${cv.id}/file`"
                                class="w-full h-[600px] rounded-md border bg-muted"
                                title="Original CV Document"
                            ></iframe>
                        </CardContent>
                    </Card>

                    <!-- Personal Info -->
                    <Card v-if="cv.personal_info">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Personal Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-if="cv.personal_info.full_name" class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ cv.personal_info.full_name }}</span>
                                </div>
                                <div v-if="cv.personal_info.email" class="flex items-center gap-2">
                                    <Mail class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ cv.personal_info.email }}</span>
                                </div>
                                <div v-if="cv.personal_info.phone" class="flex items-center gap-2">
                                    <Phone class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ cv.personal_info.phone }}</span>
                                </div>
                                <div v-if="cv.personal_info.location" class="flex items-center gap-2">
                                    <MapPin class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ cv.personal_info.location }}</span>
                                </div>
                                <div v-if="cv.personal_info.linkedin" class="flex items-center gap-2">
                                    <Linkedin class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`https://${cv.personal_info.linkedin}`" target="_blank" class="text-primary hover:underline">
                                        {{ cv.personal_info.linkedin }}
                                    </a>
                                </div>
                                <div v-if="cv.personal_info.website" class="flex items-center gap-2">
                                    <Globe class="h-4 w-4 text-muted-foreground" />
                                    <a :href="`https://${cv.personal_info.website}`" target="_blank" class="text-primary hover:underline">
                                        {{ cv.personal_info.website }}
                                    </a>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Summary -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <FileText class="h-5 w-5" />
                                    Professional Summary
                                </CardTitle>
                            </div>
                            <Button
                                v-if="!cv.summary"
                                size="sm"
                                variant="outline"
                                @click="generateSummary"
                                :disabled="isGeneratingSummary"
                            >
                                <Loader2 v-if="isGeneratingSummary" class="h-4 w-4 mr-1 animate-spin" />
                                <Sparkles v-else class="h-4 w-4 mr-1" />
                                Generate with AI
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <p v-if="cv.summary" class="whitespace-pre-wrap">{{ cv.summary }}</p>
                            <p v-else class="text-muted-foreground italic">No summary yet. Click "Generate with AI" to create one.</p>
                        </CardContent>
                    </Card>

                    <!-- Experience -->
                    <Card v-if="cv.experience && cv.experience.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Briefcase class="h-5 w-5" />
                                Work Experience
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-for="(exp, index) in cv.experience" :key="index" class="relative pl-6 pb-6 last:pb-0">
                                <div class="absolute left-0 top-2 w-3 h-3 rounded-full bg-primary"></div>
                                <div v-if="index < cv.experience.length - 1" class="absolute left-1.5 top-5 w-px h-full bg-border -translate-x-1/2"></div>
                                <div>
                                    <h4 class="font-semibold">{{ exp.title }}</h4>
                                    <p class="text-primary">{{ exp.company }}</p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground mt-1">
                                        <span>{{ formatDate(exp.start_date) }} - {{ exp.current ? 'Present' : formatDate(exp.end_date || '') }}</span>
                                        <span v-if="exp.location">• {{ exp.location }}</span>
                                    </div>
                                    <p v-if="exp.description" class="mt-2 text-muted-foreground whitespace-pre-wrap">{{ exp.description }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Education -->
                    <Card v-if="cv.education && cv.education.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <GraduationCap class="h-5 w-5" />
                                Education
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="(edu, index) in cv.education" :key="index" class="flex items-start gap-4">
                                <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <GraduationCap class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <h4 class="font-semibold">{{ edu.degree }} {{ edu.field ? `in ${edu.field}` : '' }}</h4>
                                    <p class="text-primary">{{ edu.institution }}</p>
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span v-if="edu.start_date || edu.end_date">
                                            {{ formatDate(edu.start_date || '') }} - {{ formatDate(edu.end_date || '') }}
                                        </span>
                                        <span v-if="edu.gpa">• GPA: {{ edu.gpa }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Skills -->
                    <Card v-if="cv.skills && cv.skills.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Code class="h-5 w-5" />
                                Skills
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="skill in cv.skills" :key="skill" variant="secondary">
                                    {{ skill }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Languages -->
                    <Card v-if="cv.languages && cv.languages.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Languages class="h-5 w-5" />
                                Languages
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-4">
                                <div v-for="lang in cv.languages" :key="lang.language" class="flex items-center gap-2">
                                    <span class="font-medium">{{ lang.language }}</span>
                                    <Badge variant="outline">{{ proficiencyLabels[lang.proficiency] }}</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- AI Suggestions -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Sparkles class="h-5 w-5 text-primary" />
                                    AI Suggestions
                                </CardTitle>
                                <CardDescription>Get personalized recommendations</CardDescription>
                            </div>
                            <Button
                                size="icon"
                                variant="ghost"
                                @click="getSuggestions"
                                :disabled="isGettingSuggestions"
                            >
                                <Loader2 v-if="isGettingSuggestions" class="h-4 w-4 animate-spin" />
                                <RefreshCcw v-else class="h-4 w-4" />
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="isGettingSuggestions" class="space-y-3">
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-4/5" />
                                <Skeleton class="h-4 w-3/5" />
                            </div>
                            <div v-else-if="suggestions" class="space-y-4">
                                <div v-if="suggestions.overall_score !== undefined || suggestions.match_score !== undefined" class="text-center py-4">
                                    <div
                                        class="inline-flex h-16 w-16 items-center justify-center rounded-full text-2xl font-bold"
                                        :class="{
                                            'bg-green-100 text-green-700': (suggestions.match_score || suggestions.overall_score) >= 70,
                                            'bg-yellow-100 text-yellow-700': (suggestions.match_score || suggestions.overall_score) >= 40 && (suggestions.match_score || suggestions.overall_score) < 70,
                                            'bg-red-100 text-red-700': (suggestions.match_score || suggestions.overall_score) < 40,
                                        }"
                                    >
                                        {{ suggestions.match_score || suggestions.overall_score }}
                                    </div>
                                    <p class="text-sm text-muted-foreground mt-2">CV Score</p>
                                </div>
                                <Separator />
                                <div v-if="suggestions.overall_recommendations?.length || suggestions.recommendations?.length" class="space-y-2">
                                    <h4 class="font-semibold text-sm">Key Recommendations</h4>
                                    <ul class="space-y-2 text-sm">
                                        <li v-for="(rec, index) in (suggestions.overall_recommendations || suggestions.recommendations)" :key="index" class="flex items-start gap-2">
                                            <Sparkles class="h-4 w-4 text-primary shrink-0 mt-0.5" />
                                            <span v-html="marked(typeof rec === 'string' ? rec : JSON.stringify(rec))" class="prose prose-sm dark:prose-invert max-w-none"></span>
                                        </li>
                                    </ul>
                                </div>
                                <div v-if="suggestions.skills_to_add?.length" class="space-y-2 pt-2 border-t">
                                    <h4 class="font-semibold text-sm text-green-600">Skills to Add</h4>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="skill in suggestions.skills_to_add" :key="skill" variant="outline" class="text-xs bg-green-50 border-green-200 text-green-700">
                                            + {{ skill }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-6">
                                <Sparkles class="h-10 w-10 mx-auto mb-3 text-muted-foreground/50" />
                                <p class="text-sm text-muted-foreground">
                                    Click the refresh button to get AI suggestions for improving your CV.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Feedback / Comments -->
                    <Card v-if="cv.comments && cv.comments.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <MessageSquare class="h-5 w-5 text-primary" />
                                External Feedback
                            </CardTitle>
                            <CardDescription>Comments from shared links</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="comment in cv.comments" :key="comment.id" class="space-y-2 p-3 rounded-lg border bg-card/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold">{{ comment.user?.name || comment.guest_name || 'Guest' }}</span>
                                            <Badge v-if="comment.share" variant="outline" class="text-[9px] h-4 uppercase">
                                                {{ comment.share.permission }}
                                            </Badge>
                                        </div>
                                        <span class="text-[10px] text-muted-foreground">{{ formatVersionDate(comment.created_at) }}</span>
                                    </div>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ comment.content }}</p>
                                    <Badge v-if="comment.section" variant="secondary" class="text-[9px] h-4"># {{ comment.section }}</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Version History -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <History class="h-5 w-5" />
                                Version History
                            </CardTitle>
                            <CardDescription>Previous versions of this CV</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="cv.versions.length > 0" class="space-y-3">
                                <div
                                    v-for="version in cv.versions"
                                    :key="version.id"
                                    class="flex items-center justify-between p-3 rounded-lg border"
                                >
                                    <div>
                                        <p class="font-medium">Version {{ version.version_number }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatVersionDate(version.created_at) }}</p>
                                        <p v-if="version.change_summary" class="text-xs text-muted-foreground mt-1">{{ version.change_summary }}</p>
                                    </div>
                                    <ConfirmDeleteModal
                                        title="Restore Version"
                                        description="Are you sure you want to restore this version? This will overwrite your current CV."
                                        trigger-text="Restore"
                                        variant="ghost"
                                        confirm-text="Restore"
                                        @confirm="restoreVersion(version.id)"
                                    />
                                </div>
                            </div>
                            <div v-else class="text-center py-6 text-muted-foreground">
                                <History class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">No versions yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
