<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import LandingLayout from '@/layouts/LandingLayout.vue';

const props = withDefaults(
    defineProps<{
        content?: string;
        siteName?: string;
    }>(),
    {
        content: '',
        siteName: 'CVverse',
    },
);

const defaultContent = `1. Introduction

Welcome to ${props.siteName}. We respect your privacy and are committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you use our platform.

2. Information We Collect

We collect information you provide directly to us, including:
- Account information (name, email address, password)
- Profile information (job title, industry, experience)
- CV content (education, work history, skills)
- Usage data (how you interact with our platform)

3. How We Use Your Information

We use the information we collect to:
- Provide, maintain, and improve our services
- Generate and optimize your CVs and cover letters
- Send you notifications and updates about your account
- Respond to your comments, questions, and requests
- Analyze usage patterns to improve user experience

4. Data Security

We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. Your data is encrypted both in transit and at rest.

5. Data Sharing

We do not sell, trade, or otherwise transfer your personal information to third parties. We may share information with trusted service providers who assist us in operating our platform, subject to strict confidentiality agreements.

6. Your Rights

You have the right to:
- Access your personal data
- Correct inaccurate data
- Request deletion of your data
- Export your data
- Opt out of marketing communications

7. Cookies

We use cookies and similar tracking technologies to enhance your experience. You can control cookie preferences through your browser settings.

8. Changes to This Policy

We may update this privacy policy from time to time. We will notify you of any significant changes by posting the new policy on this page and updating the effective date.

9. Contact Us

If you have questions about this Privacy Policy, please contact us through our Contact page.`;

const displayContent = props.content || defaultContent;
const paragraphs = displayContent.split('\n\n');
</script>

<template>
    <LandingLayout title="Privacy Policy">
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Legal</Badge>
                    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">Privacy Policy</h1>
                    <p class="text-lg text-muted-foreground">
                        How we collect, use, and protect your personal information.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto prose prose-neutral dark:prose-invert">
                    <div
                        v-for="(paragraph, index) in paragraphs"
                        :key="index"
                        class="mb-6"
                    >
                        <template v-if="paragraph.match(/^\d+\.\s/)">
                            <h2 class="text-xl font-semibold text-foreground mb-3">{{ paragraph }}</h2>
                        </template>
                        <template v-else-if="paragraph.includes('\n-')">
                            <div>
                                <p v-if="paragraph.split('\n')[0]" class="text-muted-foreground mb-2">{{ paragraph.split('\n')[0] }}</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li
                                        v-for="(item, i) in paragraph.split('\n').filter((l: string) => l.startsWith('-'))"
                                        :key="i"
                                        class="text-muted-foreground"
                                    >
                                        {{ item.replace(/^-\s*/, '') }}
                                    </li>
                                </ul>
                            </div>
                        </template>
                        <template v-else>
                            <p class="text-muted-foreground leading-relaxed whitespace-pre-line">{{ paragraph }}</p>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
