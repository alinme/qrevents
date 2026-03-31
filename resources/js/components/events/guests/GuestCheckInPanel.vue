<script setup lang="ts">
import { Copy, ExternalLink, Eye, Search, Table2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/composables/useTranslations';

type GuestCheckInParty = {
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
    parties: GuestCheckInParty[];
    search: string;
    publicGuestListUrl: string;
    eventTablesCount: number;
    quickSavingGuestId: number | null;
}>();

const emit = defineEmits<{
    'update:search': [value: string];
    'open-details': [party: GuestCheckInParty];
    'open-create-tables': [];
    'mark-present': [party: GuestCheckInParty];
    'mark-absent': [party: GuestCheckInParty];
    reset: [party: GuestCheckInParty];
    'copy-link': [url: string];
    'open-public-page': [url: string];
}>();

const { t } = useTranslations();
</script>

<template>
    <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-neutral-950">
                    {{ t('guests.sections.guest_list.check_in_title') }}
                </h2>
                <p class="mt-1 text-sm text-neutral-600">
                    {{ t('guests.sections.guest_list.check_in_description') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button
                    variant="outline"
                    class="rounded-full px-5"
                    @click="emit('copy-link', publicGuestListUrl)"
                >
                    <Copy class="mr-2 size-4" />
                    {{ t('guests.actions.copy_link') }}
                </Button>
                <Button
                    data-test="guest-checkin-open-public-page"
                    class="rounded-full px-5"
                    @click="emit('open-public-page', publicGuestListUrl)"
                >
                    <ExternalLink class="mr-2 size-4" />
                    {{ t('guests.actions.open_public_page') }}
                </Button>
            </div>
        </div>

        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
            <div class="space-y-3">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                    <Input
                        :model-value="search"
                        class="pl-9"
                        :placeholder="t('guests.actions.search_check_in')"
                        @update:model-value="emit('update:search', String($event))"
                    />
                </div>

                <div class="divide-y divide-neutral-200 border-t border-neutral-200">
                    <div
                        v-for="party in parties"
                        :key="party.id"
                        class="flex flex-col gap-3 py-3 transition-colors lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="min-w-0 space-y-1">
                            <p class="truncate text-sm font-semibold text-neutral-950">
                                {{ party.name }}
                            </p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-neutral-600">
                                <span>{{ party.phone || t('guests.shared.no_phone') }}</span>
                                <span>{{ party.tableName || t('guests.shared.no_table_yet') }}</span>
                                <span
                                    v-if="party.confirmedAttendeesCount !== null"
                                >
                                    {{
                                        t('guests.labels.confirmed_count', {
                                            count: party.confirmedAttendeesCount,
                                        })
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :data-test="`guest-checkin-details-${party.id}`"
                                @click="emit('open-details', party)"
                            >
                                <Eye class="mr-2 size-4" />
                                {{ t('guests.actions.details') }}
                            </Button>
                            <Button
                                v-if="!party.tableName && eventTablesCount === 0"
                                variant="ghost"
                                class="rounded-full px-4"
                                @click="emit('open-create-tables')"
                            >
                                <Table2 class="mr-2 size-4" />
                                {{ t('guests.actions.tables') }}
                            </Button>
                            <Button
                                :variant="party.actualAttendanceStatus === 'present' ? 'default' : 'outline'"
                                :class="party.actualAttendanceStatus === 'present' ? 'rounded-full bg-emerald-600 px-4 text-white hover:bg-emerald-700' : 'rounded-full px-4'"
                                :disabled="quickSavingGuestId === party.id"
                                @click="emit('mark-present', party)"
                            >
                                {{ t('guests.actions.mark_present') }}
                            </Button>
                            <Button
                                :variant="party.actualAttendanceStatus === 'absent' ? 'default' : 'outline'"
                                :class="party.actualAttendanceStatus === 'absent' ? 'rounded-full bg-rose-600 px-4 text-white hover:bg-rose-700' : 'rounded-full px-4'"
                                :disabled="quickSavingGuestId === party.id"
                                @click="emit('mark-absent', party)"
                            >
                                {{ t('guests.actions.mark_absent') }}
                            </Button>
                            <Button
                                variant="ghost"
                                class="rounded-full px-4"
                                :disabled="quickSavingGuestId === party.id"
                                @click="emit('reset', party)"
                            >
                                {{ t('guests.actions.reset') }}
                            </Button>
                        </div>
                    </div>

                    <div
                        v-if="parties.length === 0"
                        class="py-8 text-sm text-neutral-500"
                    >
                        {{ t('guests.empty.check_in_search') }}
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t border-neutral-200 pt-4 xl:border-l xl:border-t-0 xl:pl-5 xl:pt-0">
                <p class="text-sm font-semibold text-neutral-950">
                    {{ t('guests.sections.guest_list.public_page_title') }}
                </p>
                <p class="text-sm text-neutral-600">
                    {{ t('guests.sections.guest_list.public_page_description') }}
                </p>
                <div class="text-sm break-all text-neutral-600">
                    {{ publicGuestListUrl }}
                </div>
            </div>
        </div>
    </section>
</template>
