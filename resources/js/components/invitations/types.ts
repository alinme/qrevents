export type InvitationSheetProps = {
    guestLabel?: string | null;
    logoUrl?: string | null;
    leadIn: string;
    title: string;
    message: string;
    closing: string;
    detailLines?: string[];
    dateLabel?: string | null;
    venueAddress?: string | null;
    contactPhone?: string | null;
};

export type InvitationFooterItem = {
    key: string;
    label: string;
    value: string;
};
