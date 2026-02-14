<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, Mail, Shield, User, FileText, Briefcase, 
    Eye, Pencil, Trash2, RefreshCw, CheckCircle, XCircle,
    MessageSquare, Calendar, MapPin, Phone, Building2,
    Clock, CreditCard, ExternalLink, MoreVertical
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';

interface Cv {
    id: number;
    name: string;
    template: string;
    is_primary: boolean;
    created_at: string;
}

interface CoverLetter {
    id: number;
    name: string;
    tone: string;
    created_at: string;
}

interface JobApplication {
    id: number;
    company_name: string;
    job_title: string;
    status: string;
    applied_at: string;
}

interface ChatSession {
    id: number;
    title: string;
    messages_count: number;
    created_at: string;
}

interface Activity {
    id: number;
    type: string;
    description: string;
    created_at: string;
    meta?: Record<string, any>;
}

interface UserType {
    id: number;
    name: string;
    email: string;
    role: string;
    phone?: string;
    location?: string;
    job_title?: string;
    industry?: string;
    experience_level?: string;
    bio?: string;
    avatar?: string;
    onboarding_completed: boolean;
    onboarding_completed_at?: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
    pricing_plan?: {
        id: number;
        name: string;
        slug: string;
    };
    subscription_status?: string;
    subscription_ends_at?: string;
}

interface Props {
    user: UserType;
    cvs: Cv[];
    coverLetters: CoverLetter[];
    applications: JobApplication[];
    chatSessions: ChatSession[];
    activities: Activity[];
    stats: {
        total_cvs: number;
        total_cover_letters: number;
        total_applications: number;
        total_chat_sessions: number;
    };
}

const props = defineProps<Props>();

const { success, error } = useToast();

// Modal states
const showEditModal = ref(false);
const showDeleteDialog = ref(false);
const showClearActivityDialog = ref(false);

// Edit form
const editForm = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    phone: props.user.phone || '',
    location: props.user.location || '',
    job_title: props.user.job_title || '',
    industry: props.user.industry || '',
    experience_level: props.user.experience_level || '',
    bio: props.user.bio || '',
});

// Computed
const getUserInitials = computed(() => {
    return props.user.name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
});

// Methods
const openEditModal = () => {
    editForm.name = props.user.name;
    editForm.email = props.user.email;
    editForm.role = props.user.role;
    editForm.phone = props.user.phone || '';
    editForm.location = props.user.location || '';
    editForm.job_title = props.user.job_title || '';
    editForm.industry = props.user.industry || '';
    editForm.experience_level = props.user.experience_level || '';
    editForm.bio = props.user.bio || '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const submitEdit = () => {
    editForm.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            success('User updated successfully!', 'Success');
        },
        onError: () => {
            error('Failed to update user. Please check the form and try again.', 'Error');
        },
    });
};

const confirmDelete = () => {
    router.delete(`/admin/users/${props.user.id}`, {
        onSuccess: () => {
            success('User deleted successfully!', 'Success');
        },
        onError: () => {
            error('Failed to delete user. Please try again.', 'Error');
        },
    });
};

const toggleUserRole = () => {
    const newRole = props.user.role === 'admin' ? 'user' : 'admin';
    router.patch(`/admin/users/${props.user.id}/toggle-role`, {
        role: newRole,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            success(`User role changed to ${newRole}!`, 'Success');
        },
        onError: () => {
            error('Failed to change user role.', 'Error');
        },
    });
};

const resendVerification = () => {
    router.post(`/admin/users/${props.user.id}/resend-verification`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            success('Verification email sent!', 'Success');
        },
        onError: () => {
            error('Failed to send verification email.', 'Error');
        },
    });
};

const clearUserActivity = (type: string) => {
    router.delete(`/admin/users/${props.user.id}/clear-activity`, {
        data: { type },
        preserveScroll: true,
        onSuccess: () => {
            showClearActivityDialog.value = false;
            success(`User ${type} cleared successfully!`, 'Success');
        },
        onError: () => {
            error(`Failed to clear user ${type}.`, 'Error');
        },
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        applied: 'bg-blue-500/10 text-blue-600 border-blue-500',
        interviewing: 'bg-yellow-500/10 text-yellow-600 border-yellow-500',
        offered: 'bg-green-500/10 text-green-600 border-green-500',
        rejected: 'bg-red-500/10 text-red-600 border-red-500',
        accepted: 'bg-green-500/10 text-green-600 border-green-500',
        withdrawn: 'bg-gray-500/10 text-gray-600 border-gray-500',
    };
    return colors[status] || 'bg-gray-500/10 text-gray-600 border-gray-500';
};

const getActivityIcon = (type: string) => {
    const icons: Record<string, any> = {
        cv_created: FileText,
        cv_updated: FileText,
        application_created: Briefcase,
        application_status_changed: Briefcase,
        cover_letter_created: FileText,
        chat_session_started: MessageSquare,
        login: User,
    };
    return icons[type] || Clock;
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Users', href: '/admin/users' }, { title: user.name }]">
        <Head :title="`User: ${user.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Back Button & Actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <Button variant="ghost" as-child>
                    <Link href="/admin/users" class="gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back to Users
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="openEditModal">
                        <Pencil class="h-4 w-4 mr-2" />
                        Edit User
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem @click="toggleUserRole">
                                <Shield class="h-4 w-4 mr-2" />
                                {{ user.role === 'admin' ? 'Remove Admin' : 'Make Admin' }}
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="!user.email_verified_at" @click="resendVerification">
                                <Mail class="h-4 w-4 mr-2" />
                                Resend Verification
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="text-destructive focus:text-destructive" @click="showDeleteDialog = true">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Delete User
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- User Profile Header -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col md:flex-row md:items-start gap-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-2xl font-bold">
                                {{ getUserInitials }}
                            </div>
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-1 space-y-4">
                            <div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h1 class="text-2xl font-bold">{{ user.name }}</h1>
                                    <Badge :variant="user.role === 'admin' ? 'default' : 'secondary'">
                                        <Shield v-if="user.role === 'admin'" class="h-3 w-3 mr-1" />
                                        {{ user.role }}
                                    </Badge>
                                </div>
                                <p class="text-muted-foreground">{{ user.email }}</p>
                            </div>

                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-1 text-muted-foreground">
                                    <Calendar class="h-4 w-4" />
                                    Joined {{ user.created_at }}
                                </div>
                                <div v-if="user.location" class="flex items-center gap-1 text-muted-foreground">
                                    <MapPin class="h-4 w-4" />
                                    {{ user.location }}
                                </div>
                                <div v-if="user.phone" class="flex items-center gap-1 text-muted-foreground">
                                    <Phone class="h-4 w-4" />
                                    {{ user.phone }}
                                </div>
                                <div v-if="user.job_title" class="flex items-center gap-1 text-muted-foreground">
                                    <Building2 class="h-4 w-4" />
                                    {{ user.job_title }}
                                </div>
                            </div>

                            <!-- Status Badges -->
                            <div class="flex flex-wrap gap-2">
                                <Badge 
                                    :variant="user.email_verified_at ? 'default' : 'destructive'"
                                    :class="user.email_verified_at ? 'bg-green-500/10 text-green-600 hover:bg-green-500/20' : ''"
                                >
                                    <CheckCircle v-if="user.email_verified_at" class="h-3 w-3 mr-1" />
                                    <XCircle v-else class="h-3 w-3 mr-1" />
                                    {{ user.email_verified_at ? 'Email Verified' : 'Email Not Verified' }}
                                </Badge>
                                <Badge 
                                    variant="outline"
                                    :class="user.onboarding_completed ? 'border-green-500 text-green-600' : 'border-yellow-500 text-yellow-600'"
                                >
                                    {{ user.onboarding_completed ? 'Onboarding Complete' : 'Onboarding Pending' }}
                                </Badge>
                                <Badge v-if="user.pricing_plan" variant="outline" class="border-primary text-primary">
                                    <CreditCard class="h-3 w-3 mr-1" />
                                    {{ user.pricing_plan.name }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-2">
                            <div class="text-center p-3 rounded-lg bg-muted/50">
                                <div class="text-2xl font-bold">{{ stats.total_cvs }}</div>
                                <p class="text-xs text-muted-foreground">CVs</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-muted/50">
                                <div class="text-2xl font-bold">{{ stats.total_cover_letters }}</div>
                                <p class="text-xs text-muted-foreground">Cover Letters</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-muted/50">
                                <div class="text-2xl font-bold">{{ stats.total_applications }}</div>
                                <p class="text-xs text-muted-foreground">Applications</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-muted/50">
                                <div class="text-2xl font-bold">{{ stats.total_chat_sessions }}</div>
                                <p class="text-xs text-muted-foreground">Chats</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div v-if="user.bio" class="mt-6 pt-6 border-t">
                        <h3 class="font-medium mb-2">Bio</h3>
                        <p class="text-muted-foreground">{{ user.bio }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabs for User Data -->
            <Tabs default-value="cvs" class="w-full">
                <TabsList class="grid w-full grid-cols-5">
                    <TabsTrigger value="cvs" class="gap-2">
                        <FileText class="h-4 w-4" />
                        <span class="hidden sm:inline">CVs</span>
                        <Badge variant="secondary" class="ml-1">{{ cvs.length }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="cover-letters" class="gap-2">
                        <FileText class="h-4 w-4" />
                        <span class="hidden sm:inline">Cover Letters</span>
                        <Badge variant="secondary" class="ml-1">{{ coverLetters.length }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="applications" class="gap-2">
                        <Briefcase class="h-4 w-4" />
                        <span class="hidden sm:inline">Applications</span>
                        <Badge variant="secondary" class="ml-1">{{ applications.length }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="chats" class="gap-2">
                        <MessageSquare class="h-4 w-4" />
                        <span class="hidden sm:inline">Chats</span>
                        <Badge variant="secondary" class="ml-1">{{ chatSessions.length }}</Badge>
                    </TabsTrigger>
                    <TabsTrigger value="activity" class="gap-2">
                        <Clock class="h-4 w-4" />
                        <span class="hidden sm:inline">Activity</span>
                    </TabsTrigger>
                </TabsList>

                <!-- CVs Tab -->
                <TabsContent value="cvs">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>CVs</CardTitle>
                                <CardDescription>All CVs created by this user</CardDescription>
                            </div>
                            <Button v-if="cvs.length > 0" variant="outline" size="sm" @click="showClearActivityDialog = true">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Clear All
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="cvs.length > 0" class="space-y-3">
                                <div v-for="cv in cvs" :key="cv.id" class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p class="font-medium">{{ cv.name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ cv.template }} • Created {{ cv.created_at }}
                                            </p>
                                        </div>
                                        <Badge v-if="cv.is_primary" variant="default" class="ml-2">Primary</Badge>
                                    </div>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/cvs/${cv.id}`" target="_blank">
                                            <ExternalLink class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <FileText class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                <p>No CVs created yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Cover Letters Tab -->
                <TabsContent value="cover-letters">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Cover Letters</CardTitle>
                                <CardDescription>All cover letters created by this user</CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="coverLetters.length > 0" class="space-y-3">
                                <div v-for="letter in coverLetters" :key="letter.id" class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <FileText class="h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p class="font-medium">{{ letter.name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ letter.tone }} tone • Created {{ letter.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="`/cover-letters/${letter.id}`" target="_blank">
                                            <ExternalLink class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <FileText class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                <p>No cover letters created yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Applications Tab -->
                <TabsContent value="applications">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Job Applications</CardTitle>
                                <CardDescription>All job applications tracked by this user</CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applications.length > 0" class="space-y-3">
                                <div v-for="app in applications" :key="app.id" class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <Briefcase class="h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p class="font-medium">{{ app.job_title }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ app.company_name }} • Applied {{ app.applied_at }}
                                            </p>
                                        </div>
                                    </div>
                                    <Badge variant="outline" :class="getStatusColor(app.status)">
                                        {{ app.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Briefcase class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                <p>No job applications tracked yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Chat Sessions Tab -->
                <TabsContent value="chats">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Chat Sessions</CardTitle>
                                <CardDescription>AI chat history for this user</CardDescription>
                            </div>
                            <Button v-if="chatSessions.length > 0" variant="outline" size="sm" @click="clearUserActivity('chat_sessions')">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Clear All
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="chatSessions.length > 0" class="space-y-3">
                                <div v-for="session in chatSessions" :key="session.id" class="flex items-center justify-between p-4 rounded-lg border hover:bg-muted/50 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <MessageSquare class="h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p class="font-medium">{{ session.title || 'Untitled Chat' }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ session.messages_count }} messages • {{ session.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <MessageSquare class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                <p>No chat sessions yet</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Activity Tab -->
                <TabsContent value="activity">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>User's recent actions and events</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="activities.length > 0" class="space-y-4">
                                <div v-for="activity in activities" :key="activity.id" class="flex items-start gap-3 pb-4 border-b last:border-0 last:pb-0">
                                    <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center">
                                        <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">{{ activity.description }}</p>
                                        <p class="text-xs text-muted-foreground mt-1">{{ activity.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-muted-foreground">
                                <Clock class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                <p>No recent activity</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Edit User Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                    <DialogDescription>
                        Update user information and settings.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit-name">Name</Label>
                            <Input id="edit-name" v-model="editForm.name" required />
                            <p v-if="editForm.errors.name" class="text-sm text-destructive">{{ editForm.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-email">Email</Label>
                            <Input id="edit-email" v-model="editForm.email" type="email" required />
                            <p v-if="editForm.errors.email" class="text-sm text-destructive">{{ editForm.errors.email }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit-role">Role</Label>
                            <Select v-model="editForm.role">
                                <SelectTrigger id="edit-role">
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="user">User</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-phone">Phone</Label>
                            <Input id="edit-phone" v-model="editForm.phone" placeholder="+1 234 567 890" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit-location">Location</Label>
                            <Input id="edit-location" v-model="editForm.location" placeholder="New York, USA" />
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-job-title">Job Title</Label>
                            <Input id="edit-job-title" v-model="editForm.job_title" placeholder="Software Engineer" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="edit-industry">Industry</Label>
                            <Input id="edit-industry" v-model="editForm.industry" placeholder="Technology" />
                        </div>
                        <div class="space-y-2">
                            <Label for="edit-experience">Experience Level</Label>
                            <Select v-model="editForm.experience_level">
                                <SelectTrigger id="edit-experience">
                                    <SelectValue placeholder="Select level" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="entry">Entry Level</SelectItem>
                                    <SelectItem value="mid">Mid Level</SelectItem>
                                    <SelectItem value="senior">Senior Level</SelectItem>
                                    <SelectItem value="executive">Executive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit-bio">Bio</Label>
                        <Textarea id="edit-bio" v-model="editForm.bio" placeholder="Tell us about this user..." rows="3" />
                    </div>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <DialogClose as-child>
                            <Button type="button" variant="outline">Cancel</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="editForm.processing">
                            <RefreshCw v-if="editForm.processing" class="h-4 w-4 mr-2 animate-spin" />
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <Trash2 class="h-5 w-5" />
                        Delete User
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{{ user.name }}</strong>? This action cannot be undone and will permanently remove:
                    </DialogDescription>
                </DialogHeader>
                <div class="rounded-lg bg-destructive/10 p-4 text-sm">
                    <ul class="list-disc list-inside space-y-1 text-destructive">
                        <li>User account and profile data</li>
                        <li>All CVs ({{ stats.total_cvs }} documents)</li>
                        <li>All job applications ({{ stats.total_applications }} records)</li>
                        <li>All cover letters ({{ stats.total_cover_letters }} documents)</li>
                        <li>All chat history ({{ stats.total_chat_sessions }} sessions)</li>
                    </ul>
                </div>
                <DialogFooter class="gap-2 sm:gap-0">
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="confirmDelete">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Delete User
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Clear Activity Confirmation Dialog -->
        <Dialog v-model:open="showClearActivityDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <Trash2 class="h-5 w-5" />
                        Clear User Data
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to clear this user's data? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="clearUserActivity('cvs')">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Clear Data
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
