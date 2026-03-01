<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Search, Mail, Users, UserMinus, TrendingUp,
    Trash2, Download, Send, MoreHorizontal, X,
    Calendar, Globe, ArrowUpRight
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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

interface Subscriber {
    id: number;
    email: string;
    name: string | null;
    status: string;
    source: string;
    ip_address: string | null;
    subscribed_at: string;
    unsubscribed_at: string | null;
    created_at: string;
}

interface Props {
    subscribers: {
        data: Subscriber[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    stats: {
        total: number;
        active: number;
        unsubscribed: number;
        this_month: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();
const { success, error } = useToast();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const showDeleteDialog = ref(false);
const selectedSubscriber = ref<Subscriber | null>(null);

const hasActiveFilters = computed(() => {
    return search.value || statusFilter.value !== 'all';
});

const performSearch = useDebounceFn(() => {
    router.get('/admin/newsletter', {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const handleSearch = () => performSearch();
const handleFilterChange = () => performSearch();

const clearFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    performSearch();
};

const openDeleteDialog = (subscriber: Subscriber) => {
    selectedSubscriber.value = subscriber;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (!selectedSubscriber.value) return;
    router.delete(`/admin/newsletter/${selectedSubscriber.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedSubscriber.value = null;
            success('Subscriber removed successfully.', 'Deleted');
        },
        onError: () => {
            error('Failed to remove subscriber.', 'Error');
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getStatusBadge = (status: string) => {
    return status === 'active'
        ? 'bg-green-500/10 text-green-600 border-green-500/20'
        : 'bg-red-500/10 text-red-600 border-red-500/20';
};

const getSourceBadge = (source: string) => {
    const map: Record<string, string> = {
        popup: 'bg-blue-500/10 text-blue-600 border-blue-500/20',
        footer: 'bg-purple-500/10 text-purple-600 border-purple-500/20',
        admin: 'bg-orange-500/10 text-orange-600 border-orange-500/20',
    };
    return map[source] || 'bg-gray-500/10 text-gray-600 border-gray-500/20';
};
</script>

<template>
    <AppLayout>
        <Head title="Newsletter Subscribers" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Newsletter</h1>
                    <p class="text-muted-foreground">Manage subscribers and send campaigns.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <a href="/admin/newsletter/export">
                            <Download class="h-4 w-4 mr-2" />
                            Export CSV
                        </a>
                    </Button>
                    <Button as-child>
                        <Link href="/admin/newsletter/compose">
                            <Send class="h-4 w-4 mr-2" />
                            Compose Email
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                            <Users class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.total }}</p>
                            <p class="text-xs text-muted-foreground">Total Subscribers</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-500/10">
                            <Mail class="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.active }}</p>
                            <p class="text-xs text-muted-foreground">Active</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500/10">
                            <UserMinus class="h-5 w-5 text-red-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.unsubscribed }}</p>
                            <p class="text-xs text-muted-foreground">Unsubscribed</p>
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="p-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/10">
                            <TrendingUp class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold">{{ stats.this_month }}</p>
                            <p class="text-xs text-muted-foreground">This Month</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search by email or name..."
                                class="pl-9"
                                @input="handleSearch"
                            />
                        </div>
                        <Select v-model="statusFilter" @update:model-value="handleFilterChange">
                            <SelectTrigger class="w-full sm:w-40">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="unsubscribed">Unsubscribed</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            v-if="hasActiveFilters"
                            variant="ghost"
                            size="icon"
                            @click="clearFilters"
                            title="Clear filters"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Subscribers Table -->
            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-muted-foreground uppercase tracking-wider">Subscriber</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-muted-foreground uppercase tracking-wider hidden md:table-cell">Source</th>
                                    <th class="text-left px-4 py-3 text-xs font-medium text-muted-foreground uppercase tracking-wider hidden lg:table-cell">Subscribed</th>
                                    <th class="text-right px-4 py-3 text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr
                                    v-for="subscriber in subscribers.data"
                                    :key="subscriber.id"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm">
                                                {{ (subscriber.name || subscriber.email).charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-sm">{{ subscriber.name || '—' }}</p>
                                                <p class="text-xs text-muted-foreground">{{ subscriber.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline" :class="getStatusBadge(subscriber.status)" class="text-xs capitalize">
                                            {{ subscriber.status }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell">
                                        <Badge variant="outline" :class="getSourceBadge(subscriber.source)" class="text-xs capitalize">
                                            {{ subscriber.source }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <div class="flex items-center gap-1.5 text-sm text-muted-foreground">
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ formatDate(subscriber.subscribed_at) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="openDeleteDialog(subscriber)" class="text-destructive focus:text-destructive">
                                                    <Trash2 class="h-4 w-4 mr-2" />
                                                    Remove
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="subscribers.data.length === 0">
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <Mail class="h-10 w-10 mx-auto text-muted-foreground/50 mb-3" />
                                        <p class="text-sm text-muted-foreground">No subscribers found.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="subscribers.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ subscribers.from }} to {{ subscribers.to }} of {{ subscribers.total }}
                        </p>
                        <div class="flex gap-1">
                            <template v-for="link in subscribers.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1.5 text-xs rounded-md transition-colors"
                                    :class="link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-muted-foreground hover:bg-muted'"
                                    v-html="link.label"
                                    preserve-scroll
                                    preserve-state
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 text-xs text-muted-foreground/50"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <Trash2 class="h-5 w-5" />
                        Remove Subscriber
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to remove <strong>{{ selectedSubscriber?.email }}</strong>? This action is permanent.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-2">
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" @click="confirmDelete">
                        <Trash2 class="h-4 w-4 mr-2" />
                        Remove
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
