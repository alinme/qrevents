<script setup lang="ts">
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';

const props = defineProps<{
    page: 'privacy' | 'terms';
    sectionCount: number;
    canRegister?: boolean;
}>();

const { t } = useTranslations();

const sections = computed(() =>
    Array.from({ length: props.sectionCount }, (_, index) => ({
        title: t(`legal.${props.page}.s${index + 1}.title`),
        body: t(`legal.${props.page}.s${index + 1}.body`),
    })),
);
</script>

<template>
    <MarketingLayout
        :title="t(`legal.${page}.title`)"
        :description="t(`legal.${page}.intro`)"
        :can-register="canRegister"
    >
        <section class="px-6 pt-14 pb-20 sm:pt-20">
            <div class="mx-auto max-w-3xl">
                <p
                    class="text-xs font-semibold tracking-[0.26em] text-promo-primary uppercase"
                >
                    {{ t(`legal.${page}.updated`) }}
                </p>
                <h1
                    class="mt-3 text-4xl font-semibold tracking-tight text-promo-ink sm:text-5xl"
                >
                    {{ t(`legal.${page}.title`) }}
                </h1>
                <p class="mt-5 text-base leading-7 text-promo-muted">
                    {{ t(`legal.${page}.intro`) }}
                </p>

                <div class="mt-10 space-y-8">
                    <section
                        v-for="(section, index) in sections"
                        :key="index"
                        :id="section.title === 'GDPR' ? 'gdpr' : undefined"
                        class="scroll-mt-24 rounded-3xl border border-promo-line bg-promo-surface p-6 shadow-card sm:p-8"
                    >
                        <h2 class="text-lg font-semibold text-promo-ink">
                            {{ section.title }}
                        </h2>
                        <p class="mt-3 text-sm leading-7 text-promo-muted">
                            {{ section.body }}
                        </p>
                    </section>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
