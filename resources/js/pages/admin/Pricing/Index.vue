<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, Edit, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import admin from '@/routes/admin';

interface PricingPlan {
    id: number;
    name: string;
    slug: string;
    price: string;
    currency: string;
    interval: string;
    features: string[];
    is_active: boolean;
}

const props = defineProps<{
    pricingPlans: PricingPlan[];
}>();

const deletePlan = (id: number) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        router.delete(admin.pricingPlans.destroy(id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Pricing Plans' }]">
        <Head title="Manage Pricing Plans" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Pricing Plans</h1>
                    <p class="text-muted-foreground">Manage subscription plans and pricing</p>
                </div>
                <Button as-child>
                    <Link :href="admin.pricingPlans.create()">
                        <Plus class="h-4 w-4 mr-2" />
                        Create Plan
                    </Link>
                </Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="text-left p-4 font-medium text-sm">Name</th>
                                    <th class="text-left p-4 font-medium text-sm">Price</th>
                                    <th class="text-left p-4 font-medium text-sm">Interval</th>
                                    <th class="text-left p-4 font-medium text-sm">Status</th>
                                    <th class="text-right p-4 font-medium text-sm">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="plan in pricingPlans" :key="plan.id" class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                                    <td class="p-4 font-medium">{{ plan.name }}</td>
                                    <td class="p-4">{{ plan.price }} {{ plan.currency }}</td>
                                    <td class="p-4 capitalize">{{ plan.interval.replace('_', ' ') }}</td>
                                    <td class="p-4">
                                        <Badge :variant="plan.is_active ? 'default' : 'secondary'"
                                            :class="plan.is_active ? 'bg-green-500/10 text-green-500 hover:bg-green-500/20' : ''">
                                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button variant="ghost" size="icon" as-child>
                                                <Link :href="admin.pricingPlans.edit(plan.id)">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="icon" class="text-red-500 hover:text-red-600" @click="deletePlan(plan.id)">
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="pricingPlans.length === 0">
                                    <td colspan="5" class="p-8 text-center text-muted-foreground">
                                        No pricing plans found. Create one to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
