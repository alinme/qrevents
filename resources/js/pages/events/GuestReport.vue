<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Download, Printer } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type EventPayload = {
    id: number;
    name: string;
    retentionEndsAt?: string | null;
};

type EventLinks = {
    guests: string;
};

type GuestParty = {
    id: number;
    name: string;
    phone: string | null;
    tableName: string | null;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number | null;
    attendanceStatus: 'pending' | 'accepted' | 'declined';
    actualAttendeesCount: number | null;
    actualAttendanceStatus: 'unknown' | 'present' | 'absent';
    actualAttendanceRecordedAt: string | null;
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
    actualAttendeesCount: number;
    acceptedPartyCount: number;
    pendingPartyCount: number;
    declinedPartyCount: number;
    presentPartyCount: number;
    absentPartyCount: number;
    moneyGiftTotal: number;
    moneyGiftCurrency: string;
};

type GuestReport = {
    deliveredPartyCount: number;
    sentOnlinePartyCount: number;
    openedPartyCount: number;
    respondedPartyCount: number;
    giftRecordedPartyCount: number;
    presentPartyCount: number;
    absentPartyCount: number;
    responseRate: number;
    attendanceFillRate: number;
    actualAttendanceFillRate: number;
    averageMoneyGiftPerAcceptedParty: number;
    moneyGiftTotals: Array<{
        currency: string;
        amount: number;
    }>;
    recentResponses: Array<{
        name: string;
        attendanceStatus: 'pending' | 'accepted' | 'declined';
        confirmedAttendeesCount: number | null;
        actualAttendanceStatus: 'unknown' | 'present' | 'absent';
        actualAttendeesCount: number | null;
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
        label: 'Parties',
        value: props.guestPartyStats.partyCount,
        detail: `${props.guestReport.giftRecordedPartyCount} with ledger entries`,
    },
    {
        label: 'Accepted',
        value: props.guestPartyStats.acceptedPartyCount,
        detail: `${props.guestPartyStats.confirmedAttendeesCount} seats confirmed`,
    },
    {
        label: 'Arrived',
        value: props.guestPartyStats.presentPartyCount,
        detail: `${props.guestPartyStats.actualAttendeesCount} seats recorded`,
    },
    {
        label: 'Money total',
        value: props.guestReport.moneyGiftTotals.length > 0
            ? props.guestReport.moneyGiftTotals.map((total) => formatMoney(total.amount, total.currency)).join(' · ')
            : 'No money yet',
        detail: props.guestReport.moneyGiftTotals.length > 0
            ? `${props.guestReport.giftRecordedPartyCount} families recorded`
            : 'Add money, gifts, and notes from the ledger tab',
    },
]);

const retentionReminder = computed(() => {
    if (!props.currentEvent.retentionEndsAt) {
        return null;
    }

    const endsAt = new Date(props.currentEvent.retentionEndsAt);
    const diffDays = Math.ceil((endsAt.getTime() - Date.now()) / (1000 * 60 * 60 * 24));

    if (Number.isNaN(diffDays) || diffDays < 0) {
        return null;
    }

    return {
        daysLeft: diffDays,
        dateLabel: formatDateTime(props.currentEvent.retentionEndsAt),
    };
});

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

const actualAttendanceLabel = (status: GuestParty['actualAttendanceStatus']): string => {
    return {
        unknown: 'Not recorded',
        present: 'Present',
        absent: 'Absent',
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
        <Head :title="`${currentEvent.name} Ledger`" />

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8 print:max-w-none print:px-0 print:py-0">
            <section class="rounded-[32px] border border-stone-200 bg-white p-5 shadow-sm print:rounded-none print:border-0 print:shadow-none">
                <div class="flex flex-col gap-4 border-b border-stone-200 pb-5 print:hidden lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-neutral-500">
                            Printable ledger
                        </p>
                        <h1 class="text-3xl font-semibold tracking-tight text-neutral-950">
                            {{ currentEvent.name }}
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-neutral-600">
                            A slim owner-only page for totals, notes, export, and print. Attendance actions stay in Guest list.
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
                            EventSmart Ledger
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
                    <div
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="space-y-1 border-b border-stone-200 pb-4 print:border-b-0 print:pb-0"
                    >
                        <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                            {{ card.label }}
                        </p>
                        <p class="text-2xl font-semibold tracking-tight text-neutral-950">
                            {{ card.value }}
                        </p>
                        <p class="text-sm leading-6 text-neutral-600">
                            {{ card.detail }}
                        </p>
                    </div>
                </section>

                <section v-if="retentionReminder" class="mt-6 print:hidden">
                    <p class="text-sm text-amber-800">
                        Export this ledger before {{ retentionReminder.dateLabel }}.
                    </p>
                </section>

                <section class="mt-6 border-y border-stone-200 py-4">
                    <div class="flex flex-wrap gap-x-8 gap-y-3 text-sm text-neutral-700">
                        <span>{{ guestReport.respondedPartyCount }} replied</span>
                        <span>{{ guestReport.openedPartyCount }} opened the invite</span>
                        <span>{{ guestPartyStats.pendingPartyCount }} still waiting</span>
                        <span>{{ guestPartyStats.absentPartyCount }} marked absent</span>
                        <span>{{ guestReport.giftRecordedPartyCount }} ledger records</span>
                    </div>
                    <div v-if="guestReport.moneyGiftTotals.length > 0" class="mt-3 flex flex-wrap gap-x-8 gap-y-2 text-sm text-neutral-700">
                        <span
                            v-for="giftTotal in guestReport.moneyGiftTotals"
                            :key="giftTotal.currency"
                            class="font-medium text-neutral-950"
                        >
                            {{ giftTotal.currency }} {{ formatMoney(giftTotal.amount, giftTotal.currency) }}
                        </span>
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
                                    <th class="px-4 py-3 font-semibold">Table</th>
                                    <th class="px-4 py-3 font-semibold">Seats</th>
                                    <th class="px-4 py-3 font-semibold">RSVP</th>
                                    <th class="px-4 py-3 font-semibold">Event day</th>
                                    <th class="px-4 py-3 font-semibold">Gift</th>
                                    <th class="px-4 py-3 font-semibold">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200">
                                <tr v-for="party in guestParties" :key="party.id" class="align-top">
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-neutral-950">
                                            {{ party.name }}
                                        </p>
                                        <p class="mt-1 text-xs leading-5 text-neutral-500">
                                            {{ party.phone || 'No phone saved' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        {{ party.tableName || '—' }}
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
                                        <p>{{ actualAttendanceLabel(party.actualAttendanceStatus) }}</p>
                                        <p class="mt-1 text-xs text-neutral-500">
                                            Came: {{ party.actualAttendeesCount ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-neutral-600">
                                        {{ giftLabel(party) }}
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
