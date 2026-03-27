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
    ThumbsDown,
    ThumbsUp,
    Trash2,
    UserRound,
    Video,
    X,
} from 'lucide-vue-next';
import {
    IconFileText,
    IconPhoto,
    IconVideo,
} from '@tabler/icons-vue';
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
    accountDashboard: string | null;
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
    wallVisibility: 'approved' | 'rejected' | 'pending';
    createdAt: string | null;
    reviewedAt: string | null;
    commentCount: number;
    deleteUrl: string;
    moderationUpdateUrl: string;
    wallVisibilityUpdateUrl: string;
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
const bulkDeleteOpen = ref(false);
const deletingAssetId = ref<number | null>(null);
const moderationAssetId = ref<number | null>(null);
const wallVisibilityAssetId = ref<number | null>(null);
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
const latestKnownAssetId = ref(
    Math.max(0, ...assetItems.value.map((asset) => asset.id)),
);
const hasLiveUpdatingAssets = computed(() =>
    assetItems.value.some(
        (asset) => asset.videoProcessing || asset.moderationStatus === 'processing',
    ),
);
let liveMediaPollId: number | null = null;

watch(
    () => props.mediaAssets,
    (nextAssets) => {
        const nextLatestAssetId = Math.max(0, ...nextAssets.map((asset) => asset.id));
        if (nextLatestAssetId > latestKnownAssetId.value) {
            const newAssetCount = nextAssets.filter(
                (asset) => asset.id > latestKnownAssetId.value,
            ).length;
            toast.success(
                newAssetCount === 1
                    ? 'A new upload just arrived.'
                    : `${newAssetCount} new uploads just arrived.`,
            );
        }

        latestKnownAssetId.value = Math.max(latestKnownAssetId.value, nextLatestAssetId);
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

const wallVisibilityLabel = (visibility: MediaAsset['wallVisibility']): string => {
    switch (visibility) {
        case 'approved':
            return 'On wall';
        case 'rejected':
            return 'Hidden from wall';
        default:
            return 'Wall pending';
    }
};

const wallVisibilityToneClass = (visibility: MediaAsset['wallVisibility']): string => {
    switch (visibility) {
        case 'approved':
            return 'bg-emerald-100/92 text-emerald-700';
        case 'rejected':
            return 'bg-rose-100/92 text-rose-700';
        default:
            return 'bg-amber-100/92 text-amber-700';
    }
};

const showsWallDecisionOverlay = (asset: MediaAsset): boolean =>
    props.canManageMedia && asset.wallVisibility === 'pending';

const showsGridAssetChrome = (asset: MediaAsset): boolean =>
    !showsWallDecisionOverlay(asset);

const moderationMatchLabel = (match: MediaAsset['moderationMatches'][number]): string =>
    `${match.category}: ${match.keyword}`;

const kindIcon = (kind: MediaAsset['kind']) => {
    switch (kind) {
        case 'video':
            return IconVideo;
        case 'text':
            return IconFileText;
        default:
            return IconPhoto;
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
            return 'grid grid-cols-2 gap-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-3';
        case 'dense':
            return 'grid grid-cols-2 gap-1 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6';
        default:
            return 'grid grid-cols-2 gap-1 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4';
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

const csrfToken = (): string =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

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

const deleteAsset = (asset: MediaAsset): void => {
    if (deletingAssetId.value !== null) {
        return;
    }

    deletingAssetId.value = asset.id;

    fetch(asset.deleteUrl, {
        method: 'DELETE',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
        .then(async (response) => {
            if (!response.ok) {
                throw new Error('Unable to delete the selected upload.');
            }

            removeAssetLocally(asset.id);
            toast.success('Media deleted.');
        })
        .catch(() => {
            toast.error('Unable to delete the selected upload right now.');
        })
        .finally(() => {
            deletingAssetId.value = null;
        });
};

const requestDeleteAsset = (asset: MediaAsset): void => {
    if (deletingAssetId.value !== null) {
        return;
    }

    if (! window.confirm('Delete this upload? This cannot be undone.')) {
        return;
    }

    deleteAsset(asset);
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

const updateWallVisibility = (
    asset: MediaAsset,
    wallVisibility: MediaAsset['wallVisibility'],
): void => {
    if (wallVisibilityAssetId.value !== null) {
        return;
    }

    wallVisibilityAssetId.value = asset.id;

    router.patch(
        asset.wallVisibilityUpdateUrl,
        {
            wall_visibility: wallVisibility,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                assetItems.value = assetItems.value.map((item) =>
                    item.id === asset.id
                        ? { ...item, wallVisibility }
                        : item,
                );

                toast.success(
                    wallVisibility === 'approved'
                        ? 'Added to the photo wall.'
                        : 'Removed from the photo wall.',
                );
            },
            onFinish: () => {
                wallVisibilityAssetId.value = null;
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

    liveMediaPollId = window.setInterval(() => {
        reloadLiveMedia();
    }, hasLiveUpdatingAssets.value ? 5000 : 8000);
};

const handleDocumentVisibilityChange = (): void => {
    if (!hasVisibleDocument()) {
        return;
    }

    reloadLiveMedia();
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
        <div class="min-h-full space-y-5 bg-[#faf7f2] p-4 md:p-6">
            <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-3xl">
                        <div class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                            Event workspace
                        </div>
                        <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                            Media
                        </h1>
                        <p class="mt-2 text-sm text-zinc-600">
                            Review uploads, filter fast, and take action without digging through a heavy dashboard.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            v-if="eventLinks.accountDashboard"
                            as-child
                            size="sm"
                            variant="outline"
                        >
                            <a :href="eventLinks.accountDashboard">
                                <ExternalLink class="mr-2 size-4" />
                                Events
                            </a>
                        </Button>
                        <div class="inline-flex items-center gap-1 rounded-full border border-black/8 bg-[#fcfbf8] p-1">
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                            :class="mediaView === 'relaxed' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                            title="Relaxed gallery view"
                            @click="mediaView = 'relaxed'"
                        >
                            <Columns2 class="size-4" />
                            <span class="sr-only">Relaxed gallery view</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                            :class="mediaView === 'balanced' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                            title="Balanced gallery view"
                            @click="mediaView = 'balanced'"
                        >
                            <Columns3 class="size-4" />
                            <span class="sr-only">Balanced gallery view</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                            :class="mediaView === 'dense' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                            title="Dense gallery view"
                            @click="mediaView = 'dense'"
                        >
                            <Grid2x2 class="size-4" />
                            <span class="sr-only">Dense gallery view</span>
                        </button>
                        </div>
                    </div>
                </div>

                <dl class="mt-5 grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="card in statCards"
                        :key="card.key"
                        class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4 xl:first:border-l-0 xl:first:pl-0"
                    >
                        <dt class="flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                            <component :is="card.icon" class="size-3.5 text-zinc-400" />
                            {{ card.title }}
                        </dt>
                        <dd class="mt-2 text-lg font-semibold tracking-tight text-[#171411]">
                            {{ card.value }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 flex flex-col gap-3 border-t border-black/5 pt-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search attendee or filename..."
                            class="h-10 min-w-[16rem] flex-1 rounded-xl border border-black/8 bg-[#fcfbf8] px-3 text-sm"
                        />
                        <div class="inline-flex items-center gap-1 rounded-full border border-black/8 bg-[#fcfbf8] p-1">
                            <button
                                type="button"
                                class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                                :class="kindFilter === 'all' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                                title="All uploads"
                                @click="kindFilter = 'all'"
                            >
                                <Grid2x2 class="size-4" />
                                <span class="sr-only">All uploads</span>
                            </button>
                            <button
                                type="button"
                                class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                                :class="kindFilter === 'photo' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                                title="Photos"
                                @click="kindFilter = 'photo'"
                            >
                                <IconPhoto class="size-4" />
                                <span class="sr-only">Photos</span>
                            </button>
                            <button
                                type="button"
                                class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                                :class="kindFilter === 'video' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                                title="Videos"
                                @click="kindFilter = 'video'"
                            >
                                <IconVideo class="size-4" />
                                <span class="sr-only">Videos</span>
                            </button>
                            <button
                                type="button"
                                class="inline-flex size-9 items-center justify-center rounded-full text-zinc-500 transition hover:bg-black/[0.04] hover:text-[#171411]"
                                :class="kindFilter === 'text' ? 'bg-[#171411] text-white shadow-sm hover:bg-[#171411] hover:text-white' : ''"
                                title="Text posts"
                                @click="kindFilter = 'text'"
                            >
                                <IconFileText class="size-4" />
                                <span class="sr-only">Text posts</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
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
                </div>

                <div
                    v-if="canManageMedia && selectedCount > 0"
                    class="mt-1 flex flex-wrap items-center gap-2 border-t border-black/5 pt-4"
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
                    class="mt-4 rounded-2xl border bg-white py-14"
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
                    class="mt-4 rounded-2xl border bg-white py-14"
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
                    class="mt-4"
                    :class="mediaGridClass"
                >
                    <article
                        v-for="asset in visibleAssets"
                        :key="asset.id"
                        class="group relative overflow-hidden rounded-none bg-slate-100"
                    >
                        <div class="relative aspect-[3/4] w-full overflow-hidden">
                            <div
                                v-if="
                                    ((asset.kind === 'photo' || asset.kind === 'video') && assetThumbnailSource(asset))
                                    && !isMediaLoaded(mediaLoadKey(asset.id, 'grid'))
                                "
                                class="absolute inset-0 animate-pulse bg-white/50"
                            />
                            <button
                                type="button"
                                class="absolute inset-0 z-0 block h-full w-full overflow-hidden text-left"
                                @click="openAssetDetails(asset, 'main')"
                            >
                            <img
                                v-if="
                                    asset.kind === 'photo' &&
                                    assetThumbnailSource(asset)
                                "
                                :src="assetThumbnailSource(asset) ?? undefined"
                                alt="Uploaded event asset"
                                class="h-full w-full rounded-none object-cover transition duration-300 group-hover:scale-[1.03]"
                                :class="isMediaLoaded(mediaLoadKey(asset.id, 'grid')) ? 'opacity-100' : 'opacity-0'"
                                @load="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                                @error="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                            />
                            <img
                                v-else-if="asset.kind === 'video' && asset.thumbnailUrl"
                                :src="asset.thumbnailUrl"
                                alt="Uploaded event video"
                                class="h-full w-full rounded-none object-cover transition duration-300 group-hover:scale-[1.03]"
                                :class="isMediaLoaded(mediaLoadKey(asset.id, 'grid')) ? 'opacity-100' : 'opacity-0'"
                                @load="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                                @error="markMediaLoaded(mediaLoadKey(asset.id, 'grid'))"
                            />
                            <video
                                v-else-if="asset.kind === 'video' && asset.previewUrl"
                                :src="asset.previewUrl"
                                class="h-full w-full rounded-none object-cover"
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

                            <div
                                v-if="showsGridAssetChrome(asset)"
                                class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/80 via-black/35 to-transparent"
                            />

                            <div
                                v-if="showsGridAssetChrome(asset)"
                                class="absolute left-3 top-3 flex items-center gap-2"
                            >
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

                            <div
                                v-if="showsWallDecisionOverlay(asset)"
                                class="absolute inset-0 z-20 grid grid-cols-2 overflow-hidden"
                            >
                                <button
                                    type="button"
                                    class="flex h-full items-center justify-center bg-rose-500/24 text-white transition hover:bg-rose-500/34 disabled:pointer-events-none disabled:opacity-55"
                                    :disabled="wallVisibilityAssetId === asset.id"
                                    :aria-label="`Hide ${asset.guestName} upload from photo wall`"
                                    @click.stop="updateWallVisibility(asset, 'rejected')"
                                >
                                    <ThumbsDown class="size-14 drop-shadow-[0_16px_28px_rgba(0,0,0,0.2)] sm:size-16" />
                                </button>

                                <button
                                    type="button"
                                    class="flex h-full items-center justify-center bg-emerald-500/26 text-white transition hover:bg-emerald-500/36 disabled:pointer-events-none disabled:opacity-55"
                                    :disabled="wallVisibilityAssetId === asset.id"
                                    :aria-label="`Show ${asset.guestName} upload on photo wall`"
                                    @click.stop="updateWallVisibility(asset, 'approved')"
                                >
                                    <ThumbsUp class="size-14 drop-shadow-[0_16px_28px_rgba(0,0,0,0.2)] sm:size-16" />
                                </button>
                            </div>

                            <div
                                v-if="showsGridAssetChrome(asset)"
                                class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-3"
                            >
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
                                        <p
                                            class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                            :class="wallVisibilityToneClass(asset.wallVisibility)"
                                        >
                                            {{ wallVisibilityLabel(asset.wallVisibility) }}
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
                            class="group relative overflow-hidden rounded-none bg-slate-100"
                        >
                            <div class="relative aspect-[3/4] w-full overflow-hidden">
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
                                    class="h-full w-full rounded-none object-cover transition duration-300 group-hover:scale-[1.03]"
                                    :class="isMediaLoaded(mediaLoadKey(asset.id, 'attendee')) ? 'opacity-100' : 'opacity-0'"
                                    @load="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                    @error="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                />
                                <img
                                    v-else-if="asset.kind === 'video' && asset.thumbnailUrl"
                                    :src="asset.thumbnailUrl"
                                    alt="Attendee video upload"
                                    class="h-full w-full rounded-none object-cover transition duration-300 group-hover:scale-[1.03]"
                                    :class="isMediaLoaded(mediaLoadKey(asset.id, 'attendee')) ? 'opacity-100' : 'opacity-0'"
                                    @load="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                    @error="markMediaLoaded(mediaLoadKey(asset.id, 'attendee'))"
                                />
                                <video
                                    v-else-if="asset.kind === 'video' && asset.previewUrl"
                                    :src="asset.previewUrl"
                                    class="h-full w-full rounded-none object-cover"
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
                class="max-w-[min(108rem,calc(100vw-0.5rem))] overflow-hidden border-0 bg-transparent p-0 shadow-none"
            >
                <div
                    v-if="activeAsset"
                    class="overflow-hidden rounded-[1.75rem] bg-[#1f1711]"
                >
                    <div
                        class="relative flex h-[min(92vh,calc(100vh-0.75rem))] touch-pan-y items-center justify-center overflow-hidden"
                        @touchstart="onPreviewTouchStart"
                        @touchmove="onPreviewTouchMove"
                        @touchend="onPreviewTouchEnd"
                    >
                        <div class="pointer-events-none absolute inset-x-0 top-0 z-10 h-32 bg-gradient-to-b from-[#2f2219]/78 via-[#2f2219]/30 to-transparent sm:h-36" />
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-10 h-40 bg-gradient-to-t from-[#241912]/82 via-[#241912]/34 to-transparent sm:h-48" />

                        <div class="absolute inset-x-0 top-0 z-20 flex items-start justify-between gap-4 px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <Avatar class="size-10 border border-white/18 shadow-sm">
                                    <AvatarFallback
                                        :class="avatarFallbackClass(activeAsset.guestName)"
                                    >
                                        {{ guestInitials(activeAsset.guestName) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 text-white">
                                    <p class="truncate text-sm font-medium tracking-[0.01em]">
                                        {{ activeAsset.guestName }}
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-white/72 sm:text-xs">
                                        <span class="truncate">{{ formatDateTime(activeAsset.createdAt) }}</span>
                                        <span class="inline-flex items-center gap-1">
                                            <component :is="kindIcon(activeAsset.kind)" class="size-3.5" />
                                            {{ formatBytes(activeAsset.sizeBytes) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68"
                                    title="Open upload info"
                                    @click="openAssetInfo(activeAsset)"
                                >
                                    <Info class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68"
                                    aria-label="Close preview"
                                    @click="activeAssetId = null"
                                >
                                    <X class="size-5" />
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="activeAsset.kind === 'photo' && activeAsset.previewUrl && !isMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                            class="absolute inset-0 animate-pulse bg-white/8"
                        />
                        <img
                            v-if="activeAsset.kind === 'photo' && activeAsset.previewUrl"
                            :src="activeAsset.previewUrl"
                            alt="Selected event upload"
                            class="block h-full w-full object-contain px-2 py-3 sm:px-4 sm:py-4"
                            :class="isMediaLoaded(mediaLoadKey(activeAsset.id, 'preview')) ? 'opacity-100' : 'opacity-0'"
                            @load="markMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                            @error="markMediaLoaded(mediaLoadKey(activeAsset.id, 'preview'))"
                        />
                        <video
                            v-else-if="activeAsset.kind === 'video' && activeAsset.previewUrl"
                            :src="activeAsset.previewUrl"
                            :poster="activeAsset.thumbnailUrl ?? undefined"
                            class="block h-full w-full object-contain px-2 py-3 sm:px-4 sm:py-4"
                            controls
                            autoplay
                            playsinline
                        />
                        <div
                            v-else-if="
                                activeAsset.kind === 'video' &&
                                activeAsset.videoProcessing
                            "
                            class="flex h-full w-full flex-col items-center justify-center gap-4 p-6 text-center text-white/78"
                        >
                            <LoaderCircle class="size-10 animate-spin text-white/55" />
                            <div class="space-y-1">
                                <p class="text-base font-semibold text-white">
                                    Processing video
                                </p>
                                <p class="text-sm text-white/68">
                                    Preview is still being generated.
                                </p>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center p-6 sm:p-10"
                        >
                            <div
                                class="flex aspect-square w-full max-w-[min(78vh,46rem)] items-center justify-center px-8 py-10 shadow-lg"
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
                            class="absolute left-3 top-1/2 z-20 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30 sm:left-5"
                            :disabled="!hasPreviousAsset"
                            aria-label="Previous upload"
                            @click="goToPreviousAsset"
                        >
                            <ChevronLeft class="size-5" />
                        </button>
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 z-20 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30 sm:right-5"
                            :disabled="!hasNextAsset"
                            aria-label="Next upload"
                            @click="goToNextAsset"
                        >
                            <ChevronRight class="size-5" />
                        </button>

                        <div class="absolute inset-x-0 bottom-0 z-20 px-4 pb-4 pt-12 sm:px-6 sm:pb-5">
                            <div class="flex flex-col gap-3">
                                <p
                                    v-if="activeAsset.kind !== 'text' && activeAsset.message"
                                    class="max-w-3xl whitespace-pre-wrap text-sm leading-relaxed text-white/88"
                                >
                                    {{ activeAsset.message }}
                                </p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        v-if="activeAsset.previewUrl"
                                        type="button"
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68"
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
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68"
                                        title="Download or open file"
                                    >
                                        <Download class="size-4" />
                                    </a>
                                    <button
                                        v-if="canManageMedia"
                                        type="button"
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30"
                                        :disabled="
                                            wallVisibilityAssetId === activeAsset.id ||
                                            activeAsset.wallVisibility === 'approved'
                                        "
                                        title="Show on photo wall"
                                        @click="updateWallVisibility(activeAsset, 'approved')"
                                    >
                                        <ThumbsUp class="size-4" />
                                    </button>
                                    <button
                                        v-if="canManageMedia"
                                        type="button"
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30"
                                        :disabled="
                                            wallVisibilityAssetId === activeAsset.id ||
                                            activeAsset.wallVisibility === 'rejected'
                                        "
                                        title="Hide from photo wall"
                                        @click="updateWallVisibility(activeAsset, 'rejected')"
                                    >
                                        <ThumbsDown class="size-4" />
                                    </button>
                                    <button
                                        v-if="canManageMedia"
                                        type="button"
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30"
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
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-[#2d221a]/48 text-white ring-1 ring-white/16 backdrop-blur transition hover:bg-[#2d221a]/68 disabled:pointer-events-none disabled:opacity-30"
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
                                        class="inline-flex size-10 items-center justify-center rounded-full bg-rose-500/70 text-white ring-1 ring-rose-200/20 backdrop-blur transition hover:bg-rose-500/84"
                                        title="Delete"
                                        @click="requestDeleteAsset(activeAsset)"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </div>
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
            <DrawerContent class="w-full max-w-xl overflow-hidden border-l border-[#e7dccb] bg-[#f6efe5]">
                <div
                    v-if="assetInfoAsset"
                    class="flex h-full max-h-screen flex-col"
                >
                    <DrawerHeader class="relative border-b border-[#e7dccb] px-6 py-5 pr-16 text-left">
                        <DrawerTitle class="text-lg font-semibold text-slate-900">
                            Upload info
                        </DrawerTitle>
                        <DrawerDescription class="text-xs text-slate-600">
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

                    <div class="flex-1 overflow-y-auto px-6 py-6">
                        <div class="flex items-center gap-3 border-b border-[#e7dccb] pb-5">
                            <Avatar class="size-12 border border-[#e7dccb]">
                                <AvatarFallback
                                    :class="avatarFallbackClass(assetInfoAsset.guestName)"
                                >
                                    {{ guestInitials(assetInfoAsset.guestName) }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ assetInfoAsset.guestName }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500">
                                    {{ formatDateTime(assetInfoAsset.createdAt) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-x-6 gap-y-4 border-b border-[#e7dccb] py-5 sm:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    File
                                </p>
                                <p class="break-words text-xs text-slate-700">
                                    {{ displayAssetFilename(assetInfoAsset) }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Size
                                </p>
                                <p class="text-xs text-slate-700">
                                    {{ formatBytes(assetInfoAsset.sizeBytes) }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Type
                                </p>
                                <p class="text-xs capitalize text-slate-700">
                                    {{ assetInfoAsset.kind }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Status
                                </p>
                                <p class="text-xs capitalize text-slate-700">
                                    {{ assetInfoAsset.moderationStatus }}
                                </p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Pipeline
                                </p>
                                <p class="text-xs text-slate-700">
                                    {{ moderationPipelineLabel(assetInfoAsset.moderationPipeline) }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.moderationScore !== null"
                                class="space-y-1"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Score
                                </p>
                                <p class="text-xs text-slate-700">
                                    {{ assetInfoAsset.moderationScore }}/100
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.reviewedAt"
                                class="space-y-1"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Reviewed
                                </p>
                                <p class="text-xs text-slate-700">
                                    {{ formatDateTime(assetInfoAsset.reviewedAt) }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.mimeType"
                                class="space-y-1 sm:col-span-2"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Format
                                </p>
                                <p class="break-all text-xs text-slate-700">
                                    {{ assetInfoAsset.mimeType }}
                                </p>
                            </div>
                            <div
                                v-if="assetInfoAsset.moderationMatches.length > 0"
                                class="space-y-2 sm:col-span-2"
                            >
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                    Matched rules
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="match in assetInfoAsset.moderationMatches"
                                        :key="`${match.category}-${match.keyword}`"
                                        class="inline-flex items-center rounded-full border border-amber-300/70 bg-amber-100/60 px-2 py-0.5 text-[11px] text-amber-900"
                                    >
                                        {{ moderationMatchLabel(match) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="assetInfoAsset.guestEmail || assetInfoAsset.guestPhone"
                            class="space-y-3 border-b border-[#e7dccb] py-5"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                Contact
                            </p>
                            <div class="space-y-2">
                                <div
                                    v-if="assetInfoAsset.guestEmail"
                                    class="flex items-center gap-3"
                                >
                                    <Mail class="size-4 text-slate-500" />
                                    <span class="break-all text-xs text-slate-700">
                                        {{ assetInfoAsset.guestEmail }}
                                    </span>
                                </div>
                                <div
                                    v-if="assetInfoAsset.guestPhone"
                                    class="flex items-center gap-3"
                                >
                                    <Phone class="size-4 text-slate-500" />
                                    <span class="text-xs text-slate-700">
                                        {{ assetInfoAsset.guestPhone }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="assetInfoAsset.kind !== 'text' && assetInfoAsset.message"
                            class="space-y-2 border-b border-[#e7dccb] py-5"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                Guest message
                            </p>
                            <p class="whitespace-pre-wrap text-xs leading-relaxed text-slate-700">
                                {{ assetInfoAsset.message }}
                            </p>
                        </div>

                        <div
                            v-if="assetInfoAsset.kind === 'text' && assetInfoAsset.text"
                            class="space-y-2 border-b border-[#e7dccb] py-5"
                        >
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                Text post
                            </p>
                            <p class="whitespace-pre-wrap text-xs leading-relaxed text-slate-700">
                                {{ assetInfoAsset.text }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 py-5">
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
                                        wallVisibilityAssetId === assetInfoAsset.id ||
                                        assetInfoAsset.wallVisibility === 'approved'
                                    "
                                    title="Show on photo wall"
                                    @click="updateWallVisibility(assetInfoAsset, 'approved')"
                                >
                                    <ThumbsUp class="size-4" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="outline"
                                    :disabled="
                                        wallVisibilityAssetId === assetInfoAsset.id ||
                                        assetInfoAsset.wallVisibility === 'rejected'
                                    "
                                    title="Hide from photo wall"
                                    @click="updateWallVisibility(assetInfoAsset, 'rejected')"
                                >
                                    <ThumbsDown class="size-4" />
                                </Button>
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
                                    @click="requestDeleteAsset(assetInfoAsset)"
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
