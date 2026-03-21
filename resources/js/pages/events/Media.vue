<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Columns2,
    Columns3,
    Check,
    Clock3,
    Copy,
    Download,
    ExternalLink,
    Eye,
    Grid2x2,
    Image as ImageIcon,
    Info,
    LoaderCircle,
    Mail,
    MessageSquareText,
    Phone,
    Trash2,
    UserRound,
    Video,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Drawer,
    DrawerContent,
    DrawerDescription,
    DrawerHeader,
    DrawerTitle,
} from '@/components/ui/drawer';
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import {
    Item,
    ItemContent,
    ItemDescription,
    ItemMedia,
    ItemTitle,
} from '@/components/ui/item';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    name: string;
};

type EventLinks = {
    accountDashboard: string;
    media: string;
};

type MediaAsset = {
    id: number;
    kind: 'photo' | 'video' | 'text';
    thumbnailUrl: string | null;
    previewUrl: string | null;
    videoProcessing: boolean;
    originalFilename: string | null;
    mimeType: string | null;
    sizeBytes: number;
    moderationStatus: 'approved' | 'rejected' | 'processing';
    moderationScore: number | null;
    moderationPipeline: 'automatic' | 'manual' | 'disabled' | null;
    moderationMatches: Array<{
        category: string;
        keyword: string;
    }>;
    guestKey: string;
    guestName: string;
    guestEmail: string | null;
    guestPhone: string | null;
    message: string | null;
    text: string | null;
    textThemeImageUrl: string | null;
    textThemeBackgroundColor: string | null;
    textThemeTextColor: string | null;
    createdAt: string | null;
    reviewedAt: string | null;
    commentCount: number;
    deleteUrl: string;
    moderationUpdateUrl: string;
};

type MediaAttendee = {
    key: string;
    guestName: string;
    guestEmail: string | null;
    guestPhone: string | null;
    photoCount: number;
    videoCount: number;
    textCount: number;
    uploadCount: number;
    latestCreatedAt: string | null;
    assetIds: number[];
};

type KindFilter = 'all' | 'photo' | 'video' | 'text';
type ModerationFilter = 'all' | 'approved' | 'rejected' | 'processing';
type MediaViewMode = 'relaxed' | 'balanced' | 'dense';
type PreviewScope = 'main' | 'attendee';

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    mediaAssets: MediaAsset[];
    mediaAttendees: MediaAttendee[];
    mediaBulkDeleteUrl: string;
    mediaBulkModerationUrl: string;
    initialActiveAssetId: number | null;
    canManageMedia: boolean;
}>();

const avatarToneClasses = [
    'bg-amber-100 text-amber-700',
    'bg-sky-100 text-sky-700',
    'bg-emerald-100 text-emerald-700',
    'bg-rose-100 text-rose-700',
    'bg-violet-100 text-violet-700',
    'bg-orange-100 text-orange-700',
];

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.media,
    },
    {
        title: 'Media',
        href: props.eventLinks.media,
    },
];

const assetItems = ref<MediaAsset[]>([...props.mediaAssets]);
const selectedAttendeeKey = ref<string | null>(null);
const activeAssetId = ref<number | null>(props.initialActiveAssetId);
const assetInfoId = ref<number | null>(null);
const deleteAssetId = ref<number | null>(null);
const bulkDeleteOpen = ref(false);
const deletingAssetId = ref<number | null>(null);
const moderationAssetId = ref<number | null>(null);
const selectedAssetIds = ref<number[]>([]);
const kindFilter = ref<KindFilter>('all');
const moderationFilter = ref<ModerationFilter>('all');
const searchQuery = ref('');
const visibleCount = ref(24);
const mediaView = ref<MediaViewMode>('balanced');
const attendeePage = ref(1);
const previewScope = ref<PreviewScope>('main');
const copiedAssetId = ref<number | null>(null);
const previewTouchStartX = ref<number | null>(null);
const previewTouchStartY = ref<number | null>(null);
const previewTouchCurrentX = ref<number | null>(null);
const previewTouchCurrentY = ref<number | null>(null);
const loadedMediaKeys = ref<Record<string, boolean>>({});
const isRefreshingLiveMedia = ref(false);
const hasLiveUpdatingAssets = computed(() =>
    assetItems.value.some(
        (asset) => asset.videoProcessing || asset.moderationStatus === 'processing',
    ),
);
let liveMediaPollId: number | null = null;

watch(
    () => props.mediaAssets,
    (nextAssets) => {
        assetItems.value = [...nextAssets];
        attendeePage.value = 1;
    },
);

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'Unknown';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'medium',
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

const moderationPipelineLabel = (pipeline: MediaAsset['moderationPipeline']): string => {
    switch (pipeline) {
        case 'automatic':
            return 'Automatic filter';
        case 'manual':
            return 'Manual review';
        case 'disabled':
            return 'Moderation off';
        default:
            return 'Unknown';
    }
};

const moderationMatchLabel = (match: MediaAsset['moderationMatches'][number]): string =>
    `${match.category}: ${match.keyword}`;

const kindIcon = (kind: MediaAsset['kind']) => {
    switch (kind) {
        case 'video':
            return Video;
        case 'text':
            return MessageSquareText;
        default:
            return ImageIcon;
    }
};

const moderationIcon = (status: MediaAsset['moderationStatus']) => {
    switch (status) {
        case 'approved':
            return Check;
        case 'rejected':
            return X;
        default:
            return Clock3;
    }
};

const moderationBadgeClass = (status: MediaAsset['moderationStatus']): string => {
    switch (status) {
        case 'approved':
            return 'bg-emerald-500/90 text-white';
        case 'rejected':
            return 'bg-rose-500/90 text-white';
        default:
            return 'bg-amber-500/90 text-white';
    }
};

const guestInitials = (value: string | null): string => {
    const normalized = (value ?? '').trim();
    if (normalized.length === 0) {
        return 'G';
    }

    return normalized
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};

const avatarFallbackClass = (value: string | null): string => {
    const source = (value ?? '').trim() || 'guest';
    const hash = Array.from(source).reduce(
        (sum, character) => sum + character.charCodeAt(0),
        0,
    );

    return avatarToneClasses[hash % avatarToneClasses.length];
};

const generatedDisplayFilename = (asset: MediaAsset): string => {
    const guest = (asset.guestName ?? 'Guest').trim() || 'Guest';
    const label =
        asset.kind === 'photo'
            ? 'photo'
            : asset.kind === 'video'
              ? 'video'
              : 'text post';

    return `${guest} ${label}`;
};

const textPostSurfaceStyle = (asset: MediaAsset): Record<string, string> => {
    const style: Record<string, string> = {
        backgroundColor: asset.textThemeBackgroundColor || '#0f172a',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
    };

    if (asset.textThemeImageUrl) {
        style.backgroundImage = `url(${asset.textThemeImageUrl})`;
    }

    return style;
};

const textPostTextStyle = (asset: MediaAsset): Record<string, string> => ({
    color: asset.textThemeTextColor || '#ffffff',
});

const displayAssetFilename = (asset: MediaAsset): string => {
    const raw = (asset.originalFilename ?? '').trim();
    if (raw === '') {
        return generatedDisplayFilename(asset);
    }

    const extensionIndex = raw.lastIndexOf('.');
    const stem = extensionIndex > 0 ? raw.slice(0, extensionIndex) : raw;
    const compactStem = stem.replace(/[-_]/g, '');

    if (
        stem.length > 40 &&
        compactStem.length > 32 &&
        /^[a-f0-9]+$/i.test(compactStem)
    ) {
        return generatedDisplayFilename(asset);
    }

    return raw;
};

const groupedAttendees = computed<MediaAttendee[]>(() => {
    const groups = new Map<string, MediaAttendee>();

    for (const asset of assetItems.value) {
        const guestName = asset.guestName.trim() || 'Guest';
        const key = asset.guestKey.trim() || guestName.toLowerCase();
        const existing = groups.get(key);

        if (!existing) {
            groups.set(key, {
                key,
                guestName,
                guestEmail: asset.guestEmail,
                guestPhone: asset.guestPhone,
                photoCount: asset.kind === 'photo' ? 1 : 0,
                videoCount: asset.kind === 'video' ? 1 : 0,
                textCount: asset.kind === 'text' ? 1 : 0,
                uploadCount: 1,
                latestCreatedAt: asset.createdAt,
                assetIds: [asset.id],
            });
            continue;
        }

        existing.photoCount += asset.kind === 'photo' ? 1 : 0;
        existing.videoCount += asset.kind === 'video' ? 1 : 0;
        existing.textCount += asset.kind === 'text' ? 1 : 0;
        existing.uploadCount += 1;
        existing.assetIds.push(asset.id);

        if (
            asset.createdAt &&
            (!existing.latestCreatedAt ||
                new Date(asset.createdAt).getTime() >
                    new Date(existing.latestCreatedAt).getTime())
        ) {
            existing.latestCreatedAt = asset.createdAt;
        }

        if (!existing.guestEmail && asset.guestEmail) {
            existing.guestEmail = asset.guestEmail;
        }
        if (!existing.guestPhone && asset.guestPhone) {
            existing.guestPhone = asset.guestPhone;
        }
    }

    return Array.from(groups.values()).sort((a, b) => {
        const aTime = a.latestCreatedAt ? new Date(a.latestCreatedAt).getTime() : 0;
        const bTime = b.latestCreatedAt ? new Date(b.latestCreatedAt).getTime() : 0;

        return bTime - aTime;
    });
});

const attendeesPerPage = 25;
const attendeePageCount = computed(() =>
    Math.max(1, Math.ceil(groupedAttendees.value.length / attendeesPerPage)),
);
const paginatedAttendees = computed<MediaAttendee[]>(() => {
    const start = (attendeePage.value - 1) * attendeesPerPage;

    return groupedAttendees.value.slice(start, start + attendeesPerPage);
});
const attendeeHasPreviousPage = computed(() => attendeePage.value > 1);
const attendeeHasNextPage = computed(
    () => attendeePage.value < attendeePageCount.value,
);

const filteredAssets = computed<MediaAsset[]>(() =>
    assetItems.value.filter((asset) => {
        const kindMatches =
            kindFilter.value === 'all' || asset.kind === kindFilter.value;
        const moderationMatches =
            moderationFilter.value === 'all' ||
            asset.moderationStatus === moderationFilter.value;
        const searchValue = searchQuery.value.trim().toLowerCase();
        const searchMatches =
            searchValue === '' ||
            asset.guestName.toLowerCase().includes(searchValue) ||
            (asset.originalFilename ?? '').toLowerCase().includes(searchValue);

        return kindMatches && moderationMatches && searchMatches;
    }),
);
const visibleAssets = computed<MediaAsset[]>(() =>
    filteredAssets.value.slice(0, visibleCount.value),
);
const hasAnyUploads = computed(() => assetItems.value.length > 0);
const hasMediaFiltersApplied = computed(
    () =>
        kindFilter.value !== 'all' ||
        moderationFilter.value !== 'all' ||
        searchQuery.value.trim().length > 0,
);

const selectedAttendee = computed<MediaAttendee | null>(() => {
    if (!selectedAttendeeKey.value) {
        return null;
    }

    return (
        groupedAttendees.value.find(
            (attendee) => attendee.key === selectedAttendeeKey.value,
        ) ?? null
    );
});

const selectedAttendeeAssets = computed<MediaAsset[]>(() => {
    if (!selectedAttendee.value) {
        return [];
    }

    const assetIds = new Set(selectedAttendee.value.assetIds);

    return assetItems.value.filter((asset) => assetIds.has(asset.id));
});

const activeAsset = computed<MediaAsset | null>(() => {
    if (activeAssetId.value === null) {
        return null;
    }

    return assetItems.value.find((asset) => asset.id === activeAssetId.value) ?? null;
});

const assetInfoAsset = computed<MediaAsset | null>(() => {
    if (assetInfoId.value === null) {
        return null;
    }

    return assetItems.value.find((asset) => asset.id === assetInfoId.value) ?? null;
});

const previewAssets = computed<MediaAsset[]>(() => {
    if (previewScope.value === 'attendee' && selectedAttendee.value) {
        return selectedAttendeeAssets.value;
    }

    return filteredAssets.value;
});

const activeAssetIndex = computed(() => {
    if (!activeAsset.value) {
        return -1;
    }

    return previewAssets.value.findIndex((asset) => asset.id === activeAsset.value?.id);
});

const hasPreviousAsset = computed(() => activeAssetIndex.value > 0);
const hasNextAsset = computed(
    () =>
        activeAssetIndex.value >= 0 &&
        activeAssetIndex.value < previewAssets.value.length - 1,
);

const stats = computed(() => ({
    photos: assetItems.value.filter((asset) => asset.kind === 'photo').length,
    videos: assetItems.value.filter((asset) => asset.kind === 'video').length,
    texts: assetItems.value.filter((asset) => asset.kind === 'text').length,
    attendees: groupedAttendees.value.length,
}));

const assetPendingDelete = computed<MediaAsset | null>(() => {
    if (deleteAssetId.value === null) {
        return null;
    }

    return assetItems.value.find((asset) => asset.id === deleteAssetId.value) ?? null;
});

const selectedCount = computed(() => selectedAssetIds.value.length);
const allVisibleSelected = computed(
    () =>
        visibleAssets.value.length > 0 &&
        visibleAssets.value.every((asset) =>
            selectedAssetIds.value.includes(asset.id),
        ),
);
const canLoadMore = computed(
    () => visibleAssets.value.length < filteredAssets.value.length,
);

const mediaGridClass = computed(() => {
    switch (mediaView.value) {
        case 'relaxed':
            return 'grid grid-cols-2 gap-1.5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3';
        case 'dense':
            return 'grid grid-cols-2 gap-1.5 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6';
        default:
            return 'grid grid-cols-2 gap-1.5 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4';
    }
});

watch([kindFilter, moderationFilter, searchQuery], () => {
    visibleCount.value = 24;
});

watch(
    groupedAttendees,
    () => {
        attendeePage.value = Math.min(attendeePage.value, attendeePageCount.value);
    },
    { immediate: true },
);

const openAttendee = (key: string): void => {
    selectedAttendeeKey.value = key;
};

const resolvePreviewScope = (asset: MediaAsset): PreviewScope => {
    if (
        selectedAttendee.value &&
        selectedAttendee.value.assetIds.includes(asset.id)
    ) {
        return 'attendee';
    }

    return 'main';
};

const openAssetDetails = (asset: MediaAsset, scope?: PreviewScope): void => {
    previewScope.value = scope ?? resolvePreviewScope(asset);
    activeAssetId.value = asset.id;
};

const openAssetInfo = (asset: MediaAsset): void => {
    assetInfoId.value = asset.id;
};

const goToAssetAtIndex = (index: number): void => {
    const nextAsset = previewAssets.value[index];
    if (!nextAsset) {
        return;
    }

    activeAssetId.value = nextAsset.id;
};

const goToPreviousAsset = (): void => {
    if (!hasPreviousAsset.value) {
        return;
    }

    goToAssetAtIndex(activeAssetIndex.value - 1);
};

const goToNextAsset = (): void => {
    if (!hasNextAsset.value) {
        return;
    }

    goToAssetAtIndex(activeAssetIndex.value + 1);
};

const onPreviewTouchStart = (event: TouchEvent): void => {
    const touch = event.touches[0];
    previewTouchStartX.value = touch.clientX;
    previewTouchStartY.value = touch.clientY;
    previewTouchCurrentX.value = touch.clientX;
    previewTouchCurrentY.value = touch.clientY;
};

const onPreviewTouchMove = (event: TouchEvent): void => {
    const touch = event.touches[0];
    previewTouchCurrentX.value = touch.clientX;
    previewTouchCurrentY.value = touch.clientY;
};

const onPreviewTouchEnd = (): void => {
    if (
        previewTouchStartX.value === null ||
        previewTouchStartY.value === null ||
        previewTouchCurrentX.value === null ||
        previewTouchCurrentY.value === null
    ) {
        return;
    }

    const deltaX = previewTouchCurrentX.value - previewTouchStartX.value;
    const deltaY = previewTouchCurrentY.value - previewTouchStartY.value;

    previewTouchStartX.value = null;
    previewTouchStartY.value = null;
    previewTouchCurrentX.value = null;
    previewTouchCurrentY.value = null;

    if (Math.abs(deltaX) < 48 || Math.abs(deltaX) <= Math.abs(deltaY)) {
        return;
    }

    if (deltaX < 0) {
        goToNextAsset();
        return;
    }

    goToPreviousAsset();
};

const copyText = async (value: string, message: string): Promise<void> => {
    if (
        typeof navigator === 'undefined' ||
        !navigator.clipboard ||
        typeof navigator.clipboard.writeText !== 'function'
    ) {
        toast.error('Copy is not available on this device.');
        return;
    }

    await navigator.clipboard.writeText(value);
    toast.success(message);
};

const assetThumbnailSource = (asset: MediaAsset): string | null =>
    asset.thumbnailUrl ?? asset.previewUrl ?? null;

const mediaLoadKey = (assetId: number, surface: 'grid' | 'attendee' | 'preview'): string =>
    `${surface}:${assetId}`;

const markMediaLoaded = (key: string): void => {
    loadedMediaKeys.value = {
        ...loadedMediaKeys.value,
        [key]: true,
    };
};

const isMediaLoaded = (key: string): boolean => loadedMediaKeys.value[key] === true;

const copyAssetLink = async (asset: MediaAsset): Promise<void> => {
    if (!asset.previewUrl) {
        toast.error('This upload does not have a file link to copy.');
        return;
    }

    await copyText(asset.previewUrl, 'Upload link copied.');
    copiedAssetId.value = asset.id;
    window.setTimeout(() => {
        if (copiedAssetId.value === asset.id) {
            copiedAssetId.value = null;
        }
    }, 1600);
};

const removeAssetLocally = (assetId: number): void => {
    assetItems.value = assetItems.value.filter((item) => item.id !== assetId);
    selectedAssetIds.value = selectedAssetIds.value.filter((id) => id !== assetId);

    if (activeAssetId.value === assetId) {
        activeAssetId.value = null;
    }
    if (assetInfoId.value === assetId) {
        assetInfoId.value = null;
    }

    if (
        selectedAttendee.value &&
        !assetItems.value.some((item) =>
            selectedAttendee.value?.assetIds.includes(item.id),
        )
    ) {
        selectedAttendeeKey.value = null;
    }
};

const deleteAsset = (): void => {
    const asset = assetPendingDelete.value;
    if (!asset || deletingAssetId.value !== null) {
        return;
    }

    deletingAssetId.value = asset.id;

    router.delete(asset.deleteUrl, {
        preserveScroll: true,
        onSuccess: () => {
            removeAssetLocally(asset.id);
            deleteAssetId.value = null;
        },
        onFinish: () => {
            deletingAssetId.value = null;
        },
    });
};

const bulkDeleteAssets = (): void => {
    if (selectedAssetIds.value.length === 0 || deletingAssetId.value !== null) {
        return;
    }

    deletingAssetId.value = -1;

    router.post(
        props.mediaBulkDeleteUrl,
        {
            asset_ids: selectedAssetIds.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                const ids = new Set(selectedAssetIds.value);
                assetItems.value = assetItems.value.filter(
                    (asset) => !ids.has(asset.id),
                );
                selectedAssetIds.value = [];
                bulkDeleteOpen.value = false;
                if (
                    selectedAttendee.value &&
                    !assetItems.value.some((item) =>
                        selectedAttendee.value?.assetIds.includes(item.id),
                    )
                ) {
                    selectedAttendeeKey.value = null;
                }
            },
            onFinish: () => {
                deletingAssetId.value = null;
            },
        },
    );
};

const bulkUpdateModeration = (
    moderationStatus: MediaAsset['moderationStatus'],
): void => {
    if (selectedAssetIds.value.length === 0 || moderationAssetId.value !== null) {
        return;
    }

    moderationAssetId.value = -1;

    router.post(
        props.mediaBulkModerationUrl,
        {
            asset_ids: selectedAssetIds.value,
            moderation_status: moderationStatus,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                const selectedIds = new Set(selectedAssetIds.value);
                const reviewedAt =
                    moderationStatus === 'processing'
                        ? null
                        : new Date().toISOString();
                assetItems.value = assetItems.value.map((asset) =>
                    selectedIds.has(asset.id)
                        ? { ...asset, moderationStatus, reviewedAt }
                        : asset,
                );
            },
            onFinish: () => {
                moderationAssetId.value = null;
            },
        },
    );
};

const updateModeration = (
    asset: MediaAsset,
    moderationStatus: MediaAsset['moderationStatus'],
): void => {
    if (moderationAssetId.value !== null) {
        return;
    }

    moderationAssetId.value = asset.id;

    router.patch(
        asset.moderationUpdateUrl,
        {
            moderation_status: moderationStatus,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                const reviewedAt =
                    moderationStatus === 'processing'
                        ? null
                        : new Date().toISOString();
                assetItems.value = assetItems.value.map((item) =>
                    item.id === asset.id
                        ? { ...item, moderationStatus, reviewedAt }
                        : item,
                );
            },
            onFinish: () => {
                moderationAssetId.value = null;
            },
        },
    );
};

const toggleAssetSelection = (assetId: number): void => {
    if (selectedAssetIds.value.includes(assetId)) {
        selectedAssetIds.value = selectedAssetIds.value.filter((id) => id !== assetId);
        return;
    }

    selectedAssetIds.value = [...selectedAssetIds.value, assetId];
};

const toggleSelectAllVisible = (): void => {
    if (allVisibleSelected.value) {
        const visibleIds = new Set(visibleAssets.value.map((asset) => asset.id));
        selectedAssetIds.value = selectedAssetIds.value.filter(
            (id) => !visibleIds.has(id),
        );
        return;
    }

    const nextIds = new Set(selectedAssetIds.value);
    visibleAssets.value.forEach((asset) => nextIds.add(asset.id));
    selectedAssetIds.value = Array.from(nextIds);
};

const resetMediaFilters = (): void => {
    kindFilter.value = 'all';
    moderationFilter.value = 'all';
    searchQuery.value = '';
};

const hasVisibleDocument = (): boolean =>
    typeof document === 'undefined' || document.visibilityState === 'visible';

const reloadLiveMedia = (): void => {
    if (
        typeof window === 'undefined' ||
        isRefreshingLiveMedia.value ||
        !hasVisibleDocument()
    ) {
        return;
    }

    isRefreshingLiveMedia.value = true;

    router.reload({
        only: ['mediaAssets', 'mediaAttendees'],
        onFinish: () => {
            isRefreshingLiveMedia.value = false;
        },
    });
};

const syncLiveMediaPoll = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    if (liveMediaPollId !== null) {
        window.clearInterval(liveMediaPollId);
        liveMediaPollId = null;
    }

    if (!hasLiveUpdatingAssets.value) {
        return;
    }

    liveMediaPollId = window.setInterval(() => {
        reloadLiveMedia();
    }, 5000);
};

const handleDocumentVisibilityChange = (): void => {
    if (!hasVisibleDocument()) {
        return;
    }

    if (hasLiveUpdatingAssets.value) {
        reloadLiveMedia();
    }

    syncLiveMediaPoll();
};

watch(hasLiveUpdatingAssets, () => {
    syncLiveMediaPoll();
}, { immediate: true });

onMounted(() => {
    if (typeof document === 'undefined') {
        return;
    }

    document.addEventListener('visibilitychange', handleDocumentVisibilityChange);
});

onUnmounted(() => {
    if (typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', handleDocumentVisibilityChange);
    }

    if (typeof window !== 'undefined' && liveMediaPollId !== null) {
        window.clearInterval(liveMediaPollId);
        liveMediaPollId = null;
    }
});

const statCards = computed(() => [
    {
        key: 'photos',
        title: 'Photos',
        value: stats.value.photos,
        icon: ImageIcon,
    },
    {
        key: 'videos',
        title: 'Videos',
        value: stats.value.videos,
        icon: Video,
    },
    {
        key: 'texts',
        title: 'Text Posts',
        value: stats.value.texts,
        icon: MessageSquareText,
    },
    {
        key: 'attendees',
        title: 'Attendees',
        value: stats.value.attendees,
        icon: UserRound,
    },
]);
</script>

<template>
    <Head title="Media" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full space-y-6 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_85%_10%,_rgba(251,191,36,0.16),_transparent_22%)] p-4">
            <section class="grid gap-4 md:grid-cols-4">
                <Item
                    v-for="card in statCards"
                    :key="card.key"
                    variant="outline"
                    class="rounded-2xl border-black/6 bg-white"
                >
                    <ItemMedia variant="icon">
                        <component :is="card.icon" class="size-4" />
                    </ItemMedia>
                    <ItemContent>
                        <ItemTitle class="text-xs font-medium uppercase tracking-[0.12em] text-muted-foreground">
                            {{ card.title }}
                        </ItemTitle>
                        <ItemDescription class="line-clamp-none text-3xl font-semibold text-slate-900">
                            {{ card.value }}
                        </ItemDescription>
                    </ItemContent>
                </Item>
            </section>

            <section class="space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-4 rounded-[2rem] border border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2d251f_46%,#5f533f_100%)] px-6 py-5 text-white shadow-sm">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-white/62">
                            Event workspace
                        </div>
                        <h1 class="mt-2 text-2xl font-semibold">Media</h1>
                        <p class="mt-1 text-sm text-white/68">
                            Filter, moderate, and delete uploads from one place.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button as-child variant="outline" class="border-white/14 bg-white/8 text-white hover:bg-white/14 hover:text-white">
                            <a :href="eventLinks.accountDashboard">
                                <ExternalLink class="mr-2 size-4" />
                                Dashboard
                            </a>
                        </Button>
                        <div class="inline-flex items-center gap-1 rounded-full border border-white/14 bg-white/8 p-1 shadow-sm">
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-white/72 transition hover:bg-white/12 hover:text-white"
                            :class="mediaView === 'relaxed' ? 'bg-amber-200 text-[#171411] shadow-sm hover:bg-amber-200 hover:text-[#171411]' : ''"
                            title="Relaxed gallery view"
                            @click="mediaView = 'relaxed'"
                        >
                            <Columns2 class="size-4" />
                            <span class="sr-only">Relaxed gallery view</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-white/72 transition hover:bg-white/12 hover:text-white"
                            :class="mediaView === 'balanced' ? 'bg-amber-200 text-[#171411] shadow-sm hover:bg-amber-200 hover:text-[#171411]' : ''"
                            title="Balanced gallery view"
                            @click="mediaView = 'balanced'"
                        >
                            <Columns3 class="size-4" />
                            <span class="sr-only">Balanced gallery view</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-white/72 transition hover:bg-white/12 hover:text-white"
                            :class="mediaView === 'dense' ? 'bg-amber-200 text-[#171411] shadow-sm hover:bg-amber-200 hover:text-[#171411]' : ''"
                            title="Dense gallery view"
                            @click="mediaView = 'dense'"
                        >
                            <Grid2x2 class="size-4" />
                            <span class="sr-only">Dense gallery view</span>
                        </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search attendee or filename..."
                        class="h-9 min-w-64 rounded-md border bg-white px-3 text-sm"
                    />
                    <Button
                        size="sm"
                        :variant="kindFilter === 'all' ? 'default' : 'outline'"
                        @click="kindFilter = 'all'"
                    >
                        All
                    </Button>
                    <Button
                        size="sm"
                        :variant="kindFilter === 'photo' ? 'default' : 'outline'"
                        @click="kindFilter = 'photo'"
                    >
                        Photos
                    </Button>
                    <Button
                        size="sm"
                        :variant="kindFilter === 'video' ? 'default' : 'outline'"
                        @click="kindFilter = 'video'"
                    >
                        Videos
                    </Button>
                    <Button
                        size="sm"
                        :variant="kindFilter === 'text' ? 'default' : 'outline'"
                        @click="kindFilter = 'text'"
                    >
                        Text
                    </Button>
                    <Button
                        size="sm"
                        :variant="moderationFilter === 'all' ? 'secondary' : 'outline'"
                        @click="moderationFilter = 'all'"
                    >
                        Any status
                    </Button>
                    <Button
                        size="sm"
                        :variant="moderationFilter === 'approved' ? 'secondary' : 'outline'"
                        @click="moderationFilter = 'approved'"
                    >
                        Approved
                    </Button>
                    <Button
                        size="sm"
                        :variant="moderationFilter === 'processing' ? 'secondary' : 'outline'"
                        @click="moderationFilter = 'processing'"
                    >
                        Processing
                    </Button>
                    <Button
                        size="sm"
                        :variant="moderationFilter === 'rejected' ? 'secondary' : 'outline'"
                        @click="moderationFilter = 'rejected'"
                    >
                        Rejected
                    </Button>
                </div>

                <div
                    v-if="canManageMedia && selectedCount > 0"
                    class="flex flex-wrap items-center gap-2 rounded-2xl border bg-white p-3"
                >
                    <p class="text-sm text-slate-700">
                        {{ selectedCount }} selected
                    </p>
                    <Button size="sm" variant="outline" @click="toggleSelectAllVisible">
                        {{
                            allVisibleSelected
                                ? 'Unselect visible'
                                : `Select visible (${visibleAssets.length})`
                        }}
                    </Button>
                    <Button size="sm" variant="outline" @click="selectedAssetIds = []">
                        Clear selection
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        :disabled="moderationAssetId !== null"
                        data-test="approve-selected-button"
                        @click="bulkUpdateModeration('approved')"
                    >
                        <Check class="mr-2 size-4" />
                        Approve selected
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        :disabled="moderationAssetId !== null"
                        @click="bulkUpdateModeration('rejected')"
                    >
                        <X class="mr-2 size-4" />
                        Reject selected
                    </Button>
                    <Button size="sm" variant="destructive" @click="bulkDeleteOpen = true">
                        <Trash2 class="mr-2 size-4" />
                        Delete selected
                    </Button>
                </div>

                <Empty
                    v-if="!hasAnyUploads"
                    class="rounded-2xl border bg-white py-14"
                >
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <ImageIcon class="size-5" />
                        </EmptyMedia>
                        <EmptyTitle>No photos or videos yet</EmptyTitle>
                        <EmptyDescription>
                            Guest uploads will appear here as soon as someone shares a photo, video, or text post.
                        </EmptyDescription>
                    </EmptyHeader>
                </Empty>

                <Empty
                    v-else-if="filteredAssets.length === 0"
                    class="rounded-2xl border bg-white py-14"
                >
                    <EmptyHeader>
                        <EmptyMedia variant="icon">
                            <X class="size-5" />
                        </EmptyMedia>
                        <EmptyTitle>No uploads match these filters</EmptyTitle>
                        <EmptyDescription>
                            Try a different attendee name, media type, or moderation status.
                        </EmptyDescription>
                    </EmptyHeader>
                    <EmptyContent v-if="hasMediaFiltersApplied">
                        <Button variant="outline" @click="resetMediaFilters">
                            Clear filters
                        </Button>
                    </EmptyContent>
                </Empty>

                <div
                    v-else
                    :class="mediaGridClass"
                >
                    <article
                        v-for="asset in visibleAssets"
                        :key="asset.id"
                        class="group relative overflow-hidden rounded-[1.35rem] bg-slate-100"
                    >
                        <div class="relative aspect-square w-full overflow-hidden">
                            <div
                                v-if="
                                    ((asset.kind === 'photo' || asset.kind === 'video') && assetThumbnailSource(asset))
                                    && !isMediaLoaded(mediaLoadKey(asset.id, 'grid'))
                                "
                                class="absolute inset-0 animate-pulse bg-white/50"
                            />
                            <button
                                type="button"
                                class="absolute inset-0 block h-full w-full overflow-hidden text-left"
                                @click="openAssetDetails(asset, 'main')"
                            >
                            <img
                                v-if="
                                    asset.kind === 'photo' &&
                                    assetThumbnailSource(asset)
                                "
                                :src="assetThumbnailSource(asset) ?? undefined"
                                alt="Uploaded event asset"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                :class="isMediaLoaded(mediaLoadKey(asset.id, 'grid')) ? 'opacity-100' : 'opacity-0'"
                                @load="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                                @error="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                            />
                            <img
                                v-else-if="asset.kind === 'video' && asset.thumbnailUrl"
                                :src="asset.thumbnailUrl"
                                alt="Uploaded event video"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                :class="isMediaLoaded(mediaLoadKey(asset.id, 'grid')) ? 'opacity-100' : 'opacity-0'"
                                @load="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                                @error="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                            />
                            <video
                                v-else-if="asset.kind === 'video' && asset.previewUrl"
                                :src="asset.previewUrl"
                                class="h-full w-full object-cover"
                                preload="metadata"
                                playsinline
                            />
                            <div
                                v-else-if="asset.kind === 'video' && asset.videoProcessing"
                                class="flex h-full w-full flex-col items-center justify-center gap-2 bg-slate-100 text-center text-slate-500"
                            >
                                <LoaderCircle class="size-7 animate-spin text-slate-400" />
                                <p class="text-xs font-semibold text-slate-700">
                                    Processing video
                                </p>
                            </div>
                            <div
                                v-else-if="asset.kind === 'text'"
                                class="flex h-full w-full items-center justify-center p-5"
                                :style="textPostSurfaceStyle(asset)"
                            >
                                <p
                                    class="line-clamp-6 whitespace-pre-wrap text-sm font-medium"
                                    :style="textPostTextStyle(asset)"
                                >
                                    {{ asset.text ?? 'Text post' }}
                                </p>
                            </div>
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-sm text-muted-foreground"
                            >
                                Preview unavailable
                            </div>

                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/80 via-black/35 to-transparent" />

                            <div class="absolute left-3 top-3 flex items-center gap-2">
                                <label
                                    v-if="canManageMedia"
                                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-full border border-white/20 bg-black/45 text-white shadow-sm backdrop-blur"
                                    :data-test="`asset-select-toggle-${asset.id}`"
                                    @click.stop
                                >
                                    <input
                                        type="checkbox"
                                        class="sr-only"
                                        :data-test="`asset-select-${asset.id}`"
                                        :checked="selectedAssetIds.includes(asset.id)"
                                        @click.stop
                                        @change.stop="toggleAssetSelection(asset.id)"
                                    />
                                    <Check
                                        v-if="selectedAssetIds.includes(asset.id)"
                                        class="size-4"
                                    />
                                </label>
                                <span
                                    class="inline-flex size-8 items-center justify-center rounded-full border border-white/20 bg-black/45 text-white shadow-sm backdrop-blur"
                                    :title="asset.kind"
                                >
                                    <component :is="kindIcon(asset.kind)" class="size-4" />
                                </span>
                                <span
                                    class="inline-flex size-8 items-center justify-center rounded-full shadow-sm backdrop-blur"
                                    :class="moderationBadgeClass(asset.moderationStatus)"
                                    :title="asset.moderationStatus"
                                >
                                    <component
                                        :is="moderationIcon(asset.moderationStatus)"
                                        class="size-4"
                                    />
                                </span>
                            </div>
                            </button>

                            <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-3">
                                <div class="min-w-0 flex items-center gap-2">
                                    <Avatar class="size-9 border border-white/20 shadow-sm">
                                        <AvatarFallback
                                            :class="avatarFallbackClass(asset.guestName)"
                                        >
                                            {{ guestInitials(asset.guestName) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ asset.guestName }}
                                        </p>
                                    </div>
                                </div>
                                <span class="shrink-0 text-[11px] text-white/75">
                                    {{ formatDateTime(asset.createdAt) }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>

                <div
                    v-if="canLoadMore"
                    class="flex justify-center"
                >
                    <Button variant="outline" @click="visibleCount += 24">
                        Load more
                    </Button>
                </div>
            </section>

            <section class="space-y-4">
                <div>
                    <h2 class="text-xl font-semibold">Attendees</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        See who uploaded, how much they shared, and drill into their media.
                    </p>
                </div>

                <div class="overflow-hidden rounded-2xl border bg-white">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Attendee</TableHead>
                                <TableHead>Photos</TableHead>
                                <TableHead>Videos</TableHead>
                                <TableHead>Text</TableHead>
                                <TableHead>Last Upload</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty
                                v-if="groupedAttendees.length === 0"
                                :colspan="6"
                            >
                                <Empty class="border-0 p-0">
                                    <EmptyHeader>
                                        <EmptyMedia variant="icon">
                                            <UserRound class="size-5" />
                                        </EmptyMedia>
                                        <EmptyTitle>No attendees yet</EmptyTitle>
                                        <EmptyDescription>
                                            Attendees will appear here automatically once someone uploads to the album.
                                        </EmptyDescription>
                                    </EmptyHeader>
                                </Empty>
                            </TableEmpty>
                            <TableRow
                                v-for="attendee in paginatedAttendees"
                                :key="attendee.key"
                            >
                                <TableCell>
                                    <div>
                                        <p class="font-medium text-slate-900">
                                            {{ attendee.guestName }}
                                        </p>
                                        <p
                                            v-if="attendee.guestEmail"
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ attendee.guestEmail }}
                                        </p>
                                    </div>
                                </TableCell>
                                <TableCell>{{ attendee.photoCount }}</TableCell>
                                <TableCell>{{ attendee.videoCount }}</TableCell>
                                <TableCell>{{ attendee.textCount }}</TableCell>
                                <TableCell>{{ formatDateTime(attendee.latestCreatedAt) }}</TableCell>
                                <TableCell class="text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openAttendee(attendee.key)"
                                    >
                                        View uploads
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    v-if="groupedAttendees.length > attendeesPerPage"
                    class="flex items-center justify-between gap-3 rounded-[1.25rem] border border-slate-200 bg-white px-4 py-3"
                >
                    <p class="text-sm text-slate-500">
                        Page {{ attendeePage }} of {{ attendeePageCount }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="!attendeeHasPreviousPage"
                            @click="attendeePage = Math.max(1, attendeePage - 1)"
                        >
                            Previous
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="!attendeeHasNextPage"
                            @click="attendeePage = Math.min(attendeePageCount, attendeePage + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </section>
        </div>

        <Dialog
            :open="selectedAttendee !== null"
            @update:open="(open) => { if (!open) selectedAttendeeKey = null; }"
        >
            <DialogContent class="max-w-5xl rounded-[2rem] p-0">
                <div class="space-y-5 p-6">
                    <DialogHeader class="text-left">
                        <DialogTitle>{{ selectedAttendee?.guestName ?? 'Attendee uploads' }}</DialogTitle>
                        <DialogDescription class="space-y-1">
                            <span class="block">
                                {{ selectedAttendee?.uploadCount ?? 0 }} uploads
                            </span>
                            <span
                                v-if="selectedAttendee?.latestCreatedAt"
                                class="block"
                            >
                                Last upload: {{ formatDateTime(selectedAttendee.latestCreatedAt) }}
                            </span>
                        </DialogDescription>
                    </DialogHeader>

                    <div
                        v-if="selectedAttendee"
                        class="flex flex-wrap gap-3 text-sm text-muted-foreground"
                    >
                        <div class="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1.5">
                            <UserRound class="size-4" />
                            <span>{{ selectedAttendee.guestName }}</span>
                        </div>
                        <div
                            v-if="selectedAttendee.guestEmail"
                            class="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1.5"
                        >
                            <Mail class="size-4" />
                            <span>{{ selectedAttendee.guestEmail }}</span>
                        </div>
                        <div
                            v-if="selectedAttendee.guestPhone"
                            class="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1.5"
                        >
                            <Phone class="size-4" />
                            <span>{{ selectedAttendee.guestPhone }}</span>
                        </div>
                    </div>

                    <Empty
                        v-if="selectedAttendeeAssets.length === 0"
                        class="rounded-2xl border bg-muted/20 py-14"
                    >
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <ImageIcon class="size-5" />
                            </EmptyMedia>
                            <EmptyTitle>No uploads for this attendee</EmptyTitle>
                            <EmptyDescription>
                                This attendee does not have any photos, videos, or text posts yet.
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>

                    <div
                        v-else
                        :class="`${mediaGridClass} max-h-[65vh] overflow-y-auto`"
                    >
                        <article
                            v-for="asset in selectedAttendeeAssets"
                            :key="`attendee-asset-${asset.id}`"
                            class="group relative overflow-hidden rounded-[1.35rem] bg-slate-100"
                        >
                            <div class="relative aspect-square w-full overflow-hidden">
                                <div
                                    v-if="
                                        ((asset.kind === 'photo' || asset.kind === 'video') && assetThumbnailSource(asset))
                                        && !isMediaLoaded(mediaLoadKey(asset.id, 'attendee'))
                                    "
                                    class="absolute inset-0 animate-pulse bg-white/50"
                                />
                                <button
                                    type="button"
                                    class="absolute inset-0 block h-full w-full overflow-hidden text-left"
                                    @click="openAssetDetails(asset, 'attendee')"
                                >
                                <img
                                    v-if="
                                        asset.kind === 'photo' &&
                                        assetThumbnailSource(asset)
                                    "
                                    :src="assetThumbnailSource(asset) ?? undefined"
                                    alt="Attendee upload"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                    :class="isMediaLoaded(mediaLoadKey(asset.id, 'attendee')) ? 'opacity-100' : 'opacity-0'"
                                    @load="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                    @error="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                />
                                <img
                                    v-else-if="asset.kind === 'video' && asset.thumbnailUrl"
                                    :src="asset.thumbnailUrl"
                                    alt="Attendee video upload"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                    :class="isMediaLoaded(mediaLoadKey(asset.id, 'attendee')) ? 'opacity-100' : 'opacity-0'"
                                    @load="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                    @error="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                />
                                <video
                                    v-else-if="asset.kind === 'video' && asset.previewUrl"
                                    :src="asset.previewUrl"
                                    class="h-full w-full object-cover"
                                    preload="metadata"
                                    playsinline
                                />
                                <div
                                    v-else-if="asset.kind === 'video' && asset.videoProcessing"
                                    class="flex h-full w-full flex-col items-center justify-center gap-2 bg-slate-100 text-center text-slate-500"
                                >
                                    <LoaderCircle class="size-7 animate-spin text-slate-400" />
                                    <p class="text-xs font-semibold text-slate-700">
                                        Processing video
                                    </p>
                                </div>
                                <div
                                    v-else-if="asset.kind === 'text'"
                                    class="flex h-full w-full items-center justify-center p-5"
                                    :style="textPostSurfaceStyle(asset)"
                                >
                                    <p
                                        class="line-clamp-6 whitespace-pre-wrap text-sm font-medium"
                                        :style="textPostTextStyle(asset)"
                                    >
                                        {{ asset.text ?? 'Text post' }}
                                    </p>
                                </div>

                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/80 via-black/35 to-transparent" />

                                <div class="absolute left-3 top-3 flex items-center gap-2">
                                    <span
                                        class="inline-flex size-8 items-center justify-center rounded-full border border-white/20 bg-black/45 text-white shadow-sm backdrop-blur"
                                        :title="asset.kind"
                                    >
                                        <component :is="kindIcon(asset.kind)" class="size-4" />
                                    </span>
                                    <span
                                        class="inline-flex size-8 items-center justify-center rounded-full shadow-sm backdrop-blur"
                                        :class="moderationBadgeClass(asset.moderationStatus)"
                                        :title="asset.moderationStatus"
                                    >
                                        <component
                                            :is="moderationIcon(asset.moderationStatus)"
                                            class="size-4"
                                        />
                                    </span>
                                </div>
                                </button>

                                <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-3">
                                    <div class="min-w-0 flex items-center gap-2">
                                        <Avatar class="size-9 border border-white/20 shadow-sm">
                                            <AvatarFallback
                                                :class="avatarFallbackClass(asset.guestName)"
                                            >
                                                {{ guestInitials(asset.guestName) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-white">
                                                {{ asset.guestName }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-[11px] text-white/75">
                                        {{ formatDateTime(asset.createdAt) }}
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="activeAsset !== null"
            @update:open="(open) => { if (!open) activeAssetId = null; }"
        >
            <DialogContent
                :show-close-button="false"
                class="max-w-[min(96rem,calc(100vw-1rem))] overflow-hidden rounded-[2rem] border border-[#e7dccb] bg-[#fbf7f1] p-0 shadow-[0_30px_80px_rgba(91,64,34,0.18)]"
            >
                <div
                    v-if="activeAsset"
                    class="overflow-hidden"
                >
                    <div class="flex items-center justify-between gap-4 border-b border-[#e7dccb] bg-[#fffaf3] px-5 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <Avatar class="size-10 border border-[#e7dccb]">
                                <AvatarFallback
                                    :class="avatarFallbackClass(activeAsset.guestName)"
                                >
                                    {{ guestInitials(activeAsset.guestName) }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ activeAsset.guestName }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ formatDateTime(activeAsset.createdAt) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                size="icon"
                                variant="ghost"
                                class="rounded-full text-slate-600 hover:bg-[#efe7db] hover:text-slate-900"
                                title="Open upload info"
                                @click="openAssetInfo(activeAsset)"
                            >
                                <Info class="size-4" />
                            </Button>
                            <button
                                type="button"
                                class="inline-flex size-10 items-center justify-center rounded-full text-slate-500 transition hover:bg-[#efe7db] hover:text-slate-900"
                                aria-label="Close preview"
                                @click="activeAssetId = null"
                            >
                                <X class="size-5" />
                            </button>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#faf5ed] via-[#f3ebdf] to-[#e8dcc9] p-4 sm:p-6">
                        <div
                            class="relative flex h-[min(78vh,calc(100vh-12rem))] touch-pan-y items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/70 bg-white/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]"
                            @touchstart="onPreviewTouchStart"
                            @touchmove="onPreviewTouchMove"
                            @touchend="onPreviewTouchEnd"
                        >
                            <div class="pointer-events-none absolute left-4 top-4 z-10 inline-flex rounded-full bg-white/88 px-3 py-1 text-xs font-medium text-slate-700 shadow-sm backdrop-blur">
                                {{ formatBytes(activeAsset.sizeBytes) }}
                            </div>
                            <div class="pointer-events-none absolute right-4 top-4 z-10 inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur">
                                <component :is="kindIcon(activeAsset.kind)" class="size-4" />
                            </div>
                            <div
                                v-if="activeAsset.kind === 'photo' && activeAsset.previewUrl && !isMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                                class="absolute inset-0 m-3 animate-pulse rounded-[1.5rem] bg-white/55 sm:m-5"
                            />
                            <img
                                v-if="activeAsset.kind === 'photo' && activeAsset.previewUrl"
                                :src="activeAsset.previewUrl"
                                alt="Selected event upload"
                                class="block h-full w-full object-contain p-3 sm:p-5"
                                :class="isMediaLoaded(mediaLoadKey(activeAsset.id, 'preview')) ? 'opacity-100' : 'opacity-0'"
                                @load="markMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                                @error="markMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                            />
                            <video
                                v-else-if="activeAsset.kind === 'video' && activeAsset.previewUrl"
                                :src="activeAsset.previewUrl"
                                :poster="activeAsset.thumbnailUrl ?? undefined"
                                class="block h-full w-full object-contain p-3 sm:p-5"
                                controls
                                autoplay
                                playsinline
                            />
                            <div
                                v-else-if="
                                    activeAsset.kind === 'video' &&
                                    activeAsset.videoProcessing
                                "
                                class="flex h-full w-full flex-col items-center justify-center gap-4 p-6 text-center text-slate-600"
                            >
                                <LoaderCircle class="size-10 animate-spin text-slate-400" />
                                <div class="space-y-1">
                                    <p class="text-base font-semibold text-slate-900">
                                        Processing video
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        Preview is still being generated.
                                    </p>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center p-6 sm:p-10"
                            >
                                <div
                                    class="flex aspect-square w-full max-w-[min(72vh,42rem)] items-center justify-center rounded-[1.75rem] border border-white/50 px-8 py-10 shadow-lg"
                                    :style="textPostSurfaceStyle(activeAsset)"
                                >
                                    <p
                                        class="max-w-[78%] whitespace-pre-wrap text-center text-xl font-semibold leading-relaxed sm:text-2xl"
                                        :style="textPostTextStyle(activeAsset)"
                                    >
                                        {{ activeAsset.text ?? 'Text post' }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="absolute left-4 top-1/2 z-10 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white disabled:pointer-events-none disabled:opacity-35"
                                :disabled="!hasPreviousAsset"
                                aria-label="Previous upload"
                                @click="goToPreviousAsset"
                            >
                                <ChevronLeft class="size-5" />
                            </button>
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 z-10 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white disabled:pointer-events-none disabled:opacity-35"
                                :disabled="!hasNextAsset"
                                aria-label="Next upload"
                                @click="goToNextAsset"
                            >
                                <ChevronRight class="size-5" />
                            </button>
                            <div class="absolute inset-x-0 bottom-4 z-10 flex flex-wrap items-center justify-center gap-2 px-4">
                                <button
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white"
                                    title="Upload info"
                                    @click="openAssetInfo(activeAsset)"
                                >
                                    <Info class="size-4" />
                                </button>
                                <button
                                    v-if="activeAsset.previewUrl"
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white"
                                    title="Copy file link"
                                    @click="copyAssetLink(activeAsset)"
                                >
                                    <Copy class="size-4" />
                                </button>
                                <a
                                    v-if="activeAsset.previewUrl"
                                    :href="activeAsset.previewUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white"
                                    title="Download or open file"
                                >
                                    <Download class="size-4" />
                                </a>
                                <button
                                    v-if="canManageMedia"
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white disabled:pointer-events-none disabled:opacity-35"
                                    :disabled="
                                        moderationAssetId === activeAsset.id ||
                                        activeAsset.moderationStatus === 'approved'
                                    "
                                    title="Approve"
                                    @click="updateModeration(activeAsset, 'approved')"
                                >
                                    <Check class="size-4" />
                                </button>
                                <button
                                    v-if="canManageMedia"
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-white/88 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white disabled:pointer-events-none disabled:opacity-35"
                                    :disabled="
                                        moderationAssetId === activeAsset.id ||
                                        activeAsset.moderationStatus === 'rejected'
                                    "
                                    title="Reject"
                                    @click="updateModeration(activeAsset, 'rejected')"
                                >
                                    <X class="size-4" />
                                </button>
                                <button
                                    v-if="canManageMedia"
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-rose-500/88 text-white shadow-sm backdrop-blur transition hover:bg-rose-500"
                                    title="Delete"
                                    @click="deleteAssetId = activeAsset.id"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="activeAsset.kind !== 'text' && activeAsset.message"
                            class="mt-4 rounded-[1.5rem] border border-white/70 bg-white/70 px-4 py-3 shadow-sm"
                        >
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-800">
                                {{ activeAsset.message }}
                            </p>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Drawer
            direction="right"
            :open="assetInfoAsset !== null"
            @update:open="(open) => { if (!open) assetInfoId = null; }"
        >
            <DrawerContent class="w-full max-w-xl overflow-hidden border-l border-[#e7dccb] bg-[#f8f3eb]">
                <div
                    v-if="assetInfoAsset"
                    class="flex h-full max-h-screen flex-col"
                >
                    <DrawerHeader class="relative border-b border-[#e7dccb] px-6 py-5 pr-16 text-left">
                        <DrawerTitle class="text-xl font-semibold text-slate-900">
                            Upload info
                        </DrawerTitle>
                        <DrawerDescription class="text-sm text-slate-600">
                            Attendee details, file metadata, and quick actions for this upload.
                        </DrawerDescription>
                        <button
                            type="button"
                            class="absolute right-5 top-5 inline-flex size-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-white/70 hover:text-slate-900"
                            aria-label="Close upload info"
                            @click="assetInfoId = null"
                        >
                            <X class="size-5" />
                        </button>
                    </DrawerHeader>

                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-6">
                        <div class="flex items-center gap-3 rounded-[1.5rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                            <Avatar class="size-12 border border-[#e7dccb]">
                                <AvatarFallback
                                    :class="avatarFallbackClass(assetInfoAsset.guestName)"
                                >
                                    {{ guestInitials(assetInfoAsset.guestName) }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="truncate text-base font-semibold text-slate-900">
                                    {{ assetInfoAsset.guestName }}
                                </p>
                                <p class="truncate text-sm text-slate-500">
                                    {{ formatDateTime(assetInfoAsset.createdAt) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    File
                                </p>
                                <p class="mt-2 break-words text-sm font-medium text-slate-900">
                                    {{ displayAssetFilename(assetInfoAsset) }}
                                </p>
                            </div>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Size
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ formatBytes(assetInfoAsset.sizeBytes) }}
                                </p>
                            </div>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Type
                                </p>
                                <p class="mt-2 text-sm font-medium capitalize text-slate-900">
                                    {{ assetInfoAsset.kind }}
                                </p>
                            </div>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Status
                                </p>
                                <p class="mt-2 text-sm font-medium capitalize text-slate-900">
                                    {{ assetInfoAsset.moderationStatus }}
                                </p>
                            </div>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Pipeline
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ moderationPipelineLabel(assetInfoAsset.moderationPipeline) }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.moderationScore !== null"
                                class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm"
                            >
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Score
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ assetInfoAsset.moderationScore }}/100
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.reviewedAt"
                                class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm"
                            >
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Reviewed
                                </p>
                                <p class="mt-2 text-sm font-medium text-slate-900">
                                    {{ formatDateTime(assetInfoAsset.reviewedAt) }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.mimeType"
                                class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm sm:col-span-2"
                            >
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Format
                                </p>
                                <p class="mt-2 break-all text-sm font-medium text-slate-900">
                                    {{ assetInfoAsset.mimeType }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.moderationMatches.length > 0"
                                class="rounded-[1.35rem] border border-white/70 bg-white/70 p-4 shadow-sm sm:col-span-2"
                            >
                                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                    Matched rules
                                </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="match in assetInfoAsset.moderationMatches"
                                        :key="`${match.category}-${match.keyword}`"
                                        class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-800"
                                    >
                                        {{ moderationMatchLabel(match) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="assetInfoAsset.guestEmail || assetInfoAsset.guestPhone"
                            class="space-y-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Contact
                            </p>
                            <div class="space-y-3">
                                <div
                                    v-if="assetInfoAsset.guestEmail"
                                    class="flex items-center gap-3 rounded-[1.25rem] border border-white/70 bg-white/70 p-4 shadow-sm"
                                >
                                    <Mail class="size-4 text-slate-500" />
                                    <span class="break-all text-sm font-medium text-slate-900">
                                        {{ assetInfoAsset.guestEmail }}
                                    </span>
                                </div>
                                <div
                                    v-if="assetInfoAsset.guestPhone"
                                    class="flex items-center gap-3 rounded-[1.25rem] border border-white/70 bg-white/70 p-4 shadow-sm"
                                >
                                    <Phone class="size-4 text-slate-500" />
                                    <span class="text-sm font-medium text-slate-900">
                                        {{ assetInfoAsset.guestPhone }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="assetInfoAsset.kind !== 'text' && assetInfoAsset.message"
                            class="space-y-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Guest message
                            </p>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-5 shadow-sm">
                                <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-800">
                                    {{ assetInfoAsset.message }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="assetInfoAsset.kind === 'text' && assetInfoAsset.text"
                            class="space-y-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Text post
                            </p>
                            <div class="rounded-[1.35rem] border border-white/70 bg-white/70 p-5 shadow-sm">
                                <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-800">
                                    {{ assetInfoAsset.text }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="assetInfoAsset.previewUrl"
                                size="icon"
                                variant="outline"
                                title="Copy file link"
                                @click="copyAssetLink(assetInfoAsset)"
                            >
                                <Copy class="size-4" />
                            </Button>
                            <Button
                                v-if="assetInfoAsset.previewUrl"
                                as-child
                                size="icon"
                                variant="outline"
                            >
                                <a
                                    :href="assetInfoAsset.previewUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="Open file"
                                >
                                    <ExternalLink class="size-4" />
                                </a>
                            </Button>
                            <Button
                                size="icon"
                                variant="outline"
                                title="Open preview"
                                @click="assetInfoId = null; openAssetDetails(assetInfoAsset)"
                            >
                                <Eye class="size-4" />
                            </Button>
                            <template v-if="canManageMedia">
                                <Button
                                    size="icon"
                                    variant="outline"
                                    :disabled="
                                        moderationAssetId === assetInfoAsset.id ||
                                        assetInfoAsset.moderationStatus === 'approved'
                                    "
                                    title="Approve"
                                    @click="updateModeration(assetInfoAsset, 'approved')"
                                >
                                    <Check class="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    :disabled="
                                        moderationAssetId === assetInfoAsset.id ||
                                        assetInfoAsset.moderationStatus === 'rejected'
                                    "
                                    title="Reject"
                                    @click="updateModeration(assetInfoAsset, 'rejected')"
                                >
                                    <X class="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="destructive"
                                    title="Delete"
                                    @click="deleteAssetId = assetInfoAsset.id"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </template>
                        </div>
                    </div>
                </div>
            </DrawerContent>
        </Drawer>

        <AlertDialog
            v-if="canManageMedia"
            :open="assetPendingDelete !== null"
            @update:open="(open) => { if (!open) deleteAssetId = null; }"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete media?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This removes the selected upload from the event and updates storage counts immediately.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-white hover:bg-destructive/90"
                        :disabled="deletingAssetId !== null"
                        @click="deleteAsset"
                    >
                        {{ deletingAssetId !== null ? 'Deleting...' : 'Delete' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <AlertDialog
            v-if="canManageMedia"
            :open="bulkDeleteOpen"
            @update:open="(open) => { bulkDeleteOpen = open; }"
        >
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete selected media?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This will delete {{ selectedCount }} selected uploads from the event.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-white hover:bg-destructive/90"
                        :disabled="deletingAssetId !== null || selectedCount === 0"
                        @click="bulkDeleteAssets"
                    >
                        {{
                            deletingAssetId === -1
                                ? 'Deleting...'
                                : `Delete ${selectedCount} item${selectedCount === 1 ? '' : 's'}`
                        }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
