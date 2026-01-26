<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    ArrowLeft,
    Link as LinkIcon,
    Loader2,
    Sparkles,
    Building2,
    MapPin,
    Briefcase,
    DollarSign,
    GraduationCap,
    X,
    Plus,
    Wand2,
    CheckCircle,
    AlertCircle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as jobsIndex, store as jobStore, crawl as jobCrawl } from '@/routes/jobs';
import { type BreadcrumbItem } from '@/types';

interface Cv {
    id: number;
    name: string;
}

interface Props {
    cvs: Cv[];
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Job Applications', href: jobsIndex().url },
    { title: 'Track New Job', href: '#' },
];

// Tab state
const activeTab = ref<'url' | 'manual'>('url');

// URL crawl state
const urlInput = ref('');
const isCrawling = ref(false);
const crawlError = ref('');
const crawledData = ref<any>(null);

// Form state
const form = useForm({
    title: '',
    company_name: '',
    description: '',
    requirements: [] as string[],
    skills: [] as string[],
    salary_range: '',
    location: '',
    work_type: '',
    experience_level: '',
    source_url: '',
    status: 'saved',
    deadline: '',
    notes: '',
    cv_id: '',
});

const { addToast } = useToast();

// Skill input
const newSkill = ref('');
const newRequirement = ref('');

const addSkill = () => {
    if (newSkill.value.trim() && !form.skills.includes(newSkill.value.trim())) {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index: number) => {
    form.skills.splice(index, 1);
};

const addRequirement = () => {
    if (newRequirement.value.trim() && !form.requirements.includes(newRequirement.value.trim())) {
        form.requirements.push(newRequirement.value.trim());
        newRequirement.value = '';
    }
};

const removeRequirement = (index: number) => {
    form.requirements.splice(index, 1);
};

// Crawl job URL
const crawlJobUrl = async () => {
    if (!urlInput.value) return;
    
    isCrawling.value = true;
    crawlError.value = '';
    crawledData.value = null;

    try {
        const response = await fetch(jobCrawl().url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ url: urlInput.value }),
        });

        const data = await response.json();

        if (data.success) {
            crawledData.value = data.data;
            // Populate form with crawled data
            form.title = data.data.title || '';
            form.company_name = data.data.company || '';
            form.description = data.data.description || '';
            form.requirements = data.data.requirements || [];
            form.skills = data.data.skills || [];
            form.salary_range = data.data.salary_range || '';
            form.location = data.data.location || '';
            form.work_type = data.data.work_type || '';
            form.experience_level = data.data.experience_level || '';
            form.source_url = urlInput.value;
            activeTab.value = 'manual'; // Switch to manual to show/edit the data
            addToast({
                title: 'Success',
                message: 'Job details extracted successfully!',
                type: 'success'
            });
        } else {
            crawlError.value = data.message || 'Failed to extract job information';
            addToast({
                title: 'Error',
                message: crawlError.value,
                type: 'error'
            });
        }
    } catch (error) {
        crawlError.value = 'Failed to crawl job posting. Please enter details manually.';
        addToast({
            title: 'Error',
            message: 'Failed to crawl job posting.',
            type: 'error'
        });
    } finally {
        isCrawling.value = false;
    }
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        cv_id: data.cv_id && data.cv_id !== 'none' ? parseInt(data.cv_id) : null,
        deadline: data.deadline || null,
    })).post(jobStore().url);
};

const statusLabels: Record<string, string> = {
    saved: 'Saved',
    applied: 'Applied',
    interviewing: 'Interviewing',
    offered: 'Offered',
    rejected: 'Rejected',
    withdrawn: 'Withdrawn',
};
</script>

<template>
    <Head title="Track New Job" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="jobsIndex().url">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Track New Job</h1>
                    <p class="text-muted-foreground mt-1">
                        Add a job application to track your progress.
                    </p>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex gap-2 p-1 bg-muted rounded-lg w-fit">
                <button
                    @click="activeTab = 'url'"
                    class="px-4 py-2 rounded-md text-sm font-medium transition-all"
                    :class="activeTab === 'url' 
                        ? 'bg-background shadow text-foreground' 
                        : 'text-muted-foreground hover:text-foreground'"
                >
                    <LinkIcon class="h-4 w-4 inline mr-2" />
                    Import from URL
                </button>
                <button
                    @click="activeTab = 'manual'"
                    class="px-4 py-2 rounded-md text-sm font-medium transition-all"
                    :class="activeTab === 'manual' 
                        ? 'bg-background shadow text-foreground' 
                        : 'text-muted-foreground hover:text-foreground'"
                >
                    <Briefcase class="h-4 w-4 inline mr-2" />
                    Enter Manually
                </button>
            </div>

            <!-- URL Import -->
            <Card v-if="activeTab === 'url'" class="border-dashed">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Sparkles class="h-5 w-5 text-primary" />
                        AI-Powered Import
                    </CardTitle>
                    <CardDescription>
                        Paste a job posting URL and our AI will automatically extract all the details.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex gap-3">
                        <Input
                            v-model="urlInput"
                            placeholder="https://example.com/jobs/software-engineer"
                            class="flex-1"
                            @keyup.enter="crawlJobUrl"
                        />
                        <Button 
                            @click="crawlJobUrl" 
                            :disabled="!urlInput || isCrawling"
                            class="min-w-[140px]"
                        >
                            <Loader2 v-if="isCrawling" class="h-4 w-4 mr-2 animate-spin" />
                            <Wand2 v-else class="h-4 w-4 mr-2" />
                            {{ isCrawling ? 'Analyzing...' : 'Extract Details' }}
                        </Button>
                    </div>

                    <!-- Crawl Status -->
                    <div v-if="isCrawling" class="p-4 rounded-lg bg-primary/5 border border-primary/20">
                        <div class="flex items-center gap-3">
                            <div class="animate-pulse">
                                <Sparkles class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium">AI is analyzing the job posting...</p>
                                <p class="text-sm text-muted-foreground">This may take a few seconds.</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2">
                            <Skeleton class="h-4 w-full" />
                            <Skeleton class="h-4 w-4/5" />
                            <Skeleton class="h-4 w-3/5" />
                        </div>
                    </div>

                    <!-- Error -->
                    <div v-if="crawlError" class="p-4 rounded-lg bg-destructive/10 border border-destructive/20">
                        <div class="flex items-start gap-3">
                            <AlertCircle class="h-5 w-5 text-destructive shrink-0 mt-0.5" />
                            <div>
                                <p class="font-medium text-destructive">{{ crawlError }}</p>
                                <p class="text-sm text-muted-foreground mt-1">
                                    Try a different URL or switch to manual entry.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Success -->
                    <div v-if="crawledData" class="p-4 rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900">
                        <div class="flex items-start gap-3">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" />
                            <div>
                                <p class="font-medium text-green-800 dark:text-green-200">Job details extracted successfully!</p>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    Review and edit the information below, then save the application.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground">
                        Supported: LinkedIn, Indeed, Glassdoor, company career pages, and more.
                    </p>
                </CardContent>
            </Card>

            <!-- Manual Entry Form -->
            <form v-if="activeTab === 'manual' || crawledData" @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Job Information</CardTitle>
                        <CardDescription>Enter the basic details about the position.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="title">Job Title *</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="e.g. Senior Software Engineer"
                                    required
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="company">Company Name</Label>
                                <Input
                                    id="company"
                                    v-model="form.company_name"
                                    placeholder="e.g. Google"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="location">Location</Label>
                                <Input
                                    id="location"
                                    v-model="form.location"
                                    placeholder="e.g. San Francisco, CA"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="work_type">Work Type</Label>
                                <Select v-model="form.work_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select work type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="remote">Remote</SelectItem>
                                        <SelectItem value="hybrid">Hybrid</SelectItem>
                                        <SelectItem value="onsite">Onsite</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="salary">Salary Range</Label>
                                <Input
                                    id="salary"
                                    v-model="form.salary_range"
                                    placeholder="e.g. $120k - $150k"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="experience">Experience Level</Label>
                                <Select v-model="form.experience_level">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select level" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="entry">Entry Level</SelectItem>
                                        <SelectItem value="mid">Mid Level</SelectItem>
                                        <SelectItem value="senior">Senior Level</SelectItem>
                                        <SelectItem value="lead">Lead / Principal</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Job Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Paste or type the job description..."
                                rows="5"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="source_url">Source URL</Label>
                            <Input
                                id="source_url"
                                v-model="form.source_url"
                                placeholder="https://..."
                                type="url"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Skills -->
                <Card>
                    <CardHeader>
                        <CardTitle>Required Skills</CardTitle>
                        <CardDescription>Add the skills mentioned in the job posting.</CardDescription>
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
                            <Badge
                                v-for="(skill, index) in form.skills"
                                :key="skill"
                                variant="secondary"
                                class="pr-1"
                            >
                                {{ skill }}
                                <button
                                    type="button"
                                    @click="removeSkill(index)"
                                    class="ml-1 rounded-full hover:bg-destructive/20 p-0.5"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <!-- Requirements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Requirements</CardTitle>
                        <CardDescription>Add key requirements from the job posting.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex gap-2">
                            <Input
                                v-model="newRequirement"
                                placeholder="Add a requirement..."
                                @keyup.enter.prevent="addRequirement"
                            />
                            <Button type="button" variant="outline" @click="addRequirement">
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>
                        <ul v-if="form.requirements.length > 0" class="space-y-2">
                            <li
                                v-for="(req, index) in form.requirements"
                                :key="index"
                                class="flex items-start gap-2 p-2 rounded-lg bg-muted/50"
                            >
                                <span class="flex-1 text-sm">{{ req }}</span>
                                <button
                                    type="button"
                                    @click="removeRequirement(index)"
                                    class="text-muted-foreground hover:text-destructive"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Application Status -->
                <Card>
                    <CardHeader>
                        <CardTitle>Application Status</CardTitle>
                        <CardDescription>Track the current status of your application.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="status in statuses" :key="status" :value="status">
                                            {{ statusLabels[status] || status }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="deadline">Application Deadline</Label>
                                <Input
                                    id="deadline"
                                    v-model="form.deadline"
                                    type="date"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="cv">Attach CV</Label>
                                <Select v-model="form.cv_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select CV" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">None</SelectItem>
                                        <SelectItem v-for="cv in cvs" :key="cv.id" :value="String(cv.id)">
                                            {{ cv.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notes</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Any personal notes about this application..."
                                rows="3"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child>
                        <Link :href="jobsIndex().url">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Save Application
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
