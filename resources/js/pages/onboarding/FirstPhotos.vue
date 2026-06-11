<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Download,
    ExternalLink,
    Images,
    KeyRound,
    LayoutDashboard,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps<{
    eventName: string;
    albumUrl: string;
    albumShortUrl?: string | null;
    albumAccessCode: string;
    albumAccessEntryUrl: string;
    albumAccessShortcutUrl: string;
    wallUrl: string;
    wallShortUrl?: string | null;
    qrCodeDataUrl: string;
    readyUrl?: string | null;
    businessMode?: boolean;
    dashboardUrl?: string | null;
}>();

const { t } = useTranslations();

const dashboardHref = computed(
    () => props.dashboardUrl ?? props.readyUrl ?? '/dashboard',
);

const shareActions = computed(() => [
    {
        label: t('onboarding.photos.open_album'),
        href: props.albumUrl,
        icon: ExternalLink,
        external: true,
    },
    {
        label: t('onboarding.photos.open_wall'),
        href: props.wallUrl,
        icon: Images,
        external: true,
    },
    {
        label: t('onboarding.photos.download_qr'),
        href: props.qrCodeDataUrl,
        icon: Download,
        download: 'digital-album-qr.svg',
    },
    {
        label: t('onboarding.photos.open_code_entry'),
        href: props.albumAccessEntryUrl,
        icon: KeyRound,
        external: true,
    },
]);
</script>

<template>
    <Head :title="t('onboarding.photos.head_title')" />

    <div
        class="flex min-h-screen flex-col bg-brand-canvas px-4 py-6 text-brand-ink md:px-6"
    >
        <div
            class="mx-auto flex w-full max-w-4xl flex-1 items-center justify-center"
        >
            <section
                class="w-full overflow-hidden rounded-[1.5rem] bg-white shadow-card"
            >
                <div class="grid lg:grid-cols-[0.85fr_1.15fr]">
                    <!-- QR side -->
                    <div
                        class="flex flex-col items-center justify-center gap-3 border-b border-brand-border bg-brand-panel-strong/40 px-6 py-6 lg:border-r lg:border-b-0"
                    >
                        <img
                            :src="qrCodeDataUrl"
                            alt="Album QR code"
                            class="w-44 rounded-xl bg-white p-2 shadow-card sm:w-52"
                        />
                        <div class="text-center">
                            <p
                                class="text-[0.65rem] font-semibold tracking-[0.22em] text-brand-muted uppercase"
                            >
                                {{ t('onboarding.photos.album_code') }}
                            </p>
                            <p
                                class="mt-1 text-2xl font-semibold tracking-[0.3em] text-brand-ink"
                            >
                                {{ albumAccessCode }}
                            </p>
                            <p
                                class="mx-auto mt-2 max-w-[16rem] text-xs leading-5 text-brand-muted"
                            >
                                {{
                                    t('onboarding.photos.no_qr', {
                                        url: albumAccessShortcutUrl,
                                    })
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Content side -->
                    <div class="flex flex-col gap-5 px-6 py-6 sm:px-8">
                        <div>
                            <span class="dashboard-chip">
                                {{ t('onboarding.photos.badge') }}
                            </span>
                            <h1
                                class="mt-3 text-2xl font-semibold tracking-tight text-brand-ink"
                            >
                                {{ t('onboarding.photos.title') }}
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-brand-muted">
                                {{
                                    t('onboarding.photos.description', {
                                        eventName,
                                    })
                                }}
                            </p>
                        </div>

                        <div>
                            <Link
                                :href="dashboardHref"
                                class="flex w-full items-center justify-between rounded-full bg-brand-accent px-6 py-3.5 text-base font-semibold text-white shadow-[rgba(255,56,92,0.25)_0px_10px_22px] transition hover:bg-brand-accent/90"
                            >
                                <span class="inline-flex items-center gap-2.5">
                                    <LayoutDashboard class="size-5" />
                                    {{ t('onboarding.photos.go_dashboard') }}
                                </span>
                                <ArrowRight class="size-5" />
                            </Link>
                            <p
                                class="mt-2 px-1 text-xs leading-5 text-brand-muted"
                            >
                                {{ t('onboarding.photos.dashboard_hint') }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-[0.65rem] font-semibold tracking-[0.22em] text-brand-muted uppercase"
                            >
                                {{ t('onboarding.photos.share_title') }}
                            </p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <a
                                    v-for="action in shareActions"
                                    :key="action.label"
                                    :href="action.href"
                                    :target="
                                        action.external ? '_blank' : undefined
                                    "
                                    :rel="
                                        action.external
                                            ? 'noopener noreferrer'
                                            : undefined
                                    "
                                    :download="action.download"
                                    class="inline-flex items-center gap-2.5 rounded-full border border-brand-border bg-white px-4 py-2.5 text-sm font-medium text-brand-ink transition hover:bg-brand-panel-strong/60"
                                >
                                    <component
                                        :is="action.icon"
                                        class="size-4 shrink-0 text-brand-accent"
                                    />
                                    <span class="truncate">
                                        {{ action.label }}
                                    </span>
                                </a>
                            </div>
                            <p
                                v-if="albumShortUrl || wallShortUrl"
                                class="mt-3 text-xs leading-5 text-brand-muted"
                            >
                                {{ t('onboarding.photos.short_links') }}
                                <span v-if="albumShortUrl">
                                    {{ albumShortUrl }}
                                </span>
                                <span v-if="albumShortUrl && wallShortUrl">
                                    ·
                                </span>
                                <span v-if="wallShortUrl">
                                    {{ wallShortUrl }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
