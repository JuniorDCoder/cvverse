<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { register, contact, services } from '@/routes';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

const billingCycle = ref<'monthly' | 'yearly'>('monthly');

const plans = [
    {
        name: 'Free',
        description: 'Perfect for getting started',
        monthlyPrice: 0,
        yearlyPrice: 0,
        popular: false,
        features: [
            { text: '1 CV', included: true },
            { text: 'Basic templates (5)', included: true },
            { text: 'PDF download', included: true },
            { text: 'Basic AI suggestions', included: true },
            { text: 'Email support', included: true },
            { text: 'ATS optimization', included: false },
            { text: 'Cover letter builder', included: false },
            { text: 'LinkedIn import', included: false },
            { text: 'Priority support', included: false },
        ],
        cta: 'Get Started Free',
        ctaVariant: 'outline' as const,
    },
    {
        name: 'Pro',
        description: 'Best for active job seekers',
        monthlyPrice: 12,
        yearlyPrice: 96,
        popular: true,
        features: [
            { text: 'Unlimited CVs', included: true },
            { text: 'All templates (50+)', included: true },
            { text: 'PDF, DOCX, TXT export', included: true },
            { text: 'Advanced AI writing', included: true },
            { text: 'ATS optimization', included: true },
            { text: 'Cover letter builder', included: true },
            { text: 'LinkedIn import', included: true },
            { text: 'Version history', included: true },
            { text: 'Priority email support', included: true },
        ],
        cta: 'Start Pro Trial',
        ctaVariant: 'default' as const,
    },
    {
        name: 'Enterprise',
        description: 'For teams and organizations',
        monthlyPrice: 49,
        yearlyPrice: 468,
        popular: false,
        features: [
            { text: 'Everything in Pro', included: true },
            { text: 'Team management', included: true },
            { text: 'Custom branding', included: true },
            { text: 'API access', included: true },
            { text: 'SSO integration', included: true },
            { text: 'Analytics dashboard', included: true },
            { text: 'Bulk export', included: true },
            { text: 'Dedicated account manager', included: true },
            { text: '24/7 phone support', included: true },
        ],
        cta: 'Contact Sales',
        ctaVariant: 'outline' as const,
    },
];

const faqs = [
    {
        question: 'Can I try CVverse for free?',
        answer: 'Yes! Our Free plan gives you access to basic features with no time limit. You can create 1 CV and download it as a PDF anytime.',
    },
    {
        question: 'What happens when my trial ends?',
        answer: 'After your 14-day Pro trial ends, you\'ll be automatically moved to the Free plan unless you choose to subscribe. No automatic charges.',
    },
    {
        question: 'Can I switch plans anytime?',
        answer: 'Absolutely! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we\'ll prorate any differences.',
    },
    {
        question: 'Is there a discount for annual billing?',
        answer: 'Yes! When you choose annual billing, you get 2 months free compared to monthly billing. That\'s a 17% discount.',
    },
    {
        question: 'Do you offer refunds?',
        answer: 'We offer a 30-day money-back guarantee on all paid plans. If you\'re not satisfied, contact us for a full refund.',
    },
    {
        question: 'Can I cancel my subscription?',
        answer: 'Yes, you can cancel your subscription at any time from your account settings. You\'ll retain access until the end of your billing period.',
    },
];
</script>

<template>
    <LandingLayout title="Pricing">
        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.1),rgba(255,255,255,0))]" />
            
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Pricing</Badge>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        Simple, transparent
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            pricing
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground mb-8">
                        Choose the perfect plan for your needs. Start free, upgrade when you're ready.
                    </p>
                    
                    <!-- Billing Toggle -->
                    <div class="inline-flex items-center gap-3 bg-muted rounded-full p-1">
                        <button
                            @click="billingCycle = 'monthly'"
                            class="px-4 py-2 text-sm font-medium rounded-full transition-colors"
                            :class="billingCycle === 'monthly' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            Monthly
                        </button>
                        <button
                            @click="billingCycle = 'yearly'"
                            class="px-4 py-2 text-sm font-medium rounded-full transition-colors flex items-center gap-2"
                            :class="billingCycle === 'yearly' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            Yearly
                            <Badge variant="secondary" class="text-xs">Save 17%</Badge>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Cards -->
        <section class="pb-20 lg:pb-28 -mt-8">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto">
                    <Card 
                        v-for="plan in plans" 
                        :key="plan.name"
                        class="relative flex flex-col"
                        :class="plan.popular ? 'border-primary shadow-lg scale-105 z-10' : 'hover:shadow-lg transition-shadow'"
                    >
                        <!-- Popular badge -->
                        <div 
                            v-if="plan.popular" 
                            class="absolute -top-4 left-1/2 -translate-x-1/2"
                        >
                            <Badge class="px-4 py-1 bg-primary text-primary-foreground">
                                Most Popular
                            </Badge>
                        </div>
                        
                        <CardHeader class="text-center pb-2">
                            <CardTitle class="text-2xl">{{ plan.name }}</CardTitle>
                            <CardDescription>{{ plan.description }}</CardDescription>
                        </CardHeader>
                        
                        <CardContent class="flex-1">
                            <!-- Price -->
                            <div class="text-center mb-6">
                                <div class="flex items-end justify-center gap-1">
                                    <span class="text-4xl font-bold">
                                        ${{ billingCycle === 'monthly' ? plan.monthlyPrice : Math.round(plan.yearlyPrice / 12) }}
                                    </span>
                                    <span class="text-muted-foreground mb-1">/month</span>
                                </div>
                                <p v-if="plan.yearlyPrice > 0 && billingCycle === 'yearly'" class="text-sm text-muted-foreground mt-1">
                                    ${{ plan.yearlyPrice }} billed annually
                                </p>
                                <p v-if="plan.monthlyPrice === 0" class="text-sm text-muted-foreground mt-1">
                                    Free forever
                                </p>
                            </div>
                            
                            <Separator class="my-6" />
                            
                            <!-- Features -->
                            <ul class="space-y-3">
                                <li 
                                    v-for="feature in plan.features" 
                                    :key="feature.text"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <div 
                                        class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                                        :class="feature.included ? 'bg-green-500/10 text-green-500' : 'bg-muted text-muted-foreground'"
                                    >
                                        <svg v-if="feature.included" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18"/>
                                            <path d="m6 6 12 12"/>
                                        </svg>
                                    </div>
                                    <span :class="feature.included ? '' : 'text-muted-foreground'">
                                        {{ feature.text }}
                                    </span>
                                </li>
                            </ul>
                        </CardContent>
                        
                        <CardFooter>
                            <Button 
                                :variant="plan.ctaVariant" 
                                as-child 
                                class="w-full"
                                :class="plan.popular ? 'shadow-lg' : ''"
                            >
                                <Link :href="plan.name === 'Enterprise' ? contact() : register()">
                                    {{ plan.cta }}
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
                
                <!-- Enterprise callout -->
                <div class="mt-12 text-center">
                    <p class="text-muted-foreground">
                        Need a custom plan for your organization? 
                        <Link :href="contact()" class="text-primary hover:underline font-medium">
                            Contact our sales team
                        </Link>
                    </p>
                </div>
            </div>
        </section>

        <!-- Features Comparison -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">All Plans Include</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Core features in every plan
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Even our free plan includes these essential features.
                    </p>
                </div>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                    <div class="bg-card border rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2">Secure Storage</h3>
                        <p class="text-sm text-muted-foreground">Your data is encrypted and securely stored in the cloud.</p>
                    </div>
                    <div class="bg-card border rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                <line x1="8" y1="21" x2="16" y2="21"/>
                                <line x1="12" y1="17" x2="12" y2="21"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2">Real-Time Preview</h3>
                        <p class="text-sm text-muted-foreground">See your changes instantly as you edit.</p>
                    </div>
                    <div class="bg-card border rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2">Auto-Save</h3>
                        <p class="text-sm text-muted-foreground">Never lose your work with automatic saving.</p>
                    </div>
                    <div class="bg-card border rounded-xl p-6 text-center hover:shadow-lg transition-shadow">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold mb-2">Email Support</h3>
                        <p class="text-sm text-muted-foreground">Get help whenever you need it.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">FAQ</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Frequently asked questions
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Everything you need to know about our pricing and plans.
                    </p>
                </div>
                
                <div class="max-w-3xl mx-auto grid gap-6">
                    <Card 
                        v-for="faq in faqs" 
                        :key="faq.question"
                        class="hover:shadow-md transition-shadow"
                    >
                        <CardHeader class="pb-3">
                            <CardTitle class="text-lg font-semibold">{{ faq.question }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">{{ faq.answer }}</p>
                        </CardContent>
                    </Card>
                </div>
                
                <div class="mt-12 text-center">
                    <p class="text-muted-foreground mb-4">
                        Still have questions?
                    </p>
                    <Button variant="outline" as-child>
                        <Link :href="contact()">
                            Contact Support
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-primary/10 via-purple-500/10 to-pink-500/10">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Start building your CV today
                    </h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Join over 500,000 professionals who trust CVverse. Try it free—no credit card required.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="register()">
                                Get Started Free
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                        <Button variant="outline" size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="services()">
                                View All Features
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
