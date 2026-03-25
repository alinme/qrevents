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

const invitationTemplateVisuals = computed(() => {
    return {
        classic: {
            tag: 'Classic stationery',
            surfaceClass: 'border-stone-200 bg-[linear-gradient(180deg,rgba(255,252,247,0.98),rgba(255,255,255,0.92))] text-neutral-950',
            accentClass: 'border-amber-200/70 bg-amber-500/10 text-amber-700',
            ribbonClass: 'from-amber-500 via-orange-400 to-amber-600',
            cardGlowClass: 'shadow-[0_32px_90px_rgba(161,98,7,0.14)]',
            softBorderClass: 'border-stone-200/80 bg-white/75',
            mutedClass: 'text-neutral-600',
        },
        floral: {
            tag: 'Floral romance',
            surfaceClass: 'border-rose-200 bg-[linear-gradient(180deg,rgba(255,247,249,0.98),rgba(255,255,255,0.92))] text-neutral-950',
            accentClass: 'border-rose-200/70 bg-rose-500/10 text-rose-700',
            ribbonClass: 'from-rose-500 via-fuchsia-400 to-rose-600',
            cardGlowClass: 'shadow-[0_32px_90px_rgba(244,114,182,0.12)]',
            softBorderClass: 'border-rose-200/75 bg-white/78',
            mutedClass: 'text-neutral-600',
        },
        midnight: {
            tag: 'Midnight modern',
            surfaceClass: 'border-white/15 bg-[linear-gradient(180deg,rgba(15,23,42,0.98),rgba(15,23,42,0.9))] text-white',
            accentClass: 'border-white/15 bg-white/10 text-white/85',
            ribbonClass: 'from-sky-400 via-indigo-400 to-cyan-300',
            cardGlowClass: 'shadow-[0_32px_90px_rgba(15,23,42,0.35)]',
            softBorderClass: 'border-white/10 bg-white/6',
            mutedClass: 'text-white/72',
        },
    }[props.invitation.template];
});

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

const cardToneClass = computed(() => invitationTemplateVisuals.value.surfaceClass);

const invitationHeroClass = computed(() => {
    return {
        classic: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top,rgba(217,119,6,0.14),transparent_48%)] before:content-[\'\']',
        floral: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top_right,rgba(244,114,182,0.18),transparent_42%),radial-gradient(circle_at_bottom_left,rgba(251,207,232,0.22),transparent_35%)] before:content-[\'\']',
        midnight: 'before:absolute before:inset-0 before:bg-[radial-gradient(circle_at_top,rgba(250,204,21,0.14),transparent_35%),radial-gradient(circle_at_bottom,rgba(59,130,246,0.14),transparent_30%)] before:content-[\'\']',
    }[props.invitation.template];
});

const mutedTextClass = computed(() => {
    return invitationTemplateVisuals.value.mutedClass;
});

const invitationLabel = computed(() => invitationTemplateVisuals.value.tag);

const submit = (): void => {
    form.post(props.links.respond, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div
        class="relative min-h-screen overflow-hidden px-4 py-6 text-neutral-950 sm:px-6 lg:px-8"
        :style="invitationSurfaceStyle"
    >
        <Head :title="`${eventName} Invitation`" />

        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -left-24 top-8 h-72 w-72 rounded-full bg-[var(--invite-accent)]/18 blur-3xl" />
            <div class="absolute right-[-5rem] top-20 h-80 w-80 rounded-full bg-[var(--invite-primary)]/14 blur-3xl" />
            <div class="absolute bottom-[-7rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-white/28 blur-3xl" />
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] max-w-7xl items-center">
            <div class="grid w-full gap-6 xl:grid-cols-[minmax(0,1.06fr)_minmax(360px,0.94fr)]">
                <section :class="['relative overflow-hidden rounded-[36px] border p-6 shadow-2xl backdrop-blur sm:p-8', invitationTemplateVisuals.cardGlowClass, cardToneClass, invitationHeroClass]">
                    <div class="pointer-events-none absolute inset-0">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[var(--invite-primary)] via-[var(--invite-accent)] to-[var(--invite-primary)] opacity-80" />
                        <div class="absolute -right-20 top-12 h-48 w-48 rounded-full bg-white/35 blur-3xl" />
                        <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-[var(--invite-accent)]/8 blur-2xl" />
                    </div>

                    <div class="relative flex flex-wrap items-center gap-3">
                        <div :class="['inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em]', invitationTemplateVisuals.accentClass]">
                            <Sparkles class="size-3.5" />
                            {{ t('invitations.badge') }}
                        </div>
                        <div
                            v-if="guestParty && !isPublicInvite"
                            :class="[
                                'rounded-full border px-3 py-1 text-xs font-medium',
                                invitation.template === 'midnight' ? 'border-white/10 bg-white/8 text-white/80' : 'border-stone-200 bg-white/80 text-neutral-700',
                            ]"
                        >
                            {{ guestParty.name }}
                        </div>
                        <div :class="['rounded-full border px-3 py-1 text-xs font-medium', invitationTemplateVisuals.accentClass]">
                            {{ invitationLabel }}
                        </div>
                    </div>

                    <div class="relative mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(240px,0.65fr)]">
                        <div class="space-y-5">
                            <div class="flex items-center gap-4">
                                <img
                                    v-if="branding.logoUrl"
                                    :src="branding.logoUrl"
                                    alt=""
                                    class="h-14 w-14 rounded-[24px] border border-current/10 object-contain p-2 shadow-sm"
                                >
                                <div class="space-y-1">
                                    <p :class="['text-xs font-semibold uppercase tracking-[0.28em]', mutedTextClass]">
                                        {{ eventName }}
                                    </p>
                                    <p :class="['text-sm', mutedTextClass]">
                                        {{ invitationTemplateVisuals.tag }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h1 :class="['max-w-2xl text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl', invitation.template === 'midnight' ? 'text-white' : 'text-neutral-950', invitation.template !== 'midnight' ? 'font-serif' : '']">
                                    {{ invitation.headline }}
                                </h1>
                                <p :class="['max-w-2xl text-base leading-8 sm:text-lg', mutedTextClass]">
                                    {{ invitation.message }}
                                </p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div :class="['rounded-[28px] border p-4 backdrop-blur-sm', invitationTemplateVisuals.softBorderClass]">
                                    <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                        {{ t('invitations.event_date') }}
                                    </p>
                                    <p class="mt-2 text-lg font-semibold">
                                        {{ eventDetails.dateLabel }}
                                    </p>
                                </div>

                                <div :class="['rounded-[28px] border p-4 backdrop-blur-sm', invitationTemplateVisuals.softBorderClass]">
                                    <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                        {{ t('invitations.venue') }}
                                    </p>
                                    <p class="mt-2 text-lg font-semibold">
                                        {{ eventDetails.venueAddress || t('invitations.venue_pending') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-stretch">
                            <div :class="['relative flex w-full flex-col justify-between overflow-hidden rounded-[30px] border p-5', invitationTemplateVisuals.softBorderClass]">
                                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r opacity-80" :class="invitationTemplateVisuals.ribbonClass" />
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="rounded-full border border-current/10 bg-current/5 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]">
                                            {{ t('invitations.card_title') }}
                                        </div>
                                        <div class="text-xs font-medium opacity-70">
                                            {{ eventDetails.timezone }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-sm uppercase tracking-[0.3em] opacity-70">
                                                {{ t('invitations.rsvp') }}
                                        </p>
                                        <h2 class="text-3xl font-semibold tracking-tight">
                                            {{ invitation.headline }}
                                        </h2>
                                        <p class="text-sm leading-6 opacity-80">
                                            {{ invitation.message }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <div
                                        v-for="moment in eventDetails.moments.slice(0, 3)"
                                        :key="`${moment.label}-${moment.date}-${moment.time}`"
                                        class="rounded-[22px] border border-current/10 bg-white/70 p-3"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-sm font-semibold">
                                                {{ moment.label }}
                                            </p>
                                            <p class="text-xs uppercase tracking-[0.2em] opacity-60">
                                                RSVP
                                            </p>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-3 text-xs opacity-75">
                                            <span class="inline-flex items-center gap-1.5">
                                                <CalendarDays class="size-3.5" />
                                                {{ moment.date }}
                                            </span>
                                            <span class="inline-flex items-center gap-1.5">
                                                <Clock3 class="size-3.5" />
                                                {{ moment.time }}
                                            </span>
                                        </div>
                                        <p class="mt-2 inline-flex items-start gap-1.5 text-xs opacity-75">
                                            <MapPin class="mt-0.5 size-3.5 shrink-0" />
                                            <span>{{ moment.address }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative mt-8 grid gap-4 lg:grid-cols-[minmax(0,1.1fr)_minmax(260px,0.7fr)]">
                        <div class="rounded-[30px] border p-5 backdrop-blur-sm" :class="invitationTemplateVisuals.softBorderClass">
                            <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                {{ t('invitations.schedule') }}
                            </p>

                            <div class="mt-4 space-y-3">
                                <div
                                    v-for="moment in eventDetails.moments"
                                    :key="`${moment.label}-${moment.date}-${moment.time}`"
                                    :class="['rounded-[24px] border p-4', invitationTemplateVisuals.softBorderClass]"
                                >
                                    <div class="flex flex-wrap items-start justify-between gap-3">
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

                        <div :class="['flex flex-col justify-between rounded-[30px] border p-5 backdrop-blur-sm', invitationTemplateVisuals.softBorderClass]">
                            <div class="space-y-3">
                                <p class="text-base leading-7">
                                    {{ invitation.closing }}
                                </p>
                            </div>

                            <div class="mt-6 flex items-center justify-between gap-3">
                                <p v-if="invitation.contactPhone" :class="['inline-flex items-center gap-2 text-sm font-medium', mutedTextClass]">
                                    <Phone class="size-4" />
                                    {{ invitation.contactPhone }}
                                </p>
                                <div class="rounded-full border border-current/10 bg-current/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">
                                    {{ isPublicInvite ? t('invitations.public_title') : t('invitations.private_title') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section :class="['rounded-[36px] border p-6 shadow-2xl backdrop-blur sm:p-8', cardToneClass]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p :class="['text-xs font-semibold uppercase tracking-[0.24em]', mutedTextClass]">
                                {{ t('invitations.rsvp') }}
                            </p>
                            <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-[2.1rem]">
                                {{ isPublicInvite ? t('invitations.public_title') : t('invitations.private_title') }}
                            </h2>
                            <p :class="['mt-2 text-sm leading-6', mutedTextClass]">
                                {{ t('invitations.response_help') }}
                            </p>
                        </div>
                        <div class="rounded-[22px] border border-current/10 bg-[var(--invite-primary)]/10 p-3 text-[var(--invite-primary)]">
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
