<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowUp,
    AlertTriangle,
    CalendarDays,
    Camera,
    ChevronLeft,
    ChevronRight,
    CheckCircle2,
    Clock3,
    Columns2,
    Columns3,
    Copy,
    Download,
    EyeOff,
    Film,
    Heart,
    Info,
    Images,
    Lock,
    Menu,
    MessageCircle,
    MessageSquareText,
    MoreHorizontal,
    Rows3,
    Send,
    Trash2,
    LoaderCircle,
    UploadCloud,
    X,
} from 'lucide-vue-next';
import {
    IconBoxMultiple,
    IconFileText,
    IconLanguage,
    IconPhoto,
    IconVideo,
} from '@tabler/icons-vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Drawer,
    DrawerContent,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
} from '@/components/ui/drawer';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
    InputGroupText,
} from '@/components/ui/input-group';
import {
    Item,
    ItemContent,
    ItemDescription,
    ItemMedia,
    ItemTitle,
} from '@/components/ui/item';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTranslations } from '@/composables/useTranslations';

const page = usePage<{
    name?: string;
    locale?: {
        current?: string;
        available?: string[];
    };
}>();
const appName = computed(() => page.props.name ?? 'QR Events');

type Limits = {
    storageLimitBytes: number;
    storageUsedBytes: number;
    storageRemainingBytes: number;
    uploadLimit: number;
    uploadCount: number;
    uploadRemaining: number;
    photoMaxSizeBytes: number;
    videoMaxSizeBytes: number;
    videoMinDurationSeconds: number;
    videoMaxDurationSeconds: number;
};

type AssetItem = {
    id: number;
    kind: 'photo' | 'video' | 'text';
    moderationStatus: string;
    thumbnailUrl: string | null;
    previewUrl: string | null;
    videoProcessing: boolean;
    downloadUrl: string;
    deleteUrl: string;
    likeToggleUrl: string;
    sizeBytes: number;
    text: string | null;
    textThemeId: number | null;
    textThemeSlug: string | null;
    textThemeImageUrl: string | null;
    textThemeBackgroundColor: string | null;
    textThemeTextColor: string | null;
    guestName: string | null;
    guestAvatarUrl: string | null;
    message: string | null;
    originalFilename: string | null;
    mimeType: string | null;
    createdAt: string | null;
    uploadBatchId: string | null;
    uploadBatchIndex: number | null;
    galleryGroupKey: string;
    captionTitle: string | null;
    captionSubtitle: string | null;
    likeCount: number;
    commentCount: number;
    commentsUrl: string;
    commentStoreUrl: string;
};

type AssetComment = {
    id: number;
    body: string;
    guestName: string;
    guestAvatarUrl: string | null;
    createdAt: string | null;
    likeCount: number;
    liked: boolean;
    likeToggleUrl: string;
};

type UploadPreviewItem = {
    key: string;
    kind: 'photo' | 'video';
    name: string;
    objectUrl: string;
};

type TextPostThemeOption = {
    id: number;
    slug: string;
    name: string;
    imageUrl: string;
    backgroundColor: string | null;
    textColor: string;
};

type GalleryStack = {
    key: string;
    preview: AssetItem;
    assets: AssetItem[];
    guestName: string;
    latestCreatedAt: string | null;
    latestTimestamp: number;
    mediaCount: number;
};

type GuestIntent =
    | 'upload_media'
    | 'video_testimonial'
    | 'text_wish'
    | 'browse_gallery';

type UploadMode = 'mixed' | 'video_only';
type GalleryViewMode = 'grid3' | 'grid2' | 'feed';

type WelcomeField = {
    id: string;
    type: 'text' | 'email' | 'phone' | 'number';
    label: string;
    help_text: string;
    attach_to: 'caption_title' | 'caption_subtitle' | 'file_name' | null;
    required: boolean;
    enabled: boolean;
};

type WelcomeScreenConfig = {
    enabled: boolean;
    title: string;
    subtitle: string;
    buttonText: string;
    font: 'montserrat' | 'poppins' | 'playfair_display' | 'dm_sans';
    animated: boolean;
    logoUrl?: string | null;
    collectName: boolean;
    collectEmail: boolean;
    collectPhone: boolean;
    fields?: WelcomeField[];
    backgroundUrl: string | null;
};

type PublicLinks = {
    album: string;
    wall: string;
};

type AppearanceConfig = {
    primaryColor: string | null;
    accentColor: string | null;
    logoUrl: string | null;
    hideSideImages: boolean;
    hideQrCode: boolean;
    hideCaption: boolean;
    captionTheme: 'dark' | 'light';
    albumBackgroundEnabled: boolean;
    albumBackgroundMode: 'rotate' | 'solid' | 'preset' | 'image';
    albumBackgroundColor: string;
    albumBackgroundImageUrl: string | null;
};

type GuestProfilePayload = {
    id: number;
    guestToken: string;
    name: string;
    email: string | null;
    phone: string | null;
    avatarUrl: string | null;
    guestFields: Record<string, string>;
    lastIntent: GuestIntent | null;
};

const props = defineProps<{
    shareToken: string;
    eventName: string;
    eventDate: string | null;
    uploadWindowStartsAt: string | null;
    uploadWindowEndsAt: string | null;
    isUploadOpen: boolean;
    isUploadAllowed: boolean;
    isPaymentLocked: boolean;
    isPreEventTestMode: boolean;
    canViewGallery: boolean;
    preEventTestUploadLimit: number;
    preEventTestUploadsRemaining: number;
    guestProfileUrl: string;
    guestProfileUpsertUrl: string;
    uploadUrl: string;
    textPostUrl: string;
    textPostThemes: TextPostThemeOption[];
    allowTextPosts: boolean;
    allowedMediaTypes: string[];
    canGuestDownload: boolean;
    showPoweredBy: boolean;
    links: PublicLinks;
    assetFeedUrl: string;
    albumQrDataUrl: string;
    appearance: AppearanceConfig;
    welcomeScreen: WelcomeScreenConfig;
    limits: Limits;
    assets: AssetItem[];
    assetsNextCursor: number | null;
    assetsHasMore: boolean;
}>();

const { locale, t } = useTranslations();

const fileInputRef = ref<HTMLInputElement | null>(null);
const guestAvatarInputRef = ref<HTMLInputElement | null>(null);
const textComposerRef = ref<HTMLTextAreaElement | null>(null);
const loadMoreSentinelRef = ref<HTMLElement | null>(null);
const menuOpen = ref(false);
const isLanguagePickerOpen = ref(false);
const onboardingStep = ref<1 | 2>(1);
const onboardingDone = ref(false);
const guestToken = ref('');
const guestFieldValues = ref<Record<string, string>>({
    name: '',
    email: '',
    phone: '',
});
const guestAvatarUrl = ref<string | null>(null);
const guestAvatarPreviewUrl = ref<string | null>(null);
const guestAvatarFile = ref<File | null>(null);
const removeGuestAvatar = ref(false);
const onboardingErrors = ref<Record<string, string>>({});
const guestProfileError = ref<string | null>(null);
const guestProfileProcessing = ref(false);
const selectedIntent = ref<GuestIntent>('browse_gallery');
const activeView = ref<GuestIntent>('browse_gallery');
const pendingComposerIntent = ref<GuestIntent | null>(null);
const galleryView = ref<GalleryViewMode>('grid3');
const isValidatingVideos = ref(false);
const clientValidationErrors = ref<string[]>([]);
const assetItems = ref<AssetItem[]>([...props.assets]);
const likedAssetIds = ref<Record<number, boolean>>({});
const likeAnimatingAssetIds = ref<Record<number, boolean>>({});
const likePendingAssetIds = ref<Record<number, boolean>>({});
const commentItemsByAssetId = ref<Record<number, AssetComment[]>>({});
const commentLoadingAssetIds = ref<Record<number, boolean>>({});
const commentPendingAssetIds = ref<Record<number, boolean>>({});
const commentLikePendingIds = ref<Record<number, boolean>>({});
const expandedMessageKeys = ref<Record<string, boolean>>({});
const uploadPreviewItems = ref<UploadPreviewItem[]>([]);
const commentDraft = ref('');
const commentError = ref<string | null>(null);
const activeStackKey = ref<string | null>(null);
const activeStackSlideIndex = ref(0);
const activeInfoStackKey = ref<string | null>(null);
const activeInfoSlideIndex = ref(0);
const activeCommentsStackKey = ref<string | null>(null);
const isAssetInfoOpen = ref(false);
const isAssetCommentsOpen = ref(false);
const isComposerOpen = ref(false);
const isPreEventInfoOpen = ref(false);
const isHeaderCollapsed = ref(false);
const showScrollTopButton = ref(false);
const heroSectionRef = ref<HTMLElement | null>(null);
const heroGlassCardRef = ref<HTMLElement | null>(null);
const isTextComposerFocused = ref(false);
const loadedPhotoAssetIds = ref<Record<number, boolean>>({});
const viewerTouchStartX = ref<number | null>(null);
const viewerTouchStartY = ref<number | null>(null);
const viewerTouchCurrentX = ref<number | null>(null);
const viewerTouchCurrentY = ref<number | null>(null);
const isAlbumRefreshing = ref(false);
const isLoadingMoreAssets = ref(false);
const hasPendingAlbumUpdate = ref(false);
const pendingAlbumUpdateCount = ref(0);
const pullRefreshStartY = ref<number | null>(null);
const pullRefreshDistance = ref(0);
const lastTouchEndAt = ref(0);
const useMorphingHeader = false;
const morphingHeaderState = ref({
    progress: 0,
    left: 12,
    top: 12,
    width: 0,
    height: 0,
    radius: 32,
    viewportHeight: 0,
});
let morphingHeaderFrameId: number | null = null;
let heroGlassCardResizeObserver: ResizeObserver | null = null;
let albumUpdatePollId: number | null = null;
let processingVideoPollId: number | null = null;
let loadMoreObserver: IntersectionObserver | null = null;
let composerOpenTimeoutId: number | null = null;
let lockedScrollY = 0;
const commentEmojiOptions = [
    '❤️',
    '👏',
    '😍',
    '🥹',
    '🔥',
    '🎉',
    '😂',
    '🙏',
    '💯',
    '🤍',
    '✨',
    '🥂',
    '🫶',
    '😘',
    '😭',
    '🙌',
];
const uploadMessageEmojiOptions = ['❤️', '🥂', '✨', '🎉', '📸', '🤍', '🫶', '🥹'];

const uploadForm = useForm<{
    files: File[];
    guest_name: string;
    guest_email: string | null;
    guest_phone: string | null;
    message: string | null;
    guest_token: string | null;
    guest_fields: Record<string, string>;
    guest_intent: GuestIntent | null;
}>({
    files: [],
    guest_name: '',
    guest_email: null,
    guest_phone: null,
    message: null,
    guest_token: null,
    guest_fields: {},
    guest_intent: null,
});

const textForm = useForm<{
    text: string;
    text_post_theme_id: number | null;
    guest_name: string;
    guest_email: string | null;
    guest_phone: string | null;
    guest_token: string | null;
    guest_fields: Record<string, string>;
    guest_intent: GuestIntent | null;
}>({
    text: '',
    text_post_theme_id: null,
    guest_name: '',
    guest_email: null,
    guest_phone: null,
    guest_token: null,
    guest_fields: {},
    guest_intent: null,
});

const deleteAssetForm = useForm<{
    guest_name: string;
    guest_token: string | null;
}>({
    guest_name: '',
    guest_token: null,
});

const guestStorageKey = computed(() => `kululu-guest-profile:${props.shareToken}`);
const galleryViewStorageKey = computed(
    () => `kululu-gallery-view:${props.shareToken}`,
);
const assetNextCursor = ref<number | null>(props.assetsNextCursor);
const hasMoreAssets = ref(props.assetsHasMore);
const sortStackAssets = (assets: AssetItem[]): AssetItem[] => {
    return [...assets].sort((left, right) => {
        if (left.createdAt && right.createdAt) {
            const createdAtDelta =
                new Date(right.createdAt).getTime() -
                new Date(left.createdAt).getTime();
            if (createdAtDelta !== 0) {
                return createdAtDelta;
            }
        } else if (left.createdAt || right.createdAt) {
            return right.createdAt ? 1 : -1;
        }

        if (
            left.uploadBatchId !== null &&
            left.uploadBatchId === right.uploadBatchId &&
            left.uploadBatchIndex !== null &&
            right.uploadBatchIndex !== null
        ) {
            return left.uploadBatchIndex - right.uploadBatchIndex;
        }

        return right.id - left.id;
    });
};
const galleryStacks = computed<GalleryStack[]>(() => {
    const stacks = new Map<string, GalleryStack>();

    for (const asset of assetItems.value) {
        const key = asset.galleryGroupKey;
        const guestName =
            asset.guestName && asset.guestName.trim().length > 0
                ? asset.guestName.trim()
                : t('public.shared.guest');
        const createdAt = asset.createdAt;
        const createdAtTimestamp = createdAt
            ? new Date(createdAt).getTime()
            : asset.id;

        const existing = stacks.get(key);
        if (!existing) {
            stacks.set(key, {
                key,
                preview: asset,
                assets: [asset],
                guestName,
                latestCreatedAt: createdAt,
                latestTimestamp: createdAtTimestamp,
                mediaCount: asset.kind === 'text' ? 0 : 1,
            });
            continue;
        }

        existing.assets.push(asset);
        const hadNewerTimestamp = createdAtTimestamp > existing.latestTimestamp;
        existing.latestTimestamp = Math.max(existing.latestTimestamp, createdAtTimestamp);
        if (
            existing.latestCreatedAt === null ||
            (createdAt && hadNewerTimestamp)
        ) {
            existing.latestCreatedAt = createdAt;
        }
        if (asset.kind !== 'text') {
            existing.mediaCount += 1;
        }
    }

    return Array.from(stacks.values())
        .map((stack) => {
            const sortedAssets = sortStackAssets(stack.assets);
            return {
                ...stack,
                assets: sortedAssets,
                preview: sortedAssets[0] ?? stack.preview,
                mediaCount: sortedAssets.filter((asset) => asset.kind !== 'text')
                    .length,
            };
        })
        .sort((left, right) => right.latestTimestamp - left.latestTimestamp);
});
const hasProcessingVideoAssets = computed(() =>
    assetItems.value.some(
        (asset) => asset.kind === 'video' && asset.videoProcessing,
    ),
);
const selectedStack = computed(() => {
    if (activeStackKey.value === null) {
        return null;
    }

    return (
        galleryStacks.value.find((stack) => stack.key === activeStackKey.value) ??
        null
    );
});
const selectedInfoStack = computed(() => {
    if (activeInfoStackKey.value === null) {
        return null;
    }

    return (
        galleryStacks.value.find((stack) => stack.key === activeInfoStackKey.value) ??
        null
    );
});
const selectedCommentsStack = computed(() => {
    if (activeCommentsStackKey.value === null) {
        return null;
    }

    return (
        galleryStacks.value.find((stack) => stack.key === activeCommentsStackKey.value) ??
        null
    );
});
const selectedStackAssets = computed<AssetItem[]>(
    () => selectedStack.value?.assets ?? [],
);
const selectedInfoStackAssets = computed<AssetItem[]>(
    () => selectedInfoStack.value?.assets ?? [],
);
const selectedCommentsAsset = computed<AssetItem | null>(
    () => selectedCommentsStack.value?.preview ?? null,
);
const selectedComments = computed<AssetComment[]>(() => {
    const assetId = selectedCommentsAsset.value?.id ?? null;

    return assetId !== null ? (commentItemsByAssetId.value[assetId] ?? []) : [];
});
const selectedAsset = computed(() => {
    if (selectedStackAssets.value.length === 0) {
        return null;
    }

    const safeIndex = Math.min(
        Math.max(activeStackSlideIndex.value, 0),
        selectedStackAssets.value.length - 1,
    );

    return selectedStackAssets.value[safeIndex] ?? null;
});
const selectedInfoAsset = computed(() => {
    if (selectedInfoStackAssets.value.length === 0) {
        return null;
    }

    const safeIndex = Math.min(
        Math.max(activeInfoSlideIndex.value, 0),
        selectedInfoStackAssets.value.length - 1,
    );

    return selectedInfoStackAssets.value[safeIndex] ?? null;
});
const hasMultipleInSelectedStack = computed(
    () => selectedStackAssets.value.length > 1,
);
const selectedAssetCanDelete = computed(() =>
    canDeleteAsset(selectedAsset.value),
);
const selectedAssetCanDownload = computed(() =>
    canDownloadAsset(selectedAsset.value),
);

const isPhotoLoaded = (assetId: number): boolean =>
    loadedPhotoAssetIds.value[assetId] === true;

const markPhotoAsLoaded = (assetId: number): void => {
    loadedPhotoAssetIds.value = {
        ...loadedPhotoAssetIds.value,
        [assetId]: true,
    };
};

const canUpload = computed(
    () => props.isUploadAllowed && !props.isPaymentLocked,
);
const customWelcomeEnabled = computed(() => props.welcomeScreen.enabled);
const showQrCode = computed(() => !props.appearance.hideQrCode);
const showCaptions = computed(() => !props.appearance.hideCaption);
const showPreviewWatermark = computed(() => !props.canGuestDownload);
const canUploadText = computed(() => canUpload.value && props.allowTextPosts);
const canUploadPhotos = computed(() => props.allowedMediaTypes.includes('photo'));
const canUploadVideos = computed(() => props.allowedMediaTypes.includes('video'));
const hasAlbumImageBackground = computed(
    () =>
        props.appearance.albumBackgroundEnabled
        && ['preset', 'image'].includes(props.appearance.albumBackgroundMode)
        && Boolean(props.appearance.albumBackgroundImageUrl),
);
const selectedTextPostTheme = computed<TextPostThemeOption | null>(() => {
    const selectedId = textForm.text_post_theme_id;
    if (selectedId !== null) {
        const selected = props.textPostThemes.find((theme) => theme.id === selectedId);
        if (selected) {
            return selected;
        }
    }

    return props.textPostThemes[0] ?? null;
});

const fileAccept = computed(() => {
    const accepted: string[] = [];
    if (canUploadPhotos.value) {
        accepted.push('image/*');
    }
    if (canUploadVideos.value) {
        accepted.push('video/*');
    }

    return accepted.join(',');
});

const uploadMode = computed<UploadMode>(() =>
    activeView.value === 'video_testimonial' ? 'video_only' : 'mixed',
);
const uploadAccept = computed(() => {
    if (uploadMode.value === 'video_only') {
        return canUploadVideos.value ? 'video/*' : '';
    }

    return fileAccept.value;
});

const usageStoragePercent = computed(() => {
    if (props.limits.storageLimitBytes <= 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round(
            (props.limits.storageUsedBytes / props.limits.storageLimitBytes) *
                100,
        ),
    );
});

const usageUploadsPercent = computed(() => {
    if (props.limits.uploadLimit <= 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round((props.limits.uploadCount / props.limits.uploadLimit) * 100),
    );
});
const preEventUsedUploads = computed(() =>
    Math.max(
        0,
        props.preEventTestUploadLimit - props.preEventTestUploadsRemaining,
    ),
);

const statusCard = computed(() => {
    if (props.isPaymentLocked) {
        return {
            title: t('public.album.status.locked_title'),
            description: t('public.album.status.locked_description'),
            classes: 'border-rose-200 bg-rose-50 text-rose-800',
            icon: Lock,
        };
    }

    if (props.isUploadOpen) {
        return {
            title: t('public.album.status.open_title'),
            description: t('public.album.status.open_description'),
            classes: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            icon: CheckCircle2,
        };
    }

    if (props.isPreEventTestMode) {
        return {
            title: t('public.album.status.pre_event_title'),
            description: t('public.album.status.pre_event_description', {
                used: preEventUsedUploads.value,
                limit: props.preEventTestUploadLimit,
                remaining: props.preEventTestUploadsRemaining,
            }),
            classes: 'border-sky-200 bg-sky-50 text-sky-800',
            icon: AlertTriangle,
        };
    }

    return {
        title: t('public.album.status.paused_title'),
        description: t('public.album.status.paused_description'),
        classes: 'border-amber-200 bg-amber-50 text-amber-900',
        icon: Clock3,
    };
});

const uploadTitle = computed(() =>
    uploadMode.value === 'video_only'
        ? t('public.album.upload.video_title')
        : t('public.album.upload.mixed_title'),
);
const uploadDescription = computed(() =>
    uploadMode.value === 'video_only'
        ? t('public.album.upload.video_description', {
              min: props.limits.videoMinDurationSeconds,
              max: props.limits.videoMaxDurationSeconds,
          })
        : t('public.album.upload.mixed_description'),
);
const uploadButtonLabel = computed(() =>
    uploadMode.value === 'video_only'
        ? t('public.album.upload.video_button')
        : t('public.album.upload.mixed_button'),
);

const intentOptions = computed(() => [
    {
        value: 'upload_media' as GuestIntent,
        label: t('public.album.intent.upload_media_label'),
        description: t('public.album.intent.upload_media_description'),
        icon: Camera,
        enabled: canUploadPhotos.value || canUploadVideos.value,
    },
    {
        value: 'video_testimonial' as GuestIntent,
        label: t('public.album.intent.video_testimonial_label'),
        description: t('public.album.intent.video_testimonial_description'),
        icon: Film,
        enabled: canUploadVideos.value,
    },
    {
        value: 'text_wish' as GuestIntent,
        label: t('public.album.intent.text_wish_label'),
        description: t('public.album.intent.text_wish_description'),
        icon: MessageSquareText,
        enabled: props.allowTextPosts,
    },
    {
        value: 'browse_gallery' as GuestIntent,
        label: t('public.album.intent.browse_gallery_label'),
        description: t('public.album.intent.browse_gallery_description'),
        icon: Images,
        enabled: props.canViewGallery,
    },
]);

const defaultIntent = (): GuestIntent =>
    intentOptions.value.find((option) => option.enabled)?.value ?? 'browse_gallery';

const welcomeGuestFields = computed<WelcomeField[]>(() => {
    if (!customWelcomeEnabled.value) {
        return [
            {
                id: 'name',
                type: 'text',
                label: t('public.shared.name'),
                help_text: t('public.album.welcome.write_your_field', {
                    field: t('public.shared.name').toLowerCase(),
                }),
                attach_to: null,
                required: true,
                enabled: true,
            },
        ];
    }

    const configured = Array.isArray(props.welcomeScreen.fields)
        ? props.welcomeScreen.fields.filter((field) => field.enabled)
        : [];
    if (configured.length > 0) {
        return configured;
    }

    const fallback: WelcomeField[] = [
        {
            id: 'name',
            type: 'text',
            label: t('public.shared.name'),
            help_text: t('public.album.welcome.write_your_field', {
                field: t('public.shared.name').toLowerCase(),
            }),
            attach_to: null,
            required: true,
            enabled: true,
        },
    ];
    if (props.welcomeScreen.collectEmail) {
        fallback.push({
            id: 'email',
            type: 'email',
            label: t('public.shared.email'),
            help_text: t('public.album.welcome.write_your_field', {
                field: t('public.shared.email').toLowerCase(),
            }),
            attach_to: null,
            required: false,
            enabled: true,
        });
    }
    if (props.welcomeScreen.collectPhone) {
        fallback.push({
            id: 'phone',
            type: 'phone',
            label: t('public.shared.phone'),
            help_text: t('public.album.welcome.write_your_field', {
                field: t('public.shared.phone').toLowerCase(),
            }),
            attach_to: null,
            required: false,
            enabled: true,
        });
    }

    return fallback;
});

const guestName = computed(() => guestFieldValues.value.name ?? '');
const guestEmail = computed(() => guestFieldValues.value.email ?? '');
const guestPhone = computed(() => guestFieldValues.value.phone ?? '');
const currentGuestAvatarUrl = computed(
    () => guestAvatarPreviewUrl.value ?? guestAvatarUrl.value,
);
const albumAvatarFallback = computed(() =>
    (welcomeTitle.value || props.eventName || 'K').trim().charAt(0).toUpperCase(),
);
const albumLogoUrl = computed(
    () => props.welcomeScreen.logoUrl ?? props.appearance.logoUrl ?? null,
);
const albumPrimaryColor = computed(
    () => props.appearance.primaryColor || '#0f172a',
);
const albumAccentColor = computed(
    () => props.appearance.accentColor || props.appearance.primaryColor || '#334155',
);
const heroAccentStyle = computed(() => {
    return {
        backgroundColor: albumPrimaryColor.value,
        boxShadow: `0 12px 26px color-mix(in srgb, ${albumPrimaryColor.value} 18%, transparent)`,
    };
});
const albumGradientStyle = computed(() => ({
    backgroundImage: `linear-gradient(135deg, ${albumPrimaryColor.value} 0%, ${albumAccentColor.value} 56%, #fff7ed 100%)`,
}));
const albumTintStyle = computed(() => ({
    borderColor: `${albumPrimaryColor.value}33`,
    backgroundColor: `${albumPrimaryColor.value}18`,
    color: '#ffffff',
}));
const uploadProcessingTitle = computed(() => {
    if (activeView.value === 'video_testimonial') {
        return t('public.album.processing.uploading_video');
    }

    const hasPhotos = uploadForm.files.some((file) => file.type.startsWith('image/'));
    const hasVideos = uploadForm.files.some((file) => file.type.startsWith('video/'));

    if (hasPhotos && hasVideos) {
        return t('public.album.processing.uploading_photos_video');
    }

    if (hasVideos) {
        return t('public.album.processing.uploading_video');
    }

    return t('public.album.processing.uploading_photos');
});
const uploadProcessingDescription = computed(() => {
    if (activeView.value === 'video_testimonial') {
        return t('public.album.processing.uploading_video_hint');
    }

    return t('public.album.processing.uploading_files_hint');
});
const heroBackgroundStyle = computed((): Record<string, string> => {
    const style: Record<string, string> = {};

    if (!onboardingDone.value || !props.appearance.albumBackgroundEnabled) {
        return style;
    }

    if (props.appearance.albumBackgroundMode === 'solid') {
        style.backgroundColor =
            props.appearance.albumBackgroundColor || '#ffffff';

        return style;
    }

    if (hasAlbumImageBackground.value && props.appearance.albumBackgroundImageUrl) {
        style.backgroundImage = `url(${props.appearance.albumBackgroundImageUrl})`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
        style.backgroundAttachment = 'fixed';
    }

    return style;
});

const albumBodyStyle = computed((): Record<string, string> => ({}));

const welcomeTitle = computed(() => {
    if (!customWelcomeEnabled.value) {
        return props.eventName;
    }

    const value = props.welcomeScreen.title?.trim();
    return value && value.length > 0 ? value : props.eventName;
});

const welcomeSubtitle = computed(() => {
    if (!customWelcomeEnabled.value) {
        return t('public.album.default_subtitle');
    }

    const value = props.welcomeScreen.subtitle?.trim();
    return value && value.length > 0
        ? value
        : t('public.album.default_subtitle');
});

const welcomeButtonText = computed(() => {
    if (!customWelcomeEnabled.value) {
        return t('public.album.continue');
    }

    const value = props.welcomeScreen.buttonText?.trim();
    return value && value.length > 0 ? value : t('public.album.continue');
});

const welcomeFontClass = computed(() => {
    switch (props.welcomeScreen.font) {
        case 'poppins':
            return 'font-[Poppins,sans-serif]';
        case 'playfair_display':
            return 'font-[\"Playfair_Display\",serif]';
        case 'dm_sans':
            return 'font-[\"DM_Sans\",sans-serif]';
        default:
            return 'font-[Montserrat,sans-serif]';
    }
});

const galleryGridClass = computed(() =>
    galleryView.value === 'grid2' ? 'grid-cols-2' : 'grid-cols-3',
);
const latestKnownAssetId = ref(
    props.assets.reduce((max, asset) => Math.max(max, asset.id), 0),
);
const pullRefreshThreshold = 84;
const pullRefreshVisible = computed(
    () => pullRefreshDistance.value > 0 || isAlbumRefreshing.value,
);
const pullRefreshReady = computed(
    () => pullRefreshDistance.value >= pullRefreshThreshold,
);
const pullRefreshIndicatorStyle = computed((): Record<string, string> => ({
    opacity: `${
        isAlbumRefreshing.value
            ? 1
            : Math.min(1, pullRefreshDistance.value / 42)
    }`,
    transform: `translate(-50%, ${
        isAlbumRefreshing.value
            ? 18
            : Math.max(0, pullRefreshDistance.value - 10)
    }px)`,
}));
const albumContentStyle = computed((): Record<string, string> => ({
    ...(pullRefreshDistance.value > 0 || isAlbumRefreshing.value
        ? {
              transform: `translateY(${pullRefreshDistance.value}px)`,
              transition:
                  pullRefreshStartY.value === null
                      ? 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1)'
                      : 'none',
          }
        : {}),
}));
const morphingHeaderProgress = computed(
    () => morphingHeaderState.value.progress,
);
const morphingHeaderRadiusProgress = computed(() =>
    clampNumber((morphingHeaderProgress.value - 0.72) / 0.28, 0, 1),
);
const morphingHeaderVisualProgress = computed(() =>
    menuOpen.value ? 0 : morphingHeaderProgress.value,
);
const morphingHeaderVisible = computed(
    () => useMorphingHeader && (morphingHeaderProgress.value > 0.001 || menuOpen.value),
);
const morphingHeaderExpandedHeight = computed(() =>
    Math.min(
        Math.max(morphingHeaderState.value.viewportHeight - 24, 360),
        640,
    ),
);
const morphingHeaderStyle = computed((): Record<string, string> => ({
    left: `${morphingHeaderState.value.left}px`,
    top: `${morphingHeaderState.value.top}px`,
    width: `${morphingHeaderState.value.width}px`,
    height: `${
        menuOpen.value
            ? morphingHeaderExpandedHeight.value
            : morphingHeaderState.value.height
    }px`,
    borderRadius: `${
        menuOpen.value
            ? 32
            : interpolateNumber(32, 999, morphingHeaderRadiusProgress.value)
    }px`,
    opacity: `${menuOpen.value ? 1 : Math.min(1, morphingHeaderProgress.value * 1.45)}`,
    pointerEvents:
        menuOpen.value || morphingHeaderProgress.value > 0.08 ? 'auto' : 'none',
    transition:
        'height 260ms ease, border-radius 260ms ease, opacity 220ms ease',
}));
const morphingHeaderIdentityStyle = computed((): Record<string, string> => ({
    transform: `scale(${interpolateNumber(1, 0.78, morphingHeaderVisualProgress.value)})`,
    transformOrigin: 'top left',
}));
const morphingHeaderLowerStyle = computed((): Record<string, string> => ({
    opacity: `${1 - clampNumber(morphingHeaderVisualProgress.value * 2.3, 0, 1)}`,
    transform: `translateY(${-12 * morphingHeaderVisualProgress.value}px)`,
}));
const morphingHeaderMaskStyle = computed((): Record<string, string> => ({
    opacity: `${clampNumber(morphingHeaderVisualProgress.value * 1.15, 0, 0.92)}`,
}));
const heroGlassCardStyle = computed((): Record<string, string> => ({
    opacity: useMorphingHeader
        ? `${menuOpen.value ? 0 : 1 - clampNumber(morphingHeaderProgress.value * 1.4, 0, 1)}`
        : '1',
}));
const morphingHeaderTextShadowStyle = computed((): Record<string, string> => ({
    textShadow:
        morphingHeaderProgress.value > 0.35
            ? '0 1px 2px rgba(15,23,42,0.5), 0 6px 18px rgba(15,23,42,0.22)'
            : '0 1px 2px rgba(15,23,42,0.26)',
}));
const morphingHeaderIconShadowStyle = computed((): Record<string, string> => ({
    filter:
        morphingHeaderProgress.value > 0.35
            ? 'drop-shadow(0 1px 2px rgba(15,23,42,0.45)) drop-shadow(0 6px 18px rgba(15,23,42,0.18))'
            : 'drop-shadow(0 1px 2px rgba(15,23,42,0.2))',
}));
const avatarToneClasses = [
    'bg-rose-500 text-white',
    'bg-sky-500 text-white',
    'bg-emerald-500 text-white',
    'bg-amber-500 text-white',
    'bg-violet-500 text-white',
    'bg-fuchsia-500 text-white',
] as const;
const selectedAssetLiked = computed(() =>
    selectedAsset.value !== null
        ? likedAssetIds.value[selectedAsset.value.id] === true
        : false,
);
const showBottomNav = computed(() => onboardingDone.value);
const availableLocales = computed(() => {
    const nextLocales = page.props.locale?.available;

    return Array.isArray(nextLocales) && nextLocales.length > 0
        ? nextLocales.filter((value): value is string => typeof value === 'string')
        : ['en', 'ro', 'el'];
});
const albumLanguageOptions = computed(() => {
    const options = [
        {
            key: 'auto',
            flag: '🌐',
            label: t('public.album.language.auto_title'),
            description: t('public.album.language.auto_description'),
        },
        {
            key: 'en',
            flag: '🇺🇸',
            label: t('public.album.language.english_title'),
            description: t('public.album.language.english_description'),
        },
        {
            key: 'ro',
            flag: '🇷🇴',
            label: t('public.album.language.romanian_title'),
            description: t('public.album.language.romanian_description'),
        },
        {
            key: 'el',
            flag: '🇬🇷',
            label: t('public.album.language.greek_title'),
            description: t('public.album.language.greek_description'),
        },
    ];

    return options.filter(
        (option) =>
            option.key === 'auto' || availableLocales.value.includes(option.key),
    );
});
const selectedAlbumLanguageKey = computed(() => {
    if (typeof window !== 'undefined') {
        const override = new URL(window.location.href).searchParams.get('lang');

        if (override && availableLocales.value.includes(override)) {
            return override;
        }
    }

    return 'auto';
});

const textPostSurfaceStyle = (item: {
    textThemeImageUrl: string | null;
    textThemeBackgroundColor: string | null;
}): Record<string, string> => {
    const style: Record<string, string> = {
        backgroundColor: item.textThemeBackgroundColor || '#0f172a',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
    };

    if (item.textThemeImageUrl) {
        style.backgroundImage = `url(${item.textThemeImageUrl})`;
    }

    return style;
};

const textPostContentStyle = (item: {
    textThemeTextColor: string | null;
}): Record<string, string> => ({
    color: item.textThemeTextColor || '#ffffff',
    opacity: '0.94',
});

const syncTextComposerElement = (value: string): void => {
    if (!textComposerRef.value) {
        return;
    }

    const normalizedValue = value.replace(/\r/g, '');
    if (textComposerRef.value.value.replace(/\r/g, '') === normalizedValue) {
        return;
    }

    textComposerRef.value.value = normalizedValue;
};

const focusTextComposer = (): void => {
    if (
        textComposerRef.value === null ||
        !canUploadText.value ||
        textForm.processing
    ) {
        return;
    }

    const composer = textComposerRef.value;
    composer.focus({ preventScroll: true });
    const caretPosition = composer.value.length;
    composer.setSelectionRange(caretPosition, caretPosition);
};

const onTextComposerInput = (event: Event): void => {
    const composer = event.target as HTMLTextAreaElement | null;
    if (!composer) {
        return;
    }

    const normalizedValue = composer.value
        .replace(/\r/g, '')
        .replace(/\u00A0/g, ' ');

    if (normalizedValue.length > 500) {
        const trimmedValue = normalizedValue.slice(0, 500);
        textForm.text = trimmedValue;
        syncTextComposerElement(trimmedValue);
        return;
    }

    textForm.text = normalizedValue;
};

const preventDoubleTapZoom = (event: TouchEvent): void => {
    const target = event.target instanceof HTMLElement ? event.target : null;
    const isEditableTarget = target?.closest('input, textarea, select, [contenteditable="true"]');
    const now = Date.now();
    const delta = now - lastTouchEndAt.value;
    lastTouchEndAt.value = now;

    if (isEditableTarget || delta <= 0 || delta > 320) {
        return;
    }

    event.preventDefault();
};

const formatBytes = (bytes: number): string => {
    if (bytes === 0) {
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

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return t('public.shared.not_set');
    }

    return new Intl.DateTimeFormat(locale.value || 'en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatRelativeTime = (value: string | null): string => {
    if (!value) {
        return t('public.shared.just_now');
    }

    const deltaSeconds = Math.round(
        (new Date(value).getTime() - Date.now()) / 1000,
    );
    const absoluteSeconds = Math.abs(deltaSeconds);
    const formatter = new Intl.RelativeTimeFormat(
        locale.value || 'en',
        { numeric: 'auto' },
    );

    if (absoluteSeconds < 60) {
        return formatter.format(deltaSeconds, 'second');
    }
    if (absoluteSeconds < 3600) {
        return formatter.format(Math.round(deltaSeconds / 60), 'minute');
    }
    if (absoluteSeconds < 86400) {
        return formatter.format(Math.round(deltaSeconds / 3600), 'hour');
    }
    if (absoluteSeconds < 604800) {
        return formatter.format(Math.round(deltaSeconds / 86400), 'day');
    }

    return formatter.format(Math.round(deltaSeconds / 604800), 'week');
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return t('public.shared.not_set');
    }

    return new Intl.DateTimeFormat(locale.value || 'en', {
        dateStyle: 'long',
    }).format(new Date(value));
};

const formatLikeCount = (count: number): string => {
    if (count < 1000) {
        return `${count}`;
    }

    if (count < 10000) {
        return `${(count / 1000).toFixed(1)}k`;
    }

    return `${Math.round(count / 1000)}k`;
};

const albumLikesCount = computed(() =>
    assetItems.value.reduce((total, asset) => total + asset.likeCount, 0),
);
const albumPhotoCount = computed(
    () => assetItems.value.filter((asset) => asset.kind === 'photo').length,
);
const albumVideoCount = computed(
    () => assetItems.value.filter((asset) => asset.kind === 'video').length,
);
const albumHeaderStats = computed(() => [
    {
        value: formatLikeCount(albumLikesCount.value),
        label: t('public.album.stats.likes'),
    },
    {
        value: formatLikeCount(galleryStacks.value.length),
        label: t('public.album.stats.posts'),
    },
    {
        value: formatLikeCount(albumPhotoCount.value),
        label: t('public.album.stats.photos'),
    },
    {
        value: formatLikeCount(albumVideoCount.value),
        label: t('public.album.stats.videos'),
    },
]);

const stackUploadSummary = (stack: GalleryStack): string | null => {
    if (stack.mediaCount <= 1) {
        return null;
    }

    const mediaAssets = stack.assets.filter((asset) => asset.kind !== 'text');
    const photoCount = mediaAssets.filter((asset) => asset.kind === 'photo').length;
    const videoCount = mediaAssets.filter((asset) => asset.kind === 'video').length;

    if (videoCount === 0) {
        return t('public.album.gallery.stack.photos_uploaded', {
            count: stack.mediaCount,
        });
    }

    if (photoCount === 0) {
        return t('public.album.gallery.stack.videos_uploaded', {
            count: stack.mediaCount,
        });
    }

    return t('public.album.gallery.stack.items_uploaded', {
        count: stack.mediaCount,
    });
};

const stackMediaBadgeIcon = (stack: GalleryStack) =>
    stack.mediaCount > 1
        ? IconBoxMultiple
        : stack.preview.kind === 'video'
          ? IconVideo
          : stack.preview.kind === 'text'
            ? IconFileText
            : IconPhoto;

const openLanguagePicker = (): void => {
    menuOpen.value = false;
    isLanguagePickerOpen.value = true;
};

const closeLanguagePicker = (): void => {
    isLanguagePickerOpen.value = false;
};

const selectAlbumLocale = (localeKey: string): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const url = new URL(window.location.href);
    const nextLocale = localeKey === 'auto' ? 'auto' : localeKey;

    if (nextLocale === 'auto') {
        url.searchParams.delete('lang');
    } else {
        url.searchParams.set('lang', nextLocale);
    }

    isLanguagePickerOpen.value = false;
    router.visit(url.toString(), {
        method: 'get',
        preserveScroll: true,
        preserveState: false,
    });
};

const messagePreviewLimit = 180;

const hasLongStackMessage = (stack: GalleryStack): boolean =>
    (stack.preview.message?.trim().length ?? 0) > messagePreviewLimit;

const isStackMessageExpanded = (stackKey: string): boolean =>
    expandedMessageKeys.value[stackKey] === true;

const displayedStackMessage = (stack: GalleryStack): string => {
    const value = stack.preview.message?.trim() ?? '';
    if (!hasLongStackMessage(stack) || isStackMessageExpanded(stack.key)) {
        return value;
    }

    return `${value.slice(0, messagePreviewLimit).trimEnd()}...`;
};

const toggleStackMessageExpansion = (stackKey: string): void => {
    expandedMessageKeys.value = {
        ...expandedMessageKeys.value,
        [stackKey]: !isStackMessageExpanded(stackKey),
    };
};

const assetSummaryText = (asset: AssetItem): string | null => {
    const primaryText =
        asset.text?.trim() ||
        asset.message?.trim() ||
        asset.captionTitle?.trim() ||
        asset.captionSubtitle?.trim() ||
        null;

    return primaryText && primaryText.length > 0 ? primaryText : null;
};

const latestAssetIdFromItems = (items: Array<{ id: number }>): number =>
    items.reduce((max, item) => Math.max(max, item.id), 0);

const mergeAssetItems = (
    existingItems: AssetItem[],
    nextItems: AssetItem[],
): AssetItem[] => {
    const merged = new Map<number, AssetItem>();

    for (const item of existingItems) {
        merged.set(item.id, item);
    }

    for (const item of nextItems) {
        merged.set(item.id, item);
    }

    return Array.from(merged.values()).sort((left, right) => right.id - left.id);
};

const guestInitials = (value: string | null): string => {
    const normalized = (value ?? '').trim();
    if (normalized.length === 0) {
        return 'G';
    }

    const parts = normalized.split(/\s+/).slice(0, 2);
    return parts
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
};

const avatarFallbackClass = (value: string | null): string => {
    const normalized = (value ?? '').trim();
    const source = normalized.length > 0 ? normalized : 'guest';
    const hash = Array.from(source).reduce(
        (sum, character) => sum + character.charCodeAt(0),
        0,
    );

    return avatarToneClasses[hash % avatarToneClasses.length];
};

const csrfToken = (): string => {
    if (typeof document === 'undefined') {
        return '';
    }

    return (
        document
            .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? ''
    );
};

const clampNumber = (value: number, min: number, max: number): number =>
    Math.min(Math.max(value, min), max);

const interpolateNumber = (
    start: number,
    end: number,
    progress: number,
): number => start + (end - start) * progress;

const updateAssetItem = (
    assetId: number,
    patch: Partial<AssetItem>,
): void => {
    assetItems.value = assetItems.value.map((asset) =>
        asset.id === assetId
            ? {
                  ...asset,
                  ...patch,
              }
            : asset,
    );
};

const applyGuestProfilePayload = (payload: {
    guest: GuestProfilePayload | null;
    likedAssetIds: number[];
}): void => {
    if (payload.guest !== null) {
        guestFieldValues.value = {
            ...guestFieldValues.value,
            ...payload.guest.guestFields,
            name: payload.guest.name,
            email: payload.guest.email ?? '',
            phone: payload.guest.phone ?? '',
        };
        guestAvatarUrl.value = payload.guest.avatarUrl;
    }

    likedAssetIds.value = payload.likedAssetIds.reduce<Record<number, boolean>>(
        (result, assetId) => {
            result[assetId] = true;
            return result;
        },
        {},
    );
};

const updateMorphingHeader = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    showScrollTopButton.value =
        onboardingDone.value &&
        window.scrollY > 720 &&
        activeStackKey.value === null &&
        !menuOpen.value &&
        !isComposerOpen.value &&
        !isAssetCommentsOpen.value &&
        !isAssetInfoOpen.value &&
        !isPreEventInfoOpen.value;

    if (!onboardingDone.value || heroSectionRef.value === null) {
        isHeaderCollapsed.value = false;
        return;
    }

    const revealOffset = 88;
    const heroBottom =
        heroSectionRef.value.offsetTop + heroSectionRef.value.offsetHeight;

    isHeaderCollapsed.value = window.scrollY + revealOffset >= heroBottom;
};

const scrollAlbumToTop = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
};

const scheduleMorphingHeaderUpdate = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    if (morphingHeaderFrameId !== null) {
        return;
    }

    morphingHeaderFrameId = window.requestAnimationFrame(() => {
        morphingHeaderFrameId = null;
        updateMorphingHeader();
    });
};

const syncHeroGlassCardObserver = (): void => {
    heroGlassCardResizeObserver?.disconnect();
    heroGlassCardResizeObserver = null;

    if (
        heroSectionRef.value === null
    ) {
        return;
    }

    if (typeof ResizeObserver !== 'undefined') {
        heroGlassCardResizeObserver = new ResizeObserver(() => {
            scheduleMorphingHeaderUpdate();
        });
        heroGlassCardResizeObserver.observe(heroSectionRef.value);
    }
};

const createGuestToken = (): string => {
    if (
        typeof crypto !== 'undefined' &&
        typeof crypto.randomUUID === 'function'
    ) {
        return crypto.randomUUID();
    }

    return `guest-${Math.random().toString(36).slice(2)}-${Date.now().toString(36)}`;
};

const isIntentEnabled = (intent: GuestIntent): boolean => {
    const found = intentOptions.value.find((option) => option.value === intent);

    return found?.enabled ?? false;
};

watch(
    intentOptions,
    () => {
        if (!isIntentEnabled(selectedIntent.value)) {
            selectedIntent.value = defaultIntent();
        }

        if (!isIntentEnabled(activeView.value)) {
            activeView.value = defaultIntent();
        }
    },
    { immediate: true },
);

const persistGalleryView = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(galleryViewStorageKey.value, galleryView.value);
};

const hydrateGalleryView = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const raw = window.localStorage.getItem(galleryViewStorageKey.value);
    if (raw === 'grid2' || raw === 'feed' || raw === 'grid3') {
        galleryView.value = raw;
    }
};

const revokeGuestAvatarPreview = (): void => {
    if (
        guestAvatarPreviewUrl.value !== null &&
        guestAvatarPreviewUrl.value.startsWith('blob:')
    ) {
        URL.revokeObjectURL(guestAvatarPreviewUrl.value);
    }

    guestAvatarPreviewUrl.value = null;
};

const onGuestAvatarSelectionChange = (event: Event): void => {
    const target = event.target as HTMLInputElement | null;
    const file = target?.files?.[0] ?? null;

    if (file === null) {
        return;
    }

    guestProfileError.value = null;
    guestAvatarFile.value = file;
    removeGuestAvatar.value = false;
    revokeGuestAvatarPreview();
    guestAvatarPreviewUrl.value = URL.createObjectURL(file);
};

const clearGuestAvatarSelection = (): void => {
    guestAvatarFile.value = null;
    removeGuestAvatar.value = true;
    revokeGuestAvatarPreview();
    if (guestAvatarInputRef.value) {
        guestAvatarInputRef.value.value = '';
    }
};

const revokeUploadPreviews = (): void => {
    for (const preview of uploadPreviewItems.value) {
        if (preview.objectUrl.startsWith('blob:')) {
            URL.revokeObjectURL(preview.objectUrl);
        }
    }

    uploadPreviewItems.value = [];
};

const syncUploadPreviews = (files: File[]): void => {
    revokeUploadPreviews();

    uploadPreviewItems.value = files
        .filter(
            (file) =>
                file.type.startsWith('image/') || file.type.startsWith('video/'),
        )
        .map((file, index) => ({
            key: `${file.name}-${file.size}-${index}`,
            kind: file.type.startsWith('video/') ? 'video' : 'photo',
            name: file.name,
            objectUrl: URL.createObjectURL(file),
        }));
};

const openUploadFilePicker = (): void => {
    if (
        !canUpload.value ||
        uploadAccept.value.length === 0 ||
        uploadForm.processing ||
        isValidatingVideos.value
    ) {
        return;
    }

    fileInputRef.value?.click();
};

const appendUploadMessageEmoji = (emoji: string): void => {
    const currentValue = uploadForm.message ?? '';
    const nextValue = `${currentValue}${emoji}`;
    uploadForm.message = nextValue.slice(0, 500);
};

const syncGuestProfile = async (): Promise<void> => {
    if (guestToken.value.trim().length === 0) {
        return;
    }

    try {
        const response = await fetch(
            `${props.guestProfileUrl}?guest_token=${encodeURIComponent(guestToken.value)}`,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as {
            guest: GuestProfilePayload | null;
            likedAssetIds: number[];
        };
        applyGuestProfilePayload(payload);
    } catch {
        // Best-effort sync for public guests; keep local profile state if this fails.
    }
};

const upsertGuestProfile = async (): Promise<boolean> => {
    guestProfileProcessing.value = true;
    guestProfileError.value = null;

    const formData = new FormData();
    formData.set('guest_token', guestToken.value);
    formData.set('guest_name', guestName.value.trim());
    if (guestEmail.value.trim().length > 0) {
        formData.set('guest_email', guestEmail.value.trim());
    }
    if (guestPhone.value.trim().length > 0) {
        formData.set('guest_phone', guestPhone.value.trim());
    }
    formData.set('guest_intent', selectedIntent.value);
    Object.entries(guestFieldValues.value)
        .filter(([, value]) => value.trim().length > 0)
        .forEach(([key, value]) => {
            formData.set(`guest_fields[${key}]`, value.trim());
        });
    if (guestAvatarFile.value !== null) {
        formData.set('avatar_file', guestAvatarFile.value);
    }
    if (removeGuestAvatar.value) {
        formData.set('remove_avatar', '1');
    }

    try {
        const response = await fetch(props.guestProfileUpsertUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: formData,
        });

        const payload = (await response.json().catch(() => null)) as
            | {
                  guest: GuestProfilePayload | null;
                  likedAssetIds: number[];
                  message?: string;
                  errors?: Record<string, string[]>;
              }
            | null;

        if (!response.ok || payload === null) {
            guestProfileError.value =
                payload?.errors
                    ? Object.values(payload.errors)
                          .flat()
                          .find((message) => typeof message === 'string') ?? null
                    : null;
            if (guestProfileError.value === null) {
                guestProfileError.value =
                    t('public.album.errors.guest_profile_save');
            }
            guestProfileProcessing.value = false;
            return false;
        }

        applyGuestProfilePayload(payload);
        guestAvatarFile.value = null;
        removeGuestAvatar.value = false;
        revokeGuestAvatarPreview();
        if (guestAvatarInputRef.value) {
            guestAvatarInputRef.value.value = '';
        }
        guestProfileProcessing.value = false;
        return true;
    } catch {
        guestProfileError.value = t('public.album.errors.guest_profile_save');
        guestProfileProcessing.value = false;
        return false;
    }
};

const persistGuestProfile = (): void => {
    if (typeof window === 'undefined' || !onboardingDone.value) {
        return;
    }

    window.localStorage.setItem(
        guestStorageKey.value,
        JSON.stringify({
            fields: guestFieldValues.value,
            intent: activeView.value,
            guestToken: guestToken.value,
        }),
    );
};

const hydrateGuestProfile = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const raw = window.localStorage.getItem(guestStorageKey.value);
    if (!raw) {
        return;
    }

    try {
        const parsed = JSON.parse(raw) as {
            fields?: unknown;
            intent?: unknown;
            guestToken?: unknown;
        };
        if (
            parsed.fields === null ||
            typeof parsed.fields !== 'object' ||
            Array.isArray(parsed.fields)
        ) {
            return;
        }
        const fields = parsed.fields as Record<string, unknown>;
        const nextValues: Record<string, string> = {};
        for (const [key, value] of Object.entries(fields)) {
            nextValues[key] = typeof value === 'string' ? value : '';
        }
        if ((nextValues.name ?? '').trim().length < 2) {
            return;
        }
        guestFieldValues.value = {
            ...guestFieldValues.value,
            ...nextValues,
        };
        const parsedIntent =
            typeof parsed.intent === 'string'
                ? (parsed.intent as GuestIntent)
                : defaultIntent();
        guestToken.value =
            typeof parsed.guestToken === 'string' && parsed.guestToken.length > 0
                ? parsed.guestToken
                : createGuestToken();
        activeView.value = isIntentEnabled(parsedIntent)
            ? parsedIntent
            : defaultIntent();
        selectedIntent.value = activeView.value;
        onboardingDone.value = true;
        onboardingStep.value = 2;
    } catch {
        window.localStorage.removeItem(guestStorageKey.value);
    }
};

onMounted(() => {
    hydrateGuestProfile();
    hydrateGalleryView();
    latestKnownAssetId.value = latestAssetIdFromItems(props.assets);
    assetNextCursor.value = props.assetsNextCursor;
    hasMoreAssets.value = props.assetsHasMore;
    if (guestToken.value === '') {
        guestToken.value = createGuestToken();
    }
    if (onboardingDone.value) {
        void syncGuestProfile();
    }
    syncHeroGlassCardObserver();
    updateMorphingHeader();
    syncBodyScrollLock();
    window.addEventListener('scroll', scheduleMorphingHeaderUpdate, {
        passive: true,
    });
    window.addEventListener('resize', scheduleMorphingHeaderUpdate, {
        passive: true,
    });
    document.addEventListener('touchend', preventDoubleTapZoom, {
        passive: false,
    });
    syncLoadMoreObserver();
    albumUpdatePollId = window.setInterval(() => {
        void checkForAlbumUpdates();
    }, 12000);
    syncProcessingVideoPoll();
});

onUnmounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    heroGlassCardResizeObserver?.disconnect();
    if (morphingHeaderFrameId !== null) {
        window.cancelAnimationFrame(morphingHeaderFrameId);
    }
    if (albumUpdatePollId !== null) {
        window.clearInterval(albumUpdatePollId);
        albumUpdatePollId = null;
    }
    if (processingVideoPollId !== null) {
        window.clearInterval(processingVideoPollId);
        processingVideoPollId = null;
    }
    if (composerOpenTimeoutId !== null) {
        window.clearTimeout(composerOpenTimeoutId);
        composerOpenTimeoutId = null;
    }
    loadMoreObserver?.disconnect();
    loadMoreObserver = null;
    window.removeEventListener('scroll', scheduleMorphingHeaderUpdate);
    window.removeEventListener('resize', scheduleMorphingHeaderUpdate);
    document.removeEventListener('touchend', preventDoubleTapZoom);
    revokeUploadPreviews();
    revokeGuestAvatarPreview();
    unlockBodyScroll();
});

watch(
    () => props.textPostThemes,
    (themes) => {
        if (textForm.text_post_theme_id === null && themes.length > 0) {
            textForm.text_post_theme_id = themes[0].id;
        }
    },
    { immediate: true },
);

watch(heroSectionRef, () => {
    syncHeroGlassCardObserver();
    scheduleMorphingHeaderUpdate();
});

watch(loadMoreSentinelRef, () => {
    syncLoadMoreObserver();
});

watch(hasProcessingVideoAssets, () => {
    syncProcessingVideoPoll();
});

watch(onboardingDone, async () => {
    await nextTick();
    syncHeroGlassCardObserver();
    scheduleMorphingHeaderUpdate();
});

watch(
    () => textForm.text,
    (value) => {
        syncTextComposerElement(value);
    },
);

watch(
    () => props.assets,
    (nextAssets) => {
        assetItems.value = [...nextAssets];
        loadedPhotoAssetIds.value = {};
        latestKnownAssetId.value = latestAssetIdFromItems(nextAssets);
        hasPendingAlbumUpdate.value = false;
        pendingAlbumUpdateCount.value = 0;
        assetNextCursor.value = props.assetsNextCursor;
        hasMoreAssets.value = props.assetsHasMore;
        if (activeStackKey.value === null) {
            return;
        }

        const nextStack = galleryStacks.value.find(
            (stack) => stack.key === activeStackKey.value,
        );
        if (!nextStack) {
            closeAssetViewer();
            return;
        }

        activeStackSlideIndex.value = Math.min(
            activeStackSlideIndex.value,
            Math.max(0, nextStack.assets.length - 1),
        );
    },
);

watch(isComposerOpen, (open) => {
    if (open) {
        return;
    }

    activeView.value = defaultIntent();
    selectedIntent.value = activeView.value;
    revokeUploadPreviews();
    uploadForm.reset();
    uploadForm.clearErrors();
    textForm.reset('text');
    textForm.clearErrors();
    clientValidationErrors.value = [];
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
    persistGuestProfile();
});

watch(galleryView, () => {
    persistGalleryView();
});

watch(menuOpen, (open) => {
    if (open) {
        return;
    }

    const nextIntent = pendingComposerIntent.value;
    if (nextIntent === null || nextIntent === 'browse_gallery') {
        return;
    }

    if (typeof window === 'undefined') {
        pendingComposerIntent.value = null;
        isComposerOpen.value = true;
        return;
    }

    if (composerOpenTimeoutId !== null) {
        window.clearTimeout(composerOpenTimeoutId);
        composerOpenTimeoutId = null;
    }

    composerOpenTimeoutId = window.setTimeout(() => {
        composerOpenTimeoutId = null;
        pendingComposerIntent.value = null;
        void nextTick(() => {
            isComposerOpen.value = true;
        });
    }, 260);
});

const isValidEmail = (value: string): boolean =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

const validateGuestStepOne = (): boolean => {
    const errors: Record<string, string> = {};

    for (const field of welcomeGuestFields.value) {
        const value = (guestFieldValues.value[field.id] ?? '').trim();
        if (field.required && value.length === 0) {
            errors[field.id] = `Please enter ${field.label.toLowerCase()}.`;
            continue;
        }
        if (field.id === 'name' && value.length < 2) {
            errors[field.id] = t('public.album.errors.name_required');
            continue;
        }
        if (field.type === 'email' && value.length > 0 && !isValidEmail(value)) {
            errors[field.id] = t('public.album.errors.valid_email');
        }
    }

    onboardingErrors.value = errors;

    return Object.keys(errors).length === 0;
};

const goToIntentStep = (): void => {
    if (!validateGuestStepOne()) {
        return;
    }

    onboardingStep.value = 2;
};

const completeOnboarding = async (): Promise<void> => {
    if (!validateGuestStepOne()) {
        onboardingStep.value = 1;
        return;
    }

    const nextIntent = isIntentEnabled(selectedIntent.value)
        ? selectedIntent.value
        : defaultIntent();

    const saved = await upsertGuestProfile();
    if (!saved) {
        onboardingStep.value = 1;
        return;
    }

    activeView.value = nextIntent;
    selectedIntent.value = nextIntent;
    onboardingDone.value = true;
    menuOpen.value = false;
    persistGuestProfile();

    if (nextIntent !== 'browse_gallery') {
        openComposerForView(nextIntent);
    }
};

const resetGuestOnboarding = (): void => {
    onboardingDone.value = false;
    onboardingStep.value = 1;
    menuOpen.value = false;
    uploadForm.reset();
    textForm.clearErrors();
    clientValidationErrors.value = [];
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }

    if (typeof window !== 'undefined') {
        window.localStorage.removeItem(guestStorageKey.value);
    }

    guestFieldValues.value = {
        name: '',
        email: '',
        phone: '',
    };
    guestAvatarUrl.value = null;
    guestProfileError.value = null;
    clearGuestAvatarSelection();
    onboardingErrors.value = {};
};

const openComposerForView = (view: GuestIntent): void => {
    if (!isIntentEnabled(view)) {
        return;
    }

    isLanguagePickerOpen.value = false;
    activeView.value = view;
    selectedIntent.value = view;

    if (view === 'browse_gallery') {
        if (composerOpenTimeoutId !== null && typeof window !== 'undefined') {
            window.clearTimeout(composerOpenTimeoutId);
            composerOpenTimeoutId = null;
        }
        pendingComposerIntent.value = null;
        menuOpen.value = false;
        isComposerOpen.value = false;
        persistGuestProfile();
        return;
    }

    if (menuOpen.value) {
        pendingComposerIntent.value = view;
        menuOpen.value = false;
        return;
    }

    if (typeof window === 'undefined') {
        pendingComposerIntent.value = null;
        isComposerOpen.value = true;
        return;
    }

    if (composerOpenTimeoutId !== null) {
        window.clearTimeout(composerOpenTimeoutId);
        composerOpenTimeoutId = null;
    }

    const open = () => {
        composerOpenTimeoutId = null;
        pendingComposerIntent.value = null;
        void nextTick(() => {
            isComposerOpen.value = true;
        });
    };

    open();
};

const setActiveView = (view: GuestIntent): void => {
    openComposerForView(view);
};

const openGuestSettings = (): void => {
    isLanguagePickerOpen.value = false;
    menuOpen.value = true;
};

const triggerQuickUpload = (): void => {
    selectedIntent.value = 'upload_media';

    if (!onboardingDone.value) {
        return;
    }

    openComposerForView('upload_media');

    if (typeof window !== 'undefined') {
        window.setTimeout(() => {
            openUploadFilePicker();
        }, 180);
    }
};

const saveGuestSettings = async (): Promise<void> => {
    if (!validateGuestStepOne()) {
        return;
    }

    const saved = await upsertGuestProfile();
    if (!saved) {
        return;
    }

    onboardingDone.value = true;
    persistGuestProfile();
    menuOpen.value = false;
};

const setMenuActiveView = (view: GuestIntent): void => {
    if (!isIntentEnabled(view)) {
        return;
    }

    activeView.value = view;
    selectedIntent.value = view;

    if (composerOpenTimeoutId !== null && typeof window !== 'undefined') {
        window.clearTimeout(composerOpenTimeoutId);
        composerOpenTimeoutId = null;
    }

    pendingComposerIntent.value = null;
    menuOpen.value = false;

    if (view === 'browse_gallery') {
        isComposerOpen.value = false;
        persistGuestProfile();
        return;
    }

    if (typeof window === 'undefined') {
        isComposerOpen.value = true;
        return;
    }

    composerOpenTimeoutId = window.setTimeout(() => {
        composerOpenTimeoutId = null;
        isComposerOpen.value = true;
    }, 320);
};

const closeComposer = (): void => {
    if (composerOpenTimeoutId !== null && typeof window !== 'undefined') {
        window.clearTimeout(composerOpenTimeoutId);
        composerOpenTimeoutId = null;
    }
    pendingComposerIntent.value = null;
    isComposerOpen.value = false;
};

const lockBodyScroll = (): void => {
    if (typeof document === 'undefined' || typeof window === 'undefined') {
        return;
    }

    if (document.body.dataset.albumScrollLocked === 'true') {
        return;
    }

    lockedScrollY = window.scrollY;
    document.body.dataset.albumScrollLocked = 'true';
    document.body.style.position = 'fixed';
    document.body.style.top = `-${lockedScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
    document.documentElement.style.overscrollBehavior = 'none';
};

const unlockBodyScroll = (): void => {
    if (typeof document === 'undefined' || typeof window === 'undefined') {
        return;
    }

    const shouldRestoreScroll =
        document.body.dataset.albumScrollLocked === 'true';

    document.body.dataset.albumScrollLocked = 'false';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
    document.documentElement.style.overscrollBehavior = '';

    if (shouldRestoreScroll) {
        window.scrollTo(0, lockedScrollY);
    }
};

const syncBodyScrollLock = (): void => {
    if (typeof document === 'undefined') {
        return;
    }

    const shouldLock =
        activeStackKey.value !== null ||
        isComposerOpen.value ||
        isLanguagePickerOpen.value ||
        menuOpen.value ||
        isPreEventInfoOpen.value ||
        isAssetCommentsOpen.value ||
        isAssetInfoOpen.value;

    if (shouldLock) {
        lockBodyScroll();
        return;
    }

    unlockBodyScroll();
};

watch(
    [
        isComposerOpen,
        isLanguagePickerOpen,
        menuOpen,
        isPreEventInfoOpen,
        isAssetCommentsOpen,
        isAssetInfoOpen,
        activeStackKey,
    ],
    () => {
        syncBodyScrollLock();
    },
);

const openAssetViewer = (stackKey: string, slideIndex = 0): void => {
    activeStackKey.value = stackKey;
    activeStackSlideIndex.value = slideIndex;
};

const openAssetInfo = (stackKey: string, slideIndex = 0): void => {
    activeInfoStackKey.value = stackKey;
    activeInfoSlideIndex.value = slideIndex;
    isAssetInfoOpen.value = true;
};

const closeAssetViewer = (): void => {
    activeStackKey.value = null;
    activeStackSlideIndex.value = 0;
    viewerTouchStartX.value = null;
    viewerTouchStartY.value = null;
    viewerTouchCurrentX.value = null;
    viewerTouchCurrentY.value = null;
};

const closeAssetInfo = (): void => {
    isAssetInfoOpen.value = false;
    activeInfoStackKey.value = null;
    activeInfoSlideIndex.value = 0;
};

const isAssetCommentsLoading = (assetId: number | null): boolean =>
    assetId !== null && commentLoadingAssetIds.value[assetId] === true;

const isAssetCommentPending = (assetId: number | null): boolean =>
    assetId !== null && commentPendingAssetIds.value[assetId] === true;

const isCommentLikePending = (commentId: number): boolean =>
    commentLikePendingIds.value[commentId] === true;

const loadAssetComments = async (asset: AssetItem): Promise<void> => {
    if (isAssetCommentsLoading(asset.id)) {
        return;
    }

    commentLoadingAssetIds.value = {
        ...commentLoadingAssetIds.value,
        [asset.id]: true,
    };

    try {
        const commentsUrl =
            guestToken.value.trim().length > 0
                ? `${asset.commentsUrl}?guest_token=${encodeURIComponent(guestToken.value)}`
                : asset.commentsUrl;
        const response = await fetch(commentsUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (!response.ok) {
            throw new Error(t('public.album.errors.comments_load'));
        }

        const payload = (await response.json()) as {
            comments: AssetComment[];
            commentCount: number;
        };
        commentItemsByAssetId.value = {
            ...commentItemsByAssetId.value,
            [asset.id]: payload.comments,
        };
        updateAssetItem(asset.id, {
            commentCount: payload.commentCount,
        });
    } catch {
        commentError.value = t('public.album.errors.comments_load');
    } finally {
        commentLoadingAssetIds.value = omitAssetId(
            commentLoadingAssetIds.value,
            asset.id,
        );
    }
};

const openAssetComments = async (stackKey: string): Promise<void> => {
    activeCommentsStackKey.value = stackKey;
    isAssetCommentsOpen.value = true;
    commentError.value = null;

    const stack =
        galleryStacks.value.find((galleryStack) => galleryStack.key === stackKey) ??
        null;
    const asset = stack?.preview ?? null;
    if (asset === null) {
        return;
    }

    await loadAssetComments(asset);
};

const closeAssetComments = (): void => {
    isAssetCommentsOpen.value = false;
    activeCommentsStackKey.value = null;
    commentDraft.value = '';
    commentError.value = null;
};

const appendCommentEmoji = (emoji: string): void => {
    commentDraft.value = `${commentDraft.value}${emoji}`;
};

const isAssetLiked = (asset: AssetItem | null): boolean =>
    asset !== null && likedAssetIds.value[asset.id] === true;

const isAssetLikePending = (asset: AssetItem | null): boolean =>
    asset !== null && likePendingAssetIds.value[asset.id] === true;

const isAssetLikeAnimating = (asset: AssetItem | null): boolean =>
    asset !== null && likeAnimatingAssetIds.value[asset.id] === true;

const omitAssetId = <T extends Record<number, boolean>>(
    collection: T,
    assetId: number,
): T =>
    Object.fromEntries(
        Object.entries(collection).filter(([key]) => Number(key) !== assetId),
    ) as T;

const triggerLikeAnimation = (assetId: number): void => {
    likeAnimatingAssetIds.value = {
        ...likeAnimatingAssetIds.value,
        [assetId]: true,
    };

    window.setTimeout(() => {
        likeAnimatingAssetIds.value = omitAssetId(
            likeAnimatingAssetIds.value,
            assetId,
        );
    }, 360);
};

const canDeleteAsset = (asset: AssetItem | null): boolean => {
    if (asset === null) {
        return false;
    }

    return (
        guestToken.value.trim().length > 0 &&
        asset.guestName !== null &&
        asset.guestName.trim().toLowerCase() === guestName.value.trim().toLowerCase()
    );
};

const canDownloadAsset = (asset: AssetItem | null): boolean =>
    asset !== null && props.canGuestDownload;

const currentFeedAssetCanDelete = (stack: GalleryStack): boolean =>
    canDeleteAsset(stack.preview);

const currentFeedAssetCanDownload = (stack: GalleryStack): boolean =>
    canDownloadAsset(stack.preview);

const showNextInStack = (): void => {
    if (selectedStackAssets.value.length <= 1) {
        return;
    }

    activeStackSlideIndex.value =
        (activeStackSlideIndex.value + 1) % selectedStackAssets.value.length;
};

const showPreviousInStack = (): void => {
    if (selectedStackAssets.value.length <= 1) {
        return;
    }

    activeStackSlideIndex.value =
        (activeStackSlideIndex.value - 1 + selectedStackAssets.value.length) %
        selectedStackAssets.value.length;
};

const onViewerTouchStart = (event: TouchEvent): void => {
    const touch = event.changedTouches[0];

    viewerTouchStartX.value = touch?.clientX ?? null;
    viewerTouchStartY.value = touch?.clientY ?? null;
    viewerTouchCurrentX.value = touch?.clientX ?? null;
    viewerTouchCurrentY.value = touch?.clientY ?? null;
};

const onViewerTouchMove = (event: TouchEvent): void => {
    const touch = event.changedTouches[0];

    viewerTouchCurrentX.value = touch?.clientX ?? null;
    viewerTouchCurrentY.value = touch?.clientY ?? null;
};

const resetViewerTouchGesture = (): void => {
    viewerTouchStartX.value = null;
    viewerTouchStartY.value = null;
    viewerTouchCurrentX.value = null;
    viewerTouchCurrentY.value = null;
};

const onViewerTouchEnd = (event: TouchEvent): void => {
    const startX = viewerTouchStartX.value;
    const startY = viewerTouchStartY.value;
    const endX =
        viewerTouchCurrentX.value ?? event.changedTouches[0]?.clientX ?? null;
    const endY =
        viewerTouchCurrentY.value ?? event.changedTouches[0]?.clientY ?? null;

    resetViewerTouchGesture();

    if (startX === null || endX === null || !hasMultipleInSelectedStack.value) {
        return;
    }

    if (startY === null || endY === null) {
        return;
    }

    const deltaX = endX - startX;
    const deltaY = endY - startY;

    if (Math.abs(deltaX) < 40 || Math.abs(deltaX) <= Math.abs(deltaY)) {
        return;
    }

    if (deltaX < 0) {
        showNextInStack();
        return;
    }

    showPreviousInStack();
};

const onViewerTouchCancel = (): void => {
    resetViewerTouchGesture();
};

const deleteAsset = (asset: AssetItem): void => {
    if (!canDeleteAsset(asset) || deleteAssetForm.processing) {
        return;
    }

    deleteAssetForm.guest_name = guestName.value.trim();
    deleteAssetForm.guest_token = guestToken.value;
    deleteAssetForm.post(asset.deleteUrl, {
        preserveScroll: true,
        onSuccess: () => {
            assetItems.value = assetItems.value.filter(
                (item) => item.id !== asset.id,
            );
            likedAssetIds.value = omitAssetId(likedAssetIds.value, asset.id);
            const remainingInStack = selectedStackAssets.value.length;
            if (remainingInStack <= 0) {
                closeAssetViewer();
            } else {
                activeStackSlideIndex.value = Math.min(
                    activeStackSlideIndex.value,
                    remainingInStack - 1,
                );
                closeAssetInfo();
            }
            deleteAssetForm.reset();
        },
    });
};

const deleteSelectedAsset = (): void => {
    if (selectedAsset.value === null) {
        return;
    }

    deleteAsset(selectedAsset.value);
};

const toggleAssetLike = async (asset: AssetItem): Promise<void> => {
    if (!onboardingDone.value || guestToken.value.trim().length === 0) {
        return;
    }

    if (isAssetLikePending(asset)) {
        return;
    }

    likePendingAssetIds.value = {
        ...likePendingAssetIds.value,
        [asset.id]: true,
    };

    try {
        const formData = new FormData();
        formData.set('guest_token', guestToken.value);
        const response = await fetch(asset.likeToggleUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: formData,
        });
        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as {
            liked: boolean;
            likeCount: number;
        };
        likedAssetIds.value = payload.liked
            ? {
                  ...likedAssetIds.value,
                  [asset.id]: true,
              }
            : Object.fromEntries(
                  Object.entries(likedAssetIds.value).filter(
                      ([key]) => Number(key) !== asset.id,
                  ),
              );
        updateAssetItem(asset.id, {
            likeCount: payload.likeCount,
        });
        triggerLikeAnimation(asset.id);
    } catch {
        toast.error(t('public.album.errors.like_update'));
    } finally {
        likePendingAssetIds.value = omitAssetId(
            likePendingAssetIds.value,
            asset.id,
        );
    }
};

const toggleSelectedAssetLike = async (): Promise<void> => {
    if (selectedAsset.value === null) {
        return;
    }

    await toggleAssetLike(selectedAsset.value);
};

const shareAsset = async (asset: AssetItem): Promise<void> => {
    const shareUrl = props.links.album;
    const shareTitle = `${asset.guestName || 'Guest'} on ${props.eventName}`;
    const shareText =
        asset.captionTitle?.trim() ||
        asset.captionSubtitle?.trim() ||
        `Check out this moment from ${props.eventName}.`;

    if (
        typeof navigator !== 'undefined' &&
        typeof navigator.share === 'function'
    ) {
        try {
            await navigator.share({
                title: shareTitle,
                text: shareText,
                url: shareUrl,
            });
            return;
        } catch {
            // Fall back to clipboard copy below.
        }
    }

    if (
        typeof navigator !== 'undefined' &&
        navigator.clipboard &&
        typeof navigator.clipboard.writeText === 'function'
    ) {
        await navigator.clipboard.writeText(shareUrl);
        toast.success(t('public.album.copy_link_success'));
        return;
    }

    toast.error(t('public.album.errors.share_unavailable'));
};

const shareSelectedAsset = async (): Promise<void> => {
    if (selectedAsset.value === null) {
        return;
    }

    await shareAsset(selectedAsset.value);
};

const submitAssetComment = async (): Promise<void> => {
    const asset = selectedCommentsAsset.value;
    if (asset === null) {
        return;
    }
    if (!onboardingDone.value || guestToken.value.trim().length === 0) {
        commentError.value = t('public.album.errors.comment_onboarding');
        return;
    }

    const body = commentDraft.value.trim();
    if (body.length === 0) {
        commentError.value = t('public.album.errors.comment_empty');
        return;
    }
    if (isAssetCommentPending(asset.id)) {
        return;
    }

    commentPendingAssetIds.value = {
        ...commentPendingAssetIds.value,
        [asset.id]: true,
    };
    commentError.value = null;

    try {
        const formData = new FormData();
        formData.set('guest_token', guestToken.value);
        formData.set('body', body);

        const response = await fetch(asset.commentStoreUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: formData,
        });
        const payload = (await response.json().catch(() => null)) as
            | {
                  comment?: AssetComment;
                  commentCount?: number;
                  errors?: Record<string, string[]>;
              }
            | null;

        if (!response.ok || payload?.comment === undefined) {
            commentError.value =
                payload?.errors
                    ? Object.values(payload.errors)
                          .flat()
                          .find((message) => typeof message === 'string') ?? null
                    : t('public.album.errors.comment_publish');
            return;
        }

        const nextComments = [...(commentItemsByAssetId.value[asset.id] ?? []), payload.comment];
        commentItemsByAssetId.value = {
            ...commentItemsByAssetId.value,
            [asset.id]: nextComments,
        };
        updateAssetItem(asset.id, {
            commentCount: payload.commentCount ?? nextComments.length,
        });
        commentDraft.value = '';
        toast.success(t('public.album.comments.posted'));
    } catch {
        commentError.value = t('public.album.errors.comment_publish');
    } finally {
        commentPendingAssetIds.value = omitAssetId(
            commentPendingAssetIds.value,
            asset.id,
        );
    }
};

const toggleCommentLike = async (comment: AssetComment): Promise<void> => {
    if (!onboardingDone.value || guestToken.value.trim().length === 0) {
        commentError.value = t('public.album.errors.comment_like_onboarding');
        return;
    }
    if (isCommentLikePending(comment.id)) {
        return;
    }

    commentLikePendingIds.value = {
        ...commentLikePendingIds.value,
        [comment.id]: true,
    };

    try {
        const formData = new FormData();
        formData.set('guest_token', guestToken.value);
        const response = await fetch(comment.likeToggleUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: formData,
        });
        if (!response.ok || selectedCommentsAsset.value === null) {
            throw new Error(t('public.album.errors.comment_like_update'));
        }

        const payload = (await response.json()) as {
            liked: boolean;
            likeCount: number;
        };
        const assetId = selectedCommentsAsset.value.id;
        commentItemsByAssetId.value = {
            ...commentItemsByAssetId.value,
            [assetId]: (commentItemsByAssetId.value[assetId] ?? []).map(
                (item) =>
                    item.id === comment.id
                        ? {
                              ...item,
                              liked: payload.liked,
                              likeCount: payload.likeCount,
                          }
                        : item,
            ),
        };
    } catch {
        commentError.value = t('public.album.errors.comment_like_update');
    } finally {
        commentLikePendingIds.value = omitAssetId(
            commentLikePendingIds.value,
            comment.id,
        );
    }
};

const readVideoDurationSeconds = (file: File): Promise<number> => {
    return new Promise((resolve, reject) => {
        const video = document.createElement('video');
        const objectUrl = URL.createObjectURL(file);

        video.preload = 'metadata';
        video.onloadedmetadata = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(video.duration);
        };
        video.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Unable to read video duration.'));
        };

        video.src = objectUrl;
    });
};

const validateSelectedFiles = async (
    selectedFiles: File[],
    mode: UploadMode,
): Promise<File[]> => {
    const accepted: File[] = [];
    const errors: string[] = [];

    isValidatingVideos.value = true;

    for (const file of selectedFiles) {
        if (file.type.startsWith('image/')) {
            if (mode === 'video_only') {
                errors.push(`${file.name}: only video files are allowed here.`);
                continue;
            }
            if (!canUploadPhotos.value) {
                errors.push(`${file.name}: photo uploads are disabled.`);
                continue;
            }

            if (file.size > props.limits.photoMaxSizeBytes) {
                errors.push(
                    `${file.name}: photo exceeds ${formatBytes(props.limits.photoMaxSizeBytes)}.`,
                );
                continue;
            }

            accepted.push(file);
            continue;
        }

        if (file.type.startsWith('video/')) {
            if (!canUploadVideos.value) {
                errors.push(`${file.name}: video uploads are disabled.`);
                continue;
            }

            if (file.size > props.limits.videoMaxSizeBytes) {
                errors.push(
                    `${file.name}: video exceeds ${formatBytes(props.limits.videoMaxSizeBytes)}.`,
                );
                continue;
            }

            try {
                const duration = await readVideoDurationSeconds(file);
                if (
                    duration < props.limits.videoMinDurationSeconds ||
                    duration > props.limits.videoMaxDurationSeconds
                ) {
                    errors.push(
                        `${file.name}: video must be ${props.limits.videoMinDurationSeconds}-${props.limits.videoMaxDurationSeconds} seconds.`,
                    );
                    continue;
                }
            } catch {
                errors.push(`${file.name}: unable to validate video duration.`);
                continue;
            }

            accepted.push(file);
            continue;
        }

        errors.push(`${file.name}: unsupported file type.`);
    }

    isValidatingVideos.value = false;
    clientValidationErrors.value = errors;

    return accepted;
};

const onFileSelectionChange = async (event: Event): Promise<void> => {
    const target = event.target as HTMLInputElement;
    const selectedFiles = Array.from(target.files ?? []);

    if (selectedFiles.length === 0) {
        revokeUploadPreviews();
        uploadForm.files = [];
        clientValidationErrors.value = [];
        return;
    }

    uploadForm.files = await validateSelectedFiles(selectedFiles, uploadMode.value);
    syncUploadPreviews(uploadForm.files);
};

const uploadFiles = (): void => {
    if (
        !onboardingDone.value ||
        !canUpload.value ||
        uploadForm.files.length === 0 ||
        uploadForm.processing
    ) {
        return;
    }

    const normalizedGuestName = guestName.value.trim();
    if (normalizedGuestName.length < 2) {
        resetGuestOnboarding();
        return;
    }

    uploadForm.guest_name = normalizedGuestName;
    uploadForm.guest_email =
        guestEmail.value.trim().length > 0 ? guestEmail.value.trim() : null;
    uploadForm.guest_phone =
        guestPhone.value.trim().length > 0 ? guestPhone.value.trim() : null;
    uploadForm.message =
        uploadForm.message?.trim().length
            ? uploadForm.message.trim()
            : null;
    uploadForm.guest_token = guestToken.value;
    uploadForm.guest_fields = Object.fromEntries(
        Object.entries(guestFieldValues.value)
            .filter(([, value]) => value.trim().length > 0)
            .map(([key, value]) => [key, value.trim()]),
    );
    uploadForm.guest_intent = activeView.value;

    uploadForm.post(props.uploadUrl, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            revokeUploadPreviews();
            uploadForm.reset();
            clientValidationErrors.value = [];
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
            isComposerOpen.value = false;
            refreshAlbum('manual');
        },
    });
};

const submitTextPost = (): void => {
    if (!onboardingDone.value || !canUploadText.value || textForm.processing) {
        return;
    }

    if (textForm.text.trim().length === 0) {
        textForm.setError('text', t('public.album.errors.text_empty'));
        return;
    }
    if (selectedTextPostTheme.value === null) {
        textForm.setError('text', t('public.album.errors.text_background_required'));
        return;
    }

    const normalizedGuestName = guestName.value.trim();
    if (normalizedGuestName.length < 2) {
        resetGuestOnboarding();
        return;
    }

    textForm.guest_name = normalizedGuestName;
    textForm.guest_email =
        guestEmail.value.trim().length > 0 ? guestEmail.value.trim() : null;
    textForm.guest_phone =
        guestPhone.value.trim().length > 0 ? guestPhone.value.trim() : null;
    textForm.guest_token = guestToken.value;
    textForm.text_post_theme_id = selectedTextPostTheme.value.id;
    textForm.guest_fields = Object.fromEntries(
        Object.entries(guestFieldValues.value)
            .filter(([, value]) => value.trim().length > 0)
            .map(([key, value]) => [key, value.trim()]),
    );
    textForm.guest_intent = activeView.value;

    textForm.post(props.textPostUrl, {
        preserveScroll: true,
        onSuccess: () => {
            textForm.reset('text');
            textForm.clearErrors();
            openComposerForView('browse_gallery');
            persistGuestProfile();
            refreshAlbum('manual');
        },
    });
};

const resetPullRefreshState = (): void => {
    pullRefreshStartY.value = null;
    pullRefreshDistance.value = 0;
};

const canStartPullRefresh = (): boolean =>
    typeof window !== 'undefined' &&
    onboardingDone.value &&
    window.scrollY <= 0 &&
    activeStackKey.value === null &&
    !isLanguagePickerOpen.value &&
    !menuOpen.value &&
    !isComposerOpen.value &&
    !isAssetCommentsOpen.value &&
    !isAssetInfoOpen.value &&
    !isPreEventInfoOpen.value &&
    !isAlbumRefreshing.value;

const refreshAlbum = (reason: 'banner' | 'pull' | 'manual' = 'manual'): void => {
    if (isAlbumRefreshing.value) {
        return;
    }

    isAlbumRefreshing.value = true;
    hasPendingAlbumUpdate.value = false;
    pendingAlbumUpdateCount.value = 0;

    router.visit(window.location.href, {
        method: 'get',
        only: ['assets', 'assetsNextCursor', 'assetsHasMore', 'limits'],
        preserveState: true,
        preserveScroll: reason !== 'pull',
        onError: () => {
            toast.error(t('public.album.errors.refresh'));
        },
        onFinish: () => {
            isAlbumRefreshing.value = false;
            resetPullRefreshState();
        },
    });
};

const loadMoreAssets = async (): Promise<void> => {
    if (
        isLoadingMoreAssets.value ||
        isAlbumRefreshing.value ||
        !hasMoreAssets.value ||
        assetNextCursor.value === null
    ) {
        return;
    }

    isLoadingMoreAssets.value = true;

    try {
        const response = await fetch(
            `${props.assetFeedUrl}?before_cursor=${encodeURIComponent(String(assetNextCursor.value))}`,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            },
        );

        if (!response.ok) {
            throw new Error(t('public.album.errors.load_more'));
        }

        const payload = (await response.json()) as {
            assets: AssetItem[];
            nextCursor: number | null;
            hasMore: boolean;
        };

        assetItems.value = mergeAssetItems(assetItems.value, payload.assets);
        assetNextCursor.value = payload.nextCursor;
        hasMoreAssets.value = payload.hasMore;
    } catch {
        toast.error(t('public.album.errors.load_more'));
    } finally {
        isLoadingMoreAssets.value = false;
    }
};

const syncLoadMoreObserver = (): void => {
    loadMoreObserver?.disconnect();
    loadMoreObserver = null;

    if (
        typeof window === 'undefined' ||
        typeof IntersectionObserver === 'undefined' ||
        loadMoreSentinelRef.value === null
    ) {
        return;
    }

    loadMoreObserver = new IntersectionObserver(
        (entries) => {
            const visibleEntry = entries.find((entry) => entry.isIntersecting);
            if (!visibleEntry) {
                return;
            }

            void loadMoreAssets();
        },
        {
            rootMargin: '640px 0px 640px 0px',
        },
    );

    loadMoreObserver.observe(loadMoreSentinelRef.value);
};

const checkForAlbumUpdates = async (): Promise<void> => {
    if (
        typeof window === 'undefined' ||
        typeof document === 'undefined' ||
        document.visibilityState !== 'visible' ||
        isAlbumRefreshing.value ||
        !props.canViewGallery
    ) {
        return;
    }

    try {
        const response = await fetch(props.assetFeedUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as {
            assets?: AssetItem[];
        };
        const nextAssets = Array.isArray(payload.assets) ? payload.assets : [];
        const nextLatestAssetId = latestAssetIdFromItems(nextAssets);

        if (nextLatestAssetId <= latestKnownAssetId.value) {
            return;
        }

        const nextNewCount = nextAssets.filter(
            (asset) => asset.id > latestKnownAssetId.value,
        ).length;

        hasPendingAlbumUpdate.value = true;
        pendingAlbumUpdateCount.value = Math.max(1, nextNewCount);
    } catch {
        // Best-effort polling only.
    }
};

const refreshProcessingVideos = async (): Promise<void> => {
    if (
        typeof window === 'undefined' ||
        isAlbumRefreshing.value ||
        !props.canViewGallery ||
        !hasProcessingVideoAssets.value
    ) {
        return;
    }

    try {
        const response = await fetch(props.assetFeedUrl, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as {
            assets?: AssetItem[];
        };
        const nextAssets = Array.isArray(payload.assets) ? payload.assets : [];
        if (nextAssets.length === 0) {
            return;
        }

        const nextById = new Map(nextAssets.map((asset) => [asset.id, asset]));
        let changed = false;

        assetItems.value = assetItems.value.map((asset) => {
            if (asset.kind !== 'video' || !asset.videoProcessing) {
                return asset;
            }

            const refreshedAsset = nextById.get(asset.id);
            if (!refreshedAsset) {
                return asset;
            }

            if (
                refreshedAsset.videoProcessing !== asset.videoProcessing ||
                refreshedAsset.thumbnailUrl !== asset.thumbnailUrl ||
                refreshedAsset.previewUrl !== asset.previewUrl
            ) {
                changed = true;

                return refreshedAsset;
            }

            return asset;
        });

        if (changed) {
            latestKnownAssetId.value = Math.max(
                latestKnownAssetId.value,
                latestAssetIdFromItems(assetItems.value),
            );
        }
    } catch {
        // Best-effort polling only.
    }
};

const syncProcessingVideoPoll = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    if (processingVideoPollId !== null) {
        window.clearInterval(processingVideoPollId);
        processingVideoPollId = null;
    }

    if (!hasProcessingVideoAssets.value) {
        return;
    }

    processingVideoPollId = window.setInterval(() => {
        void refreshProcessingVideos();
    }, 5000);
};

const onAlbumTouchStart = (event: TouchEvent): void => {
    if (!canStartPullRefresh() || event.touches.length !== 1) {
        return;
    }

    pullRefreshStartY.value = event.touches[0]?.clientY ?? null;
    pullRefreshDistance.value = 0;
};

const onAlbumTouchMove = (event: TouchEvent): void => {
    if (pullRefreshStartY.value === null || typeof window === 'undefined') {
        return;
    }

    if (window.scrollY > 0) {
        resetPullRefreshState();
        return;
    }

    const currentY = event.touches[0]?.clientY ?? pullRefreshStartY.value;
    const deltaY = currentY - pullRefreshStartY.value;

    if (deltaY <= 0) {
        pullRefreshDistance.value = 0;
        return;
    }

    pullRefreshDistance.value = Math.min(96, deltaY * 0.45);
    event.preventDefault();
};

const onAlbumTouchEnd = (): void => {
    if (pullRefreshStartY.value === null) {
        return;
    }

    if (pullRefreshDistance.value >= pullRefreshThreshold) {
        pullRefreshDistance.value = 56;
        pullRefreshStartY.value = null;
        refreshAlbum('pull');
        return;
    }

    resetPullRefreshState();
};

const onAlbumTouchCancel = (): void => {
    resetPullRefreshState();
};
</script>

<template>
    <Head :title="t('public.album.page_title', { eventName })" />

    <main
        class="min-h-screen text-slate-900"
        :style="albumBodyStyle"
        :class="
            onboardingDone
                ? 'bg-white'
                : !onboardingDone && customWelcomeEnabled && welcomeScreen.backgroundUrl
                  ? 'bg-slate-950'
                  : 'bg-gradient-to-b from-rose-50 via-white to-amber-50'
        "
        @touchstart="onAlbumTouchStart"
        @touchmove="onAlbumTouchMove"
        @touchend="onAlbumTouchEnd"
        @touchcancel="onAlbumTouchCancel"
    >
        <div
            v-if="onboardingDone && pullRefreshVisible"
            class="pointer-events-none fixed left-1/2 top-0 z-50 safe-top"
            :style="pullRefreshIndicatorStyle"
        >
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/92 px-4 py-2 text-xs font-medium text-slate-700 shadow-lg backdrop-blur">
                <UploadCloud class="size-4" :class="isAlbumRefreshing ? 'animate-bounce' : ''" />
                <span>
                    {{
                        isAlbumRefreshing
                                    ? t('public.album.processing.refreshing_album')
                                    : pullRefreshReady
                                      ? t('public.album.processing.release_to_refresh')
                                      : t('public.album.processing.pull_to_refresh')
                    }}
                </span>
            </div>
        </div>
        <div
            class="relative w-full"
            :class="
                onboardingDone
                    ? 'pb-32'
                    : !onboardingDone && customWelcomeEnabled
                    ? ''
                    : 'mx-auto max-w-md px-4 pb-24 pt-5 sm:max-w-lg'
            "
            :style="albumContentStyle"
        >
            <div
                v-if="!onboardingDone && !customWelcomeEnabled"
                class="pointer-events-none absolute -left-24 -top-20 h-52 w-52 rounded-full bg-rose-200/70 blur-3xl"
            />
            <div
                v-if="!onboardingDone && !customWelcomeEnabled"
                class="pointer-events-none absolute -right-24 top-52 h-56 w-56 rounded-full bg-amber-200/70 blur-3xl"
            />

            <section
                v-if="!onboardingDone"
                class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 text-white"
            >
                <img
                    v-if="customWelcomeEnabled && welcomeScreen.backgroundUrl"
                    :src="welcomeScreen.backgroundUrl"
                    :alt="t('public.shared.alt.welcome_background')"
                    class="absolute inset-0 h-full w-full object-cover"
                    :class="
                        customWelcomeEnabled && welcomeScreen.animated
                            ? 'welcome-bg-animate-slow'
                            : ''
                    "
                />
                <div
                    v-else-if="customWelcomeEnabled"
                    class="absolute inset-0"
                    :style="albumGradientStyle"
                    :class="
                        customWelcomeEnabled && welcomeScreen.animated
                            ? 'welcome-bg-animate-slow'
                            : ''
                    "
                />
                <div
                    v-if="customWelcomeEnabled"
                    class="absolute inset-0 bg-black/45"
                />
                <div
                    v-else
                    class="absolute inset-0"
                    :style="albumGradientStyle"
                />
                <div
                    class="relative w-full max-w-sm rounded-[1.75rem] border border-white/25 bg-white/10 p-4 shadow-[0_24px_80px_rgba(0,0,0,0.28)] backdrop-blur-xl sm:p-5"
                >
                    <header class="mb-5 text-center">
                        <div class="mb-3 flex justify-center">
                            <div
                                class="size-14 overflow-hidden rounded-full border border-white/80 bg-white/20 shadow-md"
                            >
                                <img
                                    v-if="albumLogoUrl"
                                    :src="albumLogoUrl"
                                    :alt="t('public.shared.alt.event_logo')"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-base font-semibold text-white"
                                >
                                    {{ eventName.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                        </div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/80">
                            {{ t('public.album.kicker') }}
                        </p>
                        <h1
                            class="mt-2 text-[1.3rem] leading-[1.1] font-semibold text-white sm:text-[1.45rem]"
                            :class="welcomeFontClass"
                        >
                            {{ welcomeTitle }}
                        </h1>
                        <p class="mt-2 text-sm text-white/90">
                            {{ welcomeSubtitle }}
                        </p>
                    </header>

                    <div
                        v-if="onboardingStep === 1"
                        class="space-y-3"
                    >
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-white">
                                {{ t('public.album.avatar_label') }}
                                <span class="text-white/65">({{ t('public.shared.optional') }})</span>
                            </p>
                            <div class="flex items-center gap-3 rounded-2xl border border-white/20 bg-white/8 px-3 py-3">
                                <Avatar class="size-16 border border-white/25">
                                    <AvatarImage
                                        v-if="currentGuestAvatarUrl"
                                        :src="currentGuestAvatarUrl"
                                        :alt="t('public.shared.alt.guest_avatar')"
                                    />
                                    <AvatarFallback
                                        :class="avatarFallbackClass(guestName)"
                                    >
                                        {{ guestInitials(guestName) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-white">
                                        {{ t('public.album.avatar_title') }}
                                    </p>
                                    <p class="mt-1 text-xs text-white/75">
                                        {{ t('public.album.avatar_hint') }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center justify-center rounded-2xl bg-white px-3 text-sm font-medium text-slate-900 transition hover:bg-slate-100"
                                            @click="guestAvatarInputRef?.click()"
                                        >
                                            <Camera class="mr-2 size-4" />
                                            {{ t('public.album.choose_avatar') }}
                                        </button>
                                        <button
                                            v-if="currentGuestAvatarUrl"
                                            type="button"
                                            class="inline-flex h-10 items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-3 text-sm font-medium text-white transition hover:bg-white/15"
                                            @click="clearGuestAvatarSelection"
                                        >
                                            {{ t('public.shared.remove') }}
                                        </button>
                                    </div>
                                    <input
                                        ref="guestAvatarInputRef"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="onGuestAvatarSelectionChange"
                                    />
                                </div>
                            </div>
                        </div>

                        <div
                            v-for="field in welcomeGuestFields"
                            :key="`guest-field-${field.id}`"
                            class="space-y-2"
                        >
                            <label
                                :for="`guest-${field.id}`"
                                class="text-sm font-medium text-white"
                            >
                                {{ field.label }}
                                <span
                                    v-if="field.required"
                                    class="text-rose-300"
                                >
                                    *
                                </span>
                            </label>
                            <input
                                :id="`guest-${field.id}`"
                                v-model="guestFieldValues[field.id]"
                                :type="
                                    field.type === 'phone'
                                        ? 'tel'
                                        : field.type === 'number'
                                          ? 'number'
                                          : field.type
                                "
                                :maxlength="field.type === 'email' ? 255 : 80"
                                :autocomplete="
                                    field.id === 'name'
                                        ? 'name'
                                        : field.type === 'email'
                                          ? 'email'
                                          : field.type === 'phone'
                                            ? 'tel'
                                            : 'off'
                                "
                                :placeholder="
                                    field.help_text.length > 0
                                        ? field.help_text
                                        : t('public.album.welcome.write_your_field', {
                                              field: field.label.toLowerCase(),
                                          })
                                "
                                class="h-12 w-full rounded-2xl border border-white/20 bg-white/95 px-4 text-base text-slate-900 outline-none transition focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
                            />
                            <p
                                v-if="onboardingErrors[field.id]"
                                class="text-xs text-rose-200"
                            >
                                {{ onboardingErrors[field.id] }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-100 px-4 text-sm font-medium text-slate-900 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50"
                            data-test="guest-onboarding-next"
                            @click="goToIntentStep"
                        >
                            {{ welcomeButtonText }}
                        </button>
                        <p
                            v-if="guestProfileError"
                            class="text-xs text-rose-200"
                        >
                            {{ guestProfileError }}
                        </p>
                    </div>

                    <div
                        v-else
                        class="space-y-3"
                    >
                        <p class="text-sm font-medium text-white">
                            {{ t('public.album.welcome.next_step', { name: guestName.trim() }) }}
                        </p>
                        <button
                            v-for="option in intentOptions"
                            :key="option.value"
                            type="button"
                            class="w-full rounded-2xl border p-4 text-left transition"
                            :data-test="`guest-intent-${option.value}`"
                            :class="
                                selectedIntent === option.value
                                    ? 'border-rose-300 ring-4 ring-rose-100 bg-white'
                                    : 'border-white/30 bg-white/10 hover:border-white/50'
                            "
                            :disabled="!option.enabled"
                            @click="selectedIntent = option.value"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="mt-0.5 rounded-xl p-2"
                                    :class="
                                        selectedIntent === option.value
                                            ? 'bg-rose-100 text-rose-700'
                                            : 'bg-white/20 text-white'
                                    "
                                    :style="selectedIntent !== option.value ? albumTintStyle : undefined"
                                >
                                    <component :is="option.icon" class="size-4" />
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-semibold"
                                        :class="
                                            selectedIntent === option.value
                                                ? 'text-slate-900'
                                                : 'text-white'
                                        "
                                    >
                                        {{ option.label }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs"
                                        :class="
                                            selectedIntent === option.value
                                                ? 'text-slate-600'
                                                : 'text-white/80'
                                        "
                                    >
                                        {{ option.description }}
                                    </p>
                                    <p
                                        v-if="!option.enabled"
                                        class="mt-1 text-xs font-medium"
                                        :class="
                                            selectedIntent === option.value
                                                ? 'text-amber-700'
                                                : 'text-amber-200'
                                        "
                                    >
                                        {{ t('public.album.intent.disabled') }}
                                    </p>
                                </div>
                            </div>
                        </button>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button
                                type="button"
                                class="inline-flex h-11 items-center justify-center rounded-2xl border border-white/25 bg-white/10 px-4 text-sm font-medium text-white transition hover:bg-white/15"
                                @click="onboardingStep = 1"
                            >
                                {{ t('public.shared.back') }}
                            </button>
                            <button
                                type="button"
                                class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-100 px-4 text-sm font-medium text-slate-900 transition hover:bg-white"
                                :disabled="guestProfileProcessing"
                                data-test="guest-onboarding-complete"
                                @click="completeOnboarding"
                            >
                                {{
                                    guestProfileProcessing
                                        ? t('public.shared.saving')
                                        : welcomeButtonText
                                }}
                            </button>
                        </div>
                        <p
                            v-if="guestProfileError"
                            class="text-xs text-rose-200"
                        >
                            {{ guestProfileError }}
                        </p>
                    </div>
                </div>
            </section>

            <section
                v-else
                class="relative pb-6"
            >
                <header
                    v-if="useMorphingHeader"
                    class="pointer-events-none fixed inset-0 z-40"
                >
                    <div
                        v-if="morphingHeaderVisible"
                        class="fixed overflow-hidden border border-white/20 bg-white/12 text-white shadow-[0_24px_80px_rgba(0,0,0,0.24)] backdrop-blur-xl"
                        :style="morphingHeaderStyle"
                    >
                        <div class="relative h-full p-4">
                            <div
                                class="flex justify-between gap-3"
                                :class="menuOpen ? 'items-center' : 'h-full items-center'"
                            >
                                <div
                                    class="flex min-w-0 items-center gap-3"
                                    :style="morphingHeaderIdentityStyle"
                                >
                                    <Avatar class="size-16 border border-white/30">
                                        <AvatarImage
                                            v-if="albumLogoUrl"
                                            :src="albumLogoUrl"
                                            :alt="t('public.shared.alt.event_logo')"
                                        />
                                        <AvatarFallback class="bg-white/20 text-lg font-semibold text-white">
                                            {{ albumAvatarFallback }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <p
                                            class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/75"
                                            :style="morphingHeaderTextShadowStyle"
                                        >
                                            {{ t('public.album.kicker') }}
                                        </p>
                                        <h1
                                            class="mt-1 truncate text-[1.2rem] leading-[1.1] font-semibold text-white sm:text-[1.35rem]"
                                            :class="welcomeFontClass"
                                            :style="morphingHeaderTextShadowStyle"
                                        >
                                            {{ welcomeTitle }}
                                        </h1>
                                    </div>
                                </div>

                                <div class="pointer-events-auto ml-auto flex shrink-0 items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex size-14 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition hover:bg-white/20"
                                        :style="morphingHeaderIconShadowStyle"
                                        :aria-label="menuOpen ? t('public.shared.close_menu') : t('public.shared.open_menu')"
                                        @click="menuOpen = !menuOpen"
                                    >
                                        <Menu class="size-5" />
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="menuOpen"
                                class="mt-5 flex h-[calc(100%-4.5rem)] flex-col"
                            >
                                <div class="rounded-[1.5rem] border border-black/12 bg-black/42 p-3 shadow-[0_12px_40px_rgba(0,0,0,0.18)] backdrop-blur-md">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/72">
                                        {{ t('public.album.menu.guest_menu') }}
                                    </p>
                                    <p class="mt-2 text-sm text-white/82">
                                        {{ t('public.album.menu.signed_in_as') }}
                                        <span class="font-semibold text-white">{{ guestName.trim() }}</span>
                                    </p>
                                </div>

                                <div class="mt-3 grid flex-1 content-start gap-2 overflow-y-auto pr-1">
                                    <button
                                        v-for="option in intentOptions"
                                        :key="`morph-menu-${option.value}`"
                                        type="button"
                                        class="flex w-full items-center gap-3 rounded-[1.35rem] border px-4 py-3 text-left text-[15px] font-medium transition"
                                        :class="
                                            (option.value === 'browse_gallery' &&
                                                activeView === 'browse_gallery' &&
                                                !isComposerOpen) ||
                                            (option.value !== 'browse_gallery' &&
                                                isComposerOpen &&
                                                activeView === option.value)
                                                ? 'border-white/30 bg-white text-slate-900'
                                                : 'border-black/12 bg-black/38 text-white hover:bg-black/46'
                                        "
                                        :disabled="!option.enabled"
                                        @click="setActiveView(option.value)"
                                    >
                                        <component :is="option.icon" class="size-4 shrink-0" />
                                        <span>{{ option.label }}</span>
                                    </button>
                                    <a
                                        :href="links.wall"
                                        class="flex w-full items-center gap-3 rounded-[1.35rem] border border-black/12 bg-black/38 px-4 py-3 text-left text-[15px] font-semibold text-white transition hover:bg-black/46"
                                    >
                                        <Images class="size-4 shrink-0" />
                                        <span>{{ t('public.album.menu.open_photo_wall') }}</span>
                                    </a>
                                    <button
                                        v-if="isPreEventTestMode"
                                        type="button"
                                        class="flex w-full items-center gap-3 rounded-[1.35rem] border border-amber-200/35 bg-amber-500/26 px-4 py-3 text-left text-[15px] font-medium text-white transition hover:bg-amber-500/32"
                                        @click="isPreEventInfoOpen = true"
                                    >
                                        <AlertTriangle class="size-4 shrink-0 text-amber-100" />
                                        <span>{{ t('public.album.menu.pre_event_test_mode') }}</span>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    class="mt-3 inline-flex h-12 items-center justify-center rounded-[1.35rem] border border-black/16 bg-black/55 px-4 text-[15px] font-semibold text-white shadow-[0_10px_24px_rgba(0,0,0,0.18)] transition hover:bg-black/62"
                                    @click="resetGuestOnboarding"
                                >
                                    {{ t('public.album.menu.reset_guest_onboarding') }}
                                </button>
                            </div>

                            <div
                                v-else
                                class="mt-3 space-y-3"
                                :style="morphingHeaderLowerStyle"
                            >
                                <p class="text-sm leading-relaxed text-white/85">
                                    {{ welcomeSubtitle }}
                                </p>

                                <div
                                    v-if="eventDate || uploadWindowStartsAt || uploadWindowEndsAt"
                                    class="flex flex-col gap-2 text-xs text-white/82"
                                >
                                    <p
                                        v-if="eventDate"
                                        class="flex items-center gap-2"
                                    >
                                        <CalendarDays class="size-3.5 text-white/65" />
                                        {{ t('public.album.meta.event_date') }}
                                        <span class="font-medium text-white">{{ formatDate(eventDate) }}</span>
                                    </p>
                                    <p
                                        v-if="uploadWindowStartsAt || uploadWindowEndsAt"
                                        class="flex items-center gap-2"
                                    >
                                        <Clock3 class="size-3.5 text-white/65" />
                                        {{ t('public.album.meta.upload_window') }}
                                        <span class="font-medium text-white">{{ formatDateTime(uploadWindowStartsAt) }} - {{ formatDateTime(uploadWindowEndsAt) }}</span>
                                    </p>
                                </div>

                                <div
                                    v-if="showQrCode"
                                    class="rounded-[1.5rem] border border-white/15 bg-black/20 p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="albumQrDataUrl"
                                            :alt="t('public.shared.alt.album_qr_code')"
                                            class="size-16 rounded-lg border border-white/20 bg-white p-1"
                                        />
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/80">
                                                {{ t('public.album.menu.share_album') }}
                                            </p>
                                            <p class="mt-1 text-xs text-white/80">
                                                {{ t('public.album.menu.share_album_hint') }}
                                            </p>
                                            <a
                                                :href="links.wall"
                                                class="mt-2 inline-flex text-xs font-medium text-white/95 underline underline-offset-4"
                                            >
                                                {{ t('public.album.menu.open_photo_wall') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!menuOpen"
                                class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/55 via-black/15 to-transparent"
                                :style="morphingHeaderMaskStyle"
                            />
                        </div>
                    </div>
                </header>

                <header
                    v-if="!useMorphingHeader"
                    class="pointer-events-none fixed inset-x-0 top-0 z-40 border-b border-slate-200/80 bg-white/92 transition-all duration-300 backdrop-blur safe-top safe-x supports-[backdrop-filter]:bg-white/80"
                    :class="isHeaderCollapsed ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'"
                >
                    <div class="mx-auto flex w-full max-w-2xl items-center gap-3 py-3">
                        <div class="pointer-events-auto min-w-0 flex items-center gap-3">
                            <Avatar class="size-11 border border-slate-200">
                                <AvatarImage
                                    v-if="albumLogoUrl"
                                    :src="albumLogoUrl"
                                    :alt="t('public.shared.alt.event_logo')"
                                />
                                <AvatarFallback class="bg-slate-900 text-white">
                                    {{ albumAvatarFallback }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-base font-semibold text-slate-900"
                                    :class="welcomeFontClass"
                                >
                                    {{ welcomeTitle }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ welcomeSubtitle }}
                                </p>
                            </div>
                        </div>
                    </div>
                </header>

                <section
                    ref="heroSectionRef"
                    class="relative min-h-[52svh] overflow-hidden"
                >
                    <img
                        v-if="onboardingDone && hasAlbumImageBackground && props.appearance.albumBackgroundImageUrl"
                        :src="props.appearance.albumBackgroundImageUrl"
                        :alt="t('public.shared.alt.album_background')"
                        class="absolute inset-0 h-full w-full object-cover"
                    />
                    <img
                        v-else-if="welcomeScreen.backgroundUrl"
                        :src="welcomeScreen.backgroundUrl"
                        :alt="t('public.shared.alt.album_background')"
                        class="absolute inset-0 h-full w-full object-cover"
                        :class="welcomeScreen.animated ? 'welcome-bg-animate-slow' : ''"
                    />
                    <div
                        v-else
                        class="absolute inset-0"
                        :style="
                            onboardingDone
                                ? heroBackgroundStyle
                                : albumGradientStyle
                        "
                    />
                    <div
                        class="absolute inset-0"
                        :class="
                            hasAlbumImageBackground
                                ? 'bg-gradient-to-b from-black/30 via-black/45 to-black/75'
                                : 'bg-gradient-to-b from-black/20 via-black/35 to-black/65'
                        "
                    />

                    <div class="relative mx-auto flex min-h-[52svh] w-full max-w-2xl items-end px-4 pb-6 pt-20">
                        <div
                            ref="heroGlassCardRef"
                            class="w-full text-white transition-opacity duration-200"
                            :style="heroGlassCardStyle"
                        >
                            <div class="flex items-start gap-4">
                                <Avatar class="size-18 border border-white/25 shadow-[0_16px_30px_rgba(15,23,42,0.24)]">
                                    <AvatarImage
                                        v-if="albumLogoUrl"
                                        :src="albumLogoUrl"
                                        :alt="t('public.shared.alt.event_logo')"
                                    />
                                    <AvatarFallback class="bg-white/18 text-lg font-semibold text-white">
                                        {{ albumAvatarFallback }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1 pt-1">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/75">
                                        {{ t('public.album.kicker') }}
                                    </p>
                                    <h1
                                        class="mt-1 text-[1.32rem] leading-[1.08] font-semibold text-white sm:text-[1.48rem]"
                                        :class="welcomeFontClass"
                                    >
                                        {{ welcomeTitle }}
                                    </h1>
                                </div>
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-white/88">
                                {{ welcomeSubtitle }}
                            </p>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/72">
                                {{ t('public.album.profile_description') }}
                            </p>

                            <div class="mx-auto mt-5 grid max-w-xl grid-cols-4 gap-3 border-t border-white/14 pt-4 text-center">
                                <div
                                    v-for="stat in albumHeaderStats"
                                    :key="stat.label"
                                    class="min-w-0 text-center"
                                >
                                    <p class="truncate text-lg font-semibold text-white">
                                        {{ stat.value }}
                                    </p>
                                    <p class="mt-1 text-[11px] uppercase tracking-[0.14em] text-white/70">
                                        {{ stat.label }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mx-auto w-full max-w-2xl px-0 pt-3">
                    <div
                        v-if="props.canViewGallery"
                        class="mb-3 flex items-center justify-between gap-3 px-2"
                    >
                        <h2 class="text-sm font-semibold text-slate-900">
                            {{ t('public.album.gallery.title') }}
                        </h2>
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center rounded-full border border-slate-200 bg-white p-1 shadow-sm">
                                <button
                                    type="button"
                                    class="inline-flex size-9 items-center justify-center rounded-full transition"
                                    :class="
                                        galleryView === 'grid3'
                                            ? 'bg-slate-900 text-white'
                                            : 'text-slate-600 hover:bg-slate-100'
                                    "
                                    :aria-label="t('public.album.actions.show_grid_three')"
                                    title="3-column gallery"
                                    @click="galleryView = 'grid3'"
                                >
                                    <Columns3 class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex size-9 items-center justify-center rounded-full transition"
                                    :class="
                                        galleryView === 'grid2'
                                            ? 'bg-slate-900 text-white'
                                            : 'text-slate-600 hover:bg-slate-100'
                                    "
                                    :aria-label="t('public.album.actions.show_grid_two')"
                                    title="2-column gallery"
                                    @click="galleryView = 'grid2'"
                                >
                                    <Columns2 class="size-4" />
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex size-9 items-center justify-center rounded-full transition"
                                    :class="
                                        galleryView === 'feed'
                                            ? 'bg-slate-900 text-white'
                                            : 'text-slate-600 hover:bg-slate-100'
                                    "
                                    :aria-label="t('public.album.actions.show_feed')"
                                    title="Feed view"
                                    @click="galleryView = 'feed'"
                                >
                                    <Rows3 class="size-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="hasPendingAlbumUpdate"
                        class="px-2 pb-3"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 rounded-[1.35rem] border border-sky-200 bg-sky-50 px-4 py-3 text-left text-sky-900 transition hover:bg-sky-100"
                            :disabled="isAlbumRefreshing"
                            @click="refreshAlbum('banner')"
                        >
                            <div class="flex items-center gap-3">
                                <div class="inline-flex size-10 items-center justify-center rounded-full bg-sky-500 text-white">
                                    <UploadCloud class="size-4" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">
                                        {{ t('public.album.gallery.new_posts_available') }}
                                    </p>
                                    <p class="text-xs text-sky-800/80">
                                        {{
                                            pendingAlbumUpdateCount > 0
                                                ? t('public.album.gallery.new_posts_waiting', {
                                                      count: pendingAlbumUpdateCount,
                                                      label:
                                                          pendingAlbumUpdateCount === 1
                                                              ? t('public.album.gallery.new_post_singular')
                                                              : t('public.album.gallery.new_post_plural'),
                                                  })
                                                : t('public.album.gallery.tap_to_load_latest')
                                        }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold uppercase tracking-[0.14em]">
                                {{ t('public.shared.refresh') }}
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="!props.canViewGallery"
                        class="px-2 py-6"
                    >
                        <Empty class="rounded-[1.75rem] border bg-white py-14">
                            <EmptyHeader>
                                <EmptyMedia variant="icon">
                                    <EyeOff class="size-5" />
                                </EmptyMedia>
                                <EmptyTitle>{{ t('public.album.gallery.viewing_disabled_title') }}</EmptyTitle>
                                <EmptyDescription>
                                    {{
                                        canUpload
                                            ? t('public.album.gallery.viewing_disabled_can_upload')
                                            : t('public.album.gallery.viewing_disabled_cannot_upload')
                                    }}
                                </EmptyDescription>
                            </EmptyHeader>
                        </Empty>
                    </div>

                    <div
                        v-else-if="assetItems.length === 0"
                        class="px-2 py-6"
                    >
                        <Empty class="rounded-[1.75rem] border bg-white py-14">
                            <EmptyHeader>
                                <EmptyMedia variant="icon">
                                    <Images class="size-5" />
                                </EmptyMedia>
                                <EmptyTitle>{{ t('public.album.gallery.empty_title') }}</EmptyTitle>
                                <EmptyDescription>
                                    {{ t('public.album.gallery.empty_description') }}
                                </EmptyDescription>
                            </EmptyHeader>
                        </Empty>
                    </div>

                    <div
                        v-else-if="galleryView === 'feed'"
                        class="space-y-0"
                    >
                        <article
                            v-for="stack in galleryStacks"
                            :key="`feed-${stack.key}`"
                            class="overflow-hidden bg-white"
                        >
                            <div class="flex items-center justify-between gap-3 px-3 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <Avatar class="size-11 border border-slate-200">
                                        <AvatarImage
                                            v-if="stack.preview.guestAvatarUrl"
                                            :src="stack.preview.guestAvatarUrl ?? ''"
                                            :alt="stack.guestName || t('public.shared.alt.guest_avatar')"
                                        />
                                        <AvatarFallback
                                            :class="avatarFallbackClass(stack.guestName)"
                                        >
                                            {{ guestInitials(stack.guestName) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 items-baseline gap-2">
                                            <p class="truncate text-sm font-semibold text-slate-900">
                                                {{ stack.guestName }}
                                            </p>
                                            <span
                                                v-if="stackUploadSummary(stack)"
                                                class="shrink-0 text-xs text-slate-500"
                                            >
                                                {{ stackUploadSummary(stack) }}
                                            </span>
                                        </div>
                                        <p class="truncate text-xs text-slate-500">
                                            {{ formatDateTime(stack.preview.createdAt) }}
                                        </p>
                                    </div>
                                </div>

                                <DropdownMenu>
                                    <DropdownMenuTrigger :as-child="true">
                        <button
                            type="button"
                            class="inline-flex size-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50"
                            :aria-label="t('public.album.actions.open_upload_options')"
                        >
                                            <MoreHorizontal class="size-4" />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48">
                                        <DropdownMenuItem
                                            @select.prevent="openAssetViewer(stack.key)"
                                        >
                                            {{ t('public.album.actions.open_viewer') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @select.prevent="openAssetInfo(stack.key)"
                                        >
                                            {{ t('public.album.actions.open_info') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            @select.prevent="shareAsset(stack.preview)"
                                        >
                                            {{ t('public.album.asset.share') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="currentFeedAssetCanDownload(stack)"
                                            :as-child="true"
                                        >
                                            <a :href="stack.preview.downloadUrl">
                                                {{ t('public.shared.download') }}
                                            </a>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            v-if="currentFeedAssetCanDelete(stack)"
                                            variant="destructive"
                                            @select.prevent="deleteAsset(stack.preview)"
                                        >
                                            {{ t('public.album.actions.delete_upload') }}
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div class="relative overflow-hidden bg-slate-100">
                                <button
                                    type="button"
                                    class="block w-full border-0 bg-transparent text-left"
                                    @click="openAssetViewer(stack.key)"
                                >
                                    <template
                                        v-if="
                                            stack.preview.kind === 'photo' &&
                                            (stack.preview.thumbnailUrl ||
                                                stack.preview.previewUrl)
                                        "
                                    >
                                        <img
                                            :src="
                                                stack.preview.thumbnailUrl ??
                                                stack.preview.previewUrl ??
                                                undefined
                                            "
                                            :alt="t('public.shared.alt.uploaded_event_photo')"
                                            loading="lazy"
                                            decoding="async"
                                            class="block aspect-[4/5] w-full object-cover transition-opacity duration-300"
                                            :class="
                                                isPhotoLoaded(stack.preview.id)
                                                    ? 'opacity-100'
                                                    : 'opacity-0'
                                            "
                                            @load="markPhotoAsLoaded(stack.preview.id)"
                                            @error="markPhotoAsLoaded(stack.preview.id)"
                                        />
                                        <div
                                            v-if="!isPhotoLoaded(stack.preview.id)"
                                            class="pointer-events-none absolute inset-0 animate-pulse bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100"
                                        />
                                    </template>
                                    <img
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.thumbnailUrl
                                        "
                                        :src="stack.preview.thumbnailUrl"
                                        :alt="t('public.shared.alt.uploaded_event_video')"
                                        loading="lazy"
                                        decoding="async"
                                        class="block aspect-[4/5] w-full object-cover transition-opacity duration-300"
                                        :class="
                                            isPhotoLoaded(stack.preview.id)
                                                ? 'opacity-100'
                                                : 'opacity-0'
                                        "
                                        @load="markPhotoAsLoaded(stack.preview.id)"
                                        @error="markPhotoAsLoaded(stack.preview.id)"
                                    />
                                    <video
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.previewUrl
                                        "
                                        :src="stack.preview.previewUrl ?? undefined"
                                        class="block aspect-[4/5] w-full object-cover"
                                        autoplay
                                        loop
                                        muted
                                        playsinline
                                        preload="auto"
                                    />
                                    <div
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.videoProcessing
                                        "
                                        class="flex aspect-[4/5] w-full flex-col items-center justify-center gap-3 bg-slate-100 text-slate-500"
                                    >
                                        <LoaderCircle class="size-8 animate-spin text-slate-400" />
                                        <div class="space-y-1 text-center">
                                            <p class="text-sm font-semibold text-slate-700">
                                                {{ t('public.album.labels.processing_video') }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Preview will appear in a moment.
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        v-else-if="stack.preview.kind === 'text'"
                                        class="flex aspect-[4/5] w-full items-center justify-center p-6"
                                        :style="textPostSurfaceStyle(stack.preview)"
                                    >
                                        <p
                                            class="max-w-md whitespace-pre-wrap text-center text-[1.45rem] font-semibold leading-[1.45] sm:text-[1.7rem]"
                                            :style="textPostContentStyle(stack.preview)"
                                        >
                                            {{ stack.preview.text ?? t('public.album.labels.text_post') }}
                                        </p>
                                    </div>
                                    <div
                                        v-else
                                        class="flex aspect-[4/5] w-full items-center justify-center text-xs text-slate-500"
                                    >
                                        {{ t('public.album.labels.preview_unavailable') }}
                                    </div>
                                </button>

                                <div
                                    class="pointer-events-none absolute right-3 top-3 text-white"
                                >
                                    <component
                                        :is="stackMediaBadgeIcon(stack)"
                                        class="size-6 drop-shadow-[0_10px_18px_rgba(0,0,0,0.45)]"
                                    />
                                </div>

                                <div
                                    v-if="
                                        showPreviewWatermark &&
                                        (stack.preview.kind === 'photo' ||
                                            stack.preview.kind === 'video')
                                    "
                                    class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/15"
                                >
                                    <span class="rounded-full border border-white/40 bg-black/45 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-white">
                                            {{ t('public.album.labels.preview_only') }}
                                    </span>
                                </div>

                            </div>

                            <div class="px-3 pb-3 pt-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="inline-flex items-center gap-2 text-slate-700">
                                            <button
                                                type="button"
                                                class="inline-flex size-12 items-center justify-center rounded-full transition hover:text-slate-950"
                                                :class="
                                                    isAssetLiked(stack.preview)
                                                        ? 'text-rose-600'
                                                        : 'text-slate-700'
                                                "
                                                :disabled="isAssetLikePending(stack.preview)"
                                                :aria-pressed="isAssetLiked(stack.preview)"
                                                @click="toggleAssetLike(stack.preview)"
                                            >
                                                <Heart
                                                    class="size-6 transition-transform duration-200"
                                                    :class="
                                                        [
                                                            isAssetLiked(stack.preview)
                                                                ? 'fill-rose-500 text-rose-500'
                                                                : 'text-slate-700',
                                                            isAssetLikeAnimating(stack.preview)
                                                                ? 'scale-125'
                                                                : '',
                                                            isAssetLikePending(stack.preview)
                                                                ? 'opacity-60'
                                                                : '',
                                                        ]
                                                    "
                                                />
                                            </button>
                                            <span
                                                v-if="
                                                    !isAssetLikePending(stack.preview) &&
                                                    stack.preview.likeCount > 0
                                                "
                                                class="text-sm font-semibold leading-none text-slate-700"
                                            >
                                                {{ formatLikeCount(stack.preview.likeCount) }}
                                            </span>
                                        </div>
                                        <button
                                            type="button"
                                            class="inline-flex size-12 items-center justify-center rounded-full text-slate-700 transition hover:text-slate-950"
                                            :aria-label="t('public.album.actions.share_post')"
                                            @click="shareAsset(stack.preview)"
                                        >
                                            <Send class="size-6" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex min-w-0 items-center justify-center gap-1 rounded-full px-1 text-slate-700 transition hover:text-slate-950"
                                            :aria-label="t('public.album.actions.open_comments')"
                                            @click="openAssetComments(stack.key)"
                                        >
                                            <MessageCircle class="size-6" />
                                            <span
                                                v-if="stack.preview.commentCount > 0"
                                                class="text-sm font-semibold leading-none"
                                            >
                                                {{ formatLikeCount(stack.preview.commentCount) }}
                                            </span>
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        class="inline-flex size-12 items-center justify-center rounded-full text-slate-700 transition hover:text-slate-950"
                                        :aria-label="t('public.album.actions.open_info')"
                                        @click="openAssetInfo(stack.key)"
                                    >
                                        <Info class="size-6" />
                                    </button>
                                </div>
                                <div
                                    v-if="stack.preview.kind !== 'text' && stack.preview.message"
                                    class="mt-0.5 space-y-0.5"
                                >
                                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                                        {{ displayedStackMessage(stack) }}
                                    </p>
                                    <button
                                        v-if="hasLongStackMessage(stack)"
                                        type="button"
                                        class="text-sm font-medium text-slate-500 transition hover:text-slate-900"
                                        @click="toggleStackMessageExpansion(stack.key)"
                                    >
                                        {{
                                            isStackMessageExpanded(stack.key)
                                                ? 'Show less'
                                                : 'Show more'
                                        }}
                                    </button>
                                </div>
                                <p
                                    v-if="
                                        showCaptions &&
                                        stack.preview.kind !== 'text' &&
                                        !stack.preview.message &&
                                        assetSummaryText(stack.preview)
                                    "
                                    class="mt-1 text-sm leading-relaxed text-slate-600"
                                >
                                    {{ assetSummaryText(stack.preview) }}
                                </p>
                            </div>

                            <Separator
                                v-if="galleryStacks[galleryStacks.length - 1]?.key !== stack.key"
                                class="bg-slate-200 sm:hidden"
                            />
                        </article>

                        <div
                            v-if="!hasMoreAssets"
                            class="px-3 py-8 sm:px-0"
                        >
                            <Empty class="border-0 bg-transparent py-0 shadow-none">
                                <EmptyHeader>
                                    <EmptyMedia variant="icon" class="bg-slate-100 text-slate-500">
                                        <Images class="size-5" />
                                    </EmptyMedia>
                                    <EmptyTitle class="text-slate-900">
                                        {{ t('public.album.gallery.end_title') }}
                                    </EmptyTitle>
                                    <EmptyDescription class="text-slate-500">
                                        {{ t('public.album.gallery.end_description') }}
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        </div>
                    </div>

                    <div
                        v-else
                        class="space-y-8"
                    >
                        <div :class="['grid gap-px bg-slate-200', galleryGridClass]">
                            <article
                                v-for="stack in galleryStacks"
                                :key="stack.key"
                                class="overflow-hidden bg-white"
                            >
                                <button
                                    type="button"
                                    class="relative block aspect-square w-full overflow-hidden border-0 bg-slate-100 text-left"
                                    @click="openAssetViewer(stack.key)"
                                >
                                    <template
                                        v-if="
                                            stack.preview.kind === 'photo' &&
                                            (stack.preview.thumbnailUrl ||
                                                stack.preview.previewUrl)
                                        "
                                    >
                                        <img
                                            :src="
                                                stack.preview.thumbnailUrl ??
                                                stack.preview.previewUrl ??
                                                undefined
                                            "
                                            :alt="t('public.shared.alt.uploaded_event_photo')"
                                            loading="lazy"
                                            decoding="async"
                                            fetchpriority="low"
                                            class="block h-full w-full object-cover transition-opacity duration-300"
                                            :class="
                                                isPhotoLoaded(stack.preview.id)
                                                    ? 'opacity-100'
                                                    : 'opacity-0'
                                            "
                                            @load="markPhotoAsLoaded(stack.preview.id)"
                                            @error="markPhotoAsLoaded(stack.preview.id)"
                                        />
                                        <div
                                            v-if="!isPhotoLoaded(stack.preview.id)"
                                            class="pointer-events-none absolute inset-0 animate-pulse bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100"
                                        />
                                    </template>
                                    <img
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.thumbnailUrl
                                        "
                                        :src="stack.preview.thumbnailUrl"
                                        :alt="t('public.shared.alt.uploaded_event_video')"
                                        loading="lazy"
                                        decoding="async"
                                        fetchpriority="low"
                                        class="block h-full w-full object-cover transition-opacity duration-300"
                                        :class="
                                            isPhotoLoaded(stack.preview.id)
                                                ? 'opacity-100'
                                                : 'opacity-0'
                                        "
                                        @load="markPhotoAsLoaded(stack.preview.id)"
                                        @error="markPhotoAsLoaded(stack.preview.id)"
                                    />
                                    <video
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.previewUrl
                                        "
                                        :src="stack.preview.previewUrl"
                                        class="block h-full w-full object-cover"
                                        autoplay
                                        loop
                                        muted
                                        playsinline
                                        preload="auto"
                                    />
                                    <div
                                        v-else-if="
                                            stack.preview.kind === 'video' &&
                                            stack.preview.videoProcessing
                                        "
                                        class="flex h-full w-full flex-col items-center justify-center gap-2 bg-slate-100 px-4 text-center text-slate-500"
                                    >
                                        <LoaderCircle class="size-7 animate-spin text-slate-400" />
                                        <p class="text-xs font-semibold text-slate-700">
                                            {{ t('public.album.labels.processing_video') }}
                                        </p>
                                    </div>
                                    <div
                                        v-else-if="stack.preview.kind === 'text'"
                                        class="flex h-full w-full items-center justify-center p-3"
                                        :style="textPostSurfaceStyle(stack.preview)"
                                    >
                                        <p
                                            class="line-clamp-6 whitespace-pre-wrap text-center text-sm font-semibold leading-relaxed sm:text-base"
                                            :style="textPostContentStyle(stack.preview)"
                                        >
                                            {{ stack.preview.text ?? t('public.album.labels.text_post') }}
                                        </p>
                                    </div>
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-xs text-slate-500"
                                    >
                                        {{ t('public.album.labels.preview_unavailable') }}
                                    </div>

                                    <div
                                        v-if="stack.mediaCount > 1"
                                        class="pointer-events-none absolute right-2 top-2 inline-flex items-center justify-center rounded-full border border-white/35 bg-black/38 p-1.5 text-white shadow-[0_8px_18px_rgba(0,0,0,0.28)] backdrop-blur-sm"
                                    >
                                        <Images class="size-7" />
                                    </div>

                                    <div
                                        v-if="
                                            showPreviewWatermark &&
                                            (stack.preview.kind === 'photo' ||
                                                stack.preview.kind === 'video')
                                        "
                                        class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/20"
                                    >
                                        <span class="rounded-full border border-white/40 bg-black/45 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-white">
                                            {{ t('public.album.labels.preview_only') }}
                                        </span>
                                    </div>
                                </button>
                            </article>
                        </div>

                        <div
                            v-if="!hasMoreAssets"
                            class="px-2 sm:px-0"
                        >
                            <Empty class="border-0 bg-transparent py-0 shadow-none">
                                <EmptyHeader>
                                    <EmptyMedia variant="icon" class="bg-slate-100 text-slate-500">
                                        <Images class="size-5" />
                                    </EmptyMedia>
                                    <EmptyTitle class="text-slate-900">
                                        {{ t('public.album.gallery.end_title') }}
                                    </EmptyTitle>
                                    <EmptyDescription class="text-slate-500">
                                        {{ t('public.album.gallery.end_description') }}
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        </div>
                    </div>

                    <div
                        v-if="assetItems.length > 0 && hasMoreAssets"
                        ref="loadMoreSentinelRef"
                        class="px-2 py-6"
                    >
                        <div class="flex items-center justify-center gap-2 text-sm text-slate-500">
                            <UploadCloud
                                class="size-4"
                                :class="isLoadingMoreAssets ? 'animate-bounce' : ''"
                            />
                            <span>
                                {{
                                    isLoadingMoreAssets
                                        ? t('public.album.gallery.loading_more')
                                        : t('public.album.gallery.scroll_for_more')
                                }}
                            </span>
                        </div>
                    </div>
                </section>
            </section>
        </div>

        <footer
            v-if="onboardingDone && showPoweredBy"
            class="safe-x mt-10 border-t border-slate-200 bg-white/92 backdrop-blur supports-[backdrop-filter]:bg-white/80"
        >
            <Separator class="bg-slate-200" />
            <div class="px-3 py-2 text-center text-xs text-slate-500">
                © {{ new Date().getFullYear() }} {{ appName }}. {{ t('public.wall.footer') }}
            </div>
        </footer>

        <nav
            v-if="showBottomNav"
            class="safe-x safe-bottom fixed inset-x-0 bottom-0 z-40 px-3 pb-3"
            aria-label="Guest album actions"
        >
            <div class="mx-auto flex max-w-2xl items-center justify-between gap-2 rounded-[2rem] border border-slate-200/80 bg-white/96 px-3 py-2 shadow-[0_18px_42px_rgba(15,23,42,0.18)] backdrop-blur-xl">
                <button
                    type="button"
                    class="flex min-w-0 flex-1 items-center justify-center rounded-[1.35rem] px-2 py-3 text-slate-600 transition hover:bg-slate-100"
                    :aria-label="t('public.album.nav.language')"
                    :class="isLanguagePickerOpen ? 'bg-slate-900 text-white' : ''"
                    @click="openLanguagePicker"
                >
                    <IconLanguage class="size-6" />
                </button>
                <button
                    type="button"
                    class="flex min-w-0 flex-1 items-center justify-center rounded-[1.35rem] px-2 py-3 text-slate-600 transition hover:bg-slate-100"
                    :class="isComposerOpen && activeView === 'video_testimonial' ? 'bg-slate-900 text-white' : ''"
                    :aria-label="t('public.album.nav.video')"
                    @click="setActiveView('video_testimonial')"
                >
                    <Film class="size-6" />
                </button>
                <button
                    type="button"
                    class="-mt-4 inline-flex size-16 shrink-0 items-center justify-center rounded-[1.45rem] text-white shadow-[0_12px_24px_rgba(15,23,42,0.18)] transition hover:-translate-y-0.5"
                    :style="heroAccentStyle"
                    :aria-label="t('public.album.nav.camera')"
                    @click="triggerQuickUpload"
                >
                    <Camera class="size-7" />
                </button>
                <button
                    v-if="props.allowTextPosts"
                    type="button"
                    class="flex min-w-0 flex-1 items-center justify-center rounded-[1.35rem] px-2 py-3 text-slate-600 transition hover:bg-slate-100"
                    :class="isComposerOpen && activeView === 'text_wish' ? 'bg-slate-900 text-white' : ''"
                    :aria-label="t('public.album.nav.text')"
                    @click="setActiveView('text_wish')"
                >
                    <MessageSquareText class="size-6" />
                </button>
                <button
                    type="button"
                    class="flex min-w-0 flex-1 items-center justify-center rounded-[1.35rem] px-2 py-3 text-slate-600 transition hover:bg-slate-100"
                    :class="menuOpen ? 'bg-slate-900 text-white' : ''"
                    :aria-label="t('public.album.nav.settings')"
                    @click="openGuestSettings"
                >
                    <Menu class="size-6" />
                </button>
            </div>
        </nav>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isLanguagePickerOpen"
                class="fixed inset-0 z-[68] bg-[#fcfaf6]"
            >
                <div class="flex h-full flex-col">
                    <header class="safe-top sticky top-0 z-10 border-b border-slate-200 bg-[#fcfaf6]/96 px-5 pb-5 pt-3 backdrop-blur">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                                    <IconLanguage class="size-6" />
                                    <span>{{ t('public.album.language.title') }}</span>
                                </div>
                                <p class="mt-2 max-w-lg text-sm leading-relaxed text-slate-600">
                                    {{ t('public.album.language.subtitle') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex size-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50"
                                :aria-label="t('public.shared.close')"
                                @click="closeLanguagePicker"
                            >
                                <X class="size-5" />
                            </button>
                        </div>
                    </header>

                    <div class="safe-bottom flex-1 overflow-y-auto px-5 py-5">
                        <div class="mx-auto grid max-w-2xl gap-4 sm:grid-cols-2">
                            <button
                                v-for="option in albumLanguageOptions"
                                :key="`album-language-${option.key}`"
                                type="button"
                                class="flex w-full items-center gap-4 rounded-[1.75rem] border border-slate-200 bg-white px-5 py-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md"
                                :style="
                                    selectedAlbumLanguageKey === option.key
                                        ? {
                                              borderColor: albumPrimaryColor,
                                              boxShadow: `0 18px 40px color-mix(in srgb, ${albumPrimaryColor} 16%, transparent)`,
                                          }
                                        : undefined
                                "
                                @click="selectAlbumLocale(option.key)"
                            >
                                <span class="text-5xl leading-none">{{ option.flag }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-lg font-semibold text-slate-900">
                                            {{ option.label }}
                                        </p>
                                        <CheckCircle2
                                            v-if="selectedAlbumLanguageKey === option.key"
                                            class="size-5 shrink-0"
                                            :style="{ color: albumPrimaryColor }"
                                        />
                                    </div>
                                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                                        {{ option.description }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <Sheet v-model:open="isPreEventInfoOpen">
            <SheetContent
                side="bottom"
                class="safe-bottom max-h-[72vh] rounded-t-[2rem] px-5 pb-8 pt-8"
            >
                <SheetHeader class="px-0 text-left">
                    <SheetTitle class="flex items-center gap-2 text-base text-slate-900">
                        <AlertTriangle class="size-5 text-amber-600" />
                        {{ t('public.album.menu.pre_event_test_mode') }}
                    </SheetTitle>
                    <SheetDescription class="text-sm leading-relaxed text-slate-600">
                        {{ t('public.album.pre_event.description') }}
                    </SheetDescription>
                </SheetHeader>

                <div class="mt-5 space-y-3">
                    <Item
                        variant="muted"
                        class="rounded-[1.5rem] border-slate-200 bg-slate-50"
                    >
                        <ItemMedia
                            variant="icon"
                            :class="statusCard.classes"
                        >
                            <component :is="statusCard.icon" class="size-4" />
                        </ItemMedia>
                        <ItemContent>
                            <ItemTitle class="text-slate-900">
                                {{ statusCard.title }}
                            </ItemTitle>
                            <ItemDescription class="line-clamp-none text-slate-600">
                                {{ statusCard.description }}
                            </ItemDescription>
                        </ItemContent>
                    </Item>

                    <Item
                        variant="outline"
                        class="rounded-[1.5rem] border-slate-200 bg-white"
                    >
                        <ItemContent class="gap-2">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <ItemTitle class="text-slate-900">
                                    {{ t('public.album.pre_event.remaining_uploads') }}
                                </ItemTitle>
                                <span class="font-semibold text-slate-900">
                                    {{ props.preEventTestUploadsRemaining }} / {{ props.preEventTestUploadLimit }}
                                </span>
                            </div>
                            <ItemDescription class="line-clamp-none text-slate-600">
                                {{ t('public.album.pre_event.remaining_uploads_hint') }}
                            </ItemDescription>
                        </ItemContent>
                    </Item>

                    <Item
                        variant="outline"
                        class="rounded-[1.5rem] border-slate-200 bg-white"
                    >
                        <ItemContent class="gap-4">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ t('public.album.storage') }}</span>
                                    <span class="font-medium text-slate-900">
                                        {{ formatBytes(limits.storageUsedBytes) }} / {{ formatBytes(limits.storageLimitBytes) }}
                                    </span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full bg-slate-900 transition-all"
                                        :style="{ width: `${usageStoragePercent}%` }"
                                    />
                                </div>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ t('public.album.uploads') }}</span>
                                    <span class="font-medium text-slate-900">
                                        {{ limits.uploadCount }} / {{ limits.uploadLimit }}
                                    </span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full bg-amber-500 transition-all"
                                        :style="{ width: `${usageUploadsPercent}%` }"
                                    />
                                </div>
                            </div>
                        </ItemContent>
                    </Item>
                </div>
            </SheetContent>
        </Sheet>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isComposerOpen"
                class="fixed inset-0 z-[70] bg-[#fcfaf6]"
            >
                <div
                    v-if="uploadForm.processing"
                    class="absolute inset-0 z-20 flex items-center justify-center bg-[#fcfaf6]/88 backdrop-blur-sm"
                >
                    <div class="flex flex-col items-center gap-4 px-8 text-center">
                        <div class="inline-flex size-16 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm">
                            <LoaderCircle class="size-8 animate-spin text-slate-900" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-base font-semibold text-slate-900">
                                {{ uploadProcessingTitle }}
                            </p>
                            <p class="text-sm text-slate-600">
                                {{ uploadProcessingDescription }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex h-full flex-col">
                    <header class="safe-top sticky top-0 z-10 border-b border-slate-200 bg-[#fcfaf6]/96 px-5 pb-5 pt-3 backdrop-blur">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                                    <component
                                        :is="
                                            activeView === 'video_testimonial'
                                                ? Film
                                                : activeView === 'text_wish'
                                                  ? MessageSquareText
                                                  : Camera
                                        "
                                        class="size-6"
                                    />
                                    <span>
                                        {{
                                            activeView === 'text_wish'
                                                ? t('public.album.text.title')
                                                : uploadTitle
                                        }}
                                    </span>
                                </div>
                                <p class="mt-2 max-w-lg text-sm leading-relaxed text-slate-600">
                                    {{
                                        activeView === 'text_wish'
                                            ? t('public.album.text.description')
                                            : uploadDescription
                                    }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex size-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                                :aria-label="t('public.album.composer.close')"
                                @click="closeComposer"
                            >
                                <X class="size-5" />
                            </button>
                        </div>
                    </header>

                    <div class="flex-1 overflow-y-auto px-5 pb-36 pt-5">
                        <div
                            v-if="activeView === 'upload_media' || activeView === 'video_testimonial'"
                            class="space-y-5"
                        >
                            <p class="text-sm leading-relaxed text-slate-600">
                                {{ t('public.album.labels.limits_summary', {
                                    photoMax: formatBytes(limits.photoMaxSizeBytes),
                                    videoMax: formatBytes(limits.videoMaxSizeBytes),
                                }) }}
                            </p>

                            <div class="-mx-1 flex gap-3 overflow-x-auto px-1 pb-1">
                                <button
                                    v-for="emoji in uploadMessageEmojiOptions"
                                    :key="`upload-message-emoji-${emoji}`"
                                    type="button"
                                    class="inline-flex size-12 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-xl shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
                                    :disabled="!canUpload || uploadForm.processing"
                                    @click="appendUploadMessageEmoji(emoji)"
                                >
                                    {{ emoji }}
                                </button>
                            </div>

                            <div class="space-y-2">
                                <textarea
                                    id="upload-message"
                                    v-model="uploadForm.message"
                                    maxlength="500"
                                    :placeholder="t('public.album.upload.message_placeholder')"
                                    :disabled="!canUpload || uploadForm.processing"
                                    rows="2"
                                    class="min-h-12 w-full resize-none rounded-[1.35rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:bg-white"
                                />
                                <div class="flex items-center justify-end text-xs font-medium text-slate-400">
                                    {{ uploadForm.message?.trim().length ?? 0 }}/500
                                </div>
                                <p
                                    v-if="uploadForm.errors.message"
                                    class="text-sm text-rose-700"
                                >
                                    {{ uploadForm.errors.message }}
                                </p>
                            </div>

                            <input
                                ref="fileInputRef"
                                type="file"
                                multiple
                                :accept="uploadAccept"
                                :disabled="!canUpload || uploadAccept.length === 0 || uploadForm.processing || isValidatingVideos"
                                class="sr-only"
                                data-test="guest-upload-input"
                                @change="onFileSelectionChange"
                            />

                            <button
                                type="button"
                                class="flex min-h-56 w-full flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-300 bg-white px-6 py-8 text-center shadow-sm transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!canUpload || uploadAccept.length === 0 || uploadForm.processing || isValidatingVideos"
                                data-test="guest-upload-picker"
                                @click="openUploadFilePicker"
                            >
                                <div
                                    class="inline-flex size-18 items-center justify-center rounded-full text-white shadow-[0_14px_36px_rgba(15,23,42,0.2)]"
                                    :style="heroAccentStyle"
                                >
                                    <UploadCloud class="size-8" />
                                </div>
                                <p class="mt-5 text-lg font-semibold text-slate-900">
                                    {{
                                        uploadForm.files.length > 0
                                            ? t('public.album.upload.dropzone_selected')
                                            : activeView === 'video_testimonial'
                                              ? t('public.album.labels.choose_your_video')
                                              : t('public.album.labels.choose_photos_or_videos')
                                    }}
                                </p>
                                <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                                    {{
                                        uploadForm.files.length > 0
                                            ? t('public.album.labels.files_selected_count', {
                                                  count: uploadForm.files.length,
                                              })
                                            : activeView === 'video_testimonial'
                                              ? t('public.album.labels.pick_one_short_clip')
                                              : t('public.album.upload.dropzone_hint')
                                    }}
                                </p>
                            </button>

                            <div
                                v-if="uploadForm.files.length > 0"
                                class="rounded-[1.8rem] border border-slate-200 bg-white p-4 shadow-sm"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ t('public.album.labels.selected_files') }} ({{ uploadForm.files.length }})
                                    </p>
                                    <button
                                        v-if="uploadForm.files.length > 0"
                                        type="button"
                                        class="inline-flex h-10 items-center justify-center rounded-full border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                        :disabled="uploadForm.processing || isValidatingVideos"
                                        @click="openUploadFilePicker"
                                    >
                                        {{ t('public.album.labels.change_selected_files') }}
                                    </button>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <div
                                        v-for="preview in uploadPreviewItems.slice(0, 6)"
                                        :key="preview.key"
                                        class="relative size-20 overflow-hidden rounded-[1.35rem] border border-slate-200 bg-slate-50"
                                    >
                                        <img
                                            v-if="preview.kind === 'photo'"
                                            :src="preview.objectUrl"
                                            :alt="preview.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <video
                                            v-else
                                            :src="preview.objectUrl"
                                            class="h-full w-full object-cover"
                                            autoplay
                                            loop
                                            muted
                                            playsinline
                                            preload="auto"
                                        />
                                        <div
                                            v-if="preview.kind === 'video'"
                                            class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/65 to-transparent px-2 py-1"
                                        >
                                            <Film class="size-4 text-white" />
                                        </div>
                                    </div>
                                    <div
                                        v-if="uploadPreviewItems.length > 6"
                                        class="flex size-20 items-center justify-center rounded-[1.35rem] border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-500"
                                    >
                                        +{{ uploadPreviewItems.length - 6 }}
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="clientValidationErrors.length > 0"
                                class="rounded-[1.6rem] border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                            >
                                <p class="font-semibold">{{ t('public.album.files_skipped') }}</p>
                                <ul class="mt-2 list-inside list-disc space-y-1">
                                    <li
                                        v-for="error in clientValidationErrors"
                                        :key="error"
                                    >
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>

                            <p
                                v-if="uploadForm.errors.files"
                                class="text-sm text-rose-700"
                            >
                                {{ uploadForm.errors.files }}
                            </p>

                            <button
                                v-if="uploadForm.processing || uploadForm.files.length > 0"
                                type="button"
                                class="inline-flex h-14 w-full items-center justify-center rounded-[1.5rem] px-6 text-base font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                :style="heroAccentStyle"
                                :disabled="!canUpload || uploadForm.files.length === 0 || uploadForm.processing || isValidatingVideos"
                                data-test="guest-upload-submit"
                                @click="uploadFiles"
                            >
                                <UploadCloud class="mr-2 size-5" />
                                {{ uploadForm.processing ? t('public.album.upload.uploading') : uploadButtonLabel }}
                            </button>
                        </div>

                        <div
                            v-else-if="activeView === 'text_wish' && props.allowTextPosts"
                            class="space-y-5"
                        >
                            <div
                                class="relative mx-auto aspect-square w-full max-w-md overflow-hidden rounded-[2.2rem] border border-slate-200 bg-slate-100 shadow-sm"
                                :style="
                                    selectedTextPostTheme
                                        ? {
                                              ...textPostSurfaceStyle({
                                                  textThemeImageUrl: selectedTextPostTheme.imageUrl,
                                                  textThemeBackgroundColor: selectedTextPostTheme.backgroundColor,
                                              }),
                                          }
                                        : undefined
                                "
                                @pointerdown.prevent="focusTextComposer"
                                @click.prevent="focusTextComposer"
                            >
                                <div
                                    class="absolute inset-0"
                                    :class="
                                        selectedTextPostTheme?.textColor === '#111827'
                                            ? 'bg-white/6'
                                            : 'bg-black/18'
                                    "
                                />
                                <div class="absolute inset-0 flex items-center justify-center px-8 py-10 text-center">
                                    <textarea
                                        ref="textComposerRef"
                                        v-model="textForm.text"
                                        spellcheck="true"
                                        :disabled="!canUploadText || textForm.processing"
                                        :placeholder="t('public.album.text.canvas_hint')"
                                        rows="7"
                                        class="max-h-full min-h-56 w-full resize-none overflow-y-auto border-0 bg-transparent text-center text-[3.6rem] font-semibold leading-tight outline-none placeholder:opacity-82 sm:text-[4.2rem]"
                                        :class="
                                            !canUploadText || textForm.processing
                                                ? 'cursor-not-allowed opacity-70'
                                                : ''
                                        "
                                        :style="
                                            selectedTextPostTheme
                                                ? {
                                                      ...textPostContentStyle({
                                                          textThemeTextColor: selectedTextPostTheme.textColor,
                                                      }),
                                                      textShadow:
                                                          selectedTextPostTheme.textColor === '#111827'
                                                              ? '0 1px 14px rgba(255,255,255,0.55)'
                                                              : '0 1px 14px rgba(15,23,42,0.35)',
                                                  }
                                                : undefined
                                        "
                                        @focus="isTextComposerFocused = true"
                                        @blur="isTextComposerFocused = false"
                                        @input="onTextComposerInput"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-sm text-slate-500">
                                <span>{{ t('public.album.text.background_theme') }}</span>
                                <span>{{ textForm.text.trim().length }}/500</span>
                            </div>

                            <div class="-mx-1 flex gap-3 overflow-x-auto px-1 pb-1">
                                <button
                                    v-for="theme in props.textPostThemes"
                                    :key="theme.id"
                                    type="button"
                                    class="relative shrink-0 overflow-hidden rounded-[1.35rem] border-2 shadow-sm transition"
                                    :class="
                                        textForm.text_post_theme_id === theme.id
                                            ? 'border-slate-900'
                                            : 'border-transparent'
                                    "
                                    @click="textForm.text_post_theme_id = theme.id"
                                >
                                    <img
                                        :src="theme.imageUrl"
                                        :alt="theme.name"
                                        class="size-24 object-cover"
                                    />
                                    <div
                                        v-if="textForm.text_post_theme_id === theme.id"
                                        class="absolute inset-0 flex items-start justify-end bg-black/10 p-2"
                                    >
                                        <span class="inline-flex size-7 items-center justify-center rounded-full bg-white text-slate-900 shadow-sm">
                                            <CheckCircle2 class="size-4.5" />
                                        </span>
                                    </div>
                                </button>
                            </div>
                            <p
                                v-if="textForm.errors.text || textForm.errors.text_post_theme_id"
                                class="text-sm text-rose-700"
                            >
                                {{ textForm.errors.text || textForm.errors.text_post_theme_id }}
                            </p>
                            <button
                                v-if="textForm.processing || textForm.text.trim().length > 0"
                                type="button"
                                class="inline-flex h-14 w-full items-center justify-center rounded-[1.5rem] px-6 text-base font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                :style="heroAccentStyle"
                                :disabled="!canUploadText || textForm.processing || textForm.text.trim().length === 0"
                                @click="submitTextPost"
                            >
                                {{ textForm.processing ? t('public.album.text.posting') : t('public.album.text.post_button') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="menuOpen"
                class="fixed inset-0 z-[72] bg-[#fcfaf6]"
            >
                <div class="flex h-full flex-col">
                    <header class="safe-top sticky top-0 z-10 border-b border-slate-200 bg-[#fcfaf6]/96 px-5 pb-5 pt-3 backdrop-blur">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold text-slate-900">
                                    {{ t('public.album.settings.title') }}
                                </p>
                                <p class="mt-2 max-w-lg text-sm leading-relaxed text-slate-600">
                                    {{ t('public.album.settings.description') }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex size-12 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                                :aria-label="t('public.shared.close_menu')"
                                @click="menuOpen = false"
                            >
                                <X class="size-5" />
                            </button>
                        </div>
                    </header>

                    <div class="flex-1 overflow-y-auto px-5 pb-10 pt-5">
                        <section class="rounded-[1.9rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                                <div class="flex items-center gap-4">
                                    <Avatar class="size-20 border border-slate-200">
                                        <AvatarImage
                                            v-if="currentGuestAvatarUrl"
                                            :src="currentGuestAvatarUrl ?? ''"
                                            :alt="guestName || t('public.shared.alt.guest_avatar')"
                                        />
                                        <AvatarFallback :class="avatarFallbackClass(guestName)">
                                            {{ guestInitials(guestName) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <p class="text-base font-semibold text-slate-900">
                                            {{ t('public.album.settings.profile_title') }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ t('public.album.menu.signed_in_as') }}
                                            <span class="font-semibold text-slate-900">{{ guestName.trim() || t('public.shared.guest') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <input
                                        ref="guestAvatarInputRef"
                                        type="file"
                                        accept="image/*"
                                        class="sr-only"
                                        @change="onGuestAvatarSelectionChange"
                                    />
                                    <button
                                        type="button"
                                        class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                        @click="guestAvatarInputRef?.click()"
                                    >
                                        {{ t('public.album.choose_avatar') }}
                                    </button>
                                    <button
                                        v-if="currentGuestAvatarUrl"
                                        type="button"
                                        class="inline-flex h-11 items-center justify-center rounded-full border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                        @click="clearGuestAvatarSelection"
                                    >
                                        {{ t('public.shared.remove') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4">
                                <div
                                    v-for="field in welcomeGuestFields"
                                    :key="`settings-field-${field.id}`"
                                    class="space-y-2"
                                >
                                    <label
                                        :for="`settings-${field.id}`"
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        {{ field.label }}
                                    </label>
                                    <input
                                        :id="`settings-${field.id}`"
                                        v-model="guestFieldValues[field.id]"
                                        :type="field.type === 'phone' ? 'tel' : field.type"
                                        :placeholder="field.help_text"
                                        class="h-13 w-full rounded-[1.2rem] border border-slate-200 bg-slate-50 px-4 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:bg-white"
                                    />
                                    <p
                                        v-if="onboardingErrors[field.id]"
                                        class="text-sm text-rose-700"
                                    >
                                        {{ onboardingErrors[field.id] }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="mt-5 inline-flex h-13 w-full items-center justify-center rounded-[1.3rem] px-5 text-base font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                :style="heroAccentStyle"
                                :disabled="guestProfileProcessing"
                                @click="saveGuestSettings"
                            >
                                {{
                                    guestProfileProcessing
                                        ? t('public.shared.saving')
                                        : t('public.album.settings.save_button')
                                }}
                            </button>
                            <p
                                v-if="guestProfileError"
                                class="mt-3 text-sm text-rose-700"
                            >
                                {{ guestProfileError }}
                            </p>
                        </section>

                        <section class="mt-5 rounded-[1.9rem] border border-slate-200 bg-white p-5 shadow-sm">
                            <p class="text-base font-semibold text-slate-900">
                                {{ t('public.album.settings.event_details') }}
                            </p>
                            <div class="mt-4 space-y-3 text-sm text-slate-600">
                                <p
                                    v-if="eventDate"
                                    class="flex items-center gap-3 rounded-[1.2rem] bg-slate-50 px-4 py-3"
                                >
                                    <CalendarDays class="size-5 text-slate-500" />
                                    <span>
                                        {{ t('public.album.meta.event_date') }}
                                        <span class="font-semibold text-slate-900">{{ formatDate(eventDate) }}</span>
                                    </span>
                                </p>
                                <p
                                    v-if="uploadWindowStartsAt || uploadWindowEndsAt"
                                    class="flex items-center gap-3 rounded-[1.2rem] bg-slate-50 px-4 py-3"
                                >
                                    <Clock3 class="size-5 text-slate-500" />
                                    <span>
                                        {{ t('public.album.meta.upload_window') }}
                                        <span class="font-semibold text-slate-900">{{ formatDateTime(uploadWindowStartsAt) }} - {{ formatDateTime(uploadWindowEndsAt) }}</span>
                                    </span>
                                </p>
                            </div>

                            <div class="mt-5 grid gap-3">
                                <a
                                    :href="links.wall"
                                    class="inline-flex h-13 items-center justify-center gap-2 rounded-[1.25rem] border border-slate-200 px-5 text-base font-semibold text-slate-800 transition hover:bg-slate-50"
                                >
                                    <Images class="size-5" />
                                    {{ t('public.album.menu.open_photo_wall') }}
                                </a>
                                <button
                                    v-if="isPreEventTestMode"
                                    type="button"
                                    class="inline-flex h-13 items-center justify-center gap-2 rounded-[1.25rem] border border-amber-200 bg-amber-50 px-5 text-base font-semibold text-amber-900 transition hover:bg-amber-100"
                                    @click="
                                        menuOpen = false;
                                        isPreEventInfoOpen = true;
                                    "
                                >
                                    <AlertTriangle class="size-5" />
                                    {{ t('public.album.menu.pre_event_test_mode') }}
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex h-13 items-center justify-center gap-2 rounded-[1.25rem] border border-rose-200 bg-rose-50 px-5 text-base font-semibold text-rose-700 transition hover:bg-rose-100"
                                    @click="resetGuestOnboarding"
                                >
                                    <Trash2 class="size-5" />
                                    {{ t('public.album.menu.reset_guest_onboarding') }}
                                </button>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </transition>

        <Drawer
            direction="bottom"
            :open="isAssetCommentsOpen"
            @update:open="(open) => { if (!open) closeAssetComments(); }"
        >
            <DrawerContent
                class="safe-bottom max-h-[84vh] rounded-t-[2rem] border-t border-slate-200 bg-[#fcfaf6] px-0 pb-0"
            >
                    <DrawerHeader class="border-b border-slate-200 px-5 pb-5 pt-3 text-center">
                        <DrawerTitle class="text-center text-lg text-slate-900">
                            {{ t('public.album.comments.title') }}
                        </DrawerTitle>
                    </DrawerHeader>

                <div
                    v-if="selectedCommentsAsset"
                    class="flex max-h-[calc(84vh-5rem)] min-h-0 flex-col"
                >
                    <div
                        v-if="isAssetCommentsLoading(selectedCommentsAsset.id)"
                        class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-5"
                    >
                        <div
                            v-for="skeletonIndex in 3"
                            :key="`comment-skeleton-${skeletonIndex}`"
                            class="animate-pulse py-3"
                        >
                            <div class="h-4 w-24 rounded bg-slate-200" />
                            <div class="mt-3 h-3 w-full rounded bg-slate-200" />
                            <div class="mt-2 h-3 w-4/5 rounded bg-slate-200" />
                        </div>
                    </div>

                    <div
                        v-else-if="selectedComments.length === 0"
                        class="flex min-h-0 flex-1 items-center justify-center px-5 py-10"
                    >
                        <div class="text-center">
                            <p class="text-sm font-medium text-slate-900">{{ t('public.album.comments.none_title') }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ t('public.album.comments.none_description') }}</p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="min-h-0 flex-1 space-y-4 overflow-y-auto px-5 py-5"
                    >
                        <article
                            v-for="comment in selectedComments"
                            :key="`comment-${comment.id}`"
                            class="py-1"
                        >
                            <div class="flex items-start gap-3">
                                <Avatar class="size-10 border border-slate-200">
                                    <AvatarImage
                                        v-if="comment.guestAvatarUrl"
                                        :src="comment.guestAvatarUrl"
                                        :alt="comment.guestName"
                                    />
                                    <AvatarFallback
                                        :class="avatarFallbackClass(comment.guestName)"
                                    >
                                        {{ guestInitials(comment.guestName) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="relative min-w-0 flex-1 pr-14">
                                    <div class="min-w-0 flex items-center gap-2">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            {{ comment.guestName }}
                                        </p>
                                        <p class="shrink-0 text-xs text-slate-500">
                                            {{ formatRelativeTime(comment.createdAt) }}
                                        </p>
                                    </div>
                                    <p class="mt-0.5 whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                                        {{ comment.body }}
                                    </p>
                                    <button
                                        type="button"
                                        class="absolute right-0 top-0 inline-flex items-center gap-1 px-1 py-1 text-xs font-medium text-slate-500 transition hover:text-rose-600 disabled:opacity-50"
                                        :class="comment.liked ? 'text-rose-600' : ''"
                                        :disabled="isCommentLikePending(comment.id)"
                                        @click="toggleCommentLike(comment)"
                                    >
                                        <span>{{ formatLikeCount(comment.likeCount) }}</span>
                                        <Heart
                                            class="size-5"
                                            :class="comment.liked ? 'fill-rose-500 text-rose-500' : ''"
                                        />
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <DrawerFooter class="mt-auto gap-3 border-t border-slate-200 bg-white/95 px-5 pb-6 pt-4">
                        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
                            <button
                                v-for="emoji in commentEmojiOptions"
                                :key="`comment-emoji-${emoji}`"
                                type="button"
                                class="inline-flex size-11 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-lg transition hover:border-slate-300 hover:bg-slate-100"
                                @click="appendCommentEmoji(emoji)"
                            >
                                {{ emoji }}
                            </button>
                        </div>
                        <div class="space-y-2">
                            <InputGroup class="h-12 rounded-full border-slate-200 bg-white">
                                <InputGroupInput
                                    v-model="commentDraft"
                                    maxlength="500"
                                    :placeholder="t('public.album.comments.placeholder')"
                                    :disabled="isAssetCommentPending(selectedCommentsAsset.id)"
                                    class="h-12 rounded-full px-4 text-sm"
                                    @keydown.enter.prevent="commentDraft.trim().length > 0 ? submitAssetComment() : undefined"
                                />
                                <InputGroupAddon
                                    v-if="commentDraft.trim().length > 0"
                                    align="inline-end"
                                    class="pr-2"
                                >
                                    <InputGroupButton
                                        size="sm"
                                        class="h-8 rounded-full bg-[#1d9bf0] px-3 text-white hover:bg-[#1a8cd8]"
                                        :disabled="isAssetCommentPending(selectedCommentsAsset.id)"
                                        @click="submitAssetComment"
                                    >
                                        <ArrowUp class="size-4" />
                                    </InputGroupButton>
                                </InputGroupAddon>
                            </InputGroup>
                            <p
                                v-if="commentError"
                                class="text-xs text-rose-700"
                            >
                                {{ commentError }}
                            </p>
                        </div>
                    </DrawerFooter>
                </div>
            </DrawerContent>
        </Drawer>

        <transition
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150"
            enter-from-class="opacity-0 translate-y-3 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-3 scale-95"
        >
            <button
                v-if="showScrollTopButton"
                type="button"
                class="safe-right fixed bottom-28 right-4 z-30 inline-flex h-11 w-14 items-center justify-center rounded-full border border-white/20 bg-slate-900/76 text-white shadow-[0_14px_30px_rgba(15,23,42,0.24)] backdrop-blur-md transition hover:-translate-y-0.5 hover:bg-slate-900/84"
                :aria-label="t('public.album.actions.scroll_to_top')"
                @click="scrollAlbumToTop"
            >
                <ArrowUp class="size-5" />
            </button>
        </transition>

        <transition
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectedAsset"
                class="fixed inset-0 z-50 bg-black text-white"
            >
                <header class="absolute inset-x-0 top-0 z-20 border-b border-white/10 bg-black/90 px-3 py-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <button
                                type="button"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white"
                                :aria-label="t('public.album.actions.close_viewer')"
                                @click="closeAssetViewer"
                            >
                                <X class="size-5" />
                            </button>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-white">
                                    {{ selectedAsset.guestName || t('public.shared.guest') }}
                                </p>
                                <p class="truncate text-xs text-white/75">
                                    {{ formatDateTime(selectedAsset.createdAt) }}
                                </p>
                            </div>
                        </div>

                        <button
                            v-if="selectedAssetCanDelete"
                            type="button"
                            class="inline-flex size-10 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white disabled:opacity-50"
                            :disabled="deleteAssetForm.processing"
                            :aria-label="t('public.album.actions.delete_upload')"
                            @click="deleteSelectedAsset"
                        >
                            <Trash2 class="size-5" />
                        </button>
                    </div>
                </header>

                <div
                    class="box-border flex h-[100dvh] w-screen touch-pan-y items-center justify-center overflow-hidden px-3 pb-[calc(7.5rem+env(safe-area-inset-bottom))] pt-[calc(5rem+env(safe-area-inset-top))]"
                    @touchstart.passive="onViewerTouchStart"
                    @touchmove.passive="onViewerTouchMove"
                    @touchend.passive="onViewerTouchEnd"
                    @touchcancel.passive="onViewerTouchCancel"
                >
                    <div class="flex min-h-0 w-full flex-1 items-center justify-center overflow-hidden">
                        <img
                            v-if="selectedAsset.kind === 'photo' && selectedAsset.previewUrl"
                            :src="selectedAsset.previewUrl"
                            :alt="t('public.shared.alt.selected_event_photo')"
                            class="block max-h-full max-w-full object-contain object-center"
                        />
                        <video
                            v-else-if="selectedAsset.kind === 'video' && selectedAsset.previewUrl"
                            :src="selectedAsset.previewUrl"
                            :poster="selectedAsset.thumbnailUrl ?? undefined"
                            class="block max-h-full max-w-full object-contain object-center"
                            controls
                            autoplay
                            playsinline
                        />
                        <div
                            v-else-if="
                                selectedAsset.kind === 'video' &&
                                selectedAsset.videoProcessing
                            "
                            class="flex aspect-video w-full max-w-[min(92vw,1100px)] flex-col items-center justify-center gap-4 rounded-[2rem] border border-white/20 bg-white/10 px-8 text-center text-white backdrop-blur"
                        >
                            <LoaderCircle class="size-10 animate-spin text-white/80" />
                            <div class="space-y-1">
                                <p class="text-base font-semibold">
                                    {{ t('public.album.labels.processing_video') }}
                                </p>
                                <p class="text-sm text-white/75">
                                    {{ t('public.album.labels.upload_preparing') }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else-if="selectedAsset.kind === 'text'"
                            class="flex max-h-full w-full max-w-[min(90vw,80vh)] items-center justify-center rounded-[2rem] p-8 shadow-2xl"
                            :style="textPostSurfaceStyle(selectedAsset)"
                        >
                            <p
                                class="max-w-[78%] whitespace-pre-wrap text-center text-[2.8rem] font-semibold leading-[1.22] sm:text-[3.4rem]"
                                :style="textPostContentStyle(selectedAsset)"
                            >
                                {{ selectedAsset.text ?? t('public.album.labels.text_post') }}
                            </p>
                        </div>
                    </div>

                    <button
                        v-if="hasMultipleInSelectedStack"
                        type="button"
                        class="absolute left-2 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/55 text-white"
                        :aria-label="t('public.album.actions.previous_in_stack')"
                        @click="showPreviousInStack"
                    >
                        <ChevronLeft class="size-5" />
                    </button>
                    <button
                        v-if="hasMultipleInSelectedStack"
                        type="button"
                        class="absolute right-2 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/55 text-white"
                        :aria-label="t('public.album.actions.next_in_stack')"
                        @click="showNextInStack"
                    >
                        <ChevronRight class="size-5" />
                    </button>
                </div>
                <div
                    class="pointer-events-none absolute inset-x-0 bottom-24 z-20 flex justify-center px-4"
                >
                    <div class="inline-flex items-center gap-4 rounded-full border border-white/20 bg-black/58 px-4 py-2 text-sm font-semibold text-white shadow-[0_16px_36px_rgba(0,0,0,0.24)] backdrop-blur-md">
                        <span class="inline-flex items-center gap-2">
                            <Heart class="size-4 text-rose-400" />
                            {{ formatLikeCount(selectedAsset.likeCount) }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <MessageCircle class="size-4 text-white/80" />
                            {{ formatLikeCount(selectedAsset.commentCount) }}
                        </span>
                    </div>
                </div>
                <div
                    v-if="
                        showPreviewWatermark &&
                        (selectedAsset.kind === 'photo' || selectedAsset.kind === 'video')
                    "
                    class="pointer-events-none absolute inset-x-0 bottom-40 z-20 flex justify-center"
                >
                    <span class="rounded-full border border-white/35 bg-black/55 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white">
                        {{ t('public.album.labels.preview_only_payment_required') }}
                    </span>
                </div>

                <footer class="absolute inset-x-0 bottom-0 z-20 border-t border-white/10 bg-black/90 px-3 py-2.5">
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-white/20 px-3 py-1.5 text-xs font-medium text-white transition"
                            :class="
                                selectedAssetLiked
                                    ? 'bg-rose-500/15 text-rose-300'
                                    : 'hover:bg-white/10'
                            "
                            :disabled="selectedAsset === null || isAssetLikePending(selectedAsset)"
                            :aria-pressed="selectedAssetLiked"
                            @click="toggleSelectedAssetLike"
                        >
                            <Heart
                                class="size-3.5 transition-transform duration-200"
                                :class="
                                    [
                                        selectedAssetLiked
                                            ? 'fill-rose-500 text-rose-500'
                                            : '',
                                        selectedAsset && isAssetLikeAnimating(selectedAsset)
                                            ? 'scale-125'
                                            : '',
                                        selectedAsset && isAssetLikePending(selectedAsset)
                                            ? 'opacity-60'
                                            : '',
                                    ]
                                "
                            />
                            {{ selectedAsset && isAssetLikePending(selectedAsset) ? t('public.shared.saving') : t('public.album.actions.like') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-full border border-white/20 px-3 py-1.5 text-xs font-medium text-white"
                            :aria-label="t('public.album.asset.share')"
                            @click="shareSelectedAsset"
                        >
                            <Copy class="size-3.5" />
                            {{ t('public.album.asset.share') }}
                        </button>
                        <a
                            v-if="selectedAssetCanDownload"
                            :href="selectedAsset.downloadUrl"
                            class="inline-flex items-center gap-1 rounded-full border border-white/20 px-3 py-1.5 text-xs font-medium text-white"
                        >
                            <Download class="size-3.5" />
                            {{ t('public.shared.download') }}
                        </a>
                    </div>

                    <p
                        v-if="selectedStack?.preview.kind !== 'text' && selectedStack?.preview.message"
                        class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-white/80"
                    >
                        {{ selectedStack.preview.message }}
                    </p>

                    <div
                        v-if="hasMultipleInSelectedStack"
                        class="mt-2 flex items-center justify-center gap-1.5"
                    >
                        <span
                            v-for="(asset, index) in selectedStackAssets"
                            :key="`stack-dot-${asset.id}`"
                            class="size-1.5 rounded-full"
                            :class="
                                index === activeStackSlideIndex
                                    ? 'bg-white'
                                    : 'bg-white/30'
                            "
                        />
                    </div>
                </footer>
            </div>
        </transition>

        <Drawer
            v-if="showCaptions"
            direction="bottom"
            :open="isAssetInfoOpen"
            @update:open="(open) => { if (!open) closeAssetInfo(); }"
        >
            <DrawerContent
                class="safe-bottom max-h-[78vh] rounded-t-[2rem] border-t border-slate-200 bg-[#fcfaf6] px-0 pb-0"
            >
                <DrawerHeader class="border-b border-slate-200 px-5 pb-5 pt-3 text-center">
                    <DrawerTitle class="text-center text-lg text-slate-900">
                        {{ t('public.album.info.title') }}
                    </DrawerTitle>
                </DrawerHeader>

                <div
                    v-if="selectedInfoAsset"
                    class="min-h-0 flex-1 overflow-y-auto px-5 py-5"
                >
                    <div class="flex items-start gap-3 border-b border-slate-200 pb-4">
                        <Avatar class="size-11 border border-slate-200">
                            <AvatarImage
                                v-if="selectedInfoAsset.guestAvatarUrl"
                                :src="selectedInfoAsset.guestAvatarUrl ?? ''"
                                :alt="selectedInfoAsset.guestName || t('public.shared.alt.guest_avatar')"
                            />
                            <AvatarFallback
                                :class="avatarFallbackClass(selectedInfoAsset.guestName)"
                            >
                                {{ guestInitials(selectedInfoAsset.guestName) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">
                                {{ selectedInfoAsset.guestName || t('public.shared.guest') }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ formatDateTime(selectedInfoAsset.createdAt) }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ formatRelativeTime(selectedInfoAsset.createdAt) }}
                            </p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-200">
                        <div class="flex items-start justify-between gap-4 py-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.info.type') }}
                            </span>
                            <span class="text-right text-sm font-medium capitalize text-slate-900">
                                {{ selectedInfoAsset.kind }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.info.status') }}
                            </span>
                            <span class="text-right text-sm font-medium capitalize text-slate-900">
                                {{ selectedInfoAsset.moderationStatus }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4 py-4">
                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.info.size') }}
                            </span>
                            <span class="text-right text-sm font-medium text-slate-900">
                                {{ formatBytes(selectedInfoAsset.sizeBytes) }}
                            </span>
                        </div>

                        <div
                            v-if="selectedInfoAsset.mimeType"
                            class="flex items-start justify-between gap-4 py-4"
                        >
                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.info.format') }}
                            </span>
                            <span class="max-w-[60%] break-all text-right text-sm font-medium text-slate-900">
                                {{ selectedInfoAsset.mimeType }}
                            </span>
                        </div>

                        <div
                            v-if="selectedInfoAsset.message?.trim()"
                            class="py-4"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.info.message') }}
                            </p>
                            <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-900">
                                {{ selectedInfoAsset.message }}
                            </p>
                        </div>

                        <div
                            v-if="selectedInfoAsset.kind === 'text' && selectedInfoAsset.text?.trim()"
                            class="py-4"
                        >
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                {{ t('public.album.labels.text_post') }}
                            </p>
                            <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-900">
                                {{ selectedInfoAsset.text }}
                            </p>
                        </div>
                    </div>
                </div>
            </DrawerContent>
        </Drawer>

    </main>
</template>

<style scoped>
:deep(button),
:deep(a) {
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

:deep(button:focus),
:deep(button:focus-visible),
:deep(a:focus),
:deep(a:focus-visible),
:deep([role='button']:focus),
:deep([role='button']:focus-visible) {
    outline: none !important;
    box-shadow: none !important;
}

:deep(input),
:deep(textarea),
:deep(select),
[contenteditable='true'] {
    font-size: 16px;
    -webkit-text-size-adjust: 100%;
}

[contenteditable='true'] {
    touch-action: manipulation;
    -webkit-user-select: text;
    user-select: text;
}

.welcome-bg-animate-slow {
    animation: welcome-bg-breathe 62s ease-in-out infinite;
    transform-origin: center center;
    will-change: transform, opacity;
}

@keyframes welcome-bg-breathe {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    30% {
        transform: scale(1.06);
        opacity: 0.84;
    }
    55% {
        transform: scale(1.09);
        opacity: 0.78;
    }
    80% {
        transform: scale(1.03);
        opacity: 0.92;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>
