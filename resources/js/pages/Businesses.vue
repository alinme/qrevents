<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, Building2, LayoutPanelTop, MonitorPlay, Repeat2, ShieldCheck, Sparkles, Users } from 'lucide-vue-next';
import MarketingFeatureCard from '@/components/marketing/MarketingFeatureCard.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingStepCard from '@/components/marketing/MarketingStepCard.vue';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { pricing } from '@/routes';
import { create as onboardingCreate } from '@/routes/onboarding';

defineProps<{
    canRegister: boolean;
}>();

const { t } = useTranslations();

const businessImages = [
    '/fake-media/503748303_1048043510209927_1182952490263678396_n.jpg',
    '/fake-media/503058833_1256500452532301_4806283181595489701_n.jpg',
    '/fake-media/500454456_707421155099931_505239495313893019_n.jpg',
    '/fake-media/503096688_1013285264308603_8150632900022158723_n.jpg',
];

const businessFeatures = [
    {
        icon: Building2,
        title: t('marketing.businesses.features.1.title'),
        description: t('marketing.businesses.features.1.description'),
    },
    {
        icon: BriefcaseBusiness,
        title: t('marketing.businesses.features.2.title'),
        description: t('marketing.businesses.features.2.description'),
    },
    {
        icon: Repeat2,
        title: t('marketing.businesses.features.3.title'),
        description: t('marketing.businesses.features.3.description'),
    },
    {
        icon: ShieldCheck,
        title: t('marketing.businesses.features.4.title'),
        description: t('marketing.businesses.features.4.description'),
    },
];

const businessSteps = [
    {
        step: t('marketing.shared.step', { number: '01' }),
        title: t('marketing.businesses.steps.1.title'),
        description: t('marketing.businesses.steps.1.description'),
        image: businessImages[0],
        imageAlt: t('marketing.businesses.steps.1.image_alt'),
        highlights: [
            t('marketing.businesses.steps.1.highlights.1'),
            t('marketing.businesses.steps.1.highlights.2'),
            t('marketing.businesses.steps.1.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '02' }),
        title: t('marketing.businesses.steps.2.title'),
        description: t('marketing.businesses.steps.2.description'),
        image: businessImages[1],
        imageAlt: t('marketing.businesses.steps.2.image_alt'),
        highlights: [
            t('marketing.businesses.steps.2.highlights.1'),
            t('marketing.businesses.steps.2.highlights.2'),
            t('marketing.businesses.steps.2.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '03' }),
        title: t('marketing.businesses.steps.3.title'),
        description: t('marketing.businesses.steps.3.description'),
        image: businessImages[2],
        imageAlt: t('marketing.businesses.steps.3.image_alt'),
        highlights: [
            t('marketing.businesses.steps.3.highlights.1'),
            t('marketing.businesses.steps.3.highlights.2'),
            t('marketing.businesses.steps.3.highlights.3'),
        ],
    },
];
</script>

<template>
    <MarketingLayout
        :title="t('marketing.businesses.meta.title')"
        :description="t('marketing.businesses.meta.description')"
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="grid items-center gap-14 lg:grid-cols-[0.94fr_1.06fr]">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <Sparkles class="size-3.5" />
                        {{ t('marketing.businesses.hero.badge') }}
                    </p>

                    <h1 class="mt-6 text-[2rem] font-extrabold leading-[1.02] tracking-[-0.025em] text-promo-ink sm:text-[2.3rem]">
                        {{ t('marketing.businesses.hero.title') }}
                    </h1>

                    <p class="mt-6 text-base leading-7 text-promo-muted sm:text-lg">
                        {{ t('marketing.businesses.hero.description') }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="onboardingCreate({ query: { plan: 'free' } })"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.businesses.hero.primary_cta') }}
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.businesses.hero.secondary_cta') }}
                        </Link>
                    </div>
                </div>

                <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_24px_70px_rgba(120,86,255,0.12)]">
                    <div class="grid gap-4 lg:grid-cols-[0.92fr_1.08fr]">
                        <div class="grid gap-4">
                            <div class="rounded-[24px] border border-promo-line bg-promo-surface p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex size-11 items-center justify-center rounded-[18px] bg-white text-promo-primary">
                                        <LayoutPanelTop class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                            {{ t('marketing.businesses.hero.operations_title') }}
                                        </p>
                                        <p class="mt-2 text-sm leading-7 text-promo-muted">
                                            {{ t('marketing.businesses.hero.operations_description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-[22px] border border-promo-line bg-white p-4">
                                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                        <Users class="size-4" />
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-promo-ink">
                                        {{ t('marketing.businesses.hero.guest_album_title') }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        {{ t('marketing.businesses.hero.guest_album_description') }}
                                    </p>
                                </div>
                                <div class="rounded-[22px] border border-promo-line bg-white p-4">
                                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                        <MonitorPlay class="size-4" />
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-promo-ink">
                                        {{ t('marketing.businesses.hero.live_wall_title') }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        {{ t('marketing.businesses.hero.live_wall_description') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <img
                                :src="businessImages[0]"
                                :alt="t('marketing.businesses.hero.main_image_alt')"
                                class="aspect-[5/4] w-full rounded-[26px] object-cover"
                            />
                            <div class="grid grid-cols-2 gap-4">
                                <img
                                    :src="businessImages[1]"
                                    :alt="t('marketing.businesses.hero.gallery_upload_alt')"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                                <img
                                    :src="businessImages[2]"
                                    :alt="t('marketing.businesses.hero.gallery_moment_alt')"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.businesses.why.eyebrow')"
                    :title="t('marketing.businesses.why.title')"
                    :description="t('marketing.businesses.why.description')"
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="feature in businessFeatures"
                        :key="feature.title"
                        :icon="feature.icon"
                        :title="feature.title"
                        :description="feature.description"
                    />
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.businesses.flow.eyebrow')"
                    :title="t('marketing.businesses.flow.title')"
                    :description="t('marketing.businesses.flow.description')"
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <MarketingStepCard
                        v-for="item in businessSteps"
                        :key="item.step"
                        :step="item.step"
                        :title="item.title"
                        :description="item.description"
                        :image="item.image"
                        :image-alt="item.imageAlt"
                        :highlights="item.highlights"
                    />
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
