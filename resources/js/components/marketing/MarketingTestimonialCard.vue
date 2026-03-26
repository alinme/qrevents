<script setup lang="ts">
defineProps<{
    quote: string;
    name: string;
    detail: string;
    image: string;
}>();
</script>

<template>
    <article class="rounded-[22px] border border-promo-line/80 bg-white p-5">
        <div class="flex items-center gap-1 text-promo-primary">
            <span v-for="star in 5" :key="star">★</span>
        </div>
        <p class="mt-4 text-[0.98rem] leading-7 text-promo-ink/88">
            “{{ quote }}”
        </p>
        <div class="mt-5 flex items-center gap-4">
            <img
                :src="image"
                :alt="name"
                class="size-12 rounded-[16px] object-cover"
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
