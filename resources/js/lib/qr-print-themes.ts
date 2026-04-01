export type QrPrintThemeId =
    | 'beige_simple'
    | 'blue_modern'
    | 'cream_love'
    | 'pink_minimal'
    | 'pink_transparent'
    | 'simple_transparent';

export type QrPrintOrientation = 'portrait' | 'landscape';

type QrPrintTextBlockConfig = {
    y: number;
    width: number;
    fontSize: number;
    fontFamily: string;
    fontWeight?: string;
    maxLines: number;
    lineHeight: number;
    minScale: number;
};

type QrPrintLayoutConfig = {
    paddingX: number;
    qrSize: number;
    qrY: number;
    qrFramePadding: number;
    qrCornerRadius: number;
    eyebrow: QrPrintTextBlockConfig;
    title: QrPrintTextBlockConfig;
    body: QrPrintTextBlockConfig;
    scanHint: QrPrintTextBlockConfig;
    footer: QrPrintTextBlockConfig;
    url: QrPrintTextBlockConfig;
};

export type QrPrintThemeConfig = {
    id: QrPrintThemeId;
    label: string;
    artworkUrl: string;
    defaultCanvasColor: string;
    pickerCanvasColor: string;
    supportsCanvasColor: boolean;
    textColor: string;
    mutedColor: string;
    qrFrameColor: string;
    previewCardClass: string;
    layout: Record<QrPrintOrientation, QrPrintLayoutConfig>;
};

const portraitLayout = (overrides: Partial<QrPrintLayoutConfig> = {}): QrPrintLayoutConfig => ({
    paddingX: 0.16,
    qrSize: 0.31,
    qrY: 0.43,
    qrFramePadding: 0.04,
    qrCornerRadius: 26,
    eyebrow: {
        y: 0.12,
        width: 0.48,
        fontSize: 0.017,
        fontFamily: '"Cinzel", serif',
        fontWeight: '600',
        maxLines: 1,
        lineHeight: 1.1,
        minScale: 0.9,
    },
    title: {
        y: 0.19,
        width: 0.6,
        fontSize: 0.06,
        fontFamily: '"Cormorant Garamond", serif',
        fontWeight: '600',
        maxLines: 3,
        lineHeight: 1.04,
        minScale: 0.7,
    },
    body: {
        y: 0.33,
        width: 0.54,
        fontSize: 0.022,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 4,
        lineHeight: 1.28,
        minScale: 0.76,
    },
    scanHint: {
        y: 0.76,
        width: 0.46,
        fontSize: 0.017,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 1,
        lineHeight: 1.1,
        minScale: 0.9,
    },
    footer: {
        y: 0.82,
        width: 0.56,
        fontSize: 0.022,
        fontFamily: '"Cinzel", serif',
        fontWeight: '600',
        maxLines: 2,
        lineHeight: 1.18,
        minScale: 0.76,
    },
    url: {
        y: 0.89,
        width: 0.62,
        fontSize: 0.015,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 2,
        lineHeight: 1.16,
        minScale: 0.78,
    },
    ...overrides,
});

const landscapeLayout = (overrides: Partial<QrPrintLayoutConfig> = {}): QrPrintLayoutConfig => ({
    paddingX: 0.1,
    qrSize: 0.23,
    qrY: 0.45,
    qrFramePadding: 0.026,
    qrCornerRadius: 22,
    eyebrow: {
        y: 0.115,
        width: 0.38,
        fontSize: 0.014,
        fontFamily: '"Cinzel", serif',
        fontWeight: '600',
        maxLines: 1,
        lineHeight: 1.1,
        minScale: 0.9,
    },
    title: {
        y: 0.185,
        width: 0.5,
        fontSize: 0.042,
        fontFamily: '"Cormorant Garamond", serif',
        fontWeight: '600',
        maxLines: 2,
        lineHeight: 1.02,
        minScale: 0.76,
    },
    body: {
        y: 0.3,
        width: 0.44,
        fontSize: 0.0155,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 3,
        lineHeight: 1.22,
        minScale: 0.8,
    },
    scanHint: {
        y: 0.73,
        width: 0.34,
        fontSize: 0.013,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 1,
        lineHeight: 1.1,
        minScale: 0.9,
    },
    footer: {
        y: 0.79,
        width: 0.42,
        fontSize: 0.016,
        fontFamily: '"Cinzel", serif',
        fontWeight: '600',
        maxLines: 2,
        lineHeight: 1.14,
        minScale: 0.8,
    },
    url: {
        y: 0.865,
        width: 0.5,
        fontSize: 0.012,
        fontFamily: '"Montserrat", sans-serif',
        fontWeight: '500',
        maxLines: 2,
        lineHeight: 1.14,
        minScale: 0.82,
    },
    ...overrides,
});

export const qrPrintThemes: QrPrintThemeConfig[] = [
    {
        id: 'beige_simple',
        label: 'Beige Simple',
        artworkUrl: '/qr-bg-themes/beige-simple.svg',
        defaultCanvasColor: '#f4ede4',
        pickerCanvasColor: '#f4ede4',
        supportsCanvasColor: false,
        textColor: '#3e342d',
        mutedColor: '#76665b',
        qrFrameColor: '#fffaf4',
        previewCardClass: 'from-[#f6efe4] via-[#f1e4d5] to-[#e8dccf]',
        layout: {
            portrait: portraitLayout({
                title: {
                    y: 0.18,
                    width: 0.52,
                    fontSize: 0.057,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.03,
                    minScale: 0.72,
                },
                body: {
                    y: 0.315,
                    width: 0.46,
                    fontSize: 0.02,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 4,
                    lineHeight: 1.24,
                    minScale: 0.8,
                },
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.18,
                    width: 0.44,
                    fontSize: 0.04,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 2,
                    lineHeight: 1.02,
                    minScale: 0.78,
                },
                body: {
                    y: 0.29,
                    width: 0.38,
                    fontSize: 0.0145,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 3,
                    lineHeight: 1.2,
                    minScale: 0.82,
                },
            }),
        },
    },
    {
        id: 'blue_modern',
        label: 'Blue Modern',
        artworkUrl: '/qr-bg-themes/blue-modern.svg',
        defaultCanvasColor: '#edf2fb',
        pickerCanvasColor: '#edf2fb',
        supportsCanvasColor: false,
        textColor: '#203249',
        mutedColor: '#5d7189',
        qrFrameColor: '#ffffff',
        previewCardClass: 'from-[#eef4ff] via-[#d9e6fb] to-[#c6d7f2]',
        layout: {
            portrait: portraitLayout({
                eyebrow: {
                    y: 0.118,
                    width: 0.5,
                    fontSize: 0.0155,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '600',
                    maxLines: 1,
                    lineHeight: 1.1,
                    minScale: 0.9,
                },
                title: {
                    y: 0.19,
                    width: 0.56,
                    fontSize: 0.049,
                    fontFamily: '"Cinzel", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.05,
                    minScale: 0.72,
                },
                body: {
                    y: 0.325,
                    width: 0.5,
                    fontSize: 0.02,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 4,
                    lineHeight: 1.28,
                    minScale: 0.8,
                },
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.19,
                    width: 0.46,
                    fontSize: 0.037,
                    fontFamily: '"Cinzel", serif',
                    fontWeight: '600',
                    maxLines: 2,
                    lineHeight: 1.04,
                    minScale: 0.78,
                },
                body: {
                    y: 0.31,
                    width: 0.4,
                    fontSize: 0.014,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 3,
                    lineHeight: 1.2,
                    minScale: 0.84,
                },
            }),
        },
    },
    {
        id: 'cream_love',
        label: 'Cream Love',
        artworkUrl: '/qr-bg-themes/cream-love.svg',
        defaultCanvasColor: '#faf4ea',
        pickerCanvasColor: '#faf4ea',
        supportsCanvasColor: false,
        textColor: '#6a4137',
        mutedColor: '#8c6258',
        qrFrameColor: '#fffaf7',
        previewCardClass: 'from-[#fbf2e7] via-[#f6e8dc] to-[#f0ddcf]',
        layout: {
            portrait: portraitLayout({
                title: {
                    y: 0.18,
                    width: 0.54,
                    fontSize: 0.06,
                    fontFamily: '"Great Vibes", serif',
                    fontWeight: '400',
                    maxLines: 3,
                    lineHeight: 1.05,
                    minScale: 0.74,
                },
                body: {
                    y: 0.325,
                    width: 0.48,
                    fontSize: 0.0195,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 4,
                    lineHeight: 1.24,
                    minScale: 0.82,
                },
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.19,
                    width: 0.44,
                    fontSize: 0.04,
                    fontFamily: '"Great Vibes", serif',
                    fontWeight: '400',
                    maxLines: 2,
                    lineHeight: 1.05,
                    minScale: 0.78,
                },
                body: {
                    y: 0.305,
                    width: 0.36,
                    fontSize: 0.014,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.18,
                    minScale: 0.84,
                },
            }),
        },
    },
    {
        id: 'pink_minimal',
        label: 'Pink Minimal',
        artworkUrl: '/qr-bg-themes/pink-minimal.svg',
        defaultCanvasColor: '#f8edf0',
        pickerCanvasColor: '#f8edf0',
        supportsCanvasColor: false,
        textColor: '#5b3945',
        mutedColor: '#86656f',
        qrFrameColor: '#fffafc',
        previewCardClass: 'from-[#f9eff3] via-[#f6dee7] to-[#efcad7]',
        layout: {
            portrait: portraitLayout({
                title: {
                    y: 0.195,
                    width: 0.56,
                    fontSize: 0.052,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.05,
                    minScale: 0.74,
                },
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.19,
                    width: 0.45,
                    fontSize: 0.038,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 2,
                    lineHeight: 1.04,
                    minScale: 0.8,
                },
            }),
        },
    },
    {
        id: 'pink_transparent',
        label: 'Pink Transparent',
        artworkUrl: '/qr-bg-themes/pink-transparent.svg',
        defaultCanvasColor: '#f7e7ef',
        pickerCanvasColor: '#f7e7ef',
        supportsCanvasColor: true,
        textColor: '#69404d',
        mutedColor: '#8b6874',
        qrFrameColor: '#fff9fb',
        previewCardClass: 'from-[#f9eef3] via-[#f1dce6] to-[#e8cfdb]',
        layout: {
            portrait: portraitLayout({
                title: {
                    y: 0.195,
                    width: 0.58,
                    fontSize: 0.054,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.05,
                    minScale: 0.74,
                },
                qrFramePadding: 0.03,
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.19,
                    width: 0.46,
                    fontSize: 0.038,
                    fontFamily: '"Cormorant Garamond", serif',
                    fontWeight: '600',
                    maxLines: 2,
                    lineHeight: 1.04,
                    minScale: 0.8,
                },
                qrFramePadding: 0.024,
            }),
        },
    },
    {
        id: 'simple_transparent',
        label: 'Simple Transparent',
        artworkUrl: '/qr-bg-themes/simple-transparent.svg',
        defaultCanvasColor: '#f4efe8',
        pickerCanvasColor: '#f4efe8',
        supportsCanvasColor: true,
        textColor: '#32302f',
        mutedColor: '#696360',
        qrFrameColor: '#fffdfb',
        previewCardClass: 'from-[#f5efe7] via-[#ebe5dc] to-[#e3dbd1]',
        layout: {
            portrait: portraitLayout({
                title: {
                    y: 0.2,
                    width: 0.58,
                    fontSize: 0.05,
                    fontFamily: '"Cinzel", serif',
                    fontWeight: '600',
                    maxLines: 3,
                    lineHeight: 1.06,
                    minScale: 0.74,
                },
                body: {
                    y: 0.33,
                    width: 0.5,
                    fontSize: 0.019,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 4,
                    lineHeight: 1.26,
                    minScale: 0.82,
                },
                qrFramePadding: 0.026,
            }),
            landscape: landscapeLayout({
                title: {
                    y: 0.2,
                    width: 0.46,
                    fontSize: 0.036,
                    fontFamily: '"Cinzel", serif',
                    fontWeight: '600',
                    maxLines: 2,
                    lineHeight: 1.05,
                    minScale: 0.82,
                },
                body: {
                    y: 0.315,
                    width: 0.4,
                    fontSize: 0.0135,
                    fontFamily: '"Montserrat", sans-serif',
                    fontWeight: '500',
                    maxLines: 3,
                    lineHeight: 1.18,
                    minScale: 0.84,
                },
                qrFramePadding: 0.022,
            }),
        },
    },
];

export const qrPrintThemeMap = Object.fromEntries(
    qrPrintThemes.map((theme) => [theme.id, theme]),
) as Record<QrPrintThemeId, QrPrintThemeConfig>;
