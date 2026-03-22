<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Images, LoaderCircle, QrCode } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Separator } from '@/components/ui/separator';
import { useTranslations } from '@/composables/useTranslations';

type WallBranding = {
    primaryColor: string | null;
    accentColor: string | null;
    logoUrl: string | null;
    welcomeMessage: string | null;
    hideSideImages: boolean;
    hideQrCode: boolean;
    hideCaption: boolean;
    captionTheme: 'dark' | 'light';
    albumBackgroundEnabled: boolean;
    albumBackgroundMode: 'rotate' | 'solid' | 'image';
    albumBackgroundColor: string;
    albumBackgroundImageUrl: string | null;
};

type WallAsset = {
    id: number;
    kind: 'photo' | 'video' | 'text';
    thumbnailUrl: string | null;
    previewUrl: string | null;
    videoProcessing: boolean;
    text: string | null;
    textThemeImageUrl: string | null;
    textThemeBackgroundColor: string | null;
    textThemeTextColor: string | null;
    guestName: string | null;
    captionTitle: string | null;
    captionSubtitle: string | null;
    createdAt: string | null;
    durationSeconds: number | null;
};

const props = defineProps<{
    eventName: string;
    status: string;
    albumUrl: string;
    albumQrDataUrl: string;
    showPoweredBy: boolean;
    branding: WallBranding;
    assets: WallAsset[];
}>();

const { locale, t } = useTranslations();

const activeIndex = ref(0);
const autoplayId = ref<number | null>(null);
const wallAssets = ref<WallAsset[]>([...props.assets]);
const wallUpdatePollId = ref<number | null>(null);
const isWallRefreshing = ref(false);
const wallLiveStatus = ref<'live' | 'updating'>('live');

const displayAssets = computed<WallAsset[]>(() =>
    wallAssets.value.filter((asset) => {
        if (asset.kind === 'text') {
            return true;
        }

        return asset.previewUrl !== null || asset.videoProcessing;
    }),
);

const currentAsset = computed<WallAsset | null>(() => {
    if (displayAssets.value.length === 0) {
        return null;
    }

    const safeIndex =
        ((activeIndex.value % displayAssets.value.length) +
            displayAssets.value.length) %
        displayAssets.value.length;

    return displayAssets.value[safeIndex] ?? null;
});

const wallTitle = computed(
    () => props.branding.welcomeMessage?.trim() || props.eventName,
);
const wallLogoUrl = computed(() => props.branding.logoUrl);
const captionVisible = computed(() => !props.branding.hideCaption);
const qrVisible = computed(() => !props.branding.hideQrCode);
const captionPanelClasses = computed(() =>
    props.branding.captionTheme === 'light'
        ? 'border-slate-200/90 bg-white/90 text-slate-900'
        : 'border-white/20 bg-black/48 text-white',
);

const surfaceStyle = computed((): Record<string, string> => {
    const primary = props.branding.primaryColor || '#F59E0B';
    const accent = props.branding.accentColor || '#F97316';
    const style: Record<string, string> = {
        '--wall-primary': primary,
        '--wall-accent': accent,
    };

    if (
        props.branding.albumBackgroundEnabled &&
        props.branding.albumBackgroundMode === 'solid'
    ) {
        style.backgroundColor =
            props.branding.albumBackgroundColor || '#0f172a';

        return style;
    }

    if (
        props.branding.albumBackgroundEnabled &&
        props.branding.albumBackgroundMode === 'image' &&
        props.branding.albumBackgroundImageUrl
    ) {
        style.backgroundImage = `url(${props.branding.albumBackgroundImageUrl})`;
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';

        return style;
    }

    style.backgroundImage = `linear-gradient(135deg, ${primary} 0%, ${accent} 60%, #0f172a 100%)`;

    return style;
});

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return t('public.wall.now');
    }

    return new Intl.DateTimeFormat(locale.value || 'en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const slideTitle = computed(() => {
    if (!currentAsset.value) {
        return null;
    }

    return (
        currentAsset.value.captionTitle?.trim() ||
        currentAsset.value.guestName?.trim() ||
        t('public.wall.guest_upload')
    );
});

const slideSubtitle = computed(() => {
    if (!currentAsset.value) {
        return null;
    }

    return (
        currentAsset.value.captionSubtitle?.trim() ||
        formatDateTime(currentAsset.value.createdAt)
    );
});

const textPostSurfaceStyle = (asset: WallAsset): Record<string, string> => {
    const style: Record<string, string> = {
        backgroundColor: asset.textThemeBackgroundColor || '#0f172a',
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    };

    if (asset.textThemeImageUrl) {
        style.backgroundImage = `url(${asset.textThemeImageUrl})`;
    }

    return style;
};

const textPostTextStyle = (asset: WallAsset): Record<string, string> => ({
    color: asset.textThemeTextColor || '#ffffff',
});

const nextAsset = (): void => {
    if (displayAssets.value.length === 0) {
        return;
    }

    activeIndex.value = (activeIndex.value + 1) % displayAssets.value.length;
};

const previousAsset = (): void => {
    if (displayAssets.value.length === 0) {
        return;
    }

    activeIndex.value =
        (activeIndex.value - 1 + displayAssets.value.length) %
        displayAssets.value.length;
};

const latestWallAssetId = (assets: WallAsset[]): number =>
    assets.reduce((max, asset) => Math.max(max, asset.id), 0);

const applyWallAssets = (nextAssets: WallAsset[]): void => {
    const currentAssetId = currentAsset.value?.id ?? null;
    wallAssets.value = [...nextAssets];

    if (displayAssets.value.length === 0) {
        activeIndex.value = 0;
        return;
    }

    if (currentAssetId !== null) {
        const nextIndex = displayAssets.value.findIndex(
            (asset) => asset.id === currentAssetId,
        );

        if (nextIndex >= 0) {
            activeIndex.value = nextIndex;
            return;
        }
    }

    activeIndex.value = Math.min(
        activeIndex.value,
        Math.max(0, displayAssets.value.length - 1),
    );
};

const refreshWallAssets = async (): Promise<void> => {
    if (
        typeof window === 'undefined' ||
        typeof document === 'undefined' ||
        document.visibilityState !== 'visible' ||
        isWallRefreshing.value
    ) {
        return;
    }

    isWallRefreshing.value = true;
    wallLiveStatus.value = 'updating';

    try {
        const response = await fetch(window.location.href, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Inertia': 'true',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = (await response.json()) as {
            props?: {
                assets?: WallAsset[];
            };
        };
        const nextAssets = Array.isArray(payload.props?.assets)
            ? payload.props.assets
            : [];

        if (latestWallAssetId(nextAssets) > latestWallAssetId(wallAssets.value)) {
            applyWallAssets(nextAssets);
            startAutoplay();
        }
    } catch {
        // Silent background refresh for TVs/projectors.
    } finally {
        isWallRefreshing.value = false;
        wallLiveStatus.value = 'live';
    }
};

const startAutoplay = (): void => {
    if (typeof window === 'undefined' || displayAssets.value.length <= 1) {
        return;
    }

    if (autoplayId.value !== null) {
        window.clearInterval(autoplayId.value);
    }

    autoplayId.value = window.setInterval(() => {
        nextAsset();
    }, 9000);
};

const stopAutoplay = (): void => {
    if (typeof window === 'undefined' || autoplayId.value === null) {
        return;
    }

    window.clearInterval(autoplayId.value);
    autoplayId.value = null;
};

onMounted(() => {
    applyWallAssets(props.assets);
    startAutoplay();
    if (typeof window !== 'undefined') {
        wallUpdatePollId.value = window.setInterval(() => {
            void refreshWallAssets();
        }, 20000);
    }
});

onUnmounted(() => {
    stopAutoplay();
    if (typeof window !== 'undefined' && wallUpdatePollId.value !== null) {
        window.clearInterval(wallUpdatePollId.value);
        wallUpdatePollId.value = null;
    }
});

watch(
    () => props.assets,
    (nextAssets) => {
        applyWallAssets(nextAssets);
        startAutoplay();
    },
);
</script>

<template>
    <Head :title="t('public.wall.page_title', { eventName })" />

    <main
        class="relative min-h-screen overflow-hidden text-white"
        :style="surfaceStyle"
    >
        <div class="absolute inset-0 bg-black/55" />

        <div class="relative z-20 mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 pb-20 pt-5 lg:px-12">
            <header class="mb-4 flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="size-11 overflow-hidden rounded-full border border-white/25 bg-white/10">
                        <img
                            v-if="wallLogoUrl"
                            :src="wallLogoUrl"
                            :alt="t('public.shared.alt.event_logo')"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-sm font-semibold text-white"
                        >
                            {{ wallTitle.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-semibold text-white lg:text-base">
                            {{ wallTitle }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-white/75">
                            <p class="truncate">
                                {{ t('public.wall.live_photo_wall') }} • {{ status }}
                            </p>
                            <span class="inline-flex items-center gap-1 rounded-full border border-white/15 bg-white/10 px-2 py-0.5 text-[10px] font-medium text-white/90">
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="wallLiveStatus === 'updating' ? 'bg-amber-300' : 'bg-emerald-300'"
                                />
                                {{ wallLiveStatus === 'updating' ? t('public.wall.updating') : t('public.wall.auto_updating') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="qrVisible"
                    class="shrink-0 rounded-2xl border border-white/20 bg-black/55 p-3 text-white shadow-[0_18px_44px_rgba(0,0,0,0.24)] backdrop-blur"
                >
                    <div class="flex items-center gap-3">
                        <img
                            :src="albumQrDataUrl"
                            :alt="t('public.shared.alt.album_qr_code')"
                            class="size-16 rounded-md bg-white p-1"
                        />
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em]">
                                {{ t('public.wall.scan_to_upload') }}
                            </p>
                            <p class="mt-1 text-xs text-white/80">
                                {{ t('public.wall.open_digital_album') }}
                            </p>
                            <a
                                :href="albumUrl"
                                class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-white underline underline-offset-4"
                            >
                                <QrCode class="size-3.5" />
                                {{ t('public.wall.album_link') }}
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <section class="relative flex flex-1 items-center justify-center">
                <div
                    v-if="currentAsset"
                    class="relative w-full overflow-hidden rounded-3xl border border-white/25 bg-black/40 shadow-2xl backdrop-blur"
                >
                    <img
                        v-if="currentAsset.kind === 'photo' && currentAsset.previewUrl"
                        :src="currentAsset.previewUrl"
                        :alt="t('public.shared.alt.wall_photo')"
                        class="h-[60vh] w-full object-contain lg:h-[72vh]"
                    />

                    <video
                        v-else-if="
                            currentAsset.kind === 'video' && currentAsset.previewUrl
                        "
                        :src="currentAsset.previewUrl"
                        :poster="currentAsset.thumbnailUrl ?? undefined"
                        class="h-[60vh] w-full object-contain lg:h-[72vh]"
                        autoplay
                        loop
                        muted
                        playsinline
                    />
                    <div
                        v-else-if="
                            currentAsset.kind === 'video' &&
                            currentAsset.videoProcessing
                        "
                        class="flex h-[60vh] w-full flex-col items-center justify-center gap-4 px-8 text-center text-white/85 lg:h-[72vh]"
                    >
                        <LoaderCircle class="size-10 animate-spin text-white/80" />
                        <div class="space-y-1">
                            <p class="text-lg font-semibold">
                                {{ t('public.shared.processing_video') }}
                            </p>
                            <p class="text-sm text-white/70">
                                {{ t('public.shared.processing_video_hint') }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex h-[60vh] w-full items-center justify-center px-8 text-center lg:h-[72vh]"
                        :style="textPostSurfaceStyle(currentAsset)"
                    >
                        <p
                            class="max-w-3xl whitespace-pre-wrap text-base font-medium leading-relaxed lg:text-[1.15rem]"
                            :style="textPostTextStyle(currentAsset)"
                        >
                            {{ currentAsset.text || t('public.wall.text_post') }}
                        </p>
                    </div>

                    <div
                        v-if="captionVisible"
                        class="absolute inset-x-3 bottom-3 rounded-2xl border p-3 backdrop-blur"
                        :class="captionPanelClasses"
                    >
                        <p class="text-sm font-semibold">
                            {{ slideTitle }}
                        </p>
                        <p class="text-xs opacity-85">
                            {{ slideSubtitle }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="w-full max-w-3xl"
                >
                    <Empty class="rounded-3xl border border-white/20 bg-white/10 p-10 text-white backdrop-blur">
                        <EmptyHeader>
                            <EmptyMedia
                                variant="icon"
                                class="size-14 rounded-2xl border border-white/20 bg-white/10 text-white [&_svg]:size-7"
                            >
                                <Images />
                            </EmptyMedia>
                            <EmptyTitle class="text-base font-semibold text-white sm:text-lg">
                                {{ t('public.wall.waiting_title') }}
                            </EmptyTitle>
                            <EmptyDescription class="text-white/80">
                                {{ t('public.wall.waiting_description') }}
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                </div>

                <button
                    v-if="displayAssets.length > 1"
                    type="button"
                    class="absolute left-2 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/45 text-white transition hover:bg-black/60"
                    :aria-label="t('public.shared.actions.previous_media')"
                    @click="previousAsset"
                >
                    <ChevronLeft class="size-5" />
                </button>

                <button
                    v-if="displayAssets.length > 1"
                    type="button"
                    class="absolute right-2 top-1/2 inline-flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/45 text-white transition hover:bg-black/60"
                    :aria-label="t('public.shared.actions.next_media')"
                    @click="nextAsset"
                >
                    <ChevronRight class="size-5" />
                </button>
            </section>
        </div>
    </main>

    <footer
        v-if="showPoweredBy"
        class="safe-bottom safe-x fixed inset-x-0 bottom-0 z-40 bg-black/70 backdrop-blur supports-[backdrop-filter]:bg-black/55"
    >
        <Separator class="bg-white/12" />
        <div class="px-3 py-2 text-center text-xs text-white/55">
            © {{ new Date().getFullYear() }} Kululu. {{ t('public.wall.footer') }}
        </div>
    </footer>
</template>
