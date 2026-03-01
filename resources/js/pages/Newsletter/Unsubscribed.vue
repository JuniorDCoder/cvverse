<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { resubscribe } from '@/actions/App/Http/Controllers/NewsletterController';
import { home } from '@/routes';
import { useToast } from '@/composables/useToast';
import { MailX, ArrowLeft, RefreshCw, Loader2 } from 'lucide-vue-next';

interface Props {
    email: string;
}

const props = defineProps<Props>();
const { success, error } = useToast();
const isResubscribing = ref(false);

const handleResubscribe = async () => {
    isResubscribing.value = true;
    try {
        const response = await fetch(resubscribe.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(row => row.startsWith('XSRF-TOKEN='))?.split('=')[1] || ''),
            },
            body: JSON.stringify({ email: props.email }),
        });
        const data = await response.json();
        if (response.ok && data.success) {
            success(data.message, 'Re-subscribed!');
        } else {
            error(data.message || 'Failed to re-subscribe.', 'Error');
        }
    } catch {
        error('Network error. Please try again.', 'Error');
    } finally {
        isResubscribing.value = false;
    }
};
</script>

<template>
    <LandingLayout>
        <Head title="Unsubscribed" />

        <div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
            <div class="max-w-md w-full text-center space-y-6">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-muted">
                    <MailX class="h-10 w-10 text-muted-foreground" />
                </div>

                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">You've Been Unsubscribed</h1>
                    <p class="text-muted-foreground">
                        <strong>{{ email }}</strong> has been removed from our newsletter.
                        You will no longer receive email updates from us.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Button variant="outline" as-child>
                        <Link :href="home()">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Back to Home
                        </Link>
                    </Button>
                    <Button @click="handleResubscribe" :disabled="isResubscribing">
                        <Loader2 v-if="isResubscribing" class="h-4 w-4 mr-2 animate-spin" />
                        <RefreshCw v-else class="h-4 w-4 mr-2" />
                        {{ isResubscribing ? 'Re-subscribing...' : 'Re-subscribe' }}
                    </Button>
                </div>

                <p class="text-xs text-muted-foreground">
                    Unsubscribed by mistake? Click re-subscribe to get back on our list.
                </p>
            </div>
        </div>
    </LandingLayout>
</template>
