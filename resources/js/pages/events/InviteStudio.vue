<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Copy, ExternalLink, Eye, Printer, Save, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import InvitationSheet from '@/components/invitations/InvitationSheet.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
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
    invitationSheetThemes,
    normalizeInvitationSheetTheme,
} from '@/lib/invitation-sheet-themes';
import { readInviteStudioDraft, writeInviteStudioDraft } from '@/lib/invite-studio-draft';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    type: string;
};

type EventLinks = {
    inviteStudio: string;
    inviteStudioPreview: string;
    invitationSettingsUpdate: string;
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
    publicInvitationUrl: string;
    invitationPreview: InvitationPreviewPayload;
}>();

const { t } = useTranslations();
const configureOpen = ref(false);

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
    template: normalizeInvitationSheetTheme(props.eventInvitationSettings.template),
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

const applyDraft = (draft: ReturnType<typeof readInviteStudioDraft>): void => {
    if (draft === null) {
        return;
    }

    if (draft.template !== undefined) {
        invitationSettingsForm.template = normalizeInvitationSheetTheme(draft.template);
    }

    if (draft.headline !== undefined) {
        invitationSettingsForm.headline = draft.headline;
    }

    if (draft.message !== undefined) {
        invitationSettingsForm.message = draft.message;
    }

    if (draft.closing !== undefined) {
        invitationSettingsForm.closing = draft.closing;
    }

    if (draft.contactPhone !== undefined) {
        invitationSettingsForm.contact_phone = draft.contactPhone;
    }

    if (draft.publicRsvpEnabled !== undefined) {
        invitationSettingsForm.public_rsvp_enabled = draft.publicRsvpEnabled;
    }

    if (draft.content !== undefined) {
        invitationSettingsForm.content.partner_one_name = draft.content.partnerOneName;
        invitationSettingsForm.content.partner_two_name = draft.content.partnerTwoName;
        invitationSettingsForm.content.family_name = draft.content.familyName;
        invitationSettingsForm.content.show_family_name = draft.content.showFamilyName;
        invitationSettingsForm.content.bride_parents = draft.content.brideParents;
        invitationSettingsForm.content.groom_parents = draft.content.groomParents;
        invitationSettingsForm.content.godparents = draft.content.godparents;
        invitationSettingsForm.content.date_text = draft.content.dateText;
        invitationSettingsForm.content.venue_text = draft.content.venueText;
    }

    if (draft.visibility !== undefined) {
        invitationSettingsForm.visibility.couple = draft.visibility.couple;
        invitationSettingsForm.visibility.parents = draft.visibility.parents;
        invitationSettingsForm.visibility.godparents = draft.visibility.godparents;
        invitationSettingsForm.visibility.date = draft.visibility.date;
        invitationSettingsForm.visibility.venue = draft.visibility.venue;
        invitationSettingsForm.visibility.contact_phone = draft.visibility.contactPhone;
    }
};

onMounted(() => {
    applyDraft(readInviteStudioDraft(props.currentEvent.id));
});

watch(
    () => ({
        template: invitationSettingsForm.template,
        headline: invitationSettingsForm.headline,
        message: invitationSettingsForm.message,
        closing: invitationSettingsForm.closing,
        contactPhone: invitationSettingsForm.contact_phone,
        publicRsvpEnabled: invitationSettingsForm.public_rsvp_enabled,
        content: {
            partnerOneName: invitationSettingsForm.content.partner_one_name,
            partnerTwoName: invitationSettingsForm.content.partner_two_name,
            familyName: invitationSettingsForm.content.family_name,
            showFamilyName: invitationSettingsForm.content.show_family_name,
            brideParents: invitationSettingsForm.content.bride_parents,
            groomParents: invitationSettingsForm.content.groom_parents,
            godparents: invitationSettingsForm.content.godparents,
            dateText: invitationSettingsForm.content.date_text,
            venueText: invitationSettingsForm.content.venue_text,
        },
        visibility: {
            couple: invitationSettingsForm.visibility.couple,
            parents: invitationSettingsForm.visibility.parents,
            godparents: invitationSettingsForm.visibility.godparents,
            date: invitationSettingsForm.visibility.date,
            venue: invitationSettingsForm.visibility.venue,
            contactPhone: invitationSettingsForm.visibility.contact_phone,
        },
    }),
    (draft) => {
        writeInviteStudioDraft(props.currentEvent.id, draft);
    },
    { deep: true, immediate: true },
);

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

const invitationPresentation = computed(() => composeInvitationPaperPresentation({
    eventName: props.currentEvent.name,
    eventType: props.currentEvent.type,
    headline: invitationSettingsForm.headline || props.currentEvent.name,
    content: invitationStudioContent.value,
    visibility: invitationStudioVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const invitationFooterMeta = computed(() => resolveInvitationFooterMeta({
    defaultDateLabel: props.invitationPreview.eventDetails.dateLabel,
    defaultVenueAddress: props.invitationPreview.eventDetails.venueAddress,
    contactPhone: invitationSettingsForm.contact_phone || null,
    content: invitationStudioContent.value,
    visibility: invitationStudioVisibility.value,
    weddingDetails: props.invitationPreview.eventDetails.weddingDetails,
}));

const saveInvitationSettings = (): void => {
    invitationSettingsForm.patch(props.eventLinks.invitationSettingsUpdate, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(t('guests.messages.invitation_saved'));
        },
    });
};

const openLiveInvitation = (): void => {
    window.open(props.publicInvitationUrl, '_blank', 'noopener,noreferrer');
};

const openInvitationPreview = (): void => {
    window.open(props.eventLinks.inviteStudioPreview, '_blank', 'noopener,noreferrer');
};

const printLiveInvitation = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const previewUrl = new URL(props.eventLinks.inviteStudioPreview, window.location.origin);
    previewUrl.searchParams.set('print', '1');

    window.open(previewUrl.toString(), '_blank', 'noopener,noreferrer');
};

const copyInvitationLink = async (): Promise<void> => {
    try {
        await navigator.clipboard.writeText(props.publicInvitationUrl);
        toast.success(t('guests.messages.label_copied', { label: t('guests.invitation.preview_link_label') }));
    } catch {
        toast.error(t('guests.messages.copy_label_failed', { label: t('guests.invitation.preview_link_label').toLowerCase() }));
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${currentEvent.name} · ${t('app.nav.invite_studio')}`" />

        <div class="flex h-[calc(100svh-4rem)] flex-col gap-3 overflow-hidden p-3 md:p-4">
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-for="theme in invitationSheetThemes"
                        :key="theme.id"
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-medium transition"
                        :class="invitationSettingsForm.template === theme.id ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-300 bg-white/78 text-neutral-700 hover:border-neutral-500'"
                        @click="invitationSettingsForm.template = theme.id"
                    >
                        {{ theme.label }}
                    </button>
                </div>

                <Button variant="outline" class="ml-auto rounded-full" @click="configureOpen = true">
                    <SlidersHorizontal class="size-4" />
                    {{ t('guests.invitation.configure') }}
                </Button>

                <div class="inline-flex items-center gap-1 rounded-full border border-neutral-300 bg-white/82 p-1">
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :aria-label="t('guests.actions.open_preview')"
                        @click="openInvitationPreview"
                    >
                        <Eye class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :aria-label="t('guests.invitation.open_live')"
                        @click="openLiveInvitation"
                    >
                        <ExternalLink class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :aria-label="t('guests.invitation.copy_live')"
                        @click="copyInvitationLink"
                    >
                        <Copy class="size-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        class="rounded-full"
                        :aria-label="t('guests.invitation.print_live')"
                        @click="printLiveInvitation"
                    >
                        <Printer class="size-4" />
                    </Button>
                    <Button
                        size="icon-sm"
                        class="rounded-full"
                        :aria-label="t('guests.invitation.save_changes')"
                        :disabled="invitationSettingsForm.processing"
                        @click="saveInvitationSettings"
                    >
                        <Save class="size-4" />
                    </Button>
                </div>
            </div>

            <section class="flex min-h-0 flex-1 items-center justify-center overflow-hidden">
                <InvitationSheet
                    class="block h-full max-h-full w-auto max-w-full"
                    :template="invitationSettingsForm.template"
                    :guest-label="t('guests.invitation.preview_guest_label')"
                    :logo-url="invitationPreview.branding.logoUrl"
                    :lead-in="invitationPresentation.leadIn"
                    :title="invitationPresentation.title"
                    :message="invitationSettingsForm.message || t('guests.invitation.default_message')"
                    :closing="invitationSettingsForm.closing || t('guests.invitation.default_closing')"
                    :detail-lines="invitationPresentation.detailLines"
                    :date-label="invitationFooterMeta.dateLabel"
                    :venue-address="invitationFooterMeta.venueAddress"
                    :contact-phone="invitationFooterMeta.contactPhone"
                />
            </section>

            <Sheet v-model:open="configureOpen">
                <SheetContent side="right" class="w-full overflow-y-auto border-l border-neutral-200 bg-[#fcfaf7] sm:max-w-md">
                    <SheetHeader class="space-y-2 border-b border-neutral-200 px-6 py-5">
                        <SheetTitle class="text-left text-xl font-semibold text-neutral-950">
                            {{ t('guests.invitation.configure') }}
                        </SheetTitle>
                        <SheetDescription class="text-left text-sm leading-6 text-neutral-600">
                            {{ t('guests.invitation.configure_description') }}
                        </SheetDescription>
                    </SheetHeader>

                    <div class="space-y-5 px-6 py-5">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.partner_one_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.partner_one_name" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.partner_one_name']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.partner_two_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.partner_two_name" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.partner_two_name']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.family_name_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.family_name" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.family_name']" />
                        </div>

                        <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                            <span class="pr-4 text-sm text-neutral-700">
                                {{ t('guests.invitation.show_family_name') }}
                            </span>
                            <Switch v-model="invitationSettingsForm.content.show_family_name" />
                        </label>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.lead_in_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.headline" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors.headline" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.message_label') }}
                            </label>
                            <Textarea
                                v-model="invitationSettingsForm.message"
                                rows="5"
                                :placeholder="t('guests.invitation.message_placeholder')"
                                class="min-h-28 rounded-[1.6rem]"
                            />
                            <InputError :message="invitationSettingsForm.errors.message" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.closing_label') }}
                            </label>
                            <Textarea
                                v-model="invitationSettingsForm.closing"
                                rows="3"
                                :placeholder="t('guests.invitation.closing_placeholder')"
                                class="min-h-20 rounded-[1.6rem]"
                            />
                            <InputError :message="invitationSettingsForm.errors.closing" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.date_text_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.date_text" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.date_text']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.venue_text_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.venue_text" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.venue_text']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.contact_phone') }}
                            </label>
                            <Input v-model="invitationSettingsForm.contact_phone" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors.contact_phone" />
                        </div>

                        <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                            <span class="pr-4 text-sm text-neutral-700">
                                {{ t('guests.invitation.public_rsvp_enabled') }}
                            </span>
                            <Switch v-model="invitationSettingsForm.public_rsvp_enabled" />
                        </label>

                        <div class="grid gap-3">
                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_date') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.date" />
                            </label>

                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_venue') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.venue" />
                            </label>

                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_contact_phone') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.contact_phone" />
                            </label>
                        </div>

                        <div class="border-t border-neutral-200 pt-5">
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.extras_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.extras_description') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.bride_parents_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.bride_parents" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.bride_parents']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.groom_parents_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.groom_parents" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.groom_parents']" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.godparents_label') }}
                            </label>
                            <Input v-model="invitationSettingsForm.content.godparents" class="rounded-2xl" />
                            <InputError :message="invitationSettingsForm.errors['content.godparents']" />
                        </div>

                        <div class="grid gap-3">
                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_family_lines') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.parents" />
                            </label>

                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_godparents') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.godparents" />
                            </label>
                        </div>
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
