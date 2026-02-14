<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    Sparkles,
    User,
    Briefcase,
    FileText,
    Loader2,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface JobApplication {
    id: number;
    title: string;
    company_name?: string;
    description?: string;
}

interface Cv {
    id: number;
    name: string;
}

interface Props {
    users: UserType[];
    tones: Record<string, string>;
    selectedUser?: UserType | null;
    jobApplications?: JobApplication[];
    cvs?: Cv[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const activeTab = ref('manual');
const selectedUserId = ref(props.selectedUser?.id.toString() || '');
const userJobApplications = ref<JobApplication[]>(props.jobApplications || []);
const userCvs = ref<Cv[]>(props.cvs || []);
const loadingUserResources = ref(false);

// Manual Cover Letter Form
const manualForm = useForm({
    user_id: props.selectedUser?.id.toString() || '',
    name: '',
    content: '',
    tone: 'professional',
    job_application_id: '',
});

// AI Generation Form
const aiForm = useForm({
    user_id: props.selectedUser?.id.toString() || '',
    name: '',
    job_application_id: '',
    cv_id: '',
    tone: 'professional',
});

const isGenerating = ref(false);

const selectedUser = computed(() => {
    return props.users.find(u => u.id.toString() === selectedUserId.value);
});

const selectedJobApplication = computed(() => {
    return userJobApplications.value.find(j => j.id.toString() === aiForm.job_application_id);
});

// Watch for user selection changes
watch(selectedUserId, async (newUserId) => {
    if (newUserId) {
        manualForm.user_id = newUserId;
        aiForm.user_id = newUserId;
        
        // Load job applications and CVs for selected user
        loadingUserResources.value = true;
        try {
            const response = await fetch(`/admin/users/${newUserId}/resources`);
            const data = await response.json();
            userJobApplications.value = data.applications || [];
            userCvs.value = data.cvs || [];
        } catch {
            userJobApplications.value = [];
            userCvs.value = [];
        } finally {
            loadingUserResources.value = false;
        }
    }
});

const submitManualForm = () => {
    manualForm.post('/admin/cover-letters', {
        onSuccess: () => {
            toast({
                title: 'Cover Letter Created',
                description: 'The cover letter has been created successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to create the cover letter. Please check the form.',
                variant: 'destructive',
            });
        },
    });
};

const generateWithAi = () => {
    if (!aiForm.user_id || !aiForm.name || !aiForm.job_application_id || !aiForm.cv_id) {
        toast({
            title: 'Missing Information',
            description: 'Please fill in all required fields.',
            variant: 'destructive',
        });
        return;
    }

    isGenerating.value = true;
    aiForm.post('/admin/cover-letters/generate', {
        onSuccess: () => {
            isGenerating.value = false;
            toast({
                title: 'Cover Letter Generated',
                description: 'The cover letter has been generated successfully using AI!',
            });
        },
        onError: () => {
            isGenerating.value = false;
            toast({
                title: 'Generation Failed',
                description: 'Failed to generate cover letter. Please try again.',
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
        { title: 'Create' }
    ]">
        <Head title="Create Cover Letter" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/admin/cover-letters">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Create Cover Letter</h1>
                    <p class="text-muted-foreground">Create a new cover letter for a user manually or using AI</p>
                </div>
            </div>

            <!-- User Selection (Common for both tabs) -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <User class="h-5 w-5" />
                        Select User
                    </CardTitle>
                    <CardDescription>
                        Choose the user for whom you want to create this cover letter
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label>User *</Label>
                            <Select v-model="selectedUserId">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a user" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="user in users" 
                                        :key="user.id" 
                                        :value="user.id.toString()"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div v-if="selectedUser" class="flex items-center gap-3 p-3 rounded-lg bg-muted/50">
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                <User class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium">{{ selectedUser.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ selectedUser.email }}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Creation Method Tabs -->
            <Tabs v-model="activeTab" class="space-y-6">
                <TabsList class="grid w-full max-w-md grid-cols-2">
                    <TabsTrigger value="manual" class="flex items-center gap-2">
                        <Mail class="h-4 w-4" />
                        Manual Entry
                    </TabsTrigger>
                    <TabsTrigger value="ai" class="flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        AI Generation
                    </TabsTrigger>
                </TabsList>

                <!-- Manual Entry Tab -->
                <TabsContent value="manual" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Cover Letter Details</CardTitle>
                            <CardDescription>
                                Enter the cover letter content manually
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="manual-name">Cover Letter Name *</Label>
                                    <Input 
                                        id="manual-name"
                                        v-model="manualForm.name"
                                        placeholder="e.g., Software Engineer at Google"
                                    />
                                    <p v-if="manualForm.errors.name" class="text-sm text-destructive">
                                        {{ manualForm.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="manual-tone">Tone</Label>
                                    <Select v-model="manualForm.tone">
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
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="manual-job">Link to Job Application (Optional)</Label>
                                <Select v-model="manualForm.job_application_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a job application" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">No job application</SelectItem>
                                        <SelectItem 
                                            v-for="job in userJobApplications" 
                                            :key="job.id" 
                                            :value="job.id.toString()"
                                        >
                                            {{ job.title }} {{ job.company_name ? `at ${job.company_name}` : '' }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="loadingUserResources" class="text-sm text-muted-foreground">
                                    Loading job applications...
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="manual-content">Content *</Label>
                                <Textarea 
                                    id="manual-content"
                                    v-model="manualForm.content"
                                    placeholder="Enter the cover letter content here..."
                                    rows="15"
                                    class="font-mono text-sm"
                                />
                                <p class="text-xs text-muted-foreground">
                                    You can use HTML formatting for rich text content.
                                </p>
                                <p v-if="manualForm.errors.content" class="text-sm text-destructive">
                                    {{ manualForm.errors.content }}
                                </p>
                            </div>

                            <div class="flex justify-end gap-4">
                                <Button variant="outline" as-child>
                                    <Link href="/admin/cover-letters">Cancel</Link>
                                </Button>
                                <Button 
                                    :disabled="manualForm.processing || !selectedUserId"
                                    @click="submitManualForm"
                                >
                                    <Mail class="mr-2 h-4 w-4" />
                                    {{ manualForm.processing ? 'Creating...' : 'Create Cover Letter' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- AI Generation Tab -->
                <TabsContent value="ai" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-primary" />
                                AI-Powered Generation
                            </CardTitle>
                            <CardDescription>
                                Generate a personalized cover letter based on a CV and job application
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="rounded-lg border border-primary/20 bg-primary/5 p-4">
                                <h4 class="font-medium text-primary mb-2">How it works</h4>
                                <ul class="text-sm text-muted-foreground space-y-1">
                                    <li>• Select a CV and job application for the user</li>
                                    <li>• Choose the desired tone for the cover letter</li>
                                    <li>• AI will analyze both and generate a tailored cover letter</li>
                                    <li>• You can edit the result after generation</li>
                                </ul>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="ai-name">Cover Letter Name *</Label>
                                    <Input 
                                        id="ai-name"
                                        v-model="aiForm.name"
                                        placeholder="e.g., Application for Software Engineer"
                                    />
                                    <p v-if="aiForm.errors.name" class="text-sm text-destructive">
                                        {{ aiForm.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="ai-tone">Tone *</Label>
                                    <Select v-model="aiForm.tone">
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
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="ai-cv">
                                        <FileText class="inline-block mr-1 h-4 w-4" />
                                        Select CV *
                                    </Label>
                                    <Select v-model="aiForm.cv_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a CV" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="cv in userCvs" 
                                                :key="cv.id" 
                                                :value="cv.id.toString()"
                                            >
                                                {{ cv.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="userCvs.length === 0 && !loadingUserResources && selectedUserId" class="text-sm text-muted-foreground">
                                        No CVs found for this user. Create a CV first.
                                    </p>
                                    <p v-if="aiForm.errors.cv_id" class="text-sm text-destructive">
                                        {{ aiForm.errors.cv_id }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="ai-job">
                                        <Briefcase class="inline-block mr-1 h-4 w-4" />
                                        Job Application *
                                    </Label>
                                    <Select v-model="aiForm.job_application_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a job application" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="job in userJobApplications" 
                                                :key="job.id" 
                                                :value="job.id.toString()"
                                            >
                                                {{ job.title }} {{ job.company_name ? `at ${job.company_name}` : '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="userJobApplications.length === 0 && !loadingUserResources && selectedUserId" class="text-sm text-muted-foreground">
                                        No job applications found. Create one first.
                                    </p>
                                    <p v-if="aiForm.errors.job_application_id" class="text-sm text-destructive">
                                        {{ aiForm.errors.job_application_id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Selected Job Preview -->
                            <div v-if="selectedJobApplication" class="rounded-lg border p-4 bg-muted/50">
                                <h4 class="font-medium flex items-center gap-2 mb-2">
                                    <Briefcase class="h-4 w-4" />
                                    {{ selectedJobApplication.title }}
                                </h4>
                                <p v-if="selectedJobApplication.company_name" class="text-sm text-muted-foreground mb-2">
                                    at {{ selectedJobApplication.company_name }}
                                </p>
                                <p v-if="selectedJobApplication.description" class="text-sm line-clamp-3">
                                    {{ selectedJobApplication.description }}
                                </p>
                            </div>

                            <div class="flex justify-end gap-4">
                                <Button variant="outline" as-child>
                                    <Link href="/admin/cover-letters">Cancel</Link>
                                </Button>
                                <Button 
                                    :disabled="isGenerating || !selectedUserId || !aiForm.cv_id || !aiForm.job_application_id"
                                    @click="generateWithAi"
                                >
                                    <template v-if="isGenerating">
                                        <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                                        Generating...
                                    </template>
                                    <template v-else>
                                        <Sparkles class="mr-2 h-4 w-4" />
                                        Generate with AI
                                    </template>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
