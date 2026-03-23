<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Heart, Images, LoaderCircle, QrCode } from 'lucide-vue-next';
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

type WallReactionNote = WallAssetComment & {
    style: Record<string, string>;
    delayMs: number;
};

type WallReactionNoteVariant = {
    top?: string;
    right?: string;
    bottom?: string;
    left?: string;
    rotate: string;
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

const page = usePage();
const { locale, t } = useTranslations();
const appName = computed(() => page.props.name ?? 'QR Events');
const inertiaVersion = computed(() => page.version ?? null);

const activeIndex = ref(0);
const autoplayId = ref<number | null>(null);
const wallAssets = ref<WallAsset[]>([...props.assets]);
const wallHeartBursts = ref<WallHeartBurst[]>([]);
const wallUpdatePollId = ref<number | null>(null);
const isWallRefreshing = ref(false);
const wallLiveStatus = ref<'live' | 'updating'>('live');
let wallHeartSequence = 0;

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

const currentReactionNotes = computed<WallReactionNote[]>(() => {
    const comments = currentAsset.value?.recentComments ?? [];

    return comments.slice(-4).map((comment, index) => ({
        ...comment,
        style: wallReactionNoteStyle(comment.id, index),
        delayMs: index * 80,
    }));
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

const wallReactionNoteStyle = (commentId: number, index: number): Record<string, string> => {
    const variants: WallReactionNoteVariant[] = [
        { top: '5.5%', left: '3.5%', rotate: '-7deg' },
        { top: '10%', right: '4.5%', rotate: '6deg' },
        { bottom: '15%', left: '5.5%', rotate: '-5deg' },
        { bottom: '20%', right: '6%', rotate: '7deg' },
    ];
    const variant = variants[index % variants.length] ?? variants[0];
    const jitter = ((commentId % 7) - 3) * 0.35;

    return {
        ...(variant.top !== undefined ? { top: `calc(${variant.top} + ${jitter}%)` } : {}),
        ...(variant.bottom !== undefined ? { bottom: `calc(${variant.bottom} + ${jitter}%)` } : {}),
        ...(variant.left !== undefined ? { left: `calc(${variant.left} + ${jitter}%)` } : {}),
        ...(variant.right !== undefined ? { right: `calc(${variant.right} + ${jitter}%)` } : {}),
        transform: `rotate(${variant.rotate})`,
    };
};

const totalPositiveLikeGain = (previousAssets: WallAsset[], nextAssets: WallAsset[]): number => {
    const previousAssetsById = new Map(previousAssets.map((asset) => [asset.id, asset]));

    return nextAssets.reduce((carry, asset) => {
        const previousLikeCount = previousAssetsById.get(asset.id)?.likeCount ?? 0;

        return carry + Math.max(0, asset.likeCount - previousLikeCount);
    }, 0);
};

const wallAssetHasChanged = (previousAsset: WallAsset | undefined, nextAsset: WallAsset): boolean => {
    if (previousAsset === undefined) {
        return true;
    }

    if (
        previousAsset.previewUrl !== nextAsset.previewUrl
        || previousAsset.thumbnailUrl !== nextAsset.thumbnailUrl
        || previousAsset.videoProcessing !== nextAsset.videoProcessing
        || previousAsset.likeCount !== nextAsset.likeCount
        || previousAsset.commentCount !== nextAsset.commentCount
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

const spawnWallHeartBursts = (count: number): void => {
    if (typeof window === 'undefined' || count <= 0) {
        return;
    }

    const nextBursts = Array.from({ length: count }, () => {
        wallHeartSequence += 1;

        return {
            id: wallHeartSequence,
            left: `${12 + Math.random() * 76}%`,
            bottom: `${6 + Math.random() * 12}%`,
            size: `${20 + Math.random() * 26}px`,
            durationMs: 1800 + Math.round(Math.random() * 1600),
            delayMs: Math.round(Math.random() * 420),
            rotation: `${-18 + Math.random() * 36}deg`,
        } satisfies WallHeartBurst;
    });

    wallHeartBursts.value = [...wallHeartBursts.value, ...nextBursts];

    const burstIds = new Set(nextBursts.map((burst) => burst.id));
    const removalDelay = Math.max(
        ...nextBursts.map((burst) => burst.durationMs + burst.delayMs),
        0,
    ) + 500;

    window.setTimeout(() => {
        wallHeartBursts.value = wallHeartBursts.value.filter(
            (burst) => !burstIds.has(burst.id),
        );
    }, removalDelay);
};

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
        const previousLatestAssetId = latestWallAssetId(wallAssets.value);
        const nextLatestAssetId = latestWallAssetId(nextAssets);
        const newApprovedCount = nextAssets.filter(
            (asset) => asset.id > previousLatestAssetId,
        ).length;
        const likeGainCount = totalPositiveLikeGain(previousAssets, nextAssets);
        const hasWallChanged =
            nextAssets.length !== previousAssets.length
            || nextLatestAssetId !== previousLatestAssetId
            || nextAssets.some((asset) =>
                wallAssetHasChanged(
                    previousAssets.find((candidate) => candidate.id === asset.id),
                    asset,
                ),
            );

        if (hasWallChanged) {
            applyWallAssets(nextAssets);

            if (newApprovedCount > 0) {
                startAutoplay();
                spawnWallHeartBursts(Math.min(12, Math.max(4, newApprovedCount * 3)));
            } else if (likeGainCount > 0) {
                spawnWallHeartBursts(Math.min(10, Math.max(3, likeGainCount * 2)));
            }
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
        }, 8000);
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
                <div class="pointer-events-none absolute inset-0 z-20 overflow-hidden">
                    <Heart
                        v-for="burst in wallHeartBursts"
                        :key="burst.id"
                        class="wall-heart-burst absolute fill-pink-400/65 text-pink-300/90 drop-shadow-[0_10px_24px_rgba(244,114,182,0.35)]"
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

                <div
                    v-if="currentAsset"
                    class="relative w-full overflow-hidden rounded-3xl border border-white/25 bg-black/40 shadow-2xl backdrop-blur"
                >
                    <div
                        v-if="currentAssetBackdropUrl"
                        class="pointer-events-none absolute inset-0"
                    >
                        <img
                            :src="currentAssetBackdropUrl"
                            alt=""
                            class="h-full w-full scale-110 object-cover opacity-45 blur-2xl"
                        />
                        <div class="absolute inset-0 bg-black/35" />
                    </div>

                    <img
                        v-if="currentAsset.kind === 'photo' && currentAsset.previewUrl"
                        :src="currentAsset.previewUrl"
                        :alt="t('public.shared.alt.wall_photo')"
                        class="relative z-10 h-[60vh] w-full object-contain lg:h-[72vh]"
                    />

                    <video
                        v-else-if="
                            currentAsset.kind === 'video' && currentAsset.previewUrl
                        "
                        :src="currentAsset.previewUrl"
                        :poster="currentAsset.thumbnailUrl ?? undefined"
                        class="relative z-10 h-[60vh] w-full object-contain lg:h-[72vh]"
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
                        class="relative z-10 flex h-[60vh] w-full flex-col items-center justify-center gap-4 px-8 text-center text-white/85 lg:h-[72vh]"
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
                        class="relative z-10 flex h-[60vh] w-full items-center justify-center px-8 text-center lg:h-[72vh]"
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
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs opacity-85">
                            <p>
                                {{ slideSubtitle }}
                            </p>
                            <span class="inline-flex items-center gap-1">
                                <Heart class="size-3.5" />
                                {{ currentAsset.likeCount }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <span class="text-[0.95rem] leading-none">💬</span>
                                {{ currentAsset.commentCount }}
                            </span>
                        </div>
                    </div>

                    <div
                        v-if="currentReactionNotes.length > 0"
                        class="pointer-events-none absolute inset-0 z-10 hidden sm:block"
                    >
                        <article
                            v-for="note in currentReactionNotes"
                            :key="`wall-note-${note.id}`"
                            class="wall-note-card absolute w-40 rounded-[1.15rem] border border-[#e8d5a6] bg-[#fff4c9]/94 p-3 text-left text-[#3f3318] shadow-[0_18px_34px_rgba(62,41,12,0.24)] backdrop-blur"
                            :style="{
                                ...note.style,
                                animationDelay: `${note.delayMs}ms`,
                            }"
                        >
                            <p class="line-clamp-4 whitespace-pre-wrap text-[0.8rem] font-medium leading-5">
                                {{ note.body }}
                            </p>
                            <div class="mt-2 flex items-center justify-between gap-2 text-[0.68rem] uppercase tracking-[0.14em] text-[#70521f]/85">
                                <span class="truncate">{{ note.guestName }}</span>
                                <span
                                    v-if="note.likeCount > 0"
                                    class="inline-flex items-center gap-1 text-[#c44569]"
                                >
                                    <Heart class="size-3 fill-current" />
                                    {{ note.likeCount }}
                                </span>
                            </div>
                        </article>
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
            © {{ new Date().getFullYear() }} {{ appName }}. {{ t('public.wall.footer') }}
        </div>
    </footer>
</template>

<style scoped>
.wall-heart-burst {
    animation-name: wall-heart-float;
    animation-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    animation-fill-mode: forwards;
    opacity: 0;
    transform: translate3d(0, 0, 0) scale(0.72);
}

.wall-note-card {
    animation-name: wall-note-drift;
    animation-duration: 520ms;
    animation-timing-function: cubic-bezier(0.2, 0.9, 0.2, 1);
    animation-fill-mode: both;
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

@keyframes wall-note-drift {
    0% {
        opacity: 0;
        transform: translate3d(0, 20px, 0) scale(0.84);
    }

    100% {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
    }
}
</style>
