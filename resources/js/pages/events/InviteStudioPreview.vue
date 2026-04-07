<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import InvitationSheet from '@/components/invitations/InvitationSheet.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import {
    composeInvitationPaperPresentation,
    resolveInvitationFooterMeta,
} from '@/lib/invitation-presentation';
import type {
    InvitationStudioContent,
    InvitationStudioVisibility,
    InvitationWeddingDetails,
} from '@/lib/invitation-presentation';
import { normalizeInvitationSheetTheme } from '@/lib/invitation-sheet-themes';
import { readInviteStudioDraft } from '@/lib/invite-studio-draft';

type EventPayload = {
    id: number;
    name: string;
    type: string;
};

type EventLinks = {
    inviteStudio: string;
};

type EventInvitationSettings = {
    template: string;
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

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
    eventInvitationSettings: EventInvitationSettings;
    invitationPreview: InvitationPreviewPayload;
}>();

const { t } = useTranslations();

const draftTemplate = ref(normalizeInvitationSheetTheme(props.eventInvitationSettings.template));
const draftHeadline = ref(props.eventInvitationSettings.headline);
const draftMessage = ref(props.eventInvitationSettings.message);
const draftClosing = ref(props.eventInvitationSettings.closing);
const draftContactPhone = ref(props.eventInvitationSettings.contactPhone ?? '');
const draftContent = ref<InvitationStudioContent>({
    ...props.eventInvitationSettings.content,
});
const draftVisibility = ref<InvitationStudioVisibility>({
    ...props.eventInvitationSettings.visibility,
});

onMounted(() => {
    const draft = readInviteStudioDraft(props.currentEvent.id);

    if (draft === null) {
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.setTimeout(() => {
                window.print();
            }, 250);
        }

        return;
    }

    if (draft.template !== undefined) {
        draftTemplate.value = normalizeInvitationSheetTheme(draft.template);
    }

    if (draft.headline !== undefined) {
        draftHeadline.value = draft.headline;
    }

    if (draft.message !== undefined) {
        draftMessage.value = draft.message;
    }

    if (draft.closing !== undefined) {
        draftClosing.value = draft.closing;
    }

    if (draft.contactPhone !== undefined) {
        draftContactPhone.value = draft.contactPhone;
    }

    if (draft.content !== undefined) {
        draftContent.value = {
            partnerOneName: draft.content.partnerOneName,
            partnerTwoName: draft.content.partnerTwoName,
            familyName: draft.content.familyName,
            showFamilyName: draft.content.showFamilyName,
            brideParents: draft.content.brideParents,
            groomParents: draft.content.groomParents,
            godparents: draft.content.godparents,
            dateText: draft.content.dateText,
            venueText: draft.content.venueText,
        };
    }

    if (draft.visibility !== undefined) {
        draftVisibility.value = {
            couple: draft.visibility.couple,
            parents: draft.visibility.parents,
            godparents: draft.visibility.godparents,
            date: draft.visibility.date,
            venue: draft.visibility.venue,
            contactPhone: draft.visibility.contactPhone,
        };
    }

    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.setTimeout(() => {
            window.print();
        }, 250);
    }
});

const invitationPresentation = computed(() => composeInvitationPaperPresentation({
    eventName: props.currentEvent.name,
    eventType: props.currentEvent.type,
    headline: draftHeadline.value || props.currentEvent.name,
    content: draftContent.value,
    visibility: draftVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const invitationFooterMeta = computed(() => resolveInvitationFooterMeta({
    defaultDateLabel: props.invitationPreview.eventDetails.dateLabel,
    defaultVenueAddress: props.invitationPreview.eventDetails.venueAddress,
    contactPhone: draftContactPhone.value || null,
    content: draftContent.value,
    visibility: draftVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const printInvitation = (): void => {
    window.print();
};
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-[linear-gradient(180deg,#fff8f2_0%,#fffdf9_52%,#f5eee7_100%)]">
        <Head :title="`${currentEvent.name} · ${t('guests.invitation.preview_guest_label')}`" />

        <div class="fixed right-4 top-4 z-20 flex items-center gap-2 print:hidden">
            <Button as="a" :href="eventLinks.inviteStudio" variant="outline" size="icon-sm" class="rounded-full bg-white/88">
                <SlidersHorizontal class="size-4" />
            </Button>
            <Button type="button" size="icon-sm" class="rounded-full" @click="printInvitation">
                <Printer class="size-4" />
            </Button>
        </div>

        <div class="flex min-h-screen items-center justify-center p-4 sm:p-8 print:p-0">
            <InvitationSheet
                class="h-[calc(100vh-2rem)] max-h-[1120px] w-auto max-w-full print:h-screen print:max-h-none print:w-full print:rounded-none print:border-0 print:shadow-none"
                :template="draftTemplate"
                :guest-label="t('guests.invitation.preview_guest_label')"
                :logo-url="invitationPreview.branding.logoUrl"
                :lead-in="invitationPresentation.leadIn"
                :title="invitationPresentation.title"
                :message="draftMessage || t('guests.invitation.default_message')"
                :closing="draftClosing || t('guests.invitation.default_closing')"
                :detail-lines="invitationPresentation.detailLines"
                :date-label="invitationFooterMeta.dateLabel"
                :venue-address="invitationFooterMeta.venueAddress"
                :contact-phone="invitationFooterMeta.contactPhone"
            />
        </div>
    </div>
</template>
