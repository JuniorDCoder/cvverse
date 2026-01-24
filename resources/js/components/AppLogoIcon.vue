<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();

const logoSrc = '/logo.svg';
</script>

<template>
    <!-- Use the generated logo.svg so theming and sizing can be controlled via classes -->
    <img :src="logoSrc" :class="className" v-bind="$attrs" alt="CVverse logo" />
</template>
