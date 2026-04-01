import canvaBrownConfig from './invitation-templates/canva_brown/config.json';
import canvaCreamConfig from './invitation-templates/canva_cream/config.json';
import canvaWatercolorConfig from './invitation-templates/canva_watercolor/config.json';
import classicConfig from './invitation-templates/classic/config.json';
import floralConfig from './invitation-templates/floral/config.json';
import midnightConfig from './invitation-templates/midnight/config.json';

export type InvitationTemplateId =
    | 'classic'
    | 'floral'
    | 'midnight'
    | 'canva_cream'
    | 'canva_brown'
    | 'canva_watercolor';

export type InvitationTemplateTextBlockConfig = {
    top: string;
    left: string;
    width: string;
    align: 'left' | 'center' | 'right';
    fontSizeLive?: string;
    fontSizePreview?: string;
    lineHeight?: string;
    letterSpacing?: string;
    fontWeight?: string;
    fontFamily?: string;
    uppercase?: boolean;
    color?: string | null;
};

export type InvitationTemplateHeaderConfig = {
    enabled: boolean;
    gap: string;
    eventName: InvitationTemplateTextBlockConfig;
    guestLabel: InvitationTemplateTextBlockConfig;
};

export type InvitationTemplateFooterConfig = {
    top: string;
    left: string;
    width: string;
    align: 'left' | 'center' | 'right';
    gap: string;
    metaGap: string;
    fontSize: string;
    lineHeight: string;
    fontFamily?: string;
    color?: string | null;
    closing: Omit<InvitationTemplateTextBlockConfig, 'top' | 'left' | 'width' | 'align'> & {
        maxWidth: string;
    };
};

export type InvitationTemplateDetailsConfig = InvitationTemplateTextBlockConfig & {
    gap: string;
};

export type InvitationTemplatePaperConfig = {
    aspectRatio: string;
    innerRadius: string;
    overlayOpacity: number;
};

export type InvitationTemplateLayoutConfig = {
    paper: InvitationTemplatePaperConfig;
    header: InvitationTemplateHeaderConfig;
    headline: InvitationTemplateTextBlockConfig;
    message: InvitationTemplateTextBlockConfig;
    details?: InvitationTemplateDetailsConfig;
    footer: InvitationTemplateFooterConfig;
};

export type InvitationTemplateStudioZoneId =
    | 'lead_in'
    | 'couple'
    | 'message'
    | 'rsvp_note'
    | 'parents'
    | 'godparents'
    | 'date'
    | 'venue'
    | 'contact_phone';

export type InvitationTemplateStudioZone = {
    id: InvitationTemplateStudioZoneId;
    safeLength: number;
    maxLength: number;
    minScale: number;
};

export type InvitationTemplateStudioConfig = {
    zones: InvitationTemplateStudioZone[];
};

export type InvitationTemplateDefinition = {
    id: InvitationTemplateId;
    label: string;
    summary: string;
    artClass: string;
    accentClass: string;
    layout: InvitationTemplateLayoutConfig;
    studio: InvitationTemplateStudioConfig;
    previewUrl?: string;
    baseUrl?: string;
    artworkTemplate?: boolean;
};

const defineInvitationTemplateLayout = (layout: InvitationTemplateLayoutConfig): InvitationTemplateLayoutConfig => layout;
const defineInvitationTemplateStudio = (studio: InvitationTemplateStudioConfig): InvitationTemplateStudioConfig => studio;

export const invitationTemplateDefinitions: InvitationTemplateDefinition[] = [
    {
        id: 'classic',
        label: 'Classic',
        summary: 'Warm cream paper, elegant spacing, and a traditional feel for formal families.',
        artClass: 'border-amber-200 bg-[linear-gradient(135deg,rgba(255,251,235,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top,rgba(217,119,6,0.18),transparent_50%)] text-neutral-900',
        accentClass: 'bg-amber-500/15 text-amber-700',
        layout: defineInvitationTemplateLayout(classicConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 34, maxLength: 60, minScale: 0.82 },
                { id: 'couple', safeLength: 30, maxLength: 54, minScale: 0.72 },
                { id: 'message', safeLength: 240, maxLength: 420, minScale: 0.78 },
                { id: 'rsvp_note', safeLength: 110, maxLength: 220, minScale: 0.82 },
                { id: 'parents', safeLength: 120, maxLength: 220, minScale: 0.84 },
                { id: 'godparents', safeLength: 72, maxLength: 140, minScale: 0.86 },
                { id: 'date', safeLength: 54, maxLength: 100, minScale: 0.88 },
                { id: 'venue', safeLength: 80, maxLength: 140, minScale: 0.88 },
                { id: 'contact_phone', safeLength: 24, maxLength: 40, minScale: 0.92 },
            ],
        }),
    },
    {
        id: 'floral',
        label: 'Floral',
        summary: 'Soft rose tones with romantic energy for weddings, baptisms, and family celebrations.',
        artClass: 'border-rose-200 bg-[linear-gradient(160deg,rgba(255,241,242,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top_right,rgba(244,114,182,0.22),transparent_45%)] text-neutral-900',
        accentClass: 'bg-rose-500/15 text-rose-700',
        layout: defineInvitationTemplateLayout(floralConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 32, maxLength: 58, minScale: 0.82 },
                { id: 'couple', safeLength: 28, maxLength: 52, minScale: 0.7 },
                { id: 'message', safeLength: 220, maxLength: 390, minScale: 0.76 },
                { id: 'rsvp_note', safeLength: 104, maxLength: 210, minScale: 0.82 },
                { id: 'parents', safeLength: 112, maxLength: 210, minScale: 0.82 },
                { id: 'godparents', safeLength: 72, maxLength: 140, minScale: 0.84 },
                { id: 'date', safeLength: 50, maxLength: 96, minScale: 0.88 },
                { id: 'venue', safeLength: 72, maxLength: 130, minScale: 0.88 },
                { id: 'contact_phone', safeLength: 24, maxLength: 40, minScale: 0.92 },
            ],
        }),
    },
    {
        id: 'midnight',
        label: 'Midnight',
        summary: 'Dark, polished, and modern with a richer stage-like mood for bold invitation pages.',
        artClass: 'border-slate-800 bg-[linear-gradient(160deg,rgba(15,23,42,0.98),rgba(30,41,59,0.96)),radial-gradient(circle_at_top,rgba(234,179,8,0.18),transparent_42%)] text-white',
        accentClass: 'bg-white/10 text-white/80',
        layout: defineInvitationTemplateLayout(midnightConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 32, maxLength: 60, minScale: 0.82 },
                { id: 'couple', safeLength: 24, maxLength: 48, minScale: 0.68 },
                { id: 'message', safeLength: 210, maxLength: 360, minScale: 0.74 },
                { id: 'rsvp_note', safeLength: 100, maxLength: 200, minScale: 0.8 },
                { id: 'parents', safeLength: 108, maxLength: 200, minScale: 0.82 },
                { id: 'godparents', safeLength: 68, maxLength: 132, minScale: 0.84 },
                { id: 'date', safeLength: 48, maxLength: 90, minScale: 0.88 },
                { id: 'venue', safeLength: 70, maxLength: 126, minScale: 0.88 },
                { id: 'contact_phone', safeLength: 24, maxLength: 40, minScale: 0.92 },
            ],
        }),
    },
    {
        id: 'canva_cream',
        label: 'Cream Canva',
        summary: 'Soft floral Canva artwork with a clean center safe area for names, date, and RSVP.',
        artClass: 'border-stone-200 bg-[#f9f7f2] text-neutral-900',
        accentClass: 'bg-stone-500/10 text-stone-700',
        layout: defineInvitationTemplateLayout(canvaCreamConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 28, maxLength: 46, minScale: 0.8 },
                { id: 'couple', safeLength: 24, maxLength: 40, minScale: 0.66 },
                { id: 'message', safeLength: 180, maxLength: 320, minScale: 0.72 },
                { id: 'rsvp_note', safeLength: 84, maxLength: 160, minScale: 0.8 },
                { id: 'parents', safeLength: 88, maxLength: 160, minScale: 0.8 },
                { id: 'godparents', safeLength: 64, maxLength: 120, minScale: 0.82 },
                { id: 'date', safeLength: 44, maxLength: 84, minScale: 0.88 },
                { id: 'venue', safeLength: 64, maxLength: 120, minScale: 0.86 },
                { id: 'contact_phone', safeLength: 20, maxLength: 36, minScale: 0.9 },
            ],
        }),
        previewUrl: '/invitation-templates/canva/cream/preview.png',
        baseUrl: '/invitation-templates/canva/cream/base.png',
        artworkTemplate: true,
    },
    {
        id: 'canva_brown',
        label: 'Brown Canva',
        summary: 'Warm watercolor florals with a romantic script headline and gentle earthy tones.',
        artClass: 'border-[#e7d4c7] bg-[#fcf7f3] text-neutral-900',
        accentClass: 'bg-[#8f5c46]/10 text-[#8f5c46]',
        layout: defineInvitationTemplateLayout(canvaBrownConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 28, maxLength: 46, minScale: 0.8 },
                { id: 'couple', safeLength: 22, maxLength: 38, minScale: 0.64 },
                { id: 'message', safeLength: 176, maxLength: 310, minScale: 0.72 },
                { id: 'rsvp_note', safeLength: 82, maxLength: 156, minScale: 0.8 },
                { id: 'parents', safeLength: 84, maxLength: 156, minScale: 0.8 },
                { id: 'godparents', safeLength: 62, maxLength: 118, minScale: 0.82 },
                { id: 'date', safeLength: 42, maxLength: 80, minScale: 0.88 },
                { id: 'venue', safeLength: 62, maxLength: 116, minScale: 0.86 },
                { id: 'contact_phone', safeLength: 20, maxLength: 36, minScale: 0.9 },
            ],
        }),
        previewUrl: '/invitation-templates/canva/brown/preview.png',
        baseUrl: '/invitation-templates/canva/brown/base.png',
        artworkTemplate: true,
    },
    {
        id: 'canva_watercolor',
        label: 'Watercolor Canva',
        summary: 'Soft green watercolor paper with a framed layout and airy garden invitation mood.',
        artClass: 'border-[#dfe6d8] bg-[#f7faf5] text-neutral-900',
        accentClass: 'bg-[#7b8b72]/10 text-[#6f7e69]',
        layout: defineInvitationTemplateLayout(canvaWatercolorConfig as InvitationTemplateLayoutConfig),
        studio: defineInvitationTemplateStudio({
            zones: [
                { id: 'lead_in', safeLength: 30, maxLength: 48, minScale: 0.8 },
                { id: 'couple', safeLength: 24, maxLength: 40, minScale: 0.66 },
                { id: 'message', safeLength: 184, maxLength: 320, minScale: 0.72 },
                { id: 'rsvp_note', safeLength: 84, maxLength: 160, minScale: 0.8 },
                { id: 'parents', safeLength: 88, maxLength: 160, minScale: 0.8 },
                { id: 'godparents', safeLength: 64, maxLength: 120, minScale: 0.82 },
                { id: 'date', safeLength: 44, maxLength: 84, minScale: 0.88 },
                { id: 'venue', safeLength: 64, maxLength: 120, minScale: 0.86 },
                { id: 'contact_phone', safeLength: 20, maxLength: 36, minScale: 0.9 },
            ],
        }),
        previewUrl: '/invitation-templates/canva/watercolor/base.png',
        baseUrl: '/invitation-templates/canva/watercolor/preview.png',
        artworkTemplate: true,
    },
];

export const invitationTemplateMap = Object.fromEntries(
    invitationTemplateDefinitions.map((template) => [template.id, template]),
) as Record<InvitationTemplateId, InvitationTemplateDefinition>;

export const findInvitationTemplateStudioZone = (
    templateId: InvitationTemplateId,
    zoneId: InvitationTemplateStudioZoneId,
): InvitationTemplateStudioZone | null => {
    return invitationTemplateMap[templateId].studio.zones.find((zone) => zone.id === zoneId) ?? null;
};
