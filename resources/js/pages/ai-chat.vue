
<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { MessageSquare, Send, Loader2, User, Bot, Paperclip, X, Sparkles } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import ShimmerCard from '@/components/ShimmerCard.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import ToastContainer from '@/components/ToastContainer.vue';

const messages = ref<Array<{ role: 'user' | 'assistant', content: string, media_path?: string }>>([]);
const input = ref('');
const loading = ref(false);
const shimmer = ref(false);
const { addToast } = useToast();
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const messagesScrollArea = ref<HTMLElement | null>(null);

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

const fetchHistory = async () => {
    shimmer.value = true;
    try {
        const response = await fetch('/ai-chat/history');
        const data = await response.json();
        if (data.success) {
            messages.value = data.history;
        } else {
            messages.value = [
                { role: 'assistant', content: 'Hi! I am your AI career assistant. How can I help you today?' }
            ];
        }
    } catch {
        messages.value = [
            { role: 'assistant', content: 'Hi! I am your AI career assistant. How can I help you today?' }
        ];
    } finally {
        shimmer.value = false;
        scrollToBottom();
    }
};

const sendMessage = async () => {
    if (!input.value.trim() && !selectedFile.value) return;
    if (loading.value) return;

    const userMsg = input.value.trim();
    const tempFile = selectedFile.value;
    const tempPreview = filePreview.value;

    // Optimistic update
    messages.value.push({ 
        role: 'user', 
        content: userMsg || 'Shared a file',
        media_path: tempPreview || undefined
    });
    input.value = '';
    clearFile();
    loading.value = true;
    scrollToBottom();

    try {
        const formData = new FormData();
        formData.append('message', userMsg || 'Analyze this');
        if (tempFile) {
            formData.append('media', tempFile);
        }

        const response = await fetch('/ai-chat', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: formData,
        });

        if (response.ok) {
            const data = await response.json();
            messages.value.pop(); // Remove optimistic
            if (data.success && data.user_message && data.assistant_message) {
                messages.value.push(data.user_message);
                messages.value.push(data.assistant_message);
                playNotificationSound();
            } else {
                messages.value.push({ role: 'assistant', content: data.message || 'Sorry, something went wrong. Please try again.' });
            }
        } else {
            messages.value.push({ role: 'assistant', content: `Error: ${response.status} ${response.statusText}` });
        }
    } catch (error) {
        messages.value.push({ role: 'assistant', content: 'Network error. Please check your connection.' });
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

watch(messages, () => scrollToBottom(), { deep: true });

onMounted(() => {
    fetchHistory();
});
</script>

<template>
    <Head title="AI Chat" />
    <AppLayout>
        <div class="min-h-screen flex flex-col bg-gradient-to-br from-primary/10 to-background/80">
            <div class="flex flex-col flex-1 w-full max-w-3xl mx-auto p-4 md:p-8 gap-6">
                <div class="flex items-center gap-2 mb-2">
                    <MessageSquare class="h-6 w-6 text-primary animate-pulse" />
                    <h1 class="text-2xl font-bold tracking-tight">AI Chat Assistant</h1>
                </div>
                <Card class="flex-1 flex flex-col shadow-xl border-primary/20 overflow-hidden">
                    <CardHeader class="p-4 border-b bg-muted/30 flex flex-row items-center gap-2 space-y-0">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <Sparkles class="h-4 w-4 text-primary animate-pulse" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <CardTitle class="text-base truncate leading-tight">
                                    Career Assistant
                                </CardTitle>
                                <CardDescription class="text-xs truncate">
                                    AI Companion
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="flex-1 p-0 overflow-hidden flex flex-col bg-muted/5 relative">
                        <div class="flex-1 p-4 overflow-y-auto scroll-smooth" ref="messagesScrollArea">
                            <div class="space-y-6 max-w-2xl mx-auto">
                                <template v-if="shimmer">
                                    <ShimmerCard :lines="4" :show-avatar="true" :show-title="true" />
                                    <ShimmerCard :lines="2" />
                                </template>
                                <template v-else>
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
                                        <div class="group relative space-y-2">
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
                                                <div v-if="msg.role === 'assistant'" class="prose prose-sm dark:prose-invert max-w-none prose-p:leading-relaxed prose-pre:bg-muted prose-pre:border">{{ msg.content }}</div>
                                                <div v-else class="text-sm font-medium">{{ msg.content }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="loading" class="flex gap-4">
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
                                </template>
                            </div>
                        </div>
                    </CardContent>
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
                                :disabled="loading"
                            >
                                <Paperclip class="h-4 w-4" />
                            </Button>
                            <div class="flex-1 relative">
                                <Input
                                    v-model="input"
                                    placeholder="Type your message..."
                                    class="pr-12 h-10 rounded-xl"
                                    :disabled="loading"
                                    @keyup.enter.prevent="sendMessage"
                                />
                                <div class="absolute right-1 top-1 bottom-1 flex items-center">
                                    <Button 
                                        type="submit" 
                                        size="icon" 
                                        class="h-8 w-8 rounded-lg"
                                        :disabled="loading || (!input.trim() && !selectedFile)"
                                    >
                                        <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
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
            </div>
            <ToastContainer />
        </div>
    </AppLayout>
</template>

<style scoped>
.prose p {
    margin-bottom: 0.5rem;
}
.prose p:last-child {
    margin-bottom: 0;
}
.prose ul {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
.prose ol {
    list-style-type: decimal;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
.prose pre {
    padding: 0.5rem;
    border-radius: 0.5rem;
    background: hsl(var(--muted));
    font-size: 0.75rem;
    overflow-x: auto;
}
</style>
