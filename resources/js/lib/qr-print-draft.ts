import type { QrTemplateId } from '@/lib/qr-print-templates';

export type QrPrintDraft = {
    templateId: QrTemplateId;
    subtitle: string;
    title: string;
    slogan: string;
    message: string;
    eventTitle: string;
};

const qrPrintDraftStorageKey = (eventId: number): string => `qrevents:qr-print-draft:${eventId}`;

const isQrTemplateId = (value: unknown): value is QrTemplateId => {
    return value === 'beige' || value === 'pink' || value === 'pink_landscape';
};

export const readQrPrintDraft = (eventId: number): Partial<QrPrintDraft> | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    const raw = window.localStorage.getItem(qrPrintDraftStorageKey(eventId));

    if (raw === null) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw) as Record<string, unknown>;

        return {
            templateId: isQrTemplateId(parsed.templateId) ? parsed.templateId : undefined,
            subtitle: typeof parsed.subtitle === 'string' ? parsed.subtitle : undefined,
            title: typeof parsed.title === 'string' ? parsed.title : undefined,
            slogan: typeof parsed.slogan === 'string' ? parsed.slogan : undefined,
            message: typeof parsed.message === 'string' ? parsed.message : undefined,
            eventTitle: typeof parsed.eventTitle === 'string' ? parsed.eventTitle : undefined,
        };
    } catch {
        return null;
    }
};

export const writeQrPrintDraft = (eventId: number, draft: QrPrintDraft): void => {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(qrPrintDraftStorageKey(eventId), JSON.stringify(draft));
};
