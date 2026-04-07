<script setup lang="ts">
import { Copy, ExternalLink, Printer } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';

type QrPosterTheme = {
    id: string;
    label: string;
    backgroundPath: string;
    paperColor: string;
    overlay: string;
};

const props = defineProps<{
    eventName: string;
    albumUrl: string;
    albumAccessCode: string;
    albumQrDataUrl: string;
}>();

const { t } = useTranslations();
const cinzelFontFamily = '"Cinzel", var(--font-serif)';
const cormorantFontFamily = '"Cormorant Garamond", var(--font-serif)';

const qrPosterThemes: QrPosterTheme[] = [
    {
        id: 'beige-simple',
        label: 'Beige Simple',
        backgroundPath: '/qr-bg-themes/beige-simple.svg',
        paperColor: '#f6efe6',
        overlay: 'linear-gradient(180deg, rgba(255,248,243,0.60), rgba(255,252,249,0.78))',
    },
    {
        id: 'blue-modern',
        label: 'Blue Modern',
        backgroundPath: '/qr-bg-themes/blue-modern.svg',
        paperColor: '#e9f1fb',
        overlay: 'linear-gradient(180deg, rgba(250,252,255,0.48), rgba(250,252,255,0.72))',
    },
    {
        id: 'cream-love',
        label: 'Cream Love',
        backgroundPath: '/qr-bg-themes/cream-love.svg',
        paperColor: '#f7efe5',
        overlay: 'linear-gradient(180deg, rgba(255,248,241,0.56), rgba(255,251,246,0.76))',
    },
    {
        id: 'pink-minimal',
        label: 'Pink Minimal',
        backgroundPath: '/qr-bg-themes/pink-minimal.svg',
        paperColor: '#f6e3e9',
        overlay: 'linear-gradient(180deg, rgba(255,246,248,0.56), rgba(255,250,251,0.76))',
    },
    {
        id: 'pink-transparent',
        label: 'Pink Transparent',
        backgroundPath: '/qr-bg-themes/pink-transparent.svg',
        paperColor: '#f4e6ea',
        overlay: 'linear-gradient(180deg, rgba(255,249,250,0.64), rgba(255,252,252,0.82))',
    },
    {
        id: 'simple-transparent',
        label: 'Simple Transparent',
        backgroundPath: '/qr-bg-themes/simple-transparent.svg',
        paperColor: '#f4efe8',
        overlay: 'linear-gradient(180deg, rgba(255,251,247,0.66), rgba(255,253,251,0.84))',
    },
];

type QrPosterOrientation = 'portrait' | 'landscape';

const activeThemeId = ref<string>(qrPosterThemes[0].id);
const orientation = ref<QrPosterOrientation>('portrait');

const eyebrowText = ref<string>(t('event_home.print_pack.default_eyebrow'));
const titleText = ref<string>(props.eventName);
const bodyText = ref<string>(t('event_home.print_pack.instructions.album'));
const scanHintText = ref<string>(t('event_home.print_pack.footer.scan_hint'));
const footerText = ref<string>(
    props.albumAccessCode.trim().length > 0
        ? props.albumAccessCode
        : t('event_home.print_pack.footer.invitation_ready'),
);
const urlText = ref<string>(props.albumUrl);

const previewSurface = ref<HTMLElement | null>(null);
const previewScale = ref<number>(1);
let resizeObserver: ResizeObserver | null = null;

const activeTheme = computed<QrPosterTheme>(() => {
    return qrPosterThemes.find((theme) => theme.id === activeThemeId.value) ?? qrPosterThemes[0];
});

const orientationLabel = computed(() => {
    return orientation.value === 'portrait'
        ? t('event_home.print_pack.orientation.portrait')
        : t('event_home.print_pack.orientation.landscape');
});

const posterAspectRatio = computed(() => {
    return orientation.value === 'portrait' ? '1 / 1.4142' : '1.4142 / 1';
});

const posterBaseWidth = computed(() => {
    return orientation.value === 'portrait' ? 900 : 1280;
});

const previewPosterStyle = computed<Record<string, string>>(() => {
    return {
        '--poster-scale': previewScale.value.toFixed(4),
        aspectRatio: posterAspectRatio.value,
        backgroundColor: activeTheme.value.paperColor,
        backgroundImage: `url(${activeTheme.value.backgroundPath})`,
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        backgroundSize: 'cover',
    };
});

const previewOverlayStyle = computed<Record<string, string>>(() => {
    return {
        background: activeTheme.value.overlay,
    };
});

const previewShellClass = computed(() => {
    return orientation.value === 'portrait'
        ? 'grid grid-rows-[auto_auto_minmax(0,1fr)_auto_auto] justify-items-center'
        : 'grid h-full grid-cols-[minmax(0,1fr)_minmax(240px,32%)] grid-rows-[auto_minmax(0,1fr)_auto] items-center';
});

const qrBlockClass = computed(() => {
    return orientation.value === 'portrait'
        ? 'row-start-3 flex min-h-0 w-full items-center justify-center'
        : 'col-start-2 row-span-3 flex h-full items-center justify-center';
});

const textColumnClass = computed(() => {
    return orientation.value === 'portrait'
        ? 'w-full text-center'
        : 'col-start-1 row-span-3 flex h-full w-full flex-col justify-between text-left';
});

const portraitTextColumnStyle = computed<Record<string, string> | undefined>(() => {
    if (orientation.value !== 'portrait') {
        return undefined;
    }

    return {
        maxWidth: 'calc(100% - calc(92px * var(--poster-scale)))',
    };
});

const qrFrameStyle = computed<Record<string, string>>(() => {
    const size = orientation.value === 'portrait'
        ? 'min(100%, calc(290px * var(--poster-scale)))'
        : 'min(100%, calc(320px * var(--poster-scale)))';

    return {
        width: size,
        padding: 'calc(12px * var(--poster-scale))',
        borderRadius: 'calc(22px * var(--poster-scale))',
        backgroundColor: 'rgba(255,255,255,0.92)',
        boxShadow: '0 calc(18px * var(--poster-scale)) calc(48px * var(--poster-scale)) rgba(74, 50, 34, 0.10)',
    };
});

const updatePreviewScale = (): void => {
    if (!previewSurface.value) {
        previewScale.value = 1;
        return;
    }

    const currentWidth = previewSurface.value.clientWidth;
    if (currentWidth === 0) {
        return;
    }

    previewScale.value = Math.max(0.42, Math.min(1, currentWidth / posterBaseWidth.value));
};

const observePreviewScale = async (): Promise<void> => {
    await nextTick();

    resizeObserver?.disconnect();

    if (!previewSurface.value) {
        return;
    }

    resizeObserver = new ResizeObserver(() => {
        updatePreviewScale();
    });

    resizeObserver.observe(previewSurface.value);
    updatePreviewScale();
};

const escapeHtml = (value: string): string => {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
};

const htmlWithLineBreaks = (value: string): string => {
    return escapeHtml(value).replace(/\n/g, '<br />');
};

const printPoster = (): void => {
    const printWindow = window.open('', '_blank', 'noopener,noreferrer,width=1240,height=920');
    if (!printWindow) {
        return;
    }

    const printOrientation = orientation.value === 'portrait' ? 'portrait' : 'landscape';
    const titleAlignment = orientation.value === 'portrait' ? 'center' : 'left';
    const subtitleAlignment = titleAlignment;
    const qrLayout = orientation.value === 'portrait'
        ? 'display:grid;grid-template-rows:auto auto minmax(0,1fr) auto auto;justify-items:center;'
        : 'display:grid;grid-template-columns:minmax(0,1fr) 31%;grid-template-rows:auto minmax(0,1fr) auto;column-gap:44px;align-items:center;';
    const textColumnLayout = orientation.value === 'portrait'
        ? 'width:100%;max-width:calc(100% - 92px);text-align:center;'
        : 'grid-column:1;grid-row:1 / span 3;display:flex;height:100%;flex-direction:column;justify-content:space-between;text-align:left;';
    const qrPlacement = orientation.value === 'portrait'
        ? 'grid-row:3;display:flex;width:100%;align-items:center;justify-content:center;'
        : 'grid-column:2;grid-row:1 / span 3;display:flex;height:100%;align-items:center;justify-content:center;';

    const markup = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>${escapeHtml(titleText.value)}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Cormorant+Garamond:wght@500;600;700&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 ${printOrientation};
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            background: #efe7dc;
            font-family: "Montserrat", sans-serif;
        }

        body {
            display: grid;
            place-items: center;
            padding: 0;
        }

        .poster {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            color: #1f1711;
            background-color: ${activeTheme.value.paperColor};
            background-image: url('${activeTheme.value.backgroundPath}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .poster::before {
            content: "";
            position: absolute;
            inset: 0;
            background: ${activeTheme.value.overlay};
        }

        .poster-inner {
            position: relative;
            z-index: 1;
            height: 100%;
            padding: ${orientation.value === 'portrait' ? '42px 46px 38px' : '44px 48px'};
            ${qrLayout}
        }

        .text-column {
            ${textColumnLayout}
        }

        .eyebrow {
            font-family: "Cinzel", serif;
            font-size: ${orientation.value === 'portrait' ? '13px' : '12px'};
            font-weight: 600;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            text-align: ${subtitleAlignment};
            color: rgba(58, 39, 25, 0.72);
        }

        .title {
            margin: ${orientation.value === 'portrait' ? '16px 0 0' : '14px 0 0'};
            font-family: "Cormorant Garamond", serif;
            font-size: ${orientation.value === 'portrait' ? '64px' : '58px'};
            line-height: 0.94;
            font-weight: 600;
            letter-spacing: -0.03em;
            text-align: ${titleAlignment};
        }

        .body {
            margin: ${orientation.value === 'portrait' ? '18px 0 0' : '18px 0 0'};
            max-width: ${orientation.value === 'portrait' ? 'none' : '610px'};
            font-size: ${orientation.value === 'portrait' ? '18px' : '17px'};
            line-height: 1.75;
            text-align: ${titleAlignment};
            color: rgba(46, 31, 21, 0.82);
        }

        .qr-slot {
            ${qrPlacement}
        }

        .qr-frame {
            width: ${orientation.value === 'portrait' ? '290px' : '320px'};
            padding: 12px;
            border-radius: 22px;
            background: rgba(255,255,255,0.92);
            box-shadow: 0 18px 48px rgba(74, 50, 34, 0.10);
        }

        .qr-frame img {
            display: block;
            width: 100%;
            height: auto;
        }

        .scan-hint {
            margin: ${orientation.value === 'portrait' ? '18px 0 0' : '0'};
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-align: ${titleAlignment};
            color: rgba(36, 24, 16, 0.78);
        }

        .footer-wrap {
            margin-top: ${orientation.value === 'portrait' ? '18px' : '22px'};
            display: grid;
            gap: 10px;
        }

        .footer {
            font-family: "Cinzel", serif;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-align: ${titleAlignment};
            color: rgba(58, 39, 25, 0.70);
        }

        .url {
            font-size: 13px;
            line-height: 1.6;
            text-align: ${titleAlignment};
            color: rgba(46, 31, 21, 0.78);
            word-break: break-word;
        }
    </style>
</head>
<body>
    <article class="poster">
        <div class="poster-inner">
            <div class="text-column">
                <div>
                    <div class="eyebrow">${htmlWithLineBreaks(eyebrowText.value)}</div>
                    <div class="title">${htmlWithLineBreaks(titleText.value)}</div>
                    <div class="body">${htmlWithLineBreaks(bodyText.value)}</div>
                </div>
                <div class="footer-wrap">
                    <div class="scan-hint">${htmlWithLineBreaks(scanHintText.value)}</div>
                    <div class="footer">${htmlWithLineBreaks(footerText.value)}</div>
                    <div class="url">${htmlWithLineBreaks(urlText.value)}</div>
                </div>
            </div>
            <div class="qr-slot">
                <div class="qr-frame">
                    <img src="${props.albumQrDataUrl}" alt="${escapeHtml(t('event_home.print_pack.preview_alt'))}" />
                </div>
            </div>
        </div>
    </article>
</body>
</html>`;

    printWindow.onload = () => {
        printWindow.focus();
        window.setTimeout(() => {
            printWindow.print();
        }, 180);
    };

    printWindow.document.open();
    printWindow.document.write(markup);
    printWindow.document.close();
};

const openAlbum = (): void => {
    window.open(props.albumUrl, '_blank', 'noopener,noreferrer');
};

const copyAlbumLink = async (): Promise<void> => {
    await navigator.clipboard.writeText(urlText.value);
};

onMounted(() => {
    void observePreviewScale();
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});

watch(orientation, () => {
    void observePreviewScale();
});
</script>

<template>
    <div class="grid gap-8 xl:grid-cols-[330px_minmax(0,1fr)] xl:items-start">
        <aside class="xl:max-h-[calc(100vh-7.5rem)] xl:overflow-y-auto xl:pr-3">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-neutral-500">
                        {{ t('event_home.print_pack.background_title') }}
                    </p>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ orientationLabel }}
                    </p>
                </div>

                <div class="inline-flex items-center rounded-full border border-neutral-200 bg-white/80 p-1 shadow-sm">
                    <Button
                        type="button"
                        size="sm"
                        :variant="orientation === 'portrait' ? 'default' : 'ghost'"
                        class="rounded-full px-3"
                        @click="orientation = 'portrait'"
                    >
                        {{ t('event_home.print_pack.orientation.portrait') }}
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :variant="orientation === 'landscape' ? 'default' : 'ghost'"
                        class="rounded-full px-3"
                        @click="orientation = 'landscape'"
                    >
                        {{ t('event_home.print_pack.orientation.landscape') }}
                    </Button>
                </div>
            </div>

            <div class="mt-5 flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden xl:flex-col xl:overflow-visible xl:pb-0">
                <button
                    v-for="theme in qrPosterThemes"
                    :key="theme.id"
                    type="button"
                    class="group min-w-[132px] shrink-0 snap-start text-left xl:min-w-0"
                    @click="activeThemeId = theme.id"
                >
                    <div
                        class="overflow-hidden rounded-[1.4rem] border transition duration-200"
                        :class="activeThemeId === theme.id ? 'border-neutral-950 shadow-[0_16px_36px_rgba(33,26,20,0.14)]' : 'border-neutral-200/80 opacity-80 group-hover:opacity-100'"
                    >
                        <div
                            class="aspect-[0.76] w-full"
                            :style="{
                                backgroundColor: theme.paperColor,
                                backgroundImage: `linear-gradient(180deg, rgba(255,250,246,0.32), rgba(255,252,249,0.60)), url(${theme.backgroundPath})`,
                                backgroundPosition: 'center',
                                backgroundRepeat: 'no-repeat',
                                backgroundSize: 'cover',
                            }"
                        />
                    </div>
                    <p class="mt-2 px-1 text-sm font-medium text-neutral-700">
                        {{ theme.label }}
                    </p>
                </button>
            </div>

            <div class="mt-6 space-y-5 border-t border-neutral-200 pt-5">
                <div class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-white/85 p-1 shadow-sm">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :title="t('event_home.print_pack.actions.print_pdf')"
                        @click="printPoster"
                    >
                        <Printer class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :title="t('event_home.print_pack.actions.copy_target')"
                        @click="void copyAlbumLink()"
                    >
                        <Copy class="size-4" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :title="t('event_home.print_pack.actions.open_target')"
                        @click="openAlbum"
                    >
                        <ExternalLink class="size-4" />
                    </Button>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                        {{ t('event_home.print_pack.copy_fields.eyebrow') }}
                    </label>
                    <Input
                        v-model="eyebrowText"
                        class="h-11 rounded-2xl border-neutral-200/90 bg-white/75"
                    />
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                        {{ t('event_home.print_pack.copy_fields.title') }}
                    </label>
                    <Textarea
                        v-model="titleText"
                        class="min-h-[108px] rounded-[1.4rem] border-neutral-200/90 bg-white/75 px-4 py-3"
                    />
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                        {{ t('event_home.print_pack.copy_fields.body') }}
                    </label>
                    <Textarea
                        v-model="bodyText"
                        class="min-h-[168px] rounded-[1.4rem] border-neutral-200/90 bg-white/75 px-4 py-3"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                            {{ t('event_home.print_pack.copy_fields.scan_hint') }}
                        </label>
                        <Textarea
                            v-model="scanHintText"
                            class="min-h-[100px] rounded-[1.4rem] border-neutral-200/90 bg-white/75 px-4 py-3"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                            {{ t('event_home.print_pack.copy_fields.footer') }}
                        </label>
                        <Textarea
                            v-model="footerText"
                            class="min-h-[100px] rounded-[1.4rem] border-neutral-200/90 bg-white/75 px-4 py-3"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                        {{ t('event_home.print_pack.copy_fields.url') }}
                    </label>
                    <Textarea
                        v-model="urlText"
                        class="min-h-[96px] rounded-[1.4rem] border-neutral-200/90 bg-white/75 px-4 py-3"
                    />
                </div>
            </div>
        </aside>

        <section class="xl:sticky xl:top-6">
            <div
                ref="previewSurface"
                class="mx-auto w-full max-w-[1120px]"
            >
                <article
                    class="relative w-full overflow-hidden shadow-[0_32px_80px_rgba(53,36,24,0.18)]"
                    :class="orientation === 'portrait' ? 'rounded-[2rem]' : 'rounded-[2.2rem]'"
                    :style="previewPosterStyle"
                >
                    <div class="absolute inset-0" :style="previewOverlayStyle" />

                    <div
                        class="relative z-10 h-full px-[calc(46px*var(--poster-scale))] py-[calc(42px*var(--poster-scale))]"
                        :class="previewShellClass"
                    >
                        <div :class="textColumnClass" :style="portraitTextColumnStyle">
                            <div>
                                <p
                                    class="text-[calc(0.78rem*var(--poster-scale))] font-semibold uppercase tracking-[0.24em] text-[#463021]/72"
                                    :class="orientation === 'portrait' ? 'text-center' : 'text-left'"
                                    :style="{ fontFamily: cinzelFontFamily }"
                                >
                                    {{ eyebrowText }}
                                </p>
                                <h2
                                    class="mt-[calc(16px*var(--poster-scale))] font-semibold leading-[0.94] tracking-[-0.03em] text-[#21160f]"
                                    :class="orientation === 'portrait' ? 'text-center' : 'text-left'"
                                    :style="{
                                        fontFamily: cormorantFontFamily,
                                        fontSize: `calc(${orientation === 'portrait' ? 4.05 : 3.6}rem * var(--poster-scale))`,
                                    }"
                                >
                                    {{ titleText }}
                                </h2>
                                <p
                                    class="mt-[calc(18px*var(--poster-scale))] whitespace-pre-line text-[#2e1f15]/82"
                                    :class="orientation === 'portrait' ? 'text-center' : 'max-w-[60ch] text-left'"
                                    :style="{ fontSize: `calc(${orientation === 'portrait' ? 1.15 : 1.05}rem * var(--poster-scale))`, lineHeight: '1.75' }"
                                >
                                    {{ bodyText }}
                                </p>
                            </div>

                            <div class="mt-[calc(18px*var(--poster-scale))] grid gap-[calc(10px*var(--poster-scale))]">
                                <p
                                    class="text-[#241810]/78"
                                    :class="orientation === 'portrait' ? 'text-center' : 'text-left'"
                                    :style="{ fontSize: 'calc(1rem * var(--poster-scale))', fontWeight: '600', letterSpacing: '0.02em' }"
                                >
                                    {{ scanHintText }}
                                </p>
                                <p
                                    class="uppercase text-[#463021]/70"
                                    :class="orientation === 'portrait' ? 'text-center' : 'text-left'"
                                    :style="{
                                        fontFamily: cinzelFontFamily,
                                        fontSize: 'calc(0.72rem * var(--poster-scale))',
                                        fontWeight: '600',
                                        letterSpacing: '0.18em',
                                    }"
                                >
                                    {{ footerText }}
                                </p>
                                <p
                                    class="break-words text-[#2e1f15]/78"
                                    :class="orientation === 'portrait' ? 'text-center' : 'text-left'"
                                    :style="{ fontSize: 'calc(0.78rem * var(--poster-scale))', lineHeight: '1.6' }"
                                >
                                    {{ urlText }}
                                </p>
                            </div>
                        </div>

                        <div :class="qrBlockClass">
                            <div :style="qrFrameStyle">
                                <img
                                    :src="albumQrDataUrl"
                                    :alt="t('event_home.print_pack.preview_alt')"
                                    class="block h-auto w-full"
                                />
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
