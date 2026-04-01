<script setup lang="ts">
import {
    ChevronLeft,
    ChevronRight,
    Copy,
    Download,
    ExternalLink,
    Printer,
    ScanLine,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import { analyzeQrPrintThemeSvg } from '@/lib/qr-print-svg-layout';
import {
    qrPrintThemeMap,
    qrPrintThemes,
} from '@/lib/qr-print-themes';
import type { QrPrintLayoutConfig, QrPrintOrientation, QrPrintThemeConfig, QrPrintThemeId } from '@/lib/qr-print-themes';

const props = defineProps<{
    eventName: string;
    albumAccessCode: string;
    albumUrl: string;
    albumQrDataUrl: string;
}>();

const { t } = useTranslations();

const themeCarousel = ref<HTMLElement | null>(null);
const previewViewport = ref<HTMLElement | null>(null);
const previewDataUrl = ref<string | null>(null);
const previewViewportWidth = ref(0);
const isRenderingPreview = ref(false);
const previewRenderFailed = ref(false);
const derivedSvgLayout = ref<Record<QrPrintOrientation, Partial<QrPrintLayoutConfig>>>({
    portrait: {},
    landscape: {},
});

const selectedTheme = ref<QrPrintThemeId>('beige_simple');
const selectedOrientation = ref<QrPrintOrientation>('portrait');
const layoutMode = ref<'preview' | 'controls'>('preview');

const eyebrowText = ref(t('event_home.print_pack.default_eyebrow'));
const titleText = ref(props.eventName);
const bodyText = ref(t('event_home.print_pack.instructions.album'));
const scanHintText = ref(t('event_home.print_pack.footer.scan_hint'));
const footerText = ref(t('event_home.album.code_label', { code: props.albumAccessCode }));
const urlText = ref(props.albumUrl);
const canvasBackground = ref(qrPrintThemeMap.beige_simple.defaultCanvasColor);

let previewResizeObserver: ResizeObserver | null = null;
let previewRenderToken = 0;

const activeTheme = computed(() => qrPrintThemeMap[selectedTheme.value]);
const mergeTextBlock = (
    base: QrPrintLayoutConfig['title'],
    derived?: Partial<QrPrintLayoutConfig['title']>,
): QrPrintLayoutConfig['title'] => {
    if (!derived) {
        return base;
    }

    return {
        ...base,
        ...derived,
    };
};
const activeLayout = computed<QrPrintLayoutConfig>(() => {
    const base = activeTheme.value.layout[selectedOrientation.value];
    const derived = derivedSvgLayout.value[selectedOrientation.value] ?? {};

    return {
        ...base,
        ...derived,
        eyebrow: mergeTextBlock(base.eyebrow, derived.eyebrow),
        title: mergeTextBlock(base.title, derived.title),
        body: mergeTextBlock(base.body, derived.body),
        scanHint: mergeTextBlock(base.scanHint, derived.scanHint),
        footer: mergeTextBlock(base.footer, derived.footer),
        url: mergeTextBlock(base.url, derived.url),
    };
});
const orientations: QrPrintOrientation[] = ['portrait', 'landscape'];
const controlsExpanded = computed(() => layoutMode.value === 'controls');
const layoutClass = computed(() =>
    controlsExpanded.value
        ? 'xl:grid-cols-[minmax(0,1.5fr)_minmax(20rem,0.5fr)]'
        : 'xl:grid-cols-[minmax(19rem,0.46fr)_minmax(0,0.54fr)]',
);
const previewAspectRatio = computed(() => {
    if (selectedOrientation.value === 'landscape') {
        return '1754 / 1240';
    }

    return '1240 / 1754';
});
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

    return `${slug || 'eventsmart'}-album-${selectedTheme.value}-${selectedOrientation.value}`;
});

const themeCardStyle = (theme: QrPrintThemeConfig): Record<string, string> => {
    return {
        backgroundColor: theme.pickerCanvasColor,
        backgroundImage: `url(${theme.artworkUrl})`,
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        backgroundSize: 'cover',
    };
};

const selectTheme = (themeId: QrPrintThemeId): void => {
    const theme = qrPrintThemeMap[themeId];
    selectedTheme.value = themeId;
    canvasBackground.value = theme.defaultCanvasColor;
};

const scrollThemes = (direction: 'prev' | 'next'): void => {
    themeCarousel.value?.scrollBy({
        left: direction === 'next' ? 248 : -248,
        behavior: 'smooth',
    });
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

const fitTextBlock = (
    context: CanvasRenderingContext2D,
    themeBlock: QrPrintThemeConfig['layout'][QrPrintOrientation]['title'],
    text: string,
    canvasWidth: number,
): { lines: string[]; fontSize: number } => {
    const baseFontSize = canvasWidth * themeBlock.fontSize;
    const minFontSize = baseFontSize * themeBlock.minScale;
    const maxWidth = canvasWidth * themeBlock.width;
    let fontSize = baseFontSize;

    while (fontSize >= minFontSize) {
        context.font = `${themeBlock.fontWeight ?? '400'} ${fontSize}px ${themeBlock.fontFamily}`;
        const lines = wrapCanvasText(context, text, maxWidth);

        if (lines.length <= themeBlock.maxLines) {
            return { lines, fontSize };
        }

        fontSize -= Math.max(1.6, baseFontSize * 0.05);
    }

    context.font = `${themeBlock.fontWeight ?? '400'} ${minFontSize}px ${themeBlock.fontFamily}`;

    return {
        lines: wrapCanvasText(context, text, maxWidth).slice(0, themeBlock.maxLines),
        fontSize: minFontSize,
    };
};

const loadImage = (src: string): Promise<HTMLImageElement> => {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error(`Unable to load image: ${src}`));
        image.src = src;
    });
};

const drawContainedImage = (
    context: CanvasRenderingContext2D,
    image: HTMLImageElement,
    width: number,
    height: number,
): void => {
    const scale = Math.min(width / image.width, height / image.height);
    const drawWidth = image.width * scale;
    const drawHeight = image.height * scale;
    const x = (width - drawWidth) / 2;
    const y = (height - drawHeight) / 2;

    context.drawImage(image, x, y, drawWidth, drawHeight);
};

const drawTextBlock = (
    context: CanvasRenderingContext2D,
    text: string,
    themeBlock: QrPrintThemeConfig['layout'][QrPrintOrientation]['title'],
    width: number,
    height: number,
    color: string,
): void => {
    if (text.trim() === '') {
        return;
    }

    const fitted = fitTextBlock(context, themeBlock, text, width);
    const startY = height * themeBlock.y;
    const lineGap = fitted.fontSize * themeBlock.lineHeight;

    context.fillStyle = color;
    context.textAlign = 'center';
    context.font = `${themeBlock.fontWeight ?? '400'} ${fitted.fontSize}px ${themeBlock.fontFamily}`;

    fitted.lines.forEach((line, index) => {
        context.fillText(line, width * (themeBlock.centerX ?? 0.5), startY + index * lineGap);
    });
};

const renderPosterCanvas = async (): Promise<HTMLCanvasElement | null> => {
    if (typeof document !== 'undefined' && 'fonts' in document) {
        await document.fonts.ready;
    }

    const theme = activeTheme.value;
    const layout = activeLayout.value;
    const canvas = document.createElement('canvas');

    canvas.width = selectedOrientation.value === 'landscape' ? 1754 : 1240;
    canvas.height = selectedOrientation.value === 'landscape' ? 1240 : 1754;

    const context = canvas.getContext('2d');
    if (context === null) {
        return null;
    }

    const backgroundImage = await loadImage(theme.artworkUrl);
    const qrImage = await loadImage(props.albumQrDataUrl);

    context.fillStyle = theme.supportsCanvasColor ? canvasBackground.value : theme.defaultCanvasColor;
    context.fillRect(0, 0, canvas.width, canvas.height);
    drawContainedImage(context, backgroundImage, canvas.width, canvas.height);

    drawTextBlock(context, eyebrowText.value, layout.eyebrow, canvas.width, canvas.height, theme.mutedColor);
    drawTextBlock(context, titleText.value, layout.title, canvas.width, canvas.height, theme.textColor);
    drawTextBlock(context, bodyText.value, layout.body, canvas.width, canvas.height, theme.mutedColor);

    const qrSize = Math.min(canvas.width, canvas.height) * layout.qrSize;
    const qrX = canvas.width * (layout.qrCenterX ?? 0.5) - qrSize / 2;
    const qrY = canvas.height * layout.qrY;
    const framePadding = Math.min(canvas.width, canvas.height) * layout.qrFramePadding;

    context.fillStyle = theme.qrFrameColor;
    context.beginPath();
    context.roundRect(
        qrX - framePadding,
        qrY - framePadding,
        qrSize + framePadding * 2,
        qrSize + framePadding * 2,
        layout.qrCornerRadius,
    );
    context.fill();
    context.drawImage(qrImage, qrX, qrY, qrSize, qrSize);

    drawTextBlock(context, scanHintText.value, layout.scanHint, canvas.width, canvas.height, theme.mutedColor);
    drawTextBlock(context, footerText.value, layout.footer, canvas.width, canvas.height, theme.textColor);
    drawTextBlock(context, urlText.value, layout.url, canvas.width, canvas.height, theme.mutedColor);

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

const downloadRaster = async (): Promise<void> => {
    const canvas = await renderPosterCanvas();
    if (canvas === null) {
        return;
    }

    const outputUrl = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.href = outputUrl;
    link.download = `${posterFilenameBase.value}.png`;
    link.click();
};

const downloadSvg = (): void => {
    const link = document.createElement('a');
    link.href = props.albumQrDataUrl;
    link.download = `${posterFilenameBase.value}-qr.svg`;
    link.click();
};

const printPack = async (): Promise<void> => {
    const canvas = await renderPosterCanvas();
    if (canvas === null) {
        return;
    }

    const printWindow = window.open('', '_blank', 'width=1200,height=900');
    if (!printWindow) {
        return;
    }

    const posterDataUrl = canvas.toDataURL('image/png');
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
                    }
                    img {
                        width: min(920px, 100%);
                        height: auto;
                        display: block;
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

const copyAlbumLink = async (): Promise<void> => {
    if (
        typeof navigator === 'undefined'
        || !navigator.clipboard
        || typeof navigator.clipboard.writeText !== 'function'
    ) {
        toast.error(t('event_home.clipboard.unavailable'));
        return;
    }

    await navigator.clipboard.writeText(props.albumUrl);
    toast.success(t('event_home.print_pack.copy_success'));
};

const openAlbum = (): void => {
    window.open(props.albumUrl, '_blank', 'noopener,noreferrer');
};

watch(
    activeTheme,
    async (theme) => {
        derivedSvgLayout.value = await analyzeQrPrintThemeSvg(theme.artworkUrl, theme.layout);
    },
    { immediate: true },
);

watch(
    [
        selectedTheme,
        selectedOrientation,
        layoutMode,
        derivedSvgLayout,
        eyebrowText,
        titleText,
        bodyText,
        scanHintText,
        footerText,
        urlText,
        canvasBackground,
    ],
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
    <div class="grid gap-8" :class="layoutClass">
        <aside class="space-y-6 border-b border-black/6 pb-6 xl:max-h-[calc(100vh-11rem)] xl:overflow-y-auto xl:border-r xl:border-b-0 xl:pb-0 xl:pr-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
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

                <div class="inline-flex items-center rounded-full bg-white p-1 ring-1 ring-black/8">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full text-zinc-600 hover:text-zinc-900"
                        :aria-label="t('event_home.print_pack.actions.print_pdf')"
                        :title="t('event_home.print_pack.actions.print_pdf')"
                        @click="printPack"
                    >
                        <Printer class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full text-zinc-600 hover:text-zinc-900"
                        :aria-label="t('event_home.print_pack.actions.download_png')"
                        :title="t('event_home.print_pack.actions.download_png')"
                        @click="downloadRaster"
                    >
                        <Download class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full text-zinc-600 hover:text-zinc-900"
                        :aria-label="t('event_home.print_pack.actions.download_svg')"
                        :title="t('event_home.print_pack.actions.download_svg')"
                        @click="downloadSvg"
                    >
                        <ScanLine class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full text-zinc-600 hover:text-zinc-900"
                        :aria-label="t('event_home.print_pack.actions.copy_target')"
                        :title="t('event_home.print_pack.actions.copy_target')"
                        @click="copyAlbumLink"
                    >
                        <Copy class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="rounded-full text-zinc-600 hover:text-zinc-900"
                        :aria-label="t('event_home.print_pack.actions.open_target')"
                        :title="t('event_home.print_pack.actions.open_target')"
                        @click="openAlbum"
                    >
                        <ExternalLink class="size-4" />
                    </Button>
                </div>
            </div>

            <section class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[#171411]">
                        {{ t('event_home.print_pack.theme_title') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="icon" class="rounded-full" @click="scrollThemes('prev')">
                            <ChevronLeft class="size-4" />
                        </Button>
                        <Button variant="outline" size="icon" class="rounded-full" @click="scrollThemes('next')">
                            <ChevronRight class="size-4" />
                        </Button>
                    </div>
                </div>

                <div
                    ref="themeCarousel"
                    class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                >
                    <button
                        v-for="theme in qrPrintThemes"
                        :key="theme.id"
                        type="button"
                        class="w-[220px] shrink-0 snap-start text-left"
                        @click="selectTheme(theme.id)"
                    >
                        <div
                            :class="[
                                'overflow-hidden rounded-[1.7rem] border p-2.5 transition',
                                selectedTheme === theme.id
                                    ? 'border-neutral-950 ring-2 ring-neutral-950 shadow-sm'
                                    : 'border-black/10 hover:-translate-y-0.5 hover:shadow-sm',
                            ]"
                        >
                            <div class="overflow-hidden rounded-[1.2rem] border border-black/8">
                                <div
                                    class="relative aspect-[210/297] bg-cover bg-center"
                                    :style="themeCardStyle(theme)"
                                >
                                    <div class="absolute inset-x-[22%] top-[17%] text-center text-[0.58rem] font-semibold uppercase tracking-[0.22em] text-black/45">
                                        QR Album
                                    </div>
                                    <div
                                        class="absolute inset-x-[18%] top-[29%] text-center text-[1.55rem] leading-none text-black/75"
                                        :style="{ fontFamily: theme.layout.portrait.title.fontFamily }"
                                    >
                                        {{ eventName }}
                                    </div>
                                    <div class="absolute left-1/2 top-[58%] h-[31%] w-[33%] -translate-x-1/2 -translate-y-1/2 rounded-[1.6rem] bg-white/82 shadow-[0_12px_28px_rgba(23,20,17,0.12)]" />
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-[#171411]">
                                    {{ theme.label }}
                                </p>
                                <span
                                    class="rounded-full px-2.5 py-1 text-[0.68rem] font-medium"
                                    :class="selectedTheme === theme.id ? 'bg-neutral-950 text-white' : 'bg-black/5 text-neutral-700'"
                                >
                                    {{ selectedTheme === theme.id ? t('guests.shared.selected') : t('guests.actions.choose') }}
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.eyebrow') }}</label>
                    <Input v-model="eyebrowText" class="h-11 rounded-xl bg-white" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.title') }}</label>
                    <Input v-model="titleText" class="h-11 rounded-xl bg-white" />
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.body') }}</label>
                    <Textarea v-model="bodyText" class="min-h-24 rounded-xl bg-white" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.scan_hint') }}</label>
                    <Input v-model="scanHintText" class="h-11 rounded-xl bg-white" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.footer') }}</label>
                    <Input v-model="footerText" class="h-11 rounded-xl bg-white" />
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.url') }}</label>
                    <Input v-model="urlText" class="h-11 rounded-xl bg-white" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.orientation_title') }}</label>
                    <div class="flex flex-wrap gap-2">
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
                            {{ t(`event_home.print_pack.orientation.${orientation}`) }}
                        </button>
                    </div>
                </div>

                <div v-if="activeTheme.supportsCanvasColor" class="space-y-2">
                    <label class="text-sm font-medium text-[#171411]">{{ t('event_home.print_pack.copy_fields.background_fill') }}</label>
                    <div class="flex h-11 items-center gap-3 rounded-xl border border-black/10 bg-white px-3">
                        <input v-model="canvasBackground" type="color" class="h-7 w-7 rounded-md border-0 bg-transparent p-0" />
                        <span class="text-sm text-zinc-600">{{ canvasBackground }}</span>
                    </div>
                </div>
            </section>
        </aside>

        <section>
            <div class="xl:sticky xl:top-6">
                <div ref="previewViewport">
                    <div class="mx-auto" :style="previewDisplayWidth ? { width: `${previewDisplayWidth}px` } : undefined">
                        <div class="relative" :style="{ aspectRatio: previewAspectRatio }">
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
</template>
