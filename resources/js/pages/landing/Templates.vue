<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Search, 
    Download, 
    Eye, 
    Star, 
    Filter,
    Sparkles,
    ArrowRight,
    Crown,
    Check,
    Lock,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { index as templatesIndex, show as templateShow, editor as templateEditor } from '@/routes/templates';

interface Template {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category: string;
    is_premium: boolean;
    price: number | null;
    currency: string;
    downloads_count: number;
    views_count: number;
    image: string | null;
    thumbnail: string | null;
    styles: {
        primaryColor?: string;
    } | null;
}

interface PaginatedTemplates {
    data: Template[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    templates: PaginatedTemplates;
    categories: Record<string, string>;
    filters: {
        category?: string;
        type?: string;
        search?: string;
        sort?: string;
    };
    hasPremiumAccess: boolean;
}>();

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const type = ref(props.filters.type || '');
const sort = ref(props.filters.sort || 'popular');

const applyFilters = () => {
    router.get(templatesIndex.url({
        query: {
            search: search.value || undefined,
            category: category.value || undefined,
            type: type.value || undefined,
            sort: sort.value || undefined,
        }
    }), {}, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    category.value = '';
    type.value = '';
    sort.value = 'popular';
    router.get(templatesIndex.url());
};

const hasActiveFilters = computed(() => {
    return search.value || category.value || type.value;
});

const formatNumber = (num: number) => {
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'k';
    }
    return num.toString();
};
</script>

<template>
    <Head title="CV Templates - Professional Resume Templates" />
    
    <LandingLayout>
        <!-- Hero Section -->
        <section class="relative py-16 md:py-24 bg-gradient-to-b from-primary/5 to-background overflow-hidden">
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="container relative">
                <div class="max-w-3xl mx-auto text-center">
                    <Badge variant="outline" class="mb-4">
                        <Sparkles class="h-3 w-3 mr-1" />
                        {{ templates.total }}+ Professional Templates
                    </Badge>
                    <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">
                        Stand Out with 
                        <span class="text-primary">Beautiful CV Templates</span>
                    </h1>
                    <p class="text-lg text-muted-foreground mb-8 max-w-2xl mx-auto">
                        Choose from our collection of professionally designed CV templates. 
                        Customize with your information and download instantly.
                    </p>
                    
                    <!-- Search Bar -->
                    <div class="flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <Input 
                                v-model="search"
                                placeholder="Search templates..." 
                                class="pl-10 h-12"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Button size="lg" @click="applyFilters" class="h-12">
                            Search
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters & Templates -->
        <section class="py-12 flex flex-col items-center justify-center">
            <div class="container">
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4 mb-8 p-4 bg-muted/30 rounded-lg">
                    <div class="flex items-center gap-2 text-sm font-medium">
                        <Filter class="h-4 w-4" />
                        Filters:
                    </div>
                    
                    <Select v-model="category" @update:modelValue="applyFilters">
                        <SelectTrigger class="w-40 bg-background">
                            <SelectValue placeholder="Category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All Categories</SelectItem>
                            <SelectItem 
                                v-for="(label, value) in categories" 
                                :key="value" 
                                :value="value"
                            >
                                {{ label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="type" @update:modelValue="applyFilters">
                        <SelectTrigger class="w-32 bg-background">
                            <SelectValue placeholder="Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All</SelectItem>
                            <SelectItem value="free">Free</SelectItem>
                            <SelectItem value="premium">Premium</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="sort" @update:modelValue="applyFilters">
                        <SelectTrigger class="w-36 bg-background">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="popular">Most Popular</SelectItem>
                            <SelectItem value="newest">Newest</SelectItem>
                            <SelectItem value="name">Name A-Z</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button 
                        v-if="hasActiveFilters" 
                        variant="ghost" 
                        size="sm"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </Button>

                    <div class="ml-auto text-sm text-muted-foreground">
                        {{ templates.total }} templates found
                    </div>
                </div>

                <!-- Templates Grid -->
                <div v-if="templates.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card 
                        v-for="template in templates.data" 
                        :key="template.id" 
                        class="group overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <!-- Template Preview -->
                        <div class="aspect-[3/4] bg-muted relative overflow-hidden">
                            <img 
                                v-if="template.image" 
                                :src="`/storage/${template.image}`" 
                                :alt="template.name"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            />
                            <div 
                                v-else 
                                class="w-full h-full flex items-center justify-center"
                                :style="{ backgroundColor: template.styles?.primaryColor + '10' || '#f3f4f6' }"
                            >
                                <div class="text-center p-4">
                                    <div 
                                        class="w-16 h-20 mx-auto mb-2 rounded border-2 flex items-center justify-center"
                                        :style="{ borderColor: template.styles?.primaryColor || '#6b7280' }"
                                    >
                                        <div class="space-y-1">
                                            <div class="h-1.5 w-8 rounded" :style="{ backgroundColor: template.styles?.primaryColor || '#6b7280' }"></div>
                                            <div class="h-1 w-6 bg-gray-300 rounded"></div>
                                            <div class="h-1 w-7 bg-gray-300 rounded"></div>
                                        </div>
                                    </div>
                                    <span class="text-xs text-muted-foreground">Preview</span>
                                </div>
                            </div>

                            <!-- Premium Badge -->
                            <Badge 
                                v-if="template.is_premium" 
                                class="absolute top-3 left-3 bg-gradient-to-r from-amber-500 to-orange-500 border-0"
                            >
                                <Crown class="h-3 w-3 mr-1" />
                                Premium
                            </Badge>

                            <!-- Free Badge -->
                            <Badge 
                                v-else 
                                variant="secondary"
                                class="absolute top-3 left-3"
                            >
                                <Check class="h-3 w-3 mr-1" />
                                Free
                            </Badge>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
                                <div class="flex gap-2">
                                    <Button size="sm" variant="secondary" as-child>
                                        <Link :href="templateShow.url(template.id)">
                                            <Eye class="h-4 w-4 mr-1" />
                                            Preview
                                        </Link>
                                    </Button>
                                    <!-- Show upgrade prompt for premium templates without access -->
                                    <Button 
                                        v-if="template.is_premium && !hasPremiumAccess" 
                                        size="sm" 
                                        class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600"
                                        as-child
                                    >
                                        <Link href="/pricing">
                                            <Lock class="h-4 w-4 mr-1" />
                                            Upgrade
                                        </Link>
                                    </Button>
                                    <Button v-else size="sm" as-child>
                                        <Link :href="templateEditor.url(template.id)">
                                            Use Template
                                            <ArrowRight class="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <CardHeader class="pb-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <CardTitle class="text-base line-clamp-1">{{ template.name }}</CardTitle>
                                    <CardDescription class="capitalize">{{ categories[template.category] || template.category }}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardFooter class="pt-0 flex items-center justify-between">
                            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                <span class="flex items-center gap-1">
                                    <Download class="h-3 w-3" />
                                    {{ formatNumber(template.downloads_count) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <Eye class="h-3 w-3" />
                                    {{ formatNumber(template.views_count) }}
                                </span>
                            </div>
                            <div v-if="template.is_premium && template.price" class="font-semibold text-primary">
                                {{ template.currency }} {{ template.price }}
                            </div>
                            <span v-else class="text-sm font-medium text-green-600">Free</span>
                        </CardFooter>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
                        <Search class="h-10 w-10 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No templates found</h3>
                    <p class="text-muted-foreground mb-4">Try adjusting your filters or search terms.</p>
                    <Button variant="outline" @click="clearFilters">Clear Filters</Button>
                </div>

                <!-- Pagination -->
                <div v-if="templates.last_page > 1" class="flex items-center justify-center gap-4 mt-12">
                    <Button 
                        variant="outline"
                        :disabled="templates.current_page === 1"
                        @click="router.get(templatesIndex.url({ query: { ...filters, page: templates.current_page - 1 } }))"
                    >
                        Previous
                    </Button>
                    <span class="text-sm text-muted-foreground">
                        Page {{ templates.current_page }} of {{ templates.last_page }}
                    </span>
                    <Button 
                        variant="outline"
                        :disabled="templates.current_page === templates.last_page"
                        @click="router.get(templatesIndex.url({ query: { ...filters, page: templates.current_page + 1 } }))"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-primary/5">
            <div class="container">
                <div class="max-w-3xl mx-auto text-center">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4">
                        Can't Find What You're Looking For?
                    </h2>
                    <p class="text-muted-foreground mb-6">
                        Create your own professional CV from scratch with our AI-powered builder.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Button size="lg" as-child>
                            <Link href="/register">
                                <Sparkles class="h-4 w-4 mr-2" />
                                Create with AI
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link href="/pricing">
                                View Pricing
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>

<style scoped>
.bg-grid-pattern {
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
</style>
