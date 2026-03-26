<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Download, ExternalLink, Minus, Plus } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import InvitationPaper from '@/components/invitations/InvitationPaper.vue';
import { Button } from '@/components/ui/button';
import {
    DialogClose,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { type InvitationTemplateId } from '@/lib/invitation-templates';
import {
    composeInvitationPaperPresentation,
    type InvitationWeddingDetails,
} from '@/lib/invitation-presentation';
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
    weddingDetails: InvitationWeddingDetails;
    timezone: string;
    moments: Array<{
        label: string;
        date: string;
        time: string;
        address: string;
        mapsUrl: string | null;
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
const acceptModalOpen = ref(false);
const maybeModalOpen = ref(false);
const declineModalOpen = ref(false);
const maybeMemeImageVisible = ref(true);
const declineMemeImageVisible = ref(true);
const maybeMemeImageUrl = '/invitation-memes/angry.png';
const declineMemeImageUrl = '/invitation-memes/sad.png';

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

const confirmedAttendeeMax = computed(() => {
    if (props.isPublicInvite) {
        return 1000;
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

const confirmedCount = computed<number>({
    get: () => clampCount(form.confirmed_attendees_count, 1, confirmedAttendeeMax.value),
    set: (value) => {
        form.confirmed_attendees_count = clampCount(value, 1, confirmedAttendeeMax.value);
    },
});

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

const invitationPresentation = computed(() =>
    composeInvitationPaperPresentation({
        eventName: props.eventName,
        eventType: props.eventType,
        headline: props.invitation.headline,
        weddingDetails: props.eventDetails.weddingDetails,
    }),
);

const visibleMoments = computed(() => props.eventDetails.moments);

const submit = (): void => {
    form.post(props.links.respond, {
        preserveScroll: true,
        onSuccess: () => {
            acceptModalOpen.value = false;
            declineModalOpen.value = false;
        },
    });
};

const openAcceptModal = (): void => {
    form.attendance_status = 'accepted';
    acceptModalOpen.value = true;
};

const openMaybeModal = (): void => {
    maybeModalOpen.value = true;
};

const openDeclineModal = (): void => {
    declineModalOpen.value = true;
};

const submitAccepted = (): void => {
    form.attendance_status = 'accepted';

    if (props.isPublicInvite) {
        form.invited_attendees_count = confirmedCount.value;
    }

    submit();
};

const submitDeclined = (): void => {
    form.attendance_status = 'declined';

    if (props.isPublicInvite && form.name.trim() === '') {
        form.name = `${t('invitations.declined_guest_label')} ${new Date().toISOString().slice(0, 16).replace('T', ' ')}`;
    }

    form.invited_attendees_count = 1;
    form.confirmed_attendees_count = 0;

    submit();
};

const printInvitation = (): void => {
    window.print();
};

onMounted(() => {
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
        class="relative min-h-screen overflow-hidden px-4 py-6 text-neutral-950 print:min-h-0 print:bg-white print:px-0 print:py-0 sm:px-6 lg:px-8"
        :style="invitationSurfaceStyle"
    >
        <Head :title="`${eventName} Invitation`" />

        <div class="pointer-events-none absolute inset-0 overflow-hidden print:hidden">
            <div class="absolute -left-24 top-8 h-72 w-72 rounded-full bg-[var(--invite-accent)]/18 blur-3xl" />
            <div class="absolute right-[-5rem] top-20 h-80 w-80 rounded-full bg-[var(--invite-primary)]/14 blur-3xl" />
            <div class="absolute bottom-[-7rem] left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-white/28 blur-3xl" />
        </div>

        <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-2xl items-center print:min-h-0 print:max-w-none">
            <div class="w-full space-y-5 print:space-y-0">
                <InvitationPaper
                    :template="invitation.template"
                    :event-name="invitationPresentation.leadIn"
                    :logo-url="branding.logoUrl"
                    :guest-label="guestParty && !isPublicInvite ? guestParty.name : t('invitations.badge')"
                    :headline="invitationPresentation.title"
                    :message="invitation.message"
                    :closing="invitation.closing"
                    :detail-lines="invitationPresentation.detailLines"
                    :contact-phone="invitation.contactPhone"
                    :date-label="eventDetails.dateLabel"
                    :venue-address="eventDetails.venueAddress || t('invitations.venue_pending')"
                    mode="live"
                />

                <section class="rounded-[30px] border border-neutral-200 bg-white/92 p-5 shadow-xl backdrop-blur print:hidden sm:p-6">
                    <div
                        v-if="submitted"
                        class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="size-4" />
                            <span>{{ t('invitations.success_title') }}</span>
                        </div>
                        <p class="mt-2">
                            {{ t('invitations.success_body') }}
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <div class="space-y-1 text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-600">
                                {{ t('invitations.rsvp') }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('invitations.response_help') }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <button
                                type="button"
                                class="rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-4 text-center text-emerald-950 transition hover:border-emerald-400 hover:bg-emerald-100"
                                @click="openAcceptModal"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.16em]">
                                    {{ t('invitations.accept') }}
                                </p>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-4 text-center text-amber-950 transition hover:border-amber-400 hover:bg-amber-100"
                                @click="openMaybeModal"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.16em]">
                                    {{ t('invitations.maybe') }}
                                </p>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl border border-rose-300 bg-rose-50 px-4 py-4 text-center text-rose-950 transition hover:border-rose-400 hover:bg-rose-100"
                                @click="openDeclineModal"
                            >
                                <p class="text-sm font-semibold uppercase tracking-[0.16em]">
                                    {{ t('invitations.decline') }}
                                </p>
                            </button>
                        </div>

                        <InputError :message="form.errors.attendance_status" />
                    </div>
                </section>

                <section
                    v-if="visibleMoments.length > 0"
                    class="rounded-[30px] border border-neutral-200 bg-white/92 p-4 shadow-lg backdrop-blur print:hidden sm:p-5"
                >
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-neutral-600">
                            {{ t('invitations.moments_title') }}
                        </p>
                        <p class="text-sm text-neutral-600">
                            {{ t('invitations.moments_description') }}
                        </p>
                    </div>

                    <div class="mt-4 grid gap-3">
                        <div
                            v-for="moment in visibleMoments"
                            :key="`${moment.label}-${moment.date}-${moment.time}`"
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-neutral-900">
                                        {{ moment.label }}
                                    </p>
                                    <p class="text-sm text-neutral-600">
                                        {{ moment.date }} · {{ moment.time }}
                                    </p>
                                    <p class="text-sm text-neutral-600">
                                        {{ moment.address }}
                                    </p>
                                </div>

                                <a
                                    v-if="moment.mapsUrl"
                                    :href="moment.mapsUrl"
                                    target="_blank"
                                    rel="noreferrer"
                                    class="inline-flex items-center gap-2 rounded-full border border-neutral-200 bg-white px-3 py-2 text-sm font-medium text-neutral-700 transition hover:border-neutral-300 hover:text-neutral-950"
                                >
                                    <ExternalLink class="size-4" />
                                    {{ t('invitations.open_maps') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col items-center gap-3 text-center print:hidden">
                    <Button variant="outline" class="rounded-full px-5" @click="printInvitation">
                        <Download class="mr-2 size-4" />
                        Save or print invitation
                    </Button>

                    <Link
                        :href="links.album"
                        class="text-sm font-medium text-neutral-700 underline underline-offset-4"
                    >
                        {{ t('invitations.open_album') }}
                    </Link>

                    <p v-if="showPoweredBy" class="text-xs text-neutral-500">
                        {{ t('invitations.powered_by', { app: appName }) }}
                    </p>
                </div>
            </div>
        </div>

        <Dialog v-model:open="acceptModalOpen">
            <DialogContent class="flex h-[min(92vh,840px)] flex-col overflow-hidden rounded-[2rem] p-0 sm:max-w-2xl">
                <DialogHeader class="shrink-0 border-b border-neutral-200/80 px-6 py-5 text-left sm:px-7">
                    <div class="space-y-2">
                        <DialogTitle class="text-2xl font-semibold tracking-tight">
                            {{ isPublicInvite ? t('invitations.public_title') : t('invitations.private_title') }}
                        </DialogTitle>
                        <DialogDescription class="text-sm leading-6 text-neutral-600">
                            {{ t('invitations.accept_modal_description') }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="submitAccepted">
                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5 sm:px-7">
                        <div class="mx-auto flex w-full max-w-xs flex-col items-center space-y-2 text-center">
                            <label class="text-sm font-medium">
                                {{ t('invitations.confirmed_count') }}
                            </label>
                            <div class="w-full rounded-2xl border border-neutral-200 bg-white px-3 py-2 shadow-sm">
                                <div class="flex items-center gap-3">
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
                                            class="h-11 border-0 bg-transparent px-0 text-center text-lg font-semibold shadow-none focus-visible:ring-0"
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
                            </div>
                            <p class="text-sm text-neutral-600">
                                {{ t('invitations.how_many_hint') }}
                            </p>
                            <InputError :message="form.errors.confirmed_attendees_count" />
                        </div>

                        <div v-if="!isPublicInvite" class="space-y-1 text-center">
                            <p class="text-sm font-medium text-neutral-900">
                                {{ guestParty?.name }}
                            </p>
                            <p class="text-sm text-neutral-600">
                                {{ t('invitations.invited_for', { count: guestParty?.invitedAttendeesCount ?? 1 }) }}
                            </p>
                        </div>

                        <div v-if="isPublicInvite" class="grid gap-4 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)]">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.family_name') }}
                                </label>
                                <Input
                                    v-model="form.name"
                                    :placeholder="t('invitations.family_placeholder')"
                                    class="h-14 rounded-2xl"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.phone_optional') }}
                                </label>
                                <Input v-model="form.phone" placeholder="07..." class="h-14 rounded-2xl" />
                                <InputError :message="form.errors.phone" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.guest_names') }}
                                </label>
                                <Textarea
                                    v-model="form.guest_names"
                                    rows="2"
                                    :placeholder="t('invitations.guest_names_placeholder')"
                                    class="min-h-14 rounded-2xl py-3"
                                />
                                <p class="text-sm text-neutral-500">
                                    {{ t('invitations.guest_names_help') }}
                                </p>
                                <InputError :message="form.errors.guest_names" />
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">
                                    {{ t('invitations.meal_preference') }}
                                </label>
                                <NativeSelect v-model="form.meal_preference" class="h-14 rounded-2xl">
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
                                    rows="2"
                                    :placeholder="t('invitations.note_placeholder')"
                                    class="min-h-14 rounded-2xl py-3"
                                />
                                <InputError :message="form.errors.response_notes" />
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-neutral-200/80 px-6 py-4 sm:px-7">
                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                class="rounded-full px-6"
                                :disabled="form.processing"
                            >
                                {{ t('invitations.submit') }}
                            </Button>
                        </div>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="maybeModalOpen">
            <DialogContent
                :show-close-button="false"
                class="max-w-md overflow-hidden rounded-[2rem] border-0 bg-transparent p-0 shadow-none"
            >
                <div class="relative overflow-hidden rounded-[2rem] bg-amber-50">
                    <DialogClose class="absolute right-4 top-4 z-20 inline-flex size-11 items-center justify-center rounded-full bg-black/28 text-white backdrop-blur-sm transition hover:bg-black/40">
                        <span class="sr-only">{{ t('common.close') }}</span>
                    </DialogClose>
                    <div class="relative overflow-hidden">
                        <img
                            v-if="maybeMemeImageVisible"
                            :src="maybeMemeImageUrl"
                            :alt="t('invitations.maybe_meme_alt')"
                            class="block h-auto w-full object-cover"
                            @error="maybeMemeImageVisible = false"
                        >
                        <div v-if="maybeMemeImageVisible" class="pointer-events-none absolute inset-x-8 top-10 text-center">
                            <p class="text-5xl font-black uppercase tracking-[0.12em] text-white [-webkit-text-stroke:2.5px_rgba(0,0,0,0.95)] [paint-order:stroke_fill] sm:text-6xl">
                                {{ t('invitations.maybe_meme_top') }}
                            </p>
                        </div>
                        <div v-if="maybeMemeImageVisible" class="pointer-events-none absolute inset-x-8 bottom-10 text-center">
                            <p class="text-3xl font-black uppercase tracking-[0.12em] text-white [-webkit-text-stroke:2px_rgba(0,0,0,0.95)] [paint-order:stroke_fill] sm:text-4xl">
                                {{ t('invitations.maybe_meme_bottom') }}
                            </p>
                        </div>
                        <div v-else class="flex min-h-72 flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                            <p class="text-6xl">🙂</p>
                            <p class="text-lg font-semibold text-amber-950">
                                {{ t('invitations.maybe_fallback_title') }}
                            </p>
                            <p class="max-w-sm text-sm text-amber-900/80">
                                {{ t('invitations.maybe_fallback_body') }}
                            </p>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="declineModalOpen">
            <DialogContent
                :show-close-button="false"
                class="max-w-md overflow-hidden rounded-[2rem] border-0 bg-transparent p-0 shadow-none"
            >
                <div class="relative overflow-hidden rounded-[2rem] bg-rose-50">
                    <DialogClose class="absolute right-4 top-4 z-20 inline-flex size-11 items-center justify-center rounded-full bg-black/28 text-white backdrop-blur-sm transition hover:bg-black/40">
                        <span class="sr-only">{{ t('common.close') }}</span>
                    </DialogClose>
                    <div class="relative overflow-hidden">
                        <img
                            v-if="declineMemeImageVisible"
                            :src="declineMemeImageUrl"
                            :alt="t('invitations.decline_meme_alt')"
                            class="block h-auto w-full object-cover"
                            @error="declineMemeImageVisible = false"
                        >
                        <div v-if="declineMemeImageVisible" class="pointer-events-none absolute inset-x-8 top-10 text-center">
                            <p class="text-5xl font-black uppercase tracking-[0.12em] text-white [-webkit-text-stroke:2.5px_rgba(0,0,0,0.95)] [paint-order:stroke_fill] sm:text-6xl">
                                {{ t('invitations.decline_meme_top') }}
                            </p>
                        </div>
                        <div v-if="declineMemeImageVisible" class="pointer-events-none absolute inset-x-8 top-32 text-center sm:top-36">
                            <p class="text-3xl font-black uppercase tracking-[0.12em] text-white [-webkit-text-stroke:2px_rgba(0,0,0,0.95)] [paint-order:stroke_fill] sm:text-4xl">
                                {{ t('invitations.decline_meme_bottom') }}
                            </p>
                        </div>
                        <div v-if="declineMemeImageVisible" class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/58 to-transparent px-4 pb-4 pt-24 text-white sm:px-5 sm:pb-5">
                            <p class="text-sm leading-6 text-white/92">
                                {{ t('invitations.decline_note') }}
                            </p>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-12 rounded-2xl border-white/35 bg-white/10 px-4 text-white backdrop-blur-sm hover:bg-white/18"
                                    @click="declineModalOpen = false"
                                >
                                    {{ t('invitations.keep_invitation') }}
                                </Button>

                                <Button
                                    type="button"
                                    class="h-12 rounded-2xl bg-rose-600 px-4 text-white hover:bg-rose-700"
                                    :disabled="form.processing"
                                    @click="submitDeclined"
                                >
                                    {{ t('invitations.confirm_decline') }}
                                </Button>
                            </div>
                        </div>
                        <div v-else class="flex min-h-72 flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                            <p class="text-6xl">🥹</p>
                            <p class="text-lg font-semibold text-rose-950">
                                {{ t('invitations.decline_fallback_title') }}
                            </p>
                            <p class="max-w-sm text-sm text-rose-900/80">
                                {{ t('invitations.decline_fallback_body') }}
                            </p>
                            <p class="max-w-sm text-sm leading-6 text-rose-950/80">
                                {{ t('invitations.decline_note') }}
                            </p>
                            <div class="mt-3 grid w-full max-w-sm grid-cols-2 gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="h-12 rounded-2xl px-4"
                                    @click="declineModalOpen = false"
                                >
                                    {{ t('invitations.keep_invitation') }}
                                </Button>

                                <Button
                                    type="button"
                                    class="h-12 rounded-2xl bg-rose-600 px-4 text-white hover:bg-rose-700"
                                    :disabled="form.processing"
                                    @click="submitDeclined"
                                >
                                    {{ t('invitations.confirm_decline') }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
