<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Briefcase,
    Camera,
    CreditCard,
    FolderKanban,
    HardDrive,
    UserPlus,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
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
        businessAccounts: number;
        paidEvents: number;
        unpaidEvents: number;
        newUsers7d: number;
        eventsByStatus: StatusCount[];
    };
    recentEvents: AdminEventRow[];
}>();

const { t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('admin.shared.admin'),
        href: '/admin',
    },
];

const stats = computed(() => [
    {
        label: t('admin.overview.stats.users'),
        value: props.summary.totalUsers,
        icon: Users,
    },
    {
        label: t('admin.overview.stats.events'),
        value: props.summary.totalEvents,
        icon: FolderKanban,
    },
    {
        label: t('admin.overview.stats.uploads'),
        value: props.summary.totalUploads,
        icon: Camera,
    },
    {
        label: t('admin.overview.stats.storage_used'),
        value: props.summary.storageUsedLabel,
        icon: HardDrive,
    },
    {
        label: t('admin.overview.stats.business'),
        value: props.summary.businessAccounts,
        icon: Briefcase,
    },
    {
        label: t('admin.overview.stats.paid_events'),
        value: props.summary.paidEvents,
        icon: CreditCard,
    },
    {
        label: t('admin.overview.stats.unpaid_events'),
        value: props.summary.unpaidEvents,
        icon: FolderKanban,
    },
    {
        label: t('admin.overview.stats.new_users'),
        value: props.summary.newUsers7d,
        icon: UserPlus,
    },
]);
</script>

<template>
    <Head :title="t('admin.overview.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section class="dashboard-panel">
                    <p class="dashboard-eyebrow">
                        {{ t('admin.shared.admin') }}
                    </p>
                    <h1 class="dashboard-title mt-2">
                        {{ t('admin.overview.title') }}
                    </h1>
                    <p class="dashboard-body mt-2">
                        {{ t('admin.overview.description') }}
                    </p>

                    <dl
                        class="mt-8 grid grid-cols-2 gap-x-8 gap-y-8 sm:grid-cols-4 xl:grid-cols-8"
                    >
                        <div v-for="stat in stats" :key="stat.label">
                            <dt
                                class="flex min-h-[2.25rem] items-start gap-1.5 text-[0.7rem] leading-tight font-semibold tracking-wide text-brand-muted uppercase"
                            >
                                <component
                                    :is="stat.icon"
                                    class="mt-0.5 size-3.5 shrink-0 text-brand-muted/60"
                                />
                                <span>{{ stat.label }}</span>
                            </dt>
                            <dd
                                class="mt-1 text-2xl font-bold tracking-tight text-brand-ink"
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
                                {{ t('admin.overview.recent.title') }}
                            </h2>
                            <p class="dashboard-body mt-1">
                                {{ t('admin.overview.recent.description') }}
                            </p>
                        </div>
                        <Link
                            href="/admin/events"
                            class="text-sm font-semibold text-brand-ink underline underline-offset-4 hover:text-brand-accent"
                        >
                            {{ t('admin.overview.recent.view_all') }}
                        </Link>
                    </div>

                    <div
                        v-if="recentEvents.length === 0"
                        class="dashboard-body py-10"
                    >
                        {{ t('admin.overview.recent.empty') }}
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
