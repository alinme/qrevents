<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, Building2, LayoutPanelTop, MonitorPlay, Repeat2, ShieldCheck, Sparkles, Users } from 'lucide-vue-next';
import MarketingFeatureCard from '@/components/marketing/MarketingFeatureCard.vue';
import MarketingSectionHeading from '@/components/marketing/MarketingSectionHeading.vue';
import MarketingStepCard from '@/components/marketing/MarketingStepCard.vue';
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { pricing, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

const businessImages = [
    '/fake-media/503748303_1048043510209927_1182952490263678396_n.jpg',
    '/fake-media/503058833_1256500452532301_4806283181595489701_n.jpg',
    '/fake-media/500454456_707421155099931_505239495313893019_n.jpg',
    '/fake-media/503096688_1013285264308603_8150632900022158723_n.jpg',
];

const businessFeatures = [
    {
        icon: Building2,
        title: 'Venues',
        description: 'Offer guests a polished photo-sharing and display experience that feels like part of the event, not an afterthought.',
    },
    {
        icon: BriefcaseBusiness,
        title: 'Planners',
        description: 'Reuse the same smooth event flow across multiple clients instead of rebuilding the process from scratch.',
    },
    {
        icon: Repeat2,
        title: 'Agencies',
        description: 'Turn QR-based capture and live walls into a repeatable, branded service for activations and campaigns.',
    },
    {
        icon: ShieldCheck,
        title: 'Operations teams',
        description: 'Keep storage, moderation, downloads, and event settings in one dependable dashboard.',
    },
];

const businessSteps = [
    {
        step: 'Step 01',
        title: 'Launch a branded event page',
        description: 'Create a clean, guest-facing album and QR code for each event while keeping host controls in the dashboard.',
        image: businessImages[0],
        imageAlt: 'Branded event page for a live activation',
        highlights: ['Fast event setup', 'Branded links and QR codes', 'Friendly host workflow'],
    },
    {
        step: 'Step 02',
        title: 'Drive engagement on-site',
        description: 'Guests or attendees scan the code, upload instantly, and feel part of the event without any onboarding friction.',
        image: businessImages[1],
        imageAlt: 'Guests engaging with a live event photo wall',
        highlights: ['No app downloads', 'Great for venue signage', 'Works for activations and conferences'],
    },
    {
        step: 'Step 03',
        title: 'Deliver a polished handoff',
        description: 'Finish with an organized album, downloads, and media collection you can hand to clients or internal teams confidently.',
        image: businessImages[2],
        imageAlt: 'Event content ready for client handoff',
        highlights: ['Organized exports', 'Great for recurring events', 'Built for operational reuse'],
    },
];
</script>

<template>
    <MarketingLayout
        title="Businesses"
        description="QR Events for venues, planners, agencies, and operational event teams."
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8 lg:pb-24 lg:pt-16">
            <div class="grid items-center gap-14 lg:grid-cols-[0.94fr_1.06fr]">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <Sparkles class="size-3.5" />
                        Business use case
                    </p>

                    <h1 class="mt-6 text-5xl font-extrabold leading-[0.96] tracking-[-0.06em] text-promo-ink sm:text-6xl">
                        Event photo sharing for teams that do this again and again
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-promo-muted sm:text-xl">
                        QR Events turns guest capture, live display, and final delivery into a repeatable service that feels modern enough for clients and easy enough for every attendee.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Start Your First Event
                            <ArrowRight class="size-4" />
                        </Link>

                        <Link
                            :href="pricing()"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            View Pricing
                        </Link>
                    </div>
                </div>

                <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_24px_70px_rgba(120,86,255,0.12)]">
                    <div class="grid gap-4 lg:grid-cols-[0.92fr_1.08fr]">
                        <div class="grid gap-4">
                            <div class="rounded-[24px] border border-promo-line bg-promo-surface p-5">
                                <div class="flex items-start gap-4">
                                    <div class="flex size-11 items-center justify-center rounded-[18px] bg-white text-promo-primary">
                                        <LayoutPanelTop class="size-5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                            Operational shape
                                        </p>
                                        <p class="mt-2 text-sm leading-7 text-promo-muted">
                                            One guest-facing experience, one host dashboard, and one polished media handoff.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-[22px] border border-promo-line bg-white p-4">
                                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                        <Users class="size-4" />
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-promo-ink">
                                        Guest album
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        Scan, upload, react.
                                    </p>
                                </div>
                                <div class="rounded-[22px] border border-promo-line bg-white p-4">
                                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                        <MonitorPlay class="size-4" />
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-promo-ink">
                                        Live wall
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-promo-muted">
                                        Perfect for screens.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <img
                                :src="businessImages[0]"
                                alt="Business event photo wall with guest uploads"
                                class="aspect-[5/4] w-full rounded-[26px] object-cover"
                            />
                            <div class="grid grid-cols-2 gap-4">
                                <img
                                    :src="businessImages[1]"
                                    alt="Guests contributing to an event album"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                                <img
                                    :src="businessImages[2]"
                                    alt="Live event moment from a professional activation"
                                    class="aspect-square rounded-[22px] object-cover"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Built for repeatable operations"
                    title="A cleaner event product for venues, planners, agencies, and teams"
                    description="Everything is designed to make professional event delivery feel simpler, more modern, and easier to repeat."
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="feature in businessFeatures"
                        :key="feature.title"
                        :icon="feature.icon"
                        :title="feature.title"
                        :description="feature.description"
                    />
                </div>
            </div>
        </section>

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="How it works for business teams"
                    title="A product your team can run confidently across multiple events"
                    description="From setup to live engagement to delivery, the flow is built to feel operational instead of improvised."
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <MarketingStepCard
                        v-for="item in businessSteps"
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
