<script setup lang="ts">
import { Copy, Download, ExternalLink, ChevronLeft, ChevronRight, Clock3, ScrollText, SendHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import type { InvitationStudioContent, InvitationStudioVisibility } from '@/lib/invitation-presentation';
import type { InvitationTemplateId } from '@/lib/invitation-templates';

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

type InvitationActivity = {
    guestName: string;
    type: 'sent_online' | 'delivered_in_person' | 'reminded' | 'opened' | 'responded';
    deliveryChannel: string | null;
    createdAt: string | null;
    meta: Record<string, unknown>;
};

type InvitationOverflowWarning = {
    key: string;
    tone: 'warning' | 'danger';
    label: string;
    message: string;
};

defineProps<{
    currentEventName: string;
    invitationTemplateCards: Array<{
        id: InvitationTemplateId;
        label: string;
        artClass: string;
    }>;
    selectedTemplate: InvitationTemplateId;
    headline: string;
    message: string;
    closing: string;
    publicRsvpEnabled: boolean;
    contactPhone: string;
    invitationContent: InvitationStudioContent;
    invitationVisibility: InvitationStudioVisibility;
    invitationOverflowWarnings: InvitationOverflowWarning[];
    publicInvitationUrl: string;
    logoUrl: string | null;
    activeInvitationPresentation: {
        leadIn: string;
        title: string;
        detailLines: string[];
    };
    activeInvitationFooterMeta: {
        dateLabel: string | null;
        venueAddress: string | null;
        contactPhone: string | null;
    };
    invitationSummaryCards: Array<{
        label: string;
        value: string | number;
    }>;
    invitationRecentActivity: InvitationActivity[];
    savingInvitationSettings: boolean;
    invitationSettingsProcessing: boolean;
    invitationTemplatePreviewContent: (templateId: InvitationTemplateId) => InvitationTemplatePreviewContent;
    invitationHistoryLabel: (activity: InvitationActivity) => string;
    formatDateTime: (value: string | null) => string;
}>();

const emit = defineEmits<{
    'update:selectedTemplate': [value: InvitationTemplateId];
    'update:headline': [value: string];
    'update:message': [value: string];
    'update:closing': [value: string];
    'update:publicRsvpEnabled': [value: boolean];
    'update:contactPhone': [value: string];
    'update:content': [value: InvitationStudioContent];
    'update:visibility': [value: InvitationStudioVisibility];
    'preview-template': [value: InvitationTemplateId];
    'open-invite': [url: string];
    'save-preview': [];
    'copy-link': [url: string, label: string];
    'share-pending': [];
    'copy-pending': [];
    'remind-pending': [];
    'save-settings': [];
}>();

const { t } = useTranslations();
const invitationCarousel = ref<HTMLElement | null>(null);

const scrollInvitationTemplates = (direction: 'prev' | 'next'): void => {
    invitationCarousel.value?.scrollBy({
        left: direction === 'next' ? 320 : -320,
        behavior: 'smooth',
    });
};
</script>

<template>
    <section class="rounded-3xl border border-neutral-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-neutral-950">
                    {{ t('guests.invitation.studio_title') }}
                </h2>
                <p class="mt-1 text-sm text-neutral-600">
                    {{ t('guests.invitation.studio_description') }}
                </p>
            </div>
            <div class="rounded-full bg-neutral-100 p-2 text-neutral-700">
                <ScrollText class="size-4" />
            </div>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <section class="rounded-3xl border border-neutral-200 bg-neutral-50/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-neutral-500">
                                {{ t('guests.invitation.step_choose') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.choose_description') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="icon" class="rounded-full" @click="scrollInvitationTemplates('prev')">
                                <ChevronLeft class="size-4" />
                            </Button>
                            <Button variant="outline" size="icon" class="rounded-full" @click="scrollInvitationTemplates('next')">
                                <ChevronRight class="size-4" />
                            </Button>
                        </div>
                    </div>

                    <div
                        ref="invitationCarousel"
                        class="mt-4 flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                    >
                        <div
                            v-for="template in invitationTemplateCards"
                            :key="template.id"
                            :class="[
                                'w-[220px] shrink-0 snap-start rounded-2xl border p-2.5 transition',
                                template.artClass,
                                selectedTemplate === template.id
                                    ? 'ring-2 ring-neutral-950 shadow-sm'
                                    : 'hover:-translate-y-0.5 hover:shadow-sm',
                            ]"
                        >
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="emit('update:selectedTemplate', template.id)"
                            >
                                <div class="overflow-hidden rounded-xl border border-current/10 bg-white/45 shadow-sm">
                                    <div class="aspect-[210/297]">
                                        <InvitationPaper
                                            :template="template.id"
                                            :event-name="invitationTemplatePreviewContent(template.id).eventName"
                                            :logo-url="logoUrl"
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
                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold">
                                        {{ template.label }}
                                    </p>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-[0.68rem] font-medium"
                                        :class="selectedTemplate === template.id ? 'bg-neutral-950 text-white' : 'bg-white/70 text-neutral-700'"
                                    >
                                        {{ selectedTemplate === template.id ? t('guests.shared.selected') : t('guests.actions.choose') }}
                                    </span>
                                </div>
                            </button>

                            <button
                                type="button"
                                class="mt-1 text-xs font-medium text-current/70 underline underline-offset-4"
                                @click="emit('preview-template', template.id)"
                            >
                                {{ t('guests.actions.preview') }}
                            </button>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-neutral-200 p-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-neutral-500">
                            {{ t('guests.invitation.step_edit') }}
                        </p>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{ t('guests.invitation.edit_description') }}
                        </p>
                    </div>

                    <div v-if="invitationOverflowWarnings.length > 0" class="mt-4 space-y-2">
                        <div
                            v-for="warning in invitationOverflowWarnings"
                            :key="warning.key"
                            :class="[
                                'rounded-2xl border px-4 py-3 text-sm',
                                warning.tone === 'danger'
                                    ? 'border-rose-200 bg-rose-50 text-rose-800'
                                    : 'border-amber-200 bg-amber-50 text-amber-800',
                            ]"
                        >
                            <p class="font-medium">
                                {{ warning.label }}
                            </p>
                            <p class="mt-1">
                                {{ warning.message }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <div class="space-y-4 rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4">
                            <p class="text-sm font-semibold text-neutral-950">
                                {{ t('guests.invitation.copy_title') }}
                            </p>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.lead_in_label') }}
                                </label>
                                <Input :model-value="headline" :placeholder="currentEventName" @update:model-value="emit('update:headline', String($event))" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.message_label') }}
                                </label>
                                <Textarea
                                    :model-value="message"
                                    rows="4"
                                    :placeholder="t('guests.invitation.message_placeholder')"
                                    @update:model-value="emit('update:message', String($event))"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.rsvp_note_label') }}
                                </label>
                                <Textarea
                                    :model-value="closing"
                                    rows="3"
                                    :placeholder="t('guests.invitation.rsvp_note_placeholder')"
                                    @update:model-value="emit('update:closing', String($event))"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-neutral-950">
                                        {{ t('guests.invitation.couple_title') }}
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-600">
                                        {{ t('guests.invitation.couple_description') }}
                                    </p>
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm text-neutral-700">
                                    <input
                                        :checked="invitationVisibility.couple"
                                        type="checkbox"
                                        class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                        @change="emit('update:visibility', { ...invitationVisibility, couple: ($event.target as HTMLInputElement).checked })"
                                    >
                                    {{ t('guests.shared.show') }}
                                </label>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.invitation.partner_one_label') }}
                                    </label>
                                    <Input
                                        :model-value="invitationContent.partnerOneName"
                                        @update:model-value="emit('update:content', { ...invitationContent, partnerOneName: String($event) })"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-neutral-700">
                                        {{ t('guests.invitation.partner_two_label') }}
                                    </label>
                                    <Input
                                        :model-value="invitationContent.partnerTwoName"
                                        @update:model-value="emit('update:content', { ...invitationContent, partnerTwoName: String($event) })"
                                    />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.family_name_label') }}
                                </label>
                                <Input
                                    :model-value="invitationContent.familyName"
                                    :placeholder="t('guests.shared.optional')"
                                    @update:model-value="emit('update:content', { ...invitationContent, familyName: String($event) })"
                                />
                            </div>
                            <label class="inline-flex items-center gap-3 text-sm text-neutral-700">
                                <input
                                    :checked="invitationContent.showFamilyName"
                                    type="checkbox"
                                    class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                    @change="emit('update:content', { ...invitationContent, showFamilyName: ($event.target as HTMLInputElement).checked })"
                                >
                                {{ t('guests.invitation.show_family_name') }}
                            </label>
                        </div>

                        <div class="space-y-4 rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-neutral-950">
                                        {{ t('guests.invitation.parents_title') }}
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-600">
                                        {{ t('guests.invitation.parents_description') }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <label class="inline-flex items-center gap-2 text-sm text-neutral-700">
                                        <input
                                            :checked="invitationVisibility.parents"
                                            type="checkbox"
                                            class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                            @change="emit('update:visibility', { ...invitationVisibility, parents: ($event.target as HTMLInputElement).checked })"
                                        >
                                        {{ t('guests.shared.show') }}
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm text-neutral-700">
                                        <input
                                            :checked="invitationVisibility.godparents"
                                            type="checkbox"
                                            class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                            @change="emit('update:visibility', { ...invitationVisibility, godparents: ($event.target as HTMLInputElement).checked })"
                                        >
                                        {{ t('guests.invitation.show_godparents') }}
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.bride_parents_label') }}
                                </label>
                                <Input
                                    :model-value="invitationContent.brideParents"
                                    :placeholder="t('guests.shared.optional')"
                                    @update:model-value="emit('update:content', { ...invitationContent, brideParents: String($event) })"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.groom_parents_label') }}
                                </label>
                                <Input
                                    :model-value="invitationContent.groomParents"
                                    :placeholder="t('guests.shared.optional')"
                                    @update:model-value="emit('update:content', { ...invitationContent, groomParents: String($event) })"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-neutral-700">
                                    {{ t('guests.invitation.godparents_label') }}
                                </label>
                                <Input
                                    :model-value="invitationContent.godparents"
                                    :placeholder="t('guests.shared.optional')"
                                    @update:model-value="emit('update:content', { ...invitationContent, godparents: String($event) })"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 rounded-2xl border border-neutral-200 bg-neutral-50/70 p-4">
                            <p class="text-sm font-semibold text-neutral-950">
                                {{ t('guests.invitation.schedule_title') }}
                            </p>
                            <div class="space-y-2">
                                <label class="inline-flex items-center gap-3 text-sm text-neutral-700">
                                    <input
                                        :checked="invitationVisibility.date"
                                        type="checkbox"
                                        class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                        @change="emit('update:visibility', { ...invitationVisibility, date: ($event.target as HTMLInputElement).checked })"
                                    >
                                    {{ t('guests.invitation.show_date') }}
                                </label>
                                <Input
                                    :model-value="invitationContent.dateText"
                                    @update:model-value="emit('update:content', { ...invitationContent, dateText: String($event) })"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="inline-flex items-center gap-3 text-sm text-neutral-700">
                                    <input
                                        :checked="invitationVisibility.venue"
                                        type="checkbox"
                                        class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                        @change="emit('update:visibility', { ...invitationVisibility, venue: ($event.target as HTMLInputElement).checked })"
                                    >
                                    {{ t('guests.invitation.show_venue') }}
                                </label>
                                <Textarea
                                    :model-value="invitationContent.venueText"
                                    rows="3"
                                    @update:model-value="emit('update:content', { ...invitationContent, venueText: String($event) })"
                                />
                            </div>
                            <div class="rounded-2xl border border-neutral-200 bg-white px-4 py-4">
                                <label class="flex items-center gap-3 text-sm text-neutral-700">
                                    <input
                                        :checked="publicRsvpEnabled"
                                        type="checkbox"
                                        class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                        @change="emit('update:publicRsvpEnabled', ($event.target as HTMLInputElement).checked)"
                                    >
                                    {{ t('guests.invitation.public_rsvp_enabled') }}
                                </label>
                                <div class="mt-3 space-y-2">
                                    <label class="inline-flex items-center gap-3 text-sm text-neutral-700">
                                        <input
                                            :checked="invitationVisibility.contactPhone"
                                            type="checkbox"
                                            class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                                            @change="emit('update:visibility', { ...invitationVisibility, contactPhone: ($event.target as HTMLInputElement).checked })"
                                        >
                                        {{ t('guests.invitation.show_contact_phone') }}
                                    </label>
                                    <Input
                                        :model-value="contactPhone"
                                        :placeholder="t('guests.shared.optional')"
                                        @update:model-value="emit('update:contactPhone', String($event))"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-4 xl:sticky xl:top-24">
                <section class="rounded-3xl border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-neutral-500">
                        {{ t('guests.invitation.step_share') }}
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <Button class="rounded-full px-5" @click="emit('open-invite', publicInvitationUrl)">
                            <ExternalLink class="mr-2 size-4" />
                            {{ t('guests.actions.open_preview') }}
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="emit('save-preview')">
                            <Download class="mr-2 size-4" />
                            {{ t('guests.actions.save_or_print') }}
                        </Button>
                        <Button
                            variant="outline"
                            class="rounded-full px-5"
                            @click="emit('copy-link', publicInvitationUrl, t('guests.invitation.preview_link_label'))"
                        >
                            <Copy class="mr-2 size-4" />
                            {{ t('guests.actions.copy_preview_link') }}
                        </Button>
                    </div>

                    <div class="mt-4">
                        <InvitationPaper
                            :template="selectedTemplate"
                            :event-name="activeInvitationPresentation.leadIn"
                            :logo-url="logoUrl"
                            :guest-label="t('guests.invitation.preview_guest_label')"
                            :headline="activeInvitationPresentation.title"
                            :message="message || t('guests.invitation.default_message')"
                            :closing="closing || t('guests.invitation.default_closing')"
                            :detail-lines="activeInvitationPresentation.detailLines"
                            :contact-phone="activeInvitationFooterMeta.contactPhone"
                            :date-label="activeInvitationFooterMeta.dateLabel"
                            :venue-address="activeInvitationFooterMeta.venueAddress"
                            mode="preview"
                        />
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-200">
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

                    <div class="mt-4 flex flex-col gap-2">
                        <Button class="rounded-full px-5" @click="emit('share-pending')">
                            <SendHorizontal class="mr-2 size-4" />
                            {{ t('guests.actions.share_pending') }}
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="emit('copy-pending')">
                            <Copy class="mr-2 size-4" />
                            {{ t('guests.actions.copy_pending') }}
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="emit('remind-pending')">
                            <Clock3 class="mr-2 size-4" />
                            {{ t('guests.actions.remind_pending') }}
                        </Button>
                    </div>
                </section>

                <section class="rounded-3xl border border-neutral-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-neutral-950">
                                {{ t('guests.invitation.public_rsvp_title') }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('guests.invitation.public_rsvp_description') }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-medium',
                                publicRsvpEnabled
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-neutral-200 text-neutral-600',
                            ]"
                        >
                            {{ publicRsvpEnabled ? t('guests.shared.on') : t('guests.shared.off') }}
                        </span>
                    </div>

                    <div class="mt-4 rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm break-all text-neutral-600">
                        {{ publicInvitationUrl }}
                    </div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <Button
                            data-test="guest-invitation-copy-public-link"
                            class="rounded-full px-5"
                            @click="emit('copy-link', publicInvitationUrl, t('guests.invitation.public_rsvp_link_label'))"
                        >
                            <Copy class="mr-2 size-4" />
                            {{ t('guests.actions.copy_link') }}
                        </Button>
                        <Button variant="outline" class="rounded-full px-5" @click="emit('open-invite', publicInvitationUrl)">
                            <ExternalLink class="mr-2 size-4" />
                            {{ t('guests.actions.open_page') }}
                        </Button>
                    </div>
                </section>

                <section class="rounded-3xl border border-neutral-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-neutral-950">
                        {{ t('guests.invitation.recent_activity_title') }}
                    </p>

                    <div v-if="invitationRecentActivity.length > 0" class="mt-3 divide-y divide-neutral-200 rounded-2xl border border-neutral-200">
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

                    <p v-else class="mt-3 text-sm text-neutral-500">
                        {{ t('guests.invitation.recent_activity_empty') }}
                    </p>
                </section>

                <div class="flex justify-end">
                    <Button
                        data-test="guest-invitation-save"
                        class="rounded-full px-5"
                        :disabled="savingInvitationSettings || invitationSettingsProcessing"
                        @click="emit('save-settings')"
                    >
                        <SendHorizontal class="mr-2 size-4" />
                        {{ t('guests.actions.save_invitation') }}
                    </Button>
                </div>
            </div>
        </div>
    </section>
</template>
