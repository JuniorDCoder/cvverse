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

const defaultContent = `1. Acceptance of Terms

By accessing or using ${props.siteName}, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.

2. Description of Service

${props.siteName} provides an online platform for creating professional CVs, resumes, and cover letters. Our services include AI-powered content generation, template selection, and document export functionality.

3. User Accounts

To use certain features, you must create an account. You are responsible for:
- Maintaining the confidentiality of your account credentials
- All activities that occur under your account
- Providing accurate and complete information
- Notifying us immediately of any unauthorized use

4. Acceptable Use

You agree not to:
- Use the service for any unlawful purpose
- Upload malicious content or malware
- Attempt to gain unauthorized access to our systems
- Impersonate another person or entity
- Interfere with or disrupt the service

5. Intellectual Property

Your content remains yours. By using our service, you grant us a limited license to process and store your content solely to provide our services. Our templates, designs, and platform code are proprietary to ${props.siteName}.

6. Payment Terms

Some features require a paid subscription. By subscribing:
- You agree to pay the applicable fees
- Fees are non-refundable unless otherwise stated
- We may change pricing with reasonable notice
- Cancellation takes effect at the end of your billing period

7. Limitation of Liability

${props.siteName} is provided "as is" without warranties of any kind. We shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the service.

8. Termination

We may terminate or suspend your account at our discretion if you violate these terms. You may delete your account at any time through your account settings.

9. Changes to Terms

We reserve the right to modify these terms at any time. Continued use of the service after changes constitutes acceptance of the new terms.

10. Governing Law

These terms are governed by the laws of the jurisdiction in which ${props.siteName} operates.

11. Contact

If you have questions about these Terms of Service, please contact us through our Contact page.`;

const displayContent = props.content || defaultContent;
const paragraphs = displayContent.split('\n\n');
</script>

<template>
    <LandingLayout title="Terms of Service">
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-background" />
            <div class="container relative mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-6">Legal</Badge>
                    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mb-4">Terms of Service</h1>
                    <p class="text-lg text-muted-foreground">
                        Please read these terms carefully before using our platform.
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
