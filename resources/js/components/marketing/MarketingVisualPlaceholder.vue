<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        title: string;
        description: string;
        aspectClass?: string;
    }>(),
    {
        aspectClass: 'aspect-[4/3]',
    },
);
</script>

<template>
    <div class="marketing-placeholder" :class="aspectClass">
        <div class="max-w-md">
            <p class="marketing-placeholder-label">
                {{ label }}
            </p>
            <h3 class="marketing-placeholder-title">
                {{ title }}
            </h3>
            <p class="marketing-placeholder-copy">
                {{ description }}
            </p>
        </div>
    </div>
</template>
