<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Sparkles,
    Send,
    Loader2,
    FileText,
    Download,
    Save,
    Edit3,
    User,
    Bot,
    Paperclip,
    X,
    Check,
    ChevronDown,
    ChevronUp,
    Briefcase,
    GraduationCap,
    Code,
    Award,
    Languages,
    RefreshCcw,
    Eye,
    Wand2,
    MessageSquare,
} from 'lucide-vue-next';
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as cvsIndex } from '@/routes/cvs';
import { type BreadcrumbItem } from '@/types';

interface CvData {
    personal_info?: {
        full_name?: string;
        email?: string;
        phone?: string;
        location?: string;
        linkedin?: string;
        website?: string;
    };
    summary?: string;
    experience?: Array<{
        company: string;
        title: string;
        location?: string;
        start_date: string;
        end_date?: string;
        current?: boolean;
        description?: string;
    }>;
    education?: Array<{
        institution: string;
        degree: string;
        field?: string;
        start_date?: string;
        end_date?: string;
        gpa?: string;
    }>;
    skills?: string[];
    projects?: Array<{
        name: string;
        description?: string;
        url?: string;
        technologies?: string[];
    }>;
    certifications?: Array<{
        name: string;
        issuer?: string;
        date?: string;
        url?: string;
    }>;
    languages?: Array<{
        language: string;
        proficiency: string;
    }>;
}

interface ChatMessage {
    role: 'user' | 'assistant';
    content: string;
}

interface Props {
    templates: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'CVs', href: cvsIndex().url },
    { title: 'Generate with AI', href: '#' },
];

const { addToast } = useToast();

// State
const step = ref<'prompt' | 'generating' | 'preview' | 'editing'>('prompt');
const prompt = ref('');
const additionalContext = ref('');
const isGenerating = ref(false);
const isRefining = ref(false);
const isSaving = ref(false);
const cvData = ref<CvData | null>(null);
const cvName = ref('');
const selectedTemplate = ref('modern');
const isPrimary = ref(false);
const chatMessages = ref<ChatMessage[]>([]);
const chatInput = ref('');
const expandedSections = ref<Record<string, boolean>>({
    personal_info: true,
    summary: true,
    experience: true,
    education: true,
    skills: true,
    projects: false,
    certifications: false,
    languages: false,
});
const activeTab = ref<'preview' | 'chat'>('preview');
const messagesContainer = ref<HTMLElement | null>(null);

// File upload
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);

// Computed
const hasContent = computed(() => cvData.value !== null);
const canSave = computed(() => hasContent.value && cvName.value.trim() !== '');

// Template labels
const templateLabels: Record<string, string> = {
    modern: 'Modern',
    classic: 'Classic',
    minimal: 'Minimal',
    creative: 'Creative',
    executive: 'Executive',
};

// Methods
const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        const file = target.files[0];
        selectedFile.value = file;
        if (file.type.startsWith('image/')) {
            filePreview.value = URL.createObjectURL(file);
        } else {
            filePreview.value = null;
        }
    }
};

const clearFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const generateCv = async () => {
    if (!prompt.value.trim()) {
        addToast({ title: 'Error', message: 'Please enter a prompt describing your CV.', type: 'error' });
        return;
    }

    isGenerating.value = true;
    step.value = 'generating';

    try {
        const formData = new FormData();
        formData.append('prompt', prompt.value);
        if (additionalContext.value) {
            formData.append('context', additionalContext.value);
        }
        if (selectedFile.value) {
            formData.append('file', selectedFile.value);
        }

        const response = await fetch('/ai-cv-generator/generate', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success && data.cv_data) {
            cvData.value = data.cv_data;
            step.value = 'preview';
            
            // Auto-generate a name based on personal info or prompt
            if (data.cv_data.personal_info?.full_name) {
                cvName.value = `${data.cv_data.personal_info.full_name}'s CV`;
            } else {
                cvName.value = 'AI Generated CV';
            }

            addToast({ title: 'Success', message: 'CV generated successfully!', type: 'success' });
            
            // Initialize chat with system message
            chatMessages.value = [{
                role: 'assistant',
                content: 'Your CV has been generated! You can ask me to make changes like "Add more skills", "Rewrite the summary", or "Add a new job experience". What would you like to modify?'
            }];
        } else {
            step.value = 'prompt';
            addToast({ title: 'Error', message: data.message || 'Failed to generate CV.', type: 'error' });
        }
    } catch (error) {
        step.value = 'prompt';
        addToast({ title: 'Error', message: 'Network error. Please try again.', type: 'error' });
    } finally {
        isGenerating.value = false;
    }
};

const sendChatMessage = async () => {
    if (!chatInput.value.trim() || isRefining.value) return;

    const userMessage = chatInput.value.trim();
    chatMessages.value.push({ role: 'user', content: userMessage });
    chatInput.value = '';
    isRefining.value = true;
    scrollToBottom();

    try {
        const response = await fetch('/ai-cv-generator/refine', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                message: userMessage,
                cv_data: cvData.value,
                history: chatMessages.value.slice(0, -1), // Exclude the just-added user message
            }),
        });

        const data = await response.json();

        if (data.success) {
            chatMessages.value.push({ role: 'assistant', content: data.message });
            
            if (data.updated_cv) {
                cvData.value = data.updated_cv;
                addToast({ title: 'CV Updated', message: data.changes_summary || 'Changes applied.', type: 'success' });
            }
        } else {
            chatMessages.value.push({ role: 'assistant', content: data.message || 'Sorry, I could not process that request.' });
        }
    } catch (error) {
        chatMessages.value.push({ role: 'assistant', content: 'Network error. Please try again.' });
    } finally {
        isRefining.value = false;
        scrollToBottom();
    }
};

const saveCv = async () => {
    if (!canSave.value) return;

    isSaving.value = true;

    try {
        const response = await fetch('/ai-cv-generator/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                name: cvName.value,
                template: selectedTemplate.value,
                cv_data: cvData.value,
                is_primary: isPrimary.value,
            }),
        });

        const data = await response.json();

        if (data.success) {
            addToast({ title: 'Success', message: 'CV saved successfully!', type: 'success' });
            router.visit(data.redirect_url);
        } else {
            addToast({ title: 'Error', message: data.message || 'Failed to save CV.', type: 'error' });
        }
    } catch (error) {
        addToast({ title: 'Error', message: 'Network error. Please try again.', type: 'error' });
    } finally {
        isSaving.value = false;
    }
};

const regenerate = () => {
    step.value = 'prompt';
    cvData.value = null;
    chatMessages.value = [];
};

const toggleSection = (section: string) => {
    expandedSections.value[section] = !expandedSections.value[section];
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

// Example prompts
const examplePrompts = [
    'Create a CV for a senior software engineer with 8 years of experience in React and Node.js',
    'Generate a professional CV for a marketing manager transitioning to product management',
    'Build a CV for a fresh graduate in computer science looking for their first job',
    'Create a CV for a UX designer with experience at startups and enterprise companies',
];

const useExamplePrompt = (example: string) => {
    prompt.value = example;
};
</script>

<template>
    <Head title="Generate CV with AI" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col min-h-[calc(100vh-4rem)]">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 p-6 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="cvsIndex().url">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                            <Sparkles class="h-6 w-6 text-primary" />
                            Generate CV with AI
                        </h1>
                        <p class="text-muted-foreground">Describe your background and let AI create a professional CV</p>
                    </div>
                </div>
                
                <!-- Actions when CV is generated -->
                <div v-if="hasContent" class="flex items-center gap-3">
                    <Button variant="outline" @click="regenerate">
                        <RefreshCcw class="h-4 w-4 mr-2" />
                        Start Over
                    </Button>
                    <Button :disabled="!canSave || isSaving" @click="saveCv">
                        <Loader2 v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
                        <Save v-else class="h-4 w-4 mr-2" />
                        {{ isSaving ? 'Saving...' : 'Save CV' }}
                    </Button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 overflow-hidden">
                <!-- Step 1: Prompt Input -->
                <div v-if="step === 'prompt'" class="h-full flex items-center justify-center p-6">
                    <div class="w-full max-w-3xl space-y-8">
                        <div class="text-center space-y-4">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-primary/20 to-primary/5">
                                <Wand2 class="h-10 w-10 text-primary" />
                            </div>
                            <h2 class="text-3xl font-bold">What kind of CV would you like?</h2>
                            <p class="text-lg text-muted-foreground">
                                Describe your background, experience, and the type of role you're targeting.
                            </p>
                        </div>

                        <Card>
                            <CardContent class="pt-6 space-y-6">
                                <!-- Main Prompt -->
                                <div class="space-y-2">
                                    <Label for="prompt">Describe your CV</Label>
                                    <Textarea
                                        id="prompt"
                                        v-model="prompt"
                                        placeholder="E.g., I'm a software engineer with 5 years of experience in Python and JavaScript. I've worked at startups and I'm looking for a senior role at a tech company..."
                                        class="min-h-[150px] resize-none"
                                        @keydown.ctrl.enter="generateCv"
                                    />
                                </div>

                                <!-- Additional Context -->
                                <div class="space-y-2">
                                    <Label for="context">Additional Context (optional)</Label>
                                    <Textarea
                                        id="context"
                                        v-model="additionalContext"
                                        placeholder="Add any specific details: job descriptions you're targeting, specific achievements, technologies you want to highlight..."
                                        class="min-h-[100px] resize-none"
                                    />
                                </div>

                                <!-- File Upload -->
                                <div class="space-y-2">
                                    <Label>Upload Reference (optional)</Label>
                                    <div
                                        class="border-2 border-dashed rounded-lg p-6 text-center hover:border-primary/50 transition-colors cursor-pointer"
                                        @click="triggerFileInput"
                                    >
                                        <input
                                            ref="fileInput"
                                            type="file"
                                            accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg"
                                            class="hidden"
                                            @change="handleFileChange"
                                        />
                                        <div v-if="selectedFile" class="flex items-center justify-center gap-3">
                                            <FileText class="h-8 w-8 text-primary" />
                                            <div class="text-left">
                                                <p class="font-medium">{{ selectedFile.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ (selectedFile.size / 1024).toFixed(1) }} KB</p>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                @click.stop="clearFile"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                        <div v-else class="flex flex-col items-center gap-2">
                                            <Paperclip class="h-8 w-8 text-muted-foreground/50" />
                                            <p class="text-sm text-muted-foreground">
                                                Upload an existing CV, job description, or image to help AI understand your background
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Generate Button -->
                                <Button
                                    class="w-full h-12 text-lg"
                                    :disabled="!prompt.trim() || isGenerating"
                                    @click="generateCv"
                                >
                                    <Loader2 v-if="isGenerating" class="h-5 w-5 mr-2 animate-spin" />
                                    <Sparkles v-else class="h-5 w-5 mr-2" />
                                    {{ isGenerating ? 'Generating...' : 'Generate CV' }}
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- Example Prompts -->
                        <div class="space-y-3">
                            <p class="text-sm text-muted-foreground text-center">Or try an example:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <button
                                    v-for="example in examplePrompts"
                                    :key="example"
                                    class="p-3 text-left text-sm rounded-lg border hover:bg-muted/50 hover:border-primary/50 transition-colors"
                                    @click="useExamplePrompt(example)"
                                >
                                    {{ example }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Generating Animation -->
                <div v-else-if="step === 'generating'" class="h-full flex items-center justify-center p-6">
                    <div class="text-center space-y-6">
                        <div class="relative inline-flex">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary/30 to-primary/10 animate-pulse" />
                            <Sparkles class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-12 w-12 text-primary animate-bounce" />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Generating Your CV</h2>
                            <p class="text-muted-foreground mt-2">AI is crafting a professional CV based on your description...</p>
                        </div>
                        <div class="flex justify-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 0ms" />
                            <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 150ms" />
                            <div class="w-2 h-2 rounded-full bg-primary animate-bounce" style="animation-delay: 300ms" />
                        </div>
                    </div>
                </div>

                <!-- Step 3: Preview & Edit -->
                <div v-else-if="step === 'preview' && cvData" class="h-full flex overflow-hidden">
                    <!-- Left Panel: CV Preview -->
                    <div class="flex-1 overflow-y-auto p-6 border-r">
                        <div class="max-w-3xl mx-auto space-y-6">
                            <!-- Save Options -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Save Options</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="cv-name">CV Name *</Label>
                                            <Input
                                                id="cv-name"
                                                v-model="cvName"
                                                placeholder="Enter a name for your CV"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="template">Template</Label>
                                            <Select v-model="selectedTemplate">
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="template in templates" :key="template" :value="template">
                                                        {{ templateLabels[template] || template }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
                                        <div>
                                            <p class="font-medium">Set as Primary CV</p>
                                            <p class="text-sm text-muted-foreground">Use this CV by default</p>
                                        </div>
                                        <Switch v-model:checked="isPrimary" />
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Personal Info -->
                            <Card v-if="cvData.personal_info">
                                <CardHeader class="cursor-pointer" @click="toggleSection('personal_info')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <User class="h-5 w-5" />
                                            Personal Information
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.personal_info" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.personal_info">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm text-muted-foreground">Full Name</p>
                                            <p class="font-medium">{{ cvData.personal_info.full_name || '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-muted-foreground">Email</p>
                                            <p class="font-medium">{{ cvData.personal_info.email || '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-muted-foreground">Phone</p>
                                            <p class="font-medium">{{ cvData.personal_info.phone || '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-muted-foreground">Location</p>
                                            <p class="font-medium">{{ cvData.personal_info.location || '-' }}</p>
                                        </div>
                                        <div v-if="cvData.personal_info.linkedin">
                                            <p class="text-sm text-muted-foreground">LinkedIn</p>
                                            <p class="font-medium">{{ cvData.personal_info.linkedin }}</p>
                                        </div>
                                        <div v-if="cvData.personal_info.website">
                                            <p class="text-sm text-muted-foreground">Website</p>
                                            <p class="font-medium">{{ cvData.personal_info.website }}</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Summary -->
                            <Card v-if="cvData.summary">
                                <CardHeader class="cursor-pointer" @click="toggleSection('summary')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <FileText class="h-5 w-5" />
                                            Professional Summary
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.summary" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.summary">
                                    <p class="text-muted-foreground leading-relaxed">{{ cvData.summary }}</p>
                                </CardContent>
                            </Card>

                            <!-- Experience -->
                            <Card v-if="cvData.experience?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('experience')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <Briefcase class="h-5 w-5" />
                                            Work Experience
                                            <Badge variant="secondary">{{ cvData.experience.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.experience" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.experience" class="space-y-6">
                                    <div v-for="(exp, index) in cvData.experience" :key="index" class="relative pl-4 border-l-2 border-primary/30">
                                        <div class="absolute -left-1.5 top-0 w-3 h-3 rounded-full bg-primary" />
                                        <h4 class="font-semibold">{{ exp.title }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ exp.company }}
                                            <span v-if="exp.location"> • {{ exp.location }}</span>
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            {{ formatDate(exp.start_date) }} - {{ exp.current ? 'Present' : formatDate(exp.end_date) }}
                                        </p>
                                        <p v-if="exp.description" class="mt-2 text-sm whitespace-pre-line">{{ exp.description }}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Education -->
                            <Card v-if="cvData.education?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('education')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <GraduationCap class="h-5 w-5" />
                                            Education
                                            <Badge variant="secondary">{{ cvData.education.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.education" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.education" class="space-y-4">
                                    <div v-for="(edu, index) in cvData.education" :key="index">
                                        <h4 class="font-semibold">{{ edu.degree }} {{ edu.field ? `in ${edu.field}` : '' }}</h4>
                                        <p class="text-sm text-muted-foreground">{{ edu.institution }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formatDate(edu.start_date) }} - {{ formatDate(edu.end_date) }}
                                            <span v-if="edu.gpa"> • GPA: {{ edu.gpa }}</span>
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Skills -->
                            <Card v-if="cvData.skills?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('skills')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <Code class="h-5 w-5" />
                                            Skills
                                            <Badge variant="secondary">{{ cvData.skills.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.skills" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.skills">
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-for="skill in cvData.skills" :key="skill" variant="secondary">
                                            {{ skill }}
                                        </Badge>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Projects -->
                            <Card v-if="cvData.projects?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('projects')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <Code class="h-5 w-5" />
                                            Projects
                                            <Badge variant="secondary">{{ cvData.projects.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.projects" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.projects" class="space-y-4">
                                    <div v-for="(project, index) in cvData.projects" :key="index">
                                        <h4 class="font-semibold">{{ project.name }}</h4>
                                        <p v-if="project.description" class="text-sm text-muted-foreground">{{ project.description }}</p>
                                        <div v-if="project.technologies?.length" class="flex flex-wrap gap-1 mt-2">
                                            <Badge v-for="tech in project.technologies" :key="tech" variant="outline" class="text-xs">
                                                {{ tech }}
                                            </Badge>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Certifications -->
                            <Card v-if="cvData.certifications?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('certifications')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <Award class="h-5 w-5" />
                                            Certifications
                                            <Badge variant="secondary">{{ cvData.certifications.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.certifications" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.certifications" class="space-y-3">
                                    <div v-for="(cert, index) in cvData.certifications" :key="index">
                                        <h4 class="font-semibold">{{ cert.name }}</h4>
                                        <p class="text-sm text-muted-foreground">
                                            {{ cert.issuer }}
                                            <span v-if="cert.date"> • {{ formatDate(cert.date) }}</span>
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Languages -->
                            <Card v-if="cvData.languages?.length">
                                <CardHeader class="cursor-pointer" @click="toggleSection('languages')">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="flex items-center gap-2">
                                            <Languages class="h-5 w-5" />
                                            Languages
                                            <Badge variant="secondary">{{ cvData.languages.length }}</Badge>
                                        </CardTitle>
                                        <ChevronDown v-if="!expandedSections.languages" class="h-5 w-5" />
                                        <ChevronUp v-else class="h-5 w-5" />
                                    </div>
                                </CardHeader>
                                <CardContent v-if="expandedSections.languages">
                                    <div class="flex flex-wrap gap-4">
                                        <div v-for="(lang, index) in cvData.languages" :key="index">
                                            <span class="font-medium">{{ lang.language }}</span>
                                            <span class="text-muted-foreground"> - {{ lang.proficiency }}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Right Panel: AI Chat for Refinement -->
                    <div class="w-96 flex flex-col bg-muted/30">
                        <div class="p-4 border-b bg-background">
                            <h3 class="font-semibold flex items-center gap-2">
                                <MessageSquare class="h-4 w-4 text-primary" />
                                Refine with AI
                            </h3>
                            <p class="text-xs text-muted-foreground mt-1">Ask AI to make changes to your CV</p>
                        </div>

                        <!-- Chat Messages -->
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                            <div
                                v-for="(msg, index) in chatMessages"
                                :key="index"
                                class="flex gap-3"
                                :class="msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'"
                            >
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                    :class="msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-background border'"
                                >
                                    <User v-if="msg.role === 'user'" class="h-4 w-4" />
                                    <Bot v-else class="h-4 w-4 text-primary" />
                                </div>
                                <div
                                    class="max-w-[80%] rounded-lg p-3 text-sm"
                                    :class="msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-background border'"
                                >
                                    {{ msg.content }}
                                </div>
                            </div>

                            <!-- Loading indicator -->
                            <div v-if="isRefining" class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-background border flex items-center justify-center">
                                    <Bot class="h-4 w-4 text-primary" />
                                </div>
                                <div class="bg-background border rounded-lg p-3">
                                    <div class="flex gap-1">
                                        <div class="w-2 h-2 rounded-full bg-primary/50 animate-bounce" style="animation-delay: 0ms" />
                                        <div class="w-2 h-2 rounded-full bg-primary/50 animate-bounce" style="animation-delay: 150ms" />
                                        <div class="w-2 h-2 rounded-full bg-primary/50 animate-bounce" style="animation-delay: 300ms" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-4 border-t bg-background">
                            <form @submit.prevent="sendChatMessage" class="flex gap-2">
                                <Input
                                    v-model="chatInput"
                                    placeholder="Ask AI to modify your CV..."
                                    :disabled="isRefining"
                                    class="flex-1"
                                />
                                <Button type="submit" size="icon" :disabled="!chatInput.trim() || isRefining">
                                    <Loader2 v-if="isRefining" class="h-4 w-4 animate-spin" />
                                    <Send v-else class="h-4 w-4" />
                                </Button>
                            </form>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <button
                                    class="text-xs px-2 py-1 rounded bg-muted hover:bg-muted/80 transition-colors"
                                    @click="chatInput = 'Add more technical skills'"
                                >
                                    Add skills
                                </button>
                                <button
                                    class="text-xs px-2 py-1 rounded bg-muted hover:bg-muted/80 transition-colors"
                                    @click="chatInput = 'Rewrite the summary to be more impactful'"
                                >
                                    Improve summary
                                </button>
                                <button
                                    class="text-xs px-2 py-1 rounded bg-muted hover:bg-muted/80 transition-colors"
                                    @click="chatInput = 'Add quantifiable achievements'"
                                >
                                    Add metrics
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
