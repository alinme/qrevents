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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: props.dashboardLinks.overview,
    },
    {
        title: 'Business',
        href: props.dashboardLinks.business ?? props.dashboardLinks.overview,
    },
    {
        title: 'Billing',
        href: props.businessActionLinks.walletHistory,
    },
];

const compactMetrics = [
    {
        label: 'Balance',
        value: `${props.walletSummary.currentBalance} credits`,
        detail: `Stored in ${props.walletSummary.currency}.`,
    },
    {
        label: 'Added',
        value: `${props.walletSummary.creditsAdded} credits`,
        detail: 'Top-ups and bonus credits.',
    },
    {
        label: 'Used',
        value: `${props.walletSummary.creditsUsed} credits`,
        detail: 'Spent on new events.',
    },
    {
        label: 'Entries',
        value: `${props.walletSummary.totalTransactions}`,
        detail: 'Total ledger movements.',
    },
];

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
    <Head title="Billing" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-7xl">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-4 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="dashboard-eyebrow">
                                Business billing
                            </p>
                            <h1 class="dashboard-title mt-2">
                                Billing
                            </h1>
                            <p class="dashboard-body mt-2">
                                Top-ups, bonus credits, and event spend in one billing ledger.
                            </p>
                        </div>
                    </div>

                    <div class="pt-5">
                        <dl class="grid gap-x-5 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="metric in compactMetrics"
                                :key="metric.label"
                                class="dashboard-divider-left"
                            >
                                <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-brand-muted">
                                    {{ metric.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-brand-ink">
                                    {{ metric.value }}
                                </dd>
                                <p class="dashboard-meta mt-1">
                                    {{ metric.detail }}
                                </p>
                                <div
                                    v-if="metric.label === 'Balance'"
                                    class="mt-3 flex flex-col items-start gap-1.5"
                                >
                                    <Button
                                        type="button"
                                        variant="link"
                                        class="h-auto px-0 text-sm font-medium text-brand-ink"
                                        @click="topUpModalOpen = true"
                                    >
                                        Top up credits
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="link"
                                        class="h-auto px-0 text-sm font-medium text-brand-muted"
                                        @click="howItWorksModalOpen = true"
                                    >
                                        How it works
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
                                All activity
                            </h2>
                            <p class="dashboard-body mt-1">
                                {{ walletTransactionsPagination.total }} billing entries recorded.
                            </p>
                        </div>
                        <p class="text-sm text-brand-muted">
                            Balance now {{ walletSummary.currentBalance }} credits
                        </p>
                    </div>

                    <div v-if="walletTransactions.length === 0" class="py-12 text-center">
                        <div class="mx-auto max-w-md space-y-2">
                            <h3 class="text-lg font-semibold text-brand-ink">
                                No billing activity yet
                            </h3>
                            <p class="dashboard-body">
                                Your first top-up will appear here, followed by bonus credits and event charges.
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
                                        class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                        :class="badgeClass(walletActivityTone(item.kind))"
                                    >
                                        {{ walletActivityKindLabel(item.kind) }}
                                    </span>
                                    <p class="dashboard-meta mt-2">
                                        {{ formatDateTime(item.createdAt) }}
                                    </p>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-brand-ink">
                                        {{ item.description }}
                                    </p>
                                    <p class="mt-1 text-sm text-brand-muted">
                                        <template v-if="item.eventName">
                                            Event
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
                                            Business billing activity
                                        </template>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-3 sm:block sm:text-right">
                                    <p
                                        class="text-sm font-semibold"
                                        :class="item.credits >= 0 ? 'text-emerald-700' : 'text-amber-700'"
                                    >
                                        {{ formatCreditDelta(item.credits) }}
                                    </p>
                                    <p
                                        v-if="item.moneyLabel"
                                        class="mt-1 text-sm text-brand-muted"
                                    >
                                        Paid {{ item.moneyLabel }}
                                    </p>
                                    <p class="dashboard-meta mt-1">
                                        {{ item.credits >= 0 ? 'Added to wallet' : 'Used from wallet' }}
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
                            Showing {{ walletTransactionsPagination.from ?? 0 }} to {{ walletTransactionsPagination.to ?? 0 }} of {{ walletTransactionsPagination.total }} transactions
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button v-if="walletTransactionsPagination.prevPageUrl" as-child variant="outline">
                                <Link :href="walletTransactionsPagination.prevPageUrl">
                                    Previous
                                </Link>
                            </Button>
                            <Button v-else variant="outline" disabled>
                                Previous
                            </Button>
                            <span class="text-sm font-medium text-brand-muted">
                                Page {{ walletTransactionsPagination.currentPage }} of {{ walletTransactionsPagination.lastPage }}
                            </span>
                            <Button v-if="walletTransactionsPagination.nextPageUrl" as-child variant="outline">
                                <Link :href="walletTransactionsPagination.nextPageUrl">
                                    Next
                                </Link>
                            </Button>
                            <Button v-else variant="outline" disabled>
                                Next
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="topUpModalOpen">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader class="text-left">
                <DialogTitle>Top up credits</DialogTitle>
                <DialogDescription>
                    Pick a pack, choose checkout currency, and Stripe will bring you back here when payment is done. Top-ups and bonus credits land in the same balance, and new events deduct from that billing balance.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitTopUp">
                <div
                    role="radiogroup"
                    aria-label="Choose a top-up pack"
                    class="grid grid-cols-2 gap-2 sm:grid-cols-3"
                >
                    <button
                        v-for="pack in businessTopUp.packs"
                        :key="pack.credits"
                        type="button"
                        role="radio"
                        :aria-checked="pack.credits === topUpForm.credits"
                        :aria-label="`${pack.credits} credits${pack.bonus_credits > 0 ? ` plus ${pack.bonus_credits} bonus credits` : ''}`"
                        class="rounded-[1.15rem] border px-3 py-3 text-left transition"
                        :class="
                            pack.credits === topUpForm.credits
                                ? 'border-brand-ink bg-brand-ink text-brand-inverse'
                                : 'border-brand-border bg-brand-panel text-brand-ink hover:border-brand-accent/30 hover:bg-brand-highlight/20'
                        "
                        @click="topUpForm.credits = pack.credits"
                    >
                        <p class="text-sm font-semibold">
                            {{ pack.credits }} credits
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
                                    ? `+${pack.bonus_credits} bonus`
                                    : 'No bonus'
                            }}
                        </p>
                    </button>
                </div>

                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_150px]">
                    <div class="rounded-[1.15rem] border border-brand-border bg-brand-panel px-4 py-3">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-brand-muted">
                            You receive
                        </p>
                        <p class="mt-1 text-base font-semibold text-brand-ink">
                            {{ selectedPack?.total_credits ?? 0 }} credits
                        </p>
                        <p class="dashboard-meta mt-1">
                            {{ selectedPriceLabel ?? 'Price unavailable' }}
                        </p>
                    </div>

                    <label class="rounded-[1.15rem] border border-brand-border bg-brand-panel px-4 py-3 text-sm text-brand-muted">
                        <span class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-brand-muted">
                            Currency
                        </span>
                        <select
                            v-model="topUpForm.currency"
                            class="mt-2 block w-full border-0 bg-transparent px-0 text-sm font-medium text-brand-ink focus:ring-0"
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
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        class="bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                        :disabled="topUpForm.processing"
                    >
                        <Plus class="size-4" />
                        {{ topUpForm.processing ? 'Opening Stripe…' : `Top up ${topUpForm.credits} credits` }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="howItWorksModalOpen">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader class="text-left">
                <DialogTitle>How billing works</DialogTitle>
                <DialogDescription>
                    Keep business credits simple and reusable across paid events.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 text-sm leading-6 text-brand-muted">
                <div>
                    <p class="font-semibold text-brand-ink">Top up once</p>
                    <p class="mt-1">
                        Add credits in advance, then reuse that balance whenever you create a new paid business event.
                    </p>
                </div>

                <div>
                    <p class="font-semibold text-brand-ink">Bonus credits stay in the same balance</p>
                    <p class="mt-1">
                        Any bonus from a larger top-up lands in the same wallet, so you only need to track one credit balance.
                    </p>
                </div>

                <div>
                    <p class="font-semibold text-brand-ink">Events deduct from that wallet</p>
                    <p class="mt-1">
                        When you create a Plus or Pro business event, the required credits are subtracted automatically and recorded in the ledger below.
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
