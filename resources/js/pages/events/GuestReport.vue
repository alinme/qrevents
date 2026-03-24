<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    Download,
    MailCheck,
    Printer,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type EventPayload = {
    id: number;
    name: string;
};

type EventLinks = {
    guests: string;
};

type GuestParty = {
    id: number;
    name: string;
    phone: string | null;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number | null;
    attendanceStatus: 'pending' | 'accepted' | 'declined';
    notes: string | null;
    invitationStatus: 'draft' | 'delivered_in_person' | 'sent' | 'opened' | 'responded';
    invitationDeliveryChannel: string | null;
    invitationDeliveredAt: string | null;
    invitationOpenCount: number;
    invitationFirstOpenedAt: string | null;
    invitationLastOpenedAt: string | null;
    invitationLastOpenedIp: string | null;
    respondedAt: string | null;
    giftType: 'money' | 'gift' | null;
    giftCurrency: 'EUR' | 'GBP' | 'RON' | null;
    giftAmount: string | null;
    guestNames: string | null;
    mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
    responseNotes: string | null;
};

type GuestPartyStats = {
    partyCount: number;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number;
    acceptedPartyCount: number;
    pendingPartyCount: number;
    declinedPartyCount: number;
    moneyGiftTotal: number;
    moneyGiftCurrency: string;
};

type GuestReport = {
    deliveredPartyCount: number;
    sentOnlinePartyCount: number;
    openedPartyCount: number;
    respondedPartyCount: number;
    giftRecordedPartyCount: number;
    responseRate: number;
    attendanceFillRate: number;
    averageMoneyGiftPerAcceptedParty: number;
    moneyGiftTotals: Array<{
        currency: string;
        amount: number;
    }>;
    recentResponses: Array<{
        name: string;
        attendanceStatus: 'pending' | 'accepted' | 'declined';
        confirmedAttendeesCount: number | null;
        respondedAt: string | null;
        mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
        responseNotes: string | null;
    }>;
    recentInvitationOpens: Array<{
        name: string;
        invitationOpenCount: number;
        invitationLastOpenedAt: string | null;
        invitationLastOpenedIp: string | null;
        invitationDeliveryChannel: string | null;
    }>;
};

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    guestLedgerExportUrl: string;
    guestPartyStats: GuestPartyStats;
    guestReport: GuestReport;
    guestParties: GuestParty[];
}>();

const summaryCards = computed(() => [
    {
        label: 'Families on the list',
        value: props.guestPartyStats.partyCount,
        detail: `${props.guestReport.deliveredPartyCount} invitations already delivered`,
        icon: MailCheck,
    },
    {
        label: 'Confirmed seats',
        value: props.guestPartyStats.confirmedAttendeesCount,
        detail: `${props.guestPartyStats.invitedAttendeesCount} invited in total`,
        icon: CheckCircle2,
    },
    {
        label: 'Responses received',
        value: props.guestReport.respondedPartyCount,
        detail: `${props.guestReport.responseRate}% of the list has replied`,
        icon: Clock3,
    },
    {
        label: 'Gift records',
        value: props.guestReport.giftRecordedPartyCount,
        detail: props.guestReport.moneyGiftTotals.length > 0
            ? props.guestReport.moneyGiftTotals.map((total) => formatMoney(total.amount, total.currency)).join(' · ')
            : 'No money gifts recorded yet',
        icon: Wallet,
    },
]);

const printReport = (): void => {
    window.print();
};

const exportGuestLedger = (): void => {
    window.location.assign(props.guestLedgerExportUrl);
};

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'Not yet';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatMoney = (value: number, currency: string): string => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
        maximumFractionDigits: 2,
    }).format(value);
};

const attendanceLabel = (status: GuestParty['attendanceStatus']): string => {
    return {
        accepted: 'Accepted',
        declined: 'Declined',
        pending: 'Pending',
    }[status];
};

const invitationLabel = (status: GuestParty['invitationStatus']): string => {
    return {
        draft: 'Draft',
        delivered_in_person: 'Delivered in person',
        sent: 'Sent online',
        opened: 'Opened',
        responded: 'Responded',
    }[status];
};

const giftLabel = (party: GuestParty): string => {
    if (party.giftType === 'money' && party.giftAmount && party.giftCurrency) {
        return formatMoney(Number(party.giftAmount), party.giftCurrency);
    }

    if (party.giftType === 'gift') {
        return 'Gift recorded';
    }

    return 'Pending';
};
</script>

<template>
    <div class="min-h-screen bg-stone-100 text-neutral-950 print:bg-white">
        <Head :title="`${currentEvent.name} Guest Report`" />

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 print:max-w-none print:px-0 print:py-0">
            <section class="rounded-[32px] border border-stone-200 bg-white p-5 shadow-sm print:rounded-none print:border-0 print:shadow-none">
                <div class="flex flex-col gap-4 border-b border-stone-200 pb-5 print:hidden lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-neutral-500">
                            Guest report and printable ledger
                        </p>
                        <h1 class="text-3xl font-semibold tracking-tight text-neutral-950">
                            {{ currentEvent.name }}
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">
                            A cleaner operational report for invitations, RSVPs, and the family gift ledger. You can print this page or save it as a PDF after the event.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button as-child variant="outline" class="rounded-full px-5">
                            <Link :href="eventLinks.guests">
                                <ArrowLeft class="mr-2 size-4" />
                                Back to guests
                            </Link>
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="exportGuestLedger">
                            <Download class="mr-2 size-4" />
                            Export CSV
                        </Button>
                        <Button class="rounded-full px-5" @click="printReport">
                            <Printer class="mr-2 size-4" />
                            Print or save PDF
                        </Button>
                    </div>
                </div>

                <div class="hidden print:block">
                    <div class="border-b border-stone-200 pb-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-neutral-500">
                            EventSmart Guest Report
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-neutral-950">
                            {{ currentEvent.name }}
                        </h1>
                        <p class="mt-2 text-sm text-neutral-600">
                            Printed on {{ formatDateTime(new Date().toISOString()) }}
                        </p>
                    </div>
                </div>

                <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4 print:mt-5 print:grid-cols-4">
                    <article
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="rounded-3xl border border-stone-200 bg-stone-50 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-neutral-500">
                                    {{ card.label }}
                                </p>
                                <p class="mt-2 text-3xl font-semibold tracking-tight text-neutral-950">
                                    {{ card.value }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-neutral-950 p-2 text-white">
                                <component :is="card.icon" class="size-4" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-neutral-600">
                            {{ card.detail }}
                        </p>
                    </article>
                </section>

                <section class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,0.78fr)_minmax(320px,0.52fr)] print:grid-cols-[1.2fr_0.8fr]">
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-500">
                                    RSVP health
                                </p>
                                <h2 class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                    Response funnel
                                </h2>
                            </div>
                            <p class="text-sm font-medium text-neutral-500">
                                {{ guestReport.responseRate }}% answered
                            </p>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-sm text-neutral-600">
                                    <span>Accepted</span>
                                    <span>{{ guestPartyStats.acceptedPartyCount }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-emerald-500" :style="{ width: `${guestPartyStats.partyCount === 0 ? 0 : (guestPartyStats.acceptedPartyCount / guestPartyStats.partyCount) * 100}%` }" />
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-sm text-neutral-600">
                                    <span>Pending</span>
                                    <span>{{ guestPartyStats.pendingPartyCount }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-amber-400" :style="{ width: `${guestPartyStats.partyCount === 0 ? 0 : (guestPartyStats.pendingPartyCount / guestPartyStats.partyCount) * 100}%` }" />
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-sm text-neutral-600">
                                    <span>Declined</span>
                                    <span>{{ guestPartyStats.declinedPartyCount }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-white">
                                    <div class="h-2 rounded-full bg-rose-400" :style="{ width: `${guestPartyStats.partyCount === 0 ? 0 : (guestPartyStats.declinedPartyCount / guestPartyStats.partyCount) * 100}%` }" />
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                                <p class="text-sm font-medium text-neutral-500">
                                    Invitation opens
                                </p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                                    {{ guestReport.openedPartyCount }}
                                </p>
                                <p class="mt-2 text-sm text-neutral-600">
                                    {{ guestReport.sentOnlinePartyCount }} were sent through online channels.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                                <p class="text-sm font-medium text-neutral-500">
                                    Attendance fill
                                </p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                                    {{ guestReport.attendanceFillRate }}%
                                </p>
                                <p class="mt-2 text-sm text-neutral-600">
                                    Based on confirmed seats versus the full invited count.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-500">
                            Gift snapshot
                        </p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                            Ledger totals
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="giftTotal in guestReport.moneyGiftTotals"
                                :key="giftTotal.currency"
                                class="rounded-2xl border border-stone-200 bg-white p-4"
                            >
                                <p class="text-sm font-medium text-neutral-500">
                                    {{ giftTotal.currency }}
                                </p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                                    {{ formatMoney(giftTotal.amount, giftTotal.currency) }}
                                </p>
                            </div>

                            <div v-if="guestReport.moneyGiftTotals.length === 0" class="rounded-2xl border border-dashed border-stone-300 bg-white p-4 text-sm text-neutral-600">
                                No money gifts have been written into the ledger yet.
                            </div>

                            <div class="rounded-2xl border border-stone-200 bg-white p-4">
                                <p class="text-sm font-medium text-neutral-500">
                                    Average money gift per accepted party
                                </p>
                                <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                                    {{ guestReport.moneyGiftTotals.length === 1
                                        ? formatMoney(guestReport.averageMoneyGiftPerAcceptedParty, guestReport.moneyGiftTotals[0].currency)
                                        : guestReport.averageMoneyGiftPerAcceptedParty.toFixed(2) }}
                                </p>
                                <p class="mt-2 text-sm text-neutral-600">
                                    {{ guestReport.giftRecordedPartyCount }} parties already have a gift note or amount recorded.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-500">
                            Latest replies
                        </p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                            Recent RSVP activity
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="response in guestReport.recentResponses"
                                :key="`${response.name}-${response.respondedAt}`"
                                class="rounded-2xl border border-stone-200 bg-white p-4"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-base font-semibold text-neutral-950">
                                        {{ response.name }}
                                    </p>
                                    <p class="text-sm text-neutral-500">
                                        {{ formatDateTime(response.respondedAt) }}
                                    </p>
                                </div>
                                <p class="mt-2 text-sm text-neutral-600">
                                    {{ attendanceLabel(response.attendanceStatus) }}
                                    <span v-if="response.confirmedAttendeesCount !== null">
                                        · {{ response.confirmedAttendeesCount }} confirmed
                                    </span>
                                    <span v-if="response.mealPreference">
                                        · {{ response.mealPreference }}
                                    </span>
                                </p>
                                <p v-if="response.responseNotes" class="mt-2 text-sm leading-6 text-neutral-600">
                                    {{ response.responseNotes }}
                                </p>
                            </div>

                            <div v-if="guestReport.recentResponses.length === 0" class="rounded-2xl border border-dashed border-stone-300 bg-white p-4 text-sm text-neutral-600">
                                No RSVP answers yet.
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-stone-200 bg-stone-50 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-500">
                            Invite tracking
                        </p>
                        <h2 class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                            Recent invitation opens
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="open in guestReport.recentInvitationOpens"
                                :key="`${open.name}-${open.invitationLastOpenedAt}`"
                                class="rounded-2xl border border-stone-200 bg-white p-4"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-base font-semibold text-neutral-950">
                                        {{ open.name }}
                                    </p>
                                    <p class="text-sm text-neutral-500">
                                        {{ formatDateTime(open.invitationLastOpenedAt) }}
                                    </p>
                                </div>
                                <p class="mt-2 text-sm text-neutral-600">
                                    {{ open.invitationOpenCount }} opens
                                    <span v-if="open.invitationDeliveryChannel">
                                        · {{ open.invitationDeliveryChannel }}
                                    </span>
                                    <span v-if="open.invitationLastOpenedIp">
                                        · {{ open.invitationLastOpenedIp }}
                                    </span>
                                </p>
                            </div>

                            <div v-if="guestReport.recentInvitationOpens.length === 0" class="rounded-2xl border border-dashed border-stone-300 bg-white p-4 text-sm text-neutral-600">
                                No tracked invitation opens yet.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-500">
                                Full ledger
                            </p>
                            <h2 class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                Every family in one printable table
                            </h2>
                        </div>
                        <p class="text-sm text-neutral-500">
                            {{ guestParties.length }} rows
                        </p>
                    </div>

                    <div class="mt-4 overflow-x-auto rounded-3xl border border-stone-200 bg-white">
                        <table class="min-w-full divide-y divide-stone-200 text-left text-sm">
                            <thead class="bg-stone-50">
                                <tr class="text-xs uppercase tracking-[0.18em] text-neutral-500">
                                    <th class="px-4 py-3 font-semibold">Family / Name</th>
                                    <th class="px-4 py-3 font-semibold">Phone</th>
                                    <th class="px-4 py-3 font-semibold">Seats</th>
                                    <th class="px-4 py-3 font-semibold">RSVP</th>
                                    <th class="px-4 py-3 font-semibold">Invitation</th>
                                    <th class="px-4 py-3 font-semibold">Gift</th>
                                    <th class="px-4 py-3 font-semibold">Tracking</th>
                                    <th class="px-4 py-3 font-semibold">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200">
                                <tr v-for="party in guestParties" :key="party.id" class="align-top">
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-neutral-950">
                                            {{ party.name }}
                                        </p>
                                        <p v-if="party.guestNames" class="mt-1 text-xs leading-5 text-neutral-500">
                                            {{ party.guestNames }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        {{ party.phone || '—' }}
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        <p>Invited: {{ party.invitedAttendeesCount }}</p>
                                        <p>Confirmed: {{ party.confirmedAttendeesCount ?? 0 }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        <p>{{ attendanceLabel(party.attendanceStatus) }}</p>
                                        <p v-if="party.respondedAt" class="mt-1 text-xs text-neutral-500">
                                            {{ formatDateTime(party.respondedAt) }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        <p>{{ invitationLabel(party.invitationStatus) }}</p>
                                        <p class="mt-1 text-xs text-neutral-500">
                                            {{ party.invitationOpenCount }} opens
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        {{ giftLabel(party) }}
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        <p>Last open: {{ formatDateTime(party.invitationLastOpenedAt) }}</p>
                                        <p v-if="party.invitationLastOpenedIp" class="mt-1 text-xs text-neutral-500">
                                            {{ party.invitationLastOpenedIp }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        <p v-if="party.notes" class="leading-6">
                                            {{ party.notes }}
                                        </p>
                                        <p v-else-if="party.responseNotes" class="leading-6">
                                            {{ party.responseNotes }}
                                        </p>
                                        <span v-else>—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </section>
        </main>
    </div>
</template>
