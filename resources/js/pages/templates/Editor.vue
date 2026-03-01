<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, reactive, toRaw } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { show as templateShow, editor as templateEditor, download as templateDownload } from '@/routes/templates';
import { withData as previewWithData } from '@/routes/templates/preview';
import { login } from '@/routes';
import { useToast } from '@/composables/useToast';
import {
    Download,
    Eye,
    Save,
    ArrowLeft,
    User,
    Briefcase,
    GraduationCap,
    Wrench,
    Award,
    Languages,
    Heart,
    Plus,
    Trash2,
    FileText,
    Loader2,
    FileDown,
    CheckCircle,
} from 'lucide-vue-next';

interface Section {
    id: string;
    name: string;
    enabled: boolean;
}

interface CvTemplate {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    sections: Section[];
    styles: {
        primaryColor: string;
        secondaryColor: string;
        fontFamily: string;
        fontSize: string;
    };
}

interface Props {
    template: CvTemplate;
    previewHtml: string;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
}

const props = defineProps<Props>();

const { addToast } = useToast();

// Form for CV data
const form = useForm({
    template_id: props.template.id,
    full_name: '',
    email: '',
    phone: '',
    address: '',
    linkedin: '',
    website: '',
    summary: '',
    experiences: [
        {
            job_title: '',
            company: '',
            location: '',
            start_date: '',
            end_date: '',
            current: false,
            description: '',
        },
    ],
    education: [
        {
            degree: '',
            school: '',
            location: '',
            start_date: '',
            end_date: '',
            gpa: '',
            description: '',
        },
    ],
    skills: [''],
    certifications: [
        {
            name: '',
            issuer: '',
            date: '',
            url: '',
        },
    ],
    languages: [
        {
            language: '',
            proficiency: 'Intermediate',
        },
    ],
    interests: [''],
});

const activeSection = ref('personal');
const previewLoading = ref(false);
const livePreviewHtml = ref(props.previewHtml);
const showSaveDialog = ref(false);
const cvName = ref('');
const isSaving = ref(false);
const isDownloading = ref(false);

// Map 'header' section ID to 'personal' for the editor tabs
const enabledSections = computed(() => {
    const sections = props.template.sections?.filter((s) => s.enabled).map((s) => s.id) || [];
    if (sections.includes('header') && !sections.includes('personal')) {
        sections.push('personal');
    }
    // Always ensure personal is available since it's essential
    if (!sections.includes('personal') && !sections.includes('header')) {
        sections.push('personal');
    }
    return sections;
});

const sectionNav = computed(() => {
    const allSections = [
        { id: 'personal', label: 'Personal Info', icon: User },
        { id: 'summary', label: 'Summary', icon: FileText },
        { id: 'experience', label: 'Experience', icon: Briefcase },
        { id: 'education', label: 'Education', icon: GraduationCap },
        { id: 'skills', label: 'Skills', icon: Wrench },
        { id: 'certifications', label: 'Certifications', icon: Award },
        { id: 'languages', label: 'Languages', icon: Languages },
        { id: 'interests', label: 'Interests', icon: Heart },
    ];
    return allSections.filter((s) => enabledSections.value.includes(s.id));
});

// Add/remove helpers
const addExperience = () => {
    form.experiences.push({ job_title: '', company: '', location: '', start_date: '', end_date: '', current: false, description: '' });
};
const removeExperience = (index: number) => {
    if (form.experiences.length > 1) form.experiences.splice(index, 1);
};
const addEducation = () => {
    form.education.push({ degree: '', school: '', location: '', start_date: '', end_date: '', gpa: '', description: '' });
};
const removeEducation = (index: number) => {
    if (form.education.length > 1) form.education.splice(index, 1);
};
const addSkill = () => {
    form.skills.push('');
};
const removeSkill = (index: number) => {
    if (form.skills.length > 1) form.skills.splice(index, 1);
};
const addCertification = () => {
    form.certifications.push({ name: '', issuer: '', date: '', url: '' });
};
const removeCertification = (index: number) => {
    if (form.certifications.length > 1) form.certifications.splice(index, 1);
};
const addLanguage = () => {
    form.languages.push({ language: '', proficiency: 'Intermediate' });
};
const removeLanguage = (index: number) => {
    if (form.languages.length > 1) form.languages.splice(index, 1);
};
const addInterest = () => {
    form.interests.push('');
};
const removeInterest = (index: number) => {
    if (form.interests.length > 1) form.interests.splice(index, 1);
};

// Live preview with debounce
let previewTimeout: ReturnType<typeof setTimeout>;
const updatePreview = () => {
    previewLoading.value = true;
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(async () => {
        try {
            const payload = {
                template_id: form.template_id,
                full_name: form.full_name,
                email: form.email,
                phone: form.phone,
                address: form.address,
                linkedin: form.linkedin,
                website: form.website,
                summary: form.summary,
                experiences: JSON.parse(JSON.stringify(form.experiences)),
                education: JSON.parse(JSON.stringify(form.education)),
                skills: [...form.skills],
                certifications: JSON.parse(JSON.stringify(form.certifications)),
                languages: JSON.parse(JSON.stringify(form.languages)),
                interests: [...form.interests],
            };
            const response = await fetch(previewWithData.url(props.template.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });
            if (response.ok) {
                livePreviewHtml.value = await response.text();
            }
        } catch (error) {
            console.error('Preview error:', error);
        } finally {
            previewLoading.value = false;
        }
    }, 500);
};

// Watch individual form fields for changes to trigger preview updates
watch(
    () => [
        form.full_name, form.email, form.phone, form.address,
        form.linkedin, form.website, form.summary,
        JSON.stringify(form.experiences),
        JSON.stringify(form.education),
        JSON.stringify(form.skills),
        JSON.stringify(form.certifications),
        JSON.stringify(form.languages),
        JSON.stringify(form.interests),
    ],
    () => { updatePreview(); },
);

// Download helpers
const buildCvDataForDownload = () => ({
    personal_info: {
        full_name: form.full_name,
        email: form.email,
        phone: form.phone,
        location: form.address,
        linkedin: form.linkedin,
        website: form.website,
    },
    summary: form.summary,
    experience: form.experiences.map((exp) => ({
        title: exp.job_title,
        company: exp.company,
        location: exp.location,
        start_date: exp.start_date,
        end_date: exp.current ? 'Present' : exp.end_date,
        description: exp.description,
    })),
    education: form.education.map((edu) => ({
        degree: edu.degree,
        institution: edu.school,
        location: edu.location,
        graduation_date: edu.end_date,
        description: edu.description,
    })),
    skills: form.skills.filter((s) => !!s),
    certifications: form.certifications.filter((c) => !!c.name),
    languages: form.languages.filter((l) => !!l.language),
    interests: form.interests.filter((i) => !!i),
});

const downloadPdf = async () => {
    isDownloading.value = true;
    try {
        const response = await fetch(`/templates/${props.template.id}/download-pdf`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.data()),
        });
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${props.template.slug || 'cv'}-${new Date().toISOString().slice(0, 10)}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
            addToast({ title: 'Downloaded', message: 'PDF downloaded successfully.', type: 'success' });
        } else {
            addToast({ title: 'Error', message: 'Failed to download PDF.', type: 'error' });
        }
    } catch {
        addToast({ title: 'Error', message: 'A network error occurred.', type: 'error' });
    } finally {
        isDownloading.value = false;
    }
};

const downloadDocx = () => {
    const formData = new URLSearchParams();
    formData.append('cv_data', JSON.stringify(buildCvDataForDownload()));
    window.location.href = `${templateDownload.url(props.template.id)}?${formData.toString()}`;
};

// Save as CV
const openSaveDialog = () => {
    if (!props.user) {
        router.visit(login.url(), { data: { redirect: templateEditor.url(props.template.id) } });
        return;
    }
    cvName.value = form.full_name ? `${form.full_name} - ${props.template.name}` : props.template.name;
    showSaveDialog.value = true;
};

const saveAsCv = async () => {
    if (!cvName.value.trim()) {
        addToast({ title: 'Error', message: 'Please enter a name for your CV.', type: 'error' });
        return;
    }
    isSaving.value = true;
    try {
        const response = await fetch(`/templates/${props.template.id}/save-as-cv`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ ...form.data(), cv_name: cvName.value }),
        });
        const data = await response.json();
        if (data.success) {
            showSaveDialog.value = false;
            addToast({ title: 'Saved', message: 'CV saved to your dashboard!', type: 'success' });
            setTimeout(() => { router.visit(`/cvs/${data.cv.id}`); }, 1000);
        } else {
            addToast({ title: 'Error', message: data.message || 'Failed to save CV.', type: 'error' });
        }
    } catch {
        addToast({ title: 'Error', message: 'A network error occurred.', type: 'error' });
    } finally {
        isSaving.value = false;
    }
};

const proficiencyLevels = ['Beginner', 'Elementary', 'Intermediate', 'Upper Intermediate', 'Advanced', 'Native'];

const scrollToSection = (sectionId: string) => {
    activeSection.value = sectionId;
    const el = document.getElementById(`section-${sectionId}`);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<template>
    <LandingLayout>
        <Head :title="'Edit ' + template.name + ' - CV Template'" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Sticky Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
                <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link
                                :href="templateShow.url(template.id)"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                            >
                                <ArrowLeft class="w-5 h-5" />
                            </Link>
                            <div>
                                <h1 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ template.name }}
                                </h1>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Fill in your details and see changes in real-time
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" @click="downloadDocx" :disabled="isDownloading">
                                <FileDown class="w-4 h-4 mr-1.5" />
                                <span class="hidden sm:inline">DOCX</span>
                            </Button>
                            <Button variant="outline" size="sm" @click="downloadPdf" :disabled="isDownloading">
                                <Download class="w-4 h-4 mr-1.5" />
                                <span class="hidden sm:inline">PDF</span>
                                <Loader2 v-if="isDownloading" class="w-4 h-4 ml-1 animate-spin" />
                            </Button>
                            <Button size="sm" @click="openSaveDialog">
                                <Save class="w-4 h-4 mr-1.5" />
                                {{ user ? 'Save CV' : 'Login to Save' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex gap-6">
                    <!-- Section Navigation Sidebar (desktop) -->
                    <nav class="hidden lg:flex flex-col gap-1 w-44 shrink-0 sticky top-20 self-start">
                        <button
                            v-for="section in sectionNav"
                            :key="section.id"
                            @click="scrollToSection(section.id)"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-left"
                            :class="activeSection === section.id
                                ? 'bg-primary text-primary-foreground'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                        >
                            <component :is="section.icon" class="w-4 h-4 shrink-0" />
                            {{ section.label }}
                        </button>

                        <Separator class="my-3" />

                        <div class="space-y-2">
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="downloadPdf" :disabled="isDownloading">
                                <Download class="w-4 h-4 mr-2" />
                                Download PDF
                            </Button>
                            <Button variant="outline" size="sm" class="w-full justify-start" @click="downloadDocx">
                                <FileDown class="w-4 h-4 mr-2" />
                                Download DOCX
                            </Button>
                            <Button size="sm" class="w-full justify-start" @click="openSaveDialog">
                                <Save class="w-4 h-4 mr-2" />
                                {{ user ? 'Save as CV' : 'Login to Save' }}
                            </Button>
                        </div>
                    </nav>

                    <!-- Mobile Section Pills -->
                    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-4 py-2">
                        <div class="flex overflow-x-auto gap-2 pb-1">
                            <button
                                v-for="section in sectionNav"
                                :key="section.id"
                                @click="scrollToSection(section.id)"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors shrink-0"
                                :class="activeSection === section.id
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                            >
                                <component :is="section.icon" class="w-3.5 h-3.5" />
                                {{ section.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Main Content Area -->
                    <div class="flex-1 min-w-0">
                        <div class="grid lg:grid-cols-5 gap-6">
                            <!-- Form Column -->
                            <div class="lg:col-span-3 space-y-6 pb-20 lg:pb-0">
                                <!-- Personal Info -->
                                <Card v-if="enabledSections.includes('personal')" id="section-personal">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2">
                                            <User class="w-5 h-5 text-primary" />
                                            Personal Information
                                        </CardTitle>
                                        <CardDescription>Your basic contact details</CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="full_name">Full Name *</Label>
                                                <Input id="full_name" v-model="form.full_name" placeholder="John Doe" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="email">Email *</Label>
                                                <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" />
                                            </div>
                                        </div>
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="phone">Phone</Label>
                                                <Input id="phone" v-model="form.phone" placeholder="+1 (555) 123-4567" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="address">Location</Label>
                                                <Input id="address" v-model="form.address" placeholder="New York, NY" />
                                            </div>
                                        </div>
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="linkedin">LinkedIn</Label>
                                                <Input id="linkedin" v-model="form.linkedin" placeholder="linkedin.com/in/johndoe" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="website">Website / Portfolio</Label>
                                                <Input id="website" v-model="form.website" placeholder="johndoe.com" />
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Summary -->
                                <Card v-if="enabledSections.includes('summary')" id="section-summary">
                                    <CardHeader>
                                        <CardTitle class="flex items-center gap-2">
                                            <FileText class="w-5 h-5 text-primary" />
                                            Professional Summary
                                        </CardTitle>
                                        <CardDescription>A brief overview of your professional background</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <Textarea
                                            v-model="form.summary"
                                            placeholder="Experienced professional with a passion for..."
                                            class="min-h-[150px]"
                                        />
                                    </CardContent>
                                </Card>

                                <!-- Experience -->
                                <div v-if="enabledSections.includes('experience')" id="section-experience" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Briefcase class="w-5 h-5 text-primary" />
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Work Experience</h3>
                                        </div>
                                        <Button @click="addExperience" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-1.5" />
                                            Add
                                        </Button>
                                    </div>

                                    <Card v-for="(exp, index) in form.experiences" :key="index">
                                        <CardHeader class="pb-3">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-base">
                                                    {{ exp.job_title || exp.company
                                                        ? `${exp.job_title || 'Position'}${exp.company ? ' at ' + exp.company : ''}`
                                                        : `Experience ${index + 1}`
                                                    }}
                                                </CardTitle>
                                                <Button
                                                    v-if="form.experiences.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeExperience(index)"
                                                    class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Job Title *</Label>
                                                    <Input v-model="exp.job_title" placeholder="Software Engineer" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Company *</Label>
                                                    <Input v-model="exp.company" placeholder="Tech Company Inc." />
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <Label>Location</Label>
                                                <Input v-model="exp.location" placeholder="San Francisco, CA" />
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Start Date</Label>
                                                    <Input type="month" v-model="exp.start_date" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>End Date</Label>
                                                    <Input type="month" v-model="exp.end_date" :disabled="exp.current" />
                                                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            v-model="exp.current"
                                                            class="rounded border-gray-300 text-primary focus:ring-primary"
                                                        />
                                                        Currently working here
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <Label>Description</Label>
                                                <Textarea
                                                    v-model="exp.description"
                                                    placeholder="Describe your responsibilities and achievements..."
                                                    class="min-h-[100px]"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                <!-- Education -->
                                <div v-if="enabledSections.includes('education')" id="section-education" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <GraduationCap class="w-5 h-5 text-primary" />
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Education</h3>
                                        </div>
                                        <Button @click="addEducation" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-1.5" />
                                            Add
                                        </Button>
                                    </div>

                                    <Card v-for="(edu, index) in form.education" :key="index">
                                        <CardHeader class="pb-3">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-base">
                                                    {{ edu.degree || edu.school
                                                        ? `${edu.degree || 'Degree'}${edu.school ? ' - ' + edu.school : ''}`
                                                        : `Education ${index + 1}`
                                                    }}
                                                </CardTitle>
                                                <Button
                                                    v-if="form.education.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeEducation(index)"
                                                    class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Degree *</Label>
                                                    <Input v-model="edu.degree" placeholder="Bachelor of Science in Computer Science" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>School / University *</Label>
                                                    <Input v-model="edu.school" placeholder="Stanford University" />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Location</Label>
                                                    <Input v-model="edu.location" placeholder="Palo Alto, CA" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>GPA</Label>
                                                    <Input v-model="edu.gpa" placeholder="3.8" />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Start Date</Label>
                                                    <Input type="month" v-model="edu.start_date" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>End Date</Label>
                                                    <Input type="month" v-model="edu.end_date" />
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <Label>Additional Info</Label>
                                                <Textarea
                                                    v-model="edu.description"
                                                    placeholder="Relevant coursework, honors, activities..."
                                                    class="min-h-[80px]"
                                                />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                <!-- Skills -->
                                <Card v-if="enabledSections.includes('skills')" id="section-skills">
                                    <CardHeader>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <CardTitle class="flex items-center gap-2">
                                                    <Wrench class="w-5 h-5 text-primary" />
                                                    Skills
                                                </CardTitle>
                                                <CardDescription>List your professional skills</CardDescription>
                                            </div>
                                            <Button @click="addSkill" variant="outline" size="sm">
                                                <Plus class="w-4 h-4 mr-1.5" />
                                                Add
                                            </Button>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="flex flex-wrap gap-2">
                                            <div
                                                v-for="(skill, index) in form.skills"
                                                :key="index"
                                                class="flex items-center gap-1"
                                            >
                                                <Input
                                                    v-model="form.skills[index]"
                                                    placeholder="e.g., JavaScript"
                                                    class="w-40"
                                                />
                                                <Button
                                                    v-if="form.skills.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeSkill(index)"
                                                    class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 shrink-0"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Certifications -->
                                <div v-if="enabledSections.includes('certifications')" id="section-certifications" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <Award class="w-5 h-5 text-primary" />
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Certifications</h3>
                                        </div>
                                        <Button @click="addCertification" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-1.5" />
                                            Add
                                        </Button>
                                    </div>

                                    <Card v-for="(cert, index) in form.certifications" :key="index">
                                        <CardHeader class="pb-3">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-base">{{ cert.name || `Certification ${index + 1}` }}</CardTitle>
                                                <Button
                                                    v-if="form.certifications.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeCertification(index)"
                                                    class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Certification Name *</Label>
                                                    <Input v-model="cert.name" placeholder="AWS Solutions Architect" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Issuing Organization</Label>
                                                    <Input v-model="cert.issuer" placeholder="Amazon Web Services" />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Date Obtained</Label>
                                                    <Input type="month" v-model="cert.date" />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Credential URL</Label>
                                                    <Input v-model="cert.url" placeholder="https://..." />
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>

                                <!-- Languages -->
                                <Card v-if="enabledSections.includes('languages')" id="section-languages">
                                    <CardHeader>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <CardTitle class="flex items-center gap-2">
                                                    <Languages class="w-5 h-5 text-primary" />
                                                    Languages
                                                </CardTitle>
                                                <CardDescription>Languages you speak and proficiency</CardDescription>
                                            </div>
                                            <Button @click="addLanguage" variant="outline" size="sm">
                                                <Plus class="w-4 h-4 mr-1.5" />
                                                Add
                                            </Button>
                                        </div>
                                    </CardHeader>
                                    <CardContent class="space-y-3">
                                        <div
                                            v-for="(lang, index) in form.languages"
                                            :key="index"
                                            class="flex items-center gap-3"
                                        >
                                            <Input v-model="lang.language" placeholder="Language" class="flex-1" />
                                            <select
                                                v-model="lang.proficiency"
                                                class="h-9 rounded-md border border-input bg-background px-3 py-1.5 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            >
                                                <option v-for="level in proficiencyLevels" :key="level" :value="level">
                                                    {{ level }}
                                                </option>
                                            </select>
                                            <Button
                                                v-if="form.languages.length > 1"
                                                variant="ghost"
                                                size="icon"
                                                @click="removeLanguage(index)"
                                                class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 shrink-0"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" />
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>

                                <!-- Interests -->
                                <Card v-if="enabledSections.includes('interests')" id="section-interests">
                                    <CardHeader>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <CardTitle class="flex items-center gap-2">
                                                    <Heart class="w-5 h-5 text-primary" />
                                                    Interests & Hobbies
                                                </CardTitle>
                                                <CardDescription>Add your personal interests</CardDescription>
                                            </div>
                                            <Button @click="addInterest" variant="outline" size="sm">
                                                <Plus class="w-4 h-4 mr-1.5" />
                                                Add
                                            </Button>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="flex flex-wrap gap-2">
                                            <div
                                                v-for="(interest, index) in form.interests"
                                                :key="index"
                                                class="flex items-center gap-1"
                                            >
                                                <Input
                                                    v-model="form.interests[index]"
                                                    placeholder="e.g., Photography"
                                                    class="w-40"
                                                />
                                                <Button
                                                    v-if="form.interests.length > 1"
                                                    variant="ghost"
                                                    size="icon"
                                                    @click="removeInterest(index)"
                                                    class="h-8 w-8 text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950 shrink-0"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            <!-- Live Preview Column (desktop) -->
                            <div class="lg:col-span-2 hidden lg:block">
                                <div class="sticky top-20">
                                    <Card class="overflow-hidden">
                                        <CardHeader class="bg-gray-50 dark:bg-gray-800 border-b py-3">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="flex items-center gap-2 text-base">
                                                    <Eye class="w-4 h-4" />
                                                    Live Preview
                                                </CardTitle>
                                                <div class="flex items-center gap-2">
                                                    <div v-if="previewLoading" class="flex items-center gap-1.5 text-xs text-gray-500">
                                                        <Loader2 class="w-3.5 h-3.5 animate-spin" />
                                                        Updating...
                                                    </div>
                                                    <Badge v-else variant="secondary" class="text-xs">
                                                        <CheckCircle class="w-3 h-3 mr-1" />
                                                        Synced
                                                    </Badge>
                                                </div>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="p-0">
                                            <div class="aspect-[3/4] bg-white overflow-hidden">
                                                <iframe
                                                    :srcdoc="livePreviewHtml"
                                                    class="w-full h-full border-0"
                                                    sandbox="allow-same-origin"
                                                    title="CV Preview"
                                                ></iframe>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Preview FAB -->
            <div class="lg:hidden fixed bottom-16 right-4 z-40">
                <Button
                    size="lg"
                    class="rounded-full shadow-lg h-12 w-12"
                    @click="downloadPdf"
                    :disabled="isDownloading"
                    title="Download PDF"
                >
                    <Download v-if="!isDownloading" class="w-5 h-5" />
                    <Loader2 v-else class="w-5 h-5 animate-spin" />
                </Button>
            </div>
        </div>

        <!-- Save Dialog -->
        <Dialog v-model:open="showSaveDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Save as CV</DialogTitle>
                    <DialogDescription>
                        Your CV will be saved to your dashboard where you can edit, share, and export it anytime.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="cv-name">CV Name *</Label>
                        <Input
                            id="cv-name"
                            v-model="cvName"
                            placeholder="My Professional CV"
                            @keydown.enter="saveAsCv"
                        />
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showSaveDialog = false">Cancel</Button>
                    <Button @click="saveAsCv" :disabled="isSaving || !cvName.trim()">
                        <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                        <Save v-else class="w-4 h-4 mr-2" />
                        {{ isSaving ? 'Saving...' : 'Save CV' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </LandingLayout>
</template>
