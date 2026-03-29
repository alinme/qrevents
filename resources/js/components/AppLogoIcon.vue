<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

const BRAND_ASSET_VERSION = '20260329-6';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        :src="`/favicon.svg?v=${BRAND_ASSET_VERSION}`"
        alt="EventSmart logo"
        :class="['object-contain', className]"
        loading="eager"
        decoding="async"
        v-bind="$attrs"
    />
</template>
