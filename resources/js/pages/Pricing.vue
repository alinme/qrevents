<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import MarketingProductPreview from '@/components/marketing/MarketingProductPreview.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { businesses } from '@/routes';
import { create as onboardingCreate } from '@/routes/onboarding';

type PricingFeature = {
    label: string;
    help: string;
    available?: boolean;
};

type PricingPlan = {
    id: number;
    slug: string;
    name: string;
    summary: string;
    description: string | null;
    currency: string;
    priceLabel: string;
    billingLabel: string;
    uploadLabel: string;
    retentionLabel: string;
    activeWindowLabel: string;
    customizationLabel: string;
    qualityLabel: string;
    isHighlighted: boolean;
    isDefault: boolean;
    featureItems: PricingFeature[];
    ctaHref: string;
    ctaLabel: string;
};

const props = defineProps<{
    canRegister: boolean;
    plans: PricingPlan[];
    businessTeaser: {
        href: string;
    };
}>();

const { t } = useTranslations();

const pageTitle = computed(() => t('marketing.pricing.meta.title'));
const pageDescription = computed(() => t('marketing.pricing.meta.description'));

const serviceExamples = [
    {
        title: t('marketing.pricing.simple.examples.1.title'),
        description: t('marketing.pricing.simple.examples.1.description'),
    },
    {
        title: t('marketing.pricing.simple.examples.2.title'),
        description: t('marketing.pricing.simple.examples.2.description'),
    },
    {
        title: t('marketing.pricing.simple.examples.3.title'),
        description: t('marketing.pricing.simple.examples.3.description'),
    },
];

const comparisonRows = computed(() => [
    { label: t('marketing.pricing.simple.compare.uploads'), values: props.plans.map((plan) => plan.uploadLabel) },
    { label: t('marketing.pricing.simple.compare.retention'), values: props.plans.map((plan) => plan.retentionLabel) },
    { label: t('marketing.pricing.simple.compare.active_window'), values: props.plans.map((plan) => plan.activeWindowLabel) },
    { label: t('marketing.pricing.simple.compare.customisation'), values: props.plans.map((plan) => plan.customizationLabel) },
]);
</script>

<template>
    <MarketingLayout
        :title="pageTitle"
        :description="pageDescription"
        :can-register="canRegister"
    >
        <section class="mx-auto grid max-w-7xl gap-12 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:pb-20 lg:pt-16">
            <div class="max-w-2xl">
                <p class="marketing-kicker inline-flex items-center gap-2">
                    <Sparkles class="size-4" />
                    {{ t('marketing.pricing.simple.hero.kicker') }}
                </p>
                <h1 class="marketing-display mt-6 text-[3.1rem] sm:text-[3.9rem] lg:text-[4.5rem]">
                    {{ t('marketing.pricing.simple.hero.title') }}
                </h1>
                <p class="marketing-copy mt-6 max-w-xl">
                    {{ t('marketing.pricing.simple.hero.description') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="onboardingCreate({ query: { plan: 'free' } })"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{ t('marketing.actions.create_event') }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        :href="businessTeaser.href || businesses().url"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                    >
                        {{ t('marketing.pricing.simple.hero.secondary_cta') }}
                    </Link>
                </div>
            </div>

            <MarketingProductPreview
                variant="hero"
                caption="Simple plans. Clear guest experience."
                aspect-class="aspect-[5/4]"
            />
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.pricing.simple.sections.plans.eyebrow')"
                :title="t('marketing.pricing.simple.sections.plans.title')"
                :description="t('marketing.pricing.simple.sections.plans.description')"
                centered
            />

            <div class="mt-14 grid gap-6 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    class="rounded-[1.5rem] border border-promo-line bg-white px-6 py-6 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]"
                >
                    <p class="marketing-kicker">
                        {{ plan.name }}
                    </p>
                    <h2 class="marketing-display mt-3 text-[2.4rem]">
                        {{ plan.priceLabel }}
                    </h2>
                    <p class="mt-2 text-sm font-semibold text-promo-ink">
                        {{ plan.summary }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-promo-muted">
                        {{ plan.description || t('marketing.pricing.plan.description_fallback') }}
                    </p>

                    <div class="mt-5 divide-y divide-promo-line border-y border-promo-line">
                        <div
                            v-for="feature in plan.featureItems.slice(0, 4)"
                            :key="`${plan.id}-${feature.label}`"
                            class="py-3 text-sm text-promo-ink"
                        >
                            {{ feature.label }}
                        </div>
                    </div>

                    <Link
                        :href="plan.ctaHref"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{ plan.ctaLabel }}
                        <ArrowRight class="size-4" />
                    </Link>
                </article>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.pricing.simple.sections.examples.eyebrow')"
                    :title="t('marketing.pricing.simple.sections.examples.title')"
                    centered
                />

                <div class="mt-14 grid gap-8 lg:grid-cols-3">
                    <article
                        v-for="item in serviceExamples"
                        :key="item.title"
                        class="space-y-4"
                    >
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ item.title }}
                        </h3>
                        <p class="marketing-copy">
                            {{ item.description }}
                        </p>
                        <MarketingProductPreview
                            :variant="
                                item.title === t('marketing.pricing.simple.examples.1.title')
                                    ? 'guest-flow'
                                    : item.title === t('marketing.pricing.simple.examples.2.title')
                                      ? 'live-wall'
                                      : 'business-dashboard'
                            "
                            :caption="
                                item.title === t('marketing.pricing.simple.examples.1.title')
                                    ? 'Birthday guests upload in seconds.'
                                    : item.title === t('marketing.pricing.simple.examples.2.title')
                                      ? 'Wedding weekend memories on the wall.'
                                      : 'Client work with billing credits.'
                            "
                            aspect-class="aspect-[4/3]"
                        />
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.pricing.simple.sections.compare.eyebrow')"
                :title="t('marketing.pricing.simple.sections.compare.title')"
                :description="t('marketing.pricing.simple.sections.compare.description')"
            />

            <div class="mt-12 divide-y divide-promo-line border-y border-promo-line">
                <div
                    class="hidden gap-4 py-4 text-sm font-semibold text-promo-ink lg:grid"
                    :style="{ gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` }"
                >
                    <div>{{ t('marketing.pricing.simple.compare.capability') }}</div>
                    <div v-for="plan in plans" :key="plan.id">{{ plan.name }}</div>
                </div>

                <div
                    v-for="row in comparisonRows"
                    :key="row.label"
                    class="grid gap-3 py-4 lg:items-center"
                    :style="plans.length > 0 ? { gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` } : undefined"
                >
                    <div class="text-sm font-semibold text-promo-ink">
                        {{ row.label }}
                    </div>
                    <div
                        v-for="value in row.values"
                        :key="`${row.label}-${value}`"
                        class="text-sm text-promo-muted"
                    >
                        {{ value }}
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8">
                <div class="max-w-xl">
                    <p class="marketing-kicker">
                        {{ t('marketing.pricing.simple.sections.business.eyebrow') }}
                    </p>
                    <h2 class="marketing-display mt-3 text-[2.7rem] sm:text-[3.1rem]">
                        {{ t('marketing.pricing.simple.sections.business.title') }}
                    </h2>
                    <p class="marketing-copy mt-4">
                        {{ t('marketing.pricing.simple.sections.business.description') }}
                    </p>

                    <Link
                        :href="businessTeaser.href || businesses().url"
                        class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{ t('marketing.pricing.simple.sections.business.cta') }}
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <MarketingProductPreview
                    variant="business-billing"
                    caption="Credits, history, and event-by-event spend."
                    aspect-class="aspect-[16/10]"
                />
            </div>
        </section>
    </MarketingLayout>
</template>
