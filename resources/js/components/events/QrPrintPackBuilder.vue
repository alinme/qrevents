<script setup lang="ts">
import { Copy, ExternalLink, FileOutput, Printer } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

type PrintPackTarget = 'album' | 'wall' | 'invitation';
type PrintPackPreset = 'welcome_sign' | 'table_card' | 'invitation_insert' | 'wall_sign' | 'small_card';
type Orientation = 'portrait' | 'landscape';
type ThemeKey = 'bloom' | 'garden' | 'classic' | 'midnight';

const props = defineProps<{
    eventName: string;
    albumAccessCode: string;
    targets: Array<{
        key: PrintPackTarget;
        url: string;
        qrDataUrl: string;
    }>;
}>();

const { t } = useTranslations();

const presetKeys: PrintPackPreset[] = ['welcome_sign', 'table_card', 'invitation_insert', 'wall_sign', 'small_card'];
const orientations: Orientation[] = ['portrait', 'landscape'];
const themeKeys: ThemeKey[] = ['bloom', 'garden', 'classic', 'midnight'];

const selectedTarget = ref<PrintPackTarget>(props.targets[0]?.key ?? 'album');
const selectedPreset = ref<PrintPackPreset>('welcome_sign');
const selectedOrientation = ref<Orientation>('portrait');
const selectedTheme = ref<ThemeKey>('bloom');
const layoutMode = ref<'preview' | 'controls'>('preview');
const previewViewport = ref<HTMLElement | null>(null);
const previewDataUrl = ref<string | null>(null);
const previewViewportWidth = ref(0);
const isRenderingPreview = ref(false);
const previewRenderFailed = ref(false);

let previewResizeObserver: ResizeObserver | null = null;
let previewRenderToken = 0;

const presetMeta = computed<Record<PrintPackPreset, {
    title: string;
    body: string;
    defaultTarget: PrintPackTarget;
    defaultOrientation: Orientation;
    defaultTheme: ThemeKey;
    instruction: string;
}>>(() => ({
    welcome_sign: {
        title: t('event_home.print_pack.presets.welcome_sign.title'),
        body: t('event_home.print_pack.presets.welcome_sign.body'),
        defaultTarget: 'album',
        defaultOrientation: 'portrait',
        defaultTheme: 'bloom',
        instruction: t('event_home.print_pack.instructions.album'),
    },
    table_card: {
        title: t('event_home.print_pack.presets.table_card.title'),
        body: t('event_home.print_pack.presets.table_card.body'),
        defaultTarget: 'album',
        defaultOrientation: 'landscape',
        defaultTheme: 'garden',
        instruction: t('event_home.print_pack.instructions.album'),
    },
    invitation_insert: {
        title: t('event_home.print_pack.presets.invitation_insert.title'),
        body: t('event_home.print_pack.presets.invitation_insert.body'),
        defaultTarget: 'invitation',
        defaultOrientation: 'portrait',
        defaultTheme: 'classic',
        instruction: t('event_home.print_pack.instructions.invitation'),
    },
    wall_sign: {
        title: t('event_home.print_pack.presets.wall_sign.title'),
        body: t('event_home.print_pack.presets.wall_sign.body'),
        defaultTarget: 'wall',
        defaultOrientation: 'portrait',
        defaultTheme: 'midnight',
        instruction: t('event_home.print_pack.instructions.wall'),
    },
    small_card: {
        title: t('event_home.print_pack.presets.small_card.title'),
        body: t('event_home.print_pack.presets.small_card.body'),
        defaultTarget: 'album',
        defaultOrientation: 'landscape',
        defaultTheme: 'classic',
        instruction: t('event_home.print_pack.instructions.album'),
    },
}));

const themeMeta: Record<ThemeKey, {
    label: string;
    imageUrl: string;
    tint: string;
    textColor: string;
    mutedColor: string;
    qrFrame: string;
}>= {
    bloom: {
        label: 'Bloom',
        imageUrl: '/images/qr-themes/bloom.jpg',
        tint: 'rgba(255,247,241,0.82)',
        textColor: '#2f201b',
        mutedColor: '#694f44',
        qrFrame: '#fffaf5',
    },
    garden: {
        label: 'Garden',
        imageUrl: '/images/qr-themes/garden.jpg',
        tint: 'rgba(247,250,244,0.84)',
        textColor: '#243026',
        mutedColor: '#5f6c60',
        qrFrame: '#fcfffb',
    },
    classic: {
        label: 'Classic',
        imageUrl: '/images/qr-themes/classic.jpg',
        tint: 'rgba(255,255,255,0.88)',
        textColor: '#241d19',
        mutedColor: '#655b53',
        qrFrame: '#ffffff',
    },
    midnight: {
        label: 'Midnight',
        imageUrl: '/images/qr-themes/midnight.jpg',
        tint: 'rgba(25,22,28,0.72)',
        textColor: '#f7efe4',
        mutedColor: '#d3c7bc',
        qrFrame: '#ffffff',
    },
};

const orientationMeta: Record<Orientation, { width: number; height: number; label: string }> = {
    portrait: { width: 1240, height: 1754, label: t('event_home.print_pack.orientation.portrait') },
    landscape: { width: 1754, height: 1240, label: t('event_home.print_pack.orientation.landscape') },
};

const selectedTargetMeta = computed(
    () => props.targets.find((target) => target.key === selectedTarget.value) ?? props.targets[0] ?? null,
);
const activePresetMeta = computed(() => presetMeta.value[selectedPreset.value]);
const activeThemeMeta = computed(() => themeMeta[selectedTheme.value]);
const activeOrientationMeta = computed(() => orientationMeta[selectedOrientation.value]);
const isLandscape = computed(() => selectedOrientation.value === 'landscape');

const controlsExpanded = computed(() => layoutMode.value === 'controls');
const layoutClass = computed(() =>
    controlsExpanded.value
        ? 'xl:grid-cols-[minmax(0,1.45fr)_minmax(20rem,0.55fr)]'
        : 'xl:grid-cols-[minmax(20rem,0.42fr)_minmax(0,0.58fr)]',
);
const previewAspectRatio = computed(() => `${activeOrientationMeta.value.width} / ${activeOrientationMeta.value.height}`);
const previewDisplayWidth = computed(() => {
    if (previewViewportWidth.value === 0) {
        return null;
    }

    const layoutMaxWidth = controlsExpanded.value ? 560 : 860;

    return Math.min(previewViewportWidth.value, layoutMaxWidth);
});

const posterFilenameBase = computed(() => {
    const slug = props.eventName
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

    return `${slug || 'eventsmart'}-${selectedPreset.value}-${selectedTarget.value}-${selectedTheme.value}`;
});

const targetLabel = computed(() => {
    return t(`event_home.print_pack.targets.${selectedTarget.value}`);
});

const footerLabel = computed(() => {
    if (selectedTarget.value === 'invitation') {
        return t('event_home.print_pack.footer.invitation_ready');
    }

    return t('event_home.album.code_label', { code: props.albumAccessCode });
});

const applyPreset = (preset: PrintPackPreset): void => {
    const config = presetMeta.value[preset];
    selectedPreset.value = preset;
    selectedTarget.value = config.defaultTarget;
    selectedOrientation.value = config.defaultOrientation;
    selectedTheme.value = config.defaultTheme;
};

const wrapCanvasText = (
    context: CanvasRenderingContext2D,
    text: string,
    maxWidth: number,
): string[] => {
    const words = text.split(/\s+/).filter(Boolean);
    const lines: string[] = [];
    let current = '';

    for (const word of words) {
        const next = current === '' ? word : `${current} ${word}`;
        if (context.measureText(next).width <= maxWidth || current === '') {
            current = next;
            continue;
        }

        lines.push(current);
        current = word;
    }

    if (current !== '') {
        lines.push(current);
    }

    return lines;
};

const loadImage = (src: string): Promise<HTMLImageElement> => {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error(`Unable to load image: ${src}`));
        image.src = src;
    });
};

const drawCoverImage = (
    context: CanvasRenderingContext2D,
    image: HTMLImageElement,
    width: number,
    height: number,
): void => {
    const scale = Math.max(width / image.width, height / image.height);
    const drawWidth = image.width * scale;
    const drawHeight = image.height * scale;
    const x = (width - drawWidth) / 2;
    const y = (height - drawHeight) / 2;

    context.drawImage(image, x, y, drawWidth, drawHeight);
};

const renderPosterCanvas = async (): Promise<HTMLCanvasElement | null> => {
    const target = selectedTargetMeta.value;
    if (target === null) {
        return null;
    }

    const size = orientationMeta[selectedOrientation.value];
    const theme = activeThemeMeta.value;
    const qrImage = await loadImage(target.qrDataUrl);
    const backgroundImage = await loadImage(theme.imageUrl);

    const canvas = document.createElement('canvas');
    canvas.width = size.width;
    canvas.height = size.height;

    const context = canvas.getContext('2d');
    if (context === null) {
        return null;
    }

    drawCoverImage(context, backgroundImage, size.width, size.height);
    context.fillStyle = theme.tint;
    context.fillRect(0, 0, size.width, size.height);

    const padding = size.width * (isLandscape.value ? 0.075 : 0.1);
    const qrSize = isLandscape.value ? Math.min(size.width * 0.27, size.height * 0.39) : size.width * 0.36;
    const qrX = (size.width - qrSize) / 2;
    const qrY = size.height * (isLandscape.value ? 0.41 : 0.42);
    const titleWidth = size.width - padding * 2;

    context.textAlign = 'center';
    context.fillStyle = theme.mutedColor;
    context.font = `${Math.round(size.width * 0.018)}px Inter, Arial, sans-serif`;
    context.fillText(activePresetMeta.value.title.toUpperCase(), size.width / 2, size.height * (isLandscape.value ? 0.1 : 0.11));

    context.fillStyle = theme.textColor;
    context.font = `600 ${Math.round(size.width * (isLandscape.value ? 0.05 : 0.062))}px Georgia, serif`;
    const titleLines = wrapCanvasText(context, props.eventName, titleWidth);
    titleLines.slice(0, 3).forEach((line, index) => {
        context.fillText(
            line,
            size.width / 2,
            size.height * (isLandscape.value ? 0.18 : 0.19) + index * size.width * (isLandscape.value ? 0.043 : 0.055),
        );
    });

    context.fillStyle = theme.mutedColor;
    context.font = `${Math.round(size.width * (isLandscape.value ? 0.018 : 0.024))}px Inter, Arial, sans-serif`;
    const instructionLines = wrapCanvasText(context, activePresetMeta.value.instruction, size.width - padding * 2.2);
    instructionLines.slice(0, 4).forEach((line, index) => {
        context.fillText(
            line,
            size.width / 2,
            size.height * (isLandscape.value ? 0.31 : 0.33) + index * size.width * (isLandscape.value ? 0.022 : 0.03),
        );
    });

    const framePadding = Math.round(qrSize * 0.12);
    context.fillStyle = theme.qrFrame;
    context.beginPath();
    context.roundRect(qrX - framePadding, qrY - framePadding, qrSize + framePadding * 2, qrSize + framePadding * 2, 28);
    context.fill();
    context.drawImage(qrImage, qrX, qrY, qrSize, qrSize);

    context.fillStyle = theme.mutedColor;
    context.font = `${Math.round(size.width * 0.018)}px Inter, Arial, sans-serif`;
    context.fillText(
        t('event_home.print_pack.footer.scan_hint'),
        size.width / 2,
        qrY + qrSize + size.width * (isLandscape.value ? 0.032 : 0.05),
    );

    context.fillStyle = theme.textColor;
    context.font = `600 ${Math.round(size.width * (isLandscape.value ? 0.018 : 0.022))}px Inter, Arial, sans-serif`;
    const footerLines = wrapCanvasText(context, footerLabel.value, size.width - padding * 2.4);
    footerLines.slice(0, 2).forEach((line, index) => {
        context.fillText(
            line,
            size.width / 2,
            size.height * (isLandscape.value ? 0.82 : 0.85) + index * size.width * (isLandscape.value ? 0.02 : 0.026),
        );
    });

    context.fillStyle = theme.mutedColor;
    context.font = `${Math.round(size.width * (isLandscape.value ? 0.015 : 0.017))}px Inter, Arial, sans-serif`;
    const urlLines = wrapCanvasText(context, target.url, size.width - padding * 2.4);
    urlLines.slice(0, 2).forEach((line, index) => {
        context.fillText(
            line,
            size.width / 2,
            size.height * (isLandscape.value ? 0.89 : 0.91) + index * size.width * (isLandscape.value ? 0.017 : 0.022),
        );
    });

    return canvas;
};

const syncPreviewViewportWidth = (): void => {
    previewViewportWidth.value = previewViewport.value?.clientWidth ?? 0;
};

const refreshPreviewImage = async (): Promise<void> => {
    const renderToken = ++previewRenderToken;
    isRenderingPreview.value = true;
    previewRenderFailed.value = false;

    try {
        const canvas = await renderPosterCanvas();
        if (renderToken !== previewRenderToken) {
            return;
        }

        previewDataUrl.value = canvas?.toDataURL('image/png') ?? null;
        previewRenderFailed.value = canvas === null;
    } catch {
        if (renderToken !== previewRenderToken) {
            return;
        }

        previewDataUrl.value = null;
        previewRenderFailed.value = true;
    } finally {
        if (renderToken === previewRenderToken) {
            isRenderingPreview.value = false;
        }
    }
};

const copySelectedTarget = async (): Promise<void> => {
    if (
        selectedTargetMeta.value === null
        || typeof navigator === 'undefined'
        || !navigator.clipboard
        || typeof navigator.clipboard.writeText !== 'function'
    ) {
        toast.error(t('event_home.clipboard.unavailable'));
        return;
    }

    await navigator.clipboard.writeText(selectedTargetMeta.value.url);
    toast.success(t('event_home.print_pack.copy_success'));
};

const openSelectedTarget = (): void => {
    if (selectedTargetMeta.value === null) {
        return;
    }

    window.open(selectedTargetMeta.value.url, '_blank', 'noopener,noreferrer');
};

const downloadRaster = async (format: 'png' | 'jpeg'): Promise<void> => {
    const canvas = await renderPosterCanvas();
    if (canvas === null) {
        return;
    }

    const outputUrl = canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.94);
    const link = document.createElement('a');
    link.href = outputUrl;
    link.download = `${posterFilenameBase.value}.${format === 'png' ? 'png' : 'jpg'}`;
    link.click();
};

const downloadSvg = (): void => {
    if (selectedTargetMeta.value === null) {
        return;
    }

    const link = document.createElement('a');
    link.href = selectedTargetMeta.value.qrDataUrl;
    link.download = `${posterFilenameBase.value}-qr.svg`;
    link.click();
};

const printPack = async (): Promise<void> => {
    const canvas = await renderPosterCanvas();
    if (canvas === null) {
        return;
    }

    const posterDataUrl = canvas.toDataURL('image/png');
    const printWindow = window.open('', '_blank', 'width=1200,height=900');
    if (!printWindow) {
        return;
    }

    const html = `
        <html>
            <head>
                <title>${props.eventName} QR Studio</title>
                <style>
                    @page { margin: 0; size: auto; }
                    html, body { margin: 0; min-height: 100%; }
                    body {
                        display: grid;
                        place-items: center;
                        padding: 24px;
                        background: #efe8de;
                        font-family: Inter, Arial, sans-serif;
                    }
                    img {
                        width: min(820px, 100%);
                        height: auto;
                        display: block;
                        box-shadow: 0 28px 80px rgba(23,20,17,0.18);
                        border-radius: 32px;
                    }
                </style>
            </head>
            <body>
                <img id="print-pack" src="${posterDataUrl}" alt="QR print pack preview" />
                <script>
                    const image = document.getElementById('print-pack');
                    const runPrint = () => {
                        window.focus();
                        window.print();
                    };
                    if (image && image.complete) {
                        setTimeout(runPrint, 180);
                    } else if (image) {
                        image.addEventListener('load', () => setTimeout(runPrint, 180), { once: true });
                        image.addEventListener('error', () => setTimeout(runPrint, 180), { once: true });
                    } else {
                        setTimeout(runPrint, 180);
                    }
                <\/script>
            </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
};

watch(
    [selectedTarget, selectedPreset, selectedOrientation, selectedTheme, footerLabel, () => props.eventName],
    () => {
        void refreshPreviewImage();
    },
    { immediate: true },
);

onMounted(() => {
    syncPreviewViewportWidth();

    if (typeof ResizeObserver === 'undefined') {
        return;
    }

    previewResizeObserver = new ResizeObserver(() => {
        syncPreviewViewportWidth();
    });

    if (previewViewport.value) {
        previewResizeObserver.observe(previewViewport.value);
    }
});

onBeforeUnmount(() => {
    previewResizeObserver?.disconnect();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                    {{ t('event_home.print_pack.layout_title') }}
                </p>
                <p class="mt-1 text-sm text-zinc-600">
                    {{ t('event_home.print_pack.layout_description') }}
                </p>
            </div>

            <div class="inline-flex rounded-full bg-white p-1 ring-1 ring-black/8">
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="layoutMode === 'preview' ? 'bg-[#171411] text-white' : 'text-zinc-600 hover:text-zinc-900'"
                    @click="layoutMode = 'preview'"
                >
                    {{ t('event_home.print_pack.layout.preview_focus') }}
                </button>
                <button
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="layoutMode === 'controls' ? 'bg-[#171411] text-white' : 'text-zinc-600 hover:text-zinc-900'"
                    @click="layoutMode = 'controls'"
                >
                    {{ t('event_home.print_pack.layout.controls_focus') }}
                </button>
            </div>
        </div>

        <div class="grid gap-8" :class="layoutClass">
        <aside class="space-y-6 border-b border-black/6 pb-6 xl:max-h-[calc(100vh-13rem)] xl:overflow-y-auto xl:border-r xl:border-b-0 xl:pb-0 xl:pr-6">
            <section>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                    {{ t('event_home.print_pack.presets_title') }}
                </p>
                <div class="mt-3 grid gap-2.5" :class="controlsExpanded ? 'xl:grid-cols-2' : ''">
                    <button
                        v-for="preset in presetKeys"
                        :key="preset"
                        type="button"
                        :class="[
                            'rounded-2xl px-4 py-4 text-left transition',
                            selectedPreset === preset
                                ? 'bg-[#171411] text-white'
                                : 'bg-white text-[#171411] ring-1 ring-black/8 hover:ring-black/16',
                        ]"
                        @click="applyPreset(preset)"
                    >
                        <p class="font-semibold">{{ presetMeta[preset].title }}</p>
                        <p class="mt-1.5 text-sm leading-6" :class="selectedPreset === preset ? 'text-white/78' : 'text-zinc-600'">
                            {{ presetMeta[preset].body }}
                        </p>
                    </button>
                </div>
            </section>

            <section class="space-y-5 border-t border-black/6 pt-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.target_title') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-for="target in targets"
                            :key="target.key"
                            type="button"
                            :class="[
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                selectedTarget === target.key
                                    ? 'bg-[#171411] text-white'
                                    : 'bg-white text-[#171411] ring-1 ring-black/8 hover:ring-black/16',
                            ]"
                            @click="selectedTarget = target.key"
                        >
                            {{ t(`event_home.print_pack.targets.${target.key}`) }}
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.orientation_title') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-for="orientation in orientations"
                            :key="orientation"
                            type="button"
                            :class="[
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                selectedOrientation === orientation
                                    ? 'bg-[#171411] text-white'
                                    : 'bg-white text-[#171411] ring-1 ring-black/8 hover:ring-black/16',
                            ]"
                            @click="selectedOrientation = orientation"
                        >
                            {{ orientationMeta[orientation].label }}
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.background_title') }}
                    </p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2" :class="controlsExpanded ? 'xl:grid-cols-2' : 'xl:grid-cols-1'">
                        <button
                            v-for="theme in themeKeys"
                            :key="theme"
                            type="button"
                            :class="[
                                'overflow-hidden rounded-2xl text-left transition',
                                selectedTheme === theme
                                    ? 'ring-2 ring-[#171411]'
                                    : 'ring-1 ring-black/8 hover:ring-black/16',
                            ]"
                            @click="selectedTheme = theme"
                        >
                            <div class="h-20 bg-cover bg-center" :style="{ backgroundImage: `url(${themeMeta[theme].imageUrl})` }" />
                            <div class="bg-white px-4 py-3">
                                <p class="font-semibold text-[#171411]">{{ themeMeta[theme].label }}</p>
                            </div>
                        </button>
                    </div>
                    <p class="mt-3 text-xs leading-5 text-zinc-500">
                        `public/images/qr-themes`
                    </p>
                </div>
            </section>

            <section class="space-y-3 border-t border-black/6 pt-5">
                <Button class="w-full rounded-full" @click="printPack">
                    <Printer class="mr-2 size-4" />
                    {{ t('event_home.print_pack.actions.print_pdf') }}
                </Button>
                <Button variant="outline" class="w-full rounded-full" @click="downloadRaster('png')">
                    <FileOutput class="mr-2 size-4" />
                    {{ t('event_home.print_pack.actions.download_png') }}
                </Button>
                <div class="flex flex-wrap gap-2">
                    <Button variant="ghost" class="rounded-full px-0 text-sm text-zinc-600 hover:bg-transparent hover:text-zinc-900" @click="downloadRaster('jpeg')">
                        {{ t('event_home.print_pack.actions.download_jpg') }}
                    </Button>
                    <Button variant="ghost" class="rounded-full px-0 text-sm text-zinc-600 hover:bg-transparent hover:text-zinc-900" @click="downloadSvg">
                        {{ t('event_home.print_pack.actions.download_svg') }}
                    </Button>
                    <Button variant="ghost" class="rounded-full px-0 text-sm text-zinc-600 hover:bg-transparent hover:text-zinc-900" @click="openSelectedTarget">
                        <ExternalLink class="mr-1.5 size-3.5" />
                        {{ t('event_home.print_pack.actions.open_target') }}
                    </Button>
                    <Button variant="ghost" class="rounded-full px-0 text-sm text-zinc-600 hover:bg-transparent hover:text-zinc-900" @click="copySelectedTarget">
                        <Copy class="mr-1.5 size-3.5" />
                        {{ t('event_home.print_pack.actions.copy_target') }}
                    </Button>
                </div>
            </section>
        </aside>

        <section>
            <div class="xl:sticky xl:top-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.preview_title') }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-zinc-600">
                        {{ activePresetMeta.body }}
                    </p>
                </div>
                <div class="text-sm text-zinc-500">
                    {{ targetLabel }}
                </div>
            </div>

            <div ref="previewViewport" class="mt-5">
                <div class="mx-auto" :style="previewDisplayWidth ? { width: `${previewDisplayWidth}px` } : undefined">
                    <div
                        class="relative"
                        :style="{ aspectRatio: previewAspectRatio }"
                    >
                        <img
                            v-if="previewDataUrl"
                            :src="previewDataUrl"
                            :alt="t('event_home.print_pack.preview_alt')"
                            class="block h-full w-full rounded-[2rem] object-cover shadow-[0_28px_80px_rgba(23,20,17,0.18)]"
                        />
                        <div
                            v-else
                            class="absolute inset-0 flex items-center justify-center rounded-[2rem] bg-[linear-gradient(135deg,#f4ece1,#e8dccd)]"
                        >
                            <div v-if="isRenderingPreview" class="h-14 w-14 animate-pulse rounded-full bg-white/80 shadow-[0_14px_40px_rgba(23,20,17,0.12)]" />
                            <div
                                v-else-if="previewRenderFailed"
                                class="rounded-full bg-white/88 px-5 py-2 text-sm font-medium text-zinc-600 shadow-[0_14px_40px_rgba(23,20,17,0.12)]"
                            >
                                {{ t('event_home.print_pack.preview_title') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
        </div>
    </div>
</template>
