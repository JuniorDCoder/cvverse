<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    User,
    Briefcase,
    Save,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface JobApplication {
    id: number;
    title: string;
    company?: {
        id: number;
        name: string;
    };
}

interface CoverLetter {
    id: number;
    name: string;
    content: string;
    tone: string;
    job_application_id: number | null;
}

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface Props {
    coverLetter: CoverLetter;
    user: UserType;
    tones: Record<string, string>;
    jobApplications: JobApplication[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const form = useForm({
    name: props.coverLetter.name,
    content: props.coverLetter.content,
    tone: props.coverLetter.tone,
    job_application_id: props.coverLetter.job_application_id?.toString() || '',
});

const submitForm = () => {
    form.put(`/admin/cover-letters/${props.coverLetter.id}`, {
        onSuccess: () => {
            toast({
                title: 'Cover Letter Updated',
                description: 'The cover letter has been updated successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to update the cover letter. Please check the form.',
                variant: 'destructive',
            });
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '/admin' },
        { title: 'Cover Letters', href: '/admin/cover-letters' },
        { title: coverLetter.name, href: `/admin/cover-letters/${coverLetter.id}` },
        { title: 'Edit' }
    ]">
        <Head :title="`Edit: ${coverLetter.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/admin/cover-letters/${coverLetter.id}`">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Edit Cover Letter</h1>
                    <p class="text-muted-foreground">Update the cover letter details</p>
                </div>
            </div>

            <!-- User Info Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Cover Letter Owner
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <Link 
                        :href="`/admin/users/${user.id}`"
                        class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted transition-colors w-fit"
                    >
                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <User class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <p class="font-medium">{{ user.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                        </div>
                    </Link>
                </CardContent>
            </Card>

            <!-- Edit Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Mail class="h-5 w-5" />
                        Cover Letter Details
                    </CardTitle>
                    <CardDescription>
                        Update the cover letter information
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Cover Letter Name *</Label>
                            <Input 
                                id="name"
                                v-model="form.name"
                                placeholder="e.g., Software Engineer at Google"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label for="tone">Tone</Label>
                            <Select v-model="form.tone">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select tone" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="(label, key) in tones" 
                                        :key="key" 
                                        :value="key"
                                    >
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.tone" class="text-sm text-destructive">
                                {{ form.errors.tone }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="job_application">
                            <Briefcase class="inline-block mr-1 h-4 w-4" />
                            Link to Job Application (Optional)
                        </Label>
                        <Select v-model="form.job_application_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a job application" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">No job application</SelectItem>
                                <SelectItem 
                                    v-for="job in jobApplications" 
                                    :key="job.id" 
                                    :value="job.id.toString()"
                                >
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
                            placeholder="Enter the cover letter content here..."
                            rows="20"
                            class="font-mono text-sm"
                        />
                        <p class="text-xs text-muted-foreground">
                            You can use HTML formatting for rich text content.
                        </p>
                        <p v-if="form.errors.content" class="text-sm text-destructive">
                            {{ form.errors.content }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <Link :href="`/admin/cover-letters/${coverLetter.id}`">Cancel</Link>
                        </Button>
                        <Button 
                            :disabled="form.processing"
                            @click="submitForm"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
