<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Mail,
    User,
    Pencil,
    Trash2,
    Copy,
    Sparkles,
    Clock,
    Briefcase,
    MoreHorizontal,
    Loader2,
    CheckCircle2,
    AlertCircle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface CoverLetter {
    id: number;
    name: string;
    content: string;
    tone: string;
    ai_improvements: {
        original_content?: string;
        improved_content?: string;
        suggestions?: string[];
        generated_at?: string;
    } | null;
    job_application: {
        id: number;
        title: string;
        company_name: string | null;
    } | null;
    created_at: string;
    updated_at: string;
}

interface UserType {
    id: number;
    name: string;
    email: string;
}

interface Props {
    coverLetter: CoverLetter;
    user: UserType;
    tones: Record<string, string>;
}

const props = defineProps<Props>();
const { toast } = useToast();

const showDeleteDialog = ref(false);
const showImproveDialog = ref(false);
const improvementFeedback = ref('');
const loadingImprovement = ref(false);
const improvementResult = ref<{
    original_content?: string;
    improved_content?: string;
    suggestions?: string[];
} | null>(null);

const deleteForm = useForm({});

const deleteCoverLetter = () => {
    deleteForm.delete(`/admin/cover-letters/${props.coverLetter.id}`, {
        onSuccess: () => {
            toast({
                title: 'Cover Letter Deleted',
                description: 'The cover letter has been deleted successfully.',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Failed to delete the cover letter.',
                variant: 'destructive',
            });
        },
    });
};

const duplicateCoverLetter = () => {
    router.post(`/admin/cover-letters/${props.coverLetter.id}/duplicate`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Cover Letter Duplicated',
                description: 'The cover letter has been duplicated successfully.',
            });
        },
    });
};

const generateImprovement = async () => {
    loadingImprovement.value = true;
    improvementResult.value = null;

    try {
        const response = await fetch(`/admin/cover-letters/${props.coverLetter.id}/improve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                feedback: improvementFeedback.value,
            }),
        });

        const data = await response.json();

        if (data.success) {
            improvementResult.value = data.result;
            toast({
                title: 'Improvements Generated',
                description: 'AI has generated improvement suggestions.',
            });
        } else {
            toast({
                title: 'Error',
                description: data.message || 'Failed to generate improvements.',
                variant: 'destructive',
            });
        }
    } catch {
        toast({
            title: 'Error',
            description: 'Failed to generate improvements. Please try again.',
            variant: 'destructive',
        });
    } finally {
        loadingImprovement.value = false;
    }
};

const getToneBadgeVariant = (tone: string) => {
    const variants: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        professional: 'default',
        enthusiastic: 'secondary',
        confident: 'default',
        conversational: 'outline',
        formal: 'secondary',
    };
    return variants[tone] || 'outline';
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '/admin' },
        { title: 'Cover Letters', href: '/admin/cover-letters' },
        { title: coverLetter.name }
    ]">
        <Head :title="`Cover Letter: ${coverLetter.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/admin/cover-letters">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold">{{ coverLetter.name }}</h1>
                            <Badge :variant="getToneBadgeVariant(coverLetter.tone)" class="capitalize">
                                {{ coverLetter.tone }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">
                            Created for {{ user.name }} on {{ coverLetter.created_at }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="showImproveDialog = true">
                        <Sparkles class="mr-2 h-4 w-4" />
                        AI Improve
                    </Button>
                    <Button as-child>
                        <Link :href="`/admin/cover-letters/${coverLetter.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="duplicateCoverLetter">
                                <Copy class="mr-2 h-4 w-4" />
                                Duplicate
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem 
                                class="text-destructive focus:text-destructive"
                                @click="showDeleteDialog = true"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Cover Letter Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Mail class="h-5 w-5" />
                                Cover Letter Content
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div 
                                class="prose prose-sm max-w-none dark:prose-invert" 
                                v-html="coverLetter.content"
                            />
                        </CardContent>
                    </Card>

                    <!-- AI Improvements (if available) -->
                    <Card v-if="coverLetter.ai_improvements">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Sparkles class="h-5 w-5 text-primary" />
                                AI Improvements
                            </CardTitle>
                            <CardDescription>
                                Generated at {{ coverLetter.ai_improvements.generated_at }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="coverLetter.ai_improvements.suggestions?.length">
                                <h4 class="font-medium mb-2">Suggestions</h4>
                                <ul class="space-y-2">
                                    <li 
                                        v-for="(suggestion, index) in coverLetter.ai_improvements.suggestions" 
                                        :key="index"
                                        class="flex items-start gap-2 text-sm"
                                    >
                                        <CheckCircle2 class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                                        {{ suggestion }}
                                    </li>
                                </ul>
                            </div>
                            <div v-if="coverLetter.ai_improvements.improved_content">
                                <h4 class="font-medium mb-2">Improved Version</h4>
                                <div 
                                    class="prose prose-sm max-w-none dark:prose-invert p-4 rounded-lg bg-muted/50" 
                                    v-html="coverLetter.ai_improvements.improved_content"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- User Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                User
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Link 
                                :href="`/admin/users/${user.id}`"
                                class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 hover:bg-muted transition-colors"
                            >
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                    <User class="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ user.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Job Application -->
                    <Card v-if="coverLetter.job_application">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Briefcase class="h-5 w-5" />
                                Linked Job Application
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="p-3 rounded-lg bg-muted/50">
                                <p class="font-medium">{{ coverLetter.job_application.title }}</p>
                                <p v-if="coverLetter.job_application.company_name" class="text-sm text-muted-foreground">
                                    at {{ coverLetter.job_application.company_name }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Tone</span>
                                <Badge :variant="getToneBadgeVariant(coverLetter.tone)" class="capitalize">
                                    {{ coverLetter.tone }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Created</span>
                                <span>{{ coverLetter.created_at }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">Last Updated</span>
                                <span>{{ coverLetter.updated_at }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Delete Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Cover Letter</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete "{{ coverLetter.name }}"? 
                        This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        variant="destructive" 
                        :disabled="deleteForm.processing"
                        @click="deleteCoverLetter"
                    >
                        {{ deleteForm.processing ? 'Deleting...' : 'Delete' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- AI Improve Dialog -->
        <Dialog v-model:open="showImproveDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Sparkles class="h-5 w-5 text-primary" />
                        AI Cover Letter Improvement
                    </DialogTitle>
                    <DialogDescription>
                        Get AI-powered suggestions to improve this cover letter
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="feedback">Additional Feedback (Optional)</Label>
                        <Textarea 
                            id="feedback"
                            v-model="improvementFeedback"
                            placeholder="e.g., Make it more confident, add more technical details, shorten the introduction..."
                            rows="3"
                        />
                        <p class="text-xs text-muted-foreground">
                            Provide specific areas you'd like the AI to focus on
                        </p>
                    </div>

                    <!-- Results -->
                    <div v-if="improvementResult" class="space-y-4 pt-4 border-t">
                        <div v-if="improvementResult.suggestions?.length" class="space-y-2">
                            <h4 class="font-medium">Suggestions</h4>
                            <ul class="space-y-2">
                                <li 
                                    v-for="(suggestion, index) in improvementResult.suggestions" 
                                    :key="index"
                                    class="flex items-start gap-2 text-sm"
                                >
                                    <CheckCircle2 class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                                    {{ suggestion }}
                                </li>
                            </ul>
                        </div>
                        <div v-if="improvementResult.improved_content" class="space-y-2">
                            <h4 class="font-medium">Improved Version</h4>
                            <div 
                                class="prose prose-sm max-w-none dark:prose-invert p-4 rounded-lg bg-muted/50 max-h-64 overflow-y-auto" 
                                v-html="improvementResult.improved_content"
                            />
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showImproveDialog = false">
                        Close
                    </Button>
                    <Button 
                        :disabled="loadingImprovement"
                        @click="generateImprovement"
                    >
                        <template v-if="loadingImprovement">
                            <Loader2 class="mr-2 h-4 w-4 animate-spin" />
                            Analyzing...
                        </template>
                        <template v-else>
                            <Sparkles class="mr-2 h-4 w-4" />
                            {{ improvementResult ? 'Regenerate' : 'Generate Improvements' }}
                        </template>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
