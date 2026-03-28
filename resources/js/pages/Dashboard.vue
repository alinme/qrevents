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
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Events
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                Pick up where your event left off
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">
                                {{ ownerName }}, open the workspace you need and keep moving.
                            </p>
                        </div>
                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="card in compactStats"
                                :key="card.label"
                                class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4 xl:first:border-l-0 xl:first:pl-0"
                            >
                                <dt class="flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                    <component :is="card.icon" class="size-3.5 text-zinc-400" />
                                    {{ card.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-[#171411]">
                                    {{ card.value }}
                                </dd>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">
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

                </section>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.9fr)]">
                    <section id="events" class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="flex flex-col gap-2 border-b border-black/5 pb-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                    Your events
                                </h2>
                                <p class="mt-1 text-sm text-zinc-600">
                                    Open the right workspace, media view, or settings page fast.
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
                                    class="border-b border-black/5 py-4 last:border-b-0"
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
                                    class="border-b border-black/5 py-4 last:border-b-0"
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

                    <aside id="activity" class="flex min-h-0 rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="flex min-h-0 w-full flex-col">
                        <div class="flex flex-col gap-2 border-b border-black/5 pb-4">
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                Recent activity
                            </h2>
                            <p class="text-sm text-zinc-600">
                                Quick updates from your guest uploads.
                            </p>
                        </div>

                        <div v-if="recentActivity.length === 0" class="py-8 text-sm text-zinc-600">
                            No recent uploads yet.
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
                                    <p class="text-sm text-[#171411]">
                                        <span class="font-semibold">{{ activity.guestName }}</span>
                                        in
                                        <span class="font-medium">{{ activity.eventName }}</span>
                                    </p>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        {{ activity.summary }}
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
                        </div>
                        </div>
                    </aside>
                </div>
            </div>

                <Dialog :open="modalOpen" @update:open="modalOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Event ready</DialogTitle>
                        <DialogDescription>
                            Everything now starts from your event list. Open the workspace you need and keep going.
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
