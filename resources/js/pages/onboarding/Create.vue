<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    BadgeCheck,
    CalendarDays,
    Check,
    LogOut,
    PartyPopper,
    Sparkles,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home, logout } from '@/routes';

type EventTypeOption = {
    value: string;
    label: string;
    description: string;
    imageUrl: string;
};

const props = defineProps<{
    defaultTimezone: string;
    owner: { name: string; email: string } | null;
    eventTypes: EventTypeOption[];
    submitUrl: string;
}>();

const currentStep = ref(1);
const totalSteps = 3;

const form = useForm({
    type: '',
    name: '',
    wedding_partner_one_first_name: '',
    wedding_partner_two_first_name: '',
    wedding_family_name: '',
    event_date: '',
    timezone: props.defaultTimezone,
});

const steps = [
    { number: 1, label: 'Occasion' },
    { number: 2, label: 'Name' },
    { number: 3, label: 'Date' },
];

const selectedType = computed(() =>
    props.eventTypes.find((option) => option.value === form.type),
);
const isWeddingType = computed(() => form.type === 'wedding');

const titleCaseWords = (value: string): string =>
    value
        .trim()
        .split(/\s+/)
        .filter((word) => word.length > 0)
        .map(
            (word) =>
                word.charAt(0).toLocaleUpperCase() +
                word.slice(1).toLocaleLowerCase(),
        )
        .join(' ');

const weddingTitleSuggestions = computed(() => {
    if (!isWeddingType.value) {
        return [] as string[];
    }

    const partnerOne = titleCaseWords(form.wedding_partner_one_first_name);
    const partnerTwo = titleCaseWords(form.wedding_partner_two_first_name);
    const familyName = titleCaseWords(form.wedding_family_name);

    if (partnerOne === '' || partnerTwo === '') {
        return [] as string[];
    }

    return Array.from(
        new Set(
            [
                `${partnerOne} & ${partnerTwo}`,
                familyName !== '' ? `The ${familyName} Wedding` : '',
                `${partnerOne} & ${partnerTwo} Wedding Day`,
                familyName !== ''
                    ? `${partnerOne} & ${partnerTwo} ${familyName} Wedding`
                    : '',
                `Celebrating ${partnerOne} & ${partnerTwo}`,
            ].filter((title) => title !== ''),
        ),
    ).slice(0, 4);
});

const namePlaceholder = computed(() => {
    switch (form.type) {
        case 'wedding':
            return 'Ana & Luca Wedding';
        case 'birthday':
            return "Maria's 30th Birthday";
        case 'engagement':
            return 'Ana & Luca Engagement Party';
        case 'baptism':
            return "Little Sofia's Baptism";
        case 'party':
            return 'Summer Rooftop Party';
        default:
            return 'Our Celebration';
    }
});

const minEventDate = new Date().toISOString().slice(0, 10);

const selectType = (value: string): void => {
    form.type = value;
    form.clearErrors('type');
    currentStep.value = 2;
};

const stepTwoValid = computed(() => form.name.trim().length >= 3);
const stepThreeValid = computed(() => form.event_date !== '');

const goBack = (): void => {
    if (currentStep.value > 1) {
        currentStep.value -= 1;
    }
};

const continueToDate = (): void => {
    if (stepTwoValid.value) {
        form.clearErrors('name');
        currentStep.value = 3;
    }
};

const fieldToStep: Record<string, number> = {
    type: 1,
    name: 2,
    wedding_partner_one_first_name: 2,
    wedding_partner_two_first_name: 2,
    wedding_family_name: 2,
    event_date: 3,
};

const submit = (): void => {
    form.post(props.submitUrl, {
        onError: (errors) => {
            const firstField = Object.keys(errors)[0];
            const step = firstField ? fieldToStep[firstField] : undefined;
            if (step !== undefined) {
                currentStep.value = step;
            }
        },
    });
};
</script>

<template>
    <Head title="Create your album" />

    <div class="flex min-h-screen flex-col bg-white text-promo-ink">
        <header class="border-b border-promo-line">
            <div
                class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-4 sm:px-6"
            >
                <Link :href="home()" class="flex items-center">
                    <img
                        src="/logo.png"
                        alt="EventSmart"
                        width="124"
                        height="36"
                        class="h-8 w-auto object-contain"
                    />
                </Link>

                <div class="flex items-center gap-3">
                    <span
                        v-if="owner"
                        class="hidden text-sm text-promo-muted sm:block"
                    >
                        {{ owner.email }}
                    </span>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-2 rounded-full border border-promo-line bg-white px-4 py-2 text-sm font-medium text-promo-ink transition hover:bg-promo-surface"
                    >
                        <LogOut class="size-4" />
                        Log out
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-3xl flex-1 px-4 py-10 sm:px-6">
            <div class="mb-10 flex items-center justify-center gap-2">
                <template v-for="step in steps" :key="step.number">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-8 items-center justify-center rounded-full text-sm font-semibold transition"
                            :class="
                                currentStep > step.number
                                    ? 'bg-promo-primary text-white'
                                    : currentStep === step.number
                                      ? 'bg-promo-ink text-white'
                                      : 'bg-promo-surface-strong text-promo-muted'
                            "
                        >
                            <Check
                                v-if="currentStep > step.number"
                                class="size-4"
                            />
                            <template v-else>{{ step.number }}</template>
                        </span>
                        <span
                            class="hidden text-sm font-medium sm:block"
                            :class="
                                currentStep >= step.number
                                    ? 'text-promo-ink'
                                    : 'text-promo-muted'
                            "
                        >
                            {{ step.label }}
                        </span>
                    </div>
                    <span
                        v-if="step.number < totalSteps"
                        class="h-px w-8 bg-promo-line sm:w-12"
                    />
                </template>
            </div>

            <!-- Step 1: Occasion -->
            <section v-if="currentStep === 1">
                <div class="text-center">
                    <p class="marketing-kicker inline-flex items-center gap-2">
                        <PartyPopper class="size-4" />
                        Step 1 of 3
                    </p>
                    <h1
                        class="mt-3 text-3xl font-bold tracking-[-0.02em] sm:text-4xl"
                    >
                        What are you celebrating?
                    </h1>
                    <p class="mt-3 text-base text-promo-muted">
                        We'll tailor the album around your occasion.
                    </p>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <button
                        v-for="option in eventTypes"
                        :key="option.value"
                        type="button"
                        class="group overflow-hidden rounded-[1.25rem] text-left shadow-card transition hover:shadow-card-hover"
                        :class="
                            form.type === option.value
                                ? 'ring-2 ring-promo-primary'
                                : ''
                        "
                        @click="selectType(option.value)"
                    >
                        <div class="relative aspect-[16/9] overflow-hidden">
                            <img
                                :src="option.imageUrl"
                                :alt="option.label"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.04]"
                                loading="lazy"
                            />
                        </div>
                        <div class="px-4 py-3">
                            <span class="text-sm font-semibold text-promo-ink">
                                {{ option.label }}
                            </span>
                        </div>
                    </button>
                </div>
                <InputError class="mt-3" :message="form.errors.type" />
            </section>

            <!-- Step 2: Name -->
            <section v-else-if="currentStep === 2" class="mx-auto max-w-xl">
                <div class="text-center">
                    <p class="marketing-kicker inline-flex items-center gap-2">
                        <Sparkles class="size-4" />
                        Step 2 of 3
                    </p>
                    <h1
                        class="mt-3 text-3xl font-bold tracking-[-0.02em] sm:text-4xl"
                    >
                        Name your album
                    </h1>
                    <p class="mt-3 text-base text-promo-muted">
                        This is the title guests see when they join
                        {{
                            selectedType
                                ? `your ${selectedType.label.toLowerCase()}`
                                : 'your event'
                        }}.
                    </p>
                </div>

                <div
                    v-if="isWeddingType"
                    class="mt-8 rounded-[1.25rem] bg-promo-surface/70 p-5"
                >
                    <p class="text-sm font-medium text-promo-ink">
                        Want title ideas? Add your names
                        <span class="font-normal text-promo-muted">
                            (optional)
                        </span>
                    </p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <Input
                            v-model="form.wedding_partner_one_first_name"
                            placeholder="First name"
                            autocomplete="off"
                        />
                        <Input
                            v-model="form.wedding_partner_two_first_name"
                            placeholder="First name"
                            autocomplete="off"
                        />
                        <Input
                            v-model="form.wedding_family_name"
                            placeholder="Family name"
                            autocomplete="off"
                        />
                    </div>
                    <div
                        v-if="weddingTitleSuggestions.length > 0"
                        class="mt-4 flex flex-wrap gap-2"
                    >
                        <button
                            v-for="suggestion in weddingTitleSuggestions"
                            :key="suggestion"
                            type="button"
                            class="rounded-full border px-4 py-2 text-sm font-medium transition"
                            :class="
                                form.name === suggestion
                                    ? 'border-promo-primary bg-promo-primary/10 text-promo-primary'
                                    : 'border-promo-line bg-white text-promo-ink hover:bg-promo-surface'
                            "
                            @click="form.name = suggestion"
                        >
                            {{ suggestion }}
                        </button>
                    </div>
                </div>

                <div class="mt-8 grid gap-2">
                    <Label for="event-name">Album name</Label>
                    <Input
                        id="event-name"
                        v-model="form.name"
                        type="text"
                        :placeholder="namePlaceholder"
                        maxlength="120"
                        class="h-12 text-base"
                        autofocus
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-medium text-promo-muted transition hover:text-promo-ink"
                        @click="goBack"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-promo-ink px-6 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!stepTwoValid"
                        @click="continueToDate"
                    >
                        Continue
                        <ArrowRight class="size-4" />
                    </button>
                </div>
            </section>

            <!-- Step 3: Date -->
            <section v-else class="mx-auto max-w-xl">
                <div class="text-center">
                    <p class="marketing-kicker inline-flex items-center gap-2">
                        <CalendarDays class="size-4" />
                        Step 3 of 3
                    </p>
                    <h1
                        class="mt-3 text-3xl font-bold tracking-[-0.02em] sm:text-4xl"
                    >
                        When is the big day?
                    </h1>
                    <p class="mt-3 text-base text-promo-muted">
                        Uploads open around your date. You can fine-tune
                        everything later in settings.
                    </p>
                </div>

                <div class="mt-8 grid gap-2">
                    <Label for="event-date">Event date</Label>
                    <Input
                        id="event-date"
                        v-model="form.event_date"
                        type="date"
                        :min="minEventDate"
                        class="h-12 text-base"
                    />
                    <InputError :message="form.errors.event_date" />
                </div>

                <div
                    class="mt-8 flex items-start gap-3 rounded-[1.25rem] bg-promo-surface/70 p-5"
                >
                    <span
                        class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                    >
                        <BadgeCheck class="size-5" />
                    </span>
                    <p class="text-sm leading-6 text-promo-muted">
                        <span class="font-semibold text-promo-ink">
                            Free to start — no card needed.
                        </span>
                        Your album begins on the free plan. You can upgrade
                        anytime from your dashboard for more uploads, longer
                        retention, and one-click export.
                    </p>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-medium text-promo-muted transition hover:text-promo-ink"
                        @click="goBack"
                    >
                        <ArrowLeft class="size-4" />
                        Back
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-promo-primary px-7 py-3.5 text-sm font-semibold text-white shadow-[rgba(255,56,92,0.25)_0px_10px_22px] transition hover:bg-promo-primary-strong disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!stepThreeValid || form.processing"
                        @click="submit"
                    >
                        <Spinner v-if="form.processing" />
                        Create my free album
                        <ArrowRight v-if="!form.processing" class="size-4" />
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>
