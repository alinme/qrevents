import type { QrPrintLayoutConfig, QrPrintOrientation, QrPrintTextBlockConfig } from '@/lib/qr-print-themes';

type NormalizedBox = {
    x: number;
    y: number;
    width: number;
    height: number;
    centerX: number;
    centerY: number;
    area: number;
};

type SvgAnalysisResult = Record<QrPrintOrientation, Partial<QrPrintLayoutConfig>>;

const analysisCache = new Map<string, Promise<SvgAnalysisResult>>();

const clamp = (value: number, min: number, max: number): number => {
    return Math.min(max, Math.max(min, value));
};

const parseViewBox = (value: string | null): { width: number; height: number } | null => {
    if (!value) {
        return null;
    }

    const parts = value.trim().split(/\s+/).map(Number);
    if (parts.length !== 4 || parts.some((part) => Number.isNaN(part))) {
        return null;
    }

    return {
        width: parts[2],
        height: parts[3],
    };
};

const normalizeBox = (
    x: number,
    y: number,
    width: number,
    height: number,
    viewWidth: number,
    viewHeight: number,
): NormalizedBox => {
    return {
        x: x / viewWidth,
        y: y / viewHeight,
        width: width / viewWidth,
        height: height / viewHeight,
        centerX: (x + width / 2) / viewWidth,
        centerY: (y + height / 2) / viewHeight,
        area: (width * height) / (viewWidth * viewHeight),
    };
};

const collectBoxes = (svg: SVGSVGElement, viewWidth: number, viewHeight: number): NormalizedBox[] => {
    const boxes: NormalizedBox[] = [];

    svg.querySelectorAll('*').forEach((node) => {
        if (!(node instanceof SVGGraphicsElement)) {
            return;
        }

        const tagName = node.tagName.toLowerCase();
        if (['defs', 'clipPath', 'mask', 'filter', 'metadata', 'title', 'desc'].includes(tagName)) {
            return;
        }

        if (typeof node.getBBox !== 'function') {
            return;
        }

        try {
            const bbox = node.getBBox();
            if (bbox.width <= 1 || bbox.height <= 1) {
                return;
            }

            boxes.push(normalizeBox(bbox.x, bbox.y, bbox.width, bbox.height, viewWidth, viewHeight));
        } catch {
            return;
        }
    });

    return boxes;
};

const chooseQrCandidate = (boxes: NormalizedBox[]): NormalizedBox | null => {
    const candidates = boxes
        .filter((box) => {
            const aspectRatio = box.width / box.height;

            return (
                box.area >= 0.02
                && box.area <= 0.18
                && aspectRatio >= 0.75
                && aspectRatio <= 1.35
                && box.centerY >= 0.28
                && box.centerY <= 0.72
            );
        })
        .map((box) => {
            const aspectScore = 1 - Math.min(1, Math.abs(1 - box.width / box.height));
            const centerScore = 1 - Math.min(1, Math.abs(0.5 - box.centerX) * 1.8);
            const verticalScore = 1 - Math.min(1, Math.abs(0.54 - box.centerY) * 2.4);
            const areaScore = 1 - Math.min(1, Math.abs(0.085 - box.area) / 0.085);

            return {
                box,
                score: aspectScore * 0.35 + centerScore * 0.2 + verticalScore * 0.3 + areaScore * 0.15,
            };
        })
        .sort((first, second) => second.score - first.score);

    return candidates[0]?.box ?? null;
};

const chooseTopDecorationBottom = (boxes: NormalizedBox[]): number => {
    const topBoxes = boxes.filter((box) => {
        return box.centerY <= 0.32 && box.area >= 0.002 && box.area <= 0.14;
    });

    if (topBoxes.length === 0) {
        return 0.08;
    }

    return clamp(Math.max(...topBoxes.map((box) => box.y + box.height)), 0.06, 0.22);
};

const chooseBottomDecorationTop = (boxes: NormalizedBox[]): number => {
    const bottomBoxes = boxes.filter((box) => {
        return box.centerY >= 0.72 && box.area >= 0.002 && box.area <= 0.16;
    });

    if (bottomBoxes.length === 0) {
        return 0.94;
    }

    return clamp(Math.min(...bottomBoxes.map((box) => box.y)), 0.78, 0.96);
};

const chooseSideBounds = (boxes: NormalizedBox[]): { left: number; right: number } => {
    const edgeBoxes = boxes.filter((box) => {
        return box.area >= 0.003 && box.area <= 0.18 && (box.centerX <= 0.26 || box.centerX >= 0.74);
    });

    const left = clamp(
        edgeBoxes
            .filter((box) => box.centerX <= 0.26)
            .reduce((carry, box) => Math.max(carry, box.x + box.width + 0.03), 0.11),
        0.1,
        0.3,
    );

    const right = clamp(
        edgeBoxes
            .filter((box) => box.centerX >= 0.74)
            .reduce((carry, box) => Math.min(carry, box.x - 0.03), 0.89),
        0.7,
        0.9,
    );

    return { left, right };
};

const deriveTextBlock = (
    y: number,
    width: number,
    centerX: number,
    template: QrPrintTextBlockConfig,
): Partial<QrPrintTextBlockConfig> => {
    return {
        y,
        width,
        centerX,
        fontSize: template.fontSize,
        fontFamily: template.fontFamily,
        fontWeight: template.fontWeight,
        maxLines: template.maxLines,
        lineHeight: template.lineHeight,
        minScale: template.minScale,
    };
};

const deriveLayoutFromBoxes = (
    boxes: NormalizedBox[],
    orientation: QrPrintOrientation,
    fallback: QrPrintLayoutConfig,
): Partial<QrPrintLayoutConfig> => {
    const qrBox = chooseQrCandidate(boxes);
    if (qrBox === null) {
        return {};
    }

    const topDecorationBottom = chooseTopDecorationBottom(boxes);
    const bottomDecorationTop = chooseBottomDecorationTop(boxes);
    const sideBounds = chooseSideBounds(boxes);
    const centerX = clamp(qrBox.centerX, 0.42, 0.58);
    const safeWidth = clamp(sideBounds.right - sideBounds.left, 0.34, orientation === 'landscape' ? 0.52 : 0.62);
    const qrBottom = qrBox.y + qrBox.height;
    const titleY = clamp(topDecorationBottom + 0.075, 0.14, qrBox.y - (orientation === 'landscape' ? 0.12 : 0.17));
    const bodyY = clamp(titleY + (orientation === 'landscape' ? 0.11 : 0.13), titleY + 0.08, qrBox.y - 0.08);
    const scanHintY = clamp(qrBottom + (orientation === 'landscape' ? 0.06 : 0.07), qrBottom + 0.04, bottomDecorationTop - 0.14);
    const footerY = clamp(scanHintY + (orientation === 'landscape' ? 0.055 : 0.06), scanHintY + 0.04, bottomDecorationTop - 0.08);
    const urlY = clamp(footerY + (orientation === 'landscape' ? 0.055 : 0.065), footerY + 0.045, 0.93);

    return {
        qrCenterX: centerX,
        qrSize: clamp(Math.max(qrBox.width, qrBox.height) * (orientation === 'landscape' ? 0.84 : 0.88), 0.18, 0.36),
        qrY: clamp(qrBox.y + qrBox.height * 0.06, 0.3, 0.58),
        qrFramePadding: clamp(Math.min(qrBox.width, qrBox.height) * 0.06, 0.018, 0.032),
        eyebrow: deriveTextBlock(
            clamp(titleY - (orientation === 'landscape' ? 0.06 : 0.07), 0.08, titleY - 0.03),
            clamp(safeWidth * 0.82, 0.28, 0.56),
            centerX,
            fallback.eyebrow,
        ),
        title: deriveTextBlock(
            titleY,
            clamp(safeWidth, 0.34, orientation === 'landscape' ? 0.52 : 0.62),
            centerX,
            fallback.title,
        ),
        body: deriveTextBlock(
            bodyY,
            clamp(safeWidth * 0.9, 0.3, orientation === 'landscape' ? 0.46 : 0.56),
            centerX,
            fallback.body,
        ),
        scanHint: deriveTextBlock(
            scanHintY,
            clamp(safeWidth * 0.76, 0.24, 0.5),
            centerX,
            fallback.scanHint,
        ),
        footer: deriveTextBlock(
            footerY,
            clamp(safeWidth * 0.86, 0.28, 0.54),
            centerX,
            fallback.footer,
        ),
        url: deriveTextBlock(
            urlY,
            clamp(safeWidth * 0.92, 0.32, 0.6),
            centerX,
            fallback.url,
        ),
    };
};

const analyzeSvgDocument = (svgText: string, fallback: Record<QrPrintOrientation, QrPrintLayoutConfig>): SvgAnalysisResult => {
    if (typeof document === 'undefined') {
        return { portrait: {}, landscape: {} };
    }

    const parser = new DOMParser();
    const svgDocument = parser.parseFromString(svgText, 'image/svg+xml');
    const parsedSvg = svgDocument.documentElement;
    const viewBox = parseViewBox(parsedSvg.getAttribute('viewBox'));

    if (!(parsedSvg instanceof SVGSVGElement) || viewBox === null) {
        return { portrait: {}, landscape: {} };
    }

    const host = document.createElement('div');
    host.style.position = 'absolute';
    host.style.left = '-99999px';
    host.style.top = '0';
    host.style.width = '0';
    host.style.height = '0';
    host.style.overflow = 'hidden';

    const mountedSvg = document.importNode(parsedSvg, true) as SVGSVGElement;
    mountedSvg.setAttribute('width', String(viewBox.width));
    mountedSvg.setAttribute('height', String(viewBox.height));
    mountedSvg.style.overflow = 'visible';

    host.appendChild(mountedSvg);
    document.body.appendChild(host);

    const boxes = collectBoxes(mountedSvg, viewBox.width, viewBox.height)
        .filter((box) => box.area < 0.96)
        .filter((box) => box.width < 0.94 || box.height < 0.94);

    document.body.removeChild(host);

    return {
        portrait: deriveLayoutFromBoxes(boxes, 'portrait', fallback.portrait),
        landscape: deriveLayoutFromBoxes(boxes, 'landscape', fallback.landscape),
    };
};

export const analyzeQrPrintThemeSvg = (
    artworkUrl: string,
    fallback: Record<QrPrintOrientation, QrPrintLayoutConfig>,
): Promise<SvgAnalysisResult> => {
    const cacheKey = `${artworkUrl}:${JSON.stringify(fallback)}`;

    if (analysisCache.has(cacheKey)) {
        return analysisCache.get(cacheKey)!;
    }

    const promise = fetch(artworkUrl)
        .then((response) => response.text())
        .then((svgText) => analyzeSvgDocument(svgText, fallback))
        .catch(() => ({ portrait: {}, landscape: {} }));

    analysisCache.set(cacheKey, promise);

    return promise;
};
