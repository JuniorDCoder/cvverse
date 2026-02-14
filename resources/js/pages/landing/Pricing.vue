<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Check, Info } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { dashboard, register, subscription } from '@/routes';
import payment from '@/routes/payment';

interface Plan {
    id: number;
    name: string;
    description: string;
    price: string;
    currency: string;
    interval: string;
    features: string[];
    is_active: boolean;
    slug: string;
}

interface CurrentPlan {
    id: number | null;
    name: string;
    slug: string;
    status: 'guest' | 'free' | 'active';
    is_free: boolean;
    subscription_ends_at?: string | null;
}

const props = defineProps<{
    plans: Plan[];
    currency: string;
    country: string;
    currentPlan: CurrentPlan;
}>();

const billingCycle = ref<'monthly' | 'yearly'>('monthly');

// Helper to determine if a plan should be shown based on toggle
const filteredPlans = computed(() => {
    // Always show one_time plans (like Lifetime deals) or Free plans (price == 0) regardless of toggle?
    // Or maybe map 'monthly' to monthly plans + free plans, and 'yearly' to yearly plans + free plans?
    // For now, let's follow the standard pattern:
    // If it's a subscription (monthly/yearly), filter by cycle.
    // If it's a one-time purchase, maybe show it always or in a separate section?
    // Let's assume standard SaaS model for now.
    
    return props.plans.filter(p => {
        // Always show free plans
        if (parseFloat(p.price) === 0) return true;
        
        if (p.interval === 'one_time') return true; // Show lifetime deals always
        
        return p.interval === billingCycle.value;
    }).sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
});

const hasAuthenticatedUser = computed(() => !!usePage().props.auth.user);
const isUserOnFreePlan = computed(() => props.currentPlan?.is_free === true);

const isCurrentPlan = (plan: Plan) => {
    if (props.currentPlan?.status === 'active' && props.currentPlan?.id) {
        return props.currentPlan.id === plan.id;
    }

    return isUserOnFreePlan.value && parseFloat(plan.price) === 0;
};

const handleSubscribe = (plan: Plan) => {
    if (hasAuthenticatedUser.value) {
        if (isCurrentPlan(plan)) {
            router.visit(subscription().url);

            return;
        }

        if (parseFloat(plan.price) === 0) {
             // Ensure we handle free plan "subscription" logically, maybe just redirect to dashboard or a specific route
             // For now, let's assume checkout handles it (e.g. logs them in/upgrades them) or we just go to register
             router.visit(dashboard());
        } else {
            router.visit(payment.checkout(plan.id));
        }
    } else {
        router.visit(register());
    }
};

const faqs = [
    {
        question: 'Can I try CVverse for free?',
        answer: 'Yes! Our Free plan gives you access to basic features with no time limit. You can create 1 CV and download it as a PDF anytime.',
    },
    {
        question: 'What happens when my trial ends?',
        answer: 'If you are on a trial, you will be downgraded to the Free plan automatically.',
    },
    {
        question: 'Can I switch plans anytime?',
        answer: 'Absolutely! You can upgrade or downgrade your plan at any time.',
    },
     {
        question: 'What payment methods do you accept?',
        answer: 'We accept Mobile Money (Cameroon) and Credit Cards (Global).',
    },
];
</script>

<template>
    <LandingLayout title="Pricing">
        <!-- Background Gradients -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
             <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary/20 rounded-full blur-3xl opacity-30 animate-pulse" />
             <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl opacity-30 animate-pulse delay-1000" />
        </div>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-32 overflow-hidden">
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <Badge variant="outline" class="mb-6 border-primary/20 bg-primary/5 text-primary">Simple Pricing</Badge>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold tracking-tight mb-8">
                    Choose the plan that's
                    <span class="relative inline-block">
                        <span class="absolute -inset-1 bg-gradient-to-r from-primary to-purple-600 blur-2xl opacity-30"></span>
                        <span class="relative bg-gradient-to-r from-primary to-purple-600 bg-clip-text text-transparent">
                            right for you
                        </span>
                    </span>
                </h1>
                <p class="text-xl text-muted-foreground mb-12 max-w-2xl mx-auto">
                    Whether you're just starting out or ready to accelerate your career, we have a plan to help you land your dream job.
                </p>

                <div v-if="hasAuthenticatedUser" class="mb-8">
                    <Badge variant="outline" class="border-primary/20 bg-primary/5 text-primary">
                        Current Plan: {{ currentPlan.name }}
                    </Badge>
                </div>
                
                <!-- Billing Toggle -->
                <div class="inline-flex items-center p-1 bg-muted/50 backdrop-blur-sm border rounded-full relative">
                    <button
                        @click="billingCycle = 'monthly'"
                        class="relative z-10 px-6 py-2.5 text-sm font-medium rounded-full transition-all duration-300 ease-in-out"
                        :class="billingCycle === 'monthly' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                    >
                        Monthly
                    </button>
                    <button
                        @click="billingCycle = 'yearly'"
                        class="relative z-10 px-6 py-2.5 text-sm font-medium rounded-full transition-all duration-300 ease-in-out flex items-center gap-2"
                        :class="billingCycle === 'yearly' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                    >
                        Yearly
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-green-500/10 text-green-600 border border-green-500/20">
                            -20%
                        </span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Pricing Cards -->
        <section class="pb-32">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                    <!-- Dynamic Plans -->
                    <div 
                        v-for="plan in filteredPlans" 
                        :key="plan.id"
                        class="group relative"
                    >
                        <!-- Glow Effect for Popular Plans (Optional logic, e.g. if price > 0 and price < max) -->
                        <div v-if="parseFloat(plan.price) > 0" class="absolute -inset-[1px] bg-gradient-to-b from-primary/50 to-purple-600/50 rounded-2xl blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                        
                        <Card class="relative h-full flex flex-col bg-card/50 backdrop-blur-xl border-muted/50 transition-all duration-300 group-hover:-translate-y-1">
                            <CardHeader class="pb-8">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <CardTitle class="text-2xl font-bold mb-2">{{ plan.name }}</CardTitle>
                                        <CardDescription>{{ parseFloat(plan.price) === 0 ? 'Forever free' : 'Perfect for professionals' }}</CardDescription>
                                    </div>
                                    <Badge v-if="isCurrentPlan(plan)" variant="default" class="bg-emerald-600 text-white">
                                        Current Plan
                                    </Badge>
                                    <Badge v-else-if="parseFloat(plan.price) > 0" variant="secondary" class="bg-primary/10 text-primary border-primary/20">
                                        Popular
                                    </Badge>
                                </div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-5xl font-bold tracking-tight">
                                        {{ parseFloat(plan.price) === 0 ? 'Free' : (plan.currency === 'USD' ? '$' : '') + Number(plan.price).toLocaleString() + (plan.currency !== 'USD' ? ' ' + plan.currency : '') }}
                                    </span>
                                    <span v-if="parseFloat(plan.price) > 0" class="text-muted-foreground">
                                        /{{ plan.interval === 'one_time' ? 'lifetime' : plan.interval }}
                                    </span>
                                </div>
                            </CardHeader>
                            
                            <CardContent class="flex-1">
                                <Separator class="mb-8" />
                                <ul class="space-y-4">
                                    <li 
                                        v-for="(feature, idx) in plan.features" 
                                        :key="idx"
                                        class="flex items-start gap-3"
                                    >
                                        <div class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <Check class="w-3.5 h-3.5" />
                                        </div>
                                        <span class="text-sm text-muted-foreground">{{ feature }}</span>
                                    </li>
                                </ul>
                            </CardContent>
                            
                            <CardFooter>
                                <Button 
                                    class="w-full h-12 text-base font-semibold shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all"
                                    :variant="parseFloat(plan.price) === 0 ? 'outline' : 'default'"
                                    :disabled="isCurrentPlan(plan)"
                                    @click="handleSubscribe(plan)"
                                >
                                    {{
                                        isCurrentPlan(plan)
                                            ? 'Current Plan'
                                            : (parseFloat(plan.price) === 0 ? 'Get Started' : 'Subscribe Now')
                                    }}
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>

                    <div v-if="filteredPlans.length === 0" class="col-span-full text-center py-20">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-muted mb-4">
                            <Info class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <h3 class="text-xl font-semibold mb-2">No plans available</h3>
                        <p class="text-muted-foreground">
                            We currently don't have any plans available for {{ billingCycle }} billing in your region.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 lg:py-32 bg-muted/30">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <Badge variant="outline" class="mb-4">FAQ</Badge>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">
                        Frequently asked questions
                    </h2>
                    <p class="text-muted-foreground">
                        Everything you need to know about plans and billing.
                    </p>
                </div>
                
                <div class="max-w-3xl mx-auto grid gap-6">
                    <Card 
                        v-for="faq in faqs" 
                        :key="faq.question"
                        class="bg-background/50 backdrop-blur-sm hover:shadow-md transition-all duration-300"
                    >
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">{{ faq.question }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground leading-relaxed">{{ faq.answer }}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
