<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BriefcaseBusiness,
    MonitorPlay,
    Repeat2,
    ShieldCheck,
    Sparkles,
    Users,
} from 'lucide-vue-next';
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
    businessRegisterUrl: string | null;
    loginUrl: string | null;
    onboardingUrl: string | null;
    topUpUrl: string | null;
    dashboardUrl: string | null;
    separateAccountNotice: string | null;
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
const isBusinessUser =
    businessUser?.accountType === 'business' ||
    businessUser?.accountType === 'super_admin';
const selectedPack = computed(
    () =>
        props.businessPacks.find(
            (pack) => pack.credits === topUpForm.credits,
        ) ??
        props.businessPacks[0] ??
        null,
);
const selectedPackPriceLabel = computed(() => {
    if (!selectedPack.value) {
        return null;
    }

    return (
        selectedPack.value.priceLabels[topUpForm.currency] ??
        selectedPack.value.priceLabels.EUR ??
        null
    );
});
const businessPrimaryCtaLabel = computed(() => {
    if (props.topUpUrl && isBusinessUser && businessUser?.isBusinessOnboarded) {
        return t('marketing.businesses.simple.billing.top_up_selected', {
            credits: topUpForm.credits,
        });
    }

    if (props.businessRegisterUrl) {
        return t('marketing.businesses.simple.actions.create_business_account');
    }

    if (props.onboardingUrl) {
        return t('marketing.businesses.simple.actions.finish_profile_first');
    }

    if (props.loginUrl) {
        return t('marketing.businesses.simple.actions.sign_in_to_use_business');
    }

    return t('marketing.businesses.simple.actions.sign_in_to_top_up');
});

const plusPlan = computed(
    () => props.businessPlans.find((plan) => plan.slug === 'plus') ?? null,
);
const proPlan = computed(
    () => props.businessPlans.find((plan) => plan.slug === 'pro') ?? null,
);
const hundredCreditPack = computed(
    () =>
        props.businessPacks.find((pack) => pack.credits === 100) ??
        props.businessPacks[0] ??
        null,
);

const businessFlow = [
    {
        icon: BriefcaseBusiness,
        title: t('marketing.businesses.simple.flow.1.title'),
        description: t('marketing.businesses.simple.flow.1.description'),
        label: 'Dashboard placeholder',
        visualTitle: 'Add screenshot: business dashboard home',
        visualDescription:
            'Show the business dashboard with events, billing, and credits visible at a glance.',
    },
    {
        icon: Repeat2,
        title: t('marketing.businesses.simple.flow.2.title'),
        description: t('marketing.businesses.simple.flow.2.description'),
        label: 'Billing screenshot placeholder',
        visualTitle: 'Add screenshot: billing page with history',
        visualDescription:
            'Show credit balance, top-up history, and one event debit so the model is obvious instantly.',
    },
    {
        icon: ShieldCheck,
        title: t('marketing.businesses.simple.flow.3.title'),
        description: t('marketing.businesses.simple.flow.3.description'),
        label: 'Create-event placeholder',
        visualTitle: 'Add screenshot: business event creation',
        visualDescription:
            'Show the business create-event step where the plan cost and remaining credits are visible.',
    },
];

const creditExamples = computed(() => [
    {
        title: t('marketing.businesses.simple.examples.1.title'),
        description: plusPlan.value
            ? t(
                  'marketing.businesses.simple.examples.1.description_with_plan',
                  {
                      cost: plusPlan.value.businessCreditCost,
                      plan: plusPlan.value.name,
                      count: Math.floor(
                          100 / plusPlan.value.businessCreditCost,
                      ),
                  },
              )
            : t('marketing.businesses.simple.examples.1.description'),
    },
    {
        title: t('marketing.businesses.simple.examples.2.title'),
        description: hundredCreditPack.value
            ? t(
                  'marketing.businesses.simple.examples.2.description_with_bonus',
                  {
                      total: hundredCreditPack.value.total_credits,
                  },
              )
            : t('marketing.businesses.simple.examples.2.description'),
    },
    {
        title: t('marketing.businesses.simple.examples.3.title'),
        description: proPlan.value
            ? t(
                  'marketing.businesses.simple.examples.3.description_with_plan',
                  {
                      plan: proPlan.value.name,
                      cost: proPlan.value.businessCreditCost,
                  },
              )
            : t('marketing.businesses.simple.examples.3.description'),
    },
]);
</script>

<template>
    <MarketingLayout
        :title="t('marketing.businesses.meta.title')"
        :description="t('marketing.businesses.meta.description')"
        :can-register="canRegister"
    >
        <section
            class="mx-auto grid max-w-7xl gap-12 px-4 pt-12 pb-18 sm:px-6 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:pt-16 lg:pb-24"
        >
            <div class="max-w-2xl">
                <p class="marketing-kicker inline-flex items-center gap-2">
                    <Sparkles class="size-4" />
                    {{ t('marketing.businesses.simple.hero.kicker') }}
                </p>
                <h1
                    class="marketing-display mt-6 text-[3.1rem] sm:text-[3.9rem] lg:text-[4.5rem]"
                >
                    {{ t('marketing.businesses.simple.hero.title') }}
                </h1>
                <p class="marketing-copy mt-6 max-w-xl">
                    {{ t('marketing.businesses.simple.hero.description') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="dashboardUrl"
                        :href="dashboardUrl"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{
                            t(
                                'marketing.businesses.simple.actions.open_dashboard',
                            )
                        }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        v-else-if="businessRegisterUrl"
                        :href="businessRegisterUrl"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{
                            t(
                                'marketing.businesses.simple.actions.create_business_account',
                            )
                        }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        v-else-if="onboardingUrl"
                        :href="onboardingUrl"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{
                            t(
                                'marketing.businesses.simple.actions.finish_business_profile',
                            )
                        }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        v-else
                        :href="loginUrl ?? '/login'"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{
                            t(
                                'marketing.businesses.simple.actions.sign_in_to_use_business',
                            )
                        }}
                        <ArrowRight class="size-4" />
                    </Link>

                    <Link
                        :href="pricing()"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                    >
                        {{
                            t(
                                'marketing.businesses.simple.actions.view_consumer_pricing',
                            )
                        }}
                    </Link>
                </div>

                <div
                    v-if="separateAccountNotice"
                    class="mt-6 rounded-[24px] border border-black/6 bg-white px-5 py-4 text-sm leading-6 text-promo-muted shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]"
                >
                    <p class="font-semibold text-promo-ink">
                        {{
                            t(
                                'marketing.businesses.simple.actions.separate_account_title',
                            )
                        }}
                    </p>
                    <p class="mt-1.5">
                        {{ separateAccountNotice }}
                    </p>
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
                :eyebrow="
                    t('marketing.businesses.simple.sections.flow.eyebrow')
                "
                :title="t('marketing.businesses.simple.sections.flow.title')"
                :description="
                    t('marketing.businesses.simple.sections.flow.description')
                "
            />

            <div
                class="mt-14 divide-y divide-promo-line border-y border-promo-line"
            >
                <article
                    v-for="item in businessFlow"
                    :key="item.title"
                    class="grid gap-8 py-8 lg:grid-cols-[0.76fr_1.24fr] lg:items-center"
                >
                    <div class="max-w-md">
                        <div class="flex items-center gap-3 text-promo-ink">
                            <component
                                :is="item.icon"
                                class="size-5 text-promo-primary"
                            />
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
                    :eyebrow="
                        t(
                            'marketing.businesses.simple.sections.examples.eyebrow',
                        )
                    "
                    :title="
                        t('marketing.businesses.simple.sections.examples.title')
                    "
                    :description="
                        t(
                            'marketing.businesses.simple.sections.examples.description',
                        )
                    "
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
                :eyebrow="
                    t('marketing.businesses.simple.sections.experience.eyebrow')
                "
                :title="
                    t('marketing.businesses.simple.sections.experience.title')
                "
                :description="
                    t(
                        'marketing.businesses.simple.sections.experience.description',
                    )
                "
            />

            <div class="mt-14 grid gap-8 lg:grid-cols-2">
                <article class="space-y-4">
                    <div class="flex items-center gap-3 text-promo-ink">
                        <Users class="size-5 text-promo-primary" />
                        <h3 class="text-lg font-semibold">
                            {{
                                t(
                                    'marketing.businesses.simple.experience.album_title',
                                )
                            }}
                        </h3>
                    </div>
                    <p class="marketing-copy">
                        {{
                            t(
                                'marketing.businesses.simple.experience.album_description',
                            )
                        }}
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
                        <h3 class="text-lg font-semibold">
                            {{
                                t(
                                    'marketing.businesses.simple.experience.wall_title',
                                )
                            }}
                        </h3>
                    </div>
                    <p class="marketing-copy">
                        {{
                            t(
                                'marketing.businesses.simple.experience.wall_description',
                            )
                        }}
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
                    :eyebrow="
                        t(
                            'marketing.businesses.simple.sections.billing.eyebrow',
                        )
                    "
                    :title="
                        t('marketing.businesses.simple.sections.billing.title')
                    "
                    :description="
                        t(
                            'marketing.businesses.simple.sections.billing.description',
                        )
                    "
                />

                <div
                    class="mt-14 grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-start"
                >
                    <div class="max-w-lg">
                        <p class="marketing-copy">
                            {{ t('marketing.businesses.simple.billing.intro') }}
                        </p>

                        <div
                            class="mt-8 divide-y divide-promo-line border-y border-promo-line"
                        >
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
                                        {{
                                            t(
                                                'marketing.businesses.simple.billing.consumer_price',
                                                {
                                                    price: plan.consumerPriceLabel,
                                                },
                                            )
                                        }}
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
                                {{
                                    isBusinessUser
                                        ? t(
                                              'marketing.businesses.simple.billing.current_balance',
                                              {
                                                  credits:
                                                      businessUser?.businessWalletCredits ??
                                                      0,
                                              },
                                          )
                                        : (separateAccountNotice ??
                                          t(
                                              'marketing.businesses.simple.actions.sign_in_to_use_business',
                                          ))
                                }}
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
                                {{
                                    t(
                                        'marketing.businesses.simple.billing.pack_credits',
                                        { credits: pack.credits },
                                    )
                                }}
                            </button>
                        </div>

                        <div
                            v-if="selectedPack"
                            class="mt-6 border-t border-promo-line pt-4"
                        >
                            <p class="text-sm font-semibold text-promo-ink">
                                {{
                                    selectedPackPriceLabel ??
                                    selectedPack.priceLabels.EUR
                                }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                {{
                                    t(
                                        'marketing.businesses.simple.billing.receive_total',
                                        { total: selectedPack.total_credits },
                                    )
                                }}
                                <span v-if="selectedPack.bonus_credits > 0">
                                    {{
                                        t(
                                            'marketing.businesses.simple.billing.receive_bonus',
                                            {
                                                bonus: selectedPack.bonus_credits,
                                            },
                                        )
                                    }}
                                </span>
                            </p>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Button
                                v-if="
                                    topUpUrl &&
                                    isBusinessUser &&
                                    businessUser?.isBusinessOnboarded
                                "
                                type="button"
                                class="bg-promo-primary text-white hover:bg-promo-primary-strong"
                                :disabled="topUpForm.processing"
                                @click="topUpForm.post(topUpUrl)"
                            >
                                {{ businessPrimaryCtaLabel }}
                            </Button>
                            <Link
                                v-else-if="businessRegisterUrl"
                                :href="businessRegisterUrl"
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
                                v-else-if="loginUrl"
                                :href="loginUrl"
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
