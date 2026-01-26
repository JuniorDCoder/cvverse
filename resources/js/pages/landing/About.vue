<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { contact, register, services } from '@/routes';

const values = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>`,
        title: 'Trust & Security',
        description: 'Your data is encrypted and secure. We never share your information without explicit consent.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
        title: 'User-Centric Design',
        description: 'Every feature is designed with the user in mind. Simple, intuitive, and effective.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v10"/><path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"/><path d="M1 12h6m6 0h10"/><path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"/></svg>`,
        title: 'Innovation First',
        description: 'We leverage cutting-edge AI technology to give you the best possible results.',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>`,
        title: 'Quality Matters',
        description: 'We maintain high standards in everything we do, from code to customer service.',
    },
];

const team = [
    {
        name: 'Alex Morgan',
        role: 'CEO & Founder',
        avatar: 'AM',
        bio: 'Former HR Director with 15+ years in talent acquisition. Passionate about helping people showcase their potential.',
    },
    {
        name: 'Sarah Chen',
        role: 'CTO',
        avatar: 'SC',
        bio: 'Ex-Google engineer specializing in AI/ML. Building the technology that powers smart CV creation.',
    },
    {
        name: 'Michael Brooks',
        role: 'Head of Design',
        avatar: 'MB',
        bio: 'Award-winning designer focused on creating beautiful, functional templates that get results.',
    },
    {
        name: 'Emily Watson',
        role: 'Head of Customer Success',
        avatar: 'EW',
        bio: 'Dedicated to ensuring every user has an exceptional experience with CVverse.',
    },
];

const milestones = [
    { year: '2020', title: 'CVverse Founded', description: 'Started with a vision to democratize professional CV creation.' },
    { year: '2021', title: '100K Users', description: 'Reached our first major milestone with 100,000 registered users.' },
    { year: '2022', title: 'AI Integration', description: 'Launched AI-powered content suggestions and writing assistance.' },
    { year: '2023', title: '500K+ CVs Created', description: 'Celebrated half a million successful CV creations.' },
    { year: '2024', title: 'Global Expansion', description: 'Expanded to support 50+ languages and 150+ countries.' },
];
</script>

<template>
    <LandingLayout title="About Us">
        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.1),rgba(255,255,255,0))]" />
            
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">About CVverse</Badge>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        Empowering careers,
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            one CV at a time
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground">
                        We believe everyone deserves the opportunity to present their best selves. Our mission is to make professional CV creation accessible, intuitive, and effective for job seekers worldwide.
                    </p>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                    <div>
                        <Badge variant="outline" class="mb-4">Our Story</Badge>
                        <h2 class="text-3xl lg:text-4xl font-bold mb-6">
                            From frustration to innovation
                        </h2>
                        <div class="space-y-4 text-muted-foreground">
                            <p>
                                CVverse was born from a simple observation: creating a professional CV shouldn't be complicated. Our founder, Alex Morgan, spent years watching talented candidates struggle to present themselves effectively on paper.
                            </p>
                            <p>
                                After seeing countless qualified professionals lose opportunities due to poorly formatted resumes, we set out to build a solution that levels the playing field. Today, CVverse helps hundreds of thousands of people worldwide create CVs that truly represent their potential.
                            </p>
                            <p>
                                We combine cutting-edge AI technology with beautiful design templates, making it possible for anyone to create a standout CV in minutes, not hours.
                            </p>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 via-purple-500/20 to-pink-500/20 rounded-2xl blur-2xl opacity-50" />
                        <div class="relative bg-card border rounded-xl p-8 shadow-lg">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-foreground mb-2">500K+</div>
                                    <div class="text-sm text-muted-foreground">CVs Created</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-foreground mb-2">150+</div>
                                    <div class="text-sm text-muted-foreground">Countries</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-foreground mb-2">95%</div>
                                    <div class="text-sm text-muted-foreground">Success Rate</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-foreground mb-2">4.9</div>
                                    <div class="text-sm text-muted-foreground">User Rating</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Our Values</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        What drives us forward
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        These core principles guide every decision we make and every feature we build.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card 
                        v-for="value in values" 
                        :key="value.title"
                        class="text-center hover:shadow-lg transition-all duration-300 hover:border-primary/50"
                    >
                        <CardContent class="pt-8">
                            <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                                <div v-html="value.icon" />
                            </div>
                            <h3 class="font-semibold text-lg mb-2">{{ value.title }}</h3>
                            <p class="text-sm text-muted-foreground">{{ value.description }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Timeline Section -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Our Journey</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Milestones along the way
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        From a small startup to a global platform, here's our journey so far.
                    </p>
                </div>
                
                <div class="max-w-4xl mx-auto">
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-4 lg:left-1/2 lg:-translate-x-px top-0 bottom-0 w-0.5 bg-border" />
                        
                        <!-- Timeline items -->
                        <div class="space-y-12">
                            <div 
                                v-for="(milestone, index) in milestones" 
                                :key="milestone.year"
                                class="relative flex items-start gap-6 lg:gap-0"
                                :class="index % 2 === 0 ? 'lg:flex-row' : 'lg:flex-row-reverse'"
                            >
                                <!-- Dot -->
                                <div class="absolute left-4 lg:left-1/2 -translate-x-1/2 w-3 h-3 rounded-full bg-primary border-4 border-background z-10" />
                                
                                <!-- Content -->
                                <div 
                                    class="ml-12 lg:ml-0 lg:w-1/2 lg:px-8"
                                    :class="index % 2 === 0 ? 'lg:text-right lg:pr-12' : 'lg:text-left lg:pl-12'"
                                >
                                    <div class="bg-card border rounded-lg p-6 shadow-sm">
                                        <span class="inline-block px-3 py-1 text-sm font-semibold text-primary bg-primary/10 rounded-full mb-3">
                                            {{ milestone.year }}
                                        </span>
                                        <h3 class="font-semibold text-lg mb-2">{{ milestone.title }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ milestone.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Our Team</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Meet the people behind CVverse
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        A passionate team dedicated to helping you succeed in your career.
                    </p>
                </div>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card 
                        v-for="member in team" 
                        :key="member.name"
                        class="text-center hover:shadow-lg transition-all duration-300"
                    >
                        <CardContent class="pt-8">
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold">
                                {{ member.avatar }}
                            </div>
                            <h3 class="font-semibold text-lg mb-1">{{ member.name }}</h3>
                            <p class="text-sm text-primary font-medium mb-3">{{ member.role }}</p>
                            <p class="text-sm text-muted-foreground">{{ member.bio }}</p>
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
                        Join us on this journey
                    </h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Whether you're looking for your first job or making a career change, we're here to help you succeed.
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
                                Contact Us
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
