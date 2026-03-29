<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Heart, LoaderCircle } from 'lucide-vue-next';
import type { Swiper as SwiperInstance } from 'swiper';
import { EffectCreative } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import 'swiper/css';
import 'swiper/css/effect-creative';

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
    width: number | null;
    height: number | null;
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
    likeCount: number;
    commentCount: number;
    recentComments: WallAssetComment[];
};

type WallAssetComment = {
    id: number;
    body: string;
    guestName: string;
    createdAt: string | null;
    likeCount: number;
};

type WallHeartBurst = {
    id: number;
    left: string;
    bottom: string;
    size: string;
    durationMs: number;
    delayMs: number;
    rotation: string;
};

const props = defineProps<{
    eventName: string;
    status: string;
    albumUrl: string;
    albumAccessCode: string;
    albumEntryShortcutUrl: string;
    albumQrDataUrl: string;
    showPoweredBy: boolean;
    branding: WallBranding;
    assets: WallAsset[];
}>();

const modules = [EffectCreative];

const page = usePage();
const { locale, t } = useTranslations();
const appName = computed(() => page.props.name ?? 'EventSmart');
const inertiaVersion = computed(() => page.version ?? null);

const wallAssets = ref<WallAsset[]>([...props.assets]);
const activeIndex = ref(0);
const swiperInstance = ref<SwiperInstance | null>(null);
const wallHeartBursts = ref<WallHeartBurst[]>([]);
const wallUpdatePollId = ref<number | null>(null);
const advanceTimerId = ref<number | null>(null);
const isWallRefreshing = ref(false);
const wallLiveStatus = ref<'live' | 'updating'>('live');
const foregroundVideoElements = new Map<number, HTMLVideoElement>();
const backdropVideoElements = new Map<number, HTMLVideoElement>();
let wallHeartSequence = 0;
let activeVideoCleanup: (() => void) | null = null;

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

const wallVars = computed((): Record<string, string> => ({
    '--wall-primary': props.branding.primaryColor || '#f59e0b',
    '--wall-accent': props.branding.accentColor || '#fb7185',
}));

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

const currentReactionNotes = computed(() =>
    (currentAsset.value?.recentComments ?? []).slice(-3).reverse(),
);
const currentReactionStripItems = computed(() =>
    currentReactionNotes.value.map((note) => ({
        id: note.id,
        label:
            note.likeCount > 0
                ? `${note.guestName} • ${note.likeCount}`
                : note.guestName,
        body: note.body,
    })),
);

const currentAssetBackdropUrl = computed(() => {
    if (!currentAsset.value) {
        return null;
    }

    if (currentAsset.value.kind === 'photo') {
        return currentAsset.value.previewUrl;
    }

    if (currentAsset.value.kind === 'video') {
        return currentAsset.value.thumbnailUrl || currentAsset.value.previewUrl;
    }

    return currentAsset.value.textThemeImageUrl;
});

const currentAssetIsPortrait = computed(() => {
    const asset = currentAsset.value;

    if (!asset || asset.kind === 'text') {
        return false;
    }

    if (!asset.width || !asset.height) {
        return false;
    }

    return asset.height > asset.width * 1.05;
});

const emptyStateStyle = computed<Record<string, string>>(() => ({
    background:
        'radial-gradient(circle at top, color-mix(in srgb, var(--wall-primary) 55%, transparent), transparent 46%), linear-gradient(180deg, rgba(12,12,18,0.92) 0%, rgba(3,4,6,0.98) 100%)',
}));

const poweredByLabel = computed(() => `by ${appName.value.toLowerCase()}.app`);

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return t('public.wall.now');
    }

    return new Intl.DateTimeFormat(locale.value || 'en', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const textPostSurfaceStyle = (asset: WallAsset): Record<string, string> => {
    const style: Record<string, string> = {
        backgroundColor: asset.textThemeBackgroundColor || '#101118',
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

const latestWallAssetId = (assets: WallAsset[]): number =>
    assets.reduce((max, asset) => Math.max(max, asset.id), 0);

const totalPositiveLikeGain = (
    previousAssets: WallAsset[],
    nextAssets: WallAsset[],
): number => {
    const previousAssetsById = new Map(
        previousAssets.map((asset) => [asset.id, asset]),
    );

    return nextAssets.reduce((carry, asset) => {
        const previousLikeCount =
            previousAssetsById.get(asset.id)?.likeCount ?? 0;

        return carry + Math.max(0, asset.likeCount - previousLikeCount);
    }, 0);
};

const wallAssetHasChanged = (
    previousAsset: WallAsset | undefined,
    nextAsset: WallAsset,
): boolean => {
    if (previousAsset === undefined) {
        return true;
    }

    if (
        previousAsset.previewUrl !== nextAsset.previewUrl ||
        previousAsset.thumbnailUrl !== nextAsset.thumbnailUrl ||
        previousAsset.videoProcessing !== nextAsset.videoProcessing ||
        previousAsset.likeCount !== nextAsset.likeCount ||
        previousAsset.commentCount !== nextAsset.commentCount
    ) {
        return true;
    }

    const previousCommentSignature = previousAsset.recentComments
        .map((comment) => `${comment.id}:${comment.likeCount}`)
        .join('|');
    const nextCommentSignature = nextAsset.recentComments
        .map((comment) => `${comment.id}:${comment.likeCount}`)
        .join('|');

    return previousCommentSignature !== nextCommentSignature;
};

const clearAdvanceTimer = (): void => {
    if (typeof window === 'undefined' || advanceTimerId.value === null) {
        return;
    }

    window.clearTimeout(advanceTimerId.value);
    advanceTimerId.value = null;
};

const clearActiveVideoPlayback = (): void => {
    if (activeVideoCleanup) {
        activeVideoCleanup();
        activeVideoCleanup = null;
    }

    foregroundVideoElements.forEach((video) => {
        video.pause();
        video.currentTime = 0;
    });

    backdropVideoElements.forEach((video) => {
        video.pause();
        video.currentTime = 0;
    });
};

const nextAsset = (): void => {
    const swiper = swiperInstance.value;

    if (swiper && displayAssets.value.length > 1) {
        swiper.slideNext(900);

        return;
    }

    if (displayAssets.value.length > 1) {
        activeIndex.value = (activeIndex.value + 1) % displayAssets.value.length;
    }
};

const queueNextSlide = (delayMs: number): void => {
    clearAdvanceTimer();

    if (typeof window === 'undefined' || displayAssets.value.length <= 1) {
        return;
    }

    advanceTimerId.value = window.setTimeout(() => {
        nextAsset();
    }, delayMs);
};

const playActiveVideoSlide = (attempt = 0): void => {
    const asset = currentAsset.value;

    if (
        asset === null ||
        asset.kind !== 'video' ||
        asset.previewUrl === null ||
        asset.videoProcessing
    ) {
        return;
    }

    nextTick(() => {
        const foreground = foregroundVideoElements.get(asset.id);
        const backdrop = backdropVideoElements.get(asset.id) ?? null;

        if (!foreground) {
            if (typeof window !== 'undefined' && attempt < 6) {
                window.setTimeout(() => playActiveVideoSlide(attempt + 1), 140);
            } else {
                queueNextSlide(9000);
            }

            return;
        }

        clearActiveVideoPlayback();

        foreground.currentTime = 0;
        foreground.loop = false;
        foreground.muted = true;

        if (backdrop) {
            backdrop.currentTime = 0;
            backdrop.loop = false;
            backdrop.muted = true;
        }

        const syncBackdrop = (): void => {
            if (
                backdrop &&
                Math.abs(backdrop.currentTime - foreground.currentTime) > 0.2
            ) {
                backdrop.currentTime = foreground.currentTime;
            }
        };

        const handleEnded = (): void => {
            if (currentAsset.value?.id === asset.id) {
                nextAsset();
            }
        };

        foreground.addEventListener('timeupdate', syncBackdrop);
        foreground.addEventListener('ended', handleEnded);

        activeVideoCleanup = () => {
            foreground.removeEventListener('timeupdate', syncBackdrop);
            foreground.removeEventListener('ended', handleEnded);
        };

        if (backdrop) {
            void backdrop.play().catch(() => {});
        }

        void foreground.play().catch(() => {
            queueNextSlide(9000);
        });
    });
};

const startSlideLifecycle = (): void => {
    clearAdvanceTimer();

    if (displayAssets.value.length <= 1 && currentAsset.value?.kind !== 'video') {
        return;
    }

    const asset = currentAsset.value;

    if (!asset) {
        return;
    }

    if (asset.kind === 'video') {
        if (asset.videoProcessing || asset.previewUrl === null) {
            queueNextSlide(5000);

            return;
        }

        playActiveVideoSlide();

        return;
    }

    clearActiveVideoPlayback();
    queueNextSlide(asset.kind === 'text' ? 12000 : 9000);
};

const setForegroundVideoElement =
    (assetId: number) =>
    (element: Element | null): void => {
        if (element instanceof HTMLVideoElement) {
            foregroundVideoElements.set(assetId, element);

            return;
        }

        foregroundVideoElements.delete(assetId);
    };

const setBackdropVideoElement =
    (assetId: number) =>
    (element: Element | null): void => {
        if (element instanceof HTMLVideoElement) {
            backdropVideoElements.set(assetId, element);

            return;
        }

        backdropVideoElements.delete(assetId);
    };

const applyWallAssets = (nextAssets: WallAsset[]): void => {
    const currentAssetId = currentAsset.value?.id ?? null;

    wallAssets.value = [...nextAssets];

    if (displayAssets.value.length === 0) {
        activeIndex.value = 0;
        clearAdvanceTimer();
        clearActiveVideoPlayback();

        return;
    }

    let nextIndex = Math.min(
        activeIndex.value,
        Math.max(0, displayAssets.value.length - 1),
    );

    if (currentAssetId !== null) {
        const matchedIndex = displayAssets.value.findIndex(
            (asset) => asset.id === currentAssetId,
        );

        if (matchedIndex >= 0) {
            nextIndex = matchedIndex;
        }
    }

    activeIndex.value = nextIndex;

    nextTick(() => {
        const swiper = swiperInstance.value;

        if (swiper && swiper.activeIndex !== nextIndex) {
            swiper.slideTo(nextIndex, 0);
        }

        startSlideLifecycle();
    });
};

const spawnWallHeartBursts = (count: number): void => {
    if (typeof window === 'undefined' || count <= 0) {
        return;
    }

    const nextBursts = Array.from({ length: count }, () => {
        wallHeartSequence += 1;

        return {
            id: wallHeartSequence,
            left: `${12 + Math.random() * 76}%`,
            bottom: `${8 + Math.random() * 12}%`,
            size: `${18 + Math.random() * 22}px`,
            durationMs: 1800 + Math.round(Math.random() * 1500),
            delayMs: Math.round(Math.random() * 360),
            rotation: `${-16 + Math.random() * 32}deg`,
        } satisfies WallHeartBurst;
    });

    wallHeartBursts.value = [...wallHeartBursts.value, ...nextBursts];

    const burstIds = new Set(nextBursts.map((burst) => burst.id));
    const removalDelay =
        Math.max(
            ...nextBursts.map((burst) => burst.durationMs + burst.delayMs),
            0,
        ) + 500;

    window.setTimeout(() => {
        wallHeartBursts.value = wallHeartBursts.value.filter(
            (burst) => !burstIds.has(burst.id),
        );
    }, removalDelay);
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
                ...(inertiaVersion.value !== null
                    ? { 'X-Inertia-Version': inertiaVersion.value }
                    : {}),
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
        const previousAssets = [...wallAssets.value];
        const previousLatestAssetId = latestWallAssetId(previousAssets);
        const nextLatestAssetId = latestWallAssetId(nextAssets);
        const newApprovedCount = nextAssets.filter(
            (asset) => asset.id > previousLatestAssetId,
        ).length;
        const likeGainCount = totalPositiveLikeGain(previousAssets, nextAssets);
        const hasWallChanged =
            nextAssets.length !== previousAssets.length ||
            nextLatestAssetId !== previousLatestAssetId ||
            nextAssets.some((asset) =>
                wallAssetHasChanged(
                    previousAssets.find((candidate) => candidate.id === asset.id),
                    asset,
                ),
            );

        if (hasWallChanged) {
            applyWallAssets(nextAssets);

            if (newApprovedCount > 0) {
                spawnWallHeartBursts(
                    Math.min(10, Math.max(4, newApprovedCount * 2)),
                );
            } else if (likeGainCount > 0) {
                spawnWallHeartBursts(
                    Math.min(8, Math.max(2, likeGainCount * 2)),
                );
            }
        }
    } catch {
        // silent for passive wall displays
    } finally {
        isWallRefreshing.value = false;
        wallLiveStatus.value = 'live';
    }
};

const handleSwiperReady = (swiper: SwiperInstance): void => {
    swiperInstance.value = swiper;

    if (displayAssets.value.length > 0 && swiper.activeIndex !== activeIndex.value) {
        swiper.slideTo(activeIndex.value, 0);
    }

    startSlideLifecycle();
};

const handleSlideChange = (swiper: SwiperInstance): void => {
    activeIndex.value = swiper.activeIndex;
    startSlideLifecycle();
};

onMounted(() => {
    applyWallAssets(props.assets);

    if (typeof window !== 'undefined') {
        wallUpdatePollId.value = window.setInterval(() => {
            void refreshWallAssets();
        }, 8000);
    }
});

onUnmounted(() => {
    clearAdvanceTimer();
    clearActiveVideoPlayback();

    if (typeof window !== 'undefined' && wallUpdatePollId.value !== null) {
        window.clearInterval(wallUpdatePollId.value);
        wallUpdatePollId.value = null;
    }
});

watch(
    () => props.assets,
    (nextAssets) => {
        applyWallAssets(nextAssets);
    },
);
</script>

<template>
    <Head :title="t('public.wall.page_title', { eventName })" />

    <main
        class="relative h-screen w-screen overflow-hidden bg-[#040507] text-white"
        :style="wallVars"
    >
        <div
            v-if="currentAsset?.kind === 'text'"
            class="absolute inset-0"
            :style="textPostSurfaceStyle(currentAsset)"
        />
        <div
            v-else-if="currentAssetBackdropUrl"
            class="absolute inset-0"
        >
            <img
                v-if="
                    currentAsset?.kind !== 'video' ||
                    currentAsset?.videoProcessing ||
                    !currentAsset?.previewUrl
                "
                :src="currentAssetBackdropUrl"
                alt=""
                class="h-full w-full object-cover"
                :class="currentAssetIsPortrait ? 'scale-110 blur-2xl opacity-55' : 'opacity-92'"
            />
            <video
                v-else-if="currentAssetIsPortrait && currentAsset?.previewUrl"
                :ref="setBackdropVideoElement(currentAsset.id)"
                :src="currentAsset.previewUrl"
                :poster="currentAsset.thumbnailUrl ?? undefined"
                class="h-full w-full scale-110 object-cover opacity-45 blur-2xl"
                autoplay
                muted
                playsinline
                aria-hidden="true"
            />
        </div>

        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(3,4,6,0.78)_0%,rgba(3,4,6,0.16)_18%,rgba(3,4,6,0.12)_72%,rgba(3,4,6,0.84)_100%)]" />

        <div class="pointer-events-none absolute inset-0 z-20 overflow-hidden">
            <Heart
                v-for="burst in wallHeartBursts"
                :key="burst.id"
                class="wall-heart-burst absolute fill-pink-400/60 text-pink-300/85 drop-shadow-[0_8px_20px_rgba(244,114,182,0.34)]"
                :style="{
                    left: burst.left,
                    bottom: burst.bottom,
                    width: burst.size,
                    height: burst.size,
                    animationDuration: `${burst.durationMs}ms`,
                    animationDelay: `${burst.delayMs}ms`,
                    rotate: burst.rotation,
                }"
            />
        </div>

        <div class="wall-safe wall-safe-top pointer-events-none absolute inset-x-0 top-0 z-30">
            <div class="wall-top-strip flex items-start justify-between gap-4">
                <div class="wall-strip wall-strip-brand max-w-[min(66vw,34rem)] px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/12">
                            <img
                                v-if="wallLogoUrl"
                                :src="wallLogoUrl"
                                :alt="t('public.shared.alt.event_logo')"
                                class="h-full w-full object-cover"
                            />
                            <span
                                v-else
                                class="text-sm font-semibold uppercase tracking-[0.12em] text-white/90"
                            >
                                {{ wallTitle.charAt(0) }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-[0.7rem] font-medium uppercase tracking-[0.2em] text-white/60">
                                {{ t('public.wall.live_photo_wall') }}
                            </p>
                            <h1 class="truncate text-base font-semibold text-white sm:text-lg">
                                {{ wallTitle }}
                            </h1>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-white/72">
                                <span>{{ status }}</span>
                                <span class="inline-flex items-center gap-1.5">
                                    <span
                                        class="size-2 rounded-full"
                                        :class="wallLiveStatus === 'updating' ? 'bg-amber-300' : 'bg-emerald-300'"
                                    />
                                    {{ wallLiveStatus === 'updating' ? t('public.wall.updating') : t('public.wall.auto_updating') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="qrVisible"
                    class="wall-strip wall-strip-qr max-w-[min(26vw,14rem)] px-3 py-3 text-right"
                >
                    <div class="flex items-start justify-end gap-3">
                        <div class="min-w-0">
                            <p class="text-[0.7rem] font-medium uppercase tracking-[0.2em] text-white/60">
                                {{ t('public.wall.scan_to_upload') }}
                            </p>
                            <p class="mt-1 text-sm font-semibold text-white">
                                {{ albumAccessCode }}
                            </p>
                            <p class="mt-1 text-xs text-white/72">
                                Visit {{ albumEntryShortcutUrl }}
                            </p>
                            <p class="mt-2 text-[11px] text-white/58">
                                {{ t('public.wall.open_digital_album') }}
                            </p>
                            <p
                                v-if="showPoweredBy"
                                class="mt-2 text-[11px] uppercase tracking-[0.14em] text-white/46"
                            >
                                {{ poweredByLabel }}
                            </p>
                        </div>

                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white p-1.5">
                            <img
                                :src="albumQrDataUrl"
                                :alt="t('public.shared.alt.album_qr_code')"
                                class="h-full w-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="relative z-10 h-full w-full">
            <Swiper
                class="h-full w-full"
                :modules="modules"
                effect="creative"
                :creative-effect="{
                    limitProgress: 2,
                    prev: {
                        opacity: 0,
                        scale: 0.92,
                        translate: ['-8%', 0, -220],
                    },
                    next: {
                        opacity: 0,
                        scale: 1.08,
                        translate: ['8%', 0, -220],
                    },
                }"
                :allow-touch-move="false"
                :simulate-touch="false"
                :speed="1400"
                @swiper="handleSwiperReady"
                @slide-change="handleSlideChange"
            >
                <SwiperSlide
                    v-for="asset in displayAssets"
                    :key="asset.id"
                    class="!h-full !w-full"
                >
                    <article class="relative h-full w-full overflow-hidden">
                        <template v-if="asset.kind === 'photo' && asset.previewUrl">
                            <img
                                v-if="asset.height && asset.width && asset.height > asset.width * 1.05"
                                :src="asset.previewUrl"
                                :alt="t('public.shared.alt.wall_photo')"
                                class="absolute inset-0 m-auto h-full w-full object-contain px-[max(3vw,1rem)] py-[max(8vh,3rem)]"
                            />
                            <img
                                v-else
                                :src="asset.previewUrl"
                                :alt="t('public.shared.alt.wall_photo')"
                                class="h-full w-full object-cover"
                            />
                        </template>

                        <template
                            v-else-if="asset.kind === 'video' && asset.previewUrl"
                        >
                            <video
                                :ref="setForegroundVideoElement(asset.id)"
                                :src="asset.previewUrl"
                                :poster="asset.thumbnailUrl ?? undefined"
                                class="h-full w-full"
                                :class="
                                    asset.height && asset.width && asset.height > asset.width * 1.05
                                        ? 'object-contain px-[max(3vw,1rem)] py-[max(8vh,3rem)]'
                                        : 'object-cover'
                                "
                                muted
                                playsinline
                                preload="auto"
                            />
                        </template>

                        <div
                            v-else-if="asset.kind === 'video' && asset.videoProcessing"
                            class="flex h-full w-full flex-col items-center justify-center gap-4 text-center"
                        >
                            <LoaderCircle class="size-12 animate-spin text-white/80" />
                            <div class="space-y-2">
                                <p class="text-xl font-semibold text-white">
                                    {{ t('public.shared.processing_video') }}
                                </p>
                                <p class="text-sm text-white/68">
                                    {{ t('public.shared.processing_video_hint') }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center px-[max(8vw,2rem)] text-center"
                            :style="textPostSurfaceStyle(asset)"
                        >
                            <p
                                class="max-w-5xl whitespace-pre-wrap text-[clamp(2rem,5vw,5rem)] font-semibold leading-[1.06]"
                                :style="textPostTextStyle(asset)"
                            >
                                {{ asset.text || t('public.wall.text_post') }}
                            </p>
                        </div>
                    </article>
                </SwiperSlide>
            </Swiper>

            <div
                v-if="displayAssets.length === 0"
                class="absolute inset-0 flex items-center justify-center px-8 text-center"
                :style="emptyStateStyle"
            >
                <div class="space-y-3">
                    <p class="text-[0.75rem] font-medium uppercase tracking-[0.24em] text-white/55">
                        {{ t('public.wall.live_photo_wall') }}
                    </p>
                    <h2 class="text-[clamp(1.8rem,4vw,3.6rem)] font-semibold text-white">
                        {{ t('public.wall.waiting_title') }}
                    </h2>
                    <p class="mx-auto max-w-2xl text-sm text-white/68 sm:text-base">
                        {{ t('public.wall.waiting_description') }}
                    </p>
                </div>
            </div>
        </section>

        <div
            v-if="currentReactionStripItems.length > 0"
            class="wall-safe pointer-events-none absolute left-0 top-[max(5.75rem,12vh)] z-30 hidden xl:block"
        >
            <div class="wall-strip wall-strip-reactions flex max-w-[min(42vw,34rem)] flex-wrap items-center gap-2 px-3 py-2">
                <span
                    v-for="note in currentReactionStripItems"
                    :key="`wall-note-${note.id}`"
                    class="inline-flex max-w-full items-center gap-2 rounded-full bg-white/8 px-2.5 py-1 text-xs text-white/78"
                >
                    <Heart class="size-3 fill-current text-pink-300/90" />
                    <span class="truncate">{{ note.label }}</span>
                    <span class="max-w-[16rem] truncate text-white/56">
                        {{ note.body }}
                    </span>
                </span>
            </div>
        </div>

        <div
            class="wall-safe wall-safe-bottom pointer-events-none absolute inset-x-0 bottom-0 z-30"
        >
            <div class="wall-bottom-strip flex flex-wrap items-end justify-between gap-4">
                <div
                    v-if="captionVisible && currentAsset"
                    class="wall-strip wall-strip-caption max-w-[min(72vw,42rem)] px-4 py-3"
                >
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <p class="text-sm font-semibold text-white">
                            {{ slideTitle }}
                        </p>
                        <span class="text-sm text-white/64">
                            {{ slideSubtitle }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-sm text-white/72">
                            <Heart class="size-3.5 fill-current text-pink-300/90" />
                            {{ currentAsset.likeCount }}
                        </span>
                        <span class="text-sm text-white/72">
                            💬 {{ currentAsset.commentCount }}
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </main>
</template>

<style scoped>
.wall-safe {
    padding-left: max(1rem, calc(env(safe-area-inset-left) + 0.75rem));
    padding-right: max(1rem, calc(env(safe-area-inset-right) + 0.75rem));
}

.wall-safe-top {
    padding-top: max(1rem, calc(env(safe-area-inset-top) + 0.5rem));
}

.wall-safe-bottom {
    padding-bottom: max(1rem, calc(env(safe-area-inset-bottom) + 0.5rem));
}

.wall-top-strip,
.wall-bottom-strip {
    width: 100%;
}

.wall-strip {
    background:
        linear-gradient(180deg, rgb(7 8 11 / 72%), rgb(7 8 11 / 24%)),
        linear-gradient(90deg, rgb(255 255 255 / 0.04), transparent 36%);
    backdrop-filter: blur(18px);
}

.wall-strip-brand,
.wall-strip-qr,
.wall-strip-reactions,
.wall-strip-caption {
    border-radius: 1rem;
}

.wall-heart-burst {
    animation-name: wall-heart-float;
    animation-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    animation-fill-mode: forwards;
    opacity: 0;
    transform: translate3d(0, 0, 0) scale(0.72);
}

@keyframes wall-heart-float {
    0% {
        opacity: 0;
        transform: translate3d(0, 22px, 0) scale(0.72);
    }

    18% {
        opacity: 0.9;
        transform: translate3d(-8px, -12px, 0) scale(1);
    }

    56% {
        opacity: 0.82;
        transform: translate3d(12px, -92px, 0) scale(1.08);
    }

    100% {
        opacity: 0;
        transform: translate3d(-14px, -184px, 0) scale(0.9);
    }
}

@media (max-aspect-ratio: 4 / 3) {
    .wall-top-strip,
    .wall-bottom-strip {
        gap: 0.75rem;
    }

    .wall-strip-brand {
        max-width: min(62vw, 24rem);
    }

    .wall-strip-qr {
        max-width: min(34vw, 10.75rem);
    }

    .wall-strip-reactions {
        display: none;
    }

    .wall-bottom-strip {
        flex-direction: column;
        align-items: stretch;
    }

    .wall-strip-caption {
        max-width: none;
    }

}

@media (max-aspect-ratio: 1 / 1) {
    .wall-top-strip {
        flex-direction: column;
        align-items: flex-start;
    }

    .wall-strip-brand,
    .wall-strip-qr {
        max-width: none;
    }
}
</style>
