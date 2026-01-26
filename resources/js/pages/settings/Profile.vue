<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Briefcase, Building2, MapPin, Phone, TrendingUp, Check, User } from 'lucide-vue-next';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { type BreadcrumbItem } from '@/types';
import { ref, watch } from 'vue';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    industries?: Record<string, string>;
    experienceLevels?: Record<string, string>;
    availableInterests?: Record<string, string>;
};

const props = withDefaults(defineProps<Props>(), {
    industries: () => ({}),
    experienceLevels: () => ({}),
    availableInterests: () => ({}),
});

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const interestIcons: Record<string, string> = {
    job_search: '🔍',
    career_change: '🔄',
    freelance: '💼',
    networking: '🤝',
    personal_branding: '✨',
    skill_showcase: '🎯',
    promotion: '📈',
    academic: '🎓',
};

const selectedInterests = ref<string[]>(user.interests || []);
const bioLength = ref((user.bio || '').length);
const { addToast } = useToast();

const toggleInterest = (key: string) => {
    const index = selectedInterests.value.indexOf(key);
    if (index === -1) {
        selectedInterests.value.push(key);
    } else {
        selectedInterests.value.splice(index, 1);
    }
};

const isSelected = (key: string) => selectedInterests.value.includes(key);

// Watch for flash messages and show toasts
watch(() => page.props.flash, (flash: any, oldFlash: any) => {
    // Only show toast if flash message is new (not on initial load)
    if (flash?.success && flash.success !== oldFlash?.success) {
        addToast({
            type: 'success',
            title: 'Success',
            message: flash.success
        });
    }
    if (flash?.error && flash.error !== oldFlash?.error) {
        addToast({
            type: 'error',
            title: 'Error',
            message: flash.error
        });
    }
}, { deep: true });

// Watch for validation errors on form submission
watch(() => page.props.errors, (errors: any, oldErrors: any) => {
    // Only show toast if there are new errors (not on initial load)
    if (errors && Object.keys(errors).length > 0) {
        const errorKeys = Object.keys(errors);
        const oldErrorKeys = oldErrors ? Object.keys(oldErrors) : [];
        
        // Check if there are new errors that weren't there before
        const hasNewErrors = errorKeys.some(key => !oldErrorKeys.includes(key));
        
        if (hasNewErrors) {
            const errorMessages = Object.values(errors) as string[];
            if (errorMessages.length > 0) {
                addToast({
                    type: 'error',
                    title: 'Validation Error',
                    message: errorMessages[0] || 'Please check the form for errors.'
                });
            }
        }
    }
}, { deep: true });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile Settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your profile details and preferences"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- Hidden inputs for interests array -->
                    <input
                        v-for="(interest, index) in selectedInterests"
                        :key="index"
                        type="hidden"
                        :name="`interests[${index}]`"
                        :value="interest"
                    />
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Basic Information
                        </h3>
                        
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                class="mt-1 block w-full"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Full name"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="Email address"
                            />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>

                        <div v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="text-sm text-muted-foreground">
                                Your email address is unverified.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                >
                                    Click here to resend the verification email.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600"
                            >
                                A new verification link has been sent to your email
                                address.
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Professional Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <Briefcase class="h-5 w-5" />
                            Professional Information
                        </h3>

                        <div class="grid gap-2">
                            <Label for="job_title" class="flex items-center gap-2">
                                <Briefcase class="h-4 w-4 text-muted-foreground" />
                                Job Title
                            </Label>
                            <Input
                                id="job_title"
                                class="mt-1 block w-full"
                                name="job_title"
                                :default-value="user.job_title || ''"
                                autocomplete="organization-title"
                                placeholder="e.g., Software Engineer, Marketing Manager"
                            />
                            <InputError class="mt-2" :message="errors.job_title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="industry" class="flex items-center gap-2">
                                <Building2 class="h-4 w-4 text-muted-foreground" />
                                Industry
                            </Label>
                            <select
                                id="industry"
                                name="industry"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :value="user.industry || ''"
                            >
                                <option value="">Select your industry</option>
                                <option
                                    v-for="(label, value) in industries"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="errors.industry" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="experience_level" class="flex items-center gap-2">
                                <TrendingUp class="h-4 w-4 text-muted-foreground" />
                                Experience Level
                            </Label>
                            <select
                                id="experience_level"
                                name="experience_level"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :value="user.experience_level || ''"
                            >
                                <option value="">Select your experience level</option>
                                <option
                                    v-for="(label, value) in experienceLevels"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="errors.experience_level" />
                        </div>
                    </div>

                    <Separator />

                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <Phone class="h-5 w-5" />
                            Contact Information
                        </h3>

                        <div class="grid gap-2">
                            <Label for="phone" class="flex items-center gap-2">
                                <Phone class="h-4 w-4 text-muted-foreground" />
                                Phone Number
                            </Label>
                            <Input
                                id="phone"
                                type="tel"
                                class="mt-1 block w-full"
                                name="phone"
                                :default-value="user.phone || ''"
                                autocomplete="tel"
                                placeholder="+1 (555) 123-4567"
                            />
                            <InputError class="mt-2" :message="errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="location" class="flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                                Location
                            </Label>
                            <Input
                                id="location"
                                type="text"
                                class="mt-1 block w-full"
                                name="location"
                                :default-value="user.location || ''"
                                autocomplete="address-level2"
                                placeholder="e.g., San Francisco, CA"
                            />
                            <InputError class="mt-2" :message="errors.location" />
                        </div>
                    </div>

                    <Separator />

                    <!-- About & Interests -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">About & Interests</h3>

                        <div class="grid gap-2">
                            <Label for="bio">Bio</Label>
                            <Textarea
                                id="bio"
                                name="bio"
                                class="min-h-[100px]"
                                :default-value="user.bio || ''"
                                placeholder="Tell us a bit about yourself and your career aspirations..."
                                maxlength="500"
                                @input="(e) => { bioLength = (e.target as HTMLTextAreaElement).value.length }"
                            />
                            <p class="text-xs text-muted-foreground text-right">
                                {{ bioLength }}/500 characters
                            </p>
                            <InputError class="mt-2" :message="errors.bio" />
                        </div>

                        <div class="space-y-3">
                            <Label>Interests & Goals</Label>
                            <div v-if="Object.keys(availableInterests).length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    v-for="(label, key) in availableInterests"
                                    :key="key"
                                    type="button"
                                    @click="toggleInterest(key)"
                                    :class="[
                                        'relative flex items-center gap-3 p-4 rounded-xl border-2 text-left transition-all duration-200',
                                        isSelected(key) 
                                            ? 'border-primary bg-primary/5 shadow-sm' 
                                            : 'border-border hover:border-muted-foreground/50 hover:bg-muted/50'
                                    ]"
                                >
                                    <span class="text-2xl">{{ interestIcons[key] || '📌' }}</span>
                                    <span class="flex-1 text-sm font-medium">{{ label }}</span>
                                    <div 
                                        v-if="isSelected(key)"
                                        class="w-5 h-5 rounded-full bg-primary text-primary-foreground flex items-center justify-center"
                                    >
                                        <Check class="w-3 h-3" />
                                    </div>
                                </button>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">No interests available.</p>
                            <InputError :message="errors.interests" />
                        </div>
                    </div>

                    <Separator />

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="processing"
                            data-test="update-profile-button"
                        >
                            Save Changes
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
