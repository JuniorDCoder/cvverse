<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    Save,
    Loader2,
    Sparkles,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    job_application_id?: number | null;
}

interface Props {
    coverLetter: CoverLetter;
    jobApplications: JobApplication[];
    tones: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Cover Letters', href: '/cover-letters' },
    { title: props.coverLetter.name, href: `/cover-letters/${props.coverLetter.id}` },
    { title: 'Edit', href: '#' },
];

const form = useForm({
    name: props.coverLetter.name,
    tone: props.coverLetter.tone,
    content: props.coverLetter.content,
    job_application_id: props.coverLetter.job_application_id?.toString() ?? '',
});

const toneLabels: Record<string, string> = {
    professional: 'Professional',
    enthusiastic: 'Enthusiastic',
    confident: 'Confident',
    conversational: 'Conversational',
    formal: 'Formal',
};

const isImproving = ref(false);

const improveWithAI = async () => {
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
        if (data.success && data.result?.improved_content) {
            form.content = data.result.improved_content;
        }
    } finally {
        isImproving.value = false;
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        job_application_id: data.job_application_id && data.job_application_id !== 'none' ? parseInt(data.job_application_id) : null,
    })).put(`/cover-letters/${props.coverLetter.id}`);
};
</script>

<template>
    <Head :title="`Edit - ${coverLetter.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/cover-letters/${coverLetter.id}`">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Cover Letter</h1>
                    <p class="text-muted-foreground">Update your cover letter content</p>
                </div>
            </div>

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
                            <div class="flex items-center justify-between">
                                <Label for="content">Content *</Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="improveWithAI"
                                    :disabled="isImproving || !form.content"
                                >
                                    <Loader2 v-if="isImproving" class="h-4 w-4 mr-1 animate-spin" />
                                    <Sparkles v-else class="h-4 w-4 mr-1" />
                                    Improve with AI
                                </Button>
                            </div>
                            <Textarea
                                id="content"
                                v-model="form.content"
                                placeholder="Dear Hiring Manager,

I am writing to express my interest in..."
                                rows="20"
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
                        <Link :href="`/cover-letters/${coverLetter.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                        <Save v-else class="h-4 w-4 mr-2" />
                        Save Changes
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
