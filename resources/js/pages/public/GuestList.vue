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

type GuestListParty = {
    id: number;
    name: string;
    phone: string | null;
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
    guestParties: GuestListParty[];
}>();

const search = ref('');
const quickSavingGuestId = ref<number | null>(null);
const detailsDialogOpen = ref(false);
const detailsParty = ref<GuestListParty | null>(null);

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

const actualAttendanceRowClass = (status: GuestListParty['actualAttendanceStatus']): string => {
    if (status === 'present') {
        return 'bg-emerald-50/80';
    }

    if (status === 'absent') {
        return 'bg-rose-50/70';
    }

    return '';
};

const openDetails = (party: GuestListParty): void => {
    detailsParty.value = party;
    detailsDialogOpen.value = true;
};

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
                    Search an invitee, check where they are seated, and mark them as arrived.
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
                        :class="[
                            'flex flex-col gap-3 px-1 py-3 transition-colors md:flex-row md:items-center md:justify-between',
                            actualAttendanceRowClass(party.actualAttendanceStatus),
                        ]"
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

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="openDetails(party)"
                            >
                                <Eye class="mr-2 size-4" />
                                Details
                            </Button>
                            <Button
                                :variant="party.actualAttendanceStatus === 'present' ? 'default' : 'outline'"
                                :class="party.actualAttendanceStatus === 'present' ? 'rounded-full bg-emerald-600 px-4 text-white hover:bg-emerald-700' : 'rounded-full px-4'"
                                :disabled="quickSavingGuestId === party.id"
                                @click="updateAttendance(party, 'present')"
                            >
                                <CheckCircle2 class="mr-2 size-4" />
                                Came
                            </Button>
                            <Button
                                :variant="party.actualAttendanceStatus === 'absent' ? 'default' : 'outline'"
                                :class="party.actualAttendanceStatus === 'absent' ? 'rounded-full bg-rose-600 px-4 text-white hover:bg-rose-700' : 'rounded-full px-4'"
                                :disabled="quickSavingGuestId === party.id"
                                @click="updateAttendance(party, 'absent')"
                            >
                                <XCircle class="mr-2 size-4" />
                                No show
                            </Button>
                            <Button
                                variant="ghost"
                                class="rounded-full px-4"
                                :disabled="quickSavingGuestId === party.id"
                                @click="updateAttendance(party, 'unknown')"
                            >
                                <Clock3 class="mr-2 size-4" />
                                Reset
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
                </div>
            </DialogContent>
        </Dialog>
    </main>
</template>
