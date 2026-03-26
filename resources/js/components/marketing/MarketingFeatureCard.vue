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
    <article class="group rounded-[22px] border border-promo-line/80 bg-white px-5 py-5 transition duration-300 hover:border-promo-primary/20 hover:bg-promo-surface/35">
        <div class="flex size-11 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
            <component :is="icon" class="size-5" />
        </div>
        <p
            v-if="eyebrow"
            class="mt-4 text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary"
        >
            {{ eyebrow }}
        </p>
        <h3 class="mt-3 text-[0.98rem] font-bold text-promo-ink sm:text-base">
            {{ title }}
        </h3>
        <p class="mt-2 text-sm leading-6 text-promo-muted">
            {{ description }}
        </p>
    </article>
</template>
