<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetTrigger, SheetClose } from '@/components/ui/sheet';
import { useAppearance } from '@/composables/useAppearance';
import { home, about, services, pricing, contact, login, register, dashboard } from '@/routes';

const props = defineProps<{
    title?: string;
}>();

const currentYear = new Date().getFullYear();
const isScrolled = ref(false);
const { appearance, updateAppearance } = useAppearance();

const page = usePage();

const isActive = (path: string) => {
    return page.url === path || page.url.startsWith(path + '?');
};

const navLinks = [
    { name: 'Home', href: home, path: '/' },
    { name: 'About', href: about, path: '/about' },
    { name: 'Services', href: services, path: '/services' },
    { name: 'Pricing', href: pricing, path: '/pricing' },
    { name: 'Contact', href: contact, path: '/contact' },
];

const handleScroll = () => {
    isScrolled.value = window.scrollY > 10;
};

const toggleTheme = () => {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen flex flex-col bg-background text-foreground">
        <!-- Header -->
        <header 
            class="sticky top-0 z-50 w-full transition-all duration-300"
            :class="[
                isScrolled 
                    ? 'border-b border-border/40 bg-background/80 backdrop-blur-xl shadow-sm' 
                    : 'bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60'
            ]"
        >
            <div class="container flex h-16 items-center justify-between mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <Link :href="home()" class="flex items-center space-x-2 group">
                    <AppLogoIcon class-name="h-10 w-10 transition-transform group-hover:scale-105" />
                    <span class="font-bold text-xl bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text">CVverse</span>
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center space-x-1">
                    <Link
                        v-for="link in navLinks"
                        :key="link.path"
                        :href="link.href()"
                        class="relative px-4 py-2 text-sm font-medium transition-colors rounded-md hover:bg-accent"
                        :class="[
                            isActive(link.path) 
                                ? 'text-foreground' 
                                : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        {{ link.name }}
                        <span 
                            v-if="isActive(link.path)" 
                            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 bg-primary rounded-full"
                        />
                    </Link>
                </nav>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2">
                    <!-- Theme Toggle -->
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        @click="toggleTheme"
                        class="hidden sm:flex"
                    >
                        <svg v-if="appearance === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="4"/>
                            <path d="M12 2v2"/>
                            <path d="M12 20v2"/>
                            <path d="m4.93 4.93 1.41 1.41"/>
                            <path d="m17.66 17.66 1.41 1.41"/>
                            <path d="M2 12h2"/>
                            <path d="M20 12h2"/>
                            <path d="m6.34 17.66-1.41 1.41"/>
                            <path d="m19.07 4.93-1.41 1.41"/>
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                        </svg>
                    </Button>

                    <!-- Auth Buttons -->
                    <template v-if="$page.props.auth.user">
                        <Button as-child>
                            <Link :href="dashboard()">
                                Dashboard
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child class="hidden sm:inline-flex">
                            <Link :href="login()">
                                Log in
                            </Link>
                        </Button>
                        <Button as-child>
                            <Link :href="register()">
                                Get Started
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </Link>
                        </Button>
                    </template>

                    <!-- Mobile Menu -->
                    <Sheet>
                        <SheetTrigger as-child>
                            <Button variant="ghost" size="icon" class="md:hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" x2="20" y1="12" y2="12"/>
                                    <line x1="4" x2="20" y1="6" y2="6"/>
                                    <line x1="4" x2="20" y1="18" y2="18"/>
                                </svg>
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="right" class="w-[300px] sm:w-[350px]">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <Link :href="home()" class="flex items-center space-x-2">
                                        <AppLogoIcon class-name="h-10 w-10" />
                                        <span class="font-bold text-xl">CVverse</span>
                                    </Link>
                                </div>
                                
                                <nav class="flex flex-col space-y-1">
                                    <SheetClose as-child v-for="link in navLinks" :key="link.path">
                                        <Link
                                            :href="link.href()"
                                            class="flex items-center px-4 py-3 text-base font-medium rounded-lg transition-colors"
                                            :class="[
                                                isActive(link.path) 
                                                    ? 'bg-accent text-foreground' 
                                                    : 'text-muted-foreground hover:text-foreground hover:bg-accent/50'
                                            ]"
                                        >
                                            {{ link.name }}
                                        </Link>
                                    </SheetClose>
                                </nav>
                                
                                <Separator class="my-6" />
                                
                                <div class="flex flex-col gap-3">
                                    <template v-if="!$page.props.auth.user">
                                        <SheetClose as-child>
                                            <Button variant="outline" as-child class="w-full">
                                                <Link :href="login()">Log in</Link>
                                            </Button>
                                        </SheetClose>
                                        <SheetClose as-child>
                                            <Button as-child class="w-full">
                                                <Link :href="register()">Get Started</Link>
                                            </Button>
                                        </SheetClose>
                                    </template>
                                    <template v-else>
                                        <SheetClose as-child>
                                            <Button as-child class="w-full">
                                                <Link :href="dashboard()">Dashboard</Link>
                                            </Button>
                                        </SheetClose>
                                    </template>
                                </div>
                                
                                <div class="mt-auto pt-6">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-muted-foreground">Theme</span>
                                        <Button variant="outline" size="sm" @click="toggleTheme">
                                            <svg v-if="appearance === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="4"/>
                                                <path d="M12 2v2"/>
                                                <path d="M12 20v2"/>
                                                <path d="m4.93 4.93 1.41 1.41"/>
                                                <path d="m17.66 17.66 1.41 1.41"/>
                                                <path d="M2 12h2"/>
                                                <path d="M20 12h2"/>
                                                <path d="m6.34 17.66-1.41 1.41"/>
                                                <path d="m19.07 4.93-1.41 1.41"/>
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                                            </svg>
                                            {{ appearance === 'dark' ? 'Light' : 'Dark' }}
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Main Footer Content -->
                <div class="py-12 lg:py-16">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 lg:gap-12">
                        <!-- Brand -->
                        <div class="col-span-2 lg:col-span-1">
                            <Link :href="home()" class="flex items-center space-x-2 mb-4">
                                <AppLogoIcon class-name="h-10 w-10" />
                                <span class="font-bold text-xl">CVverse</span>
                            </Link>
                            <p class="text-sm text-muted-foreground max-w-xs">
                                Create professional CVs and resumes with AI-powered tools. Stand out from the crowd and land your dream job.
                            </p>
                            <div class="flex items-center space-x-4 mt-6">
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="Twitter">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                    </svg>
                                </a>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="GitHub">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="#" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="LinkedIn">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Product -->
                        <div>
                            <h3 class="font-semibold mb-4 text-foreground">Product</h3>
                            <ul class="space-y-3 text-sm">
                                <li>
                                    <Link :href="services()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Features
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="pricing()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Pricing
                                    </Link>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Templates
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Integrations
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Company -->
                        <div>
                            <h3 class="font-semibold mb-4 text-foreground">Company</h3>
                            <ul class="space-y-3 text-sm">
                                <li>
                                    <Link :href="about()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        About Us
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="contact()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Contact
                                    </Link>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Blog
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Careers
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Resources -->
                        <div>
                            <h3 class="font-semibold mb-4 text-foreground">Resources</h3>
                            <ul class="space-y-3 text-sm">
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Help Center
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Documentation
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Guides
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        API Reference
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Legal -->
                        <div>
                            <h3 class="font-semibold mb-4 text-foreground">Legal</h3>
                            <ul class="space-y-3 text-sm">
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Privacy Policy
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Terms of Service
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Cookie Policy
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                                        GDPR
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="py-6 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-muted-foreground">
                        © {{ currentYear }} CVverse. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Status
                        </a>
                        <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Security
                        </a>
                        <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Sitemap
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
