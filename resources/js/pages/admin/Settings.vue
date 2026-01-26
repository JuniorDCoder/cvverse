<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Settings' }]">
        <Head title="Admin Settings" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold">Settings</h1>
                <p class="text-muted-foreground">Manage platform configuration</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- General Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>General Settings</CardTitle>
                        <CardDescription>Basic platform configuration</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="siteName">Site Name</Label>
                            <Input id="siteName" value="CVverse" />
                        </div>
                        <div class="space-y-2">
                            <Label for="siteDescription">Site Description</Label>
                            <Input id="siteDescription" value="Build professional CVs with AI" />
                        </div>
                        <div class="space-y-2">
                            <Label for="supportEmail">Support Email</Label>
                            <Input id="supportEmail" type="email" value="support@cvverse.com" />
                        </div>
                        <Button>
                            <Save class="h-4 w-4 mr-2" />
                            Save Changes
                        </Button>
                    </CardContent>
                </Card>

                <!-- Subscription Plans -->
                <Card>
                    <CardHeader>
                        <CardTitle>Subscription Plans</CardTitle>
                        <CardDescription>Manage pricing and features</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">Free Plan</span>
                                <span class="text-muted-foreground">$0/month</span>
                            </div>
                            <p class="text-sm text-muted-foreground">3 CVs, Basic templates</p>
                        </div>
                        <div class="p-4 border rounded-lg border-primary">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">Pro Plan</span>
                                <span class="text-muted-foreground">$12/month</span>
                            </div>
                            <p class="text-sm text-muted-foreground">Unlimited CVs, All templates, AI features</p>
                        </div>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">Enterprise Plan</span>
                                <span class="text-muted-foreground">$29/month</span>
                            </div>
                            <p class="text-sm text-muted-foreground">Everything + Team features, Priority support</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Email Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>Email Settings</CardTitle>
                        <CardDescription>Configure email notifications</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Welcome Emails</p>
                                <p class="text-sm text-muted-foreground">Send welcome email to new users</p>
                            </div>
                            <Button variant="outline" size="sm">Enabled</Button>
                        </div>
                        <Separator />
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Weekly Digest</p>
                                <p class="text-sm text-muted-foreground">Send weekly activity summary</p>
                            </div>
                            <Button variant="outline" size="sm">Enabled</Button>
                        </div>
                        <Separator />
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Marketing Emails</p>
                                <p class="text-sm text-muted-foreground">Send promotional content</p>
                            </div>
                            <Button variant="secondary" size="sm">Disabled</Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Danger Zone -->
                <Card class="border-red-200 dark:border-red-900">
                    <CardHeader>
                        <CardTitle class="text-red-500">Danger Zone</CardTitle>
                        <CardDescription>Irreversible actions</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between p-4 border border-red-200 dark:border-red-900 rounded-lg">
                            <div>
                                <p class="font-medium">Clear All Data</p>
                                <p class="text-sm text-muted-foreground">Delete all user data and reset platform</p>
                            </div>
                            <Button variant="destructive" size="sm">Clear Data</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
