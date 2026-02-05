<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { services, pricing, about } from '@/routes';

interface Department {
    name: string;
    email: string;
    description: string;
}

interface ContactData {
    site_name: string;
    support_email: string;
    phone: string;
    address: string;
    city: string;
    country: string;
    timezone: string;
    departments: Department[];
    social: Record<string, string>;
}

const props = defineProps<{
    contactData: ContactData;
}>();

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

const contactMethods = computed(() => [
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>`,
        title: 'Email',
        description: 'Our friendly team is here to help.',
        value: props.contactData.support_email,
        href: `mailto:${props.contactData.support_email}`,
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
        value: props.contactData.phone,
        href: `tel:${props.contactData.phone.replace(/[^0-9+]/g, '')}`,
    },
    {
        icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
        title: 'Office',
        description: 'Come say hello at our HQ.',
        value: `${props.contactData.address}, ${props.contactData.city}`,
        href: '#',
    },
]);

const office = computed(() => ({
    city: props.contactData.city,
    country: props.contactData.country,
    address: props.contactData.address,
    timezone: props.contactData.timezone,
}));

const socialIcons: Record<string, string> = {
    twitter: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
    facebook: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>`,
    linkedin: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>`,
    instagram: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>`,
    youtube: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>`,
    github: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>`,
};

const activeSocialLinks = computed(() => {
    const links: { name: string; url: string; icon: string }[] = [];
    for (const [key, url] of Object.entries(props.contactData.social)) {
        if (url && socialIcons[key]) {
            links.push({
                name: key.charAt(0).toUpperCase() + key.slice(1),
                url,
                icon: socialIcons[key],
            });
        }
    }
    return links;
});
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
                                v-for="dept in contactData.departments" 
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

        <!-- Office Location & Social Links -->
        <section class="py-20 lg:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
                    <!-- Office Location -->
                    <div>
                        <Badge variant="outline" class="mb-4">Our Office</Badge>
                        <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                            Visit us
                        </h2>
                        <p class="text-lg text-muted-foreground mb-8">
                            We're always here when you need us.
                        </p>
                        
                        <Card class="hover:shadow-lg transition-shadow">
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

                    <!-- Social Links -->
                    <div v-if="activeSocialLinks.length > 0">
                        <Badge variant="outline" class="mb-4">Follow Us</Badge>
                        <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                            Stay connected
                        </h2>
                        <p class="text-lg text-muted-foreground mb-8">
                            Follow us on social media for updates and tips.
                        </p>

                        <div class="grid grid-cols-2 gap-4">
                            <a 
                                v-for="social in activeSocialLinks" 
                                :key="social.name"
                                :href="social.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center gap-3 p-4 rounded-xl border bg-card hover:shadow-lg hover:border-primary/50 transition-all duration-300"
                            >
                                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center" v-html="social.icon" />
                                <span class="font-medium">{{ social.name }}</span>
                            </a>
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
