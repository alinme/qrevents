<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    CircleHelp,
    Instagram,
    Menu,
    Newspaper,
    QrCode,
    Star,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { dashboard, home, login, pricing, register } from '@/routes';

defineProps<{
    title: string;
    description?: string;
    canRegister?: boolean;
}>();

const page = usePage();
const authedUser = page.props.auth?.user ?? null;

const homeUrl = home().url;

const useCaseLinks = [
    { label: 'Weddings', href: `${homeUrl}#use-cases` },
    { label: 'Parties', href: `${homeUrl}#use-cases` },
    { label: 'Corporate events', href: `${homeUrl}#use-cases` },
    { label: 'Conferences', href: `${homeUrl}#use-cases` },
];

const primaryNavItems = [
    { label: 'Pricing', href: pricing().url },
    { label: 'Reviews', href: `${homeUrl}#reviews` },
    { label: 'Blog', href: `${homeUrl}#blog` },
];

const footerProductLinks = [
    { label: 'How it works', href: `${homeUrl}#how-it-works` },
    { label: 'Capabilities', href: `${homeUrl}#capabilities` },
    { label: 'Photo wall', href: `${homeUrl}#steps` },
    { label: 'Pricing', href: pricing().url },
];

const footerUseCaseLinks = [
    { label: 'Weddings', href: `${homeUrl}#use-cases` },
    { label: 'Birthdays', href: `${homeUrl}#use-cases` },
    { label: 'Corporate', href: `${homeUrl}#use-cases` },
    { label: 'Public events', href: `${homeUrl}#use-cases` },
];

const footerSupportLinks = [
    { label: 'Wall of love', href: `${homeUrl}#reviews` },
    { label: 'FAQ', href: `${homeUrl}#faq` },
    { label: 'Blog', href: `${homeUrl}#blog` },
    { label: 'Support', href: `${homeUrl}#cta` },
];
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
    </Head>

    <div class="min-h-screen bg-promo-bg text-promo-ink">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden">
            <div class="mx-auto max-w-[1400px]">
                <div class="relative h-[34rem]">
                    <div class="absolute left-[-8rem] top-[-9rem] h-[24rem] w-[24rem] rounded-full bg-promo-purple/70 blur-3xl" />
                    <div class="absolute right-[-5rem] top-[2rem] h-[28rem] w-[28rem] rounded-full bg-promo-surface-strong/70 blur-3xl" />
                    <div class="absolute left-[34%] top-[6rem] h-[16rem] w-[16rem] rounded-full bg-promo-warm/80 blur-3xl" />
                </div>
            </div>
        </div>

        <header class="sticky top-0 z-40 border-b border-promo-line/80 bg-promo-bg/90 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-primary to-promo-primary-strong text-white shadow-[0_10px_24px_rgba(232,79,154,0.25)]">
                        <QrCode class="size-5" />
                    </div>
                    <div>
                        <div class="text-lg font-extrabold tracking-[-0.04em] text-promo-ink">
                            Kululu-style joy
                        </div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                            QR Events
                        </div>
                    </div>
                </Link>

                <nav class="hidden items-center gap-7 lg:flex">
                    <div class="group relative">
                        <button class="inline-flex items-center gap-2 text-sm font-semibold text-promo-ink/82 transition hover:text-promo-ink">
                            Use Cases
                            <ChevronDown class="size-4 transition group-hover:rotate-180" />
                        </button>

                        <div class="invisible absolute left-0 top-full mt-4 w-64 rounded-[22px] border border-promo-line bg-white p-3 opacity-0 shadow-[0_20px_60px_rgba(120,86,255,0.12)] transition duration-200 group-hover:visible group-hover:opacity-100">
                            <Link
                                v-for="item in useCaseLinks"
                                :key="item.label"
                                :href="item.href"
                                class="block rounded-[16px] px-4 py-3 text-sm font-medium text-promo-ink/82 transition hover:bg-promo-surface hover:text-promo-ink"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <Link
                        v-for="item in primaryNavItems"
                        :key="item.label"
                        :href="item.href"
                        class="text-sm font-semibold text-promo-ink/82 transition hover:text-promo-ink"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <Link
                        v-if="!authedUser"
                        :href="login()"
                        class="inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold text-promo-ink/78 transition hover:bg-white hover:text-promo-ink"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="authedUser ? dashboard() : register()"
                        class="inline-flex items-center gap-2 rounded-full bg-promo-primary px-5 py-2.5 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(232,79,154,0.26)] transition hover:bg-promo-primary-strong"
                    >
                        {{ authedUser ? 'Open dashboard' : 'Get started' }}
                        <ArrowRight class="size-4" />
                    </Link>
                </div>

                <Sheet>
                    <SheetTrigger as-child>
                        <Button
                            variant="outline"
                            size="icon"
                            class="rounded-full border-promo-line bg-white text-promo-ink hover:bg-promo-surface lg:hidden"
                        >
                            <Menu class="size-5" />
                            <span class="sr-only">Open navigation</span>
                        </Button>
                    </SheetTrigger>

                    <SheetContent
                        side="right"
                        class="border-promo-line bg-promo-bg px-0 text-promo-ink"
                    >
                        <SheetHeader class="border-b border-promo-line px-6 pb-5 text-left">
                            <SheetTitle class="text-2xl font-extrabold tracking-[-0.04em] text-promo-ink">
                                Explore QR Events
                            </SheetTitle>
                            <SheetDescription class="text-sm text-promo-muted">
                                Create an event, share a QR code, and turn guest photos into a beautiful album.
                            </SheetDescription>
                        </SheetHeader>

                        <div class="flex flex-col gap-6 px-6 py-6">
                            <div class="grid gap-2">
                                <div class="px-1 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                    Use cases
                                </div>
                                <Link
                                    v-for="item in useCaseLinks"
                                    :key="`mobile-${item.label}`"
                                    :href="item.href"
                                    class="rounded-[18px] border border-promo-line bg-white px-4 py-3 text-sm font-medium text-promo-ink transition hover:bg-promo-surface"
                                >
                                    {{ item.label }}
                                </Link>
                            </div>

                            <div class="grid gap-2">
                                <Link
                                    v-for="item in primaryNavItems"
                                    :key="`primary-${item.label}`"
                                    :href="item.href"
                                    class="rounded-[18px] border border-promo-line bg-white px-4 py-3 text-sm font-medium text-promo-ink transition hover:bg-promo-surface"
                                >
                                    {{ item.label }}
                                </Link>
                            </div>

                            <div class="grid gap-3 border-t border-promo-line pt-6">
                                <Link
                                    v-if="!authedUser"
                                    :href="login()"
                                    class="inline-flex items-center justify-center rounded-full border border-promo-line bg-white px-4 py-3 text-sm font-semibold text-promo-ink"
                                >
                                    Log in
                                </Link>
                                <Link
                                    :href="authedUser ? dashboard() : register()"
                                    class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-4 py-3 text-sm font-semibold text-white"
                                >
                                    {{ authedUser ? 'Open dashboard' : 'Get started' }}
                                    <ArrowRight class="size-4" />
                                </Link>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer class="border-t border-promo-line bg-white">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="grid gap-12 border-b border-promo-line pb-12 md:grid-cols-[1.3fr_1fr_1fr_1fr]">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex size-11 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-primary to-promo-primary-strong text-white">
                                <QrCode class="size-5" />
                            </div>
                            <div>
                                <div class="text-lg font-extrabold tracking-[-0.04em] text-promo-ink">
                                    QR Events
                                </div>
                                <div class="text-sm text-promo-muted">
                                    Photo sharing that feels effortless for guests.
                                </div>
                            </div>
                        </div>

                        <p class="mt-5 max-w-sm text-sm leading-7 text-promo-muted">
                            Create a digital event album, let guests join with a link or QR code, and turn every moment into something worth keeping.
                        </p>

                        <div class="mt-6 flex items-center gap-3">
                            <a
                                href="#"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-promo-purple"
                            >
                                <Instagram class="size-4" />
                            </a>
                            <a
                                href="#faq"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-promo-purple"
                            >
                                <CircleHelp class="size-4" />
                            </a>
                            <a
                                href="#blog"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-promo-purple"
                            >
                                <Newspaper class="size-4" />
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.26em] text-promo-primary">
                            Product
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-promo-muted">
                            <Link
                                v-for="item in footerProductLinks"
                                :key="item.label"
                                :href="item.href"
                                class="block transition hover:text-promo-ink"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.26em] text-promo-primary">
                            Use cases
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-promo-muted">
                            <Link
                                v-for="item in footerUseCaseLinks"
                                :key="item.label"
                                :href="item.href"
                                class="block transition hover:text-promo-ink"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.26em] text-promo-primary">
                            Support
                        </div>
                        <div class="mt-4 space-y-3 text-sm text-promo-muted">
                            <Link
                                v-for="item in footerSupportLinks"
                                :key="item.label"
                                :href="item.href"
                                class="block transition hover:text-promo-ink"
                            >
                                {{ item.label }}
                            </Link>
                            <a href="#" class="block transition hover:text-promo-ink">Privacy policy</a>
                            <a href="#" class="block transition hover:text-promo-ink">Terms</a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 pt-6 text-xs uppercase tracking-[0.2em] text-promo-muted sm:flex-row sm:items-center sm:justify-between">
                    <div>Made for weddings, parties, and live events</div>
                    <div class="inline-flex items-center gap-2">
                        <Star class="size-3.5 fill-current" />
                        Loved by hosts who want zero-friction sharing
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
