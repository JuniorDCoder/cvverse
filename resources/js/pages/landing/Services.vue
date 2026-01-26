<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { register, pricing, contact } from '@/routes';

const mainFeatures = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v10"/><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"/><path d="M1 12h6m6 0h10"/><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"/></svg>`,
        title: 'AI-Powered Content Writing',
        description: 'Our advanced AI analyzes your experience and suggests compelling bullet points, professional summaries, and skill descriptions that resonate with recruiters.',
        benefits: ['Contextual suggestions based on your industry', 'Action verb optimization', 'Achievement quantification help', 'Grammar and tone refinement'],
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>`,
        title: 'Professional Templates',
        description: 'Choose from 50+ meticulously designed templates, each crafted by professional designers and optimized for both visual appeal and ATS compatibility.',
        benefits: ['Industry-specific designs', 'Customizable layouts', 'Multiple color schemes', 'Print-ready formatting'],
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>`,
        title: 'ATS Optimization',
        description: 'Every CV is automatically optimized to pass through Applicant Tracking Systems, ensuring your resume reaches human eyes.',
        benefits: ['Keyword optimization', 'Format compatibility checks', 'Section structure validation', 'Real-time ATS score'],
    },
];

const additionalFeatures = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`,
        title: 'Real-Time Preview',
        description: 'See your changes instantly with our live preview feature. Perfect your CV with immediate visual feedback.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
        title: 'Multi-Format Export',
        description: 'Download your CV in PDF, DOCX, or TXT format. Share a live link for easy access anytime.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>`,
        title: 'Easy Customization',
        description: 'Change colors, fonts, spacing, and layouts with simple controls. No design skills required.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
        title: 'Version History',
        description: 'Keep track of all your CV versions. Easily switch between different versions for different applications.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
        title: 'LinkedIn Import',
        description: 'Import your profile data directly from LinkedIn. Save time and ensure accuracy.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>`,
        title: 'Auto-Save',
        description: 'Never lose your work. Every change is automatically saved to the cloud.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`,
        title: 'Cover Letter Builder',
        description: 'Create matching cover letters with AI assistance. Stand out with a complete application package.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>`,
        title: 'Expert Support',
        description: '24/7 customer support via chat and email. Get help whenever you need it.',
    },
];

const comparisons = [
    {
        aspect: 'Time to create a CV',
        traditional: '3-4 hours',
        cvverse: '15 minutes',
    },
    {
        aspect: 'ATS optimization',
        traditional: 'Manual research',
        cvverse: 'Automatic',
    },
    {
        aspect: 'Design quality',
        traditional: 'Varies',
        cvverse: 'Professional',
    },
    {
        aspect: 'Content suggestions',
        traditional: 'None',
        cvverse: 'AI-powered',
    },
    {
        aspect: 'Version management',
        traditional: 'Manual files',
        cvverse: 'Cloud-based',
    },
    {
        aspect: 'Updates',
        traditional: 'Start over',
        cvverse: 'Instant editing',
    },
];
</script>

<template>
    <LandingLayout title="Services">
        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.1),rgba(255,255,255,0))]" />
            
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Our Services</Badge>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        Powerful tools for
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            career success
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground mb-8">
                        From AI-powered writing to professional templates, discover all the features that make CVverse the preferred choice for job seekers worldwide.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="register()">
                                Try It Free
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                        <Button variant="outline" size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="pricing()">
                                View Pricing
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Features Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Core Features</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        The complete CV building solution
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Three powerful pillars that set CVverse apart from the rest.
                    </p>
                </div>
                
                <div class="space-y-20">
                    <div 
                        v-for="(feature, index) in mainFeatures" 
                        :key="feature.title"
                        class="grid lg:grid-cols-2 gap-12 items-center"
                        :class="index % 2 === 1 ? 'lg:flex-row-reverse' : ''"
                    >
                        <div :class="index % 2 === 1 ? 'lg:order-2' : ''">
                            <div class="w-16 h-16 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                                <div v-html="feature.icon" />
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold mb-4">{{ feature.title }}</h3>
                            <p class="text-muted-foreground mb-6">{{ feature.description }}</p>
                            <ul class="space-y-3">
                                <li 
                                    v-for="benefit in feature.benefits" 
                                    :key="benefit"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <div class="w-5 h-5 rounded-full bg-green-500/10 text-green-500 flex items-center justify-center flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20,6 9,17 4,12"/>
                                        </svg>
                                    </div>
                                    <span>{{ benefit }}</span>
                                </li>
                            </ul>
                        </div>
                        <div :class="index % 2 === 1 ? 'lg:order-1' : ''">
                            <div class="relative">
                                <div class="absolute -inset-4 bg-gradient-to-r from-primary/10 via-purple-500/10 to-pink-500/10 rounded-2xl blur-2xl opacity-50" />
                                <div class="relative bg-card border rounded-xl p-8 shadow-lg min-h-[300px] flex items-center justify-center">
                                    <!-- Feature illustration placeholder -->
                                    <div class="text-center">
                                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary/20 to-purple-500/20 flex items-center justify-center mx-auto mb-4">
                                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary/30 to-purple-500/30 flex items-center justify-center text-primary" v-html="feature.icon" />
                                        </div>
                                        <div class="space-y-2">
                                            <div class="h-3 bg-muted-foreground/10 rounded w-48 mx-auto" />
                                            <div class="h-3 bg-muted-foreground/10 rounded w-36 mx-auto" />
                                            <div class="h-3 bg-muted-foreground/10 rounded w-40 mx-auto" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Additional Features Grid -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">More Features</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Everything else you need
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        A comprehensive toolkit designed to give you every advantage in your job search.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card 
                        v-for="feature in additionalFeatures" 
                        :key="feature.title"
                        class="hover:shadow-lg transition-all duration-300 hover:border-primary/50"
                    >
                        <CardHeader class="pb-3">
                            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-3">
                                <div v-html="feature.icon" />
                            </div>
                            <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-sm">{{ feature.description }}</CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Comparison Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Why CVverse?</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Traditional vs. CVverse
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        See how CVverse transforms the CV creation experience.
                    </p>
                </div>
                
                <div class="max-w-4xl mx-auto">
                    <div class="bg-card border rounded-xl overflow-hidden shadow-lg">
                        <!-- Header -->
                        <div class="grid grid-cols-3 bg-muted/50 p-4 border-b">
                            <div class="font-semibold text-muted-foreground">Aspect</div>
                            <div class="font-semibold text-center text-muted-foreground">Traditional</div>
                            <div class="font-semibold text-center text-primary">CVverse</div>
                        </div>
                        <!-- Rows -->
                        <div 
                            v-for="(comparison, index) in comparisons" 
                            :key="comparison.aspect"
                            class="grid grid-cols-3 p-4"
                            :class="index < comparisons.length - 1 ? 'border-b' : ''"
                        >
                            <div class="font-medium">{{ comparison.aspect }}</div>
                            <div class="text-center text-muted-foreground">{{ comparison.traditional }}</div>
                            <div class="text-center font-medium text-primary">{{ comparison.cvverse }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-primary/10 via-purple-500/10 to-pink-500/10">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Ready to experience the difference?
                    </h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Join thousands of professionals who have already transformed their job search with CVverse.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="register()">
                                Start Building Now
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                        <Button variant="outline" size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="contact()">
                                Talk to Sales
                            </Link>
                        </Button>
                    </div>
                    <p class="mt-6 text-sm text-muted-foreground">
                        Free plan available • No credit card required
                    </p>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
