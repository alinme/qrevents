<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, BriefcaseBusiness, CirclePlay, MonitorPlay, QrCode } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingVisualPlaceholder from '@/components/marketing/MarketingVisualPlaceholder.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { businesses, pricing } from '@/routes';
import { create as onboardingCreate } from '@/routes/onboarding';

const props = defineProps<{
    canRegister: boolean;
    pwaLaunch?: boolean;
}>();

const { t } = useTranslations();

type StoredGuestAlbumHint = {
    shareToken: string;
    albumUrl: string;
    eventName: string;
    guestName: string | null;
    guestToken: string | null;
    logoUrl: string | null;
    savedAt: string;
};

const pageTitle = computed(() => t('marketing.home.meta.title'));
const pageDescription = computed(() => t('marketing.home.meta.description'));
const guestAlbumHintStorageKey = 'qrevents-last-guest-album';
const resumeGuestAlbum = ref<StoredGuestAlbumHint | null>(null);
const resumeGuestAlbumOpen = ref(false);

const quickProof = [
    'Guests join with a QR code or 4-character album code.',
    'The live wall opens on a TV faster with short wall links.',
    'Business teams can top up credits and launch client events from one dashboard.',
];

const walkthrough = [
    {
        step: 'Step 1',
        title: 'Create the event fast',
        body: 'Choose a plan, set the date, and get your album plus wall ready in one flow.',
        label: 'Screenshot placeholder',
        visualTitle: 'Add screenshot: create event screen',
        visualDescription: 'Show the simplified event setup with title, date, plan, and short code information visible.',
    },
    {
        step: 'Step 2',
        title: 'Guests join without downloading an app',
        body: 'Share the QR or the short album code. Guests land straight in the upload flow on mobile.',
        label: 'Short video placeholder',
        visualTitle: 'Add 10s video: guest scans QR and opens album',
        visualDescription: 'Show phone camera scan, the code fallback, and the upload page opening in one quick sequence.',
    },
    {
        step: 'Step 3',
        title: 'Photos land in the album and on the wall',
        body: 'Hosts keep the dashboard, guests keep the upload flow, and the TV wall stays easy to open with the short wall link.',
        label: 'Short video placeholder',
        visualTitle: 'Add 10s video: upload appears on wall',
        visualDescription: 'Show one guest upload and the same photo appearing on the live wall a moment later.',
    },
];

const visualExamples = [
    {
        icon: QrCode,
        title: 'Album access',
        body: 'Explain the 4-character album code and show the exact mobile entry screen.',
        label: 'Mobile screenshot placeholder',
        visualTitle: 'Add screenshot: /album code entry',
        visualDescription: 'Use a real phone screenshot of the PIN-style album access page with the album/wall switch.',
    },
    {
        icon: MonitorPlay,
        title: 'Wall access',
        body: 'Show the short wall link and the TV-friendly wall screen so people understand the new flow immediately.',
        label: 'TV browser placeholder',
        visualTitle: 'Add screenshot: wall opened with short link',
        visualDescription: 'Use a TV or laptop browser example showing the wall opened from the short wall URL.',
    },
    {
        icon: BriefcaseBusiness,
        title: 'Business billing',
        body: 'Show how credit top-ups, billing history, and multi-event creation work for professional users.',
        label: 'Dashboard screenshot placeholder',
        visualTitle: 'Add screenshot: business billing and event list',
        visualDescription: 'Show balance, billing history, and event creation in one clean dashboard capture.',
    },
];

const serviceUpdates = [
    {
        title: 'Album codes are now easier to share',
        description: 'Guests can use a short code instead of typing a long album URL.',
    },
    {
        title: 'Wall links are shorter for TVs',
        description: 'The wall can open from a shorter route, which is easier to type into a TV browser.',
    },
    {
        title: 'Business now uses billing credits',
        description: 'Multi-event teams top up once, then spend credits on each Plus or Pro event they create.',
    },
];

const faqItems = [
    {
        question: 'Do guests need an app?',
        answer: 'No. Guests open a link, scan a QR code, or enter the album code in the browser.',
    },
    {
        question: 'Can I still use the live wall on a TV?',
        answer: 'Yes. The wall is now easier to open with the shorter wall route and the same event code.',
    },
    {
        question: 'What changed for business users?',
        answer: 'Business now uses prepaid billing credits, event-by-event spending, and a dedicated billing dashboard.',
    },
    {
        question: 'Can I start simple and upgrade later?',
        answer: 'Yes. You can start with the normal plans and switch into business mode when you need multi-event billing.',
    },
];

const isStandalonePwa = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(display-mode: standalone)').matches
        || (window.navigator as Navigator & { standalone?: boolean }).standalone === true;
};

const clearStoredGuestAlbum = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.removeItem(guestAlbumHintStorageKey);
    resumeGuestAlbum.value = null;
    resumeGuestAlbumOpen.value = false;
};

const loadStoredGuestAlbum = (): StoredGuestAlbumHint | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    const raw = window.localStorage.getItem(guestAlbumHintStorageKey);
    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw) as Partial<StoredGuestAlbumHint>;
        if (
            typeof parsed.albumUrl !== 'string' ||
            parsed.albumUrl.length === 0 ||
            typeof parsed.eventName !== 'string' ||
            parsed.eventName.length === 0 ||
            typeof parsed.shareToken !== 'string' ||
            parsed.shareToken.length === 0
        ) {
            clearStoredGuestAlbum();
            return null;
        }

        if (typeof parsed.savedAt === 'string') {
            const savedAtMs = new Date(parsed.savedAt).getTime();
            const maxAgeMs = 1000 * 60 * 60 * 24 * 30;

            if (!Number.isFinite(savedAtMs) || Date.now() - savedAtMs > maxAgeMs) {
                clearStoredGuestAlbum();
                return null;
            }
        }

        return {
            shareToken: parsed.shareToken,
            albumUrl: parsed.albumUrl,
            eventName: parsed.eventName,
            guestName: typeof parsed.guestName === 'string' ? parsed.guestName : null,
            guestToken: typeof parsed.guestToken === 'string' ? parsed.guestToken : null,
            logoUrl: typeof parsed.logoUrl === 'string' ? parsed.logoUrl : null,
            savedAt: typeof parsed.savedAt === 'string' ? parsed.savedAt : new Date().toISOString(),
        };
    } catch {
        clearStoredGuestAlbum();
        return null;
    }
};

const openStoredGuestAlbum = (): void => {
    if (typeof window === 'undefined' || resumeGuestAlbum.value === null) {
        return;
    }

    window.location.assign(resumeGuestAlbum.value.albumUrl);
};

onMounted(() => {
    const storedAlbum = loadStoredGuestAlbum();
    if (storedAlbum === null) {
        return;
    }

    if (isStandalonePwa()) {
        window.location.replace(storedAlbum.albumUrl);

        return;
    }

    if (!props.pwaLaunch) {
        return;
    }

    resumeGuestAlbum.value = storedAlbum;
    resumeGuestAlbumOpen.value = true;
});
</script>

<template>
    <MarketingLayout
        :title="pageTitle"
        :description="pageDescription"
        :can-register="canRegister"
    >
        <Dialog :open="resumeGuestAlbumOpen" @update:open="resumeGuestAlbumOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader class="text-left">
                    <div class="flex items-center gap-4">
                        <div
                            v-if="resumeGuestAlbum?.logoUrl"
                            class="size-14 overflow-hidden rounded-[18px] border border-promo-line bg-promo-surface"
                        >
                            <img
                                :src="resumeGuestAlbum.logoUrl"
                                :alt="resumeGuestAlbum.eventName"
                                class="h-full w-full object-cover"
                            >
                        </div>
                        <div
                            v-else
                            class="flex size-14 items-center justify-center rounded-[18px] bg-promo-primary/12 text-promo-primary"
                        >
                            <QrCode class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <p class="marketing-kicker">
                                {{ t('marketing.pwa.resume.badge') }}
                            </p>
                            <DialogTitle class="mt-1 text-left text-xl">
                                {{ t('marketing.pwa.resume.title') }}
                            </DialogTitle>
                        </div>
                    </div>

                    <DialogDescription class="space-y-3 pt-3 text-left">
                        <p>
                            {{ t('marketing.pwa.resume.description', { eventName: resumeGuestAlbum?.eventName ?? '' }) }}
                        </p>
                        <p v-if="resumeGuestAlbum?.guestName" class="text-sm font-medium text-promo-ink">
                            {{ t('marketing.pwa.resume.guest_name', { name: resumeGuestAlbum.guestName }) }}
                        </p>
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2 sm:justify-start">
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-full bg-promo-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        @click="openStoredGuestAlbum"
                    >
                        {{ t('marketing.pwa.resume.open') }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-full border border-promo-line bg-white px-5 py-3 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        @click="resumeGuestAlbumOpen = false"
                    >
                        {{ t('marketing.pwa.resume.dismiss') }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex min-h-11 items-center justify-center rounded-full px-3 py-2 text-sm font-medium text-promo-muted transition hover:text-promo-ink"
                        @click="clearStoredGuestAlbum"
                    >
                        {{ t('marketing.pwa.resume.forget') }}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <section class="mx-auto grid max-w-7xl gap-12 px-4 pb-18 pt-10 sm:px-6 lg:grid-cols-[0.88fr_1.12fr] lg:px-8 lg:pb-24 lg:pt-16">
            <div class="max-w-2xl">
                <p class="marketing-kicker inline-flex items-center gap-2">
                    <BadgeCheck class="size-4" />
                    Photo sharing for events
                </p>
                <h1 class="marketing-display mt-6 text-[3.2rem] sm:text-[4rem] lg:text-[4.8rem]">
                    Show the product fast. Explain it in one glance.
                </h1>
                <p class="marketing-copy mt-6 max-w-xl">
                    EventSmart helps guests join quickly, upload from their phones, and see photos in the album and on the live wall.
                    The public site should prove that visually, not bury it in text.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="onboardingCreate({ query: { plan: 'free' } })"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        Create an event
                        <ArrowRight class="size-4" />
                    </Link>
                    <a
                        href="#how-it-works"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                    >
                        See the flow
                        <CirclePlay class="size-4" />
                    </a>
                </div>

                <div class="mt-8 divide-y divide-promo-line border-y border-promo-line">
                    <div
                        v-for="item in quickProof"
                        :key="item"
                        class="py-3 text-sm font-medium text-promo-ink"
                    >
                        {{ item }}
                    </div>
                </div>
            </div>

            <MarketingVisualPlaceholder
                label="Hero video placeholder"
                title="Add 12s overview video"
                description="Show the full guest path: create event, scan QR or enter code, upload on mobile, then watch the photo appear on the live wall."
                aspect-class="aspect-[5/4] lg:aspect-[4/3]"
            />
        </section>

        <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="How it works"
                title="Three short steps. Real screens. No extra story."
                description="Keep the homepage focused on the actual flow people need to understand before they buy."
            />

            <div class="mt-14 divide-y divide-promo-line border-y border-promo-line">
                <article
                    v-for="item in walkthrough"
                    :key="item.step"
                    class="grid gap-8 py-8 lg:grid-cols-[0.76fr_1.24fr] lg:items-center"
                >
                    <div class="max-w-md">
                        <p class="marketing-kicker">
                            {{ item.step }}
                        </p>
                        <h3 class="marketing-display mt-3 text-[2.2rem] sm:text-[2.6rem]">
                            {{ item.title }}
                        </h3>
                        <p class="marketing-copy mt-4">
                            {{ item.body }}
                        </p>
                    </div>

                    <MarketingVisualPlaceholder
                        :label="item.label"
                        :title="item.visualTitle"
                        :description="item.visualDescription"
                    />
                </article>
            </div>
        </section>

        <section id="proof" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Product examples"
                    title="Show what changed in the app"
                    description="These blocks should become real screenshots or short loops from the actual product, not lifestyle photos."
                    centered
                />

                <div class="mt-14 grid gap-8 lg:grid-cols-3">
                    <article
                        v-for="item in visualExamples"
                        :key="item.title"
                        class="space-y-5"
                    >
                        <div class="flex items-center gap-3 text-promo-ink">
                            <component :is="item.icon" class="size-5 text-promo-primary" />
                            <h3 class="text-lg font-semibold">{{ item.title }}</h3>
                        </div>
                        <p class="marketing-copy">
                            {{ item.body }}
                        </p>
                        <MarketingVisualPlaceholder
                            :label="item.label"
                            :title="item.visualTitle"
                            :description="item.visualDescription"
                            aspect-class="aspect-[4/3]"
                        />
                    </article>
                </div>
            </div>
        </section>

        <section id="services" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="What changed"
                title="The new service model needs a clear public explanation"
                description="This section replaces long copy blocks with short updates people can scan in a few seconds."
            />

            <div class="mt-14 grid gap-6 lg:grid-cols-[0.86fr_1.14fr] lg:items-start">
                <div class="divide-y divide-promo-line border-y border-promo-line">
                    <article
                        v-for="item in serviceUpdates"
                        :key="item.title"
                        class="py-5"
                    >
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ item.title }}
                        </h3>
                        <p class="marketing-copy mt-2">
                            {{ item.description }}
                        </p>
                    </article>
                </div>

                <MarketingVisualPlaceholder
                    label="Collage placeholder"
                    title="Add 3 real screenshots in one frame"
                    description="Use one clean collage here: album code entry, short wall link on TV, and business billing credits."
                    aspect-class="aspect-[5/4]"
                />
            </div>
        </section>

        <section id="faq" class="bg-white">
            <div class="mx-auto max-w-5xl px-4 py-18 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="FAQ"
                    title="Keep the answers short too"
                    centered
                />

                <div class="mt-12 divide-y divide-promo-line border-y border-promo-line">
                    <article
                        v-for="item in faqItems"
                        :key="item.question"
                        class="grid gap-3 py-5 md:grid-cols-[0.9fr_1.1fr]"
                    >
                        <h3 class="text-base font-semibold text-promo-ink">
                            {{ item.question }}
                        </h3>
                        <p class="text-sm leading-6 text-promo-muted">
                            {{ item.answer }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section id="cta" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <div class="grid gap-8 border-y border-promo-line py-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                <div class="max-w-xl">
                    <p class="marketing-kicker">
                        Start here
                    </p>
                    <h2 class="marketing-display mt-3 text-[2.5rem] sm:text-[3rem]">
                        One page for guests. One page for pricing. One page for business.
                    </h2>
                    <p class="marketing-copy mt-4">
                        That is enough for launch. Use the screenshots and short clips to do the explaining, then let the app do the rest.
                    </p>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            View pricing
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="businesses().url"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            Explore business
                        </Link>
                    </div>
                </div>

                <MarketingVisualPlaceholder
                    label="Final proof placeholder"
                    title="Add one last comparison image"
                    description="Show the homepage stack you want: product screenshot, guest mobile upload, and live wall on a TV."
                    aspect-class="aspect-[16/10]"
                />
            </div>
        </section>
    </MarketingLayout>
</template>
