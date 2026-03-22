<script setup lang="ts">
withDefaults(
    defineProps<{
        eyebrow: string;
        title: string;
        description?: string;
        centered?: boolean;
    }>(),
    {
        description: '',
        centered: false,
    },
);
</script>

<template>
    <div
        :class="
            centered
                ? 'mx-auto max-w-3xl text-center'
                : 'max-w-2xl'
        "
    >
        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-promo-primary">
            {{ eyebrow }}
        </p>
        <h2 class="mt-4 text-xl font-extrabold tracking-[-0.02em] text-promo-ink sm:text-[1.45rem] lg:text-[1.7rem]">
            {{ title }}
        </h2>
        <p v-if="description" class="mt-4 text-sm leading-7 text-promo-muted sm:text-base">
            {{ description }}
        </p>
    </div>
</template>
