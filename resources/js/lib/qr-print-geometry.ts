/**
 * Layout geometry for the QR print templates.
 *
 * Each background PNG in /qr-bg-themes has a drawn QR placeholder zone; the
 * fractions below were measured from those images so the rendered QR lands
 * exactly inside the artwork, on screen and in print alike.
 */
export type QrZone = {
    /** Fraction of page width from the left edge. */
    left: number;
    /** Fraction of page height from the top edge. */
    top: number;
    /** Fraction of page width. */
    width: number;
};

export type QrTemplateGeometry = {
    /** Page pixel ratio used to keep the QR square (pageWidth / pageHeight). */
    pageRatio: number;
    qr: QrZone;
    headerTop: number;
    messageTop: number;
    footerTop: number;
};

export const qrTemplateGeometry: Record<
    'beige' | 'pink' | 'pink_landscape',
    QrTemplateGeometry
> = {
    // 1410 x 2000 — drawn frame at x 30.8–69.8%, y 53.4–81.0%
    beige: {
        pageRatio: 1410 / 2000,
        qr: { left: 0.328, top: 0.552, width: 0.345 },
        headerTop: 0.065,
        messageTop: 0.38,
        footerTop: 0.86,
    },
    // 1414 x 2000 — white card at x 31.6–72.6%, y 45.9–77.7%
    pink: {
        pageRatio: 1414 / 2000,
        qr: { left: 0.378, top: 0.502, width: 0.288 },
        headerTop: 0.085,
        messageTop: 0.33,
        footerTop: 0.82,
    },
    // 1748 x 1240 — white box at x 67.4–92.8%, y 28.5–71.4%
    pink_landscape: {
        pageRatio: 1748 / 1240,
        qr: { left: 0.6925, top: 0.348, width: 0.215 },
        headerTop: 0.1,
        messageTop: 0.52,
        footerTop: 0.8,
    },
};

export const qrZoneHeightFraction = (geometry: QrTemplateGeometry): number =>
    geometry.qr.width * geometry.pageRatio;
