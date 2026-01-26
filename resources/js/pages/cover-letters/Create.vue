<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    Sparkles,
    Save,
    Loader2,
    Briefcase,
    Wand2,
    CheckCircle,
    AlertCircle,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
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

interface Cv {
    id: number;
    name: string;
}

interface Props {
    jobApplications: JobApplication[];
    cvs: Cv[];
    tones: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cover Letters', href: '/cover-letters' },
    { title: 'Create', href: '#' },
];

const activeTab = ref('generate');
const isGenerating = ref(false);
const generatedContent = ref('');
const generateError = ref('');
const generateSuccess = ref(false);

const form = useForm({
    name: '',
    tone: 'professional',
    content: '',
    job_application_id: '',
});

const generateForm = ref({
    job_application_id: '',
    cv_id: '',
    tone: 'professional',
    additional_context: '',
});

const toneLabels: Record<string, string> = {
    professional: 'Professional',
    enthusiastic: 'Enthusiastic',
    confident: 'Confident',
    conversational: 'Conversational',
    formal: 'Formal',
};

const toneDescriptions: Record<string, string> = {
    professional: 'Balanced and business-appropriate',
    enthusiastic: 'Energetic and passionate',
    confident: 'Bold and self-assured',
    conversational: 'Friendly and approachable',
    formal: 'Traditional and respectful',
};

const generateCoverLetter = async () => {
    if (!generateForm.value.job_application_id) {
        generateError.value = 'Please select a job application';
        return;
    }

    isGenerating.value = true;
    generateError.value = '';
    generateSuccess.value = false;

    try {
        const response = await fetch('/cover-letters/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                job_application_id: parseInt(generateForm.value.job_application_id),
                cv_id: generateForm.value.cv_id && generateForm.value.cv_id !== 'none' ? parseInt(generateForm.value.cv_id) : null,
                tone: generateForm.value.tone,
                additional_context: generateForm.value.additional_context,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            generatedContent.value = data.content;
            generateSuccess.value = true;

            // Auto-fill the manual form
            const selectedJob = props.jobApplications.find(j => j.id.toString() === generateForm.value.job_application_id);
            form.name = `Cover Letter - ${selectedJob?.title || 'Job Application'}`;
            form.tone = generateForm.value.tone;
            form.content = data.content;
            form.job_application_id = generateForm.value.job_application_id;
        } else {
            generateError.value = data.message || 'Failed to generate cover letter';
        }
    } catch (error) {
        generateError.value = 'An error occurred while generating the cover letter';
    } finally {
        isGenerating.value = false;
    }
};

const useGeneratedContent = () => {
    activeTab.value = 'manual';
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        job_application_id: data.job_application_id && data.job_application_id !== 'none' ? parseInt(data.job_application_id) : null,
    })).post('/cover-letters');
};
</script>

<template>
    <Head title="Create Cover Letter" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/cover-letters">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create Cover Letter</h1>
                    <p class="text-muted-foreground">Generate with AI or write your own</p>
                </div>
            </div>

            <!-- Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-2">
                    <TabsTrigger value="generate">
                        <Sparkles class="h-4 w-4 mr-2" />
                        AI Generate
                    </TabsTrigger>
                    <TabsTrigger value="manual">
                        <Mail class="h-4 w-4 mr-2" />
                        Write Manually
                    </TabsTrigger>
                </TabsList>

                <!-- AI Generate Tab -->
                <TabsContent value="generate">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Wand2 class="h-5 w-5 text-primary" />
                                Generate with AI
                            </CardTitle>
                            <CardDescription>
                                Select a job application and let AI create a personalized cover letter for you
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label>Job Application *</Label>
                                    <Select v-model="generateForm.job_application_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a job" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="job in jobApplications" :key="job.id" :value="job.id.toString()">
                                                {{ job.title }} {{ job.company ? `at ${job.company.name}` : '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>CV / Resume (Optional)</Label>
                                    <Select v-model="generateForm.cv_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a CV" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">None</SelectItem>
                                            <SelectItem v-for="cv in cvs" :key="cv.id" :value="cv.id.toString()">
                                                {{ cv.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Tone</Label>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                                    <Button
                                        v-for="(label, tone) in toneLabels"
                                        :key="tone"
                                        type="button"
                                        :variant="generateForm.tone === tone ? 'default' : 'outline'"
                                        size="sm"
                                        class="flex flex-col h-auto py-3 px-2 text-center"
                                        @click="generateForm.tone = tone"
                                    >
                                        <span class="text-sm font-semibold truncate w-full">{{ label }}</span>
                                        <span class="text-xs font-normal opacity-70 line-clamp-2 mt-1">{{ toneDescriptions[tone] }}</span>
                                    </Button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label>Additional Context (Optional)</Label>
                                <Textarea
                                    v-model="generateForm.additional_context"
                                    placeholder="Any specific points you want to highlight, achievements to mention, or style preferences..."
                                    rows="3"
                                />
                            </div>

                            <!-- Error Message -->
                            <div v-if="generateError" class="flex items-center gap-2 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300">
                                <AlertCircle class="h-5 w-5" />
                                <p>{{ generateError }}</p>
                            </div>

                            <Button
                                @click="generateCoverLetter"
                                :disabled="isGenerating || !generateForm.job_application_id"
                                class="w-full"
                            >
                                <Loader2 v-if="isGenerating" class="h-4 w-4 mr-2 animate-spin" />
                                <Sparkles v-else class="h-4 w-4 mr-2" />
                                {{ isGenerating ? 'Generating...' : 'Generate Cover Letter' }}
                            </Button>

                            <!-- Generated Content -->
                            <div v-if="isGenerating" class="space-y-4 pt-4 border-t">
                                <div class="flex items-center gap-3">
                                    <div class="animate-pulse">
                                        <Sparkles class="h-5 w-5 text-primary" />
                                    </div>
                                    <span class="font-medium">Crafting your cover letter...</span>
                                </div>
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-4/5" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-3/4" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-2/3" />
                            </div>

                            <div v-else-if="generateSuccess && generatedContent" class="space-y-4 pt-4 border-t">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                        <CheckCircle class="h-5 w-5" />
                                        <span class="font-medium">Cover letter generated!</span>
                                    </div>
                                    <Button size="sm" @click="useGeneratedContent">
                                        Use & Edit
                                    </Button>
                                </div>
                                <div class="p-4 rounded-lg bg-muted/50 max-h-96 overflow-y-auto">
                                    <p class="whitespace-pre-wrap text-sm">{{ generatedContent }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Manual Tab -->
                <TabsContent value="manual">
                    <form @submit.prevent="submit" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Mail class="h-5 w-5" />
                                    Cover Letter Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="name">Name *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            placeholder="e.g. Cover Letter - Software Engineer at Google"
                                            :class="{ 'border-red-500': form.errors.name }"
                                        />
                                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="tone">Tone</Label>
                                        <Select v-model="form.tone">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="(label, tone) in toneLabels" :key="tone" :value="tone">
                                                    {{ label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Link to Job Application (Optional)</Label>
                                    <Select v-model="form.job_application_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a job" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">None</SelectItem>
                                            <SelectItem v-for="job in jobApplications" :key="job.id" :value="job.id.toString()">
                                                {{ job.title }} {{ job.company ? `at ${job.company.name}` : '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="content">Content *</Label>
                                    <Textarea
                                        id="content"
                                        v-model="form.content"
                                        placeholder="Dear Hiring Manager,

I am writing to express my interest in..."
                                        rows="15"
                                        class="font-mono text-sm"
                                        :class="{ 'border-red-500': form.errors.content }"
                                    />
                                    <p v-if="form.errors.content" class="text-sm text-red-500">{{ form.errors.content }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4">
                            <Button variant="outline" type="button" as-child>
                                <Link href="/cover-letters">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                                <Save v-else class="h-4 w-4 mr-2" />
                                Save Cover Letter
                            </Button>
                        </div>
                    </form>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
