<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { register, contact } from '@/routes';

const steps = [
    {
        number: '01',
        title: 'Create Your Account',
        description: 'Sign up for free in seconds with just your email. No credit card required to get started.',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>`,
        color: 'from-blue-500 to-cyan-500',
        tips: [
            'Use a professional email address',
            'Complete your profile for better AI suggestions',
            'Choose your industry during onboarding',
        ],
    },
    {
        number: '02',
        title: 'Choose a Template',
        description: 'Browse our library of professionally designed templates. Each one is crafted to make a great first impression with recruiters.',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
        color: 'from-purple-500 to-pink-500',
        tips: [
            'Preview templates before selecting',
            'Consider your industry when choosing',
            'Modern designs work well for tech roles',
            'Classic layouts suit traditional industries',
        ],
    },
    {
        number: '03',
        title: 'Fill In Your Details',
        description: 'Add your personal information, work experience, education, and skills. Our intuitive editor makes it easy to organize your content.',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
        color: 'from-orange-500 to-red-500',
        tips: [
            'Start with your most recent experience',
            'Use action verbs to describe achievements',
            'Quantify your accomplishments where possible',
            'Keep descriptions concise and impactful',
        ],
    },
    {
        number: '04',
        title: 'Enhance with AI',
        description: 'Let our AI assistant help you write compelling descriptions, suggest improvements, and optimize your content for applicant tracking systems (ATS).',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a4 4 0 0 0-4 4c0 2 1.4 3.7 2.5 4.5C8 12 6 14 6 16.5V20h12v-3.5c0-2.5-2-4.5-4.5-6C14.6 9.7 16 8 16 6a4 4 0 0 0-4-4z"/><path d="M12 2v4"/><path d="M8.5 8.5 12 6l3.5 2.5"/></svg>`,
        color: 'from-emerald-500 to-teal-500',
        tips: [
            'Use AI to rewrite weak bullet points',
            'Generate tailored content for specific roles',
            'Review AI suggestions before accepting',
            'Optimize for keywords from job descriptions',
        ],
    },
    {
        number: '05',
        title: 'Preview & Download',
        description: 'Preview your finished CV in real-time. When you\'re satisfied, download it as a polished PDF ready to send to employers.',
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
        color: 'from-indigo-500 to-purple-500',
        tips: [
            'Always preview before downloading',
            'Check formatting on different screen sizes',
            'Save multiple versions for different roles',
            'Share your CV link for feedback',
        ],
    },
];

const faqs = [
    {
        question: 'Is CVverse free to use?',
        answer: 'Yes! You can create and download CVs for free. Premium features like AI enhancements and additional templates are available with a paid subscription.',
    },
    {
        question: 'Can I create multiple CVs?',
        answer: 'Absolutely. You can create as many CVs as you need, each tailored to different job applications or industries.',
    },
    {
        question: 'How does the AI feature work?',
        answer: 'Our AI analyzes your experience and the job requirements to suggest improvements, rewrite content, and optimize your CV for applicant tracking systems.',
    },
    {
        question: 'Can I share my CV with others?',
        answer: 'Yes, you can generate a shareable link for your CV, allowing colleagues or mentors to review and provide feedback.',
    },
    {
        question: 'What file formats can I download?',
        answer: 'CVs can be downloaded as high-quality PDF files, which is the most widely accepted format by employers and recruiters.',
    },
    {
        question: 'Is my data secure?',
        answer: 'Your data is encrypted and stored securely. We never share your personal information with third parties without your consent.',
    },
];
</script>

<template>
    <LandingLayout title="Guides">
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.1),rgba(255,255,255,0))]" />
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Getting Started</Badge>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        How to create your
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            perfect CV
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground mb-8">
                        Follow these simple steps to build a professional CV that helps you stand out and land your dream job.
                    </p>
                    <Button size="lg" as-child class="text-base px-8">
                        <Link :href="register()">
                            Start Building Now
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Steps -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-5xl mx-auto">
                    <div class="space-y-24">
                        <div
                            v-for="(step, index) in steps"
                            :key="step.number"
                            class="relative"
                        >
                            <!-- Connector line -->
                            <div
                                v-if="index < steps.length - 1"
                                class="hidden lg:block absolute left-1/2 -translate-x-px top-full h-24 w-0.5 bg-gradient-to-b from-border to-transparent"
                            />

                            <div
                                class="grid lg:grid-cols-2 gap-8 lg:gap-16 items-center"
                                :class="index % 2 === 1 ? 'lg:direction-rtl' : ''"
                            >
                                <!-- Content side -->
                                <div :class="index % 2 === 1 ? 'lg:order-2 lg:text-left' : ''">
                                    <div class="flex items-center gap-4 mb-4">
                                        <span
                                            class="text-5xl font-black bg-gradient-to-r bg-clip-text text-transparent"
                                            :class="step.color"
                                        >
                                            {{ step.number }}
                                        </span>
                                    </div>
                                    <h2 class="text-2xl lg:text-3xl font-bold mb-4">{{ step.title }}</h2>
                                    <p class="text-lg text-muted-foreground mb-6">{{ step.description }}</p>

                                    <!-- Tips -->
                                    <div class="space-y-3">
                                        <div
                                            v-for="(tip, i) in step.tips"
                                            :key="i"
                                            class="flex items-start gap-3"
                                        >
                                            <div class="shrink-0 mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm text-muted-foreground">{{ tip }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Visual side -->
                                <div :class="index % 2 === 1 ? 'lg:order-1' : ''">
                                    <div
                                        class="relative rounded-2xl p-8 lg:p-12 bg-gradient-to-br border shadow-lg"
                                        :class="[
                                            index % 2 === 0 ? 'bg-muted/50' : 'bg-card',
                                        ]"
                                    >
                                        <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r opacity-10 blur-sm" :class="step.color" />
                                        <div class="relative flex flex-col items-center text-center">
                                            <div
                                                class="w-20 h-20 rounded-2xl bg-gradient-to-br flex items-center justify-center mb-6 text-white shadow-lg"
                                                :class="step.color"
                                            >
                                                <div v-html="step.icon" />
                                            </div>
                                            <h3 class="text-lg font-semibold mb-2">Step {{ step.number }}</h3>
                                            <p class="text-sm text-muted-foreground">{{ step.title }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">FAQ</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Frequently Asked Questions</h2>
                    <p class="text-lg text-muted-foreground">
                        Common questions about using CVverse.
                    </p>
                </div>

                <div class="max-w-3xl mx-auto grid gap-4">
                    <Card v-for="(faq, index) in faqs" :key="index" class="hover:shadow-md transition-shadow">
                        <CardContent class="pt-6">
                            <h3 class="font-semibold text-lg mb-2">{{ faq.question }}</h3>
                            <p class="text-muted-foreground">{{ faq.answer }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-primary/10 via-purple-500/10 to-pink-500/10">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Ready to build your CV?</h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Join thousands of professionals who have already created stunning CVs with CVverse.
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
                            <Link :href="contact()">
                                Need Help?
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
