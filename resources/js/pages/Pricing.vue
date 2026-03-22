<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, CircleHelp, Sparkles } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
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
}>();

const { t } = useTranslations();

const pageTitle = computed(() => t('marketing.pricing.meta.title'));
const pageDescription = computed(() => t('marketing.pricing.meta.description'));
const featureHelpOpen = ref(false);
const refundPolicyOpen = ref(false);
const selectedFeatureHelp = ref<{ label: string; help: string } | null>(null);

const compareRows = computed(() => [
    { label: t('marketing.pricing.compare.rows.uploads'), values: props.plans.map((plan) => plan.uploadLabel) },
    { label: t('marketing.pricing.compare.rows.retention'), values: props.plans.map((plan) => plan.retentionLabel) },
    { label: t('marketing.pricing.compare.rows.active_window'), values: props.plans.map((plan) => plan.activeWindowLabel) },
    { label: t('marketing.pricing.compare.rows.customization'), values: props.plans.map((plan) => plan.customizationLabel) },
]);

const compareColumns = computed(() =>
    props.plans.map((plan, index) => ({
        id: plan.id,
        name: plan.name,
        rows: compareRows.value.map((row) => ({
            label: row.label,
            value: row.values[index] ?? '',
        })),
    })),
);

const commonFeatures = computed(() => [
    {
        title: t('marketing.pricing.common_features.live_wall.title'),
        description: t('marketing.pricing.common_features.live_wall.description'),
    },
    {
        title: t('marketing.pricing.common_features.digital_album.title'),
        description: t('marketing.pricing.common_features.digital_album.description'),
    },
    {
        title: t('marketing.pricing.common_features.no_app.title'),
        description: t('marketing.pricing.common_features.no_app.description'),
    },
    {
        title: t('marketing.pricing.common_features.customizations.title'),
        description: t('marketing.pricing.common_features.customizations.description'),
    },
    {
        title: t('marketing.pricing.common_features.captions.title'),
        description: t('marketing.pricing.common_features.captions.description'),
    },
    {
        title: t('marketing.pricing.common_features.private.title'),
        description: t('marketing.pricing.common_features.private.description'),
    },
]);

const openFeatureHelp = (label: string, help: string): void => {
    selectedFeatureHelp.value = { label, help };
    featureHelpOpen.value = true;
};
</script>

<template>
    <MarketingLayout
        :title="pageTitle"
        :description="pageDescription"
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-5xl px-4 pb-14 pt-12 text-center sm:px-6 lg:px-8 lg:pb-18 lg:pt-16">
            <div class="mx-auto max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-promo-primary/18 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.06)]">
                        <Sparkles class="size-3.5" />
                        {{ t('marketing.pricing.hero.badge') }}
                </p>
                <h1 class="mt-6 text-[1.85rem] font-extrabold leading-tight tracking-[-0.03em] text-promo-ink sm:text-[2.15rem]">
                    {{ t('marketing.pricing.hero.badge') }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-promo-muted sm:text-base">
                    {{ t('marketing.pricing.hero.title') }}
                </p>
            </div>

            <div class="mx-auto mt-8 max-w-4xl rounded-[26px] border-2 border-promo-primary/30 bg-white px-5 py-5 text-left shadow-[0_16px_44px_rgba(232,79,154,0.08)] sm:px-6">
                <p class="text-sm leading-7 text-promo-ink sm:text-[0.97rem]">
                    <span class="font-semibold text-promo-primary">{{ t('marketing.pricing.guarantee.title') }}</span>
                    {{ ' ' }}{{ t('marketing.pricing.guarantee.description') }}
                    <button
                        type="button"
                        class="font-semibold text-promo-primary underline decoration-promo-primary/40 underline-offset-4 transition hover:text-promo-primary-strong"
                        @click="refundPolicyOpen = true"
                    >
                        {{ t('marketing.pricing.guarantee.link_label') }}
                    </button>
                </p>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-18 sm:px-6 lg:px-8 lg:pb-22">
            <div
                v-if="plans.length > 0"
                class="grid gap-5 [grid-template-columns:repeat(auto-fit,minmax(280px,1fr))]"
            >
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    class="flex h-full flex-col rounded-[28px] border-2 bg-white px-5 py-6 shadow-[0_16px_44px_rgba(232,79,154,0.06)] sm:px-6"
                    :class="plan.isHighlighted ? 'border-promo-primary/45' : 'border-promo-line'"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                {{ plan.name }}
                            </p>
                            <h2 class="mt-3 text-[1.7rem] font-extrabold tracking-[-0.03em] text-promo-ink">
                                {{ plan.priceLabel }}
                            </h2>
                            <p class="mt-2 text-sm text-promo-muted">
                                {{ plan.billingLabel }}
                            </p>
                        </div>

                        <div
                            v-if="plan.isHighlighted"
                            class="rounded-full border border-promo-primary/20 bg-promo-surface px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-promo-primary"
                        >
                            {{ t('marketing.pricing.plan.most_loved') }}
                        </div>
                    </div>

                    <p class="mt-5 text-sm font-semibold text-promo-ink">
                        {{ plan.summary }}
                    </p>
                    <p class="mt-2 text-sm leading-7 text-promo-muted">
                        {{ plan.description || t('marketing.pricing.plan.description_fallback') }}
                    </p>

                    <div class="mt-6 flex-1 border-t border-promo-line/80 pt-5">
                        <div
                            v-for="feature in plan.featureItems"
                            :key="`${plan.id}-${feature.label}`"
                            class="flex items-start gap-3 py-2.5"
                        >
                            <div class="min-w-0 flex-1 text-sm leading-6 text-promo-ink">
                                <strong class="font-semibold" :class="feature.available === false ? 'text-promo-muted' : 'text-promo-ink'">
                                    {{ feature.label }}
                                </strong>
                            </div>
                            <button
                                type="button"
                                class="inline-flex shrink-0 rounded-full border border-promo-line p-1.5 text-promo-muted transition hover:border-promo-primary/25 hover:text-promo-ink"
                                @click="openFeatureHelp(feature.label, feature.help)"
                            >
                                <CircleHelp class="size-4" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-promo-line/80 pt-5 text-sm text-promo-muted">
                        {{ plan.customizationLabel }}
                        <div
                            v-if="plan.isDefault"
                            class="mt-2 text-[0.68rem] uppercase tracking-[0.16em] text-promo-primary"
                        >
                            {{ t('marketing.pricing.plan.default_hint') }}
                        </div>
                    </div>

                    <Link
                        :href="plan.ctaHref"
                        class="mt-7 inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 text-sm font-semibold transition"
                        :class="
                            plan.isHighlighted
                                ? 'border-promo-primary bg-promo-primary text-white hover:bg-promo-primary-strong'
                                : 'border-promo-line bg-white text-promo-ink hover:border-promo-primary/25 hover:bg-promo-surface'
                        "
                    >
                        {{ plan.ctaLabel }}
                        <ArrowRight class="size-4" />
                    </Link>
                </article>
            </div>
            <div
                v-else
                class="rounded-[28px] border border-promo-line bg-white px-8 py-12 text-center text-promo-muted"
            >
                {{ t('marketing.pricing.empty') }}
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.pricing.compare.eyebrow')"
                    :title="t('marketing.pricing.compare.title')"
                    :description="t('marketing.pricing.compare.description')"
                    centered
                />

                <div v-if="plans.length > 0" class="mt-12 grid gap-4 lg:hidden">
                    <article
                        v-for="plan in compareColumns"
                        :key="plan.id"
                        class="rounded-[26px] border border-promo-line bg-promo-bg p-5"
                    >
                        <h3 class="text-base font-bold text-promo-ink sm:text-lg">{{ plan.name }}</h3>
                        <div class="mt-4 grid gap-3">
                            <div
                                v-for="row in plan.rows"
                                :key="`${plan.id}-${row.label}`"
                                class="rounded-[18px] bg-white px-4 py-3"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                    {{ row.label }}
                                </p>
                                <p class="mt-2 text-sm font-medium leading-6 text-promo-ink">
                                    {{ row.value }}
                                </p>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-if="plans.length > 0"
                    class="mt-12 hidden overflow-hidden rounded-[28px] border border-promo-line bg-white lg:block"
                >
                    <div
                        class="grid border-b border-promo-line bg-promo-surface px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-promo-muted"
                        :style="{ gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` }"
                    >
                        <div>{{ t('marketing.pricing.compare.capability') }}</div>
                        <div v-for="plan in plans" :key="plan.id">{{ plan.name }}</div>
                    </div>

                    <div
                        v-for="row in compareRows"
                        :key="row.label"
                        class="grid px-6 py-4 text-sm text-promo-muted [&:not(:last-child)]:border-b [&:not(:last-child)]:border-promo-line"
                        :style="{ gridTemplateColumns: `1.1fr repeat(${plans.length}, minmax(0, 1fr))` }"
                    >
                        <div class="font-semibold text-promo-ink">{{ row.label }}</div>
                        <div v-for="value in row.values" :key="`${row.label}-${value}`">{{ value }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/38">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-[0.72rem] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                        {{ t('marketing.pricing.common_features.eyebrow') }}
                    </p>
                    <h2 class="mt-4 text-[1.8rem] font-extrabold tracking-[-0.03em] text-promo-ink sm:text-[2.05rem]">
                        {{ t('marketing.pricing.common_features.title') }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <h3 class="text-base font-bold text-promo-ink sm:text-lg">{{ t('marketing.pricing.common_features.live_wall.title') }}</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            {{ t('marketing.pricing.common_features.live_wall.description') }}
                        </p>
                    </article>
                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <h3 class="text-base font-bold text-promo-ink sm:text-lg">{{ t('marketing.pricing.common_features.digital_album.title') }}</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            {{ t('marketing.pricing.common_features.digital_album.description') }}
                        </p>
                    </article>
                    <article class="rounded-[26px] border border-promo-line bg-white px-6 py-7">
                        <h3 class="text-base font-bold text-promo-ink sm:text-lg">{{ t('marketing.pricing.common_features.no_app.title') }}</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            {{ t('marketing.pricing.common_features.no_app.description') }}
                        </p>
                    </article>

                    <article
                        v-for="feature in commonFeatures.slice(3)"
                        :key="feature.title"
                        class="rounded-[26px] border border-promo-line bg-white px-6 py-7"
                    >
                        <h3 class="text-base font-bold text-promo-ink sm:text-lg">{{ feature.title }}</h3>
                        <p class="mt-4 text-sm leading-7 text-promo-muted">
                            {{ feature.description }}
                        </p>
                    </article>
                </div>

                <div class="mt-10 text-center text-sm text-promo-muted">
                    {{ t('marketing.pricing.footer_note') }}
                </div>

                <div class="mt-12 text-center">
                    <p class="mb-4 text-sm text-promo-muted">
                        {{ t('marketing.pricing.professionals.description') }}
                    </p>
                    <Link
                        :href="onboardingCreate({ query: { plan: 'free' } })"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{ t('marketing.actions.create_event') }}
                        <ArrowRight class="size-4" />
                    </Link>
                </div>
            </div>
        </section>

        <Dialog v-model:open="refundPolicyOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ t('marketing.pricing.refund.title') }}</DialogTitle>
                    <DialogDescription class="space-y-3 text-left">
                        <p>{{ t('marketing.pricing.refund.subtitle') }}</p>
                        <p>{{ t('marketing.pricing.refund.description') }}</p>
                        <p>{{ t('marketing.pricing.refund.contact') }}</p>
                    </DialogDescription>
                </DialogHeader>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="featureHelpOpen">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ selectedFeatureHelp?.label }}</DialogTitle>
                    <DialogDescription>
                        {{ selectedFeatureHelp?.help }}
                    </DialogDescription>
                </DialogHeader>
            </DialogContent>
        </Dialog>
    </MarketingLayout>
</template>
