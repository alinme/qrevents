<script setup lang="ts">
import { Download, Printer } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

type LedgerParty = {
    id: number;
    name: string;
    phone: string | null;
    eventTableId: number | null;
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
    reminderCount: number;
    lastReminderAt: string | null;
    invitationHistory: Array<{
        type: 'sent_online' | 'delivered_in_person' | 'reminded' | 'opened' | 'responded';
        deliveryChannel: string | null;
        createdAt: string | null;
        meta: Record<string, unknown>;
    }>;
    giftType: 'money' | 'gift' | null;
    giftCurrency: 'EUR' | 'GBP' | 'RON' | null;
    giftAmount: string | null;
    guestNames: string | null;
    mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
    responseNotes: string | null;
    inviteUrl: string;
    publicCheckInUrl: string;
    updateUrl: string;
    deleteUrl: string;
};

defineProps<{
    retentionReminder: {
        daysLeft: number;
        dateLabel: string;
    } | null;
    statCards: Array<{
        label: string;
        value: string | number;
        detail: string;
    }>;
    parties: LedgerParty[];
    attendanceBadgeClass: (status: LedgerParty['attendanceStatus']) => string;
    attendanceLabel: (status: LedgerParty['attendanceStatus']) => string;
    actualAttendanceLabel: (status: LedgerParty['actualAttendanceStatus']) => string;
    ledgerGiftLabel: (party: LedgerParty) => string;
}>();

const emit = defineEmits<{
    'open-report': [];
    'export-csv': [];
    'record-money': [party: LedgerParty];
    'record-gift': [party: LedgerParty];
    'record-both': [party: LedgerParty];
}>();

const { t } = useTranslations();
</script>

<template>
    <section class="space-y-4">
        <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-neutral-950">
                        {{ t('guests.sections.ledger.title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ t('guests.sections.ledger.description') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button data-test="guest-ledger-open-report" class="rounded-full px-5" @click="emit('open-report')">
                        <Printer class="mr-2 size-4" />
                        {{ t('guests.ledger.open_page') }}
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="emit('export-csv')">
                        <Download class="mr-2 size-4" />
                        {{ t('guests.ledger.export_csv') }}
                    </Button>
                </div>
            </div>

            <p
                v-if="retentionReminder"
                class="mt-4 text-sm text-amber-800"
            >
                {{ t('guests.ledger.export_before', { date: retentionReminder.dateLabel }) }}
            </p>

            <div class="mt-5 grid gap-4 border-y border-neutral-200 py-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="space-y-1"
                >
                    <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                        {{ stat.label }}
                    </p>
                    <p class="text-xl font-semibold tracking-tight text-neutral-950">
                        {{ stat.value }}
                    </p>
                    <p class="text-xs text-neutral-500">
                        {{ stat.detail }}
                    </p>
                </div>
            </div>

            <div class="mt-2 divide-y divide-neutral-200">
                <div
                    v-for="party in parties"
                    :key="party.id"
                    class="py-4"
                >
                    <div class="flex flex-col gap-4 xl:grid xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)_auto] xl:items-center">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-neutral-950">
                                    {{ party.name }}
                                </p>
                                <Badge variant="outline" :class="attendanceBadgeClass(party.attendanceStatus)">
                                    {{ attendanceLabel(party.attendanceStatus) }}
                                </Badge>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-neutral-600">
                                <span>{{ party.tableName || t('guests.shared.no_table_yet') }}</span>
                                <span>{{ t('guests.ledger.reserved_count', { count: party.invitedAttendeesCount }) }}</span>
                                <span v-if="party.confirmedAttendeesCount !== null">{{ t('guests.ledger.confirmed_count', { count: party.confirmedAttendeesCount }) }}</span>
                                <span>{{ actualAttendanceLabel(party.actualAttendanceStatus) }}</span>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.sections.ledger.title') }}
                            </p>
                            <p class="text-sm font-medium text-neutral-950">
                                {{ ledgerGiftLabel(party) }}
                            </p>
                            <p class="text-sm leading-6 text-neutral-600">
                                {{ party.notes || t('guests.ledger.no_note_yet') }}
                            </p>
                        </div>

                        <div class="xl:justify-self-end">
                            <div class="inline-flex items-center overflow-hidden rounded-full border border-neutral-200 bg-white">
                                <button
                                    type="button"
                                    :data-test="`guest-ledger-record-money-${party.id}`"
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50 hover:text-neutral-950"
                                    @click="emit('record-money', party)"
                                >
                                    {{ t('guests.ledger.modes.money') }}
                                </button>
                                <span class="h-6 w-px bg-neutral-200" />
                                <button
                                    type="button"
                                    :data-test="`guest-ledger-record-gift-${party.id}`"
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50 hover:text-neutral-950"
                                    @click="emit('record-gift', party)"
                                >
                                    {{ t('guests.ledger.modes.gift') }}
                                </button>
                                <span class="h-6 w-px bg-neutral-200" />
                                <button
                                    type="button"
                                    :data-test="`guest-ledger-record-both-${party.id}`"
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50 hover:text-neutral-950"
                                    @click="emit('record-both', party)"
                                >
                                    {{ t('guests.ledger.modes.both') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="parties.length === 0" class="py-10 text-sm text-neutral-500">
                    {{ t('guests.ledger.empty') }}
                </div>
            </div>
        </section>
    </section>
</template>
