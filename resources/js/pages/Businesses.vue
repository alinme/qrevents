<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, Building2, LayoutPanelTop, MonitorPlay, Repeat2, ShieldCheck, Sparkles, Users } from 'lucide-vue-next';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { pricing } from '@/routes';

const props = defineProps<{
    canRegister: boolean;
    businessPacks: Array<{
        credits: number;
        bonus_percent: number;
        bonus_credits: number;
        total_credits: number;
        priceLabels: Record<string, string>;
    }>;
    businessPlans: Array<{
        slug: string;
        name: string;
        businessCreditCost: number;
        consumerPriceLabel: string;
    }>;
    supportedCheckoutCurrencies: string[];
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

const businessOperationNotes = [
    {
        title: 'One credit equals one euro',
        description: 'Top-ups stay simple. The wallet always stores EUR-based credits, even if checkout happens in RON or GBP.',
    },
    {
        title: 'Business uses paid plans only',
        description: 'Business events use Plus or Pro. Free is kept for consumer accounts, so resource usage stays fair.',
    },
    {
        title: 'Top up once, create as needed',
        description: 'Your team can add credit in advance, then launch new events without repeating a full checkout every time.',
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
const selectedPack = computed(
    () => props.businessPacks.find((pack) => pack.credits === topUpForm.credits) ?? props.businessPacks[0] ?? null,
);
const selectedPackPriceLabel = computed(() => {
    if (!selectedPack.value) {
        return null;
    }

    return selectedPack.value.priceLabels[topUpForm.currency] ?? selectedPack.value.priceLabels.EUR ?? null;
});
const businessPrimaryCtaLabel = computed(() => {
    if (topUpUrl && isBusinessUser && businessUser?.isBusinessOnboarded) {
        return `Top up ${topUpForm.credits} credits`;
    }

    if (activateUrl) {
        return 'Switch to business first';
    }

    if (onboardingUrl) {
        return 'Finish profile first';
    }

    return 'Sign in to top up';
});
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

                    <div class="mt-10 grid gap-4 border-t border-promo-line pt-6 sm:grid-cols-3">
                        <div class="sm:pr-4">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                Wallet
                            </p>
                            <p class="mt-2 text-sm font-semibold text-promo-ink">
                                Prepaid credits
                            </p>
                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                Buy once, spend across future paid events.
                            </p>
                        </div>
                        <div class="border-t border-promo-line pt-4 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                Plans
                            </p>
                            <p class="mt-2 text-sm font-semibold text-promo-ink">
                                Plus and Pro
                            </p>
                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                Business mode stays on the same paid resource model as every other event.
                            </p>
                        </div>
                        <div class="border-t border-promo-line pt-4 sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                Fit
                            </p>
                            <p class="mt-2 text-sm font-semibold text-promo-ink">
                                Repeatable delivery
                            </p>
                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                Built for venues, planners, agencies, and event teams running multiple client events.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="grid gap-4 sm:grid-cols-[0.84fr_1.16fr]">
                        <div class="border border-promo-line bg-white px-5 py-5">
                            <div class="flex items-start gap-4">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-[18px] bg-promo-surface text-promo-primary">
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
                        <img
                            :src="businessImages[0]"
                            :alt="t('marketing.businesses.hero.main_image_alt')"
                            class="aspect-[5/4] w-full object-cover"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="border border-promo-line bg-white px-5 py-5">
                            <div class="flex items-start gap-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                    <Users class="size-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-promo-ink">
                                        {{ t('marketing.businesses.hero.guest_album_title') }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        {{ t('marketing.businesses.hero.guest_album_description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="border border-promo-line bg-white px-5 py-5">
                            <div class="flex items-start gap-4">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                    <MonitorPlay class="size-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-promo-ink">
                                        {{ t('marketing.businesses.hero.live_wall_title') }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        {{ t('marketing.businesses.hero.live_wall_description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-[0.88fr_1.12fr] lg:items-start">
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

                        <div class="mt-8 space-y-4 border-t border-promo-line pt-6">
                            <div
                                v-for="note in businessOperationNotes"
                                :key="note.title"
                                class="grid gap-2 border-b border-promo-line pb-4 last:border-b-0 last:pb-0"
                            >
                                <p class="text-sm font-semibold text-promo-ink">
                                    {{ note.title }}
                                </p>
                                <p class="text-sm leading-6 text-promo-muted">
                                    {{ note.description }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-promo-line pt-6">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                Event costs
                            </p>
                            <div class="mt-4 divide-y divide-promo-line border-y border-promo-line">
                                <div
                                    v-for="plan in businessPlans"
                                    :key="plan.slug"
                                    class="flex items-start justify-between gap-4 py-4 text-sm"
                                >
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ plan.name }}</p>
                                        <p class="mt-1 text-promo-muted">Consumer price: {{ plan.consumerPriceLabel }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-promo-primary">
                                        {{ plan.businessCreditCost }} credits
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border border-promo-line bg-promo-bg px-5 py-5 sm:px-6 sm:py-6">
                        <div class="flex flex-col gap-4 border-b border-promo-line pb-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-promo-ink">Launch top-up packs</p>
                                <p class="mt-1 text-sm text-promo-muted">
                                    {{ isBusinessUser ? `Current wallet: ${businessUser?.businessWalletCredits ?? 0} credits` : 'Switch to business mode, complete the profile, then top up credits.' }}
                                </p>
                            </div>

                            <select v-model="topUpForm.currency" class="rounded-full border border-promo-line bg-white px-3 py-2 text-sm text-promo-ink">
                                <option v-for="currency in supportedCheckoutCurrencies" :key="currency" :value="currency">
                                    {{ currency }}
                                </option>
                            </select>
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <button
                                v-for="pack in businessPacks"
                                :key="pack.credits"
                                type="button"
                                class="border px-4 py-4 text-left transition"
                                :class="
                                    topUpForm.credits === pack.credits
                                        ? 'border-promo-primary bg-white'
                                        : 'border-promo-line bg-white/75 hover:border-promo-primary/30'
                                "
                                @click="topUpForm.credits = pack.credits"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <p class="text-lg font-semibold text-promo-ink">{{ pack.credits }} credits</p>
                                    <span
                                        v-if="topUpForm.credits === pack.credits"
                                        class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-promo-primary"
                                    >
                                        Selected
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-promo-muted">
                                    {{ pack.bonus_percent > 0 ? `+${pack.bonus_credits} bonus (${pack.bonus_percent}%)` : 'No bonus' }}
                                </p>
                                <p class="mt-3 text-sm font-medium text-promo-ink">
                                    {{ pack.priceLabels[topUpForm.currency] ?? pack.priceLabels.EUR }}
                                </p>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.16em] text-promo-primary">
                                    Total granted: {{ pack.total_credits }}
                                </p>
                            </button>
                        </div>

                        <div v-if="selectedPack" class="mt-5 border-t border-promo-line pt-4">
                            <p class="text-sm font-semibold text-promo-ink">
                                {{ selectedPackPriceLabel ?? selectedPack.priceLabels.EUR }} for {{ selectedPack.credits }} credits
                            </p>
                            <p class="mt-1 text-sm leading-6 text-promo-muted">
                                You will receive {{ selectedPack.total_credits }} total credits in your wallet after payment.
                                <span v-if="selectedPack.bonus_credits > 0">
                                    That includes {{ selectedPack.bonus_credits }} bonus credits.
                                </span>
                            </p>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <Button
                                v-if="topUpUrl && isBusinessUser && businessUser?.isBusinessOnboarded"
                                type="button"
                                class="bg-promo-primary text-white hover:bg-promo-primary-strong"
                                :disabled="topUpForm.processing"
                                @click="topUpForm.post(topUpUrl)"
                            >
                                {{ businessPrimaryCtaLabel }}
                            </Button>
                            <Link
                                v-else-if="activateUrl"
                                :href="activateUrl"
                                method="post"
                                as="button"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                {{ businessPrimaryCtaLabel }}
                            </Link>
                            <Link
                                v-else-if="onboardingUrl"
                                :href="onboardingUrl"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                {{ businessPrimaryCtaLabel }}
                            </Link>
                            <Link
                                v-else
                                href="/login"
                                class="inline-flex items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                            >
                                {{ businessPrimaryCtaLabel }}
                            </Link>
                        </div>
                    </div>
                </div>

                <MarketingSectionHeading
                    :eyebrow="t('marketing.businesses.why.eyebrow')"
                    :title="t('marketing.businesses.why.title')"
                    :description="t('marketing.businesses.why.description')"
                />

                <div class="mt-14 divide-y divide-promo-line border-y border-promo-line">
                    <div
                        v-for="feature in businessFeatures"
                        :key="feature.title"
                        class="grid gap-4 py-5 md:grid-cols-[180px_minmax(0,1fr)] md:items-start"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                <component :is="feature.icon" class="size-4" />
                            </div>
                            <p class="text-sm font-semibold text-promo-ink">
                                {{ feature.title }}
                            </p>
                        </div>
                        <p class="text-sm leading-6 text-promo-muted">
                            {{ feature.description }}
                        </p>
                    </div>
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

                <div class="mt-14 divide-y divide-promo-line border-y border-promo-line bg-white">
                    <article
                        v-for="item in businessSteps"
                        :key="item.step"
                        class="grid gap-5 px-5 py-5 lg:grid-cols-[150px_minmax(0,1fr)_220px] lg:items-start"
                    >
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                {{ item.step }}
                            </p>
                            <p class="mt-2 text-base font-semibold text-promo-ink">
                                {{ item.title }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm leading-6 text-promo-muted">
                                {{ item.description }}
                            </p>
                            <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm text-promo-ink">
                                <li
                                    v-for="highlight in item.highlights"
                                    :key="highlight"
                                    class="font-medium"
                                >
                                    {{ highlight }}
                                </li>
                            </ul>
                        </div>

                        <img
                            :src="item.image"
                            :alt="item.imageAlt"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    </article>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
