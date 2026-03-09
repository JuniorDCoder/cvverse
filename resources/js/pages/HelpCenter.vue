<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Headset,
    Send,
    Loader2,
    User,
    Bot,
    Shield,
    Paperclip,
    X,
    XCircle,
    Plus,
    Check,
    CheckCheck,
    MessageSquare,
    RotateCcw,
    ChevronLeft,
} from 'lucide-vue-next';
import { marked } from 'marked';
import { useToast } from '@/composables/useToast';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import ToastContainer from '@/components/ToastContainer.vue';
import { type BreadcrumbItem } from '@/types';

interface Message {
    id: number;
    sender_type: 'user' | 'admin' | 'ai';
    sender_id: number | null;
    content: string;
    media_path: string | null;
    media_mime_type: string | null;
    read_at: string | null;
    created_at: string;
}

interface ConversationSummary {
    id: number;
    status: 'open' | 'ai_active' | 'closed';
    subject: string | null;
    updated_at: string;
    created_at: string;
    latest_message: { content: string; sender_type: string; created_at: string } | null;
    unread_count: number;
}

interface Props {
    conversation: {
        id: number;
        status: 'open' | 'ai_active' | 'closed';
        subject: string | null;
    };
    messages: Message[];
    conversations: ConversationSummary[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Help Center', href: '#' },
];

const messages = ref<Message[]>([...props.messages]);
const input = ref('');
const loading = ref(false);
const conversationStatus = ref(props.conversation.status);
const { addToast } = useToast();
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const messagesScrollArea = ref<HTMLElement | null>(null);
const adminTyping = ref(false);
const showSidebar = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;
let lastTypingSent = 0;

const activeConversationId = computed(() => props.conversation.id);

const playNotificationSound = () => {
    try {
        const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = 600;
        gain.gain.value = 0.1;
        osc.start();
        setTimeout(() => osc.stop(), 200);
    } catch (e) { }
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesScrollArea.value) {
        messagesScrollArea.value.scrollTop = messagesScrollArea.value.scrollHeight;
    }
};

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

const getCsrfToken = () => decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
);

const getLastMessageId = (): number => {
    const realMessages = messages.value.filter(m => m.id > 0);
    if (realMessages.length === 0) return 0;
    return Math.max(...realMessages.map(m => m.id));
};

const sendTypingIndicator = async (typing: boolean) => {
    const now = Date.now();
    if (typing && now - lastTypingSent < 3000) return;
    lastTypingSent = now;
    try {
        await fetch('/help-center/typing', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ conversation_id: props.conversation.id, typing }),
        });
    } catch { /* silent */ }
};

const handleInput = () => {
    sendTypingIndicator(true);
    if (typingTimeout) clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => sendTypingIndicator(false), 4000);
};

const pollForMessages = async () => {
    try {
        const response = await fetch(`/help-center/poll?conversation_id=${props.conversation.id}&after_id=${getLastMessageId()}`);
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.messages.length > 0) {
                messages.value = messages.value.filter(m => m.id > 0);
                const existingIds = new Set(messages.value.map(m => m.id));
                const newMessages = data.messages.filter((m: Message) => !existingIds.has(m.id));
                if (newMessages.length > 0) {
                    messages.value.push(...newMessages);
                    if (newMessages.some((m: Message) => m.sender_type !== 'user')) {
                        playNotificationSound();
                    }
                    scrollToBottom();
                }
            }
            if (data.conversation_status) {
                conversationStatus.value = data.conversation_status;
            }
            // Update read receipts for user's own messages
            if (data.read_updates) {
                for (const [id, readAt] of Object.entries(data.read_updates)) {
                    const msg = messages.value.find(m => m.id === Number(id));
                    if (msg && !msg.read_at) {
                        msg.read_at = readAt as string;
                    }
                }
            }
            adminTyping.value = !!data.admin_typing;
        }
    } catch { /* silent */ }
};

const sendMessage = async () => {
    if ((!input.value.trim() && !selectedFile.value) || loading.value) return;

    const userMsg = input.value.trim();
    const tempFile = selectedFile.value;

    sendTypingIndicator(false);
    if (typingTimeout) clearTimeout(typingTimeout);

    const tempId = -(Date.now());
    messages.value.push({
        id: tempId,
        sender_type: 'user',
        sender_id: null,
        content: userMsg || 'Shared a file',
        media_path: filePreview.value,
        media_mime_type: tempFile?.type || null,
        read_at: null,
        created_at: new Date().toISOString(),
    });
    input.value = '';
    clearFile();
    loading.value = true;
    scrollToBottom();

    try {
        const formData = new FormData();
        formData.append('conversation_id', String(props.conversation.id));
        formData.append('message', userMsg || 'Shared a file');
        if (tempFile) {
            formData.append('media', tempFile);
        }

        const response = await fetch('/help-center/send', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: formData,
        });

        if (response.ok) {
            await pollForMessages();
        } else {
            const data = await response.json();
            addToast({ message: data.message || 'Failed to send message', type: 'error' });
            messages.value = messages.value.filter(m => m.id !== tempId);
        }
    } catch {
        addToast({ message: 'Network error. Please try again.', type: 'error' });
        messages.value = messages.value.filter(m => m.id !== tempId);
    } finally {
        loading.value = false;
    }
};

const closeConversation = async () => {
    try {
        const response = await fetch('/help-center/close', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ conversation_id: props.conversation.id }),
        });
        if (response.ok) {
            conversationStatus.value = 'closed';
            addToast({ message: 'Conversation closed.', type: 'success' });
        }
    } catch {
        addToast({ message: 'Failed to close conversation.', type: 'error' });
    }
};

const reopenConversation = async () => {
    try {
        const response = await fetch('/help-center/reopen', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ conversation_id: props.conversation.id }),
        });
        if (response.ok) {
            conversationStatus.value = 'open';
            addToast({ message: 'Conversation reopened.', type: 'success' });
            await pollForMessages();
        }
    } catch {
        addToast({ message: 'Failed to reopen conversation.', type: 'error' });
    }
};

const startNewConversation = () => {
    router.get('/help-center');
};

const switchConversation = (id: number) => {
    showSidebar.value = false;
    router.get(`/help-center/${id}`);
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const today = new Date();
    if (date.toDateString() === today.toDateString()) return 'Today';
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return date.toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatRelativeTime = (dateString: string) => {
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

const getSenderIcon = (type: string) => {
    if (type === 'user') return User;
    if (type === 'admin') return Shield;
    return Bot;
};

const getSenderLabel = (type: string) => {
    if (type === 'user') return 'You';
    if (type === 'admin') return 'Support';
    return 'AI Assistant';
};

const renderMarkdown = (text: any): string => {
    if (!text) return '';
    if (typeof text !== 'string') return marked(JSON.stringify(text)) as string;
    return marked(text) as string;
};

const groupedMessages = () => {
    const groups: { date: string; messages: Message[] }[] = [];
    let currentDate = '';
    for (const msg of messages.value) {
        const date = formatDate(msg.created_at);
        if (date !== currentDate) {
            currentDate = date;
            groups.push({ date, messages: [msg] });
        } else {
            groups[groups.length - 1].messages.push(msg);
        }
    }
    return groups;
};

watch(messages, () => scrollToBottom(), { deep: true });

onMounted(() => {
    scrollToBottom();
    pollInterval = setInterval(pollForMessages, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (typingTimeout) clearTimeout(typingTimeout);
    sendTypingIndicator(false);
});
</script>

<template>
    <Head title="Help Center" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] p-4 md:p-6 gap-4">
            <!-- Conversation Sidebar (desktop: always visible, mobile: overlay) -->
            <div
                :class="[
                    'flex-col border rounded-xl bg-background shadow-sm overflow-hidden',
                    showSidebar ? 'fixed inset-0 z-50 flex md:relative md:inset-auto md:z-auto md:w-72 lg:w-80' : 'hidden md:flex md:w-72 lg:w-80'
                ]"
            >
                <!-- Sidebar header -->
                <div class="p-3 border-b flex items-center justify-between bg-muted/30">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <MessageSquare class="h-4 w-4 text-primary" />
                        Conversations
                    </h3>
                    <div class="flex items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-7 w-7" @click="startNewConversation" title="New conversation">
                            <Plus class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-7 w-7 md:hidden" @click="showSidebar = false">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Conversation list -->
                <div class="flex-1 overflow-y-auto">
                    <div v-if="conversations.length === 0" class="p-6 text-center text-muted-foreground text-sm">
                        No conversations yet.
                    </div>
                    <button
                        v-for="conv in conversations"
                        :key="conv.id"
                        class="w-full text-left p-3 border-b hover:bg-muted/50 transition-colors"
                        :class="conv.id === activeConversationId ? 'bg-primary/5 border-l-2 border-l-primary' : ''"
                        @click="switchConversation(conv.id)"
                    >
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-medium truncate">{{ conv.subject || 'Help Request' }}</span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <Badge v-if="conv.unread_count > 0" class="bg-red-500 text-white text-[10px] h-4 min-w-4 px-1 flex items-center justify-center">
                                    {{ conv.unread_count }}
                                </Badge>
                                <span
                                    class="inline-block h-2 w-2 rounded-full"
                                    :class="{
                                        'bg-green-500': conv.status === 'open',
                                        'bg-blue-500': conv.status === 'ai_active',
                                        'bg-gray-400': conv.status === 'closed',
                                    }"
                                ></span>
                            </div>
                        </div>
                        <p v-if="conv.latest_message" class="text-xs text-muted-foreground truncate">
                            <span v-if="conv.latest_message.sender_type === 'admin'" class="text-green-600 dark:text-green-400">Support: </span>
                            <span v-else-if="conv.latest_message.sender_type === 'ai'" class="text-blue-600 dark:text-blue-400">AI: </span>
                            <span v-else>You: </span>
                            {{ conv.latest_message.content }}
                        </p>
                        <p class="text-[10px] text-muted-foreground mt-1">{{ formatRelativeTime(conv.updated_at) }}</p>
                    </button>
                </div>
            </div>

            <!-- Chat Area -->
            <Card class="flex-1 flex flex-col shadow-xl border-primary/20 overflow-hidden">
                <!-- Header -->
                <CardHeader class="p-4 border-b bg-muted/30 flex flex-row items-center justify-between gap-2 space-y-0">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <!-- Mobile sidebar toggle -->
                        <Button variant="ghost" size="icon" class="shrink-0 h-8 w-8 md:hidden" @click="showSidebar = true">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <Headset class="h-5 w-5 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <CardTitle class="text-base md:text-lg truncate leading-tight">
                                Help Center
                            </CardTitle>
                            <CardDescription class="text-xs truncate flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping"
                                        :class="conversationStatus === 'closed' ? 'bg-gray-400' : conversationStatus === 'ai_active' ? 'bg-blue-400' : 'bg-green-400'"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2 w-2"
                                        :class="conversationStatus === 'closed' ? 'bg-gray-500' : conversationStatus === 'ai_active' ? 'bg-blue-500' : 'bg-green-500'"
                                    ></span>
                                </span>
                                <span v-if="adminTyping">Support is typing...</span>
                                <span v-else-if="conversationStatus === 'ai_active'">AI Assistant Active</span>
                                <span v-else-if="conversationStatus === 'closed'">Conversation Closed</span>
                                <span v-else>Live Support</span>
                            </CardDescription>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <Badge v-if="conversationStatus === 'ai_active'" variant="outline" class="bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800 text-xs">
                            AI Mode
                        </Badge>
                        <Button
                            v-if="conversationStatus !== 'closed'"
                            variant="ghost"
                            size="sm"
                            class="text-muted-foreground hover:text-destructive"
                            @click="closeConversation"
                        >
                            <XCircle class="h-4 w-4 mr-1" />
                            <span class="hidden sm:inline">Close</span>
                        </Button>
                    </div>
                </CardHeader>

                <!-- Messages Area -->
                <CardContent class="flex-1 p-0 overflow-hidden flex flex-col bg-muted/5 relative">
                    <div class="flex-1 p-4 overflow-y-auto scroll-smooth" ref="messagesScrollArea">
                        <div class="space-y-1 max-w-3xl mx-auto">
                            <template v-for="(group, gi) in groupedMessages()" :key="gi">
                                <div class="flex items-center justify-center my-4">
                                    <div class="bg-muted text-muted-foreground text-xs px-3 py-1 rounded-full">
                                        {{ group.date }}
                                    </div>
                                </div>

                                <div
                                    v-for="msg in group.messages"
                                    :key="msg.id"
                                    class="flex gap-3 mb-4 animate-in fade-in slide-in-from-bottom-2 duration-300"
                                    :class="msg.sender_type === 'user' ? 'flex-row-reverse' : 'flex-row'"
                                >
                                    <div
                                        class="h-8 w-8 rounded-full flex items-center justify-center shrink-0 shadow-sm"
                                        :class="{
                                            'bg-primary text-primary-foreground': msg.sender_type === 'user',
                                            'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 ring-1 ring-green-200 dark:ring-green-800': msg.sender_type === 'admin',
                                            'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 ring-1 ring-blue-200 dark:ring-blue-800': msg.sender_type === 'ai',
                                        }"
                                    >
                                        <component :is="getSenderIcon(msg.sender_type)" class="h-4 w-4" />
                                    </div>

                                    <div class="group relative max-w-[80%] md:max-w-[70%]">
                                        <div class="text-[10px] text-muted-foreground mb-0.5" :class="msg.sender_type === 'user' ? 'text-right' : 'text-left'">
                                            {{ getSenderLabel(msg.sender_type) }} &middot; {{ formatTime(msg.created_at) }}
                                        </div>
                                        <div
                                            class="rounded-2xl px-4 py-2.5 shadow-sm select-text whitespace-pre-wrap leading-relaxed text-sm"
                                            :class="{
                                                'bg-primary text-primary-foreground rounded-tr-sm': msg.sender_type === 'user',
                                                'bg-background border rounded-tl-sm': msg.sender_type !== 'user',
                                            }"
                                        >
                                            <div v-if="msg.media_path" class="mb-2 max-w-xs overflow-hidden rounded-lg border">
                                                <img
                                                    v-if="msg.media_path.match(/\.(jpg|jpeg|png|webp)/i) || msg.media_path.startsWith('blob:')"
                                                    :src="msg.media_path.startsWith('blob:') || msg.media_path.startsWith('/storage') ? msg.media_path : `/storage/${msg.media_path}`"
                                                    class="w-full h-auto object-cover max-h-48"
                                                />
                                                <div v-else class="p-3 bg-muted flex items-center gap-2 text-xs">
                                                    <Paperclip class="h-4 w-4" />
                                                    <span class="truncate">File Shared</span>
                                                </div>
                                            </div>
                                            <template v-if="msg.sender_type === 'user'">
                                                {{ msg.content }}
                                            </template>
                                            <div v-else class="prose prose-sm dark:prose-invert max-w-none [&>p]:mb-1 [&>p:last-child]:mb-0 [&>ul]:mb-1 [&>ol]:mb-1" v-html="renderMarkdown(msg.content)"></div>
                                        </div>
                                        <!-- Read receipts for user messages -->
                                        <div v-if="msg.sender_type === 'user' && msg.id > 0" class="flex justify-end mt-0.5">
                                            <CheckCheck v-if="msg.read_at" class="h-3.5 w-3.5 text-blue-500" />
                                            <Check v-else class="h-3.5 w-3.5 text-muted-foreground/50" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Admin / AI typing indicator -->
                            <div v-if="adminTyping" class="flex gap-3 mb-4">
                                <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 flex items-center justify-center shrink-0 ring-1 ring-green-200 dark:ring-green-800">
                                    <Shield class="h-4 w-4" />
                                </div>
                                <div class="bg-background border rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                                    <div class="flex gap-1">
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce"></div>
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading indicator when sending -->
                            <div v-if="loading && !adminTyping" class="flex gap-3 mb-4">
                                <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0">
                                    <Bot class="h-4 w-4" />
                                </div>
                                <div class="bg-background border rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                                    <div class="flex gap-1">
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce"></div>
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                        <div class="h-1.5 w-1.5 bg-primary/40 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>

                <!-- Input Area -->
                <CardFooter class="p-4 border-t bg-background flex-col space-y-3">
                    <div v-if="selectedFile" class="flex items-center gap-3 p-2 bg-muted/50 rounded-lg w-full animate-in slide-in-from-bottom-1 duration-200">
                        <div class="h-10 w-10 rounded border bg-background overflow-hidden shrink-0">
                            <img v-if="filePreview" :src="filePreview" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <Paperclip class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </div>
                        <span class="text-sm truncate flex-1">{{ selectedFile.name }}</span>
                        <Button variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click="clearFile">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>

                    <template v-if="conversationStatus !== 'closed'">
                        <div class="flex items-end gap-2 w-full">
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp,application/pdf,.doc,.docx"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <Button variant="ghost" size="icon" class="shrink-0 h-10 w-10" @click="triggerFileInput">
                                <Paperclip class="h-5 w-5" />
                            </Button>
                            <Textarea
                                v-model="input"
                                placeholder="Type your message..."
                                class="flex-1 min-h-[40px] max-h-[120px] resize-none"
                                rows="1"
                                @keydown="handleKeydown"
                                @input="handleInput"
                            />
                            <Button
                                class="shrink-0 h-10 w-10"
                                size="icon"
                                :disabled="(!input.trim() && !selectedFile) || loading"
                                @click="sendMessage"
                            >
                                <Loader2 v-if="loading" class="h-5 w-5 animate-spin" />
                                <Send v-else class="h-5 w-5" />
                            </Button>
                        </div>
                    </template>
                    <template v-else>
                        <div class="text-center w-full space-y-3 py-2">
                            <p class="text-sm text-muted-foreground">This conversation has been closed.</p>
                            <div class="flex items-center justify-center gap-3">
                                <Button variant="outline" @click="reopenConversation">
                                    <RotateCcw class="h-4 w-4 mr-1.5" />
                                    Reopen
                                </Button>
                                <Button @click="startNewConversation">
                                    <Plus class="h-4 w-4 mr-1.5" />
                                    New Conversation
                                </Button>
                            </div>
                        </div>
                    </template>
                </CardFooter>
            </Card>
        </div>
        <ToastContainer />
    </AppLayout>
</template>
