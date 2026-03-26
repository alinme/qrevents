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
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
            {{ eyebrow }}
        </p>
        <h2 class="mt-3 text-xl font-extrabold tracking-[-0.02em] text-promo-ink sm:text-[1.35rem] lg:text-[1.55rem]">
            {{ title }}
        </h2>
        <p v-if="description" class="mt-3 max-w-[42rem] text-sm leading-6 text-promo-muted sm:text-[0.95rem]">
            {{ description }}
        </p>
    </div>
</template>
