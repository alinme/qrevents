<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowUpRight, FolderKanban } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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

type EventRow = {
    id: number;
    name: string;
    ownerName: string;
    ownerEmail: string;
    planName: string;
    planPriceLabel: string;
    statusLabel: string;
    statusTone: Tone;
    billingLabel: string;
    billingTone: Tone;
    eventDate: string | null;
    createdAt: string | null;
    paymentDueAt: string | null;
    paidAt: string | null;
    guestCount: number;
    assetCount: number;
    isPaid: boolean;
    storage: StorageQuota;
    billingNote: string | null;
    links: {
        event: string;
        media: string;
        settings: string;
    };
};

const props = defineProps<{
    summary: Summary;
    adminLinks: AdminLinks;
    events: EventRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: props.adminLinks.overview,
    },
    {
        title: 'Events',
        href: props.adminLinks.events,
    },
];

const formatDate = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
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

const search = ref('');

const filteredEvents = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (term === '') {
        return props.events;
    }

    return props.events.filter((event) =>
        [event.name, event.ownerName, event.ownerEmail, event.planName].some((value) =>
            value.toLowerCase().includes(term),
        ),
    );
});

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
</script>

<template>
    <Head title="Admin Events" />

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
                                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Events</h1>
                                <p class="max-w-2xl text-sm leading-6 text-white/72 md:text-base">
                                    A direct feed of customer workspaces, including who owns them, how active they are, and whether billing is healthy.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="adminLinks.overview" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Overview
                                </Link>
                                <Link :href="adminLinks.billing" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Billing
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-5 xl:p-6">
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalEvents }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Uploads</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalUploads }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Pending moderation</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.pendingModerationCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Unpaid events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.unpaidEventCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Storage used</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalUsedStorageBytes) }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Directory</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">All customer events</h2>
                        </div>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search by event, owner, email, or plan"
                            class="h-11 rounded-full border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                        >
                    </div>

                    <div v-if="filteredEvents.length === 0" class="py-12 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]">
                                <FolderKanban class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-[#171411]">No matching events</h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Try a different search term.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-4">
                        <article v-for="event in filteredEvents" :key="event.id" class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(event.statusTone)">{{ event.statusLabel }}</span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(event.billingTone)">{{ event.billingLabel }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        <h2 class="text-2xl font-semibold tracking-tight text-[#171411]">{{ event.name }}</h2>
                                        <p class="text-sm text-zinc-600">{{ event.ownerName }} · {{ event.ownerEmail }}</p>
                                    </div>
                                    <p class="text-sm text-zinc-500">{{ event.planName }} · {{ event.planPriceLabel }}</p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Guests</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ event.guestCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Uploads</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ event.assetCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Event date</p>
                                        <p class="mt-1 text-sm font-semibold text-[#171411]">{{ formatDate(event.eventDate) }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Payment due</p>
                                        <p class="mt-1 text-sm font-semibold text-[#171411]">{{ formatDate(event.paymentDueAt) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-black/6 bg-white px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <span class="font-medium text-[#171411]">Storage quota</span>
                                    <span class="text-zinc-500">{{ formatBytes(event.storage.usedBytes) }} / {{ formatBytes(event.storage.limitBytes) }}</span>
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
                                <p class="mt-2 text-sm text-zinc-500">
                                    Free space: {{ formatBytes(event.storage.freeBytes) }}
                                </p>
                            </div>

                            <p v-if="event.billingNote" class="mt-4 text-sm text-zinc-600">
                                {{ event.billingNote }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <Link :href="event.links.event" class="inline-flex items-center gap-2 rounded-full border border-black/10 bg-[#171411] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#2b241d]">
                                    Open event
                                    <ArrowUpRight class="size-4" />
                                </Link>
                                <Link :href="event.links.media" class="rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#f7f1e7]">
                                    Media
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
</template>
