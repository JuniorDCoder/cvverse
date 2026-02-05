<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { register, login, services, pricing } from '@/routes';

interface Testimonial {
    id: number;
    author_name: string;
    author_role: string;
    author_company: string | null;
    quote: string;
    rating: number;
    initials: string;
}

interface Stats {
    users_count: string;
    cvs_created: string;
    success_rate: string;
    countries: string;
    user_rating: string;
}

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        testimonials?: Testimonial[];
        stats?: Stats;
    }>(),
    {
        canRegister: true,
        testimonials: () => [],
        stats: () => ({
            users_count: '500+',
            cvs_created: '100+',
            success_rate: '95%',
            countries: '150+',
            user_rating: '4.9/5',
        }),
    },
);

const displayStats = computed(() => [
    { value: props.stats.cvs_created, label: 'CVs Created' },
    { value: props.stats.success_rate, label: 'Success Rate' },
    { value: props.stats.countries, label: 'Countries' },
    { value: props.stats.user_rating, label: 'User Rating' },
]);

const displayTestimonials = computed(() => {
    if (props.testimonials.length > 0) {
        return props.testimonials.map(t => ({
            quote: t.quote,
            author: t.author_name,
            role: t.author_company ? `${t.author_role} at ${t.author_company}` : t.author_role,
            avatar: t.initials,
            rating: t.rating,
        }));
    }
    // Fallback testimonials if none from backend
    return [
        {
            quote: "CVverse helped me land my dream job at a Fortune 500 company. The AI suggestions were incredibly helpful!",
            author: "Sarah Chen",
            role: "Software Engineer at Google",
            avatar: "SC",
            rating: 5,
        },
        {
            quote: "The templates are stunning and the ATS optimization gave me peace of mind. Got 3 interviews in my first week!",
            author: "Michael Rodriguez",
            role: "Marketing Manager at Spotify",
            avatar: "MR",
            rating: 5,
        },
        {
            quote: "Best CV builder I've ever used. The real-time preview feature saved me hours of formatting headaches.",
            author: "Emma Thompson",
            role: "UX Designer at Apple",
            avatar: "ET",
            rating: 5,
        },
    ];
});

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
                        Build professional, ATS-optimized resumes in minutes. Join {{ stats.users_count }} professionals who landed their dream jobs with CVverse.
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
                        <span>Join <strong class="text-foreground">{{ stats.users_count }}</strong> professionals</span>
                    </div>
                </div>
                
                <!-- Hero Image/Preview -->
                <div class="mt-16 lg:mt-20 max-w-6xl mx-auto px-4">
                    <div class="relative">
                        <!-- Glow effect -->
                        <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-3xl opacity-40" />
                        
                        <!-- Desktop Browser mockup - Hidden on mobile -->
                        <div class="relative bg-card border rounded-2xl shadow-2xl overflow-hidden hidden md:block">
                            <!-- Browser chrome -->
                            <div class="flex items-center gap-3 px-4 py-3 border-b bg-muted/50">
                                <div class="flex gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500 hover:bg-red-600 transition-colors" />
                                    <div class="w-3 h-3 rounded-full bg-yellow-500 hover:bg-yellow-600 transition-colors" />
                                    <div class="w-3 h-3 rounded-full bg-green-500 hover:bg-green-600 transition-colors" />
                                </div>
                                <div class="flex-1 flex justify-center">
                                    <div class="bg-background rounded-lg px-4 py-1.5 text-sm text-muted-foreground max-w-md w-full flex items-center justify-center gap-2 border">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        <span class="font-medium">cvverse.com</span>
                                        <span class="text-muted-foreground/60">/dashboard</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-xs font-medium">
                                        SC
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dashboard Content -->
                            <div class="aspect-[16/9] bg-gradient-to-br from-background via-background to-muted/30 p-6">
                                <!-- Header -->
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 class="text-lg font-bold flex items-center gap-2">
                                            Good morning, Sarah! 
                                            <span class="text-xl">👋</span>
                                        </h2>
                                        <p class="text-xs text-muted-foreground">Here's what's happening with your job search today.</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="px-3 py-1.5 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs font-medium flex items-center gap-1.5 shadow-lg shadow-purple-500/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/></svg>
                                            Generate CV with AI
                                        </div>
                                        <div class="px-3 py-1.5 rounded-lg bg-primary text-primary-foreground text-xs font-medium flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                                            Track New Job
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Stats Cards -->
                                <div class="grid grid-cols-4 gap-4 mb-6">
                                    <div class="bg-card border rounded-xl p-4 group hover:shadow-lg transition-all">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] text-muted-foreground font-medium">Total Applications</span>
                                            <div class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold">24</div>
                                        <p class="text-[9px] text-muted-foreground"><span class="text-green-500 font-medium">+8</span> active</p>
                                    </div>
                                    <div class="bg-card border rounded-xl p-4 group hover:shadow-lg transition-all">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] text-muted-foreground font-medium">Interviews</span>
                                            <div class="p-1.5 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold">5</div>
                                        <p class="text-[9px] text-muted-foreground">Scheduled or in progress</p>
                                    </div>
                                    <div class="bg-card border rounded-xl p-4 group hover:shadow-lg transition-all">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] text-muted-foreground font-medium">Offers Received</span>
                                            <div class="p-1.5 rounded-lg bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold">2</div>
                                        <p class="text-[9px] text-muted-foreground">Congratulations! 🎉</p>
                                    </div>
                                    <div class="bg-card border rounded-xl p-4 group hover:shadow-lg transition-all">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] text-muted-foreground font-medium">Documents</span>
                                            <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                            </div>
                                        </div>
                                        <div class="text-2xl font-bold">7</div>
                                        <p class="text-[9px] text-muted-foreground">3 CVs, 4 letters</p>
                                    </div>
                                </div>
                                
                                <!-- Main Content -->
                                <div class="grid grid-cols-3 gap-4">
                                    <!-- Recent Applications -->
                                    <div class="col-span-2 bg-card border rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h3 class="text-sm font-semibold">Recent Applications</h3>
                                                <p class="text-[10px] text-muted-foreground">Your latest job applications</p>
                                            </div>
                                            <div class="text-[10px] text-primary font-medium flex items-center gap-1 cursor-pointer">
                                                View all
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <!-- Application Item -->
                                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-muted/50 transition-colors">
                                                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-blue-500/20 to-blue-500/10 flex items-center justify-center text-blue-600 font-semibold text-[10px]">G</div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium truncate">Senior Frontend Engineer</p>
                                                    <p class="text-[10px] text-muted-foreground flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                                        Google • Jan 15
                                                    </p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-medium bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">Interviewing</span>
                                            </div>
                                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-muted/50 transition-colors">
                                                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-green-500/20 to-green-500/10 flex items-center justify-center text-green-600 font-semibold text-[10px]">S</div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium truncate">Full Stack Developer</p>
                                                    <p class="text-[10px] text-muted-foreground flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                                        Spotify • Jan 12
                                                    </p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-medium bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">Offered</span>
                                            </div>
                                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-muted/50 transition-colors">
                                                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-orange-500/20 to-orange-500/10 flex items-center justify-center text-orange-600 font-semibold text-[10px]">M</div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium truncate">React Developer</p>
                                                    <p class="text-[10px] text-muted-foreground flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                                        Meta • Jan 10
                                                    </p>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">Applied</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="bg-card border rounded-xl p-4">
                                        <h3 class="text-sm font-semibold mb-3 flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/></svg>
                                            Quick Actions
                                        </h3>
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 p-2 rounded-lg border-2 border-primary/50 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-950/30 dark:to-blue-950/30">
                                                <div class="p-1.5 rounded-lg bg-gradient-to-br from-purple-500 to-blue-500 text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[10px] font-medium">Generate CV with AI</p>
                                                    <p class="text-[9px] text-muted-foreground">Describe yourself and let AI create</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 p-2 rounded-lg border hover:bg-muted/50 transition-all">
                                                <div class="p-1.5 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[10px] font-medium">Track New Job</p>
                                                    <p class="text-[9px] text-muted-foreground">Add application via URL</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 p-2 rounded-lg border hover:bg-muted/50 transition-all">
                                                <div class="p-1.5 rounded-lg bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[10px] font-medium">Create CV</p>
                                                    <p class="text-[9px] text-muted-foreground">Build an AI-optimized CV</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Phone mockup - Shown on mobile screens -->
                        <div class="relative md:hidden mx-auto max-w-[280px]">
                            <!-- Phone frame -->
                            <div class="relative bg-zinc-900 rounded-[2.5rem] p-2 shadow-2xl">
                                <!-- Notch -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-6 bg-zinc-900 rounded-b-2xl z-10"></div>
                                
                                <!-- Screen -->
                                <div class="relative bg-card rounded-[2rem] overflow-hidden">
                                    <!-- Status bar -->
                                    <div class="flex items-center justify-between px-6 py-2 bg-background/80 backdrop-blur-sm">
                                        <span class="text-[10px] font-medium">9:41</span>
                                        <div class="flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3c-1.1 0-2 .9-2 2v3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h4c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2V5c0-1.1-.9-2-2-2z"/></svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3a4.237 4.237 0 0 0-6 0zm-4-4l2 2a7.074 7.074 0 0 1 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg>
                                            <div class="w-6 h-3 bg-green-500 rounded-sm"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- App Content -->
                                    <div class="px-4 pb-6 pt-2 space-y-4 min-h-[480px] bg-gradient-to-b from-background to-muted/30">
                                        <!-- Header -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h2 class="text-sm font-bold">Good morning! 👋</h2>
                                                <p class="text-[10px] text-muted-foreground">Your job search at a glance</p>
                                            </div>
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-[10px] font-medium">
                                                SC
                                            </div>
                                        </div>
                                        
                                        <!-- Stats Grid -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="bg-card border rounded-xl p-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-[9px] text-muted-foreground">Applications</span>
                                                    <div class="p-1 rounded bg-primary/10 text-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                                    </div>
                                                </div>
                                                <div class="text-lg font-bold">24</div>
                                            </div>
                                            <div class="bg-card border rounded-xl p-3">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-[9px] text-muted-foreground">Interviews</span>
                                                    <div class="p-1 rounded bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                    </div>
                                                </div>
                                                <div class="text-lg font-bold">5</div>
                                            </div>
                                        </div>
                                        
                                        <!-- AI Generate Button -->
                                        <div class="px-3 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-blue-600 text-white flex items-center justify-center gap-2 shadow-lg shadow-purple-500/25">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"/><path d="M17 4a2 2 0 0 0 2 2a2 2 0 0 0 -2 2a2 2 0 0 0 -2 -2a2 2 0 0 0 2 -2"/></svg>
                                            <span class="text-xs font-medium">Generate CV with AI</span>
                                        </div>
                                        
                                        <!-- Recent Applications -->
                                        <div class="bg-card border rounded-xl p-3">
                                            <h3 class="text-[11px] font-semibold mb-2">Recent Applications</h3>
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-7 w-7 rounded-lg bg-gradient-to-br from-blue-500/20 to-blue-500/10 flex items-center justify-center text-blue-600 font-semibold text-[9px]">G</div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[10px] font-medium truncate">Senior Frontend Engineer</p>
                                                        <p class="text-[9px] text-muted-foreground">Google</p>
                                                    </div>
                                                    <span class="px-1.5 py-0.5 rounded-full text-[8px] font-medium bg-purple-100 text-purple-700">Interviewing</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="h-7 w-7 rounded-lg bg-gradient-to-br from-green-500/20 to-green-500/10 flex items-center justify-center text-green-600 font-semibold text-[9px]">S</div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[10px] font-medium truncate">Full Stack Developer</p>
                                                        <p class="text-[9px] text-muted-foreground">Spotify</p>
                                                    </div>
                                                    <span class="px-1.5 py-0.5 rounded-full text-[8px] font-medium bg-green-100 text-green-700">Offered</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Actions -->
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="flex flex-col items-center gap-1.5 p-2 rounded-xl border bg-card">
                                                <div class="p-2 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                                                </div>
                                                <span class="text-[8px] font-medium">Track Job</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-1.5 p-2 rounded-xl border bg-card">
                                                <div class="p-2 rounded-lg bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                                                </div>
                                                <span class="text-[8px] font-medium">Create CV</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-1.5 p-2 rounded-xl border bg-card">
                                                <div class="p-2 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                                </div>
                                                <span class="text-[8px] font-medium">Cover Letter</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bottom navigation bar -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-card/95 backdrop-blur border-t px-6 py-2">
                                        <div class="flex items-center justify-around">
                                            <div class="flex flex-col items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                                <span class="text-[8px] font-medium text-primary">Home</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                                <span class="text-[8px] text-muted-foreground">Jobs</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/></svg>
                                                <span class="text-[8px] text-muted-foreground">CVs</span>
                                            </div>
                                            <div class="flex flex-col items-center gap-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                                <span class="text-[8px] text-muted-foreground">Profile</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Phone shadow/glow -->
                            <div class="absolute inset-x-4 -bottom-4 h-8 bg-gradient-to-t from-black/20 to-transparent blur-xl rounded-full" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="border-y border-border bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="stat in displayStats" :key="stat.label" class="text-center">
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
                    <Card v-for="testimonial in displayTestimonials" :key="testimonial.author" class="relative">
                        <CardContent class="pt-6">
                            <!-- Quote icon -->
                            <svg class="absolute top-6 right-6 h-8 w-8 text-muted-foreground/20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.192 15.757c0-.88-.23-1.618-.69-2.217-.326-.412-.768-.683-1.327-.812-.55-.128-1.07-.137-1.54-.028-.16-.95.1-1.956.76-3.022.66-1.065 1.515-1.867 2.558-2.403L9.373 5c-.8.396-1.56.898-2.26 1.505-.71.607-1.34 1.305-1.9 2.094s-.98 1.68-1.25 2.69-.346 2.04-.217 3.1c.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l.002.004zm9.124 0c0-.88-.23-1.618-.69-2.217-.326-.42-.77-.692-1.327-.817-.56-.124-1.074-.13-1.54-.022-.16-.94.09-1.95.75-3.02.66-1.06 1.514-1.86 2.557-2.4L18.49 5c-.8.396-1.555.898-2.26 1.505-.708.607-1.34 1.305-1.894 2.094-.556.79-.97 1.68-1.24 2.69-.273 1-.345 2.04-.217 3.1.168 1.4.62 2.52 1.356 3.35.735.84 1.652 1.26 2.748 1.26.965 0 1.766-.29 2.4-.878.628-.576.94-1.365.94-2.368l-.007.006z"/>
                            </svg>
                            
                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-3">
                                <svg v-for="star in testimonial.rating" :key="star" class="h-4 w-4 text-yellow-500 fill-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </div>
                            
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
                        Join over {{ stats.users_count }} professionals who have already created their perfect resume with CVverse. Start for free today.
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
