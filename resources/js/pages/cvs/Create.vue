<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Plus,
    X,
    Upload,
    FileText,
    Sparkles,
    Save,
    Loader2,
    User,
    Briefcase,
    GraduationCap,
    Code,
    FolderOpen,
    Award,
    Languages,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as cvsIndex, store as cvStore } from '@/routes/cvs';
import { index as jobsIndex } from '@/routes/jobs';
import { type BreadcrumbItem } from '@/types';

interface Props {
    templates: string[];
    jobApplications: Array<{
        id: number;
        title: string;
        company: { name: string };
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'CVs', href: cvsIndex().url },
    { title: 'Create CV', href: '#' },
];

const activeTab = ref('upload');

// Form for file upload
const uploadForm = useForm({
    file: null as File | null,
    name: '',
    template: 'modern',
    is_primary: false,
});

// Form for AI generation
const generateForm = useForm({
    name: '',
    template: 'modern',
    job_application_id: '',
});

// Form for manual creation
const form = useForm({
    name: '',
    template: 'modern',
    is_primary: false,
    summary: '',
    personal_info: {
        full_name: '',
        email: '',
        phone: '',
        location: '',
        linkedin: '',
        website: '',
    },
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
    projects: [] as Array<{
        name: string;
        description: string;
        url: string;
        technologies: string[];
    }>,
    certifications: [] as Array<{
        name: string;
        issuer: string;
        date: string;
        url: string;
    }>,
    languages: [] as Array<{
        language: string;
        proficiency: string;
    }>,
});

const { addToast } = useToast();

const templateLabels: Record<string, string> = {
    modern: 'Modern',
    classic: 'Classic',
    minimal: 'Minimal',
    creative: 'Creative',
    executive: 'Executive',
};

// Skill handling
const newSkill = ref('');
const addSkill = () => {
    const skill = newSkill.value.trim();
    if (skill && !form.skills.includes(skill)) {
        form.skills.push(skill);
        newSkill.value = '';
    }
};
const removeSkill = (index: number) => {
    form.skills.splice(index, 1);
};

// Experience handling
const addExperience = () => {
    form.experience.push({
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
    form.experience.splice(index, 1);
};

// Education handling
const addEducation = () => {
    form.education.push({
        institution: '',
        degree: '',
        field: '',
        start_date: '',
        end_date: '',
        gpa: '',
    });
};
const removeEducation = (index: number) => {
    form.education.splice(index, 1);
};

// Project handling
const addProject = () => {
    form.projects.push({
        name: '',
        description: '',
        url: '',
        technologies: [],
    });
};
const removeProject = (index: number) => {
    form.projects.splice(index, 1);
};

// Certification handling
const addCertification = () => {
    form.certifications.push({
        name: '',
        issuer: '',
        date: '',
        url: '',
    });
};
const removeCertification = (index: number) => {
    form.certifications.splice(index, 1);
};

// Language handling
const addLanguage = () => {
    form.languages.push({
        language: '',
        proficiency: 'intermediate',
    });
};
const removeLanguage = (index: number) => {
    form.languages.splice(index, 1);
};

const proficiencyLevels = [
    { value: 'native', label: 'Native' },
    { value: 'fluent', label: 'Fluent' },
    { value: 'advanced', label: 'Advanced' },
    { value: 'intermediate', label: 'Intermediate' },
    { value: 'beginner', label: 'Beginner' },
];

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
        uploadForm.file = target.files[0];
        if (!uploadForm.name) {
            uploadForm.name = target.files[0].name.replace(/\.[^/.]+$/, '');
        }
    }
};

const submitUpload = async () => {
    const formData = new FormData();
    if (uploadForm.file) {
        formData.append('file', uploadForm.file);
    }
    formData.append('name', uploadForm.name);
    formData.append('template', uploadForm.template);
    formData.append('is_primary', uploadForm.is_primary ? '1' : '0');

    uploadForm.processing = true;
    try {
        const response = await fetch('/cvs/upload', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
            body: formData,
        });
        const data = await response.json();
        if (response.ok) {
            addToast({
                title: 'Success',
                message: 'CV uploaded and parsed successfully.',
                type: 'success'
            });
            window.location.href = `/cvs/${data.cv.id}`;
        } else {
            addToast({
                title: 'Error',
                message: data.message || 'Failed to upload CV.',
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
        uploadForm.processing = false;
    }
};

const submitManual = () => {
    form.post('/cvs');
};

const submitGenerate = () => {
    generateForm.post('/cvs/generate');
};
</script>

<template>
    <Head title="Create CV" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="cvsIndex().url">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Create CV</h1>
                    <p class="text-muted-foreground">Upload an existing CV or create one from scratch</p>
                </div>
            </div>

            <!-- Tabs -->
            <Tabs v-model="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="upload">
                        <Upload class="h-4 w-4 mr-2" />
                        Upload CV
                    </TabsTrigger>
                    <TabsTrigger value="generate">
                        <Sparkles class="h-4 w-4 mr-2" />
                        AI Generate
                    </TabsTrigger>
                    <TabsTrigger value="manual">
                        <FileText class="h-4 w-4 mr-2" />
                        Create Manually
                    </TabsTrigger>
                </TabsList>

                <!-- Upload Tab -->
                <TabsContent value="upload">
                    <Card>
                        <CardHeader>
                            <CardTitle>Upload Your CV</CardTitle>
                            <CardDescription>
                                Upload a PDF or DOCX file. We'll extract the content and help you improve it with AI.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitUpload" class="space-y-6">
                                <!-- File Upload -->
                                <div
                                    class="border-2 border-dashed rounded-lg p-8 text-center hover:border-primary/50 transition-colors cursor-pointer"
                                    @click="fileInput?.click()"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".pdf,.doc,.docx"
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />
                                    <div v-if="selectedFile" class="flex flex-col items-center">
                                        <FileText class="h-12 w-12 text-primary mb-4" />
                                        <p class="font-medium">{{ selectedFile.name }}</p>
                                        <p class="text-sm text-muted-foreground mt-1">
                                            {{ (selectedFile.size / 1024).toFixed(1) }} KB
                                        </p>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="mt-2"
                                            @click.stop="selectedFile = null; uploadForm.file = null"
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                    <div v-else class="flex flex-col items-center">
                                        <Upload class="h-12 w-12 text-muted-foreground/50 mb-4" />
                                        <p class="font-medium">Click to upload or drag and drop</p>
                                        <p class="text-sm text-muted-foreground mt-1">PDF, DOC, or DOCX (max 10MB)</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="upload-name">CV Name *</Label>
                                        <Input
                                            id="upload-name"
                                            v-model="uploadForm.name"
                                            placeholder="e.g. Software Engineer Resume"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="upload-template">Template</Label>
                                        <Select v-model="uploadForm.template">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="template in templates" :key="template" :value="template">
                                                    {{ templateLabels[template] }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50">
                                    <div>
                                        <p class="font-medium">Set as Primary CV</p>
                                        <p class="text-sm text-muted-foreground">This CV will be used by default</p>
                                    </div>
                                    <Switch v-model:checked="uploadForm.is_primary" />
                                </div>

                                <div class="flex justify-end gap-3">
                                    <Button variant="outline" type="button" as-child>
                                        <Link :href="cvsIndex().url">Cancel</Link>
                                    </Button>
                                    <Button type="submit" :disabled="!selectedFile || !uploadForm.name || uploadForm.processing">
                                        <Loader2 v-if="uploadForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                                        <Upload v-else class="h-4 w-4 mr-2" />
                                        Upload CV
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Generate Tab -->
                <TabsContent value="generate">
                    <Card>
                        <CardHeader>
                            <CardTitle>Generate with AI</CardTitle>
                            <CardDescription>
                                Select a job application and let AI create a tailored CV for you based on your profile.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitGenerate" class="space-y-6">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="space-y-2">
                                        <Label for="gen-name">CV Name *</Label>
                                        <Input
                                            id="gen-name"
                                            v-model="generateForm.name"
                                            placeholder="e.g. Tailored Resume for [Job]"
                                        />
                                        <p v-if="generateForm.errors.name" class="text-sm text-red-500">{{ generateForm.errors.name }}</p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="gen-job">Target Job Application *</Label>
                                        <Select v-model="generateForm.job_application_id">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a job..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="job in jobApplications"
                                                    :key="job.id"
                                                    :value="job.id.toString()"
                                                >
                                                    {{ job.title }} ({{ job.company?.name || 'Unknown Company' }})
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="generateForm.errors.job_application_id" class="text-sm text-red-500">{{ generateForm.errors.job_application_id }}</p>
                                        <p v-if="jobApplications.length === 0" class="text-sm text-muted-foreground mt-1">
                                            No job applications found. <Link :href="jobsIndex().url" class="text-primary hover:underline">Find a job</Link> to apply for first.
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="gen-template">Template</Label>
                                        <Select v-model="generateForm.template">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="template in templates" :key="template" :value="template">
                                                    {{ templateLabels[template] }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="generateForm.errors.template" class="text-sm text-red-500">{{ generateForm.errors.template }}</p>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3">
                                    <Button variant="outline" type="button" as-child>
                                        <Link :href="cvsIndex().url">Cancel</Link>
                                    </Button>
                                    <Button type="submit" :disabled="generateForm.processing">
                                        <Loader2 v-if="generateForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                                        <Sparkles v-else class="h-4 w-4 mr-2" />
                                        Generate CV
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Manual Tab -->
                <TabsContent value="manual">
                    <form @submit.prevent="submitManual" class="space-y-6">
                        <!-- Basic Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <FileText class="h-5 w-5" />
                                    CV Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="name">CV Name *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            placeholder="e.g. Software Engineer Resume"
                                            :class="{ 'border-red-500': form.errors.name }"
                                        />
                                        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="template">Template</Label>
                                        <Select v-model="form.template">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="template in templates" :key="template" :value="template">
                                                    {{ templateLabels[template] }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-4 rounded-lg bg-muted/50">
                                    <div>
                                        <p class="font-medium">Set as Primary CV</p>
                                        <p class="text-sm text-muted-foreground">This CV will be used by default</p>
                                    </div>
                                    <Switch v-model:checked="form.is_primary" />
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label>Full Name</Label>
                                        <Input v-model="form.personal_info.full_name" placeholder="John Doe" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Email</Label>
                                        <Input v-model="form.personal_info.email" type="email" placeholder="john@example.com" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Phone</Label>
                                        <Input v-model="form.personal_info.phone" placeholder="+1 (555) 123-4567" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Location</Label>
                                        <Input v-model="form.personal_info.location" placeholder="San Francisco, CA" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>LinkedIn</Label>
                                        <Input v-model="form.personal_info.linkedin" placeholder="linkedin.com/in/johndoe" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Website</Label>
                                        <Input v-model="form.personal_info.website" placeholder="johndoe.com" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Summary -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Sparkles class="h-5 w-5" />
                                    Professional Summary
                                </CardTitle>
                                <CardDescription>A brief overview of your experience and goals</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Textarea
                                    v-model="form.summary"
                                    placeholder="Experienced software engineer with 5+ years of expertise in building scalable web applications..."
                                    rows="4"
                                />
                            </CardContent>
                        </Card>

                        <!-- Experience -->
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <Briefcase class="h-5 w-5" />
                                        Work Experience
                                    </CardTitle>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="addExperience">
                                    <Plus class="h-4 w-4 mr-1" />
                                    Add
                                </Button>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div v-if="form.experience.length === 0" class="text-center py-8 text-muted-foreground">
                                    <Briefcase class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                    <p>No experience added yet</p>
                                </div>
                                <div
                                    v-for="(exp, index) in form.experience"
                                    :key="index"
                                    class="p-4 border rounded-lg space-y-4"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">Experience {{ index + 1 }}</span>
                                        <Button type="button" variant="ghost" size="sm" @click="removeExperience(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label>Company</Label>
                                            <Input v-model="exp.company" placeholder="Company name" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Job Title</Label>
                                            <Input v-model="exp.title" placeholder="Software Engineer" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Location</Label>
                                            <Input v-model="exp.location" placeholder="City, Country" />
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1 space-y-2">
                                                <Label>Start Date</Label>
                                                <Input v-model="exp.start_date" type="month" />
                                            </div>
                                            <div class="flex-1 space-y-2">
                                                <Label>End Date</Label>
                                                <Input v-model="exp.end_date" type="month" :disabled="exp.current" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Switch v-model:checked="exp.current" />
                                        <Label>I currently work here</Label>
                                    </div>
                                    <div class="space-y-2">
                                        <Label>Description</Label>
                                        <Textarea v-model="exp.description" placeholder="Describe your responsibilities and achievements..." rows="3" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Education -->
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <GraduationCap class="h-5 w-5" />
                                        Education
                                    </CardTitle>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="addEducation">
                                    <Plus class="h-4 w-4 mr-1" />
                                    Add
                                </Button>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div v-if="form.education.length === 0" class="text-center py-8 text-muted-foreground">
                                    <GraduationCap class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                    <p>No education added yet</p>
                                </div>
                                <div
                                    v-for="(edu, index) in form.education"
                                    :key="index"
                                    class="p-4 border rounded-lg space-y-4"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium">Education {{ index + 1 }}</span>
                                        <Button type="button" variant="ghost" size="sm" @click="removeEducation(index)">
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label>Institution</Label>
                                            <Input v-model="edu.institution" placeholder="University name" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Degree</Label>
                                            <Input v-model="edu.degree" placeholder="Bachelor's, Master's..." />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>Field of Study</Label>
                                            <Input v-model="edu.field" placeholder="Computer Science" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label>GPA (optional)</Label>
                                            <Input v-model="edu.gpa" placeholder="3.8/4.0" />
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
                            </CardContent>
                        </Card>

                        <!-- Skills -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Code class="h-5 w-5" />
                                    Skills
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex gap-2">
                                    <Input
                                        v-model="newSkill"
                                        placeholder="Add a skill..."
                                        @keyup.enter.prevent="addSkill"
                                    />
                                    <Button type="button" variant="outline" @click="addSkill">
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </div>
                                <div v-if="form.skills.length > 0" class="flex flex-wrap gap-2">
                                    <Badge v-for="(skill, index) in form.skills" :key="index" variant="secondary" class="pl-3 pr-1 py-1">
                                        {{ skill }}
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-5 w-5 p-0 ml-1 hover:bg-transparent"
                                            @click="removeSkill(index)"
                                        >
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Languages -->
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between">
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        <Languages class="h-5 w-5" />
                                        Languages
                                    </CardTitle>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="addLanguage">
                                    <Plus class="h-4 w-4 mr-1" />
                                    Add
                                </Button>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div v-if="form.languages.length === 0" class="text-center py-8 text-muted-foreground">
                                    <Languages class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                    <p>No languages added yet</p>
                                </div>
                                <div
                                    v-for="(lang, index) in form.languages"
                                    :key="index"
                                    class="flex items-center gap-4"
                                >
                                    <div class="flex-1">
                                        <Input v-model="lang.language" placeholder="Language" />
                                    </div>
                                    <div class="w-40">
                                        <Select v-model="lang.proficiency">
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="level in proficiencyLevels" :key="level.value" :value="level.value">
                                                    {{ level.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <Button type="button" variant="ghost" size="sm" @click="removeLanguage(index)">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="cvsIndex().url">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                                <Save v-else class="h-4 w-4 mr-2" />
                                Create CV
                            </Button>
                        </div>
                    </form>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
