<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Search, Mail, MailOpen, Reply, Trash2,
    MoreHorizontal, X, MessageSquare, Inbox, CheckCircle, Clock,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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

interface ContactMessage {
    id: number;
    name: string;
    email: string;
    company: string | null;
    subject: string;
    message: string;
    status: 'new' | 'read' | 'replied';
    admin_reply: string | null;
    replied_by: number | null;
    replied_at: string | null;
    replied_by_user?: { id: number; name: string } | null;
    created_at: string;
}

interface Props {
    messages: {
        data: ContactMessage[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total: number;
        new: number;
        read: number;
        replied: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();
const { addToast } = useToast();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');

const selectedMessage = ref<ContactMessage | null>(null);
const showViewDialog = ref(false);
const showReplyDialog = ref(false);
const showDeleteDialog = ref(false);
const replyText = ref('');
const isReplying = ref(false);

const hasActiveFilters = computed(() => search.value || statusFilter.value);

const applyFilters = useDebounceFn(() => {
    router.get('/admin/contact-messages', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
}, 300);

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    applyFilters();
};

const viewMessage = (message: ContactMessage) => {
    selectedMessage.value = message;
    showViewDialog.value = true;

    if (message.status === 'new') {
        router.patch(`/admin/contact-messages/${message.id}/read`, {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                message.status = 'read';
            },
        });
    }
};

const openReplyDialog = (message: ContactMessage) => {
    selectedMessage.value = message;
    replyText.value = message.admin_reply ?? '';
    showReplyDialog.value = true;
};

const sendReply = () => {
    if (!selectedMessage.value || !replyText.value.trim()) {
        return;
    }

    isReplying.value = true;

    router.post(`/admin/contact-messages/${selectedMessage.value.id}/reply`, {
        reply: replyText.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showReplyDialog.value = false;
            showViewDialog.value = false;
            replyText.value = '';
            addToast({ type: 'success', message: 'Reply sent successfully.' });
        },
        onError: () => {
            addToast({ type: 'error', message: 'Failed to send reply.' });
        },
        onFinish: () => {
            isReplying.value = false;
        },
    });
};

const confirmDelete = (message: ContactMessage) => {
    selectedMessage.value = message;
    showDeleteDialog.value = true;
};

const deleteMessage = () => {
    if (!selectedMessage.value) {
        return;
    }

    router.delete(`/admin/contact-messages/${selectedMessage.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            showViewDialog.value = false;
            addToast({ type: 'success', message: 'Message deleted.' });
        },
        onError: () => {
            addToast({ type: 'error', message: 'Failed to delete message.' });
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusBadge = (status: string) => {
    switch (status) {
        case 'new': return 'default';
        case 'read': return 'secondary';
        case 'replied': return 'outline';
        default: return 'secondary';
    }
};

const breadcrumbs = [
    { title: 'Admin', href: '/admin' },
    { title: 'Contact Messages', href: '/admin/contact-messages' },
];
</script>

<template>
    <Head title="Contact Messages" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Contact Messages</h1>
                    <p class="text-muted-foreground">Manage messages from the contact form.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <Card>
                    <CardContent class="flex items-center gap-3 pt-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <MessageSquare class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                            <p class="text-xs text-muted-foreground">Total Messages</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500">
                            <Inbox class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.new }}</p>
                            <p class="text-xs text-muted-foreground">New</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-500/10 text-yellow-500">
                            <MailOpen class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.read }}</p>
                            <p class="text-xs text-muted-foreground">Read</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="flex items-center gap-3 pt-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10 text-green-500">
                            <CheckCircle class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.replied }}</p>
                            <p class="text-xs text-muted-foreground">Replied</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search by name, email, or subject..."
                                class="pl-9"
                                @input="applyFilters"
                            />
                        </div>
                        <Select v-model="statusFilter" @update:model-value="applyFilters">
                            <SelectTrigger class="w-full sm:w-[160px]">
                                <SelectValue placeholder="All statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All statuses</SelectItem>
                                <SelectItem value="new">New</SelectItem>
                                <SelectItem value="read">Read</SelectItem>
                                <SelectItem value="replied">Replied</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="sm"
                            @click="clearFilters"
                        >
                            <X class="mr-1 h-4 w-4" />
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Messages Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">From</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Subject</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Date</th>
                                    <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="message in messages.data"
                                    :key="message.id"
                                    class="border-b transition-colors hover:bg-muted/50 cursor-pointer"
                                    :class="{ 'bg-primary/5 font-medium': message.status === 'new' }"
                                    @click="viewMessage(message)"
                                >
                                    <td class="px-4 py-3">
                                        <Badge :variant="statusBadge(message.status)">
                                            {{ message.status }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium">{{ message.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ message.email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="max-w-xs truncate text-sm">{{ message.subject }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ formatDate(message.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-right" @click.stop>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="viewMessage(message)">
                                                    <MailOpen class="mr-2 h-4 w-4" />
                                                    View
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="openReplyDialog(message)">
                                                    <Reply class="mr-2 h-4 w-4" />
                                                    Reply
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem class="text-destructive" @click="confirmDelete(message)">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="messages.data.length === 0">
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <Inbox class="mx-auto h-12 w-12 text-muted-foreground/50" />
                                        <p class="mt-4 text-sm text-muted-foreground">No contact messages found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.last_page > 1" class="flex items-center justify-between border-t px-4 py-3">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ (messages.current_page - 1) * messages.per_page + 1 }} to
                            {{ Math.min(messages.current_page * messages.per_page, messages.total) }}
                            of {{ messages.total }} messages
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in messages.links" :key="link.label">
                                <Button
                                    v-if="link.url"
                                    variant="outline"
                                    size="sm"
                                    :class="{ 'bg-primary text-primary-foreground': link.active }"
                                    @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                                >
                                    <span v-html="link.label" />
                                </Button>
                                <Button v-else variant="outline" size="sm" disabled>
                                    <span v-html="link.label" />
                                </Button>
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- View Message Dialog -->
        <Dialog v-model:open="showViewDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{{ selectedMessage?.subject }}</DialogTitle>
                    <DialogDescription>
                        From {{ selectedMessage?.name }} ({{ selectedMessage?.email }})
                        <span v-if="selectedMessage?.company"> &mdash; {{ selectedMessage?.company }}</span>
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Clock class="h-4 w-4" />
                        {{ selectedMessage ? formatDate(selectedMessage.created_at) : '' }}
                        <Badge :variant="statusBadge(selectedMessage?.status ?? 'new')" class="ml-2">
                            {{ selectedMessage?.status }}
                        </Badge>
                    </div>

                    <div class="rounded-lg border bg-muted/30 p-4">
                        <p class="whitespace-pre-wrap text-sm">{{ selectedMessage?.message }}</p>
                    </div>

                    <div v-if="selectedMessage?.admin_reply" class="space-y-2">
                        <h4 class="text-sm font-semibold text-muted-foreground">Your Reply</h4>
                        <div class="rounded-lg border border-green-500/20 bg-green-500/5 p-4">
                            <p class="whitespace-pre-wrap text-sm">{{ selectedMessage.admin_reply }}</p>
                            <p v-if="selectedMessage.replied_at" class="mt-2 text-xs text-muted-foreground">
                                Replied {{ formatDate(selectedMessage.replied_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="destructive" size="sm" @click="confirmDelete(selectedMessage!)">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                    <Button @click="openReplyDialog(selectedMessage!)">
                        <Reply class="mr-2 h-4 w-4" />
                        {{ selectedMessage?.status === 'replied' ? 'Reply Again' : 'Reply' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reply Dialog -->
        <Dialog v-model:open="showReplyDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Reply to {{ selectedMessage?.name }}</DialogTitle>
                    <DialogDescription>
                        Re: {{ selectedMessage?.subject }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4">
                    <div class="rounded-lg border bg-muted/30 p-3">
                        <p class="text-xs font-medium text-muted-foreground mb-1">Original Message:</p>
                        <p class="whitespace-pre-wrap text-sm line-clamp-4">{{ selectedMessage?.message }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="reply">Your Reply</Label>
                        <textarea
                            id="reply"
                            v-model="replyText"
                            rows="6"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                            placeholder="Type your reply..."
                        />
                    </div>
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button :disabled="isReplying || !replyText.trim()" @click="sendReply">
                        <Mail class="mr-2 h-4 w-4" />
                        {{ isReplying ? 'Sending...' : 'Send Reply' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Message</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete this message from {{ selectedMessage?.name }}? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="deleteMessage">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
