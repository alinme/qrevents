<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Download, Minus, Plus } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { type InvitationTemplateId } from '@/lib/invitation-templates';
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
    template: InvitationTemplateId;
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
const showResponseDetails = computed(() => form.attendance_status === 'accepted');
const confirmedAttendeeMax = computed(() => {
    if (props.isPublicInvite) {
        return Math.max(1, Number(form.invited_attendees_count) || 1);
    }

    return Math.max(1, props.guestParty?.invitedAttendeesCount ?? 1);
});

const clampCount = (value: unknown, min: number, max: number): number => {
    const numericValue = typeof value === 'number' ? value : Number(value);

    if (! Number.isFinite(numericValue)) {
        return min;
    }

    return Math.min(max, Math.max(min, Math.round(numericValue)));
};

const invitedCount = computed<number>({
    get: () => clampCount(form.invited_attendees_count, 1, 1000),
    set: (value) => {
        form.invited_attendees_count = clampCount(value, 1, 1000);
    },
});

const confirmedCount = computed<number>({
    get: () => clampCount(form.confirmed_attendees_count, 1, confirmedAttendeeMax.value),
    set: (value) => {
        form.confirmed_attendees_count = clampCount(value, 1, confirmedAttendeeMax.value);
    },
});

const adjustInvitedCount = (delta: number): void => {
    invitedCount.value += delta;

    if (confirmedCount.value > invitedCount.value) {
        confirmedCount.value = invitedCount.value;
    }
};

const adjustConfirmedCount = (delta: number): void => {
    confirmedCount.value += delta;
};

const invitationSurfaceStyle = computed(() => ({
    '--invite-primary': props.branding.primaryColor,
    '--invite-accent': props.branding.accentColor,
    backgroundImage: props.branding.albumBackgroundImageUrl
        ? `linear-gradient(180deg, rgba(15, 23, 42, 0.58), rgba(15, 23, 42, 0.84)), url(${props.branding.albumBackgroundImageUrl})`
        : `radial-gradient(circle at top, ${props.branding.accentColor}18, transparent 42%), linear-gradient(180deg, #fff8ef 0%, #ffffff 48%, ${props.branding.primaryColor}08 100%)`,
    backgroundSize: props.branding.albumBackgroundImageUrl ? 'cover' : 'auto',
    backgroundPosition: 'center',
}));

const submit = (): void => {
    form.post(props.links.respond, {
        preserveScroll: true,
    });
};

const printInvitation = (): void => {
    window.print();
};

onMounted(() => {
    invitedCount.value = invitedCount.value;
    confirmedCount.value = confirmedCount.value;

    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.setTimeout(() => {
            window.print();
        }, 250);
    }
});
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

        <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-2xl items-center">
            <div class="w-full space-y-5">
                <InvitationPaper
                    :template="invitation.template"
                    :event-name="eventName"
                    :guest-label="guestParty && !isPublicInvite ? guestParty.name : t('invitations.badge')"
                    :headline="invitation.headline"
                    :message="invitation.message"
                    :closing="invitation.closing"
                    :contact-phone="invitation.contactPhone"
                    :date-label="eventDetails.dateLabel"
                    :venue-address="eventDetails.venueAddress || t('invitations.venue_pending')"
                    mode="live"
                />

                <div class="flex justify-end">
                    <Button variant="outline" class="rounded-full px-5" @click="printInvitation">
                        <Download class="mr-2 size-4" />
                        Save or print invitation
                    </Button>
                </div>

                <section class="rounded-[30px] border border-neutral-200 bg-white/92 p-5 shadow-xl backdrop-blur sm:p-6">
                    <div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-600">
                                {{ t('invitations.rsvp') }}
                            </p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-[2rem]">
                                {{ isPublicInvite ? t('invitations.public_title') : t('invitations.private_title') }}
                            </h2>
                            <p class="mt-2 max-w-lg text-sm leading-6 text-neutral-600">
                                {{ t('invitations.response_help') }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="submitted"
                        class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="size-4" />
                            <span>{{ t('invitations.success_title') }}</span>
                        </div>
                        <p class="mt-2">
                            {{ t('invitations.success_body') }}
                        </p>
                    </div>

                    <form class="mt-5 space-y-4" @submit.prevent="submit">
                        <div v-if="isPublicInvite" class="grid gap-4 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)]">
                            <div class="space-y-2">
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
                                <div class="flex items-center gap-3 rounded-2xl border border-neutral-200 bg-white px-3 py-2 shadow-sm">
                                    <button
                                        type="button"
                                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-50 text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="invitedCount <= 1"
                                        @click="adjustInvitedCount(-1)"
                                    >
                                        <Minus class="size-5" />
                                    </button>

                                    <div class="min-w-0 flex-1 text-center">
                                        <Input
                                            :model-value="String(invitedCount)"
                                            readonly
                                            inputmode="none"
                                            class="border-0 bg-transparent px-0 text-center text-lg font-semibold shadow-none focus-visible:ring-0"
                                        />
                                    </div>

                                    <button
                                        type="button"
                                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-50 text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="invitedCount >= 1000"
                                        @click="adjustInvitedCount(1)"
                                    >
                                        <Plus class="size-5" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.invited_attendees_count" />
                            </div>
                        </div>

                        <div v-else class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <p class="text-sm font-medium">
                                {{ guestParty?.name }}
                            </p>
                            <p class="mt-1 text-sm text-neutral-600">
                                {{ t('invitations.invited_for', { count: guestParty?.invitedAttendeesCount ?? 1 }) }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                type="button"
                                :class="[
                                    'rounded-2xl border px-4 py-4 text-left transition',
                                    form.attendance_status === 'accepted'
                                        ? 'border-emerald-400 bg-emerald-50 text-emerald-900'
                                        : 'border-neutral-200 bg-white/50 text-neutral-700',
                                ]"
                                @click="form.attendance_status = 'accepted'"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.16em]">
                                    {{ t('invitations.accept') }}
                                </p>
                                <p class="mt-2 text-sm">
                                    {{ t('invitations.accept_help') }}
                                </p>
                            </button>

                            <button
                                type="button"
                                :class="[
                                    'rounded-2xl border px-4 py-4 text-left transition',
                                    form.attendance_status === 'declined'
                                        ? 'border-rose-400 bg-rose-50 text-rose-900'
                                        : 'border-neutral-200 bg-white/50 text-neutral-700',
                                ]"
                                @click="form.attendance_status = 'declined'"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.16em]">
                                    {{ t('invitations.decline') }}
                                </p>
                                <p class="mt-2 text-sm">
                                    {{ t('invitations.decline_help') }}
                                </p>
                            </button>
                        </div>
                        <InputError :message="form.errors.attendance_status" />

                        <div v-if="showResponseDetails" class="grid gap-4 rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.confirmed_count') }}
                                </label>
                                <div class="flex items-center gap-3 rounded-2xl border border-neutral-200 bg-white px-3 py-2 shadow-sm">
                                    <button
                                        type="button"
                                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-50 text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="confirmedCount <= 1"
                                        @click="adjustConfirmedCount(-1)"
                                    >
                                        <Minus class="size-5" />
                                    </button>

                                    <div class="min-w-0 flex-1 text-center">
                                        <Input
                                            :model-value="String(confirmedCount)"
                                            readonly
                                            inputmode="none"
                                            class="border-0 bg-transparent px-0 text-center text-lg font-semibold shadow-none focus-visible:ring-0"
                                        />
                                    </div>

                                    <button
                                        type="button"
                                        class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl border border-neutral-200 bg-neutral-50 text-neutral-700 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="confirmedCount >= confirmedAttendeeMax"
                                        @click="adjustConfirmedCount(1)"
                                    >
                                        <Plus class="size-5" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.confirmed_attendees_count" />
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
                                    {{ t('invitations.guest_names') }}
                                </label>
                                <Textarea
                                    v-model="form.guest_names"
                                    rows="2"
                                    :placeholder="t('invitations.guest_names_placeholder')"
                                />
                                <InputError :message="form.errors.guest_names" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.note_optional') }}
                                </label>
                                <Textarea
                                    v-model="form.response_notes"
                                    rows="2"
                                    :placeholder="t('invitations.note_placeholder')"
                                />
                                <InputError :message="form.errors.response_notes" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                            <Link
                                :href="links.album"
                                class="text-sm font-medium text-neutral-600 underline underline-offset-4"
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

                    <p v-if="showPoweredBy" class="mt-5 text-xs text-neutral-500">
                        {{ t('invitations.powered_by', { app: appName }) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</template>
