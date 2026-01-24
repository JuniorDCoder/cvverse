<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
            v-for="i in 4"
            :key="i"
            class="p-6 rounded-xl border bg-card space-y-3"
        >
            <div class="flex items-center justify-between">
                <Skeleton class="h-4 w-20" />
                <Skeleton class="h-8 w-8 rounded-lg" />
            </div>
            <Skeleton class="h-8 w-24" />
            <Skeleton class="h-3 w-32" />
        </div>
    </div>
</template>
