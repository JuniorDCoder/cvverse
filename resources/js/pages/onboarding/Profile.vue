<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Briefcase, MapPin, Phone, Building2, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    industries: Record<string, string>;
    experienceLevels: Record<string, string>;
}>();

const form = useForm({
    job_title: '',
    industry: '',
    experience_level: '',
    phone: '',
    location: '',
});

const submit = () => {
    form.post('/onboarding/profile');
};
</script>

<template>
    <OnboardingLayout
        title="Tell us about yourself"
        description="Help us personalize your CV building experience"
        :step="1"
        :total-steps="3"
    >
        <Head title="Profile Setup" />

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Job Title -->
            <div class="space-y-2">
                <Label for="job_title" class="flex items-center gap-2">
                    <Briefcase class="h-4 w-4 text-muted-foreground" />
                    Current or Desired Job Title *
                </Label>
                <Input
                    id="job_title"
                    v-model="form.job_title"
                    type="text"
                    placeholder="e.g., Software Engineer, Marketing Manager"
                    required
                    autofocus
                />
                <InputError :message="form.errors.job_title" />
            </div>

            <!-- Industry -->
            <div class="space-y-2">
                <Label for="industry" class="flex items-center gap-2">
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                    Industry *
                </Label>
                <select
                    id="industry"
                    v-model="form.industry"
                    required
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="" disabled>Select your industry</option>
                    <option 
                        v-for="(label, value) in industries" 
                        :key="value" 
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
                <InputError :message="form.errors.industry" />
            </div>

            <!-- Experience Level -->
            <div class="space-y-2">
                <Label for="experience_level" class="flex items-center gap-2">
                    <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    Experience Level *
                </Label>
                <select
                    id="experience_level"
                    v-model="form.experience_level"
                    required
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="" disabled>Select your experience level</option>
                    <option 
                        v-for="(label, value) in experienceLevels" 
                        :key="value" 
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
                <InputError :message="form.errors.experience_level" />
            </div>

            <!-- Phone (Optional) -->
            <div class="space-y-2">
                <Label for="phone" class="flex items-center gap-2">
                    <Phone class="h-4 w-4 text-muted-foreground" />
                    Phone Number
                    <span class="text-xs text-muted-foreground">(Optional)</span>
                </Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    placeholder="+1 (555) 123-4567"
                />
                <InputError :message="form.errors.phone" />
            </div>

            <!-- Location (Optional) -->
            <div class="space-y-2">
                <Label for="location" class="flex items-center gap-2">
                    <MapPin class="h-4 w-4 text-muted-foreground" />
                    Location
                    <span class="text-xs text-muted-foreground">(Optional)</span>
                </Label>
                <Input
                    id="location"
                    v-model="form.location"
                    type="text"
                    placeholder="e.g., San Francisco, CA"
                />
                <InputError :message="form.errors.location" />
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <Button variant="ghost" as-child>
                    <Link href="/onboarding">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5"/>
                            <path d="m12 19-7-7 7-7"/>
                        </svg>
                        Back
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else class="flex items-center">
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </span>
                </Button>
            </div>
        </form>
    </OnboardingLayout>
</template>
