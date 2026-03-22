<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    CheckCircle2,
    Clock3,
    Copy,
    Download,
    Image as ImageIcon,
    LoaderCircle,
    MessageSquareText,
    Settings,
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

const formatDateTime = (value: string | null, fallback = 'Unknown'): string => {
    if (!value) {
        return fallback;
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
        return 'Not scheduled yet';
    }

    return `${formatDateTime(props.currentEvent.uploadWindowStartsAt)} - ${formatDateTime(props.currentEvent.uploadWindowEndsAt)}`;
});

const storageUsageLabel = computed(
    () => `${formatBytes(props.currentEvent.storageUsedBytes)} / ${formatBytes(props.currentEvent.storageLimitBytes)}`,
);

const uploadUsageLabel = computed(
    () => `${props.currentEvent.uploadCount} / ${props.currentEvent.uploadLimit}`,
);

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

const billingActionLabel = computed(() =>
    props.currentEvent.billing.canManage ? 'Open billing' : 'View billing',
);

const mediaExportLabel = computed(() => {
    if (!canDownloadAll.value) {
        return 'Upgrade for export';
    }

    if (mediaExportBusy.value) {
        return 'Exporting...';
    }

    if (mediaExportReady.value) {
        return 'Download album';
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return 'Retry export';
    }

    return 'Build album export';
});

const mediaExportHint = computed(() => {
    if (!canDownloadAll.value) {
        return 'ZIP export unlocks on paid plans.';
    }

    if (mediaExportBusy.value) {
        return 'Export is being prepared in the background.';
    }

    if (mediaExportReady.value) {
        return props.currentEvent.mediaExport.completedAt
            ? `Ready since ${formatDateTime(props.currentEvent.mediaExport.completedAt)}`
            : 'Ready to download.';
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return props.currentEvent.mediaExport.error || 'The previous export failed.';
    }

    return 'Create a ZIP of the approved album when you need a handoff.';
});

const summaryItems = computed(() => [
    {
        label: 'Guests',
        value: String(props.dashboardStats.guestCount),
        detail: props.dashboardStats.lastUploadAt
            ? `Last upload ${formatDateTime(props.dashboardStats.lastUploadAt)}`
            : 'No uploads yet',
        icon: Users,
    },
    {
        label: 'Uploads',
        value: uploadUsageLabel.value,
        detail: `${props.dashboardStats.uploadRemaining} remaining`,
        icon: ImageIcon,
    },
    {
        label: 'Pending review',
        value: String(props.currentEvent.moderationSummary.processingCount),
        detail: `${props.dashboardStats.approvedCount} approved`,
        icon: Clock3,
    },
    {
        label: 'Storage',
        value: storageUsageLabel.value,
        detail: `${formatBytes(props.dashboardStats.storageRemainingBytes)} free`,
        icon: CheckCircle2,
    },
]);

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
        typeof navigator === 'undefined'
        || !navigator.clipboard
        || typeof navigator.clipboard.writeText !== 'function'
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
        <div class="min-h-full bg-[#faf7f2]">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-5 p-4 md:p-6">
                <section
                    v-if="showBillingBanner"
                    class="rounded-[1.25rem] border border-amber-200 bg-amber-50/80 px-4 py-3"
                >
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="rounded-full bg-white p-2 text-amber-700 shadow-sm">
                                <AlertCircle class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-amber-950">
                                    {{ currentEvent.billing.statusLabel }}
                                </p>
                                <p class="text-sm text-amber-800">
                                    {{ currentEvent.billing.statusHint }}
                                </p>
                            </div>
                        </div>

                        <Button as-child size="sm" class="bg-[#171411] text-white hover:bg-[#2b2621]">
                            <Link :href="eventLinks.settings">
                                {{ billingActionLabel }}
                            </Link>
                        </Button>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Workspace
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                {{ currentEvent.name }}
                            </h1>
                            <p class="mt-2 text-sm text-zinc-600">
                                {{ currentEvent.plan }} · {{ formatDateOnly(currentEvent.eventDate) }} · {{ uploadWindowLabel }}
                            </p>
                            <p class="mt-2 text-sm text-zinc-500">
                                {{ mediaExportHint }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button as-child size="sm" variant="outline">
                                <Link :href="eventLinks.accountDashboard">
                                    Events
                                </Link>
                            </Button>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="eventLinks.media">
                                    Media
                                </Link>
                            </Button>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="eventLinks.settings">
                                    <Settings class="mr-2 size-4" />
                                    Settings
                                </Link>
                            </Button>
                            <Button
                                size="sm"
                                class="bg-[#171411] text-white hover:bg-[#2b2621]"
                                :disabled="mediaExportBusy"
                                data-test="export-album-button"
                                @click="handleMediaExport"
                            >
                                <LoaderCircle v-if="mediaExportBusy" class="mr-2 size-4 animate-spin" />
                                <Download v-else class="mr-2 size-4" />
                                {{ mediaExportLabel }}
                            </Button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <article
                            v-for="item in summaryItems"
                            :key="item.label"
                            class="rounded-[1rem] border border-black/6 bg-[#fcfbf8] px-3.5 py-3"
                        >
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-white p-2 text-[#171411] shadow-sm">
                                    <component :is="item.icon" class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                        {{ item.label }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411] sm:text-base">
                                        {{ item.value }}
                                    </p>
                                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                                        {{ item.detail }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="border-b border-black/5 pb-4">
                        <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                            Share links
                        </h2>
                        <p class="mt-1 text-sm text-zinc-600">
                            Open, copy, or download the guest-facing album and wall.
                        </p>
                    </div>

                    <div class="divide-y divide-black/5">
                        <div class="flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-4">
                                <img
                                    :src="eventLinks.albumQrDataUrl"
                                    alt="Digital album QR code"
                                    class="size-20 rounded-[1rem] border border-slate-200 bg-white p-2"
                                />
                                <div>
                            <h3 class="text-sm font-semibold text-[#171411]">
                                Digital album
                            </h3>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        Guests upload and browse from here.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button as-child size="sm" variant="outline">
                                    <a :href="eventLinks.album" target="_blank" rel="noopener noreferrer">
                                        Open
                                    </a>
                                </Button>
                                <Button size="sm" variant="outline" @click="copyText(eventLinks.album, 'Album link copied.')">
                                    <Copy class="mr-2 size-4" />
                                    Copy
                                </Button>
                                <Button as-child size="sm" variant="outline">
                                    <a :href="eventLinks.albumQrDataUrl" download="digital-album-qr.svg">
                                        <Download class="mr-2 size-4" />
                                        QR
                                    </a>
                                </Button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-4">
                                <img
                                    :src="eventLinks.wallQrDataUrl"
                                    alt="Photo wall QR code"
                                    class="size-20 rounded-[1rem] border border-slate-200 bg-white p-2"
                                />
                                <div>
                            <h3 class="text-sm font-semibold text-[#171411]">
                                Photo wall
                            </h3>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        Open this on a screen during the event.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button as-child size="sm" variant="outline">
                                    <a :href="eventLinks.wall" target="_blank" rel="noopener noreferrer">
                                        Open
                                    </a>
                                </Button>
                                <Button size="sm" variant="outline" @click="copyText(eventLinks.wall, 'Photo wall link copied.')">
                                    <Copy class="mr-2 size-4" />
                                    Copy
                                </Button>
                                <Button as-child size="sm" variant="outline">
                                    <a :href="eventLinks.wallQrDataUrl" download="photo-wall-qr.svg">
                                        <Download class="mr-2 size-4" />
                                        QR
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex items-end justify-between gap-4 border-b border-black/5 pb-4">
                        <div>
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                Recent uploads
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                Short, scannable updates from the guest album.
                            </p>
                        </div>
                        <Button as-child size="sm" variant="outline">
                            <Link :href="eventLinks.media">
                                Open media
                            </Link>
                        </Button>
                    </div>

                    <div v-if="dashboardRecentUploads.length === 0" class="py-8 text-sm text-zinc-600">
                        No uploads yet.
                    </div>

                    <div v-else class="divide-y divide-black/5">
                        <div
                            v-for="upload in dashboardRecentUploads"
                            :key="upload.id"
                            class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm text-[#171411]">
                                    <span class="font-semibold">{{ upload.guestName }}</span>
                                    ·
                                    <span class="text-zinc-600">{{ recentUploadSummary(upload) }}</span>
                                </p>
                                <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                    <ImageIcon v-if="upload.kind === 'photo'" class="size-4" />
                                    <Video v-else-if="upload.kind === 'video'" class="size-4" />
                                    <MessageSquareText v-else class="size-4" />
                                    <span class="capitalize">{{ upload.kind }}</span>
                                    <span>·</span>
                                    <span>{{ formatDateTime(upload.createdAt) }}</span>
                                </div>
                            </div>

                            <span
                                class="inline-flex w-fit rounded-full px-2.5 py-1 text-[0.68rem] font-semibold capitalize"
                                :class="moderationToneClass(upload.moderationStatus)"
                            >
                                {{ upload.moderationStatus }}
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="modalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Event ready</DialogTitle>
                <DialogDescription>
                    This page is now the compact event workspace. Use media and settings from here whenever you need them.
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>
</template>
