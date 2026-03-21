<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Shield, Users } from 'lucide-vue-next';
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

type UserRow = {
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

const props = defineProps<{
    summary: Summary;
    adminLinks: AdminLinks;
    users: UserRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: props.adminLinks.overview,
    },
    {
        title: 'Users',
        href: props.adminLinks.users,
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

const search = ref('');

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (term === '') {
        return props.users;
    }

    return props.users.filter((user) =>
        [user.name, user.email, user.latestEventName ?? ''].some((value) =>
            value.toLowerCase().includes(term),
        ),
    );
});
</script>

<template>
    <Head title="Admin Users" />

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
                                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Users</h1>
                                <p class="max-w-2xl text-sm leading-6 text-white/72 md:text-base">
                                    Everyone who registered, plus how many events they own and whether their billing needs a follow-up.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="adminLinks.overview" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Overview
                                </Link>
                                <Link :href="adminLinks.events" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Events
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-4 xl:p-6">
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total users</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalUsers }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Business accounts</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.businessCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Locked events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.lockedEventCount }}</p>
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
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Customer accounts</h2>
                        </div>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Search by name, email, or latest event"
                            class="h-11 rounded-full border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                        >
                    </div>

                    <div v-if="filteredUsers.length === 0" class="py-12 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]">
                                <Users class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-[#171411]">No matching users</h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Try a different name or email search.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-4">
                        <article v-for="user in filteredUsers" :key="user.id" class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-2xl font-semibold tracking-tight text-[#171411]">{{ user.name }}</h2>
                                        <span v-if="user.isSuperAdmin" class="inline-flex items-center gap-1 rounded-full bg-[#171411] px-2.5 py-1 text-xs font-semibold text-white">
                                            <Shield class="size-3.5" />
                                            Super admin
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-600">{{ user.email }}</p>
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Joined {{ formatDateTime(user.createdAt) }}</p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-4">
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Events</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ user.eventCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Unpaid</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ user.unpaidEventCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Locked</p>
                                        <p class="mt-1 text-xl font-semibold text-[#171411]">{{ user.lockedEventCount }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                        <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Storage</p>
                                        <p class="mt-1 text-sm font-semibold text-[#171411]">{{ formatBytes(user.storage.usedBytes) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl border border-dashed border-[#decfb6] bg-[#fbf6ee] px-4 py-3 text-sm text-zinc-600">
                                <template v-if="user.latestEventUrl">
                                    Latest event:
                                    <Link :href="user.latestEventUrl" class="font-semibold text-[#171411] hover:text-[#7b5a2c]">
                                        {{ user.latestEventName }}
                                    </Link>
                                    <span v-if="user.latestEventPlan"> · {{ user.latestEventPlan }}</span>
                                </template>
                                <template v-else>
                                    No events created yet.
                                </template>
                            </div>
                            <div class="mt-4 rounded-2xl border border-black/6 bg-white px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                                    <span class="font-medium text-[#171411]">Storage quota</span>
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
                                <p class="mt-2 text-sm text-zinc-500">
                                    Free space: {{ formatBytes(user.storage.freeBytes) }}
                                </p>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
