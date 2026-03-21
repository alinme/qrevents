<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArchiveX, ArrowUpRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Summary = {
    totalUsers: number;
    businessCount: number;
    totalEvents: number;
    totalUploads: number;
    pendingModerationCount: number;
    unpaidEventCount: number;
    overdueEventCount: number;
    lockedEventCount: number;
    cleanupPendingReviewCount: number;
    cleanupApprovedCount: number;
    cleanupProtectedCount: number;
    cleanupCompletedCount: number;
    totalAllocatedStorageBytes: number;
    totalUsedStorageBytes: number;
    totalFreeStorageBytes: number;
    storageCleanupCandidateCount: number;
};

type AdminLinks = {
    overview: string;
    users: string;
    events: string;
    billing: string;
    cleanup: string;
    dashboard: string;
};

type Tone = 'dark' | 'emerald' | 'amber' | 'sky' | 'violet' | 'zinc' | 'rose';

type StorageQuota = {
    limitBytes: number;
    usedBytes: number;
    freeBytes: number;
    usagePercent: number;
    isNearLimit: boolean;
    isOverLimit: boolean;
};

type CleanupRow = {
    id: number;
    name: string;
    ownerName: string;
    ownerEmail: string;
    planName: string;
    planPriceLabel: string;
    queueLabel: string;
    queueTone: Tone;
    paymentDueAt: string | null;
    paidAt: string | null;
    status: string;
    reviewStateRaw: string | null;
    reviewedAt: string | null;
    assetCount: number;
    hasExportArchive: boolean;
    canPurgeMedia: boolean;
    billingNote: string | null;
    storage: StorageQuota;
    cleanup: {
        stateCode: string;
        stateLabel: string;
        stateTone: Tone;
        hint: string;
        candidateAt: string | null;
        canRunCleanup: boolean;
    };
    links: {
        event: string;
        settings: string;
        cleanup: string;
        cleanupReview: string;
    };
};

const props = defineProps<{
    summary: Summary;
    adminLinks: AdminLinks;
    cleanupEvents: CleanupRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: props.adminLinks.overview,
    },
    {
        title: 'Cleanup',
        href: props.adminLinks.cleanup,
    },
];

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatBytes = (bytes: number): string => {
    if (bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / 1024 ** exponent;

    return `${value.toFixed(value >= 10 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
};

const badgeClass = (tone: Tone): string => {
    switch (tone) {
        case 'dark':
            return 'bg-[#171411] text-white';
        case 'emerald':
            return 'bg-emerald-100 text-emerald-800';
        case 'amber':
            return 'bg-amber-100 text-amber-800';
        case 'sky':
            return 'bg-sky-100 text-sky-800';
        case 'violet':
            return 'bg-violet-100 text-violet-800';
        case 'rose':
            return 'bg-rose-100 text-rose-800';
        default:
            return 'bg-zinc-100 text-zinc-700';
    }
};

const search = ref('');
const stateFilter = ref<'all' | 'review' | 'approved' | 'protected' | 'completed' | 'cooldown'>('all');
const cleanupDialogOpen = ref(false);
const selectedCleanupEvent = ref<CleanupRow | null>(null);
const selectedCleanupAction = ref<'clear_export' | 'purge_media'>('clear_export');

const cleanupForm = useForm({
    action: 'clear_export' as 'clear_export' | 'purge_media',
    confirmation_name: '',
});

const reviewForm = useForm({
    review_state: 'approved' as 'approved' | 'protected' | 'clear',
});

const filteredCleanupEvents = computed(() => {
    const term = search.value.trim().toLowerCase();

    return props.cleanupEvents.filter((event) => {
        const matchesTerm = term === ''
            || [event.name, event.ownerName, event.ownerEmail, event.planName, event.billingNote ?? '']
                .some((value) => value.toLowerCase().includes(term));
        const matchesState = stateFilter.value === 'all' || event.cleanup.stateCode === stateFilter.value;

        return matchesTerm && matchesState;
    });
});

const cleanupDialogTitle = computed(() => selectedCleanupAction.value === 'purge_media' ? 'Purge event media' : 'Clear export archive');

const cleanupDialogDescription = computed(() => {
    if (!selectedCleanupEvent.value) {
        return '';
    }

    if (selectedCleanupAction.value === 'purge_media') {
        return `This will permanently delete ${selectedCleanupEvent.value.assetCount} media item(s), reclaim storage, and clear any stored export archive for ${selectedCleanupEvent.value.name}.`;
    }

    return `This will remove the stored export ZIP for ${selectedCleanupEvent.value.name} without deleting guest media.`;
});

const openCleanupDialog = (event: CleanupRow, action: 'clear_export' | 'purge_media'): void => {
    selectedCleanupEvent.value = event;
    selectedCleanupAction.value = action;
    cleanupForm.action = action;
    cleanupForm.confirmation_name = '';
    cleanupForm.clearErrors();
    cleanupDialogOpen.value = true;
};

const closeCleanupDialog = (): void => {
    cleanupDialogOpen.value = false;
    selectedCleanupEvent.value = null;
    cleanupForm.reset();
    cleanupForm.clearErrors();
};

const runCleanup = (): void => {
    if (!selectedCleanupEvent.value) {
        return;
    }

    cleanupForm.action = selectedCleanupAction.value;
    cleanupForm.post(selectedCleanupEvent.value.links.cleanup, {
        preserveScroll: true,
        onSuccess: () => {
            closeCleanupDialog();
        },
    });
};

const updateCleanupReview = (event: CleanupRow, reviewState: 'approved' | 'protected' | 'clear'): void => {
    reviewForm.review_state = reviewState;
    reviewForm.post(event.links.cleanupReview, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Admin Cleanup" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_85%_10%,_rgba(251,191,36,0.14),_transparent_22%)]">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
                <section class="overflow-hidden rounded-[2rem] border border-black/5 bg-white shadow-sm">
                    <div class="border-b border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2d251f_46%,#5f533f_100%)] px-6 py-8 text-white md:px-8">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div class="space-y-2">
                                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white/80">
                                    Super admin
                                </div>
                                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Cleanup operations</h1>
                                <p class="max-w-2xl text-sm leading-6 text-white/72 md:text-base">
                                    Review cleanup candidates, protect exceptions, approve destructive cleanup, and keep a light history of what already ran.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="adminLinks.billing" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Billing queue
                                </Link>
                                <Link :href="adminLinks.events" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Events
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-5 xl:p-6">
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Needs review</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.cleanupPendingReviewCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Approved</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.cleanupApprovedCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Protected</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.cleanupProtectedCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Completed</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.cleanupCompletedCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Storage at risk</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalUsedStorageBytes) }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Queue</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Cleanup state tracker</h2>
                        </div>
                        <div class="flex flex-col gap-3 md:flex-row">
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Search by event, owner, email, or note"
                                class="h-11 rounded-full border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                            >
                            <select
                                v-model="stateFilter"
                                class="h-11 rounded-full border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                            >
                                <option value="all">All states</option>
                                <option value="review">Needs review</option>
                                <option value="approved">Approved</option>
                                <option value="protected">Protected</option>
                                <option value="completed">Completed</option>
                                <option value="cooldown">Cooldown</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="filteredCleanupEvents.length === 0" class="py-12 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]">
                                <ArchiveX class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-[#171411]">No matching cleanup entries</h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Adjust the search or state filter to widen the list.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="mt-5 grid gap-4">
                        <article
                            v-for="event in filteredCleanupEvents"
                            :key="event.id"
                            class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5"
                        >
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(event.queueTone)">
                                            {{ event.queueLabel }}
                                        </span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(event.cleanup.stateTone)">
                                            {{ event.cleanup.stateLabel }}
                                        </span>
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="text-2xl font-semibold tracking-tight text-[#171411]">{{ event.name }}</h3>
                                        <p class="text-sm text-zinc-600">{{ event.ownerName }} · {{ event.ownerEmail }}</p>
                                    </div>
                                    <p class="text-sm text-zinc-500">{{ event.planName }} · {{ event.planPriceLabel }}</p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Assets</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ event.assetCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Storage used</p>
                                        <p class="mt-1 text-sm font-semibold text-[#171411]">{{ formatBytes(event.storage.usedBytes) }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Candidate after</p>
                                        <p class="mt-1 text-sm font-semibold text-[#171411]">{{ formatDateTime(event.cleanup.candidateAt) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-dashed border-[#decfb6] bg-[#fbf6ee] px-4 py-3 text-sm text-zinc-700">
                                <p class="font-medium text-[#171411]">Cleanup policy</p>
                                <p class="mt-1">{{ event.cleanup.hint }}</p>
                                <p v-if="event.reviewedAt" class="mt-1 text-zinc-500">
                                    Last reviewed {{ formatDateTime(event.reviewedAt) }}
                                </p>
                            </div>

                            <div class="mt-4 rounded-2xl border border-black/6 bg-white px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <span class="font-medium text-[#171411]">Storage pressure</span>
                                    <span class="text-zinc-500">{{ event.storage.usagePercent }}%</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-200">
                                    <div
                                        class="h-full rounded-full"
                                        :class="
                                            event.storage.isOverLimit
                                                ? 'bg-rose-500'
                                                : event.storage.isNearLimit
                                                  ? 'bg-amber-500'
                                                  : 'bg-emerald-500'
                                        "
                                        :style="{ width: `${Math.min(event.storage.usagePercent, 100)}%` }"
                                    />
                                </div>
                            </div>

                            <p v-if="event.billingNote" class="mt-4 text-sm text-zinc-600">
                                {{ event.billingNote }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <Button
                                    v-if="event.cleanup.stateCode === 'review'"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100"
                                    :disabled="reviewForm.processing"
                                    @click="updateCleanupReview(event, 'approved')"
                                >
                                    Approve cleanup
                                </Button>
                                <Button
                                    v-if="event.cleanup.stateCode === 'review' || event.cleanup.stateCode === 'approved'"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100"
                                    :disabled="reviewForm.processing"
                                    @click="updateCleanupReview(event, 'protected')"
                                >
                                    Protect
                                </Button>
                                <Button
                                    v-if="event.cleanup.stateCode === 'protected' || event.cleanup.stateCode === 'approved' || event.cleanup.stateCode === 'completed'"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-black/10 bg-white text-[#171411] hover:bg-[#f7f1e7]"
                                    :disabled="reviewForm.processing"
                                    @click="updateCleanupReview(event, 'clear')"
                                >
                                    Clear review
                                </Button>
                                <Button
                                    v-if="event.hasExportArchive"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-black/10 bg-white text-[#171411] hover:bg-[#f7f1e7]"
                                    @click="openCleanupDialog(event, 'clear_export')"
                                >
                                    Clear export archive
                                </Button>
                                <Button
                                    v-if="event.canPurgeMedia"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100"
                                    @click="openCleanupDialog(event, 'purge_media')"
                                >
                                    Purge media
                                </Button>
                                <Link :href="event.links.event" class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-[#171411] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#2b241d]">
                                    Open event
                                    <ArrowUpRight class="size-4" />
                                </Link>
                                <Link :href="event.links.settings" class="rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#f7f1e7]">
                                    Settings
                                </Link>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog :open="cleanupDialogOpen" @update:open="(value) => (value ? null : closeCleanupDialog())">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ cleanupDialogTitle }}</DialogTitle>
                <DialogDescription>
                    {{ cleanupDialogDescription }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="selectedCleanupEvent" class="space-y-4">
                <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4 text-sm text-zinc-700">
                    <p><span class="font-medium text-[#171411]">Event:</span> {{ selectedCleanupEvent.name }}</p>
                    <p class="mt-1"><span class="font-medium text-[#171411]">Owner:</span> {{ selectedCleanupEvent.ownerEmail }}</p>
                    <p class="mt-1"><span class="font-medium text-[#171411]">Cleanup state:</span> {{ selectedCleanupEvent.cleanup.stateLabel }}</p>
                    <p class="mt-1"><span class="font-medium text-[#171411]">Assets:</span> {{ selectedCleanupEvent.assetCount }}</p>
                    <p class="mt-1"><span class="font-medium text-[#171411]">Storage used:</span> {{ formatBytes(selectedCleanupEvent.storage.usedBytes) }}</p>
                </div>

                <div class="space-y-2">
                    <label for="confirmation_name" class="text-sm font-medium text-[#171411]">
                        Type the exact event name to confirm
                    </label>
                    <Input
                        id="confirmation_name"
                        v-model="cleanupForm.confirmation_name"
                        :placeholder="selectedCleanupEvent.name"
                    />
                    <p v-if="cleanupForm.errors.confirmation_name" class="text-sm text-rose-600">
                        {{ cleanupForm.errors.confirmation_name }}
                    </p>
                    <p v-if="cleanupForm.errors.action" class="text-sm text-rose-600">
                        {{ cleanupForm.errors.action }}
                    </p>
                </div>
            </div>

            <DialogFooter class="gap-2 sm:justify-end">
                <Button type="button" variant="outline" @click="closeCleanupDialog">
                    Cancel
                </Button>
                <Button
                    type="button"
                    :disabled="cleanupForm.processing || !selectedCleanupEvent"
                    :class="selectedCleanupAction === 'purge_media' ? 'bg-rose-600 text-white hover:bg-rose-700' : ''"
                    @click="runCleanup"
                >
                    {{ selectedCleanupAction === 'purge_media' ? 'Confirm purge' : 'Clear export' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
