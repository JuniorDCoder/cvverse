<script setup lang="ts">
import { ref, watch, nextTick, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { MessageSquare, Send, X, Bot, User, Loader2, Sparkles, Minus } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { marked } from 'marked';

const page = usePage();

// Detect context from page props
const cvContext = computed(() => page.props.cv as any);
const cvId = computed(() => cvContext.value?.id);

const isOpen = ref(false);
const isMinimized = ref(false);
const message = ref('');
const isLoading = ref(false);
const messages = ref<Array<{ role: 'user' | 'assistant', content: string }>>([]);

// Default welcome message based on context
const welcomeMessage = computed(() => { 
    if (cvId.value) {
        return 'Hi! I can help you edit this CV. Ask me to change sections, rewrite summaries, or improve skills.';
    }
    return 'Hi! I am your career assistant. Ask me anything about your job search, CVs, or career advice.';
});

const initialMessage = { role: 'assistant' as const, content: welcomeMessage.value };

// Initialize messages
watch(() => cvId.value, () => {
    // Reset or contextualize when navigating between CVs or pages
    if (messages.value.length === 0 || messages.value.length === 1) {
         messages.value = [{ role: 'assistant', content: welcomeMessage.value }];
    }
}, { immediate: true });


const messagesContainer = ref<HTMLElement | null>(null);

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        // Use the div for scrolling
        const scrollDiv = messagesContainer.value.querySelector('.overflow-y-auto');
        if (scrollDiv) {
            scrollDiv.scrollTop = scrollDiv.scrollHeight;
        }
    }
};

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    isMinimized.value = false;
    if (isOpen.value) scrollToBottom();
};

const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value;
};

// Sound notification
const playNotificationSound = () => {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        // Check if sound file exists or just use a placeholder/standard beep if permissible.
        // For now, let's assume valid file or fail silently. 
        // Better: simple beep using Web Audio API to avoid file dependency errors?
        // User asked for "cool sound notifications", implying a file.
        // I will use a simple reliable approach: create a short beep with Web Audio API.
        const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = 600;
        gain.gain.value = 0.1;
        osc.start();
        setTimeout(() => osc.stop(), 200);
    } catch (e) {
        // Audio might be blocked
    }
};

const renderMarkdown = (text: string) => {
    return marked(text);
};

const sendMessage = async () => {
    if (!message.value.trim() || isLoading.value) return;

    const userMsg = message.value.trim();
    messages.value.push({ role: 'user', content: userMsg });
    message.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        // Determine endpoint based on context
        const endpoint = cvId.value 
            ? `/cvs/${cvId.value}/chat` 
            : `/dashboard/chat`;

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                message: userMsg,
                history: messages.value.slice(0, -1).map(m => ({
                    role: m.role,
                    parts: [{ text: m.content }]
                }))
            }),
        });

        const data = await response.json();

        if (data.success) {
            messages.value.push({ role: 'assistant', content: data.message });
            playNotificationSound();
            
            if (data.updated_cv) {
                // Refresh the CV data in the parent component
                router.reload({ only: ['cv'], preserveScroll: true } as any);
            }
        } else {
            messages.value.push({ role: 'assistant', content: 'Sorry, something went wrong. Please try again.' });
        }
    } catch (error) {
        messages.value.push({ role: 'assistant', content: 'Network error. Please try again.' });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

watch(isOpen, (val) => {
    if (val) scrollToBottom();
});

defineExpose({ toggleChat });
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2 print:hidden">
        <!-- Chat Window -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <Card v-if="isOpen && !isMinimized" class="w-[350px] md:w-[400px] shadow-xl border-primary/20 flex flex-col h-[500px]">
                <CardHeader class="p-4 border-b bg-muted/30 flex flex-row items-center justify-between space-y-0">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                            <Sparkles class="h-4 w-4 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-base">{{ cvId ? 'CV Editor' : 'Career Assistant' }}</CardTitle>
                            <CardDescription class="text-xs">{{ cvId ? 'Editing ' + cvContext.name : 'Ask me anything' }}</CardDescription>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="toggleMinimize">
                            <Minus class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="isOpen = false">
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="flex-1 p-0 overflow-hidden" ref="messagesContainer">
                    <div class="h-full overflow-y-auto p-4">
                        <div class="space-y-4">
                            <div
                                v-for="(msg, index) in messages"
                                :key="index"
                                class="flex gap-3 text-sm"
                                :class="{ 'flex-row-reverse': msg.role === 'user' }"
                            >
                                <div
                                    class="h-8 w-8 rounded-full flex items-center justify-center shrink-0"
                                    :class="msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted'"
                                >
                                    <User v-if="msg.role === 'user'" class="h-4 w-4" />
                                    <Bot v-else class="h-4 w-4" />
                                </div>
                                <div
                                    class="rounded-lg p-3 max-w-[85%]"
                                    :class="msg.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted'"
                                >
                                    <!-- Markdown rendering -->
                                    <div v-if="msg.role === 'assistant'" v-html="renderMarkdown(msg.content)" class="prose prose-sm dark:prose-invert max-w-none"></div>
                                    <div v-else>{{ msg.content }}</div>
                                </div>
                            </div>
                            <div v-if="isLoading" class="flex gap-3 text-sm">
                                <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center shrink-0">
                                    <Bot class="h-4 w-4" />
                                </div>
                                <div class="bg-muted rounded-lg p-3">
                                    <Loader2 class="h-4 w-4 animate-spin" />
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
                <CardFooter class="p-3 border-t">
                    <form @submit.prevent="sendMessage" class="flex w-full gap-2">
                        <Input
                            v-model="message"
                            placeholder="Type a message..."
                            class="flex-1"
                            :disabled="isLoading"
                        />
                        <Button type="submit" size="icon" :disabled="isLoading || !message.trim()">
                            <Send class="h-4 w-4" />
                        </Button>
                    </form>
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
             <Card v-if="isOpen && isMinimized" class="w-[300px] shadow-lg border-primary/20 cursor-pointer" @click="toggleMinimize">
                <CardHeader class="p-3 flex flex-row items-center justify-between space-y-0">
                    <div class="flex items-center gap-2">
                        <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center">
                            <Sparkles class="h-3 w-3 text-primary" />
                        </div>
                        <span class="font-medium text-sm">AI {{ cvId ? 'Editor' : 'Assistant' }} (Minimized)</span>
                    </div>
                     <Button variant="ghost" size="icon" class="h-6 w-6" @click.stop="isOpen = false">
                        <X class="h-3 w-3" />
                    </Button>
                </CardHeader>
             </Card>
        </transition>

        <!-- Toggle Button -->
        <Button
            v-if="!isOpen"
            size="lg"
            class="rounded-full shadow-lg h-14 w-14 p-0 animate-in fade-in zoom-in duration-300"
            @click="toggleChat"
        >
            <MessageSquare class="h-6 w-6" />
        </Button>
    </div>
</template>

<style scoped>
/* Ensure prose styles don't break layout */
:deep(.prose p) {
    margin-bottom: 0.5em;
}
:deep(.prose p:last-child) {
    margin-bottom: 0;
}
:deep(.prose ul) {
    list-style-type: disc;
    padding-left: 1.25em;
    margin-bottom: 0.5em;
}
:deep(.prose ol) {
    list-style-type: decimal;
    padding-left: 1.25em;
    margin-bottom: 0.5em;
}
</style>
