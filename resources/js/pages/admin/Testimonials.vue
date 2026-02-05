<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    Star, 
    Trash2, 
    Pencil, 
    Eye, 
    EyeOff,
    Quote,
    Users,
    FileText,
    Globe,
    ThumbsUp,
    BarChart3,
    Save
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Testimonial {
    id: number;
    author_name: string;
    author_role: string;
    author_company: string | null;
    author_avatar: string | null;
    quote: string;
    rating: number;
    is_featured: boolean;
    is_active: boolean;
    sort_order: number;
    initials: string;
    created_at: string;
}

interface Stats {
    users_count: string;
    cvs_created: string;
    success_rate: string;
    countries: string;
    user_rating: string;
}

interface Props {
    testimonials: Testimonial[];
    stats: Stats;
}

const props = defineProps<Props>();

const search = ref('');
const isDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const testimonialToDelete = ref<Testimonial | null>(null);
const editingTestimonial = ref<Testimonial | null>(null);

const form = useForm({
    author_name: '',
    author_role: '',
    author_company: '',
    quote: '',
    rating: 5,
    is_featured: true,
    is_active: true,
    sort_order: 0,
});

const statsForm = useForm({
    users_count: props.stats.users_count,
    cvs_created: props.stats.cvs_created,
    success_rate: props.stats.success_rate,
    countries: props.stats.countries,
    user_rating: props.stats.user_rating,
});

const filteredTestimonials = computed(() => {
    if (!search.value) return props.testimonials;
    const searchLower = search.value.toLowerCase();
    return props.testimonials.filter(t => 
        t.author_name.toLowerCase().includes(searchLower) ||
        t.author_company?.toLowerCase().includes(searchLower) ||
        t.quote.toLowerCase().includes(searchLower)
    );
});

const openCreateDialog = () => {
    editingTestimonial.value = null;
    form.reset();
    form.sort_order = props.testimonials.length;
    isDialogOpen.value = true;
};

const openEditDialog = (testimonial: Testimonial) => {
    editingTestimonial.value = testimonial;
    form.author_name = testimonial.author_name;
    form.author_role = testimonial.author_role;
    form.author_company = testimonial.author_company || '';
    form.quote = testimonial.quote;
    form.rating = testimonial.rating;
    form.is_featured = testimonial.is_featured;
    form.is_active = testimonial.is_active;
    form.sort_order = testimonial.sort_order;
    isDialogOpen.value = true;
};

const submitForm = () => {
    if (editingTestimonial.value) {
        form.put(`/admin/testimonials/${editingTestimonial.value.id}`, {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/testimonials', {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = (testimonial: Testimonial) => {
    testimonialToDelete.value = testimonial;
    isDeleteDialogOpen.value = true;
};

const deleteTestimonial = () => {
    if (testimonialToDelete.value) {
        router.delete(`/admin/testimonials/${testimonialToDelete.value.id}`, {
            onSuccess: () => {
                isDeleteDialogOpen.value = false;
                testimonialToDelete.value = null;
            },
        });
    }
};

const toggleStatus = (testimonial: Testimonial) => {
    router.patch(`/admin/testimonials/${testimonial.id}/toggle-status`, {}, {
        preserveScroll: true,
    });
};

const toggleFeatured = (testimonial: Testimonial) => {
    router.patch(`/admin/testimonials/${testimonial.id}/toggle-featured`, {}, {
        preserveScroll: true,
    });
};

const saveStats = () => {
    statsForm.put('/admin/site-stats', {
        preserveScroll: true,
    });
};

const renderStars = (rating: number) => {
    return Array(5).fill(0).map((_, i) => i < rating);
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Testimonials & Stats' }]">
        <Head title="Manage Testimonials & Stats" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Testimonials & Site Stats</h1>
                    <p class="text-muted-foreground">Manage testimonials and statistics shown on the landing page</p>
                </div>
                <Button @click="openCreateDialog">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Testimonial
                </Button>
            </div>

            <!-- Site Stats Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5 text-primary" />
                        Site Statistics
                    </CardTitle>
                    <CardDescription>These stats are displayed on the landing page hero and CTA sections</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="space-y-2">
                            <Label class="flex items-center gap-2">
                                <Users class="h-4 w-4 text-muted-foreground" />
                                Users Count
                            </Label>
                            <Input v-model="statsForm.users_count" placeholder="e.g., 500,000+" />
                        </div>
                        <div class="space-y-2">
                            <Label class="flex items-center gap-2">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                CVs Created
                            </Label>
                            <Input v-model="statsForm.cvs_created" placeholder="e.g., 500K+" />
                        </div>
                        <div class="space-y-2">
                            <Label class="flex items-center gap-2">
                                <ThumbsUp class="h-4 w-4 text-muted-foreground" />
                                Success Rate
                            </Label>
                            <Input v-model="statsForm.success_rate" placeholder="e.g., 95%" />
                        </div>
                        <div class="space-y-2">
                            <Label class="flex items-center gap-2">
                                <Globe class="h-4 w-4 text-muted-foreground" />
                                Countries
                            </Label>
                            <Input v-model="statsForm.countries" placeholder="e.g., 150+" />
                        </div>
                        <div class="space-y-2">
                            <Label class="flex items-center gap-2">
                                <Star class="h-4 w-4 text-muted-foreground" />
                                User Rating
                            </Label>
                            <Input v-model="statsForm.user_rating" placeholder="e.g., 4.9/5" />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <Button @click="saveStats" :disabled="statsForm.processing">
                            <Save class="h-4 w-4 mr-2" />
                            Save Statistics
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Search -->
            <div class="flex items-center gap-4">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="search"
                        placeholder="Search testimonials..." 
                        class="pl-9"
                    />
                </div>
                <Badge variant="secondary">{{ filteredTestimonials.length }} testimonials</Badge>
            </div>

            <!-- Testimonials Grid -->
            <div v-if="filteredTestimonials.length === 0" class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
                    <Quote class="h-8 w-8 text-muted-foreground" />
                </div>
                <h3 class="font-semibold text-lg mb-2">No testimonials found</h3>
                <p class="text-muted-foreground mb-4">Add your first testimonial to display on the landing page.</p>
                <Button @click="openCreateDialog">
                    <Plus class="h-4 w-4 mr-2" />
                    Add Testimonial
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card 
                    v-for="testimonial in filteredTestimonials" 
                    :key="testimonial.id"
                    class="relative group"
                    :class="{ 'opacity-50': !testimonial.is_active }"
                >
                    <CardContent class="pt-6">
                        <!-- Status badges -->
                        <div class="absolute top-3 right-3 flex items-center gap-2">
                            <Badge 
                                v-if="testimonial.is_featured" 
                                variant="default"
                                class="bg-yellow-500/10 text-yellow-600 hover:bg-yellow-500/20"
                            >
                                <Star class="h-3 w-3 mr-1 fill-current" />
                                Featured
                            </Badge>
                            <Badge 
                                :variant="testimonial.is_active ? 'default' : 'secondary'"
                                :class="testimonial.is_active ? 'bg-green-500/10 text-green-600' : ''"
                            >
                                {{ testimonial.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>

                        <!-- Quote -->
                        <div class="mb-4 pr-24">
                            <Quote class="h-8 w-8 text-muted-foreground/20 mb-2" />
                            <p class="text-sm text-muted-foreground line-clamp-3">
                                "{{ testimonial.quote }}"
                            </p>
                        </div>

                        <!-- Rating -->
                        <div class="flex items-center gap-1 mb-4">
                            <Star 
                                v-for="(filled, i) in renderStars(testimonial.rating)" 
                                :key="i"
                                class="h-4 w-4"
                                :class="filled ? 'text-yellow-500 fill-yellow-500' : 'text-muted-foreground/30'"
                            />
                        </div>

                        <!-- Author -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center text-white text-sm font-medium">
                                {{ testimonial.initials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ testimonial.author_name }}</p>
                                <p class="text-xs text-muted-foreground truncate">
                                    {{ testimonial.author_role }}
                                    <span v-if="testimonial.author_company"> at {{ testimonial.author_company }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t opacity-0 group-hover:opacity-100 transition-opacity">
                            <Button variant="ghost" size="sm" @click="toggleStatus(testimonial)">
                                <component :is="testimonial.is_active ? EyeOff : Eye" class="h-4 w-4 mr-1" />
                                {{ testimonial.is_active ? 'Hide' : 'Show' }}
                            </Button>
                            <Button variant="ghost" size="sm" @click="toggleFeatured(testimonial)">
                                <Star class="h-4 w-4 mr-1" :class="{ 'fill-yellow-500 text-yellow-500': testimonial.is_featured }" />
                                {{ testimonial.is_featured ? 'Unfeature' : 'Feature' }}
                            </Button>
                            <div class="flex-1" />
                            <Button variant="ghost" size="icon" @click="openEditDialog(testimonial)">
                                <Pencil class="h-4 w-4" />
                            </Button>
                            <Button variant="ghost" size="icon" class="text-destructive" @click="confirmDelete(testimonial)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingTestimonial ? 'Edit Testimonial' : 'Add Testimonial' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingTestimonial ? 'Update the testimonial details.' : 'Add a new testimonial to display on the landing page.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="author_name">Author Name</Label>
                        <Input id="author_name" v-model="form.author_name" placeholder="John Doe" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="author_role">Role/Title</Label>
                        <Input id="author_role" v-model="form.author_role" placeholder="Software Engineer" required />
                    </div>

                    <div class="space-y-2">
                        <Label for="author_company">Company (optional)</Label>
                        <Input id="author_company" v-model="form.author_company" placeholder="Google" />
                    </div>

                    <div class="space-y-2">
                        <Label for="quote">Testimonial Quote</Label>
                        <Textarea 
                            id="quote" 
                            v-model="form.quote" 
                            placeholder="Share their experience with CVverse..." 
                            rows="3"
                            required 
                        />
                    </div>

                    <div class="space-y-2">
                        <Label>Rating</Label>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                @click="form.rating = star"
                                class="p-1 hover:scale-110 transition-transform"
                            >
                                <Star 
                                    class="h-6 w-6 transition-colors"
                                    :class="star <= form.rating ? 'text-yellow-500 fill-yellow-500' : 'text-muted-foreground/30'"
                                />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Switch id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active">Active</Label>
                        </div>
                        <div class="flex items-center gap-2">
                            <Switch id="is_featured" v-model:checked="form.is_featured" />
                            <Label for="is_featured">Featured</Label>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isDialogOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingTestimonial ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <ConfirmDeleteModal
            v-model:open="isDeleteDialogOpen"
            title="Delete Testimonial"
            :description="`Are you sure you want to delete the testimonial from '${testimonialToDelete?.author_name}'? This action cannot be undone.`"
            confirm-text="Delete"
            @confirm="deleteTestimonial"
        />
    </AppLayout>
</template>
