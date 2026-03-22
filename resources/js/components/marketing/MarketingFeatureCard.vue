<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon: Component;
    title: string;
    description: string;
    eyebrow?: string;
}>();
</script>

<template>
    <article class="group rounded-[24px] border border-promo-line bg-white p-6 shadow-[0_18px_50px_rgba(232,79,154,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_65px_rgba(232,79,154,0.13)]">
        <div class="flex size-12 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-surface to-promo-purple text-promo-primary">
            <component :is="icon" class="size-5" />
        </div>
        <p
            v-if="eyebrow"
            class="mt-5 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary"
        >
            {{ eyebrow }}
        </p>
        <h3 class="mt-3 text-base font-bold text-promo-ink sm:text-lg">
            {{ title }}
        </h3>
        <p class="mt-3 text-sm leading-6 text-promo-muted">
            {{ description }}
        </p>
    </article>
</template>
