import type { Component } from 'vue';
import BeigeQrTemplate from '@/components/events/qr-templates/BeigeQrTemplate.vue';
import PinkLandscapeQrTemplate from '@/components/events/qr-templates/PinkLandscapeQrTemplate.vue';
import PinkQrTemplate from '@/components/events/qr-templates/PinkQrTemplate.vue';
import type {
    QrTemplateContent,
    QrTemplateFonts,
} from '@/components/events/qr-templates/types';
import {
    qrTemplateGeometry,
    qrZoneHeightFraction,
} from '@/lib/qr-print-geometry';

export type QrTemplateId = 'beige' | 'pink' | 'pink_landscape';

export type QrTemplateDefinition = {
    id: QrTemplateId;
    label: string;
    component: Component;
    fonts: QrTemplateFonts;
    printOrientation: 'portrait' | 'landscape';
    renderPrintHtml: (
        content: QrTemplateContent,
        qrDataUrl: string,
        previewAlt: string,
    ) => string;
};

const cormorantAndManropeFonts: QrTemplateFonts = {
    stylesheetHref:
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap',
    headingFamily: '"Cormorant Garamond", serif',
    bodyFamily: '"Manrope", sans-serif',
};

const escapeHtml = (value: string): string => {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
};

const lineBreaks = (value: string): string => {
    return escapeHtml(value).replace(/\n/g, '<br />');
};

const pct = (fraction: number): string => `${(fraction * 100).toFixed(2)}%`;

const baseDocument = (
    title: string,
    body: string,
    orientation: 'portrait' | 'landscape',
    fonts: QrTemplateFonts,
): string => `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>${escapeHtml(title)}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="${fonts.stylesheetHref}" rel="stylesheet">
    <style>
        @page {
            size: A4 ${orientation};
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            height: 100%;
            font-family: ${fonts.bodyFamily};
        }
    </style>
</head>
<body>
${body}
</body>
</html>`;

const qrCardStyle = (id: QrTemplateId): string => {
    const geometry = qrTemplateGeometry[id];

    return [
        'position:absolute',
        `left:${pct(geometry.qr.left)}`,
        `top:${pct(geometry.qr.top)}`,
        `width:${pct(geometry.qr.width)}`,
        `height:${pct(qrZoneHeightFraction(geometry))}`,
        'display:flex',
        'align-items:center',
        'justify-content:center',
    ].join(';');
};

const qrImage = (qrDataUrl: string, previewAlt: string): string =>
    `<img src="${qrDataUrl}" alt="${escapeHtml(previewAlt)}" style="display:block;width:100%;height:100%;object-fit:contain;" />`;

const beigePrint = (
    content: QrTemplateContent,
    qrDataUrl: string,
    previewAlt: string,
): string => {
    const geometry = qrTemplateGeometry.beige;

    return baseDocument(
        content.title,
        `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7efe6 center/cover no-repeat url('/qr-bg-themes/beige-base.png');color:#2f211a;">
        <header style="position:absolute;left:8%;right:8%;top:${pct(geometry.headerTop)};text-align:center;">
            <p style="margin:0;font-size:19px;font-weight:800;letter-spacing:0.32em;text-transform:uppercase;color:rgba(81,57,45,0.75);">${lineBreaks(content.subtitle)}</p>
            <h1 style="margin:8px 0 0;font-family:${cormorantAndManropeFonts.headingFamily};font-size:122px;font-weight:600;line-height:0.88;letter-spacing:-0.04em;">${lineBreaks(content.title)}</h1>
            <p style="margin:10px 0 0;font-size:24px;line-height:1.45;color:rgba(75,52,41,0.8);">${lineBreaks(content.slogan)}</p>
        </header>
        <p style="position:absolute;left:14%;right:14%;top:${pct(geometry.messageTop)};margin:0;white-space:pre-line;font-size:22px;line-height:1.7;text-align:center;color:rgba(56,38,29,0.84);">${lineBreaks(content.message)}</p>
        <div style="${qrCardStyle('beige')}">
            <div style="width:88%;height:88%;border-radius:18px;background:#ffffff;padding:14px;">${qrImage(qrDataUrl, previewAlt)}</div>
        </div>
        <footer style="position:absolute;left:18%;right:18%;top:${pct(geometry.footerTop)};padding-top:18px;border-top:1px solid rgba(80,56,44,0.15);text-align:center;font-size:26px;font-weight:700;letter-spacing:0.01em;">
            ${lineBreaks(content.eventTitle)}
        </footer>
    </article>`,
        'portrait',
        cormorantAndManropeFonts,
    );
};

const pinkPrint = (
    content: QrTemplateContent,
    qrDataUrl: string,
    previewAlt: string,
): string => {
    const geometry = qrTemplateGeometry.pink;

    return baseDocument(
        content.title,
        `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7e8ed center/cover no-repeat url('/qr-bg-themes/pink-base.png');color:#38232d;">
        <header style="position:absolute;left:8%;right:8%;top:${pct(geometry.headerTop)};text-align:center;">
            <p style="margin:0;font-size:18px;font-weight:800;letter-spacing:0.28em;text-transform:uppercase;color:rgba(111,70,83,0.78);">${lineBreaks(content.subtitle)}</p>
            <h1 style="margin:8px 0 0;font-family:${cormorantAndManropeFonts.headingFamily};font-size:114px;font-weight:600;line-height:0.86;letter-spacing:-0.05em;">${lineBreaks(content.title)}</h1>
            <p style="margin:10px 0 0;font-size:22px;line-height:1.4;color:rgba(98,63,75,0.82);">${lineBreaks(content.slogan)}</p>
        </header>
        <p style="position:absolute;left:16%;right:16%;top:${pct(geometry.messageTop)};margin:0;white-space:pre-line;font-size:20px;line-height:1.7;text-align:center;color:rgba(59,37,46,0.84);">${lineBreaks(content.message)}</p>
        <div style="${qrCardStyle('pink')}">${qrImage(qrDataUrl, previewAlt)}</div>
        <footer style="position:absolute;left:22%;right:22%;top:${pct(geometry.footerTop)};padding-top:16px;border-top:1px solid rgba(122,82,96,0.16);text-align:center;font-size:27px;font-weight:600;letter-spacing:0.03em;">
            ${lineBreaks(content.eventTitle)}
        </footer>
    </article>`,
        'portrait',
        cormorantAndManropeFonts,
    );
};

const pinkLandscapePrint = (
    content: QrTemplateContent,
    qrDataUrl: string,
    previewAlt: string,
): string => {
    const geometry = qrTemplateGeometry.pink_landscape;

    return baseDocument(
        content.title,
        `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7e8ed center/cover no-repeat url('/qr-bg-themes/pink-landscape-base.png');color:#38232d;">
        <header style="position:absolute;left:6%;width:54%;top:${pct(geometry.headerTop)};text-align:left;">
            <p style="margin:0;font-size:18px;font-weight:800;letter-spacing:0.28em;text-transform:uppercase;color:rgba(111,70,83,0.78);">${lineBreaks(content.subtitle)}</p>
            <h1 style="margin:8px 0 0;font-family:${cormorantAndManropeFonts.headingFamily};font-size:110px;font-weight:600;line-height:0.84;letter-spacing:-0.05em;">${lineBreaks(content.title)}</h1>
            <p style="margin:10px 0 0;font-size:24px;line-height:1.45;color:rgba(98,63,75,0.82);">${lineBreaks(content.slogan)}</p>
        </header>
        <p style="position:absolute;left:6%;width:50%;top:${pct(geometry.messageTop)};margin:0;white-space:pre-line;font-size:21px;line-height:1.7;color:rgba(59,37,46,0.84);">${lineBreaks(content.message)}</p>
        <div style="${qrCardStyle('pink_landscape')}">${qrImage(qrDataUrl, previewAlt)}</div>
        <footer style="position:absolute;left:6%;width:46%;top:${pct(geometry.footerTop)};padding-top:16px;border-top:1px solid rgba(122,82,96,0.16);font-size:27px;font-weight:600;letter-spacing:0.03em;">
            ${lineBreaks(content.eventTitle)}
        </footer>
    </article>`,
        'landscape',
        cormorantAndManropeFonts,
    );
};

export const qrTemplateDefinitions: QrTemplateDefinition[] = [
    {
        id: 'beige',
        label: 'Beige',
        component: BeigeQrTemplate,
        fonts: cormorantAndManropeFonts,
        printOrientation: 'portrait',
        renderPrintHtml: beigePrint,
    },
    {
        id: 'pink',
        label: 'Pink',
        component: PinkQrTemplate,
        fonts: cormorantAndManropeFonts,
        printOrientation: 'portrait',
        renderPrintHtml: pinkPrint,
    },
    {
        id: 'pink_landscape',
        label: 'Pink Landscape',
        component: PinkLandscapeQrTemplate,
        fonts: cormorantAndManropeFonts,
        printOrientation: 'landscape',
        renderPrintHtml: pinkLandscapePrint,
    },
];

export const resolveQrTemplateDefinition = (
    templateId: string | null | undefined,
): QrTemplateDefinition => {
    return (
        qrTemplateDefinitions.find((template) => template.id === templateId) ??
        qrTemplateDefinitions[0]
    );
};
