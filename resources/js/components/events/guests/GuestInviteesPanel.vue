<script setup lang="ts">
import { CheckCircle2, Clock3, Copy, ExternalLink, Pencil, Phone, Search, SendHorizontal, Trash2, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { NativeSelect, NativeSelectOption } from '@/components/ui/native-select';
import { useTranslations } from '@/composables/useTranslations';

type GuestParty = {
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

const props = defineProps<{
    overview: Array<{
        label: string;
        value: string | number;
    }>;
    guestPartiesCount: number;
    filteredGuestParties: GuestParty[];
    selectedGuestIds: number[];
    selectedPendingCount: number;
    allGuestsSelected: boolean;
    expandedGuestPartyId: number | null;
    guestSearch: string;
    guestFilter: 'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift';
    invitationBulkProcessing: boolean;
    attendanceBadgeClass: (status: GuestParty['attendanceStatus']) => string;
    attendanceLabel: (status: GuestParty['attendanceStatus']) => string;
    invitationLabel: (status: GuestParty['invitationStatus']) => string;
    giftLabel: (party: GuestParty) => string;
    actualAttendanceLabel: (status: GuestParty['actualAttendanceStatus']) => string;
    formatDateTime: (value: string | null) => string;
    mealPreferenceLabel: (value: GuestParty['mealPreference']) => string | null;
    invitationHistoryLabel: (activity: GuestParty['invitationHistory'][number]) => string;
    deliveryChannelLabel: (channel: string | null) => string;
}>();

const emit = defineEmits<{
    'update:guestSearch': [value: string];
    'update:guestFilter': [value: 'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift'];
    'toggle-select-all': [checked: boolean];
    'toggle-select-guest': [guestPartyId: number, checked: boolean];
    'open-create': [];
    'open-import': [];
    'share-selected': [];
    'copy-selected': [];
    'remind-selected': [];
    'mark-selected-delivered': [];
    'mark-selected-sent': [];
    'clear-selection': [];
    'share-guest': [party: GuestParty];
    'remind-guest': [party: GuestParty];
    'edit-guest': [party: GuestParty];
    'toggle-details': [guestPartyId: number];
    'copy-link': [url: string, label: string];
    'open-invite': [url: string];
    'mark-delivered': [party: GuestParty];
    'mark-sent': [party: GuestParty];
    'confirm-delete': [party: GuestParty];
}>();

const { t } = useTranslations();

const guestFilterModel = computed({
    get: () => props.guestFilter,
    set: (value: 'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift') => {
        emit('update:guestFilter', value);
    },
});
</script>

<template>
    <section class="space-y-4">
        <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
            <div class="grid gap-px bg-neutral-200 sm:grid-cols-3">
                <div
                    v-for="item in overview"
                    :key="item.label"
                    class="bg-white px-4 py-3"
                >
                    <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                        {{ item.label }}
                    </p>
                    <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                        {{ item.value }}
                    </p>
                </div>
            </div>
        </div>

        <section class="rounded-3xl border border-neutral-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-neutral-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-neutral-950">
                        {{ t('guests.sections.invitees.title') }}
                    </h2>
                    <p class="text-sm text-neutral-600">
                        {{ t('guests.sections.invitees.count', { count: guestPartiesCount }) }}
                    </p>
                </div>

                <div class="flex flex-1 flex-col gap-3 lg:max-w-3xl">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-neutral-600">
                        <label class="inline-flex items-center gap-3 font-medium text-neutral-700">
                            <Checkbox
                                :checked="allGuestsSelected"
                                @update:checked="emit('toggle-select-all', Boolean($event))"
                            />
                            {{ t('guests.actions.select_visible') }}
                        </label>
                        <span>{{ t('guests.selection.selected', { count: selectedGuestIds.length }) }}</span>
                        <span>{{ t('guests.selection.shown', { count: filteredGuestParties.length }) }}</span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
                        <div class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                            <Input
                                :model-value="guestSearch"
                                class="pl-9"
                                :placeholder="t('guests.actions.search_invitee')"
                                @update:model-value="emit('update:guestSearch', String($event))"
                            />
                        </div>
                        <NativeSelect v-model="guestFilterModel">
                            <NativeSelectOption value="all">{{ t('guests.filters.all') }}</NativeSelectOption>
                            <NativeSelectOption value="needing_reply">{{ t('guests.filters.needing_reply') }}</NativeSelectOption>
                            <NativeSelectOption value="accepted">{{ t('guests.filters.accepted') }}</NativeSelectOption>
                            <NativeSelectOption value="declined">{{ t('guests.filters.declined') }}</NativeSelectOption>
                            <NativeSelectOption value="present">{{ t('guests.filters.present') }}</NativeSelectOption>
                            <NativeSelectOption value="absent">{{ t('guests.filters.absent') }}</NativeSelectOption>
                            <NativeSelectOption value="not_sent">{{ t('guests.filters.not_sent') }}</NativeSelectOption>
                            <NativeSelectOption value="responded">{{ t('guests.filters.responded') }}</NativeSelectOption>
                            <NativeSelectOption value="no_gift">{{ t('guests.filters.no_gift') }}</NativeSelectOption>
                        </NativeSelect>
                    </div>
                </div>
            </div>

            <div v-if="guestPartiesCount === 0" class="px-5 py-12 text-center">
                <Users class="mx-auto size-10 text-neutral-300" />
                <h3 class="mt-4 text-lg font-semibold text-neutral-950">
                    {{ t('guests.empty.invitees_title') }}
                </h3>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-neutral-600">
                    {{ t('guests.empty.invitees_description') }}
                </p>
                <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                    <Button class="rounded-full px-5" @click="emit('open-create')">
                        {{ t('guests.actions.add_first_invitee') }}
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="emit('open-import')">
                        {{ t('guests.actions.paste_or_upload') }}
                    </Button>
                </div>
            </div>

            <div v-else class="divide-y divide-neutral-200">
                <div v-if="selectedGuestIds.length > 0" class="flex flex-wrap items-center gap-2 border-b border-neutral-200 bg-neutral-50/70 px-5 py-3">
                    <span class="mr-1 text-sm font-medium text-neutral-700">
                        {{ t('guests.selection.selected', { count: selectedGuestIds.length }) }}
                    </span>
                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        :disabled="selectedGuestIds.length === 0 || invitationBulkProcessing"
                        @click="emit('share-selected')"
                    >
                        <SendHorizontal class="mr-2 size-4" />
                        {{ t('guests.actions.share_selected') }}
                    </Button>
                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        :disabled="selectedGuestIds.length === 0"
                        @click="emit('copy-selected')"
                    >
                        <Copy class="mr-2 size-4" />
                        {{ t('guests.actions.copy_selected') }}
                    </Button>
                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        :disabled="(selectedGuestIds.length > 0 && selectedPendingCount === 0) || invitationBulkProcessing"
                        @click="emit('remind-selected')"
                    >
                        <Clock3 class="mr-2 size-4" />
                        {{ t('guests.actions.remind_pending') }}
                    </Button>
                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        :disabled="selectedGuestIds.length === 0 || invitationBulkProcessing"
                        @click="emit('mark-selected-delivered')"
                    >
                        <CheckCircle2 class="mr-2 size-4" />
                        {{ t('guests.actions.mark_delivered') }}
                    </Button>
                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        :disabled="selectedGuestIds.length === 0 || invitationBulkProcessing"
                        @click="emit('mark-selected-sent')"
                    >
                        <SendHorizontal class="mr-2 size-4" />
                        {{ t('guests.actions.mark_sent') }}
                    </Button>
                    <Button
                        variant="ghost"
                        class="rounded-full px-4"
                        :disabled="selectedGuestIds.length === 0"
                        @click="emit('clear-selection')"
                    >
                        {{ t('guests.actions.clear') }}
                    </Button>
                </div>

                <div
                    v-if="filteredGuestParties.length === 0"
                    class="px-5 py-12 text-center"
                >
                    <Users class="mx-auto size-10 text-neutral-300" />
                    <h3 class="mt-4 text-lg font-semibold text-neutral-950">
                        {{ t('guests.empty.filtered_title') }}
                    </h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-neutral-600">
                        {{ t('guests.empty.filtered_description') }}
                    </p>
                </div>

                <div
                    v-for="party in filteredGuestParties"
                    :key="party.id"
                    class="px-5 py-4"
                >
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-2">
                            <label class="inline-flex items-center gap-3 text-sm font-medium text-neutral-500">
                                <Checkbox
                                    :checked="selectedGuestIds.includes(party.id)"
                                    @update:checked="emit('toggle-select-guest', party.id, Boolean($event))"
                                />
                                {{ t('guests.actions.select') }}
                            </label>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold text-neutral-950">
                                    {{ party.name }}
                                </h3>
                                <Badge :class="attendanceBadgeClass(party.attendanceStatus)">
                                    {{ attendanceLabel(party.attendanceStatus) }}
                                </Badge>
                                <Badge variant="outline" class="border-neutral-200 bg-neutral-50 text-neutral-700">
                                    {{ invitationLabel(party.invitationStatus) }}
                                </Badge>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-600">
                                <span class="inline-flex items-center gap-1.5">
                                    <Phone class="size-4" />
                                    {{ party.phone || t('guests.shared.no_phone') }}
                                </span>
                                <span>{{ party.tableName || t('guests.shared.no_table') }}</span>
                                <span>{{ t('guests.labels.invited_count', { count: party.invitedAttendeesCount }) }}</span>
                                <span v-if="party.confirmedAttendeesCount !== null">{{ t('guests.labels.confirmed_count', { count: party.confirmedAttendeesCount }) }}</span>
                                <span v-if="party.actualAttendeesCount !== null">{{ t('guests.labels.came_count', { count: party.actualAttendeesCount }) }}</span>
                                <span>{{ giftLabel(party) }}</span>
                                <span v-if="party.reminderCount > 0">{{ t('guests.labels.reminded_count', { count: party.reminderCount }) }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="emit('share-guest', party)"
                            >
                                <SendHorizontal class="mr-2 size-4" />
                                {{ t('guests.actions.share') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="party.attendanceStatus !== 'pending'"
                                @click="emit('remind-guest', party)"
                            >
                                <Clock3 class="mr-2 size-4" />
                                {{ t('guests.actions.remind') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="emit('edit-guest', party)"
                            >
                                <Pencil class="mr-2 size-4" />
                                {{ t('guests.actions.edit') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="emit('toggle-details', party.id)"
                            >
                                {{
                                    expandedGuestPartyId === party.id
                                        ? t('guests.actions.hide_details')
                                        : t('guests.actions.details')
                                }}
                            </Button>
                        </div>
                    </div>

                    <div
                        v-if="expandedGuestPartyId === party.id"
                        class="mt-4 space-y-4 border-t border-neutral-200 pt-4"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2 text-sm text-neutral-600">
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.opens') }}:</span> {{ party.invitationOpenCount }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.first_open') }}:</span> {{ formatDateTime(party.invitationFirstOpenedAt) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.last_open') }}:</span> {{ formatDateTime(party.invitationLastOpenedAt) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.last_ip') }}:</span> {{ party.invitationLastOpenedIp || t('guests.shared.not_captured_yet') }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.delivery') }}:</span> {{ deliveryChannelLabel(party.invitationDeliveryChannel) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.delivered') }}:</span> {{ formatDateTime(party.invitationDeliveredAt) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.last_reminder') }}:</span> {{ formatDateTime(party.lastReminderAt) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.responded') }}:</span> {{ formatDateTime(party.respondedAt) }}</p>
                            </div>

                            <div class="space-y-2 text-sm text-neutral-600">
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.event_day') }}:</span> {{ actualAttendanceLabel(party.actualAttendanceStatus) }}</p>
                                <p><span class="font-medium text-neutral-900">{{ t('guests.details.count') }}:</span> {{ party.actualAttendeesCount ?? t('guests.status.not_recorded') }}</p>
                                <p v-if="party.notes"><span class="font-medium text-neutral-900">{{ t('guests.fields.notes') }}:</span> {{ party.notes }}</p>
                                <p v-if="party.guestNames"><span class="font-medium text-neutral-900">{{ t('guests.details.guest_names') }}:</span> {{ party.guestNames }}</p>
                                <p v-if="mealPreferenceLabel(party.mealPreference)"><span class="font-medium text-neutral-900">{{ t('guests.details.meal') }}:</span> {{ mealPreferenceLabel(party.mealPreference) }}</p>
                                <p v-if="party.responseNotes"><span class="font-medium text-neutral-900">{{ t('guests.details.rsvp_note') }}:</span> {{ party.responseNotes }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.details.history_title') }}
                            </p>
                            <div v-if="party.invitationHistory.length > 0" class="divide-y divide-neutral-200 rounded-2xl border border-neutral-200">
                                <div
                                    v-for="activity in party.invitationHistory"
                                    :key="`${activity.type}-${activity.createdAt}-${activity.deliveryChannel}`"
                                    class="flex items-start justify-between gap-3 px-3 py-2.5 text-sm"
                                >
                                    <div>
                                        <p class="font-medium text-neutral-900">
                                            {{ invitationHistoryLabel(activity) }}
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            {{ deliveryChannelLabel(activity.deliveryChannel) }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-neutral-500">
                                        {{ formatDateTime(activity.createdAt) }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="mt-3 text-sm text-neutral-500">
                                {{ t('guests.details.no_activity') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="emit('copy-link', party.inviteUrl, `${party.name} invite link`)"
                            >
                                <Copy class="mr-2 size-4" />
                                {{ t('guests.actions.copy_link') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="emit('open-invite', party.inviteUrl)"
                            >
                                <ExternalLink class="mr-2 size-4" />
                                {{ t('guests.actions.open_invite') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="invitationBulkProcessing"
                                @click="emit('mark-delivered', party)"
                            >
                                <CheckCircle2 class="mr-2 size-4" />
                                {{ t('guests.actions.delivered') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="invitationBulkProcessing"
                                @click="emit('mark-sent', party)"
                            >
                                <SendHorizontal class="mr-2 size-4" />
                                {{ t('guests.actions.mark_sent') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="party.attendanceStatus !== 'pending' || invitationBulkProcessing"
                                @click="emit('remind-guest', party)"
                            >
                                <Clock3 class="mr-2 size-4" />
                                {{ t('guests.actions.remind') }}
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4 text-rose-600 hover:text-rose-700"
                                @click="emit('confirm-delete', party)"
                            >
                                <Trash2 class="mr-2 size-4" />
                                {{ t('guests.actions.delete') }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</template>
