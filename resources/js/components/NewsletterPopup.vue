<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { subscribe } from '@/actions/App/Http/Controllers/NewsletterController';
import { useToast } from '@/composables/useToast';
import { Mail, Sparkles, X, Loader2, CheckCircle } from 'lucide-vue-next';

const STORAGE_KEY = 'cvverse_newsletter_dismissed';
const SHOW_DELAY_MS = 5000;

const page = usePage();
const { success } = useToast();

const showPopup = ref(false);
const email = ref('');
const name = ref('');
const isSubmitting = ref(false);
const isSubscribed = ref(false);
const errorMessage = ref('');

onMounted(() => {
    // Don't show if already dismissed or subscribed
    const dismissed = localStorage.getItem(STORAGE_KEY);
    if (dismissed) return;

    // Pre-fill email if user is authenticated
    const user = page.props.auth?.user as { email?: string; name?: string } | null;
    if (user?.email) {
        email.value = user.email;
    }
    if (user?.name) {
        name.value = user.name;
    }

    // Show after a delay so it doesn't interrupt the user immediately
    setTimeout(() => {
        showPopup.value = true;
    }, SHOW_DELAY_MS);
});

const dismissPopup = () => {
    showPopup.value = false;
    localStorage.setItem(STORAGE_KEY, Date.now().toString());
};

const handleSubscribe = async () => {
    if (!email.value.trim()) {
        errorMessage.value = 'Please enter your email address.';
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch(subscribe.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({
                email: email.value.trim(),
                name: name.value.trim() || null,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            isSubscribed.value = true;
            localStorage.setItem(STORAGE_KEY, Date.now().toString());
            success(data.message, 'Subscribed!');

            // Auto-close after showing success
            setTimeout(() => {
                showPopup.value = false;
            }, 3000);
        } else {
            errorMessage.value = data.message || 'Something went wrong. Please try again.';
        }
    } catch {
        errorMessage.value = 'Network error. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};

const handleOpenChange = (open: boolean) => {
    if (!open) {
        dismissPopup();
    }
};
</script>

<template>
    <Dialog :open="showPopup" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md p-0 overflow-hidden border-0 shadow-2xl">
            <!-- Success State -->
            <div v-if="isSubscribed" class="p-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <CheckCircle class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <h3 class="text-xl font-bold text-foreground mb-2">You're In!</h3>
                <p class="text-muted-foreground text-sm">
                    Check your inbox for a welcome email. We're excited to have you!
                </p>
            </div>

            <!-- Subscribe Form -->
            <template v-else>
                <!-- Gradient Header -->
                <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 px-6 pt-8 pb-6">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2230%22%20height%3D%2230%22%20viewBox%3D%220%200%2030%2030%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M0%2010h30M10%200v30%22%20stroke%3D%22rgba(255%2C255%2C255%2C0.08)%22%20fill%3D%22none%22%2F%3E%3C%2Fsvg%3E')] opacity-40" />
                    <div class="relative">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            <Mail class="h-6 w-6 text-white" />
                        </div>
                        <DialogHeader class="text-left space-y-1">
                            <DialogTitle class="text-xl font-bold text-white">
                                Stay Ahead in Your Career
                            </DialogTitle>
                            <DialogDescription class="text-white/80 text-sm">
                                Join thousands of professionals getting weekly career tips, new templates, and exclusive features.
                            </DialogDescription>
                        </DialogHeader>
                    </div>
                </div>

                <!-- Form Body -->
                <div class="px-6 pb-6 pt-5 space-y-4">
                    <!-- Benefits -->
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/5 px-3 py-1 text-xs font-medium text-primary dark:bg-primary/10">
                            <Sparkles class="h-3 w-3" />
                            New Templates
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/5 px-3 py-1 text-xs font-medium text-primary dark:bg-primary/10">
                            <Sparkles class="h-3 w-3" />
                            Career Tips
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/5 px-3 py-1 text-xs font-medium text-primary dark:bg-primary/10">
                            <Sparkles class="h-3 w-3" />
                            Exclusive Offers
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1.5">
                            <Label for="newsletter-name" class="text-xs font-medium">Name <span class="text-muted-foreground">(optional)</span></Label>
                            <Input
                                id="newsletter-name"
                                v-model="name"
                                placeholder="Your name"
                                class="h-10"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="newsletter-email" class="text-xs font-medium">Email Address *</Label>
                            <Input
                                id="newsletter-email"
                                type="email"
                                v-model="email"
                                placeholder="you@example.com"
                                class="h-10"
                                @keydown.enter="handleSubscribe"
                            />
                        </div>
                    </div>

                    <p v-if="errorMessage" class="text-xs text-destructive">{{ errorMessage }}</p>

                    <div class="flex flex-col gap-2">
                        <Button
                            @click="handleSubscribe"
                            :disabled="isSubmitting"
                            class="w-full h-10"
                        >
                            <Loader2 v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
                            <Mail v-else class="h-4 w-4 mr-2" />
                            {{ isSubmitting ? 'Subscribing...' : 'Subscribe — It\'s Free' }}
                        </Button>
                        <button
                            @click="dismissPopup"
                            class="text-xs text-muted-foreground hover:text-foreground transition-colors py-1"
                        >
                            No thanks, maybe later
                        </button>
                    </div>

                    <p class="text-[11px] text-muted-foreground/70 text-center leading-relaxed">
                        We respect your privacy. Unsubscribe anytime with one click.
                    </p>
                </div>
            </template>
        </DialogContent>
    </Dialog>
</template>
