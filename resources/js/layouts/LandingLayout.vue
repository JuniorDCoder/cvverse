<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import NewsletterPopup from '@/components/NewsletterPopup.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Sheet, SheetContent, SheetTrigger, SheetClose } from '@/components/ui/sheet';
import { useAppearance } from '@/composables/useAppearance';
import { home, about, services, pricing, contact, login, register, dashboard, privacyPolicy, termsOfService, guides } from '@/routes';
import { index as templatesIndex } from '@/routes/templates';

const props = defineProps<{
    title?: string;
}>();

const currentYear = new Date().getFullYear();
const isScrolled = ref(false);
const { appearance, updateAppearance } = useAppearance();

const page = usePage();

const socialLinks = computed(() => (page.props.socialLinks ?? {}) as Record<string, string>);

const isActive = (path: string) => {
    return page.url === path || page.url.startsWith(path + '?');
};

const navLinks = [
    { name: 'Home', href: home, path: '/' },
    { name: 'About', href: about, path: '/about' },
    { name: 'Services', href: services, path: '/services' },
    { name: 'Templates', href: templatesIndex, path: '/templates' },
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
                        <SheetContent side="right" class="w-[300px] sm:w-[350px] p-6 flex flex-col">
                            <div class="flex flex-col h-full">
                                <div class="flex items-center justify-between mb-8">
                                    <Link :href="home()" class="flex items-center space-x-2">
                                        <AppLogoIcon class-name="h-10 w-10" />
                                        <span class="font-bold text-xl">CVverse</span>
                                    </Link>
                                </div>
                                
                                <nav class="flex flex-col space-y-4">
                                    <SheetClose as-child v-for="link in navLinks" :key="link.path">
                                        <Link
                                            :href="link.href()"
                                            class="flex items-center px-4 py-3 text-lg font-medium rounded-lg transition-colors"
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
                                
                                <Separator class="my-8" />
                                
                                <div class="flex flex-col gap-4">
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
                                <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="Twitter">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                    </svg>
                                </a>
                                <a v-if="socialLinks.github" :href="socialLinks.github" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="GitHub">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="LinkedIn">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                                <a v-if="socialLinks.facebook" :href="socialLinks.facebook" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="Facebook">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a v-if="socialLinks.instagram" :href="socialLinks.instagram" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="Instagram">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a v-if="socialLinks.youtube" :href="socialLinks.youtube" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-foreground transition-colors" aria-label="YouTube">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.418-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
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
                                    <Link :href="templatesIndex()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Templates
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="pricing()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Pricing
                                    </Link>
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
                                    <Link :href="guides()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Guides
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <!-- Legal -->
                        <div>
                            <h3 class="font-semibold mb-4 text-foreground">Legal</h3>
                            <ul class="space-y-3 text-sm">
                                <li>
                                    <Link :href="privacyPolicy()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Privacy Policy
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="termsOfService()" class="text-muted-foreground hover:text-foreground transition-colors">
                                        Terms of Service
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="py-6 border-t border-border flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-muted-foreground">
                        &copy; {{ currentYear }} CVverse. All rights reserved.
                    </p>
                    <div class="flex items-center gap-6">
                        <Link :href="privacyPolicy()" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Privacy
                        </Link>
                        <Link :href="termsOfService()" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Terms
                        </Link>
                        <Link :href="contact()" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                            Contact
                        </Link>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Newsletter Popup -->
        <NewsletterPopup />
    </div>
</template>
