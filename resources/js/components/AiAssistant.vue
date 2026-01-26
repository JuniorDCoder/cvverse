<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { 
    MessageSquare, 
    Send, 
    X, 
    Bot, 
    User, 
    Loader2, 
    Sparkles, 
    Minus, 
    Maximize2, 
    Minimize2, 
    History, 
    Paperclip, 
    Image as ImageIcon,
    Trash2,
    Plus
} from 'lucide-vue-next';
import { marked } from 'marked';
import { ref, watch, nextTick, computed, onMounted } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useToast } from '@/composables/useToast';

const page = usePage();

// Detect context from page props
const cvContext = computed(() => page.props.cv as any);
const jobContext = computed(() => page.props.application as any);
const coverLetterContext = computed(() => page.props.coverLetter as any);

const resourceContext = computed(() => {
    if (cvContext.value) return { type: 'cv', id: cvContext.value.id, name: cvContext.value.name, label: 'CV Editor' };
    if (jobContext.value) return { type: 'job', id: jobContext.value.id, name: jobContext.value.title, label: 'Job Assistant' };
    if (coverLetterContext.value) return { type: 'cover_letter', id: coverLetterContext.value.id, name: coverLetterContext.value.name, label: 'Letter Assistant' };
    return null;
});

const isOpen = ref(false);
const isMinimized = ref(false);
const isFullscreen = ref(false);
const showHistory = ref(false);
const message = ref('');
const isLoading = ref(false);
const isHistoryLoading = ref(false);
const messages = ref<Array<{ role: 'user' | 'assistant', content: string, media_path?: string }>>([]);
const sessions = ref<Array<any>>([]);
const currentSessionId = ref<number | null>(null);
const { addToast } = useToast();
const deleteSessionId = ref<number | null>(null);
const isDeleteModalOpen = ref(false);

// Media Upload
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);

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

// Default welcome message based on context
const welcomeMessage = computed(() => { 
    if (resourceContext.value) {
        return `Hi! I'm in **${resourceContext.value.label}** mode. I can help you edit this ${resourceContext.value.type.replace('_', ' ')} directly. Just ask me to "change the title" or "improve the description"!`;
    }
    return 'Hi! I am your career assistant. Ask me anything about your job search, CVs, or career advice.';
});

const scrollToBottom = async () => {
    await nextTick();
    if (messagesScrollArea.value) {
        messagesScrollArea.value.scrollTop = messagesScrollArea.value.scrollHeight;
    }
};

const messagesScrollArea = ref<HTMLElement | null>(null);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    isMinimized.value = false;
    if (isOpen.value) {
        fetchSessions();
        scrollToBottom();
    }
};

const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value;
    if (isFullscreen.value) isFullscreen.value = false;
};

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    if (isFullscreen.value) isMinimized.value = false;
};

const fetchSessions = async () => {
    isHistoryLoading.value = true;
    try {
        const response = await fetch('/chat/sessions');
        const data = await response.json();
        if (data.success) {
            sessions.value = data.sessions;
        }
    } finally {
        isHistoryLoading.value = false;
    }
};

const loadSession = async (sessionId: number) => {
    isLoading.value = true;
    currentSessionId.value = sessionId;
    showHistory.value = false;
    try {
        const response = await fetch(`/chat/sessions/${sessionId}`);
        const data = await response.json();
        if (data.success) {
            messages.value = data.messages;
            scrollToBottom();
        }
    } finally {
        isLoading.value = false;
    }
};

const startNewChat = () => {
    currentSessionId.value = null;
    messages.value = [{ role: 'assistant', content: welcomeMessage.value }];
    showHistory.value = false;
    scrollToBottom();
};

const confirmDeleteSession = (sessionId: number) => {
    deleteSessionId.value = sessionId;
    isDeleteModalOpen.value = true;
};

const deleteSession = async () => {
    if (!deleteSessionId.value) return;
    
    const sessionId = deleteSessionId.value;
    try {
        const response = await fetch(`/chat/sessions/${sessionId}`, {
            method: 'DELETE',
            headers: {
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
        });
        if (response.ok) {
            sessions.value = sessions.value.filter(s => s.id !== sessionId);
            if (currentSessionId.value === sessionId) {
                startNewChat();
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        deleteSessionId.value = null;
        isDeleteModalOpen.value = false;
    }
};

// Sound notification
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

const renderMarkdown = (text: any) => {
    if (!text) return '';
    if (typeof text !== 'string') return marked(JSON.stringify(text));
    return marked(text);
};

const sendMessage = async () => {
    if (!message.value.trim() && !selectedFile.value) return;
    if (isLoading.value) return;

    const userMsg = message.value.trim();
    const tempFile = selectedFile.value;
    const tempPreview = filePreview.value;

    // Optimistic Update
    messages.value.push({ 
        role: 'user', 
        content: userMsg || 'Shared a file',
        media_path: tempPreview || undefined
    });
    
    message.value = '';
    clearFile();
    isLoading.value = true;
    scrollToBottom();

    try {
        const formData = new FormData();
        formData.append('message', userMsg || 'Analyze this');
        if (currentSessionId.value) {
            formData.append('session_id', currentSessionId.value.toString());
        } else if (resourceContext.value) {
            formData.append('context_type', resourceContext.value.type);
            formData.append('context_id', resourceContext.value.id.toString());
        }
        
        if (tempFile) {
            formData.append('media', tempFile);
        }

        const response = await fetch('/chat/messages', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: formData,
        });

        if (response.ok) {
            const data = await response.json();

            if (data.success) {
                // Update session if it was new
                if (!currentSessionId.value) {
                    currentSessionId.value = data.session.id;
                    fetchSessions();
                }
                
                messages.value.pop(); // Remove optimistic and use real messages
                messages.value.push(data.user_message);
                messages.value.push(data.assistant_message);
                
                if (data.edit_applied) {
                    addToast({
                        title: 'Edit Applied',
                        message: data.changes_summary || 'Your changes have been saved.',
                        type: 'success'
                    });
                    window.dispatchEvent(new CustomEvent('ai-edit-applied', { detail: data }));
                    router.reload({ preserveScroll: true });
                }

                playNotificationSound();
            } else {
                messages.value.push({ role: 'assistant', content: data.message || 'Sorry, something went wrong. Please try again.' });
            }
        } else {
            console.error('AI Chat Error:', response.status, response.statusText);
            const text = await response.text();
            console.error('Server Response:', text);
            messages.value.push({ role: 'assistant', content: `Error: ${response.status} ${response.statusText}. Check console for details.` });
        }
    } catch (error) {
        console.error('Network/JS Error:', error);
        messages.value.push({ role: 'assistant', content: 'Network error. Please check your connection and console.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

watch(messages, () => {
    scrollToBottom();
}, { deep: true });

onMounted(() => {
    if (messages.value.length === 0) {
        messages.value = [{ role: 'assistant', content: welcomeMessage.value }];
    }
});

defineExpose({ toggleChat });
</script>

<template>
    <div 
        class="fixed z-50 flex flex-col items-end gap-2 print:hidden transition-all duration-300"
        :class="[
            isFullscreen ? 'inset-0 items-center justify-center bg-black/40 backdrop-blur-sm p-4 md:p-10' : 'bottom-6 right-6'
        ]"
    >
        <!-- Chat Window -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <Card 
                v-if="isOpen && !isMinimized" 
                class="shadow-2xl border-primary/20 flex flex-col transition-all duration-300 overflow-hidden glass-morphism"
                :class="[
                    isFullscreen ? 'w-full h-full max-w-5xl' : 'w-[350px] md:w-[450px] h-[600px]'
                ]"
            >
                <CardHeader class="p-3 border-b bg-muted/30 flex flex-row items-center justify-between gap-2 space-y-0 overflow-hidden">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <Sparkles class="h-4 w-4 text-primary animate-pulse" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <CardTitle class="text-sm md:text-base truncate leading-tight">
                                    {{ resourceContext ? resourceContext.label : 'Career Assistant' }}
                                </CardTitle>
                                <Badge v-if="resourceContext" variant="outline" class="h-4 px-1 text-[8px] bg-primary/10 text-primary border-primary/20 animate-pulse">
                                    LIVE EDIT
                                </Badge>
                            </div>
                            <CardDescription class="text-[10px] md:text-xs truncate">
                                {{ resourceContext ? 'Editing: ' + resourceContext.name : 'AI Companion' }}
                            </CardDescription>
                        </div>
                    </div>
                    <div class="flex items-center gap-0.5 shrink-0">
                        <TooltipProvider :delay-duration="300">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="showHistory = !showHistory">
                                        <History class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>History</TooltipContent>
                            </Tooltip>
                            
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleFullscreen">
                                        <Minimize2 v-if="isFullscreen" class="h-4 w-4" />
                                        <Maximize2 v-else class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>{{ isFullscreen ? 'Exit' : 'Fullscreen' }}</TooltipContent>
                            </Tooltip>

                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleMinimize">
                                        <Minus class="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>Minimize</TooltipContent>
                            </Tooltip>

                            <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:bg-destructive/10" @click="isOpen = false; isFullscreen = false">
                                <X class="h-4 w-4" />
                            </Button>
                        </TooltipProvider>
                    </div>
                </CardHeader>

                <div class="flex flex-1 overflow-hidden relative">
                    <!-- History Sidebar/Overlay -->
                    <transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="-translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transition ease-in duration-200"
                        leave-from-class="translate-x-0"
                        leave-to-class="-translate-x-full"
                    >
                        <div v-if="showHistory" class="absolute inset-y-0 left-0 w-64 bg-background border-r z-20 shadow-xl flex flex-col">
                            <div class="p-4 border-b flex items-center justify-between">
                                <h3 class="font-semibold text-sm">Chat History</h3>
                                <Button variant="ghost" size="icon" class="h-8 w-8" @click="showHistory = false">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <div class="p-2">
                                <Button variant="outline" class="w-full justify-start gap-2" size="sm" @click="startNewChat">
                                    <Plus class="h-4 w-4" />
                                    New Chat
                                </Button>
                            </div>
                            <div class="flex-1 overflow-y-auto">
                                <div class="p-2 space-y-1">
                                    <div v-if="isHistoryLoading" class="flex justify-center py-4">
                                        <Loader2 class="h-4 w-4 animate-spin text-muted-foreground" />
                                    </div>
                                    <div 
                                        v-for="s in sessions" 
                                        :key="s.id"
                                        class="group flex items-center justify-between p-2 rounded-md hover:bg-muted cursor-pointer transition-colors"
                                        :class="{ 'bg-primary/5 border border-primary/20': currentSessionId === s.id }"
                                        @click="loadSession(s.id)"
                                    >
                                        <p class="text-xs truncate flex-1 pr-2">{{ s.title }}</p>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-6 w-6 opacity-0 group-hover:opacity-100 text-destructive hover:bg-destructive/10"
                                            @click.stop="confirmDeleteSession(s.id)"
                                        >
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                    <div v-if="!isHistoryLoading && sessions.length === 0" class="text-center py-8 text-xs text-muted-foreground">
                                        No recent chats
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <!-- Messages Section -->
                    <CardContent class="flex-1 p-0 overflow-hidden flex flex-col bg-muted/5 relative">
                        <!-- Context Badge for UX -->
                        <div v-if="resourceContext && messages.length < 5" class="px-4 py-2 bg-primary/5 border-b flex items-center gap-2 animate-in fade-in slide-in-from-top-1 duration-500">
                             <Sparkles class="h-3 w-3 text-primary" />
                             <p class="text-[10px] font-medium text-primary uppercase tracking-wider">
                                Powerful Tip: You can ask me to edit this {{ resourceContext.type.replace('_', ' ') }} directly!
                             </p>
                        </div>
                        <div class="flex-1 p-4 overflow-y-auto scroll-smooth" ref="messagesScrollArea">
                            <div class="space-y-6 max-w-3xl mx-auto">
                                <div
                                    v-for="(msg, index) in messages"
                                    :key="index"
                                    class="flex gap-4 animate-in fade-in slide-in-from-bottom-2 duration-300"
                                    :class="[
                                        msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'
                                    ]"
                                >
                                    <div
                                        class="h-9 w-9 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                                        :class="msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-background border'"
                                    >
                                        <User v-if="msg.role === 'user'" class="h-5 w-5" />
                                        <Bot v-else class="h-5 w-5 text-primary" />
                                    </div>
                                    <div
                                        class="group relative space-y-2"
                                        :class="msg.role === 'user' ? 'items-end' : 'items-start'"
                                    >
                                        <div
                                            class="rounded-2xl px-4 py-3 shadow-sm select-text whitespace-pre-wrap leading-relaxed"
                                            :class="[
                                                msg.role === 'user' 
                                                    ? 'bg-primary text-primary-foreground rounded-tr-none' 
                                                    : 'bg-background border rounded-tl-none'
                                            ]"
                                        >
                                            <!-- Media Preview in Message -->
                                            <div v-if="msg.media_path" class="mb-2 max-w-xs overflow-hidden rounded-lg border">
                                                <img 
                                                    v-if="msg.media_path.match(/\.(jpg|jpeg|png|webp)/i)" 
                                                    :src="msg.media_path.startsWith('blob:') ? msg.media_path : `/storage/${msg.media_path}`" 
                                                    class="w-full h-auto object-cover max-h-48"
                                                />
                                                <div v-else class="p-3 bg-muted flex items-center gap-2">
                                                    <Paperclip class="h-4 w-4" />
                                                    <span class="text-xs truncate">Document Shared</span>
                                                </div>
                                            </div>

                                            <div 
                                                v-if="msg.role === 'assistant'" 
                                                v-html="renderMarkdown(msg.content)" 
                                                class="prose prose-sm dark:prose-invert max-w-none prose-p:leading-relaxed prose-pre:bg-muted prose-pre:border"
                                            ></div>
                                            <div v-else class="text-sm font-medium">{{ msg.content }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="isLoading" class="flex gap-4">
                                    <div class="h-9 w-9 rounded-xl bg-background border flex items-center justify-center shrink-0">
                                        <Bot class="h-5 w-5 text-primary" />
                                    </div>
                                    <div class="bg-background border rounded-2xl rounded-tl-none p-4 shadow-sm">
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
                </div>

                <!-- Footer / Input -->
                <CardFooter class="p-4 border-t bg-background space-y-4 flex-col">
                    <!-- File Preview Area -->
                    <div v-if="selectedFile" class="flex items-center gap-3 p-2 bg-muted/50 rounded-lg w-full animate-in slide-in-from-bottom-1 duration-200">
                        <div class="h-12 w-12 rounded border bg-background overflow-hidden shrink-0">
                            <img v-if="filePreview" :src="filePreview" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <Paperclip class="h-5 w-5 text-muted-foreground" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium truncate">{{ selectedFile.name }}</p>
                            <p class="text-[10px] text-muted-foreground uppercase">{{ (selectedFile.size / 1024).toFixed(1) }} KB</p>
                        </div>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="clearFile">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>

                    <form @submit.prevent="sendMessage" class="flex w-full gap-2 items-end">
                        <input 
                            type="file" 
                            ref="fileInput" 
                            class="hidden" 
                            accept="image/*,.pdf" 
                            @change="handleFileChange"
                        />
                        <Button 
                            type="button" 
                            variant="outline" 
                            size="icon" 
                            class="shrink-0 h-10 w-10" 
                            @click="triggerFileInput"
                            :disabled="isLoading"
                        >
                            <Paperclip class="h-4 w-4" />
                        </Button>

                        <div class="flex-1 relative">
                            <Input
                                v-model="message"
                                placeholder="Type your message..."
                                class="pr-12 h-10 rounded-xl"
                                :disabled="isLoading"
                                @keyup.enter.prevent="sendMessage"
                            />
                            <div class="absolute right-1 top-1 bottom-1 flex items-center">
                                <Button 
                                    type="submit" 
                                    size="icon" 
                                    class="h-8 w-8 rounded-lg"
                                    :disabled="isLoading || (!message.trim() && !selectedFile)"
                                >
                                    <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
                                    <Send v-else class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </form>
                    <p class="text-[10px] text-muted-foreground text-center">
                        AI can make mistakes. Verify important info.
                    </p>
                </CardFooter>
            </Card>
        </transition>

        <!-- Minimized State -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
             <Card 
                v-if="isOpen && isMinimized" 
                class="w-[300px] shadow-lg border-primary/20 cursor-pointer hover:shadow-xl transition-all glass-morphism" 
                @click="toggleMinimize"
            >
                <CardHeader class="p-3 flex flex-row items-center justify-between space-y-0">
                    <div class="flex items-center gap-2">
                        <div class="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center shadow-inner">
                            <Sparkles class="h-4 w-4 text-primary" />
                        </div>
                        <span class="font-medium text-sm">AI Assistant</span>
                    </div>
                     <Button variant="ghost" size="icon" class="h-7 w-7 hover:bg-destructive/10 hover:text-destructive" @click.stop="isOpen = false">
                        <X class="h-4 w-4" />
                    </Button>
                </CardHeader>
             </Card>
        </transition>

        <!-- Toggle Button -->
        <Button
            v-if="!isOpen"
            size="lg"
            class="rounded-full shadow-2xl h-16 w-16 p-0 animate-in fade-in zoom-in duration-500 hover:scale-110 active:scale-95 transition-all bg-gradient-to-br from-primary to-primary/80"
            @click="toggleChat"
        >
            <MessageSquare class="h-7 w-7" />
        </Button>
    </div>

    <ConfirmDeleteModal
        v-model:open="isDeleteModalOpen"
        title="Delete Chat History"
        description="Are you sure you want to delete this chat history? This action cannot be undone."
        @confirm="deleteSession"
    />
</template>

<style scoped>
.glass-morphism {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.dark .glass-morphism {
    background: rgba(0, 0, 0, 0.7);
}

/* Ensure prose styles don't break layout */
:deep(.prose p) {
    margin-bottom: 0.5rem;
}
:deep(.prose p:last-child) {
    margin-bottom: 0;
}
:deep(.prose ul) {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
:deep(.prose ol) {
    list-style-type: decimal;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
:deep(.prose pre) {
    padding: 0.5rem;
    border-radius: 0.5rem;
    background: hsl(var(--muted));
    font-size: 0.75rem;
    overflow-x: auto;
}
</style>
