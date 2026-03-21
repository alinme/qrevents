<script setup lang="ts">
defineProps<{
    quote: string;
    name: string;
    detail: string;
    image: string;
}>();
</script>

<template>
    <article class="rounded-[26px] border border-promo-line bg-white p-6 shadow-[0_18px_50px_rgba(232,79,154,0.08)]">
        <div class="flex items-center gap-1 text-promo-primary">
            <span v-for="star in 5" :key="star">★</span>
        </div>
        <p class="mt-5 text-base leading-8 text-promo-ink/88">
            “{{ quote }}”
        </p>
        <div class="mt-6 flex items-center gap-4">
            <img
                :src="image"
                :alt="name"
                class="size-14 rounded-[18px] object-cover"
                loading="lazy"
                decoding="async"
            />
            <div>
                <p class="font-semibold text-promo-ink">{{ name }}</p>
                <p class="text-sm text-promo-muted">{{ detail }}</p>
            </div>
        </div>
    </article>
</template>
