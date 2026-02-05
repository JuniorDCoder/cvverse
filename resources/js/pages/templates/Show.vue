<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index as templatesIndex, show as templateShow, editor as templateEditor, download as templateDownload, preview as templatePreview } from '@/routes/templates';
import { 
    Download, 
    Eye, 
    Edit, 
    Crown, 
    ArrowLeft,
    Clock,
    User,
    Palette,
    FileText,
    Check,
    Star,
    ChevronRight
} from 'lucide-vue-next';

interface CvTemplate {
    id: number;
    name: string;
    description: string | null;
    thumbnail: string | null;
    category: string;
    is_premium: boolean;
    price: number | null;
    currency: string;
    downloads_count: number;
    views_count: number;
    sections: Array<{
        id: string;
        name: string;
        enabled: boolean;
    }>;
    styles: {
        primaryColor: string;
        secondaryColor: string;
        fontFamily: string;
        fontSize: string;
    };
    html_content: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    template: CvTemplate;
    relatedTemplates: CvTemplate[];
    previewHtml: string;
}

const props = defineProps<Props>();

const showPreviewModal = ref(false);

const enabledSections = computed(() => {
    return props.template.sections?.filter(s => s.enabled) || [];
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatNumber = (num: number) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
};

const useTemplate = () => {
    router.visit(templateEditor.url(props.template.id));
};

const downloadTemplate = () => {
    window.location.href = templateDownload.url(props.template.id);
};

const previewTemplate = () => {
    window.open(templatePreview.url(props.template.id), '_blank');
};

const categoryLabels: Record<string, string> = {
    professional: 'Professional',
    creative: 'Creative',
    modern: 'Modern',
    simple: 'Simple',
    executive: 'Executive',
    academic: 'Academic',
    technical: 'Technical'
};
</script>

<template>
    <LandingLayout>
        <Head :title="template.name + ' - CV Template'" />

        <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
            <!-- Breadcrumb -->
            <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <nav class="flex items-center gap-2 text-sm">
                        <Link 
                            href="/" 
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                        >
                            Home
                        </Link>
                        <ChevronRight class="w-4 h-4 text-gray-400" />
                        <Link 
                            :href="templatesIndex.url()" 
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300"
                        >
                            Templates
                        </Link>
                        <ChevronRight class="w-4 h-4 text-gray-400" />
                        <span class="text-gray-900 dark:text-white font-medium">{{ template.name }}</span>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Left Column - Preview -->
                    <div class="lg:col-span-2">
                        <Card class="overflow-hidden">
                            <CardContent class="p-0">
                                <!-- Template Preview -->
                                <div class="relative bg-gray-100 dark:bg-gray-700">
                                    <div 
                                        class="aspect-[3/4] w-full overflow-hidden"
                                        v-if="previewHtml"
                                    >
                                        <iframe
                                            :srcdoc="previewHtml"
                                            class="w-full h-full border-0 transform scale-100"
                                            sandbox="allow-same-origin"
                                            title="Template Preview"
                                        ></iframe>
                                    </div>
                                    <div 
                                        v-else-if="template.thumbnail"
                                        class="aspect-[3/4] w-full bg-cover bg-center"
                                        :style="{ backgroundImage: `url(${template.thumbnail})` }"
                                    ></div>
                                    <div 
                                        v-else
                                        class="aspect-[3/4] w-full flex items-center justify-center"
                                    >
                                        <FileText class="w-24 h-24 text-gray-300 dark:text-gray-500" />
                                    </div>

                                    <!-- Premium Badge Overlay -->
                                    <div v-if="template.is_premium" class="absolute top-4 left-4">
                                        <Badge class="bg-gradient-to-r from-yellow-500 to-amber-500 text-white px-3 py-1 text-sm">
                                            <Crown class="w-4 h-4 mr-1" />
                                            Premium
                                        </Badge>
                                    </div>

                                    <!-- Full Preview Button -->
                                    <div class="absolute bottom-4 right-4">
                                        <Button 
                                            @click="previewTemplate"
                                            variant="secondary"
                                            class="shadow-lg"
                                        >
                                            <Eye class="w-4 h-4 mr-2" />
                                            Full Preview
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Template Sections -->
                        <Card class="mt-6">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <FileText class="w-5 h-5" />
                                    Included Sections
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <div 
                                        v-for="section in enabledSections"
                                        :key="section.id"
                                        class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                                    >
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                            <Check class="w-4 h-4 text-green-600 dark:text-green-400" />
                                        </div>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ section.name }}</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Right Column - Details -->
                    <div class="space-y-6">
                        <!-- Template Info -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-4">
                                    <!-- Title & Category -->
                                    <div>
                                        <div class="flex items-start justify-between gap-4">
                                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                                {{ template.name }}
                                            </h1>
                                        </div>
                                        <Badge variant="secondary" class="mt-2">
                                            {{ categoryLabels[template.category] || template.category }}
                                        </Badge>
                                    </div>

                                    <!-- Description -->
                                    <p 
                                        v-if="template.description" 
                                        class="text-gray-600 dark:text-gray-400"
                                    >
                                        {{ template.description }}
                                    </p>

                                    <!-- Stats -->
                                    <div class="flex items-center gap-6 py-4 border-t border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <Download class="w-4 h-4" />
                                            <span class="font-medium">{{ formatNumber(template.downloads_count) }}</span>
                                            <span class="text-sm">downloads</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                            <Eye class="w-4 h-4" />
                                            <span class="font-medium">{{ formatNumber(template.views_count) }}</span>
                                            <span class="text-sm">views</span>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="py-2">
                                        <div v-if="template.is_premium && template.price" class="flex items-baseline gap-1">
                                            <span class="text-3xl font-bold text-gray-900 dark:text-white">
                                                ${{ template.price.toFixed(2) }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">{{ template.currency }}</span>
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <span class="text-3xl font-bold text-green-600 dark:text-green-400">Free</span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="space-y-3 pt-2">
                                        <Button 
                                            @click="useTemplate"
                                            class="w-full bg-gradient-to-r from-primary to-primary/80 hover:from-primary/90 hover:to-primary/70"
                                            size="lg"
                                        >
                                            <Edit class="w-5 h-5 mr-2" />
                                            Use This Template
                                        </Button>
                                        <Button 
                                            @click="downloadTemplate"
                                            variant="outline"
                                            class="w-full"
                                            size="lg"
                                        >
                                            <Download class="w-5 h-5 mr-2" />
                                            Download Template
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Style Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Palette class="w-5 h-5" />
                                    Style Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <!-- Colors -->
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Colors</label>
                                        <div class="flex items-center gap-3 mt-2">
                                            <div 
                                                class="w-8 h-8 rounded-full border-2 border-gray-200 dark:border-gray-600"
                                                :style="{ backgroundColor: template.styles?.primaryColor || '#2563eb' }"
                                                :title="'Primary: ' + (template.styles?.primaryColor || '#2563eb')"
                                            ></div>
                                            <div 
                                                class="w-8 h-8 rounded-full border-2 border-gray-200 dark:border-gray-600"
                                                :style="{ backgroundColor: template.styles?.secondaryColor || '#64748b' }"
                                                :title="'Secondary: ' + (template.styles?.secondaryColor || '#64748b')"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Font -->
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Font Family</label>
                                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                            {{ template.styles?.fontFamily || 'Inter' }}
                                        </p>
                                    </div>

                                    <!-- Font Size -->
                                    <div>
                                        <label class="text-sm text-gray-500 dark:text-gray-400">Font Size</label>
                                        <p class="mt-1 font-medium text-gray-900 dark:text-white capitalize">
                                            {{ template.styles?.fontSize || 'medium' }}
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Last Updated -->
                        <Card>
                            <CardContent class="py-4">
                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                    <Clock class="w-4 h-4" />
                                    <span>Last updated {{ formatDate(template.updated_at) }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Related Templates -->
                <div v-if="relatedTemplates.length > 0" class="mt-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Related Templates
                        </h2>
                        <Link 
                            :href="templatesIndex.url()"
                            class="text-primary hover:underline flex items-center gap-1"
                        >
                            View all templates
                            <ChevronRight class="w-4 h-4" />
                        </Link>
                    </div>

                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <Link
                            v-for="related in relatedTemplates"
                            :key="related.id"
                            :href="templateShow.url(related.id)"
                            class="group"
                        >
                            <Card class="overflow-hidden transition-all duration-300 group-hover:shadow-lg group-hover:-translate-y-1">
                                <CardContent class="p-0">
                                    <div class="relative aspect-[3/4] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                        <div 
                                            v-if="related.thumbnail"
                                            class="w-full h-full bg-cover bg-center transition-transform duration-300 group-hover:scale-105"
                                            :style="{ backgroundImage: `url(${related.thumbnail})` }"
                                        ></div>
                                        <div 
                                            v-else
                                            class="w-full h-full flex items-center justify-center"
                                        >
                                            <FileText class="w-12 h-12 text-gray-300 dark:text-gray-500" />
                                        </div>

                                        <div v-if="related.is_premium" class="absolute top-2 left-2">
                                            <Badge class="bg-gradient-to-r from-yellow-500 to-amber-500 text-white text-xs px-2 py-0.5">
                                                <Crown class="w-3 h-3 mr-1" />
                                                Premium
                                            </Badge>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-primary transition-colors">
                                            {{ related.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ categoryLabels[related.category] || related.category }}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-8">
                    <Link 
                        :href="templatesIndex.url()"
                        class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back to all templates
                    </Link>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>
