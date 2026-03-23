<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CalendarDays,
    Camera,
    CheckCircle2,
    Clock3,
    Download,
    Film,
    Info,
    Images,
    Lock,
    Menu,
    MessageSquareText,
    Trash2,
    UploadCloud,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Table, TableBody, TableCell, TableHead, TableRow } from '@/components/ui/table';

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
    previewUrl: string | null;
    downloadUrl: string;
    deleteUrl: string;
    sizeBytes: number;
    text: string | null;
    guestName: string | null;
    originalFilename: string | null;
    mimeType: string | null;
    createdAt: string | null;
};

type AssetGroup = {
    key: string;
    guestName: string;
    latestCreatedAt: string | null;
    assets: AssetItem[];
};

type GuestIntent =
    | 'upload_media'
    | 'video_testimonial'
    | 'text_wish'
    | 'browse_gallery';

type UploadMode = 'mixed' | 'video_only';

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
    albumBackgroundMode: 'rotate' | 'solid' | 'image';
    albumBackgroundColor: string;
    albumBackgroundImageUrl: string | null;
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
    preEventTestUploadLimit: number;
    preEventTestUploadsRemaining: number;
    uploadUrl: string;
    textPostUrl: string;
    allowTextPosts: boolean;
    allowedMediaTypes: string[];
    canGuestDownload: boolean;
    links: PublicLinks;
    albumQrDataUrl: string;
    appearance: AppearanceConfig;
    welcomeScreen: WelcomeScreenConfig;
    limits: Limits;
    assets: AssetItem[];
}>();

const page = usePage();
const appName = computed(() => page.props.name ?? 'QR Events');

const fileInputRef = ref<HTMLInputElement | null>(null);
const menuOpen = ref(false);
const onboardingStep = ref<1 | 2>(1);
const onboardingDone = ref(false);
const guestToken = ref('');
const guestFieldValues = ref<Record<string, string>>({
    name: '',
    email: '',
    phone: '',
});
const onboardingErrors = ref<Record<string, string>>({});
const selectedIntent = ref<GuestIntent>('browse_gallery');
const activeView = ref<GuestIntent>('browse_gallery');
const isValidatingVideos = ref(false);
const clientValidationErrors = ref<string[]>([]);
const assetItems = ref<AssetItem[]>([...props.assets]);
const activeAssetId = ref<number | null>(null);
const isAssetInfoOpen = ref(false);
const isComposerOpen = ref(false);
const isPreEventInfoOpen = ref(false);
const isHeaderCollapsed = ref(false);
const loadedPhotoAssetIds = ref<Record<number, boolean>>({});

const uploadForm = useForm<{
    files: File[];
    guest_name: string;
    guest_email: string | null;
    guest_phone: string | null;
    guest_token: string | null;
    guest_fields: Record<string, string>;
    guest_intent: GuestIntent | null;
}>({
    files: [],
    guest_name: '',
    guest_email: null,
    guest_phone: null,
    guest_token: null,
    guest_fields: {},
    guest_intent: null,
});

const textForm = useForm<{
    text: string;
    guest_name: string;
    guest_email: string | null;
    guest_phone: string | null;
    guest_token: string | null;
    guest_fields: Record<string, string>;
    guest_intent: GuestIntent | null;
}>({
    text: '',
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
const selectedAsset = computed(() =>
    assetItems.value.find((asset) => asset.id === activeAssetId.value) ?? null,
);
const selectedAssetCanDelete = computed(() => {
    const asset = selectedAsset.value;
    if (!asset) {
        return false;
    }

    return (
        asset.guestName !== null &&
        asset.guestName.trim().toLowerCase() === guestName.value.trim().toLowerCase()
    );
});
const selectedAssetCanDownload = computed(
    () => selectedAsset.value !== null && props.canGuestDownload,
);
const groupedAssetItems = computed<AssetGroup[]>(() => {
    const groups = new Map<string, AssetGroup>();

    for (const asset of assetItems.value) {
        const normalizedGuestName = asset.guestName?.trim();
        const guestName =
            normalizedGuestName && normalizedGuestName.length > 0
                ? normalizedGuestName
                : 'Guest';
        const key = guestName.toLowerCase();

        if (!groups.has(key)) {
            groups.set(key, {
                key,
                guestName,
                latestCreatedAt: asset.createdAt,
                assets: [asset],
            });
            continue;
        }

        const group = groups.get(key);
        if (!group) {
            continue;
        }

        group.assets.push(asset);
    }

    return Array.from(groups.values());
});

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

const statusCard = computed(() => {
    if (props.isPaymentLocked) {
        return {
            title: 'Event locked until payment',
            description:
                'Guests can preview content, but uploads and full-quality access are locked.',
            classes: 'border-rose-200 bg-rose-50 text-rose-800',
            icon: Lock,
        };
    }

    if (props.isUploadOpen) {
        return {
            title: 'Uploads are open',
            description:
                'Guests can upload photos, short videos, and messages right now.',
            classes: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            icon: CheckCircle2,
        };
    }

    if (props.isPreEventTestMode) {
        return {
            title: 'Pre-event test mode',
            description: `Testing is open with ${props.preEventTestUploadsRemaining}/${props.preEventTestUploadLimit} uploads remaining.`,
            classes: 'border-sky-200 bg-sky-50 text-sky-800',
            icon: AlertTriangle,
        };
    }

    return {
        title: 'Uploads are currently paused',
        description: 'Uploads become available during the event upload window.',
        classes: 'border-amber-200 bg-amber-50 text-amber-900',
        icon: Clock3,
    };
});

const uploadTitle = computed(() =>
    uploadMode.value === 'video_only'
        ? 'Leave a video testimonial'
        : 'Upload event photos & videos',
);
const uploadDescription = computed(() =>
    uploadMode.value === 'video_only'
        ? `Record or select a short clip (${props.limits.videoMinDurationSeconds}-${props.limits.videoMaxDurationSeconds}s).`
        : 'Share beautiful moments from the event directly to this album.',
);
const uploadButtonLabel = computed(() =>
    uploadMode.value === 'video_only'
        ? 'Upload testimonial'
        : 'Upload selected files',
);

const intentOptions = computed(() => [
    {
        value: 'upload_media' as GuestIntent,
        label: 'Upload event photos',
        description: 'Share images and short clips from your phone.',
        icon: Camera,
        enabled: canUploadPhotos.value || canUploadVideos.value,
    },
    {
        value: 'video_testimonial' as GuestIntent,
        label: 'Leave video testimonial',
        description: 'Upload one short congratulation video.',
        icon: Film,
        enabled: canUploadVideos.value,
    },
    {
        value: 'text_wish' as GuestIntent,
        label: 'Write a text wish',
        description: 'Post a message for the couple and family.',
        icon: MessageSquareText,
        enabled: props.allowTextPosts,
    },
    {
        value: 'browse_gallery' as GuestIntent,
        label: 'Just view gallery',
        description: 'See what guests already shared.',
        icon: Images,
        enabled: true,
    },
]);

const welcomeGuestFields = computed<WelcomeField[]>(() => {
    if (!customWelcomeEnabled.value) {
        return [
            {
                id: 'name',
                type: 'text',
                label: 'Name',
                help_text: 'Write your name',
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
            label: 'Name',
            help_text: 'Write your name',
            attach_to: null,
            required: true,
            enabled: true,
        },
    ];
    if (props.welcomeScreen.collectEmail) {
        fallback.push({
            id: 'email',
            type: 'email',
            label: 'Email',
            help_text: 'Write your email',
            attach_to: null,
            required: false,
            enabled: true,
        });
    }
    if (props.welcomeScreen.collectPhone) {
        fallback.push({
            id: 'phone',
            type: 'phone',
            label: 'Phone',
            help_text: 'Write your phone',
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
const albumAvatarFallback = computed(() =>
    (welcomeTitle.value || props.eventName || 'K').trim().charAt(0).toUpperCase(),
);
const albumLogoUrl = computed(
    () => props.welcomeScreen.logoUrl ?? props.appearance.logoUrl ?? null,
);
const heroAccentStyle = computed(() => {
    return {
        backgroundColor: props.appearance.primaryColor || '#0f172a',
    };
});
const albumBodyStyle = computed((): Record<string, string> => {
    const style: Record<string, string> = {};

    if (!onboardingDone.value || !props.appearance.albumBackgroundEnabled) {
        return style;
    }

    if (props.appearance.albumBackgroundMode === 'solid') {
        style.backgroundColor =
            props.appearance.albumBackgroundColor || '#ffffff';

        return style;
    }

    if (
        props.appearance.albumBackgroundMode === 'image' &&
        props.appearance.albumBackgroundImageUrl
    ) {
        style.backgroundImage = `url(${props.appearance.albumBackgroundImageUrl})`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
        style.backgroundAttachment = 'fixed';
    }

    return style;
});

const welcomeTitle = computed(() => {
    if (!customWelcomeEnabled.value) {
        return props.eventName;
    }

    const value = props.welcomeScreen.title?.trim();
    return value && value.length > 0 ? value : props.eventName;
});

const welcomeSubtitle = computed(() => {
    if (!customWelcomeEnabled.value) {
        return 'Share your photos & videos with us!';
    }

    const value = props.welcomeScreen.subtitle?.trim();
    return value && value.length > 0
        ? value
        : 'Share your photos & videos with us!';
});

const welcomeButtonText = computed(() => {
    if (!customWelcomeEnabled.value) {
        return 'Continue';
    }

    const value = props.welcomeScreen.buttonText?.trim();
    return value && value.length > 0 ? value : 'Continue';
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

const welcomeConfigFingerprint = computed(() =>
    JSON.stringify({
        enabled: customWelcomeEnabled.value,
        title: welcomeTitle.value,
        subtitle: welcomeSubtitle.value,
        buttonText: welcomeButtonText.value,
        font: props.welcomeScreen.font,
        animated: props.welcomeScreen.animated,
        logoUrl: albumLogoUrl.value,
        backgroundUrl: props.welcomeScreen.backgroundUrl,
        fields: welcomeGuestFields.value.map((field) => ({
            id: field.id,
            type: field.type,
            label: field.label,
            help_text: field.help_text,
            required: field.required,
            enabled: field.enabled,
        })),
    }),
);

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
        return 'Not set';
    }

    return new Intl.DateTimeFormat(
        typeof navigator !== 'undefined' ? navigator.language : 'en-GB',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
        },
    ).format(new Date(value));
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat(
        typeof navigator !== 'undefined' ? navigator.language : 'en-GB',
        {
            dateStyle: 'long',
        },
    ).format(new Date(value));
};

const updateHeaderCollapsed = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    isHeaderCollapsed.value = window.scrollY > 180;
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

const persistGuestProfile = (): void => {
    if (typeof window === 'undefined' || !onboardingDone.value) {
        return;
    }

    window.localStorage.setItem(
        guestStorageKey.value,
        JSON.stringify({
            fields: guestFieldValues.value,
            intent: activeView.value,
            welcomeFingerprint: welcomeConfigFingerprint.value,
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
            welcomeFingerprint?: unknown;
            guestToken?: unknown;
        };
        const storedWelcomeFingerprint =
            typeof parsed.welcomeFingerprint === 'string'
                ? parsed.welcomeFingerprint
                : null;
        if (
            storedWelcomeFingerprint === null ||
            storedWelcomeFingerprint !== welcomeConfigFingerprint.value
        ) {
            window.localStorage.removeItem(guestStorageKey.value);
            return;
        }
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
                : 'browse_gallery';
        guestToken.value =
            typeof parsed.guestToken === 'string' && parsed.guestToken.length > 0
                ? parsed.guestToken
                : createGuestToken();
        activeView.value = isIntentEnabled(parsedIntent)
            ? parsedIntent
            : 'browse_gallery';
        selectedIntent.value = activeView.value;
        onboardingDone.value = true;
        onboardingStep.value = 2;
    } catch {
        window.localStorage.removeItem(guestStorageKey.value);
    }
};

onMounted(() => {
    hydrateGuestProfile();
    if (guestToken.value === '') {
        guestToken.value = createGuestToken();
    }
    updateHeaderCollapsed();
    window.addEventListener('scroll', updateHeaderCollapsed, { passive: true });
});

onUnmounted(() => {
    if (typeof window === 'undefined') {
        return;
    }

    window.removeEventListener('scroll', updateHeaderCollapsed);
});

watch(
    () => props.assets,
    (nextAssets) => {
        assetItems.value = [...nextAssets];
        loadedPhotoAssetIds.value = {};
        if (
            activeAssetId.value !== null &&
            !nextAssets.some((asset) => asset.id === activeAssetId.value)
        ) {
            closeAssetViewer();
        }
    },
);

watch(isComposerOpen, (open) => {
    if (open) {
        return;
    }

    activeView.value = 'browse_gallery';
    selectedIntent.value = 'browse_gallery';
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
            errors[field.id] = 'Please enter your name.';
            continue;
        }
        if (field.type === 'email' && value.length > 0 && !isValidEmail(value)) {
            errors[field.id] = 'Use a valid email address.';
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

const completeOnboarding = (): void => {
    if (!validateGuestStepOne()) {
        onboardingStep.value = 1;
        return;
    }

    const nextIntent = isIntentEnabled(selectedIntent.value)
        ? selectedIntent.value
        : 'browse_gallery';

    activeView.value = nextIntent;
    selectedIntent.value = nextIntent;
    onboardingDone.value = true;
    menuOpen.value = false;
    persistGuestProfile();

    if (nextIntent !== 'browse_gallery') {
        isComposerOpen.value = true;
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
    onboardingErrors.value = {};
};

const setActiveView = (view: GuestIntent): void => {
    if (!isIntentEnabled(view)) {
        return;
    }

    activeView.value = view;
    selectedIntent.value = view;
    menuOpen.value = false;

    if (view === 'browse_gallery') {
        isComposerOpen.value = false;
        persistGuestProfile();
        return;
    }

    isComposerOpen.value = true;
};

const closeComposer = (): void => {
    isComposerOpen.value = false;
};

const openAssetViewer = (assetId: number): void => {
    activeAssetId.value = assetId;
};

const closeAssetViewer = (): void => {
    activeAssetId.value = null;
    isAssetInfoOpen.value = false;
};

const deleteSelectedAsset = (): void => {
    const asset = selectedAsset.value;
    if (!asset || !selectedAssetCanDelete.value || deleteAssetForm.processing) {
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
            isAssetInfoOpen.value = false;
            activeAssetId.value = null;
            deleteAssetForm.reset();
        },
    });
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
        uploadForm.files = [];
        clientValidationErrors.value = [];
        return;
    }

    uploadForm.files = await validateSelectedFiles(selectedFiles, uploadMode.value);
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
            uploadForm.reset();
            clientValidationErrors.value = [];
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
    });
};

const submitTextPost = (): void => {
    if (!onboardingDone.value || !canUploadText.value || textForm.processing) {
        return;
    }

    if (textForm.text.trim().length === 0) {
        textForm.setError('text', 'Text post cannot be empty.');
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
        },
    });
};
</script>

<template>
    <Head :title="`${eventName} Album`" />

    <main
        class="min-h-screen text-slate-900"
        :style="albumBodyStyle"
        :class="
            onboardingDone
                ? props.appearance.albumBackgroundEnabled &&
                  props.appearance.albumBackgroundMode === 'image' &&
                  props.appearance.albumBackgroundImageUrl
                    ? 'bg-slate-950'
                    : 'bg-white'
                : !onboardingDone && customWelcomeEnabled && welcomeScreen.backgroundUrl
                  ? 'bg-slate-950'
                  : 'bg-gradient-to-b from-rose-50 via-white to-amber-50'
        "
    >
        <div
            class="relative w-full"
            :class="
                onboardingDone
                    ? 'pb-24'
                    : !onboardingDone && customWelcomeEnabled
                    ? ''
                    : 'mx-auto max-w-md px-4 pb-24 pt-5 sm:max-w-lg'
            "
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
                    alt="Welcome background"
                    class="absolute inset-0 h-full w-full object-cover"
                    :class="
                        customWelcomeEnabled && welcomeScreen.animated
                            ? 'welcome-bg-animate-slow'
                            : ''
                    "
                />
                <div
                    v-else-if="customWelcomeEnabled"
                    class="absolute inset-0 bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500"
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
                    class="absolute inset-0 bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500"
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
                                    alt="Event logo"
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
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/85">
                            Digital Album
                        </p>
                        <h1
                            class="mt-2 text-3xl leading-tight font-semibold text-white"
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
                                        : `Write your ${field.label.toLowerCase()}`
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
                            @click="goToIntentStep"
                        >
                            {{ welcomeButtonText }}
                        </button>
                    </div>

                    <div
                        v-else
                        class="space-y-3"
                    >
                        <p class="text-sm font-medium text-white">
                            Nice to meet you, {{ guestName.trim() }}. What would you like to do first?
                        </p>
                        <button
                            v-for="option in intentOptions"
                            :key="option.value"
                            type="button"
                            class="w-full rounded-2xl border p-4 text-left transition"
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
                                        This option is disabled for this event.
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
                                Back
                            </button>
                            <button
                                type="button"
                                class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-100 px-4 text-sm font-medium text-slate-900 transition hover:bg-white"
                                @click="completeOnboarding"
                            >
                                {{ welcomeButtonText }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section
                v-else
                class="relative pb-6"
            >
                <header
                    class="pointer-events-none fixed inset-x-0 top-0 z-30 p-3 transition-all duration-300"
                    :class="isHeaderCollapsed ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'"
                >
                    <div class="mx-auto flex max-w-2xl items-center justify-between gap-3 rounded-full border border-slate-200 bg-white/92 px-3 py-2 shadow-lg backdrop-blur">
                        <div class="min-w-0 flex items-center gap-3">
                            <Avatar class="size-11 border border-slate-200">
                                <AvatarImage
                                    v-if="albumLogoUrl"
                                    :src="albumLogoUrl"
                                    alt="Event logo"
                                />
                                <AvatarFallback class="bg-slate-900 text-white">
                                    {{ albumAvatarFallback }}
                                </AvatarFallback>
                            </Avatar>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ welcomeTitle }}
                                </p>
                                <p class="truncate text-xs text-slate-600">
                                    {{ welcomeSubtitle }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="pointer-events-auto inline-flex size-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                            aria-label="Open menu"
                            @click="menuOpen = true"
                        >
                            <Menu class="size-5" />
                        </button>
                    </div>
                </header>

                <section class="relative min-h-[58svh] overflow-hidden">
                    <img
                        v-if="welcomeScreen.backgroundUrl"
                        :src="welcomeScreen.backgroundUrl"
                        alt="Album background"
                        class="absolute inset-0 h-full w-full object-cover"
                        :class="welcomeScreen.animated ? 'welcome-bg-animate-slow' : ''"
                    />
                    <div
                        v-else
                        class="absolute inset-0 bg-gradient-to-br from-amber-200 via-orange-100 to-white"
                    />
                    <div
                        class="absolute inset-0"
                        :class="
                            props.appearance.albumBackgroundEnabled &&
                            props.appearance.albumBackgroundMode === 'image'
                                ? 'bg-gradient-to-b from-black/30 via-black/45 to-black/75'
                                : 'bg-gradient-to-b from-black/20 via-black/35 to-black/65'
                        "
                    />

                    <div class="relative flex min-h-[58svh] items-end px-3 pb-4 pt-24">
                        <div class="w-full rounded-[2rem] border border-white/20 bg-white/12 p-4 text-white shadow-[0_24px_80px_rgba(0,0,0,0.24)] backdrop-blur-xl">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <Avatar class="size-14 border border-white/30">
                                        <AvatarImage
                                            v-if="albumLogoUrl"
                                            :src="albumLogoUrl"
                                            alt="Event logo"
                                        />
                                        <AvatarFallback class="bg-white/20 text-white">
                                            {{ albumAvatarFallback }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">
                                            Digital album
                                        </p>
                                        <h1
                                            class="mt-1 text-3xl leading-tight font-semibold text-white"
                                            :class="welcomeFontClass"
                                        >
                                            {{ welcomeTitle }}
                                        </h1>
                                    </div>
                                </div>

                                <button
                                    v-if="isPreEventTestMode"
                                    type="button"
                                    class="inline-flex size-10 items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition hover:bg-white/20"
                                    aria-label="Open pre-event test mode info"
                                    @click="isPreEventInfoOpen = true"
                                >
                                    <AlertTriangle class="size-4" />
                                </button>
                            </div>

                            <p class="mt-3 text-sm leading-relaxed text-white/85">
                                {{ welcomeSubtitle }}
                            </p>

                            <div class="mt-4 rounded-[1.5rem] border border-white/15 bg-black/20 p-3">
                                <div class="flex items-center gap-2">
                                    <component :is="statusCard.icon" class="size-4 text-white" />
                                    <p class="text-sm font-semibold text-white">
                                        {{ statusCard.title }}
                                    </p>
                                </div>
                                <p class="mt-1 text-xs leading-relaxed text-white/75">
                                    {{ statusCard.description }}
                                </p>

                                <div class="mt-4 grid gap-2 text-xs text-white/80 sm:grid-cols-2">
                                    <p class="flex items-center gap-2">
                                        <CalendarDays class="size-3.5 text-white/65" />
                                        Event date:
                                        <span class="font-medium text-white">{{ formatDate(eventDate) }}</span>
                                    </p>
                                    <p class="flex items-center gap-2">
                                        <Clock3 class="size-3.5 text-white/65" />
                                        Upload window:
                                        <span class="font-medium text-white">{{ formatDateTime(uploadWindowStartsAt) }} - {{ formatDateTime(uploadWindowEndsAt) }}</span>
                                    </p>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between text-xs text-white/75">
                                            <span>Storage</span>
                                            <span class="font-medium text-white">
                                                {{ formatBytes(limits.storageUsedBytes) }} / {{ formatBytes(limits.storageLimitBytes) }}
                                            </span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white/15">
                                            <div
                                                class="h-full rounded-full bg-white transition-all"
                                                :style="{ width: `${usageStoragePercent}%` }"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between text-xs text-white/75">
                                            <span>Uploads</span>
                                            <span class="font-medium text-white">{{ limits.uploadCount }} / {{ limits.uploadLimit }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white/15">
                                            <div
                                                class="h-full rounded-full bg-amber-300 transition-all"
                                                :style="{ width: `${usageUploadsPercent}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="showQrCode"
                                    class="mt-4 rounded-[1.25rem] border border-white/15 bg-white/10 p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="albumQrDataUrl"
                                            alt="Album QR code"
                                            class="size-16 rounded-lg border border-white/20 bg-white p-1"
                                        />
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/80">
                                                Share Album
                                            </p>
                                            <p class="mt-1 text-xs text-white/80">
                                                Guests can scan this code to open the album instantly.
                                            </p>
                                            <a
                                                :href="links.wall"
                                                class="mt-2 inline-flex text-xs font-medium text-white/95 underline underline-offset-4"
                                            >
                                                Open Photo Wall
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="px-0 pt-3">
                    <div class="mb-3 flex items-center justify-between gap-3 px-2">
                        <h2 class="text-sm font-semibold text-slate-900">Guest gallery</h2>
                        <span class="text-xs text-slate-500">{{ assetItems.length }} items</span>
                    </div>

                    <div
                        v-if="assetItems.length === 0"
                        class="px-2 py-8 text-center text-xs text-slate-500"
                    >
                        No uploads yet. Be the first guest to share a memory.
                    </div>

                    <div
                        v-else
                        class="space-y-5"
                    >
                        <section
                            v-for="group in groupedAssetItems"
                            :key="group.key"
                        >
                            <div
                                v-if="showCaptions"
                                class="mb-2 px-2"
                            >
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ group.guestName }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ formatDateTime(group.latestCreatedAt) }}
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-px bg-slate-200">
                                <article
                                    v-for="asset in group.assets"
                                    :key="asset.id"
                                    class="overflow-hidden bg-white"
                                >
                                    <button
                                        type="button"
                                        class="relative block aspect-square w-full overflow-hidden border-0 bg-slate-100 text-left"
                                        @click="openAssetViewer(asset.id)"
                                    >
                                        <template v-if="asset.kind === 'photo' && asset.previewUrl">
                                            <img
                                                :src="asset.previewUrl"
                                                alt="Uploaded event photo"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                                class="block h-full w-full object-cover transition-opacity duration-300"
                                                :class="isPhotoLoaded(asset.id) ? 'opacity-100' : 'opacity-0'"
                                                @load="markPhotoAsLoaded(asset.id)"
                                                @error="markPhotoAsLoaded(asset.id)"
                                            />
                                            <div
                                                v-if="!isPhotoLoaded(asset.id)"
                                                class="pointer-events-none absolute inset-0 animate-pulse bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100"
                                            />
                                        </template>
                                        <video
                                            v-else-if="asset.kind === 'video' && asset.previewUrl"
                                            :src="asset.previewUrl"
                                            class="block h-full w-full object-cover"
                                            preload="none"
                                            playsinline
                                        />
                                        <div
                                            v-else-if="asset.kind === 'text'"
                                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-900 to-slate-700 p-3"
                                        >
                                            <p class="line-clamp-6 whitespace-pre-wrap text-center text-xs text-white/95">
                                                {{ asset.text ?? 'Text post' }}
                                            </p>
                                        </div>
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center text-xs text-slate-500"
                                        >
                                            Preview unavailable
                                        </div>
                                        <div
                                            v-if="
                                                showPreviewWatermark &&
                                                (asset.kind === 'photo' || asset.kind === 'video')
                                            "
                                            class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/20"
                                        >
                                            <span class="rounded-full border border-white/40 bg-black/45 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-white">
                                                Preview only
                                            </span>
                                        </div>
                                    </button>
                                </article>
                            </div>
                        </section>
                    </div>
                </section>
            </section>
        </div>

        <footer
            v-if="onboardingDone"
            class="fixed inset-x-0 bottom-0 z-20 bg-white/92 backdrop-blur supports-[backdrop-filter]:bg-white/80"
        >
            <Separator class="bg-slate-200" />
            <div class="px-3 py-2 text-center text-xs text-slate-500">
                © {{ new Date().getFullYear() }} {{ appName }}. All rights reserved.
            </div>
        </footer>

        <Sheet v-model:open="isPreEventInfoOpen">
            <SheetContent
                side="bottom"
                class="max-h-[72vh] rounded-t-[2rem] px-5 pb-8 pt-8"
            >
                <SheetHeader class="px-0 text-left">
                    <SheetTitle class="flex items-center gap-2 text-xl text-slate-900">
                        <AlertTriangle class="size-5 text-amber-600" />
                        Pre-event test mode
                    </SheetTitle>
                    <SheetDescription class="text-sm leading-relaxed text-slate-600">
                        Guests can test the album setup before the event starts, but these uploads are for setup checks only.
                    </SheetDescription>
                </SheetHeader>

                <div class="mt-5 space-y-3 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-slate-500">Remaining test uploads</span>
                        <span class="font-semibold text-slate-900">
                            {{ props.preEventTestUploadsRemaining }} / {{ props.preEventTestUploadLimit }}
                        </span>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-600">
                        Full uploads open automatically when the event upload window starts. Until then, use this mode only to verify QR flow, upload limits, and gallery display.
                    </p>
                </div>
            </SheetContent>
        </Sheet>

        <Sheet v-model:open="isComposerOpen">
            <SheetContent
                side="bottom"
                class="max-h-[88vh] rounded-t-[2rem] px-5 pb-8 pt-8"
            >
                <SheetHeader class="px-0 text-left">
                    <SheetTitle class="flex items-center gap-2 text-xl">
                        <component
                            :is="
                                activeView === 'video_testimonial'
                                    ? Film
                                    : activeView === 'text_wish'
                                      ? MessageSquareText
                                      : Camera
                            "
                            class="size-5"
                        />
                        <span>
                            {{
                                activeView === 'text_wish'
                                    ? 'Write a text wish'
                                    : uploadTitle
                            }}
                        </span>
                    </SheetTitle>
                    <SheetDescription class="text-sm leading-relaxed">
                        {{
                            activeView === 'text_wish'
                                ? 'Leave a short message for the couple.'
                                : uploadDescription
                        }}
                    </SheetDescription>
                </SheetHeader>

                <div
                    v-if="activeView === 'upload_media' || activeView === 'video_testimonial'"
                    class="mt-6"
                >
                    <p class="text-xs text-slate-600">
                        Photo max: {{ formatBytes(limits.photoMaxSizeBytes) }}. Video max: {{ formatBytes(limits.videoMaxSizeBytes) }}.
                    </p>

                    <input
                        ref="fileInputRef"
                        type="file"
                        multiple
                        :accept="uploadAccept"
                        :disabled="!canUpload || uploadAccept.length === 0 || uploadForm.processing || isValidatingVideos"
                        class="mt-4 block w-full rounded-2xl border border-slate-200 bg-white text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2.5 file:text-sm file:font-medium file:text-white"
                        @change="onFileSelectionChange"
                    />

                    <div
                        v-if="uploadForm.files.length > 0"
                        class="mt-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4"
                    >
                        <p class="text-xs font-medium text-slate-800">
                            Selected files ({{ uploadForm.files.length }})
                        </p>
                        <ul class="mt-2 space-y-1 text-xs text-slate-600">
                            <li
                                v-for="file in uploadForm.files"
                                :key="`${file.name}-${file.size}`"
                                class="truncate"
                            >
                                {{ file.name }} ({{ formatBytes(file.size) }})
                            </li>
                        </ul>
                    </div>

                    <div
                        v-if="clientValidationErrors.length > 0"
                        class="mt-4 rounded-[1.5rem] border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900"
                    >
                        <p class="font-medium">Some files were skipped:</p>
                        <ul class="mt-1 list-inside list-disc space-y-1">
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
                        class="mt-3 text-xs text-rose-700"
                    >
                        {{ uploadForm.errors.files }}
                    </p>

                    <button
                        type="button"
                        class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl px-4 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        :style="heroAccentStyle"
                        :disabled="!canUpload || uploadForm.files.length === 0 || uploadForm.processing || isValidatingVideos"
                        @click="uploadFiles"
                    >
                        <UploadCloud class="mr-2 size-4" />
                        {{ uploadForm.processing ? 'Uploading...' : uploadButtonLabel }}
                    </button>
                </div>

                <div
                    v-else-if="activeView === 'text_wish' && allowTextPosts"
                    class="mt-6"
                >
                    <textarea
                        v-model="textForm.text"
                        rows="5"
                        maxlength="500"
                        placeholder="Write your message..."
                        :disabled="!canUploadText || textForm.processing"
                        class="w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-amber-300 focus:ring-4 focus:ring-amber-100 disabled:bg-slate-100"
                    />
                    <p
                        v-if="textForm.errors.text"
                        class="mt-3 text-xs text-rose-700"
                    >
                        {{ textForm.errors.text }}
                    </p>
                    <button
                        type="button"
                        class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl px-4 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                        :style="heroAccentStyle"
                        :disabled="!canUploadText || textForm.processing || textForm.text.trim().length === 0"
                        @click="submitTextPost"
                    >
                        {{ textForm.processing ? 'Posting...' : 'Post message' }}
                    </button>
                </div>

                <button
                    type="button"
                    class="mt-4 inline-flex h-11 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    @click="closeComposer"
                >
                    Back to gallery
                </button>
            </SheetContent>
        </Sheet>

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
                class="fixed inset-0 z-50 bg-black/92"
            >
                <button
                    type="button"
                    class="absolute left-4 top-4 z-10 inline-flex size-11 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white backdrop-blur"
                    aria-label="Close viewer"
                    @click="closeAssetViewer"
                >
                    <X class="size-5" />
                </button>

                <button
                    v-if="selectedAssetCanDelete"
                    type="button"
                    class="absolute right-4 top-4 z-10 inline-flex size-11 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white backdrop-blur disabled:opacity-50"
                    :disabled="deleteAssetForm.processing"
                    aria-label="Delete upload"
                    @click="deleteSelectedAsset"
                >
                    <Trash2 class="size-5" />
                </button>

                <a
                    v-if="selectedAssetCanDownload"
                    :href="selectedAsset.downloadUrl"
                    class="absolute bottom-4 left-4 z-10 inline-flex size-11 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white backdrop-blur"
                    aria-label="Download upload"
                >
                    <Download class="size-5" />
                </a>

                <button
                    v-if="showCaptions"
                    type="button"
                    class="absolute bottom-4 right-4 z-10 inline-flex size-11 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white backdrop-blur"
                    aria-label="Open upload info"
                    @click="isAssetInfoOpen = true"
                >
                    <Info class="size-5" />
                </button>

                <div class="flex h-screen w-screen items-center justify-center overflow-hidden">
                    <img
                        v-if="selectedAsset.kind === 'photo' && selectedAsset.previewUrl"
                        :src="selectedAsset.previewUrl"
                        alt="Selected event photo"
                        class="block max-h-screen max-w-screen object-contain"
                    />
                    <video
                        v-else-if="selectedAsset.kind === 'video' && selectedAsset.previewUrl"
                        :src="selectedAsset.previewUrl"
                        class="block max-h-screen max-w-screen object-contain"
                        controls
                        autoplay
                        playsinline
                    />
                    <div
                        v-else-if="selectedAsset.kind === 'text'"
                        class="w-full max-w-md rounded-[2rem] bg-gradient-to-br from-slate-900 to-slate-700 p-8 shadow-2xl"
                    >
                        <p class="whitespace-pre-wrap text-base leading-relaxed text-white/95">
                            {{ selectedAsset.text ?? 'Text post' }}
                        </p>
                    </div>
                </div>
                <div
                    v-if="
                        showPreviewWatermark &&
                        selectedAsset &&
                        (selectedAsset.kind === 'photo' || selectedAsset.kind === 'video')
                    "
                    class="pointer-events-none absolute inset-x-0 bottom-20 flex justify-center"
                >
                    <span class="rounded-full border border-white/35 bg-black/55 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-white">
                        Preview only - payment required
                    </span>
                </div>
            </div>
        </transition>

        <Sheet
            v-if="showCaptions"
            v-model:open="isAssetInfoOpen"
        >
            <SheetContent
                side="bottom"
                class="max-h-[72vh] rounded-t-[2rem] px-5 pb-8 pt-10"
            >
                <SheetHeader class="px-0 text-left">
                    <SheetTitle>Upload Info</SheetTitle>
                    <SheetDescription>
                        Details for the selected upload.
                    </SheetDescription>
                </SheetHeader>
                <div
                    v-if="selectedAsset"
                    class="mt-5"
                >
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <Table>
                            <TableBody>
                                <TableRow>
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Type
                                    </TableHead>
                                    <TableCell class="text-sm font-medium capitalize text-slate-900">
                                        {{ selectedAsset.kind }}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Status
                                    </TableHead>
                                    <TableCell class="text-sm font-medium capitalize text-slate-900">
                                        {{ selectedAsset.moderationStatus }}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Shared by
                                    </TableHead>
                                    <TableCell class="text-sm font-medium text-slate-900">
                                        {{ selectedAsset.guestName || 'Guest' }}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Date
                                    </TableHead>
                                    <TableCell class="text-sm font-medium text-slate-900">
                                        {{ formatDateTime(selectedAsset.createdAt) }}
                                    </TableCell>
                                </TableRow>
                                <TableRow>
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Size
                                    </TableHead>
                                    <TableCell class="text-sm font-medium text-slate-900">
                                        {{ formatBytes(selectedAsset.sizeBytes) }}
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="selectedAsset.originalFilename">
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        File name
                                    </TableHead>
                                    <TableCell class="break-all text-sm font-medium text-slate-900">
                                        {{ selectedAsset.originalFilename }}
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="selectedAsset.mimeType">
                                    <TableHead class="w-32 bg-slate-50 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                                        Format
                                    </TableHead>
                                    <TableCell class="break-all text-sm font-medium text-slate-900">
                                        {{ selectedAsset.mimeType }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </SheetContent>
        </Sheet>

        <transition
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="menuOpen"
                class="fixed inset-0 z-40 bg-black/45"
                @click="menuOpen = false"
            />
        </transition>

        <transition
            enter-active-class="transition duration-200 ease-out"
            leave-active-class="transition duration-150 ease-in"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="menuOpen && onboardingDone"
                class="fixed right-0 top-0 z-50 flex h-full w-[85%] max-w-sm flex-col border-l border-slate-200 bg-white p-4 shadow-2xl"
            >
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900">Guest menu</p>
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600"
                        @click="menuOpen = false"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <p class="text-xs text-slate-500">
                    Signed in as <span class="font-medium text-slate-700">{{ guestName.trim() }}</span>
                </p>

                <nav class="mt-4 space-y-2">
                    <button
                        v-for="option in intentOptions"
                        :key="`menu-${option.value}`"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-xl border px-3 py-2.5 text-left text-sm transition"
                        :class="
                            (option.value === 'browse_gallery' &&
                                activeView === 'browse_gallery' &&
                                !isComposerOpen) ||
                            (option.value !== 'browse_gallery' &&
                                isComposerOpen &&
                                activeView === option.value)
                                ? 'border-slate-900 bg-slate-900 text-white'
                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                        "
                        :disabled="!option.enabled"
                        @click="setActiveView(option.value)"
                    >
                        <component :is="option.icon" class="size-4 shrink-0" />
                        <span>{{ option.label }}</span>
                    </button>
                </nav>

                <button
                    type="button"
                    class="mt-auto inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    @click="resetGuestOnboarding"
                >
                    Reset guest onboarding
                </button>
            </aside>
        </transition>
    </main>
</template>

<style scoped>
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
