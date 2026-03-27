<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock3, Eye, Search, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    NativeSelect,
    NativeSelectOption,
} from '@/components/ui/native-select';

type GuestListParty = {
    id: number;
    name: string;
    phone: string | null;
    eventTableId: number | null;
    tableName: string | null;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number | null;
    actualAttendanceStatus: 'unknown' | 'present' | 'absent';
    actualAttendeesCount: number | null;
    notes: string | null;
    updateUrl: string;
};

const props = defineProps<{
    currentEvent: {
        id: number;
        name: string;
    };
    guestList: {
        searchPlaceholder: string;
        publicUrl: string;
    };
    eventTables: Array<{
        id: number;
        name: string;
        remainingSeats: number;
        isFull: boolean;
    }>;
    guestParties: GuestListParty[];
}>();

const search = ref('');
const quickSavingGuestId = ref<number | null>(null);
const detailsDialogOpen = ref(false);
const detailsParty = ref<GuestListParty | null>(null);
const selectedTableId = ref('');

const filteredGuestParties = computed(() => {
    const needle = search.value.trim().toLowerCase();

    return props.guestParties.filter((party) => needle === ''
        || party.name.toLowerCase().includes(needle)
        || (party.phone ?? '').toLowerCase().includes(needle)
        || (party.tableName ?? '').toLowerCase().includes(needle));
});

const attendanceLabel = (status: GuestListParty['actualAttendanceStatus']): string => {
    return {
        unknown: 'Not checked in',
        present: 'Checked in',
        absent: 'Marked absent',
    }[status];
};

const openDetails = (party: GuestListParty): void => {
    detailsParty.value = party;
    selectedTableId.value = party.eventTableId?.toString() ?? '';
    detailsDialogOpen.value = true;
};

const selectableTables = computed(() => props.eventTables.map((table) => ({
    ...table,
    selectable: !table.isFull || table.id === Number(selectedTableId.value || 0),
})));

const updateAttendance = (
    party: GuestListParty,
    status: GuestListParty['actualAttendanceStatus'],
): void => {
    if (quickSavingGuestId.value !== null) {
        return;
    }

    quickSavingGuestId.value = party.id;

    router.patch(
        party.updateUrl,
        {
            actual_attendance_status: status,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                quickSavingGuestId.value = null;
            },
        },
    );
};

const saveTableAssignment = (): void => {
    if (!detailsParty.value || quickSavingGuestId.value !== null) {
        return;
    }

    quickSavingGuestId.value = detailsParty.value.id;

    router.patch(
        detailsParty.value.updateUrl,
        {
            actual_attendance_status: detailsParty.value.actualAttendanceStatus,
            event_table_id: selectedTableId.value === '' ? null : Number(selectedTableId.value),
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                quickSavingGuestId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head :title="`${currentEvent.name} guest list`" />

    <main class="min-h-dvh bg-stone-50 px-4 py-6 text-stone-900 md:px-6">
        <div class="mx-auto max-w-5xl space-y-5">
            <section class="space-y-2">
                <p class="text-xs font-medium uppercase tracking-[0.24em] text-stone-500">
                    Entrance list
                </p>
                <h1 class="text-2xl font-semibold tracking-tight text-stone-950 md:text-3xl">
                    {{ currentEvent.name }}
                </h1>
                <p class="max-w-2xl text-sm text-stone-600 md:text-base">
                    Search an invitee, greet them, check them in, and tell them where to sit.
                </p>
            </section>

            <section class="rounded-3xl border border-stone-200 bg-white p-4 shadow-sm">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-stone-400" />
                    <Input
                        v-model="search"
                        class="h-12 pl-9 text-base"
                        :placeholder="guestList.searchPlaceholder"
                    />
                </div>

                <div class="mt-4 divide-y divide-stone-200 border-t border-stone-200">
                    <div
                        v-for="party in filteredGuestParties"
                        :key="party.id"
                        class="flex flex-col gap-3 px-1 py-3 transition-colors md:flex-row md:items-center md:justify-between"
                    >
                        <div class="min-w-0 space-y-1">
                            <p class="truncate text-base font-semibold text-stone-950">
                                {{ party.name }}
                            </p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-stone-600">
                                <span>{{ party.phone || 'No phone saved' }}</span>
                                <span>{{ party.tableName || 'No table yet' }}</span>
                                <span>{{ attendanceLabel(party.actualAttendanceStatus) }}</span>
                            </div>
                        </div>

                        <div class="inline-flex items-center rounded-full border border-stone-200 bg-stone-50 p-1">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-9 rounded-full text-stone-600 hover:bg-white hover:text-stone-950"
                                title="Details and table"
                                aria-label="Details and table"
                                @click="openDetails(party)"
                            >
                                <Eye class="size-4" />
                                <span class="sr-only">Details and table</span>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                :class="party.actualAttendanceStatus === 'present'
                                    ? 'size-9 rounded-full bg-emerald-600 text-white hover:bg-emerald-700'
                                    : 'size-9 rounded-full text-stone-600 hover:bg-white hover:text-emerald-700'"
                                :disabled="quickSavingGuestId === party.id"
                                title="Mark as came"
                                aria-label="Mark as came"
                                @click="updateAttendance(party, 'present')"
                            >
                                <CheckCircle2 class="size-4" />
                                <span class="sr-only">Came</span>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                :class="party.actualAttendanceStatus === 'absent'
                                    ? 'size-9 rounded-full bg-rose-600 text-white hover:bg-rose-700'
                                    : 'size-9 rounded-full text-stone-600 hover:bg-white hover:text-rose-700'"
                                :disabled="quickSavingGuestId === party.id"
                                title="Mark as no show"
                                aria-label="Mark as no show"
                                @click="updateAttendance(party, 'absent')"
                            >
                                <XCircle class="size-4" />
                                <span class="sr-only">No show</span>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="size-9 rounded-full text-stone-600 hover:bg-white hover:text-stone-950"
                                :disabled="quickSavingGuestId === party.id"
                                title="Reset status"
                                aria-label="Reset status"
                                @click="updateAttendance(party, 'unknown')"
                            >
                                <Clock3 class="size-4" />
                                <span class="sr-only">Reset</span>
                            </Button>
                        </div>
                    </div>

                    <div v-if="filteredGuestParties.length === 0" class="px-4 py-10 text-center text-sm text-stone-500">
                        No invitees match this search.
                    </div>
                </div>
            </section>
        </div>

        <Dialog :open="detailsDialogOpen" @update:open="detailsDialogOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{ detailsParty?.name ?? 'Invitee details' }}
                    </DialogTitle>
                    <DialogDescription>
                        Quick details for the entrance team.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="detailsParty" class="space-y-4 py-2">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Phone</p>
                            <p class="text-sm text-stone-800">{{ detailsParty.phone || 'No phone saved' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Table</p>
                            <p class="text-sm text-stone-800">{{ detailsParty.tableName || 'No table yet' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Invited</p>
                            <p class="text-sm text-stone-800">{{ detailsParty.invitedAttendeesCount }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Confirmed</p>
                            <p class="text-sm text-stone-800">{{ detailsParty.confirmedAttendeesCount ?? 'Not answered yet' }}</p>
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Notes</p>
                            <p class="text-sm text-stone-800">{{ detailsParty.notes || 'No notes added' }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-stone-200 pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Seat them at</p>
                                <p class="mt-1 text-sm text-stone-600">
                                    Choose a table here so the entrance team can greet them and tell them where to sit.
                                </p>
                            </div>
                        </div>

                        <div v-if="props.eventTables.length > 0" class="space-y-2">
                            <div class="grid gap-0 sm:grid-cols-[minmax(0,1fr)_auto]">
                                <NativeSelect
                                    v-model="selectedTableId"
                                    class="h-11 rounded-b-none rounded-t-2xl border-b-0 sm:rounded-l-2xl sm:rounded-r-none sm:rounded-t-none sm:border-b sm:border-r-0"
                                >
                                    <NativeSelectOption value="">No table yet</NativeSelectOption>
                                    <NativeSelectOption
                                        v-for="table in selectableTables"
                                        :key="table.id"
                                        :value="String(table.id)"
                                        :disabled="!table.selectable"
                                    >
                                        {{ table.name }} · {{ table.remainingSeats }} seats left
                                    </NativeSelectOption>
                                </NativeSelect>
                                <Button
                                    class="h-11 rounded-t-none rounded-b-2xl px-4 sm:rounded-l-none sm:rounded-r-2xl sm:rounded-t-none"
                                    :disabled="quickSavingGuestId === detailsParty.id"
                                    @click="saveTableAssignment"
                                >
                                    Save table
                                </Button>
                            </div>
                            <p class="text-xs text-stone-500">
                                Full tables stay locked until seats open up.
                            </p>
                        </div>

                        <div v-else class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            No tables have been created by the host yet, so the entrance team cannot assign seating from this page yet.
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </main>
</template>
