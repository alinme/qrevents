import type { Composer } from 'vue-i18n';
import type { InvitationFooterItem, InvitationSheetProps } from '@/components/invitations/types';

type TranslationFunction = Composer['t'] | ((key: string) => string);

export const buildInvitationFooterItems = (
    t: TranslationFunction,
    props: InvitationSheetProps,
): InvitationFooterItem[] => {
    return [
        props.dateLabel
            ? {
                key: 'date',
                label: t('invitations.event_date') as string,
                value: props.dateLabel,
            }
            : null,
        props.venueAddress
            ? {
                key: 'venue',
                label: t('invitations.venue') as string,
                value: props.venueAddress,
            }
            : null,
        props.contactPhone
            ? {
                key: 'contact',
                label: t('guests.invitation.contact_phone') as string,
                value: props.contactPhone,
            }
            : null,
    ].filter((item): item is InvitationFooterItem => item !== null);
};
