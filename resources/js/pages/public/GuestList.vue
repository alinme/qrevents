<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock3, Search, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

                <div class="mt-4 divide-y divide-stone-200 overflow-hidden rounded-2xl border border-stone-200">
                    <div
                        v-for="party in filteredGuestParties"
                        :key="party.id"
                        class="flex flex-col gap-3 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="min-w-0 space-y-1">
                            <p class="truncate text-base font-semibold text-stone-950">
                                {{ party.name }}
                            </p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-stone-600">
                                <span>{{ party.phone || 'No phone saved' }}</span>
                                <span>{{ party.tableName || 'No table set' }}</span>
                                <span>{{ attendanceLabel(party.actualAttendanceStatus) }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="quickSavingGuestId === party.id"
                                @click="updateAttendance(party, 'present')"
                            >
                                <CheckCircle2 class="mr-2 size-4" />
                                Came
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
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
    </main>
</template>
