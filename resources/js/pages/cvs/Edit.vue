<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as cvsIndex, show as cvShow } from '@/routes/cvs';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Plus,
    X,
    FileText,
    Save,
    Loader2,
    User,
    Briefcase,
    GraduationCap,
    Code,
    Languages,
    Sparkles,
} from 'lucide-vue-next';
import { ref } from 'vue';

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
    languages?: Array<{
        language: string;
        proficiency: string;
    }> | null;
}

interface Props {
    cv: Cv;
    templates: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'CVs', href: cvsIndex().url },
    { title: props.cv.name, href: `/cvs/${props.cv.id}` },
    { title: 'Edit', href: '#' },
];

const form = useForm({
    name: props.cv.name,
    template: props.cv.template,
    is_primary: props.cv.is_primary,
    summary: props.cv.summary ?? '',
    personal_info: {
        full_name: props.cv.personal_info?.full_name ?? '',
        email: props.cv.personal_info?.email ?? '',
        phone: props.cv.personal_info?.phone ?? '',
        location: props.cv.personal_info?.location ?? '',
        linkedin: props.cv.personal_info?.linkedin ?? '',
        website: props.cv.personal_info?.website ?? '',
    },
    experience: props.cv.experience ?? [],
    education: props.cv.education ?? [],
    skills: props.cv.skills ?? [],
    languages: props.cv.languages ?? [],
});

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

const submit = () => {
    form.put(`/cvs/${props.cv.id}`);
};
</script>

<template>
    <Head :title="`Edit - ${cv.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/cvs/${cv.id}`">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit CV</h1>
                    <p class="text-muted-foreground">Update your resume details</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
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
                            placeholder="Experienced software engineer with 5+ years of expertise..."
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
                                <Textarea v-model="exp.description" placeholder="Describe your responsibilities..." rows="3" />
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
                        <Link :href="`/cvs/${cv.id}`">Cancel</Link>
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
