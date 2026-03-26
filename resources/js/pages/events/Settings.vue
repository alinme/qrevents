<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Asterisk,
    Calendar as CalendarIcon,
    Camera,
    Check,
    CreditCard,
    GripVertical,
    Hash,
    Mail,
    Palette,
    Phone,
    Plus,
    ShieldCheck,
    SlidersHorizontal,
    Sparkles,
    User,
    UserPlus,
    Users,
    WandSparkles,
    X,
} from 'lucide-vue-next';
import {
    IconDownload,
    IconEye,
    IconFileText,
    IconPhoto,
    IconUpload,
    IconVideo,
} from '@tabler/icons-vue';
import type { DateValue } from 'reka-ui';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
} from '@/components/ui/input-group';
import {
    NativeSelect,
    NativeSelectOption,
} from '@/components/ui/native-select';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventSettingsPayload = {
    welcomeScreenFields: Array<{
        id: string;
        type: 'text' | 'email' | 'phone' | 'number';
        label: string;
        help_text: string;
        attach_to: 'caption_title' | 'caption_subtitle' | 'file_name' | null;
        required: boolean;
        enabled: boolean;
    }>;
    displayLanguage: 'automatic' | 'ro' | 'en' | 'el';
    hideSideImages: boolean;
    hideQrCode: boolean;
    hideCaption: boolean;
    disableGuestDownload: boolean;
    welcomeScreenEnabled: boolean;
    welcomeScreenTitle: string;
    welcomeScreenSubtitle: string;
    welcomeScreenButtonText: string;
    welcomeScreenFont:
        | 'montserrat'
        | 'poppins'
        | 'playfair_display'
        | 'dm_sans';
    welcomeScreenAnimated: boolean;
    welcomeScreenBackgroundUrl: string | null;
    welcomeScreenCollectName: boolean;
    welcomeScreenCollectEmail: boolean;
    welcomeScreenCollectPhone: boolean;
    albumBackgroundEnabled: boolean;
    albumBackgroundMode: 'rotate' | 'solid' | 'preset' | 'image';
    albumBackgroundColor: string;
    albumBackgroundPresetThemeId: number | null;
    albumBackgroundImageUrl: string | null;
    textPostsBackgroundsEnabled: boolean;
    textPostsBackgroundPalette: string[];
    albumPermission: 'view_upload' | 'view_only' | 'upload_only';
    allowedMediaTypes: string[];
    moderationFilters: string[];
    weddingDetails: {
        partnerOneName: string;
        partnerTwoName: string;
        familyName: string;
        showFamilyName: boolean;
        brideParents: string;
        groomParents: string;
        godparents: string;
    };
};

type WelcomeScreenField = {
    id: string;
    type: 'text' | 'email' | 'phone' | 'number';
    label: string;
    help_text: string;
    attach_to: 'caption_title' | 'caption_subtitle' | 'file_name' | null;
    required: boolean;
    enabled: boolean;
};

type CollaboratorPayload = {
    id: number | string;
    email: string;
    role: 'owner' | 'manager' | 'viewer';
    status: 'active' | 'invited' | 'accepted' | 'pending';
};

type EventPlanFeatures = {
    customizationTier: 'basic' | 'better' | 'advanced';
    allowsBetterCustomization: boolean;
    allowsAdvancedCustomization: boolean;
    allowsDownloadAll: boolean;
    allowsModerationTools: boolean;
    removesAppBranding: boolean;
    uploadWindowDays: number;
};

type EventPayload = {
    id: number;
    name: string;
    type: string;
    planId: number | null;
    planFeatures: EventPlanFeatures;
    eventDate: string | null;
    timezone: string;
    paymentDueAt: string | null;
    paidAt: string | null;
    retentionEndsAt: string | null;
    albumPublic: boolean;
    moderationEnabled: boolean;
    autoModerationEnabled: boolean;
    billing: {
        planId: number | null;
        planName: string;
        planPriceLabel: string;
        planFeatures: EventPlanFeatures;
        isPaid: boolean;
        paymentDueAt: string | null;
        paidAt: string | null;
        graceEndsAt: string | null;
        retentionEndsAt: string | null;
        note: string | null;
        statusCode: 'paid' | 'locked' | 'pending';
        statusLabel: string;
        statusHint: string;
        statusTone: 'emerald' | 'rose' | 'amber';
        isLocked: boolean;
        canManage: boolean;
        canCheckout: boolean;
        checkoutLabel: string | null;
        checkoutHint: string | null;
        storage: {
            limitBytes: number;
            usedBytes: number;
            freeBytes: number;
            usagePercent: number;
            isNearLimit: boolean;
            isOverLimit: boolean;
        };
    };
    branding: {
        primaryColor: string | null;
        accentColor: string | null;
        logoUrl: string | null;
        welcomeMessage: string | null;
    };
    collaborators: CollaboratorPayload[];
    settings?: EventSettingsPayload;
};

type EventTypeOption = {
    value: string;
    label: string;
};

type EventLinks = {
    settings: string;
    settingsUpdate: string;
    billingUpdate: string;
    billingCheckout: string;
    collaboratorsStore: string;
};

type TabId =
    | 'general'
    | 'billing'
    | 'appearance'
    | 'photo_wall'
    | 'moderation'
    | 'collaborators';

type BillingPlanOption = {
    id: number;
    name: string;
    priceLabel: string;
    description: string;
    limitsLabel: string;
};

type TextPostThemeOption = {
    id: number;
    slug: string;
    name: string;
    imageUrl: string;
    backgroundColor: string | null;
    textColor: string;
};

type PageProps = {
    auth?: {
        user?: {
            email?: string;
        };
    };
};

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    eventTypes: EventTypeOption[];
    eventDateMax: string;
    availableBillingPlans: BillingPlanOption[];
    textPostThemes: TextPostThemeOption[];
}>();

const page = usePage<PageProps>();
const checkoutState = computed(() => {
    const query = page.url.split('?')[1] ?? '';

    return new URLSearchParams(query).get('stripe_checkout');
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.settings,
    },
    {
        title: 'Settings',
        href: props.eventLinks.settings,
    },
];

const defaultWelcomeScreenFields: WelcomeScreenField[] = [
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

const defaults: EventSettingsPayload = {
    welcomeScreenFields: defaultWelcomeScreenFields,
    displayLanguage: 'automatic',
    hideSideImages: false,
    hideQrCode: false,
    hideCaption: false,
    disableGuestDownload: false,
    welcomeScreenEnabled: false,
    welcomeScreenTitle: '',
    welcomeScreenSubtitle: '',
    welcomeScreenButtonText: 'Continue',
    welcomeScreenFont: 'montserrat',
    welcomeScreenAnimated: false,
    welcomeScreenBackgroundUrl: null,
    welcomeScreenCollectName: true,
    welcomeScreenCollectEmail: false,
    welcomeScreenCollectPhone: false,
    albumBackgroundEnabled: false,
    albumBackgroundMode: 'rotate',
    albumBackgroundColor: '#0F172A',
    albumBackgroundPresetThemeId: null,
    albumBackgroundImageUrl: null,
    textPostsBackgroundsEnabled: false,
    textPostsBackgroundPalette: ['#1D4ED8', '#0F766E', '#EA580C'],
    albumPermission: props.currentEvent.albumPublic
        ? 'view_upload'
        : 'upload_only',
    allowedMediaTypes: ['photo', 'video'],
    moderationFilters: ['adult', 'nudity', 'violence', 'suggestive'],
    weddingDetails: {
        partnerOneName: '',
        partnerTwoName: '',
        familyName: '',
        showFamilyName: false,
        brideParents: '',
        groomParents: '',
        godparents: '',
    },
};

const currentSettings: EventSettingsPayload = {
    ...defaults,
    ...(props.currentEvent.settings ?? {}),
    weddingDetails: {
        ...defaults.weddingDetails,
        ...(props.currentEvent.settings?.weddingDetails ?? {}),
    },
};

const normalizeWelcomeScreenFields = (value: unknown): WelcomeScreenField[] => {
    if (!Array.isArray(value)) {
        return defaultWelcomeScreenFields.map((field) => ({ ...field }));
    }

    const normalized = value
        .map((field, index): WelcomeScreenField | null => {
            if (
                field === null ||
                typeof field !== 'object' ||
                Array.isArray(field)
            ) {
                return null;
            }

            const source = field as Partial<WelcomeScreenField>;
            const type: WelcomeScreenField['type'] =
                source.type === 'email' ||
                source.type === 'phone' ||
                source.type === 'number'
                    ? source.type
                    : 'text';
            const id =
                typeof source.id === 'string' && source.id.trim().length > 0
                    ? source.id.trim()
                    : `field_${index + 1}`;
            const label =
                typeof source.label === 'string' &&
                source.label.trim().length > 0
                    ? source.label.trim()
                    : 'Field';
            const helpText =
                typeof source.help_text === 'string'
                    ? source.help_text.trim()
                    : '';
            const attachTo =
                source.attach_to === 'caption_title' ||
                source.attach_to === 'caption_subtitle' ||
                source.attach_to === 'file_name'
                    ? source.attach_to
                    : null;
            const required = Boolean(source.required);
            const enabled = Boolean(source.enabled);

            return {
                id,
                type,
                label,
                help_text: helpText,
                attach_to: attachTo,
                required,
                enabled,
            };
        })
        .filter((field): field is WelcomeScreenField => field !== null);

    if (normalized.length === 0) {
        return defaultWelcomeScreenFields.map((field) => ({ ...field }));
    }

    const hasName = normalized.some((field) => field.id === 'name');
    if (!hasName) {
        normalized.unshift({ ...defaultWelcomeScreenFields[0] });
    }

    return normalized.map((field) =>
        field.id === 'name'
            ? {
                  ...field,
                  type: 'text',
                  required: true,
                  enabled: true,
              }
            : field,
    );
};

const activeTab = ref<TabId>('general');
const allTabItems: Array<{ id: TabId; label: string; icon: unknown }> = [
    { id: 'general', label: 'General', icon: SlidersHorizontal },
    { id: 'billing', label: 'Billing', icon: CreditCard },
    { id: 'appearance', label: 'Appearance', icon: Palette },
    { id: 'photo_wall', label: 'Photo Wall', icon: Camera },
    { id: 'moderation', label: 'Moderation', icon: ShieldCheck },
    { id: 'collaborators', label: 'Collaborators', icon: Users },
];

const planFeatures = computed(() => props.currentEvent.planFeatures);
const canEditLogo = computed(() => planFeatures.value.allowsBetterCustomization);
const canEditAdvancedAppearance = computed(() => planFeatures.value.allowsAdvancedCustomization);
const canUseModeration = computed(() => planFeatures.value.allowsModerationTools);
const isWeddingEventType = computed(() => form.type === 'wedding');
const hasWeddingDetails = computed(() => {
    const details = form.wedding_details;

    return [
        details.partner_one_name,
        details.partner_two_name,
        details.family_name,
        details.bride_parents,
        details.groom_parents,
        details.godparents,
    ].some((value) => value.trim().length > 0);
});
const visibleTabItems = computed(() =>
    allTabItems.filter(
        (tab) => tab.id !== 'moderation' || canUseModeration.value,
    ),
);

const requestedTab = computed<TabId | null>(() => {
    const query = page.url.split('?')[1] ?? '';
    const tab = new URLSearchParams(query).get('tab');

    switch (tab) {
        case 'billing':
        case 'appearance':
        case 'photo_wall':
        case 'moderation':
        case 'collaborators':
        case 'general':
            return tab;
        default:
            return null;
    }
});

if (requestedTab.value !== null) {
    activeTab.value = requestedTab.value;
}

watch(requestedTab, (tab) => {
    if (tab === 'moderation' && !canUseModeration.value) {
        activeTab.value = 'billing';
        return;
    }

    if (tab !== null) {
        activeTab.value = tab;
    }
});

watch(canUseModeration, (enabled) => {
    if (!enabled && activeTab.value === 'moderation') {
        activeTab.value = 'billing';
    }
});

const form = useForm({
    type: props.currentEvent.type,
    name: props.currentEvent.name,
    event_date: props.currentEvent.eventDate ?? '',
    timezone: props.currentEvent.timezone,
    album_public: props.currentEvent.albumPublic,
    moderation_enabled: props.currentEvent.moderationEnabled,
    auto_moderation_enabled: props.currentEvent.autoModerationEnabled,
    album_permission: currentSettings.albumPermission,
    allowed_media_types: currentSettings.allowedMediaTypes,
    display_language: currentSettings.displayLanguage,
    hide_side_images: currentSettings.hideSideImages,
    hide_qr_code: currentSettings.hideQrCode,
    hide_caption: currentSettings.hideCaption,
    disable_guest_download: currentSettings.disableGuestDownload,
    welcome_screen_enabled: currentSettings.welcomeScreenEnabled,
    welcome_screen_subtitle: currentSettings.welcomeScreenSubtitle,
    welcome_screen_button_text: currentSettings.welcomeScreenButtonText,
    welcome_screen_font: currentSettings.welcomeScreenFont,
    welcome_screen_animated: currentSettings.welcomeScreenAnimated,
    welcome_screen_fields: normalizeWelcomeScreenFields(
        currentSettings.welcomeScreenFields,
    ),
    welcome_screen_collect_name: currentSettings.welcomeScreenCollectName,
    welcome_screen_collect_email: currentSettings.welcomeScreenCollectEmail,
    welcome_screen_collect_phone: currentSettings.welcomeScreenCollectPhone,
    wedding_details: {
        partner_one_name: currentSettings.weddingDetails.partnerOneName,
        partner_two_name: currentSettings.weddingDetails.partnerTwoName,
        family_name: currentSettings.weddingDetails.familyName,
        show_family_name: currentSettings.weddingDetails.showFamilyName,
        bride_parents: currentSettings.weddingDetails.brideParents,
        groom_parents: currentSettings.weddingDetails.groomParents,
        godparents: currentSettings.weddingDetails.godparents,
    },
    welcome_screen_background_file: null as File | null,
    remove_welcome_screen_background: false,
    album_background_enabled: currentSettings.albumBackgroundEnabled,
    album_background_mode: currentSettings.albumBackgroundMode,
    album_background_color: currentSettings.albumBackgroundColor,
    album_background_preset_theme_id:
        currentSettings.albumBackgroundPresetThemeId,
    album_background_file: null as File | null,
    remove_album_background: false,
    text_posts_backgrounds_enabled: currentSettings.textPostsBackgroundsEnabled,
    text_posts_background_palette: currentSettings.textPostsBackgroundPalette,
    moderation_filters: currentSettings.moderationFilters,
    logo_file: null as File | null,
    remove_logo: false,
    branding: {
        primary_color: props.currentEvent.branding.primaryColor ?? '',
        accent_color: props.currentEvent.branding.accentColor ?? '',
        welcome_message:
            currentSettings.welcomeScreenTitle ||
            props.currentEvent.branding.welcomeMessage ||
            '',
    },
});

const billingForm = useForm({
    plan_id: props.currentEvent.billing.planId?.toString() ?? '',
    is_paid: props.currentEvent.billing.isPaid,
    payment_due_at: props.currentEvent.billing.paymentDueAt?.slice(0, 10) ?? '',
    paid_at: props.currentEvent.billing.paidAt?.slice(0, 16) ?? '',
    billing_note: props.currentEvent.billing.note ?? '',
});

type AlbumPermissionToggle = 'view' | 'upload' | 'download';

const albumPermissionCardOptions = [
    {
        value: 'view' as const,
        title: 'View',
        description: 'Guests can open and browse the album.',
        icon: IconEye,
    },
    {
        value: 'upload' as const,
        title: 'Upload',
        description: 'Guests can send photos, videos, and text wishes.',
        icon: IconUpload,
    },
    {
        value: 'download' as const,
        title: 'Download',
        description: 'Guests can save approved media to their device.',
        icon: IconDownload,
    },
] as const;

const mediaTypeOptions = [
    { value: 'photo', label: 'Photos', icon: IconPhoto },
    { value: 'video', label: 'Videos', icon: IconVideo },
    { value: 'text', label: 'Text wishes', icon: IconFileText },
] as const;

const albumPermissionSelection = computed(() => ({
    view: ['view_upload', 'view_only'].includes(form.album_permission),
    upload: ['view_upload', 'upload_only'].includes(form.album_permission),
    download: !form.disable_guest_download,
}));

const toggleAlbumPermissionSelection = (
    permission: AlbumPermissionToggle,
): void => {
    if (permission === 'download') {
        form.disable_guest_download = !albumPermissionSelection.value.download;
        return;
    }

    const nextView =
        permission === 'view'
            ? !albumPermissionSelection.value.view
            : albumPermissionSelection.value.view;
    const nextUpload =
        permission === 'upload'
            ? !albumPermissionSelection.value.upload
            : albumPermissionSelection.value.upload;

    if (!nextView && !nextUpload) {
        return;
    }

    form.album_permission =
        nextView && nextUpload
            ? 'view_upload'
            : nextView
              ? 'view_only'
              : 'upload_only';
};
const collaboratorRoleOptions = ['manager', 'viewer'] as const;

const moderationFilterOptions = [
    { value: 'adult', label: 'Adult Content' },
    { value: 'nudity', label: 'Nudity' },
    { value: 'violence', label: 'Violence' },
    { value: 'suggestive', label: 'Suggestive' },
    { value: 'hate', label: 'Hate Symbols' },
] as const;

const welcomeScreenFontOptions = [
    { value: 'montserrat', label: 'Montserrat' },
    { value: 'poppins', label: 'Poppins' },
    { value: 'playfair_display', label: 'Playfair Display' },
    { value: 'dm_sans', label: 'DM Sans' },
] as const;

const welcomeFieldTypeOptions = [
    { value: 'text', label: 'Text' },
    { value: 'email', label: 'Email' },
    { value: 'phone', label: 'Phone' },
    { value: 'number', label: 'Number' },
] as const;

const attachToOptions = [
    { value: '', label: 'None' },
    { value: 'caption_title', label: 'Caption Title' },
    { value: 'caption_subtitle', label: 'Caption Subtitle' },
    { value: 'file_name', label: 'File Name' },
] as const;

const isDatePickerOpen = ref(false);
const isTimezonePickerOpen = ref(false);
const isWelcomeScreenDialogOpen = ref(false);
const isAlbumBackgroundSheetOpen = ref(false);
const isTextPostsSheetOpen = ref(false);
const isModerationFiltersSheetOpen = ref(false);
const isCollaboratorInviteSheetOpen = ref(false);
const welcomeScreenSheetTab = ref<'appearance' | 'guest_form'>('appearance');
const isWelcomeFieldSheetOpen = ref(false);
const selectedWelcomeFieldId = ref<string>('name');
const newWelcomeFieldType = ref<WelcomeScreenField['type']>('text');
const selectedEventDate = ref<DateValue>();
const timezoneQuery = ref('');
const logoPreviewUrl = ref<string | null>(null);
const logoFileInputRef = ref<HTMLInputElement | null>(null);
const welcomeScreenBackgroundPreviewUrl = ref<string | null>(null);
const welcomeScreenBackgroundFileInputRef = ref<HTMLInputElement | null>(null);
const lastWelcomeScreenBackgroundFileName = ref('');
const albumBackgroundPreviewUrl = ref<string | null>(null);
const albumBackgroundFileInputRef = ref<HTMLInputElement | null>(null);
const textBackgroundDraftColor = ref('#1D4ED8');
const textBackgroundPaletteError = ref('');
const collaboratorForm = useForm({
    email: '',
    role: 'manager' as 'manager' | 'viewer',
});
const isAutoSaving = ref(false);
const hasPendingAutoSave = ref(false);
const hasPendingFormDataSave = ref(false);
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

watch(selectedEventDate, (value) => {
    if (!value) {
        form.event_date = '';
        return;
    }

    form.event_date = `${value.year}-${String(value.month).padStart(2, '0')}-${String(
        value.day,
    ).padStart(2, '0')}`;
});

watch(
    () => form.logo_file,
    (file) => {
        if (logoPreviewUrl.value !== null) {
            URL.revokeObjectURL(logoPreviewUrl.value);
            logoPreviewUrl.value = null;
        }

        if (file instanceof File) {
            logoPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

watch(
    () => form.welcome_screen_background_file,
    (file) => {
        if (welcomeScreenBackgroundPreviewUrl.value !== null) {
            URL.revokeObjectURL(welcomeScreenBackgroundPreviewUrl.value);
            welcomeScreenBackgroundPreviewUrl.value = null;
        }

        if (file instanceof File) {
            welcomeScreenBackgroundPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

watch(
    () => form.album_background_file,
    (file) => {
        if (albumBackgroundPreviewUrl.value !== null) {
            URL.revokeObjectURL(albumBackgroundPreviewUrl.value);
            albumBackgroundPreviewUrl.value = null;
        }

        if (file instanceof File) {
            albumBackgroundPreviewUrl.value = URL.createObjectURL(file);
        }
    },
);

const timezoneValues = resolveSupportedTimezones();
const filteredTimezoneValues = computed(() => {
    const query = timezoneQuery.value.trim().toLowerCase();
    if (query === '') {
        return timezoneValues;
    }

    return timezoneValues.filter((value) =>
        value.toLowerCase().includes(query),
    );
});

const selectedDateLabel = computed(() => {
    if (selectedEventDate.value) {
        return formatDateLabel(
            selectedEventDate.value.year,
            selectedEventDate.value.month,
            selectedEventDate.value.day,
        );
    }

    if (form.event_date) {
        const [year, month, day] = form.event_date.split('-').map(Number);
        if (
            Number.isInteger(year) &&
            Number.isInteger(month) &&
            Number.isInteger(day)
        ) {
            return formatDateLabel(year, month, day);
        }
    }

    return 'Pick a date';
});

const currentLogoUrl = computed(() => {
    if (logoPreviewUrl.value !== null) {
        return logoPreviewUrl.value;
    }

    if (form.remove_logo) {
        return null;
    }

    return props.currentEvent.branding.logoUrl;
});

const logoFileName = computed(() => {
    if (form.logo_file instanceof File) {
        return form.logo_file.name;
    }

    return 'No file selected';
});

const currentWelcomeScreenBackgroundUrl = computed(() => {
    if (welcomeScreenBackgroundPreviewUrl.value !== null) {
        return welcomeScreenBackgroundPreviewUrl.value;
    }

    if (form.remove_welcome_screen_background) {
        return null;
    }

    return props.currentEvent.settings?.welcomeScreenBackgroundUrl ?? null;
});

const welcomeScreenFileName = computed(() => {
    if (form.welcome_screen_background_file instanceof File) {
        return form.welcome_screen_background_file.name;
    }

    if (lastWelcomeScreenBackgroundFileName.value !== '') {
        return lastWelcomeScreenBackgroundFileName.value;
    }

    if (currentWelcomeScreenBackgroundUrl.value !== null) {
        return 'Background selected';
    }

    return 'No file selected';
});

const selectedAlbumBackgroundPreset = computed(() => {
    if (form.album_background_preset_theme_id === null) {
        return null;
    }

    return (
        props.textPostThemes.find(
            (theme) => theme.id === form.album_background_preset_theme_id,
        ) ?? null
    );
});

const currentAlbumBackgroundUrl = computed(() => {
    if (albumBackgroundPreviewUrl.value !== null) {
        return albumBackgroundPreviewUrl.value;
    }

    if (
        form.album_background_mode === 'preset' &&
        selectedAlbumBackgroundPreset.value !== null
    ) {
        return selectedAlbumBackgroundPreset.value.imageUrl;
    }

    if (form.remove_album_background) {
        return null;
    }

    return props.currentEvent.settings?.albumBackgroundImageUrl ?? null;
});

const albumBackgroundFileName = computed(() => {
    if (form.album_background_file instanceof File) {
        return form.album_background_file.name;
    }

    if (
        form.album_background_mode === 'preset' &&
        selectedAlbumBackgroundPreset.value !== null
    ) {
        return selectedAlbumBackgroundPreset.value.name;
    }

    return 'No file selected';
});

const ownerEmail = computed(
    () => page.props.auth?.user?.email ?? 'owner@example.com',
);
const collaboratorRows = computed(() => {
    if (props.currentEvent.collaborators.length > 0) {
        return props.currentEvent.collaborators;
    }

    return [
        {
            id: `owner-fallback-${props.currentEvent.id}`,
            email: ownerEmail.value,
            role: 'owner' as const,
            status: 'active' as const,
        },
    ];
});

const autoSavePayload = computed(() => ({
    type: form.type,
    name: form.name,
    event_date: form.event_date,
    timezone: form.timezone,
    moderation_enabled: form.moderation_enabled,
    auto_moderation_enabled: form.auto_moderation_enabled,
    album_permission: form.album_permission,
    allowed_media_types: [...form.allowed_media_types],
    display_language: form.display_language,
    hide_side_images: form.hide_side_images,
    hide_qr_code: form.hide_qr_code,
    hide_caption: form.hide_caption,
    disable_guest_download: form.disable_guest_download,
    welcome_screen_enabled: form.welcome_screen_enabled,
    welcome_screen_subtitle: form.welcome_screen_subtitle,
    welcome_screen_button_text: form.welcome_screen_button_text,
    welcome_screen_font: form.welcome_screen_font,
    welcome_screen_animated: form.welcome_screen_animated,
    welcome_screen_fields: [...form.welcome_screen_fields],
    welcome_screen_collect_name: form.welcome_screen_collect_name,
    welcome_screen_collect_email: form.welcome_screen_collect_email,
    welcome_screen_collect_phone: form.welcome_screen_collect_phone,
    wedding_details: { ...form.wedding_details },
    album_background_enabled: form.album_background_enabled,
    album_background_mode: form.album_background_mode,
    album_background_color: form.album_background_color,
    album_background_preset_theme_id: form.album_background_preset_theme_id,
    text_posts_backgrounds_enabled: form.text_posts_backgrounds_enabled,
    text_posts_background_palette: [...form.text_posts_background_palette],
    moderation_filters: [...form.moderation_filters],
    primary_color: form.branding.primary_color,
    accent_color: form.branding.accent_color,
    welcome_message: form.branding.welcome_message,
}));

const saveHintText = computed(() => {
    if (form.processing || isAutoSaving.value) {
        return 'Saving changes...';
    }

    if (form.recentlySuccessful) {
        return 'Changes saved';
    }

    return 'Changes save automatically';
});

const billingStatusLabel = computed(() => {
    return props.currentEvent.billing.statusLabel;
});

const billingStatusClass = computed(() => {
    if (props.currentEvent.billing.statusTone === 'emerald') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (props.currentEvent.billing.statusTone === 'rose') {
        return 'bg-rose-100 text-rose-700';
    }

    return 'bg-amber-100 text-amber-700';
});

const welcomeScreenPreviewTitle = computed(() => {
    const value = form.branding.welcome_message?.trim();
    return value && value.length > 0
        ? value
        : form.name || props.currentEvent.name;
});

const welcomeScreenPreviewSubtitle = computed(() => {
    const value = form.welcome_screen_subtitle?.trim();
    return value && value.length > 0
        ? value
        : 'Share your photos & videos with us!';
});

const welcomeScreenPreviewButtonText = computed(() => {
    const value = form.welcome_screen_button_text?.trim();
    return value && value.length > 0 ? value : 'Continue';
});

const welcomeScreenPreviewFontClass = computed(() => {
    switch (form.welcome_screen_font) {
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

const welcomeScreenConfiguredFields = computed(() =>
    normalizeWelcomeScreenFields(form.welcome_screen_fields).map((field) => ({
        ...field,
        typeLabel:
            field.type === 'email'
                ? 'Email'
                : field.type === 'phone'
                  ? 'Phone'
                  : field.type === 'number'
                    ? 'Number'
                    : 'Text',
        icon:
            field.type === 'email'
                ? Mail
                : field.type === 'phone'
                  ? Phone
                  : field.type === 'number'
                    ? Hash
                    : User,
    })),
);

const welcomeScreenGuestFields = computed(() => {
    const fields = welcomeScreenConfiguredFields.value.filter(
        (field) => field.enabled,
    );
    if (fields.length > 0) {
        return fields;
    }

    return [
        {
            id: 'name',
            type: 'text',
            typeLabel: 'Text',
            label: 'Name',
            help_text: 'Write your name',
            attach_to: null,
            required: true,
            enabled: true,
            icon: User,
        },
    ];
});

const selectedWelcomeField = computed(() =>
    welcomeScreenConfiguredFields.value.find(
        (field) => field.id === selectedWelcomeFieldId.value,
    ),
);

const primaryColorPicker = computed({
    get: (): string =>
        normalizeColorForPicker(form.branding.primary_color, '#3B82F6'),
    set: (value: string) => {
        form.branding.primary_color = value.toUpperCase();
    },
});

const clearAutoSaveTimer = (): void => {
    if (autoSaveTimer === null) {
        return;
    }

    clearTimeout(autoSaveTimer);
    autoSaveTimer = null;
};

const clearTransientFileState = (): void => {
    if (logoPreviewUrl.value !== null) {
        URL.revokeObjectURL(logoPreviewUrl.value);
        logoPreviewUrl.value = null;
    }
    if (welcomeScreenBackgroundPreviewUrl.value !== null) {
        URL.revokeObjectURL(welcomeScreenBackgroundPreviewUrl.value);
        welcomeScreenBackgroundPreviewUrl.value = null;
    }
    if (albumBackgroundPreviewUrl.value !== null) {
        URL.revokeObjectURL(albumBackgroundPreviewUrl.value);
        albumBackgroundPreviewUrl.value = null;
    }

    form.logo_file = null;
    form.remove_logo = false;
    form.welcome_screen_background_file = null;
    form.remove_welcome_screen_background = false;
    form.album_background_file = null;
    form.remove_album_background = false;
};

const saveSettings = (forceFormData = false): void => {
    clearAutoSaveTimer();

    if (form.processing) {
        hasPendingAutoSave.value = true;
        hasPendingFormDataSave.value =
            hasPendingFormDataSave.value || forceFormData;
        return;
    }

    form.album_public = form.album_permission !== 'upload_only';
    isAutoSaving.value = true;
    form.patch(props.eventLinks.settingsUpdate, {
        forceFormData,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            clearTransientFileState();
        },
        onFinish: () => {
            isAutoSaving.value = false;

            if (!hasPendingAutoSave.value) {
                return;
            }

            const shouldUseFormData = hasPendingFormDataSave.value;
            hasPendingAutoSave.value = false;
            hasPendingFormDataSave.value = false;
            queueAutoSave(150, shouldUseFormData);
        },
    });
};

const queueAutoSave = (delay = 700, forceFormData = false): void => {
    clearAutoSaveTimer();

    hasPendingFormDataSave.value =
        hasPendingFormDataSave.value || forceFormData;
    autoSaveTimer = setTimeout(() => {
        autoSaveTimer = null;
        const shouldUseFormData = hasPendingFormDataSave.value;
        hasPendingFormDataSave.value = false;
        saveSettings(shouldUseFormData);
    }, delay);
};

const selectTimezone = (timezone: string): void => {
    form.timezone = timezone;
    timezoneQuery.value = '';
    isTimezonePickerOpen.value = false;
};

const clearEventDate = (): void => {
    selectedEventDate.value = undefined;
    form.event_date = '';
};

const onLogoFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.logo_file = file;
    form.remove_logo = false;
    if (file !== null) {
        queueAutoSave(100, true);
    }
};

const openLogoPicker = (): void => {
    logoFileInputRef.value?.click();
};

const removeCurrentLogo = (): void => {
    form.logo_file = null;
    form.remove_logo = true;
    if (logoFileInputRef.value) {
        logoFileInputRef.value.value = '';
    }
    queueAutoSave(100, true);
};

const openWelcomeScreenBackgroundPicker = (): void => {
    if (!canEditAdvancedAppearance.value) {
        return;
    }

    welcomeScreenBackgroundFileInputRef.value?.click();
};

const onWelcomeScreenBackgroundFileChange = (event: Event): void => {
    if (!canEditAdvancedAppearance.value) {
        return;
    }

    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.welcome_screen_background_file = file;
    form.remove_welcome_screen_background = false;
    lastWelcomeScreenBackgroundFileName.value = file?.name ?? '';
    if (file !== null) {
        queueAutoSave(100, true);
    }
};

const removeWelcomeScreenBackgroundImage = (): void => {
    if (!canEditAdvancedAppearance.value) {
        return;
    }

    form.welcome_screen_background_file = null;
    form.remove_welcome_screen_background = true;
    lastWelcomeScreenBackgroundFileName.value = '';
    if (welcomeScreenBackgroundFileInputRef.value) {
        welcomeScreenBackgroundFileInputRef.value.value = '';
    }
    queueAutoSave(100, true);
};

const syncLegacyWelcomeCollectToggles = (): void => {
    const fields = normalizeWelcomeScreenFields(form.welcome_screen_fields);
    form.welcome_screen_fields = fields;
    form.welcome_screen_collect_name = fields.some(
        (field) => field.id === 'name' && field.enabled,
    );
    form.welcome_screen_collect_email = fields.some(
        (field) => field.type === 'email' && field.enabled,
    );
    form.welcome_screen_collect_phone = fields.some(
        (field) => field.type === 'phone' && field.enabled,
    );
};

const openWelcomeFieldSheet = (fieldId: string): void => {
    selectedWelcomeFieldId.value = fieldId;
    isWelcomeFieldSheetOpen.value = true;
};

const addWelcomeField = (): void => {
    const type = newWelcomeFieldType.value;
    const now = Date.now().toString(36);
    const nextField: WelcomeScreenField = {
        id: `field_${type}_${now}`,
        type,
        label:
            type === 'email'
                ? 'Email'
                : type === 'phone'
                  ? 'Phone'
                  : type === 'number'
                    ? 'Guests Count'
                    : 'Text Field',
        help_text:
            type === 'email'
                ? 'Write your email'
                : type === 'phone'
                  ? 'Write your phone'
                  : type === 'number'
                    ? 'Write a number'
                    : 'Write your answer',
        attach_to: null,
        required: false,
        enabled: true,
    };

    form.welcome_screen_fields = [
        ...normalizeWelcomeScreenFields(form.welcome_screen_fields),
        nextField,
    ];
    syncLegacyWelcomeCollectToggles();
    openWelcomeFieldSheet(nextField.id);
    queueAutoSave(120);
};

const removeWelcomeField = (fieldId: string): void => {
    if (fieldId === 'name') {
        return;
    }

    form.welcome_screen_fields = normalizeWelcomeScreenFields(
        form.welcome_screen_fields,
    ).filter((field) => field.id !== fieldId);
    syncLegacyWelcomeCollectToggles();

    if (selectedWelcomeFieldId.value === fieldId) {
        selectedWelcomeFieldId.value = 'name';
        isWelcomeFieldSheetOpen.value = false;
    }

    queueAutoSave(120);
};

const updateSelectedWelcomeField = (
    patch: Partial<WelcomeScreenField>,
): void => {
    const fields = normalizeWelcomeScreenFields(form.welcome_screen_fields);
    const index = fields.findIndex(
        (field) => field.id === selectedWelcomeFieldId.value,
    );
    if (index === -1) {
        return;
    }

    const currentField = fields[index];
    const nextField: WelcomeScreenField = {
        ...currentField,
        ...patch,
    };
    if (currentField.id === 'name') {
        nextField.type = 'text';
        nextField.required = true;
        nextField.enabled = true;
    }

    fields.splice(index, 1, nextField);
    form.welcome_screen_fields = fields;
    syncLegacyWelcomeCollectToggles();
    queueAutoSave(120);
};

const onSelectedWelcomeFieldAttachToChange = (value: unknown): void => {
    const normalized =
        value === 'caption_title' ||
        value === 'caption_subtitle' ||
        value === 'file_name'
            ? value
            : null;
    updateSelectedWelcomeField({
        attach_to: normalized,
    });
};

const onSelectedWelcomeFieldAttachToSelectChange = (event: Event): void => {
    onSelectedWelcomeFieldAttachToChange(
        (event.target as HTMLSelectElement | null)?.value ?? '',
    );
};

syncLegacyWelcomeCollectToggles();

const onAllowedMediaTypesChange = (value: unknown): void => {
    const values = Array.isArray(value)
        ? value.filter(
              (item): item is string =>
                  typeof item === 'string' &&
                  ['photo', 'video', 'text'].includes(item),
          )
        : [];

    if (values.length === 0) {
        return;
    }

    form.allowed_media_types = values;
};

const onModerationFiltersChange = (value: unknown): void => {
    const values = Array.isArray(value)
        ? value.filter(
              (item): item is string =>
                  typeof item === 'string' &&
                  [
                      'adult',
                      'nudity',
                      'violence',
                      'suggestive',
                      'hate',
                  ].includes(item),
          )
        : [];

    if (values.length === 0) {
        return;
    }

    form.moderation_filters = values;
};

const openAlbumBackgroundPicker = (): void => {
    if (!canEditAdvancedAppearance.value) {
        return;
    }

    albumBackgroundFileInputRef.value?.click();
};

const selectAlbumBackgroundPreset = (themeId: number): void => {
    form.album_background_preset_theme_id = themeId;
    form.album_background_file = null;
    form.remove_album_background = false;
    form.album_background_mode = 'preset';
    if (albumBackgroundFileInputRef.value) {
        albumBackgroundFileInputRef.value.value = '';
    }
    queueAutoSave(100, true);
};

const onAlbumBackgroundFileChange = (event: Event): void => {
    if (!canEditAdvancedAppearance.value) {
        return;
    }

    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;
    form.album_background_file = file;
    form.album_background_preset_theme_id = null;
    form.remove_album_background = false;
    if (file !== null) {
        form.album_background_mode = 'image';
        queueAutoSave(100, true);
    }
};

const removeAlbumBackgroundImage = (): void => {
    form.album_background_file = null;
    form.album_background_preset_theme_id = null;
    form.remove_album_background = true;
    if (albumBackgroundFileInputRef.value) {
        albumBackgroundFileInputRef.value.value = '';
    }
    if (
        form.album_background_mode === 'image' ||
        form.album_background_mode === 'preset'
    ) {
        form.album_background_mode = 'rotate';
    }
    queueAutoSave(100, true);
};

const addTextBackgroundColor = (): void => {
    const normalized = normalizeHexColor(textBackgroundDraftColor.value);
    if (normalized === null) {
        textBackgroundPaletteError.value = 'Use a valid hex color.';
        return;
    }

    if (form.text_posts_background_palette.includes(normalized)) {
        textBackgroundPaletteError.value = 'Color already added.';
        return;
    }

    if (form.text_posts_background_palette.length >= 8) {
        textBackgroundPaletteError.value = 'Maximum 8 colors.';
        return;
    }

    form.text_posts_background_palette = [
        ...form.text_posts_background_palette,
        normalized,
    ];
    textBackgroundPaletteError.value = '';
};

const removeTextBackgroundColor = (color: string): void => {
    if (form.text_posts_background_palette.length <= 1) {
        textBackgroundPaletteError.value =
            'Keep at least one background color.';
        return;
    }

    form.text_posts_background_palette =
        form.text_posts_background_palette.filter((item) => item !== color);
    textBackgroundPaletteError.value = '';
};

const clearTimezoneQuery = (): void => {
    timezoneQuery.value = '';
};

const formatBillingDate = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
};

const formatBillingDateTime = (value: string | null): string => {
    if (!value) {
        return 'Not set';
    }

    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
};

const formatBytes = (bytes: number): string => {
    if (bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** exponent;

    return `${value.toFixed(value >= 10 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
};

const submit = (): void => {
    saveSettings(true);
};

const submitBilling = (): void => {
    billingForm.patch(props.eventLinks.billingUpdate, {
        preserveScroll: true,
        preserveState: true,
    });
};

const submitCollaboratorInvite = (): void => {
    collaboratorForm.post(props.eventLinks.collaboratorsStore, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            collaboratorForm.reset('email');
            collaboratorForm.role = 'manager';
            collaboratorForm.clearErrors();
            isCollaboratorInviteSheetOpen.value = false;
        },
    });
};

const onCollaboratorRoleChange = (value: unknown): void => {
    if (
        typeof value === 'string' &&
        (collaboratorRoleOptions as readonly string[]).includes(value)
    ) {
        collaboratorForm.role =
            value as (typeof collaboratorRoleOptions)[number];
    }
};

watch(
    autoSavePayload,
    () => {
        queueAutoSave();
    },
    { deep: true },
);

watch(isWelcomeScreenDialogOpen, (isOpen) => {
    if (!isOpen) {
        return;
    }

    welcomeScreenSheetTab.value = 'appearance';
    isWelcomeFieldSheetOpen.value = false;
    selectedWelcomeFieldId.value = 'name';
    syncLegacyWelcomeCollectToggles();
});

onBeforeUnmount(() => {
    clearAutoSaveTimer();
    clearTransientFileState();
});

const formatDateLabel = (year: number, month: number, day: number): string => {
    return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date(year, month - 1, day));
};

const normalizeColorForPicker = (value: string, fallback: string): string => {
    const normalized = normalizeHexColor(value);
    return normalized ?? fallback;
};

const normalizeHexColor = (value: string): string | null => {
    const trimmed = value.trim();
    if (!/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(trimmed)) {
        return null;
    }

    if (trimmed.length === 7) {
        return trimmed.toUpperCase();
    }

    const [r, g, b] = trimmed.slice(1).split('');
    return `#${r}${r}${g}${g}${b}${b}`.toUpperCase();
};

function resolveSupportedTimezones(): string[] {
    const fallback = [
        'Europe/Bucharest',
        'Europe/London',
        'Europe/Paris',
        'Europe/Berlin',
        'America/New_York',
        'America/Los_Angeles',
        'UTC',
    ];

    try {
        const intl = Intl as unknown as {
            supportedValuesOf?: (key: string) => string[];
        };
        const values = intl.supportedValuesOf?.('timeZone');
        if (Array.isArray(values) && values.length > 0) {
            return values;
        }
    } catch {
        return fallback;
    }

    return fallback;
}
</script>

<template>
    <Head title="Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <form @submit.prevent="submit">
                <Card class="overflow-hidden">
                    <CardHeader class="border-b px-4 py-0">
                        <nav class="flex flex-wrap gap-2 pt-2">
                            <button
                                v-for="tab in visibleTabItems"
                                :key="tab.id"
                                type="button"
                                class="inline-flex items-center gap-2 border-b-2 px-2 py-3 text-sm font-medium transition-colors"
                                :class="
                                    activeTab === tab.id
                                        ? 'border-primary text-foreground'
                                        : 'border-transparent text-muted-foreground hover:text-foreground'
                                "
                                @click="activeTab = tab.id"
                            >
                                <component :is="tab.icon" class="size-4" />
                                {{ tab.label }}
                            </button>
                        </nav>
                    </CardHeader>

                    <CardContent class="space-y-8 p-4 md:p-6">
                        <section
                            v-show="activeTab === 'general'"
                            class="space-y-6"
                        >
                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Event Name
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        It will be shown across the app and to
                                        your guests.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        class="h-11"
                                        placeholder="My Wedding"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Event Date
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Set when your event is scheduled to
                                        start.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Popover v-model:open="isDatePickerOpen">
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="h-11 w-full justify-start text-left font-normal"
                                            >
                                                <CalendarIcon
                                                    class="mr-2 size-4"
                                                />
                                                {{ selectedDateLabel }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-auto p-0"
                                            align="start"
                                        >
                                            <Calendar
                                                v-model="selectedEventDate"
                                                :weekday-format="'short'"
                                                initial-focus
                                                @update:model-value="
                                                    isDatePickerOpen = false
                                                "
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <Button
                                        v-if="form.event_date"
                                        type="button"
                                        variant="ghost"
                                        class="h-auto px-3 text-sm"
                                        @click="clearEventDate"
                                    >
                                        Clear date
                                    </Button>
                                    <InputError
                                        :message="form.errors.event_date"
                                    />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Timezone
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Search and select the timezone for this
                                        event.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Popover
                                        v-model:open="isTimezonePickerOpen"
                                    >
                                        <PopoverTrigger as-child>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                class="h-11 w-full justify-start text-left font-normal"
                                            >
                                                {{
                                                    form.timezone ||
                                                    'Select timezone'
                                                }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-[340px] p-2"
                                            align="start"
                                        >
                                            <InputGroup class="mb-2 h-11">
                                                <InputGroupInput
                                                    v-model="timezoneQuery"
                                                    placeholder="Search timezone..."
                                                />
                                                <InputGroupAddon
                                                    align="inline-end"
                                                >
                                                    <InputGroupButton
                                                        v-if="
                                                            timezoneQuery.length >
                                                            0
                                                        "
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        class="h-8"
                                                        @click="
                                                            clearTimezoneQuery
                                                        "
                                                    >
                                                        <X class="size-4" />
                                                    </InputGroupButton>
                                                </InputGroupAddon>
                                            </InputGroup>
                                            <div
                                                class="max-h-60 overflow-y-auto rounded-md border"
                                            >
                                                <button
                                                    v-for="timezoneValue in filteredTimezoneValues"
                                                    :key="timezoneValue"
                                                    type="button"
                                                    class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-muted"
                                                    @click="
                                                        selectTimezone(
                                                            timezoneValue,
                                                        )
                                                    "
                                                >
                                                    <span>{{
                                                        timezoneValue
                                                    }}</span>
                                                    <Check
                                                        v-if="
                                                            timezoneValue ===
                                                            form.timezone
                                                        "
                                                        class="size-4"
                                                    />
                                                </button>
                                                <p
                                                    v-if="
                                                        filteredTimezoneValues.length ===
                                                        0
                                                    "
                                                    class="px-3 py-4 text-sm text-muted-foreground"
                                                >
                                                    No timezone found.
                                                </p>
                                            </div>
                                        </PopoverContent>
                                    </Popover>
                                    <InputError
                                        :message="form.errors.timezone"
                                    />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Event Type
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        We adjust the experience according to
                                        your event type.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <NativeSelect
                                        v-model="form.type"
                                        class="h-11"
                                        name="type"
                                    >
                                        <NativeSelectOption
                                            v-for="typeOption in eventTypes"
                                            :key="typeOption.value"
                                            :value="typeOption.value"
                                        >
                                            {{ typeOption.label }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                    <InputError :message="form.errors.type" />
                                </div>
                            </div>

                            <div
                                v-if="isWeddingEventType"
                                class="grid gap-4 rounded-2xl border border-rose-200/80 bg-rose-50/70 p-4 md:grid-cols-[minmax(0,1fr)_420px] md:items-start"
                            >
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Wedding Details
                                        </h3>
                                        <Badge
                                            variant="secondary"
                                            class="border-rose-200 bg-white text-rose-700"
                                        >
                                            Optional
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Keep this light for now. These names
                                        will help us prepare wedding
                                        invitations later, and you can refine
                                        them anytime.
                                    </p>
                                    <p
                                        v-if="!hasWeddingDetails"
                                        class="text-sm text-rose-700/80"
                                    >
                                        Start with the couple names only if you
                                        want. Parents and godparents can be
                                        added later.
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label
                                                class="text-sm font-medium text-slate-700"
                                                for="partner-one-name"
                                            >
                                                Partner one
                                            </label>
                                            <Input
                                                id="partner-one-name"
                                                v-model="
                                                    form.wedding_details.partner_one_name
                                                "
                                                class="h-11 bg-white"
                                                placeholder="Ana"
                                            />
                                            <InputError
                                                :message="
                                                    form.errors[
                                                        'wedding_details.partner_one_name'
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-sm font-medium text-slate-700"
                                                for="partner-two-name"
                                            >
                                                Partner two
                                            </label>
                                            <Input
                                                id="partner-two-name"
                                                v-model="
                                                    form.wedding_details.partner_two_name
                                                "
                                                class="h-11 bg-white"
                                                placeholder="Andrei"
                                            />
                                            <InputError
                                                :message="
                                                    form.errors[
                                                        'wedding_details.partner_two_name'
                                                    ]
                                                "
                                            />
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-medium text-slate-700"
                                            for="family-name"
                                        >
                                            Shared last name
                                        </label>
                                        <Input
                                            id="family-name"
                                            v-model="
                                                form.wedding_details.family_name
                                            "
                                            class="h-11 bg-white"
                                            placeholder="Miller"
                                        />
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Keep the shared last name here and
                                            choose later if the invitation
                                            should show it.
                                        </p>
                                        <label
                                            class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3"
                                            for="show-family-name"
                                        >
                                            <Switch
                                                id="show-family-name"
                                                v-model="
                                                    form.wedding_details.show_family_name
                                                "
                                            />
                                            <span class="min-w-0">
                                                <span
                                                    class="block text-sm font-medium text-slate-700"
                                                >
                                                    Show last name on the
                                                    invitation
                                                </span>
                                                <span
                                                    class="block text-xs text-muted-foreground"
                                                >
                                                    Example: Jessica &amp;
                                                    Simon Miller
                                                </span>
                                            </span>
                                        </label>
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'wedding_details.family_name'
                                                ]
                                            "
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'wedding_details.show_family_name'
                                                ]
                                            "
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-medium text-slate-700"
                                            for="bride-parents"
                                        >
                                            Bride's parents
                                        </label>
                                        <Input
                                            id="bride-parents"
                                            v-model="
                                                form.wedding_details.bride_parents
                                            "
                                            class="h-11 bg-white"
                                            placeholder="Maria and Ion Popescu"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'wedding_details.bride_parents'
                                                ]
                                            "
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-medium text-slate-700"
                                            for="groom-parents"
                                        >
                                            Groom's parents
                                        </label>
                                        <Input
                                            id="groom-parents"
                                            v-model="
                                                form.wedding_details.groom_parents
                                            "
                                            class="h-11 bg-white"
                                            placeholder="Elena and Mihai Ionescu"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'wedding_details.groom_parents'
                                                ]
                                            "
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <label
                                            class="text-sm font-medium text-slate-700"
                                            for="godparents"
                                        >
                                            Godparents
                                        </label>
                                        <Input
                                            id="godparents"
                                            v-model="
                                                form.wedding_details.godparents
                                            "
                                            class="h-11 bg-white"
                                            placeholder="Raluca and Stefan Marin"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'wedding_details.godparents'
                                                ]
                                            "
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Digital Album Permissions
                                        </h3>
                                        <Badge variant="secondary">
                                            <Sparkles />
                                            Pro
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Control how guests interact with your
                                        digital album.
                                    </p>
                                </div>
                                <div class="grid gap-3 md:grid-cols-3">
                                    <button
                                        v-for="permission in albumPermissionCardOptions"
                                        :key="permission.value"
                                        type="button"
                                        class="rounded-xl border p-4 text-left transition"
                                        :class="
                                            albumPermissionSelection[permission.value]
                                                ? 'border-primary bg-primary/10 text-primary shadow-sm'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                        "
                                        @click="
                                            toggleAlbumPermissionSelection(
                                                permission.value,
                                            )
                                        "
                                    >
                                        <component
                                            :is="permission.icon"
                                            class="mb-3 size-5"
                                            :class="
                                                albumPermissionSelection[permission.value]
                                                    ? 'text-primary'
                                                    : 'text-slate-400'
                                            "
                                        />
                                        <p class="text-sm font-semibold">
                                            {{ permission.title }}
                                        </p>
                                        <p
                                            class="mt-2 text-sm text-muted-foreground"
                                        >
                                            {{ permission.description }}
                                        </p>
                                    </button>
                                </div>
                                <InputError
                                    :message="form.errors.album_permission"
                                />
                            </div>

                            <div
                                id="guest-upload-types"
                                class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Guest Uploads
                                        </h3>
                                        <Badge variant="secondary">
                                            <Sparkles />
                                            Plus
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Choose which guest upload actions stay
                                        available in the public album,
                                        including text wishes.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <ToggleGroup
                                        type="multiple"
                                        variant="outline"
                                        :model-value="form.allowed_media_types"
                                        class="flex w-full flex-wrap"
                                        @update:model-value="
                                            onAllowedMediaTypesChange
                                        "
                                    >
                                        <ToggleGroupItem
                                            v-for="mediaType in mediaTypeOptions"
                                            :key="mediaType.value"
                                            :value="mediaType.value"
                                            class="h-12 flex-1 justify-center gap-2 border-slate-200 bg-white text-slate-600 data-[state=on]:border-primary data-[state=on]:bg-primary/10 data-[state=on]:text-primary"
                                        >
                                            <component
                                                :is="mediaType.icon"
                                                class="size-4.5"
                                            />
                                            {{ mediaType.label }}
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                    <p class="text-xs text-muted-foreground">
                                        At least one media type must stay
                                        enabled.
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors.allowed_media_types
                                        "
                                    />
                                </div>
                            </div>
                        </section>

                        <section
                            v-show="activeTab === 'billing'"
                            class="space-y-6"
                        >
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div
                                    class="flex flex-wrap items-start justify-between gap-3"
                                >
                                    <div class="space-y-1">
                                        <p
                                            class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase"
                                        >
                                            Billing overview
                                        </p>
                                        <h3
                                            class="text-lg font-semibold text-slate-900"
                                        >
                                            {{
                                                props.currentEvent.billing
                                                    .planName
                                            }}
                                        </h3>
                                        <p class="text-sm text-slate-600">
                                            {{
                                                props.currentEvent.billing
                                                    .planPriceLabel
                                            }}
                                        </p>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <Badge variant="secondary">
                                                {{ props.currentEvent.billing.planFeatures.customizationTier }}
                                                customization
                                            </Badge>
                                            <Badge
                                                :variant="
                                                    props.currentEvent.billing.planFeatures.allowsDownloadAll
                                                        ? 'secondary'
                                                        : 'outline'
                                                "
                                            >
                                                {{
                                                    props.currentEvent.billing.planFeatures.allowsDownloadAll
                                                        ? 'ZIP export included'
                                                        : 'ZIP export locked'
                                                }}
                                            </Badge>
                                            <Badge
                                                :variant="
                                                    props.currentEvent.billing.planFeatures.allowsModerationTools
                                                        ? 'secondary'
                                                        : 'outline'
                                                "
                                            >
                                                {{
                                                    props.currentEvent.billing.planFeatures.allowsModerationTools
                                                        ? 'Moderation included'
                                                        : 'Moderation locked'
                                                }}
                                            </Badge>
                                        </div>
                                    </div>
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="billingStatusClass"
                                    >
                                        {{ billingStatusLabel }}
                                    </span>
                                </div>
                                <p class="mt-3 text-sm text-slate-600">
                                    {{ props.currentEvent.billing.statusHint }}
                                </p>

                                <div
                                    class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-6"
                                >
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Payment due
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBillingDate(
                                                    props.currentEvent.billing
                                                        .paymentDueAt,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Paid at
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBillingDateTime(
                                                    props.currentEvent.billing
                                                        .paidAt,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Grace ends
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBillingDate(
                                                    props.currentEvent.billing
                                                        .graceEndsAt,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Retention ends
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBillingDate(
                                                    props.currentEvent.billing
                                                        .retentionEndsAt,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Storage used
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBytes(
                                                    props.currentEvent.billing
                                                        .storage.usedBytes,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3"
                                    >
                                        <p
                                            class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                        >
                                            Storage free
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium text-slate-900"
                                        >
                                            {{
                                                formatBytes(
                                                    props.currentEvent.billing
                                                        .storage.freeBytes,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 rounded-xl border border-slate-200 bg-white p-4"
                                >
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-2"
                                    >
                                        <div>
                                            <p
                                                class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"
                                            >
                                                Plan storage quota
                                            </p>
                                            <p
                                                class="mt-1 text-sm text-slate-700"
                                            >
                                                {{
                                                    formatBytes(
                                                        props.currentEvent
                                                            .billing.storage
                                                            .usedBytes,
                                                    )
                                                }}
                                                used of
                                                {{
                                                    formatBytes(
                                                        props.currentEvent
                                                            .billing.storage
                                                            .limitBytes,
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                props.currentEvent.billing
                                                    .storage.isOverLimit
                                                    ? 'bg-rose-100 text-rose-700'
                                                    : props.currentEvent.billing
                                                            .storage.isNearLimit
                                                      ? 'bg-amber-100 text-amber-700'
                                                      : 'bg-emerald-100 text-emerald-700'
                                            "
                                        >
                                            {{
                                                props.currentEvent.billing
                                                    .storage.usagePercent
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-200"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all"
                                            :class="
                                                props.currentEvent.billing
                                                    .storage.isOverLimit
                                                    ? 'bg-rose-500'
                                                    : props.currentEvent.billing
                                                            .storage.isNearLimit
                                                      ? 'bg-amber-500'
                                                      : 'bg-emerald-500'
                                            "
                                            :style="{
                                                width: `${Math.min(props.currentEvent.billing.storage.usagePercent, 100)}%`,
                                            }"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="props.currentEvent.billing.canManage"
                                class="space-y-6"
                            >
                                <div
                                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                                >
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            Billing Plan
                                        </h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Choose the commercial plan for this
                                            event. Event storage and upload
                                            limits update when you save.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <NativeSelect
                                            v-model="billingForm.plan_id"
                                            class="h-11"
                                        >
                                            <NativeSelectOption
                                                v-for="plan in availableBillingPlans"
                                                :key="plan.id"
                                                :value="String(plan.id)"
                                            >
                                                {{ plan.name }} ·
                                                {{ plan.priceLabel }}
                                            </NativeSelectOption>
                                        </NativeSelect>
                                        <p
                                            v-if="
                                                availableBillingPlans.some(
                                                    (plan) =>
                                                        String(plan.id) ===
                                                        String(
                                                            billingForm.plan_id,
                                                        ),
                                                )
                                            "
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                availableBillingPlans.find(
                                                    (plan) =>
                                                        String(plan.id) ===
                                                        String(
                                                            billingForm.plan_id,
                                                        ),
                                                )?.limitsLabel
                                            }}
                                        </p>
                                        <InputError
                                            :message="
                                                billingForm.errors.plan_id
                                            "
                                        />
                                    </div>
                                </div>

                                <div
                                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                                >
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            Mark As Paid
                                        </h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Paid events stay available through
                                            the retention window instead of
                                            locking at the payment due date.
                                        </p>
                                    </div>
                                    <div class="flex justify-end">
                                        <Switch v-model="billingForm.is_paid" />
                                    </div>
                                </div>

                                <div
                                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                                >
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            Payment Due Date
                                        </h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Used to determine when unpaid events
                                            become locked.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Input
                                            v-model="billingForm.payment_due_at"
                                            type="date"
                                            class="h-11"
                                        />
                                        <InputError
                                            :message="
                                                billingForm.errors
                                                    .payment_due_at
                                            "
                                        />
                                    </div>
                                </div>

                                <div
                                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                                >
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            Paid At
                                        </h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Optional manual override. Leave
                                            blank to use the current time when
                                            marking this event paid.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Input
                                            v-model="billingForm.paid_at"
                                            type="datetime-local"
                                            class="h-11"
                                            :disabled="!billingForm.is_paid"
                                        />
                                        <InputError
                                            :message="
                                                billingForm.errors.paid_at
                                            "
                                        />
                                    </div>
                                </div>

                                <div
                                    class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                                >
                                    <div>
                                        <h3 class="text-sm font-semibold">
                                            Admin Billing Note
                                        </h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Internal note for invoice status,
                                            payment method, or next follow-up.
                                            Customers do not see this.
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Textarea
                                            v-model="billingForm.billing_note"
                                            rows="4"
                                            placeholder="Example: Paid via bank transfer, invoice copy still pending."
                                        />
                                        <InputError
                                            :message="
                                                billingForm.errors.billing_note
                                            "
                                        />
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <Button
                                        type="button"
                                        class="h-11 min-w-[160px]"
                                        :disabled="billingForm.processing"
                                        @click="submitBilling"
                                    >
                                        Save Billing
                                    </Button>
                                </div>
                            </div>

                            <div
                                v-else
                                class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
                            >
                                <div class="space-y-4">
                                    <div
                                        v-if="checkoutState === 'success'"
                                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900"
                                    >
                                        Stripe checkout completed. Payment
                                        confirmation should appear here as soon
                                        as the webhook is processed.
                                    </div>
                                    <div
                                        v-else-if="
                                            checkoutState === 'cancelled'
                                        "
                                        class="rounded-xl border border-zinc-200 bg-white px-4 py-3 text-zinc-700"
                                    >
                                        Checkout was cancelled. Your event is
                                        still unpaid.
                                    </div>

                                    <template
                                        v-if="
                                            props.currentEvent.billing
                                                .canCheckout
                                        "
                                    >
                                        <div class="space-y-2">
                                            <h3 class="text-sm font-semibold">
                                                Pay online
                                            </h3>
                                            <p class="text-sm leading-6">
                                                {{
                                                    props.currentEvent.billing
                                                        .checkoutHint
                                                }}
                                            </p>
                                        </div>

                                        <Button as-child class="h-11">
                                            <Link
                                                :href="
                                                    props.eventLinks
                                                        .billingCheckout
                                                "
                                                method="post"
                                                as="button"
                                            >
                                                <CreditCard class="size-4" />
                                                {{
                                                    props.currentEvent.billing
                                                        .checkoutLabel
                                                }}
                                            </Link>
                                        </Button>
                                    </template>
                                    <p v-else>
                                        Billing for this event is managed by QR
                                        Events admin. If you need plan or
                                        payment changes, contact the platform
                                        administrator.
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="
                                    !props.currentEvent.billing.canManage &&
                                    props.currentEvent.billing.note
                                "
                                class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    Admin note
                                </p>
                                <p class="mt-2 whitespace-pre-line">
                                    {{ props.currentEvent.billing.note }}
                                </p>
                            </div>
                        </section>

                        <section
                            v-show="activeTab === 'appearance'"
                            class="space-y-6"
                        >
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                <p class="font-semibold text-slate-900">
                                    Current plan: {{ props.currentEvent.billing.planName }}
                                </p>
                                <p class="mt-2">
                                    {{
                                        canEditAdvancedAppearance
                                            ? 'Advanced branding is active for this event.'
                                            : canEditLogo
                                              ? 'This plan includes logo-level customization. Colors and custom backgrounds stay on Pro.'
                                              : 'This plan keeps branding simple. Upgrade to Plus for logos or Pro for advanced appearance controls.'
                                    }}
                                </p>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Event Logo
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Brand your event with a logo shown
                                        through public pages.
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <input
                                        ref="logoFileInputRef"
                                        type="file"
                                        accept="image/*"
                                        class="sr-only"
                                        @change="onLogoFileChange"
                                    />
                                    <div
                                        v-if="currentLogoUrl"
                                        class="relative inline-block"
                                    >
                                        <img
                                            :src="currentLogoUrl"
                                            alt="Event logo"
                                            class="h-24 w-24 rounded-xl border object-cover"
                                        />
                                        <button
                                            type="button"
                                            class="absolute -top-2 -right-2 rounded-full border bg-background p-1 shadow"
                                            :disabled="!canEditLogo"
                                            @click="removeCurrentLogo"
                                        >
                                            <X class="size-4" />
                                        </button>
                                    </div>
                                    <InputGroup v-else class="h-11">
                                        <InputGroupInput
                                            :model-value="logoFileName"
                                            readonly
                                        />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupButton
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-8"
                                                :disabled="!canEditLogo"
                                                @click="openLogoPicker"
                                            >
                                                Browse
                                            </InputGroupButton>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <InputError
                                        :message="form.errors.logo_file"
                                    />
                                    <p
                                        v-if="!canEditLogo"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Logo uploads unlock on Plus and Pro.
                                    </p>
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Display Language
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Localize the experience according to
                                        your audience. With Automatic, each
                                        guest sees the app in browser language
                                        when available.
                                    </p>
                                </div>
                                <NativeSelect
                                    v-model="form.display_language"
                                    class="h-11"
                                >
                                    <NativeSelectOption value="automatic">
                                        Automatic
                                    </NativeSelectOption>
                                    <NativeSelectOption value="ro">
                                        Romanian
                                    </NativeSelectOption>
                                    <NativeSelectOption value="en">
                                        English
                                    </NativeSelectOption>
                                    <NativeSelectOption value="el">
                                        Greek
                                    </NativeSelectOption>
                                </NativeSelect>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-start"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Theme Color
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Choose a theme color for your brand.
                                        We'll use that across public pages.
                                    </p>
                                </div>
                                <InputGroup class="h-11">
                                    <InputGroupAddon
                                        align="inline-start"
                                        class="pr-1"
                                    >
                                        <input
                                            v-model="primaryColorPicker"
                                            type="color"
                                            class="h-7 w-7 rounded border-0 bg-transparent p-0"
                                        />
                                    </InputGroupAddon>
                                    <InputGroupInput
                                        v-model="form.branding.primary_color"
                                        class="font-mono"
                                        placeholder="#3B82F6"
                                    />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupButton
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            class="h-8"
                                            @click="
                                                form.branding.primary_color = ''
                                            "
                                        >
                                            Reset
                                        </InputGroupButton>
                                    </InputGroupAddon>
                                </InputGroup>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Welcome Screen
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Set up an intro screen for guests' first
                                        visit, with a form to collect their
                                        info.
                                    </p>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-11 min-w-[120px]"
                                        @click="
                                            isWelcomeScreenDialogOpen = true
                                        "
                                    >
                                        Configure
                                    </Button>

                                    <Switch
                                        v-model="form.welcome_screen_enabled"
                                    />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Album Background
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Choose a preset backdrop, a solid
                                        color, or let the album rotate through
                                        uploaded photos.
                                    </p>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-11 min-w-[120px]"
                                        @click="
                                            isAlbumBackgroundSheetOpen = true
                                        "
                                    >
                                        Customize
                                    </Button>

                                    <Switch
                                        v-model="form.album_background_enabled"
                                    />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Text Posts Backgrounds
                                        </h3>
                                        <Badge variant="secondary">
                                            <Sparkles />
                                            Plus
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Customize backgrounds for uploading text
                                        posts.
                                    </p>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-11 min-w-[120px]"
                                        @click="isTextPostsSheetOpen = true"
                                    >
                                        Customize
                                    </Button>
                                    <Switch
                                        v-model="
                                            form.text_posts_backgrounds_enabled
                                        "
                                    />
                                </div>
                            </div>
                        </section>

                        <section
                            v-show="activeTab === 'photo_wall'"
                            class="space-y-6"
                        >
                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Hide Side Images
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Turn on to hide the moving images from
                                        the sides.
                                    </p>
                                </div>
                                <div class="flex justify-end">
                                    <Switch v-model="form.hide_side_images" />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Hide QR Code
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Turn on to remove the QR code.
                                    </p>
                                </div>
                                <div class="flex justify-end">
                                    <Switch v-model="form.hide_qr_code" />
                                </div>
                            </div>

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <h3 class="text-sm font-semibold">
                                        Hide Caption
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Toggle this option to hide media
                                        captions.
                                    </p>
                                </div>
                                <div class="flex justify-end">
                                    <Switch v-model="form.hide_caption" />
                                </div>
                            </div>
                        </section>

                        <section
                            v-show="activeTab === 'moderation'"
                            class="space-y-6"
                        >
                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Manually Approve Guest Uploads
                                        </h3>
                                        <Badge variant="secondary">
                                            <Sparkles />
                                            Pro
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Manually approve uploads before they
                                        appear on Photo Wall and Digital Album.
                                        <span class="font-medium">
                                            More Info</span
                                        >
                                    </p>
                                </div>
                                <div class="flex justify-end">
                                    <Switch v-model="form.moderation_enabled" />
                                </div>
                            </div>
                            <InputError
                                :message="form.errors.moderation_enabled"
                            />

                            <div
                                class="grid gap-4 md:grid-cols-[minmax(0,1fr)_320px] md:items-center"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold">
                                            Automatic Content Filter
                                        </h3>
                                        <Badge variant="secondary">
                                            <Sparkles />
                                            Pro
                                        </Badge>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        Automatically detect and block adult,
                                        explicit, violent, or suggestive
                                        content.
                                    </p>
                                </div>
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="h-11"
                                        @click="
                                            isModerationFiltersSheetOpen = true
                                        "
                                    >
                                        Manage Filters
                                    </Button>
                                    <Switch
                                        v-model="form.auto_moderation_enabled"
                                    />
                                </div>
                            </div>
                            <InputError
                                :message="form.errors.auto_moderation_enabled"
                            />

                        </section>

                        <section
                            v-show="activeTab === 'collaborators'"
                            class="space-y-6"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <p class="text-sm text-muted-foreground">
                                    Invite members who can access and manage
                                    this event.
                                </p>
                                <Button
                                    type="button"
                                    class="h-11"
                                    @click="
                                        isCollaboratorInviteSheetOpen = true
                                    "
                                >
                                    Invite Collaborator
                                </Button>
                            </div>

                            <div class="overflow-hidden rounded-xl border">
                                <div
                                    class="grid grid-cols-[2fr_1fr_1fr] border-b bg-muted/20 px-4 py-3 text-sm font-semibold"
                                >
                                    <p>Collaborator</p>
                                    <p>Role</p>
                                    <p>Status</p>
                                </div>
                                <div
                                    v-for="collaborator in collaboratorRows"
                                    :key="collaborator.id"
                                    class="grid grid-cols-[2fr_1fr_1fr] px-4 py-4 text-sm"
                                >
                                    <p>{{ collaborator.email }}</p>
                                    <p class="capitalize">
                                        {{ collaborator.role }}
                                    </p>
                                    <div>
                                        <Badge
                                            :variant="
                                                ['accepted', 'active'].includes(
                                                    collaborator.status,
                                                )
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                            class="capitalize"
                                        >
                                            {{
                                                ['accepted', 'active'].includes(
                                                    collaborator.status,
                                                )
                                                    ? 'active'
                                                    : 'invited'
                                            }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </CardContent>
                </Card>

                <Dialog v-model:open="isWelcomeScreenDialogOpen">
                    <DialogContent
                        :show-close-button="true"
                        class="h-[94vh] w-[calc(100vw-1rem)] max-w-[calc(100vw-1rem)] overflow-hidden p-0 sm:w-[calc(100vw-2rem)] sm:max-w-[1320px]"
                    >
                        <div class="flex h-full min-h-0 flex-col">
                            <div
                                class="grid h-full min-h-0 flex-1 gap-6 overflow-hidden p-6 md:grid-cols-[minmax(0,1fr)_320px]"
                            >
                                <div
                                    class="h-full min-h-0 overflow-hidden pr-1 md:flex md:flex-col"
                                >
                                    <div class="border-b pb-4">
                                        <h2 class="text-2xl font-semibold">
                                            Welcome Screen Settings
                                        </h2>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            Set up a welcome screen that appears
                                            once for first-time guests.
                                        </p>
                                    </div>
                                    <div class="pt-3 pb-1">
                                        <div class="flex items-center gap-6">
                                            <button
                                                type="button"
                                                class="border-b-2 px-0 pb-3 text-sm font-semibold transition-colors"
                                                :class="
                                                    welcomeScreenSheetTab ===
                                                    'appearance'
                                                        ? 'border-primary text-foreground'
                                                        : 'border-transparent text-muted-foreground hover:text-foreground'
                                                "
                                                @click="
                                                    welcomeScreenSheetTab =
                                                        'appearance'
                                                "
                                            >
                                                Appearance
                                            </button>
                                            <button
                                                type="button"
                                                class="border-b-2 px-0 pb-3 text-sm font-semibold transition-colors"
                                                :class="
                                                    welcomeScreenSheetTab ===
                                                    'guest_form'
                                                        ? 'border-primary text-foreground'
                                                        : 'border-transparent text-muted-foreground hover:text-foreground'
                                                "
                                                @click="
                                                    welcomeScreenSheetTab =
                                                        'guest_form'
                                                "
                                            >
                                                Guest Form
                                            </button>
                                        </div>
                                    </div>

                                    <div
                                        class="min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain pt-4"
                                    >
                                        <input
                                            ref="welcomeScreenBackgroundFileInputRef"
                                            type="file"
                                            accept="image/*"
                                            class="sr-only"
                                            @change="
                                                onWelcomeScreenBackgroundFileChange
                                            "
                                        />

                                        <template
                                            v-if="
                                                welcomeScreenSheetTab ===
                                                'appearance'
                                            "
                                        >
                                            <div class="space-y-2">
                                                <h4
                                                    class="text-sm font-semibold"
                                                >
                                                    Title
                                                </h4>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    Enter the main title for
                                                    your welcome screen.
                                                </p>
                                                <div
                                                    class="grid gap-2 lg:grid-cols-[minmax(0,1fr)_210px]"
                                                >
                                                    <Input
                                                        v-model="
                                                            form.branding
                                                                .welcome_message
                                                        "
                                                        class="h-11"
                                                        placeholder="My Wedding"
                                                    />
                                                    <NativeSelect
                                                        v-model="
                                                            form.welcome_screen_font
                                                        "
                                                        class="h-11"
                                                    >
                                                        <NativeSelectOption
                                                            v-for="fontOption in welcomeScreenFontOptions"
                                                            :key="
                                                                fontOption.value
                                                            "
                                                            :value="
                                                                fontOption.value
                                                            "
                                                        >
                                                            {{
                                                                fontOption.label
                                                            }}
                                                        </NativeSelectOption>
                                                    </NativeSelect>
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors[
                                                            'branding.welcome_message'
                                                        ]
                                                    "
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .welcome_screen_font
                                                    "
                                                />
                                            </div>

                                            <div class="space-y-2">
                                                <h4
                                                    class="text-sm font-semibold"
                                                >
                                                    Description
                                                </h4>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    Add intro text below the
                                                    title.
                                                </p>
                                                <Textarea
                                                    v-model="
                                                        form.welcome_screen_subtitle
                                                    "
                                                    class="min-h-24"
                                                    maxlength="220"
                                                    placeholder="Share your photos & videos with us!"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .welcome_screen_subtitle
                                                    "
                                                />
                                            </div>

                                            <div class="space-y-2">
                                                <h4
                                                    class="text-sm font-semibold"
                                                >
                                                    Button
                                                </h4>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    Set the submit button text.
                                                </p>
                                                <Input
                                                    v-model="
                                                        form.welcome_screen_button_text
                                                    "
                                                    class="h-11 max-w-[280px]"
                                                    maxlength="40"
                                                    placeholder="Continue"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .welcome_screen_button_text
                                                    "
                                                />
                                            </div>

                                            <div class="space-y-2">
                                                <h4
                                                    class="text-sm font-semibold"
                                                >
                                                    Background
                                                </h4>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    Set a background for the
                                                    welcome screen.
                                                </p>
                                                <p
                                                    v-if="
                                                        !canEditAdvancedAppearance
                                                    "
                                                    class="text-xs font-medium text-amber-700"
                                                >
                                                    Welcome screen backgrounds
                                                    are available on Pro.
                                                </p>
                                                <div
                                                    class="grid gap-3 sm:grid-cols-[1fr_auto]"
                                                >
                                                    <InputGroup class="h-11">
                                                        <InputGroupInput
                                                            :model-value="
                                                                welcomeScreenFileName
                                                            "
                                                            readonly
                                                        />
                                                        <InputGroupAddon
                                                            align="inline-end"
                                                        >
                                                            <InputGroupButton
                                                                type="button"
                                                                variant="outline"
                                                                size="sm"
                                                                class="h-8"
                                                                :disabled="
                                                                    !canEditAdvancedAppearance
                                                                "
                                                                @click="
                                                                    openWelcomeScreenBackgroundPicker
                                                                "
                                                            >
                                                                Change
                                                            </InputGroupButton>
                                                        </InputGroupAddon>
                                                    </InputGroup>
                                                    <Button
                                                        v-if="
                                                            currentWelcomeScreenBackgroundUrl
                                                        "
                                                        type="button"
                                                        variant="outline"
                                                        class="h-11"
                                                        :disabled="
                                                            !canEditAdvancedAppearance
                                                        "
                                                        @click="
                                                            removeWelcomeScreenBackgroundImage
                                                        "
                                                    >
                                                        Remove
                                                    </Button>
                                                </div>
                                                <div
                                                    class="flex items-center justify-between rounded-lg border px-3 py-2"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <WandSparkles
                                                            class="size-4"
                                                        />
                                                        <p class="text-sm">
                                                            Animated
                                                        </p>
                                                    </div>
                                                    <Switch
                                                        v-model="
                                                            form.welcome_screen_animated
                                                        "
                                                    />
                                                </div>
                                                <InputError
                                                    :message="
                                                        form.errors
                                                            .welcome_screen_background_file
                                                    "
                                                />
                                            </div>
                                        </template>

                                        <template v-else>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Create a form to collect guest
                                                information before they proceed
                                                to your album.
                                            </p>

                                            <div
                                                class="space-y-2 rounded-xl border p-2"
                                            >
                                                <button
                                                    v-for="field in welcomeScreenConfiguredFields"
                                                    :key="field.id"
                                                    type="button"
                                                    class="flex w-full items-center justify-between gap-3 rounded-lg border px-3 py-2 text-left transition hover:bg-muted/40"
                                                    @click="
                                                        openWelcomeFieldSheet(
                                                            field.id,
                                                        )
                                                    "
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <GripVertical
                                                            class="size-4 text-muted-foreground"
                                                        />
                                                        <component
                                                            :is="field.icon"
                                                            class="size-4 text-muted-foreground"
                                                        />
                                                        <p
                                                            class="text-sm font-medium"
                                                        >
                                                            {{ field.label }}
                                                        </p>
                                                        <span
                                                            class="text-xs text-muted-foreground"
                                                            >({{
                                                                field.typeLabel
                                                            }})</span
                                                        >
                                                        <Asterisk
                                                            v-if="
                                                                field.required
                                                            "
                                                            class="size-3 text-rose-500"
                                                        />
                                                    </div>
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <Badge
                                                            :variant="
                                                                field.enabled
                                                                    ? 'secondary'
                                                                    : 'outline'
                                                            "
                                                            class="text-xs"
                                                        >
                                                            {{
                                                                field.enabled
                                                                    ? 'Enabled'
                                                                    : 'Hidden'
                                                            }}
                                                        </Badge>
                                                    </div>
                                                </button>
                                            </div>

                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <NativeSelect
                                                    v-model="
                                                        newWelcomeFieldType
                                                    "
                                                    class="h-11 w-[200px]"
                                                >
                                                    <NativeSelectOption
                                                        v-for="typeOption in welcomeFieldTypeOptions"
                                                        :key="typeOption.value"
                                                        :value="
                                                            typeOption.value
                                                        "
                                                    >
                                                        {{ typeOption.label }}
                                                    </NativeSelectOption>
                                                </NativeSelect>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="h-11"
                                                    @click="addWelcomeField"
                                                >
                                                    <Plus class="mr-2 size-4" />
                                                    Add New Field
                                                </Button>
                                            </div>

                                            <div class="space-y-2">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <h4
                                                        class="text-sm font-semibold"
                                                    >
                                                        Export Guest Submissions
                                                    </h4>
                                                    <Badge variant="secondary">
                                                        <Sparkles />
                                                        Pro
                                                    </Badge>
                                                </div>
                                                <p
                                                    class="text-sm text-muted-foreground"
                                                >
                                                    Download all guest form
                                                    responses as a CSV file.
                                                </p>
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        class="h-11"
                                                        disabled
                                                    >
                                                        Download
                                                    </Button>
                                                    <span
                                                        class="text-sm text-muted-foreground"
                                                    >
                                                        0 submissions
                                                    </span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div
                                    class="hidden min-h-0 md:flex md:items-center md:justify-center md:overflow-y-auto md:py-3"
                                >
                                    <div
                                        class="relative w-[262px] rounded-[1.9rem] border-[3px] border-slate-900 bg-black p-2 shadow-2xl"
                                    >
                                        <div
                                            class="relative h-[500px] overflow-hidden rounded-[1.5rem]"
                                        >
                                            <img
                                                v-if="
                                                    currentWelcomeScreenBackgroundUrl
                                                "
                                                :src="
                                                    currentWelcomeScreenBackgroundUrl
                                                "
                                                alt="Welcome preview background"
                                                class="absolute inset-0 h-full w-full object-cover"
                                                :class="
                                                    form.welcome_screen_animated
                                                        ? 'welcome-bg-animate-slow'
                                                        : ''
                                                "
                                            />
                                            <div
                                                v-else
                                                class="absolute inset-0 bg-gradient-to-br from-slate-500 via-slate-700 to-slate-900"
                                                :class="
                                                    form.welcome_screen_animated
                                                        ? 'welcome-bg-animate-slow'
                                                        : ''
                                                "
                                            />
                                            <div
                                                class="absolute inset-0 bg-gradient-to-b from-black/32 via-black/46 to-black/58"
                                            />
                                            <div
                                                class="relative flex h-full flex-col px-2.5 pt-5 pb-3 text-white"
                                            >
                                                <div
                                                    class="mt-auto space-y-1.5 rounded-2xl p-2.5 shadow-[0_12px_32px_rgba(0,0,0,0.28)] backdrop-blur-xl"
                                                    :class="[
                                                        'border border-white/25 bg-white/10',
                                                        welcomeScreenPreviewFontClass,
                                                    ]"
                                                >
                                                    <div
                                                        class="flex justify-center pb-0.5"
                                                    >
                                                        <div
                                                            class="size-12 overflow-hidden rounded-full border border-white/75 bg-white/25 shadow-md"
                                                        >
                                                            <img
                                                                v-if="
                                                                    currentLogoUrl
                                                                "
                                                                :src="
                                                                    currentLogoUrl
                                                                "
                                                                alt="Event logo"
                                                                class="h-full w-full object-cover"
                                                            />
                                                            <div
                                                                v-else
                                                                class="flex h-full w-full items-center justify-center text-base font-semibold text-white"
                                                            >
                                                                {{
                                                                    (
                                                                        form.name ||
                                                                        currentEvent.name
                                                                    )
                                                                        .charAt(
                                                                            0,
                                                                        )
                                                                        .toUpperCase()
                                                                }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5
                                                        class="text-center text-xl leading-snug font-semibold"
                                                    >
                                                        {{
                                                            welcomeScreenPreviewTitle
                                                        }}
                                                    </h5>
                                                    <p
                                                        class="text-center text-xs leading-relaxed"
                                                        :class="'text-white/90'"
                                                    >
                                                        {{
                                                            welcomeScreenPreviewSubtitle
                                                        }}
                                                    </p>
                                                    <div class="space-y-1.5">
                                                        <div
                                                            v-for="field in welcomeScreenGuestFields"
                                                            :key="`preview-${field.id}`"
                                                            v-show="
                                                                field.enabled
                                                            "
                                                            class="rounded-lg bg-white/18 px-2.5 py-1.5 backdrop-blur-sm"
                                                        >
                                                            <p
                                                                class="text-[11px] font-semibold text-white/95"
                                                            >
                                                                {{
                                                                    field.label
                                                                }}
                                                            </p>
                                                            <p
                                                                class="text-xs text-white/75"
                                                            >
                                                                {{
                                                                    field.help_text ||
                                                                    'Write your answer'
                                                                }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="h-9 w-full rounded-lg bg-slate-100 text-sm font-semibold text-slate-900 shadow-md"
                                                    >
                                                        {{
                                                            welcomeScreenPreviewButtonText
                                                        }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <DialogFooter class="border-t px-6 py-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10"
                                    @click="isWelcomeScreenDialogOpen = false"
                                >
                                    Done
                                </Button>
                            </DialogFooter>
                        </div>

                        <Sheet v-model:open="isWelcomeFieldSheetOpen">
                            <SheetContent
                                side="right"
                                class="w-full p-0 sm:max-w-none md:w-1/2"
                            >
                                <div class="flex h-full flex-col">
                                    <SheetHeader
                                        class="border-b px-6 py-5 text-left"
                                    >
                                        <SheetTitle
                                            class="text-base font-semibold"
                                        >
                                            Field Settings
                                        </SheetTitle>
                                        <SheetDescription>
                                            Configure this guest form input.
                                        </SheetDescription>
                                    </SheetHeader>
                                    <div
                                        class="flex-1 space-y-4 overflow-y-auto p-6"
                                    >
                                        <div class="space-y-2">
                                            <label
                                                class="text-sm font-semibold"
                                            >
                                                Label*
                                            </label>
                                            <Input
                                                :model-value="
                                                    selectedWelcomeField?.label ??
                                                    ''
                                                "
                                                class="h-11"
                                                @update:model-value="
                                                    (value) =>
                                                        updateSelectedWelcomeField(
                                                            {
                                                                label: String(
                                                                    value,
                                                                ),
                                                            },
                                                        )
                                                "
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <label
                                                class="text-sm font-semibold"
                                            >
                                                Help text
                                            </label>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                Appears as a placeholder to
                                                guide guests.
                                            </p>
                                            <Input
                                                :model-value="
                                                    selectedWelcomeField?.help_text ??
                                                    ''
                                                "
                                                class="h-11"
                                                placeholder="Write your name"
                                                @update:model-value="
                                                    (value) =>
                                                        updateSelectedWelcomeField(
                                                            {
                                                                help_text:
                                                                    String(
                                                                        value,
                                                                    ),
                                                            },
                                                        )
                                                "
                                            />
                                        </div>

                                        <div class="space-y-2">
                                            <label
                                                class="text-sm font-semibold"
                                            >
                                                Attach To
                                            </label>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                Link the input from this field
                                                to properties of future uploads
                                                by the guest.
                                                <button
                                                    type="button"
                                                    class="font-medium underline underline-offset-2"
                                                >
                                                    More Info
                                                </button>
                                            </p>
                                            <NativeSelect
                                                :model-value="
                                                    selectedWelcomeField?.attach_to ??
                                                    ''
                                                "
                                                class="h-11"
                                                @change="
                                                    onSelectedWelcomeFieldAttachToSelectChange
                                                "
                                            >
                                                <NativeSelectOption
                                                    v-for="attachOption in attachToOptions"
                                                    :key="attachOption.value"
                                                    :value="attachOption.value"
                                                >
                                                    {{ attachOption.label }}
                                                </NativeSelectOption>
                                            </NativeSelect>
                                        </div>

                                        <div
                                            class="rounded-lg border px-3 py-3"
                                        >
                                            <div
                                                class="flex items-start justify-between gap-2"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm font-semibold"
                                                    >
                                                        Required
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        Mark this field as
                                                        mandatory for guests to
                                                        complete before
                                                        proceeding.
                                                    </p>
                                                </div>
                                                <Switch
                                                    :model-value="
                                                        selectedWelcomeField?.required ??
                                                        false
                                                    "
                                                    :disabled="
                                                        selectedWelcomeField?.id ===
                                                        'name'
                                                    "
                                                    @update:model-value="
                                                        (checked) =>
                                                            updateSelectedWelcomeField(
                                                                {
                                                                    required:
                                                                        checked,
                                                                },
                                                            )
                                                    "
                                                />
                                            </div>
                                        </div>

                                        <div
                                            class="rounded-lg border px-3 py-3"
                                        >
                                            <div
                                                class="flex items-start justify-between gap-2"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm font-semibold"
                                                    >
                                                        Enabled
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        Show or hide this input
                                                        on the welcome screen.
                                                    </p>
                                                </div>
                                                <Switch
                                                    :model-value="
                                                        selectedWelcomeField?.enabled ??
                                                        false
                                                    "
                                                    :disabled="
                                                        selectedWelcomeField?.id ===
                                                        'name'
                                                    "
                                                    @update:model-value="
                                                        (checked) =>
                                                            updateSelectedWelcomeField(
                                                                {
                                                                    enabled:
                                                                        checked,
                                                                },
                                                            )
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <SheetFooter class="border-t px-6 py-4">
                                        <div
                                            class="flex w-full items-center justify-between"
                                        >
                                            <Button
                                                type="button"
                                                variant="outline"
                                                :disabled="
                                                    selectedWelcomeField?.id ===
                                                    'name'
                                                "
                                                @click="
                                                    selectedWelcomeField &&
                                                    removeWelcomeField(
                                                        selectedWelcomeField.id,
                                                    )
                                                "
                                            >
                                                Remove Field
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                @click="
                                                    isWelcomeFieldSheetOpen = false
                                                "
                                            >
                                                Done
                                            </Button>
                                        </div>
                                    </SheetFooter>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </DialogContent>
                </Dialog>

                <Sheet v-model:open="isAlbumBackgroundSheetOpen">
                    <SheetContent
                        side="right"
                        class="w-full p-0 sm:max-w-none md:w-1/2"
                    >
                        <div class="flex h-full flex-col">
                            <SheetHeader class="border-b px-6 py-5 text-left">
                                <SheetTitle class="text-base font-semibold">
                                    Album Background
                                </SheetTitle>
                                <SheetDescription>
                                    Choose how the album background is rendered
                                    on public pages.
                                </SheetDescription>
                            </SheetHeader>
                            <div class="flex-1 space-y-5 overflow-y-auto p-6">
                                <input
                                    ref="albumBackgroundFileInputRef"
                                    type="file"
                                    accept="image/*"
                                    class="sr-only"
                                    @change="onAlbumBackgroundFileChange"
                                />

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Background Mode
                                    </h4>
                                    <NativeSelect
                                        v-model="form.album_background_mode"
                                        class="h-11"
                                    >
                                        <NativeSelectOption value="rotate">
                                            Rotate Event Photos
                                        </NativeSelectOption>
                                        <NativeSelectOption value="solid">
                                            Solid Color
                                        </NativeSelectOption>
                                        <NativeSelectOption
                                            value="preset"
                                        >
                                            Preset Artwork
                                        </NativeSelectOption>
                                        <NativeSelectOption
                                            value="image"
                                            :disabled="
                                                !canEditAdvancedAppearance
                                            "
                                        >
                                            Custom Image
                                        </NativeSelectOption>
                                    </NativeSelect>
                                    <p class="text-xs text-muted-foreground">
                                        Preset artwork is available on every
                                        plan. Custom uploads stay on Pro.
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors.album_background_mode
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Solid Color
                                    </h4>
                                    <InputGroup class="h-11">
                                        <InputGroupAddon
                                            align="inline-start"
                                            class="pr-1"
                                        >
                                            <input
                                                v-model="
                                                    form.album_background_color
                                                "
                                                type="color"
                                                class="h-7 w-7 rounded border-0 bg-transparent p-0"
                                            />
                                        </InputGroupAddon>
                                        <InputGroupInput
                                            v-model="
                                                form.album_background_color
                                            "
                                            class="font-mono"
                                            placeholder="#0F172A"
                                        />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupButton
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="h-8"
                                                @click="
                                                    form.album_background_color =
                                                        '#0F172A'
                                                "
                                            >
                                                Reset
                                            </InputGroupButton>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <InputError
                                        :message="
                                            form.errors.album_background_color
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <h4 class="text-sm font-semibold">
                                            Preset Artwork
                                        </h4>
                                        <p class="text-xs text-muted-foreground">
                                            Reuses the same backgrounds guests
                                            already see in text wishes.
                                        </p>
                                    </div>
                                    <div
                                        v-if="props.textPostThemes.length > 0"
                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                                    >
                                        <button
                                            v-for="theme in props.textPostThemes"
                                            :key="theme.id"
                                            type="button"
                                            class="group relative overflow-hidden rounded-2xl border-2 text-left transition"
                                            :class="
                                                form.album_background_preset_theme_id === theme.id &&
                                                form.album_background_mode === 'preset'
                                                    ? 'border-primary shadow-sm'
                                                    : 'border-border'
                                            "
                                            @click="
                                                selectAlbumBackgroundPreset(
                                                    theme.id,
                                                )
                                            "
                                        >
                                            <img
                                                :src="theme.imageUrl"
                                                :alt="theme.name"
                                                class="h-28 w-full object-cover"
                                            />
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent" />
                                            <div class="absolute inset-x-0 bottom-0 p-3">
                                                <p class="text-sm font-semibold text-white">
                                                    {{ theme.name }}
                                                </p>
                                            </div>
                                        </button>
                                    </div>
                                    <p
                                        v-else
                                        class="rounded-xl border border-dashed px-4 py-3 text-sm text-muted-foreground"
                                    >
                                        Add text wish backgrounds first and
                                        they will appear here automatically.
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors
                                                .album_background_preset_theme_id
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Custom Image
                                    </h4>
                                    <div
                                        v-if="
                                            currentAlbumBackgroundUrl &&
                                            form.album_background_mode ===
                                                'image'
                                        "
                                        class="relative overflow-hidden rounded-xl border"
                                    >
                                        <img
                                            :src="currentAlbumBackgroundUrl"
                                            alt="Album background preview"
                                            class="h-36 w-full object-cover"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            class="absolute top-3 right-3"
                                            :disabled="
                                                !canEditAdvancedAppearance
                                            "
                                            @click="removeAlbumBackgroundImage"
                                        >
                                            Remove
                                        </Button>
                                    </div>
                                    <InputGroup v-else class="h-11">
                                        <InputGroupInput
                                            :model-value="
                                                albumBackgroundFileName
                                            "
                                            readonly
                                        />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupButton
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-8"
                                                :disabled="
                                                    !canEditAdvancedAppearance
                                                "
                                                @click="
                                                    openAlbumBackgroundPicker
                                                "
                                            >
                                                Browse
                                            </InputGroupButton>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <InputError
                                        :message="
                                            form.errors.album_background_file
                                        "
                                    />
                                    <p
                                        v-if="!canEditAdvancedAppearance"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Upgrade to Pro if you want to upload
                                        your own album background image.
                                    </p>
                                </div>
                            </div>
                            <SheetFooter class="border-t px-6 py-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10"
                                    @click="isAlbumBackgroundSheetOpen = false"
                                >
                                    Done
                                </Button>
                            </SheetFooter>
                        </div>
                    </SheetContent>
                </Sheet>

                <Sheet v-model:open="isTextPostsSheetOpen">
                    <SheetContent
                        side="right"
                        class="w-full p-0 sm:max-w-none md:w-1/2"
                    >
                        <div class="flex h-full flex-col">
                            <SheetHeader class="border-b px-6 py-5 text-left">
                                <SheetTitle class="text-base font-semibold">
                                    Text Post Backgrounds
                                </SheetTitle>
                                <SheetDescription>
                                    Create the palette available when guests
                                    post text cards.
                                </SheetDescription>
                            </SheetHeader>
                            <div class="flex-1 space-y-5 overflow-y-auto p-6">
                                <div
                                    class="flex items-center justify-between rounded-lg border px-3 py-2"
                                >
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Enable Text Post Backgrounds
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Allows guests to choose from your
                                            custom palette.
                                        </p>
                                    </div>
                                    <Switch
                                        v-model="
                                            form.text_posts_backgrounds_enabled
                                        "
                                    />
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Palette
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <div
                                            v-for="color in form.text_posts_background_palette"
                                            :key="color"
                                            class="inline-flex items-center gap-2 rounded-full border px-2 py-1"
                                        >
                                            <span
                                                class="size-5 rounded-full border"
                                                :style="{
                                                    backgroundColor: color,
                                                }"
                                            />
                                            <span class="text-xs font-medium">{{
                                                color
                                            }}</span>
                                            <button
                                                type="button"
                                                class="text-muted-foreground hover:text-foreground"
                                                @click="
                                                    removeTextBackgroundColor(
                                                        color,
                                                    )
                                                "
                                            >
                                                <X class="size-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Add Color
                                    </h4>
                                    <InputGroup class="h-11">
                                        <InputGroupAddon
                                            align="inline-start"
                                            class="pr-1"
                                        >
                                            <input
                                                v-model="
                                                    textBackgroundDraftColor
                                                "
                                                type="color"
                                                class="h-7 w-7 rounded border-0 bg-transparent p-0"
                                            />
                                        </InputGroupAddon>
                                        <InputGroupInput
                                            v-model="textBackgroundDraftColor"
                                            class="font-mono"
                                            placeholder="#1D4ED8"
                                        />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupButton
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                class="h-8"
                                                @click="addTextBackgroundColor"
                                            >
                                                Add
                                            </InputGroupButton>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <p
                                        v-if="textBackgroundPaletteError"
                                        class="text-xs text-destructive"
                                    >
                                        {{ textBackgroundPaletteError }}
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors
                                                .text_posts_background_palette
                                        "
                                    />
                                </div>
                            </div>
                            <SheetFooter class="border-t px-6 py-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10"
                                    @click="isTextPostsSheetOpen = false"
                                >
                                    Done
                                </Button>
                            </SheetFooter>
                        </div>
                    </SheetContent>
                </Sheet>

                <Sheet v-model:open="isModerationFiltersSheetOpen">
                    <SheetContent
                        side="right"
                        class="w-full p-0 sm:max-w-none md:w-1/2"
                    >
                        <div class="flex h-full flex-col">
                            <SheetHeader class="border-b px-6 py-5 text-left">
                                <SheetTitle class="text-base font-semibold">
                                    Automatic Filter Rules
                                </SheetTitle>
                                <SheetDescription>
                                    Choose which categories are flagged by
                                    automatic moderation.
                                </SheetDescription>
                            </SheetHeader>
                            <div class="flex-1 space-y-5 overflow-y-auto p-6">
                                <div
                                    class="flex items-center justify-between rounded-lg border px-3 py-2"
                                >
                                    <div>
                                        <p class="text-sm font-semibold">
                                            Automatic Filter
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            If disabled, uploads skip automatic
                                            checks.
                                        </p>
                                    </div>
                                    <Switch
                                        v-model="form.auto_moderation_enabled"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Active Categories
                                    </h4>
                                    <ToggleGroup
                                        type="multiple"
                                        variant="outline"
                                        :model-value="form.moderation_filters"
                                        class="flex w-full flex-wrap"
                                        @update:model-value="
                                            onModerationFiltersChange
                                        "
                                    >
                                        <ToggleGroupItem
                                            v-for="filter in moderationFilterOptions"
                                            :key="filter.value"
                                            :value="filter.value"
                                            class="h-11"
                                        >
                                            {{ filter.label }}
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                    <p class="text-xs text-muted-foreground">
                                        Keep at least one category selected.
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors.moderation_filters
                                        "
                                    />
                                </div>
                            </div>
                            <SheetFooter class="border-t px-6 py-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10"
                                    @click="
                                        isModerationFiltersSheetOpen = false
                                    "
                                >
                                    Done
                                </Button>
                            </SheetFooter>
                        </div>
                    </SheetContent>
                </Sheet>

                <Sheet v-model:open="isCollaboratorInviteSheetOpen">
                    <SheetContent
                        side="right"
                        class="w-full p-0 sm:max-w-none md:w-1/2"
                    >
                        <div class="flex h-full flex-col">
                            <SheetHeader class="border-b px-6 py-5 text-left">
                                <SheetTitle class="text-base font-semibold">
                                    Invite Collaborator
                                </SheetTitle>
                                <SheetDescription>
                                    Prepare invitations for team members who
                                    help manage this event.
                                </SheetDescription>
                            </SheetHeader>
                            <div class="flex-1 space-y-5 overflow-y-auto p-6">
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">
                                        Email Address
                                    </h4>
                                    <InputGroup class="h-11">
                                        <InputGroupAddon
                                            align="inline-start"
                                            class="pl-3"
                                        >
                                            <UserPlus class="size-4" />
                                        </InputGroupAddon>
                                        <InputGroupInput
                                            v-model="collaboratorForm.email"
                                            type="email"
                                            placeholder="teammate@example.com"
                                        />
                                    </InputGroup>
                                    <InputError
                                        :message="collaboratorForm.errors.email"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold">Role</h4>
                                    <ToggleGroup
                                        type="single"
                                        variant="outline"
                                        :model-value="collaboratorForm.role"
                                        class="flex w-full flex-wrap"
                                        @update:model-value="
                                            onCollaboratorRoleChange
                                        "
                                    >
                                        <ToggleGroupItem
                                            value="manager"
                                            class="h-11 flex-1"
                                        >
                                            Manager
                                        </ToggleGroupItem>
                                        <ToggleGroupItem
                                            value="viewer"
                                            class="h-11 flex-1"
                                        >
                                            Viewer
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                    <InputError
                                        :message="collaboratorForm.errors.role"
                                    />
                                </div>

                                <p class="text-sm text-muted-foreground">
                                    Invitation sending will be enabled when the
                                    collaborator backend module is ready.
                                </p>
                            </div>
                            <SheetFooter class="border-t px-6 py-4">
                                <Button
                                    type="button"
                                    class="h-10"
                                    :disabled="collaboratorForm.processing"
                                    @click="submitCollaboratorInvite"
                                >
                                    {{
                                        collaboratorForm.processing
                                            ? 'Inviting...'
                                            : 'Send Invite'
                                    }}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-10"
                                    @click="
                                        isCollaboratorInviteSheetOpen = false
                                    "
                                >
                                    Close
                                </Button>
                            </SheetFooter>
                        </div>
                    </SheetContent>
                </Sheet>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <p class="text-sm text-muted-foreground">
                        {{ saveHintText }}
                    </p>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-11"
                    >
                        Save now
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
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
