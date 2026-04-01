<script setup lang="ts">
import { Copy, ExternalLink, FileOutput, Printer, QrCode } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

type PrintPackTarget = 'album' | 'wall' | 'invitation';
type PrintPackPreset = 'welcome_sign' | 'table_card' | 'invitation_insert' | 'wall_sign' | 'small_card';
type PaperSize = 'A4' | 'A5' | '5x7' | 'card';
type BackgroundStyle = 'linen' | 'botanical' | 'midnight' | 'arch';

const props = defineProps<{
    eventName: string;
    albumAccessCode: string;
    branding: {
        primaryColor: string | null;
        accentColor: string | null;
        logoUrl: string | null;
    };
    targets: Array<{
        key: PrintPackTarget;
        url: string;
        qrDataUrl: string;
    }>;
}>();

const { t } = useTranslations();

const presetKeys: PrintPackPreset[] = ['welcome_sign', 'table_card', 'invitation_insert', 'wall_sign', 'small_card'];
const paperSizes: PaperSize[] = ['A4', 'A5', '5x7', 'card'];
const backgroundStyles: BackgroundStyle[] = ['linen', 'botanical', 'midnight', 'arch'];

const selectedTarget = ref<PrintPackTarget>(props.targets[0]?.key ?? 'album');
const selectedPreset = ref<PrintPackPreset>('welcome_sign');
const selectedPaperSize = ref<PaperSize>('A4');
const selectedBackground = ref<BackgroundStyle>('linen');

const sanitizeHexColor = (value: string | null | undefined, fallback: string): string => {
    if (!value) {
        return fallback;
    }

    const normalized = value.trim();

    if (/^#[0-9a-fA-F]{6}$/.test(normalized)) {
        return normalized;
    }

    return fallback;
};

const hexToRgb = (value: string): { r: number; g: number; b: number } => {
    const normalized = sanitizeHexColor(value, '#171411').replace('#', '');

    return {
        r: Number.parseInt(normalized.slice(0, 2), 16),
        g: Number.parseInt(normalized.slice(2, 4), 16),
        b: Number.parseInt(normalized.slice(4, 6), 16),
    };
};

const rgba = (hex: string, alpha: number): string => {
    const { r, g, b } = hexToRgb(hex);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const escapeXml = (value: string): string => {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&apos;');
};

const wrapText = (value: string, maxChars: number, maxLines: number): string[] => {
    const words = value.trim().split(/\s+/).filter(Boolean);

    if (words.length === 0) {
        return [];
    }

    const lines: string[] = [];
    let currentLine = '';

    for (const word of words) {
        const nextLine = currentLine === '' ? word : `${currentLine} ${word}`;

        if (nextLine.length <= maxChars || currentLine === '') {
            currentLine = nextLine;
            continue;
        }

        lines.push(currentLine);
        currentLine = word;

        if (lines.length === maxLines - 1) {
            break;
        }
    }

    const consumedWords = lines.join(' ').split(/\s+/).filter(Boolean).length;
    const remainingWords = words.slice(consumedWords);
    const finalLine = currentLine === '' ? remainingWords.join(' ') : [currentLine, ...remainingWords.slice(1)].join(' ');

    if (finalLine !== '') {
        lines.push(finalLine);
    }

    return lines.slice(0, maxLines).map((line, index, array) => {
        if (index !== array.length - 1) {
            return line;
        }

        const totalWordsUsed = array
            .slice(0, index + 1)
            .join(' ')
            .split(/\s+/)
            .filter(Boolean).length;

        if (totalWordsUsed < words.length && !line.endsWith('...')) {
            return `${line.replace(/[.,;:!?-]$/, '')}...`;
        }

        return line;
    });
};

const renderTextBlock = (
    lines: string[],
    x: number,
    y: number,
    fontSize: number,
    lineHeight: number,
    options: {
        fill: string;
        fontWeight?: string;
        textAnchor?: 'start' | 'middle' | 'end';
        fontFamily?: string;
        letterSpacing?: number;
    },
): string => {
    if (lines.length === 0) {
        return '';
    }

    return `
        <text
            x="${x}"
            y="${y}"
            fill="${options.fill}"
            font-size="${fontSize}"
            font-weight="${options.fontWeight ?? '400'}"
            text-anchor="${options.textAnchor ?? 'start'}"
            font-family="${options.fontFamily ?? 'Georgia, serif'}"
            ${options.letterSpacing !== undefined ? `letter-spacing="${options.letterSpacing}"` : ''}
        >
            ${lines
                .map((line, index) => `<tspan x="${x}" dy="${index === 0 ? 0 : lineHeight}">${escapeXml(line)}</tspan>`)
                .join('')}
        </text>
    `;
};

const eventInitials = computed(() => {
    return props.eventName
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 3)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');
});

const presetMeta = computed<Record<PrintPackPreset, {
    title: string;
    body: string;
    defaultTarget: PrintPackTarget;
    defaultPaperSize: PaperSize;
    defaultBackground: BackgroundStyle;
    instruction: string;
    layout: 'hero' | 'compact' | 'horizontal';
}>>(() => ({
    welcome_sign: {
        title: t('event_home.print_pack.presets.welcome_sign.title'),
        body: t('event_home.print_pack.presets.welcome_sign.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'A4',
        defaultBackground: 'linen',
        instruction: t('event_home.print_pack.instructions.album'),
        layout: 'hero',
    },
    table_card: {
        title: t('event_home.print_pack.presets.table_card.title'),
        body: t('event_home.print_pack.presets.table_card.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'card',
        defaultBackground: 'botanical',
        instruction: t('event_home.print_pack.instructions.album'),
        layout: 'compact',
    },
    invitation_insert: {
        title: t('event_home.print_pack.presets.invitation_insert.title'),
        body: t('event_home.print_pack.presets.invitation_insert.body'),
        defaultTarget: 'invitation',
        defaultPaperSize: '5x7',
        defaultBackground: 'arch',
        instruction: t('event_home.print_pack.instructions.invitation'),
        layout: 'hero',
    },
    wall_sign: {
        title: t('event_home.print_pack.presets.wall_sign.title'),
        body: t('event_home.print_pack.presets.wall_sign.body'),
        defaultTarget: 'wall',
        defaultPaperSize: 'A4',
        defaultBackground: 'midnight',
        instruction: t('event_home.print_pack.instructions.wall'),
        layout: 'hero',
    },
    small_card: {
        title: t('event_home.print_pack.presets.small_card.title'),
        body: t('event_home.print_pack.presets.small_card.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'card',
        defaultBackground: 'arch',
        instruction: t('event_home.print_pack.instructions.album'),
        layout: 'horizontal',
    },
}));

const backgroundMeta = computed<Record<BackgroundStyle, {
    title: string;
    body: string;
    backgroundStart: string;
    backgroundEnd: string;
    cardSurface: string;
    text: string;
    muted: string;
    frame: string;
    shapeOne: string;
    shapeTwo: string;
}>>(() => ({
    linen: {
        title: t('event_home.print_pack.backgrounds.linen.title'),
        body: t('event_home.print_pack.backgrounds.linen.body'),
        backgroundStart: '#f6eee3',
        backgroundEnd: '#fffdf9',
        cardSurface: '#fffaf3',
        text: '#2f231d',
        muted: '#7a6156',
        frame: '#d8c4b1',
        shapeOne: '#eacaa5',
        shapeTwo: '#f0dfc8',
    },
    botanical: {
        title: t('event_home.print_pack.backgrounds.botanical.title'),
        body: t('event_home.print_pack.backgrounds.botanical.body'),
        backgroundStart: '#f2efe6',
        backgroundEnd: '#fbfaf7',
        cardSurface: '#fffdf8',
        text: '#233025',
        muted: '#637266',
        frame: '#cbd6c7',
        shapeOne: '#c7d7b3',
        shapeTwo: '#e3dccd',
    },
    midnight: {
        title: t('event_home.print_pack.backgrounds.midnight.title'),
        body: t('event_home.print_pack.backgrounds.midnight.body'),
        backgroundStart: '#18151d',
        backgroundEnd: '#2b2431',
        cardSurface: '#251d2b',
        text: '#f7f0e6',
        muted: '#d1c3b5',
        frame: '#6c5b6f',
        shapeOne: '#5f4e79',
        shapeTwo: '#8d6a6e',
    },
    arch: {
        title: t('event_home.print_pack.backgrounds.arch.title'),
        body: t('event_home.print_pack.backgrounds.arch.body'),
        backgroundStart: '#f7efe7',
        backgroundEnd: '#fffaf5',
        cardSurface: '#fffcf8',
        text: '#30201a',
        muted: '#846657',
        frame: '#e2c1ad',
        shapeOne: '#d7b19f',
        shapeTwo: '#f1dac8',
    },
}));

type BackgroundTheme = {
    title: string;
    body: string;
    backgroundStart: string;
    backgroundEnd: string;
    cardSurface: string;
    text: string;
    muted: string;
    frame: string;
    shapeOne: string;
    shapeTwo: string;
};

const sizeMeta: Record<PaperSize, { width: number; height: number; label: string }> = {
    A4: { width: 1240, height: 1754, label: 'A4' },
    A5: { width: 874, height: 1240, label: 'A5' },
    '5x7': { width: 1000, height: 1400, label: '5x7' },
    card: { width: 1120, height: 720, label: t('event_home.print_pack.sizes.card') },
};

const selectedTargetMeta = computed(
    () => props.targets.find((target) => target.key === selectedTarget.value) ?? props.targets[0] ?? null,
);
const activePresetMeta = computed(() => presetMeta.value[selectedPreset.value]);
const activeBackgroundMeta = computed(() => backgroundMeta.value[selectedBackground.value]);

const brandPrimary = computed(() => sanitizeHexColor(props.branding.primaryColor, '#171411'));
const brandAccent = computed(() => sanitizeHexColor(props.branding.accentColor, '#c57a4e'));

const previewStyle = computed(() => {
    const size = sizeMeta[selectedPaperSize.value];

    return {
        aspectRatio: `${size.width} / ${size.height}`,
    };
});

const posterFilenameBase = computed(() => {
    const slug = props.eventName
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');

    return `${slug || 'eventsmart'}-${selectedPreset.value}-${selectedTarget.value}-${selectedBackground.value}`;
});

const backgroundDecoration = (
    style: BackgroundStyle,
    width: number,
    height: number,
    theme: BackgroundTheme,
): string => {
    if (style === 'midnight') {
        return `
            <circle cx="${width * 0.16}" cy="${height * 0.18}" r="${width * 0.14}" fill="${rgba(theme.shapeOne, 0.34)}" />
            <circle cx="${width * 0.84}" cy="${height * 0.14}" r="${width * 0.18}" fill="${rgba(theme.shapeTwo, 0.24)}" />
            <path d="M 0 ${height * 0.88} Q ${width * 0.26} ${height * 0.72} ${width * 0.48} ${height * 0.88} T ${width} ${height * 0.84} L ${width} ${height} L 0 ${height} Z" fill="${rgba(brandAccent.value, 0.18)}" />
        `;
    }

    if (style === 'botanical') {
        return `
            <ellipse cx="${width * 0.12}" cy="${height * 0.16}" rx="${width * 0.18}" ry="${height * 0.1}" fill="${rgba(theme.shapeOne, 0.28)}" />
            <ellipse cx="${width * 0.88}" cy="${height * 0.24}" rx="${width * 0.16}" ry="${height * 0.08}" fill="${rgba(theme.shapeTwo, 0.22)}" />
            <path d="M ${width * 0.82} ${height * 0.8} C ${width * 0.76} ${height * 0.72}, ${width * 0.7} ${height * 0.62}, ${width * 0.62} ${height * 0.54}" stroke="${rgba(brandPrimary.value, 0.22)}" stroke-width="${Math.max(12, width * 0.008)}" stroke-linecap="round" fill="none" />
            <path d="M ${width * 0.16} ${height * 0.78} C ${width * 0.22} ${height * 0.66}, ${width * 0.28} ${height * 0.58}, ${width * 0.36} ${height * 0.52}" stroke="${rgba(theme.shapeOne, 0.36)}" stroke-width="${Math.max(10, width * 0.006)}" stroke-linecap="round" fill="none" />
        `;
    }

    if (style === 'arch') {
        return `
            <path d="M ${width * 0.14} ${height * 0.26} a ${width * 0.24} ${height * 0.18} 0 0 1 ${width * 0.48} 0 v ${height * 0.42} h -${width * 0.48} z" fill="${rgba(theme.shapeOne, 0.18)}" />
            <path d="M ${width * 0.52} ${height * 0.16} a ${width * 0.22} ${height * 0.15} 0 0 1 ${width * 0.44} 0 v ${height * 0.32} h -${width * 0.44} z" fill="${rgba(theme.shapeTwo, 0.2)}" />
            <circle cx="${width * 0.86}" cy="${height * 0.82}" r="${width * 0.1}" fill="${rgba(brandAccent.value, 0.16)}" />
        `;
    }

    return `
        <circle cx="${width * 0.2}" cy="${height * 0.16}" r="${width * 0.18}" fill="${rgba(theme.shapeOne, 0.26)}" />
        <circle cx="${width * 0.84}" cy="${height * 0.18}" r="${width * 0.14}" fill="${rgba(theme.shapeTwo, 0.22)}" />
        <path d="M 0 ${height * 0.9} C ${width * 0.22} ${height * 0.78}, ${width * 0.38} ${height * 0.96}, ${width * 0.6} ${height * 0.86} S ${width * 0.88} ${height * 0.8}, ${width} ${height * 0.9} L ${width} ${height} L 0 ${height} Z" fill="${rgba(brandAccent.value, 0.12)}" />
    `;
};

const posterSvgMarkup = computed(() => {
    const target = selectedTargetMeta.value;
    if (target === null) {
        return '';
    }

    const size = sizeMeta[selectedPaperSize.value];
    const theme = activeBackgroundMeta.value;
    const preset = activePresetMeta.value;
    const width = size.width;
    const height = size.height;
    const isHorizontal = preset.layout === 'horizontal';
    const isCompact = preset.layout === 'compact';
    const outerPadding = isHorizontal ? width * 0.055 : width * 0.085;
    const titleLines = wrapText(
        props.eventName,
        isHorizontal ? 16 : isCompact ? 18 : 20,
        isHorizontal ? 3 : 3,
    );
    const instructionLines = wrapText(
        preset.instruction,
        isHorizontal ? 25 : isCompact ? 22 : 28,
        isHorizontal ? 4 : isCompact ? 4 : 5,
    );
    const urlLines = wrapText(
        target.url,
        isHorizontal ? 28 : 34,
        2,
    );
    const footerLabel = target.key === 'invitation'
        ? t('event_home.print_pack.footer.invitation_ready')
        : t('event_home.album.code_label', { code: props.albumAccessCode });
    const footerLines = wrapText(footerLabel, isHorizontal ? 16 : 24, 2);
    const titleX = isHorizontal ? outerPadding + 40 : width / 2;
    const eyebrowX = isHorizontal ? outerPadding + 40 : width / 2;
    const bodyX = isHorizontal ? outerPadding + 40 : width / 2;
    const footerX = isHorizontal ? outerPadding + 40 : width / 2;
    const urlX = isHorizontal ? outerPadding + 40 : width / 2;

    const headlineBlock = renderTextBlock(titleLines, titleX, isCompact ? height * 0.21 : height * 0.2, isHorizontal ? 72 : isCompact ? 60 : 78, isHorizontal ? 82 : isCompact ? 70 : 90, {
        fill: theme.text,
        fontWeight: '600',
        textAnchor: isHorizontal ? 'start' : 'middle',
        fontFamily: '"Cormorant Garamond", Georgia, serif',
    });

    const eyebrow = renderTextBlock([preset.title.toUpperCase()], eyebrowX, isCompact ? height * 0.12 : height * 0.1, 22, 26, {
        fill: theme.muted,
        fontWeight: '600',
        textAnchor: isHorizontal ? 'start' : 'middle',
        fontFamily: 'Inter, Arial, sans-serif',
        letterSpacing: 6,
    });

    const bodyBlock = renderTextBlock(
        instructionLines,
        bodyX,
        isHorizontal ? height * 0.42 : isCompact ? height * 0.39 : height * 0.38,
        isHorizontal ? 28 : isCompact ? 28 : 30,
        isHorizontal ? 36 : 38,
        {
            fill: theme.muted,
            textAnchor: isHorizontal ? 'start' : 'middle',
            fontFamily: 'Inter, Arial, sans-serif',
        },
    );

    const urlBlock = renderTextBlock(
        urlLines,
        urlX,
        isHorizontal ? height * 0.78 : height * 0.84,
        isHorizontal ? 22 : 24,
        isHorizontal ? 28 : 30,
        {
            fill: theme.muted,
            textAnchor: isHorizontal ? 'start' : 'middle',
            fontFamily: 'Inter, Arial, sans-serif',
        },
    );

    const footerBlock = renderTextBlock(
        footerLines,
        footerX,
        isHorizontal ? height * 0.69 : height * 0.74,
        isHorizontal ? 24 : 26,
        isHorizontal ? 30 : 32,
        {
            fill: theme.text,
            fontWeight: '600',
            textAnchor: isHorizontal ? 'start' : 'middle',
            fontFamily: 'Inter, Arial, sans-serif',
            letterSpacing: target.key === 'invitation' ? 0 : 3,
        },
    );

    const qrSize = isHorizontal ? height * 0.34 : isCompact ? width * 0.33 : width * 0.34;
    const qrX = isHorizontal ? width * 0.66 : (width - qrSize) / 2;
    const qrY = isHorizontal ? height * 0.28 : isCompact ? height * 0.48 : height * 0.46;

    const monogramX = isHorizontal ? width * 0.86 : width * 0.85;
    const monogramY = isHorizontal ? height * 0.14 : height * 0.14;

    return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
    <defs>
        <linearGradient id="sheet-gradient" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="${theme.backgroundStart}" />
            <stop offset="100%" stop-color="${theme.backgroundEnd}" />
        </linearGradient>
        <filter id="sheet-shadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="0" dy="${Math.max(24, width * 0.02)}" stdDeviation="${Math.max(18, width * 0.016)}" flood-color="${rgba('#171411', 0.16)}" />
        </filter>
    </defs>

    <rect width="${width}" height="${height}" rx="${isHorizontal ? 56 : 72}" fill="url(#sheet-gradient)" />
    ${backgroundDecoration(selectedBackground.value, width, height, theme)}

    <rect
        x="${outerPadding}"
        y="${outerPadding}"
        width="${width - outerPadding * 2}"
        height="${height - outerPadding * 2}"
        rx="${isHorizontal ? 42 : 54}"
        fill="${rgba(theme.cardSurface, theme.backgroundStart === theme.cardSurface ? 1 : 0.84)}"
        stroke="${rgba(theme.frame, 0.54)}"
        stroke-width="${Math.max(2, width * 0.0024)}"
        filter="url(#sheet-shadow)"
    />

    <rect
        x="${outerPadding + (isHorizontal ? 32 : 42)}"
        y="${outerPadding + (isHorizontal ? 28 : 36)}"
        width="${isHorizontal ? width * 0.18 : width * 0.22}"
        height="${Math.max(8, width * 0.008)}"
        rx="${Math.max(4, width * 0.004)}"
        fill="${brandAccent.value}"
        opacity="0.86"
    />

    <circle cx="${monogramX}" cy="${monogramY}" r="${isHorizontal ? 42 : 52}" fill="${rgba(brandPrimary.value, 0.14)}" />
    <text
        x="${monogramX}"
        y="${monogramY + 16}"
        fill="${theme.text}"
        font-size="${isHorizontal ? 34 : 40}"
        font-weight="600"
        text-anchor="middle"
        font-family='"Cormorant Garamond", Georgia, serif'
    >${escapeXml(eventInitials.value)}</text>

    ${eyebrow}
    ${headlineBlock}
    ${bodyBlock}

    <rect
        x="${qrX - (isHorizontal ? 22 : 28)}"
        y="${qrY - (isHorizontal ? 22 : 28)}"
        width="${qrSize + (isHorizontal ? 44 : 56)}"
        height="${qrSize + (isHorizontal ? 44 : 56)}"
        rx="${isHorizontal ? 34 : 40}"
        fill="#ffffff"
        stroke="${rgba(theme.frame, 0.38)}"
        stroke-width="${Math.max(2, width * 0.0018)}"
    />
    <image href="${target.qrDataUrl}" x="${qrX}" y="${qrY}" width="${qrSize}" height="${qrSize}" preserveAspectRatio="xMidYMid meet" />

    <text
        x="${isHorizontal ? outerPadding + 40 : width / 2}"
        y="${isHorizontal ? height * 0.63 : height * 0.69}"
        fill="${theme.muted}"
        font-size="${isHorizontal ? 18 : 22}"
        font-weight="600"
        text-anchor="${isHorizontal ? 'start' : 'middle'}"
        font-family="Inter, Arial, sans-serif"
        letter-spacing="3"
    >${escapeXml(t('event_home.print_pack.footer.scan_hint').toUpperCase())}</text>

    ${footerBlock}
    ${urlBlock}
</svg>`;
});

const posterDataUrl = computed(() => {
    if (posterSvgMarkup.value === '') {
        return '';
    }

    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(posterSvgMarkup.value)}`;
});

const applyPreset = (preset: PrintPackPreset): void => {
    const config = presetMeta.value[preset];

    selectedPreset.value = preset;
    selectedTarget.value = config.defaultTarget;
    selectedPaperSize.value = config.defaultPaperSize;
    selectedBackground.value = config.defaultBackground;
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

const downloadSvg = (): void => {
    if (posterSvgMarkup.value === '') {
        return;
    }

    const blob = new Blob([posterSvgMarkup.value], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `${posterFilenameBase.value}.svg`;
    link.click();
    URL.revokeObjectURL(url);
};

const downloadRaster = async (format: 'png' | 'jpeg'): Promise<void> => {
    if (posterDataUrl.value === '') {
        return;
    }

    const image = new Image();

    await new Promise<void>((resolve, reject) => {
        image.onload = () => resolve();
        image.onerror = () => reject(new Error('Unable to render the print design.'));
        image.src = posterDataUrl.value;
    });

    const size = sizeMeta[selectedPaperSize.value];
    const canvas = document.createElement('canvas');
    canvas.width = size.width;
    canvas.height = size.height;
    const context = canvas.getContext('2d');

    if (context === null) {
        return;
    }

    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    const outputUrl = canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.94);
    const link = document.createElement('a');
    link.href = outputUrl;
    link.download = `${posterFilenameBase.value}.${format === 'png' ? 'png' : 'jpg'}`;
    link.click();
};

const printPack = (): void => {
    if (posterDataUrl.value === '') {
        return;
    }

    const size = sizeMeta[selectedPaperSize.value];
    const printWindow = window.open('', '_blank', 'width=1200,height=920');

    if (!printWindow) {
        return;
    }

    const html = `
        <html>
            <head>
                <title>${escapeXml(props.eventName)} Print Pack</title>
                <style>
                    @page { margin: 0; size: auto; }
                    html, body {
                        margin: 0;
                        background: #efe7dc;
                        min-height: 100%;
                        font-family: Inter, Arial, sans-serif;
                    }
                    body {
                        display: grid;
                        place-items: center;
                        padding: 24px;
                    }
                    img {
                        display: block;
                        width: min(100%, ${Math.round(size.width / 1.8)}px);
                        height: auto;
                    }
                </style>
            </head>
            <body>
                <img id="print-pack-artwork" src="${posterDataUrl.value}" alt="Print pack artwork" />
                <script>
                    const artwork = document.getElementById('print-pack-artwork');
                    const runPrint = () => {
                        window.focus();
                        window.print();
                    };

                    if (artwork && artwork.complete) {
                        setTimeout(runPrint, 180);
                    } else if (artwork) {
                        artwork.addEventListener('load', () => setTimeout(runPrint, 180), { once: true });
                        artwork.addEventListener('error', () => setTimeout(runPrint, 180), { once: true });
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
</script>

<template>
    <div class="grid gap-0 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <aside class="border-b border-black/5 bg-[#f4ede4] xl:border-r xl:border-b-0">
            <div class="space-y-6 px-5 py-6 md:px-6">
                <section>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.presets_title') }}
                    </p>
                    <div class="mt-3 flex flex-col gap-2.5">
                        <button
                            v-for="preset in presetKeys"
                            :key="preset"
                            type="button"
                            :class="[
                                'rounded-[1.2rem] px-4 py-4 text-left transition',
                                selectedPreset === preset
                                    ? 'bg-[#171411] text-white shadow-sm'
                                    : 'bg-white/80 text-[#171411] hover:bg-white',
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

                <section class="border-t border-black/6 pt-5">
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
                                    : 'bg-white/82 text-[#171411] hover:bg-white',
                            ]"
                            @click="selectedTarget = target.key"
                        >
                            {{ t(`event_home.print_pack.targets.${target.key}`) }}
                        </button>
                    </div>
                </section>

                <section class="border-t border-black/6 pt-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.size_title') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-for="paperSize in paperSizes"
                            :key="paperSize"
                            type="button"
                            :class="[
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                selectedPaperSize === paperSize
                                    ? 'bg-[#171411] text-white'
                                    : 'bg-white/82 text-[#171411] hover:bg-white',
                            ]"
                            @click="selectedPaperSize = paperSize"
                        >
                            {{ sizeMeta[paperSize].label }}
                        </button>
                    </div>
                </section>

                <section class="border-t border-black/6 pt-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        {{ t('event_home.print_pack.background_title') }}
                    </p>
                    <div class="mt-3 flex flex-col gap-2.5">
                        <button
                            v-for="background in backgroundStyles"
                            :key="background"
                            type="button"
                            :class="[
                                'rounded-[1.1rem] px-4 py-3 text-left transition',
                                selectedBackground === background
                                    ? 'bg-[#171411] text-white'
                                    : 'bg-white/82 text-[#171411] hover:bg-white',
                            ]"
                            @click="selectedBackground = background"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex size-8 shrink-0 rounded-full"
                                    :style="{
                                        background: `linear-gradient(135deg, ${backgroundMeta[background].backgroundStart}, ${backgroundMeta[background].backgroundEnd})`,
                                        boxShadow: `inset 0 0 0 1px ${backgroundMeta[background].frame}`,
                                    }"
                                />
                                <div>
                                    <p class="font-semibold">{{ backgroundMeta[background].title }}</p>
                                    <p class="mt-1 text-sm leading-5" :class="selectedBackground === background ? 'text-white/76' : 'text-zinc-600'">
                                        {{ backgroundMeta[background].body }}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>
                </section>
            </div>
        </aside>

        <section class="bg-[#191513] text-white">
            <div class="border-b border-white/8 px-5 py-4 md:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">
                            {{ t('event_home.print_pack.preview_title') }}
                        </p>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-white/68">
                            {{ t('event_home.print_pack.stage_helper') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button class="rounded-full border-0 bg-white text-[#171411] hover:bg-white/90" @click="printPack">
                            <Printer class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.print_pdf') }}
                        </Button>
                        <Button variant="outline" class="rounded-full border-white/18 bg-white/6 text-white hover:bg-white/10 hover:text-white" @click="downloadRaster('png')">
                            <FileOutput class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.download_png') }}
                        </Button>
                        <Button variant="outline" class="rounded-full border-white/18 bg-white/6 text-white hover:bg-white/10 hover:text-white" @click="downloadRaster('jpeg')">
                            <FileOutput class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.download_jpg') }}
                        </Button>
                        <Button variant="outline" class="rounded-full border-white/18 bg-white/6 text-white hover:bg-white/10 hover:text-white" @click="downloadSvg">
                            <QrCode class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.download_svg') }}
                        </Button>
                        <Button variant="outline" class="rounded-full border-white/18 bg-white/6 text-white hover:bg-white/10 hover:text-white" @click="openSelectedTarget">
                            <ExternalLink class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.open_target') }}
                        </Button>
                        <Button variant="outline" class="rounded-full border-white/18 bg-white/6 text-white hover:bg-white/10 hover:text-white" @click="copySelectedTarget">
                            <Copy class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.copy_target') }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="grid gap-0 lg:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_40%),linear-gradient(180deg,#231c19_0%,#171311_100%)] px-5 py-6 md:px-6">
                    <div class="mx-auto flex min-h-[44rem] max-w-4xl items-center justify-center">
                        <div class="w-full max-w-[46rem]" :style="previewStyle">
                            <img
                                :src="posterDataUrl"
                                :alt="t('event_home.print_pack.preview_alt')"
                                class="block size-full rounded-[1.8rem] object-contain shadow-[0_40px_100px_rgba(0,0,0,0.28)]"
                            />
                        </div>
                    </div>
                </div>

                <aside class="border-t border-white/8 bg-white/[0.03] px-5 py-6 lg:border-t-0 lg:border-l md:px-6">
                    <div class="space-y-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">
                                {{ t('event_home.print_pack.selection_title') }}
                            </p>
                            <div class="mt-3 space-y-3 text-sm leading-6 text-white/78">
                                <div>
                                    <p class="text-white/48">{{ t('event_home.print_pack.presets_title') }}</p>
                                    <p class="font-medium text-white">{{ activePresetMeta.title }}</p>
                                </div>
                                <div>
                                    <p class="text-white/48">{{ t('event_home.print_pack.target_title') }}</p>
                                    <p class="font-medium text-white">{{ t(`event_home.print_pack.targets.${selectedTarget}`) }}</p>
                                </div>
                                <div>
                                    <p class="text-white/48">{{ t('event_home.print_pack.size_title') }}</p>
                                    <p class="font-medium text-white">{{ sizeMeta[selectedPaperSize].label }}</p>
                                </div>
                                <div>
                                    <p class="text-white/48">{{ t('event_home.print_pack.background_title') }}</p>
                                    <p class="font-medium text-white">{{ activeBackgroundMeta.title }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-white/8 pt-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">
                                {{ t('event_home.print_pack.preview_title') }}
                            </p>
                            <p class="mt-3 text-sm leading-6 text-white/68">
                                {{ activePresetMeta.body }}
                            </p>
                        </div>

                        <div class="border-t border-white/8 pt-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">
                                {{ t('event_home.print_pack.link_title') }}
                            </p>
                            <p class="mt-3 break-words text-sm leading-6 text-white/78">
                                {{ selectedTargetMeta?.url }}
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    </div>
</template>
