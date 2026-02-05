<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import PricingForm from './Form.vue';
import admin from '@/routes/admin';

</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Admin', href: '/admin' },
        { title: 'Pricing Plans', href: admin.pricingPlans.index() },
        { title: 'Create Plan' }
    ]">
        <Head title="Create Pricing Plan" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="admin.pricingPlans.index()">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Create Pricing Plan</h1>
                    <p class="text-muted-foreground">Add a new pricing plan to your system</p>
                </div>
            </div>

            <Card class="max-w-3xl">
                <CardHeader>
                    <CardTitle>Plan Details</CardTitle>
                    <CardDescription>
                        Fill in the details for the new pricing plan.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <PricingForm
                        :is-editing="false"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
