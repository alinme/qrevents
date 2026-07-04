<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Coins, Plus, Wallet } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';

type Pack = {
    credits: number;
    bonus_percent: number;
    bonus_credits: number;
    total_credits: number;
    prices: Record<string, string>;
};

type Transaction = {
    id: number;
    kind: string;
    credits: number;
    description: string;
    createdAt: string | null;
};

type EventRow = {
    id: number;
    name: string;
    status: string;
    planName: string;
    isPaid: boolean;
    assetCount: number;
    links: { open: string; payCredits: string };
};

type BusinessPlan = {
    id: number;
    name: string;
    creditCost: number;
};

const props = defineProps<{
    profile: {
        companyName: string;
        brandName: string;
        billingEmail: string;
        logoUrl: string | null;
    };
    stats: {
        events: number;
        liveEvents: number;
        uploads: number;
        credits: number;
    };
    businessPlans: BusinessPlan[];
    wallet: { credits: number; currency: string };
    topUpPacks: Pack[];
    currencies: string[];
    transactions: Transaction[];
    events: EventRow[];
    walletCheckoutUrl: string;
    editProfileUrl: string;
    createEventUrl: string;
    checkoutResult: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Business', href: '/dashboard/business' },
];

const currency = ref(props.currencies[0] ?? 'EUR');
const buying = ref<number | null>(null);

const buyPack = (pack: Pack): void => {
    router.post(
        props.walletCheckoutUrl,
        { credits: pack.credits, currency: currency.value },
        {
            onStart: () => (buying.value = pack.credits),
            onFinish: () => (buying.value = null),
        },
    );
};

const payingEventId = ref<number | null>(null);

const payWithCredits = (event: EventRow, plan: BusinessPlan): void => {
    router.post(
        event.links.payCredits,
        { plan_id: plan.id },
        {
            preserveScroll: true,
            onStart: () => (payingEventId.value = event.id),
            onFinish: () => (payingEventId.value = null),
        },
    );
};
</script>

<template>
    <Head title="Business dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="mx-auto max-w-4xl p-4 md:p-6">
                <div
                    v-if="checkoutResult === 'success'"
                    class="mb-8 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"
                >
                    Payment received — your credits will appear once the payment
                    is confirmed.
                </div>
                <div
                    v-else-if="checkoutResult === 'cancelled'"
                    class="mb-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800"
                >
                    Checkout cancelled — no credits were purchased.
                </div>

                <!-- Page header -->
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-3">
                        <img
                            v-if="profile.logoUrl"
                            :src="profile.logoUrl"
                            alt="Logo"
                            class="h-12 w-12 rounded-lg object-contain"
                        />
                        <div>
                            <p class="dashboard-eyebrow">Business</p>
                            <h1 class="dashboard-title mt-1">
                                {{ profile.companyName || profile.brandName }}
                            </h1>
                            <p class="dashboard-meta">{{ profile.billingEmail }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button as-child size="sm" variant="outline">
                            <Link :href="editProfileUrl">Edit profile</Link>
                        </Button>
                        <Button
                            as-child
                            size="sm"
                            class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                        >
                            <Link :href="createEventUrl">
                                <Plus class="size-4" /> New event
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Stats -->
                <dl
                    class="mt-8 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div>
                        <dt class="dashboard-eyebrow">Events</dt>
                        <dd class="mt-1 text-xl font-bold text-brand-ink">
                            {{ stats.events }}
                        </dd>
                    </div>
                    <div>
                        <dt class="dashboard-eyebrow">Live now</dt>
                        <dd class="mt-1 text-xl font-bold text-brand-ink">
                            {{ stats.liveEvents }}
                        </dd>
                    </div>
                    <div>
                        <dt class="dashboard-eyebrow">Total uploads</dt>
                        <dd class="mt-1 text-xl font-bold text-brand-ink">
                            {{ stats.uploads }}
                        </dd>
                    </div>
                    <div>
                        <dt class="dashboard-eyebrow">Wallet credits</dt>
                        <dd class="mt-1 text-xl font-bold text-brand-ink">
                            {{ stats.credits }}
                        </dd>
                    </div>
                </dl>

                <!-- Wallet + top up -->
                <section class="mt-8 border-t border-brand-border/70 pt-8">
                    <div
                        class="grid gap-x-10 gap-y-5 md:grid-cols-[240px_minmax(0,1fr)]"
                    >
                        <div>
                            <h2 class="flex items-center gap-2 text-sm font-semibold text-brand-ink">
                                <Wallet class="size-4 text-brand-accent" />
                                Wallet
                            </h2>
                            <p class="dashboard-meta mt-1.5 leading-relaxed">
                                Prepay credits and spend them on client events.
                            </p>
                            <p class="mt-3 text-2xl font-bold text-brand-ink">
                                {{ wallet.credits }}
                                <span class="text-sm font-medium text-brand-muted">credits</span>
                            </p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-semibold text-brand-ink">
                                    Top up
                                </h3>
                                <label class="flex items-center gap-2 text-sm">
                                    <span class="text-brand-muted">Currency</span>
                                    <select
                                        v-model="currency"
                                        class="h-8 rounded-md border border-brand-border bg-transparent px-2 text-sm"
                                    >
                                        <option
                                            v-for="c in currencies"
                                            :key="c"
                                            :value="c"
                                        >
                                            {{ c }}
                                        </option>
                                    </select>
                                </label>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="pack in topUpPacks"
                                    :key="pack.credits"
                                    class="flex flex-col gap-2 rounded-xl border border-brand-border/70 p-4"
                                >
                                    <div class="flex items-center gap-2">
                                        <Coins class="size-4 text-brand-accent" />
                                        <span class="text-lg font-bold text-brand-ink">
                                            {{ pack.total_credits }} credits
                                        </span>
                                    </div>
                                    <p class="dashboard-meta">
                                        {{ pack.prices[currency] ?? '—' }}
                                        <span
                                            v-if="pack.bonus_percent > 0"
                                            class="font-semibold text-emerald-600"
                                        >
                                            +{{ pack.bonus_percent }}% bonus
                                        </span>
                                    </p>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        :disabled="buying === pack.credits"
                                        @click="buyPack(pack)"
                                    >
                                        Buy
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Your events -->
                <section class="mt-8 border-t border-brand-border/70 pt-8">
                    <div
                        class="grid gap-x-10 gap-y-5 md:grid-cols-[240px_minmax(0,1fr)]"
                    >
                        <div>
                            <h2 class="text-sm font-semibold text-brand-ink">
                                Your events
                            </h2>
                            <p class="dashboard-meta mt-1.5 leading-relaxed">
                                Pay an unpaid event with wallet credits, or open it
                                to manage.
                            </p>
                        </div>
                        <div>
                            <div v-if="events.length === 0" class="dashboard-body">
                                No events yet. Create your first one.
                            </div>
                            <div v-else class="divide-y divide-brand-border/70">
                                <div
                                    v-for="event in events"
                                    :key="event.id"
                                    class="flex flex-col gap-2 py-3 first:pt-0 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-brand-ink">
                                                {{ event.name }}
                                            </p>
                                            <span
                                                v-if="event.isPaid"
                                                class="rounded-full bg-emerald-100 px-2 py-0.5 text-[0.65rem] font-semibold text-emerald-700"
                                            >
                                                Paid
                                            </span>
                                        </div>
                                        <p class="dashboard-meta">
                                            {{ event.status }} · {{ event.planName }} ·
                                            {{ event.assetCount }} uploads
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <template v-if="!event.isPaid">
                                            <Button
                                                v-for="plan in businessPlans"
                                                :key="plan.id"
                                                size="sm"
                                                variant="outline"
                                                :disabled="
                                                    payingEventId === event.id ||
                                                    wallet.credits < plan.creditCost
                                                "
                                                :title="
                                                    wallet.credits < plan.creditCost
                                                        ? 'Not enough credits'
                                                        : ''
                                                "
                                                @click="payWithCredits(event, plan)"
                                            >
                                                {{ plan.name }} · {{ plan.creditCost }} cr
                                            </Button>
                                        </template>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.open">Open</Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Wallet history -->
                <section class="mt-8 border-t border-brand-border/70 pt-8">
                    <div
                        class="grid gap-x-10 gap-y-5 md:grid-cols-[240px_minmax(0,1fr)]"
                    >
                        <div>
                            <h2 class="text-sm font-semibold text-brand-ink">
                                Wallet history
                            </h2>
                            <p class="dashboard-meta mt-1.5 leading-relaxed">
                                Top-ups, bonuses, and event charges.
                            </p>
                        </div>
                        <div>
                            <div
                                v-if="transactions.length === 0"
                                class="dashboard-body"
                            >
                                No wallet activity yet.
                            </div>
                            <ul v-else class="divide-y divide-brand-border/70">
                                <li
                                    v-for="tx in transactions"
                                    :key="tx.id"
                                    class="flex items-center justify-between gap-3 py-2.5 text-sm first:pt-0"
                                >
                                    <div>
                                        <p class="text-brand-ink">{{ tx.description }}</p>
                                        <p class="dashboard-meta">
                                            {{ formatDateTime(tx.createdAt) }}
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 font-semibold"
                                        :class="
                                            tx.credits < 0
                                                ? 'text-rose-600'
                                                : 'text-emerald-600'
                                        "
                                    >
                                        {{ tx.credits > 0 ? '+' : '' }}{{ tx.credits }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
