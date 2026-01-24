<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index as jobsIndex, show as jobShow, update as jobUpdate } from '@/routes/jobs';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, X, Save, Loader2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Company {
    id: number;
    name: string;
}

interface Cv {
    id: number;
    name: string;
}

interface CoverLetter {
    id: number;
    name: string;
}

interface JobApplication {
    id: number;
    title: string;
    status: string;
    company?: Company | null;
    cv_id?: number | null;
    cover_letter_id?: number | null;
    description?: string | null;
    requirements?: string[] | null;
    skills?: string[] | null;
    salary_range?: string | null;
    location?: string | null;
    work_type?: string | null;
    experience_level?: string | null;
    source_url?: string | null;
    applied_at?: string | null;
    deadline?: string | null;
    notes?: string | null;
}

interface Props {
    application: JobApplication;
    cvs: Cv[];
    coverLetters: CoverLetter[];
    statuses: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Job Applications', href: jobsIndex().url },
    { title: props.application.title, href: `/jobs/${props.application.id}` },
    { title: 'Edit', href: '#' },
];

const form = useForm({
    title: props.application.title,
    company_name: props.application.company?.name ?? '',
    location: props.application.location ?? '',
    work_type: props.application.work_type ?? '',
    salary_range: props.application.salary_range ?? '',
    experience_level: props.application.experience_level ?? '',
    description: props.application.description ?? '',
    requirements: props.application.requirements ?? [],
    skills: props.application.skills ?? [],
    status: props.application.status,
    source_url: props.application.source_url ?? '',
    deadline: props.application.deadline?.split('T')[0] ?? '',
    applied_at: props.application.applied_at?.split('T')[0] ?? '',
    cv_id: props.application.cv_id?.toString() ?? '',
    cover_letter_id: props.application.cover_letter_id?.toString() ?? '',
    notes: props.application.notes ?? '',
});

const newSkill = ref('');
const newRequirement = ref('');

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

const addRequirement = () => {
    const req = newRequirement.value.trim();
    if (req && !form.requirements.includes(req)) {
        form.requirements.push(req);
        newRequirement.value = '';
    }
};

const removeRequirement = (index: number) => {
    form.requirements.splice(index, 1);
};

const workTypes = [
    { value: 'onsite', label: 'On-site' },
    { value: 'remote', label: 'Remote' },
    { value: 'hybrid', label: 'Hybrid' },
];

const experienceLevels = [
    { value: 'entry', label: 'Entry Level' },
    { value: 'mid', label: 'Mid Level' },
    { value: 'senior', label: 'Senior' },
    { value: 'lead', label: 'Lead / Manager' },
    { value: 'executive', label: 'Executive' },
];

const statusLabels: Record<string, string> = {
    saved: 'Saved',
    applied: 'Applied',
    interviewing: 'Interviewing',
    offered: 'Offered',
    rejected: 'Rejected',
    withdrawn: 'Withdrawn',
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        cv_id: data.cv_id && data.cv_id !== 'none' ? parseInt(data.cv_id) : null,
        cover_letter_id: data.cover_letter_id && data.cover_letter_id !== 'none' ? parseInt(data.cover_letter_id) : null,
        deadline: data.deadline || null,
        applied_at: data.applied_at || null,
    })).put(`/jobs/${props.application.id}`);
};
</script>

<template>
    <Head :title="`Edit - ${application.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="`/jobs/${application.id}`">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Job Application</h1>
                    <p class="text-muted-foreground">Update the details for this job application</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                        <CardDescription>The core details about this position</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="title">Job Title *</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="e.g. Senior Software Engineer"
                                    :class="{ 'border-red-500': form.errors.title }"
                                />
                                <p v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="company">Company *</Label>
                                <Input
                                    id="company"
                                    v-model="form.company_name"
                                    placeholder="e.g. Google"
                                    :class="{ 'border-red-500': form.errors.company_name }"
                                />
                                <p v-if="form.errors.company_name" class="text-sm text-red-500">{{ form.errors.company_name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                        <SelectItem v-for="type in workTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="salary">Salary Range</Label>
                                <Input
                                    id="salary"
                                    v-model="form.salary_range"
                                    placeholder="e.g. $100,000 - $150,000"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="experience">Experience Level</Label>
                                <Select v-model="form.experience_level">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select level" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="level in experienceLevels" :key="level.value" :value="level.value">
                                            {{ level.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="source_url">Job Posting URL</Label>
                            <Input
                                id="source_url"
                                v-model="form.source_url"
                                type="url"
                                placeholder="https://..."
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Paste or enter the job description..."
                                rows="6"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Skills -->
                <Card>
                    <CardHeader>
                        <CardTitle>Required Skills</CardTitle>
                        <CardDescription>Key skills mentioned in the job posting</CardDescription>
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

                <!-- Requirements -->
                <Card>
                    <CardHeader>
                        <CardTitle>Requirements</CardTitle>
                        <CardDescription>Qualifications and requirements for the position</CardDescription>
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
                        <div v-if="form.requirements.length > 0" class="space-y-2">
                            <div
                                v-for="(req, index) in form.requirements"
                                :key="index"
                                class="flex items-center gap-2 p-3 rounded-lg bg-muted/50"
                            >
                                <span class="flex-1 text-sm">{{ req }}</span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeRequirement(index)"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Application Status -->
                <Card>
                    <CardHeader>
                        <CardTitle>Application Status</CardTitle>
                        <CardDescription>Track the progress of your application</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="status in statuses" :key="status" :value="status">
                                            {{ statusLabels[status] }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="applied_at">Applied Date</Label>
                                <Input
                                    id="applied_at"
                                    v-model="form.applied_at"
                                    type="date"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="deadline">Deadline</Label>
                                <Input
                                    id="deadline"
                                    v-model="form.deadline"
                                    type="date"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Documents -->
                <Card>
                    <CardHeader>
                        <CardTitle>Documents</CardTitle>
                        <CardDescription>Attach your CV and cover letter</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="cv">CV / Resume</Label>
                                <Select v-model="form.cv_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a CV" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">None</SelectItem>
                                        <SelectItem v-for="cv in cvs" :key="cv.id" :value="cv.id.toString()">
                                            {{ cv.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="cover_letter">Cover Letter</Label>
                                <Select v-model="form.cover_letter_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select a cover letter" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">None</SelectItem>
                                        <SelectItem v-for="letter in coverLetters" :key="letter.id" :value="letter.id.toString()">
                                            {{ letter.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                        <CardDescription>Any additional notes about this application</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Textarea
                            v-model="form.notes"
                            placeholder="Interview dates, contacts, reminders..."
                            rows="4"
                        />
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="`/jobs/${application.id}`">Cancel</Link>
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
