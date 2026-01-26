<script setup lang="ts">
import { useForm, Head, Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import OnboardingLayout from '@/layouts/OnboardingLayout.vue';

const props = defineProps<{
    availableInterests: Record<string, string>;
}>();

const form = useForm({
    interests: [] as string[],
    bio: '',
});

const toggleInterest = (key: string) => {
    const index = form.interests.indexOf(key);
    if (index === -1) {
        form.interests.push(key);
    } else {
        form.interests.splice(index, 1);
    }
};

const isSelected = (key: string) => form.interests.includes(key);

const submit = () => {
    form.post('/onboarding/interests');
};

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
</script>

<template>
    <OnboardingLayout
        title="What brings you here?"
        description="Select all that apply to help us customize your experience"
        :step="2"
        :total-steps="3"
    >
        <Head title="Your Interests" />

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Interests Grid -->
            <div class="space-y-3">
                <Label class="text-base">Select your goals *</Label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                <InputError :message="form.errors.interests" />
            </div>

            <!-- Bio -->
            <div class="space-y-2">
                <Label for="bio" class="flex items-center gap-2">
                    Short Bio
                    <span class="text-xs text-muted-foreground">(Optional)</span>
                </Label>
                <textarea
                    id="bio"
                    v-model="form.bio"
                    rows="3"
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    placeholder="Tell us a bit about yourself and your career aspirations..."
                />
                <p class="text-xs text-muted-foreground text-right">
                    {{ form.bio.length }}/500 characters
                </p>
                <InputError :message="form.errors.bio" />
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <Button variant="ghost" as-child>
                    <Link href="/onboarding/profile">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5"/>
                            <path d="m12 19-7-7 7-7"/>
                        </svg>
                        Back
                    </Link>
                </Button>
                <Button 
                    type="submit" 
                    :disabled="form.processing || form.interests.length === 0"
                >
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
