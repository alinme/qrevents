<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock3,
    Copy,
    Download,
    Eye,
    ExternalLink,
    Import,
    ListChecks,
    Pencil,
    Phone,
    Printer,
    Search,
    ScrollText,
    SendHorizontal,
    Table2,
    Trash2,
    UserPlus,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
    invitationTemplateDefinitions,
    type InvitationTemplateId,
} from '@/lib/invitation-templates';
import {
    composeInvitationPaperPresentation,
    type InvitationWeddingDetails,
} from '@/lib/invitation-presentation';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import AppLayout from '@/layouts/AppLayout.vue';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.guests,
    },
    {
        title: 'Guests',
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
const showInvitationAdvanced = ref(false);
const showGuestAdvanced = ref(false);
const previewingInvitationTemplateId = ref<InvitationTemplateId | null>(null);
const editingTableId = ref<number | null>(null);
const activeSection = ref<'invitees' | 'invitation' | 'ledger' | 'guest_list'>('invitees');
const expandedGuestPartyId = ref<number | null>(null);
const quickSavingGuestId = ref<number | null>(null);
const selectedGuestIds = ref<number[]>([]);
const guestSearch = ref('');
const guestFilter = ref<'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift'>('all');
const quickGiftTypes = ref<Record<number, '' | 'gift' | 'money'>>({});
const quickGiftCurrencies = ref<Record<number, 'EUR' | 'GBP' | 'RON'>>({});
const quickGiftAmounts = ref<Record<number, string>>({});

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

const invitationTemplateCards = invitationTemplateDefinitions;

const statCards = computed(() => [
    {
        label: 'Parties',
        value: props.guestPartyStats.partyCount,
        detail: 'Invitees on the list',
        icon: Users,
    },
    {
        label: 'Invited',
        value: props.guestPartyStats.invitedAttendeesCount,
        detail: 'Expected attendees',
        icon: UserPlus,
    },
    {
        label: 'Confirmed',
        value: props.guestPartyStats.confirmedAttendeesCount,
        detail: `${props.guestPartyStats.acceptedPartyCount} accepted`,
        icon: CheckCircle2,
    },
    {
        label: 'Present',
        value: props.guestPartyStats.actualAttendeesCount,
        detail: `${props.guestPartyStats.presentPartyCount} marked present`,
        icon: Clock3,
    },
    {
        label: 'Ledger',
        value: formatMoney(
            props.guestPartyStats.moneyGiftTotal,
            props.guestPartyStats.moneyGiftCurrency,
        ),
        detail: `${props.guestPartyStats.pendingPartyCount} still waiting`,
        icon: Wallet,
    },
]);

const familyOverview = computed(() => [
    {
        label: 'Invitees',
        value: props.guestPartyStats.partyCount,
    },
    {
        label: 'Pending',
        value: props.guestPartyStats.pendingPartyCount,
    },
    {
        label: 'Accepted',
        value: props.guestPartyStats.acceptedPartyCount,
    },
]);

const ledgerGuestParties = computed(() => {
    return [...props.guestParties].sort((left, right) => {
        const leftNeedsAttendance = left.actualAttendanceStatus === 'unknown' ? 1 : 0;
        const rightNeedsAttendance = right.actualAttendanceStatus === 'unknown' ? 1 : 0;

        if (leftNeedsAttendance !== rightNeedsAttendance) {
            return rightNeedsAttendance - leftNeedsAttendance;
        }

        const leftNeedsGift = left.giftType === null ? 1 : 0;
        const rightNeedsGift = right.giftType === null ? 1 : 0;

        if (leftNeedsGift !== rightNeedsGift) {
            return rightNeedsGift - leftNeedsGift;
        }

        return left.name.localeCompare(right.name);
    });
});

const previewingInvitationTemplateCard = computed(() => {
    if (!previewingInvitationTemplateId.value) {
        return null;
    }

    return invitationTemplateCards.find((template) => template.id === previewingInvitationTemplateId.value) ?? null;
});

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
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    });

    return {
        eventName: presentation.leadIn,
        guestLabel: 'Invitation preview',
        headline: presentation.title,
        message: invitationSettingsForm.message || 'Add the main invitation message here.',
        closing: invitationSettingsForm.closing || 'A short RSVP reminder.',
        detailLines: presentation.detailLines,
        dateLabel: props.invitationPreview.eventDetails.dateLabel,
        venueAddress: props.invitationPreview.eventDetails.venueAddress,
    };
};

const activeInvitationPresentation = computed(() =>
    composeInvitationPaperPresentation({
        eventName: currentEvent.name,
        eventType: currentEvent.type,
        headline: invitationSettingsForm.headline || currentEvent.name,
        weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
    }),
);

const invitationSummaryCards = computed(() => [
    {
        label: 'Pending',
        value: pendingInvitationParties.value.length,
    },
    {
        label: 'Opened',
        value: props.guestParties.filter((party) => party.invitationOpenCount > 0).length,
    },
    {
        label: 'Answered',
        value: props.guestParties.filter((party) => party.respondedAt !== null).length,
    },
    {
        label: 'Reminded',
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
            toast.success('Invitation settings saved.');
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
        notes: party.notes ?? '',
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

const quickGiftTypeFor = (party: GuestParty): '' | 'gift' | 'money' => {
    return quickGiftTypes.value[party.id] ?? party.giftType ?? '';
};

const quickGiftCurrencyFor = (party: GuestParty): 'EUR' | 'GBP' | 'RON' => {
    return quickGiftCurrencies.value[party.id] ?? party.giftCurrency ?? 'EUR';
};

const quickGiftAmountFor = (party: GuestParty): string => {
    return quickGiftAmounts.value[party.id] ?? party.giftAmount ?? '';
};

const setQuickGiftType = (party: GuestParty, value: '' | 'gift' | 'money'): void => {
    quickGiftTypes.value = {
        ...quickGiftTypes.value,
        [party.id]: value,
    };
};

const setQuickGiftCurrency = (party: GuestParty, value: 'EUR' | 'GBP' | 'RON'): void => {
    quickGiftCurrencies.value = {
        ...quickGiftCurrencies.value,
        [party.id]: value,
    };
};

const setQuickGiftAmount = (party: GuestParty, value: string): void => {
    quickGiftAmounts.value = {
        ...quickGiftAmounts.value,
        [party.id]: value,
    };
};

const updateQuickGiftType = (party: GuestParty, value: unknown): void => {
    if (value === '' || value === 'gift' || value === 'money') {
        setQuickGiftType(party, value);
    }
};

const updateQuickGiftCurrency = (party: GuestParty, value: unknown): void => {
    if (value === 'EUR' || value === 'GBP' || value === 'RON') {
        setQuickGiftCurrency(party, value);
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
        props.eventInvitationSettings.headline,
        `${party.name},`,
        props.eventInvitationSettings.message,
        props.eventInvitationSettings.closing,
        props.eventInvitationSettings.contactPhone ? `Contact: ${props.eventInvitationSettings.contactPhone}` : null,
        `RSVP: ${party.inviteUrl}`,
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
        'Just a kind reminder to let us know if you can join us.',
        props.eventInvitationSettings.headline,
        props.eventInvitationSettings.message,
        props.eventInvitationSettings.contactPhone ? `Contact: ${props.eventInvitationSettings.contactPhone}` : null,
        `RSVP: ${party.inviteUrl}`,
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
        toast.success(`${label} copied.`);
    } catch {
        toast.error(`Could not copy ${label.toLowerCase()}.`);
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
        toast.error(mode === 'reminder' ? 'No pending invitees to remind.' : 'Select at least one invitee first.');

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
            toast.success(mode === 'reminder' ? 'Reminder bundle copied.' : 'Invitation bundle copied.');
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

        toast.error(mode === 'reminder' ? 'Could not share the reminder bundle.' : 'Could not share the invitation bundle.');
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
        `${party.name} marked as present.`,
    );
};

const markGuestPartyAbsent = (party: GuestParty): void => {
    patchGuestPartyQuickly(
        party,
        {
            actual_attendance_status: 'absent',
            actual_attendees_count: 0,
        },
        `${party.name} marked as absent.`,
    );
};

const resetGuestPartyAttendance = (party: GuestParty): void => {
    patchGuestPartyQuickly(
        party,
        {
            actual_attendance_status: 'unknown',
            actual_attendees_count: null,
        },
        `${party.name} moved back to not recorded.`,
    );
};

const saveQuickGift = (party: GuestParty): void => {
    const giftType = quickGiftTypeFor(party);

    patchGuestPartyQuickly(
        party,
        {
            gift_type: giftType === '' ? null : giftType,
            gift_currency: giftType === 'money' ? quickGiftCurrencyFor(party) : null,
            gift_amount: giftType === 'money' ? quickGiftAmountFor(party) : null,
        },
        `${party.name} ledger updated.`,
    );
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
            toast.success(`${guestListInfoParty.value?.name ?? 'Invitee'} table updated.`);
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
        toast.error('There are no pending invitees to copy right now.');

        return;
    }

    try {
        await navigator.clipboard.writeText(invitationMessageForParties(pendingInvitationParties.value));
        toast.success('Pending invitation bundle copied.');
    } catch {
        toast.error('Could not copy the pending invitation bundle.');
    }
};

const copySelectedInvites = async (): Promise<void> => {
    if (selectedGuestParties.value.length === 0) {
        toast.error('Select at least one invitee first.');

        return;
    }

    try {
        await navigator.clipboard.writeText(invitationMessageForParties(selectedGuestParties.value));
        toast.success('Selected invitation bundle copied.');
    } catch {
        toast.error('Could not copy the selected invitation bundle.');
    }
};

const onImportFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    importForm.import_file = input.files?.[0] ?? null;
};

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'Not yet';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatMoney = (value: number, currency: string): string => {
    return new Intl.NumberFormat('en-GB', {
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
        return 'Accepted';
    }

    if (status === 'declined') {
        return 'Declined';
    }

    return 'Waiting';
};

const actualAttendanceLabel = (status: GuestParty['actualAttendanceStatus']): string => {
    return {
        unknown: 'Not recorded',
        present: 'Came to the event',
        absent: 'Did not come',
    }[status];
};

const invitationLabel = (status: GuestParty['invitationStatus']): string => {
    return {
        draft: 'Draft',
        delivered_in_person: 'Delivered in person',
        sent: 'Sent',
        opened: 'Opened',
        responded: 'Responded',
    }[status];
};

const giftLabel = (party: GuestParty): string => {
    if (party.giftType === 'money' && party.giftAmount !== null && party.giftCurrency !== null) {
        return formatMoney(Number(party.giftAmount), party.giftCurrency);
    }

    if (party.giftType === 'gift') {
        return 'Gift recorded';
    }

    return 'No gift yet';
};

const mealPreferenceLabel = (value: GuestParty['mealPreference']): string | null => {
    if (!value) {
        return null;
    }

    return {
        standard: 'Standard meal',
        vegetarian: 'Vegetarian meal',
        vegan: 'Vegan meal',
        halal: 'Halal meal',
        other: 'Other meal request',
    }[value];
};

const invitationHistoryLabel = (party: GuestParty['invitationHistory'][number]): string => {
    const attendanceStatus = typeof party.meta.attendanceStatus === 'string' ? party.meta.attendanceStatus : null;

    return {
        sent_online: 'Invitation shared',
        delivered_in_person: 'Delivered in person',
        reminded: 'Reminder sent',
        opened: 'Invitation opened',
        responded: attendanceStatus === 'accepted'
            ? 'RSVP accepted'
            : attendanceStatus === 'declined'
                ? 'RSVP declined'
                : 'RSVP updated',
    }[party.type];
};
</script>

<template>
    <Head title="Guests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-5 p-4 md:p-6">
            <section class="space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-semibold tracking-tight text-neutral-950">
                                Guests
                            </h1>
                            <span v-if="retentionReminder" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                {{ retentionReminder.daysLeft }} days left to export
                            </span>
                        </div>
                        <p class="max-w-2xl text-sm text-neutral-600">
                            Work on one thing at a time: invitees, invitation, ledger, or guest list.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button v-if="activeSection === 'invitees'" variant="outline" class="h-10 rounded-full px-4" @click="importDialogOpen = true">
                            <Import class="mr-2 size-4" />
                            Import
                        </Button>
                        <Button v-if="activeSection === 'invitees'" variant="outline" class="h-10 rounded-full px-4" @click="openCreateTableDialog">
                            <Table2 class="mr-2 size-4" />
                            Tables
                        </Button>
                        <Button v-if="activeSection === 'invitees'" class="h-10 rounded-full px-4" @click="openCreateDialog">
                            <UserPlus class="mr-2 size-4" />
                            Add invitee
                        </Button>
                        <Button v-if="activeSection === 'ledger'" variant="outline" class="h-10 rounded-full px-4" @click="exportGuestLedger">
                            <Download class="mr-2 size-4" />
                            Export
                        </Button>
                        <Button v-if="activeSection === 'ledger'" class="h-10 rounded-full px-4" @click="openGuestReport">
                            <Printer class="mr-2 size-4" />
                            Report
                        </Button>
                        <Button v-if="activeSection === 'guest_list'" variant="outline" class="h-10 rounded-full px-4" @click="copyLink(publicGuestListUrl, 'Guest list link')">
                            <Copy class="mr-2 size-4" />
                            Copy guest list
                        </Button>
                        <Button v-if="activeSection === 'guest_list'" class="h-10 rounded-full px-4" @click="openInvite(publicGuestListUrl)">
                            <ExternalLink class="mr-2 size-4" />
                            Open guest list
                        </Button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        :class="[
                            'inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition',
                            activeSection === 'invitees' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300',
                        ]"
                        @click="activeSection = 'invitees'"
                    >
                        <Users class="size-4" />
                        <span>Invitees</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition',
                            activeSection === 'invitation' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300',
                        ]"
                        @click="activeSection = 'invitation'"
                    >
                        <ScrollText class="size-4" />
                        <span>Invitation</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition',
                            activeSection === 'ledger' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300',
                        ]"
                        @click="activeSection = 'ledger'"
                    >
                        <Wallet class="size-4" />
                        <span>Ledger</span>
                    </button>
                    <button
                        type="button"
                        :class="[
                            'inline-flex items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition',
                            activeSection === 'guest_list' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300',
                        ]"
                        @click="activeSection = 'guest_list'"
                    >
                        <ListChecks class="size-4" />
                        <span>Guest list</span>
                    </button>
                </div>
            </section>

            <section v-if="activeSection === 'invitees'" class="space-y-4">
                <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
                    <div class="grid gap-px bg-neutral-200 sm:grid-cols-3">
                        <div
                            v-for="item in familyOverview"
                            :key="item.label"
                            class="bg-white px-4 py-3"
                        >
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ item.label }}
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ item.value }}
                            </p>
                        </div>
                    </div>
                </div>

                <section class="rounded-3xl border border-neutral-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-neutral-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-neutral-950">
                                Invitees
                            </h2>
                            <p class="text-sm text-neutral-600">
                                {{ guestParties.length }} people or families on the list
                            </p>
                        </div>

                        <div class="flex flex-1 flex-col gap-3 lg:max-w-3xl">
                            <div class="flex flex-wrap items-center gap-3 text-sm text-neutral-600">
                                <label class="inline-flex items-center gap-3 font-medium text-neutral-700">
                                    <Checkbox
                                        :checked="allGuestsSelected"
                                        @update:checked="toggleSelectAllGuests(Boolean($event))"
                                    />
                                Select visible
                            </label>
                            <span>{{ selectedGuestIds.length }} selected</span>
                            <span>{{ filteredGuestParties.length }} shown</span>
                        </div>

                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
                            <div class="relative">
                                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                                <Input v-model="guestSearch" class="pl-9" placeholder="Search invitee, phone, or notes" />
                            </div>
                            <NativeSelect v-model="guestFilter">
                                <NativeSelectOption value="all">All invitees</NativeSelectOption>
                                <NativeSelectOption value="needing_reply">Need reply</NativeSelectOption>
                                <NativeSelectOption value="accepted">Accepted RSVP</NativeSelectOption>
                                <NativeSelectOption value="declined">Declined RSVP</NativeSelectOption>
                                <NativeSelectOption value="present">Actually came</NativeSelectOption>
                                <NativeSelectOption value="absent">Did not come</NativeSelectOption>
                                <NativeSelectOption value="not_sent">Not sent yet</NativeSelectOption>
                                <NativeSelectOption value="responded">Responded</NativeSelectOption>
                                <NativeSelectOption value="no_gift">No gift recorded</NativeSelectOption>
                            </NativeSelect>
                        </div>
                        </div>
                    </div>

                    <div v-if="guestParties.length === 0" class="px-5 py-12 text-center">
                        <Users class="mx-auto size-10 text-neutral-300" />
                        <h3 class="mt-4 text-lg font-semibold text-neutral-950">
                            No invitees yet
                        </h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-neutral-600">
                            Start with one name and add the rest later, or paste a list like <span class="font-medium text-neutral-900">Familia Popescu - 0722...</span>.
                        </p>
                        <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                            <Button class="rounded-full px-5" @click="openCreateDialog">
                                Add first invitee
                            </Button>
                            <Button variant="outline" class="rounded-full px-5" @click="importDialogOpen = true">
                                Paste or upload a list
                            </Button>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-neutral-200">
                        <div v-if="selectedGuestIds.length > 0" class="flex flex-wrap items-center gap-2 border-b border-neutral-200 bg-neutral-50/70 px-5 py-3">
                            <span class="mr-1 text-sm font-medium text-neutral-700">
                                {{ selectedGuestIds.length }} selected
                            </span>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="selectedGuestIds.length === 0 || invitationBulkForm.processing"
                                @click="shareSelectedInvites"
                            >
                                <SendHorizontal class="mr-2 size-4" />
                                Share selected
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="selectedGuestIds.length === 0"
                                @click="copySelectedInvites"
                            >
                                <Copy class="mr-2 size-4" />
                                Copy selected
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="(selectedGuestIds.length > 0 && selectedPendingGuestParties.length === 0) || invitationBulkForm.processing"
                                @click="remindSelectedInvites"
                            >
                                <Clock3 class="mr-2 size-4" />
                                Remind pending
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="selectedGuestIds.length === 0 || invitationBulkForm.processing"
                                @click="updateInvitationDelivery(selectedGuestIds, 'mark_delivered_in_person', 'in_person')"
                            >
                                <CheckCircle2 class="mr-2 size-4" />
                                Mark delivered
                            </Button>
                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                :disabled="selectedGuestIds.length === 0 || invitationBulkForm.processing"
                                @click="updateInvitationDelivery(selectedGuestIds, 'mark_sent_online', 'other')"
                            >
                                <SendHorizontal class="mr-2 size-4" />
                                Mark sent
                            </Button>
                            <Button
                                variant="ghost"
                                class="rounded-full px-4"
                                :disabled="selectedGuestIds.length === 0"
                                @click="clearGuestSelection"
                            >
                                Clear
                            </Button>
                        </div>

                        <div
                            v-if="filteredGuestParties.length === 0"
                            class="px-5 py-12 text-center"
                        >
                            <Users class="mx-auto size-10 text-neutral-300" />
                            <h3 class="mt-4 text-lg font-semibold text-neutral-950">
                                No invitees match this filter
                            </h3>
                            <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-neutral-600">
                                Try a different filter or clear the search.
                            </p>
                        </div>

                        <div
                            v-for="party in filteredGuestParties"
                            :key="party.id"
                            class="px-5 py-4"
                        >
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-2">
                                    <label class="inline-flex items-center gap-3 text-sm font-medium text-neutral-500">
                                        <Checkbox
                                            :checked="selectedGuestIds.includes(party.id)"
                                            @update:checked="toggleGuestSelection(party.id, Boolean($event))"
                                        />
                                        Select
                                    </label>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-neutral-950">
                                            {{ party.name }}
                                        </h3>
                                        <Badge :class="attendanceBadgeClass(party.attendanceStatus)">
                                            {{ attendanceLabel(party.attendanceStatus) }}
                                        </Badge>
                                        <Badge variant="outline" class="border-neutral-200 bg-neutral-50 text-neutral-700">
                                            {{ invitationLabel(party.invitationStatus) }}
                                        </Badge>
                                    </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-600">
                                    <span class="inline-flex items-center gap-1.5">
                                        <Phone class="size-4" />
                                        {{ party.phone || 'No phone saved' }}
                                    </span>
                                    <span>{{ party.tableName || 'No table set' }}</span>
                                    <span>Invited {{ party.invitedAttendeesCount }}</span>
                                    <span v-if="party.confirmedAttendeesCount !== null">Confirmed {{ party.confirmedAttendeesCount }}</span>
                                    <span v-if="party.actualAttendeesCount !== null">Came {{ party.actualAttendeesCount }}</span>
                                    <span>{{ giftLabel(party) }}</span>
                                    <span v-if="party.reminderCount > 0">Reminded {{ party.reminderCount }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        @click="shareGuestInvite(party)"
                                    >
                                    <SendHorizontal class="mr-2 size-4" />
                                    Share
                                </Button>
                                <Button
                                    variant="outline"
                                    class="rounded-full px-4"
                                    :disabled="party.attendanceStatus !== 'pending'"
                                    @click="remindGuestInvite(party)"
                                >
                                    <Clock3 class="mr-2 size-4" />
                                    Remind
                                </Button>
                                <Button
                                    variant="outline"
                                    class="rounded-full px-4"
                                    @click="openEditDialog(party)"
                                >
                                        <Pencil class="mr-2 size-4" />
                                        Edit
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        @click="toggleGuestDetails(party.id)"
                                    >
                                        {{ expandedGuestPartyId === party.id ? 'Hide details' : 'Details' }}
                                    </Button>
                                </div>
                            </div>

                            <div
                                v-if="expandedGuestPartyId === party.id"
                                class="mt-4 space-y-4 border-t border-neutral-200 pt-4"
                            >
                                <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2 text-sm text-neutral-600">
                                    <p><span class="font-medium text-neutral-900">Opens:</span> {{ party.invitationOpenCount }}</p>
                                    <p><span class="font-medium text-neutral-900">First open:</span> {{ formatDateTime(party.invitationFirstOpenedAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Last open:</span> {{ formatDateTime(party.invitationLastOpenedAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Last IP:</span> {{ party.invitationLastOpenedIp || 'Not captured yet' }}</p>
                                    <p><span class="font-medium text-neutral-900">Delivery:</span> {{ party.invitationDeliveryChannel || 'Not set' }}</p>
                                    <p><span class="font-medium text-neutral-900">Delivered:</span> {{ formatDateTime(party.invitationDeliveredAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Last reminder:</span> {{ formatDateTime(party.lastReminderAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Responded:</span> {{ formatDateTime(party.respondedAt) }}</p>
                                </div>

                                <div class="space-y-2 text-sm text-neutral-600">
                                    <p><span class="font-medium text-neutral-900">Event day:</span> {{ actualAttendanceLabel(party.actualAttendanceStatus) }}</p>
                                    <p><span class="font-medium text-neutral-900">Count:</span> {{ party.actualAttendeesCount ?? 'Not recorded' }}</p>
                                    <p v-if="party.notes"><span class="font-medium text-neutral-900">Notes:</span> {{ party.notes }}</p>
                                    <p v-if="party.guestNames"><span class="font-medium text-neutral-900">Guest names:</span> {{ party.guestNames }}</p>
                                    <p v-if="mealPreferenceLabel(party.mealPreference)"><span class="font-medium text-neutral-900">Meal:</span> {{ mealPreferenceLabel(party.mealPreference) }}</p>
                                    <p v-if="party.responseNotes"><span class="font-medium text-neutral-900">RSVP note:</span> {{ party.responseNotes }}</p>
                                </div>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-neutral-500">
                                        Invitation history
                                    </p>
                                    <div v-if="party.invitationHistory.length > 0" class="divide-y divide-neutral-200 rounded-2xl border border-neutral-200">
                                        <div
                                            v-for="activity in party.invitationHistory"
                                            :key="`${activity.type}-${activity.createdAt}-${activity.deliveryChannel}`"
                                            class="flex items-start justify-between gap-3 px-3 py-2.5 text-sm"
                                        >
                                            <div>
                                                <p class="font-medium text-neutral-900">
                                                    {{ invitationHistoryLabel(activity) }}
                                                </p>
                                                <p class="text-xs text-neutral-500">
                                                    {{ activity.deliveryChannel || 'No channel saved' }}
                                                </p>
                                            </div>
                                            <p class="text-xs text-neutral-500">
                                                {{ formatDateTime(activity.createdAt) }}
                                            </p>
                                        </div>
                                    </div>
                                    <p v-else class="mt-3 text-sm text-neutral-500">
                                        No invitation activity yet.
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        @click="copyLink(party.inviteUrl, `${party.name} invite link`)"
                                    >
                                        <Copy class="mr-2 size-4" />
                                        Copy link
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        @click="openInvite(party.inviteUrl)"
                                    >
                                        <ExternalLink class="mr-2 size-4" />
                                        Open invite
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        :disabled="invitationBulkForm.processing"
                                        @click="updateInvitationDelivery([party.id], 'mark_delivered_in_person', 'in_person')"
                                    >
                                        <CheckCircle2 class="mr-2 size-4" />
                                        Delivered
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        :disabled="invitationBulkForm.processing"
                                        @click="updateInvitationDelivery([party.id], 'mark_sent_online', 'other')"
                                    >
                                        <SendHorizontal class="mr-2 size-4" />
                                        Mark sent
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4"
                                        :disabled="party.attendanceStatus !== 'pending' || invitationBulkForm.processing"
                                        @click="remindGuestInvite(party)"
                                    >
                                        <Clock3 class="mr-2 size-4" />
                                        Remind
                                    </Button>
                                    <Button
                                        variant="outline"
                                        class="rounded-full px-4 text-rose-600 hover:text-rose-700"
                                        @click="confirmDelete(party)"
                                    >
                                        <Trash2 class="mr-2 size-4" />
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <section v-else-if="activeSection === 'invitation'" class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-5 xl:grid xl:grid-cols-[minmax(0,1fr)_320px] xl:items-start">
                    <div class="space-y-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-neutral-950">
                                    Invitation
                                </h2>
                                <p class="mt-1 text-sm text-neutral-600">
                                    Pick a style, write the message, then share the link.
                                </p>
                            </div>
                            <div class="rounded-full bg-neutral-100 p-2 text-neutral-700">
                                <ScrollText class="size-4" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Template
                            </label>
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                <div
                                    v-for="template in invitationTemplateCards"
                                    :key="template.id"
                                    :class="[
                                        'rounded-2xl border p-2.5 transition',
                                        template.artClass,
                                        invitationSettingsForm.template === template.id
                                            ? 'ring-2 ring-neutral-950 shadow-sm'
                                            : 'hover:-translate-y-0.5 hover:shadow-sm',
                                    ]"
                                >
                                    <button
                                        type="button"
                                        class="block w-full text-left"
                                        @click="invitationSettingsForm.template = template.id"
                                    >
                                        <div
                                            class="overflow-hidden rounded-xl border border-current/10 bg-white/45 shadow-sm"
                                        >
                                            <div class="aspect-[210/297]">
                                                <InvitationPaper
                                                    :template="template.id"
                                                    :event-name="invitationTemplatePreviewContent(template.id).eventName"
                                                    :logo-url="props.invitationPreview.branding.logoUrl"
                                                    :guest-label="invitationTemplatePreviewContent(template.id).guestLabel"
                                                    :headline="invitationTemplatePreviewContent(template.id).headline"
                                                    :message="invitationTemplatePreviewContent(template.id).message"
                                                    :closing="invitationTemplatePreviewContent(template.id).closing"
                                                    :detail-lines="invitationTemplatePreviewContent(template.id).detailLines"
                                                    :date-label="invitationTemplatePreviewContent(template.id).dateLabel"
                                                    :venue-address="invitationTemplatePreviewContent(template.id).venueAddress"
                                                    mode="preview"
                                                />
                                            </div>
                                        </div>
                                        <p class="mt-2 text-sm font-semibold">
                                            {{ template.label }}
                                        </p>
                                    </button>

                                    <button
                                        type="button"
                                        class="mt-1 text-xs font-medium text-current/70 underline underline-offset-4"
                                        @click="previewingInvitationTemplateId = template.id"
                                    >
                                        Preview
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Title
                            </label>
                            <Input v-model="invitationSettingsForm.headline" :placeholder="currentEvent.name" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Message
                            </label>
                            <Textarea
                                v-model="invitationSettingsForm.message"
                                rows="3"
                                placeholder="Short invitation message."
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Closing
                            </label>
                            <Textarea
                                v-model="invitationSettingsForm.closing"
                                rows="2"
                                placeholder="A short RSVP reminder."
                            />
                        </div>

                        <div class="flex flex-col gap-3 rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <label class="flex items-center gap-3 text-sm text-neutral-700">
                                <input
                                    v-model="invitationSettingsForm.public_rsvp_enabled"
                                    type="checkbox"
                                    class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                >
                                Public RSVP link enabled
                            </label>

                            <Button
                                variant="outline"
                                class="rounded-full px-4"
                                @click="showInvitationAdvanced = !showInvitationAdvanced"
                            >
                                {{ showInvitationAdvanced ? 'Hide advanced' : 'Advanced' }}
                            </Button>
                        </div>

                        <div v-if="showInvitationAdvanced" class="space-y-2 rounded-2xl border border-neutral-200 px-4 py-4">
                            <label class="text-sm font-medium text-neutral-700">
                                Contact phone
                            </label>
                            <Input v-model="invitationSettingsForm.contact_phone" placeholder="Optional" />
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-neutral-200 pt-4 xl:border-l xl:border-t-0 xl:pl-5 xl:pt-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <Button class="rounded-full px-5" @click="openInvite(publicInvitationUrl)">
                                <ExternalLink class="mr-2 size-4" />
                                Open preview
                            </Button>
                            <Button variant="outline" class="rounded-full px-5" @click="saveInvitationPreview">
                                <Download class="mr-2 size-4" />
                                Save or print
                            </Button>
                            <Button variant="outline" class="rounded-full px-5" @click="copyLink(publicInvitationUrl, 'Invitation preview link')">
                                <Copy class="mr-2 size-4" />
                                Copy preview link
                            </Button>
                        </div>

                        <InvitationPaper
                            :template="invitationSettingsForm.template"
                            :event-name="activeInvitationPresentation.leadIn"
                            :logo-url="props.invitationPreview.branding.logoUrl"
                            guest-label="Invitation preview"
                            :headline="activeInvitationPresentation.title"
                            :message="invitationSettingsForm.message || 'Add the main invitation message here.'"
                            :closing="invitationSettingsForm.closing || 'A short RSVP reminder.'"
                            :detail-lines="activeInvitationPresentation.detailLines"
                            :contact-phone="showInvitationAdvanced ? invitationSettingsForm.contact_phone : null"
                            :date-label="props.invitationPreview.eventDetails.dateLabel"
                            :venue-address="props.invitationPreview.eventDetails.venueAddress"
                            mode="preview"
                        />

                        <div class="space-y-4 border-t border-neutral-200 pt-4">
                            <p class="text-sm font-semibold text-neutral-950">
                                Invitation campaign
                            </p>
                            <div class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-200">
                                <div
                                    v-for="item in invitationSummaryCards"
                                    :key="item.label"
                                    class="bg-white px-4 py-3"
                                >
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">
                                        {{ item.label }}
                                    </p>
                                    <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                                        {{ item.value }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <Button class="rounded-full px-5" @click="sharePendingInvites">
                                    <SendHorizontal class="mr-2 size-4" />
                                    Share pending
                                </Button>
                                <Button variant="outline" class="rounded-full px-5" @click="copyPendingInvites">
                                    <Copy class="mr-2 size-4" />
                                    Copy pending
                                </Button>
                                <Button variant="outline" class="rounded-full px-5" @click="remindPendingInvites">
                                    <Clock3 class="mr-2 size-4" />
                                    Remind pending
                                </Button>
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-neutral-200 pt-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-neutral-950">
                                        Public RSVP link
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-600">
                                        Share this if someone is not already on the list.
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full px-3 py-1 text-xs font-medium',
                                        invitationSettingsForm.public_rsvp_enabled
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-neutral-200 text-neutral-600',
                                    ]"
                                >
                                    {{ invitationSettingsForm.public_rsvp_enabled ? 'On' : 'Off' }}
                                </span>
                            </div>

                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600 break-all">
                                {{ publicInvitationUrl }}
                            </div>

                            <div class="flex flex-col gap-2 sm:flex-row">
                                <Button class="rounded-full px-5" @click="copyLink(publicInvitationUrl, 'Public RSVP link')">
                                    <Copy class="mr-2 size-4" />
                                    Copy link
                                </Button>
                                <Button variant="outline" class="rounded-full px-5" @click="openInvite(publicInvitationUrl)">
                                    <ExternalLink class="mr-2 size-4" />
                                    Open page
                                </Button>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-neutral-200 pt-4">
                            <p class="text-sm font-semibold text-neutral-950">
                                Recent activity
                            </p>

                            <div v-if="invitationRecentActivity.length > 0" class="divide-y divide-neutral-200 rounded-2xl border border-neutral-200">
                                <div
                                    v-for="activity in invitationRecentActivity"
                                    :key="`${activity.guestName}-${activity.type}-${activity.createdAt}`"
                                    class="flex items-start justify-between gap-3 px-4 py-3"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-neutral-950">
                                            {{ activity.guestName }}
                                        </p>
                                        <p class="mt-1 text-sm text-neutral-600">
                                            {{ invitationHistoryLabel(activity) }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-neutral-500">
                                        {{ formatDateTime(activity.createdAt) }}
                                    </p>
                                </div>
                            </div>

                            <p v-else class="text-sm text-neutral-500">
                                Invitation activity will appear here after you start sharing links.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex justify-end border-t border-neutral-200 pt-4">
                    <Button
                        class="rounded-full px-5"
                        :disabled="savingInvitationSettings || invitationSettingsForm.processing"
                        @click="saveInvitationSettings"
                    >
                        <SendHorizontal class="mr-2 size-4" />
                        Save invitation
                    </Button>
                </div>
            </section>

            <section v-else-if="activeSection === 'ledger'" class="space-y-4">
                <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
                    <div class="grid gap-px bg-neutral-200 sm:grid-cols-2 xl:grid-cols-5">
                        <div
                            v-for="stat in statCards"
                            :key="stat.label"
                            class="bg-white px-4 py-3"
                        >
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                {{ stat.label }}
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ stat.value }}
                            </p>
                            <p class="mt-1 text-xs text-neutral-500">
                                {{ stat.detail }}
                            </p>
                        </div>
                    </div>
                </div>

                <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-neutral-950">
                                Event day ledger
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Mark who came, then record the gift or money right here.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button class="rounded-full px-5" @click="openGuestReport">
                                <Printer class="mr-2 size-4" />
                                Open report
                            </Button>
                            <Button variant="outline" class="rounded-full px-5" @click="exportGuestLedger">
                                <Download class="mr-2 size-4" />
                                Export CSV
                            </Button>
                        </div>
                    </div>

                    <div
                        v-if="retentionReminder"
                        class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                    >
                        Retention ends on {{ retentionReminder.dateLabel }}.
                    </div>

                    <div class="mt-5 divide-y divide-neutral-200 overflow-hidden rounded-2xl border border-neutral-200">
                        <div
                            v-for="party in ledgerGuestParties"
                            :key="party.id"
                            class="bg-white p-4"
                        >
                            <div class="flex flex-col gap-4 xl:grid xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)_minmax(0,1fr)] xl:items-center">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-neutral-950">
                                            {{ party.name }}
                                        </p>
                                        <Badge variant="outline" :class="attendanceBadgeClass(party.attendanceStatus)">
                                            {{ attendanceLabel(party.attendanceStatus) }}
                                        </Badge>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-neutral-600">
                                        <span>Invited {{ party.invitedAttendeesCount }}</span>
                                        <span v-if="party.confirmedAttendeesCount !== null">Confirmed {{ party.confirmedAttendeesCount }}</span>
                                        <span>{{ actualAttendanceLabel(party.actualAttendanceStatus) }}</span>
                                        <span>{{ giftLabel(party) }}</span>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">
                                        Attendance
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            variant="outline"
                                            class="rounded-full px-4"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="markGuestPartyPresent(party)"
                                        >
                                            <CheckCircle2 class="mr-2 size-4" />
                                            Came
                                        </Button>
                                        <Button
                                            variant="outline"
                                            class="rounded-full px-4"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="markGuestPartyAbsent(party)"
                                        >
                                            <Trash2 class="mr-2 size-4" />
                                            Did not come
                                        </Button>
                                        <Button
                                            variant="outline"
                                            class="rounded-full px-4"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="resetGuestPartyAttendance(party)"
                                        >
                                            <Clock3 class="mr-2 size-4" />
                                            Reset
                                        </Button>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">
                                        Gift / Money
                                    </p>
                                    <div class="grid gap-2 sm:grid-cols-[120px_minmax(0,120px)_minmax(0,1fr)_auto]">
                                        <NativeSelect
                                            :model-value="quickGiftTypeFor(party)"
                                            @update:model-value="updateQuickGiftType(party, $event)"
                                        >
                                            <NativeSelectOption value="">None</NativeSelectOption>
                                            <NativeSelectOption value="gift">Gift</NativeSelectOption>
                                            <NativeSelectOption value="money">Money</NativeSelectOption>
                                        </NativeSelect>

                                        <NativeSelect
                                            v-if="quickGiftTypeFor(party) === 'money'"
                                            :model-value="quickGiftCurrencyFor(party)"
                                            @update:model-value="updateQuickGiftCurrency(party, $event)"
                                        >
                                            <NativeSelectOption value="EUR">EUR</NativeSelectOption>
                                            <NativeSelectOption value="GBP">GBP</NativeSelectOption>
                                            <NativeSelectOption value="RON">RON</NativeSelectOption>
                                        </NativeSelect>

                                        <Input
                                            v-if="quickGiftTypeFor(party) === 'money'"
                                            :model-value="quickGiftAmountFor(party)"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            placeholder="Amount"
                                            @update:model-value="setQuickGiftAmount(party, String($event))"
                                        />

                                        <Button
                                            class="rounded-full px-4"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="saveQuickGift(party)"
                                        >
                                            Save
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <section v-else class="space-y-4">
                <div class="overflow-hidden rounded-3xl border border-neutral-200 bg-white">
                    <div class="grid gap-px bg-neutral-200 sm:grid-cols-3">
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                Invitees
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.partyCount }}
                            </p>
                        </div>
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                Marked present
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.presentPartyCount }}
                            </p>
                        </div>
                        <div class="bg-white px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.2em] text-neutral-500">
                                Not recorded
                            </p>
                            <p class="mt-1.5 text-xl font-semibold tracking-tight text-neutral-950">
                                {{ props.guestPartyStats.partyCount - props.guestPartyStats.presentPartyCount - props.guestPartyStats.absentPartyCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-neutral-950">
                                Entrance guest list
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Search fast, check who arrived, and see their table.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button variant="outline" class="rounded-full px-5" @click="copyLink(publicGuestListUrl, 'Guest list link')">
                                <Copy class="mr-2 size-4" />
                                Copy link
                            </Button>
                            <Button class="rounded-full px-5" @click="openInvite(publicGuestListUrl)">
                                <ExternalLink class="mr-2 size-4" />
                                Open public page
                            </Button>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_280px]">
                        <div class="space-y-3">
                            <div class="relative">
                                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-neutral-400" />
                                <Input v-model="guestListSearch" class="pl-9" placeholder="Search invitee, phone, or table" />
                            </div>

                            <div class="divide-y divide-neutral-200 border-t border-neutral-200">
                                <div
                                    v-for="party in guestListParties"
                                    :key="party.id"
                                    class="flex flex-col gap-3 py-3 transition-colors lg:flex-row lg:items-center lg:justify-between"
                                >
                                    <div class="min-w-0 space-y-1">
                                        <p class="truncate text-sm font-semibold text-neutral-950">
                                            {{ party.name }}
                                        </p>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-neutral-600">
                                            <span>{{ party.phone || 'No phone saved' }}</span>
                                            <span>{{ party.tableName || 'No table yet' }}</span>
                                            <span v-if="party.confirmedAttendeesCount !== null">Confirmed {{ party.confirmedAttendeesCount }}</span>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <Button variant="outline" class="rounded-full px-4" @click="openGuestListInfo(party)">
                                            <Eye class="mr-2 size-4" />
                                            Details
                                        </Button>
                                        <Button
                                            v-if="!party.tableName && props.eventTables.length === 0"
                                            variant="ghost"
                                            class="rounded-full px-4"
                                            @click="openCreateTableDialog"
                                        >
                                            <Table2 class="mr-2 size-4" />
                                            Add tables
                                        </Button>
                                        <Button
                                            :variant="party.actualAttendanceStatus === 'present' ? 'default' : 'outline'"
                                            :class="party.actualAttendanceStatus === 'present' ? 'rounded-full bg-emerald-600 px-4 text-white hover:bg-emerald-700' : 'rounded-full px-4'"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="markGuestPartyPresent(party)"
                                        >
                                            Came
                                        </Button>
                                        <Button
                                            :variant="party.actualAttendanceStatus === 'absent' ? 'default' : 'outline'"
                                            :class="party.actualAttendanceStatus === 'absent' ? 'rounded-full bg-rose-600 px-4 text-white hover:bg-rose-700' : 'rounded-full px-4'"
                                            :disabled="quickSavingGuestId === party.id"
                                            @click="markGuestPartyAbsent(party)"
                                        >
                                            No show
                                        </Button>
                                        <Button variant="ghost" class="rounded-full px-4" :disabled="quickSavingGuestId === party.id" @click="resetGuestPartyAttendance(party)">
                                            Reset
                                        </Button>
                                    </div>
                                </div>

                                <div v-if="guestListParties.length === 0" class="py-8 text-sm text-neutral-500">
                                    No invitees match this search.
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 border-t border-neutral-200 pt-4 xl:border-l xl:border-t-0 xl:pl-5 xl:pt-0">
                            <p class="text-sm font-semibold text-neutral-950">
                                Public entrance page
                            </p>
                            <p class="text-sm text-neutral-600">
                                Share this with the people at the entrance so they can search the list and mark arrivals.
                            </p>
                            <div class="text-sm text-neutral-600 break-all">
                                {{ publicGuestListUrl }}
                            </div>
                        </div>
                    </div>
                </section>
            </section>
        </div>

        <Dialog :open="guestDialogOpen" @update:open="guestDialogOpen = $event">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? 'Edit invitee' : 'Add invitee' }}
                    </DialogTitle>
                    <DialogDescription>
                        Start with the name. Everything else can stay on the default settings until you need it.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Family / Name
                        </label>
                        <Input v-model="guestForm.name" placeholder="Familia Popescu or James Webb" />
                        <p v-if="guestForm.errors.name" class="text-sm text-rose-600">
                            {{ guestForm.errors.name }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-y border-neutral-200 py-3">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                Advanced details
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                Use this only when you want to adjust attendance, invitation, table, or ledger details by hand.
                            </p>
                        </div>
                        <Button variant="outline" class="rounded-full px-4" @click="showGuestAdvanced = !showGuestAdvanced">
                            {{ showGuestAdvanced ? 'Hide advanced' : 'Show advanced' }}
                        </Button>
                    </div>

                    <div v-if="showGuestAdvanced" class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Phone
                            </label>
                            <Input v-model="guestForm.phone" placeholder="07..." />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Table
                            </label>
                            <NativeSelect v-model="guestForm.event_table_id">
                                <NativeSelectOption value="">No table yet</NativeSelectOption>
                                <NativeSelectOption
                                    v-for="table in selectableTables"
                                    :key="table.id"
                                    :value="String(table.id)"
                                    :disabled="!table.selectable"
                                >
                                    {{ table.name }} · {{ table.remainingSeats }} seats left
                                </NativeSelectOption>
                            </NativeSelect>
                            <p class="text-xs text-neutral-500">
                                Full tables are locked until seats open up.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Invited attendees
                            </label>
                            <Input v-model="guestForm.invited_attendees_count" type="number" min="1" max="1000" />
                            <p class="text-xs text-neutral-500">
                                How many seats you are reserving for this invitee or family before they respond. If you are not sure yet, leave it at 1 and adjust it later.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                RSVP status
                            </label>
                            <NativeSelect v-model="guestForm.attendance_status">
                                <NativeSelectOption value="pending">Waiting</NativeSelectOption>
                                <NativeSelectOption value="accepted">Accepted</NativeSelectOption>
                                <NativeSelectOption value="declined">Declined</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <div v-if="showConfirmedCount" class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Confirmed attendees
                            </label>
                            <Input v-model="guestForm.confirmed_attendees_count" type="number" min="0" max="1000" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Actually attended
                            </label>
                            <NativeSelect v-model="guestForm.actual_attendance_status">
                                <NativeSelectOption value="unknown">Not recorded yet</NativeSelectOption>
                                <NativeSelectOption value="present">Came to the event</NativeSelectOption>
                                <NativeSelectOption value="absent">Did not come</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <div v-if="showActualCount" class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Attended count
                            </label>
                            <Input v-model="guestForm.actual_attendees_count" type="number" min="0" max="1000" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Invitation status
                            </label>
                            <NativeSelect v-model="guestForm.invitation_status">
                                <NativeSelectOption value="draft">Draft</NativeSelectOption>
                                <NativeSelectOption value="delivered_in_person">Delivered in person</NativeSelectOption>
                                <NativeSelectOption value="sent">Sent online</NativeSelectOption>
                                <NativeSelectOption value="opened">Opened</NativeSelectOption>
                                <NativeSelectOption value="responded">Responded</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Delivery channel
                            </label>
                            <NativeSelect v-model="guestForm.invitation_delivery_channel">
                                <NativeSelectOption value="">Not set</NativeSelectOption>
                                <NativeSelectOption value="in_person">In person</NativeSelectOption>
                                <NativeSelectOption value="phone">Phone</NativeSelectOption>
                                <NativeSelectOption value="whatsapp">WhatsApp</NativeSelectOption>
                                <NativeSelectOption value="facebook">Facebook</NativeSelectOption>
                                <NativeSelectOption value="public_link">Public link</NativeSelectOption>
                                <NativeSelectOption value="other">Other</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Gift type
                            </label>
                            <NativeSelect v-model="guestForm.gift_type">
                                <NativeSelectOption value="">Not set</NativeSelectOption>
                                <NativeSelectOption value="money">Money</NativeSelectOption>
                                <NativeSelectOption value="gift">Gift</NativeSelectOption>
                            </NativeSelect>
                        </div>

                        <template v-if="showGiftAmount">
                            <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Currency
                            </label>
                            <NativeSelect v-model="guestForm.gift_currency">
                                <NativeSelectOption value="EUR">EUR</NativeSelectOption>
                                <NativeSelectOption value="GBP">GBP</NativeSelectOption>
                                <NativeSelectOption value="RON">RON</NativeSelectOption>
                            </NativeSelect>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    Amount
                                </label>
                                <Input v-model="guestForm.gift_amount" type="number" min="0" step="0.01" />
                            </div>
                        </template>

                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Notes
                            </label>
                            <Textarea
                                v-model="guestForm.notes"
                                rows="4"
                                placeholder="Example: Marcel + wife + 2 kids, close family friends, table near band."
                            />
                        </div>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" class="rounded-full px-5" @click="guestDialogOpen = false">
                        Cancel
                    </Button>
                    <Button class="rounded-full px-5" :disabled="guestForm.processing" @click="saveGuestParty">
                        {{ isEditing ? 'Save changes' : 'Add invitee' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="importDialogOpen" @update:open="importDialogOpen = $event">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Import guest list</DialogTitle>
                    <DialogDescription>
                        Paste free text or upload a CSV/TXT file. The importer understands formats like
                        <span class="font-medium text-neutral-900">Familia Popescu - 0722...</span> and
                        <span class="font-medium text-neutral-900">Ion Vasile, 0712...</span>.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Paste text
                        </label>
                        <Textarea
                            v-model="importForm.import_text"
                            rows="8"
                            placeholder="Familia Popescu - 0722123456&#10;Ion Vasile, 07126326123&#10;James Webb - 4 guests - 0744556677"
                        />
                        <p v-if="importForm.errors.import_text" class="text-sm text-rose-600">
                            {{ importForm.errors.import_text }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Or upload CSV / TXT
                        </label>
                        <Input type="file" accept=".csv,.txt" @change="onImportFileChange" />
                        <p class="text-sm text-neutral-500">
                            Simple spreadsheet exports and copied note files both work here.
                        </p>
                        <p v-if="importForm.errors.import_file" class="text-sm text-rose-600">
                            {{ importForm.errors.import_file }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <Button variant="outline" class="rounded-full px-5" @click="importDialogOpen = false">
                        Cancel
                    </Button>
                    <Button class="rounded-full px-5" :disabled="importForm.processing" @click="importGuestParties">
                        Import guests
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="tablesDialogOpen" @update:open="tablesDialogOpen = $event">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ editingTable ? 'Edit table' : 'Manage tables' }}
                    </DialogTitle>
                    <DialogDescription>
                        Add the tables and seat counts here, then assign invitees to them from advanced details.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_160px_auto]">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Table name
                            </label>
                            <Input v-model="tableForm.name" placeholder="Table 8" />
                            <p v-if="tableForm.errors.name" class="text-sm text-rose-600">
                                {{ tableForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Seats
                            </label>
                            <Input v-model="tableForm.seats_count" type="number" min="1" max="1000" />
                            <p v-if="tableForm.errors.seats_count" class="text-sm text-rose-600">
                                {{ tableForm.errors.seats_count }}
                            </p>
                        </div>

                        <div class="flex items-end">
                            <Button class="w-full rounded-full px-5" :disabled="tableForm.processing" @click="saveTable">
                                {{ editingTable ? 'Save table' : 'Add table' }}
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
                                    <span>{{ table.seatsCount }} seats</span>
                                    <span>{{ table.occupiedSeats }} used</span>
                                    <span>{{ table.remainingSeats }} left</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button variant="outline" class="rounded-full px-4" @click="openEditTableDialog(table)">
                                    <Pencil class="mr-2 size-4" />
                                    Edit
                                </Button>
                                <Button variant="ghost" class="rounded-full px-4 text-rose-600 hover:text-rose-700" @click="deleteTable(table)">
                                    <Trash2 class="mr-2 size-4" />
                                    Delete
                                </Button>
                            </div>
                        </div>

                        <div v-if="props.eventTables.length === 0" class="py-8 text-sm text-neutral-500">
                            No tables yet. Add the first one above.
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog :open="previewingInvitationTemplateCard !== null" @update:open="handleInvitationTemplatePreviewOpenChange">
            <DialogContent class="max-w-[min(96vw,46rem)] p-4 sm:p-5">
                <DialogHeader>
                    <DialogTitle>
                        {{ previewingInvitationTemplateCard?.label ?? 'Invitation preview' }}
                    </DialogTitle>
                    <DialogDescription>
                        Full preview of the invitation artwork.
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
                        {{ guestListInfoParty?.name ?? 'Invitee details' }}
                    </DialogTitle>
                    <DialogDescription>
                        Quick details for the entrance team.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="guestListInfoParty" class="space-y-4 py-2">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Phone</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.phone || 'No phone saved' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">RSVP</p>
                            <p class="text-sm text-neutral-800">{{ attendanceLabel(guestListInfoParty.attendanceStatus) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Invited</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.invitedAttendeesCount }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Confirmed</p>
                            <p class="text-sm text-neutral-800">{{ guestListInfoParty.confirmedAttendeesCount ?? 'Not answered yet' }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Event day</p>
                            <p class="text-sm text-neutral-800">{{ actualAttendanceLabel(guestListInfoParty.actualAttendanceStatus) }}</p>
                        </div>
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Gift</p>
                            <p class="text-sm text-neutral-800">{{ giftLabel(guestListInfoParty) }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-neutral-200 pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Table</p>
                                <p class="mt-1 text-sm text-neutral-800">
                                    {{ guestListInfoParty.tableName || 'No table assigned yet' }}
                                </p>
                            </div>
                            <Button
                                v-if="props.eventTables.length === 0"
                                variant="outline"
                                class="rounded-full px-4"
                                @click="guestListInfoDialogOpen = false; openCreateTableDialog();"
                            >
                                <Table2 class="mr-2 size-4" />
                                Add tables
                            </Button>
                        </div>

                        <div v-if="props.eventTables.length > 0" class="space-y-2">
                            <div class="grid gap-0 sm:grid-cols-[minmax(0,1fr)_auto]">
                                <NativeSelect
                                    v-model="guestListTableForm.event_table_id"
                                    class="h-11 rounded-b-none rounded-t-2xl border-b-0 sm:rounded-l-2xl sm:rounded-r-none sm:rounded-t-none sm:border-b sm:border-r-0"
                                >
                                    <NativeSelectOption value="">No table yet</NativeSelectOption>
                                    <NativeSelectOption
                                        v-for="table in guestListSelectableTables"
                                        :key="table.id"
                                        :value="String(table.id)"
                                        :disabled="!table.selectable"
                                    >
                                        {{ table.name }} · {{ table.remainingSeats }} seats left
                                    </NativeSelectOption>
                                </NativeSelect>
                                <Button
                                    class="h-11 rounded-t-none rounded-b-2xl px-4 sm:rounded-l-none sm:rounded-r-2xl sm:rounded-t-none"
                                    :disabled="quickSavingGuestId === guestListInfoParty.id"
                                    @click="saveGuestListTableAssignment"
                                >
                                    Save table
                                </Button>
                            </div>
                            <p class="text-xs text-neutral-500">
                                Full tables stay locked until seats open up.
                            </p>
                            <p v-if="guestListTableForm.errors.event_table_id" class="text-sm text-rose-600">
                                {{ guestListTableForm.errors.event_table_id }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1 border-t border-neutral-200 pt-4">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">Notes</p>
                        <p class="text-sm text-neutral-800">{{ guestListInfoParty.notes || 'No notes added' }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete invitee?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This removes the invitee record and its ledger details from this event.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction class="bg-rose-600 hover:bg-rose-700" @click="deleteGuestParty">
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
