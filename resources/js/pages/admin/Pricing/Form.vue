<script setup lang="ts">
import { watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Trash2, Plus } from 'lucide-vue-next';
import admin from '@/routes/admin';

const props = defineProps<{
    plan?: any;
    isEditing?: boolean;
}>();

const form = useForm({
    name: props.plan?.name || '',
    slug: props.plan?.slug || '',
    price: props.plan?.price || 0,
    currency: props.plan?.currency || 'XAF',
    interval: props.plan?.interval || 'monthly',
    features: props.plan?.features || [''],
    is_active: props.plan?.is_active ?? true,
});

watch(() => form.name, (val) => {
    if (!props.isEditing) {
        form.slug = val.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
    }
});

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index: number) => {
    form.features.splice(index, 1);
};

const submit = () => {
    if (props.isEditing) {
        form.put(admin.pricingPlans.update(props.plan.id));
    } else {
        form.post(admin.pricingPlans.store());
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input id="name" v-model="form.name" placeholder="e.g. Pro Plan" required />
                <div v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</div>
            </div>
            
            <div class="space-y-2">
                <Label for="slug">Slug</Label>
                <Input id="slug" v-model="form.slug" placeholder="e.g. pro-plan" required />
                <div v-if="form.errors.slug" class="text-sm text-red-500">{{ form.errors.slug }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
                <Label for="price">Price</Label>
                <Input id="price" type="number" step="0.01" v-model="form.price" required />
                <div v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</div>
            </div>

            <div class="space-y-2">
                <Label for="currency">Currency</Label>
                <Select v-model="form.currency">
                    <SelectTrigger>
                        <SelectValue placeholder="Select currency" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="XAF">XAF</SelectItem>
                        <SelectItem value="USD">USD</SelectItem>
                        <SelectItem value="EUR">EUR</SelectItem>
                    </SelectContent>
                </Select>
                <div v-if="form.errors.currency" class="text-sm text-red-500">{{ form.errors.currency }}</div>
            </div>

            <div class="space-y-2">
                <Label for="interval">Interval</Label>
                <Select v-model="form.interval">
                    <SelectTrigger>
                        <SelectValue placeholder="Select interval" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="monthly">Monthly</SelectItem>
                        <SelectItem value="yearly">Yearly</SelectItem>
                        <SelectItem value="one_time">One Time</SelectItem>
                    </SelectContent>
                </Select>
                <div v-if="form.errors.interval" class="text-sm text-red-500">{{ form.errors.interval }}</div>
            </div>
        </div>

        <div class="space-y-3">
            <Label>Features</Label>
            <div v-for="(feature, index) in form.features" :key="index" class="flex gap-2">
                <Input v-model="form.features[index]" placeholder="Feature description" required />
                <Button type="button" variant="ghost" size="icon" @click="removeFeature(index)" :disabled="form.features.length === 1">
                    <Trash2 class="h-4 w-4 text-muted-foreground hover:text-red-500" />
                </Button>
            </div>
            <Button type="button" variant="outline" size="sm" @click="addFeature" class="mt-2">
                <Plus class="h-4 w-4 mr-2" /> Add Feature
            </Button>
            <div v-if="form.errors.features" class="text-sm text-red-500">{{ form.errors.features }}</div>
        </div>

        <div class="flex items-center space-x-2">
            <Switch id="is-active" :checked="form.is_active" @update:checked="form.is_active = $event" />
            <Label for="is-active">Active Status</Label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <Button variant="outline" as-child>
                <Link :href="admin.pricingPlans.index()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ isEditing ? 'Update Plan' : 'Create Plan' }}
            </Button>
        </div>
    </form>
</template>
