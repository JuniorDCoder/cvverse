<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Save, Globe, Mail, Share2, BarChart3 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { updateGeneral, updateContact, updateSocial, updateEmail, updateStats } from '@/actions/App/Http/Controllers/Admin/SiteSettingController';

interface Settings {
    general: {
        site_name: string;
        site_description: string;
        site_tagline: string;
    };
    contact: {
        support_email: string;
        sales_email: string;
        press_email: string;
        partnerships_email: string;
        phone: string;
        address: string;
        city: string;
        country: string;
        timezone: string;
    };
    social: {
        twitter: string;
        facebook: string;
        linkedin: string;
        instagram: string;
        youtube: string;
        github: string;
    };
    email: {
        welcome_emails: boolean;
        weekly_digest: boolean;
        marketing_emails: boolean;
    };
    stats: {
        users_count: string;
        cvs_created: string;
        success_rate: string;
        countries: string;
        user_rating: string;
    };
}

const props = defineProps<{
    settings: Settings;
}>();

// General Settings Form
const generalForm = useForm({
    site_name: props.settings.general.site_name,
    site_description: props.settings.general.site_description,
    site_tagline: props.settings.general.site_tagline,
});

const saveGeneralSettings = () => {
    generalForm.put(updateGeneral.url(), {
        preserveScroll: true,
    });
};

// Contact Settings Form
const contactForm = useForm({
    support_email: props.settings.contact.support_email,
    sales_email: props.settings.contact.sales_email,
    press_email: props.settings.contact.press_email,
    partnerships_email: props.settings.contact.partnerships_email,
    phone: props.settings.contact.phone,
    address: props.settings.contact.address,
    city: props.settings.contact.city,
    country: props.settings.contact.country,
    timezone: props.settings.contact.timezone,
});

const saveContactSettings = () => {
    contactForm.put(updateContact.url(), {
        preserveScroll: true,
    });
};

// Social Settings Form
const socialForm = useForm({
    twitter: props.settings.social.twitter,
    facebook: props.settings.social.facebook,
    linkedin: props.settings.social.linkedin,
    instagram: props.settings.social.instagram,
    youtube: props.settings.social.youtube,
    github: props.settings.social.github,
});

const saveSocialSettings = () => {
    socialForm.put(updateSocial.url(), {
        preserveScroll: true,
    });
};

// Email Settings Form
const emailForm = useForm({
    welcome_emails: props.settings.email.welcome_emails,
    weekly_digest: props.settings.email.weekly_digest,
    marketing_emails: props.settings.email.marketing_emails,
});

const saveEmailSettings = () => {
    emailForm.put(updateEmail.url(), {
        preserveScroll: true,
    });
};

// Stats Settings Form
const statsForm = useForm({
    users_count: props.settings.stats.users_count,
    cvs_created: props.settings.stats.cvs_created,
    success_rate: props.settings.stats.success_rate,
    countries: props.settings.stats.countries,
    user_rating: props.settings.stats.user_rating,
});

const saveStatsSettings = () => {
    statsForm.put(updateStats.url(), {
        preserveScroll: true,
    });
};
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
                        <div class="flex items-center gap-2">
                            <Globe class="h-5 w-5 text-primary" />
                            <CardTitle>General Settings</CardTitle>
                        </div>
                        <CardDescription>Basic platform configuration</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveGeneralSettings" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="site_name">Site Name</Label>
                                <Input id="site_name" v-model="generalForm.site_name" />
                                <p v-if="generalForm.errors.site_name" class="text-sm text-destructive">
                                    {{ generalForm.errors.site_name }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="site_description">Site Description</Label>
                                <Input id="site_description" v-model="generalForm.site_description" />
                                <p v-if="generalForm.errors.site_description" class="text-sm text-destructive">
                                    {{ generalForm.errors.site_description }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="site_tagline">Site Tagline</Label>
                                <Input id="site_tagline" v-model="generalForm.site_tagline" placeholder="Your Career, Your Story" />
                                <p v-if="generalForm.errors.site_tagline" class="text-sm text-destructive">
                                    {{ generalForm.errors.site_tagline }}
                                </p>
                            </div>
                            <Button type="submit" :disabled="generalForm.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ generalForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Contact Settings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Mail class="h-5 w-5 text-primary" />
                            <CardTitle>Contact Settings</CardTitle>
                        </div>
                        <CardDescription>Contact information displayed on the site</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveContactSettings" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="support_email">Support Email</Label>
                                    <Input id="support_email" type="email" v-model="contactForm.support_email" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="sales_email">Sales Email</Label>
                                    <Input id="sales_email" type="email" v-model="contactForm.sales_email" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="press_email">Press Email</Label>
                                    <Input id="press_email" type="email" v-model="contactForm.press_email" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="partnerships_email">Partnerships Email</Label>
                                    <Input id="partnerships_email" type="email" v-model="contactForm.partnerships_email" />
                                </div>
                            </div>
                            <Separator />
                            <div class="space-y-2">
                                <Label for="phone">Phone</Label>
                                <Input id="phone" type="tel" v-model="contactForm.phone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="address">Address</Label>
                                <Input id="address" v-model="contactForm.address" />
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <Label for="city">City</Label>
                                    <Input id="city" v-model="contactForm.city" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="country">Country</Label>
                                    <Input id="country" v-model="contactForm.country" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="timezone">Timezone</Label>
                                    <Input id="timezone" v-model="contactForm.timezone" />
                                </div>
                            </div>
                            <Button type="submit" :disabled="contactForm.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ contactForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Social Media Settings -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Share2 class="h-5 w-5 text-primary" />
                            <CardTitle>Social Media</CardTitle>
                        </div>
                        <CardDescription>Social media links</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveSocialSettings" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="twitter">Twitter / X</Label>
                                    <Input id="twitter" type="url" v-model="socialForm.twitter" placeholder="https://twitter.com/..." />
                                </div>
                                <div class="space-y-2">
                                    <Label for="facebook">Facebook</Label>
                                    <Input id="facebook" type="url" v-model="socialForm.facebook" placeholder="https://facebook.com/..." />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="linkedin">LinkedIn</Label>
                                    <Input id="linkedin" type="url" v-model="socialForm.linkedin" placeholder="https://linkedin.com/..." />
                                </div>
                                <div class="space-y-2">
                                    <Label for="instagram">Instagram</Label>
                                    <Input id="instagram" type="url" v-model="socialForm.instagram" placeholder="https://instagram.com/..." />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="youtube">YouTube</Label>
                                    <Input id="youtube" type="url" v-model="socialForm.youtube" placeholder="https://youtube.com/..." />
                                </div>
                                <div class="space-y-2">
                                    <Label for="github">GitHub</Label>
                                    <Input id="github" type="url" v-model="socialForm.github" placeholder="https://github.com/..." />
                                </div>
                            </div>
                            <Button type="submit" :disabled="socialForm.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ socialForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Email Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>Email Settings</CardTitle>
                        <CardDescription>Configure email notifications</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveEmailSettings" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Welcome Emails</p>
                                    <p class="text-sm text-muted-foreground">Send welcome email to new users</p>
                                </div>
                                <Switch v-model:checked="emailForm.welcome_emails" />
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Weekly Digest</p>
                                    <p class="text-sm text-muted-foreground">Send weekly activity summary</p>
                                </div>
                                <Switch v-model:checked="emailForm.weekly_digest" />
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">Marketing Emails</p>
                                    <p class="text-sm text-muted-foreground">Send promotional content</p>
                                </div>
                                <Switch v-model:checked="emailForm.marketing_emails" />
                            </div>
                            <Separator />
                            <Button type="submit" :disabled="emailForm.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ emailForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Landing Page Stats -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-primary" />
                            <CardTitle>Landing Page Stats</CardTitle>
                        </div>
                        <CardDescription>Statistics displayed on the landing page</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="saveStatsSettings" class="space-y-4">
                            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div class="space-y-2">
                                    <Label for="users_count">Users Count</Label>
                                    <Input id="users_count" v-model="statsForm.users_count" placeholder="500,000+" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="cvs_created">CVs Created</Label>
                                    <Input id="cvs_created" v-model="statsForm.cvs_created" placeholder="500K+" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="success_rate">Success Rate</Label>
                                    <Input id="success_rate" v-model="statsForm.success_rate" placeholder="95%" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="countries">Countries</Label>
                                    <Input id="countries" v-model="statsForm.countries" placeholder="150+" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="user_rating">User Rating</Label>
                                    <Input id="user_rating" v-model="statsForm.user_rating" placeholder="4.9/5" />
                                </div>
                            </div>
                            <Button type="submit" :disabled="statsForm.processing">
                                <Save class="h-4 w-4 mr-2" />
                                {{ statsForm.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Danger Zone -->
                <Card class="border-red-200 dark:border-red-900 lg:col-span-2">
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
