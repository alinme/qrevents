<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    badgeClass,
    formatCreditDelta,
    formatDateTime,
    walletActivityKindLabel,
    walletActivityTone,
} from '@/lib/dashboard';
import type {
    BreadcrumbItem,
    BusinessWalletActivity,
    BusinessWalletSummary,
    DashboardLinks,
    PaginationMeta,
} from '@/types';

const props = defineProps<{
    dashboardLinks: DashboardLinks;
    businessActionLinks: {
        walletHistory: string;
    };
    businessTopUp: {
        submitUrl: string;
        defaultCredits: number;
        defaultCurrency: string;
        supportedCheckoutCurrencies: string[];
        packs: Array<{
            credits: number;
            bonus_percent: number;
            bonus_credits: number;
            total_credits: number;
            priceLabels: Record<string, string>;
        }>;
    };
    walletSummary: BusinessWalletSummary;
    walletTransactions: BusinessWalletActivity[];
    walletTransactionsPagination: PaginationMeta;
}>();

const { locale, t } = useTranslations();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('app.nav.dashboard'),
        href: props.dashboardLinks.overview,
    },
    {
        title: t('app.nav.business'),
        href: props.dashboardLinks.business ?? props.dashboardLinks.overview,
    },
    {
        title: t('app.nav.billing'),
        href: props.businessActionLinks.walletHistory,
    },
]);

const formatNumber = (value: number): string =>
    new Intl.NumberFormat(
        locale.value === 'ro'
            ? 'ro-RO'
            : locale.value === 'el'
              ? 'el-GR'
              : 'en-GB',
    ).format(value);

const formatLocalizedDateTime = (value: string | null): string =>
    formatDateTime(value, {
        locale: locale.value,
        emptyLabel: t('dashboard.business.wallet.no_activity'),
    });

const creditLabel = computed(() => t('dashboard.business.wallet.credits_unit'));

const compactMetrics = computed(() => [
    {
        key: 'balance',
        label: t('dashboard.business.wallet.metrics.balance.label'),
        value: t('dashboard.business.wallet.metrics.balance.value', {
            count: formatNumber(props.walletSummary.currentBalance),
        }),
        detail: t('dashboard.business.wallet.metrics.balance.detail', {
            currency: props.walletSummary.currency,
        }),
    },
    {
        key: 'added',
        label: t('dashboard.business.wallet.metrics.added.label'),
        value: t('dashboard.business.wallet.metrics.added.value', {
            count: formatNumber(props.walletSummary.creditsAdded),
        }),
        detail: t('dashboard.business.wallet.metrics.added.detail'),
    },
    {
        key: 'used',
        label: t('dashboard.business.wallet.metrics.used.label'),
        value: t('dashboard.business.wallet.metrics.used.value', {
            count: formatNumber(props.walletSummary.creditsUsed),
        }),
        detail: t('dashboard.business.wallet.metrics.used.detail'),
    },
    {
        key: 'entries',
        label: t('dashboard.business.wallet.metrics.entries.label'),
        value: formatNumber(props.walletSummary.totalTransactions),
        detail: t('dashboard.business.wallet.metrics.entries.detail'),
    },
]);

const topUpForm = useForm({
    credits: props.businessTopUp.defaultCredits,
    currency: props.businessTopUp.defaultCurrency,
});
const topUpModalOpen = ref(false);
const howItWorksModalOpen = ref(false);

const selectedPack = computed(
    () =>
        props.businessTopUp.packs.find(
            (pack) => pack.credits === topUpForm.credits,
        ) ?? props.businessTopUp.packs[0] ?? null,
);

const selectedPriceLabel = computed(() => {
    if (!selectedPack.value) {
        return null;
    }

    return (
        selectedPack.value.priceLabels[topUpForm.currency] ??
        selectedPack.value.priceLabels.EUR ??
        null
    );
});

const submitTopUp = (): void => {
    topUpForm.post(props.businessTopUp.submitUrl, {
        preserveScroll: true,
        onSuccess: () => {
            topUpModalOpen.value = false;
        },
    });
};
</script>

<template>
    <Head :title="t('dashboard.business.wallet.page_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-7xl">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-4 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="dashboard-eyebrow">
                                {{ t('dashboard.business.wallet.hero.kicker') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('dashboard.business.wallet.hero.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{ t('dashboard.business.wallet.hero.description') }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-5">
                        <dl class="grid gap-x-5 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="metric in compactMetrics"
                                :key="metric.key"
                                class="dashboard-divider-left"
                            >
                                <dt class="dashboard-eyebrow">
                                    {{ metric.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-brand-ink">
                                    {{ metric.value }}
                                </dd>
                                <p class="dashboard-meta mt-1">
                                    {{ metric.detail }}
                                </p>
                                <div
                                    v-if="metric.key === 'balance'"
                                    class="mt-3 flex flex-col items-start gap-2 pt-0.5"
                                >
                                    <Button
                                        type="button"
                                        variant="link"
                                        class="h-auto px-0 text-sm font-medium text-brand-ink"
                                        @click="topUpModalOpen = true"
                                    >
                                        {{ t('dashboard.business.wallet.actions.top_up') }}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="link"
                                        class="h-auto px-0 text-sm font-medium text-brand-muted"
                                        @click="howItWorksModalOpen = true"
                                    >
                                        {{ t('dashboard.business.wallet.actions.how_it_works') }}
                                    </Button>
                                </div>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-2 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="dashboard-section-title">
                                {{ t('dashboard.business.wallet.section.title') }}
                            </h2>
                            <p class="dashboard-body mt-1">
                                {{ t('dashboard.business.wallet.section.description', {
                                    count: formatNumber(walletTransactionsPagination.total),
                                }) }}
                            </p>
                        </div>
                        <p class="text-sm text-brand-muted">
                            {{ t('dashboard.business.wallet.balance_now', {
                                count: formatNumber(walletSummary.currentBalance),
                            }) }}
                        </p>
                    </div>

                    <div v-if="walletTransactions.length === 0" class="py-12 text-center">
                        <div class="mx-auto max-w-md space-y-2">
                            <h3 class="text-lg font-semibold text-brand-ink">
                                {{ t('dashboard.business.wallet.empty.title') }}
                            </h3>
                            <p class="dashboard-body">
                                {{ t('dashboard.business.wallet.empty.description') }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="max-h-[34rem] overflow-y-auto overscroll-contain pr-1">
                        <div class="divide-y divide-black/5">
                            <article
                                v-for="item in walletTransactions"
                                :key="item.id"
                                class="grid gap-3 py-4 sm:grid-cols-[140px_minmax(0,1fr)_140px] sm:items-start"
                            >
                                <div class="min-w-0 sm:pr-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                        :class="badgeClass(walletActivityTone(item.kind))"
                                    >
                                        {{ walletActivityKindLabel(item.kind, { t }) }}
                                    </span>
                                    <p class="dashboard-meta mt-2">
                                        {{ formatLocalizedDateTime(item.createdAt) }}
                                    </p>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-brand-ink">
                                        {{ item.description }}
                                    </p>
                                    <p class="mt-1 text-sm text-brand-muted">
                                        <template v-if="item.eventName">
                                            {{ t('dashboard.business.wallet.event_prefix') }}
                                            <Link
                                                v-if="item.eventUrl"
                                                :href="item.eventUrl"
                                                class="font-medium text-brand-ink hover:text-brand-accent"
                                            >
                                                {{ item.eventName }}
                                            </Link>
                                            <span v-else class="font-medium text-brand-ink">{{ item.eventName }}</span>
                                        </template>
                                        <template v-else>
                                            {{ t('dashboard.business.wallet.generic_activity') }}
                                        </template>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-3 sm:block sm:text-right">
                                    <p
                                        class="text-sm font-semibold"
                                        :class="item.credits >= 0 ? 'text-emerald-700' : 'text-amber-700'"
                                    >
                                        {{ formatCreditDelta(item.credits, { creditsLabel: creditLabel }) }}
                                    </p>
                                    <p
                                        v-if="item.moneyLabel"
                                        class="mt-1 text-sm text-brand-muted"
                                    >
                                        {{ t('dashboard.business.wallet.paid_label', { amount: item.moneyLabel }) }}
                                    </p>
                                    <p class="dashboard-meta mt-1">
                                        {{
                                            item.credits >= 0
                                                ? t('dashboard.business.wallet.entry.added')
                                                : t('dashboard.business.wallet.entry.used')
                                        }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div
                        v-if="walletTransactionsPagination.lastPage > 1"
                        class="mt-4 flex flex-col gap-3 border-t border-brand-border/70 pt-4 md:flex-row md:items-center md:justify-between"
                    >
                        <p class="text-sm text-brand-muted">
                            {{ t('dashboard.business.pagination.showing', {
                                from: formatNumber(walletTransactionsPagination.from ?? 0),
                                to: formatNumber(walletTransactionsPagination.to ?? 0),
                                total: formatNumber(walletTransactionsPagination.total),
                                item: t('dashboard.business.wallet.transactions_label'),
                            }) }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button v-if="walletTransactionsPagination.prevPageUrl" as-child variant="outline">
                                <Link :href="walletTransactionsPagination.prevPageUrl">
                                    {{ t('dashboard.business.pagination.previous') }}
                                </Link>
                            </Button>
                            <Button v-else variant="outline" disabled>
                                {{ t('dashboard.business.pagination.previous') }}
                            </Button>
                            <span class="text-sm font-medium text-brand-muted">
                                {{ t('dashboard.business.pagination.page', {
                                    current: formatNumber(walletTransactionsPagination.currentPage),
                                    total: formatNumber(walletTransactionsPagination.lastPage),
                                }) }}
                            </span>
                            <Button v-if="walletTransactionsPagination.nextPageUrl" as-child variant="outline">
                                <Link :href="walletTransactionsPagination.nextPageUrl">
                                    {{ t('dashboard.business.pagination.next') }}
                                </Link>
                            </Button>
                            <Button v-else variant="outline" disabled>
                                {{ t('dashboard.business.pagination.next') }}
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="topUpModalOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader class="text-left">
                <DialogTitle>{{ t('dashboard.business.wallet.top_up.title') }}</DialogTitle>
                <DialogDescription>
                    {{ t('dashboard.business.wallet.top_up.description') }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-5" @submit.prevent="submitTopUp">
                <div
                    role="radiogroup"
                    :aria-label="t('dashboard.business.wallet.top_up.choose_pack')"
                    class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                >
                    <button
                        v-for="pack in businessTopUp.packs"
                        :key="pack.credits"
                        type="button"
                        role="radio"
                        :aria-checked="pack.credits === topUpForm.credits"
                        :aria-label="
                            pack.bonus_credits > 0
                                ? t('dashboard.business.wallet.top_up.pack_aria_bonus', {
                                    credits: formatNumber(pack.credits),
                                    bonus: formatNumber(pack.bonus_credits),
                                })
                                : t('dashboard.business.wallet.top_up.pack_aria', {
                                    credits: formatNumber(pack.credits),
                                })
                        "
                        class="rounded-[1.15rem] border px-3 py-3 text-left transition"
                        :class="
                            pack.credits === topUpForm.credits
                                ? 'border-brand-ink bg-brand-ink text-brand-inverse'
                                : 'border-brand-border bg-brand-panel text-brand-ink hover:border-brand-accent/30 hover:bg-brand-highlight/20'
                        "
                        @click="topUpForm.credits = pack.credits"
                    >
                        <p class="text-sm font-semibold">
                            {{ t('dashboard.business.wallet.top_up.pack_credits', { count: formatNumber(pack.credits) }) }}
                        </p>
                        <p
                            class="mt-1 text-xs"
                            :class="
                                pack.credits === topUpForm.credits
                                    ? 'text-white/75'
                                    : 'text-brand-muted'
                            "
                        >
                            {{
                                pack.bonus_credits > 0
                                    ? t('dashboard.business.wallet.top_up.pack_bonus', {
                                        count: formatNumber(pack.bonus_credits),
                                    })
                                    : t('dashboard.business.wallet.top_up.pack_no_bonus')
                            }}
                        </p>
                    </button>
                </div>

                <div class="space-y-3 border-t border-brand-border/70 pt-4">
                    <div>
                        <p class="dashboard-eyebrow">{{ t('dashboard.business.wallet.top_up.receive_label') }}</p>
                        <p class="mt-1 text-base font-semibold text-brand-ink">
                            {{ t('dashboard.business.wallet.top_up.receive_value', { count: formatNumber(selectedPack?.total_credits ?? 0) }) }}
                        </p>
                        <p class="dashboard-meta mt-1">
                            {{ selectedPriceLabel ?? t('dashboard.business.wallet.top_up.price_unavailable') }}
                        </p>
                    </div>

                    <label class="block text-sm text-brand-muted">
                        <span class="dashboard-eyebrow">{{ t('dashboard.business.wallet.top_up.currency') }}</span>
                        <select
                            v-model="topUpForm.currency"
                            class="mt-2 block h-11 w-full rounded-xl border border-brand-border bg-brand-inverse px-3 text-sm font-medium text-brand-ink focus:border-brand-accent focus:outline-none"
                        >
                            <option
                                v-for="currency in businessTopUp.supportedCheckoutCurrencies"
                                :key="currency"
                                :value="currency"
                            >
                                {{ currency }}
                            </option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <Button type="button" variant="outline" @click="topUpModalOpen = false">
                        {{ t('public.shared.back') }}
                    </Button>
                    <Button
                        type="submit"
                        class="bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                        :disabled="topUpForm.processing"
                    >
                        <Plus class="size-4" />
                        {{
                            topUpForm.processing
                                ? t('dashboard.business.wallet.top_up.processing')
                                : t('dashboard.business.wallet.top_up.submit', {
                                    count: formatNumber(topUpForm.credits),
                                })
                        }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="howItWorksModalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="text-left">
                <DialogTitle>{{ t('dashboard.business.wallet.help.title') }}</DialogTitle>
                <DialogDescription>
                    {{ t('dashboard.business.wallet.help.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-3 text-sm leading-6 text-brand-muted">
                <div class="border-t border-brand-border/70 pt-3 first:border-0 first:pt-0">
                    <p class="font-semibold text-brand-ink">{{ t('dashboard.business.wallet.help.top_up_once.title') }}</p>
                    <p class="mt-1">
                        {{ t('dashboard.business.wallet.help.top_up_once.body') }}
                    </p>
                </div>

                <div class="border-t border-brand-border/70 pt-3">
                    <p class="font-semibold text-brand-ink">{{ t('dashboard.business.wallet.help.bonus_balance.title') }}</p>
                    <p class="mt-1">
                        {{ t('dashboard.business.wallet.help.bonus_balance.body') }}
                    </p>
                </div>

                <div class="border-t border-brand-border/70 pt-3">
                    <p class="font-semibold text-brand-ink">{{ t('dashboard.business.wallet.help.event_spend.title') }}</p>
                    <p class="mt-1">
                        {{ t('dashboard.business.wallet.help.event_spend.body') }}
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
