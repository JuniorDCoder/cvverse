<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { services, pricing, about } from '@/routes';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';

const isSubmitting = ref(false);
const isSubmitted = ref(false);

const form = useForm({
    name: '',
    email: '',
    company: '',
    subject: '',
    message: '',
});

const submitForm = () => {
    isSubmitting.value = true;
    // Simulate form submission
    setTimeout(() => {
        isSubmitting.value = false;
        isSubmitted.value = true;
        form.reset();
    }, 1500);
};

const contactMethods = [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`,
        title: 'Email',
        description: 'Our friendly team is here to help.',
        value: 'support@cvverse.com',
        href: 'mailto:support@cvverse.com',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>`,
        title: 'Live Chat',
        description: 'Available 24/7 for Pro users.',
        value: 'Start a conversation',
        href: '#',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`,
        title: 'Phone',
        description: 'Mon-Fri from 8am to 5pm.',
        value: '+1 (555) 123-4567',
        href: 'tel:+15551234567',
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
        title: 'Office',
        description: 'Come say hello at our HQ.',
        value: '123 Innovation Drive, SF',
        href: '#',
    },
];

const offices = [
    {
        city: 'San Francisco',
        country: 'USA',
        address: '123 Innovation Drive, Suite 400',
        timezone: 'PST (UTC-8)',
    },
    {
        city: 'London',
        country: 'UK',
        address: '45 Tech Square, Floor 12',
        timezone: 'GMT (UTC+0)',
    },
    {
        city: 'Singapore',
        country: 'Singapore',
        address: '78 Marina Bay Tower',
        timezone: 'SGT (UTC+8)',
    },
];

const departments = [
    { name: 'Sales', email: 'sales@cvverse.com', description: 'For pricing and enterprise inquiries' },
    { name: 'Support', email: 'support@cvverse.com', description: 'For technical help and questions' },
    { name: 'Press', email: 'press@cvverse.com', description: 'For media and press inquiries' },
    { name: 'Partnerships', email: 'partners@cvverse.com', description: 'For collaboration opportunities' },
];
</script>

<template>
    <LandingLayout title="Contact Us">
        <!-- Hero Section -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.1),rgba(255,255,255,0))]" />
            
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Contact Us</Badge>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
                        We'd love to
                        <span class="bg-gradient-to-r from-primary via-purple-500 to-pink-500 bg-clip-text text-transparent">
                            hear from you
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground">
                        Have a question, feedback, or just want to say hello? We're here to help and always happy to chat.
                    </p>
                </div>
            </div>
        </section>

        <!-- Contact Methods -->
        <section class="pb-20 lg:pb-28 -mt-8">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                    <Card 
                        v-for="method in contactMethods" 
                        :key="method.title"
                        class="text-center hover:shadow-lg transition-all duration-300 hover:border-primary/50"
                    >
                        <CardContent class="pt-6">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center mx-auto mb-4">
                                <div v-html="method.icon" />
                            </div>
                            <h3 class="font-semibold text-lg mb-1">{{ method.title }}</h3>
                            <p class="text-sm text-muted-foreground mb-3">{{ method.description }}</p>
                            <a 
                                :href="method.href" 
                                class="text-sm font-medium text-primary hover:underline"
                            >
                                {{ method.value }}
                            </a>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Contact Form & Info -->
        <section class="py-20 lg:py-28 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 max-w-6xl mx-auto">
                    <!-- Contact Form -->
                    <div>
                        <Badge variant="outline" class="mb-4">Send a Message</Badge>
                        <h2 class="text-3xl font-bold mb-4">Get in touch</h2>
                        <p class="text-muted-foreground mb-8">
                            Fill out the form below and we'll get back to you within 24 hours.
                        </p>
                        
                        <div v-if="isSubmitted" class="bg-green-500/10 border border-green-500/20 rounded-xl p-8 text-center">
                            <div class="w-16 h-16 rounded-full bg-green-500/20 text-green-500 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20,6 9,17 4,12"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Message Sent!</h3>
                            <p class="text-muted-foreground mb-6">
                                Thank you for reaching out. We'll get back to you soon.
                            </p>
                            <Button variant="outline" @click="isSubmitted = false">
                                Send Another Message
                            </Button>
                        </div>
                        
                        <form v-else @submit.prevent="submitForm" class="space-y-6">
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="name">Full Name *</Label>
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        type="text" 
                                        placeholder="John Doe"
                                        required 
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="email">Email Address *</Label>
                                    <Input 
                                        id="email" 
                                        v-model="form.email" 
                                        type="email" 
                                        placeholder="john@example.com"
                                        required 
                                    />
                                </div>
                            </div>
                            
                            <div class="grid sm:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label for="company">Company</Label>
                                    <Input 
                                        id="company" 
                                        v-model="form.company" 
                                        type="text" 
                                        placeholder="Your company"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="subject">Subject *</Label>
                                    <Input 
                                        id="subject" 
                                        v-model="form.subject" 
                                        type="text" 
                                        placeholder="How can we help?"
                                        required 
                                    />
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <Label for="message">Message *</Label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="5"
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-input/30"
                                    placeholder="Tell us more about your inquiry..."
                                    required
                                />
                            </div>
                            
                            <Button 
                                type="submit" 
                                size="lg" 
                                class="w-full sm:w-auto"
                                :disabled="isSubmitting"
                            >
                                <span v-if="isSubmitting" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sending...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Send Message
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="22" y1="2" x2="11" y2="13"/>
                                        <polygon points="22,2 15,22 11,13 2,9"/>
                                    </svg>
                                </span>
                            </Button>
                        </form>
                    </div>
                    
                    <!-- Department Contacts -->
                    <div>
                        <Badge variant="outline" class="mb-4">Departments</Badge>
                        <h2 class="text-3xl font-bold mb-4">Reach the right team</h2>
                        <p class="text-muted-foreground mb-8">
                            Not sure who to contact? Here are the best ways to reach each department.
                        </p>
                        
                        <div class="space-y-4">
                            <Card 
                                v-for="dept in departments" 
                                :key="dept.name"
                                class="hover:shadow-md transition-shadow"
                            >
                                <CardContent class="py-4 flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold">{{ dept.name }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ dept.description }}</p>
                                    </div>
                                    <a 
                                        :href="`mailto:${dept.email}`" 
                                        class="text-sm font-medium text-primary hover:underline"
                                    >
                                        {{ dept.email }}
                                    </a>
                                </CardContent>
                            </Card>
                        </div>
                        
                        <Separator class="my-8" />
                        
                        <!-- FAQ Link -->
                        <div class="bg-card border rounded-xl p-6">
                            <h3 class="font-semibold mb-2">Looking for quick answers?</h3>
                            <p class="text-sm text-muted-foreground mb-4">
                                Check out our comprehensive FAQ section or browse our help documentation.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="pricing()">
                                        View FAQ
                                    </Link>
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <a href="#">
                                        Help Center
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Office Locations -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">Our Offices</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Global presence, local support
                    </h2>
                    <p class="text-lg text-muted-foreground">
                        With offices around the world, we're always here when you need us.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <Card 
                        v-for="office in offices" 
                        :key="office.city"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <CardHeader>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                                <div>
                                    <CardTitle class="text-lg">{{ office.city }}</CardTitle>
                                    <CardDescription>{{ office.country }}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground mb-2">{{ office.address }}</p>
                            <p class="text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12,6 12,12 16,14"/>
                                    </svg>
                                    {{ office.timezone }}
                                </span>
                            </p>
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
                        Ready to get started?
                    </h2>
                    <p class="text-lg text-muted-foreground mb-8">
                        Join thousands of professionals who have already created their perfect CV with CVverse.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Button size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="services()">
                                Explore Features
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                        <Button variant="outline" size="lg" as-child class="w-full sm:w-auto text-base px-8">
                            <Link :href="about()">
                                About Us
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
