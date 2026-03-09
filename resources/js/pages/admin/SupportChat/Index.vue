<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Headset,
    MessageSquare,
    User,
    Clock,
    Bot,
    Shield,
    Settings,
    Save,
    RotateCcw,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import ToastContainer from '@/components/ToastContainer.vue';

interface Conversation {
    id: number;
    status: 'open' | 'ai_active' | 'closed';
    subject: string | null;
    updated_at: string;
    created_at: string;
    unread_count: number;
    user: { id: number; name: string; email: string };
    admin: { id: number; name: string } | null;
    latest_message: { content: string; sender_type: string; created_at: string } | null;
}

interface PaginatedData {
    data: Conversation[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

interface Props {
    conversations: PaginatedData;
    aiTimeout: number;
}

const props = defineProps<Props>();
const { addToast } = useToast();

const aiTimeout = ref(props.aiTimeout);
const savingSettings = ref(false);

const saveSettings = async () => {
    savingSettings.value = true;
    try {
        const response = await fetch('/admin/support-chat/settings', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
                ),
            },
            body: JSON.stringify({ ai_timeout: aiTimeout.value }),
        });
        if (response.ok) {
            addToast({ message: 'AI timeout settings saved successfully', type: 'success' });
        }
    } catch {
        addToast({ message: 'Failed to save settings', type: 'error' });
    } finally {
        savingSettings.value = false;
    }
};

const statusColors: Record<string, string> = {
    open: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    ai_active: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

const statusLabels: Record<string, string> = {
    open: 'Open',
    ai_active: 'AI Active',
    closed: 'Closed',
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / 60000);
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

const reopenConversation = async (convId: number, event: Event) => {
    event.preventDefault();
    event.stopPropagation();
    try {
        const response = await fetch(`/admin/support-chat/${convId}/reopen`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
                ),
            },
        });
        if (response.ok) {
            addToast({ message: 'Conversation reopened', type: 'success' });
            router.reload();
        }
    } catch {
        addToast({ message: 'Failed to reopen conversation', type: 'error' });
    }
};
</script>

<template>
    <Head title="Support Chat" />
    <AppLayout>
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight flex items-center gap-2">
                        <Headset class="h-7 w-7 text-primary" />
                        Support Chat
                    </h1>
                    <p class="text-muted-foreground mt-1">
                        Manage live support conversations with users.
                    </p>
                </div>
            </div>

            <!-- AI Timeout Settings -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base flex items-center gap-2">
                        <Settings class="h-4 w-4" />
                        AI Auto-Reply Settings
                    </CardTitle>
                    <CardDescription>
                        Configure how long before AI takes over when no admin responds.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-3">
                        <div class="space-y-1.5">
                            <Label for="timeout">AI takeover timeout (minutes)</Label>
                            <Input
                                id="timeout"
                                v-model.number="aiTimeout"
                                type="number"
                                min="1"
                                max="60"
                                class="w-32"
                            />
                        </div>
                        <Button @click="saveSettings" :disabled="savingSettings" size="sm">
                            <Save class="h-4 w-4 mr-1" />
                            Save
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Conversations List -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        Conversations
                        <Badge variant="outline" class="ml-2">{{ conversations.total }}</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div v-if="conversations.data.length === 0" class="p-8 text-center text-muted-foreground">
                        <MessageSquare class="h-10 w-10 mx-auto mb-3 opacity-50" />
                        <p>No support conversations yet.</p>
                    </div>

                    <div v-else class="divide-y">
                        <Link
                            v-for="conv in conversations.data"
                            :key="conv.id"
                            :href="`/admin/support-chat/${conv.id}`"
                            class="flex items-center gap-4 p-4 hover:bg-muted/50 transition-colors group"
                        >
                            <!-- Avatar -->
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <User class="h-5 w-5 text-primary" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium truncate">{{ conv.user.name }}</span>
                                    <Badge :class="statusColors[conv.status]" class="text-[10px] px-1.5 py-0">
                                        {{ statusLabels[conv.status] }}
                                    </Badge>
                                    <Badge v-if="conv.unread_count > 0" class="bg-red-500 text-white text-[10px] px-1.5 py-0">
                                        {{ conv.unread_count }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-muted-foreground truncate mt-0.5">
                                    {{ conv.user.email }}
                                </p>
                                <p v-if="conv.latest_message" class="text-xs text-muted-foreground truncate mt-1">
                                    <span v-if="conv.latest_message.sender_type === 'admin'" class="text-green-600 dark:text-green-400">You: </span>
                                    <span v-else-if="conv.latest_message.sender_type === 'ai'" class="text-blue-600 dark:text-blue-400">AI: </span>
                                    {{ conv.latest_message.content }}
                                </p>
                            </div>

                            <!-- Time -->
                            <div class="flex items-center gap-2 shrink-0">
                                <Button
                                    v-if="conv.status === 'closed'"
                                    variant="outline"
                                    size="sm"
                                    class="text-xs h-7"
                                    @click="reopenConversation(conv.id, $event)"
                                >
                                    <RotateCcw class="h-3 w-3 mr-1" />
                                    Reopen
                                </Button>
                                <div class="text-xs text-muted-foreground flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ formatTime(conv.updated_at) }}
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="conversations.last_page > 1" class="flex items-center justify-center gap-2 p-4 border-t">
                        <Button
                            v-for="link in conversations.links"
                            :key="link.label"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            size="sm"
                            @click="link.url && router.get(link.url)"
                            v-html="link.label"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>
        <ToastContainer />
    </AppLayout>
</template>
