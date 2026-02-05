<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CreditCard, CheckCircle, Package } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
    subscription?: {
        name: string;
        ends_at?: string;
        status: string;
        plan: {
            name: string;
            price: string;
            currency: string;
            interval: string;
            features: string[];
        }
    } | null;
}>();

const hasActiveSubscription = computed(() => !!props.subscription && props.subscription.status === 'active');

</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: '/settings' }, { title: 'Subscription' }]">
        <Head title="Subscription" />

        <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
            <div class="max-w-4xl w-full mx-auto space-y-6">
                
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Subscription</h1>
                    <p class="text-muted-foreground">Manage your billing and subscription plan.</p>
                </div>

                <!-- Active Subscription Card -->
                <Card v-if="hasActiveSubscription" class="border-green-200 dark:border-green-900 bg-green-50/50 dark:bg-green-900/10">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <CardTitle class="text-xl flex items-center gap-2">
                                    Current Plan: {{ subscription?.plan.name }}
                                    <Badge class="bg-green-500 hover:bg-green-600">Active</Badge>
                                </CardTitle>
                                <CardDescription>
                                    Your subscription is active. 
                                    <span v-if="subscription?.ends_at">Renews on {{ new Date(subscription.ends_at).toLocaleDateString() }}</span>
                                </CardDescription>
                            </div>
                            <Package class="h-12 w-12 text-green-600 opacity-50" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <h3 class="font-medium mb-2">Plan Features:</h3>
                                <ul class="space-y-1">
                                    <li v-for="feature in subscription?.plan.features" :key="feature" class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <CheckCircle class="h-4 w-4 text-green-500" />
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <div class="text-sm font-medium text-muted-foreground">Billing</div>
                                <div class="text-2xl font-bold">
                                    {{ subscription?.plan.price }} {{ subscription?.plan.currency }} 
                                    <span class="text-sm font-normal text-muted-foreground">/ {{ subscription?.plan.interval }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="justify-end gap-2">
                        <Button variant="outline" as-child>
                             <Link href="/pricing">Change Plan</Link>
                        </Button>
                        <Button variant="destructive">Cancel Subscription</Button>
                    </CardFooter>
                </Card>

                <!-- No Subscription State -->
                <Card v-else class="text-center py-12">
                    <CardContent class="space-y-4">
                        <div class="mx-auto w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                             <CreditCard class="h-8 w-8 text-primary" />
                        </div>
                        <h2 class="text-xl font-semibold">No Active Subscription</h2>
                        <p class="text-muted-foreground max-w-md mx-auto">
                            You are currently on the Free plan. Upgrade to unlock premium features like unlimited CVs, AI suggestions, and more.
                        </p>
                        <div class="pt-4">
                            <Button size="lg" as-child>
                                <Link href="/pricing">View Plans & Pricing</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>
