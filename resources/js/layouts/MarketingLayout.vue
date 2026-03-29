<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChevronDown,
    CircleHelp,
    Languages,
    Instagram,
    Menu,
    Newspaper,
    Star,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard, home, login, pricing } from '@/routes';
import { create as onboardingCreate } from '@/routes/onboarding';

defineProps<{
    title: string;
    description?: string;
    canRegister?: boolean;
}>();

const page = usePage();
const authedUser = page.props.auth?.user ?? null;
const { locale, t } = useTranslations();
const appName = computed(() => page.props.name ?? 'EventSmart');

const homeUrl = home().url;

const localeOptions = [
    { code: 'en', label: 'English', nativeLabel: 'English', flag: '🇬🇧' },
    { code: 'ro', label: 'Romanian', nativeLabel: 'Romana', flag: '🇷🇴' },
    { code: 'el', label: 'Greek', nativeLabel: 'Eλληνικα', flag: '🇬🇷' },
] as const;

const useCaseLinks = computed(() => [
    { label: t('marketing.nav.weddings'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.parties'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.corporate_events'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.conferences'), href: `${homeUrl}#use-cases` },
]);

const primaryNavItems = computed(() => [
    { label: t('marketing.nav.pricing'), href: pricing().url },
    { label: t('marketing.nav.reviews'), href: `${homeUrl}#reviews` },
    { label: t('marketing.nav.blog'), href: `${homeUrl}#blog` },
]);

const footerProductLinks = computed(() => [
    { label: t('marketing.footer.how_it_works'), href: `${homeUrl}#how-it-works` },
    { label: t('marketing.footer.capabilities'), href: `${homeUrl}#capabilities` },
    { label: t('marketing.footer.photo_wall'), href: `${homeUrl}#steps` },
    { label: t('marketing.nav.pricing'), href: pricing().url },
]);

const footerUseCaseLinks = computed(() => [
    { label: t('marketing.nav.weddings'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.birthdays'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.corporate'), href: `${homeUrl}#use-cases` },
    { label: t('marketing.nav.public_events'), href: `${homeUrl}#use-cases` },
]);

const footerSupportLinks = computed(() => [
    { label: t('marketing.footer.wall_of_love'), href: `${homeUrl}#reviews` },
    { label: t('marketing.footer.faq'), href: `${homeUrl}#faq` },
    { label: t('marketing.nav.blog'), href: `${homeUrl}#blog` },
    { label: t('marketing.footer.support'), href: `${homeUrl}#cta` },
]);

const selectedLocaleOption = computed(
    () => localeOptions.find((option) => option.code === locale.value) ?? localeOptions[0],
);

const switchMarketingLocale = (nextLocale: string): void => {
    if (typeof document === 'undefined' || typeof window === 'undefined') {
        return;
    }

    const maxAge = 60 * 60 * 24 * 365;
    document.cookie = `site_locale=${nextLocale}; path=/; max-age=${maxAge}; SameSite=Lax`;
    window.location.reload();
};
</script>

<template>
    <Head :title="title">
        <meta v-if="description" name="description" :content="description" />
    </Head>

    <div class="min-h-screen bg-promo-bg text-promo-ink">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden">
            <div class="mx-auto max-w-[1400px]">
                <div class="relative h-[34rem]">
                    <div class="absolute left-[-8rem] top-[-9rem] h-[21rem] w-[21rem] rounded-full bg-promo-purple/40 blur-3xl" />
                    <div class="absolute right-[-5rem] top-[2rem] h-[24rem] w-[24rem] rounded-full bg-promo-surface-strong/45 blur-3xl" />
                    <div class="absolute left-[34%] top-[6rem] h-[13rem] w-[13rem] rounded-full bg-promo-warm/45 blur-3xl" />
                </div>
            </div>
        </div>

        <header class="sticky top-0 z-40 border-b border-promo-line/60 bg-promo-bg/92 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <Link :href="home()" class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-white shadow-[0_8px_18px_rgba(23,20,17,0.08)] ring-1 ring-promo-line/70">
                        <AppLogoIcon class="size-7" />
                    </div>
                    <div>
                        <div class="text-base font-extrabold tracking-[-0.03em] text-promo-ink sm:text-lg">
                            {{ t('marketing.brand.title', { appName }) }}
                        </div>
                        <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-promo-muted">
                            {{ t('marketing.brand.subtitle') }}
                        </div>
                    </div>
                </Link>

                <nav class="hidden items-center gap-6 lg:flex">
                    <div class="group relative">
                        <button class="inline-flex items-center gap-2 text-sm font-medium text-promo-ink/82 transition hover:text-promo-ink">
                            {{ t('marketing.nav.use_cases') }}
                            <ChevronDown class="size-4 transition group-hover:rotate-180" />
                        </button>

                        <div class="invisible absolute left-0 top-full mt-4 w-64 rounded-[20px] border border-promo-line/80 bg-white p-3 opacity-0 shadow-[0_14px_32px_rgba(120,86,255,0.08)] transition duration-200 group-hover:visible group-hover:opacity-100">
                            <Link
                                v-for="item in useCaseLinks"
                                :key="item.label"
                                :href="item.href"
                                class="block rounded-[16px] px-4 py-3 text-sm text-promo-ink/82 transition hover:bg-promo-surface hover:text-promo-ink"
                            >
                                {{ item.label }}
                            </Link>
                        </div>
                    </div>

                    <Link
                        v-for="item in primaryNavItems"
                        :key="item.label"
                        :href="item.href"
                        class="text-sm font-medium text-promo-ink/82 transition hover:text-promo-ink"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <button
                                type="button"
                                class="inline-flex items-center gap-3 rounded-full border border-promo-line bg-white px-3 py-2 text-sm font-medium text-promo-ink transition hover:bg-promo-surface"
                            >
                                <span class="text-base leading-none">{{ selectedLocaleOption.flag }}</span>
                                <span class="hidden min-w-0 text-left xl:block">
                                    {{ selectedLocaleOption.nativeLabel }}
                                </span>
                                <Languages class="size-4 text-promo-primary" />
                            </button>
                        </DropdownMenuTrigger>

                        <DropdownMenuContent
                            align="end"
                            class="w-64 rounded-[22px] border-promo-line/80 bg-white p-2 shadow-[0_14px_32px_rgba(120,86,255,0.08)]"
                        >
                            <div class="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                {{ t('marketing.language.label') }}
                            </div>
                            <DropdownMenuItem
                                v-for="option in localeOptions"
                                :key="option.code"
                                class="rounded-[18px] px-3 py-3 focus:bg-promo-surface"
                                @click="switchMarketingLocale(option.code)"
                            >
                                <div class="flex w-full items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg leading-none">{{ option.flag }}</span>
                                        <div>
                                            <div class="text-sm font-semibold text-promo-ink">
                                                {{ option.nativeLabel }}
                                            </div>
                                            <div class="text-xs text-promo-muted">
                                                {{ option.label }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="size-2.5 rounded-full"
                                        :class="locale === option.code ? 'bg-promo-primary' : 'bg-promo-line'"
                                    />
                                </div>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Link
                        v-if="!authedUser"
                        :href="login()"
                        class="inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-medium text-promo-ink/78 transition hover:bg-white hover:text-promo-ink"
                    >
                        {{ t('marketing.actions.log_in') }}
                    </Link>
                    <Link
                        :href="
                            authedUser
                                ? dashboard()
                                : onboardingCreate({ query: { plan: 'free' } })
                        "
                        class="inline-flex items-center gap-2 rounded-full bg-promo-primary px-5 py-2.5 text-sm font-semibold text-white shadow-[0_14px_30px_rgba(232,79,154,0.26)] transition hover:bg-promo-primary-strong"
                    >
                        {{ authedUser ? t('marketing.actions.open_dashboard') : t('marketing.actions.get_started') }}
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
                            <span class="sr-only">{{ t('marketing.actions.open_navigation') }}</span>
                        </Button>
                    </SheetTrigger>

                    <SheetContent
                        side="right"
                        class="border-promo-line bg-promo-bg px-0 text-promo-ink"
                    >
                        <SheetHeader class="border-b border-promo-line px-6 pb-5 text-left">
                            <SheetTitle class="text-lg font-extrabold tracking-[-0.02em] text-promo-ink">
                                {{ t('marketing.mobile.title') }}
                            </SheetTitle>
                            <SheetDescription class="text-sm text-promo-muted">
                                {{ t('marketing.mobile.description') }}
                            </SheetDescription>
                        </SheetHeader>

                        <div class="flex flex-col gap-6 px-6 py-6">
                            <div class="rounded-[20px] border border-promo-line/80 bg-white p-4">
                                <div class="mb-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                    {{ t('marketing.language.label') }}
                                </div>
                                <div class="grid gap-2">
                                    <button
                                        v-for="option in localeOptions"
                                        :key="`mobile-locale-${option.code}`"
                                        type="button"
                                        class="flex items-center justify-between rounded-[18px] border px-4 py-3 text-left transition"
                                        :class="
                                            locale === option.code
                                                ? 'border-promo-primary/30 bg-promo-surface'
                                                : 'border-promo-line bg-white hover:bg-promo-surface'
                                        "
                                        @click="switchMarketingLocale(option.code)"
                                    >
                                        <div class="flex items-center gap-3">
                                            <span class="text-lg leading-none">{{ option.flag }}</span>
                                            <div>
                                                <div class="text-sm font-semibold text-promo-ink">
                                                    {{ option.nativeLabel }}
                                                </div>
                                                <div class="text-xs text-promo-muted">
                                                    {{ option.label }}
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="size-2.5 rounded-full"
                                            :class="locale === option.code ? 'bg-promo-primary' : 'bg-promo-line'"
                                        />
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <div class="px-1 text-xs font-semibold uppercase tracking-[0.24em] text-promo-primary">
                                    {{ t('marketing.nav.use_cases') }}
                                </div>
                                <Link
                                    v-for="item in useCaseLinks"
                                    :key="`mobile-${item.label}`"
                                    :href="item.href"
                                    class="rounded-[16px] border border-promo-line/80 bg-white px-4 py-3 text-sm text-promo-ink transition hover:bg-promo-surface"
                                >
                                    {{ item.label }}
                                </Link>
                            </div>

                            <div class="grid gap-2">
                                <Link
                                    v-for="item in primaryNavItems"
                                    :key="`primary-${item.label}`"
                                    :href="item.href"
                                    class="rounded-[16px] border border-promo-line/80 bg-white px-4 py-3 text-sm text-promo-ink transition hover:bg-promo-surface"
                                >
                                    {{ item.label }}
                                </Link>
                            </div>

                            <div class="grid gap-3 border-t border-promo-line pt-6">
                                <Link
                                    v-if="!authedUser"
                                    :href="login()"
                                    class="inline-flex items-center justify-center rounded-full border border-promo-line bg-white px-4 py-3 text-sm font-medium text-promo-ink"
                                >
                                    {{ t('marketing.actions.log_in') }}
                                </Link>
                                <Link
                                    :href="
                                        authedUser
                                            ? dashboard()
                                            : onboardingCreate({
                                                  query: { plan: 'free' },
                                              })
                                    "
                                    class="inline-flex items-center justify-center gap-2 rounded-full bg-promo-primary px-4 py-3 text-sm font-semibold text-white"
                                >
                                    {{ authedUser ? t('marketing.actions.open_dashboard') : t('marketing.actions.get_started') }}
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
                            <div class="flex size-11 items-center justify-center rounded-[18px] bg-white ring-1 ring-promo-line/70">
                                <AppLogoIcon class="size-8" />
                            </div>
                            <div>
                                <div class="text-base font-extrabold tracking-[-0.03em] text-promo-ink sm:text-lg">
                                    {{ t('marketing.brand.title', { appName }) }}
                                </div>
                                <div class="text-sm text-promo-muted">
                                    {{ t('marketing.footer.tagline') }}
                                </div>
                            </div>
                        </div>

                        <p class="mt-5 max-w-sm text-sm leading-6 text-promo-muted">
                            {{ t('marketing.footer.description') }}
                        </p>

                        <div class="mt-6 flex items-center gap-3">
                            <a
                                href="#"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-white"
                            >
                                <Instagram class="size-4" />
                            </a>
                            <a
                                href="#faq"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-white"
                            >
                                <CircleHelp class="size-4" />
                            </a>
                            <a
                                href="#blog"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-promo-line bg-promo-surface text-promo-ink transition hover:bg-white"
                            >
                                <Newspaper class="size-4" />
                            </a>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.26em] text-promo-primary">
                            {{ t('marketing.footer.product') }}
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
                            {{ t('marketing.nav.use_cases') }}
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
                            {{ t('marketing.footer.support_title') }}
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
                            <a href="#" class="block transition hover:text-promo-ink">{{ t('marketing.footer.privacy') }}</a>
                            <a href="#" class="block transition hover:text-promo-ink">{{ t('marketing.footer.terms') }}</a>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-4 pt-6 text-xs uppercase tracking-[0.2em] text-promo-muted sm:flex-row sm:items-center sm:justify-between">
                    <div>{{ t('marketing.footer.made_for') }}</div>
                    <div class="inline-flex items-center gap-2">
                        <Star class="size-3.5 fill-current" />
                        {{ t('marketing.footer.loved_by') }}
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
