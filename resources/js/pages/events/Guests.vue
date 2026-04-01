<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Copy,
    ExternalLink,
    Import,
    ListChecks,
    Pencil,
    ScrollText,
    Table2,
    Trash2,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import GuestCheckInPanel from '@/components/events/guests/GuestCheckInPanel.vue';
import GuestInvitationPanel from '@/components/events/guests/GuestInvitationPanel.vue';
import GuestInviteesPanel from '@/components/events/guests/GuestInviteesPanel.vue';
import GuestLedgerPanel from '@/components/events/guests/GuestLedgerPanel.vue';
import GuestSectionSwitcher from '@/components/events/guests/GuestSectionSwitcher.vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    NativeSelect,
    NativeSelectOption,
} from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    composeInvitationPaperPresentation,
    resolveInvitationFooterMeta,
} from '@/lib/invitation-presentation';
import type { InvitationStudioContent, InvitationStudioVisibility, InvitationWeddingDetails } from '@/lib/invitation-presentation';
import {
    findInvitationTemplateStudioZone,
    invitationTemplateDefinitions
} from '@/lib/invitation-templates';
import type { InvitationTemplateId } from '@/lib/invitation-templates';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    type: string;
    retentionEndsAt?: string | null;
};

type EventLinks = {
    guests: string;
    guestReport: string;
    guestPartiesStore: string;
    guestPartiesImport: string;
    guestInvitationsBulkUpdate: string;
    invitationSettingsUpdate: string;
    tablesStore: string;
    publicGuestList: string;
};

type EventTable = {
    id: number;
    name: string;
    seatsCount: number;
    occupiedSeats: number;
    remainingSeats: number;
    isFull: boolean;
    updateUrl: string;
    deleteUrl: string;
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

type GuestParty = {
    id: number;
    name: string;
    phone: string | null;
    eventTableId: number | null;
    tableName: string | null;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number | null;
    attendanceStatus: 'pending' | 'accepted' | 'declined';
    actualAttendeesCount: number | null;
    actualAttendanceStatus: 'unknown' | 'present' | 'absent';
    actualAttendanceRecordedAt: string | null;
    notes: string | null;
    invitationStatus: 'draft' | 'delivered_in_person' | 'sent' | 'opened' | 'responded';
    invitationDeliveryChannel: string | null;
    invitationDeliveredAt: string | null;
    invitationOpenCount: number;
    invitationFirstOpenedAt: string | null;
    invitationLastOpenedAt: string | null;
    invitationLastOpenedIp: string | null;
    respondedAt: string | null;
    reminderCount: number;
    lastReminderAt: string | null;
    invitationHistory: Array<{
        type: 'sent_online' | 'delivered_in_person' | 'reminded' | 'opened' | 'responded';
        deliveryChannel: string | null;
        createdAt: string | null;
        meta: Record<string, unknown>;
    }>;
    giftType: 'money' | 'gift' | null;
    giftCurrency: 'EUR' | 'GBP' | 'RON' | null;
    giftAmount: string | null;
    guestNames: string | null;
    mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
    responseNotes: string | null;
    inviteUrl: string;
    publicCheckInUrl: string;
    updateUrl: string;
    deleteUrl: string;
};

type GuestPartyStats = {
    partyCount: number;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number;
    actualAttendeesCount: number;
    acceptedPartyCount: number;
    pendingPartyCount: number;
    declinedPartyCount: number;
    presentPartyCount: number;
    absentPartyCount: number;
    moneyGiftTotal: number;
    moneyGiftCurrency: string;
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

type GuestSection = 'invitees' | 'invitation' | 'ledger' | 'guest_list';

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    eventInvitationSettings: EventInvitationSettings;
    publicInvitationUrl: string;
    publicGuestListUrl: string;
    invitationPreview: InvitationPreviewPayload;
    guestLedgerExportUrl: string;
    guestPartyStats: GuestPartyStats;
    eventTables: EventTable[];
    guestParties: GuestParty[];
}>();

const currentEvent = props.currentEvent;
const publicInvitationUrl = props.publicInvitationUrl;
const publicGuestListUrl = props.publicGuestListUrl;
const { locale, t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.guests,
    },
    {
        title: t('guests.page.title'),
        href: props.eventLinks.guests,
    },
];

const guestDialogOpen = ref(false);
const importDialogOpen = ref(false);
const tablesDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const guestListInfoDialogOpen = ref(false);
const activeGuestParty = ref<GuestParty | null>(null);
const guestListInfoParty = ref<GuestParty | null>(null);
const savingInvitationSettings = ref(false);
const showGuestAdvanced = ref(false);
const previewingInvitationTemplateId = ref<InvitationTemplateId | null>(null);
const editingTableId = ref<number | null>(null);
const ledgerEntryDialogOpen = ref(false);
const activeLedgerParty = ref<GuestParty | null>(null);
const ledgerGiftMode = ref<'money' | 'gift' | 'both'>('money');
const activeSection = ref<GuestSection>('invitees');
const expandedGuestPartyId = ref<number | null>(null);
const quickSavingGuestId = ref<number | null>(null);
const selectedGuestIds = ref<number[]>([]);
const guestSearch = ref('');
const guestFilter = ref<'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift'>('all');
const ledgerEntryForm = useForm({
    gift_currency: 'EUR' as 'EUR' | 'GBP' | 'RON',
    gift_amount: '',
    note: '',
});

const guestForm = useForm({
    name: '',
    phone: '',
    table_name: '',
    event_table_id: '',
    invited_attendees_count: 1,
    confirmed_attendees_count: '',
    attendance_status: 'pending',
    actual_attendees_count: '',
    actual_attendance_status: 'unknown',
    notes: '',
    invitation_status: 'draft',
    invitation_delivery_channel: 'public_link',
    gift_type: '',
    gift_currency: 'EUR',
    gift_amount: '',
});

const importForm = useForm<{
    import_text: string;
    import_file: File | null;
}>({
    import_text: '',
    import_file: null,
});

const tableForm = useForm({
    name: '',
    seats_count: 8,
});

const guestListTableForm = useForm({
    event_table_id: '',
});

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

const isEditing = computed(() => activeGuestParty.value !== null);
const showGiftAmount = computed(() => guestForm.gift_type === 'money');
const showConfirmedCount = computed(() => guestForm.attendance_status === 'accepted');
const showActualCount = computed(() => guestForm.actual_attendance_status === 'present');
const editingTable = computed(() => props.eventTables.find((table) => table.id === editingTableId.value) ?? null);
const selectableTables = computed(() => props.eventTables.map((table) => ({
    ...table,
    selectable: !table.isFull || table.id === Number(guestForm.event_table_id || 0),
})));
const guestListSelectableTables = computed(() => props.eventTables.map((table) => ({
    ...table,
    selectable: !table.isFull || table.id === Number(guestListTableForm.event_table_id || 0),
})));
const filteredGuestParties = computed(() => {
    const search = guestSearch.value.trim().toLowerCase();

    return props.guestParties.filter((party) => {
        const matchesSearch = search === ''
            || party.name.toLowerCase().includes(search)
            || (party.phone ?? '').toLowerCase().includes(search)
            || (party.notes ?? '').toLowerCase().includes(search);

        const matchesFilter = (() => {
            switch (guestFilter.value) {
                case 'needing_reply':
                    return party.attendanceStatus === 'pending';
                case 'accepted':
                    return party.attendanceStatus === 'accepted';
                case 'declined':
                    return party.attendanceStatus === 'declined';
                case 'present':
                    return party.actualAttendanceStatus === 'present';
                case 'absent':
                    return party.actualAttendanceStatus === 'absent';
                case 'not_sent':
                    return party.invitationStatus === 'draft';
                case 'responded':
                    return party.invitationStatus === 'responded';
                case 'no_gift':
                    return party.giftType === null;
                default:
                    return true;
            }
        })();

        return matchesSearch && matchesFilter;
    });
});
const selectedGuestParties = computed(() => props.guestParties.filter((party) => selectedGuestIds.value.includes(party.id)));
const selectedPendingGuestParties = computed(() => selectedGuestParties.value.filter((party) => party.attendanceStatus === 'pending'));
const pendingInvitationParties = computed(() => props.guestParties.filter((party) => party.attendanceStatus === 'pending'));
const allGuestsSelected = computed(() => filteredGuestParties.value.length > 0 && filteredGuestParties.value.every((party) => selectedGuestIds.value.includes(party.id)));
const guestListSearch = ref('');
const guestListParties = computed(() => {
    const search = guestListSearch.value.trim().toLowerCase();

    return [...props.guestParties]
        .filter((party) => search === ''
            || party.name.toLowerCase().includes(search)
            || (party.phone ?? '').toLowerCase().includes(search)
            || (party.tableName ?? '').toLowerCase().includes(search))
        .sort((left, right) => {
            const leftPending = left.actualAttendanceStatus === 'unknown' ? 1 : 0;
            const rightPending = right.actualAttendanceStatus === 'unknown' ? 1 : 0;

            if (leftPending !== rightPending) {
                return rightPending - leftPending;
            }

            return left.name.localeCompare(right.name);
        });
});
const retentionReminder = computed(() => {
    if (!props.currentEvent.retentionEndsAt) {
        return null;
    }

    const endsAt = new Date(props.currentEvent.retentionEndsAt);
    const diffDays = Math.ceil((endsAt.getTime() - Date.now()) / (1000 * 60 * 60 * 24));

    if (Number.isNaN(diffDays) || diffDays < 0) {
        return null;
    }

    return {
        daysLeft: diffDays,
        dateLabel: formatDateTime(props.currentEvent.retentionEndsAt),
    };
});

const localeFormat = computed(() => {
    if (locale.value === 'ro') {
        return 'ro-RO';
    }

    if (locale.value === 'el') {
        return 'el-GR';
    }

    return 'en-GB';
});

const invitationTemplateCards = invitationTemplateDefinitions;

const statCards = computed(() => [
    {
        label: t('guests.stats.parties'),
        value: props.guestPartyStats.partyCount,
        detail: t('guests.stats.parties_detail', {
            count: props.guestParties.filter((party) => party.giftType !== null || (party.notes ?? '').trim() !== '').length,
        }),
        icon: Users,
    },
    {
        label: t('guests.stats.accepted'),
        value: props.guestPartyStats.acceptedPartyCount,
        detail: t('guests.stats.accepted_detail', {
            count: props.guestPartyStats.confirmedAttendeesCount,
        }),
        icon: UserPlus,
    },
    {
        label: t('guests.stats.arrived'),
        value: props.guestPartyStats.presentPartyCount,
        detail: t('guests.stats.arrived_detail', {
            count: props.guestPartyStats.actualAttendeesCount,
        }),
        icon: CheckCircle2,
    },
    {
        label: t('guests.stats.money_total'),
        value: formatMoney(
            props.guestPartyStats.moneyGiftTotal,
            props.guestPartyStats.moneyGiftCurrency,
        ),
        detail: t('guests.stats.money_total_detail', {
            count: props.guestPartyStats.pendingPartyCount,
        }),
        icon: Wallet,
    },
]);

const familyOverview = computed(() => [
    {
        label: t('guests.metrics.invitees'),
        value: props.guestPartyStats.partyCount,
    },
    {
        label: t('guests.metrics.pending'),
        value: props.guestPartyStats.pendingPartyCount,
    },
    {
        label: t('guests.metrics.accepted'),
        value: props.guestPartyStats.acceptedPartyCount,
    },
]);

const guestSectionCards = computed(() => [
    {
        key: 'invitees' as GuestSection,
        icon: Users,
        label: t('guests.sections.invitees.title'),
        description: t('guests.sections.invitees.description'),
        value: String(props.guestPartyStats.partyCount),
        detail: t('guests.sections.invitees.detail', {
            count: props.guestPartyStats.pendingPartyCount,
        }),
    },
    {
        key: 'invitation' as GuestSection,
        icon: ScrollText,
        label: t('guests.sections.invitation.title'),
        description: t('guests.sections.invitation.description'),
        value: String(pendingInvitationParties.value.length),
        detail: t('guests.sections.invitation.detail', {
            count: props.guestParties.filter((party) => party.respondedAt !== null).length,
        }),
    },
    {
        key: 'ledger' as GuestSection,
        icon: Wallet,
        label: t('guests.sections.ledger.title'),
        description: t('guests.sections.ledger.description'),
        value: formatMoney(
            props.guestPartyStats.moneyGiftTotal,
            props.guestPartyStats.moneyGiftCurrency,
        ),
        detail: t('guests.sections.ledger.detail', {
            count: props.guestParties.filter((party) => party.giftType === null).length,
        }),
    },
    {
        key: 'guest_list' as GuestSection,
        icon: ListChecks,
        label: t('guests.sections.guest_list.title'),
        description: t('guests.sections.guest_list.description'),
        value: String(props.guestPartyStats.presentPartyCount),
        detail: t('guests.sections.guest_list.detail', {
            count: props.guestPartyStats.partyCount - props.guestPartyStats.presentPartyCount - props.guestPartyStats.absentPartyCount,
        }),
    },
]);

const activeSectionCard = computed(
    () => guestSectionCards.value.find((section) => section.key === activeSection.value) ?? guestSectionCards.value[0],
);

const ledgerGuestParties = computed(() => {
    return [...props.guestParties].sort((left, right) => {
        const leftNeedsGift = left.giftType === null ? 1 : 0;
        const rightNeedsGift = right.giftType === null ? 1 : 0;

        if (leftNeedsGift !== rightNeedsGift) {
            return rightNeedsGift - leftNeedsGift;
        }

        return left.name.localeCompare(right.name);
    });
});

const tableSeatSummary = computed(() => props.eventTables.reduce((summary, table) => ({
    totalSeats: summary.totalSeats + table.seatsCount,
    openSeats: summary.openSeats + table.remainingSeats,
}), {
    totalSeats: 0,
    openSeats: 0,
}));

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
        eventName: currentEvent.name,
        eventType: currentEvent.type,
        headline: invitationSettingsForm.headline || currentEvent.name,
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

const activeInvitationPresentation = computed(() =>
    composeInvitationPaperPresentation({
        eventName: currentEvent.name,
        eventType: currentEvent.type,
        headline: invitationSettingsForm.headline || currentEvent.name,
        content: invitationStudioContent.value,
        visibility: invitationStudioVisibility.value,
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    }),
);

const activeInvitationFooterMeta = computed(() =>
    resolveInvitationFooterMeta({
        defaultDateLabel: props.invitationPreview.eventDetails.dateLabel,
        defaultVenueAddress: props.invitationPreview.eventDetails.venueAddress,
        contactPhone: invitationSettingsForm.contact_phone || null,
        content: invitationStudioContent.value,
        visibility: invitationStudioVisibility.value,
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    }),
);

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

const invitationRecentActivity = computed(() => {
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

const openCreateDialog = (): void => {
    activeGuestParty.value = null;
    guestForm.reset();
    guestForm.clearErrors();
    guestForm.table_name = '';
    guestForm.event_table_id = '';
    guestForm.attendance_status = 'pending';
    guestForm.invitation_status = 'draft';
    guestForm.invitation_delivery_channel = 'public_link';
    guestForm.invited_attendees_count = 1;
    guestForm.gift_currency = 'EUR';
    guestForm.actual_attendance_status = 'unknown';
    showGuestAdvanced.value = false;
    guestDialogOpen.value = true;
};

const openEditDialog = (party: GuestParty): void => {
    activeGuestParty.value = party;
    guestForm.clearErrors();
    guestForm.name = party.name;
    guestForm.phone = party.phone ?? '';
    guestForm.table_name = party.tableName ?? '';
    guestForm.event_table_id = party.eventTableId?.toString() ?? '';
    guestForm.invited_attendees_count = party.invitedAttendeesCount;
    guestForm.confirmed_attendees_count = party.confirmedAttendeesCount?.toString() ?? '';
    guestForm.attendance_status = party.attendanceStatus;
    guestForm.actual_attendees_count = party.actualAttendeesCount?.toString() ?? '';
    guestForm.actual_attendance_status = party.actualAttendanceStatus;
    guestForm.notes = party.notes ?? '';
    guestForm.invitation_status = party.invitationStatus;
    guestForm.invitation_delivery_channel = party.invitationDeliveryChannel ?? '';
    guestForm.gift_type = party.giftType ?? '';
    guestForm.gift_currency = party.giftCurrency ?? 'EUR';
    guestForm.gift_amount = party.giftAmount ?? '';
    showGuestAdvanced.value = true;
    guestDialogOpen.value = true;
};

const confirmDelete = (party: GuestParty): void => {
    activeGuestParty.value = party;
    deleteDialogOpen.value = true;
};

const openGuestListInfo = (party: GuestParty): void => {
    guestListInfoParty.value = party;
    guestListTableForm.event_table_id = party.eventTableId?.toString() ?? '';
    guestListTableForm.clearErrors();
    guestListInfoDialogOpen.value = true;
};

const openCreateTableDialog = (): void => {
    editingTableId.value = null;
    tableForm.reset();
    tableForm.clearErrors();
    tableForm.seats_count = 8;
    tablesDialogOpen.value = true;
};

const openEditTableDialog = (table: EventTable): void => {
    editingTableId.value = table.id;
    tableForm.clearErrors();
    tableForm.name = table.name;
    tableForm.seats_count = table.seatsCount;
    tablesDialogOpen.value = true;
};

const saveTable = (): void => {
    if (editingTable.value) {
        tableForm.patch(editingTable.value.updateUrl, {
            preserveScroll: true,
            onSuccess: () => {
                editingTableId.value = null;
                tableForm.reset();
                tableForm.clearErrors();
                tableForm.seats_count = 8;
            },
        });

        return;
    }

    tableForm.post(props.eventLinks.tablesStore, {
        preserveScroll: true,
        onSuccess: () => {
            tableForm.reset();
            tableForm.clearErrors();
            tableForm.seats_count = 8;
        },
    });
};

const deleteTable = (table: EventTable): void => {
    if (!window.confirm(`Delete ${table.name}?`)) {
        return;
    }

    router.delete(table.deleteUrl, {
        preserveScroll: true,
    });
};

const saveGuestParty = (): void => {
    const payload = {
        ...guestForm.data(),
        event_table_id: guestForm.event_table_id === '' ? null : Number(guestForm.event_table_id),
        confirmed_attendees_count: guestForm.confirmed_attendees_count === ''
            ? null
            : Number(guestForm.confirmed_attendees_count),
        actual_attendees_count: guestForm.actual_attendees_count === ''
            ? null
            : Number(guestForm.actual_attendees_count),
        invitation_delivery_channel: guestForm.invitation_delivery_channel || null,
        actual_attendance_status: guestForm.actual_attendance_status,
        gift_type: guestForm.gift_type || null,
        gift_currency: guestForm.gift_type === 'money' ? guestForm.gift_currency : null,
        gift_amount: guestForm.gift_type === 'money' ? guestForm.gift_amount : null,
    };

    if (activeGuestParty.value) {
        guestForm.transform(() => payload).patch(activeGuestParty.value.updateUrl, {
            preserveScroll: true,
            onSuccess: () => {
                guestDialogOpen.value = false;
                activeGuestParty.value = null;
            },
        });

        return;
    }

    guestForm.transform(() => payload).post(props.eventLinks.guestPartiesStore, {
        preserveScroll: true,
        onSuccess: () => {
            guestDialogOpen.value = false;
            guestForm.reset();
            guestForm.table_name = '';
            guestForm.event_table_id = '';
            guestForm.actual_attendance_status = 'unknown';
            guestForm.invitation_status = 'draft';
            guestForm.invitation_delivery_channel = 'public_link';
            guestForm.invited_attendees_count = 1;
            showGuestAdvanced.value = false;
        },
    });
};

const deleteGuestParty = (): void => {
    if (!activeGuestParty.value) {
        return;
    }

    guestForm.delete(activeGuestParty.value.deleteUrl, {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialogOpen.value = false;
            activeGuestParty.value = null;
        },
    });
};

const importGuestParties = (): void => {
    importForm.post(props.eventLinks.guestPartiesImport, {
        preserveScroll: true,
        forceFormData: importForm.import_file !== null,
        onSuccess: () => {
            importDialogOpen.value = false;
            importForm.reset();
        },
    });
};

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

const exportGuestLedger = (): void => {
    window.location.assign(props.guestLedgerExportUrl);
};

const openGuestReport = (): void => {
    window.open(props.eventLinks.guestReport, '_blank', 'noopener,noreferrer');
};

const guestPartyUpdatePayload = (
    party: GuestParty,
    overrides: Partial<{
        event_table_id: number | null;
        invited_attendees_count: number;
        confirmed_attendees_count: number | null;
        attendance_status: GuestParty['attendanceStatus'];
        actual_attendees_count: number | null;
        actual_attendance_status: GuestParty['actualAttendanceStatus'];
        notes: string | null;
        invitation_status: GuestParty['invitationStatus'];
        invitation_delivery_channel: GuestParty['invitationDeliveryChannel'];
        gift_type: '' | 'gift' | 'money' | null;
        gift_currency: GuestParty['giftCurrency'];
        gift_amount: string | null;
    }> = {},
): Record<string, string | number | null> => {
    const giftType = overrides.gift_type ?? party.giftType ?? '';

    return {
        name: party.name,
        phone: party.phone ?? '',
        table_name: party.tableName ?? '',
        event_table_id: overrides.event_table_id ?? party.eventTableId,
        invited_attendees_count: overrides.invited_attendees_count ?? party.invitedAttendeesCount,
        confirmed_attendees_count: overrides.confirmed_attendees_count ?? party.confirmedAttendeesCount,
        attendance_status: overrides.attendance_status ?? party.attendanceStatus,
        actual_attendees_count: overrides.actual_attendees_count ?? party.actualAttendeesCount,
        actual_attendance_status: overrides.actual_attendance_status ?? party.actualAttendanceStatus,
        notes: overrides.notes ?? party.notes ?? '',
        invitation_status: overrides.invitation_status ?? party.invitationStatus,
        invitation_delivery_channel: overrides.invitation_delivery_channel ?? party.invitationDeliveryChannel ?? '',
        gift_type: giftType,
        gift_currency: giftType === 'money'
            ? (overrides.gift_currency ?? party.giftCurrency ?? 'EUR')
            : null,
        gift_amount: giftType === 'money'
            ? (overrides.gift_amount ?? party.giftAmount ?? '')
            : null,
    };
};

const patchGuestPartyQuickly = (
    party: GuestParty,
    overrides: Parameters<typeof guestPartyUpdatePayload>[1],
    successMessage: string,
): void => {
    quickSavingGuestId.value = party.id;

    router.patch(party.updateUrl, guestPartyUpdatePayload(party, overrides), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success(successMessage);
        },
        onFinish: () => {
            quickSavingGuestId.value = null;
        },
    });
};

const closeInvitationTemplatePreview = (): void => {
    previewingInvitationTemplateId.value = null;
};

const handleInvitationTemplatePreviewOpenChange = (open: boolean): void => {
    if (!open) {
        closeInvitationTemplatePreview();
    }
};

const toggleGuestSelection = (guestPartyId: number, checked: boolean): void => {
    if (checked) {
        selectedGuestIds.value = Array.from(new Set([...selectedGuestIds.value, guestPartyId]));

        return;
    }

    selectedGuestIds.value = selectedGuestIds.value.filter((id) => id !== guestPartyId);
};

const toggleSelectAllGuests = (checked: boolean): void => {
    const visibleIds = filteredGuestParties.value.map((party) => party.id);

    if (checked) {
        selectedGuestIds.value = Array.from(new Set([...selectedGuestIds.value, ...visibleIds]));

        return;
    }

    selectedGuestIds.value = selectedGuestIds.value.filter((id) => !visibleIds.includes(id));
};

const clearGuestSelection = (): void => {
    selectedGuestIds.value = [];
};

const toggleGuestDetails = (guestPartyId: number): void => {
    expandedGuestPartyId.value = expandedGuestPartyId.value === guestPartyId
        ? null
        : guestPartyId;
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
        onSuccess: () => {
            if (guestPartyIds.length === selectedGuestIds.value.length) {
                clearGuestSelection();
            }
        },
    });
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

const shareGuestInvite = async (party: GuestParty): Promise<void> => {
    await shareGuestPartyBundle([party], 'invite');
};

const remindGuestInvite = async (party: GuestParty): Promise<void> => {
    await shareGuestPartyBundle([party], 'reminder');
};

const shareSelectedInvites = async (): Promise<void> => {
    await shareGuestPartyBundle(selectedGuestParties.value, 'invite');
};

const sharePendingInvites = async (): Promise<void> => {
    await shareGuestPartyBundle(pendingInvitationParties.value, 'invite');
};

const remindSelectedInvites = async (): Promise<void> => {
    const parties = selectedGuestIds.value.length > 0
        ? selectedPendingGuestParties.value
        : filteredGuestParties.value.filter((party) => party.attendanceStatus === 'pending');

    await shareGuestPartyBundle(parties, 'reminder');
};

const remindPendingInvites = async (): Promise<void> => {
    await shareGuestPartyBundle(pendingInvitationParties.value, 'reminder');
};

const markGuestPartyPresent = (party: GuestParty): void => {
    patchGuestPartyQuickly(
        party,
        {
            actual_attendance_status: 'present',
            actual_attendees_count: party.confirmedAttendeesCount ?? party.invitedAttendeesCount,
        },
        t('guests.messages.marked_present', { name: party.name }),
    );
};

const markGuestPartyAbsent = (party: GuestParty): void => {
    patchGuestPartyQuickly(
        party,
        {
            actual_attendance_status: 'absent',
            actual_attendees_count: 0,
        },
        t('guests.messages.marked_absent', { name: party.name }),
    );
};

const resetGuestPartyAttendance = (party: GuestParty): void => {
    patchGuestPartyQuickly(
        party,
        {
            actual_attendance_status: 'unknown',
            actual_attendees_count: null,
        },
        t('guests.messages.marked_not_recorded', { name: party.name }),
    );
};

const ledgerGiftLabel = (party: GuestParty): string => {
    if (party.giftType === 'money' && party.giftAmount !== null && party.giftCurrency !== null && party.notes) {
        return `${formatMoney(Number(party.giftAmount), party.giftCurrency)} + note`;
    }

    if (party.giftType === 'money' && party.giftAmount !== null && party.giftCurrency !== null) {
        return formatMoney(Number(party.giftAmount), party.giftCurrency);
    }

    if (party.giftType === 'gift' && party.notes) {
        return t('guests.ledger.gift_with_note');
    }

    if (party.giftType === 'gift') {
        return t('guests.shared.gift_recorded');
    }

    if (party.notes) {
        return t('guests.ledger.note_recorded');
    }

    return t('guests.shared.no_gift_yet');
};

const openLedgerEntry = (party: GuestParty, mode: 'money' | 'gift' | 'both'): void => {
    activeLedgerParty.value = party;
    ledgerGiftMode.value = mode;
    ledgerEntryForm.clearErrors();
    ledgerEntryForm.gift_currency = party.giftCurrency ?? 'EUR';
    ledgerEntryForm.gift_amount = party.giftAmount ?? '';
    ledgerEntryForm.note = party.notes ?? '';
    ledgerEntryDialogOpen.value = true;
};

const saveLedgerEntry = (): void => {
    if (!activeLedgerParty.value) {
        return;
    }

    const party = activeLedgerParty.value;
    const nextGiftType = ledgerGiftMode.value === 'gift' ? 'gift' : 'money';

    patchGuestPartyQuickly(
        party,
        {
            gift_type: nextGiftType,
            gift_currency: ledgerGiftMode.value === 'gift' ? null : ledgerEntryForm.gift_currency,
            gift_amount: ledgerGiftMode.value === 'gift' ? null : ledgerEntryForm.gift_amount,
            notes: ledgerGiftMode.value === 'money' ? (party.notes ?? '') : ledgerEntryForm.note,
        },
        t('guests.messages.ledger_updated', { name: party.name }),
    );

    ledgerEntryDialogOpen.value = false;
    activeLedgerParty.value = null;
};

const saveGuestListTableAssignment = (): void => {
    if (!guestListInfoParty.value) {
        return;
    }

    guestListTableForm.clearErrors();
    quickSavingGuestId.value = guestListInfoParty.value.id;

    router.patch(guestListInfoParty.value.updateUrl, guestPartyUpdatePayload(guestListInfoParty.value, {
        event_table_id: guestListTableForm.event_table_id === '' ? null : Number(guestListTableForm.event_table_id),
    }), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.success(
                t('guests.messages.table_updated', {
                    name: guestListInfoParty.value?.name ?? t('guests.dialogs.guest.default_title'),
                }),
            );
        },
        onError: (errors) => {
            if (errors.event_table_id) {
                guestListTableForm.setError('event_table_id', errors.event_table_id);
            }
        },
        onFinish: () => {
            quickSavingGuestId.value = null;
        },
    });
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

const copySelectedInvites = async (): Promise<void> => {
    if (selectedGuestParties.value.length === 0) {
        toast.error(t('guests.messages.select_invitee_first'));

        return;
    }

    try {
        await navigator.clipboard.writeText(invitationMessageForParties(selectedGuestParties.value));
        toast.success(t('guests.messages.selected_bundle_copied'));
    } catch {
        toast.error(t('guests.messages.selected_bundle_copy_failed'));
    }
};

const deliveryChannelLabel = (channel: string | null): string => {
    const labels: Record<NonNullable<GuestParty['invitationDeliveryChannel']>, string> = {
        in_person: t('guests.delivery_channels.in_person'),
        phone: t('guests.delivery_channels.phone'),
        whatsapp: t('guests.delivery_channels.whatsapp'),
        facebook: t('guests.delivery_channels.facebook'),
        public_link: t('guests.delivery_channels.public_link'),
        other: t('guests.delivery_channels.other'),
    };

    if (!channel) {
        return t('guests.shared.not_set');
    }

    return labels[channel as keyof typeof labels] ?? t('guests.shared.not_set');
};

const onImportFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    importForm.import_file = input.files?.[0] ?? null;
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

const formatMoney = (value: number, currency: string): string => {
    return new Intl.NumberFormat(localeFormat.value, {
        style: 'currency',
        currency,
        maximumFractionDigits: 2,
    }).format(value);
};

const attendanceBadgeClass = (status: GuestParty['attendanceStatus']): string => {
    if (status === 'accepted') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    if (status === 'declined') {
        return 'border-rose-200 bg-rose-50 text-rose-700';
    }

    return 'border-amber-200 bg-amber-50 text-amber-700';
};

const attendanceLabel = (status: GuestParty['attendanceStatus']): string => {
    if (status === 'accepted') {
        return t('guests.status.accepted');
    }

    if (status === 'declined') {
        return t('guests.status.declined');
    }

    return t('guests.status.waiting');
};

const actualAttendanceLabel = (status: GuestParty['actualAttendanceStatus']): string => {
    return {
        unknown: t('guests.status.not_recorded'),
        present: t('guests.status.present'),
        absent: t('guests.status.absent'),
    }[status];
};

const invitationLabel = (status: GuestParty['invitationStatus']): string => {
    return {
        draft: t('guests.invitation_status.draft'),
        delivered_in_person: t('guests.invitation_status.delivered'),
        sent: t('guests.invitation_status.sent'),
        opened: t('guests.invitation_status.opened'),
        responded: t('guests.invitation_status.responded'),
    }[status];
};

const giftLabel = (party: GuestParty): string => {
    if (party.giftType === 'money' && party.giftAmount !== null && party.giftCurrency !== null) {
        return formatMoney(Number(party.giftAmount), party.giftCurrency);
    }

    if (party.giftType === 'gift') {
        return t('guests.shared.gift_recorded');
    }

    return t('guests.shared.no_gift_yet');
};

const mealPreferenceLabel = (value: GuestParty['mealPreference']): string | null => {
    if (!value) {
        return null;
    }

    return {
        standard: t('guests.meals.standard'),
        vegetarian: t('guests.meals.vegetarian'),
        vegan: t('guests.meals.vegan'),
        halal: t('guests.meals.halal'),
        other: t('guests.meals.other'),
    }[value];
};

const invitationHistoryLabel = (party: GuestParty['invitationHistory'][number]): string => {
    const attendanceStatus = typeof party.meta.attendanceStatus === 'string' ? party.meta.attendanceStatus : null;

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
    }[party.type];
};
</script>

<template>
    <Head :title="t('guests.page.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-5 p-4 md:p-6">
            <section class="space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight text-neutral-950">
                                {{ t('guests.page.title') }}
                            </h1>
                            <span v-if="retentionReminder" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                {{ t('guests.page.retention', { count: retentionReminder.daysLeft }) }}
                            </span>
                        </div>
                        <p class="max-w-2xl text-sm text-neutral-600">
                            {{ t('guests.page.description') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button v-if="activeSection === 'invitees'" variant="outline" class="h-10 rounded-full px-4" @click="importDialogOpen = true">
                            <Import class="mr-2 size-4" />
                            {{ t('guests.actions.import') }}
                        </Button>
                        <Button v-if="activeSection === 'invitees'" variant="outline" class="h-10 rounded-full px-4" @click="openCreateTableDialog">
                            <Table2 class="mr-2 size-4" />
                            {{ t('guests.actions.tables') }}
                        </Button>
                        <Button v-if="activeSection === 'invitees'" data-test="guest-open-create-dialog" class="h-10 rounded-full px-4" @click="openCreateDialog">
                            <UserPlus class="mr-2 size-4" />
                            {{ t('guests.actions.add_invitee') }}
                        </Button>
                        <Button v-if="activeSection === 'ledger'" variant="outline" class="h-10 rounded-full px-4" @click="exportGuestLedger">
                            <Download class="mr-2 size-4" />
                            {{ t('guests.actions.export') }}
                        </Button>
                        <Button v-if="activeSection === 'ledger'" class="h-10 rounded-full px-4" @click="openGuestReport">
                            <Printer class="mr-2 size-4" />
                            {{ t('guests.actions.ledger_page') }}
                        </Button>
                        <Button v-if="activeSection === 'guest_list'" variant="outline" class="h-10 rounded-full px-4" @click="copyLink(publicGuestListUrl, t('guests.sections.guest_list.link_label'))">
                            <Copy class="mr-2 size-4" />
                            {{ t('guests.actions.copy_guest_list') }}
                        </Button>
                        <Button v-if="activeSection === 'guest_list'" class="h-10 rounded-full px-4" @click="openInvite(publicGuestListUrl)">
                            <ExternalLink class="mr-2 size-4" />
                            {{ t('guests.actions.open_guest_list') }}
                        </Button>
                    </div>
                </div>

                <GuestSectionSwitcher
                    v-model="activeSection"
                    :sections="guestSectionCards"
                />

                <div class="rounded-3xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-700">
                    <span class="font-semibold text-neutral-950">{{ activeSectionCard.label }}:</span>
                    {{ activeSectionCard.description }}
                </div>
            </section>

            <GuestInviteesPanel
                v-if="activeSection === 'invitees'"
                :overview="familyOverview"
                :guest-parties-count="guestParties.length"
                :filtered-guest-parties="filteredGuestParties"
                :selected-guest-ids="selectedGuestIds"
                :selected-pending-count="selectedPendingGuestParties.length"
                :all-guests-selected="allGuestsSelected"
                :expanded-guest-party-id="expandedGuestPartyId"
                :guest-search="guestSearch"
                :guest-filter="guestFilter"
                :invitation-bulk-processing="invitationBulkForm.processing"
                :attendance-badge-class="attendanceBadgeClass"
                :attendance-label="attendanceLabel"
                :invitation-label="invitationLabel"
                :gift-label="giftLabel"
                :actual-attendance-label="actualAttendanceLabel"
                :format-date-time="formatDateTime"
                :meal-preference-label="mealPreferenceLabel"
                :invitation-history-label="invitationHistoryLabel"
                :delivery-channel-label="deliveryChannelLabel"
                @update:guest-search="guestSearch = $event"
                @update:guest-filter="guestFilter = $event"
                @toggle-select-all="toggleSelectAllGuests"
                @toggle-select-guest="(guestPartyId, checked) => toggleGuestSelection(guestPartyId, checked)"
                @open-create="openCreateDialog"
                @open-import="importDialogOpen = true"
                @share-selected="shareSelectedInvites"
                @copy-selected="copySelectedInvites"
                @remind-selected="remindSelectedInvites"
                @mark-selected-delivered="updateInvitationDelivery(selectedGuestIds, 'mark_delivered_in_person', 'in_person')"
                @mark-selected-sent="updateInvitationDelivery(selectedGuestIds, 'mark_sent_online', 'other')"
                @clear-selection="clearGuestSelection"
                @share-guest="shareGuestInvite"
                @remind-guest="remindGuestInvite"
                @edit-guest="openEditDialog"
                @toggle-details="toggleGuestDetails"
                @copy-link="(url, label) => copyLink(url, label)"
                @open-invite="openInvite"
                @mark-delivered="updateInvitationDelivery([$event.id], 'mark_delivered_in_person', 'in_person')"
                @mark-sent="updateInvitationDelivery([$event.id], 'mark_sent_online', 'other')"
                @confirm-delete="confirmDelete"
            />

            <GuestInvitationPanel
                v-else-if="activeSection === 'invitation'"
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
                :logo-url="props.invitationPreview.branding.logoUrl"
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

            <GuestLedgerPanel
                v-else-if="activeSection === 'ledger'"
                :retention-reminder="retentionReminder"
                :stat-cards="statCards"
                :parties="ledgerGuestParties"
                :attendance-badge-class="attendanceBadgeClass"
                :attendance-label="attendanceLabel"
                :actual-attendance-label="actualAttendanceLabel"
                :ledger-gift-label="ledgerGiftLabel"
                @open-report="openGuestReport"
                @export-csv="exportGuestLedger"
                @record-money="openLedgerEntry($event, 'money')"
                @record-gift="openLedgerEntry($event, 'gift')"
                @record-both="openLedgerEntry($event, 'both')"
            />

            <section v-else class="space-y-4">
                <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
                    <div class="grid gap-px bg-neutral-200 sm:grid-cols-3">
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.metrics.invitees') }}
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.partyCount }}
                            </p>
                        </div>
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.metrics.marked_present') }}
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.presentPartyCount }}
                            </p>
                        </div>
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.metrics.not_recorded') }}
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.partyCount - props.guestPartyStats.presentPartyCount - props.guestPartyStats.absentPartyCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <GuestCheckInPanel
                    :parties="guestListParties"
                    :search="guestListSearch"
                    :public-guest-list-url="publicGuestListUrl"
                    :event-tables-count="props.eventTables.length"
                    :quick-saving-guest-id="quickSavingGuestId"
                    @update:search="guestListSearch = $event"
                    @open-details="openGuestListInfo"
                    @open-create-tables="openCreateTableDialog"
                    @mark-present="markGuestPartyPresent"
                    @mark-absent="markGuestPartyAbsent"
                    @reset="resetGuestPartyAttendance"
                    @copy-link="copyLink($event, t('guests.sections.guest_list.link_label'))"
                    @open-public-page="openInvite"
                />
            </section>
        </div>

        <Dialog :open="guestDialogOpen" @update:open="guestDialogOpen = $event">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? t('guests.dialogs.guest.edit_title') : t('guests.dialogs.guest.add_title') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ t('guests.dialogs.guest.description') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="max-h-[72vh] space-y-5 overflow-y-auto py-2 pr-1">
                    <section class="space-y-4 rounded-3xl border border-neutral-200 bg-neutral-50 px-4 py-4">
                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.guest.quick_title') }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('guests.dialogs.guest.quick_description') }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.fields.family_name') }}
                                </label>
                                <Input v-model="guestForm.name" :placeholder="t('guests.dialogs.guest.name_placeholder')" />
                                <p v-if="guestForm.errors.name" class="text-sm text-rose-600">
                                    {{ guestForm.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.fields.phone') }}
                                </label>
                                <Input v-model="guestForm.phone" placeholder="07..." />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.fields.invited_attendees') }}
                                </label>
                                <Input v-model="guestForm.invited_attendees_count" type="number" min="1" max="1000" />
                                <p class="text-xs text-neutral-500">
                                    {{ t('guests.dialogs.guest.invited_help') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <div class="flex items-center justify-between gap-3 border-y border-neutral-200 py-3">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.dialogs.guest.advanced_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.dialogs.guest.advanced_description') }}
                            </p>
                        </div>
                        <Button variant="outline" class="rounded-full px-4" @click="showGuestAdvanced = !showGuestAdvanced">
                            {{ showGuestAdvanced ? t('guests.actions.hide_advanced') : t('guests.dialogs.guest.show_advanced') }}
                        </Button>
                    </div>

                    <div v-if="showGuestAdvanced" class="space-y-4">
                        <section class="rounded-3xl border border-neutral-200 px-4 py-4">
                            <div class="mb-4 space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                    {{ t('guests.dialogs.guest.sections.logistics') }}
                                </p>
                                <p class="text-sm text-neutral-600">
                                    {{ t('guests.dialogs.guest.sections.logistics_description') }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.table') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.event_table_id">
                                        <NativeSelectOption value="">{{ t('guests.shared.no_table_yet') }}</NativeSelectOption>
                                        <NativeSelectOption
                                            v-for="table in selectableTables"
                                            :key="table.id"
                                            :value="String(table.id)"
                                            :disabled="!table.selectable"
                                        >
                                            {{ table.name }} · {{ t('guests.dialogs.tables.seats_left', { count: table.remainingSeats }) }}
                                        </NativeSelectOption>
                                    </NativeSelect>
                                    <p class="text-xs text-neutral-500">
                                        {{ t('guests.dialogs.guest.full_tables_locked') }}
                                    </p>
                                </div>

                                <div class="space-y-2 md:col-span-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.notes') }}
                                    </label>
                                    <Textarea
                                        v-model="guestForm.notes"
                                        rows="4"
                                        :placeholder="t('guests.dialogs.guest.notes_placeholder')"
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-neutral-200 px-4 py-4">
                            <div class="mb-4 space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                    {{ t('guests.dialogs.guest.sections.attendance') }}
                                </p>
                                <p class="text-sm text-neutral-600">
                                    {{ t('guests.dialogs.guest.sections.attendance_description') }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.rsvp_status') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.attendance_status">
                                        <NativeSelectOption value="pending">{{ t('guests.status.waiting') }}</NativeSelectOption>
                                        <NativeSelectOption value="accepted">{{ t('guests.status.accepted') }}</NativeSelectOption>
                                        <NativeSelectOption value="declined">{{ t('guests.status.declined') }}</NativeSelectOption>
                                    </NativeSelect>
                                </div>

                                <div v-if="showConfirmedCount" class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.confirmed_attendees') }}
                                    </label>
                                    <Input v-model="guestForm.confirmed_attendees_count" type="number" min="0" max="1000" />
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.event_day_status') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.actual_attendance_status">
                                        <NativeSelectOption value="unknown">{{ t('guests.dialogs.guest.not_recorded_yet') }}</NativeSelectOption>
                                        <NativeSelectOption value="present">{{ t('guests.status.present') }}</NativeSelectOption>
                                        <NativeSelectOption value="absent">{{ t('guests.status.absent') }}</NativeSelectOption>
                                    </NativeSelect>
                                </div>

                                <div v-if="showActualCount" class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.attended_count') }}
                                    </label>
                                    <Input v-model="guestForm.actual_attendees_count" type="number" min="0" max="1000" />
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-neutral-200 px-4 py-4">
                            <div class="mb-4 space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                    {{ t('guests.dialogs.guest.sections.invitation') }}
                                </p>
                                <p class="text-sm text-neutral-600">
                                    {{ t('guests.dialogs.guest.sections.invitation_description') }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.invitation_status') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.invitation_status">
                                        <NativeSelectOption value="draft">{{ t('guests.invitation_status.draft') }}</NativeSelectOption>
                                        <NativeSelectOption value="delivered_in_person">{{ t('guests.invitation_status.delivered') }}</NativeSelectOption>
                                        <NativeSelectOption value="sent">{{ t('guests.invitation_status.sent') }}</NativeSelectOption>
                                        <NativeSelectOption value="opened">{{ t('guests.invitation_status.opened') }}</NativeSelectOption>
                                        <NativeSelectOption value="responded">{{ t('guests.invitation_status.responded') }}</NativeSelectOption>
                                    </NativeSelect>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.delivery_channel') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.invitation_delivery_channel">
                                        <NativeSelectOption value="">{{ t('guests.shared.not_set') }}</NativeSelectOption>
                                        <NativeSelectOption value="in_person">{{ t('guests.delivery_channels.in_person') }}</NativeSelectOption>
                                        <NativeSelectOption value="phone">{{ t('guests.delivery_channels.phone') }}</NativeSelectOption>
                                        <NativeSelectOption value="whatsapp">{{ t('guests.delivery_channels.whatsapp') }}</NativeSelectOption>
                                        <NativeSelectOption value="facebook">{{ t('guests.delivery_channels.facebook') }}</NativeSelectOption>
                                        <NativeSelectOption value="public_link">{{ t('guests.delivery_channels.public_link') }}</NativeSelectOption>
                                        <NativeSelectOption value="other">{{ t('guests.delivery_channels.other') }}</NativeSelectOption>
                                    </NativeSelect>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-3xl border border-neutral-200 px-4 py-4">
                            <div class="mb-4 space-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                    {{ t('guests.dialogs.guest.sections.gift') }}
                                </p>
                                <p class="text-sm text-neutral-600">
                                    {{ t('guests.dialogs.guest.sections.gift_description') }}
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.fields.gift_type') }}
                                    </label>
                                    <NativeSelect v-model="guestForm.gift_type">
                                        <NativeSelectOption value="">{{ t('guests.shared.not_set') }}</NativeSelectOption>
                                        <NativeSelectOption value="money">{{ t('guests.ledger.modes.money') }}</NativeSelectOption>
                                        <NativeSelectOption value="gift">{{ t('guests.ledger.modes.gift') }}</NativeSelectOption>
                                    </NativeSelect>
                                </div>

                                <template v-if="showGiftAmount">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-neutral-700">
                                            {{ t('guests.fields.currency') }}
                                        </label>
                                        <NativeSelect v-model="guestForm.gift_currency">
                                            <NativeSelectOption value="EUR">EUR</NativeSelectOption>
                                            <NativeSelectOption value="GBP">GBP</NativeSelectOption>
                                            <NativeSelectOption value="RON">RON</NativeSelectOption>
                                        </NativeSelect>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-neutral-700">
                                            {{ t('guests.fields.amount') }}
                                        </label>
                                        <Input v-model="guestForm.gift_amount" type="number" min="0" step="0.01" />
                                    </div>
                                </template>
                            </div>
                        </section>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button data-test="guest-dialog-cancel" variant="outline" class="rounded-full px-5" @click="guestDialogOpen = false">
                        {{ t('guests.actions.cancel') }}
                    </Button>
                    <Button data-test="guest-dialog-save" class="rounded-full px-5" :disabled="guestForm.processing" @click="saveGuestParty">
                        {{ isEditing ? t('guests.actions.save_changes') : t('guests.actions.add_invitee') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="importDialogOpen" @update:open="importDialogOpen = $event">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{{ t('guests.dialogs.import.title') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('guests.dialogs.import.description') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-2 lg:grid-cols-[minmax(0,1fr)_260px]">
                    <div class="space-y-4 rounded-3xl border border-neutral-200 px-4 py-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.dialogs.import.paste_text') }}
                            </label>
                            <Textarea
                                v-model="importForm.import_text"
                                rows="10"
                                :placeholder="t('guests.dialogs.import.placeholder')"
                            />
                            <p v-if="importForm.errors.import_text" class="text-sm text-rose-600">
                                {{ importForm.errors.import_text }}
                            </p>
                        </div>

                        <div class="space-y-2 rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-4">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.dialogs.import.upload_file') }}
                            </label>
                            <Input type="file" accept=".csv,.txt" @change="onImportFileChange" />
                            <p class="text-sm text-neutral-500">
                                {{ t('guests.dialogs.import.upload_help') }}
                            </p>
                            <p v-if="importForm.errors.import_file" class="text-sm text-rose-600">
                                {{ importForm.errors.import_file }}
                            </p>
                        </div>
                    </div>

                    <aside class="space-y-4 rounded-3xl border border-neutral-200 bg-neutral-50 px-4 py-4">
                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.import.examples_title') }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('guests.dialogs.import.examples_description') }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white px-4 py-3 text-sm leading-6 text-neutral-700">
                            <pre class="whitespace-pre-wrap font-sans">{{ t('guests.dialogs.import.placeholder') }}</pre>
                        </div>

                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.import.supported_title') }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('guests.dialogs.import.supported_description') }}
                            </p>
                        </div>
                    </aside>
                </div>

                <DialogFooter class="gap-2">
                    <Button data-test="guest-import-cancel" variant="outline" class="rounded-full px-5" @click="importDialogOpen = false">
                        {{ t('guests.actions.cancel') }}
                    </Button>
                    <Button class="rounded-full px-5" :disabled="importForm.processing" @click="importGuestParties">
                        {{ t('guests.dialogs.import.submit') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="tablesDialogOpen" @update:open="tablesDialogOpen = $event">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingTable ? t('guests.dialogs.tables.edit_title') : t('guests.dialogs.tables.manage_title') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ t('guests.dialogs.tables.description') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.tables.summary_tables') }}
                            </p>
                            <p class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.eventTables.length }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.tables.summary_seats') }}
                            </p>
                            <p class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ tableSeatSummary.totalSeats }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.tables.summary_open') }}
                            </p>
                            <p class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ tableSeatSummary.openSeats }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 rounded-3xl border border-neutral-200 px-4 py-4 md:grid-cols-[minmax(0,1fr)_160px_auto]">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.dialogs.tables.name_label') }}
                            </label>
                            <Input v-model="tableForm.name" :placeholder="t('guests.dialogs.tables.name_placeholder')" />
                            <p v-if="tableForm.errors.name" class="text-sm text-rose-600">
                                {{ tableForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.dialogs.tables.seats_label') }}
                            </label>
                            <Input v-model="tableForm.seats_count" type="number" min="1" max="1000" />
                            <p v-if="tableForm.errors.seats_count" class="text-sm text-rose-600">
                                {{ tableForm.errors.seats_count }}
                            </p>
                        </div>

                        <div class="flex items-end">
                            <Button class="w-full rounded-full px-5" :disabled="tableForm.processing" @click="saveTable">
                                {{ editingTable ? t('guests.dialogs.tables.save') : t('guests.dialogs.tables.add') }}
                            </Button>
                        </div>
                    </div>

                    <div class="divide-y divide-neutral-200 border-t border-neutral-200">
                        <div
                            v-for="table in props.eventTables"
                            :key="table.id"
                            class="flex flex-col gap-3 py-3 md:flex-row md:items-center md:justify-between"
                        >
                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-neutral-950">
                                    {{ table.name }}
                                </p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-neutral-600">
                                    <span>{{ t('guests.dialogs.tables.seats_count', { count: table.seatsCount }) }}</span>
                                    <span>{{ t('guests.dialogs.tables.used_count', { count: table.occupiedSeats }) }}</span>
                                    <span>{{ t('guests.dialogs.tables.left_count', { count: table.remainingSeats }) }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button variant="outline" class="rounded-full px-4" @click="openEditTableDialog(table)">
                                    <Pencil class="mr-2 size-4" />
                                    {{ t('guests.actions.edit') }}
                                </Button>
                                <Button variant="ghost" class="rounded-full px-4 text-rose-600 hover:text-rose-700" @click="deleteTable(table)">
                                    <Trash2 class="mr-2 size-4" />
                                    {{ t('guests.actions.delete') }}
                                </Button>
                            </div>
                        </div>

                        <div v-if="props.eventTables.length === 0" class="py-8 text-sm text-neutral-500">
                            {{ t('guests.dialogs.tables.empty') }}
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

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
                        :logo-url="props.invitationPreview.branding.logoUrl"
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

        <Dialog :open="guestListInfoDialogOpen" @update:open="guestListInfoDialogOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{ guestListInfoParty?.name ?? t('guests.dialogs.check_in_info.default_title') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ t('guests.dialogs.check_in_info.description') }}
                    </DialogDescription>
                </DialogHeader>

                <div v-if="guestListInfoParty" class="space-y-4 py-2">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.phone') }}</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.phone || t('guests.shared.no_phone') }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.rsvp_status') }}</p>
                            <p class="text-sm text-neutral-800">{{ attendanceLabel(guestListInfoParty.attendanceStatus) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.invited_attendees') }}</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.invitedAttendeesCount }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.confirmed_attendees') }}</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.confirmedAttendeesCount ?? t('guests.dialogs.check_in_info.not_answered_yet') }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.details.event_day') }}</p>
                            <p class="text-sm text-neutral-800">{{ actualAttendanceLabel(guestListInfoParty.actualAttendanceStatus) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.gift_type') }}</p>
                            <p class="text-sm text-neutral-800">{{ giftLabel(guestListInfoParty) }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 rounded-3xl border border-neutral-200 px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.table') }}</p>
                                <p class="mt-1 text-sm text-neutral-800">
                                    {{ guestListInfoParty.tableName || t('guests.dialogs.check_in_info.no_table_assigned') }}
                                </p>
                            </div>
                            <Button
                                v-if="props.eventTables.length === 0"
                                variant="outline"
                                class="rounded-full px-4"
                                @click="guestListInfoDialogOpen = false; openCreateTableDialog();"
                            >
                                <Table2 class="mr-2 size-4" />
                                {{ t('guests.dialogs.check_in_info.add_tables') }}
                            </Button>
                        </div>

                        <div v-if="props.eventTables.length > 0" class="space-y-2">
                            <div class="grid gap-0 sm:grid-cols-[minmax(0,1fr)_auto]">
                                <NativeSelect
                                    v-model="guestListTableForm.event_table_id"
                                    class="h-11 rounded-b-none rounded-t-2xl border-b-0 sm:rounded-l-2xl sm:rounded-r-none sm:rounded-t-none sm:border-b sm:border-r-0"
                                >
                                    <NativeSelectOption value="">{{ t('guests.shared.no_table_yet') }}</NativeSelectOption>
                                    <NativeSelectOption
                                        v-for="table in guestListSelectableTables"
                                        :key="table.id"
                                        :value="String(table.id)"
                                        :disabled="!table.selectable"
                                    >
                                        {{ table.name }} · {{ t('guests.dialogs.tables.seats_left', { count: table.remainingSeats }) }}
                                    </NativeSelectOption>
                                </NativeSelect>
                                <Button
                                    class="h-11 rounded-t-none rounded-b-2xl px-4 sm:rounded-l-none sm:rounded-r-2xl sm:rounded-t-none"
                                    :disabled="quickSavingGuestId === guestListInfoParty.id"
                                    @click="saveGuestListTableAssignment"
                                >
                                    {{ t('guests.dialogs.check_in_info.save_table') }}
                                </Button>
                            </div>
                            <p class="text-xs text-neutral-500">
                                {{ t('guests.dialogs.check_in_info.full_tables_locked') }}
                            </p>
                            <p v-if="guestListTableForm.errors.event_table_id" class="text-sm text-rose-600">
                                {{ guestListTableForm.errors.event_table_id }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1 rounded-3xl border border-neutral-200 px-4 py-4">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">{{ t('guests.fields.notes') }}</p>
                        <p class="text-sm text-neutral-800">{{ guestListInfoParty.notes || t('guests.dialogs.check_in_info.no_notes') }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="ledgerEntryDialogOpen" @update:open="ledgerEntryDialogOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            ledgerGiftMode === 'money'
                                ? t('guests.dialogs.ledger.record_money')
                                : ledgerGiftMode === 'gift'
                                    ? t('guests.dialogs.ledger.record_gift')
                                    : t('guests.dialogs.ledger.record_both')
                        }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ activeLedgerParty?.name ?? t('guests.dialogs.guest.default_title') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="space-y-3 rounded-3xl border border-neutral-200 bg-neutral-50 px-4 py-4">
                        <div class="space-y-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                {{ t('guests.dialogs.ledger.mode_title') }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('guests.dialogs.ledger.mode_description') }}
                            </p>
                        </div>

                        <div class="inline-flex items-center overflow-hidden rounded-full border border-neutral-200 bg-white">
                        <button
                            type="button"
                            :class="[
                                'px-4 py-2 text-sm font-medium transition',
                                ledgerGiftMode === 'money' ? 'bg-neutral-950 text-white' : 'text-neutral-700 hover:bg-neutral-50 hover:text-neutral-950',
                            ]"
                            @click="ledgerGiftMode = 'money'"
                        >
                            {{ t('guests.ledger.modes.money') }}
                        </button>
                        <span class="h-6 w-px bg-neutral-200" />
                        <button
                            type="button"
                            :class="[
                                'px-4 py-2 text-sm font-medium transition',
                                ledgerGiftMode === 'gift' ? 'bg-neutral-950 text-white' : 'text-neutral-700 hover:bg-neutral-50 hover:text-neutral-950',
                            ]"
                            @click="ledgerGiftMode = 'gift'"
                        >
                            {{ t('guests.ledger.modes.gift') }}
                        </button>
                        <span class="h-6 w-px bg-neutral-200" />
                        <button
                            type="button"
                            :class="[
                                'px-4 py-2 text-sm font-medium transition',
                                ledgerGiftMode === 'both' ? 'bg-neutral-950 text-white' : 'text-neutral-700 hover:bg-neutral-50 hover:text-neutral-950',
                            ]"
                            @click="ledgerGiftMode = 'both'"
                        >
                            {{ t('guests.ledger.modes.both') }}
                        </button>
                    </div>
                    </div>

                    <div v-if="ledgerGiftMode !== 'gift'" class="grid gap-3 rounded-3xl border border-neutral-200 px-4 py-4 sm:grid-cols-[150px_minmax(0,1fr)]">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.fields.currency') }}
                            </label>
                            <NativeSelect v-model="ledgerEntryForm.gift_currency">
                                <NativeSelectOption value="EUR">EUR</NativeSelectOption>
                                <NativeSelectOption value="GBP">GBP</NativeSelectOption>
                                <NativeSelectOption value="RON">RON</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                {{ t('guests.dialogs.ledger.value_label') }}
                            </label>
                            <Input v-model="ledgerEntryForm.gift_amount" type="number" min="0" step="0.01" placeholder="0.00" />
                        </div>
                    </div>

                    <div v-if="ledgerGiftMode !== 'money'" class="space-y-2 rounded-3xl border border-neutral-200 px-4 py-4">
                        <label class="text-sm font-medium text-neutral-700">
                            {{ t('guests.dialogs.ledger.note_label') }}
                        </label>
                        <Textarea
                            v-model="ledgerEntryForm.note"
                            rows="4"
                            :placeholder="t('guests.dialogs.ledger.note_placeholder')"
                        />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button data-test="guest-ledger-cancel" variant="outline" class="rounded-full px-5" @click="ledgerEntryDialogOpen = false">
                        {{ t('guests.actions.cancel') }}
                    </Button>
                    <Button class="rounded-full px-5" :disabled="quickSavingGuestId !== null" @click="saveLedgerEntry">
                        {{ t('guests.dialogs.ledger.save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ t('guests.dialogs.delete.title') }}</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ t('guests.dialogs.delete.description') }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>{{ t('guests.actions.cancel') }}</AlertDialogCancel>
                    <AlertDialogAction class="bg-rose-600 hover:bg-rose-700" @click="deleteGuestParty">
                        {{ t('guests.actions.delete') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
