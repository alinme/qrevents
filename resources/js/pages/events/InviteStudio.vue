<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import GuestInvitationPanel from '@/components/events/guests/GuestInvitationPanel.vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    composeInvitationPaperPresentation,
    resolveInvitationFooterMeta,
} from '@/lib/invitation-presentation';
import type {
    InvitationStudioContent,
    InvitationStudioVisibility,
    InvitationWeddingDetails,
} from '@/lib/invitation-presentation';
import {
    findInvitationTemplateStudioZone,
    invitationTemplateDefinitions,
} from '@/lib/invitation-templates';
import type { InvitationTemplateId } from '@/lib/invitation-templates';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    type: string;
};

type EventLinks = {
    inviteStudio: string;
    invitationSettingsUpdate: string;
    guestInvitationsBulkUpdate: string;
};

type EventInvitationSettings = {
    template: InvitationTemplateId;
    headline: string;
    message: string;
    closing: string;
    contactPhone: string | null;
    publicRsvpEnabled: boolean;
    content: InvitationStudioContent;
    visibility: InvitationStudioVisibility;
};

type InvitationPreviewPayload = {
    eventDetails: {
        dateLabel: string;
        venueAddress: string | null;
        weddingDetails: InvitationWeddingDetails;
    };
    branding: {
        logoUrl: string | null;
    };
};

type GuestParty = {
    id: number;
    name: string;
    attendanceStatus: 'pending' | 'accepted' | 'declined';
    invitationOpenCount: number;
    reminderCount: number;
    respondedAt: string | null;
    inviteUrl: string;
    invitationHistory: Array<{
        type: 'sent_online' | 'delivered_in_person' | 'reminded' | 'opened' | 'responded';
        deliveryChannel: string | null;
        createdAt: string | null;
        meta: Record<string, unknown>;
    }>;
};

type InvitationTemplatePreviewContent = {
    eventName: string;
    guestLabel: string | null;
    headline: string;
    message: string;
    closing: string;
    detailLines: string[];
    dateLabel: string | null;
    venueAddress: string | null;
};

type InvitationOverflowWarning = {
    key: string;
    tone: 'warning' | 'danger';
    label: string;
    message: string;
};

type InvitationActivity = {
    guestName: string;
    type: 'sent_online' | 'delivered_in_person' | 'reminded' | 'opened' | 'responded';
    deliveryChannel: string | null;
    createdAt: string | null;
    meta: Record<string, unknown>;
};

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    eventInvitationSettings: EventInvitationSettings;
    publicInvitationUrl: string;
    invitationPreview: InvitationPreviewPayload;
    guestParties: GuestParty[];
}>();

const { locale, t } = useTranslations();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.inviteStudio,
    },
    {
        title: t('app.nav.invite_studio'),
        href: props.eventLinks.inviteStudio,
    },
]);

const invitationSettingsForm = useForm({
    template: props.eventInvitationSettings.template,
    headline: props.eventInvitationSettings.headline,
    message: props.eventInvitationSettings.message,
    closing: props.eventInvitationSettings.closing,
    contact_phone: props.eventInvitationSettings.contactPhone ?? '',
    public_rsvp_enabled: props.eventInvitationSettings.publicRsvpEnabled,
    content: {
        partner_one_name: props.eventInvitationSettings.content.partnerOneName,
        partner_two_name: props.eventInvitationSettings.content.partnerTwoName,
        family_name: props.eventInvitationSettings.content.familyName,
        show_family_name: props.eventInvitationSettings.content.showFamilyName,
        bride_parents: props.eventInvitationSettings.content.brideParents,
        groom_parents: props.eventInvitationSettings.content.groomParents,
        godparents: props.eventInvitationSettings.content.godparents,
        date_text: props.eventInvitationSettings.content.dateText,
        venue_text: props.eventInvitationSettings.content.venueText,
    },
    visibility: {
        couple: props.eventInvitationSettings.visibility.couple,
        parents: props.eventInvitationSettings.visibility.parents,
        godparents: props.eventInvitationSettings.visibility.godparents,
        date: props.eventInvitationSettings.visibility.date,
        venue: props.eventInvitationSettings.visibility.venue,
        contact_phone: props.eventInvitationSettings.visibility.contactPhone,
    },
});

const invitationBulkForm = useForm({
    guest_party_ids: [] as number[],
    action: 'mark_sent_online' as 'mark_delivered_in_person' | 'mark_sent_online' | 'mark_reminded_online',
    delivery_channel: 'other' as 'in_person' | 'phone' | 'whatsapp' | 'facebook' | 'public_link' | 'other',
});

const savingInvitationSettings = ref(false);
const previewingInvitationTemplateId = ref<InvitationTemplateId | null>(null);
const invitationTemplateCards = invitationTemplateDefinitions;

const localeFormat = computed(() => {
    if (locale.value === 'ro') {
        return 'ro-RO';
    }

    if (locale.value === 'el') {
        return 'el-GR';
    }

    return 'en-GB';
});

const pendingInvitationParties = computed(() => {
    return props.guestParties.filter((party) => party.attendanceStatus === 'pending');
});

const previewingInvitationTemplateCard = computed(() => {
    if (!previewingInvitationTemplateId.value) {
        return null;
    }

    return invitationTemplateCards.find((template) => template.id === previewingInvitationTemplateId.value) ?? null;
});

const invitationStudioContent = computed<InvitationStudioContent>(() => ({
    partnerOneName: invitationSettingsForm.content.partner_one_name,
    partnerTwoName: invitationSettingsForm.content.partner_two_name,
    familyName: invitationSettingsForm.content.family_name,
    showFamilyName: invitationSettingsForm.content.show_family_name,
    brideParents: invitationSettingsForm.content.bride_parents,
    groomParents: invitationSettingsForm.content.groom_parents,
    godparents: invitationSettingsForm.content.godparents,
    dateText: invitationSettingsForm.content.date_text,
    venueText: invitationSettingsForm.content.venue_text,
}));

const invitationStudioVisibility = computed<InvitationStudioVisibility>(() => ({
    couple: invitationSettingsForm.visibility.couple,
    parents: invitationSettingsForm.visibility.parents,
    godparents: invitationSettingsForm.visibility.godparents,
    date: invitationSettingsForm.visibility.date,
    venue: invitationSettingsForm.visibility.venue,
    contactPhone: invitationSettingsForm.visibility.contact_phone,
}));

const invitationTemplatePreviewContent = (templateId: InvitationTemplateId): InvitationTemplatePreviewContent => {
    if (templateId === 'canva_cream') {
        const samplePresentation = composeInvitationPaperPresentation({
            eventName: 'You are invited to',
            eventType: 'wedding',
            headline: 'You are invited to',
            weddingDetails: {
                partnerOneName: 'Luca',
                partnerTwoName: 'Danielle',
                familyName: '',
                showFamilyName: false,
                brideParents: 'Maria and Daniel',
                groomParents: 'Elena and Victor',
                godparents: 'Bianca and Stefan',
            },
        });

        return {
            eventName: samplePresentation.leadIn,
            guestLabel: null,
            headline: samplePresentation.title,
            message: 'We are getting married!',
            closing: 'See you there',
            detailLines: samplePresentation.detailLines,
            dateLabel: 'Saturday • 15 November 2023 • 6 PM',
            venueAddress: '123 Anywhere St., Any City',
        };
    }

    if (templateId === 'canva_brown') {
        const samplePresentation = composeInvitationPaperPresentation({
            eventName: 'You are invited to',
            eventType: 'wedding',
            headline: 'You are invited to',
            weddingDetails: {
                partnerOneName: 'Elena',
                partnerTwoName: 'Matei',
                familyName: '',
                showFamilyName: false,
                brideParents: 'Adriana and Pavel',
                groomParents: 'Monica and Sorin',
                godparents: 'Bianca and Vlad',
            },
        });

        return {
            eventName: samplePresentation.leadIn,
            guestLabel: null,
            headline: samplePresentation.title,
            message: 'We are getting married!',
            closing: 'With love',
            detailLines: samplePresentation.detailLines,
            dateLabel: 'Saturday • 20 September 2026 • 10 PM',
            venueAddress: '123 Anywhere St., Any City',
        };
    }

    if (templateId === 'canva_watercolor') {
        const samplePresentation = composeInvitationPaperPresentation({
            eventName: 'You are invited to',
            eventType: 'wedding',
            headline: 'You are invited to',
            weddingDetails: {
                partnerOneName: 'Francois',
                partnerTwoName: 'Juliana',
                familyName: '',
                showFamilyName: false,
                brideParents: 'Sofia and Mircea',
                groomParents: 'Camelia and Adrian',
                godparents: 'Andreea and Stefan',
            },
        });

        return {
            eventName: samplePresentation.leadIn,
            guestLabel: null,
            headline: samplePresentation.title,
            message: 'Join us for our wedding celebration.',
            closing: 'Please respond when you can',
            detailLines: samplePresentation.detailLines,
            dateLabel: 'Sunday • 28 January 2027 • 10 PM',
            venueAddress: '123 Anywhere St., Any City',
        };
    }

    const presentation = composeInvitationPaperPresentation({
        eventName: props.currentEvent.name,
        eventType: props.currentEvent.type,
        headline: invitationSettingsForm.headline || props.currentEvent.name,
        content: invitationStudioContent.value,
        visibility: invitationStudioVisibility.value,
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    });
    const footerMeta = resolveInvitationFooterMeta({
        defaultDateLabel: props.invitationPreview.eventDetails.dateLabel,
        defaultVenueAddress: props.invitationPreview.eventDetails.venueAddress,
        contactPhone: invitationSettingsForm.contact_phone || null,
        content: invitationStudioContent.value,
        visibility: invitationStudioVisibility.value,
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    });

    return {
        eventName: presentation.leadIn,
        guestLabel: t('guests.invitation.preview_guest_label'),
        headline: presentation.title,
        message: invitationSettingsForm.message || t('guests.invitation.default_message'),
        closing: invitationSettingsForm.closing || t('guests.invitation.default_closing'),
        detailLines: presentation.detailLines,
        dateLabel: footerMeta.dateLabel,
        venueAddress: footerMeta.venueAddress,
    };
};

const activeInvitationPresentation = computed(() => composeInvitationPaperPresentation({
    eventName: props.currentEvent.name,
    eventType: props.currentEvent.type,
    headline: invitationSettingsForm.headline || props.currentEvent.name,
    content: invitationStudioContent.value,
    visibility: invitationStudioVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const activeInvitationFooterMeta = computed(() => resolveInvitationFooterMeta({
    defaultDateLabel: props.invitationPreview.eventDetails.dateLabel,
    defaultVenueAddress: props.invitationPreview.eventDetails.venueAddress,
    contactPhone: invitationSettingsForm.contact_phone || null,
    content: invitationStudioContent.value,
    visibility: invitationStudioVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const invitationOverflowWarnings = computed<InvitationOverflowWarning[]>(() => {
    const titleZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'couple');
    const leadInZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'lead_in');
    const messageZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'message');
    const rsvpZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'rsvp_note');
    const parentsZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'parents');
    const godparentsZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'godparents');
    const venueZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'venue');
    const dateZone = findInvitationTemplateStudioZone(invitationSettingsForm.template, 'date');
    const warnings: InvitationOverflowWarning[] = [];

    const pushZoneWarning = (
        key: string,
        label: string,
        value: string | null,
        safeLength: number | undefined,
        maxLength: number | undefined,
    ): void => {
        const length = value?.trim().length ?? 0;
        if (length === 0 || safeLength === undefined || maxLength === undefined) {
            return;
        }

        if (length > maxLength) {
            warnings.push({
                key,
                tone: 'danger',
                label,
                message: t('guests.invitation.fit_error', { label, max: maxLength }),
            });

            return;
        }

        if (length > safeLength) {
            warnings.push({
                key,
                tone: 'warning',
                label,
                message: t('guests.invitation.fit_warning', { label, max: maxLength }),
            });
        }
    };

    pushZoneWarning('lead_in', t('guests.invitation.lead_in_label'), invitationSettingsForm.headline, leadInZone?.safeLength, leadInZone?.maxLength);
    pushZoneWarning('couple', t('guests.invitation.couple_title'), activeInvitationPresentation.value.title, titleZone?.safeLength, titleZone?.maxLength);
    pushZoneWarning('message', t('guests.invitation.message_label'), invitationSettingsForm.message, messageZone?.safeLength, messageZone?.maxLength);
    pushZoneWarning('rsvp_note', t('guests.invitation.rsvp_note_label'), invitationSettingsForm.closing, rsvpZone?.safeLength, rsvpZone?.maxLength);

    if (invitationSettingsForm.visibility.parents) {
        pushZoneWarning(
            'parents',
            t('guests.invitation.parents_title'),
            [invitationSettingsForm.content.bride_parents, invitationSettingsForm.content.groom_parents].filter(Boolean).join(' '),
            parentsZone?.safeLength,
            parentsZone?.maxLength,
        );
    }

    if (invitationSettingsForm.visibility.godparents) {
        pushZoneWarning(
            'godparents',
            t('guests.invitation.godparents_label'),
            invitationSettingsForm.content.godparents,
            godparentsZone?.safeLength,
            godparentsZone?.maxLength,
        );
    }

    if (invitationSettingsForm.visibility.date) {
        pushZoneWarning(
            'date',
            t('guests.invitation.date_text_label'),
            activeInvitationFooterMeta.value.dateLabel,
            dateZone?.safeLength,
            dateZone?.maxLength,
        );
    }

    if (invitationSettingsForm.visibility.venue) {
        pushZoneWarning(
            'venue',
            t('guests.invitation.venue_text_label'),
            activeInvitationFooterMeta.value.venueAddress,
            venueZone?.safeLength,
            venueZone?.maxLength,
        );
    }

    return warnings;
});

const invitationSummaryCards = computed(() => [
    {
        label: t('guests.invitation.summary.pending'),
        value: pendingInvitationParties.value.length,
    },
    {
        label: t('guests.invitation.summary.opened'),
        value: props.guestParties.filter((party) => party.invitationOpenCount > 0).length,
    },
    {
        label: t('guests.invitation.summary.answered'),
        value: props.guestParties.filter((party) => party.respondedAt !== null).length,
    },
    {
        label: t('guests.invitation.summary.reminded'),
        value: props.guestParties.filter((party) => party.reminderCount > 0).length,
    },
]);

const invitationRecentActivity = computed<InvitationActivity[]>(() => {
    return props.guestParties
        .flatMap((party) => party.invitationHistory.map((activity) => ({
            ...activity,
            guestName: party.name,
        })))
        .sort((left, right) => {
            const leftTime = left.createdAt ? new Date(left.createdAt).getTime() : 0;
            const rightTime = right.createdAt ? new Date(right.createdAt).getTime() : 0;

            return rightTime - leftTime;
        })
        .slice(0, 6);
});

const saveInvitationSettings = (): void => {
    savingInvitationSettings.value = true;

    invitationSettingsForm.patch(props.eventLinks.invitationSettingsUpdate, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(t('guests.messages.invitation_saved'));
        },
        onFinish: () => {
            savingInvitationSettings.value = false;
        },
    });
};

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return t('guests.shared.not_yet');
    }

    return new Intl.DateTimeFormat(localeFormat.value, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const invitationHistoryLabel = (activity: GuestParty['invitationHistory'][number]): string => {
    const attendanceStatus = typeof activity.meta.attendanceStatus === 'string' ? activity.meta.attendanceStatus : null;

    return {
        sent_online: t('guests.history.sent_online'),
        delivered_in_person: t('guests.history.delivered'),
        reminded: t('guests.history.reminded'),
        opened: t('guests.history.opened'),
        responded: attendanceStatus === 'accepted'
            ? t('guests.history.responded_accepted')
            : attendanceStatus === 'declined'
                ? t('guests.history.responded_declined')
                : t('guests.history.responded_updated'),
    }[activity.type];
};

const copyLink = async (url: string, label: string): Promise<void> => {
    try {
        await navigator.clipboard.writeText(url);
        toast.success(t('guests.messages.label_copied', { label }));
    } catch {
        toast.error(t('guests.messages.copy_label_failed', { label: label.toLowerCase() }));
    }
};

const openInvite = (url: string): void => {
    window.open(url, '_blank', 'noopener,noreferrer');
};

const saveInvitationPreview = (): void => {
    const separator = props.publicInvitationUrl.includes('?') ? '&' : '?';

    window.open(`${props.publicInvitationUrl}${separator}print=1`, '_blank', 'noopener,noreferrer');
};

const invitationMessageForParty = (party: GuestParty): string => {
    return [
        invitationSettingsForm.headline,
        `${party.name},`,
        invitationSettingsForm.message,
        invitationSettingsForm.closing,
        invitationSettingsForm.visibility.contact_phone && invitationSettingsForm.contact_phone
            ? t('guests.messages.contact_line', { phone: invitationSettingsForm.contact_phone })
            : null,
        t('guests.messages.rsvp_line', { url: party.inviteUrl }),
    ]
        .filter((line): line is string => Boolean(line && line.trim() !== ''))
        .join('\n\n');
};

const invitationMessageForParties = (parties: GuestParty[]): string => {
    return parties
        .map((party) => invitationMessageForParty(party))
        .join('\n\n--------------------\n\n');
};

const reminderMessageForParty = (party: GuestParty): string => {
    return [
        `${party.name},`,
        t('guests.messages.reminder_intro'),
        invitationSettingsForm.headline,
        invitationSettingsForm.message,
        invitationSettingsForm.visibility.contact_phone && invitationSettingsForm.contact_phone
            ? t('guests.messages.contact_line', { phone: invitationSettingsForm.contact_phone })
            : null,
        t('guests.messages.rsvp_line', { url: party.inviteUrl }),
    ]
        .filter((line): line is string => Boolean(line && line.trim() !== ''))
        .join('\n\n');
};

const reminderMessageForParties = (parties: GuestParty[]): string => {
    return parties
        .map((party) => reminderMessageForParty(party))
        .join('\n\n--------------------\n\n');
};

const updateInvitationDelivery = (
    guestPartyIds: number[],
    action: 'mark_delivered_in_person' | 'mark_sent_online' | 'mark_reminded_online',
    deliveryChannel: 'in_person' | 'phone' | 'whatsapp' | 'facebook' | 'public_link' | 'other',
): void => {
    if (guestPartyIds.length === 0) {
        return;
    }

    invitationBulkForm.guest_party_ids = guestPartyIds;
    invitationBulkForm.action = action;
    invitationBulkForm.delivery_channel = deliveryChannel;

    invitationBulkForm.post(props.eventLinks.guestInvitationsBulkUpdate, {
        preserveScroll: true,
    });
};

const shareGuestPartyBundle = async (
    parties: GuestParty[],
    mode: 'invite' | 'reminder',
): Promise<void> => {
    if (parties.length === 0) {
        toast.error(
            mode === 'reminder'
                ? t('guests.messages.no_pending_reminders')
                : t('guests.messages.select_invitee_first'),
        );

        return;
    }

    const text = mode === 'reminder'
        ? reminderMessageForParties(parties)
        : invitationMessageForParties(parties);
    const title = mode === 'reminder'
        ? `${props.currentEvent.name} reminder`
        : `${props.currentEvent.name} invitations`;

    try {
        if (navigator.share) {
            await navigator.share({
                title,
                text,
            });
        } else {
            await navigator.clipboard.writeText(text);
            toast.success(
                mode === 'reminder'
                    ? t('guests.messages.reminder_bundle_copied')
                    : t('guests.messages.invitation_bundle_copied'),
            );
        }

        updateInvitationDelivery(
            parties.map((party) => party.id),
            mode === 'reminder' ? 'mark_reminded_online' : 'mark_sent_online',
            'other',
        );
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }

        toast.error(
            mode === 'reminder'
                ? t('guests.messages.share_reminder_failed')
                : t('guests.messages.share_invitation_failed'),
        );
    }
};

const sharePendingInvites = async (): Promise<void> => {
    await shareGuestPartyBundle(pendingInvitationParties.value, 'invite');
};

const remindPendingInvites = async (): Promise<void> => {
    await shareGuestPartyBundle(pendingInvitationParties.value, 'reminder');
};

const copyPendingInvites = async (): Promise<void> => {
    if (pendingInvitationParties.value.length === 0) {
        toast.error(t('guests.messages.no_pending_to_copy'));

        return;
    }

    try {
        await navigator.clipboard.writeText(invitationMessageForParties(pendingInvitationParties.value));
        toast.success(t('guests.messages.pending_bundle_copied'));
    } catch {
        toast.error(t('guests.messages.pending_bundle_copy_failed'));
    }
};

const closeInvitationTemplatePreview = (): void => {
    previewingInvitationTemplateId.value = null;
};

const handleInvitationTemplatePreviewOpenChange = (open: boolean): void => {
    if (!open) {
        closeInvitationTemplatePreview();
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${currentEvent.name} · ${t('app.nav.invite_studio')}`" />

        <div class="space-y-5 p-4 md:p-6">
            <section class="space-y-2">
                <h1 class="text-xl font-semibold tracking-tight text-neutral-950">
                    {{ t('guests.invitation.studio_title') }}
                </h1>
                <p class="max-w-3xl text-sm text-neutral-600">
                    {{ t('guests.invitation.studio_description') }}
                </p>
            </section>

            <GuestInvitationPanel
                :current-event-name="currentEvent.name"
                :invitation-template-cards="invitationTemplateCards"
                :selected-template="invitationSettingsForm.template"
                :headline="invitationSettingsForm.headline"
                :message="invitationSettingsForm.message"
                :closing="invitationSettingsForm.closing"
                :public-rsvp-enabled="invitationSettingsForm.public_rsvp_enabled"
                :contact-phone="invitationSettingsForm.contact_phone"
                :invitation-content="invitationStudioContent"
                :invitation-visibility="invitationStudioVisibility"
                :invitation-overflow-warnings="invitationOverflowWarnings"
                :public-invitation-url="publicInvitationUrl"
                :logo-url="invitationPreview.branding.logoUrl"
                :active-invitation-presentation="activeInvitationPresentation"
                :active-invitation-footer-meta="activeInvitationFooterMeta"
                :invitation-summary-cards="invitationSummaryCards"
                :invitation-recent-activity="invitationRecentActivity"
                :saving-invitation-settings="savingInvitationSettings"
                :invitation-settings-processing="invitationSettingsForm.processing"
                :invitation-template-preview-content="invitationTemplatePreviewContent"
                :invitation-history-label="invitationHistoryLabel"
                :format-date-time="formatDateTime"
                @update:selected-template="invitationSettingsForm.template = $event"
                @update:headline="invitationSettingsForm.headline = $event"
                @update:message="invitationSettingsForm.message = $event"
                @update:closing="invitationSettingsForm.closing = $event"
                @update:public-rsvp-enabled="invitationSettingsForm.public_rsvp_enabled = $event"
                @update:contact-phone="invitationSettingsForm.contact_phone = $event"
                @update:content="invitationSettingsForm.content = {
                    partner_one_name: $event.partnerOneName,
                    partner_two_name: $event.partnerTwoName,
                    family_name: $event.familyName,
                    show_family_name: $event.showFamilyName,
                    bride_parents: $event.brideParents,
                    groom_parents: $event.groomParents,
                    godparents: $event.godparents,
                    date_text: $event.dateText,
                    venue_text: $event.venueText,
                }"
                @update:visibility="invitationSettingsForm.visibility = {
                    couple: $event.couple,
                    parents: $event.parents,
                    godparents: $event.godparents,
                    date: $event.date,
                    venue: $event.venue,
                    contact_phone: $event.contactPhone,
                }"
                @preview-template="previewingInvitationTemplateId = $event"
                @open-invite="openInvite"
                @save-preview="saveInvitationPreview"
                @copy-link="(url, label) => copyLink(url, label)"
                @share-pending="sharePendingInvites"
                @copy-pending="copyPendingInvites"
                @remind-pending="remindPendingInvites"
                @save-settings="saveInvitationSettings"
            />
        </div>

        <Dialog :open="previewingInvitationTemplateCard !== null" @update:open="handleInvitationTemplatePreviewOpenChange">
            <DialogContent class="max-w-[min(96vw,46rem)] p-4 sm:p-5">
                <DialogHeader>
                    <DialogTitle>
                        {{ previewingInvitationTemplateCard?.label ?? t('guests.invitation.preview_guest_label') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ t('guests.dialogs.preview.description') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-50 p-3 sm:p-4">
                    <InvitationPaper
                        v-if="previewingInvitationTemplateCard"
                        :template="previewingInvitationTemplateCard.id"
                        :event-name="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).eventName"
                        :logo-url="invitationPreview.branding.logoUrl"
                        :guest-label="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).guestLabel"
                        :headline="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).headline"
                        :message="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).message"
                        :closing="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).closing"
                        :detail-lines="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).detailLines"
                        :date-label="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).dateLabel"
                        :venue-address="invitationTemplatePreviewContent(previewingInvitationTemplateCard.id).venueAddress"
                        mode="preview"
                    />
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
