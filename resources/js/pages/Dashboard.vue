<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    Camera,
    CheckCircle2,
    Clock3,
    Download,
    ExternalLink,
    FolderKanban,
    Image as ImageIcon,
    Plus,
    Settings,
    Users,
    Video,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import DashboardQuickActionCard from '@/components/dashboard/DashboardQuickActionCard.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    badgeClass,
    formatBytes,
    formatDateOnly,
    formatDateTime,
    moderationBadgeClass,
} from '@/lib/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    BusinessAttentionEvent,
    BusinessOverview,
    DashboardEvent,
    DashboardLinks,
    QuickAction,
    RecentActivity,
    Summary,
} from '@/types';

const props = defineProps<{
    summary: Summary;
    businessOverview: BusinessOverview;
    businessAttentionEvents: BusinessAttentionEvent[];
    quickActions: QuickAction[];
    continueSetupEvent: DashboardEvent | null;
    dashboardLinks: DashboardLinks;
    ownedEvents: DashboardEvent[];
    collaboratorEvents: DashboardEvent[];
    recentActivity: RecentActivity[];
    showDashboardModal: boolean;
}>();

const page = usePage<{
    auth: {
        user: {
            name: string;
        };
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: props.dashboardLinks.overview,
    },
];

const modalOpen = ref(props.showDashboardModal);

const summaryCards = computed(() => [
    {
        label: 'Owned events',
        value: props.summary.ownedEventCount,
        detail: 'Events you fully control',
        icon: FolderKanban,
    },
    {
        label: 'Shared access',
        value: props.summary.collaboratorEventCount,
        detail: 'Events shared with you',
        icon: Users,
    },
    {
        label: 'Setup queue',
        value: props.summary.pendingSetupCount,
        detail: 'Events still finishing onboarding',
        icon: Clock3,
    },
    {
        label: 'Guest uploads',
        value: props.summary.totalUploadCount,
        detail: 'Media and text collected so far',
        icon: ImageIcon,
    },
    {
        label: 'Pending review',
        value: props.summary.pendingModerationCount,
        detail: 'Uploads waiting for moderation',
        icon: Camera,
    },
    {
        label: 'Exports ready',
        value: props.summary.readyExportCount,
        detail: 'Downloadable album ZIPs',
        icon: Download,
    },
]);

const businessHealthCards = computed(() => [
    {
        label: 'Business workspaces',
        value: props.businessOverview.activeEventCount,
        detail: 'Owned events currently in draft, scheduled, live, or grace.',
    },
    {
        label: 'Billing attention',
        value:
            props.businessOverview.overdueEventCount > 0
                ? `${props.businessOverview.overdueEventCount} overdue`
                : `${props.businessOverview.unpaidEventCount} unpaid`,
        detail: 'Open balances that still need follow-up.',
    },
    {
        label: 'Storage footprint',
        value: `${props.businessOverview.storageUsagePercent}%`,
        detail: `${formatBytes(props.businessOverview.totalUsedStorageBytes)} used of ${formatBytes(props.businessOverview.totalAllocatedStorageBytes)}.`,
    },
]);

const accountHeading = computed(() => {
    if (
        props.summary.ownedEventCount === 0 &&
        props.summary.collaboratorEventCount === 0
    ) {
        return 'Your account is ready for its first event.';
    }

    if (props.summary.pendingSetupCount > 0) {
        return 'You have event setup still in motion.';
    }

    return 'Your event operations are all in one place.';
});

const activityIcon = (kind: RecentActivity['kind']) => {
    switch (kind) {
        case 'video':
            return Video;
        case 'text':
            return CheckCircle2;
        default:
            return ImageIcon;
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_85%_10%,_rgba(251,191,36,0.14),_transparent_22%)]"
        >
            <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
                <section
                    class="overflow-hidden rounded-[2rem] border border-black/5 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2d251f_46%,#5f533f_100%)] px-6 py-8 text-white md:px-8"
                    >
                        <div
                            class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div class="max-w-3xl space-y-3">
                                <div
                                    class="inline-flex w-fit items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-white/80 uppercase"
                                >
                                    Account dashboard
                                </div>
                                <div class="space-y-2">
                                    <h1
                                        class="text-3xl font-semibold tracking-tight md:text-4xl"
                                    >
                                        {{ accountHeading }}
                                    </h1>
                                    <p
                                        class="max-w-2xl text-sm leading-6 text-white/72 md:text-base"
                                    >
                                        {{ page.props.auth.user.name }}, this is
                                        the fastest route into event setup,
                                        moderation, album sharing, and export
                                        handoff.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <DashboardQuickActionCard
                                    v-for="action in quickActions"
                                    :key="action.label"
                                    :action="action"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3 xl:p-6"
                    >
                        <DashboardMetricCard
                            v-for="card in summaryCards"
                            :key="card.label"
                            :label="card.label"
                            :value="card.value"
                            :detail="card.detail"
                            :icon="card.icon"
                        />
                    </div>
                </section>

                <section
                    v-if="continueSetupEvent"
                    class="rounded-[2rem] border border-amber-200 bg-[linear-gradient(135deg,rgba(255,251,235,1)_0%,rgba(254,243,199,0.8)_100%)] p-6 shadow-sm"
                >
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div class="max-w-3xl space-y-3">
                            <div
                                class="inline-flex w-fit items-center gap-2 rounded-full bg-white/75 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-amber-900 uppercase"
                            >
                                Continue setup
                            </div>
                            <div class="space-y-2">
                                <h2
                                    class="text-2xl font-semibold text-[#171411]"
                                >
                                    {{ continueSetupEvent.name }} is still being
                                    configured.
                                </h2>
                                <p
                                    class="text-sm leading-6 text-zinc-700 md:text-base"
                                >
                                    Finish onboarding to unlock the full owner
                                    dashboard, public album, and guest upload
                                    flow.
                                </p>
                            </div>
                            <div
                                class="flex flex-wrap items-center gap-2 text-sm text-zinc-700"
                            >
                                <span class="rounded-full bg-white px-3 py-1">{{
                                    continueSetupEvent.plan
                                }}</span>
                                <span class="rounded-full bg-white px-3 py-1">{{
                                    continueSetupEvent.statusLabel
                                }}</span>
                                <span class="rounded-full bg-white px-3 py-1">{{
                                    formatDateOnly(continueSetupEvent.eventDate)
                                }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button
                                as-child
                                size="lg"
                                class="bg-[#171411] text-white hover:bg-[#2b2621]"
                            >
                                <Link
                                    :href="continueSetupEvent.primaryAction.url"
                                >
                                    {{ continueSetupEvent.primaryAction.label }}
                                </Link>
                            </Button>
                            <Button
                                as-child
                                size="lg"
                                variant="outline"
                                class="border-amber-300 bg-white/70"
                            >
                                <Link :href="continueSetupEvent.links.album">
                                    Preview album
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>

                <section
                    v-if="dashboardLinks.business"
                    class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                >
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div class="max-w-3xl space-y-3">
                            <div
                                class="inline-flex w-fit items-center gap-2 rounded-full bg-[#fbfaf7] px-3 py-1 text-xs font-semibold tracking-[0.24em] text-zinc-600 uppercase"
                            >
                                Business dashboard
                            </div>
                            <div class="space-y-2">
                                <h2
                                    class="text-2xl font-semibold text-[#171411]"
                                >
                                    SaaS operations now live in a dedicated
                                    business workspace
                                </h2>
                                <p
                                    class="text-sm leading-6 text-zinc-600 md:text-base"
                                >
                                    Keep this account overview focused on
                                    switching contexts. Use the business
                                    dashboard for billing follow-up, storage
                                    health, export readiness, and event-level
                                    operational queues.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button
                                as-child
                                class="bg-[#171411] text-white hover:bg-[#2b2621]"
                            >
                                <Link :href="dashboardLinks.business">
                                    Open business dashboard
                                </Link>
                            </Button>
                            <Button as-child variant="outline">
                                <Link :href="props.dashboardLinks.ownedEvents">
                                    View owned events
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <DashboardMetricCard
                            v-for="card in businessHealthCards"
                            :key="card.label"
                            :label="card.label"
                            :value="card.value"
                            :detail="card.detail"
                            class="rounded-[1.5rem] bg-[#fcfbf8]"
                        />
                    </div>

                    <div
                        class="mt-4 flex flex-wrap gap-2 text-sm text-zinc-600"
                    >
                        <span
                            class="rounded-full border border-black/8 bg-[#fcfbf8] px-3 py-1.5"
                        >
                            {{ businessAttentionEvents.length }} events need
                            follow-up
                        </span>
                        <span
                            class="rounded-full border border-black/8 bg-[#fcfbf8] px-3 py-1.5"
                        >
                            {{ businessOverview.liveEventCount }} events live
                            now
                        </span>
                        <span
                            class="rounded-full border border-black/8 bg-[#fcfbf8] px-3 py-1.5"
                        >
                            {{ businessOverview.readyExportCount }} exports
                            ready
                        </span>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[1.65fr_1fr]">
                    <div class="flex flex-col gap-6">
                        <section
                            id="owned-events"
                            class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                        >
                            <div
                                class="flex flex-col gap-3 border-b border-black/5 pb-5 md:flex-row md:items-end md:justify-between"
                            >
                                <div class="space-y-1">
                                    <h2
                                        class="text-2xl font-semibold text-[#171411]"
                                    >
                                        Events you own
                                    </h2>
                                    <p class="text-sm leading-6 text-zinc-600">
                                        Account-level switching into each event
                                        workspace. Detailed reporting stays
                                        inside the event itself.
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        as-child
                                        variant="outline"
                                        class="border-black/10"
                                    >
                                        <Link
                                            :href="
                                                props.dashboardLinks.ownedEvents
                                            "
                                        >
                                            View all
                                        </Link>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="outline"
                                        class="border-black/10"
                                    >
                                        <Link
                                            :href="
                                                quickActions[0]?.url ??
                                                '/onboarding'
                                            "
                                        >
                                            <Plus class="size-4" />
                                            New event
                                        </Link>
                                    </Button>
                                </div>
                            </div>

                            <div
                                v-if="ownedEvents.length === 0"
                                class="py-12 text-center"
                            >
                                <div
                                    class="mx-auto flex max-w-md flex-col items-center gap-4"
                                >
                                    <div
                                        class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]"
                                    >
                                        <FolderKanban class="size-7" />
                                    </div>
                                    <div class="space-y-2">
                                        <h3
                                            class="text-xl font-semibold text-[#171411]"
                                        >
                                            No owned events yet
                                        </h3>
                                        <p
                                            class="text-sm leading-6 text-zinc-600"
                                        >
                                            Create your first event to generate
                                            an album, wall, and moderation
                                            workspace.
                                        </p>
                                    </div>
                                    <Button
                                        as-child
                                        class="bg-[#171411] text-white hover:bg-[#2b2621]"
                                    >
                                        <Link
                                            :href="
                                                quickActions[0]?.url ??
                                                '/onboarding'
                                            "
                                        >
                                            Start onboarding
                                        </Link>
                                    </Button>
                                </div>
                            </div>

                            <div v-else class="mt-6 grid gap-5">
                                <article
                                    v-for="event in ownedEvents"
                                    :key="event.id"
                                    class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5"
                                >
                                    <div class="flex flex-col gap-5">
                                        <div
                                            class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                                        >
                                            <div class="space-y-3">
                                                <div
                                                    class="flex flex-wrap items-center gap-2"
                                                >
                                                    <span
                                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                        :class="
                                                            badgeClass(
                                                                event.roleTone,
                                                            )
                                                        "
                                                    >
                                                        {{ event.roleLabel }}
                                                    </span>
                                                    <span
                                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                        :class="
                                                            badgeClass(
                                                                event.statusTone,
                                                            )
                                                        "
                                                    >
                                                        {{ event.statusLabel }}
                                                    </span>
                                                    <span
                                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                        :class="
                                                            badgeClass(
                                                                event.billingTone,
                                                            )
                                                        "
                                                    >
                                                        {{ event.billingLabel }}
                                                    </span>
                                                </div>

                                                <div class="space-y-1">
                                                    <h3
                                                        class="text-2xl font-semibold tracking-tight text-[#171411]"
                                                    >
                                                        {{ event.name }}
                                                    </h3>
                                                    <p
                                                        class="text-sm text-zinc-600"
                                                    >
                                                        {{ event.plan }} ·
                                                        {{
                                                            formatDateOnly(
                                                                event.eventDate,
                                                            )
                                                        }}
                                                        · {{ event.timezone }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div
                                                class="rounded-2xl border border-black/6 bg-white px-4 py-3 text-left lg:max-w-xs"
                                            >
                                                <p
                                                    class="text-xs font-semibold tracking-[0.2em] text-zinc-500 uppercase"
                                                >
                                                    Next stop
                                                </p>
                                                <p
                                                    class="mt-2 text-base font-semibold text-[#171411]"
                                                >
                                                    {{
                                                        event.onboardingComplete
                                                            ? 'Open the event workspace'
                                                            : 'Finish event setup'
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-1 text-sm text-zinc-500"
                                                >
                                                    {{
                                                        event.onboardingComplete
                                                            ? 'Media review, exports, and guest controls live in the event workspace.'
                                                            : 'Complete onboarding before guests start sharing.'
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2 text-sm text-zinc-600"
                                        >
                                            <span
                                                class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                            >
                                                {{ event.guestCount }} guests
                                            </span>
                                            <span
                                                class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                            >
                                                {{ event.assetCount }} uploads
                                            </span>
                                            <span
                                                class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                            >
                                                {{ event.processingCount }}
                                                pending review
                                            </span>
                                            <span
                                                class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                            >
                                                {{ event.mediaExportLabel }}
                                            </span>
                                            <span
                                                class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                            >
                                                {{
                                                    event.lastUploadAt
                                                        ? `Last upload ${formatDateTime(event.lastUploadAt)}`
                                                        : 'No guest uploads yet'
                                                }}
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap gap-3">
                                            <Button
                                                as-child
                                                class="bg-[#171411] text-white hover:bg-[#2b2621]"
                                            >
                                                <Link
                                                    :href="
                                                        event.primaryAction.url
                                                    "
                                                >
                                                    {{
                                                        event.primaryAction
                                                            .label
                                                    }}
                                                </Link>
                                            </Button>
                                            <Button as-child variant="outline">
                                                <Link :href="event.links.media">
                                                    <Camera class="size-4" />
                                                    Review media
                                                </Link>
                                            </Button>
                                            <Button as-child variant="outline">
                                                <Link
                                                    :href="event.links.settings"
                                                >
                                                    <Settings class="size-4" />
                                                    Settings
                                                </Link>
                                            </Button>
                                            <Button as-child variant="outline">
                                                <Link :href="event.links.album">
                                                    <ExternalLink
                                                        class="size-4"
                                                    />
                                                    Open album
                                                </Link>
                                            </Button>
                                            <Button
                                                v-if="
                                                    event.canManage &&
                                                    event.mediaExportStatus ===
                                                        'ready'
                                                "
                                                as-child
                                                variant="outline"
                                            >
                                                <Link
                                                    :href="
                                                        event.links
                                                            .mediaExportDownload
                                                    "
                                                >
                                                    <Download class="size-4" />
                                                    Download export
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section
                            id="shared-events"
                            class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                        >
                            <div class="space-y-1 border-b border-black/5 pb-5">
                                <h2
                                    class="text-2xl font-semibold text-[#171411]"
                                >
                                    Shared with you
                                </h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Collaborator workspaces stay visible here so
                                    you can jump between responsibilities
                                    without duplicating the full event
                                    dashboard.
                                </p>
                            </div>

                            <div
                                v-if="collaboratorEvents.length === 0"
                                class="py-10 text-center text-sm leading-6 text-zinc-600"
                            >
                                No collaborator events are linked to this
                                account yet.
                            </div>

                            <div v-else class="mt-6 grid gap-4">
                                <article
                                    v-for="event in collaboratorEvents"
                                    :key="event.id"
                                    class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5"
                                >
                                    <div
                                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                                    >
                                        <div class="space-y-3">
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                    :class="
                                                        badgeClass(
                                                            event.roleTone,
                                                        )
                                                    "
                                                >
                                                    {{ event.roleLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                    :class="
                                                        badgeClass(
                                                            event.statusTone,
                                                        )
                                                    "
                                                >
                                                    {{ event.statusLabel }}
                                                </span>
                                                <span
                                                    class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                    :class="
                                                        badgeClass(
                                                            event.mediaExportTone,
                                                        )
                                                    "
                                                >
                                                    {{ event.mediaExportLabel }}
                                                </span>
                                            </div>

                                            <div class="space-y-1">
                                                <h3
                                                    class="text-xl font-semibold text-[#171411]"
                                                >
                                                    {{ event.name }}
                                                </h3>
                                                <p
                                                    class="text-sm text-zinc-600"
                                                >
                                                    {{ event.plan }} ·
                                                    {{
                                                        formatDateOnly(
                                                            event.eventDate,
                                                        )
                                                    }}
                                                    ·
                                                    {{ event.assetCount }}
                                                    uploads
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap gap-3">
                                            <Button as-child variant="outline">
                                                <Link
                                                    :href="
                                                        event.links.dashboard
                                                    "
                                                >
                                                    <FolderKanban
                                                        class="size-4"
                                                    />
                                                    Event home
                                                </Link>
                                            </Button>
                                            <Button as-child variant="outline">
                                                <Link :href="event.links.media">
                                                    <Camera class="size-4" />
                                                    Media
                                                </Link>
                                            </Button>
                                            <Button as-child variant="outline">
                                                <Link
                                                    :href="event.links.settings"
                                                >
                                                    <Settings class="size-4" />
                                                    Settings
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>

                    <aside
                        id="recent-activity"
                        class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                    >
                        <div
                            class="flex items-end justify-between gap-3 border-b border-black/5 pb-5"
                        >
                            <div class="space-y-1">
                                <h2
                                    class="text-2xl font-semibold text-[#171411]"
                                >
                                    Recent activity
                                </h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    The latest guest uploads across the events
                                    this account can access.
                                </p>
                            </div>
                            <Button
                                as-child
                                variant="outline"
                                class="border-black/10"
                            >
                                <Link
                                    :href="props.dashboardLinks.recentActivity"
                                >
                                    View all
                                </Link>
                            </Button>
                        </div>

                        <div
                            v-if="recentActivity.length === 0"
                            class="py-10 text-center"
                        >
                            <div
                                class="mx-auto flex max-w-sm flex-col items-center gap-4"
                            >
                                <div
                                    class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]"
                                >
                                    <CalendarDays class="size-7" />
                                </div>
                                <div class="space-y-2">
                                    <h3
                                        class="text-lg font-semibold text-[#171411]"
                                    >
                                        Nothing to review yet
                                    </h3>
                                    <p class="text-sm leading-6 text-zinc-600">
                                        Once guests upload media, this feed
                                        becomes the quickest way to spot fresh
                                        activity.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mt-6 grid gap-4">
                            <Link
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                :href="activity.activityUrl"
                                class="group rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-4 transition hover:border-black/12 hover:bg-[#faf7f1]"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        class="rounded-2xl bg-white p-3 text-[#171411] shadow-sm"
                                    >
                                        <component
                                            :is="activityIcon(activity.kind)"
                                            class="size-5"
                                        />
                                    </div>

                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="text-sm font-semibold text-[#171411]"
                                            >
                                                {{ activity.guestName }}
                                            </span>
                                            <span class="text-sm text-zinc-500">
                                                in {{ activity.eventName }}
                                            </span>
                                        </div>
                                        <p
                                            class="line-clamp-2 text-sm leading-6 text-zinc-600"
                                        >
                                            {{ activity.summary }}
                                        </p>
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                                :class="
                                                    moderationBadgeClass(
                                                        activity.moderationStatus,
                                                    )
                                                "
                                            >
                                                {{ activity.moderationStatus }}
                                            </span>
                                            <span class="text-xs text-zinc-500">
                                                {{
                                                    formatDateTime(
                                                        activity.createdAt,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </aside>
                </div>
            </div>

            <Dialog :open="modalOpen" @update:open="modalOpen = $event">
                <DialogContent class="sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Event dashboard unlocked</DialogTitle>
                        <DialogDescription>
                            Your onboarding is complete. From here you can
                            switch events, review uploads, and download exports
                            when they are ready.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <Button
                            as-child
                            class="bg-[#171411] text-white hover:bg-[#2b2621]"
                        >
                            <Link
                                :href="
                                    continueSetupEvent?.links.dashboard ??
                                    ownedEvents[0]?.links.dashboard ??
                                    quickActions[0]?.url ??
                                    '/onboarding'
                                "
                            >
                                Open main workspace
                            </Link>
                        </Button>
                        <Button variant="outline" @click="modalOpen = false">
                            Keep browsing
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
