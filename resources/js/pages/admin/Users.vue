<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { 
    Search, MoreHorizontal, Mail, Shield, User, FileText, Briefcase, 
    Eye, Pencil, Trash2, UserPlus, RefreshCw, CheckCircle, XCircle,
    MessageSquare, X, Download
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
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
    onboarding_completed: boolean;
    email_verified_at?: string;
    cvs_count: number;
    applications_count: number;
    cover_letters_count: number;
    chat_sessions_count: number;
    created_at: string;
}

interface Props {
    users: {
        data: UserType[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
        role?: string;
        status?: string;
        onboarding?: string;
    };
    stats: {
        total_users: number;
        active_users: number;
        admin_users: number;
        pending_onboarding: number;
    };
}

const props = defineProps<Props>();

const { success, error } = useToast();

// Filter states
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');
const statusFilter = ref(props.filters.status || 'all');
const onboardingFilter = ref(props.filters.onboarding || 'all');

// Modal states
const showInviteModal = ref(false);
const showEditModal = ref(false);
const showDeleteDialog = ref(false);
const selectedUser = ref<UserType | null>(null);

// Forms
const inviteForm = useForm({
    name: '',
    email: '',
    role: 'user',
    send_invitation: true,
});

const editForm = useForm({
    name: '',
    email: '',
    role: 'user',
    phone: '',
    location: '',
    job_title: '',
    bio: '',
});

// Computed
const hasActiveFilters = computed(() => {
    return search.value || roleFilter.value !== 'all' || statusFilter.value !== 'all' || onboardingFilter.value !== 'all';
});

// Methods
const performSearch = useDebounceFn(() => {
    router.get('/admin/users', {
        search: search.value || undefined,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
        onboarding: onboardingFilter.value !== 'all' ? onboardingFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const handleSearch = () => {
    performSearch();
};

const handleFilterChange = () => {
    performSearch();
};

const clearFilters = () => {
    search.value = '';
    roleFilter.value = 'all';
    statusFilter.value = 'all';
    onboardingFilter.value = 'all';
    performSearch();
};

const openInviteModal = () => {
    inviteForm.reset();
    inviteForm.clearErrors();
    showInviteModal.value = true;
};

const openEditModal = (user: UserType) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.phone = user.phone || '';
    editForm.location = user.location || '';
    editForm.job_title = user.job_title || '';
    editForm.bio = user.bio || '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const openDeleteDialog = (user: UserType) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};

const submitInvite = () => {
    inviteForm.post('/admin/users', {
        preserveScroll: true,
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
            success('User invited successfully!', 'Success');
        },
        onError: () => {
            error('Failed to invite user. Please check the form and try again.', 'Error');
        },
    });
};

const submitEdit = () => {
    if (!selectedUser.value) return;
    
    editForm.put(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            selectedUser.value = null;
            success('User updated successfully!', 'Success');
        },
        onError: () => {
            error('Failed to update user. Please check the form and try again.', 'Error');
        },
    });
};

const confirmDelete = () => {
    if (!selectedUser.value) return;

    router.delete(`/admin/users/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedUser.value = null;
            success('User deleted successfully!', 'Success');
        },
        onError: () => {
            showDeleteDialog.value = false;
            error('Failed to delete user. Please try again.', 'Error');
        },
    });
};

const toggleUserRole = (user: UserType) => {
    const newRole = user.role === 'admin' ? 'user' : 'admin';
    router.patch(`/admin/users/${user.id}/toggle-role`, {
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

const resendVerification = (user: UserType) => {
    router.post(`/admin/users/${user.id}/resend-verification`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            success('Verification email sent!', 'Success');
        },
        onError: () => {
            error('Failed to send verification email.', 'Error');
        },
    });
};

const exportUsers = () => {
    window.location.href = '/admin/users/export?' + new URLSearchParams({
        search: search.value || '',
        role: roleFilter.value !== 'all' ? roleFilter.value : '',
        status: statusFilter.value !== 'all' ? statusFilter.value : '',
        onboarding: onboardingFilter.value !== 'all' ? onboardingFilter.value : '',
    }).toString();
};

const getUserInitials = (name: string) => {
    return name.split(' ').map((n: string) => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Users' }]">
        <Head title="Manage Users" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Users</h1>
                    <p class="text-muted-foreground">Manage all users on the platform</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" @click="exportUsers">
                        <Download class="h-4 w-4 mr-2" />
                        Export
                    </Button>
                    <Button @click="openInviteModal">
                        <UserPlus class="h-4 w-4 mr-2" />
                        Add User
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div v-if="stats" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold">{{ stats.total_users }}</div>
                        <p class="text-xs text-muted-foreground">Total Users</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold text-green-600">{{ stats.active_users }}</div>
                        <p class="text-xs text-muted-foreground">Active Users</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold text-blue-600">{{ stats.admin_users }}</div>
                        <p class="text-xs text-muted-foreground">Admins</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-6">
                        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_onboarding }}</div>
                        <p class="text-xs text-muted-foreground">Pending Onboarding</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Search & Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input 
                                v-model="search"
                                placeholder="Search by name or email..." 
                                class="pl-9"
                                @input="handleSearch"
                            />
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <Select :model-value="roleFilter" @update:model-value="(v) => { roleFilter = String(v ?? 'all'); handleFilterChange(); }">
                                <SelectTrigger class="w-[140px]">
                                    <SelectValue placeholder="Role" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Roles</SelectItem>
                                    <SelectItem value="admin">Admin</SelectItem>
                                    <SelectItem value="user">User</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select :model-value="statusFilter" @update:model-value="(v) => { statusFilter = String(v ?? 'all'); handleFilterChange(); }">
                                <SelectTrigger class="w-[160px]">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Status</SelectItem>
                                    <SelectItem value="verified">Verified</SelectItem>
                                    <SelectItem value="unverified">Unverified</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select :model-value="onboardingFilter" @update:model-value="(v) => { onboardingFilter = String(v ?? 'all'); handleFilterChange(); }">
                                <SelectTrigger class="w-[180px]">
                                    <SelectValue placeholder="Onboarding" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Onboarding</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearFilters">
                                <X class="h-4 w-4 mr-1" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Users Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">User</th>
                                    <th class="text-left p-4 font-medium text-sm">Role</th>
                                    <th class="text-left p-4 font-medium text-sm">Status</th>
                                    <th class="text-left p-4 font-medium text-sm">Activity</th>
                                    <th class="text-left p-4 font-medium text-sm">Joined</th>
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4">
                                        <Link :href="`/admin/users/${user.id}`" class="flex items-center gap-3 hover:opacity-80">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                                {{ getUserInitials(user.name) }}
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ user.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                            </div>
                                        </Link>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <component :is="user.role === 'admin' ? Shield : User" class="h-4 w-4 text-muted-foreground" />
                                            <Badge :variant="user.role === 'admin' ? 'default' : 'secondary'">
                                                {{ user.role }}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <Badge 
                                                :variant="user.email_verified_at ? 'default' : 'destructive'"
                                                :class="user.email_verified_at ? 'bg-green-500/10 text-green-600 hover:bg-green-500/20' : ''"
                                            >
                                                <CheckCircle v-if="user.email_verified_at" class="h-3 w-3 mr-1" />
                                                <XCircle v-else class="h-3 w-3 mr-1" />
                                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </Badge>
                                            <Badge 
                                                variant="outline"
                                                :class="user.onboarding_completed ? 'border-green-500 text-green-600' : 'border-yellow-500 text-yellow-600'"
                                            >
                                                {{ user.onboarding_completed ? 'Onboarded' : 'Pending' }}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                            <div class="flex items-center gap-1" title="CVs">
                                                <FileText class="h-3 w-3" />
                                                {{ user.cvs_count }}
                                            </div>
                                            <div class="flex items-center gap-1" title="Applications">
                                                <Briefcase class="h-3 w-3" />
                                                {{ user.applications_count }}
                                            </div>
                                            <div class="flex items-center gap-1" title="Chat Sessions">
                                                <MessageSquare class="h-3 w-3" />
                                                {{ user.chat_sessions_count }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ user.created_at }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/admin/users/${user.id}`" class="flex items-center">
                                                        <Eye class="h-4 w-4 mr-2" />
                                                        View Details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openEditModal(user)">
                                                    <Pencil class="h-4 w-4 mr-2" />
                                                    Edit User
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="toggleUserRole(user)">
                                                    <Shield class="h-4 w-4 mr-2" />
                                                    {{ user.role === 'admin' ? 'Remove Admin' : 'Make Admin' }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="!user.email_verified_at" @click="resendVerification(user)">
                                                    <Mail class="h-4 w-4 mr-2" />
                                                    Resend Verification
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="openDeleteDialog(user)">
                                                    <Trash2 class="h-4 w-4 mr-2" />
                                                    Delete User
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="p-8 text-center text-muted-foreground">
                                        <User class="h-12 w-12 mx-auto mb-4 opacity-20" />
                                        <p>No users found</p>
                                        <p v-if="hasActiveFilters" class="text-sm mt-1">Try adjusting your filters</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-muted-foreground">
                    Showing {{ users.from || 0 }} to {{ users.to || 0 }} of {{ users.total || 0 }} users
                </p>
                <div v-if="users.last_page > 1" class="flex items-center gap-2">
                    <template v-for="link in users.links" :key="link.label">
                        <Button
                            v-if="link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            as-child
                        >
                            <Link :href="link.url" v-html="link.label" preserve-scroll />
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            disabled
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Invite/Add User Modal -->
        <Dialog v-model:open="showInviteModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Add New User</DialogTitle>
                    <DialogDescription>
                        Create a new user account or send an invitation email.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitInvite" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="invite-name">Name</Label>
                        <Input 
                            id="invite-name" 
                            v-model="inviteForm.name" 
                            placeholder="John Doe"
                            required 
                        />
                        <p v-if="inviteForm.errors.name" class="text-sm text-destructive">{{ inviteForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="invite-email">Email</Label>
                        <Input 
                            id="invite-email" 
                            v-model="inviteForm.email" 
                            type="email" 
                            placeholder="john@example.com"
                            required 
                        />
                        <p v-if="inviteForm.errors.email" class="text-sm text-destructive">{{ inviteForm.errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="invite-role">Role</Label>
                        <Select v-model="inviteForm.role">
                            <SelectTrigger id="invite-role">
                                <SelectValue placeholder="Select role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="user">User</SelectItem>
                                <SelectItem value="admin">Admin</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="inviteForm.errors.role" class="text-sm text-destructive">{{ inviteForm.errors.role }}</p>
                    </div>
                    <div class="flex items-center justify-between rounded-lg border p-3">
                        <div>
                            <Label for="send-invitation" class="font-medium">Send Invitation Email</Label>
                            <p class="text-sm text-muted-foreground">User will receive an email to set their password</p>
                        </div>
                        <Switch id="send-invitation" v-model:checked="inviteForm.send_invitation" />
                    </div>
                    <DialogFooter class="gap-2 sm:gap-0">
                        <DialogClose as-child>
                            <Button type="button" variant="outline">Cancel</Button>
                        </DialogClose>
                        <Button type="submit" :disabled="inviteForm.processing">
                            <UserPlus v-if="!inviteForm.processing" class="h-4 w-4 mr-2" />
                            <RefreshCw v-else class="h-4 w-4 mr-2 animate-spin" />
                            {{ inviteForm.send_invitation ? 'Send Invitation' : 'Create User' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

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
                        Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone and will permanently remove:
                    </DialogDescription>
                </DialogHeader>
                <div class="rounded-lg bg-destructive/10 p-4 text-sm">
                    <ul class="list-disc list-inside space-y-1 text-destructive">
                        <li>User account and profile data</li>
                        <li>All CVs ({{ selectedUser?.cvs_count }} documents)</li>
                        <li>All job applications ({{ selectedUser?.applications_count }} records)</li>
                        <li>All cover letters ({{ selectedUser?.cover_letters_count }} documents)</li>
                        <li>All chat history</li>
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
    </AppLayout>
</template>
