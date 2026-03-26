<script setup lang="ts">
defineProps<{
    step: string;
    title: string;
    description: string;
    image: string;
    imageAlt: string;
    highlights?: string[];
}>();
</script>

<template>
    <article class="overflow-hidden rounded-[24px] border border-promo-line/80 bg-white">
        <img
            :src="image"
            :alt="imageAlt"
            class="aspect-[4/3] w-full object-cover"
            loading="lazy"
            decoding="async"
        />
        <div class="space-y-3 p-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                {{ step }}
            </p>
            <h3 class="text-[0.98rem] font-bold tracking-[-0.015em] text-promo-ink sm:text-[1.05rem]">
                {{ title }}
            </h3>
            <p class="text-sm leading-6 text-promo-muted">
                {{ description }}
            </p>

            <ul v-if="highlights?.length" class="grid gap-1.5 text-[13px] text-promo-ink/82 sm:text-sm">
                <li
                    v-for="highlight in highlights"
                    :key="highlight"
                    class="flex items-start gap-2"
                >
                    <span class="mt-2 size-1.5 rounded-full bg-promo-primary" />
                    <span>{{ highlight }}</span>
                </li>
            </ul>
        </div>
    </article>
</template>
