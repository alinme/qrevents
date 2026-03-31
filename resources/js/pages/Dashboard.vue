<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Camera,
    CheckCircle2,
    Clock3,
    ExternalLink,
    FolderKanban,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    badgeClass,
    moderationBadgeClass,
} from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type {
    BusinessAttentionEvent,
    BusinessOverview,
    DashboardEvent,
    DashboardLinks,
    RecentActivity,
    Summary,
} from '@/types/dashboard';

const props = defineProps<{
    summary: Summary;
    businessOverview: BusinessOverview;
    businessAttentionEvents: BusinessAttentionEvent[];
    continueSetupEvent: DashboardEvent | null;
    dashboardLinks: DashboardLinks;
    ownedEvents: DashboardEvent[];
    collaboratorEvents: DashboardEvent[];
    recentActivity: RecentActivity[];
    showDashboardModal: boolean;
}>();

const { locale, t } = useTranslations();

const page = usePage<{
    auth?: {
        user?: {
            name?: string;
        };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('app.nav.dashboard'),
        href: props.dashboardLinks.overview,
    },
];

const modalOpen = ref(props.showDashboardModal);

const totalAccessibleEvents = computed(
    () => props.summary.ownedEventCount + props.summary.collaboratorEventCount,
);

const intlLocale = computed(() => {
    if (locale.value === 'ro') {
        return 'ro-RO';
    }

    if (locale.value === 'el') {
        return 'el-GR';
    }

    return 'en-GB';
});

const formatLocalizedDateOnly = (value: string | null): string => {
    if (!value) {
        return t('event_home.fallback.not_set');
    }

    return new Intl.DateTimeFormat(intlLocale.value, {
        dateStyle: 'long',
    }).format(new Date(value));
};

const formatLocalizedDateTime = (value: string | null): string => {
    if (!value) {
        return t('dashboard.sections.activity.empty');
    }

    return new Intl.DateTimeFormat(intlLocale.value, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatNumber = (value: number): string =>
    new Intl.NumberFormat(intlLocale.value).format(value);

const compactStats = computed(() => [
    {
        label: t('dashboard.stats.events'),
        value: totalAccessibleEvents.value,
        detail: t('dashboard.stats.events_detail', { count: formatNumber(props.summary.ownedEventCount) }),
        icon: FolderKanban,
    },
    {
        label: t('dashboard.stats.uploads'),
        value: props.summary.totalUploadCount,
        detail: t('dashboard.stats.uploads_detail'),
        icon: Camera,
    },
    {
        label: t('dashboard.stats.pending'),
        value: props.summary.pendingModerationCount,
        detail: t('dashboard.stats.pending_detail'),
        icon: Clock3,
    },
    {
        label: t('dashboard.stats.exports'),
        value: props.summary.readyExportCount,
        detail: t('dashboard.stats.exports_detail'),
        icon: CheckCircle2,
    },
]);

const ownerName = computed(() => page.props.auth?.user?.name ?? t('dashboard.owner_fallback'));

const workspaceLabel = (event: DashboardEvent): string =>
    event.onboardingComplete ? t('app.nav.workspace') : t('dashboard.actions.continue_setup');

const needsBillingAttention = (event: DashboardEvent): boolean =>
    event.planDetails.priceCents > 0 && !event.isPaid;

const managementLink = (event: DashboardEvent): string =>
    needsBillingAttention(event) ? event.links.billing : event.links.settings;

const managementLabel = (event: DashboardEvent): string =>
    needsBillingAttention(event) ? t('app.nav.billing') : t('app.nav.settings');

const formatStorageLimit = (bytes: number): string => {
    if (bytes >= 1024 ** 3) {
        const gigabytes = bytes / 1024 ** 3;
        const value = Number.isInteger(gigabytes)
            ? gigabytes.toString()
            : gigabytes.toFixed(1);

        return t('dashboard.plan.storage_gb', { value });
    }

    return t('dashboard.plan.storage_mb', {
        value: Math.max(1, Math.round(bytes / 1024 ** 2)),
    });
};

const uploadWindowLabel = (days: number): string =>
    t('dashboard.plan.upload_window', { count: formatNumber(days) });

const planSummary = (event: DashboardEvent): string =>
    [
        formatStorageLimit(event.planDetails.storageLimitBytes),
        t('dashboard.plan.uploads', { count: formatNumber(event.planDetails.uploadLimit) }),
        uploadWindowLabel(event.planDetails.uploadWindowDays),
    ].join(' · ');

const planCapabilities = (event: DashboardEvent): string[] => {
    const capabilities: string[] = [];

    if (event.planDetails.downloadAllEnabled) {
        capabilities.push(t('dashboard.plan.capabilities.full_downloads'));
    } else {
        capabilities.push(t('dashboard.plan.capabilities.branding'));
    }

    if (event.planDetails.moderationToolsEnabled) {
        capabilities.push(t('dashboard.plan.capabilities.moderation_tools'));
    }

    if (event.planDetails.removeAppBranding) {
        capabilities.push(t('dashboard.plan.capabilities.white_label'));
    }

    return capabilities;
};
</script>

<template>
    <Head :title="t('dashboard.page_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section class="dashboard-panel">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="dashboard-eyebrow">
                                {{ t('dashboard.hero.kicker') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('dashboard.hero.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{ t('dashboard.hero.description', { name: ownerName }) }}
                            </p>
                        </div>
                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="card in compactStats"
                                :key="card.label"
                                class="dashboard-divider-left"
                            >
                                <dt class="dashboard-eyebrow flex items-center gap-2">
                                    <component :is="card.icon" class="size-3.5 text-brand-muted/70" />
                                    {{ card.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-brand-ink">
                                    {{ card.value }}
                                </dd>
                                <p class="dashboard-meta mt-1">
                                    {{ card.detail }}
                                </p>
                            </div>
                        </dl>
                    </div>

                    <div
                        v-if="continueSetupEvent"
                        class="mt-5 flex flex-col gap-3 border-t border-amber-200/80 pt-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-semibold text-amber-950">
                                {{ t('dashboard.continue_setup.title', { event: continueSetupEvent.name }) }}
                            </p>
                            <p class="text-sm text-amber-800">
                                {{ t('dashboard.continue_setup.description') }}
                            </p>
                        </div>
                        <Button as-child size="sm" class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                            <Link :href="continueSetupEvent.primaryAction.url">
                                {{ t('dashboard.actions.continue_setup') }}
                            </Link>
                        </Button>
                    </div>

                </section>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.9fr)]">
                    <section id="events" class="dashboard-panel">
                        <div class="dashboard-panel-divider flex flex-col gap-2 pb-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="dashboard-section-title">
                                    {{ t('dashboard.sections.events.title') }}
                                </h2>
                                <p class="dashboard-body mt-1">
                                    {{ t('dashboard.sections.events.description') }}
                                </p>
                            </div>
                            <p class="text-sm text-brand-muted">
                                {{ t('dashboard.sections.events.total', { count: formatNumber(totalAccessibleEvents) }) }}
                            </p>
                        </div>

                        <div
                            v-if="ownedEvents.length === 0 && collaboratorEvents.length === 0"
                            class="dashboard-body py-10"
                        >
                            {{ t('dashboard.empty.no_events') }}
                        </div>

                        <div v-else class="space-y-5 pt-5">
                            <div v-if="ownedEvents.length > 0" class="space-y-3">
                                <p class="dashboard-eyebrow">
                                    {{ t('dashboard.sections.events.owned') }}
                                </p>

                                <article
                                    v-for="event in ownedEvents"
                                    :key="`owner-${event.id}`"
                                    class="border-b border-brand-border/70 py-4 last:border-b-0"
                                >
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 space-y-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.statusTone)"
                                                >
                                                    {{ event.statusLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.billingTone)"
                                                >
                                                    {{ event.billingLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.planDetails.tone)"
                                                >
                                                    {{ event.planDetails.name }}
                                                </span>
                                            </div>

                                            <div>
                                                <h3 class="text-sm font-semibold text-brand-ink sm:text-base">
                                                    {{ event.name }}
                                                </h3>
                                                <p class="mt-1 text-sm text-brand-muted">
                                                    {{ t('dashboard.event_summary.inline', {
                                                        date: formatLocalizedDateOnly(event.eventDate),
                                                        guests: formatNumber(event.guestCount),
                                                        uploads: formatNumber(event.assetCount),
                                                        pending: formatNumber(event.processingCount),
                                                    }) }}
                                                </p>
                                                <p class="dashboard-meta mt-1">
                                                    {{
                                                        event.lastUploadAt
                                                            ? t('dashboard.event_summary.last_upload', { date: formatLocalizedDateTime(event.lastUploadAt) })
                                                            : t('dashboard.event_summary.no_uploads')
                                                    }}
                                                </p>
                                                <p class="dashboard-meta mt-1">
                                                    {{ planSummary(event) }}
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <span
                                                        v-for="capability in planCapabilities(event)"
                                                        :key="`${event.id}-${capability}`"
                                                        class="inline-flex rounded-full bg-brand-panel-strong/35 px-2.5 py-1 text-[0.72rem] font-medium text-brand-muted"
                                                    >
                                                        {{ capability }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <Button
                                                as-child
                                                size="sm"
                                                class="bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                                            >
                                                <Link :href="event.primaryAction.url">
                                                    {{ workspaceLabel(event) }}
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.media">
                                                    {{ t('app.nav.media') }}
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="managementLink(event)">
                                                    {{ managementLabel(event) }}
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </article>
                            </div>

                            <div v-if="collaboratorEvents.length > 0" class="space-y-3">
                                <p class="dashboard-eyebrow">
                                    {{ t('dashboard.sections.events.shared') }}
                                </p>

                                <article
                                    v-for="event in collaboratorEvents"
                                    :key="`collab-${event.id}`"
                                    class="border-b border-brand-border/70 py-4 last:border-b-0"
                                >
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 space-y-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.roleTone)"
                                                >
                                                    {{ event.roleLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.statusTone)"
                                                >
                                                    {{ event.statusLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                                    :class="badgeClass(event.planDetails.tone)"
                                                >
                                                    {{ event.planDetails.name }}
                                                </span>
                                            </div>

                                            <div>
                                                <h3 class="text-sm font-semibold text-brand-ink sm:text-base">
                                                    {{ event.name }}
                                                </h3>
                                                <p class="mt-1 text-sm text-brand-muted">
                                                    {{ t('dashboard.event_summary.shared_inline', {
                                                        date: formatLocalizedDateOnly(event.eventDate),
                                                        uploads: formatNumber(event.assetCount),
                                                        pending: formatNumber(event.processingCount),
                                                    }) }}
                                                </p>
                                                <p class="dashboard-meta mt-1">
                                                    {{ planSummary(event) }}
                                                </p>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <span
                                                        v-for="capability in planCapabilities(event)"
                                                        :key="`${event.id}-${capability}`"
                                                        class="inline-flex rounded-full bg-brand-panel-strong/35 px-2.5 py-1 text-[0.72rem] font-medium text-brand-muted"
                                                    >
                                                        {{ capability }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <Button as-child size="sm" class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                                                <Link :href="event.links.dashboard">
                                                    {{ t('app.nav.workspace') }}
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.media">
                                                    {{ t('app.nav.media') }}
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.settings">
                                                    {{ t('app.nav.settings') }}
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </section>

                    <aside id="activity" class="dashboard-panel flex min-h-0">
                        <div class="flex min-h-0 w-full flex-col">
                        <div class="dashboard-panel-divider flex flex-col gap-2 pb-4">
                            <h2 class="dashboard-section-title">
                                {{ t('dashboard.sections.activity.title') }}
                            </h2>
                            <p class="dashboard-body">
                                {{ t('dashboard.sections.activity.description') }}
                            </p>
                        </div>

                        <div v-if="recentActivity.length === 0" class="dashboard-body py-8">
                            {{ t('dashboard.sections.activity.empty') }}
                        </div>

                        <div v-else class="min-h-0 flex-1 overflow-y-auto pt-2">
                            <div class="divide-y divide-black/5 pr-1">
                            <Link
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                :href="activity.activityUrl"
                                class="flex flex-col gap-2 py-3 transition hover:bg-black/[0.015]"
                            >
                                <div class="min-w-0">
                                    <p class="text-sm text-brand-ink">
                                        {{ t('dashboard.sections.activity.item', {
                                            guest: activity.guestName,
                                            event: activity.eventName,
                                        }) }}
                                    </p>
                                    <p class="mt-1 text-sm text-brand-muted">
                                        {{ activity.summary }}
                                    </p>
                                    <p class="dashboard-meta mt-1">
                                        {{ formatLocalizedDateTime(activity.createdAt) }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold capitalize"
                                        :class="moderationBadgeClass(activity.moderationStatus)"
                                    >
                                        {{ activity.moderationStatus }}
                                    </span>
                                    <ExternalLink class="size-4 text-brand-muted/70" />
                                </div>
                            </Link>
                            </div>
                        </div>
                        </div>
                    </aside>
                </div>
            </div>

                <Dialog :open="modalOpen" @update:open="modalOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{{ t('dashboard.dialog.title') }}</DialogTitle>
                        <DialogDescription>
                            {{ t('dashboard.dialog.description') }}
                        </DialogDescription>
                    </DialogHeader>
                    <Button
                        as-child
                        class="mt-2 bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                    >
                        <Link
                            :href="
                                continueSetupEvent?.links.dashboard
                                ?? ownedEvents[0]?.links.dashboard
                                ?? collaboratorEvents[0]?.links.dashboard
                                ?? dashboardLinks.overview
                            "
                        >
                            {{ t('dashboard.actions.open_event') }}
                        </Link>
                    </Button>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
