<script setup lang="ts">
import { ref, watch, nextTick, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Send,
    Loader2,
    User,
    Bot,
    Shield,
    Paperclip,
    X,
    XCircle,
    Check,
    CheckCheck,
    RotateCcw,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { marked } from 'marked';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import ToastContainer from '@/components/ToastContainer.vue';

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

interface Props {
    conversation: {
        id: number;
        status: 'open' | 'ai_active' | 'closed';
        subject: string | null;
        user: { id: number; name: string; email: string };
        admin_id: number | null;
        created_at: string;
    };
    messages: Message[];
}

const props = defineProps<Props>();

const messages = ref<Message[]>([...props.messages]);
const input = ref('');
const loading = ref(false);
const conversationStatus = ref(props.conversation.status);
const { addToast } = useToast();
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const messagesScrollArea = ref<HTMLElement | null>(null);
const userTyping = ref(false);
const hasUnrespondedUserMessage = ref(false);
let pollInterval: ReturnType<typeof setInterval> | null = null;
let soundInterval: ReturnType<typeof setInterval> | null = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;
let lastTypingSent = 0;

const getCsrfToken = () => decodeURIComponent(
    document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''
);

const playNotificationSound = () => {
    try {
        const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = 800;
        gain.gain.value = 0.1;
        osc.start();
        setTimeout(() => osc.stop(), 150);
    } catch (e) { }
};

const startPersistentNotification = () => {
    if (soundInterval) return;
    playNotificationSound();
    soundInterval = setInterval(playNotificationSound, 10000);
};

const stopPersistentNotification = () => {
    if (soundInterval) {
        clearInterval(soundInterval);
        soundInterval = null;
    }
};

// Check if the last message is from user (unanswered)
const checkUnrespondedMessages = () => {
    const realMessages = messages.value.filter(m => m.id > 0);
    if (realMessages.length === 0) {
        hasUnrespondedUserMessage.value = false;
        return;
    }
    const lastMsg = realMessages[realMessages.length - 1];
    hasUnrespondedUserMessage.value = lastMsg.sender_type === 'user';
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
        await fetch(`/admin/support-chat/${props.conversation.id}/typing`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: JSON.stringify({ typing }),
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
        const response = await fetch(`/admin/support-chat/${props.conversation.id}/poll?after_id=${getLastMessageId()}`);
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.messages.length > 0) {
                messages.value = messages.value.filter(m => m.id > 0);
                const existingIds = new Set(messages.value.map(m => m.id));
                const newMessages = data.messages.filter((m: Message) => !existingIds.has(m.id));
                if (newMessages.length > 0) {
                    messages.value.push(...newMessages);
                    if (newMessages.some((m: Message) => m.sender_type === 'user')) {
                        startPersistentNotification();
                    }
                    scrollToBottom();
                }
            }
            if (data.conversation_status) {
                conversationStatus.value = data.conversation_status;
            }
            // Update read receipts for admin's own messages
            if (data.read_updates) {
                for (const [id, readAt] of Object.entries(data.read_updates)) {
                    const msg = messages.value.find(m => m.id === Number(id));
                    if (msg && !msg.read_at) {
                        msg.read_at = readAt as string;
                    }
                }
            }
            userTyping.value = !!data.user_typing;
            checkUnrespondedMessages();
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
        sender_type: 'admin',
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

    // Admin responded — stop persistent sound
    stopPersistentNotification();
    hasUnrespondedUserMessage.value = false;

    try {
        const formData = new FormData();
        formData.append('message', userMsg || 'Shared a file');
        if (tempFile) {
            formData.append('media', tempFile);
        }

        const response = await fetch(`/admin/support-chat/${props.conversation.id}/reply`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
            body: formData,
        });

        if (response.ok) {
            conversationStatus.value = 'open';
            await pollForMessages();
        } else {
            const data = await response.json();
            addToast({ message: data.message || 'Failed to send', type: 'error' });
            messages.value = messages.value.filter(m => m.id !== tempId);
        }
    } catch {
        addToast({ message: 'Network error', type: 'error' });
        messages.value = messages.value.filter(m => m.id !== tempId);
    } finally {
        loading.value = false;
    }
};

const closeConversation = async () => {
    try {
        const response = await fetch(`/admin/support-chat/${props.conversation.id}/close`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
        });
        if (response.ok) {
            conversationStatus.value = 'closed';
            addToast({ message: 'Conversation closed', type: 'success' });
            stopPersistentNotification();
            await pollForMessages();
        }
    } catch {
        addToast({ message: 'Failed to close conversation', type: 'error' });
    }
};

const reopenConversation = async () => {
    try {
        const response = await fetch(`/admin/support-chat/${props.conversation.id}/reopen`, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getCsrfToken() },
        });
        if (response.ok) {
            conversationStatus.value = 'open';
            addToast({ message: 'Conversation reopened', type: 'success' });
            await pollForMessages();
        }
    } catch {
        addToast({ message: 'Failed to reopen conversation', type: 'error' });
    }
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

const getSenderIcon = (type: string) => {
    if (type === 'user') return User;
    if (type === 'admin') return Shield;
    return Bot;
};

const getSenderLabel = (type: string) => {
    if (type === 'user') return props.conversation.user.name;
    if (type === 'admin') return 'You (Admin)';
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
    checkUnrespondedMessages();
    if (hasUnrespondedUserMessage.value) {
        startPersistentNotification();
    }
    pollInterval = setInterval(pollForMessages, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (typingTimeout) clearTimeout(typingTimeout);
    stopPersistentNotification();
    sendTypingIndicator(false);
});
</script>

<template>
    <Head :title="`Chat with ${conversation.user.name}`" />
    <AppLayout>
        <div class="flex flex-col h-[calc(100vh-4rem)] p-4 md:p-6">
            <Card class="flex-1 flex flex-col shadow-xl border-primary/20 overflow-hidden">
                <!-- Header -->
                <CardHeader class="p-4 border-b bg-muted/30 flex flex-row items-center justify-between gap-2 space-y-0">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <Button variant="ghost" size="icon" as-child class="shrink-0 h-8 w-8">
                            <Link href="/admin/support-chat">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <User class="h-5 w-5 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <CardTitle class="text-base truncate leading-tight">
                                {{ conversation.user.name }}
                            </CardTitle>
                            <CardDescription class="text-xs truncate flex items-center gap-1.5">
                                <template v-if="userTyping">
                                    <span class="text-green-600 dark:text-green-400 font-medium">typing...</span>
                                </template>
                                <template v-else>
                                    {{ conversation.user.email }}
                                    <span v-if="conversation.subject"> &middot; {{ conversation.subject }}</span>
                                </template>
                            </CardDescription>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <Badge
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': conversationStatus === 'open',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': conversationStatus === 'ai_active',
                                'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300': conversationStatus === 'closed',
                            }"
                            class="text-xs"
                        >
                            {{ conversationStatus === 'ai_active' ? 'AI Active' : conversationStatus === 'closed' ? 'Closed' : 'Open' }}
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

                <!-- Messages -->
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
                                    :class="msg.sender_type === 'admin' ? 'flex-row-reverse' : 'flex-row'"
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
                                        <div class="text-[10px] text-muted-foreground mb-0.5" :class="msg.sender_type === 'admin' ? 'text-right' : 'text-left'">
                                            {{ getSenderLabel(msg.sender_type) }} &middot; {{ formatTime(msg.created_at) }}
                                        </div>
                                        <div
                                            class="rounded-2xl px-4 py-2.5 shadow-sm select-text whitespace-pre-wrap leading-relaxed text-sm"
                                            :class="{
                                                'bg-green-600 text-white rounded-tr-sm': msg.sender_type === 'admin',
                                                'bg-background border rounded-tl-sm': msg.sender_type !== 'admin',
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
                                            <template v-if="msg.sender_type === 'admin'">
                                                {{ msg.content }}
                                            </template>
                                            <div v-else class="prose prose-sm dark:prose-invert max-w-none [&>p]:mb-1 [&>p:last-child]:mb-0 [&>ul]:mb-1 [&>ol]:mb-1" v-html="renderMarkdown(msg.content)"></div>
                                        </div>
                                        <!-- Read receipts for admin messages -->
                                        <div v-if="msg.sender_type === 'admin' && msg.id > 0" class="flex justify-end mt-0.5">
                                            <CheckCheck v-if="msg.read_at" class="h-3.5 w-3.5 text-blue-500" />
                                            <Check v-else class="h-3.5 w-3.5 text-gray-300" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- User typing indicator -->
                            <div v-if="userTyping" class="flex gap-3 mb-4">
                                <div class="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center shrink-0 shadow-sm">
                                    <User class="h-4 w-4" />
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

                <!-- Input -->
                <CardFooter class="p-4 border-t bg-background flex-col space-y-3">
                    <div v-if="selectedFile" class="flex items-center gap-3 p-2 bg-muted/50 rounded-lg w-full">
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
                                placeholder="Type your reply..."
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
                        <p v-if="conversationStatus === 'ai_active'" class="text-xs text-blue-600 dark:text-blue-400 text-center">
                            AI is responding to this user. Sending a message will take over the conversation.
                        </p>
                    </template>
                    <template v-else>
                        <div class="text-center w-full space-y-3 py-2">
                            <p class="text-sm text-muted-foreground">This conversation has been closed.</p>
                            <Button variant="outline" @click="reopenConversation">
                                <RotateCcw class="h-4 w-4 mr-1.5" />
                                Reopen Conversation
                            </Button>
                        </div>
                    </template>
                </CardFooter>
            </Card>
        </div>
        <ToastContainer />
    </AppLayout>
</template>
