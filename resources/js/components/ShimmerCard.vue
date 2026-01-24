<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

defineProps<{
    lines?: number;
    showAvatar?: boolean;
    showTitle?: boolean;
}>();
</script>

<template>
    <div class="space-y-4 p-6 rounded-xl border bg-card">
        <!-- Header with avatar -->
        <div v-if="showAvatar" class="flex items-center gap-4">
            <Skeleton class="h-12 w-12 rounded-full" />
            <div class="space-y-2 flex-1">
                <Skeleton class="h-4 w-3/4" />
                <Skeleton class="h-3 w-1/2" />
            </div>
        </div>

        <!-- Title -->
        <Skeleton v-if="showTitle" class="h-6 w-2/3" />

        <!-- Content lines -->
        <div class="space-y-3">
            <Skeleton
                v-for="i in (lines ?? 3)"
                :key="i"
                class="h-4"
                :class="i === (lines ?? 3) ? 'w-4/5' : 'w-full'"
            />
        </div>
    </div>
</template>
