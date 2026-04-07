export type InviteStudioDraft = {
    template: string;
    headline: string;
    message: string;
    closing: string;
    contactPhone: string;
    publicRsvpEnabled: boolean;
    content: {
        partnerOneName: string;
        partnerTwoName: string;
        familyName: string;
        showFamilyName: boolean;
        brideParents: string;
        groomParents: string;
        godparents: string;
        dateText: string;
        venueText: string;
    };
    visibility: {
        couple: boolean;
        parents: boolean;
        godparents: boolean;
        date: boolean;
        venue: boolean;
        contactPhone: boolean;
    };
};

const inviteStudioDraftStorageKey = (eventId: number): string => `qrevents:invite-studio-draft:${eventId}`;

const asBoolean = (value: unknown, fallback: boolean): boolean => {
    return typeof value === 'boolean' ? value : fallback;
};

export const readInviteStudioDraft = (eventId: number): Partial<InviteStudioDraft> | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    const raw = window.localStorage.getItem(inviteStudioDraftStorageKey(eventId));

    if (raw === null) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw) as Record<string, unknown>;
        const content = parsed.content && typeof parsed.content === 'object' ? parsed.content as Record<string, unknown> : {};
        const visibility = parsed.visibility && typeof parsed.visibility === 'object' ? parsed.visibility as Record<string, unknown> : {};

        return {
            template: typeof parsed.template === 'string' ? parsed.template : undefined,
            headline: typeof parsed.headline === 'string' ? parsed.headline : undefined,
            message: typeof parsed.message === 'string' ? parsed.message : undefined,
            closing: typeof parsed.closing === 'string' ? parsed.closing : undefined,
            contactPhone: typeof parsed.contactPhone === 'string' ? parsed.contactPhone : undefined,
            publicRsvpEnabled: typeof parsed.publicRsvpEnabled === 'boolean' ? parsed.publicRsvpEnabled : undefined,
            content: {
                partnerOneName: typeof content.partnerOneName === 'string' ? content.partnerOneName : '',
                partnerTwoName: typeof content.partnerTwoName === 'string' ? content.partnerTwoName : '',
                familyName: typeof content.familyName === 'string' ? content.familyName : '',
                showFamilyName: asBoolean(content.showFamilyName, true),
                brideParents: typeof content.brideParents === 'string' ? content.brideParents : '',
                groomParents: typeof content.groomParents === 'string' ? content.groomParents : '',
                godparents: typeof content.godparents === 'string' ? content.godparents : '',
                dateText: typeof content.dateText === 'string' ? content.dateText : '',
                venueText: typeof content.venueText === 'string' ? content.venueText : '',
            },
            visibility: {
                couple: asBoolean(visibility.couple, true),
                parents: asBoolean(visibility.parents, true),
                godparents: asBoolean(visibility.godparents, true),
                date: asBoolean(visibility.date, true),
                venue: asBoolean(visibility.venue, true),
                contactPhone: asBoolean(visibility.contactPhone, true),
            },
        };
    } catch {
        return null;
    }
};

export const writeInviteStudioDraft = (eventId: number, draft: InviteStudioDraft): void => {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(inviteStudioDraftStorageKey(eventId), JSON.stringify(draft));
};
