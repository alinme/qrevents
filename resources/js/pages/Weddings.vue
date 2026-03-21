<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Camera, Heart, MessageSquareText, MonitorPlay, QrCode, Sparkles } from 'lucide-vue-next';
import MarketingFeatureCard from '@/components/marketing/MarketingFeatureCard.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingStepCard from '@/components/marketing/MarketingStepCard.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { pricing, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

const weddingImages = [
    '/fake-media/503096688_1013285264308603_8150632900022158723_n.jpg',
    '/fake-media/503962837_9861361353953264_525766796662049219_n.jpg',
    '/fake-media/504824543_1521239702593913_4818893469591191053_n.jpg',
    '/fake-media/503748303_1048043510209927_1182952490263678396_n.jpg',
];

const weddingBenefits = [
    {
        icon: Camera,
        title: 'Candid moments from every table',
        description: 'Capture the little scenes and reactions that rarely make it back to the couple later.',
    },
    {
        icon: MessageSquareText,
        title: 'A digital guestbook with real warmth',
        description: 'Pair photos with wishes, captions, and congratulations while the celebration is still happening.',
    },
    {
        icon: MonitorPlay,
        title: 'A live wall guests love watching',
        description: 'Turn a venue display into something joyful and interactive instead of a passive screen.',
    },
    {
        icon: Heart,
        title: 'Simple enough for every guest',
        description: 'One QR code, no downloads, no awkward setup, and almost no explanation required.',
    },
];

const weddingSteps = [
    {
        step: 'Step 01',
        title: 'Create your wedding album',
        description: 'Set up the album, add your title and event details, and get a wedding-ready sharing page in minutes.',
        image: weddingImages[0],
        imageAlt: 'Wedding event album cover photo',
        highlights: ['Fast setup', 'Beautiful shared album', 'Ready before the first guest arrives'],
    },
    {
        step: 'Step 02',
        title: 'Share one QR code with everyone',
        description: 'Place it on welcome signs, table cards, menus, or programs so guests always know where to upload.',
        image: weddingImages[1],
        imageAlt: 'Wedding guest joining the shared album from a phone',
        highlights: ['Print on signs and stationery', 'No registration required', 'Works for every age group'],
    },
    {
        step: 'Step 03',
        title: 'Watch the reception come alive',
        description: 'As photos arrive, the live wall turns the room into part of the experience and keeps uploads coming.',
        image: weddingImages[3],
        imageAlt: 'Live wedding slideshow on a venue screen',
        highlights: ['Great for receptions', 'Real-time updates', 'Fun, celebratory energy'],
    },
];
</script>

<template>
    <MarketingLayout
        title="Weddings"
        description="A joyful wedding photo-sharing experience with QR code access, guest uploads, and a live slideshow."
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="grid items-center gap-14 lg:grid-cols-[0.92fr_1.08fr]">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <Sparkles class="size-3.5" />
                        Wedding use case
                    </p>

                    <h1 class="mt-6 text-5xl font-extrabold leading-[0.96] tracking-[-0.06em] text-promo-ink sm:text-6xl">
                        A wedding photo album guests actually want to use on the day itself
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-promo-muted sm:text-xl">
                        QR Events turns your wedding into a live digital guestbook, candid photo collector, and reception-ready slideshow without making guests download anything.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Create Your Wedding
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            See Pricing
                        </Link>
                    </div>
                </div>

                <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_24px_70px_rgba(232,79,154,0.12)]">
                    <div class="grid gap-4 lg:grid-cols-[1.08fr_0.92fr]">
                        <img
                            :src="weddingImages[0]"
                            alt="Wedding guests sharing photos in a digital album"
                            class="aspect-[4/5] w-full rounded-[26px] object-cover"
                        />

                        <div class="grid gap-4">
                            <div class="rounded-[24px] border border-promo-line bg-promo-surface p-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-11 items-center justify-center rounded-[18px] bg-white text-promo-primary">
                                        <QrCode class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                            One QR, no app
                                        </p>
                                        <p class="mt-2 text-sm leading-6 text-promo-muted">
                                            Guests scan once and start sharing immediately.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <img
                                    :src="weddingImages[1]"
                                    alt="Wedding guest photo upload preview"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                                <img
                                    :src="weddingImages[2]"
                                    alt="Wedding memory inside the event gallery"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                            </div>

                            <div class="rounded-[24px] border border-promo-line bg-promo-warm p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                    Reception-ready
                                </p>
                                <p class="mt-3 text-sm leading-7 text-promo-muted">
                                    Show the live wall on a venue screen and let every upload feel instantly part of the celebration.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Why couples love it"
                    title="Wedding photo sharing that feels emotional, easy, and genuinely modern"
                    description="Built for couples who want more than a file drop and less friction than a typical app."
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="benefit in weddingBenefits"
                        :key="benefit.title"
                        :icon="benefit.icon"
                        :title="benefit.title"
                        :description="benefit.description"
                    />
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="How it works for weddings"
                    title="From ceremony to afterparty, every guest knows exactly what to do"
                    description="The whole experience is built to feel obvious, lightweight, and wedding-friendly."
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <MarketingStepCard
                        v-for="item in weddingSteps"
                        :key="item.step"
                        :step="item.step"
                        :title="item.title"
                        :description="item.description"
                        :image="item.image"
                        :image-alt="item.imageAlt"
                        :highlights="item.highlights"
                    />
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>
