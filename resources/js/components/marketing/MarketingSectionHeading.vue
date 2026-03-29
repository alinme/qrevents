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
        <p class="marketing-kicker">
            {{ eyebrow }}
        </p>
        <h2 class="marketing-display mt-3 text-[2.1rem] sm:text-[2.4rem] lg:text-[2.7rem]">
            {{ title }}
        </h2>
        <p v-if="description" class="marketing-copy mt-4 max-w-[42rem]">
            {{ description }}
        </p>
    </div>
</template>
