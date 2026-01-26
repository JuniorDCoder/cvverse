<script setup lang="ts">
import { usePage, Head, Link } from '@inertiajs/vue3';
import { Sparkles, FileText, Target, Zap } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const benefits = [
    {
        icon: FileText,
        title: 'Personalized Templates',
        description: 'Get template recommendations based on your industry and experience.',
    },
    {
        icon: Target,
        title: 'Targeted Content',
        description: 'AI suggestions tailored to your career goals and interests.',
    },
    {
        icon: Zap,
        title: 'Smart Optimization',
        description: 'ATS optimization specific to your target roles and industries.',
    },
];
</script>

<template>
    <OnboardingLayout>
        <Head title="Welcome to CVverse" />
        
        <div class="text-center">
            <!-- Welcome illustration -->
            <div class="mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-primary/20 to-purple-500/20 flex items-center justify-center">
                    <Sparkles class="w-12 h-12 text-primary" />
                </div>
            </div>

            <!-- Welcome message -->
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">
                Welcome, {{ user?.name?.split(' ')[0] }}! 🎉
            </h1>
            <p class="text-lg text-muted-foreground mb-8 max-w-md mx-auto">
                Let's set up your profile to personalize your CV building experience. This will only take a minute.
            </p>

            <!-- Benefits -->
            <div class="grid gap-4 mb-10 text-left">
                <div 
                    v-for="benefit in benefits" 
                    :key="benefit.title"
                    class="flex items-start gap-4 p-4 rounded-xl bg-muted/50 hover:bg-muted transition-colors"
                >
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                        <component :is="benefit.icon" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1">{{ benefit.title }}</h3>
                        <p class="text-sm text-muted-foreground">{{ benefit.description }}</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <Button size="lg" as-child class="w-full sm:w-auto px-8">
                <Link href="/onboarding/profile">
                    Let's Get Started
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </Link>
            </Button>
        </div>
    </OnboardingLayout>
</template>
