<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileText,
    Sparkles,
    Plus,
    Minus,
    Briefcase,
    User,
    GraduationCap,
    Languages,
    Loader2,
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
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

interface Template {
    id: number;
    name: string;
    slug: string;
    description: string;
}

interface JobApplication {
    id: number;
    title: string;
    company_name: string;
    description?: string;
    requirements?: string;
    skills?: string[];
}

interface Props {
    users: UserType[];
    templates: Template[];
    selectedUser?: UserType | null;
    jobApplications?: JobApplication[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const activeTab = ref('manual');
const selectedUserId = ref(props.selectedUser?.id.toString() || '');
const userJobApplications = ref<JobApplication[]>(props.jobApplications || []);
const loadingJobApplications = ref(false);

// Manual CV Form
const manualForm = useForm({
    user_id: props.selectedUser?.id.toString() || '',
    name: '',
    template: '',
    is_primary: false,
    personal_info: {
        full_name: '',
        email: '',
        phone: '',
        location: '',
        linkedin: '',
        website: '',
    },
    summary: '',
    experience: [] as Array<{
        company: string;
        title: string;
        location: string;
        start_date: string;
        end_date: string;
        current: boolean;
        description: string;
    }>,
    education: [] as Array<{
        institution: string;
        degree: string;
        field: string;
        start_date: string;
        end_date: string;
        gpa: string;
    }>,
    skills: [] as string[],
    languages: [] as Array<{
        language: string;
        proficiency: string;
    }>,
});

// AI Generation Form
const aiForm = useForm({
    user_id: props.selectedUser?.id.toString() || '',
    name: '',
    template: '',
    job_application_id: '',
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
        
        // Load job applications for selected user
        loadingJobApplications.value = true;
        try {
            const response = await fetch(`/admin/users/${newUserId}/job-applications`);
            const data = await response.json();
            userJobApplications.value = data.applications || [];
        } catch {
            userJobApplications.value = [];
        } finally {
            loadingJobApplications.value = false;
        }
        
        // Pre-fill personal info if available
        const user = props.users.find(u => u.id.toString() === newUserId);
        if (user) {
            manualForm.personal_info.full_name = user.name;
            manualForm.personal_info.email = user.email;
        }
    }
});

// Experience helpers
const addExperience = () => {
    manualForm.experience.push({
        company: '',
        title: '',
        location: '',
        start_date: '',
        end_date: '',
        current: false,
        description: '',
    });
};

const removeExperience = (index: number) => {
    manualForm.experience.splice(index, 1);
};

// Education helpers
const addEducation = () => {
    manualForm.education.push({
        institution: '',
        degree: '',
        field: '',
        start_date: '',
        end_date: '',
        gpa: '',
    });
};

const removeEducation = (index: number) => {
    manualForm.education.splice(index, 1);
};

// Skills helpers
const newSkill = ref('');
const addSkill = () => {
    if (newSkill.value.trim() && !manualForm.skills.includes(newSkill.value.trim())) {
        manualForm.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index: number) => {
    manualForm.skills.splice(index, 1);
};

// Language helpers
const addLanguage = () => {
    manualForm.languages.push({
        language: '',
        proficiency: 'Intermediate',
    });
};

const removeLanguage = (index: number) => {
    manualForm.languages.splice(index, 1);
};

const submitManualForm = () => {
    manualForm.post('/admin/cvs', {
        onSuccess: () => {
            toast({
                title: 'CV Created',
                description: 'The CV has been created successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to create the CV. Please check the form.',
                variant: 'destructive',
            });
        },
    });
};

const generateWithAi = () => {
    if (!aiForm.user_id || !aiForm.name || !aiForm.template || !aiForm.job_application_id) {
        toast({
            title: 'Missing Information',
            description: 'Please fill in all required fields.',
            variant: 'destructive',
        });
        return;
    }

    isGenerating.value = true;
    aiForm.post('/admin/cvs/generate', {
        onSuccess: () => {
            isGenerating.value = false;
            toast({
                title: 'CV Generated',
                description: 'The CV has been generated successfully using AI!',
            });
        },
        onError: () => {
            isGenerating.value = false;
            toast({
                title: 'Generation Failed',
                description: 'Failed to generate CV. Please try again.',
                variant: 'destructive',
            });
        },
    });
};

const proficiencyLevels = ['Native', 'Fluent', 'Advanced', 'Intermediate', 'Beginner'];
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '/admin' },
        { title: 'CVs', href: '/admin/cvs' },
        { title: 'Create' }
    ]">
        <Head title="Create CV" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/admin/cvs">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Create CV</h1>
                    <p class="text-muted-foreground">Create a new CV for a user manually or using AI</p>
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
                        Choose the user for whom you want to create this CV
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
                        <FileText class="h-4 w-4" />
                        Manual Entry
                    </TabsTrigger>
                    <TabsTrigger value="ai" class="flex items-center gap-2">
                        <Sparkles class="h-4 w-4" />
                        AI Generation
                    </TabsTrigger>
                </TabsList>

                <!-- Manual Entry Tab -->
                <TabsContent value="manual" class="space-y-6">
                    <!-- Basic Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>CV Name *</Label>
                                    <Input 
                                        v-model="manualForm.name" 
                                        placeholder="e.g., Software Engineer Resume"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label>Template</Label>
                                    <Select v-model="manualForm.template">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select template" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem 
                                                v-for="template in templates" 
                                                :key="template.id" 
                                                :value="template.slug"
                                            >
                                                {{ template.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch 
                                    :checked="manualForm.is_primary"
                                    @update:checked="manualForm.is_primary = $event"
                                />
                                <Label>Set as primary CV</Label>
                            </div>
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
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Full Name</Label>
                                    <Input v-model="manualForm.personal_info.full_name" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Email</Label>
                                    <Input v-model="manualForm.personal_info.email" type="email" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Phone</Label>
                                    <Input v-model="manualForm.personal_info.phone" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Location</Label>
                                    <Input v-model="manualForm.personal_info.location" placeholder="City, Country" />
                                </div>
                                <div class="space-y-2">
                                    <Label>LinkedIn</Label>
                                    <Input v-model="manualForm.personal_info.linkedin" placeholder="https://linkedin.com/in/..." />
                                </div>
                                <div class="space-y-2">
                                    <Label>Website</Label>
                                    <Input v-model="manualForm.personal_info.website" placeholder="https://..." />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <Label>Professional Summary</Label>
                                <Textarea 
                                    v-model="manualForm.summary" 
                                    placeholder="Write a brief professional summary..."
                                    rows="4"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Experience -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <Briefcase class="h-5 w-5" />
                                    Work Experience
                                </CardTitle>
                                <Button type="button" variant="outline" size="sm" @click="addExperience">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Experience
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div 
                                v-for="(exp, index) in manualForm.experience" 
                                :key="index"
                                class="p-4 border rounded-lg space-y-4"
                            >
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium">Experience {{ index + 1 }}</h4>
                                    <Button 
                                        type="button" 
                                        variant="ghost" 
                                        size="icon"
                                        @click="removeExperience(index)"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Job Title</Label>
                                        <Input v-model="exp.title" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Company</Label>
                                        <Input v-model="exp.company" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Location</Label>
                                        <Input v-model="exp.location" />
                                    </div>
                                    <div class="flex items-center gap-2 pt-6">
                                        <Switch 
                                            :checked="exp.current"
                                            @update:checked="exp.current = $event"
                                        />
                                        <Label>Currently working here</Label>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Start Date</Label>
                                        <Input v-model="exp.start_date" type="month" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>End Date</Label>
                                        <Input 
                                            v-model="exp.end_date" 
                                            type="month"
                                            :disabled="exp.current"
                                        />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Description</Label>
                                    <Textarea v-model="exp.description" rows="3" />
                                </div>
                            </div>
                            <div v-if="manualForm.experience.length === 0" class="text-center py-8 text-muted-foreground">
                                <Briefcase class="mx-auto h-8 w-8 mb-2" />
                                <p>No experience added yet</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Education -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <GraduationCap class="h-5 w-5" />
                                    Education
                                </CardTitle>
                                <Button type="button" variant="outline" size="sm" @click="addEducation">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Education
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div 
                                v-for="(edu, index) in manualForm.education" 
                                :key="index"
                                class="p-4 border rounded-lg space-y-4"
                            >
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium">Education {{ index + 1 }}</h4>
                                    <Button 
                                        type="button" 
                                        variant="ghost" 
                                        size="icon"
                                        @click="removeEducation(index)"
                                    >
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>Institution</Label>
                                        <Input v-model="edu.institution" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Degree</Label>
                                        <Input v-model="edu.degree" placeholder="e.g., Bachelor's, Master's" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Field of Study</Label>
                                        <Input v-model="edu.field" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>GPA (optional)</Label>
                                        <Input v-model="edu.gpa" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Start Date</Label>
                                        <Input v-model="edu.start_date" type="month" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>End Date</Label>
                                        <Input v-model="edu.end_date" type="month" />
                                    </div>
                                </div>
                            </div>
                            <div v-if="manualForm.education.length === 0" class="text-center py-8 text-muted-foreground">
                                <GraduationCap class="mx-auto h-8 w-8 mb-2" />
                                <p>No education added yet</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Skills -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Skills</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex gap-2">
                                <Input 
                                    v-model="newSkill" 
                                    placeholder="Type a skill and press Enter"
                                    @keyup.enter="addSkill"
                                />
                                <Button type="button" @click="addSkill">Add</Button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Badge 
                                    v-for="(skill, index) in manualForm.skills" 
                                    :key="index"
                                    variant="secondary"
                                    class="cursor-pointer"
                                    @click="removeSkill(index)"
                                >
                                    {{ skill }}
                                    <span class="ml-1">×</span>
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Languages -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle class="flex items-center gap-2">
                                    <Languages class="h-5 w-5" />
                                    Languages
                                </CardTitle>
                                <Button type="button" variant="outline" size="sm" @click="addLanguage">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Language
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div 
                                v-for="(lang, index) in manualForm.languages" 
                                :key="index"
                                class="flex items-center gap-4"
                            >
                                <Input 
                                    v-model="lang.language" 
                                    placeholder="Language"
                                    class="flex-1"
                                />
                                <Select v-model="lang.proficiency">
                                    <SelectTrigger class="w-[150px]">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="level in proficiencyLevels" 
                                            :key="level" 
                                            :value="level"
                                        >
                                            {{ level }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button 
                                    type="button" 
                                    variant="ghost" 
                                    size="icon"
                                    @click="removeLanguage(index)"
                                >
                                    <Minus class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-if="manualForm.languages.length === 0" class="text-center py-4 text-muted-foreground">
                                <Languages class="mx-auto h-8 w-8 mb-2" />
                                <p>No languages added yet</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Submit -->
                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <Link href="/admin/cvs">Cancel</Link>
                        </Button>
                        <Button 
                            :disabled="!selectedUserId || !manualForm.name || manualForm.processing"
                            @click="submitManualForm"
                        >
                            <FileText class="mr-2 h-4 w-4" />
                            {{ manualForm.processing ? 'Creating...' : 'Create CV' }}
                        </Button>
                    </div>
                </TabsContent>

                <!-- AI Generation Tab -->
                <TabsContent value="ai" class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-amber-500" />
                                AI CV Generation
                            </CardTitle>
                            <CardDescription>
                                Generate a tailored CV based on a job application using AI
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-if="!selectedUserId" class="text-center py-8 text-muted-foreground">
                                <User class="mx-auto h-12 w-12 mb-4" />
                                <p class="text-lg font-medium">Select a user first</p>
                                <p class="text-sm">Choose a user above to see their job applications</p>
                            </div>

                            <template v-else>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label>CV Name *</Label>
                                        <Input 
                                            v-model="aiForm.name" 
                                            placeholder="e.g., Frontend Developer CV"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Template *</Label>
                                        <Select v-model="aiForm.template">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select template" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem 
                                                    v-for="template in templates" 
                                                    :key="template.id" 
                                                    :value="template.slug"
                                                >
                                                    {{ template.name }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Job Application *</Label>
                                    <p class="text-sm text-muted-foreground mb-2">
                                        Select a job application to tailor the CV for
                                    </p>
                                    <div v-if="loadingJobApplications" class="flex items-center gap-2 py-4">
                                        <Loader2 class="h-4 w-4 animate-spin" />
                                        <span class="text-sm text-muted-foreground">Loading job applications...</span>
                                    </div>
                                    <div v-else-if="userJobApplications.length === 0" class="text-center py-8 border rounded-lg bg-muted/20">
                                        <Briefcase class="mx-auto h-8 w-8 text-muted-foreground mb-2" />
                                        <p class="text-muted-foreground">No job applications found for this user</p>
                                        <p class="text-sm text-muted-foreground">The user needs to add job applications first</p>
                                    </div>
                                    <div v-else class="grid gap-3">
                                        <div
                                            v-for="job in userJobApplications"
                                            :key="job.id"
                                            class="p-4 border rounded-lg cursor-pointer transition-colors"
                                            :class="[
                                                aiForm.job_application_id === job.id.toString()
                                                    ? 'border-primary bg-primary/5'
                                                    : 'hover:border-primary/50 hover:bg-muted/50'
                                            ]"
                                            @click="aiForm.job_application_id = job.id.toString()"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <h4 class="font-medium">{{ job.title }}</h4>
                                                    <p class="text-sm text-muted-foreground">{{ job.company_name }}</p>
                                                </div>
                                                <div 
                                                    v-if="aiForm.job_application_id === job.id.toString()"
                                                    class="h-5 w-5 rounded-full bg-primary flex items-center justify-center"
                                                >
                                                    <svg class="h-3 w-3 text-primary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <p v-if="job.description" class="mt-2 text-sm text-muted-foreground line-clamp-2">
                                                {{ job.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Job Preview -->
                                <div v-if="selectedJobApplication" class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <Sparkles class="h-5 w-5 text-amber-500 mt-0.5" />
                                        <div>
                                            <h4 class="font-medium">Ready to Generate</h4>
                                            <p class="text-sm text-muted-foreground">
                                                AI will create a CV tailored for the "{{ selectedJobApplication.title }}" 
                                                position at {{ selectedJobApplication.company_name }}.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </CardContent>
                    </Card>

                    <!-- Submit -->
                    <div class="flex justify-end gap-4">
                        <Button variant="outline" as-child>
                            <Link href="/admin/cvs">Cancel</Link>
                        </Button>
                        <Button 
                            :disabled="!selectedUserId || !aiForm.name || !aiForm.template || !aiForm.job_application_id || isGenerating"
                            @click="generateWithAi"
                        >
                            <template v-if="isGenerating">
                                <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                                Generating...
                            </template>
                            <template v-else>
                                <Sparkles class="mr-2 h-4 w-4" />
                                Generate CV with AI
                            </template>
                        </Button>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
