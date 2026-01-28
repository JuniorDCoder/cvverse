<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    MessageSquare,
    Send,
    User,
    Mail,
    Phone,
    MapPin,
    Linkedin,
    Globe,
    Briefcase,
    GraduationCap,
    Code,
    Languages,
    Clock,
    Shield,
    FileText,
    History,
    Save,
    Loader2,
    Check
} from 'lucide-vue-next';
import { marked } from 'marked';
import { ref, computed } from 'vue';
import ToastContainer from '@/components/ToastContainer.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from '@/composables/useToast';

const page = usePage();
const auth = computed(() => page.props.auth);
const { addToast } = useToast();

interface Comment {
    id: number;
    content: string;
    section: string | null;
    guest_name: string | null;
    created_at: string;
    user?: {
        name: string;
    } | null;
}

interface Cv {
    id: number;
    name: string;
    template: string;
    summary?: string | null;
    personal_info?: any;
    experience?: any[];
    education?: any[];
    skills?: string[];
    languages?: any[];
    projects?: any[];
    certifications?: any[];
    comments: Comment[];
}

interface Share {
    id: string;
    permission: 'view' | 'review' | 'edit';
    token: string;
}

const props = defineProps<{
    cv: Cv;
    share: Share;
}>();

const isEditing = ref(false);
const isSaving = ref(false);
const isSendingComment = ref(false);
const newComment = ref('');
const activeSection = ref<string | null>(null);
const guestName = ref('');
const showMobileFeedback = ref(false);

const canEdit = computed(() => props.share.permission === 'edit');
const canComment = computed(() => props.share.permission === 'review' || props.share.permission === 'edit');

const cvData = ref({ ...props.cv });

const saveChanges = async () => {
    isSaving.value = true;
    try {
        const response = await fetch(`/s/${props.share.token}/update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify(cvData.value),
        });
        const data = await response.json();
        if (data.success) {
            isEditing.value = false;
            addToast({
                title: 'Success',
                message: 'CV updated successfully.',
                type: 'success'
            });
        } else {
            addToast({
                title: 'Error',
                message: data.message || 'Failed to update CV.',
                type: 'error'
            });
        }
    } catch (e) {
        addToast({
            title: 'Error',
            message: 'A network error occurred.',
            type: 'error'
        });
    } finally {
        isSaving.value = false;
    }
};

const sendComment = async () => {
    if (!newComment.value.trim()) return;
    
    isSendingComment.value = true;
    try {
        const response = await fetch(`/s/${props.share.token}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                content: newComment.value,
                section: activeSection.value,
                guest_name: guestName.value || 'Guest',
            }),
        });
        const data = await response.json();
        if (data.success) {
            props.cv.comments.unshift(data.comment);
            newComment.value = '';
            addToast({
                title: 'Success',
                message: 'Your feedback has been posted.',
                type: 'success'
            });
        } else {
            addToast({
                title: 'Error',
                message: data.message || 'Failed to post feedback.',
                type: 'error'
            });
        }
    } catch (e) {
        addToast({
            title: 'Error',
            message: 'A network error occurred.',
            type: 'error'
        });
    } finally {
        isSendingComment.value = false;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const templateColors: Record<string, string> = {
    modern: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    classic: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    minimal: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    creative: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    executive: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300',
};

const sortedComments = computed(() => {
    return [...props.cv.comments].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
});
</script>

<template>
    <Head :title="`Shared CV: ${cv.name}`" />

    <div class="min-h-screen bg-muted/30 flex flex-col">
        <!-- Top Banner -->
        <div class="bg-primary text-primary-foreground py-2 px-6 flex items-center justify-between text-xs font-medium sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1">
                    <Shield class="h-3 w-3" />
                    Shared with {{ share.permission }} permission
                </span>
                <Button 
                    v-if="canComment" 
                    variant="ghost" 
                    size="sm" 
                    @click="showMobileFeedback = !showMobileFeedback" 
                    class="lg:hidden h-7 text-primary-foreground hover:bg-primary-foreground/10"
                >
                    <MessageSquare class="h-3 w-3 mr-1" />
                    {{ showMobileFeedback ? 'Hide' : 'Show' }} Feedback
                </Button>
            </div>
            <div v-if="canEdit" class="flex items-center gap-2">
                <Button v-if="!isEditing" variant="secondary" size="xs" @click="isEditing = true" class="h-7 px-3">
                    Edit CV
                </Button>
                <div v-else class="flex items-center gap-2">
                    <Button variant="secondary" size="xs" @click="saveChanges" :disabled="isSaving" class="h-7 px-3">
                        <Loader2 v-if="isSaving" class="h-3 w-3 mr-1 animate-spin" />
                        <Save v-else class="h-3 w-3 mr-1" />
                        Save Changes
                    </Button>
                    <Button variant="ghost" size="xs" @click="isEditing = false" class="h-7 text-primary-foreground hover:bg-primary-foreground/10">
                        Cancel
                    </Button>
                </div>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <!-- Main Content: CV View -->
            <main class="flex-1 overflow-y-auto p-6 md:p-12 lg:p-16">
                <div class="max-w-4xl mx-auto space-y-8">
                    <!-- CV Header Card -->
                    <Card class="border-0 shadow-lg bg-white dark:bg-zinc-900">
                        <CardContent class="p-8 md:p-12">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-zinc-100 dark:border-zinc-800">
                                <div>
                                    <h1 v-if="!isEditing" class="text-3xl md:text-4xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">
                                        {{ cvData.personal_info?.full_name || 'Your Name' }}
                                    </h1>
                                    <Input v-else v-model="cvData.personal_info.full_name" class="text-3xl font-bold h-auto py-2" />
                                    
                                    <p v-if="!isEditing" class="text-lg text-primary font-medium mt-1">
                                        {{ cvData.personal_info?.title || 'Job Title' }}
                                    </p>
                                    <Input v-else v-model="cvData.personal_info.title" class="mt-2" placeholder="Job Title" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-zinc-500">
                                    <div v-if="cvData.personal_info?.email" class="flex items-center gap-2">
                                        <Mail class="h-4 w-4" />
                                        <span>{{ cvData.personal_info.email }}</span>
                                    </div>
                                    <div v-if="cvData.personal_info?.phone" class="flex items-center gap-2">
                                        <Phone class="h-4 w-4" />
                                        <span>{{ cvData.personal_info.phone }}</span>
                                    </div>
                                    <div v-if="cvData.personal_info?.location" class="flex items-center gap-2">
                                        <MapPin class="h-4 w-4" />
                                        <span>{{ cvData.personal_info.location }}</span>
                                    </div>
                                    <div v-if="cvData.personal_info?.linkedin" class="flex items-center gap-2">
                                        <Linkedin class="h-4 w-4" />
                                        <span>{{ cvData.personal_info.linkedin }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="py-8 space-y-4">
                                <h2 class="text-lg font-semibold flex items-center gap-2 text-zinc-900 dark:text-zinc-100 border-l-4 border-primary pl-4">
                                    Professional Summary
                                </h2>
                                <p v-if="!isEditing" class="text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-wrap">
                                    {{ cvData.summary || 'Write a compelling summary about your professional background and goals.' }}
                                </p>
                                <Textarea v-else v-model="cvData.summary" rows="4" class="resize-none" />
                            </div>

                            <!-- Experience -->
                            <div v-if="cvData.experience?.length || isEditing" class="py-8 space-y-6">
                                <h2 class="text-lg font-semibold flex items-center gap-2 text-zinc-900 dark:text-zinc-100 border-l-4 border-primary pl-4">
                                    Experience
                                </h2>
                                <div class="space-y-8">
                                    <div v-for="(exp, index) in cvData.experience" :key="index" class="relative pl-6 last:pb-0 group">
                                        <div class="absolute left-0 top-1.5 w-2 h-2 rounded-full bg-primary ring-4 ring-primary/10"></div>
                                        <div v-if="index < cvData.experience.length - 1" class="absolute left-[3.5px] top-4 w-px h-[calc(100%+32px)] bg-zinc-100 dark:bg-zinc-800"></div>
                                        
                                        <div class="space-y-1">
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between text-sm">
                                                <h3 v-if="!isEditing" class="font-bold text-zinc-900 dark:text-zinc-100 text-base">{{ exp.title }}</h3>
                                                <Input v-else v-model="exp.title" class="mb-2" />
                                                
                                                <span class="text-zinc-400 tabular-nums">
                                                    {{ exp.start_date }} — {{ exp.current ? 'Present' : exp.end_date }}
                                                </span>
                                            </div>
                                            <p v-if="!isEditing" class="text-primary font-medium">{{ exp.company }}</p>
                                            <Input v-else v-model="exp.company" class="mb-2" />

                                            <p v-if="!isEditing" class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mt-2 whitespace-pre-wrap">
                                                {{ exp.description }}
                                            </p>
                                            <Textarea v-else v-model="exp.description" rows="3" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills -->
                            <div v-if="cvData.skills?.length || isEditing" class="py-8 space-y-4">
                                <h2 class="text-lg font-semibold flex items-center gap-2 text-zinc-900 dark:text-zinc-100 border-l-4 border-primary pl-4">
                                    Skills
                                </h2>
                                <div class="flex flex-wrap gap-2">
                                    <Badge v-for="skill in cvData.skills" :key="skill" variant="secondary" class="bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ skill }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </main>

            <!-- Sidebar: Comments (Review Mode) -->
            <aside 
                v-if="canComment" 
                :class="[
                    'w-80 border-l bg-white dark:bg-zinc-900 flex flex-col shadow-2xl relative z-10 transition-all duration-300',
                    'lg:flex fixed inset-y-0 right-0 lg:relative lg:translate-x-0',
                    showMobileFeedback ? 'translate-x-0 flex' : 'translate-x-full hidden lg:flex'
                ]"
            >
                <div class="p-6 border-b">
                    <h3 class="font-bold flex items-center gap-2">
                        <MessageSquare class="h-5 w-5 text-primary" />
                        Feedback & Comments
                    </h3>
                    <p class="text-xs text-muted-foreground mt-1">Share your thoughts on this CV</p>
                </div>

                <div class="flex-1 p-6 overflow-y-auto">
                    <div class="space-y-6">
                        <!-- Comment Input -->
                        <div class="space-y-3 p-4 bg-muted/40 rounded-xl border border-muted-foreground/10">
                            <div v-if="!auth?.user" class="space-y-2">
                                <Label class="text-[10px] uppercase tracking-wider text-muted-foreground">Your Name</Label>
                                <Input v-model="guestName" placeholder="Guest Name" size="sm" class="h-8 text-xs" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-[10px] uppercase tracking-wider text-muted-foreground">Comment</Label>
                                <Textarea v-model="newComment" placeholder="Add your feedback..." class="text-xs min-h-[80px] resize-none" />
                            </div>
                            <Button @click="sendComment" :disabled="isSendingComment || !newComment.trim()" size="sm" class="w-full">
                                <Loader2 v-if="isSendingComment" class="h-4 w-4 mr-2 animate-spin" />
                                <Send v-else class="h-3 w-3 mr-2" />
                                Post Feedback
                            </Button>
                        </div>

                        <Separator />

                        <!-- Comments List -->
                        <div class="space-y-4">
                            <div v-for="comment in sortedComments" :key="comment.id" class="space-y-2 p-3 rounded-lg border bg-card/50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <Avatar class="h-6 w-6">
                                            <AvatarFallback class="text-[10px]">
                                                {{ (comment.user?.name || comment.guest_name || 'G').charAt(0).toUpperCase() }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span class="text-xs font-bold">{{ comment.user?.name || comment.guest_name || 'Guest' }}</span>
                                    </div>
                                    <span class="text-[10px] text-muted-foreground">{{ formatDate(comment.created_at) }}</span>
                                </div>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ comment.content }}</p>
                                <Badge v-if="comment.section" variant="outline" class="text-[9px] h-4"># {{ comment.section }}</Badge>
                            </div>
                            
                            <div v-if="cv.comments.length === 0" class="text-center py-12 opacity-50">
                                <MessageSquare class="h-8 w-8 mx-auto mb-2" />
                                <p class="text-xs">No feedback yet. Be the first to comment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <ToastContainer />
    </div>
</template>

<style scoped>
.prose {
    font-size: 0.875rem;
    line-height: 1.5;
}
</style>
