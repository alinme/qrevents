<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, CreditCard, FolderKanban, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
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
        createEvent: string;
        topUpWallet: string;
        walletHistory: string;
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
        detail: 'Top-ups and bonus credits combined.',
    },
    {
        label: 'Used',
        value: `${props.walletSummary.creditsUsed} credits`,
        detail: 'Credits spent on business event creation.',
    },
];
</script>

<template>
    <Head title="Wallet History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[#faf7f2]">
            <div class="mx-auto grid max-w-7xl gap-5 p-4 md:p-6 xl:grid-cols-[minmax(0,1.55fr)_320px]">
                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-3 border-b border-black/5 pb-5">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Business wallet
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                Transaction history
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">
                                Every credit movement in one simple ledger.
                            </p>
                        </div>

                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-3">
                            <div
                                v-for="metric in compactMetrics"
                                :key="metric.label"
                                class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4"
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
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-b border-black/5 py-4">
                        <div>
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                All activity
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                {{ walletTransactionsPagination.total }} transactions recorded.
                            </p>
                        </div>
                        <p class="text-sm text-zinc-500">
                            Latest {{ formatDateTime(walletSummary.latestActivityAt, 'No activity yet') }}
                        </p>
                    </div>

                    <div v-if="walletTransactions.length === 0" class="py-12 text-center">
                        <div class="mx-auto max-w-md space-y-2">
                            <h3 class="text-lg font-semibold text-[#171411]">
                                No wallet activity yet
                            </h3>
                            <p class="text-sm leading-6 text-zinc-600">
                                Top up credits or create your first business event and the ledger will start here.
                            </p>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-black/5">
                        <article
                            v-for="item in walletTransactions"
                            :key="item.id"
                            class="grid gap-3 py-4 md:grid-cols-[minmax(0,1fr)_160px]"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                        :class="badgeClass(walletActivityTone(item.kind))"
                                    >
                                        {{ walletActivityKindLabel(item.kind) }}
                                    </span>
                                    <span class="text-xs text-zinc-500">
                                        {{ formatDateTime(item.createdAt) }}
                                    </span>
                                </div>

                                <p class="mt-3 text-sm font-semibold text-[#171411]">
                                    {{ item.description }}
                                </p>
                                <p class="mt-1 text-sm text-zinc-600">
                                    <template v-if="item.eventName">
                                        Workspace
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
                                        Business wallet account activity
                                    </template>
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3 md:flex-col md:items-end md:justify-center">
                                <p
                                    class="text-sm font-semibold"
                                    :class="item.credits >= 0 ? 'text-emerald-700' : 'text-amber-700'"
                                >
                                    {{ formatCreditDelta(item.credits) }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ walletActivityKindLabel(item.kind) }}
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

                <aside class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="space-y-5">
                        <div class="border-b border-black/5 pb-4">
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                Next move
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                Add credits when you need more capacity, then create the next client workspace from the same wallet.
                            </p>
                        </div>

                        <div class="space-y-3 text-sm text-zinc-600">
                            <p class="flex items-start gap-3">
                                <CreditCard class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                One credit equals one euro in wallet value.
                            </p>
                            <p class="flex items-start gap-3">
                                <ArrowUpRight class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Bonus credits stay visible in the same ledger so top-ups and promotions are easy to audit.
                            </p>
                            <p class="flex items-start gap-3">
                                <FolderKanban class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Event creation debits are linked back to the workspace when available.
                            </p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <Button as-child class="bg-[#171411] text-white hover:bg-[#2b2621]">
                                <Link :href="businessActionLinks.topUpWallet">
                                    <Plus class="size-4" />
                                    Top up wallet
                                </Link>
                            </Button>
                            <Button as-child variant="outline">
                                <Link :href="businessActionLinks.createEvent">
                                    Create event
                                </Link>
                            </Button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
