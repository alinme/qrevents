import type { Component } from 'vue';
import CanvaBrownInvitationTemplate from '@/components/invitations/templates/CanvaBrownInvitationTemplate.vue';
import CanvaCreamInvitationTemplate from '@/components/invitations/templates/CanvaCreamInvitationTemplate.vue';
import CanvaWatercolorInvitationTemplate from '@/components/invitations/templates/CanvaWatercolorInvitationTemplate.vue';

export type InvitationSheetThemeId = 'canva_cream' | 'canva_brown' | 'canva_watercolor';

export type InvitationSheetTheme = {
    id: InvitationSheetThemeId;
    label: string;
    component: Component;
};

export const invitationSheetThemes: InvitationSheetTheme[] = [
    {
        id: 'canva_cream',
        label: 'Cream',
        component: CanvaCreamInvitationTemplate,
    },
    {
        id: 'canva_brown',
        label: 'Brown',
        component: CanvaBrownInvitationTemplate,
    },
    {
        id: 'canva_watercolor',
        label: 'Watercolor',
        component: CanvaWatercolorInvitationTemplate,
    },
];

export const normalizeInvitationSheetTheme = (value: string | null | undefined): InvitationSheetThemeId => {
    if (value === 'canva_brown') {
        return 'canva_brown';
    }

    if (value === 'canva_watercolor' || value === 'floral') {
        return 'canva_watercolor';
    }

    return 'canva_cream';
};

export const resolveInvitationSheetTheme = (value: string | null | undefined): InvitationSheetTheme => {
    const themeId = normalizeInvitationSheetTheme(value);

    return invitationSheetThemes.find((theme) => theme.id === themeId) ?? invitationSheetThemes[0];
};
