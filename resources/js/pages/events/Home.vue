<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CalendarDays,
    CheckCircle2,
    Clock3,
    Copy,
    Download,
    ExternalLink,
    Image as ImageIcon,
    LoaderCircle,
    MessageSquareText,
    Settings,
    ShieldX,
    Users,
    Video,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import {
    Item,
    ItemActions,
    ItemContent,
    ItemDescription,
    ItemMedia,
    ItemTitle,
} from '@/components/ui/item';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    plan: string;
    planFeatures: {
        allowsDownloadAll: boolean;
        allowsModerationTools: boolean;
        customizationTier: 'basic' | 'better' | 'advanced';
        uploadWindowDays: number;
    };
    eventDate: string | null;
    timezone: string;
    uploadWindowStartsAt: string | null;
    uploadWindowEndsAt: string | null;
    uploadCount: number;
    uploadLimit: number;
    storageUsedBytes: number;
    storageLimitBytes: number;
    moderationEnabled: boolean;
    autoModerationEnabled: boolean;
    moderationSummary: {
        processingCount: number;
        autoRejectedCount: number;
        approvedTodayCount: number;
    };
    mediaExport: {
        status: 'idle' | 'pending' | 'processing' | 'ready' | 'failed';
        requestedAt: string | null;
        startedAt: string | null;
        completedAt: string | null;
        failedAt: string | null;
        error: string | null;
    };
    billing: {
        statusCode: 'paid' | 'locked' | 'pending';
        statusLabel: string;
        statusHint: string;
        canManage: boolean;
    };
};

type DashboardStats = {
    guestCount: number;
    photoCount: number;
    videoCount: number;
    textCount: number;
    approvedCount: number;
    rejectedCount: number;
    storageRemainingBytes: number;
    uploadRemaining: number;
    lastUploadAt: string | null;
};

type RecentUpload = {
    id: number;
    kind: 'photo' | 'video' | 'text';
    guestName: string;
    message: string | null;
    text: string | null;
    moderationStatus: 'approved' | 'rejected' | 'processing';
    createdAt: string | null;
};

type EventLinks = {
    accountDashboard: string;
    dashboard: string;
    media: string;
    mediaExportStart: string;
    mediaExportDownload: string;
    settings: string;
    album: string;
    wall: string;
    albumQrDataUrl: string;
    wallQrDataUrl: string;
};

const props = defineProps<{
    currentEvent: EventPayload;
    dashboardStats: DashboardStats;
    dashboardRecentUploads: RecentUpload[];
    eventLinks: EventLinks;
    showDashboardModal: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.dashboard,
    },
];

const modalOpen = ref(props.showDashboardModal);
const exportSubmitting = ref(false);

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'Unknown';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatDateOnly = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'long',
    }).format(new Date(value));
};

const formatBytes = (bytes: number): string => {
    if (bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const index = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** index;

    return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
};

const uploadWindowLabel = computed(() => {
    if (!props.currentEvent.uploadWindowStartsAt || !props.currentEvent.uploadWindowEndsAt) {
        return 'Upload window not scheduled yet';
    }

    return `${formatDateTime(props.currentEvent.uploadWindowStartsAt)} - ${formatDateTime(props.currentEvent.uploadWindowEndsAt)}`;
});

const storageUsageLabel = computed(
    () =>
        `${formatBytes(props.currentEvent.storageUsedBytes)} / ${formatBytes(props.currentEvent.storageLimitBytes)}`,
);

const uploadUsageLabel = computed(
    () => `${props.currentEvent.uploadCount} / ${props.currentEvent.uploadLimit}`,
);

const moderationModeLabel = computed(() => {
    if (!props.currentEvent.moderationEnabled) {
        return 'Moderation off';
    }

    return props.currentEvent.autoModerationEnabled
        ? 'Automatic filter + manual tools'
        : 'Manual approval only';
});

const canDownloadAll = computed(
    () => props.currentEvent.planFeatures.allowsDownloadAll,
);

const mediaExportBusy = computed(
    () =>
        canDownloadAll.value
        && (
            exportSubmitting.value
            || ['pending', 'processing'].includes(props.currentEvent.mediaExport.status)
        ),
);

const mediaExportReady = computed(
    () => props.currentEvent.mediaExport.status === 'ready',
);

const showBillingBanner = computed(
    () => props.currentEvent.billing.statusCode !== 'paid',
);

const billingBannerTitle = computed(() => {
    if (props.currentEvent.billing.statusCode === 'locked') {
        return 'This event is locked until payment is confirmed.';
    }

    return 'Billing still needs attention for this event.';
});

const billingActionLabel = computed(() =>
    props.currentEvent.billing.canManage ? 'Review billing' : 'View billing status',
);

const mediaExportLabel = computed(() => {
    if (!canDownloadAll.value) {
        return 'Upgrade for ZIP export';
    }

    if (mediaExportBusy.value) {
        return 'Exporting...';
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return 'Retry export';
    }

    return 'Download Album';
});

const mediaExportHint = computed(() => {
    if (!canDownloadAll.value) {
        return 'Download-all ZIP exports unlock on Plus and Pro after payment.';
    }

    if (mediaExportBusy.value) {
        return 'You can leave this page and come back while the export is being prepared.';
    }

    if (mediaExportReady.value) {
        return props.currentEvent.mediaExport.completedAt
            ? `Ready since ${formatDateTime(props.currentEvent.mediaExport.completedAt)}`
            : 'The album export is ready to download.';
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return props.currentEvent.mediaExport.error || 'The last export failed. Start it again.';
    }

    return 'Build a ZIP of the approved event album for the owner dashboard.';
});

const recentUploadSummary = (upload: RecentUpload): string => {
    if (upload.kind === 'text') {
        return upload.text?.trim() || 'Text post';
    }

    return upload.message?.trim() || `${upload.kind} upload`;
};

const moderationToneClass = (status: RecentUpload['moderationStatus']): string => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-700';
        case 'rejected':
            return 'bg-rose-100 text-rose-700';
        default:
            return 'bg-amber-100 text-amber-700';
    }
};

const copyText = async (value: string, successMessage: string): Promise<void> => {
    if (
        typeof navigator === 'undefined' ||
        !navigator.clipboard ||
        typeof navigator.clipboard.writeText !== 'function'
    ) {
        toast.error('Copy is not available on this device.');
        return;
    }

    await navigator.clipboard.writeText(value);
    toast.success(successMessage);
};

const handleMediaExport = (): void => {
    if (!canDownloadAll.value) {
        router.visit(`${props.eventLinks.settings}?tab=billing`);
        return;
    }

    if (mediaExportBusy.value) {
        return;
    }

    if (mediaExportReady.value) {
        window.location.assign(props.eventLinks.mediaExportDownload);
        return;
    }

    exportSubmitting.value = true;

    router.post(
        props.eventLinks.mediaExportStart,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                exportSubmitting.value = false;
            },
        },
    );
};
</script>

<template>
    <Head :title="currentEvent.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_85%_10%,_rgba(251,191,36,0.16),_transparent_22%)]">
            <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 p-4 md:p-6">
                <Item
                    v-if="showBillingBanner"
                    variant="outline"
                    class="border-amber-200 bg-[linear-gradient(135deg,#fff7db_0%,#fde9a7_100%)] shadow-sm"
                >
                    <ItemMedia variant="icon" class="bg-white text-amber-700 shadow-sm">
                        <AlertCircle class="size-4" />
                    </ItemMedia>
                    <ItemContent>
                        <ItemTitle class="text-[#171411]">{{ billingBannerTitle }}</ItemTitle>
                        <ItemDescription class="text-[#5c4a2d]">
                            {{ currentEvent.billing.statusHint }}
                        </ItemDescription>
                    </ItemContent>
                    <ItemActions>
                        <Button as-child size="sm" class="bg-[#171411] text-white hover:bg-[#2b2621]">
                            <Link :href="eventLinks.settings">{{ billingActionLabel }}</Link>
                        </Button>
                    </ItemActions>
                </Item>

                <section class="overflow-hidden rounded-[2rem] border border-black/5 bg-white shadow-sm">
                    <div class="border-b border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2d251f_46%,#5f533f_100%)] px-6 py-6 text-white">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/68">
                                        Event workspace
                                    </p>
                                    <h1 class="mt-2 text-3xl font-semibold text-white">
                                        {{ currentEvent.name }}
                                    </h1>
                                </div>
                                <div class="flex flex-wrap gap-2 text-sm text-white/72">
                                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5">
                                        Plan: {{ currentEvent.plan }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5">
                                        {{ moderationModeLabel }}
                                    </span>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[1.25rem] border border-white/10 bg-white/8 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/58">
                                            Event date
                                        </p>
                                        <p class="mt-2 text-sm font-medium text-white">
                                            {{ formatDateOnly(currentEvent.eventDate) }}
                                        </p>
                                    </div>
                                    <div class="rounded-[1.25rem] border border-white/10 bg-white/8 px-4 py-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/58">
                                            Upload window
                                        </p>
                                        <p class="mt-2 text-sm font-medium text-white">
                                            {{ uploadWindowLabel }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2 lg:w-[22rem]">
                                <Button as-child variant="outline" class="h-11 justify-start border-white/14 bg-white/8 text-white hover:bg-white/14 hover:text-white">
                                    <a :href="eventLinks.accountDashboard">
                                        <ExternalLink class="mr-2 size-4" />
                                        Dashboard
                                    </a>
                                </Button>
                                <Button as-child variant="outline" class="h-11 justify-start border-white/14 bg-white text-[#171411] hover:bg-[#f8f3eb] hover:text-[#171411]">
                                    <a :href="eventLinks.media">
                                        <ExternalLink class="mr-2 size-4" />
                                        Media
                                    </a>
                                </Button>
                                <Button as-child variant="outline" class="h-11 justify-start border-white/14 bg-white text-[#171411] hover:bg-[#f8f3eb] hover:text-[#171411]">
                                    <a :href="eventLinks.settings">
                                        <Settings class="mr-2 size-4" />
                                        Settings
                                    </a>
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-11 justify-start border-white/14 bg-white text-[#171411] hover:bg-[#f8f3eb] hover:text-[#171411]"
                                    :disabled="mediaExportBusy"
                                    data-test="export-album-button"
                                    @click="handleMediaExport"
                                >
                                    <LoaderCircle v-if="mediaExportBusy" class="mr-2 size-4 animate-spin" />
                                    <Download v-else class="mr-2 size-4" />
                                    {{ mediaExportLabel }}
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-11 justify-start border-white/14 bg-white text-[#171411] hover:bg-[#f8f3eb] hover:text-[#171411]"
                                    @click="copyText(eventLinks.album, 'Album link copied.')"
                                >
                                    <Copy class="mr-2 size-4" />
                                    Copy album link
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-11 justify-start border-white/14 bg-white text-[#171411] hover:bg-[#f8f3eb] hover:text-[#171411]"
                                    @click="copyText(eventLinks.wall, 'Photo wall link copied.')"
                                >
                                    <Copy class="mr-2 size-4" />
                                    Copy wall link
                                </Button>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-white/62">
                            {{ mediaExportHint }}
                        </p>
                    </div>
                </section>

                <section class="grid gap-3 md:grid-cols-4">
                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                            <Users class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Guests
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ dashboardStats.guestCount }}
                            </p>
                        </div>
                    </div>
                </article>

                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-violet-100 text-violet-700">
                            <ImageIcon class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Media
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ dashboardStats.photoCount + dashboardStats.videoCount + dashboardStats.textCount }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ dashboardStats.photoCount }} photos, {{ dashboardStats.videoCount }} videos, {{ dashboardStats.textCount }} text posts
                    </p>
                </article>

                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                            <CalendarDays class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Upload usage
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ uploadUsageLabel }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ dashboardStats.uploadRemaining }} uploads remaining
                    </p>
                </article>

                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <CheckCircle2 class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Storage
                            </p>
                            <p class="text-lg font-semibold text-slate-900">
                                {{ storageUsageLabel }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ formatBytes(dashboardStats.storageRemainingBytes) }} remaining
                    </p>
                </article>
                </section>

                <section class="grid gap-3 md:grid-cols-3">
                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                            <Clock3 class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Processing
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ currentEvent.moderationSummary.processingCount }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        Uploads waiting for manual review.
                    </p>
                </article>

                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                            <ShieldX class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Auto-Rejected
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ currentEvent.moderationSummary.autoRejectedCount }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        Blocked automatically by the active filter rules.
                    </p>
                </article>

                <article class="rounded-[1.4rem] border bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <CheckCircle2 class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Approved Today
                            </p>
                            <p class="text-2xl font-semibold text-slate-900">
                                {{ currentEvent.moderationSummary.approvedTodayCount }}
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">
                        Items approved today across manual and automatic review.
                    </p>
                </article>
                </section>

                <section class="grid gap-4 lg:grid-cols-2">
                    <article class="rounded-[1.75rem] border bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-950">Digital album</h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Share the album link or QR code so guests can upload and browse from their phones.
                                </p>
                            </div>
                            <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-2">
                                <img
                                    :src="eventLinks.albumQrDataUrl"
                                    alt="Digital album QR code"
                                    class="size-28"
                                />
                            </div>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <Button as-child>
                                <a :href="eventLinks.album" target="_blank" rel="noopener noreferrer">
                                    <ExternalLink class="mr-2 size-4" />
                                    Open album
                                </a>
                            </Button>
                            <Button variant="outline" @click="copyText(eventLinks.album, 'Album link copied.')">
                                <Copy class="mr-2 size-4" />
                                Copy link
                            </Button>
                            <Button as-child variant="outline">
                                <a :href="eventLinks.albumQrDataUrl" download="digital-album-qr.svg">
                                    <Download class="mr-2 size-4" />
                                    Download QR
                                </a>
                            </Button>
                        </div>
                    </article>

                    <article class="rounded-[1.75rem] border bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-950">Photo wall</h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Open the live wall on a projector, TV, or laptop and let it refresh during the event.
                                </p>
                            </div>
                            <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 p-2">
                                <img
                                    :src="eventLinks.wallQrDataUrl"
                                    alt="Photo wall QR code"
                                    class="size-28"
                                />
                            </div>
                        </div>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <Button as-child>
                                <a :href="eventLinks.wall" target="_blank" rel="noopener noreferrer">
                                    <ExternalLink class="mr-2 size-4" />
                                    Open wall
                                </a>
                            </Button>
                            <Button variant="outline" @click="copyText(eventLinks.wall, 'Photo wall link copied.')">
                                <Copy class="mr-2 size-4" />
                                Copy link
                            </Button>
                            <Button as-child variant="outline">
                                <a :href="eventLinks.wallQrDataUrl" download="photo-wall-qr.svg">
                                    <Download class="mr-2 size-4" />
                                    Download QR
                                </a>
                            </Button>
                        </div>
                    </article>
                </section>

                <section>
                    <article class="rounded-[1.75rem] border bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-950">Recent uploads</h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Latest guest activity and moderation status.
                                </p>
                            </div>
                            <Button as-child variant="outline" size="sm">
                                <a :href="eventLinks.media">Open media</a>
                            </Button>
                        </div>

                        <div class="mt-5">
                            <Empty
                                v-if="dashboardRecentUploads.length === 0"
                                class="border-0 bg-transparent py-8 shadow-none"
                            >
                                <EmptyHeader>
                                    <EmptyMedia variant="icon" class="bg-slate-100 text-slate-500">
                                        <ImageIcon class="size-5" />
                                    </EmptyMedia>
                                    <EmptyTitle class="text-slate-900">No uploads yet</EmptyTitle>
                                    <EmptyDescription class="text-slate-500">
                                        As soon as guests start sharing, the latest uploads will appear here.
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>

                            <div v-else class="space-y-3">
                                <article
                                    v-for="upload in dashboardRecentUploads"
                                    :key="upload.id"
                                    class="rounded-[1.2rem] border border-slate-200 bg-slate-50 p-4"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-slate-900">
                                                    {{ upload.guestName }}
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    {{ formatDateTime(upload.createdAt) }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-slate-600">
                                                {{ recentUploadSummary(upload) }}
                                            </p>
                                        </div>
                                        <span
                                            class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                            :class="moderationToneClass(upload.moderationStatus)"
                                        >
                                            {{ upload.moderationStatus }}
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                                        <ImageIcon v-if="upload.kind === 'photo'" class="size-4" />
                                        <Video v-else-if="upload.kind === 'video'" class="size-4" />
                                        <MessageSquareText v-else class="size-4" />
                                        <span class="capitalize">{{ upload.kind }}</span>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="modalOpen">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Your event is all set!</DialogTitle>
                <DialogDescription>
                    Waiting just behind this message is your dashboard, where you can review uploads,
                    share the album and wall, and manage event settings.
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>
