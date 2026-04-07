<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Copy, ExternalLink, Printer, Save } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import InputError from '@/components/InputError.vue';
import InvitationSheet from '@/components/invitations/InvitationSheet.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
    type: string;
};

type EventLinks = {
    inviteStudio: string;
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

const liveInvitationPrintUrl = computed(() => {
    const separator = props.publicInvitationUrl.includes('?') ? '&' : '?';

    return `${props.publicInvitationUrl}${separator}print=1`;
});

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

const printLiveInvitation = (): void => {
    window.open(liveInvitationPrintUrl.value, '_blank', 'noopener,noreferrer');
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

        <div class="grid gap-6 p-4 lg:grid-cols-[24rem_minmax(0,1fr)] lg:items-start md:p-6">
            <section class="rounded-[2rem] border border-neutral-200/80 bg-white/90 shadow-sm backdrop-blur">
                <div class="space-y-4 p-5">
                    <div class="space-y-2">
                        <h1 class="text-xl font-semibold tracking-tight text-neutral-950">
                            {{ t('guests.invitation.studio_title') }}
                        </h1>
                        <p class="text-sm leading-6 text-neutral-600">
                            {{ t('guests.invitation.simple_description') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            class="rounded-full"
                            :aria-label="t('guests.invitation.open_live')"
                            @click="openLiveInvitation"
                        >
                            <ExternalLink class="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            class="rounded-full"
                            :aria-label="t('guests.invitation.copy_live')"
                            @click="copyInvitationLink"
                        >
                            <Copy class="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            class="rounded-full"
                            :aria-label="t('guests.invitation.print_live')"
                            @click="printLiveInvitation"
                        >
                            <Printer class="size-4" />
                        </Button>
                        <Button
                            class="ml-auto rounded-full px-4"
                            :disabled="invitationSettingsForm.processing"
                            @click="saveInvitationSettings"
                        >
                            <Save class="size-4" />
                            {{ t('guests.invitation.save_changes') }}
                        </Button>
                    </div>
                </div>

                <div class="border-t border-neutral-200/80 p-5">
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.template') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.style_picker_description') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto pb-1">
                            <div class="flex min-w-max gap-3">
                                <button
                                    v-for="theme in invitationSheetThemes"
                                    :key="theme.id"
                                    type="button"
                                    class="group w-28 shrink-0 text-left"
                                    @click="invitationSettingsForm.template = theme.id"
                                >
                                    <span
                                        class="block overflow-hidden rounded-[1.4rem] border transition"
                                        :class="invitationSettingsForm.template === theme.id ? 'border-neutral-950 shadow-sm' : 'border-neutral-200 group-hover:border-neutral-400'"
                                    >
                                        <img
                                            :src="theme.previewUrl"
                                            :alt="theme.label"
                                            class="aspect-[1240/1748] w-full object-cover"
                                        >
                                    </span>
                                    <span class="mt-2 block text-sm font-medium text-neutral-900">
                                        {{ theme.label }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-neutral-200/80">
                    <div class="space-y-4 p-5">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.couple_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.couple_description') }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
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
                    </div>

                    <div class="space-y-4 p-5">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.copy_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.edit_description') }}
                            </p>
                        </div>

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
                    </div>

                    <div class="space-y-4 p-5">
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ t('guests.invitation.schedule_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.schedule_description_simple') }}
                            </p>
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

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
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

                            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-neutral-200 px-4 py-3 sm:col-span-2 lg:col-span-1 xl:col-span-2">
                                <span class="pr-4 text-sm text-neutral-700">
                                    {{ t('guests.invitation.show_contact_phone') }}
                                </span>
                                <Switch v-model="invitationSettingsForm.visibility.contact_phone" />
                            </label>
                        </div>
                    </div>

                    <details class="group p-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-neutral-900">
                                    {{ t('guests.invitation.extras_title') }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-600">
                                    {{ t('guests.invitation.extras_description') }}
                                </p>
                            </div>
                            <span class="text-sm text-neutral-500 transition group-open:rotate-45">+</span>
                        </summary>

                        <div class="mt-4 space-y-4">
                            <div class="grid gap-3">
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
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
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
                    </details>
                </div>
            </section>

            <section class="lg:sticky lg:top-6">
                <InvitationSheet
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
        </div>
    </AppLayout>
</template>
