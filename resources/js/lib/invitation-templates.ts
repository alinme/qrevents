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

export type InvitationTemplateDefinition = {
    id: InvitationTemplateId;
    label: string;
    summary: string;
    artClass: string;
    accentClass: string;
    layout: InvitationTemplateLayoutConfig;
    previewUrl?: string;
    baseUrl?: string;
    artworkTemplate?: boolean;
};

const defineInvitationTemplateLayout = (layout: InvitationTemplateLayoutConfig): InvitationTemplateLayoutConfig => layout;

export const invitationTemplateDefinitions: InvitationTemplateDefinition[] = [
    {
        id: 'classic',
        label: 'Classic',
        summary: 'Warm cream paper, elegant spacing, and a traditional feel for formal families.',
        artClass: 'border-amber-200 bg-[linear-gradient(135deg,rgba(255,251,235,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top,rgba(217,119,6,0.18),transparent_50%)] text-neutral-900',
        accentClass: 'bg-amber-500/15 text-amber-700',
        layout: defineInvitationTemplateLayout(classicConfig as InvitationTemplateLayoutConfig),
    },
    {
        id: 'floral',
        label: 'Floral',
        summary: 'Soft rose tones with romantic energy for weddings, baptisms, and family celebrations.',
        artClass: 'border-rose-200 bg-[linear-gradient(160deg,rgba(255,241,242,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top_right,rgba(244,114,182,0.22),transparent_45%)] text-neutral-900',
        accentClass: 'bg-rose-500/15 text-rose-700',
        layout: defineInvitationTemplateLayout(floralConfig as InvitationTemplateLayoutConfig),
    },
    {
        id: 'midnight',
        label: 'Midnight',
        summary: 'Dark, polished, and modern with a richer stage-like mood for bold invitation pages.',
        artClass: 'border-slate-800 bg-[linear-gradient(160deg,rgba(15,23,42,0.98),rgba(30,41,59,0.96)),radial-gradient(circle_at_top,rgba(234,179,8,0.18),transparent_42%)] text-white',
        accentClass: 'bg-white/10 text-white/80',
        layout: defineInvitationTemplateLayout(midnightConfig as InvitationTemplateLayoutConfig),
    },
    {
        id: 'canva_cream',
        label: 'Cream Canva',
        summary: 'Soft floral Canva artwork with a clean center safe area for names, date, and RSVP.',
        artClass: 'border-stone-200 bg-[#f9f7f2] text-neutral-900',
        accentClass: 'bg-stone-500/10 text-stone-700',
        layout: defineInvitationTemplateLayout(canvaCreamConfig as InvitationTemplateLayoutConfig),
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
        previewUrl: '/invitation-templates/canva/watercolor/base.png',
        baseUrl: '/invitation-templates/canva/watercolor/preview.png',
        artworkTemplate: true,
    },
];

export const invitationTemplateMap = Object.fromEntries(
    invitationTemplateDefinitions.map((template) => [template.id, template]),
) as Record<InvitationTemplateId, InvitationTemplateDefinition>;
