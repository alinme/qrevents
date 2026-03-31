<script setup lang="ts">
import { Copy, Download, ExternalLink, ChevronLeft, ChevronRight, Clock3, ScrollText, SendHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
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
    showAdvanced: boolean;
    publicInvitationUrl: string;
    logoUrl: string | null;
    eventDateLabel: string;
    eventVenueAddress: string | null;
    activeInvitationPresentation: {
        leadIn: string;
        title: string;
        detailLines: string[];
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
    'update:showAdvanced': [value: boolean];
    'update:contactPhone': [value: string];
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
        <div class="flex flex-col gap-5 xl:grid xl:grid-cols-[minmax(0,1fr)_320px] xl:items-start">
            <div class="space-y-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-neutral-950">
                            {{ t('guests.sections.invitation.title') }}
                        </h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{ t('guests.sections.invitation.description') }}
                        </p>
                    </div>
                    <div class="rounded-full bg-neutral-100 p-2 text-neutral-700">
                        <ScrollText class="size-4" />
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-medium text-neutral-700">
                            {{ t('guests.invitation.template') }}
                        </label>
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
                        class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
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
                                <p class="mt-2 text-sm font-semibold">
                                    {{ template.label }}
                                </p>
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
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-neutral-700">
                        {{ t('guests.invitation.title_label') }}
                    </label>
                    <Input :model-value="headline" :placeholder="currentEventName" @update:model-value="emit('update:headline', String($event))" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-neutral-700">
                        {{ t('guests.invitation.message_label') }}
                    </label>
                    <Textarea
                        :model-value="message"
                        rows="3"
                        :placeholder="t('guests.invitation.message_placeholder')"
                        @update:model-value="emit('update:message', String($event))"
                    />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-neutral-700">
                        {{ t('guests.invitation.closing_label') }}
                    </label>
                    <Textarea
                        :model-value="closing"
                        rows="2"
                        :placeholder="t('guests.invitation.closing_placeholder')"
                        @update:model-value="emit('update:closing', String($event))"
                    />
                </div>

                <div class="flex flex-col gap-3 rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-3 text-sm text-neutral-700">
                        <input
                            :checked="publicRsvpEnabled"
                            type="checkbox"
                            class="size-4 rounded border-neutral-300 text-neutral-950 focus:ring-neutral-950"
                            @change="emit('update:publicRsvpEnabled', ($event.target as HTMLInputElement).checked)"
                        >
                        {{ t('guests.invitation.public_rsvp_enabled') }}
                    </label>

                    <Button
                        variant="outline"
                        class="rounded-full px-4"
                        @click="emit('update:showAdvanced', !showAdvanced)"
                    >
                        {{
                            showAdvanced
                                ? t('guests.actions.hide_advanced')
                                : t('guests.actions.advanced')
                        }}
                    </Button>
                </div>

                <div v-if="showAdvanced" class="space-y-2 rounded-2xl border border-neutral-200 px-4 py-4">
                    <label class="text-sm font-medium text-neutral-700">
                        {{ t('guests.invitation.contact_phone') }}
                    </label>
                    <Input
                        :model-value="contactPhone"
                        :placeholder="t('guests.shared.optional')"
                        @update:model-value="emit('update:contactPhone', String($event))"
                    />
                </div>
            </div>

            <div class="space-y-4 border-t border-neutral-200 pt-4 xl:border-l xl:border-t-0 xl:pl-5 xl:pt-0">
                <div class="flex flex-wrap items-center gap-2">
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

                <InvitationPaper
                    :template="selectedTemplate"
                    :event-name="activeInvitationPresentation.leadIn"
                    :logo-url="logoUrl"
                    :guest-label="t('guests.invitation.preview_guest_label')"
                    :headline="activeInvitationPresentation.title"
                    :message="message || t('guests.invitation.default_message')"
                    :closing="closing || t('guests.invitation.default_closing')"
                    :detail-lines="activeInvitationPresentation.detailLines"
                    :contact-phone="showAdvanced ? contactPhone : null"
                    :date-label="eventDateLabel"
                    :venue-address="eventVenueAddress"
                    mode="preview"
                />

                <div class="space-y-4 border-t border-neutral-200 pt-4">
                    <p class="text-sm font-semibold text-neutral-950">
                        {{ t('guests.invitation.campaign_title') }}
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
                </div>

                <div class="space-y-4 border-t border-neutral-200 pt-4">
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

                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-sm break-all text-neutral-600">
                        {{ publicInvitationUrl }}
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
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
                </div>

                <div class="space-y-3 border-t border-neutral-200 pt-4">
                    <p class="text-sm font-semibold text-neutral-950">
                        {{ t('guests.invitation.recent_activity_title') }}
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
                        {{ t('guests.invitation.recent_activity_empty') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-5 flex justify-end border-t border-neutral-200 pt-4">
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
    </section>
</template>
