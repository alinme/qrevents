<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    Camera,
    Download,
    ExternalLink,
    FolderKanban,
    Settings,
    Users,
} from 'lucide-vue-next';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import { badgeClass, formatDateOnly, formatDateTime } from '@/lib/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    DashboardEvent,
    DashboardLinks,
    Summary,
} from '@/types';

const props = defineProps<{
    summary: Summary;
    dashboardLinks: DashboardLinks;
    ownedEvents: DashboardEvent[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: props.dashboardLinks.overview,
    },
    {
        title: 'Owned Events',
        href: props.dashboardLinks.ownedEvents,
    },
];
</script>

<template>
    <Head title="Owned Events" />

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
                            class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between"
                        >
                            <div class="space-y-2">
                                <div
                                    class="inline-flex w-fit items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold tracking-[0.24em] text-white/80 uppercase"
                                >
                                    Account dashboard
                                </div>
                                <h1
                                    class="text-3xl font-semibold tracking-tight md:text-4xl"
                                >
                                    Owned events
                                </h1>
                                <p
                                    class="max-w-2xl text-sm leading-6 text-white/72 md:text-base"
                                >
                                    Every event you control, with quick routes
                                    into the actual workspace, media queue, and
                                    event settings.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    :href="props.dashboardLinks.overview"
                                    class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14"
                                >
                                    Back to overview
                                </Link>
                                <Link
                                    v-if="props.dashboardLinks.business"
                                    :href="props.dashboardLinks.business"
                                    class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14"
                                >
                                    Business
                                </Link>
                                <Link
                                    :href="props.dashboardLinks.recentActivity"
                                    class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14"
                                >
                                    Recent activity
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-3 xl:p-6">
                        <DashboardMetricCard
                            label="Owned events"
                            :value="summary.ownedEventCount"
                        />
                        <DashboardMetricCard
                            label="Setup queue"
                            :value="summary.pendingSetupCount"
                        />
                        <DashboardMetricCard
                            label="Exports ready"
                            :value="summary.readyExportCount"
                        />
                    </div>
                </section>

                <section
                    class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                >
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
                                <h2
                                    class="text-xl font-semibold text-[#171411]"
                                >
                                    No owned events yet
                                </h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Start your first event from the main
                                    overview and it will show up here.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-5">
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
                                                    badgeClass(event.roleTone)
                                                "
                                            >
                                                {{ event.roleLabel }}
                                            </span>
                                            <span
                                                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="
                                                    badgeClass(event.statusTone)
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
                                            <h2
                                                class="text-2xl font-semibold tracking-tight text-[#171411]"
                                            >
                                                {{ event.name }}
                                            </h2>
                                            <p class="text-sm text-zinc-600">
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
                                            Latest activity
                                        </p>
                                        <p
                                            class="mt-2 text-base font-semibold text-[#171411]"
                                        >
                                            {{
                                                event.onboardingComplete
                                                    ? 'Workspace ready'
                                                    : 'Setup still in progress'
                                            }}
                                        </p>
                                        <p class="mt-1 text-sm text-zinc-500">
                                            {{
                                                formatDateTime(
                                                    event.lastUploadAt,
                                                    'No uploads yet',
                                                )
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
                                        <Users class="mr-1 inline size-4" />
                                        {{ event.guestCount }} guests
                                    </span>
                                    <span
                                        class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                    >
                                        <Camera class="mr-1 inline size-4" />
                                        {{ event.assetCount }} uploads
                                    </span>
                                    <span
                                        class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                    >
                                        <CalendarDays
                                            class="mr-1 inline size-4"
                                        />
                                        {{ event.processingCount }} pending
                                        review
                                    </span>
                                    <span
                                        class="rounded-full border border-black/8 bg-white px-3 py-1.5"
                                    >
                                        {{ event.mediaExportLabel }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <Link
                                        :href="event.primaryAction.url"
                                        class="inline-flex items-center rounded-md bg-[#171411] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#2b2621]"
                                    >
                                        {{ event.primaryAction.label }}
                                    </Link>
                                    <Link
                                        :href="event.links.media"
                                        class="inline-flex items-center gap-2 rounded-md border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#faf7f1]"
                                    >
                                        <Camera class="size-4" />
                                        Review media
                                    </Link>
                                    <Link
                                        :href="event.links.settings"
                                        class="inline-flex items-center gap-2 rounded-md border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#faf7f1]"
                                    >
                                        <Settings class="size-4" />
                                        Settings
                                    </Link>
                                    <Link
                                        :href="event.links.album"
                                        class="inline-flex items-center gap-2 rounded-md border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#faf7f1]"
                                    >
                                        <ExternalLink class="size-4" />
                                        Album
                                    </Link>
                                    <Link
                                        v-if="
                                            event.canManage &&
                                            event.mediaExportStatus === 'ready'
                                        "
                                        :href="event.links.mediaExportDownload"
                                        class="inline-flex items-center gap-2 rounded-md border border-black/10 bg-white px-4 py-2 text-sm font-medium text-[#171411] transition hover:bg-[#faf7f1]"
                                    >
                                        <Download class="size-4" />
                                        Download export
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
