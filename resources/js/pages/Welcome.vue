<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { register, login, services, pricing } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const features = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>`,
        title: 'Smart Templates',
        description: 'Choose from 50+ professionally designed templates that are ATS-optimized and recruiter-approved.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v10"/><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"/><path d="M1 12h6m6 0h10"/><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"/></svg>`,
        title: 'AI-Powered Writing',
        description: 'Let our AI assist you in crafting compelling content that highlights your strengths and achievements.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>`,
        title: 'ATS-Friendly',
        description: 'Ensure your resume passes through Applicant Tracking Systems with our optimized formatting.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`,
        title: 'Real-Time Preview',
        description: 'See changes instantly as you edit. What you see is exactly what recruiters will receive.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>`,
        title: 'Multi-Format Export',
        description: 'Download your CV as PDF, DOCX, or share a live link. Perfect for any application method.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>`,
        title: 'Easy Customization',
        description: 'Customize colors, fonts, and layouts to match your personal brand and industry standards.',
    },
];

const stats = [
    { value: '500K+', label: 'CVs Created' },
    { value: '95%', label: 'Success Rate' },
    { value: '150+', label: 'Countries' },
    { value: '4.9/5', label: 'User Rating' },
];

const testimonials = [
    {
        quote: "CVverse helped me land my dream job at a Fortune 500 company. The AI suggestions were incredibly helpful!",
        author: "Sarah Chen",
        role: "Software Engineer at Google",
        avatar: "SC",
    },
    {
        quote: "The templates are stunning and the ATS optimization gave me peace of mind. Got 3 interviews in my first week!",
        author: "Michael Rodriguez",
        role: "Marketing Manager at Spotify",
        avatar: "MR",
    },
    {
        quote: "Best CV builder I've ever used. The real-time preview feature saved me hours of formatting headaches.",
        author: "Emma Thompson",
        role: "UX Designer at Apple",
        avatar: "ET",
    },
];
</script>

<template>
    <LandingLayout title="Welcome">
        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <!-- Background gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.15),rgba(255,255,255,0))]" />
            
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Badge -->
                    <Badge variant="secondary" class="mb-6 px-4 py-1.5">
                        <span class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            Now with AI-powered writing assistant
                        </span>
                    </Badge>
                    
                    <!-- Headline -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        Create a CV that
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            stands out
                        </span>
                    </h1>
                    
                    <!-- Subheadline -->
                    <p class="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto mb-8">
                        Build professional, ATS-optimized resumes in minutes. Join 500,000+ professionals who landed their dream jobs with CVverse.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="register()">
                                Start Building for Free
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                        <Button variant="outline" size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="services()">
                                See How It Works
                            </Link>
                        </Button>
                    </div>
                    
                    <!-- Social Proof -->
                    <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 border-2 border-background flex items-center justify-center text-xs font-medium text-white">JD</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-500 border-2 border-background flex items-center justify-center text-xs font-medium text-white">AK</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-red-500 border-2 border-background flex items-center justify-center text-xs font-medium text-white">MR</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-500 to-rose-500 border-2 border-background flex items-center justify-center text-xs font-medium text-white">SC</div>
                        </div>
                        <span>Join <strong class="text-foreground">500,000+</strong> professionals</span>
                    </div>
                </div>
                
                <!-- Hero Image/Preview -->
                <div class="mt-16 lg:mt-20 max-w-5xl mx-auto">
                    <div class="relative">
                        <!-- Glow effect -->
                        <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 via-purple-500/20 to-pink-500/20 rounded-2xl blur-2xl opacity-50" />
                        
                        <!-- Browser mockup -->
                        <div class="relative bg-card border rounded-xl shadow-2xl overflow-hidden">
                            <!-- Browser chrome -->
                            <div class="flex items-center gap-2 px-4 py-3 border-b bg-muted/50">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500" />
                                    <div class="w-3 h-3 rounded-full bg-yellow-500" />
                                    <div class="w-3 h-3 rounded-full bg-green-500" />
                                </div>
                                <div class="flex-1 flex justify-center">
                                    <div class="bg-background rounded-md px-4 py-1 text-sm text-muted-foreground w-64 text-center flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        cvverse.com/dashboard
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded bg-muted flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                </div>
                            </div>
                            <!-- Content -->
                            <div class="aspect-[16/9] bg-gradient-to-br from-background via-muted/30 to-muted/50">
                                <div class="grid grid-cols-12 h-full">
                                    <!-- Left Sidebar - Sections -->
                                    <div class="col-span-2 border-r bg-card/50 p-3 space-y-2 hidden sm:block">
                                        <div class="text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-3">Sections</div>
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-primary/10 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            <span class="text-[10px] font-medium">Profile</span>
                                        </div>
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-muted/50 text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                            <span class="text-[10px]">Experience</span>
                                        </div>
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-muted/50 text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                            <span class="text-[10px]">Education</span>
                                        </div>
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-muted/50 text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            <span class="text-[10px]">Skills</span>
                                        </div>
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-muted/50 text-muted-foreground">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            <span class="text-[10px]">Projects</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Center - Editor -->
                                    <div class="col-span-12 sm:col-span-6 p-4 space-y-3 overflow-hidden">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-semibold">Edit Profile</span>
                                            <div class="flex items-center gap-1">
                                                <div class="px-2 py-0.5 rounded text-[9px] bg-green-500/10 text-green-600 dark:text-green-400">Saved</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Form Fields -->
                                        <div class="space-y-3">
                                            <div>
                                                <div class="text-[10px] text-muted-foreground mb-1">Full Name</div>
                                                <div class="h-7 bg-background border rounded-md px-2 flex items-center text-xs">Sarah Chen</div>
                                            </div>
                                            <div>
                                                <div class="text-[10px] text-muted-foreground mb-1">Job Title</div>
                                                <div class="h-7 bg-background border rounded-md px-2 flex items-center text-xs">Senior Software Engineer</div>
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between mb-1">
                                                    <div class="text-[10px] text-muted-foreground">Professional Summary</div>
                                                    <div class="flex items-center gap-1 text-[9px] text-primary cursor-pointer">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/><path d="M19 11h2m-1 -1v2"/></svg>
                                                        AI Enhance
                                                    </div>
                                                </div>
                                                <div class="h-16 bg-background border rounded-md px-2 py-1.5 text-[10px] text-muted-foreground leading-relaxed">
                                                    Passionate software engineer with 8+ years of experience building scalable web applications...
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- AI Suggestion Card -->
                                        <div class="bg-gradient-to-r from-primary/5 via-purple-500/5 to-pink-500/5 border border-primary/20 rounded-lg p-3 mt-2">
                                            <div class="flex items-start gap-2">
                                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-[10px] font-medium mb-1 flex items-center gap-1">
                                                        AI Suggestion
                                                        <span class="px-1 py-0.5 rounded bg-primary/10 text-primary text-[8px]">NEW</span>
                                                    </div>
                                                    <div class="text-[9px] text-muted-foreground leading-relaxed">
                                                        "Add quantifiable achievements like 'Led team of 5 engineers' or 'Reduced load time by 40%' to make your summary more impactful."
                                                    </div>
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <div class="px-2 py-1 rounded bg-primary text-primary-foreground text-[9px] font-medium cursor-pointer">Apply</div>
                                                        <div class="px-2 py-1 rounded bg-muted text-muted-foreground text-[9px] cursor-pointer">Dismiss</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right - Live Preview -->
                                    <div class="col-span-4 bg-muted/30 p-4 hidden sm:block">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-[10px] font-medium text-muted-foreground">Live Preview</span>
                                            <div class="flex items-center gap-1">
                                                <div class="w-5 h-5 rounded bg-background border flex items-center justify-center cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- CV Preview Card -->
                                        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-4 space-y-3 aspect-[3/4] overflow-hidden">
                                            <!-- Header -->
                                            <div class="flex items-center gap-3 pb-2 border-b border-zinc-200 dark:border-zinc-700">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-xs font-bold">SC</div>
                                                <div>
                                                    <div class="text-[11px] font-bold text-zinc-900 dark:text-zinc-100">Sarah Chen</div>
                                                    <div class="text-[9px] text-zinc-500">Senior Software Engineer</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Contact -->
                                            <div class="flex flex-wrap gap-2 text-[8px] text-zinc-500">
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                    sarah@email.com
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                    San Francisco
                                                </span>
                                            </div>
                                            
                                            <!-- Summary -->
                                            <div>
                                                <div class="text-[9px] font-semibold text-zinc-900 dark:text-zinc-100 mb-1">Summary</div>
                                                <div class="text-[8px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                                    Passionate software engineer with 8+ years of experience building scalable applications...
                                                </div>
                                            </div>
                                            
                                            <!-- Experience -->
                                            <div>
                                                <div class="text-[9px] font-semibold text-zinc-900 dark:text-zinc-100 mb-1">Experience</div>
                                                <div class="space-y-1">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <div class="text-[8px] font-medium text-zinc-800 dark:text-zinc-200">Senior Engineer</div>
                                                            <div class="text-[7px] text-zinc-500">Google • 2020 - Present</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Skills -->
                                            <div>
                                                <div class="text-[9px] font-semibold text-zinc-900 dark:text-zinc-100 mb-1">Skills</div>
                                                <div class="flex flex-wrap gap-1">
                                                    <span class="px-1.5 py-0.5 rounded text-[7px] bg-primary/10 text-primary">React</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[7px] bg-primary/10 text-primary">TypeScript</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[7px] bg-primary/10 text-primary">Node.js</span>
                                                    <span class="px-1.5 py-0.5 rounded text-[7px] bg-primary/10 text-primary">AWS</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-y border-border bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-3xl lg:text-4xl font-bold text-foreground mb-2">{{ stat.value }}</div>
                        <div class="text-sm text-muted-foreground">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Features</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Everything you need to create the perfect CV
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        Powerful tools designed to help you stand out in today's competitive job market.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <Card 
                        v-for="feature in features" 
                        :key="feature.title"
                        class="group hover:shadow-lg transition-all duration-300 hover:border-primary/50"
                    >
                        <CardHeader>
                            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                                <div v-html="feature.icon" />
                            </div>
                            <CardTitle class="text-xl">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-base">{{ feature.description }}</CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">How It Works</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Create your professional CV in 3 simple steps
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        No design skills required. Our intuitive builder guides you through the process.
                    </p>
                </div>
                
                <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg">
                                1
                            </div>
                            <h3 class="text-xl font-semibold">Choose a Template</h3>
                        </div>
                        <p class="text-muted-foreground pl-14">
                            Browse our collection of 50+ professionally designed templates. Each one is crafted for different industries and career levels.
                        </p>
                        <!-- Connector line for desktop -->
                        <div class="hidden lg:block absolute top-5 left-[calc(100%+1rem)] w-8 border-t-2 border-dashed border-border" />
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg">
                                2
                            </div>
                            <h3 class="text-xl font-semibold">Fill in Your Details</h3>
                        </div>
                        <p class="text-muted-foreground pl-14">
                            Use our smart editor with AI-powered suggestions to craft compelling content. Import from LinkedIn or start fresh.
                        </p>
                        <!-- Connector line for desktop -->
                        <div class="hidden lg:block absolute top-5 left-[calc(100%+1rem)] w-8 border-t-2 border-dashed border-border" />
                    </div>
                    
                    <!-- Step 3 -->
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg">
                                3
                            </div>
                            <h3 class="text-xl font-semibold">Download & Apply</h3>
                        </div>
                        <p class="text-muted-foreground pl-14">
                            Export your polished CV in multiple formats. Share a live link or download PDF/DOCX files instantly.
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-12">
                    <Button size="lg" as-child>
                        <Link :href="register()">
                            Get Started Now
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Testimonials</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Loved by professionals worldwide
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        See what our users have to say about their experience with CVverse.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                    <Card v-for="testimonial in testimonials" :key="testimonial.author" class="relative">
                        <CardContent class="pt-6">
                            <!-- Quote icon -->
                            <svg class="absolute top-6 right-6 h-8 w-8 text-muted-foreground/20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.192 15.757c0-.88-.23-1.618-.69-2.217-.326-.412-.768-.683-1.327-.812-.55-.128-1.07-.137-1.54-.028-.16-.95.1-1.956.76-3.022.66-1.065 1.515-1.867 2.558-2.403L9.373 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.98 1.68-1.25 2.69-.346 2.04-.217 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l.002.004zm9.124 0c0-.88-.23-1.618-.69-2.217-.326-.42-.77-.692-1.327-.817-.56-.124-1.074-.13-1.54-.022-.16-.94.09-1.95.75-3.02.66-1.06 1.514-1.86 2.557-2.4L18.49 5c-.8.396-1.555.898-2.26 1.505-.708.607-1.34 1.305-1.894 2.094-.556.79-.97 1.68-1.24 2.69-.273 1-.345 2.04-.217 3.1.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l-.007.006z"/>
                            </svg>
                            
                            <p class="text-muted-foreground mb-6">
                                "{{ testimonial.quote }}"
                            </p>
                            
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ testimonial.avatar }}
                                </div>
                                <div>
                                    <div class="font-semibold text-sm">{{ testimonial.author }}</div>
                                    <div class="text-xs text-muted-foreground">{{ testimonial.role }}</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 lg:py-28 bg-gradient-to-br from-primary/10 via-purple-500/10 to-pink-500/10">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Ready to build your standout CV?
                    </h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Join over 500,000 professionals who have already created their perfect resume with CVverse. Start for free today.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="register()">
                                Create Your CV Now
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
                    <p class="mt-6 text-sm text-muted-foreground">
                        No credit card required • Free forever plan available
                    </p>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
