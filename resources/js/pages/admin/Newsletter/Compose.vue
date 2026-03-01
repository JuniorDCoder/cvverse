<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ArrowLeft, Send, Sparkles, Loader2, Eye, Users, Mail, RefreshCw,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from '@/composables/useToast';
import { generateEmail, send } from '@/actions/App/Http/Controllers/Admin/AdminNewsletterController';

interface Props {
    stats: {
        active_subscribers: number;
        total_users: number;
    };
}

const props = defineProps<Props>();
const { success, error } = useToast();

const subject = ref('');
const body = ref('');
const audience = ref('subscribers');
const aiPrompt = ref('');
const aiTone = ref('professional');
const isGenerating = ref(false);
const showPreview = ref(false);
const showConfirmSend = ref(false);
const isSending = ref(false);

const recipientCount = () => {
    if (audience.value === 'subscribers') return props.stats.active_subscribers;
    if (audience.value === 'users') return props.stats.total_users;
    return props.stats.active_subscribers + props.stats.total_users;
};

const generateWithAi = async () => {
    if (!aiPrompt.value.trim()) return;
    isGenerating.value = true;

    try {
        const response = await fetch(generateEmail.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                prompt: aiPrompt.value,
                tone: aiTone.value,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            subject.value = data.subject;
            body.value = data.body;
            success('Email content generated successfully!', 'AI Generated');
        } else {
            error(data.message || 'Failed to generate email.', 'Error');
        }
    } catch {
        error('Network error. Please try again.', 'Error');
    } finally {
        isGenerating.value = false;
    }
};

const openSendConfirmation = () => {
    if (!subject.value.trim() || !body.value.trim()) {
        error('Please fill in both the subject and body.', 'Missing Content');
        return;
    }
    showConfirmSend.value = true;
};

const confirmSend = () => {
    isSending.value = true;

    router.post(send.url(), {
        subject: subject.value,
        body: body.value,
        audience: audience.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmSend.value = false;
            isSending.value = false;
            success(`Newsletter sent to ${recipientCount()} recipients!`, 'Sent');
            subject.value = '';
            body.value = '';
            aiPrompt.value = '';
        },
        onError: () => {
            isSending.value = false;
            error('Failed to send newsletter.', 'Error');
        },
    });
};

const previewHtml = () => {
    // Convert markdown-ish to basic HTML for preview
    let html = body.value
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^- (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>')
        .replace(/\n\n/g, '<br><br>')
        .replace(/\n/g, '<br>');
    return html;
};
</script>

<template>
    <AppLayout>
        <Head title="Compose Newsletter" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/admin/newsletter" class="text-muted-foreground hover:text-foreground transition-colors">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Compose Newsletter</h1>
                    <p class="text-muted-foreground">Write or generate an email to send to your audience.</p>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Main Editor -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- AI Generator Card -->
                    <Card class="border-primary/20 bg-gradient-to-br from-primary/5 to-transparent">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <Sparkles class="h-5 w-5 text-primary" />
                                AI Email Generator
                            </CardTitle>
                            <CardDescription>Describe what you want to write and AI will generate the email for you.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <Textarea
                                        v-model="aiPrompt"
                                        placeholder="e.g., Announce our new professional CV templates with a 20% launch discount..."
                                        class="min-h-[80px] resize-none"
                                        @keydown.meta.enter="generateWithAi"
                                    />
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Select v-model="aiTone">
                                    <SelectTrigger class="w-40">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="professional">Professional</SelectItem>
                                        <SelectItem value="friendly">Friendly</SelectItem>
                                        <SelectItem value="casual">Casual</SelectItem>
                                        <SelectItem value="urgent">Urgent</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button
                                    @click="generateWithAi"
                                    :disabled="isGenerating || !aiPrompt.trim()"
                                    class="bg-gradient-to-r from-primary to-primary/80"
                                >
                                    <Loader2 v-if="isGenerating" class="h-4 w-4 mr-2 animate-spin" />
                                    <Sparkles v-else class="h-4 w-4 mr-2" />
                                    {{ isGenerating ? 'Generating...' : 'Generate with AI' }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Email Content -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Email Content</CardTitle>
                            <CardDescription>Write your email in Markdown format. It will be rendered beautifully for recipients.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="subject">Subject Line *</Label>
                                <Input
                                    id="subject"
                                    v-model="subject"
                                    placeholder="Your email subject..."
                                    class="text-lg font-medium"
                                />
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label for="body">Email Body *</Label>
                                    <Button
                                        v-if="body"
                                        variant="ghost"
                                        size="sm"
                                        @click="showPreview = true"
                                    >
                                        <Eye class="h-4 w-4 mr-1.5" />
                                        Preview
                                    </Button>
                                </div>
                                <Textarea
                                    id="body"
                                    v-model="body"
                                    placeholder="Write your email content here (Markdown supported)..."
                                    class="min-h-[300px] font-mono text-sm"
                                />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Audience Selector -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Audience</CardTitle>
                            <CardDescription>Who should receive this email?</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <label
                                v-for="option in [
                                    { value: 'subscribers', label: 'Newsletter Subscribers', count: stats.active_subscribers, icon: Mail },
                                    { value: 'users', label: 'Registered Users', count: stats.total_users, icon: Users },
                                    { value: 'all', label: 'Everyone', count: stats.active_subscribers + stats.total_users, icon: Users },
                                ]"
                                :key="option.value"
                                class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                                :class="audience === option.value
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:bg-muted/50'"
                            >
                                <input
                                    type="radio"
                                    :value="option.value"
                                    v-model="audience"
                                    class="rounded-full border-gray-300 text-primary focus:ring-primary"
                                />
                                <component :is="option.icon" class="h-4 w-4 text-muted-foreground shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium">{{ option.label }}</p>
                                    <p class="text-xs text-muted-foreground">{{ option.count }} recipients</p>
                                </div>
                            </label>
                        </CardContent>
                    </Card>

                    <!-- Send Card -->
                    <Card>
                        <CardContent class="p-4 space-y-4">
                            <div class="text-center space-y-2">
                                <div class="flex items-center justify-center gap-2">
                                    <Send class="h-5 w-5 text-primary" />
                                    <span class="text-2xl font-bold">{{ recipientCount() }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">recipients will receive this email</p>
                            </div>
                            <Separator />
                            <Button
                                @click="openSendConfirmation"
                                :disabled="!subject.trim() || !body.trim()"
                                class="w-full"
                                size="lg"
                            >
                                <Send class="h-4 w-4 mr-2" />
                                Send Newsletter
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Tips -->
                    <Card class="bg-muted/30">
                        <CardContent class="p-4">
                            <h4 class="text-sm font-medium mb-2">Writing Tips</h4>
                            <ul class="space-y-1.5 text-xs text-muted-foreground">
                                <li>Use **bold** for emphasis</li>
                                <li>Use # for headings</li>
                                <li>Keep subject lines under 50 characters</li>
                                <li>Include a clear call-to-action</li>
                                <li>Press <Badge variant="outline" class="text-[10px] px-1">Cmd+Enter</Badge> to generate with AI</li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Preview Dialog -->
        <Dialog v-model:open="showPreview">
            <DialogContent class="sm:max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Email Preview</DialogTitle>
                    <DialogDescription>This is a rough preview of how your email will look.</DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="border rounded-lg p-4 bg-muted/30">
                        <p class="text-xs text-muted-foreground mb-1">Subject</p>
                        <p class="font-semibold">{{ subject }}</p>
                    </div>
                    <div class="border rounded-lg p-6 prose prose-sm dark:prose-invert max-w-none" v-html="previewHtml()" />
                </div>
                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="outline">Close</Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Send Confirmation Dialog -->
        <Dialog v-model:open="showConfirmSend">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Send class="h-5 w-5 text-primary" />
                        Confirm Send
                    </DialogTitle>
                    <DialogDescription>
                        You are about to send this newsletter to <strong>{{ recipientCount() }}</strong> recipients. This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <div class="rounded-lg bg-muted/50 p-4 space-y-2">
                    <div>
                        <p class="text-xs text-muted-foreground">Subject</p>
                        <p class="text-sm font-medium">{{ subject }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Audience</p>
                        <p class="text-sm font-medium capitalize">{{ audience === 'all' ? 'Everyone' : audience }}</p>
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="outline">Cancel</Button>
                    </DialogClose>
                    <Button @click="confirmSend" :disabled="isSending">
                        <Loader2 v-if="isSending" class="h-4 w-4 mr-2 animate-spin" />
                        <Send v-else class="h-4 w-4 mr-2" />
                        {{ isSending ? 'Sending...' : 'Send Now' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
