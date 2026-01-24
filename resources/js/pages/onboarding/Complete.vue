<script setup lang="ts">
import { Button } from '@/components/ui/button';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';
import { usePage, Head, router } from '@inertiajs/vue3';
import { PartyPopper, FileText, Sparkles, Palette, Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isFinishing = ref(false);

const features = [
    {
        icon: FileText,
        title: 'Professional Templates',
        description: 'Access 50+ industry-specific templates designed to impress.',
    },
    {
        icon: Sparkles,
        title: 'AI Writing Assistant',
        description: 'Get smart suggestions to enhance your CV content.',
    },
    {
        icon: Palette,
        title: 'Customization',
        description: 'Personalize colors, fonts, and layouts to match your style.',
    },
    {
        icon: Download,
        title: 'Multi-format Export',
        description: 'Download as PDF, DOCX, or share a live link.',
    },
];

const finish = () => {
    isFinishing.value = true;
    router.post('/onboarding/finish');
};
</script>

<template>
    <OnboardingLayout :step="3" :total-steps="3">
        <Head title="You're All Set!" />

        <div class="text-center">
            <!-- Success illustration -->
            <div class="mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center animate-bounce">
                    <PartyPopper class="w-12 h-12 text-green-500" />
                </div>
            </div>

            <!-- Success message -->
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">
                You're all set! 🎉
            </h1>
            <p class="text-lg text-muted-foreground mb-8 max-w-md mx-auto">
                Great job, {{ user?.name?.split(' ')[0] }}! Your profile is ready. Now let's create a CV that lands you your dream job.
            </p>

            <!-- What's next -->
            <div class="bg-muted/50 rounded-xl p-6 mb-8 text-left">
                <h3 class="font-semibold mb-4 text-center">What you can do now:</h3>
                <div class="grid gap-3">
                    <div 
                        v-for="feature in features" 
                        :key="feature.title"
                        class="flex items-start gap-3"
                    >
                        <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                            <component :is="feature.icon" class="w-4 h-4" />
                        </div>
                        <div>
                            <h4 class="font-medium text-sm">{{ feature.title }}</h4>
                            <p class="text-xs text-muted-foreground">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <Button 
                size="lg" 
                class="w-full sm:w-auto px-8"
                @click="finish"
                :disabled="isFinishing"
            >
                <span v-if="isFinishing">Taking you to dashboard...</span>
                <span v-else class="flex items-center">
                    Go to Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </Button>
        </div>
    </OnboardingLayout>
</template>
