<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface Props {
    title?: string;
    description?: string;
    triggerText?: string;
    confirmText?: string;
    variant?: 'destructive' | 'default' | 'secondary' | 'outline' | 'ghost' | 'link'; 
    open?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    description: 'This action cannot be undone. This will permanently delete the resource.',
    triggerText: 'Delete',
    confirmText: 'Delete',
    variant: 'destructive',
    open: undefined,
});

const emit = defineEmits(['confirm', 'cancel', 'update:open']);
const internalOpen = ref(false);

const isOpen = computed({
    get: () => props.open !== undefined ? props.open : internalOpen.value,
    set: (val: boolean) => {
        internalOpen.value = val;
        emit('update:open', val);
    }
});

const handleConfirm = () => {
    emit('confirm');
    isOpen.value = false;
};

const handleCancel = () => {
    emit('cancel');
    isOpen.value = false;
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child v-if="$slots.trigger">
            <slot name="trigger">
                <Button :variant="variant" size="sm">
                    {{ triggerText }}
                </Button>
            </slot>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription class="pt-2">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <slot />
            <DialogFooter class="gap-2 mt-4">
                <DialogClose as-child>
                    <Button variant="secondary" @click="handleCancel">
                        Cancel
                    </Button>
                </DialogClose>
                <Button :variant="variant" @click="handleConfirm">
                    {{ confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
