<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
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

type StorageQuota = {
    limitBytes: number;
    usedBytes: number;
    freeBytes: number;
    usagePercent: number;
    isNearLimit: boolean;
    isOverLimit: boolean;
};

type AdminLinks = {
    overview: string;
    users: string;
    events: string;
    billing: string;
    dashboard: string;
};

type RecentUser = {
    id: number;
    name: string;
    email: string;
    isSuperAdmin: boolean;
    eventCount: number;
    unpaidEventCount: number;
    lockedEventCount: number;
    latestEventName: string | null;
    latestEventPlan: string | null;
    latestEventUrl: string | null;
    createdAt: string | null;
    storage: StorageQuota;
};

type RecentEvent = {
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
    guestCount: number;
    assetCount: number;
    storage: StorageQuota;
    links: {
        event: string;
        media: string;
        settings: string;
    };
};

type BillingRow = {
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
    billingNote: string | null;
    storage: StorageQuota;
    links: {
        event: string;
        settings: string;
    };
};

type Tone = 'dark' | 'emerald' | 'amber' | 'sky' | 'violet' | 'zinc' | 'rose';

const props = defineProps<{
    summary: Summary;
    adminLinks: AdminLinks;
    recentUsers: RecentUser[];
    recentEvents: RecentEvent[];
    billingQueue: BillingRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: props.adminLinks.overview,
    },
];

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'No timestamp';
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
</script>

<template>
    <Head title="Admin Overview" />

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
                                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Platform overview</h1>
                                <p class="max-w-2xl text-sm leading-6 text-white/72 md:text-base">
                                    One place to watch the whole app: who signed up, what events are active, and which accounts need billing attention.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="adminLinks.users" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Users
                                </Link>
                                <Link :href="adminLinks.events" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Events
                                </Link>
                                <Link :href="adminLinks.billing" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Billing
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-5 xl:p-6">
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total users</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalUsers }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Business accounts</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.businessCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalEvents }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total uploads</p>
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
                            <p class="text-sm font-medium text-zinc-500">Overdue events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.overdueEventCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Locked events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.lockedEventCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Storage provisioned</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalAllocatedStorageBytes) }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Storage used</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalUsedStorageBytes) }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Storage free</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalFreeStorageBytes) }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Cleanup candidates</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.storageCleanupCandidateCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-dashed border-[#d8c4a6] bg-[linear-gradient(135deg,#f9ecd7_0%,#f6efe4_100%)] p-4">
                            <p class="text-sm font-medium text-[#7b5a2c]">Control surface</p>
                            <p class="mt-2 text-lg font-semibold text-[#171411]">You can jump into any event settings to change billing or review media.</p>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[1.05fr_1fr]">
                    <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Recent users</p>
                                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Latest accounts joining the platform</h2>
                            </div>
                            <Link :href="adminLinks.users" class="inline-flex items-center gap-2 text-sm font-semibold text-[#171411]">
                                View all
                                <ArrowRight class="size-4" />
                            </Link>
                        </div>

                        <div class="mt-5 grid gap-3">
                            <article v-for="user in recentUsers" :key="user.id" class="rounded-[1.35rem] border border-black/6 bg-[#fcfbf8] p-4">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-lg font-semibold text-[#171411]">{{ user.name }}</h3>
                                            <span v-if="user.isSuperAdmin" class="inline-flex rounded-full bg-[#171411] px-2.5 py-1 text-xs font-semibold text-white">
                                                Super admin
                                            </span>
                                        </div>
                                        <p class="text-sm text-zinc-600">{{ user.email }}</p>
                                    </div>
                                    <div class="grid gap-2 text-sm text-zinc-600 sm:grid-cols-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Events</p>
                                            <p class="mt-1 font-semibold text-[#171411]">{{ user.eventCount }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Unpaid</p>
                                            <p class="mt-1 font-semibold text-[#171411]">{{ user.unpaidEventCount }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Locked</p>
                                            <p class="mt-1 font-semibold text-[#171411]">{{ user.lockedEventCount }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                                    <span>Joined {{ formatDateTime(user.createdAt) }}</span>
                                    <span v-if="user.latestEventName">·</span>
                                    <Link v-if="user.latestEventUrl" :href="user.latestEventUrl" class="font-medium text-[#171411] hover:text-[#7b5a2c]">
                                        Latest event: {{ user.latestEventName }}<span v-if="user.latestEventPlan"> · {{ user.latestEventPlan }}</span>
                                    </Link>
                                </div>
                                <div class="mt-3 rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                        <span class="font-medium text-[#171411]">Storage</span>
                                        <span class="text-zinc-500">{{ formatBytes(user.storage.usedBytes) }} / {{ formatBytes(user.storage.limitBytes) }}</span>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-200">
                                        <div
                                            class="h-full rounded-full"
                                            :class="
                                                user.storage.isOverLimit
                                                    ? 'bg-rose-500'
                                                    : user.storage.isNearLimit
                                                      ? 'bg-amber-500'
                                                      : 'bg-emerald-500'
                                            "
                                            :style="{ width: `${Math.min(user.storage.usagePercent, 100)}%` }"
                                        />
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>

                    <div class="grid gap-6">
                        <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Recent events</p>
                                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Newest workspaces created by customers</h2>
                                </div>
                                <Link :href="adminLinks.events" class="inline-flex items-center gap-2 text-sm font-semibold text-[#171411]">
                                    View all
                                    <ArrowRight class="size-4" />
                                </Link>
                            </div>

                            <div class="mt-5 grid gap-3">
                                <article v-for="event in recentEvents" :key="event.id" class="rounded-[1.35rem] border border-black/6 bg-[#fcfbf8] p-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(event.statusTone)">{{ event.statusLabel }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(event.billingTone)">{{ event.billingLabel }}</span>
                                    </div>
                                    <h3 class="mt-3 text-lg font-semibold text-[#171411]">{{ event.name }}</h3>
                                    <p class="mt-1 text-sm text-zinc-600">{{ event.ownerName }} · {{ event.ownerEmail }}</p>
                                    <p class="mt-2 text-sm text-zinc-500">{{ event.planName }} · {{ event.planPriceLabel }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2 text-sm text-zinc-500">
                                        <span>{{ event.assetCount }} uploads</span>
                                        <span>·</span>
                                        <span>{{ event.guestCount }} guests</span>
                                    </div>
                                    <div class="mt-3 rounded-2xl border border-black/6 bg-white px-4 py-3 text-sm text-zinc-600">
                                        <p><span class="font-medium text-[#171411]">Storage:</span> {{ formatBytes(event.storage.usedBytes) }} / {{ formatBytes(event.storage.limitBytes) }}</p>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Billing queue</p>
                                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Events that need your attention</h2>
                                </div>
                                <Link :href="adminLinks.billing" class="inline-flex items-center gap-2 text-sm font-semibold text-[#171411]">
                                    Open billing
                                    <ArrowRight class="size-4" />
                                </Link>
                            </div>

                            <div class="mt-5 grid gap-3">
                                <article v-for="row in billingQueue" :key="row.id" class="rounded-[1.35rem] border border-black/6 bg-[#fcfbf8] p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-semibold text-[#171411]">{{ row.name }}</h3>
                                            <p class="text-sm text-zinc-600">{{ row.ownerEmail }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(row.queueTone)">
                                            {{ row.queueLabel }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2 text-sm text-zinc-500">
                                        <span>{{ row.planName }}</span>
                                        <span>·</span>
                                        <span>{{ row.planPriceLabel }}</span>
                                        <span>·</span>
                                        <span>Due {{ formatDateTime(row.paymentDueAt) }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-zinc-600">
                                        Storage {{ formatBytes(row.storage.usedBytes) }} used
                                    </p>
                                    <p v-if="row.billingNote" class="mt-2 text-sm text-zinc-600">
                                        {{ row.billingNote }}
                                    </p>
                                </article>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
