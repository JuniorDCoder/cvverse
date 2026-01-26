<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';

const { toasts, removeToast, addToast } = useToast();
const page = usePage();

// Watch for flash messages from Inertia
watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        addToast({ type: 'success', message: flash.success, title: 'Success' });
    }
    if (flash?.error) {
        addToast({ type: 'error', message: flash.error, title: 'Error' });
    }
    if (flash?.warning) {
        addToast({ type: 'warning', message: flash.warning, title: 'Warning' });
    }
    if (flash?.info) {
        addToast({ type: 'info', message: flash.info, title: 'Info' });
    }
}, { deep: true, immediate: true });

const getIcon = (type: string) => {
    switch (type) {
        case 'success': return CheckCircle;
        case 'error': return XCircle;
        case 'warning': return AlertTriangle;
        case 'info': return Info;
        default: return Info;
    }
};

const getColorClasses = (type: string) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-900 text-green-800 dark:text-green-200';
        case 'error':
            return 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-900 text-red-800 dark:text-red-200';
        case 'warning':
            return 'bg-yellow-50 dark:bg-yellow-950/30 border-yellow-200 dark:border-yellow-900 text-yellow-800 dark:text-yellow-200';
        case 'info':
            return 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-900 text-blue-800 dark:text-blue-200';
        default:
            return 'bg-gray-50 dark:bg-gray-950/30 border-gray-200 dark:border-gray-900 text-gray-800 dark:text-gray-200';
    }
};

const getIconColorClass = (type: string) => {
    switch (type) {
        case 'success': return 'text-green-600 dark:text-green-400';
        case 'error': return 'text-red-600 dark:text-red-400';
        case 'warning': return 'text-yellow-600 dark:text-yellow-400';
        case 'info': return 'text-blue-600 dark:text-blue-400';
        default: return 'text-gray-600 dark:text-gray-400';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none max-w-md w-full px-4">
        <transition-group
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-x-full"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-full"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'flex items-start gap-3 p-4 rounded-lg border shadow-lg pointer-events-auto',
                    getColorClasses(toast.type)
                ]"
            >
                <component 
                    :is="getIcon(toast.type)" 
                    :class="['h-5 w-5 shrink-0 mt-0.5', getIconColorClass(toast.type)]"
                />
                <div class="flex-1 min-w-0">
                    <p v-if="toast.title" class="font-semibold text-sm">{{ toast.title }}</p>
                    <p class="text-sm" :class="{ 'mt-1': toast.title }">{{ toast.message }}</p>
                </div>
                <button
                    @click="removeToast(toast.id)"
                    class="shrink-0 rounded-full p-1 hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </transition-group>
    </div>
</template>
