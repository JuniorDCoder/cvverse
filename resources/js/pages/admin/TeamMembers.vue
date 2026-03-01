<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Eye, EyeOff, Users } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    store,
    update,
    destroy,
    toggleStatus,
    toggleSectionVisibility,
} from '@/actions/App/Http/Controllers/Admin/TeamMemberController';

interface TeamMember {
    id: number;
    name: string;
    role: string;
    bio: string | null;
    photo: string | null;
    photo_url: string | null;
    initials: string;
    is_active: boolean;
    sort_order: number;
}

const props = defineProps<{
    teamMembers: TeamMember[];
    teamSectionVisible: boolean;
}>();

const showDialog = ref(false);
const editingMember = ref<TeamMember | null>(null);
const photoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    name: '',
    role: '',
    bio: '',
    photo: null as File | null,
    is_active: true,
    sort_order: 0,
});

const sectionVisible = ref(props.teamSectionVisible);

const openCreateDialog = () => {
    editingMember.value = null;
    form.reset();
    form.clearErrors();
    photoPreview.value = null;
    showDialog.value = true;
};

const openEditDialog = (member: TeamMember) => {
    editingMember.value = member;
    form.name = member.name;
    form.role = member.role;
    form.bio = member.bio ?? '';
    form.photo = null;
    form.is_active = member.is_active;
    form.sort_order = member.sort_order;
    form.clearErrors();
    photoPreview.value = member.photo_url;
    showDialog.value = true;
};

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.[0]) {
        form.photo = target.files[0];
        photoPreview.value = URL.createObjectURL(target.files[0]);
    }
};

const submitForm = () => {
    if (editingMember.value) {
        form.post(update.url({ teamMember: editingMember.value.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    } else {
        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                showDialog.value = false;
            },
        });
    }
};

const deleteMember = (member: TeamMember) => {
    if (confirm(`Are you sure you want to delete ${member.name}?`)) {
        router.delete(destroy.url({ teamMember: member.id }), {
            preserveScroll: true,
        });
    }
};

const toggleMemberStatus = (member: TeamMember) => {
    router.patch(toggleStatus.url({ teamMember: member.id }), {}, {
        preserveScroll: true,
    });
};

const toggleSection = (value: boolean) => {
    sectionVisible.value = value;
    router.put(toggleSectionVisibility.url(), { visible: value }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Admin', href: '/admin' }, { title: 'Team Members' }]">
        <Head title="Team Members" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Team Members</h1>
                    <p class="text-muted-foreground">Manage team members shown on the About page</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <Label for="section-toggle" class="text-sm">Show Team Section</Label>
                        <Switch
                            id="section-toggle"
                            :checked="sectionVisible"
                            @update:checked="toggleSection"
                        />
                    </div>
                    <Button @click="openCreateDialog">
                        <Plus class="h-4 w-4 mr-2" />
                        Add Member
                    </Button>
                </div>
            </div>

            <!-- Team Members Grid -->
            <div v-if="teamMembers.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Card
                    v-for="member in teamMembers"
                    :key="member.id"
                    class="relative"
                    :class="{ 'opacity-50': !member.is_active }"
                >
                    <CardContent class="pt-6 text-center">
                        <img
                            v-if="member.photo_url"
                            :src="member.photo_url"
                            :alt="member.name"
                            class="w-20 h-20 rounded-full object-cover mx-auto mb-4"
                        />
                        <div
                            v-else
                            class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center mx-auto mb-4 text-white text-xl font-bold"
                        >
                            {{ member.initials }}
                        </div>

                        <h3 class="font-semibold text-lg mb-1">{{ member.name }}</h3>
                        <p class="text-sm text-primary font-medium mb-2">{{ member.role }}</p>
                        <p class="text-sm text-muted-foreground mb-4 line-clamp-2">{{ member.bio }}</p>

                        <div class="flex items-center justify-between">
                            <Badge :variant="member.is_active ? 'default' : 'secondary'">
                                {{ member.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                            <div class="flex items-center gap-1">
                                <Button variant="ghost" size="icon" @click="toggleMemberStatus(member)" :title="member.is_active ? 'Deactivate' : 'Activate'">
                                    <Eye v-if="member.is_active" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="openEditDialog(member)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" @click="deleteMember(member)">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="py-16">
                <CardContent class="text-center">
                    <Users class="h-12 w-12 mx-auto mb-4 text-muted-foreground" />
                    <h3 class="text-lg font-semibold mb-2">No team members yet</h3>
                    <p class="text-muted-foreground mb-4">Add team members to display on the About page.</p>
                    <Button @click="openCreateDialog">
                        <Plus class="h-4 w-4 mr-2" />
                        Add First Member
                    </Button>
                </CardContent>
            </Card>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:open="showDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingMember ? 'Edit' : 'Add' }} Team Member</DialogTitle>
                    <DialogDescription>
                        {{ editingMember ? 'Update team member details.' : 'Add a new team member to display on the About page.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Photo -->
                    <div class="space-y-2">
                        <Label>Photo</Label>
                        <div class="flex items-center gap-4">
                            <img
                                v-if="photoPreview"
                                :src="photoPreview"
                                class="w-16 h-16 rounded-full object-cover"
                                alt="Preview"
                            />
                            <div
                                v-else
                                class="w-16 h-16 rounded-full bg-muted flex items-center justify-center text-muted-foreground"
                            >
                                <Users class="h-6 w-6" />
                            </div>
                            <div>
                                <input
                                    ref="photoInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handlePhotoChange"
                                />
                                <Button type="button" variant="outline" size="sm" @click="photoInput?.click()">
                                    Choose Photo
                                </Button>
                            </div>
                        </div>
                        <p v-if="form.errors.photo" class="text-sm text-destructive">{{ form.errors.photo }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" placeholder="John Doe" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="role">Role</Label>
                        <Input id="role" v-model="form.role" placeholder="CEO & Founder" />
                        <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="bio">Bio</Label>
                        <Textarea id="bio" v-model="form.bio" placeholder="A brief bio..." rows="3" />
                        <p v-if="form.errors.bio" class="text-sm text-destructive">{{ form.errors.bio }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="sort_order">Sort Order</Label>
                            <Input id="sort_order" v-model.number="form.sort_order" type="number" />
                        </div>
                        <div class="flex items-center gap-2 pt-7">
                            <Switch id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active">Active</Label>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : editingMember ? 'Update' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
