<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
    step?: number;
    totalSteps?: number;
}>();

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-svh bg-gradient-to-br from-background via-muted/20 to-background">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 z-50 border-b bg-background/80 backdrop-blur-md">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <Link :href="home()" class="flex items-center gap-2">
                        <AppLogoIcon class="size-10" />
                        <span class="font-bold text-xl">CVverse</span>
                    </Link>
                    
                    <Button variant="ghost" size="sm" @click="logout">
                        <LogOut class="h-4 w-4 mr-2" />
                        Logout
                    </Button>
                </div>
            </div>
        </header>

        <!-- Progress bar -->
        <div v-if="step && totalSteps" class="fixed top-16 left-0 right-0 z-40">
            <div class="h-1 bg-muted">
                <div 
                    class="h-full bg-gradient-to-r from-primary to-purple-500 transition-all duration-500"
                    :style="{ width: `${(step / totalSteps) * 100}%` }"
                />
            </div>
        </div>

        <!-- Main content -->
        <main class="pt-24 pb-12">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto">
                    <!-- Step indicator -->
                    <div v-if="step && totalSteps" class="mb-8 text-center">
                        <span class="text-sm text-muted-foreground">
                            Step {{ step }} of {{ totalSteps }}
                        </span>
                    </div>

                    <!-- Card container -->
                    <div class="bg-card border rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-8 sm:p-12">
                            <!-- Header -->
                            <div v-if="title || description" class="text-center mb-8">
                                <h1 v-if="title" class="text-2xl sm:text-3xl font-bold mb-2">
                                    {{ title }}
                                </h1>
                                <p v-if="description" class="text-muted-foreground">
                                    {{ description }}
                                </p>
                            </div>

                            <!-- Slot content -->
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
