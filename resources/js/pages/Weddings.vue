<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Camera, Heart, MessageSquareText, MonitorPlay, QrCode, Sparkles } from 'lucide-vue-next';
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

const weddingImages = [
    '/fake-media/503096688_1013285264308603_8150632900022158723_n.jpg',
    '/fake-media/503962837_9861361353953264_525766796662049219_n.jpg',
    '/fake-media/504824543_1521239702593913_4818893469591191053_n.jpg',
    '/fake-media/503748303_1048043510209927_1182952490263678396_n.jpg',
];

const weddingBenefits = [
    {
        icon: Camera,
        title: t('marketing.weddings.benefits.1.title'),
        description: t('marketing.weddings.benefits.1.description'),
    },
    {
        icon: MessageSquareText,
        title: t('marketing.weddings.benefits.2.title'),
        description: t('marketing.weddings.benefits.2.description'),
    },
    {
        icon: MonitorPlay,
        title: t('marketing.weddings.benefits.3.title'),
        description: t('marketing.weddings.benefits.3.description'),
    },
    {
        icon: Heart,
        title: t('marketing.weddings.benefits.4.title'),
        description: t('marketing.weddings.benefits.4.description'),
    },
];

const weddingSteps = [
    {
        step: t('marketing.shared.step', { number: '01' }),
        title: t('marketing.weddings.steps.1.title'),
        description: t('marketing.weddings.steps.1.description'),
        image: weddingImages[0],
        imageAlt: t('marketing.weddings.steps.1.image_alt'),
        highlights: [
            t('marketing.weddings.steps.1.highlights.1'),
            t('marketing.weddings.steps.1.highlights.2'),
            t('marketing.weddings.steps.1.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '02' }),
        title: t('marketing.weddings.steps.2.title'),
        description: t('marketing.weddings.steps.2.description'),
        image: weddingImages[1],
        imageAlt: t('marketing.weddings.steps.2.image_alt'),
        highlights: [
            t('marketing.weddings.steps.2.highlights.1'),
            t('marketing.weddings.steps.2.highlights.2'),
            t('marketing.weddings.steps.2.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '03' }),
        title: t('marketing.weddings.steps.3.title'),
        description: t('marketing.weddings.steps.3.description'),
        image: weddingImages[3],
        imageAlt: t('marketing.weddings.steps.3.image_alt'),
        highlights: [
            t('marketing.weddings.steps.3.highlights.1'),
            t('marketing.weddings.steps.3.highlights.2'),
            t('marketing.weddings.steps.3.highlights.3'),
        ],
    },
];
</script>

<template>
    <MarketingLayout
        :title="t('marketing.weddings.meta.title')"
        :description="t('marketing.weddings.meta.description')"
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="grid items-center gap-14 lg:grid-cols-[0.92fr_1.08fr]">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <Sparkles class="size-3.5" />
                        {{ t('marketing.weddings.hero.badge') }}
                    </p>

                    <h1 class="mt-6 text-[2rem] font-extrabold leading-[1.02] tracking-[-0.025em] text-promo-ink sm:text-[2.3rem]">
                        {{ t('marketing.weddings.hero.title') }}
                    </h1>

                    <p class="mt-6 text-base leading-7 text-promo-muted sm:text-lg">
                        {{ t('marketing.weddings.hero.description') }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="onboardingCreate({ query: { plan: 'free' } })"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.weddings.hero.primary_cta') }}
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.weddings.hero.secondary_cta') }}
                        </Link>
                    </div>
                </div>

                <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_24px_70px_rgba(232,79,154,0.12)]">
                    <div class="grid gap-4 lg:grid-cols-[1.08fr_0.92fr]">
                        <img
                            :src="weddingImages[0]"
                            :alt="t('marketing.weddings.hero.main_image_alt')"
                            class="aspect-[4/5] w-full rounded-[26px] object-cover"
                        />

                        <div class="grid gap-4">
                            <div class="rounded-[24px] border border-promo-line bg-promo-surface p-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-11 items-center justify-center rounded-[18px] bg-white text-promo-primary">
                                        <QrCode class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                            {{ t('marketing.weddings.hero.qr_title') }}
                                        </p>
                                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                                            {{ t('marketing.weddings.hero.qr_description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <img
                                    :src="weddingImages[1]"
                                    :alt="t('marketing.weddings.hero.gallery_upload_alt')"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                                <img
                                    :src="weddingImages[2]"
                                    :alt="t('marketing.weddings.hero.gallery_memory_alt')"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                            </div>

                            <div class="rounded-[24px] border border-promo-line bg-promo-warm p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                    {{ t('marketing.weddings.hero.reception_title') }}
                                </p>
                                <p class="mt-3 text-sm leading-7 text-promo-muted">
                                    {{ t('marketing.weddings.hero.reception_description') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.weddings.why.eyebrow')"
                    :title="t('marketing.weddings.why.title')"
                    :description="t('marketing.weddings.why.description')"
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="benefit in weddingBenefits"
                        :key="benefit.title"
                        :icon="benefit.icon"
                        :title="benefit.title"
                        :description="benefit.description"
                    />
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.weddings.flow.eyebrow')"
                    :title="t('marketing.weddings.flow.title')"
                    :description="t('marketing.weddings.flow.description')"
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <MarketingStepCard
                        v-for="item in weddingSteps"
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
