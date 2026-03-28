<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, Building2, LayoutPanelTop, MonitorPlay, Repeat2, ShieldCheck, Sparkles, Users } from 'lucide-vue-next';
import MarketingFeatureCard from '@/components/marketing/MarketingFeatureCard.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingStepCard from '@/components/marketing/MarketingStepCard.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { pricing } from '@/routes';

defineProps<{
    canRegister: boolean;
    businessPacks: Array<{
        credits: number;
        bonus_percent: number;
        bonus_credits: number;
        total_credits: number;
    }>;
    businessPlans: Array<{
        slug: string;
        name: string;
        businessCreditCost: number;
        consumerPriceLabel: string;
    }>;
    activateUrl: string | null;
    onboardingUrl: string | null;
    topUpUrl: string | null;
    dashboardUrl: string | null;
}>();

const { t } = useTranslations();
const page = usePage<{
    auth?: {
        user?: {
            accountType?: string;
            isBusinessOnboarded?: boolean;
            businessWalletCredits?: number;
        };
    };
}>();
const topUpForm = useForm({
    credits: 100,
    currency: 'EUR',
});

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

const businessUser = page.props.auth?.user;
const isBusinessUser = businessUser?.accountType === 'business' || businessUser?.accountType === 'super_admin';
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
                            v-if="dashboardUrl"
                            :href="dashboardUrl"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Open business dashboard
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            v-else-if="activateUrl"
                            :href="activateUrl"
                            method="post"
                            as="button"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Switch to business
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            v-else-if="onboardingUrl"
                            :href="onboardingUrl"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Finish business profile
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            v-else
                            href="/login"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Sign in to use business mode
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
                <div class="grid gap-6 rounded-[28px] border border-promo-line bg-promo-bg p-6 lg:grid-cols-[0.9fr_1.1fr]">
                    <div>
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                            Business wallet
                        </p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-promo-ink">
                            Top up credits once, use them across paid events
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-promo-muted">
                            One credit is one euro. Business events consume credits instead of going through the normal consumer checkout every time.
                        </p>

                        <div class="mt-5 grid gap-3">
                            <div
                                v-for="plan in businessPlans"
                                :key="plan.slug"
                                class="rounded-[20px] border border-promo-line bg-white px-4 py-4 text-sm"
                            >
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ plan.name }}</p>
                                        <p class="mt-1 text-promo-muted">Consumer price: {{ plan.consumerPriceLabel }}</p>
                                    </div>
                                    <div class="rounded-full bg-promo-surface px-3 py-1 font-semibold text-promo-primary">
                                        {{ plan.businessCreditCost }} credits
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-promo-line bg-white p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-promo-ink">Launch top-up packs</p>
                                <p class="mt-1 text-sm text-promo-muted">
                                    {{ isBusinessUser ? `Current wallet: ${businessUser?.businessWalletCredits ?? 0} credits` : 'Switch to business mode, complete the profile, then top up credits.' }}
                                </p>
                            </div>

                            <select v-model="topUpForm.currency" class="rounded-full border border-promo-line bg-white px-3 py-2 text-sm text-promo-ink">
                                <option value="EUR">EUR</option>
                                <option value="RON">RON</option>
                                <option value="GBP">GBP</option>
                            </select>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <button
                                v-for="pack in businessPacks"
                                :key="pack.credits"
                                type="button"
                                class="rounded-[20px] border border-promo-line bg-promo-bg px-4 py-4 text-left transition hover:border-promo-primary/30 hover:bg-white"
                                @click="topUpForm.credits = pack.credits"
                            >
                                <p class="text-lg font-semibold text-promo-ink">{{ pack.credits }} credits</p>
                                <p class="mt-1 text-sm text-promo-muted">
                                    {{ pack.bonus_percent > 0 ? `+${pack.bonus_credits} bonus (${pack.bonus_percent}%)` : 'No bonus' }}
                                </p>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-promo-primary">
                                    Total granted: {{ pack.total_credits }}
                                </p>
                            </button>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <Button
                                v-if="topUpUrl && isBusinessUser && businessUser?.isBusinessOnboarded"
                                type="button"
                                class="bg-promo-primary text-white hover:bg-promo-primary-strong"
                                @click="topUpForm.post(topUpUrl)"
                            >
                                Top up {{ topUpForm.credits }} credits
                            </Button>
                            <Link
                                v-else-if="activateUrl"
                                :href="activateUrl"
                                method="post"
                                as="button"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                Switch to business first
                            </Link>
                            <Link
                                v-else-if="onboardingUrl"
                                :href="onboardingUrl"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                Finish profile first
                            </Link>
                            <Link
                                v-else
                                href="/login"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                Sign in to top up
                            </Link>
                        </div>
                    </div>
                </div>

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
