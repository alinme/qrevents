<script setup lang="ts">
import { computed } from 'vue';
import type { InvitationSheetProps } from '@/components/invitations/types';
import { resolveInvitationSheetTheme } from '@/lib/invitation-sheet-themes';

const props = withDefaults(defineProps<InvitationSheetProps & {
    template?: string | null;
}>(), {
    template: 'canva_cream',
    guestLabel: null,
    logoUrl: null,
    detailLines: () => [],
    dateLabel: null,
    venueAddress: null,
    contactPhone: null,
});

const activeTemplate = computed(() => resolveInvitationSheetTheme(props.template));
</script>

<template>
    <component
        :is="activeTemplate.component"
        :guest-label="guestLabel"
        :logo-url="logoUrl"
        :lead-in="leadIn"
        :title="title"
        :message="message"
        :closing="closing"
        :detail-lines="detailLines"
        :date-label="dateLabel"
        :venue-address="venueAddress"
        :contact-phone="contactPhone"
    />
</template>
