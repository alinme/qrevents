<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    CalendarDays,
    Clock3,
    MapPin,
    Plus,
    Sparkles,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import InputError from '@/components/InputError.vue';
import ChoiceCardRadioGroup from '@/components/onboarding/ChoiceCardRadioGroup.vue';
import PrettyDatePicker from '@/components/onboarding/PrettyDatePicker.vue';
import PrettyTimePicker from '@/components/onboarding/PrettyTimePicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { home } from '@/routes';
import { store } from '@/routes/onboarding';

type EventSubEventOption = {
    key: string;
    label: string;
    description: string;
    imageUrl: string;
};

type EventTypeOption = {
    value: string;
    label: string;
    description: string;
    imageUrl: string;
    subEvents: EventSubEventOption[];
};

type EventDateInput = {
    label: string;
    date: string;
};

type SelectedSubEvent = {
    key: string;
    label: string;
    date: string;
    start_time: string;
};

type OnboardingStep = 1 | 2 | 3;

const props = defineProps<{
    eventTypes: EventTypeOption[];
    defaultTimezone: string;
}>();

const step = ref<OnboardingStep>(1);
const wizardPanelRef = ref<HTMLElement | null>(null);
const formActionsRef = ref<HTMLElement | null>(null);
const lastSuggestedWeddingTitle = ref('');

const stepItems = [
    {
        number: 1,
        label: 'Format',
        title: 'Choose the kind of event',
        description: 'We only ask relevant planning questions for the event you are actually running.',
    },
    {
        number: 2,
        label: 'Essentials',
        title: 'Capture the basics',
        description: 'Name, location, and guest estimate help shape invitations, menus, and recommendations later.',
    },
    {
        number: 3,
        label: 'Timeline',
        title: 'Map dates and moments',
        description: 'Support multi-day events and the sub-events that matter for this format.',
    },
] as const;

const attendeePresets = [30, 75, 150, 300, 600, 1200] as const;

const createEventDate = (label = ''): EventDateInput => ({
    label,
    date: '',
});

const form = useForm({
    type: props.eventTypes[0]?.value ?? 'wedding',
    name: '',
    wedding_partner_one_first_name: '',
    wedding_partner_two_first_name: '',
    wedding_family_name: '',
    venue_address: '',
    attendee_estimate: '',
    event_dates: [createEventDate('Main day')],
    sub_events: [] as SelectedSubEvent[],
    timezone: props.defaultTimezone,
});

const selectedType = computed<EventTypeOption | undefined>(() =>
    props.eventTypes.find((eventType) => eventType.value === form.type),
);

const availableSubEvents = computed<EventSubEventOption[]>(() => selectedType.value?.subEvents ?? []);

const selectedSubEventKeys = computed(() => new Set(form.sub_events.map((subEvent) => subEvent.key)));

const reviewEventDates = computed(() =>
    form.event_dates.filter((eventDate) => eventDate.date.trim() !== ''),
);

const isWeddingType = computed(() => form.type === 'wedding');

const firstKnownDate = computed(() => reviewEventDates.value[0]?.date ?? '');

const progressWidth = computed(() => `${((step.value - 1) / (stepItems.length - 1)) * 100}%`);

const canMoveToNext = computed(() => {
    if (step.value === 1) {
        return Boolean(selectedType.value);
    }

    if (step.value === 2) {
        const hasWeddingNamingFields = !isWeddingType.value || (
            form.wedding_partner_one_first_name.trim().length >= 2
            && form.wedding_partner_two_first_name.trim().length >= 2
            && form.wedding_family_name.trim().length >= 2
        );

        return hasWeddingNamingFields
            && form.name.trim().length >= 3
            && form.venue_address.trim().length >= 6
            && Number(form.attendee_estimate) > 0;
    }

    return reviewEventDates.value.length > 0
        && form.sub_events.every((subEvent) => subEvent.date.trim() !== '' && subEvent.start_time.trim() !== '');
});

const scrollToWizardPanel = (): void => {
    if (typeof window === 'undefined' || wizardPanelRef.value === null) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    window.setTimeout(() => {
        wizardPanelRef.value?.scrollIntoView({
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
            block: 'start',
        });
    }, 40);
};

const scrollToFormActions = (): void => {
    if (typeof window === 'undefined' || formActionsRef.value === null) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    window.setTimeout(() => {
        formActionsRef.value?.scrollIntoView({
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
            block: 'center',
        });
    }, 40);
};

const titleCaseWords = (value: string): string =>
    value
        .trim()
        .split(/\s+/)
        .filter((part) => part.length > 0)
        .map((part) => `${part.charAt(0).toUpperCase()}${part.slice(1)}`)
        .join(' ');

const weddingTitleSuggestions = computed(() => {
    if (!isWeddingType.value) {
        return [] as string[];
    }

    const partnerOneFirstName = titleCaseWords(form.wedding_partner_one_first_name);
    const partnerTwoFirstName = titleCaseWords(form.wedding_partner_two_first_name);
    const familyName = titleCaseWords(form.wedding_family_name);

    if (partnerOneFirstName === '' || partnerTwoFirstName === '' || familyName === '') {
        return [] as string[];
    }

    return Array.from(new Set([
        `${partnerOneFirstName} & ${partnerTwoFirstName} ${familyName} Wedding`,
        `${partnerOneFirstName} & ${partnerTwoFirstName} ${familyName} Wedding Weekend`,
        `${partnerOneFirstName} & ${partnerTwoFirstName} Wedding Day`,
        `${partnerOneFirstName} and ${partnerTwoFirstName} Wedding Celebration`,
        `${partnerOneFirstName} + ${partnerTwoFirstName} | Wedding Weekend`,
        `The ${familyName} Wedding`,
        `The ${familyName} Wedding Weekend`,
        `${familyName} Family Wedding Celebration`,
        `Celebrating ${partnerOneFirstName} & ${partnerTwoFirstName}`,
        `${partnerOneFirstName} & ${partnerTwoFirstName} Say Yes`,
        `${partnerOneFirstName} & ${partnerTwoFirstName} Forever Starts Here`,
        `${partnerOneFirstName} and ${partnerTwoFirstName} | ${familyName} Celebration`,
    ]));
});

const weddingTitlePlaceholder = computed(() =>
    weddingTitleSuggestions.value[0] ?? 'Dan and Rachel Wedding Weekend',
);

const applyWeddingTitleSuggestion = (value: string): void => {
    form.name = value;
    lastSuggestedWeddingTitle.value = value;
};

const syncSuggestedWeddingTitle = (): void => {
    if (!isWeddingType.value || weddingTitleSuggestions.value.length === 0) {
        return;
    }

    const nextSuggestedTitle = weddingTitleSuggestions.value[0] ?? '';
    const currentTitle = form.name.trim();

    if (
        currentTitle === ''
        || currentTitle === lastSuggestedWeddingTitle.value
        || weddingTitleSuggestions.value.includes(currentTitle)
    ) {
        form.name = nextSuggestedTitle;
        lastSuggestedWeddingTitle.value = nextSuggestedTitle;
    }
};

watch(
    () => form.type,
    (nextType, previousType) => {
        const allowedSubEventKeys = new Set(availableSubEvents.value.map((subEvent) => subEvent.key));
        form.sub_events = form.sub_events.filter((subEvent) => allowedSubEventKeys.has(subEvent.key));

        if (step.value === 1 && nextType !== previousType) {
            void nextTick(() => {
                scrollToFormActions();
            });
        }
    },
);

watch(
    () => [
        form.type,
        form.wedding_partner_one_first_name,
        form.wedding_partner_two_first_name,
        form.wedding_family_name,
    ],
    () => {
        syncSuggestedWeddingTitle();
    },
);

const handleEventTypeCardClick = (): void => {
    if (step.value !== 1) {
        return;
    }

    scrollToFormActions();
};

const goToNext = (): void => {
    if (!canMoveToNext.value || step.value === 3) {
        return;
    }

    step.value = (step.value + 1) as OnboardingStep;
    void nextTick(() => {
        scrollToWizardPanel();
    });
};

const goToPrevious = (): void => {
    if (step.value === 1) {
        return;
    }

    step.value = (step.value - 1) as OnboardingStep;
    void nextTick(() => {
        scrollToWizardPanel();
    });
};

const addEventDate = (): void => {
    if (form.event_dates.length >= 6) {
        return;
    }

    form.event_dates.push(createEventDate(`Day ${form.event_dates.length + 1}`));
};

const removeEventDate = (index: number): void => {
    if (form.event_dates.length === 1) {
        form.event_dates[0] = createEventDate('Main day');

        return;
    }

    form.event_dates.splice(index, 1);
};

const applyAttendeePreset = (value: number): void => {
    form.attendee_estimate = String(value);
};

const isSubEventSelected = (key: string): boolean => selectedSubEventKeys.value.has(key);

const toggleSubEvent = (subEventOption: EventSubEventOption): void => {
    const existingSubEventIndex = form.sub_events.findIndex((subEvent) => subEvent.key === subEventOption.key);

    if (existingSubEventIndex >= 0) {
        form.sub_events.splice(existingSubEventIndex, 1);

        return;
    }

    form.sub_events.push({
        key: subEventOption.key,
        label: subEventOption.label,
        date: firstKnownDate.value,
        start_time: '',
    });
};

const removeSubEvent = (key: string): void => {
    const existingSubEventIndex = form.sub_events.findIndex((subEvent) => subEvent.key === key);

    if (existingSubEventIndex >= 0) {
        form.sub_events.splice(existingSubEventIndex, 1);
    }
};

const fieldError = (field: string): string | undefined => {
    const errors = form.errors as Record<string, string | undefined>;

    return errors[field];
};

const submit = (): void => {
    form.transform((data) => {
        const eventDates = data.event_dates
            .map((eventDate, index) => ({
                label: eventDate.label.trim() !== '' ? eventDate.label.trim() : `Event day ${index + 1}`,
                date: eventDate.date,
            }))
            .filter((eventDate) => eventDate.date.trim() !== '');
        const subEvents = data.sub_events.map((subEvent) => ({
            key: subEvent.key,
            label: subEvent.label,
            date: subEvent.date,
            start_time: subEvent.start_time,
        }));

        return {
            ...data,
            attendee_estimate: Number(data.attendee_estimate),
            event_date: eventDates[0]?.date ?? subEvents[0]?.date ?? null,
            event_dates: eventDates,
            sub_events: subEvents,
        };
    }).post(store.url(), {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.type) {
                step.value = 1;
                void nextTick(() => {
                    scrollToWizardPanel();
                });

                return;
            }

            if (errors.name || errors.venue_address || errors.attendee_estimate) {
                step.value = 2;
                void nextTick(() => {
                    scrollToWizardPanel();
                });

                return;
            }

            step.value = 3;
            void nextTick(() => {
                scrollToWizardPanel();
            });
        },
    });
};
</script>

<template>
    <Head title="Create your event" />

    <div class="min-h-svh bg-[linear-gradient(180deg,oklch(0.985_0.014_338)_0%,oklch(0.975_0.018_338)_52%,oklch(0.988_0.008_28)_100%)] text-promo-ink">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden">
            <div class="mx-auto max-w-7xl">
                <div class="relative h-[24rem]">
                    <div class="absolute left-[-5rem] top-[-7rem] h-[18rem] w-[18rem] rounded-full bg-promo-purple/55 blur-3xl" />
                    <div class="absolute right-[-4rem] top-[1rem] h-[20rem] w-[20rem] rounded-full bg-promo-surface-strong/70 blur-3xl" />
                </div>
            </div>
        </div>

        <div class="mx-auto flex min-h-svh max-w-7xl flex-col px-5 py-6 lg:px-8 lg:py-8">
            <header class="flex flex-col gap-4 rounded-[28px] border border-promo-line/80 bg-white/80 px-5 py-4 shadow-[0_18px_48px_rgba(232,79,154,0.08)] backdrop-blur-sm lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="home()" class="inline-flex items-center gap-3">
                        <div class="flex size-11 items-center justify-center rounded-[16px] bg-linear-to-br from-promo-primary to-promo-primary-strong text-white shadow-[0_12px_28px_rgba(232,79,154,0.22)]">
                            <AppLogoIcon class="size-7 fill-current" />
                        </div>
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                QR Events
                            </p>
                            <p class="text-lg font-extrabold tracking-[-0.04em] text-promo-ink">
                                Event planning setup
                            </p>
                        </div>
                    </Link>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-full bg-promo-surface px-4 py-2 text-sm font-medium text-promo-muted">
                        Setup takes about 2 minutes
                    </div>
                    <div class="rounded-full border border-promo-line px-4 py-2 text-sm font-medium text-promo-muted">
                        Step {{ step }} of {{ stepItems.length }}
                    </div>
                </div>
            </header>

            <main class="grid flex-1 gap-6 py-6 lg:grid-cols-[18rem_minmax(0,1fr)] lg:py-8">
                <aside class="lg:sticky lg:top-8 lg:self-start">
                    <div class="overflow-hidden rounded-[30px] border border-promo-line bg-white shadow-[0_22px_60px_rgba(120,86,255,0.08)]">
                        <div class="border-b border-promo-line bg-promo-surface/70 px-5 py-5">
                            <p class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-promo-primary">
                                <Sparkles class="size-3.5" />
                                Why this matters
                            </p>
                            <h1 class="mt-4 text-2xl font-extrabold tracking-[-0.05em] text-promo-ink">
                                Plan it once. Use it everywhere.
                            </h1>
                            <p class="mt-3 text-sm leading-7 text-promo-muted">
                                This wizard is for event setup, not account access. The details here will later support invitations, menus, guest flows, supplier suggestions, and smarter event tools.
                            </p>
                        </div>

                        <div class="space-y-3 px-4 py-4">
                            <article
                                v-for="stepItem in stepItems"
                                :key="stepItem.number"
                                class="relative overflow-hidden rounded-[22px] border px-4 py-4 transition-colors duration-200"
                                :class="
                                    stepItem.number === step
                                        ? 'border-promo-primary/30 bg-promo-surface'
                                        : stepItem.number < step
                                          ? 'border-promo-line bg-promo-surface/45'
                                          : 'border-promo-line bg-white'
                                "
                            >
                                <span
                                    class="pointer-events-none absolute right-1 top-1/2 -translate-y-1/2 select-none text-[6rem] leading-none font-black tracking-[-0.12em] transition-colors duration-200"
                                    :class="
                                        stepItem.number === step
                                            ? 'text-promo-primary/20'
                                            : stepItem.number < step
                                              ? 'text-promo-primary/14'
                                              : 'text-neutral-200'
                                    "
                                >
                                    0{{ stepItem.number }}
                                </span>

                                <div class="relative max-w-[10.5rem]">
                                    <p
                                        class="text-[0.68rem] font-semibold uppercase tracking-[0.22em]"
                                        :class="stepItem.number <= step ? 'text-promo-primary' : 'text-promo-muted'"
                                    >
                                        {{ stepItem.label }}
                                    </p>
                                    <h2 class="mt-1 text-sm font-semibold text-promo-ink">
                                        {{ stepItem.title }}
                                    </h2>
                                    <p class="mt-1.5 text-sm leading-6 text-promo-muted">
                                        {{ stepItem.description }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>
                </aside>

                <section
                    ref="wizardPanelRef"
                    class="scroll-mt-6 overflow-hidden rounded-[32px] border border-promo-line bg-white shadow-[0_26px_70px_rgba(232,79,154,0.10)] lg:scroll-mt-8"
                >
                    <div class="border-b border-promo-line px-6 py-6 sm:px-8">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                    {{ stepItems[step - 1]?.label }}
                                </p>
                                <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.05em] text-promo-ink">
                                    {{ stepItems[step - 1]?.title }}
                                </h2>
                                <p class="mt-3 max-w-2xl text-sm leading-7 text-promo-muted">
                                    {{ stepItems[step - 1]?.description }}
                                </p>
                            </div>

                            <div class="min-w-0 lg:w-72">
                                <div class="h-2 rounded-full bg-promo-surface">
                                    <div
                                        class="h-full rounded-full bg-linear-to-r from-promo-primary to-promo-primary-strong transition-all duration-300"
                                        :style="{ width: progressWidth }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <form class="space-y-8 px-6 py-6 sm:px-8 sm:py-8" @submit.prevent="submit">
                        <section v-if="step === 1" class="space-y-5">
                            <ChoiceCardRadioGroup
                                v-model="form.type"
                                :options="props.eventTypes"
                                @option-click="handleEventTypeCardClick"
                            />

                            <InputError :message="form.errors.type" />
                        </section>

                        <section v-if="step === 2" class="space-y-8">
                            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
                                <div class="space-y-5">
                                    <div
                                        v-if="isWeddingType"
                                        class="rounded-[26px] border border-promo-line bg-promo-surface/45 p-5"
                                    >
                                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                            <Sparkles class="size-3.5" />
                                            Wedding naming
                                        </div>

                                        <h3 class="mt-4 text-lg font-bold tracking-[-0.04em] text-promo-ink">
                                            We can build the wedding title for you
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                                            Add the couple&apos;s first names and the family name. We&apos;ll suggest polished event titles you can use right away.
                                        </p>

                                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label for="wedding_partner_one_first_name" class="text-sm font-semibold text-promo-ink">
                                                    Groom / partner one
                                                </Label>
                                                <Input
                                                    id="wedding_partner_one_first_name"
                                                    v-model="form.wedding_partner_one_first_name"
                                                    name="wedding_partner_one_first_name"
                                                    placeholder="Andrei"
                                                    class="h-12 rounded-[20px] border-promo-line bg-white"
                                                />
                                                <InputError :message="form.errors.wedding_partner_one_first_name" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label for="wedding_partner_two_first_name" class="text-sm font-semibold text-promo-ink">
                                                    Bride / partner two
                                                </Label>
                                                <Input
                                                    id="wedding_partner_two_first_name"
                                                    v-model="form.wedding_partner_two_first_name"
                                                    name="wedding_partner_two_first_name"
                                                    placeholder="Maria"
                                                    class="h-12 rounded-[20px] border-promo-line bg-white"
                                                />
                                                <InputError :message="form.errors.wedding_partner_two_first_name" />
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2">
                                            <Label for="wedding_family_name" class="text-sm font-semibold text-promo-ink">
                                                Family name
                                            </Label>
                                            <Input
                                                id="wedding_family_name"
                                                v-model="form.wedding_family_name"
                                                name="wedding_family_name"
                                                placeholder="Popescu"
                                                class="h-12 rounded-[20px] border-promo-line bg-white"
                                            />
                                            <InputError :message="form.errors.wedding_family_name" />
                                        </div>

                                        <div
                                            v-if="weddingTitleSuggestions.length > 0"
                                            class="mt-5 space-y-3"
                                        >
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-sm font-semibold text-promo-ink">
                                                    Suggested titles
                                                </p>
                                                <p class="text-xs text-promo-muted">
                                                    Tap one to use it
                                                </p>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <button
                                                    v-for="suggestion in weddingTitleSuggestions"
                                                    :key="suggestion"
                                                    type="button"
                                                    class="rounded-[18px] border px-4 py-3 text-left text-sm font-medium transition-colors duration-200"
                                                    :class="
                                                        form.name.trim() === suggestion
                                                            ? 'border-promo-primary bg-white text-promo-ink shadow-[0_12px_24px_rgba(232,79,154,0.10)]'
                                                            : 'border-promo-line bg-white/70 text-promo-muted hover:bg-white'
                                                    "
                                                    @click="applyWeddingTitleSuggestion(suggestion)"
                                                >
                                                    {{ suggestion }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="name" class="text-sm font-semibold text-promo-ink">
                                            Event name
                                        </Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            name="name"
                                            :placeholder="weddingTitlePlaceholder"
                                            class="h-12 rounded-[20px] border-promo-line bg-promo-surface/40"
                                        />
                                        <p
                                            v-if="isWeddingType"
                                            class="text-xs leading-5 text-promo-muted"
                                        >
                                            You can keep one of the suggested titles or write your own.
                                        </p>
                                        <InputError :message="form.errors.name" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="venue_address" class="text-sm font-semibold text-promo-ink">
                                            Event address
                                        </Label>
                                        <Textarea
                                            id="venue_address"
                                            v-model="form.venue_address"
                                            name="venue_address"
                                            placeholder="Venue name, street, city, and anything guests need in order to find it easily."
                                            class="min-h-28 rounded-[22px] border-promo-line bg-promo-surface/40"
                                        />
                                        <InputError :message="form.errors.venue_address" />
                                    </div>
                                </div>

                                <div class="rounded-[26px] border border-promo-line bg-promo-surface/55 p-5">
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                        <Users class="size-3.5" />
                                        Guest planning
                                    </div>

                                    <h3 class="mt-4 text-lg font-bold tracking-[-0.04em] text-promo-ink">
                                        How many attendees are you expecting?
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        An estimate is enough. This will help later with planning recommendations and supplier-related features.
                                    </p>

                                    <div class="mt-5 grid grid-cols-2 gap-3">
                                        <button
                                            v-for="preset in attendeePresets"
                                            :key="preset"
                                            type="button"
                                            class="rounded-[18px] border px-4 py-3 text-left transition-colors duration-200"
                                            :class="
                                                Number(form.attendee_estimate) === preset
                                                    ? 'border-promo-primary bg-white text-promo-ink'
                                                    : 'border-promo-line bg-white/70 text-promo-muted hover:bg-white'
                                            "
                                            @click="applyAttendeePreset(preset)"
                                        >
                                            <span class="block text-lg font-bold text-current">
                                                {{ preset }}
                                            </span>
                                            <span class="block text-[0.68rem] uppercase tracking-[0.2em]">
                                                guests
                                            </span>
                                        </button>
                                    </div>

                                    <div class="mt-4 grid gap-2">
                                        <Label for="attendee_estimate" class="text-sm font-semibold text-promo-ink">
                                            Custom estimate
                                        </Label>
                                        <Input
                                            id="attendee_estimate"
                                            v-model="form.attendee_estimate"
                                            name="attendee_estimate"
                                            type="number"
                                            min="1"
                                            step="1"
                                            placeholder="Type a number"
                                            class="h-12 rounded-[20px] border-promo-line bg-white"
                                        />
                                        <InputError :message="form.errors.attendee_estimate" />
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section v-if="step === 3" class="space-y-8">
                            <div class="rounded-[28px] border border-promo-line bg-promo-surface/45 p-5 sm:p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                            <CalendarDays class="size-3.5" />
                                            Event dates
                                        </div>
                                        <h3 class="mt-4 text-xl font-bold tracking-[-0.04em] text-promo-ink">
                                            Add every day that matters
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                                            One date works, but this can also support rehearsal dinners, second days, conferences, or multi-day celebrations.
                                        </p>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="rounded-full border-promo-line bg-white text-promo-ink"
                                        :disabled="form.event_dates.length >= 6"
                                        @click="addEventDate"
                                    >
                                        <Plus class="mr-2 size-4" />
                                        Add date
                                    </Button>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div
                                        v-for="(eventDate, index) in form.event_dates"
                                        :key="`event-date-${index}`"
                                        class="rounded-[22px] border border-promo-line bg-white p-4"
                                    >
                                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_16rem_auto] lg:items-end">
                                            <div class="grid gap-2">
                                                <Label :for="`event-date-label-${index}`" class="text-sm font-semibold text-promo-ink">
                                                    Label
                                                </Label>
                                                <Input
                                                    :id="`event-date-label-${index}`"
                                                    v-model="eventDate.label"
                                                    :name="`event_dates.${index}.label`"
                                                    placeholder="Main day, church day, rehearsal, second day..."
                                                    class="h-11 rounded-[18px] border-promo-line bg-promo-surface/35"
                                                />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label :for="`event-date-date-${index}`" class="text-sm font-semibold text-promo-ink">
                                                    Date
                                                </Label>
                                                <PrettyDatePicker
                                                    :id="`event-date-date-${index}`"
                                                    v-model="eventDate.date"
                                                    placeholder="Choose event day"
                                                />
                                            </div>

                                            <Button
                                                type="button"
                                                variant="ghost"
                                                class="h-11 rounded-full text-promo-muted"
                                                :disabled="form.event_dates.length === 1"
                                                @click="removeEventDate(index)"
                                            >
                                                <X class="size-4" />
                                            </Button>
                                        </div>

                                        <InputError :message="fieldError(`event_dates.${index}.date`)" class="mt-3" />
                                    </div>
                                </div>

                                <InputError :message="form.errors.event_dates" class="mt-3" />
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <div class="inline-flex items-center gap-2 rounded-full bg-promo-surface px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-promo-primary">
                                        <Clock3 class="size-3.5" />
                                        Relevant moments
                                    </div>
                                    <h3 class="mt-4 text-xl font-bold tracking-[-0.04em] text-promo-ink">
                                        Pick the moments you need for {{ selectedType?.label ?? 'this event' }}
                                    </h3>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        We only show moments that make sense for the selected event type.
                                    </p>
                                </div>

                                <div class="grid gap-4 xl:grid-cols-2">
                                    <button
                                        v-for="subEventOption in availableSubEvents"
                                        :key="subEventOption.key"
                                        type="button"
                                        class="group relative overflow-hidden rounded-[26px] border text-left transition duration-200 hover:-translate-y-0.5"
                                        :class="
                                            isSubEventSelected(subEventOption.key)
                                                ? 'border-promo-primary shadow-[0_18px_44px_rgba(232,79,154,0.16)]'
                                                : 'border-neutral-300 shadow-[0_12px_34px_rgba(17,24,39,0.06)]'
                                        "
                                        @click="toggleSubEvent(subEventOption)"
                                    >
                                        <div
                                            class="absolute inset-0 bg-cover bg-center"
                                            :class="
                                                isSubEventSelected(subEventOption.key)
                                                    ? 'grayscale-0 saturate-110'
                                                    : 'grayscale contrast-110'
                                            "
                                            :style="{ backgroundImage: `url(${subEventOption.imageUrl})` }"
                                        />
                                        <div
                                            class="absolute inset-0 transition duration-300"
                                            :class="
                                                isSubEventSelected(subEventOption.key)
                                                    ? 'bg-linear-to-t from-[rgba(42,22,35,0.92)] via-[rgba(42,22,35,0.34)] to-[rgba(255,255,255,0.04)]'
                                                    : 'bg-linear-to-t from-[rgba(17,17,17,0.84)] via-[rgba(17,17,17,0.42)] to-[rgba(255,255,255,0.08)]'
                                            "
                                        />

                                        <div class="relative flex min-h-52 flex-col justify-between p-5 text-white">
                                            <div class="flex items-start justify-between gap-3">
                                                <span
                                                    class="rounded-full px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.2em] backdrop-blur-sm transition duration-300"
                                                    :class="
                                                        isSubEventSelected(subEventOption.key)
                                                            ? 'bg-white/18 text-white'
                                                            : 'bg-white/88 text-neutral-900'
                                                    "
                                                >
                                                    Optional sub-event
                                                </span>

                                                <span
                                                    class="size-3 rounded-full border transition duration-300"
                                                    :class="
                                                        isSubEventSelected(subEventOption.key)
                                                            ? 'border-promo-primary bg-promo-primary shadow-[0_0_0_4px_rgba(255,255,255,0.22)]'
                                                            : 'border-white/95 bg-white/85'
                                                    "
                                                >
                                                </span>
                                            </div>

                                            <div>
                                                <h4 class="text-2xl font-extrabold tracking-[-0.04em]">
                                                    {{ subEventOption.label }}
                                                </h4>
                                                <p class="mt-3 max-w-sm text-sm leading-6 text-white/84">
                                                    {{ subEventOption.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="form.sub_events.length > 0"
                                class="rounded-[28px] border border-promo-line bg-white p-5 sm:p-6"
                            >
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold tracking-[-0.04em] text-promo-ink">
                                            Schedule the selected moments
                                        </h3>
                                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                                            Each selected moment needs a date and a start time.
                                        </p>
                                    </div>

                                    <div class="rounded-full bg-promo-surface px-4 py-2 text-sm text-promo-muted">
                                        {{ form.sub_events.length }} moment{{ form.sub_events.length === 1 ? '' : 's' }} selected
                                    </div>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div
                                        v-for="(subEvent, index) in form.sub_events"
                                        :key="subEvent.key"
                                        class="rounded-[22px] border border-promo-line bg-promo-surface/35 p-4"
                                    >
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-base font-semibold text-promo-ink">
                                                    {{ subEvent.label }}
                                                </p>
                                                <p class="mt-1 text-sm text-promo-muted">
                                                    Add the date and starting hour for this part of the event.
                                                </p>
                                            </div>

                                            <button
                                                type="button"
                                                class="rounded-full p-2 text-promo-muted transition-colors hover:bg-white"
                                                @click="removeSubEvent(subEvent.key)"
                                            >
                                                <X class="size-4" />
                                            </button>
                                        </div>

                                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label :for="`sub-event-date-${index}`" class="text-sm font-semibold text-promo-ink">
                                                    Date
                                                </Label>
                                                <PrettyDatePicker
                                                    :id="`sub-event-date-${index}`"
                                                    v-model="subEvent.date"
                                                    placeholder="Choose sub-event day"
                                                />
                                                <InputError :message="fieldError(`sub_events.${index}.date`)" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label :for="`sub-event-time-${index}`" class="text-sm font-semibold text-promo-ink">
                                                    Start time
                                                </Label>
                                                <PrettyTimePicker
                                                    :id="`sub-event-time-${index}`"
                                                    v-model="subEvent.start_time"
                                                    placeholder="Choose start time"
                                                />
                                                <InputError :message="fieldError(`sub_events.${index}.start_time`)" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div
                            ref="formActionsRef"
                            class="flex flex-col gap-4 rounded-[24px] border border-promo-line bg-promo-surface/35 px-5 py-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div class="grid gap-2 text-sm text-promo-muted sm:grid-cols-3 sm:gap-4">
                                <div class="inline-flex items-center gap-2">
                                    <MapPin class="size-4 text-promo-primary" />
                                    {{ form.venue_address.trim() !== '' ? 'Address added' : 'Address pending' }}
                                </div>
                                <div class="inline-flex items-center gap-2">
                                    <CalendarDays class="size-4 text-promo-primary" />
                                    {{ reviewEventDates.length }} date{{ reviewEventDates.length === 1 ? '' : 's' }}
                                </div>
                                <div class="inline-flex items-center gap-2">
                                    <Users class="size-4 text-promo-primary" />
                                    {{ form.attendee_estimate || '0' }} guests
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-3 sm:flex-row">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    class="rounded-full text-promo-muted hover:text-promo-ink"
                                    :disabled="step === 1 || form.processing"
                                    @click="goToPrevious"
                                >
                                    <ArrowLeft class="mr-2 size-4" />
                                    Back
                                </Button>

                                <Button
                                    v-if="step < 3"
                                    type="button"
                                    class="rounded-full bg-promo-primary px-6 text-white hover:bg-promo-primary-strong"
                                    :disabled="!canMoveToNext || form.processing"
                                    @click="goToNext"
                                >
                                    Continue
                                    <ArrowRight class="ml-2 size-4" />
                                </Button>

                                <Button
                                    v-else
                                    type="submit"
                                    class="rounded-full bg-promo-primary px-6 text-white hover:bg-promo-primary-strong"
                                    :disabled="form.processing || !canMoveToNext"
                                >
                                    <Spinner v-if="form.processing" class="mr-2" />
                                    Create my event
                                </Button>
                            </div>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>
</template>
