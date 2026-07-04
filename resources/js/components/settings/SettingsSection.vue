<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <section
        class="grid gap-x-10 gap-y-5 border-t border-brand-border/70 py-8 first:border-t-0 first:pt-0 md:grid-cols-[240px_minmax(0,1fr)]"
    >
        <div>
            <h2 class="text-sm font-semibold text-brand-ink">{{ title }}</h2>
            <p v-if="description" class="dashboard-meta mt-1.5 leading-relaxed">
                {{ description }}
            </p>
        </div>
        <div class="space-y-5">
            <slot />
        </div>
    </section>
</template>
