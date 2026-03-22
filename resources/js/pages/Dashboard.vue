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
import {
    badgeClass,
    formatDateOnly,
    formatDateTime,
    moderationBadgeClass,
} from '@/lib/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
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

const page = usePage<{
    auth?: {
        user?: {
            name?: string;
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

const totalAccessibleEvents = computed(
    () => props.summary.ownedEventCount + props.summary.collaboratorEventCount,
);

const compactStats = computed(() => [
    {
        label: 'Events',
        value: totalAccessibleEvents.value,
        detail: `${props.summary.ownedEventCount} owned`,
        icon: FolderKanban,
    },
    {
        label: 'Uploads',
        value: props.summary.totalUploadCount,
        detail: 'Across all accessible events',
        icon: Camera,
    },
    {
        label: 'Pending',
        value: props.summary.pendingModerationCount,
        detail: 'Waiting for review',
        icon: Clock3,
    },
    {
        label: 'Exports',
        value: props.summary.readyExportCount,
        detail: 'Ready to download',
        icon: CheckCircle2,
    },
]);

const ownerName = computed(() => page.props.auth?.user?.name ?? 'You');

const workspaceLabel = (event: DashboardEvent): string =>
    event.onboardingComplete ? 'Workspace' : event.primaryAction.label;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[#faf7f2]">
            <div class="mx-auto max-w-6xl space-y-5 p-4 md:p-6">
                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Events
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                Events
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">
                                {{ ownerName }}, open the event you need and jump straight to workspace, media, or settings.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="card in compactStats"
                            :key="card.label"
                            class="rounded-[1rem] border border-black/6 bg-[#fcfbf8] px-3.5 py-3"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-white p-2 text-[#171411] shadow-sm">
                                    <component :is="card.icon" class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                        {{ card.label }}
                                    </p>
                                    <p class="mt-1 text-lg font-semibold text-[#171411]">
                                        {{ card.value }}
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                                        {{ card.detail }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="continueSetupEvent"
                        class="mt-4 rounded-[1.25rem] border border-amber-200 bg-amber-50/80 px-4 py-3"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-amber-950">
                                    {{ continueSetupEvent.name }} still needs setup.
                                </p>
                                <p class="text-sm text-amber-800">
                                    Finish onboarding, then manage it here with the rest of your events.
                                </p>
                            </div>
                            <Button as-child size="sm" class="bg-[#171411] text-white hover:bg-[#2b2621]">
                                <Link :href="continueSetupEvent.primaryAction.url">
                                    Continue setup
                                </Link>
                            </Button>
                        </div>
                    </div>

                </section>

                <section id="events" class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-2 border-b border-black/5 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                Events
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                Pick an event, then open workspace, media, or settings directly.
                            </p>
                        </div>
                        <p class="text-sm text-zinc-500">
                            {{ totalAccessibleEvents }} total
                        </p>
                    </div>

                    <div
                        v-if="ownedEvents.length === 0 && collaboratorEvents.length === 0"
                        class="py-10 text-sm leading-6 text-zinc-600"
                    >
                        No events are linked to this account yet.
                    </div>

                    <div v-else class="space-y-5 pt-5">
                        <div v-if="ownedEvents.length > 0" class="space-y-3">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                Your events
                            </p>

                            <article
                                v-for="event in ownedEvents"
                                :key="`owner-${event.id}`"
                                class="rounded-[1.25rem] border border-black/6 bg-[#fcfbf8] px-4 py-4"
                            >
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                                :class="badgeClass(event.statusTone)"
                                            >
                                                {{ event.statusLabel }}
                                            </span>
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                                :class="badgeClass(event.billingTone)"
                                            >
                                                {{ event.billingLabel }}
                                            </span>
                                            <span class="text-xs text-zinc-500">
                                                {{ event.plan }}
                                            </span>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-semibold text-[#171411] sm:text-base">
                                                {{ event.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-zinc-600">
                                                {{ formatDateOnly(event.eventDate) }} ·
                                                {{ event.guestCount }} guests ·
                                                {{ event.assetCount }} uploads ·
                                                {{ event.processingCount }} pending
                                            </p>
                                            <p class="mt-1 text-xs text-zinc-500">
                                                {{
                                                    event.lastUploadAt
                                                        ? `Last upload ${formatDateTime(event.lastUploadAt)}`
                                                        : 'No guest uploads yet'
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            as-child
                                            size="sm"
                                            class="bg-[#171411] text-white hover:bg-[#2b2621]"
                                        >
                                            <Link :href="event.primaryAction.url">
                                                {{ workspaceLabel(event) }}
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.media">
                                                Media
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.settings">
                                                Settings
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-if="collaboratorEvents.length > 0" class="space-y-3">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                Shared with you
                            </p>

                            <article
                                v-for="event in collaboratorEvents"
                                :key="`collab-${event.id}`"
                                class="rounded-[1.25rem] border border-black/6 bg-[#fcfbf8] px-4 py-4"
                            >
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                                :class="badgeClass(event.roleTone)"
                                            >
                                                {{ event.roleLabel }}
                                            </span>
                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold"
                                                :class="badgeClass(event.statusTone)"
                                            >
                                                {{ event.statusLabel }}
                                            </span>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-semibold text-[#171411] sm:text-base">
                                                {{ event.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-zinc-600">
                                                {{ formatDateOnly(event.eventDate) }} ·
                                                {{ event.assetCount }} uploads ·
                                                {{ event.processingCount }} pending
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <Button as-child size="sm" class="bg-[#171411] text-white hover:bg-[#2b2621]">
                                            <Link :href="event.links.dashboard">
                                                Workspace
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.media">
                                                Media
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.settings">
                                                Settings
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="activity" class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-2 border-b border-black/5 pb-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                Recent activity
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                One line per upload, so you can scan what changed fast.
                            </p>
                        </div>
                        <p class="text-sm text-zinc-500">
                            {{ recentActivity.length }} shown
                        </p>
                    </div>

                    <div v-if="recentActivity.length === 0" class="py-8 text-sm text-zinc-600">
                        No recent uploads yet.
                    </div>

                    <div v-else class="divide-y divide-black/5">
                        <Link
                            v-for="activity in recentActivity"
                            :key="activity.id"
                            :href="activity.activityUrl"
                            class="flex flex-col gap-2 py-3 transition hover:bg-black/[0.015] sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm text-[#171411]">
                                    <span class="font-semibold">{{ activity.guestName }}</span>
                                    in
                                    <span class="font-medium">{{ activity.eventName }}</span>
                                    ·
                                    <span class="text-zinc-600">{{ activity.summary }}</span>
                                </p>
                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ formatDateTime(activity.createdAt) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold capitalize"
                                    :class="moderationBadgeClass(activity.moderationStatus)"
                                >
                                    {{ activity.moderationStatus }}
                                </span>
                                <ExternalLink class="size-4 text-zinc-400" />
                            </div>
                        </Link>
                    </div>
                </section>
            </div>

            <Dialog :open="modalOpen" @update:open="modalOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Event ready</DialogTitle>
                        <DialogDescription>
                            Everything now lives on the Events page. Open the workspace when you need detail, or jump straight into media or settings.
                        </DialogDescription>
                    </DialogHeader>
                    <Button
                        as-child
                        class="mt-2 bg-[#171411] text-white hover:bg-[#2b2621]"
                    >
                        <Link
                            :href="
                                continueSetupEvent?.links.dashboard
                                ?? ownedEvents[0]?.links.dashboard
                                ?? collaboratorEvents[0]?.links.dashboard
                                ?? dashboardLinks.overview
                            "
                        >
                            Open event
                        </Link>
                    </Button>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
