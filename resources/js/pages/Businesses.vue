<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, MonitorPlay, Repeat2, ShieldCheck, Sparkles, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingVisualPlaceholder from '@/components/marketing/MarketingVisualPlaceholder.vue';
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
    if (props.topUpUrl && isBusinessUser && businessUser?.isBusinessOnboarded) {
        return `Top up ${topUpForm.credits} credits`;
    }

    if (props.activateUrl) {
        return 'Switch to business first';
    }

    if (props.onboardingUrl) {
        return 'Finish profile first';
    }

    return 'Sign in to top up';
});

const plusPlan = computed(() => props.businessPlans.find((plan) => plan.slug === 'plus') ?? null);
const proPlan = computed(() => props.businessPlans.find((plan) => plan.slug === 'pro') ?? null);
const hundredCreditPack = computed(() => props.businessPacks.find((pack) => pack.credits === 100) ?? props.businessPacks[0] ?? null);

const businessFlow = [
    {
        icon: BriefcaseBusiness,
        title: 'Switch into business mode once',
        description: 'The account becomes multi-event, keeps the same paid event model, and unlocks billing credits.',
        label: 'Dashboard placeholder',
        visualTitle: 'Add screenshot: business dashboard home',
        visualDescription: 'Show the business dashboard with events, billing, and credits visible at a glance.',
    },
    {
        icon: Repeat2,
        title: 'Top up billing credits',
        description: 'Teams preload credits, then spend them per event instead of paying a consumer checkout every time.',
        label: 'Billing screenshot placeholder',
        visualTitle: 'Add screenshot: billing page with history',
        visualDescription: 'Show credit balance, top-up history, and one event debit so the model is obvious instantly.',
    },
    {
        icon: ShieldCheck,
        title: 'Create paid events for clients',
        description: 'Each new Plus or Pro event consumes credits, which keeps usage aligned with the real event cost.',
        label: 'Create-event placeholder',
        visualTitle: 'Add screenshot: business event creation',
        visualDescription: 'Show the business create-event step where the plan cost and remaining credits are visible.',
    },
];

const creditExamples = computed(() => [
    {
        title: '100 credits for repeat planners',
        description: plusPlan.value
            ? `At ${plusPlan.value.businessCreditCost} credits per ${plusPlan.value.name} event, 100 credits covers about ${Math.floor(100 / plusPlan.value.businessCreditCost)} events before bonuses.`
            : 'Use this space to show what a 100-credit pack covers for your most common event type.',
    },
    {
        title: 'Bonus credits make the math easier',
        description: hundredCreditPack.value
            ? `A 100-credit top-up currently grants ${hundredCreditPack.value.total_credits} total credits with the launch bonus.`
            : 'Use this space to explain the current launch bonus and how it changes the effective event count.',
    },
    {
        title: 'Pro stays clear too',
        description: proPlan.value
            ? `${proPlan.value.name} costs ${proPlan.value.businessCreditCost} credits per event, so teams can forecast spend without another pricing model.`
            : 'Use this space to explain the Pro credit cost with one simple example.',
    },
]);
</script>

<template>
    <MarketingLayout
        :title="t('marketing.businesses.meta.title')"
        :description="t('marketing.businesses.meta.description')"
        :can-register="canRegister"
    >
        <section class="mx-auto grid max-w-7xl gap-12 px-4 pb-18 pt-12 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:pb-24 lg:pt-16">
            <div class="max-w-2xl">
                <p class="marketing-kicker inline-flex items-center gap-2">
                    <Sparkles class="size-4" />
                    Business
                </p>
                <h1 class="marketing-display mt-6 text-[3.1rem] sm:text-[3.9rem] lg:text-[4.5rem]">
                    Explain business billing with examples, not a wall of text.
                </h1>
                <p class="marketing-copy mt-6 max-w-xl">
                    Business should feel simple: top up credits, create events, and keep the same event-based cost model as everyone else.
                    Show the dashboard, the billing page, and the event flow clearly.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
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
                        View consumer pricing
                    </Link>
                </div>
            </div>

            <MarketingVisualPlaceholder
                label="Hero video placeholder"
                title="Add 12s business overview video"
                description="Show one team topping up credits, creating a client event, then opening the album and wall so the business model feels concrete."
                aspect-class="aspect-[5/4]"
            />
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="How business works"
                title="Three things teams need to understand immediately"
                description="No nested promo cards. Just the model, the screenshots, and the action."
            />

            <div class="mt-14 divide-y divide-promo-line border-y border-promo-line">
                <article
                    v-for="item in businessFlow"
                    :key="item.title"
                    class="grid gap-8 py-8 lg:grid-cols-[0.76fr_1.24fr] lg:items-center"
                >
                    <div class="max-w-md">
                        <div class="flex items-center gap-3 text-promo-ink">
                            <component :is="item.icon" class="size-5 text-promo-primary" />
                            <h3 class="text-lg font-semibold">
                                {{ item.title }}
                            </h3>
                        </div>
                        <p class="marketing-copy mt-4">
                            {{ item.description }}
                        </p>
                    </div>

                    <MarketingVisualPlaceholder
                        :label="item.label"
                        :title="item.visualTitle"
                        :description="item.visualDescription"
                    />
                </article>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Examples"
                    title="Do the credit math in public"
                    description="Give teams one or two concrete scenarios so they understand the wallet model without reading a policy page."
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <article
                        v-for="item in creditExamples"
                        :key="item.title"
                        class="border border-promo-line bg-white px-6 py-6"
                    >
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ item.title }}
                        </h3>
                        <p class="marketing-copy mt-3">
                            {{ item.description }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="Client and guest side"
                title="Show the final experience too"
                description="Business buyers still need to see what guests and clients will actually open."
            />

            <div class="mt-14 grid gap-8 lg:grid-cols-2">
                <article class="space-y-4">
                    <div class="flex items-center gap-3 text-promo-ink">
                        <Users class="size-5 text-promo-primary" />
                        <h3 class="text-lg font-semibold">Album access for guests</h3>
                    </div>
                    <p class="marketing-copy">
                        Show the 4-character album code screen and the mobile upload page so teams know guests can still join quickly.
                    </p>
                    <MarketingVisualPlaceholder
                        label="Mobile screenshot placeholder"
                        title="Add screenshot: album code + upload"
                        description="Use one or two real mobile screens showing the album code entry and the guest upload interface."
                        aspect-class="aspect-[4/3]"
                    />
                </article>

                <article class="space-y-4">
                    <div class="flex items-center gap-3 text-promo-ink">
                        <MonitorPlay class="size-5 text-promo-primary" />
                        <h3 class="text-lg font-semibold">Wall access for venues and TVs</h3>
                    </div>
                    <p class="marketing-copy">
                        Show the shorter wall route and the live wall itself so the venue setup looks easy and fast.
                    </p>
                    <MarketingVisualPlaceholder
                        label="TV screenshot placeholder"
                        title="Add screenshot: short wall link + live wall"
                        description="Use a real wall screen example with the shorter wall URL visible somewhere in the composition."
                        aspect-class="aspect-[4/3]"
                    />
                </article>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Billing"
                    title="Keep the top-up area simple too"
                    description="Choose a pack, show the current payout clearly, and let the user move forward."
                />

                <div class="mt-14 grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-start">
                    <div class="max-w-lg">
                        <p class="marketing-copy">
                            One credit equals one euro. Business events use credits against Plus and Pro, so the billing page should feel predictable, not special-case or unlimited.
                        </p>

                        <div class="mt-8 divide-y divide-promo-line border-y border-promo-line">
                            <div
                                v-for="plan in businessPlans"
                                :key="plan.slug"
                                class="flex items-start justify-between gap-4 py-4 text-sm"
                            >
                                <div>
                                    <p class="font-semibold text-promo-ink">
                                        {{ plan.name }}
                                    </p>
                                    <p class="mt-1 text-promo-muted">
                                        Consumer price: {{ plan.consumerPriceLabel }}
                                    </p>
                                </div>
                                <p class="font-semibold text-promo-primary">
                                    {{ plan.businessCreditCost }} credits
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <select
                                v-model="topUpForm.currency"
                                class="rounded-full border border-promo-line bg-white px-4 py-3 text-sm text-promo-ink"
                            >
                                <option
                                    v-for="currency in supportedCheckoutCurrencies"
                                    :key="currency"
                                    :value="currency"
                                >
                                    {{ currency }}
                                </option>
                            </select>
                            <p class="text-sm text-promo-muted">
                                {{ isBusinessUser ? `Current balance: ${businessUser?.businessWalletCredits ?? 0} credits` : 'Switch to business mode first, then top up.' }}
                            </p>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <button
                                v-for="pack in businessPacks"
                                :key="pack.credits"
                                type="button"
                                class="rounded-full border px-4 py-3 text-sm font-semibold transition"
                                :class="
                                    topUpForm.credits === pack.credits
                                        ? 'border-promo-primary bg-promo-primary text-white'
                                        : 'border-promo-line bg-white text-promo-ink hover:bg-promo-surface'
                                "
                                @click="topUpForm.credits = pack.credits"
                            >
                                {{ pack.credits }} credits
                            </button>
                        </div>

                        <div v-if="selectedPack" class="mt-6 border-t border-promo-line pt-4">
                            <p class="text-sm font-semibold text-promo-ink">
                                {{ selectedPackPriceLabel ?? selectedPack.priceLabels.EUR }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                You receive {{ selectedPack.total_credits }} total credits.
                                <span v-if="selectedPack.bonus_credits > 0">
                                    That includes {{ selectedPack.bonus_credits }} bonus credits.
                                </span>
                            </p>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
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
            </div>
        </section>
    </MarketingLayout>
</template>
