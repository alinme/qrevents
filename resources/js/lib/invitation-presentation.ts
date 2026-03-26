export type InvitationWeddingDetails = {
    partnerOneName: string;
    partnerTwoName: string;
    familyName: string;
    showFamilyName: boolean;
    brideParents: string;
    groomParents: string;
    godparents: string;
};

type ComposeInvitationPresentationInput = {
    eventName: string;
    eventType: string;
    headline: string;
    weddingDetails?: InvitationWeddingDetails | null;
};

type InvitationPaperPresentation = {
    leadIn: string;
    title: string;
    detailLines: string[];
};

const trimmed = (value: string | null | undefined): string => value?.trim() ?? '';

export const composeInvitationPaperPresentation = (
    input: ComposeInvitationPresentationInput,
): InvitationPaperPresentation => {
    const fallbackHeadline = trimmed(input.headline) || trimmed(input.eventName) || 'Invitation';
    const weddingDetails = input.weddingDetails;

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

    const partnerOneName = trimmed(weddingDetails.partnerOneName);
    const partnerTwoName = trimmed(weddingDetails.partnerTwoName);
    const familyName = trimmed(weddingDetails.familyName);
    const showFamilyName = weddingDetails.showFamilyName && familyName !== '';
    const brideParents = trimmed(weddingDetails.brideParents);
    const groomParents = trimmed(weddingDetails.groomParents);
    const godparents = trimmed(weddingDetails.godparents);

    if (partnerOneName === '' && partnerTwoName === '') {
        return {
            leadIn: trimmed(input.eventName) || 'Invitation',
            title: fallbackHeadline,
            detailLines: [],
        };
    }

    const partnerNames = [partnerOneName, partnerTwoName].filter((value) => value !== '');
    const title = showFamilyName
        ? `${partnerNames.join(' & ')} ${familyName}`
        : partnerNames.join(' & ');

    const detailLines = [
        brideParents !== '' ? `Bride's parents: ${brideParents}` : null,
        groomParents !== '' ? `Groom's parents: ${groomParents}` : null,
        godparents !== '' ? `Godparents: ${godparents}` : null,
    ].filter((value): value is string => value !== null);

    return {
        leadIn: fallbackHeadline,
        title,
        detailLines,
    };
};
