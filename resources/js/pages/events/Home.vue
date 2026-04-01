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
    Users,
    Video,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
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
    allowedMediaTypes: string[];
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
    branding: {
        primaryColor: string | null;
        accentColor: string | null;
        logoUrl: string | null;
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
    accountDashboard: string | null;
    dashboard: string;
    printPack: string;
    media: string;
    mediaExportStart: string;
    mediaExportDownload: string;
    settings: string;
    album: string;
    albumShortUrl?: string | null;
    albumAccessCode: string;
    albumEntry: string;
    albumEntryShortcut: string;
    wall: string;
    wallShortUrl?: string | null;
    invitation: string;
    albumQrDataUrl: string;
    wallQrDataUrl: string;
    invitationQrDataUrl: string;
};

const props = defineProps<{
    currentEvent: EventPayload;
    dashboardStats: DashboardStats;
    dashboardRecentUploads: RecentUpload[];
    eventLinks: EventLinks;
    showDashboardModal: boolean;
}>();

const { locale, t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.dashboard,
    },
];

const intlLocale = computed(() => {
    if (locale.value === 'ro') {
        return 'ro-RO';
    }

    if (locale.value === 'el') {
        return 'el-GR';
    }

    return 'en-GB';
});

const modalOpen = ref(props.showDashboardModal);
const exportSubmitting = ref(false);
const latestKnownUploadId = ref(props.dashboardRecentUploads[0]?.id ?? 0);
const isRefreshingWorkspace = ref(false);
const qrPreview = ref<'album' | 'wall' | null>(null);
let workspacePollId: number | null = null;

const formatDateTime = (value: string | null, fallback = t('event_home.fallback.unknown')): string => {
    if (!value) {
        return fallback;
    }

    return new Intl.DateTimeFormat(intlLocale.value, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatDateOnly = (value: string | null): string => {
    if (!value) {
        return t('event_home.fallback.not_set');
    }

    return new Intl.DateTimeFormat(intlLocale.value, {
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
        return t('event_home.fallback.not_scheduled_yet');
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
    props.currentEvent.billing.canManage ? t('event_home.actions.open_billing') : t('event_home.actions.view_billing'),
);

const mediaExportLabel = computed(() => {
    if (!canDownloadAll.value) {
        return t('event_home.export.upgrade');
    }

    if (mediaExportBusy.value) {
        return t('event_home.export.exporting');
    }

    if (mediaExportReady.value) {
        return t('event_home.export.download');
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return t('event_home.export.retry');
    }

    return t('event_home.export.build');
});

const mediaExportHint = computed(() => {
    if (!canDownloadAll.value) {
        return t('event_home.export.hint_locked');
    }

    if (mediaExportBusy.value) {
        return t('event_home.export.hint_processing');
    }

    if (mediaExportReady.value) {
        return props.currentEvent.mediaExport.completedAt
            ? t('event_home.export.hint_ready_since', { date: formatDateTime(props.currentEvent.mediaExport.completedAt) })
            : t('event_home.export.hint_ready');
    }

    if (props.currentEvent.mediaExport.status === 'failed') {
        return props.currentEvent.mediaExport.error || t('event_home.export.hint_failed');
    }

    return t('event_home.export.hint_idle');
});

const guestUploadTypeLabels: Record<string, string> = {
    photo: t('event_home.media_types.photo'),
    video: t('event_home.media_types.video'),
    text: t('event_home.media_types.text'),
};

const summaryItems = computed(() => [
    {
        label: t('event_home.summary.guests'),
        value: String(props.dashboardStats.guestCount),
        detail: props.dashboardStats.lastUploadAt
            ? t('event_home.summary.last_upload', { date: formatDateTime(props.dashboardStats.lastUploadAt) })
            : t('event_home.summary.no_uploads'),
        icon: Users,
    },
    {
        label: t('event_home.summary.uploads'),
        value: uploadUsageLabel.value,
        detail: t('event_home.summary.remaining', { count: props.dashboardStats.uploadRemaining }),
        icon: ImageIcon,
    },
    {
        label: t('event_home.summary.pending_review'),
        value: String(props.currentEvent.moderationSummary.processingCount),
        detail: t('event_home.summary.approved', { count: props.dashboardStats.approvedCount }),
        icon: Clock3,
    },
    {
        label: t('event_home.summary.storage'),
        value: storageUsageLabel.value,
        detail: t('event_home.summary.free', { amount: formatBytes(props.dashboardStats.storageRemainingBytes) }),
        icon: CheckCircle2,
    },
]);

const recentUploadSummary = (upload: RecentUpload): string => {
    if (upload.kind === 'text') {
        return upload.text?.trim() || t('event_home.recent.text_post');
    }

    return upload.message?.trim() || t(`event_home.recent.kind_upload.${upload.kind}`);
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

const moderationLabel = (status: RecentUpload['moderationStatus']): string =>
    t(`event_home.status.${status}`);

const qrPreviewData = computed(() => {
    if (qrPreview.value === 'album') {
        return {
            title: t('event_home.qr.album_title'),
            image: props.eventLinks.albumQrDataUrl,
            alt: t('event_home.qr.album_alt'),
        };
    }

    if (qrPreview.value === 'wall') {
        return {
            title: t('event_home.qr.wall_title'),
            image: props.eventLinks.wallQrDataUrl,
            alt: t('event_home.qr.wall_alt'),
        };
    }

    return null;
});

const copyText = async (value: string, successMessage: string): Promise<void> => {
    if (
        typeof navigator === 'undefined'
        || !navigator.clipboard
        || typeof navigator.clipboard.writeText !== 'function'
    ) {
        toast.error(t('event_home.clipboard.unavailable'));
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

const hasVisibleDocument = (): boolean =>
    typeof document === 'undefined' || document.visibilityState === 'visible';

const refreshWorkspace = (): void => {
    if (
        typeof window === 'undefined' ||
        isRefreshingWorkspace.value ||
        !hasVisibleDocument()
    ) {
        return;
    }

    isRefreshingWorkspace.value = true;

    router.reload({
        only: ['currentEvent', 'dashboardStats', 'dashboardRecentUploads'],
        onFinish: () => {
            isRefreshingWorkspace.value = false;
        },
    });
};

const syncWorkspacePoll = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    if (workspacePollId !== null) {
        window.clearInterval(workspacePollId);
        workspacePollId = null;
    }

    workspacePollId = window.setInterval(() => {
        refreshWorkspace();
    }, mediaExportBusy.value ? 5000 : 8000);
};

const handleDocumentVisibilityChange = (): void => {
    if (!hasVisibleDocument()) {
        return;
    }

    refreshWorkspace();
    syncWorkspacePoll();
};

watch(
    () => props.dashboardRecentUploads,
    (nextUploads) => {
        const nextLatestUploadId = nextUploads[0]?.id ?? 0;
        if (nextLatestUploadId > latestKnownUploadId.value) {
            const newUploadCount = nextUploads.filter(
                (upload) => upload.id > latestKnownUploadId.value,
            ).length;
            toast.success(
                newUploadCount === 1
                    ? t('event_home.activity.new_upload_single')
                    : t('event_home.activity.new_upload_plural', { count: newUploadCount }),
            );
        }

        latestKnownUploadId.value = Math.max(latestKnownUploadId.value, nextLatestUploadId);
    },
);

watch(
    () => props.currentEvent.mediaExport.status,
    (nextStatus, previousStatus) => {
        if (nextStatus === previousStatus) {
            return;
        }

        if (
            previousStatus &&
            ['pending', 'processing'].includes(previousStatus) &&
            nextStatus === 'ready'
        ) {
            toast.success(t('event_home.export.ready'));
        }

        if (
            previousStatus &&
            ['pending', 'processing'].includes(previousStatus) &&
            nextStatus === 'failed'
        ) {
            toast.error(t('event_home.export.failed'));
        }
    },
);

watch(mediaExportBusy, () => {
    syncWorkspacePoll();
});

onMounted(() => {
    syncWorkspacePoll();

    if (typeof document === 'undefined') {
        return;
    }

    document.addEventListener('visibilitychange', handleDocumentVisibilityChange);
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', handleDocumentVisibilityChange);
    }

    if (typeof window !== 'undefined' && workspacePollId !== null) {
        window.clearInterval(workspacePollId);
        workspacePollId = null;
    }
});
</script>

<template>
    <Head :title="t('event_home.page_title', { event: currentEvent.name })" />

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
                                {{ t('event_home.hero.kicker') }}
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                {{ currentEvent.name }}
                            </h1>
                            <p class="mt-2 text-sm text-zinc-600">
                                {{ currentEvent.plan }} · {{ formatDateOnly(currentEvent.eventDate) }} · {{ uploadWindowLabel }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500">
                                    {{ t('event_home.hero.uploads_label') }}
                                </span>
                                <span
                                    v-for="mediaType in currentEvent.allowedMediaTypes"
                                    :key="mediaType"
                                    class="inline-flex rounded-full border border-black/8 bg-[#fcfbf8] px-2.5 py-1 text-xs font-medium text-zinc-700"
                                >
                                    {{ guestUploadTypeLabels[mediaType] ?? mediaType }}
                                </span>
                                <Link
                                    :href="`${eventLinks.settings}#guest-upload-types`"
                                    class="text-xs font-medium text-zinc-600 underline-offset-4 hover:text-[#171411] hover:underline"
                                >
                                    {{ t('event_home.actions.edit') }}
                                </Link>
                            </div>
                            <p class="mt-2 text-sm text-zinc-500">
                                {{ mediaExportHint }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:max-w-sm lg:justify-end">
                            <Button v-if="eventLinks.accountDashboard" as-child size="sm" variant="outline">
                                <Link :href="eventLinks.accountDashboard">
                                    {{ t('app.nav.events') }}
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

                    <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            v-for="item in summaryItems"
                            :key="item.label"
                            class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4 xl:first:border-l-0 xl:first:pl-0"
                        >
                            <dt class="flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                <component :is="item.icon" class="size-3.5 text-zinc-400" />
                                {{ item.label }}
                            </dt>
                            <dd class="mt-2 text-sm font-semibold text-[#171411] sm:text-base">
                                {{ item.value }}
                            </dd>
                            <p class="mt-1 text-xs leading-5 text-zinc-500">
                                {{ item.detail }}
                            </p>
                        </div>
                    </dl>
                </section>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.9fr)]">
                    <section class="flex min-h-0 flex-col rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="flex items-end justify-between gap-4 border-b border-black/5 pb-4">
                            <div>
                                <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                    {{ t('event_home.section.recent_title') }}
                                </h2>
                                <p class="mt-1 text-sm text-zinc-600">
                                    {{ t('event_home.section.recent_description') }}
                                </p>
                            </div>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="eventLinks.media">
                                    {{ t('event_home.actions.open_media') }}
                                </Link>
                            </Button>
                        </div>

                        <div v-if="dashboardRecentUploads.length === 0" class="py-8 text-sm text-zinc-600">
                            {{ t('event_home.section.recent_empty') }}
                        </div>

                        <div v-else class="min-h-0 max-h-[30rem] flex-1 overflow-y-auto pt-2">
                            <div class="divide-y divide-black/5">
                            <div
                                v-for="upload in dashboardRecentUploads"
                                :key="upload.id"
                                class="flex flex-col gap-2 rounded-[1rem] px-1 py-3 sm:flex-row sm:items-center sm:justify-between"
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
                                        <span>{{ guestUploadTypeLabels[upload.kind] ?? upload.kind }}</span>
                                        <span>·</span>
                                        <span>{{ formatDateTime(upload.createdAt) }}</span>
                                    </div>
                                </div>

                                <span
                                    class="inline-flex w-fit rounded-full px-2.5 py-1 text-[0.68rem] font-semibold capitalize"
                                    :class="moderationToneClass(upload.moderationStatus)"
                                >
                                    {{ moderationLabel(upload.moderationStatus) }}
                                </span>
                            </div>
                            </div>
                        </div>
                    </section>

                    <aside class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="border-b border-black/5 pb-4">
                            <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                {{ t('event_home.section.share_title') }}
                            </h2>
                            <p class="mt-1 text-sm text-zinc-600">
                                {{ t('event_home.section.share_description') }}
                            </p>
                        </div>

                        <div class="space-y-5 pt-5">
                            <div class="space-y-3">
                                <div class="flex items-start gap-4">
                                    <button
                                        type="button"
                                        class="shrink-0 rounded-[1rem] border border-slate-200 bg-white p-2 transition hover:border-slate-300"
                                        @click="qrPreview = 'album'"
                                    >
                                        <img
                                            :src="eventLinks.albumQrDataUrl"
                                            :alt="t('event_home.qr.album_alt')"
                                            class="size-20 rounded-[0.8rem]"
                                        />
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm font-semibold text-[#171411]">
                                            {{ t('event_home.album.title') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-zinc-600">
                                            {{ t('event_home.album.description') }}
                                        </p>
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                            {{ t('event_home.album.code_label', { code: eventLinks.albumAccessCode }) }}
                                        </p>
                                        <p class="mt-1 text-xs text-zinc-500">
                                            {{ t('event_home.album.code_hint', { shortcut: eventLinks.albumEntryShortcut }) }}
                                        </p>
                                        <p v-if="eventLinks.albumShortUrl" class="mt-1 text-xs text-zinc-500">
                                            {{ t('event_home.album.short_link', { url: eventLinks.albumShortUrl }) }}
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <Button as-child size="sm" variant="outline">
                                                <a :href="eventLinks.album" target="_blank" rel="noopener noreferrer">
                                                    {{ t('event_home.actions.open') }}
                                                </a>
                                            </Button>
                                            <Button size="sm" variant="outline" @click="copyText(eventLinks.album, t('event_home.clipboard.album_link'))">
                                                <Copy class="mr-2 size-4" />
                                                {{ t('event_home.actions.copy') }}
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <a :href="eventLinks.albumQrDataUrl" download="digital-album-qr.svg">
                                                    <Download class="mr-2 size-4" />
                                                    {{ t('event_home.actions.qr') }}
                                                </a>
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="copyText(eventLinks.albumShortUrl ?? eventLinks.albumAccessCode, eventLinks.albumShortUrl ? t('event_home.clipboard.album_short_link') : t('event_home.clipboard.album_code'))"
                                            >
                                                <Copy class="mr-2 size-4" />
                                                {{ eventLinks.albumShortUrl ? t('event_home.actions.short_link') : t('event_home.actions.code') }}
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-black/5 pt-5">
                                <div class="flex items-start gap-4">
                                    <button
                                        type="button"
                                        class="shrink-0 rounded-[1rem] border border-slate-200 bg-white p-2 transition hover:border-slate-300"
                                        @click="qrPreview = 'wall'"
                                    >
                                        <img
                                            :src="eventLinks.wallQrDataUrl"
                                            :alt="t('event_home.qr.wall_alt')"
                                            class="size-20 rounded-[0.8rem]"
                                        />
                                    </button>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm font-semibold text-[#171411]">
                                            {{ t('event_home.wall.title') }}
                                        </h3>
                                        <p class="mt-1 text-sm text-zinc-600">
                                            {{ t('event_home.wall.description') }}
                                        </p>
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                            {{ t('event_home.wall.code_label', { code: eventLinks.albumAccessCode }) }}
                                        </p>
                                        <p v-if="eventLinks.wallShortUrl" class="mt-1 text-xs text-zinc-500">
                                            {{ t('event_home.wall.short_link', { url: eventLinks.wallShortUrl }) }}
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <Button as-child size="sm" variant="outline">
                                                <a :href="eventLinks.wall" target="_blank" rel="noopener noreferrer">
                                                    {{ t('event_home.actions.open') }}
                                                </a>
                                            </Button>
                                            <Button size="sm" variant="outline" @click="copyText(eventLinks.wall, t('event_home.clipboard.wall_link'))">
                                                <Copy class="mr-2 size-4" />
                                                {{ t('event_home.actions.copy') }}
                                            </Button>
                                            <Button
                                                v-if="eventLinks.wallShortUrl"
                                                size="sm"
                                                variant="outline"
                                                @click="copyText(eventLinks.wallShortUrl, t('event_home.clipboard.wall_short_link'))"
                                            >
                                                <Copy class="mr-2 size-4" />
                                                {{ t('event_home.actions.short_link') }}
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <a :href="eventLinks.wallQrDataUrl" download="photo-wall-qr.svg">
                                                    <Download class="mr-2 size-4" />
                                                    {{ t('event_home.actions.qr') }}
                                                </a>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <section class="mt-5 overflow-hidden rounded-[1.75rem] border border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2b211d_42%,#6a4c3a_100%)] text-white shadow-[0_18px_44px_rgba(23,20,17,0.14)]">
                    <div class="flex flex-col gap-5 px-5 py-6 md:px-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.72rem] font-semibold uppercase tracking-[0.24em] text-white/60">
                                {{ t('event_home.print_pack.page_kicker') }}
                            </p>
                            <h2 class="mt-3 text-2xl font-semibold tracking-tight">
                                {{ t('event_home.print_pack.title') }}
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-white/75 sm:text-base">
                                {{ t('event_home.print_pack.workspace_description') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button as-child class="rounded-full border-0 bg-white text-[#171411] hover:bg-white/90">
                                <Link :href="eventLinks.printPack">
                                    {{ t('event_home.print_pack.open_studio') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:open="modalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ t('event_home.dialog.ready_title') }}</DialogTitle>
                <DialogDescription>
                    {{ t('event_home.dialog.ready_description') }}
                </DialogDescription>
            </DialogHeader>
        </DialogContent>
    </Dialog>

    <Dialog :open="qrPreview !== null" @update:open="(open) => { if (!open) qrPreview = null; }">
        <DialogContent
            class="max-w-fit border-0 bg-transparent p-0 shadow-none"
        >
            <div
                v-if="qrPreviewData"
                class="rounded-[1.75rem] bg-white p-4 text-center shadow-2xl"
            >
                <img
                    :src="qrPreviewData.image"
                    :alt="qrPreviewData.alt"
                    class="w-[min(88vw,30rem)] rounded-[1.5rem] bg-white"
                />
                <template v-if="qrPreview === 'album'">
                    <p class="mt-4 text-[0.72rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        {{ t('event_home.dialog.album_code_label') }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold tracking-[0.32em] text-[#171411]">
                        {{ eventLinks.albumAccessCode }}
                    </p>
                    <p class="mt-2 text-sm text-zinc-500">
                        {{ t('event_home.dialog.album_code_hint', { shortcut: eventLinks.albumEntryShortcut }) }}
                    </p>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>
