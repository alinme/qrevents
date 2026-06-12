<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BadgeCheck,
    Check,
    ChevronDown,
    CirclePlay,
    Download,
    Images,
    MessageSquareText,
    Minus,
    MonitorPlay,
    Palette,
    QrCode,
    ShieldCheck,
    Smartphone,
    Star,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
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

const heroPills = [
    t('marketing.home.hero.pill_free'),
    t('marketing.home.hero.pill_setup'),
    t('marketing.home.hero.no_app_title'),
];

const flowImages = [
    '/images/album/beatriz-bg-md.jpg',
    '/images/album/jeremy-bg-md.jpg',
    '/images/album/nathan-bg-md.jpg',
    '/images/album/alvin-bg-md.jpg',
] as const;

const walkthrough = [1, 2, 3, 4].map((step, index) => ({
    step: t('marketing.shared.step', { number: String(step) }),
    title: t(`marketing.home.flow.items.${step}.title`),
    body: t(`marketing.home.flow.items.${step}.description`),
    highlights: [1, 2, 3].map((h) =>
        t(`marketing.home.flow.items.${step}.highlights.${h}`),
    ),
    image: flowImages[index],
    imageAlt: t(`marketing.home.flow.items.${step}.image_alt`),
}));

const capabilityIcons = [
    Images,
    Download,
    Smartphone,
    QrCode,
    MonitorPlay,
    Palette,
    MessageSquareText,
    ShieldCheck,
] as const;

const capabilities = capabilityIcons.map((icon, index) => ({
    icon,
    title: t(`marketing.home.capabilities.items.${index + 1}.title`),
    body: t(`marketing.home.capabilities.items.${index + 1}.description`),
}));

const comparisonRows = [1, 2, 3, 4, 5, 6].map((row) => ({
    label: t(`marketing.home.comparison.rows.${row}.label`),
    ours: t(`marketing.home.comparison.rows.${row}.ours`),
    others: t(`marketing.home.comparison.rows.${row}.others`),
}));

const faqItems = [1, 2, 3, 4, 5, 6, 7, 8, 9].map((item) => ({
    question: t(`marketing.home.faq.items.${item}.question`),
    answer: t(`marketing.home.faq.items.${item}.answer`),
}));

const isStandalonePwa = (): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        (window.navigator as Navigator & { standalone?: boolean })
            .standalone === true
    );
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

            if (
                !Number.isFinite(savedAtMs) ||
                Date.now() - savedAtMs > maxAgeMs
            ) {
                clearStoredGuestAlbum();
                return null;
            }
        }

        return {
            shareToken: parsed.shareToken,
            albumUrl: parsed.albumUrl,
            eventName: parsed.eventName,
            guestName:
                typeof parsed.guestName === 'string' ? parsed.guestName : null,
            guestToken:
                typeof parsed.guestToken === 'string'
                    ? parsed.guestToken
                    : null,
            logoUrl: typeof parsed.logoUrl === 'string' ? parsed.logoUrl : null,
            savedAt:
                typeof parsed.savedAt === 'string'
                    ? parsed.savedAt
                    : new Date().toISOString(),
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
        <Dialog
            :open="resumeGuestAlbumOpen"
            @update:open="resumeGuestAlbumOpen = $event"
        >
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
                            {{
                                t('marketing.pwa.resume.description', {
                                    eventName:
                                        resumeGuestAlbum?.eventName ?? '',
                                })
                            }}
                        </p>
                        <p
                            v-if="resumeGuestAlbum?.guestName"
                            class="text-sm font-medium text-promo-ink"
                        >
                            {{
                                t('marketing.pwa.resume.guest_name', {
                                    name: resumeGuestAlbum.guestName,
                                })
                            }}
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

        <!-- Hero -->
        <section
            class="mx-auto grid max-w-7xl items-center gap-12 px-4 pt-10 pb-20 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:pt-16 lg:pb-28"
        >
            <div class="max-w-2xl">
                <p
                    class="inline-flex items-center gap-2 rounded-full border border-promo-line bg-white px-4 py-2 text-xs font-semibold tracking-[0.14em] text-promo-primary uppercase shadow-card"
                >
                    <BadgeCheck class="size-4" />
                    {{ t('marketing.home.hero.badge') }}
                </p>

                <h1
                    class="mt-6 text-[2.6rem] leading-[1.04] font-bold tracking-[-0.03em] text-promo-ink sm:text-[3.4rem] lg:text-[3.9rem]"
                >
                    {{ t('marketing.home.hero.title') }}
                </h1>

                <p
                    class="mt-6 max-w-xl text-base leading-7 text-promo-muted sm:text-lg sm:leading-8"
                >
                    {{ t('marketing.home.hero.description') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="onboardingCreate({ query: { plan: 'free' } })"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-7 py-4 text-base font-semibold text-white shadow-[rgba(255,56,92,0.25)_0px_12px_28px] transition hover:bg-promo-primary-strong"
                    >
                        {{ t('marketing.actions.create_event') }}
                        <ArrowRight class="size-4" />
                    </Link>
                    <a
                        href="#how-it-works"
                        class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-7 py-4 text-base font-semibold text-promo-ink transition hover:bg-promo-surface"
                    >
                        <CirclePlay class="size-5 text-promo-primary" />
                        {{ t('marketing.home.hero.watch_demo') }}
                    </a>
                </div>

                <div
                    class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm font-medium text-promo-muted"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <span class="flex text-promo-primary">
                            <Star
                                v-for="i in 5"
                                :key="i"
                                class="size-4 fill-current"
                            />
                        </span>
                        {{ t('marketing.home.hero.rating') }}
                    </span>
                    <span
                        v-for="pill in heroPills"
                        :key="pill"
                        class="inline-flex items-center gap-1.5"
                    >
                        <Check class="size-4 text-promo-primary" />
                        {{ pill }}
                    </span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl lg:max-w-none">
                <div
                    class="overflow-hidden rounded-[2rem] shadow-[rgba(0,0,0,0.04)_0px_6px_16px,rgba(0,0,0,0.12)_0px_24px_48px]"
                >
                    <img
                        src="/images/album/drew-bg-md.jpg"
                        :alt="t('marketing.home.hero.gallery_main_alt')"
                        class="aspect-[4/3] w-full object-cover"
                        fetchpriority="high"
                    />
                </div>

                <div
                    class="absolute -bottom-8 -left-4 w-44 overflow-hidden rounded-[1.4rem] border-4 border-white shadow-[rgba(0,0,0,0.18)_0px_18px_36px] sm:-left-8 sm:w-56"
                >
                    <img
                        src="/images/album/sandy-bg-sm.jpg"
                        :alt="t('marketing.home.hero.gallery_moment_alt')"
                        class="aspect-[4/3] w-full object-cover"
                        loading="lazy"
                    />
                </div>

                <div
                    class="absolute -top-5 -left-3 inline-flex items-center gap-3 rounded-[1.2rem] bg-white px-4 py-3 shadow-card sm:-left-6"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                    >
                        <QrCode class="size-5" />
                    </span>
                    <span>
                        <span
                            class="block text-sm font-semibold text-promo-ink"
                        >
                            {{ t('marketing.home.hero.join_qr_title') }}
                        </span>
                        <span class="block text-xs text-promo-muted">
                            {{ t('marketing.home.hero.join_qr_description') }}
                        </span>
                    </span>
                </div>

                <div
                    class="absolute -right-3 -bottom-5 inline-flex items-center gap-3 rounded-[1.2rem] bg-white px-4 py-3 shadow-card sm:-right-5"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                    >
                        <MonitorPlay class="size-5" />
                    </span>
                    <span>
                        <span
                            class="block text-sm font-semibold text-promo-ink"
                        >
                            {{ t('marketing.home.hero.live_wall_title') }}
                        </span>
                        <span class="block text-xs text-promo-muted">
                            {{ t('marketing.home.hero.no_app_description') }}
                        </span>
                    </span>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section
            id="how-it-works"
            class="border-y border-promo-line bg-promo-surface/60"
        >
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.flow.eyebrow')"
                    :title="t('marketing.home.flow.title')"
                    :description="t('marketing.home.flow.description')"
                    centered
                />

                <div class="mt-16 space-y-16 lg:space-y-20">
                    <article
                        v-for="(item, index) in walkthrough"
                        :key="item.step"
                        class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16"
                    >
                        <div
                            class="max-w-xl"
                            :class="{ 'lg:order-2': index % 2 === 1 }"
                        >
                            <p class="marketing-kicker">{{ item.step }}</p>
                            <h3
                                class="mt-3 text-2xl font-bold tracking-[-0.02em] text-promo-ink sm:text-3xl"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="mt-4 text-base leading-7 text-promo-muted"
                            >
                                {{ item.body }}
                            </p>
                            <ul class="mt-6 space-y-3">
                                <li
                                    v-for="highlight in item.highlights"
                                    :key="highlight"
                                    class="flex items-center gap-3 text-sm font-medium text-promo-ink"
                                >
                                    <span
                                        class="flex size-6 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                                    >
                                        <Check class="size-3.5" />
                                    </span>
                                    {{ highlight }}
                                </li>
                            </ul>
                        </div>

                        <div :class="{ 'lg:order-1': index % 2 === 1 }">
                            <div
                                class="overflow-hidden rounded-[1.5rem] shadow-card"
                            >
                                <img
                                    :src="item.image"
                                    :alt="item.imageAlt"
                                    class="aspect-[16/10] w-full object-cover transition duration-500 hover:scale-[1.02]"
                                    loading="lazy"
                                />
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Capabilities -->
        <section
            id="services"
            class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8"
        >
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.capabilities.eyebrow')"
                :title="t('marketing.home.capabilities.title')"
                :description="t('marketing.home.capabilities.description')"
                centered
            />

            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article
                    v-for="capability in capabilities"
                    :key="capability.title"
                    class="rounded-[1.25rem] bg-white p-6 shadow-card transition hover:shadow-card-hover"
                >
                    <span
                        class="flex size-11 items-center justify-center rounded-full bg-promo-primary/10 text-promo-primary"
                    >
                        <component :is="capability.icon" class="size-5" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-promo-ink">
                        {{ capability.title }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                        {{ capability.body }}
                    </p>
                </article>
            </div>
        </section>

        <!-- Comparison -->
        <section
            id="proof"
            class="border-y border-promo-line bg-promo-surface/60"
        >
            <div class="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    :eyebrow="t('marketing.home.comparison.eyebrow')"
                    :title="t('marketing.home.comparison.title')"
                    :description="t('marketing.home.comparison.description')"
                    centered
                />

                <div
                    class="mt-14 overflow-hidden rounded-[1.5rem] bg-white shadow-card"
                >
                    <div
                        class="hidden grid-cols-[1fr_1.2fr_1.2fr] gap-4 border-b border-promo-line px-6 py-4 text-xs font-semibold tracking-[0.14em] uppercase sm:grid"
                    >
                        <span class="text-promo-muted">&nbsp;</span>
                        <span class="text-promo-primary">
                            {{ t('marketing.shared.our_product') }}
                        </span>
                        <span class="text-promo-muted">
                            {{ t('marketing.shared.other_apps') }}
                        </span>
                    </div>

                    <div
                        v-for="(row, index) in comparisonRows"
                        :key="row.label"
                        class="grid gap-3 px-6 py-5 sm:grid-cols-[1fr_1.2fr_1.2fr] sm:gap-4"
                        :class="{
                            'border-t border-promo-line': index > 0,
                        }"
                    >
                        <div class="text-sm font-semibold text-promo-ink">
                            {{ row.label }}
                        </div>
                        <div
                            class="flex items-start gap-2 text-sm text-promo-ink"
                        >
                            <span
                                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                            >
                                <Check class="size-3" />
                            </span>
                            {{ row.ours }}
                        </div>
                        <div
                            class="flex items-start gap-2 text-sm text-promo-muted"
                        >
                            <span
                                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-promo-surface-strong text-promo-muted"
                            >
                                <Minus class="size-3" />
                            </span>
                            {{ row.others }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                :eyebrow="t('marketing.home.faq.eyebrow')"
                :title="t('marketing.home.faq.title')"
                :description="t('marketing.home.faq.description')"
                centered
            />

            <div class="mt-12 space-y-3">
                <details
                    v-for="item in faqItems"
                    :key="item.question"
                    class="group rounded-[1.25rem] bg-white shadow-card"
                >
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 text-base font-semibold text-promo-ink [&::-webkit-details-marker]:hidden"
                    >
                        {{ item.question }}
                        <ChevronDown
                            class="size-5 shrink-0 text-promo-muted transition group-open:rotate-180"
                        />
                    </summary>
                    <p class="px-6 pb-6 text-sm leading-7 text-promo-muted">
                        {{ item.answer }}
                    </p>
                </details>
            </div>
        </section>

        <!-- CTA -->
        <section id="cta" class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem]">
                <img
                    src="/images/album/nathan-bg-md.jpg"
                    :alt="t('marketing.home.hero.gallery_moment_alt')"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                />
                <div
                    class="absolute inset-0 bg-[linear-gradient(100deg,rgba(20,12,8,0.82)_0%,rgba(20,12,8,0.55)_55%,rgba(20,12,8,0.25)_100%)]"
                />

                <div
                    class="relative px-7 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24"
                >
                    <p
                        class="text-xs font-semibold tracking-[0.2em] text-white/80 uppercase"
                    >
                        {{ t('marketing.home.cta.eyebrow') }}
                    </p>
                    <h2
                        class="mt-4 max-w-2xl text-3xl leading-tight font-bold tracking-[-0.02em] text-white sm:text-4xl lg:text-[2.75rem]"
                    >
                        {{ t('marketing.home.cta.title') }}
                    </h2>
                    <p class="mt-5 max-w-xl text-base leading-7 text-white/85">
                        {{ t('marketing.home.cta.description') }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="
                                onboardingCreate({ query: { plan: 'free' } })
                            "
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-7 py-4 text-base font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            {{ t('marketing.actions.create_event') }}
                            <ArrowRight class="size-4" />
                        </Link>
                        <Link
                            href="/album"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-white/35 bg-white/10 px-7 py-4 text-base font-semibold text-white backdrop-blur transition hover:bg-white/20"
                        >
                            {{ t('marketing.footer.album_access') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
