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
    <article class="overflow-hidden rounded-[28px] border border-promo-line bg-white shadow-[0_18px_55px_rgba(120,86,255,0.08)]">
        <img
            :src="image"
            :alt="imageAlt"
            class="aspect-[4/3] w-full object-cover"
            loading="lazy"
            decoding="async"
        />
        <div class="space-y-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                {{ step }}
            </p>
            <h3 class="text-2xl font-bold tracking-[-0.03em] text-promo-ink">
                {{ title }}
            </h3>
            <p class="text-sm leading-7 text-promo-muted">
                {{ description }}
            </p>

            <ul v-if="highlights?.length" class="grid gap-2 text-sm text-promo-ink/82">
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
