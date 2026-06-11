<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Camera, FolderKanban, HardDrive, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { badgeClass, formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type { Tone } from '@/types/dashboard';

type StatusCount = {
    status: string;
    label: string;
    tone: Tone;
    count: number;
};

type AdminEventRow = {
    id: number;
    name: string;
    ownerName: string;
    ownerEmail: string;
    planName: string;
    planPriceLabel: string;
    statusLabel: string;
    statusTone: Tone;
    isPaid: boolean;
    assetCount: number;
    createdAt: string | null;
    links: {
        settings: string;
    };
};

const props = defineProps<{
    summary: {
        totalUsers: number;
        totalEvents: number;
        totalUploads: number;
        storageUsedBytes: number;
        storageUsedLabel: string;
        eventsByStatus: StatusCount[];
    };
    recentEvents: AdminEventRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
];

const stats = computed(() => [
    {
        label: 'Users',
        value: props.summary.totalUsers,
        icon: Users,
    },
    {
        label: 'Events',
        value: props.summary.totalEvents,
        icon: FolderKanban,
    },
    {
        label: 'Uploads',
        value: props.summary.totalUploads,
        icon: Camera,
    },
    {
        label: 'Storage used',
        value: props.summary.storageUsedLabel,
        icon: HardDrive,
    },
]);
</script>

<template>
    <Head title="Admin · Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section class="dashboard-panel">
                    <p class="dashboard-eyebrow">Admin</p>
                    <h1 class="dashboard-title mt-2">Platform overview</h1>
                    <p class="dashboard-body mt-2">
                        A quick pulse on accounts, events, and storage across
                        the platform.
                    </p>

                    <dl
                        class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="dashboard-divider-left"
                        >
                            <dt
                                class="dashboard-eyebrow flex items-center gap-2"
                            >
                                <component
                                    :is="stat.icon"
                                    class="size-3.5 text-brand-muted/70"
                                />
                                {{ stat.label }}
                            </dt>
                            <dd
                                class="mt-2 text-lg font-semibold tracking-tight text-brand-ink"
                            >
                                {{ stat.value }}
                            </dd>
                        </div>
                    </dl>

                    <div
                        class="mt-6 flex flex-wrap gap-2 border-t border-brand-border/70 pt-4"
                    >
                        <span
                            v-for="entry in summary.eventsByStatus"
                            :key="entry.status"
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                            :class="badgeClass(entry.tone)"
                        >
                            {{ entry.label }}
                            <span class="font-bold">{{ entry.count }}</span>
                        </span>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div
                        class="dashboard-panel-divider flex flex-col gap-2 pb-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <h2 class="dashboard-section-title">
                                Recent events
                            </h2>
                            <p class="dashboard-body mt-1">
                                The latest events created on the platform.
                            </p>
                        </div>
                        <Link
                            href="/admin/events"
                            class="text-sm font-semibold text-brand-ink underline underline-offset-4 hover:text-brand-accent"
                        >
                            View all events
                        </Link>
                    </div>

                    <div
                        v-if="recentEvents.length === 0"
                        class="dashboard-body py-10"
                    >
                        No events yet.
                    </div>

                    <div v-else class="divide-y divide-brand-border/70">
                        <article
                            v-for="event in recentEvents"
                            :key="event.id"
                            class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3
                                        class="text-sm font-semibold text-brand-ink"
                                    >
                                        {{ event.name }}
                                    </h3>
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                        :class="badgeClass(event.statusTone)"
                                    >
                                        {{ event.statusLabel }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-brand-muted">
                                    {{ event.ownerEmail }} ·
                                    {{ event.planName }} ({{
                                        event.planPriceLabel
                                    }})
                                </p>
                            </div>
                            <p class="dashboard-meta shrink-0">
                                {{ formatDateTime(event.createdAt) }}
                            </p>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
