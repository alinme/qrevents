export type InvitationSheetThemeId = 'canva_cream' | 'canva_brown' | 'canva_watercolor';

export type InvitationSheetTheme = {
    id: InvitationSheetThemeId;
    label: string;
    backgroundUrl: string;
    overlay: string;
    paperTint: string;
    inkColor: string;
    accentColor: string;
    chipBackground: string;
    chipColor: string;
};

export const invitationSheetThemes: InvitationSheetTheme[] = [
    {
        id: 'canva_cream',
        label: 'Cream',
        backgroundUrl: '/invitation-templates/canva/cream/base.png',
        overlay: 'linear-gradient(180deg, rgba(255, 248, 241, 0.78), rgba(255, 252, 248, 0.88))',
        paperTint: '#fff8f1',
        inkColor: '#4a3526',
        accentColor: '#ab7b54',
        chipBackground: 'rgba(255, 250, 245, 0.78)',
        chipColor: '#6a4a35',
    },
    {
        id: 'canva_brown',
        label: 'Brown',
        backgroundUrl: '/invitation-templates/canva/brown/base.png',
        overlay: 'linear-gradient(180deg, rgba(244, 236, 228, 0.74), rgba(250, 245, 240, 0.86))',
        paperTint: '#f7efe8',
        inkColor: '#3f2b20',
        accentColor: '#8c6248',
        chipBackground: 'rgba(255, 247, 241, 0.7)',
        chipColor: '#5b3d2c',
    },
    {
        id: 'canva_watercolor',
        label: 'Watercolor',
        backgroundUrl: '/invitation-templates/canva/watercolor/base.png',
        overlay: 'linear-gradient(180deg, rgba(255, 250, 248, 0.68), rgba(255, 253, 252, 0.84))',
        paperTint: '#fff9f7',
        inkColor: '#4a3942',
        accentColor: '#bc7b7d',
        chipBackground: 'rgba(255, 250, 249, 0.7)',
        chipColor: '#7b4f58',
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
