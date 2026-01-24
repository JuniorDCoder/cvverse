<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

defineProps<{
    rows?: number;
    columns?: number;
}>();
</script>

<template>
    <div class="rounded-xl border bg-card overflow-hidden">
        <!-- Header -->
        <div class="border-b bg-muted/50 p-4">
            <div class="flex gap-4">
                <Skeleton
                    v-for="i in (columns ?? 4)"
                    :key="i"
                    class="h-4"
                    :class="i === 1 ? 'w-32' : 'w-24'"
                />
            </div>
        </div>
        
        <!-- Rows -->
        <div class="divide-y">
            <div
                v-for="row in (rows ?? 5)"
                :key="row"
                class="p-4 flex items-center gap-4"
            >
                <Skeleton class="h-10 w-10 rounded-full" />
                <div class="flex-1 flex gap-4">
                    <Skeleton
                        v-for="col in (columns ?? 4)"
                        :key="col"
                        class="h-4"
                        :class="col === 1 ? 'w-40' : 'w-24'"
                    />
                </div>
                <Skeleton class="h-8 w-20 rounded-md" />
            </div>
        </div>
    </div>
</template>
