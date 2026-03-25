<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Clock3,
    Copy,
    Download,
    ExternalLink,
    Import,
    Pencil,
    Phone,
    Printer,
    Search,
    ScrollText,
    SendHorizontal,
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
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    retentionEndsAt?: string | null;
};

type EventLinks = {
    guests: string;
    guestReport: string;
    guestPartiesStore: string;
    guestPartiesImport: string;
    guestInvitationsBulkUpdate: string;
    invitationSettingsUpdate: string;
};

type EventInvitationSettings = {
    template: 'classic' | 'floral' | 'midnight';
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
    giftType: 'money' | 'gift' | null;
    giftCurrency: 'EUR' | 'GBP' | 'RON' | null;
    giftAmount: string | null;
    guestNames: string | null;
    mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
    responseNotes: string | null;
    inviteUrl: string;
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

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    eventInvitationSettings: EventInvitationSettings;
    publicInvitationUrl: string;
    guestLedgerExportUrl: string;
    guestPartyStats: GuestPartyStats;
    guestParties: GuestParty[];
}>();

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
const deleteDialogOpen = ref(false);
const activeGuestParty = ref<GuestParty | null>(null);
const savingInvitationSettings = ref(false);
const activeSection = ref<'families' | 'invitation' | 'ledger'>('families');
const expandedGuestPartyId = ref<number | null>(null);
const selectedGuestIds = ref<number[]>([]);
const guestSearch = ref('');
const guestFilter = ref<'all' | 'needing_reply' | 'accepted' | 'declined' | 'present' | 'absent' | 'not_sent' | 'responded' | 'no_gift'>('all');

const guestForm = useForm({
    name: '',
    phone: '',
    invited_attendees_count: 1,
    confirmed_attendees_count: '',
    attendance_status: 'pending',
    actual_attendees_count: '',
    actual_attendance_status: 'unknown',
    notes: '',
    invitation_status: 'draft',
    invitation_delivery_channel: '',
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
    action: 'mark_sent_online' as 'mark_delivered_in_person' | 'mark_sent_online',
    delivery_channel: 'other' as 'in_person' | 'phone' | 'whatsapp' | 'facebook' | 'public_link' | 'other',
});

const isEditing = computed(() => activeGuestParty.value !== null);
const showGiftAmount = computed(() => guestForm.gift_type === 'money');
const showConfirmedCount = computed(() => guestForm.attendance_status === 'accepted');
const showActualCount = computed(() => guestForm.actual_attendance_status === 'present');
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
const allGuestsSelected = computed(() => filteredGuestParties.value.length > 0 && filteredGuestParties.value.every((party) => selectedGuestIds.value.includes(party.id)));
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

const invitationTemplateCards = [
    {
        id: 'classic',
        label: 'Classic',
        summary: 'Warm cream paper, elegant spacing, and a traditional feel for formal families.',
        artClass: 'border-amber-200 bg-[linear-gradient(135deg,rgba(255,251,235,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top,rgba(217,119,6,0.18),transparent_50%)] text-neutral-900',
        accentClass: 'bg-amber-500/15 text-amber-700',
    },
    {
        id: 'floral',
        label: 'Floral',
        summary: 'Soft rose tones with romantic energy for weddings, baptisms, and family celebrations.',
        artClass: 'border-rose-200 bg-[linear-gradient(160deg,rgba(255,241,242,0.98),rgba(255,255,255,0.95)),radial-gradient(circle_at_top_right,rgba(244,114,182,0.22),transparent_45%)] text-neutral-900',
        accentClass: 'bg-rose-500/15 text-rose-700',
    },
    {
        id: 'midnight',
        label: 'Midnight',
        summary: 'Dark, polished, and modern with a richer stage-like mood for bold invitation pages.',
        artClass: 'border-slate-800 bg-[linear-gradient(160deg,rgba(15,23,42,0.98),rgba(30,41,59,0.96)),radial-gradient(circle_at_top,rgba(234,179,8,0.18),transparent_42%)] text-white',
        accentClass: 'bg-white/10 text-white/80',
    },
] as const;

const statCards = computed(() => [
    {
        label: 'Parties',
        value: props.guestPartyStats.partyCount,
        detail: 'Families on the list',
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
        label: 'Families',
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

const openCreateDialog = (): void => {
    activeGuestParty.value = null;
    guestForm.reset();
    guestForm.clearErrors();
    guestForm.attendance_status = 'pending';
    guestForm.invitation_status = 'draft';
    guestForm.invited_attendees_count = 1;
    guestForm.gift_currency = 'EUR';
    guestForm.actual_attendance_status = 'unknown';
    guestDialogOpen.value = true;
};

const openEditDialog = (party: GuestParty): void => {
    activeGuestParty.value = party;
    guestForm.clearErrors();
    guestForm.name = party.name;
    guestForm.phone = party.phone ?? '';
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
    guestDialogOpen.value = true;
};

const confirmDelete = (party: GuestParty): void => {
    activeGuestParty.value = party;
    deleteDialogOpen.value = true;
};

const saveGuestParty = (): void => {
    const payload = {
        ...guestForm.data(),
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
            guestForm.actual_attendance_status = 'unknown';
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

const updateInvitationDelivery = (
    guestPartyIds: number[],
    action: 'mark_delivered_in_person' | 'mark_sent_online',
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

const shareGuestInvite = async (party: GuestParty): Promise<void> => {
    const text = invitationMessageForParty(party);

    try {
        if (navigator.share) {
            await navigator.share({
                title: props.currentEvent.name,
                text,
                url: party.inviteUrl,
            });
        } else {
            await navigator.clipboard.writeText(text);
            toast.success(`${party.name} invitation copied.`);
        }

        updateInvitationDelivery([party.id], 'mark_sent_online', 'other');
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }

        toast.error(`Could not share ${party.name}'s invitation.`);
    }
};

const shareSelectedInvites = async (): Promise<void> => {
    if (selectedGuestParties.value.length === 0) {
        toast.error('Select at least one guest party first.');

        return;
    }

    const text = invitationMessageForParties(selectedGuestParties.value);

    try {
        if (navigator.share) {
            await navigator.share({
                title: `${props.currentEvent.name} invitations`,
                text,
            });
        } else {
            await navigator.clipboard.writeText(text);
            toast.success('Selected invitation bundle copied.');
        }

        updateInvitationDelivery(selectedGuestParties.value.map((party) => party.id), 'mark_sent_online', 'other');
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') {
            return;
        }

        toast.error('Could not share the selected invitations.');
    }
};

const copySelectedInvites = async (): Promise<void> => {
    if (selectedGuestParties.value.length === 0) {
        toast.error('Select at least one guest party first.');

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
</script>

<template>
    <Head title="Guests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-5 p-4 md:p-6">
            <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
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
                            Work on one thing at a time: families, invitation, or ledger.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button v-if="activeSection === 'families'" variant="outline" class="h-10 rounded-full px-4" @click="importDialogOpen = true">
                            <Import class="mr-2 size-4" />
                            Import
                        </Button>
                        <Button v-if="activeSection === 'families'" class="h-10 rounded-full px-4" @click="openCreateDialog">
                            <UserPlus class="mr-2 size-4" />
                            Add family
                        </Button>
                        <Button v-if="activeSection === 'ledger'" variant="outline" class="h-10 rounded-full px-4" @click="exportGuestLedger">
                            <Download class="mr-2 size-4" />
                            Export
                        </Button>
                        <Button v-if="activeSection === 'ledger'" class="h-10 rounded-full px-4" @click="openGuestReport">
                            <Printer class="mr-2 size-4" />
                            Report
                        </Button>
                    </div>
                </div>

                <div class="mt-4 grid gap-2 sm:grid-cols-3">
                    <button
                        type="button"
                        :class="[
                            'flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition',
                            activeSection === 'families' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700',
                        ]"
                        @click="activeSection = 'families'"
                    >
                        <div>
                            <p class="text-sm font-semibold">Families</p>
                            <p :class="activeSection === 'families' ? 'text-xs text-white/70' : 'text-xs text-neutral-500'">
                                Main working list
                            </p>
                        </div>
                        <Users class="size-4" />
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition',
                            activeSection === 'invitation' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700',
                        ]"
                        @click="activeSection = 'invitation'"
                    >
                        <div>
                            <p class="text-sm font-semibold">Invitation</p>
                            <p :class="activeSection === 'invitation' ? 'text-xs text-white/70' : 'text-xs text-neutral-500'">
                                Message and public link
                            </p>
                        </div>
                        <ScrollText class="size-4" />
                    </button>
                    <button
                        type="button"
                        :class="[
                            'flex items-center justify-between rounded-2xl border px-4 py-3 text-left transition',
                            activeSection === 'ledger' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-200 bg-white text-neutral-700',
                        ]"
                        @click="activeSection = 'ledger'"
                    >
                        <div>
                            <p class="text-sm font-semibold">Ledger</p>
                            <p :class="activeSection === 'ledger' ? 'text-xs text-white/70' : 'text-xs text-neutral-500'">
                                Gifts, attendance, export
                            </p>
                        </div>
                        <Wallet class="size-4" />
                    </button>
                </div>
            </section>

            <section v-if="activeSection === 'families'" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="item in familyOverview"
                        :key="item.label"
                        class="rounded-2xl border border-neutral-200 bg-white px-4 py-3 shadow-sm"
                    >
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">
                            {{ item.label }}
                        </p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-neutral-950">
                            {{ item.value }}
                        </p>
                    </div>
                </div>

                <section class="rounded-3xl border border-neutral-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-neutral-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-neutral-950">
                                Families
                            </h2>
                            <p class="text-sm text-neutral-600">
                                {{ guestParties.length }} total records
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
                                <Input v-model="guestSearch" class="pl-9" placeholder="Search family, phone, or notes" />
                            </div>
                            <NativeSelect v-model="guestFilter">
                                <NativeSelectOption value="all">All guest parties</NativeSelectOption>
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
                            No guest parties yet
                        </h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-neutral-600">
                            Start with one family manually or paste a list like <span class="font-medium text-neutral-900">Familia Popescu - 0722...</span>.
                        </p>
                        <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                            <Button class="rounded-full px-5" @click="openCreateDialog">
                                Add first guest party
                            </Button>
                            <Button variant="outline" class="rounded-full px-5" @click="importDialogOpen = true">
                                Paste or upload a list
                            </Button>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-neutral-200">
                        <div class="flex flex-wrap gap-2 px-5 py-4">
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
                                No guest parties match this filter
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
                                        <span>Invited {{ party.invitedAttendeesCount }}</span>
                                        <span v-if="party.confirmedAttendeesCount !== null">Confirmed {{ party.confirmedAttendeesCount }}</span>
                                        <span v-if="party.actualAttendeesCount !== null">Came {{ party.actualAttendeesCount }}</span>
                                        <span>{{ giftLabel(party) }}</span>
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
                                class="mt-4 grid gap-3 rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4 md:grid-cols-2"
                            >
                                <div class="space-y-2 text-sm text-neutral-600">
                                    <p><span class="font-medium text-neutral-900">Opens:</span> {{ party.invitationOpenCount }}</p>
                                    <p><span class="font-medium text-neutral-900">First open:</span> {{ formatDateTime(party.invitationFirstOpenedAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Last open:</span> {{ formatDateTime(party.invitationLastOpenedAt) }}</p>
                                    <p><span class="font-medium text-neutral-900">Last IP:</span> {{ party.invitationLastOpenedIp || 'Not captured yet' }}</p>
                                    <p><span class="font-medium text-neutral-900">Delivery:</span> {{ party.invitationDeliveryChannel || 'Not set' }}</p>
                                    <p><span class="font-medium text-neutral-900">Delivered:</span> {{ formatDateTime(party.invitationDeliveredAt) }}</p>
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

                                <div class="flex flex-wrap gap-2 md:col-span-2">
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

            <section v-else-if="activeSection === 'invitation'" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-neutral-950">
                                Invitation setup
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Keep it simple, save it once, then share family links.
                            </p>
                        </div>
                        <div class="rounded-full bg-neutral-100 p-2 text-neutral-700">
                            <ScrollText class="size-4" />
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Template
                            </label>
                            <div class="grid gap-2 sm:grid-cols-3">
                                <button
                                    v-for="template in invitationTemplateCards"
                                    :key="template.id"
                                    type="button"
                                    :class="[
                                        'rounded-2xl border px-4 py-3 text-left transition',
                                        template.artClass,
                                        invitationSettingsForm.template === template.id
                                            ? 'ring-2 ring-neutral-950 shadow-sm'
                                            : 'opacity-80 hover:opacity-100',
                                    ]"
                                    @click="invitationSettingsForm.template = template.id"
                                >
                                    <p class="text-sm font-semibold">
                                        {{ template.label }}
                                    </p>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-700">
                                Headline
                            </label>
                            <Input v-model="invitationSettingsForm.headline" placeholder="You're invited..." />
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

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    Contact phone
                                </label>
                                <Input v-model="invitationSettingsForm.contact_phone" placeholder="Optional" />
                            </div>

                            <label class="flex items-center gap-3 rounded-2xl border border-neutral-200 px-4 py-3 text-sm text-neutral-700">
                                <input
                                    v-model="invitationSettingsForm.public_rsvp_enabled"
                                    type="checkbox"
                                    class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                >
                                Public RSVP link enabled
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end border-t border-neutral-200 pt-4">
                        <Button
                            class="rounded-full px-5"
                            :disabled="savingInvitationSettings || invitationSettingsForm.processing"
                            @click="saveInvitationSettings"
                        >
                            <SendHorizontal class="mr-2 size-4" />
                            Save invitation
                        </Button>
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-neutral-950">
                        Public RSVP link
                    </h2>
                    <p class="mt-1 text-sm text-neutral-600">
                        Share this if a family is not already in the list.
                    </p>

                    <div class="mt-4 rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600 break-all">
                        {{ publicInvitationUrl }}
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
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
            </section>

            <section v-else class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    <div
                        v-for="stat in statCards"
                        :key="stat.label"
                        class="rounded-2xl border border-neutral-200 bg-white px-4 py-3 shadow-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500">
                                    {{ stat.label }}
                                </p>
                                <p class="mt-2 text-xl font-semibold tracking-tight text-neutral-950">
                                    {{ stat.value }}
                                </p>
                                <p class="mt-1 text-xs text-neutral-500">
                                    {{ stat.detail }}
                                </p>
                            </div>
                            <div class="rounded-full bg-neutral-100 p-2 text-neutral-700">
                                <component :is="stat.icon" class="size-4" />
                            </div>
                        </div>
                    </div>
                </div>

                <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold text-neutral-950">
                        Ledger tools
                    </h2>
                    <p class="mt-1 text-sm text-neutral-600">
                        Use the printable report for a full event-day view, then export the ledger before retention ends.
                    </p>

                    <div
                        v-if="retentionReminder"
                        class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                    >
                        Retention ends on {{ retentionReminder.dateLabel }}.
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <Button class="rounded-full px-5" @click="openGuestReport">
                            <Printer class="mr-2 size-4" />
                            Open report
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="exportGuestLedger">
                            <Download class="mr-2 size-4" />
                            Export CSV
                        </Button>
                    </div>
                </section>
            </section>
        </div>

        <Dialog :open="guestDialogOpen" @update:open="guestDialogOpen = $event">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? 'Edit guest party' : 'Add guest party' }}
                    </DialogTitle>
                    <DialogDescription>
                        Track the family name, attendance, invitation status, and gift notes in one record.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-2 md:grid-cols-2">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Family / Name
                        </label>
                        <Input v-model="guestForm.name" placeholder="Familia Popescu or James Webb" />
                        <p v-if="guestForm.errors.name" class="text-sm text-rose-600">
                            {{ guestForm.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Phone
                        </label>
                        <Input v-model="guestForm.phone" placeholder="07..." />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Invited attendees
                        </label>
                        <Input v-model="guestForm.invited_attendees_count" type="number" min="1" max="1000" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-700">
                            Attendance status
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

                <DialogFooter class="gap-2">
                    <Button variant="outline" class="rounded-full px-5" @click="guestDialogOpen = false">
                        Cancel
                    </Button>
                    <Button class="rounded-full px-5" :disabled="guestForm.processing" @click="saveGuestParty">
                        {{ isEditing ? 'Save changes' : 'Add guest party' }}
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

        <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete guest party?</AlertDialogTitle>
                    <AlertDialogDescription>
                        This removes the guest party record and its ledger details from this event.
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
