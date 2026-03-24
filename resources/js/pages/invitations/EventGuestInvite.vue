<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarDays, CheckCircle2, Clock3, MapPin, Phone, Sparkles, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    NativeSelect,
    NativeSelectOption,
} from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';

type GuestParty = {
    name: string;
    phone: string | null;
    invitedAttendeesCount: number;
    confirmedAttendeesCount: number | null;
    attendanceStatus: 'pending' | 'accepted' | 'declined';
    guestNames: string | null;
    mealPreference: 'standard' | 'vegetarian' | 'vegan' | 'halal' | 'other' | null;
    responseNotes: string | null;
};

type InvitationCopy = {
    template: 'classic' | 'floral' | 'midnight';
    headline: string;
    message: string;
    closing: string;
    contactPhone: string | null;
};

type EventDetails = {
    dateLabel: string;
    venueAddress: string | null;
    timezone: string;
    moments: Array<{
        label: string;
        date: string;
        time: string;
        address: string;
    }>;
};

type Branding = {
    primaryColor: string;
    accentColor: string;
    logoUrl: string | null;
    albumBackgroundMode: string;
    albumBackgroundColor: string;
    albumBackgroundImageUrl: string | null;
};

const props = defineProps<{
    eventName: string;
    eventType: string;
    submitted: boolean;
    isPublicInvite: boolean;
    guestParty: GuestParty | null;
    invitation: InvitationCopy;
    eventDetails: EventDetails;
    links: {
        respond: string;
        album: string;
    };
    branding: Branding;
    appName: string;
    showPoweredBy: boolean;
}>();

const { t } = useTranslations();

const form = useForm({
    name: props.guestParty?.name ?? '',
    phone: props.guestParty?.phone ?? '',
    invited_attendees_count: props.guestParty?.invitedAttendeesCount ?? 1,
    attendance_status: props.guestParty?.attendanceStatus === 'declined' ? 'declined' : 'accepted',
    confirmed_attendees_count: props.guestParty?.confirmedAttendeesCount ?? props.guestParty?.invitedAttendeesCount ?? 1,
    guest_names: props.guestParty?.guestNames ?? '',
    meal_preference: props.guestParty?.mealPreference ?? 'standard',
    response_notes: props.guestParty?.responseNotes ?? '',
});

const showConfirmedCount = computed(() => form.attendance_status === 'accepted');

const invitationSurfaceStyle = computed(() => ({
    '--invite-primary': props.branding.primaryColor,
    '--invite-accent': props.branding.accentColor,
    '--invite-background': props.branding.albumBackgroundColor,
    backgroundImage: props.branding.albumBackgroundImageUrl
        ? `linear-gradient(135deg, rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.45)), url(${props.branding.albumBackgroundImageUrl})`
        : `radial-gradient(circle at top, ${props.branding.accentColor}22, transparent 46%), linear-gradient(135deg, #fff8ef, #ffffff 56%, ${props.branding.primaryColor}10)`,
    backgroundSize: props.branding.albumBackgroundImageUrl ? 'cover' : 'auto',
    backgroundPosition: 'center',
}));

const cardToneClass = computed(() => {
    return {
        classic: 'border-neutral-200 bg-white/92 text-neutral-900',
        floral: 'border-rose-200 bg-white/88 text-neutral-900',
        midnight: 'border-white/20 bg-slate-950/78 text-white',
    }[props.invitation.template];
});

const invitationHeroClass = computed(() => {
    return {
        classic: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top,rgba(217,119,6,0.14),transparent_48%)] before:content-[\'\']',
        floral: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top_right,rgba(244,114,182,0.18),transparent_42%),radial-gradient(circle_at_bottom_left,rgba(251,207,232,0.22),transparent_35%)] before:content-[\'\']',
        midnight: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top,rgba(250,204,21,0.14),transparent_35%),radial-gradient(circle_at_bottom,rgba(59,130,246,0.14),transparent_30%)] before:content-[\'\']',
    }[props.invitation.template];
});

const headlineClass = computed(() => {
    return props.invitation.template === 'midnight'
        ? 'text-4xl font-semibold tracking-tight sm:text-5xl'
        : 'text-4xl font-semibold tracking-tight sm:text-5xl font-serif';
});

const mutedTextClass = computed(() => {
    return props.invitation.template === 'midnight'
        ? 'text-white/70'
        : 'text-neutral-600';
});

const submit = (): void => {
    form.post(props.links.respond, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div
        class="min-h-screen px-4 py-6 text-neutral-950 sm:px-6 lg:px-8"
        :style="invitationSurfaceStyle"
    >
        <Head :title="`${eventName} Invitation`" />

        <div class="mx-auto flex min-h-[calc(100vh-3rem)] max-w-6xl items-center">
            <div class="grid w-full gap-5 xl:grid-cols-[minmax(0,0.95fr)_minmax(360px,0.85fr)]">
                <section :class="['relative overflow-hidden rounded-[32px] border p-6 shadow-2xl backdrop-blur sm:p-8', cardToneClass, invitationHeroClass]">
                    <div class="relative flex flex-wrap items-center gap-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-current/15 bg-current/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em]">
                            <Sparkles class="size-3.5" />
                            {{ t('invitations.badge') }}
                        </div>
                        <div v-if="guestParty && !isPublicInvite" :class="['rounded-full px-3 py-1 text-xs font-medium', invitation.template === 'midnight' ? 'bg-white/10 text-white/80' : 'bg-neutral-100 text-neutral-600']">
                            {{ guestParty.name }}
                        </div>
                    </div>

                    <div class="relative mt-6 space-y-4">
                        <img
                            v-if="branding.logoUrl"
                            :src="branding.logoUrl"
                            alt=""
                            class="h-12 w-auto rounded-2xl object-contain"
                        >

                        <div>
                            <p :class="['text-sm uppercase tracking-[0.28em]', mutedTextClass]">
                                {{ eventName }}
                            </p>
                            <h1 :class="['mt-3', headlineClass]">
                                {{ invitation.headline }}
                            </h1>
                        </div>

                        <p :class="['max-w-2xl text-base leading-7 sm:text-lg', mutedTextClass]">
                            {{ invitation.message }}
                        </p>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-2">
                        <div :class="['rounded-3xl border p-4', invitation.template === 'midnight' ? 'border-white/10 bg-white/5' : 'border-neutral-200 bg-white/70']">
                            <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                {{ t('invitations.event_date') }}
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ eventDetails.dateLabel }}
                            </p>
                        </div>

                        <div :class="['rounded-3xl border p-4', invitation.template === 'midnight' ? 'border-white/10 bg-white/5' : 'border-neutral-200 bg-white/70']">
                            <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                {{ t('invitations.venue') }}
                            </p>
                            <p class="mt-2 text-lg font-semibold">
                                {{ eventDetails.venueAddress || t('invitations.venue_pending') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                            {{ t('invitations.schedule') }}
                        </p>

                        <div class="mt-3 space-y-3">
                            <div
                                v-for="moment in eventDetails.moments"
                                :key="`${moment.label}-${moment.date}-${moment.time}`"
                                :class="['rounded-3xl border p-4', invitation.template === 'midnight' ? 'border-white/10 bg-white/5' : 'border-neutral-200 bg-white/70']"
                            >
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-semibold">
                                            {{ moment.label }}
                                        </p>
                                        <div :class="['mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm', mutedTextClass]">
                                            <span class="inline-flex items-center gap-1.5">
                                                <CalendarDays class="size-4" />
                                                {{ moment.date }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <Clock3 class="size-4" />
                                                {{ moment.time }}
                                            </span>
                                        </div>
                                    </div>
                                    <p :class="['inline-flex max-w-sm items-start gap-1.5 text-sm text-right', mutedTextClass]">
                                        <MapPin class="mt-0.5 size-4 shrink-0" />
                                        <span>{{ moment.address }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 space-y-3">
                        <p :class="['text-base leading-7', mutedTextClass]">
                            {{ invitation.closing }}
                        </p>
                        <p v-if="invitation.contactPhone" :class="['inline-flex items-center gap-2 text-sm font-medium', mutedTextClass]">
                            <Phone class="size-4" />
                            {{ invitation.contactPhone }}
                        </p>
                    </div>
                </section>

                <section :class="['rounded-[32px] border p-6 shadow-2xl backdrop-blur sm:p-8', cardToneClass]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                {{ t('invitations.rsvp') }}
                            </p>
                            <h2 class="mt-2 text-3xl font-semibold tracking-tight">
                                {{ isPublicInvite ? t('invitations.public_title') : t('invitations.private_title') }}
                            </h2>
                            <p :class="['mt-2 text-sm leading-6', mutedTextClass]">
                                {{ t('invitations.response_help') }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-[var(--invite-primary)]/10 p-3 text-[var(--invite-primary)]">
                            <Users class="size-5" />
                        </div>
                    </div>

                    <div
                        v-if="submitted"
                        class="mt-5 rounded-3xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="size-4" />
                            <span>{{ t('invitations.success_title') }}</span>
                        </div>
                        <p class="mt-2">
                            {{ t('invitations.success_body') }}
                        </p>
                    </div>

                    <form class="mt-6 space-y-4" @submit.prevent="submit">
                        <div v-if="isPublicInvite" class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2 sm:col-span-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.family_name') }}
                                </label>
                                <Input v-model="form.name" :placeholder="t('invitations.family_placeholder')" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.phone_optional') }}
                                </label>
                                <Input v-model="form.phone" placeholder="07..." />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.invited_count') }}
                                </label>
                                <Input v-model="form.invited_attendees_count" type="number" min="1" max="1000" />
                                <InputError :message="form.errors.invited_attendees_count" />
                            </div>
                        </div>

                        <div v-else class="rounded-3xl border border-current/10 bg-current/5 px-4 py-3">
                            <p class="text-sm font-medium">
                                {{ guestParty?.name }}
                            </p>
                            <p :class="['mt-1 text-sm', mutedTextClass]">
                                {{ t('invitations.invited_for', { count: guestParty?.invitedAttendeesCount ?? 1 }) }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                :class="[
                                    'rounded-3xl border px-4 py-4 text-left transition',
                                    form.attendance_status === 'accepted'
                                        ? 'border-emerald-400 bg-emerald-50 text-emerald-900'
                                        : 'border-neutral-200 bg-white/50 text-neutral-700',
                                ]"
                                @click="form.attendance_status = 'accepted'"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.18em]">
                                    {{ t('invitations.accept') }}
                                </p>
                                <p class="mt-2 text-sm">
                                    {{ t('invitations.accept_help') }}
                                </p>
                            </button>

                            <button
                                type="button"
                                :class="[
                                    'rounded-3xl border px-4 py-4 text-left transition',
                                    form.attendance_status === 'declined'
                                        ? 'border-rose-400 bg-rose-50 text-rose-900'
                                        : 'border-neutral-200 bg-white/50 text-neutral-700',
                                ]"
                                @click="form.attendance_status = 'declined'"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.18em]">
                                    {{ t('invitations.decline') }}
                                </p>
                                <p class="mt-2 text-sm">
                                    {{ t('invitations.decline_help') }}
                                </p>
                            </button>
                        </div>
                        <InputError :message="form.errors.attendance_status" />

                        <div v-if="showConfirmedCount" class="space-y-2">
                            <label class="text-sm font-medium">
                                {{ t('invitations.confirmed_count') }}
                            </label>
                            <Input v-model="form.confirmed_attendees_count" type="number" min="1" max="1000" />
                            <InputError :message="form.errors.confirmed_attendees_count" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">
                                {{ t('invitations.guest_names') }}
                            </label>
                            <Textarea
                                v-model="form.guest_names"
                                rows="4"
                                :placeholder="t('invitations.guest_names_placeholder')"
                            />
                            <InputError :message="form.errors.guest_names" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">
                                {{ t('invitations.meal_preference') }}
                            </label>
                            <NativeSelect v-model="form.meal_preference">
                                <NativeSelectOption value="standard">{{ t('invitations.meal.standard') }}</NativeSelectOption>
                                <NativeSelectOption value="vegetarian">{{ t('invitations.meal.vegetarian') }}</NativeSelectOption>
                                <NativeSelectOption value="vegan">{{ t('invitations.meal.vegan') }}</NativeSelectOption>
                                <NativeSelectOption value="halal">{{ t('invitations.meal.halal') }}</NativeSelectOption>
                                <NativeSelectOption value="other">{{ t('invitations.meal.other') }}</NativeSelectOption>
                            </NativeSelect>
                            <InputError :message="form.errors.meal_preference" />
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">
                                {{ t('invitations.note_optional') }}
                            </label>
                            <Textarea
                                v-model="form.response_notes"
                                rows="4"
                                :placeholder="t('invitations.note_placeholder')"
                            />
                            <InputError :message="form.errors.response_notes" />
                        </div>

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                            <Link
                                :href="links.album"
                                :class="['text-sm font-medium underline underline-offset-4', mutedTextClass]"
                            >
                                {{ t('invitations.open_album') }}
                            </Link>

                            <Button
                                type="submit"
                                class="rounded-full px-6"
                                :disabled="form.processing"
                            >
                                {{ t('invitations.submit') }}
                            </Button>
                        </div>
                    </form>

                    <p v-if="showPoweredBy" :class="['mt-6 text-xs', mutedTextClass]">
                        {{ t('invitations.powered_by', { app: appName }) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
