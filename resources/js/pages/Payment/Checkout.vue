<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Loader2, CreditCard, Smartphone } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import payment from '@/routes/payment';

const props = defineProps<{
    plan: any;
}>();

const paymentMethod = ref('mobile_money');
const successMessage = ref('');
const errorMessage = ref('');

const form = useForm({
    payer: '', // Phone number
    service: 'MTN', // MTN, ORANGE, etc.
});

const submit = () => {
    successMessage.value = '';
    errorMessage.value = '';
    
    form.post(payment.process(props.plan.id), {
        onSuccess: () => {
            successMessage.value = "Payment Initiated. Please check your phone to confirm the transaction.";
        },
        onError: () => {
            errorMessage.value = "Something went wrong. Please try again.";
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Pricing', href: '/pricing' }, { title: 'Checkout' }]">
        <Head title="Checkout" />

        <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
            <div class="flex h-full items-center justify-center p-4">
                <Card class="w-full max-w-md">
                    <CardHeader>
                        <CardTitle>Checkout - {{ plan.name }}</CardTitle>
                        <CardDescription>
                            You are subscribing to the {{ plan.name }} plan for 
                            <strong>{{ Number(plan.price).toLocaleString() }} {{ plan.currency }}</strong> / {{ plan.interval }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Tabs default-value="mobile_money" class="w-full">
                            <TabsList class="grid w-full grid-cols-2">
                                <TabsTrigger value="mobile_money">Mobile Money</TabsTrigger>
                                <TabsTrigger value="card">Credit Card</TabsTrigger>
                            </TabsList>
                            
                            <TabsContent value="mobile_money">
                                <form @submit.prevent="submit" class="space-y-4 mt-4">
                                    <div v-if="successMessage" class="p-3 bg-green-100 text-green-700 rounded-md text-sm">
                                        {{ successMessage }}
                                    </div>
                                    <div v-if="errorMessage" class="p-3 bg-red-100 text-red-700 rounded-md text-sm">
                                        {{ errorMessage }}
                                    </div>

                                    <div class="space-y-4">
                                        <Label>Select Provider</Label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div 
                                                class="flex items-center space-x-2 border rounded-md p-4 cursor-pointer transition-colors"
                                                :class="form.service === 'MTN' ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                                @click="form.service = 'MTN'"
                                            >
                                                <input type="radio" value="MTN" v-model="form.service" class="h-4 w-4 border-gray-300 text-primary focus:ring-primary" />
                                                <span class="font-medium">MTN Mobile Money</span>
                                            </div>
                                            <div 
                                                class="flex items-center space-x-2 border rounded-md p-4 cursor-pointer transition-colors"
                                                :class="form.service === 'ORANGE' ? 'border-primary bg-primary/5' : 'hover:bg-muted'"
                                                @click="form.service = 'ORANGE'"
                                            >
                                                <input type="radio" value="ORANGE" v-model="form.service" class="h-4 w-4 border-gray-300 text-primary focus:ring-primary" />
                                                <span class="font-medium">Orange Money</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="phone">Phone Number</Label>
                                        <Input id="phone" v-model="form.payer" placeholder="2376..." required />
                                        <p class="text-sm text-muted-foreground">Enter your Mobile Money number.</p>
                                    </div>

                                    <Button type="submit" class="w-full" :disabled="form.processing">
                                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Pay {{ plan.price }} {{ plan.currency }}
                                    </Button>
                                </form>
                            </TabsContent>
                            
                            <TabsContent value="card">
                                <div class="text-center py-8 text-muted-foreground">
                                    <CreditCard class="mx-auto h-12 w-12 mb-4 opacity-50" />
                                    <p>Card payments are handled via secure redirect.</p>
                                    <Button class="mt-4" disabled>Coming Soon</Button>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
