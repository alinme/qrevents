import type { Component } from 'vue';
import BeigeQrTemplate from '@/components/events/qr-templates/BeigeQrTemplate.vue';
import PinkLandscapeQrTemplate from '@/components/events/qr-templates/PinkLandscapeQrTemplate.vue';
import PinkQrTemplate from '@/components/events/qr-templates/PinkQrTemplate.vue';
import type { QrTemplateContent } from '@/components/events/qr-templates/types';

export type QrTemplateId = 'beige' | 'pink' | 'pink_landscape';

export type QrTemplateDefinition = {
    id: QrTemplateId;
    label: string;
    component: Component;
    printOrientation: 'portrait' | 'landscape';
    renderPrintHtml: (content: QrTemplateContent, qrDataUrl: string, previewAlt: string) => string;
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

const baseDocument = (title: string, body: string, orientation: 'portrait' | 'landscape'): string => `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>${escapeHtml(title)}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: "Manrope", sans-serif;
        }
    </style>
</head>
<body>
${body}
</body>
</html>`;

const beigePrint = (content: QrTemplateContent, qrDataUrl: string, previewAlt: string): string => {
    return baseDocument(content.title, `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7efe6 center/cover no-repeat url('/qr-bg-themes/beige-base.png');color:#2f211a;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at top, rgba(255,255,255,0.34), transparent 42%), linear-gradient(180deg, rgba(255,249,243,0.52), rgba(255,252,249,0.68));"></div>
        <div style="position:relative;z-index:1;display:flex;height:100%;flex-direction:column;align-items:center;justify-content:space-between;padding:58px 54px 52px;text-align:center;">
            <header>
                <p style="margin:0;font-size:19px;font-weight:800;letter-spacing:0.32em;text-transform:uppercase;color:rgba(81,57,45,0.75);">${lineBreaks(content.subtitle)}</p>
                <h1 style="margin:8px 0 0;font-family:'Cormorant Garamond',serif;font-size:122px;font-weight:600;line-height:0.88;letter-spacing:-0.04em;">${lineBreaks(content.title)}</h1>
                <p style="margin:8px 0 0;font-size:24px;line-height:1.45;color:rgba(75,52,41,0.8);">${lineBreaks(content.slogan)}</p>
            </header>
            <div style="display:flex;width:100%;flex-direction:column;align-items:center;gap:30px;">
                <div style="width:318px;border-radius:24px;background:rgba(255,255,255,0.94);padding:12px;box-shadow:0 24px 52px rgba(60,38,30,0.12);">
                    <img src="${qrDataUrl}" alt="${escapeHtml(previewAlt)}" style="display:block;width:100%;height:auto;" />
                </div>
                <p style="margin:0;max-width:620px;white-space:pre-line;font-size:22px;line-height:1.75;color:rgba(56,38,29,0.84);">${lineBreaks(content.message)}</p>
            </div>
            <footer style="width:min(100%,640px);padding-top:18px;border-top:1px solid rgba(80,56,44,0.15);font-size:26px;font-weight:700;letter-spacing:0.01em;">
                ${lineBreaks(content.eventTitle)}
            </footer>
        </div>
    </article>`, 'portrait');
};

const pinkPrint = (content: QrTemplateContent, qrDataUrl: string, previewAlt: string): string => {
    return baseDocument(content.title, `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7e8ed center/cover no-repeat url('/qr-bg-themes/pink-base.png');color:#38232d;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at top, rgba(255,255,255,0.32), transparent 45%), linear-gradient(180deg, rgba(255,247,250,0.48), rgba(255,251,252,0.70));"></div>
        <div style="position:relative;z-index:1;display:grid;height:100%;grid-template-rows:auto minmax(0,1fr) auto;padding:44px 54px;text-align:center;">
            <header>
                <p style="margin:0;font-size:18px;font-weight:800;letter-spacing:0.28em;text-transform:uppercase;color:rgba(111,70,83,0.78);">${lineBreaks(content.subtitle)}</p>
                <h1 style="margin:8px 0 0;font-family:'Cormorant Garamond',serif;font-size:114px;font-weight:600;line-height:0.86;letter-spacing:-0.05em;">${lineBreaks(content.title)}</h1>
                <p style="margin:8px 0 0;font-size:22px;line-height:1.4;color:rgba(98,63,75,0.82);">${lineBreaks(content.slogan)}</p>
            </header>
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px;">
                <p style="order:2;margin:0;max-width:540px;white-space:pre-line;font-size:20px;line-height:1.75;color:rgba(59,37,46,0.84);">${lineBreaks(content.message)}</p>
                <div style="order:1;width:296px;border-radius:26px;background:rgba(255,255,255,0.95);padding:12px;box-shadow:0 24px 52px rgba(84,46,58,0.12);">
                    <img src="${qrDataUrl}" alt="${escapeHtml(previewAlt)}" style="display:block;width:100%;height:auto;" />
                </div>
            </div>
            <footer style="margin:0 auto;width:min(100%,520px);padding-top:16px;border-top:1px solid rgba(122,82,96,0.16);font-size:27px;font-weight:600;letter-spacing:0.03em;">
                ${lineBreaks(content.eventTitle)}
            </footer>
        </div>
    </article>`, 'portrait');
};

const pinkLandscapePrint = (content: QrTemplateContent, qrDataUrl: string, previewAlt: string): string => {
    return baseDocument(content.title, `
    <article style="position:relative;width:100vw;height:100vh;overflow:hidden;background:#f7e8ed center/cover no-repeat url('/qr-bg-themes/pink-landscape-base.png');color:#38232d;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at top left, rgba(255,255,255,0.3), transparent 42%), linear-gradient(180deg, rgba(255,247,250,0.42), rgba(255,251,252,0.64));"></div>
        <div style="position:relative;z-index:1;display:grid;height:100%;grid-template-columns:minmax(0,1fr) 19rem;gap:32px;padding:38px 58px 42px;text-align:left;">
            <div style="display:flex;height:100%;flex-direction:column;justify-content:space-between;">
                <header>
                    <p style="margin:0;font-size:18px;font-weight:800;letter-spacing:0.28em;text-transform:uppercase;color:rgba(111,70,83,0.78);">${lineBreaks(content.subtitle)}</p>
                    <h1 style="margin:8px 0 0;font-family:'Cormorant Garamond',serif;font-size:110px;font-weight:600;line-height:0.84;letter-spacing:-0.05em;">${lineBreaks(content.title)}</h1>
                    <p style="margin:10px 0 0;font-size:24px;line-height:1.45;color:rgba(98,63,75,0.82);">${lineBreaks(content.slogan)}</p>
                </header>
                <div style="display:grid;gap:24px;">
                    <p style="margin:0;max-width:700px;white-space:pre-line;font-size:21px;line-height:1.78;color:rgba(59,37,46,0.84);">${lineBreaks(content.message)}</p>
                    <footer style="width:min(100%,540px);padding-top:16px;border-top:1px solid rgba(122,82,96,0.16);font-size:27px;font-weight:600;letter-spacing:0.03em;">
                        ${lineBreaks(content.eventTitle)}
                    </footer>
                </div>
            </div>
            <div style="display:flex;height:100%;align-items:center;justify-content:center;">
                <div style="width:100%;border-radius:26px;background:rgba(255,255,255,0.95);padding:12px;box-shadow:0 24px 52px rgba(84,46,58,0.12);">
                    <img src="${qrDataUrl}" alt="${escapeHtml(previewAlt)}" style="display:block;width:100%;height:auto;" />
                </div>
            </div>
        </div>
    </article>`, 'landscape');
};

export const qrTemplateDefinitions: QrTemplateDefinition[] = [
    {
        id: 'beige',
        label: 'Beige',
        component: BeigeQrTemplate,
        printOrientation: 'portrait',
        renderPrintHtml: beigePrint,
    },
    {
        id: 'pink',
        label: 'Pink',
        component: PinkQrTemplate,
        printOrientation: 'portrait',
        renderPrintHtml: pinkPrint,
    },
    {
        id: 'pink_landscape',
        label: 'Pink Landscape',
        component: PinkLandscapeQrTemplate,
        printOrientation: 'landscape',
        renderPrintHtml: pinkLandscapePrint,
    },
];

export const resolveQrTemplateDefinition = (templateId: string | null | undefined): QrTemplateDefinition => {
    return qrTemplateDefinitions.find((template) => template.id === templateId) ?? qrTemplateDefinitions[0];
};
