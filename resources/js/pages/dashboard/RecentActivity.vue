<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    CheckCircle2,
    Image as ImageIcon,
    Video,
} from 'lucide-vue-next';
import DashboardMetricCard from '@/components/dashboard/DashboardMetricCard.vue';
import { formatDateTime, moderationBadgeClass } from '@/lib/dashboard';
import AppLayout from '@/layouts/AppLayout.vue';
import type {
    BreadcrumbItem,
    DashboardLinks,
    RecentActivity,
    Summary,
} from '@/types';

const props = defineProps<{
    summary: Summary;
    dashboardLinks: DashboardLinks;
    recentActivity: RecentActivity[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: props.dashboardLinks.overview,
    },
    {
        title: 'Recent Activity',
        href: props.dashboardLinks.recentActivity,
    },
];

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
    <Head title="Recent Activity" />

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
                                    Recent activity
                                </h1>
                                <p
                                    class="max-w-2xl text-sm leading-6 text-white/72 md:text-base"
                                >
                                    A fuller activity feed for the account.
                                    Clicking any item opens the exact upload in
                                    the event media workspace.
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
                                    :href="props.dashboardLinks.ownedEvents"
                                    class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14"
                                >
                                    Owned events
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-3 xl:p-6">
                        <DashboardMetricCard
                            label="Activity items"
                            :value="recentActivity.length"
                        />
                        <DashboardMetricCard
                            label="Guest uploads"
                            :value="summary.totalUploadCount"
                        />
                        <DashboardMetricCard
                            label="Pending review"
                            :value="summary.pendingModerationCount"
                        />
                    </div>
                </section>

                <section
                    class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6"
                >
                    <div
                        v-if="recentActivity.length === 0"
                        class="py-12 text-center"
                    >
                        <div
                            class="mx-auto flex max-w-md flex-col items-center gap-4"
                        >
                            <div
                                class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]"
                            >
                                <CalendarDays class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2
                                    class="text-xl font-semibold text-[#171411]"
                                >
                                    Nothing to review yet
                                </h2>
                                <p class="text-sm leading-6 text-zinc-600">
                                    Once guests start uploading, this page
                                    becomes the fast lane into the exact asset.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-4">
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
                                            >{{ activity.guestName }}</span
                                        >
                                        <span class="text-sm text-zinc-500"
                                            >in {{ activity.eventName }}</span
                                        >
                                    </div>
                                    <p class="text-sm leading-6 text-zinc-600">
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
                                        <span class="text-xs text-zinc-500">{{
                                            formatDateTime(
                                                activity.createdAt,
                                                'No timestamp',
                                            )
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
