<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    badgeClass,
    formatCreditDelta,
    formatDateTime,
    walletActivityKindLabel,
    walletActivityTone,
} from '@/lib/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
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
        title: 'Wallet history',
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
    <Head title="Wallet History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[#faf7f2]">
            <div class="mx-auto max-w-7xl space-y-5 p-4 md:p-6">
                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-4 border-b border-black/5 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Business wallet
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                Wallet history
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">
                                A full ledger for top-ups, bonus credits, and event spend.
                            </p>
                        </div>
                    </div>

                    <div class="pt-5">
                        <dl class="grid gap-x-5 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="metric in compactMetrics"
                                :key="metric.label"
                                class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4 xl:first:border-l-0 xl:first:pl-0"
                            >
                                <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                    {{ metric.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-[#171411]">
                                    {{ metric.value }}
                                </dd>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">
                                    {{ metric.detail }}
                                </p>
                                <Button
                                    v-if="metric.label === 'Balance'"
                                    type="button"
                                    variant="link"
                                    class="mt-2 h-auto px-0 text-sm font-medium text-[#171411]"
                                    @click="topUpModalOpen = true"
                                >
                                    Top up credits
                                </Button>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-2 border-b border-black/5 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                All activity
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                {{ walletTransactionsPagination.total }} transactions recorded.
                            </p>
                        </div>
                        <p class="text-sm text-zinc-500">
                            Balance now {{ walletSummary.currentBalance }} credits
                        </p>
                    </div>

                    <div v-if="walletTransactions.length === 0" class="py-12 text-center">
                        <div class="mx-auto max-w-md space-y-2">
                            <h3 class="text-lg font-semibold text-[#171411]">
                                No wallet activity yet
                            </h3>
                            <p class="text-sm leading-6 text-zinc-600">
                                Your next top-up will appear here first, followed by bonus credits and event debits.
                            </p>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-black/5">
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
                                <p class="mt-2 text-xs text-zinc-500">
                                    {{ formatDateTime(item.createdAt) }}
                                </p>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#171411]">
                                    {{ item.description }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600">
                                    <template v-if="item.eventName">
                                        Event
                                        <Link
                                            v-if="item.eventUrl"
                                            :href="item.eventUrl"
                                            class="font-medium text-[#171411] hover:text-[#2b2621]"
                                        >
                                            {{ item.eventName }}
                                        </Link>
                                        <span v-else class="font-medium text-[#171411]">{{ item.eventName }}</span>
                                    </template>
                                    <template v-else>
                                        Business wallet activity
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
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ item.credits >= 0 ? 'Added to wallet' : 'Used from wallet' }}
                                </p>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="walletTransactionsPagination.lastPage > 1"
                        class="mt-4 flex flex-col gap-3 border-t border-black/5 pt-4 md:flex-row md:items-center md:justify-between"
                    >
                        <p class="text-sm text-zinc-600">
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
                            <span class="text-sm font-medium text-zinc-600">
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
                    Pick a pack, choose checkout currency, and Stripe will bring you back here when payment is done. Top-ups and bonus credits land in the same balance, and new events deduct from that wallet.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitTopUp">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <button
                        v-for="pack in businessTopUp.packs"
                        :key="pack.credits"
                        type="button"
                        class="rounded-[1.15rem] border px-3 py-3 text-left transition"
                        :class="
                            pack.credits === topUpForm.credits
                                ? 'border-[#171411] bg-[#171411] text-white'
                                : 'border-black/10 bg-[#fbfaf7] text-[#171411] hover:border-black/20 hover:bg-white'
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
                                    : 'text-zinc-500'
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
                    <div class="rounded-[1.15rem] border border-black/10 bg-[#fbfaf7] px-4 py-3">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                            You receive
                        </p>
                        <p class="mt-1 text-base font-semibold text-[#171411]">
                            {{ selectedPack?.total_credits ?? 0 }} credits
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ selectedPriceLabel ?? 'Price unavailable' }}
                        </p>
                    </div>

                    <label class="rounded-[1.15rem] border border-black/10 bg-[#fbfaf7] px-4 py-3 text-sm text-zinc-600">
                        <span class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                            Currency
                        </span>
                        <select
                            v-model="topUpForm.currency"
                            class="mt-2 block w-full border-0 bg-transparent px-0 text-sm font-medium text-[#171411] focus:ring-0"
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
                        class="bg-[#171411] text-white hover:bg-[#2b2621]"
                        :disabled="topUpForm.processing"
                    >
                        <Plus class="size-4" />
                        {{ topUpForm.processing ? 'Opening Stripe…' : `Top up ${topUpForm.credits} credits` }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
