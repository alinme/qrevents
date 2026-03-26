<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BadgeCheck,
    BriefcaseBusiness,
    Building2,
    CirclePlay,
    Download,
    Gift,
    Globe2,
    Heart,
    Image,
    LockKeyhole,
    MessageSquareText,
    MonitorPlay,
    PartyPopper,
    QrCode,
    ShieldCheck,
    SlidersHorizontal,
    Smartphone,
    Sparkles,
    Star,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import type { Component } from 'vue';
import MarketingFeatureCard from '@/components/marketing/MarketingFeatureCard.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingStepCard from '@/components/marketing/MarketingStepCard.vue';
import MarketingTestimonialCard from '@/components/marketing/MarketingTestimonialCard.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { businesses, pricing, weddings } from '@/routes';
import { create as onboardingCreate } from '@/routes/onboarding';

const props = defineProps<{
    canRegister: boolean;
    pwaLaunch?: boolean;
}>();

const { t } = useTranslations();

type FeatureItem = {
    icon: Component;
    title: string;
    description: string;
    eyebrow?: string;
};

type StoredGuestAlbumHint = {
    shareToken: string;
    albumUrl: string;
    eventName: string;
    guestName: string | null;
    guestToken: string | null;
    logoUrl: string | null;
    savedAt: string;
};

const heroGallery = [
    '/fake-media/503096688_1013285264308603_8150632900022158723_n.jpg',
    '/fake-media/503962837_9861361353953264_525766796662049219_n.jpg',
    '/fake-media/504824543_1521239702593913_4818893469591191053_n.jpg',
    '/fake-media/503748303_1048043510209927_1182952490263678396_n.jpg',
];

const logoRow = ['brides', 'eventbrite', 'honeybook', 'airtable', 'canva', 'mailchimp'];

const introSteps = [
    {
        step: t('marketing.shared.step', { number: '01' }),
        title: t('marketing.home.intro_steps.1.title'),
        description: t('marketing.home.intro_steps.1.description'),
        image: heroGallery[0],
        imageAlt: t('marketing.home.intro_steps.1.image_alt'),
        highlights: [
            t('marketing.home.intro_steps.1.highlights.1'),
            t('marketing.home.intro_steps.1.highlights.2'),
            t('marketing.home.intro_steps.1.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '02' }),
        title: t('marketing.home.intro_steps.2.title'),
        description: t('marketing.home.intro_steps.2.description'),
        image: heroGallery[1],
        imageAlt: t('marketing.home.intro_steps.2.image_alt'),
        highlights: [
            t('marketing.home.intro_steps.2.highlights.1'),
            t('marketing.home.intro_steps.2.highlights.2'),
            t('marketing.home.intro_steps.2.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '03' }),
        title: t('marketing.home.intro_steps.3.title'),
        description: t('marketing.home.intro_steps.3.description'),
        image: heroGallery[3],
        imageAlt: t('marketing.home.intro_steps.3.image_alt'),
        highlights: [
            t('marketing.home.intro_steps.3.highlights.1'),
            t('marketing.home.intro_steps.3.highlights.2'),
            t('marketing.home.intro_steps.3.highlights.3'),
        ],
    },
    {
        step: t('marketing.shared.step', { number: '04' }),
        title: t('marketing.home.intro_steps.4.title'),
        description: t('marketing.home.intro_steps.4.description'),
        image: heroGallery[2],
        imageAlt: t('marketing.home.intro_steps.4.image_alt'),
        highlights: [
            t('marketing.home.intro_steps.4.highlights.1'),
            t('marketing.home.intro_steps.4.highlights.2'),
            t('marketing.home.intro_steps.4.highlights.3'),
        ],
    },
];

const valueFeatures: FeatureItem[] = [
    {
        icon: Image,
        title: t('marketing.home.value.features.1.title'),
        description: t('marketing.home.value.features.1.description'),
    },
    {
        icon: MessageSquareText,
        title: t('marketing.home.value.features.2.title'),
        description: t('marketing.home.value.features.2.description'),
    },
    {
        icon: Smartphone,
        title: t('marketing.home.value.features.3.title'),
        description: t('marketing.home.value.features.3.description'),
    },
    {
        icon: MonitorPlay,
        title: t('marketing.home.value.features.4.title'),
        description: t('marketing.home.value.features.4.description'),
    },
];

const howItWorks = [
    {
        step: '01',
        title: t('marketing.home.flow.items.1.title'),
        description: t('marketing.home.flow.items.1.description'),
        image: heroGallery[0],
        imageAlt: t('marketing.home.flow.items.1.image_alt'),
        highlights: [
            t('marketing.home.flow.items.1.highlights.1'),
            t('marketing.home.flow.items.1.highlights.2'),
            t('marketing.home.flow.items.1.highlights.3'),
        ],
    },
    {
        step: '02',
        title: t('marketing.home.flow.items.2.title'),
        description: t('marketing.home.flow.items.2.description'),
        image: heroGallery[1],
        imageAlt: t('marketing.home.flow.items.2.image_alt'),
        highlights: [
            t('marketing.home.flow.items.2.highlights.1'),
            t('marketing.home.flow.items.2.highlights.2'),
            t('marketing.home.flow.items.2.highlights.3'),
        ],
    },
    {
        step: '03',
        title: t('marketing.home.flow.items.3.title'),
        description: t('marketing.home.flow.items.3.description'),
        image: heroGallery[3],
        imageAlt: t('marketing.home.flow.items.3.image_alt'),
        highlights: [
            t('marketing.home.flow.items.3.highlights.1'),
            t('marketing.home.flow.items.3.highlights.2'),
            t('marketing.home.flow.items.3.highlights.3'),
        ],
    },
    {
        step: '04',
        title: t('marketing.home.flow.items.4.title'),
        description: t('marketing.home.flow.items.4.description'),
        image: heroGallery[2],
        imageAlt: t('marketing.home.flow.items.4.image_alt'),
        highlights: [
            t('marketing.home.flow.items.4.highlights.1'),
            t('marketing.home.flow.items.4.highlights.2'),
            t('marketing.home.flow.items.4.highlights.3'),
        ],
    },
];

const capabilities: FeatureItem[] = [
    { icon: Image, title: t('marketing.home.capabilities.items.1.title'), description: t('marketing.home.capabilities.items.1.description') },
    { icon: Download, title: t('marketing.home.capabilities.items.2.title'), description: t('marketing.home.capabilities.items.2.description') },
    { icon: Smartphone, title: t('marketing.home.capabilities.items.3.title'), description: t('marketing.home.capabilities.items.3.description') },
    { icon: QrCode, title: t('marketing.home.capabilities.items.4.title'), description: t('marketing.home.capabilities.items.4.description') },
    { icon: MonitorPlay, title: t('marketing.home.capabilities.items.5.title'), description: t('marketing.home.capabilities.items.5.description') },
    { icon: SlidersHorizontal, title: t('marketing.home.capabilities.items.6.title'), description: t('marketing.home.capabilities.items.6.description') },
    { icon: MessageSquareText, title: t('marketing.home.capabilities.items.7.title'), description: t('marketing.home.capabilities.items.7.description') },
    { icon: LockKeyhole, title: t('marketing.home.capabilities.items.8.title'), description: t('marketing.home.capabilities.items.8.description') },
];

const useCases = [
    {
        label: t('marketing.home.use_cases.items.1.label'),
        title: t('marketing.home.use_cases.items.1.title'),
        description: t('marketing.home.use_cases.items.1.description'),
        icon: Heart,
        href: weddings().url,
    },
    {
        label: t('marketing.home.use_cases.items.2.label'),
        title: t('marketing.home.use_cases.items.2.title'),
        description: t('marketing.home.use_cases.items.2.description'),
        icon: PartyPopper,
        href: `${weddings().url}#celebrations`,
    },
    {
        label: t('marketing.home.use_cases.items.3.label'),
        title: t('marketing.home.use_cases.items.3.title'),
        description: t('marketing.home.use_cases.items.3.description'),
        icon: Gift,
        href: `${pricing().url}#birthday`,
    },
    {
        label: t('marketing.home.use_cases.items.4.label'),
        title: t('marketing.home.use_cases.items.4.title'),
        description: t('marketing.home.use_cases.items.4.description'),
        icon: Globe2,
        href: businesses().url,
    },
    {
        label: t('marketing.home.use_cases.items.5.label'),
        title: t('marketing.home.use_cases.items.5.title'),
        description: t('marketing.home.use_cases.items.5.description'),
        icon: Building2,
        href: businesses().url,
    },
    {
        label: t('marketing.home.use_cases.items.6.label'),
        title: t('marketing.home.use_cases.items.6.title'),
        description: t('marketing.home.use_cases.items.6.description'),
        icon: BriefcaseBusiness,
        href: businesses().url,
    },
];

const stats = [
    { value: '2 min', label: t('marketing.home.social.stats.1.label') },
    { value: '10k+', label: t('marketing.home.social.stats.2.label') },
    { value: '1M+', label: t('marketing.home.social.stats.3.label') },
    { value: '100%', label: t('marketing.home.social.stats.4.label') },
];

const testimonials = [
    {
        quote:
            t('marketing.home.reviews.testimonials.1.quote'),
        name: 'Emma',
        detail: t('marketing.home.reviews.testimonials.1.detail'),
        image: heroGallery[0],
    },
    {
        quote:
            t('marketing.home.reviews.testimonials.2.quote'),
        name: 'Marcus',
        detail: t('marketing.home.reviews.testimonials.2.detail'),
        image: heroGallery[1],
    },
    {
        quote:
            t('marketing.home.reviews.testimonials.3.quote'),
        name: 'Leah',
        detail: t('marketing.home.reviews.testimonials.3.detail'),
        image: heroGallery[3],
    },
    {
        quote:
            t('marketing.home.reviews.testimonials.4.quote'),
        name: 'Noah',
        detail: t('marketing.home.reviews.testimonials.4.detail'),
        image: heroGallery[2],
    },
];

const comparisonRows = [
    {
        label: t('marketing.home.comparison.rows.1.label'),
        ours: t('marketing.home.comparison.rows.1.ours'),
        others: t('marketing.home.comparison.rows.1.others'),
    },
    {
        label: t('marketing.home.comparison.rows.2.label'),
        ours: t('marketing.home.comparison.rows.2.ours'),
        others: t('marketing.home.comparison.rows.2.others'),
    },
    {
        label: t('marketing.home.comparison.rows.3.label'),
        ours: t('marketing.home.comparison.rows.3.ours'),
        others: t('marketing.home.comparison.rows.3.others'),
    },
    {
        label: t('marketing.home.comparison.rows.4.label'),
        ours: t('marketing.home.comparison.rows.4.ours'),
        others: t('marketing.home.comparison.rows.4.others'),
    },
    {
        label: t('marketing.home.comparison.rows.5.label'),
        ours: t('marketing.home.comparison.rows.5.ours'),
        others: t('marketing.home.comparison.rows.5.others'),
    },
    {
        label: t('marketing.home.comparison.rows.6.label'),
        ours: t('marketing.home.comparison.rows.6.ours'),
        others: t('marketing.home.comparison.rows.6.others'),
    },
];

const faqs = [
    {
        question: t('marketing.home.faq.items.1.question'),
        answer: t('marketing.home.faq.items.1.answer'),
    },
    {
        question: t('marketing.home.faq.items.2.question'),
        answer: t('marketing.home.faq.items.2.answer'),
    },
    {
        question: t('marketing.home.faq.items.3.question'),
        answer: t('marketing.home.faq.items.3.answer'),
    },
    {
        question: t('marketing.home.faq.items.4.question'),
        answer: t('marketing.home.faq.items.4.answer'),
    },
    {
        question: t('marketing.home.faq.items.5.question'),
        answer: t('marketing.home.faq.items.5.answer'),
    },
];

const blogPosts = [
    {
        category: t('marketing.home.blog.posts.1.category'),
        title: t('marketing.home.blog.posts.1.title'),
        body: t('marketing.home.blog.posts.1.body'),
    },
    {
        category: t('marketing.home.blog.posts.2.category'),
        title: t('marketing.home.blog.posts.2.title'),
        body: t('marketing.home.blog.posts.2.body'),
    },
    {
        category: t('marketing.home.blog.posts.3.category'),
        title: t('marketing.home.blog.posts.3.title'),
        body: t('marketing.home.blog.posts.3.body'),
    },
];

const pageTitle = computed(() => t('marketing.home.meta.title'));
const pageDescription = computed(() => t('marketing.home.meta.description'));
const guestAlbumHintStorageKey = 'qrevents-last-guest-album';
const resumeGuestAlbum = ref<StoredGuestAlbumHint | null>(null);
const resumeGuestAlbumOpen = ref(false);

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

    if (!props.pwaLaunch && !isStandalonePwa()) {
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
                            />
                        </div>
                        <div
                            v-else
                            class="flex size-14 items-center justify-center rounded-[18px] bg-promo-primary/12 text-promo-primary"
                        >
                            <QrCode class="size-6" />
                        </div>

                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
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

        <section class="mx-auto max-w-7xl px-4 pb-18 pt-10 sm:px-6 lg:px-8 lg:pb-24 lg:pt-18">
            <div class="grid items-center gap-14 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-promo-line bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <BadgeCheck class="size-4" />
                        {{ t('marketing.home.hero.badge') }}
                    </div>

                    <h1 class="mt-6 text-[2rem] font-extrabold leading-[1.02] tracking-[-0.025em] text-promo-ink sm:text-[2.35rem] lg:text-[2.8rem]">
                        {{ t('marketing.home.hero.title') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-promo-muted sm:text-lg">
                        {{ t('marketing.home.hero.description') }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="onboardingCreate({ query: { plan: 'free' } })"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(232,79,154,0.28)] transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.actions.create_event') }}
                            <ArrowRight class="size-4" />
                        </Link>

                        <a
                            href="#how-it-works"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.home.hero.watch_demo') }}
                            <CirclePlay class="size-4" />
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-4 text-sm text-promo-ink/82">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-[0_10px_24px_rgba(120,86,255,0.08)]">
                            <div class="flex items-center gap-1 text-promo-primary">
                                <Star v-for="star in 5" :key="star" class="size-4 fill-current" />
                            </div>
                            {{ t('marketing.home.hero.rating') }}
                        </div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">{{ t('marketing.home.hero.pill_free') }}</div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">{{ t('marketing.home.hero.pill_setup') }}</div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">{{ t('marketing.home.hero.pill_hosts') }}</div>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_28px_80px_rgba(120,86,255,0.14)]">
                        <div class="grid gap-4 lg:grid-cols-[0.56fr_0.44fr]">
                            <div class="overflow-hidden rounded-[26px] bg-promo-surface">
                                <img
                                    :src="heroGallery[0]"
                                    :alt="t('marketing.home.hero.gallery_main_alt')"
                                    class="aspect-[4/5] w-full object-cover"
                                />
                            </div>

                            <div class="grid gap-4">
                                <div class="rounded-[24px] border border-promo-line bg-promo-surface p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                                {{ t('marketing.home.hero.join_qr_title') }}
                                            </p>
                                            <p class="mt-1 text-sm text-promo-muted">
                                                {{ t('marketing.home.hero.join_qr_description') }}
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-4 gap-1 rounded-[16px] bg-white p-2">
                                            <div
                                                v-for="cell in 16"
                                                :key="cell"
                                                class="size-2 rounded-[4px]"
                                                :class="cell % 2 === 0 || cell % 5 === 0 ? 'bg-promo-ink' : 'bg-promo-surface'"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <img
                                        :src="heroGallery[1]"
                                        :alt="t('marketing.home.hero.gallery_phone_alt')"
                                        class="aspect-square rounded-[24px] object-cover"
                                    />
                                    <img
                                        :src="heroGallery[2]"
                                        :alt="t('marketing.home.hero.gallery_moment_alt')"
                                        class="aspect-square rounded-[24px] object-cover"
                                    />
                                </div>

                                <div class="rounded-[24px] border border-promo-line bg-promo-warm p-5">
                                    <div class="flex items-start gap-3">
                                        <div class="flex size-11 items-center justify-center rounded-[18px] bg-white text-promo-primary">
                                            <MonitorPlay class="size-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-promo-ink">
                                                {{ t('marketing.home.hero.live_wall_title') }}
                                            </p>
                                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                                {{ t('marketing.home.hero.live_wall_description') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -left-4 rounded-[22px] border border-promo-line bg-white px-5 py-4 shadow-[0_18px_44px_rgba(232,79,154,0.14)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                            {{ t('marketing.home.hero.no_app_title') }}
                        </p>
                        <p class="mt-2 text-sm font-medium text-promo-ink">
                            {{ t('marketing.home.hero.no_app_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-promo-line bg-white">
            <div class="mx-auto max-w-6xl px-4 py-8 text-center sm:px-6 lg:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-promo-muted">
                    {{ t('marketing.home.logos.title') }}
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-[12px] font-semibold uppercase tracking-[0.2em] text-promo-muted/82 sm:text-[13px]">
                    <span v-for="logo in logoRow" :key="logo">{{ logo }}</span>
                </div>
            </div>
        </section>

        <section id="steps" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.steps.eyebrow')"
                :title="t('marketing.home.steps.title')"
                centered
            />

            <div class="mt-14 grid gap-6 lg:grid-cols-4">
                <MarketingStepCard
                    v-for="item in introSteps"
                    :key="item.step"
                    :step="item.step"
                    :title="item.title"
                    :description="item.description"
                    :image="item.image"
                    :image-alt="item.imageAlt"
                    :highlights="item.highlights"
                />
            </div>
        </section>

        <section class="bg-promo-surface/45">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.value.eyebrow')"
                    :title="t('marketing.home.value.title')"
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="feature in valueFeatures"
                        :key="feature.title"
                        :icon="feature.icon"
                        :title="feature.title"
                        :description="feature.description"
                    />
                </div>
            </div>
        </section>

        <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.flow.eyebrow')"
                    :title="t('marketing.home.flow.title')"
                />

            <div class="mt-14 space-y-6">
                <article
                    v-for="(item, index) in howItWorks"
                    :key="item.step"
                    class="grid items-center gap-7 rounded-[24px] border border-promo-line/80 bg-white p-5 lg:grid-cols-[1fr_1fr] lg:p-6"
                >
                    <div :class="index % 2 === 1 ? 'lg:order-2' : ''">
                        <div class="inline-flex items-center gap-3 rounded-full bg-promo-surface px-4 py-2">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                Step {{ item.step }}
                            </span>
                        </div>
                        <h3 class="mt-4 text-lg font-extrabold tracking-[-0.015em] text-promo-ink sm:text-[1.2rem]">
                            {{ item.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-promo-muted sm:text-[0.95rem]">
                            {{ item.description }}
                        </p>
                        <ul class="mt-5 grid gap-2 text-sm text-promo-ink/84">
                            <li
                                v-for="highlight in item.highlights"
                                :key="highlight"
                                class="flex items-start gap-3"
                            >
                                <div class="mt-1 flex size-6 items-center justify-center rounded-full bg-promo-surface text-promo-primary">
                                    <Sparkles class="size-3.5" />
                                </div>
                                <span>{{ highlight }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-[22px] bg-promo-surface/55 p-3" :class="index % 2 === 1 ? 'lg:order-1' : ''">
                        <img
                            :src="item.image"
                            :alt="item.imageAlt"
                            class="aspect-[5/4] w-full rounded-[18px] object-cover"
                            loading="lazy"
                            decoding="async"
                        />
                    </div>
                </article>
            </div>
        </section>

        <section id="capabilities" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.capabilities.eyebrow')"
                    :title="t('marketing.home.capabilities.title')"
                    centered
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="feature in capabilities"
                        :key="feature.title"
                        class="group relative overflow-hidden rounded-[24px] border border-promo-line/80 bg-white px-5 py-7 text-center transition duration-300 hover:border-promo-primary/20 hover:bg-promo-surface/30"
                    >
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center text-promo-primary/8 transition duration-300 group-hover:text-promo-primary/12">
                            <component :is="feature.icon" class="size-28 sm:size-32" />
                        </div>

                        <div class="relative z-10 flex min-h-[12rem] flex-col items-center justify-center">
                            <h3 class="max-w-[12rem] text-[0.98rem] font-bold text-promo-ink sm:text-base">
                                {{ feature.title }}
                            </h3>
                            <p class="mt-2 max-w-[16rem] text-sm leading-6 text-promo-muted">
                                {{ feature.description }}
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="use-cases" class="bg-promo-surface/45">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.use_cases.eyebrow')"
                    :title="t('marketing.home.use_cases.title')"
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="item in useCases"
                        :key="item.title"
                        :href="item.href"
                        class="group rounded-[22px] border border-promo-line/80 bg-white p-5 transition duration-300 hover:border-promo-primary/20 hover:bg-promo-surface/30"
                    >
                        <div class="flex size-11 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                            <component :is="item.icon" class="size-5" />
                        </div>
                        <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                            {{ item.label }}
                        </p>
                        <h3 class="mt-2 text-[0.98rem] font-bold tracking-[-0.015em] text-promo-ink sm:text-base">
                            {{ item.title }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                            {{ item.description }}
                        </p>
                        <div class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-promo-ink">
                            {{ t('marketing.shared.learn_more') }}
                            <ArrowRight class="size-4 transition group-hover:translate-x-1" />
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                        {{ t('marketing.home.social.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-xl font-extrabold tracking-[-0.02em] text-promo-ink sm:text-[1.35rem] lg:text-[1.55rem]">
                        {{ t('marketing.home.social.title') }}
                    </h2>
                    <p class="mt-3 max-w-lg text-sm leading-6 text-promo-muted sm:text-[0.95rem]">
                        {{ t('marketing.home.social.description') }}
                    </p>

                    <dl class="mt-8 grid gap-4 border-y border-promo-line/70 py-5 sm:grid-cols-2">
                        <div
                            v-for="item in stats"
                            :key="item.label"
                            class="min-w-0"
                        >
                            <dt class="text-xl font-extrabold tracking-[-0.02em] text-promo-ink sm:text-[1.35rem]">
                                {{ item.value }}
                            </dt>
                            <dd class="mt-1 text-sm text-promo-muted">
                                {{ item.label }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="onboardingCreate({ query: { plan: 'free' } })"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.actions.create_event') }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.shared.view_pricing') }}
                        </Link>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <img
                        v-for="image in heroGallery"
                        :key="`stat-${image}`"
                        :src="image"
                        :alt="t('marketing.home.social.gallery_alt')"
                        class="aspect-[4/3] w-full rounded-[24px] object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                </div>
            </div>
        </section>

        <section id="reviews" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.reviews.eyebrow')"
                    :title="t('marketing.home.reviews.title')"
                />

                <div class="mt-5 inline-flex items-center gap-3 rounded-full bg-promo-surface px-4 py-2 text-sm font-semibold text-promo-ink">
                    <div class="flex items-center gap-1 text-promo-primary">
                        <Star v-for="star in 5" :key="`summary-${star}`" class="size-4 fill-current" />
                    </div>
                    {{ t('marketing.home.reviews.rating_summary') }}
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingTestimonialCard
                        v-for="item in testimonials"
                        :key="item.name"
                        :quote="item.quote"
                        :name="item.name"
                        :detail="item.detail"
                        :image="item.image"
                    />
                </div>
            </div>
        </section>

        <section id="comparison" class="bg-promo-surface/45">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.comparison.eyebrow')"
                    :title="t('marketing.home.comparison.title')"
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <div class="rounded-[24px] border border-promo-line/80 bg-white p-5">
                        <div class="inline-flex items-center gap-2 rounded-full bg-promo-surface px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                            {{ t('marketing.shared.our_product') }}
                        </div>
                        <div class="mt-5 space-y-3">
                            <div
                                v-for="row in comparisonRows"
                                :key="`ours-${row.label}`"
                                class="border-b border-promo-line/70 pb-3 last:border-b-0 last:pb-0"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex size-8 items-center justify-center rounded-full bg-promo-surface text-promo-primary">
                                        <ShieldCheck class="size-4" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ row.label }}</p>
                                        <p class="mt-1.5 text-sm leading-6 text-promo-muted">
                                            {{ row.ours }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-promo-line/80 bg-white p-5">
                        <div class="inline-flex items-center gap-2 rounded-full bg-promo-warm px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-ink">
                            {{ t('marketing.shared.other_apps') }}
                        </div>
                        <div class="mt-5 space-y-3">
                            <div
                                v-for="row in comparisonRows"
                                :key="`other-${row.label}`"
                                class="border-b border-promo-line/70 pb-3 last:border-b-0 last:pb-0"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex size-8 items-center justify-center rounded-full bg-promo-surface text-promo-muted">
                                        <Users class="size-4" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ row.label }}</p>
                                        <p class="mt-1.5 text-sm leading-6 text-promo-muted">
                                            {{ row.others }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="blog" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.blog.eyebrow')"
                :title="t('marketing.home.blog.title')"
            />

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                <article
                    v-for="post in blogPosts"
                    :key="post.title"
                    class="rounded-[22px] border border-promo-line/80 bg-white p-5"
                >
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-promo-primary">
                        {{ post.category }}
                    </p>
                    <h3 class="mt-3 text-[0.98rem] font-bold tracking-[-0.015em] text-promo-ink sm:text-base">
                        {{ post.title }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-promo-muted">
                        {{ post.body }}
                    </p>
                    <a href="#" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-promo-ink">
                        {{ t('marketing.shared.read_article') }}
                        <ArrowRight class="size-4" />
                    </a>
                </article>
            </div>
        </section>

        <section id="faq" class="bg-white">
            <div class="mx-auto max-w-4xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.faq.eyebrow')"
                    :title="t('marketing.home.faq.title')"
                    centered
                />

                <div class="mt-10 rounded-[24px] border border-promo-line/80 bg-promo-bg px-5 py-1">
                    <Accordion type="single" collapsible class="w-full">
                        <AccordionItem
                            v-for="item in faqs"
                            :key="item.question"
                            :value="item.question"
                            class="border-promo-line"
                        >
                            <AccordionTrigger class="text-base font-semibold text-promo-ink hover:no-underline">
                                {{ item.question }}
                            </AccordionTrigger>
                            <AccordionContent class="text-sm leading-7 text-promo-muted">
                                {{ item.answer }}
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </div>
        </section>

        <section id="cta" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[28px] border border-promo-line/80 bg-linear-to-br from-promo-surface via-white to-promo-purple/55 p-7 lg:p-10">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                        {{ t('marketing.home.cta.eyebrow') }}
                    </p>
                    <h2 class="mt-3 text-xl font-extrabold tracking-[-0.02em] text-promo-ink sm:text-[1.4rem]">
                        {{ t('marketing.home.cta.title') }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-promo-muted sm:text-[0.95rem]">
                        {{ t('marketing.home.cta.description') }}
                    </p>

                    <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
                        <Link
                            :href="onboardingCreate({ query: { plan: 'free' } })"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.actions.create_event') }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            {{ t('marketing.shared.view_pricing') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
