<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Loader2, ExternalLink, Download } from 'lucide-vue-next';

interface Props {
    previewUrl: string;
    downloadUrl?: string;
    title?: string;
    open?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'CV Preview',
    open: undefined,
    downloadUrl: undefined,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const internalOpen = ref(false);
const isLoading = ref(true);

const isOpen = computed({
    get: () => props.open !== undefined ? props.open : internalOpen.value,
    set: (val: boolean) => {
        internalOpen.value = val;
        emit('update:open', val);
    },
});

watch(isOpen, (val) => {
    if (val) {
        isLoading.value = true;
    }
});

const onIframeLoad = () => {
    isLoading.value = false;
};

const openInNewTab = () => {
    window.open(props.previewUrl, '_blank');
};

const downloadPdf = () => {
    if (props.downloadUrl) {
        window.open(props.downloadUrl, '_blank');
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogContent class="sm:max-w-4xl h-[90vh] flex flex-col p-0">
            <DialogHeader class="px-6 pt-6 pb-0 shrink-0">
                <div class="flex items-center justify-between pr-8">
                    <div>
                        <DialogTitle>{{ title }}</DialogTitle>
                        <DialogDescription>
                            Preview how your CV will look when exported
                        </DialogDescription>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="downloadUrl"
                            variant="outline"
                            size="sm"
                            @click="downloadPdf"
                        >
                            <Download class="h-4 w-4 mr-2" />
                            PDF
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="openInNewTab"
                        >
                            <ExternalLink class="h-4 w-4 mr-2" />
                            Open in Tab
                        </Button>
                    </div>
                </div>
            </DialogHeader>

            <div class="flex-1 overflow-hidden px-6 pb-6 pt-4">
                <div class="relative w-full h-full rounded-md border bg-white">
                    <!-- Loading State -->
                    <div
                        v-if="isLoading"
                        class="absolute inset-0 flex items-center justify-center bg-muted/50 rounded-md z-10"
                    >
                        <div class="flex flex-col items-center gap-3">
                            <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
                            <span class="text-sm text-muted-foreground">Loading preview...</span>
                        </div>
                    </div>

                    <!-- Preview iframe -->
                    <iframe
                        :src="previewUrl"
                        class="w-full h-full border-0 rounded-md"
                        title="CV Preview"
                        @load="onIframeLoad"
                    ></iframe>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
