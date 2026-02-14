<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileText,
    User,
    Pencil,
    Trash2,
    Download,
    Copy,
    Star,
    StarOff,
    Sparkles,
    Clock,
    Briefcase,
    GraduationCap,
    Languages,
    MoreHorizontal,
    History,
    Loader2,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface PersonalInfo {
    full_name?: string;
    email?: string;
    phone?: string;
    location?: string;
    linkedin?: string;
    website?: string;
}

interface Experience {
    company: string;
    title: string;
    location?: string;
    start_date: string;
    end_date?: string;
    current?: boolean;
    description?: string;
}

interface Education {
    institution: string;
    degree: string;
    field?: string;
    start_date: string;
    end_date?: string;
    gpa?: string;
}

interface Language {
    language: string;
    proficiency: string;
}

interface Version {
    id: number;
    notes: string;
    created_at: string;
}

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    personal_info: PersonalInfo;
    experience: Experience[];
    education: Education[];
    skills: string[];
    projects?: unknown[];
    certifications?: unknown[];
    languages: Language[];
    summary?: string;
    created_at: string;
    updated_at: string;
}

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface Props {
    cv: Cv;
    user: UserType;
    versions: Version[];
    templates: string[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const showDeleteDialog = ref(false);
const showSuggestionsDialog = ref(false);
const suggestions = ref<Record<string, unknown> | null>(null);
const loadingSuggestions = ref(false);

const deleteForm = useForm({});

const deleteCv = () => {
    deleteForm.delete(`/admin/cvs/${props.cv.id}`, {
        onSuccess: () => {
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

const togglePrimary = () => {
    router.patch(`/admin/cvs/${props.cv.id}/toggle-primary`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: props.cv.is_primary ? 'Primary Removed' : 'Set as Primary',
                description: props.cv.is_primary 
                    ? 'CV is no longer the primary CV.'
                    : 'CV has been set as primary.',
            });
        },
    });
};

const downloadPdf = () => {
    window.open(`/admin/cvs/${props.cv.id}/download-pdf`, '_blank');
};

const duplicateCv = () => {
    router.post(`/admin/cvs/${props.cv.id}/duplicate`, {}, {
        onSuccess: () => {
            toast({
                title: 'CV Duplicated',
                description: 'The CV has been duplicated successfully.',
            });
        },
    });
};

const generateSuggestions = async () => {
    loadingSuggestions.value = true;
    showSuggestionsDialog.value = true;
    
    try {
        const response = await fetch(`/admin/cvs/${props.cv.id}/suggestions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        suggestions.value = data.suggestions;
    } catch {
        toast({
            title: 'Error',
            description: 'Failed to generate suggestions.',
            variant: 'destructive',
        });
        showSuggestionsDialog.value = false;
    } finally {
        loadingSuggestions.value = false;
    }
};

const formatDate = (date: string) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '/admin' },
        { title: 'CVs', href: '/admin/cvs' },
        { title: cv.name }
    ]">
        <Head :title="cv.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/admin/cvs">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold">{{ cv.name }}</h1>
                            <Badge v-if="cv.is_primary" variant="default">
                                <Star class="mr-1 h-3 w-3" />
                                Primary
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">
                            Owner: 
                            <Link :href="`/admin/users/${user.id}`" class="hover:underline">
                                {{ user.name }}
                            </Link>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="generateSuggestions">
                        <Sparkles class="mr-2 h-4 w-4" />
                        AI Suggestions
                    </Button>
                    <Button variant="outline" @click="downloadPdf">
                        <Download class="mr-2 h-4 w-4" />
                        Download PDF
                    </Button>
                    <Button as-child>
                        <Link :href="`/admin/cvs/${cv.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit CV
                        </Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="togglePrimary">
                                <template v-if="cv.is_primary">
                                    <StarOff class="mr-2 h-4 w-4" />
                                    Remove Primary
                                </template>
                                <template v-else>
                                    <Star class="mr-2 h-4 w-4" />
                                    Set as Primary
                                </template>
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="duplicateCv">
                                <Copy class="mr-2 h-4 w-4" />
                                Duplicate
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem 
                                class="text-destructive focus:text-destructive"
                                @click="showDeleteDialog = true"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete CV
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <Tabs default-value="content">
                        <TabsList>
                            <TabsTrigger value="content">
                                <FileText class="mr-2 h-4 w-4" />
                                Content
                            </TabsTrigger>
                            <TabsTrigger value="versions">
                                <History class="mr-2 h-4 w-4" />
                                Versions ({{ versions.length }})
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="content" class="space-y-6 mt-6">
                            <!-- Summary -->
                            <Card v-if="cv.summary">
                                <CardHeader>
                                    <CardTitle>Professional Summary</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p class="text-muted-foreground whitespace-pre-wrap">{{ cv.summary }}</p>
                                </CardContent>
                            </Card>

                            <!-- Personal Info -->
                            <Card>
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <User class="h-5 w-5" />
                                        Personal Information
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div v-if="cv.personal_info?.full_name">
                                            <p class="text-sm text-muted-foreground">Full Name</p>
                                            <p class="font-medium">{{ cv.personal_info.full_name }}</p>
                                        </div>
                                        <div v-if="cv.personal_info?.email">
                                            <p class="text-sm text-muted-foreground">Email</p>
                                            <p class="font-medium">{{ cv.personal_info.email }}</p>
                                        </div>
                                        <div v-if="cv.personal_info?.phone">
                                            <p class="text-sm text-muted-foreground">Phone</p>
                                            <p class="font-medium">{{ cv.personal_info.phone }}</p>
                                        </div>
                                        <div v-if="cv.personal_info?.location">
                                            <p class="text-sm text-muted-foreground">Location</p>
                                            <p class="font-medium">{{ cv.personal_info.location }}</p>
                                        </div>
                                        <div v-if="cv.personal_info?.linkedin">
                                            <p class="text-sm text-muted-foreground">LinkedIn</p>
                                            <a :href="cv.personal_info.linkedin" target="_blank" class="font-medium text-primary hover:underline">
                                                {{ cv.personal_info.linkedin }}
                                            </a>
                                        </div>
                                        <div v-if="cv.personal_info?.website">
                                            <p class="text-sm text-muted-foreground">Website</p>
                                            <a :href="cv.personal_info.website" target="_blank" class="font-medium text-primary hover:underline">
                                                {{ cv.personal_info.website }}
                                            </a>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Experience -->
                            <Card v-if="cv.experience?.length">
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <Briefcase class="h-5 w-5" />
                                        Work Experience
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-6">
                                    <div 
                                        v-for="(exp, index) in cv.experience" 
                                        :key="index"
                                        class="relative pl-6 border-l-2 border-muted pb-6 last:pb-0"
                                    >
                                        <div class="absolute -left-2 top-0 h-4 w-4 rounded-full bg-primary"></div>
                                        <div>
                                            <h4 class="font-semibold">{{ exp.title }}</h4>
                                            <p class="text-muted-foreground">{{ exp.company }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ formatDate(exp.start_date) }} - 
                                                {{ exp.current ? 'Present' : formatDate(exp.end_date || '') }}
                                                <span v-if="exp.location"> · {{ exp.location }}</span>
                                            </p>
                                            <p v-if="exp.description" class="mt-2 text-sm whitespace-pre-wrap">{{ exp.description }}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Education -->
                            <Card v-if="cv.education?.length">
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <GraduationCap class="h-5 w-5" />
                                        Education
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div 
                                        v-for="(edu, index) in cv.education" 
                                        :key="index"
                                        class="flex items-start gap-4"
                                    >
                                        <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <GraduationCap class="h-5 w-5 text-primary" />
                                        </div>
                                        <div>
                                            <h4 class="font-semibold">{{ edu.degree }}</h4>
                                            <p class="text-muted-foreground">{{ edu.institution }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ edu.field }}
                                                <span v-if="edu.gpa"> · GPA: {{ edu.gpa }}</span>
                                            </p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ formatDate(edu.start_date) }} - {{ formatDate(edu.end_date || '') }}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Skills -->
                            <Card v-if="cv.skills?.length">
                                <CardHeader>
                                    <CardTitle>Skills</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge 
                                            v-for="(skill, index) in cv.skills" 
                                            :key="index"
                                            variant="secondary"
                                        >
                                            {{ skill }}
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Languages -->
                            <Card v-if="cv.languages?.length">
                                <CardHeader>
                                    <CardTitle class="flex items-center gap-2">
                                        <Languages class="h-5 w-5" />
                                        Languages
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div 
                                            v-for="(lang, index) in cv.languages" 
                                            :key="index"
                                            class="flex items-center justify-between p-3 bg-muted/50 rounded-lg"
                                        >
                                            <span class="font-medium">{{ lang.language }}</span>
                                            <Badge variant="outline">{{ lang.proficiency }}</Badge>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="versions" class="mt-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Version History</CardTitle>
                                    <CardDescription>Track changes made to this CV</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div v-if="versions.length === 0" class="text-center py-8 text-muted-foreground">
                                        <History class="mx-auto h-12 w-12 mb-4" />
                                        <p>No version history available</p>
                                    </div>
                                    <div v-else class="space-y-4">
                                        <div 
                                            v-for="version in versions" 
                                            :key="version.id"
                                            class="flex items-start gap-4 p-4 border rounded-lg"
                                        >
                                            <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center shrink-0">
                                                <Clock class="h-5 w-5 text-muted-foreground" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">{{ version.notes || 'Version saved' }}</p>
                                                <p class="text-sm text-muted-foreground">{{ version.created_at }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- CV Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>CV Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">Template</p>
                                <Badge variant="outline" class="capitalize mt-1">{{ cv.template }}</Badge>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Created</p>
                                <p class="font-medium">{{ cv.created_at }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Last Updated</p>
                                <p class="font-medium">{{ cv.updated_at }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Versions</p>
                                <p class="font-medium">{{ versions.length }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Owner -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Owner</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                                    <User class="h-6 w-6 text-primary" />
                                </div>
                                <div>
                                    <Link 
                                        :href="`/admin/users/${user.id}`"
                                        class="font-medium hover:underline"
                                    >
                                        {{ user.name }}
                                    </Link>
                                    <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </div>
                            <Button variant="outline" class="w-full mt-4" as-child>
                                <Link :href="`/admin/users/${user.id}`">
                                    View User Profile
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button variant="outline" class="w-full justify-start" @click="downloadPdf">
                                <Download class="mr-2 h-4 w-4" />
                                Download PDF
                            </Button>
                            <Button variant="outline" class="w-full justify-start" @click="duplicateCv">
                                <Copy class="mr-2 h-4 w-4" />
                                Duplicate CV
                            </Button>
                            <Button variant="outline" class="w-full justify-start" @click="generateSuggestions">
                                <Sparkles class="mr-2 h-4 w-4" />
                                AI Suggestions
                            </Button>
                        </CardContent>
                    </Card>
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
                <div class="py-4">
                    <Card class="bg-destructive/5 border-destructive/20">
                        <CardContent class="p-4">
                            <div class="flex items-center gap-3">
                                <FileText class="h-8 w-8 text-destructive" />
                                <div>
                                    <p class="font-semibold">{{ cv.name }}</p>
                                    <p class="text-sm text-muted-foreground">Owner: {{ user.name }}</p>
                                </div>
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

        <!-- AI Suggestions Dialog -->
        <Dialog v-model:open="showSuggestionsDialog">
            <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Sparkles class="h-5 w-5 text-amber-500" />
                        AI Suggestions
                    </DialogTitle>
                    <DialogDescription>
                        AI-powered recommendations to improve this CV
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <div v-if="loadingSuggestions" class="flex flex-col items-center py-12">
                        <Loader2 class="h-8 w-8 animate-spin text-primary mb-4" />
                        <p class="text-muted-foreground">Analyzing CV and generating suggestions...</p>
                    </div>
                    <div v-else-if="suggestions" class="space-y-6">
                        <div v-if="suggestions.match_score" class="text-center p-4 bg-primary/10 rounded-lg">
                            <p class="text-sm text-muted-foreground">Match Score</p>
                            <p class="text-4xl font-bold text-primary">{{ suggestions.match_score }}%</p>
                        </div>
                        
                        <div v-if="suggestions.summary_suggestions">
                            <h4 class="font-medium mb-2">Summary Improvements</h4>
                            <p class="text-sm text-muted-foreground">{{ suggestions.summary_suggestions }}</p>
                        </div>

                        <div v-if="suggestions.skills_to_add?.length">
                            <h4 class="font-medium mb-2">Skills to Add</h4>
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="skill in suggestions.skills_to_add as string[]" :key="skill" variant="secondary">
                                    + {{ skill }}
                                </Badge>
                            </div>
                        </div>

                        <div v-if="suggestions.overall_recommendations?.length">
                            <h4 class="font-medium mb-2">Overall Recommendations</h4>
                            <ul class="space-y-2">
                                <li 
                                    v-for="(rec, index) in suggestions.overall_recommendations as string[]" 
                                    :key="index"
                                    class="flex items-start gap-2 text-sm"
                                >
                                    <span class="text-primary">•</span>
                                    {{ rec }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <p>No suggestions available</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="showSuggestionsDialog = false">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
