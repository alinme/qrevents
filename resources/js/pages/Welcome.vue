<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BadgeCheck, BriefcaseBusiness, CirclePlay, MonitorPlay, QrCode } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import MarketingProductPreview from '@/components/marketing/MarketingProductPreview.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
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
    t('marketing.home.simple.proof.1'),
    t('marketing.home.simple.proof.2'),
    t('marketing.home.simple.proof.3'),
];

const walkthrough = [
    {
        step: t('marketing.shared.step', { number: '1' }),
        title: t('marketing.home.simple.steps.1.title'),
        body: t('marketing.home.simple.steps.1.body'),
        variant: 'event-setup' as const,
        caption: 'Plan, date, album code, wall link.',
    },
    {
        step: t('marketing.shared.step', { number: '2' }),
        title: t('marketing.home.simple.steps.2.title'),
        body: t('marketing.home.simple.steps.2.body'),
        variant: 'guest-flow' as const,
        caption: 'QR or short code. No app download.',
    },
    {
        step: t('marketing.shared.step', { number: '3' }),
        title: t('marketing.home.simple.steps.3.title'),
        body: t('marketing.home.simple.steps.3.body'),
        variant: 'live-wall' as const,
        caption: 'Uploads show up on the wall in seconds.',
    },
];

const visualExamples = [
    {
        icon: QrCode,
        title: t('marketing.home.simple.examples.1.title'),
        body: t('marketing.home.simple.examples.1.body'),
        variant: 'album-access' as const,
        caption: 'Short code entry on mobile.',
    },
    {
        icon: MonitorPlay,
        title: t('marketing.home.simple.examples.2.title'),
        body: t('marketing.home.simple.examples.2.body'),
        variant: 'live-wall' as const,
        caption: 'TV-friendly live wall.',
    },
    {
        icon: BriefcaseBusiness,
        title: t('marketing.home.simple.examples.3.title'),
        body: t('marketing.home.simple.examples.3.body'),
        variant: 'business-dashboard' as const,
        caption: 'Billing and events in one dashboard.',
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

        <section class="mx-auto grid max-w-7xl gap-10 px-4 pb-18 pt-10 sm:px-6 lg:grid-cols-[0.82fr_1.18fr] lg:px-8 lg:pb-24 lg:pt-16">
            <div class="max-w-2xl">
                <p class="marketing-kicker inline-flex items-center gap-2">
                    <BadgeCheck class="size-4" />
                    {{ t('marketing.home.simple.hero.kicker') }}
                </p>
                <h1 class="marketing-display mt-6 text-[3.2rem] sm:text-[4rem] lg:text-[4.8rem]">
                    {{ t('marketing.home.simple.hero.title') }}
                </h1>
                <p class="marketing-copy mt-6 max-w-xl">
                    {{ t('marketing.home.simple.hero.description') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="onboardingCreate({ query: { plan: 'free' } })"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                    >
                        {{ t('marketing.actions.create_event') }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <a
                        href="#how-it-works"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                    >
                        {{ t('marketing.home.simple.hero.secondary_cta') }}
                        <CirclePlay class="size-4" />
                    </a>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div
                        v-for="item in quickProof"
                        :key="item"
                        class="rounded-[1.35rem] border border-promo-line bg-white px-4 py-4 text-sm font-medium text-promo-ink shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]"
                    >
                        {{ item }}
                    </div>
                </div>
            </div>

            <MarketingProductPreview
                variant="hero"
                caption="Scan. Upload. Relive together."
                aspect-class="aspect-[5/4] lg:aspect-[4/3]"
            />
        </section>

        <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.simple.sections.flow.eyebrow')"
                :title="t('marketing.home.simple.sections.flow.title')"
                :description="t('marketing.home.simple.sections.flow.description')"
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
                        <h3 class="marketing-display mt-3 text-[2rem] sm:text-[2.4rem]">
                            {{ item.title }}
                        </h3>
                        <p class="marketing-copy mt-4">
                            {{ item.body }}
                        </p>
                    </div>

                    <MarketingProductPreview :variant="item.variant" :caption="item.caption" />
                </article>
            </div>
        </section>

        <section id="proof" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.simple.sections.examples.eyebrow')"
                    :title="t('marketing.home.simple.sections.examples.title')"
                    :description="t('marketing.home.simple.sections.examples.description')"
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
                        <MarketingProductPreview
                            :variant="item.variant"
                            :caption="item.caption"
                            aspect-class="aspect-[4/3]"
                        />
                    </article>
                </div>
            </div>
        </section>

        <section id="services" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.simple.sections.updates.eyebrow')"
                :title="t('marketing.home.simple.sections.updates.title')"
                :description="t('marketing.home.simple.sections.updates.description')"
            />

            <div class="mt-14 grid gap-8 lg:grid-cols-[0.84fr_1.16fr] lg:items-center">
                <div class="space-y-4">
                    <div class="rounded-[1.35rem] border border-promo-line bg-white px-5 py-5 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]">
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ t('marketing.home.simple.updates.1.title') }}
                        </h3>
                        <p class="marketing-copy mt-2">
                            {{ t('marketing.home.simple.updates.1.description') }}
                        </p>
                    </div>
                    <div class="rounded-[1.35rem] border border-promo-line bg-white px-5 py-5 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]">
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ t('marketing.home.simple.updates.2.title') }}
                        </h3>
                        <p class="marketing-copy mt-2">
                            {{ t('marketing.home.simple.updates.2.description') }}
                        </p>
                    </div>
                    <div class="rounded-[1.35rem] border border-promo-line bg-white px-5 py-5 shadow-[rgba(0,0,0,0.02)_0px_0px_0px_1px,rgba(0,0,0,0.04)_0px_2px_6px]">
                        <h3 class="text-lg font-semibold text-promo-ink">
                            {{ t('marketing.home.simple.updates.3.title') }}
                        </h3>
                        <p class="marketing-copy mt-2">
                            {{ t('marketing.home.simple.updates.3.description') }}
                        </p>
                    </div>
                </div>

                <MarketingProductPreview
                    variant="story-collage"
                    caption="Album code, guest post, live wall."
                    aspect-class="aspect-[5/4]"
                />
            </div>
        </section>

        <section id="cta" class="mx-auto max-w-7xl px-4 py-18 sm:px-6 lg:px-8">
            <div class="grid gap-8 border-y border-promo-line py-8 lg:grid-cols-[0.92fr_1.08fr] lg:items-center">
                <div class="max-w-xl">
                    <p class="marketing-kicker">
                        {{ t('marketing.home.simple.sections.cta.eyebrow') }}
                    </p>
                    <h2 class="marketing-display mt-3 text-[2.5rem] sm:text-[3rem]">
                        {{ t('marketing.home.simple.sections.cta.title') }}
                    </h2>
                    <p class="marketing-copy mt-4">
                        {{ t('marketing.home.simple.sections.cta.description') }}
                    </p>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.shared.view_pricing') }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="businesses().url"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.home.simple.sections.cta.secondary_cta') }}
                        </Link>
                    </div>
                </div>

                <MarketingProductPreview
                    variant="story-collage"
                    caption="Everything guests need, at a glance."
                    aspect-class="aspect-[16/10]"
                />
            </div>
        </section>
    </MarketingLayout>
</template>
