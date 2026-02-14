<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    FileText,
    User,
    Plus,
    Minus,
    Briefcase,
    GraduationCap,
    Languages,
    Save,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

interface Template {
    id: number;
    name: string;
    slug: string;
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
}

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface Props {
    cv: Cv;
    user: UserType;
    templates: Template[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const form = useForm({
    name: props.cv.name,
    template: props.cv.template,
    is_primary: props.cv.is_primary,
    personal_info: {
        full_name: props.cv.personal_info?.full_name || '',
        email: props.cv.personal_info?.email || '',
        phone: props.cv.personal_info?.phone || '',
        location: props.cv.personal_info?.location || '',
        linkedin: props.cv.personal_info?.linkedin || '',
        website: props.cv.personal_info?.website || '',
    },
    summary: props.cv.summary || '',
    experience: props.cv.experience?.map(exp => ({
        company: exp.company || '',
        title: exp.title || '',
        location: exp.location || '',
        start_date: exp.start_date || '',
        end_date: exp.end_date || '',
        current: exp.current || false,
        description: exp.description || '',
    })) || [],
    education: props.cv.education?.map(edu => ({
        institution: edu.institution || '',
        degree: edu.degree || '',
        field: edu.field || '',
        start_date: edu.start_date || '',
        end_date: edu.end_date || '',
        gpa: edu.gpa || '',
    })) || [],
    skills: props.cv.skills || [],
    languages: props.cv.languages?.map(lang => ({
        language: lang.language || '',
        proficiency: lang.proficiency || 'Intermediate',
    })) || [],
});

// Experience helpers
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

// Education helpers
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

// Skills helpers
const newSkill = ref('');
const addSkill = () => {
    if (newSkill.value.trim() && !form.skills.includes(newSkill.value.trim())) {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index: number) => {
    form.skills.splice(index, 1);
};

// Language helpers
const addLanguage = () => {
    form.languages.push({
        language: '',
        proficiency: 'Intermediate',
    });
};

const removeLanguage = (index: number) => {
    form.languages.splice(index, 1);
};

const submitForm = () => {
    form.put(`/admin/cvs/${props.cv.id}`, {
        onSuccess: () => {
            toast({
                title: 'CV Updated',
                description: 'The CV has been updated successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to update the CV. Please check the form.',
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
        { title: cv.name, href: `/admin/cvs/${cv.id}` },
        { title: 'Edit' }
    ]">
        <Head :title="`Edit ${cv.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/admin/cvs/${cv.id}`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold">Edit CV</h1>
                        <p class="text-muted-foreground">
                            Editing CV for 
                            <Link :href="`/admin/users/${user.id}`" class="hover:underline">
                                {{ user.name }}
                            </Link>
                        </p>
                    </div>
                </div>
                <Button :disabled="form.processing" @click="submitForm">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>

            <form class="space-y-6" @submit.prevent="submitForm">
                <!-- Basic Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label>CV Name *</Label>
                                <Input v-model="form.name" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label>Template</Label>
                                <Select v-model="form.template">
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
                                :checked="form.is_primary"
                                @update:checked="form.is_primary = $event"
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
                                <Input v-model="form.personal_info.full_name" />
                            </div>
                            <div class="space-y-2">
                                <Label>Email</Label>
                                <Input v-model="form.personal_info.email" type="email" />
                            </div>
                            <div class="space-y-2">
                                <Label>Phone</Label>
                                <Input v-model="form.personal_info.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label>Location</Label>
                                <Input v-model="form.personal_info.location" placeholder="City, Country" />
                            </div>
                            <div class="space-y-2">
                                <Label>LinkedIn</Label>
                                <Input v-model="form.personal_info.linkedin" placeholder="https://linkedin.com/in/..." />
                            </div>
                            <div class="space-y-2">
                                <Label>Website</Label>
                                <Input v-model="form.personal_info.website" placeholder="https://..." />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label>Professional Summary</Label>
                            <Textarea 
                                v-model="form.summary" 
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
                            v-for="(exp, index) in form.experience" 
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
                        <div v-if="form.experience.length === 0" class="text-center py-8 text-muted-foreground">
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
                            v-for="(edu, index) in form.education" 
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
                        <div v-if="form.education.length === 0" class="text-center py-8 text-muted-foreground">
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
                                @keyup.enter.prevent="addSkill"
                            />
                            <Button type="button" @click="addSkill">Add</Button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Badge 
                                v-for="(skill, index) in form.skills" 
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
                            v-for="(lang, index) in form.languages" 
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
                        <div v-if="form.languages.length === 0" class="text-center py-4 text-muted-foreground">
                            <Languages class="mx-auto h-8 w-8 mb-2" />
                            <p>No languages added yet</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end gap-4">
                    <Button variant="outline" as-child>
                        <Link :href="`/admin/cvs/${cv.id}`">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
