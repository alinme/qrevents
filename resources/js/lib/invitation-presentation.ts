export type InvitationWeddingDetails = {
    partnerOneName: string;
    partnerTwoName: string;
    familyName: string;
    showFamilyName: boolean;
    brideParents: string;
    groomParents: string;
    godparents: string;
};

export type InvitationStudioContent = {
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

export type InvitationStudioVisibility = {
    couple: boolean;
    parents: boolean;
    godparents: boolean;
    date: boolean;
    venue: boolean;
    contactPhone: boolean;
};

type ComposeInvitationPresentationInput = {
    eventName: string;
    eventType: string;
    headline: string;
    content?: Partial<InvitationStudioContent> | null;
    visibility?: Partial<InvitationStudioVisibility> | null;
    weddingDetails?: InvitationWeddingDetails | null;
};

type InvitationPaperPresentation = {
    leadIn: string;
    title: string;
    detailLines: string[];
};

const trimmed = (value: string | null | undefined): string => value?.trim() ?? '';

const resolvedVisibility = (
    content: Partial<InvitationStudioContent> | null | undefined,
    weddingDetails: InvitationWeddingDetails | null | undefined,
    visibility: Partial<InvitationStudioVisibility> | null | undefined,
): InvitationStudioVisibility => {
    const brideParents = trimmed(content?.brideParents) || trimmed(weddingDetails?.brideParents);
    const groomParents = trimmed(content?.groomParents) || trimmed(weddingDetails?.groomParents);
    const godparents = trimmed(content?.godparents) || trimmed(weddingDetails?.godparents);
    const dateText = trimmed(content?.dateText);
    const venueText = trimmed(content?.venueText);

    return {
        couple: visibility?.couple ?? true,
        parents: visibility?.parents ?? (brideParents !== '' || groomParents !== ''),
        godparents: visibility?.godparents ?? (godparents !== ''),
        date: visibility?.date ?? (dateText !== ''),
        venue: visibility?.venue ?? (venueText !== ''),
        contactPhone: visibility?.contactPhone ?? false,
    };
};

export const composeInvitationPaperPresentation = (
    input: ComposeInvitationPresentationInput,
): InvitationPaperPresentation => {
    const fallbackHeadline = trimmed(input.headline) || trimmed(input.eventName) || 'Invitation';
    const weddingDetails = input.weddingDetails;
    const content = input.content;
    const visibility = resolvedVisibility(content, weddingDetails, input.visibility);

    if (
        input.eventType !== 'wedding'
        || weddingDetails === null
        || weddingDetails === undefined
    ) {
        return {
            leadIn: trimmed(input.eventName) || 'Invitation',
            title: fallbackHeadline,
            detailLines: [],
        };
    }

    const partnerOneName = trimmed(content?.partnerOneName) || trimmed(weddingDetails.partnerOneName);
    const partnerTwoName = trimmed(content?.partnerTwoName) || trimmed(weddingDetails.partnerTwoName);
    const familyName = trimmed(content?.familyName) || trimmed(weddingDetails.familyName);
    const showFamilyName = (content?.showFamilyName ?? weddingDetails.showFamilyName) && familyName !== '';
    const brideParents = trimmed(content?.brideParents) || trimmed(weddingDetails.brideParents);
    const groomParents = trimmed(content?.groomParents) || trimmed(weddingDetails.groomParents);
    const godparents = trimmed(content?.godparents) || trimmed(weddingDetails.godparents);

    if (partnerOneName === '' && partnerTwoName === '') {
        return {
            leadIn: trimmed(input.eventName) || 'Invitation',
            title: fallbackHeadline,
            detailLines: [],
        };
    }

    const partnerNames = [partnerOneName, partnerTwoName].filter((value) => value !== '');
    const title = visibility.couple
        ? (showFamilyName ? `${partnerNames.join(' & ')} ${familyName}` : partnerNames.join(' & '))
        : fallbackHeadline;

    const detailLines = [
        visibility.parents && brideParents !== '' ? `Bride's parents: ${brideParents}` : null,
        visibility.parents && groomParents !== '' ? `Groom's parents: ${groomParents}` : null,
        visibility.godparents && godparents !== '' ? `Godparents: ${godparents}` : null,
    ].filter((value): value is string => value !== null);

    return {
        leadIn: fallbackHeadline,
        title,
        detailLines,
    };
};

type ResolveInvitationFooterMetaInput = {
    defaultDateLabel: string | null;
    defaultVenueAddress: string | null;
    contactPhone: string | null;
    content?: Partial<InvitationStudioContent> | null;
    visibility?: Partial<InvitationStudioVisibility> | null;
    weddingDetails?: InvitationWeddingDetails | null;
};

export const resolveInvitationFooterMeta = (
    input: ResolveInvitationFooterMetaInput,
): {
    dateLabel: string | null;
    venueAddress: string | null;
    contactPhone: string | null;
} => {
    const visibility = resolvedVisibility(input.content, input.weddingDetails, input.visibility);
    const dateText = trimmed(input.content?.dateText) || trimmed(input.defaultDateLabel);
    const venueText = trimmed(input.content?.venueText) || trimmed(input.defaultVenueAddress);
    const contactPhone = trimmed(input.contactPhone);

    return {
        dateLabel: visibility.date && dateText !== '' ? dateText : null,
        venueAddress: visibility.venue && venueText !== '' ? venueText : null,
        contactPhone: visibility.contactPhone && contactPhone !== '' ? contactPhone : null,
    };
};
