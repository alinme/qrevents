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
import MarketingLayout from '@/layouts/MarketingLayout.vue';
import { businesses, pricing, register, weddings } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

type FeatureItem = {
    icon: Component;
    title: string;
    description: string;
    eyebrow?: string;
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
        step: 'Step 01',
        title: 'Sign up and create your event',
        description:
            'Launch a digital album in minutes with a clean host dashboard and event-ready settings.',
        image: heroGallery[0],
        imageAlt: 'Event host creating a new event album',
        highlights: ['Start free', 'Custom event page', 'Setup in under two minutes'],
    },
    {
        step: 'Step 02',
        title: 'Guests join with a link or QR code',
        description:
            'Share a single event link or print a QR code on signs, menus, invitations, and table cards.',
        image: heroGallery[1],
        imageAlt: 'Guests joining an event photo album from a phone',
        highlights: ['No app download', 'No registration required', 'Works in any browser'],
    },
    {
        step: 'Step 03',
        title: 'Display a live slideshow or photo wall',
        description:
            'Turn guest uploads into a real-time event moment with a screen-ready live wall that keeps energy high.',
        image: heroGallery[3],
        imageAlt: 'Live event slideshow displaying guest photos on a screen',
        highlights: ['Auto-refreshing display', 'Great for TVs and projectors', 'Keeps guests engaged'],
    },
    {
        step: 'Step 04',
        title: 'Download every captured memory',
        description:
            'Wrap up with a beautiful album full of candid moments, videos, captions, and one-click exports.',
        image: heroGallery[2],
        imageAlt: 'Host reviewing and downloading a finished event album',
        highlights: ['One-click download', 'Organized handoff', 'Every guest contribution in one place'],
    },
];

const valueFeatures: FeatureItem[] = [
    {
        icon: Image,
        title: 'Guest Photos & Videos',
        description:
            'Collect the moments your photographer never sees, from every table, angle, and surprise on the day.',
    },
    {
        icon: MessageSquareText,
        title: 'Text Posts & Captions',
        description:
            'Let guests leave notes, stories, and congratulations alongside their photos so the album feels personal.',
    },
    {
        icon: Smartphone,
        title: 'Extremely Easy to Use',
        description:
            'If someone can scan a QR code, they can contribute. No apps. No accounts. No friction.',
    },
    {
        icon: MonitorPlay,
        title: 'Live Slideshow',
        description:
            'Show uploads in real time on a TV, projector, or venue display and turn the room into part of the product.',
    },
];

const howItWorks = [
    {
        step: '01',
        title: 'Create your event',
        description:
            'Set up a polished event page with your title, dates, branding, and photo wall settings in a host dashboard designed for speed.',
        image: heroGallery[0],
        imageAlt: 'Dashboard screen for creating a new event',
        highlights: ['Fast setup wizard', 'Friendly host dashboard', 'Beautiful album landing page'],
    },
    {
        step: '02',
        title: 'Share it with your guests',
        description:
            'Drop the link into chats, email, or SMS, or print the QR code anywhere guests are already looking.',
        image: heroGallery[1],
        imageAlt: 'Guests scanning a QR code to join an event album',
        highlights: ['Share via email, SMS, or chat apps', 'Print on cards and signs', 'Unlimited guests can join'],
    },
    {
        step: '03',
        title: 'Display it all on a live slideshow',
        description:
            'As photos arrive, your event wall stays fresh and lively, helping every upload feel instantly seen.',
        image: heroGallery[3],
        imageAlt: 'Live photo wall showing event moments in real time',
        highlights: ['Real-time updates', 'Designed for big screens', 'Great for receptions and public events'],
    },
    {
        step: '04',
        title: 'Enjoy all captured moments',
        description:
            'After the event, everything is already organized in a beautiful digital album ready to browse, share, and export.',
        image: heroGallery[2],
        imageAlt: 'Finished event album with candid guest photos',
        highlights: ['One-click download of all photos', 'Guests can relive the album', 'Perfect for multi-day events too'],
    },
];

const capabilities: FeatureItem[] = [
    { icon: Image, title: 'Digital Album', description: 'A polished shared album guests actually enjoy using.' },
    { icon: Download, title: 'One-click Download', description: 'Download the whole event collection without messy file chasing.' },
    { icon: Smartphone, title: 'No Apps Required', description: 'Open the link, upload, and enjoy. That is the whole experience.' },
    { icon: QrCode, title: 'QR Code Templates', description: 'Turn printed signs, invitations, and table cards into entry points.' },
    { icon: MonitorPlay, title: 'Live Photo Wall', description: 'Keep a TV or projector alive with guest moments in real time.' },
    { icon: SlidersHorizontal, title: 'Customizations', description: 'Tune the event look and flow without making setup feel technical.' },
    { icon: MessageSquareText, title: 'Text & Captions', description: 'Capture wishes, notes, and context instead of only media files.' },
    { icon: LockKeyhole, title: 'Private & Secured', description: 'Hosts stay in control of access, moderation, and final delivery.' },
];

const useCases = [
    {
        label: 'Weddings',
        title: 'A joyful digital guestbook for the whole day',
        description: 'Collect candid moments, wishes, and videos from every guest without awkward setup.',
        icon: Heart,
        href: weddings().url,
    },
    {
        label: 'Parties & Celebrations',
        title: 'Perfect for surprise parties, showers, and reunions',
        description: 'Let everyone add to one shared album while the celebration is still happening.',
        icon: PartyPopper,
        href: `${weddings().url}#celebrations`,
    },
    {
        label: 'Birthdays',
        title: 'Keep every laugh, photo, and candid clip together',
        description: 'Make it easy for guests of every age to contribute without downloading anything.',
        icon: Gift,
        href: `${pricing().url}#birthday`,
    },
    {
        label: 'Conferences & Public Events',
        title: 'Boost participation with a live wall on site',
        description: 'Turn guests and attendees into contributors for activations, meetups, and public programs.',
        icon: Globe2,
        href: businesses().url,
    },
    {
        label: 'Corporate Events',
        title: 'Easy event content for internal teams and guests',
        description: 'Create branded albums and live displays that feel simple enough for everyone to use.',
        icon: Building2,
        href: businesses().url,
    },
    {
        label: 'Businesses / Professionals',
        title: 'Offer polished event photo sharing as part of your service',
        description: 'Ideal for planners, venues, agencies, and event-focused teams.',
        icon: BriefcaseBusiness,
        href: businesses().url,
    },
];

const stats = [
    { value: '2 min', label: 'Average setup time' },
    { value: '10k+', label: 'Albums created' },
    { value: '1M+', label: 'Moments captured' },
    { value: '100%', label: 'Browser-based for guests' },
];

const testimonials = [
    {
        quote:
            'We put the QR code on every reception table and had hundreds of candid photos by the end of dinner. Guests kept saying how easy it was.',
        name: 'Emma',
        detail: 'Wedding host, France',
        image: heroGallery[0],
    },
    {
        quote:
            'The live wall was the surprise hit. People uploaded more just to see their pictures show up on the screen a minute later.',
        name: 'Marcus',
        detail: 'Birthday celebration, Germany',
        image: heroGallery[1],
    },
    {
        quote:
            'It felt premium without feeling technical. That is incredibly rare with event tools.',
        name: 'Leah',
        detail: 'Venue manager, UK',
        image: heroGallery[3],
    },
    {
        quote:
            'We used it for a two-day conference and it gave us a beautiful attendee-powered recap with almost no admin overhead.',
        name: 'Noah',
        detail: 'Conference organizer, Netherlands',
        image: heroGallery[2],
    },
];

const comparisonRows = [
    {
        label: 'Guest experience',
        ours: 'Open link or scan QR and upload instantly',
        others: 'Extra steps, confusing flows, or account creation',
    },
    {
        label: 'Album design',
        ours: 'Beautiful digital albums built for events',
        others: 'Generic file buckets or dated galleries',
    },
    {
        label: 'Live slideshow',
        ours: 'Included and ready for screens',
        others: 'Often missing entirely',
    },
    {
        label: 'Guest capacity',
        ours: 'Built for the whole room to join',
        others: 'Often limited or awkward to scale',
    },
    {
        label: 'Customization',
        ours: 'Flexible sharing, branding, and display options',
        others: 'Minimal control and weak presentation',
    },
    {
        label: 'Getting started',
        ours: 'Start free and launch in minutes',
        others: 'More setup friction or upfront commitment',
    },
];

const faqs = [
    {
        question: 'Do guests need to download an app?',
        answer:
            'No. Guests open the event link or scan the QR code and upload directly from their browser.',
    },
    {
        question: 'Can I download all guests’ photos and videos?',
        answer:
            'Yes. Hosts can export the entire collection in one organized download after the event.',
    },
    {
        question: 'Why is this better than generic sharing apps?',
        answer:
            'It is designed for events first: easier guest onboarding, prettier albums, QR access, and an optional live photo wall.',
    },
    {
        question: 'Can I print the QR code on signs?',
        answer:
            'Absolutely. It works great on welcome signs, table cards, programs, badges, and posters.',
    },
    {
        question: 'Can multi-day events use it?',
        answer:
            'Yes. It works well for weddings, retreats, festivals, conferences, and any event that stretches across multiple days.',
    },
    {
        question: 'How does the live photo wall work?',
        answer:
            'Open the dedicated wall view on a TV or projector and incoming guest photos can appear in a clean, screen-friendly layout.',
    },
];

const blogPosts = [
    {
        category: 'Planning',
        title: 'Where to place your event QR code so more guests actually upload',
        body: 'The tiny placement choices that make photo sharing feel obvious instead of optional.',
    },
    {
        category: 'Weddings',
        title: 'How to turn guest photos into a digital guestbook people revisit',
        body: 'Mix photos, wishes, captions, and a slideshow into something that feels worth keeping.',
    },
    {
        category: 'Venues',
        title: 'Why live photo walls create better event energy than passive displays',
        body: 'Screens become participation magnets when guests know their upload can show up in seconds.',
    },
];
</script>

<template>
    <MarketingLayout
        title="QR Events"
        description="A joyful event photo-sharing platform for collecting guest uploads, live slideshows, and beautiful digital albums."
        :can-register="canRegister"
    >
        <section class="mx-auto max-w-7xl px-4 pb-18 pt-10 sm:px-6 lg:px-8 lg:pb-24 lg:pt-18">
            <div class="grid items-center gap-14 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-promo-line bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                        <BadgeCheck class="size-4" />
                        #1 photo sharing app
                    </div>

                    <h1 class="mt-6 text-5xl font-extrabold leading-[0.95] tracking-[-0.06em] text-promo-ink sm:text-6xl lg:text-[5.2rem]">
                        Easily collect photos from every guest at your event
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-promo-muted sm:text-xl">
                        Let guests upload photos and videos into one beautiful digital album, with no app downloads, no complicated setup, and an optional live slideshow that keeps the room buzzing.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white shadow-[0_16px_34px_rgba(232,79,154,0.28)] transition hover:bg-promo-primary-strong"
                        >
                            Create Your Event
                            <ArrowRight class="size-4" />
                        </Link>

                        <a
                            href="#how-it-works"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-promo-line bg-white px-6 py-4 text-sm font-semibold text-promo-ink transition hover:bg-promo-surface"
                        >
                            Watch How It Works
                            <CirclePlay class="size-4" />
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-4 text-sm text-promo-ink/82">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-[0_10px_24px_rgba(120,86,255,0.08)]">
                            <div class="flex items-center gap-1 text-promo-primary">
                                <Star v-for="star in 5" :key="star" class="size-4 fill-current" />
                            </div>
                            4.9/5 host rating
                        </div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">Start free</div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">Setup in under 2 minutes</div>
                        <div class="rounded-full bg-promo-surface px-4 py-2">Chosen by thousands of hosts</div>
                    </div>
                </div>

                <div class="relative">
                    <div class="rounded-[32px] border border-promo-line bg-white p-4 shadow-[0_28px_80px_rgba(120,86,255,0.14)]">
                        <div class="grid gap-4 lg:grid-cols-[0.56fr_0.44fr]">
                            <div class="overflow-hidden rounded-[26px] bg-promo-surface">
                                <img
                                    :src="heroGallery[0]"
                                    alt="Guests sharing event photos in a digital album"
                                    class="aspect-[4/5] w-full object-cover"
                                />
                            </div>

                            <div class="grid gap-4">
                                <div class="rounded-[24px] border border-promo-line bg-promo-surface p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                                Join with QR
                                            </p>
                                            <p class="mt-1 text-sm text-promo-muted">
                                                Scan and upload in seconds
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
                                        alt="Phone screen with a shared event album"
                                        class="aspect-square rounded-[24px] object-cover"
                                    />
                                    <img
                                        :src="heroGallery[2]"
                                        alt="Happy event moment inside a digital gallery"
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
                                                Live slideshow ready
                                            </p>
                                            <p class="mt-2 text-sm leading-6 text-promo-muted">
                                                Display incoming moments on a TV or projector while guests keep uploading.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -left-4 rounded-[22px] border border-promo-line bg-white px-5 py-4 shadow-[0_18px_44px_rgba(232,79,154,0.14)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                            No app downloads
                        </p>
                        <p class="mt-2 text-sm font-medium text-promo-ink">
                            Just a link or QR code
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-promo-line bg-white">
            <div class="mx-auto max-w-6xl px-4 py-8 text-center sm:px-6 lg:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-promo-muted">
                    As seen on
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-x-8 gap-y-4 text-sm font-semibold uppercase tracking-[0.24em] text-promo-muted/82">
                    <span v-for="logo in logoRow" :key="logo">{{ logo }}</span>
                </div>
            </div>
        </section>

        <section id="steps" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="How it starts"
                title="Launch your album in four joyful steps"
                description="Everything is designed to feel lightweight for guests and reassuring for hosts."
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

        <section class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Event photo sharing made easy"
                    title="Collect and relive event memories in one beautiful digital album"
                    description="Built for hosts who want something polished enough to feel premium and simple enough for every guest to understand instantly."
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

        <section id="how-it-works" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="How it works"
                title="From first setup to final download, every step feels easy"
                description="The product is designed to reduce friction for guests while still giving hosts the controls they need."
            />

            <div class="mt-16 space-y-8">
                <article
                    v-for="(item, index) in howItWorks"
                    :key="item.step"
                    class="grid items-center gap-8 rounded-[30px] border border-promo-line bg-white p-6 shadow-[0_18px_55px_rgba(120,86,255,0.08)] lg:grid-cols-[1fr_1fr] lg:p-8"
                >
                    <div :class="index % 2 === 1 ? 'lg:order-2' : ''">
                        <div class="inline-flex items-center gap-3 rounded-full bg-promo-surface px-4 py-2">
                            <span class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                Step {{ item.step }}
                            </span>
                        </div>
                        <h3 class="mt-5 text-3xl font-extrabold tracking-[-0.04em] text-promo-ink">
                            {{ item.title }}
                        </h3>
                        <p class="mt-4 text-base leading-8 text-promo-muted">
                            {{ item.description }}
                        </p>
                        <ul class="mt-6 grid gap-3 text-sm text-promo-ink/84">
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

                    <div
                        class="rounded-[26px] border border-promo-line bg-linear-to-br from-promo-surface to-white p-4"
                        :class="index % 2 === 1 ? 'lg:order-1' : ''"
                    >
                        <img
                            :src="item.image"
                            :alt="item.imageAlt"
                            class="aspect-[5/4] w-full rounded-[22px] object-cover"
                            loading="lazy"
                            decoding="async"
                        />
                    </div>
                </article>
            </div>
        </section>

        <section id="capabilities" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="All you need for a perfect event"
                    title="Built to help every event feel easy, elegant, and fully shared"
                    description="Everything from QR code entry to secure downloads is designed to feel polished and reassuring."
                    centered
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <MarketingFeatureCard
                        v-for="feature in capabilities"
                        :key="feature.title"
                        :icon="feature.icon"
                        :title="feature.title"
                        :description="feature.description"
                    />
                </div>
            </div>
        </section>

        <section id="use-cases" class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Event photo sharing for any occasion"
                    title="Made for weddings, celebrations, conferences, and every event in between"
                    description="The same product logic works beautifully for private milestones and large public experiences alike."
                />

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <Link
                        v-for="item in useCases"
                        :key="item.title"
                        :href="item.href"
                        class="group rounded-[26px] border border-promo-line bg-white p-6 shadow-[0_16px_46px_rgba(232,79,154,0.08)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_22px_60px_rgba(232,79,154,0.12)]"
                    >
                        <div class="flex size-12 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-surface to-promo-purple text-promo-primary">
                            <component :is="item.icon" class="size-5" />
                        </div>
                        <p class="mt-5 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                            {{ item.label }}
                        </p>
                        <h3 class="mt-3 text-2xl font-bold tracking-[-0.03em] text-promo-ink">
                            {{ item.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-promo-muted">
                            {{ item.description }}
                        </p>
                        <div class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-promo-ink">
                            Learn more
                            <ArrowRight class="size-4 transition group-hover:translate-x-1" />
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="grid gap-10 rounded-[34px] border border-promo-line bg-white p-6 shadow-[0_22px_70px_rgba(120,86,255,0.1)] lg:grid-cols-[0.9fr_1.1fr] lg:p-10">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-promo-primary">
                        Social proof
                    </p>
                    <h2 class="mt-4 text-4xl font-extrabold tracking-[-0.05em] text-promo-ink lg:text-5xl">
                        Millions of moments shared across thousands of unforgettable events
                    </h2>
                    <p class="mt-5 max-w-lg text-base leading-8 text-promo-muted">
                        Hosts choose QR Events because it feels joyful for guests, fast for planners, and polished enough to trust with once-in-a-lifetime memories.
                    </p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="item in stats"
                            :key="item.label"
                            class="rounded-[22px] border border-promo-line bg-promo-surface p-5"
                        >
                            <div class="text-3xl font-extrabold tracking-[-0.04em] text-promo-ink">
                                {{ item.value }}
                            </div>
                            <div class="mt-2 text-sm text-promo-muted">
                                {{ item.label }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Create Your Event
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

                <div class="grid gap-4 sm:grid-cols-2">
                    <img
                        v-for="image in heroGallery"
                        :key="`stat-${image}`"
                        :src="image"
                        alt="Happy event photo moment"
                        class="aspect-[4/3] w-full rounded-[26px] object-cover"
                        loading="lazy"
                        decoding="async"
                    />
                </div>
            </div>
        </section>

        <section id="reviews" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Don’t just take our word for it"
                    title="Loved by hosts who want beautiful event sharing without friction"
                    description="Warm, candid reviews from people who needed something elegant enough for guests and simple enough for everyone to use."
                />

                <div class="mt-6 inline-flex items-center gap-3 rounded-full bg-promo-surface px-4 py-2 text-sm font-semibold text-promo-ink">
                    <div class="flex items-center gap-1 text-promo-primary">
                        <Star v-for="star in 5" :key="`summary-${star}`" class="size-4 fill-current" />
                    </div>
                    Rated 4.9 by hosts across weddings, parties, and public events
                </div>

                <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
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

        <section id="comparison" class="bg-promo-surface/55">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="Why it wins"
                    title="A better event experience than generic photo sharing apps"
                    description="The difference is not only what guests can upload. It is how easy, modern, and event-ready the whole experience feels."
                    centered
                />

                <div class="mt-14 grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <div class="rounded-[30px] border border-promo-primary/20 bg-white p-6 shadow-[0_18px_60px_rgba(232,79,154,0.12)]">
                        <div class="inline-flex items-center gap-2 rounded-full bg-promo-surface px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                            Our product
                        </div>
                        <div class="mt-6 space-y-4">
                            <div
                                v-for="row in comparisonRows"
                                :key="`ours-${row.label}`"
                                class="rounded-[22px] border border-promo-line bg-promo-surface/60 p-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex size-8 items-center justify-center rounded-full bg-promo-primary text-white">
                                        <ShieldCheck class="size-4" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ row.label }}</p>
                                        <p class="mt-2 text-sm leading-7 text-promo-muted">
                                            {{ row.ours }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[30px] border border-promo-line bg-white p-6">
                        <div class="inline-flex items-center gap-2 rounded-full bg-promo-warm px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-promo-ink">
                            Other apps
                        </div>
                        <div class="mt-6 space-y-4">
                            <div
                                v-for="row in comparisonRows"
                                :key="`other-${row.label}`"
                                class="rounded-[22px] border border-promo-line bg-white p-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex size-8 items-center justify-center rounded-full bg-promo-surface text-promo-muted">
                                        <Users class="size-4" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-promo-ink">{{ row.label }}</p>
                                        <p class="mt-2 text-sm leading-7 text-promo-muted">
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

        <section id="blog" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <MarketingSectionHeading
                eyebrow="Blog"
                title="Fresh ideas for making guest photo sharing work beautifully"
                description="Helpful, practical guidance for hosts, planners, venues, and anyone trying to get more guests to actually contribute."
            />

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                <article
                    v-for="post in blogPosts"
                    :key="post.title"
                    class="rounded-[26px] border border-promo-line bg-white p-6 shadow-[0_16px_45px_rgba(120,86,255,0.08)]"
                >
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                        {{ post.category }}
                    </p>
                    <h3 class="mt-4 text-2xl font-bold tracking-[-0.03em] text-promo-ink">
                        {{ post.title }}
                    </h3>
                    <p class="mt-4 text-sm leading-7 text-promo-muted">
                        {{ post.body }}
                    </p>
                    <a href="#" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-promo-ink">
                        Read article
                        <ArrowRight class="size-4" />
                    </a>
                </article>
            </div>
        </section>

        <section id="faq" class="bg-white">
            <div class="mx-auto max-w-4xl px-4 py-24 sm:px-6 lg:px-8">
                <MarketingSectionHeading
                    eyebrow="FAQ"
                    title="Everything hosts ask before launching their first event"
                    description="Simple answers to the questions that come up most often."
                    centered
                />

                <div class="mt-12 rounded-[28px] border border-promo-line bg-promo-bg px-6 py-2 shadow-[0_14px_40px_rgba(232,79,154,0.08)]">
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

        <section id="cta" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-[36px] border border-promo-line bg-linear-to-br from-promo-surface via-white to-promo-purple p-8 shadow-[0_24px_70px_rgba(232,79,154,0.14)] lg:p-12">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-promo-primary">
                        Start your event
                    </p>
                    <h2 class="mt-4 text-4xl font-extrabold tracking-[-0.05em] text-promo-ink sm:text-5xl">
                        Turn every guest into part of the memory
                    </h2>
                    <p class="mt-5 text-base leading-8 text-promo-muted sm:text-lg">
                        Launch your digital album, share one link or QR code, and let the best candid moments find their way to you automatically.
                    </p>

                    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        <Link
                            :href="register()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-6 py-4 text-sm font-semibold text-white transition hover:bg-promo-primary-strong"
                        >
                            Create Your Event
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
            </div>
        </section>
    </MarketingLayout>
</template>
