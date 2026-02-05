<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { show as templateShow, editor as templateEditor, download as templateDownload, saveAsCv as templateSaveAsCv } from '@/routes/templates';
import { withData as previewWithData } from '@/routes/templates/preview';
import { login } from '@/routes';
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
    ChevronRight
} from 'lucide-vue-next';

interface Section {
    id: string;
    name: string;
    enabled: boolean;
}

interface CvTemplate {
    id: number;
    name: string;
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

// Form for CV data
const form = useForm({
    template_id: props.template.id,
    // Personal Info
    full_name: '',
    email: '',
    phone: '',
    address: '',
    linkedin: '',
    website: '',
    summary: '',
    
    // Experience (array of jobs)
    experiences: [
        {
            job_title: '',
            company: '',
            location: '',
            start_date: '',
            end_date: '',
            current: false,
            description: ''
        }
    ],
    
    // Education
    education: [
        {
            degree: '',
            school: '',
            location: '',
            start_date: '',
            end_date: '',
            gpa: '',
            description: ''
        }
    ],
    
    // Skills
    skills: [''],
    
    // Certifications
    certifications: [
        {
            name: '',
            issuer: '',
            date: '',
            url: ''
        }
    ],
    
    // Languages
    languages: [
        {
            language: '',
            proficiency: 'Intermediate'
        }
    ],
    
    // Interests
    interests: ['']
});

const activeTab = ref('personal');
const previewLoading = ref(false);
const livePreviewHtml = ref(props.previewHtml);

const enabledSections = computed(() => {
    return props.template.sections?.filter(s => s.enabled).map(s => s.id) || [];
});

const sectionIcons: Record<string, any> = {
    personal: User,
    summary: FileText,
    experience: Briefcase,
    education: GraduationCap,
    skills: Wrench,
    certifications: Award,
    languages: Languages,
    interests: Heart
};

// Add experience entry
const addExperience = () => {
    form.experiences.push({
        job_title: '',
        company: '',
        location: '',
        start_date: '',
        end_date: '',
        current: false,
        description: ''
    });
};

const removeExperience = (index: number) => {
    if (form.experiences.length > 1) {
        form.experiences.splice(index, 1);
    }
};

// Add education entry
const addEducation = () => {
    form.education.push({
        degree: '',
        school: '',
        location: '',
        start_date: '',
        end_date: '',
        gpa: '',
        description: ''
    });
};

const removeEducation = (index: number) => {
    if (form.education.length > 1) {
        form.education.splice(index, 1);
    }
};

// Add skill
const addSkill = () => {
    form.skills.push('');
};

const removeSkill = (index: number) => {
    if (form.skills.length > 1) {
        form.skills.splice(index, 1);
    }
};

// Add certification
const addCertification = () => {
    form.certifications.push({
        name: '',
        issuer: '',
        date: '',
        url: ''
    });
};

const removeCertification = (index: number) => {
    if (form.certifications.length > 1) {
        form.certifications.splice(index, 1);
    }
};

// Add language
const addLanguage = () => {
    form.languages.push({
        language: '',
        proficiency: 'Intermediate'
    });
};

const removeLanguage = (index: number) => {
    if (form.languages.length > 1) {
        form.languages.splice(index, 1);
    }
};

// Add interest
const addInterest = () => {
    form.interests.push('');
};

const removeInterest = (index: number) => {
    if (form.interests.length > 1) {
        form.interests.splice(index, 1);
    }
};

// Preview with debounce
let previewTimeout: ReturnType<typeof setTimeout>;
const updatePreview = () => {
    previewLoading.value = true;
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(async () => {
        try {
            const response = await fetch(previewWithData.url(props.template.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(form.data())
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

// Watch form changes for live preview
watch(() => form.data(), () => {
    updatePreview();
}, { deep: true });

// Download as PDF
const downloadPdf = () => {
    const formData = new URLSearchParams();
    formData.append('data', JSON.stringify(form.data()));
    window.location.href = `${templateDownload.url(props.template.id)}?${formData.toString()}`;
};

// Save as CV (requires auth)
const saveAsCv = () => {
    if (!props.user) {
        router.visit(login.url(), {
            data: { redirect: templateEditor.url(props.template.id) }
        });
        return;
    }
    
    form.post(templateSaveAsCv.url(props.template.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to CV dashboard or show success
        }
    });
};

const proficiencyLevels = [
    'Beginner',
    'Elementary',
    'Intermediate',
    'Upper Intermediate',
    'Advanced',
    'Native'
];
</script>

<template>
    <LandingLayout>
        <Head :title="'Edit ' + template.name + ' - CV Template'" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <Link 
                                :href="templateShow.url(template.id)"
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                <ArrowLeft class="w-5 h-5" />
                            </Link>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ template.name }}
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Fill in your details to create your CV
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Button 
                                @click="saveAsCv"
                                variant="outline"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ user ? 'Save as CV' : 'Login to Save' }}
                            </Button>
                            <Button 
                                @click="downloadPdf"
                                :disabled="form.processing"
                            >
                                <Download class="w-4 h-4 mr-2" />
                                Download PDF
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Left Column - Form -->
                    <div>
                        <Tabs v-model="activeTab" class="w-full">
                            <TabsList class="w-full grid grid-cols-4 lg:grid-cols-8 mb-6">
                                <TabsTrigger 
                                    v-if="enabledSections.includes('personal')" 
                                    value="personal"
                                    class="flex items-center gap-1"
                                >
                                    <User class="w-4 h-4" />
                                    <span class="hidden sm:inline">Personal</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('summary')" 
                                    value="summary"
                                    class="flex items-center gap-1"
                                >
                                    <FileText class="w-4 h-4" />
                                    <span class="hidden sm:inline">Summary</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('experience')" 
                                    value="experience"
                                    class="flex items-center gap-1"
                                >
                                    <Briefcase class="w-4 h-4" />
                                    <span class="hidden sm:inline">Experience</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('education')" 
                                    value="education"
                                    class="flex items-center gap-1"
                                >
                                    <GraduationCap class="w-4 h-4" />
                                    <span class="hidden sm:inline">Education</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('skills')" 
                                    value="skills"
                                    class="flex items-center gap-1"
                                >
                                    <Wrench class="w-4 h-4" />
                                    <span class="hidden sm:inline">Skills</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('certifications')" 
                                    value="certifications"
                                    class="flex items-center gap-1"
                                >
                                    <Award class="w-4 h-4" />
                                    <span class="hidden sm:inline">Certs</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('languages')" 
                                    value="languages"
                                    class="flex items-center gap-1"
                                >
                                    <Languages class="w-4 h-4" />
                                    <span class="hidden sm:inline">Languages</span>
                                </TabsTrigger>
                                <TabsTrigger 
                                    v-if="enabledSections.includes('interests')" 
                                    value="interests"
                                    class="flex items-center gap-1"
                                >
                                    <Heart class="w-4 h-4" />
                                    <span class="hidden sm:inline">Interests</span>
                                </TabsTrigger>
                            </TabsList>

                            <!-- Personal Info Tab -->
                            <TabsContent value="personal">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Personal Information</CardTitle>
                                        <CardDescription>Enter your basic contact details</CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="full_name">Full Name *</Label>
                                                <Input 
                                                    id="full_name" 
                                                    v-model="form.full_name" 
                                                    placeholder="John Doe"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="email">Email *</Label>
                                                <Input 
                                                    id="email" 
                                                    type="email"
                                                    v-model="form.email" 
                                                    placeholder="john@example.com"
                                                />
                                            </div>
                                        </div>
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="phone">Phone</Label>
                                                <Input 
                                                    id="phone" 
                                                    v-model="form.phone" 
                                                    placeholder="+1 (555) 123-4567"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="address">Location</Label>
                                                <Input 
                                                    id="address" 
                                                    v-model="form.address" 
                                                    placeholder="New York, NY"
                                                />
                                            </div>
                                        </div>
                                        <div class="grid sm:grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="linkedin">LinkedIn</Label>
                                                <Input 
                                                    id="linkedin" 
                                                    v-model="form.linkedin" 
                                                    placeholder="linkedin.com/in/johndoe"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="website">Website / Portfolio</Label>
                                                <Input 
                                                    id="website" 
                                                    v-model="form.website" 
                                                    placeholder="johndoe.com"
                                                />
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <!-- Summary Tab -->
                            <TabsContent value="summary">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Professional Summary</CardTitle>
                                        <CardDescription>Write a brief summary about yourself</CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <Textarea 
                                            v-model="form.summary"
                                            placeholder="Experienced professional with a passion for..."
                                            class="min-h-[200px]"
                                        />
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <!-- Experience Tab -->
                            <TabsContent value="experience">
                                <div class="space-y-4">
                                    <Card v-for="(exp, index) in form.experiences" :key="index">
                                        <CardHeader class="pb-4">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-lg">Experience {{ index + 1 }}</CardTitle>
                                                <Button 
                                                    v-if="form.experiences.length > 1"
                                                    variant="ghost" 
                                                    size="sm"
                                                    @click="removeExperience(index)"
                                                    class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Job Title *</Label>
                                                    <Input 
                                                        v-model="exp.job_title" 
                                                        placeholder="Software Engineer"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Company *</Label>
                                                    <Input 
                                                        v-model="exp.company" 
                                                        placeholder="Tech Company Inc."
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-3 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Location</Label>
                                                    <Input 
                                                        v-model="exp.location" 
                                                        placeholder="San Francisco, CA"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Start Date</Label>
                                                    <Input 
                                                        type="month"
                                                        v-model="exp.start_date" 
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>End Date</Label>
                                                    <Input 
                                                        type="month"
                                                        v-model="exp.end_date"
                                                        :disabled="exp.current"
                                                        :placeholder="exp.current ? 'Present' : ''"
                                                    />
                                                    <label class="flex items-center gap-2 text-sm">
                                                        <input 
                                                            type="checkbox" 
                                                            v-model="exp.current"
                                                            class="rounded"
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
                                    <Button @click="addExperience" variant="outline" class="w-full">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Experience
                                    </Button>
                                </div>
                            </TabsContent>

                            <!-- Education Tab -->
                            <TabsContent value="education">
                                <div class="space-y-4">
                                    <Card v-for="(edu, index) in form.education" :key="index">
                                        <CardHeader class="pb-4">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-lg">Education {{ index + 1 }}</CardTitle>
                                                <Button 
                                                    v-if="form.education.length > 1"
                                                    variant="ghost" 
                                                    size="sm"
                                                    @click="removeEducation(index)"
                                                    class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Degree *</Label>
                                                    <Input 
                                                        v-model="edu.degree" 
                                                        placeholder="Bachelor of Science in Computer Science"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>School / University *</Label>
                                                    <Input 
                                                        v-model="edu.school" 
                                                        placeholder="Stanford University"
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-4 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Location</Label>
                                                    <Input 
                                                        v-model="edu.location" 
                                                        placeholder="Palo Alto, CA"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Start Date</Label>
                                                    <Input 
                                                        type="month"
                                                        v-model="edu.start_date" 
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>End Date</Label>
                                                    <Input 
                                                        type="month"
                                                        v-model="edu.end_date" 
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>GPA</Label>
                                                    <Input 
                                                        v-model="edu.gpa" 
                                                        placeholder="3.8"
                                                    />
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
                                    <Button @click="addEducation" variant="outline" class="w-full">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Education
                                    </Button>
                                </div>
                            </TabsContent>

                            <!-- Skills Tab -->
                            <TabsContent value="skills">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Skills</CardTitle>
                                        <CardDescription>List your professional skills</CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div 
                                            v-for="(skill, index) in form.skills" 
                                            :key="index"
                                            class="flex items-center gap-2"
                                        >
                                            <Input 
                                                v-model="form.skills[index]" 
                                                placeholder="e.g., JavaScript, Project Management, Leadership"
                                                class="flex-1"
                                            />
                                            <Button 
                                                v-if="form.skills.length > 1"
                                                variant="ghost" 
                                                size="icon"
                                                @click="removeSkill(index)"
                                                class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <Button @click="addSkill" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-2" />
                                            Add Skill
                                        </Button>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <!-- Certifications Tab -->
                            <TabsContent value="certifications">
                                <div class="space-y-4">
                                    <Card v-for="(cert, index) in form.certifications" :key="index">
                                        <CardHeader class="pb-4">
                                            <div class="flex items-center justify-between">
                                                <CardTitle class="text-lg">Certification {{ index + 1 }}</CardTitle>
                                                <Button 
                                                    v-if="form.certifications.length > 1"
                                                    variant="ghost" 
                                                    size="sm"
                                                    @click="removeCertification(index)"
                                                    class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </CardHeader>
                                        <CardContent class="space-y-4">
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Certification Name *</Label>
                                                    <Input 
                                                        v-model="cert.name" 
                                                        placeholder="AWS Solutions Architect"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Issuing Organization</Label>
                                                    <Input 
                                                        v-model="cert.issuer" 
                                                        placeholder="Amazon Web Services"
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label>Date Obtained</Label>
                                                    <Input 
                                                        type="month"
                                                        v-model="cert.date" 
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Credential URL</Label>
                                                    <Input 
                                                        v-model="cert.url" 
                                                        placeholder="https://..."
                                                    />
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                    <Button @click="addCertification" variant="outline" class="w-full">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Add Certification
                                    </Button>
                                </div>
                            </TabsContent>

                            <!-- Languages Tab -->
                            <TabsContent value="languages">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Languages</CardTitle>
                                        <CardDescription>Languages you speak and your proficiency level</CardDescription>
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
                                            <select 
                                                v-model="lang.proficiency"
                                                class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            >
                                                <option 
                                                    v-for="level in proficiencyLevels" 
                                                    :key="level" 
                                                    :value="level"
                                                >
                                                    {{ level }}
                                                </option>
                                            </select>
                                            <Button 
                                                v-if="form.languages.length > 1"
                                                variant="ghost" 
                                                size="icon"
                                                @click="removeLanguage(index)"
                                                class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <Button @click="addLanguage" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-2" />
                                            Add Language
                                        </Button>
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <!-- Interests Tab -->
                            <TabsContent value="interests">
                                <Card>
                                    <CardHeader>
                                        <CardTitle>Interests & Hobbies</CardTitle>
                                        <CardDescription>Add your personal interests</CardDescription>
                                    </CardHeader>
                                    <CardContent class="space-y-4">
                                        <div 
                                            v-for="(interest, index) in form.interests" 
                                            :key="index"
                                            class="flex items-center gap-2"
                                        >
                                            <Input 
                                                v-model="form.interests[index]" 
                                                placeholder="e.g., Photography, Hiking, Open Source"
                                                class="flex-1"
                                            />
                                            <Button 
                                                v-if="form.interests.length > 1"
                                                variant="ghost" 
                                                size="icon"
                                                @click="removeInterest(index)"
                                                class="text-red-500 hover:text-red-600 hover:bg-red-50"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                        <Button @click="addInterest" variant="outline" size="sm">
                                            <Plus class="w-4 h-4 mr-2" />
                                            Add Interest
                                        </Button>
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>

                    <!-- Right Column - Live Preview -->
                    <div class="lg:sticky lg:top-24 lg:self-start">
                        <Card class="overflow-hidden">
                            <CardHeader class="bg-gray-50 dark:bg-gray-800 border-b">
                                <div class="flex items-center justify-between">
                                    <CardTitle class="flex items-center gap-2">
                                        <Eye class="w-5 h-5" />
                                        Live Preview
                                    </CardTitle>
                                    <div v-if="previewLoading" class="flex items-center gap-2 text-sm text-gray-500">
                                        <Loader2 class="w-4 h-4 animate-spin" />
                                        Updating...
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

                        <!-- Quick Actions -->
                        <div class="mt-4 flex gap-3">
                            <Button 
                                @click="downloadPdf"
                                class="flex-1"
                                :disabled="form.processing"
                            >
                                <Download class="w-4 h-4 mr-2" />
                                Download PDF
                            </Button>
                            <Button 
                                @click="saveAsCv"
                                variant="outline"
                                class="flex-1"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ user ? 'Save' : 'Login' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>
