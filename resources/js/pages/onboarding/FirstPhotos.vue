<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Download, ExternalLink, Images } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

defineProps<{
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
</script>

<template>
    <Head :title="t('onboarding.photos.head_title')" />

    <div
        class="min-h-screen bg-brand-canvas px-4 py-10 text-brand-ink md:px-6 md:py-14"
    >
        <div
            class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-2xl items-center justify-center"
        >
            <div class="w-full text-center">
                <span class="dashboard-chip">{{
                    t('onboarding.photos.badge')
                }}</span>

                <h1
                    class="mt-5 text-3xl font-semibold tracking-tight text-brand-ink sm:text-[2.3rem]"
                >
                    {{ t('onboarding.photos.title') }}
                </h1>

                <p
                    class="mx-auto mt-3 max-w-xl text-base leading-7 text-brand-muted"
                >
                    {{ t('onboarding.photos.description', { eventName }) }}
                </p>

                <div class="mt-8 flex justify-center">
                    <img
                        :src="qrCodeDataUrl"
                        alt="Album QR code"
                        class="w-full max-w-[320px]"
                    />
                </div>

                <div class="mt-5 space-y-2 text-center">
                    <p
                        class="text-[0.72rem] font-semibold tracking-[0.22em] text-brand-muted uppercase"
                    >
                        {{ t('onboarding.photos.album_code') }}
                    </p>
                    <p
                        class="text-3xl font-semibold tracking-[0.32em] text-brand-ink"
                    >
                        {{ albumAccessCode }}
                    </p>
                    <p
                        class="mx-auto max-w-md text-sm leading-6 text-brand-muted"
                    >
                        {{
                            t('onboarding.photos.no_qr', {
                                url: albumAccessShortcutUrl,
                            })
                        }}
                    </p>
                    <p
                        v-if="albumShortUrl || wallShortUrl"
                        class="mx-auto max-w-md text-sm leading-6 text-brand-muted"
                    >
                        {{ t('onboarding.photos.short_links') }}
                        <span v-if="albumShortUrl">{{ albumShortUrl }}</span>
                        <span v-if="albumShortUrl && wallShortUrl"> · </span>
                        <span v-if="wallShortUrl">{{ wallShortUrl }}</span>
                    </p>
                </div>

                <div class="mx-auto mt-8 flex max-w-md flex-col gap-3">
                    <Button
                        as-child
                        class="justify-between rounded-full bg-brand-accent text-white hover:bg-brand-accent/90"
                    >
                        <a
                            :href="albumUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span class="inline-flex items-center gap-2">
                                <ExternalLink class="size-4" />
                                {{ t('onboarding.photos.open_album') }}
                            </span>
                            <ArrowRight class="size-4" />
                        </a>
                    </Button>

                    <Button
                        as-child
                        variant="outline"
                        class="justify-between rounded-full border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/20"
                    >
                        <a
                            :href="wallUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span class="inline-flex items-center gap-2">
                                <Images class="size-4" />
                                {{ t('onboarding.photos.open_wall') }}
                            </span>
                            <ArrowRight class="size-4" />
                        </a>
                    </Button>

                    <Button
                        as-child
                        variant="outline"
                        class="justify-between rounded-full border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/20"
                    >
                        <a
                            :href="qrCodeDataUrl"
                            download="digital-album-qr.svg"
                        >
                            <span class="inline-flex items-center gap-2">
                                <Download class="size-4" />
                                {{ t('onboarding.photos.download_qr') }}
                            </span>
                            <ArrowRight class="size-4" />
                        </a>
                    </Button>

                    <Button
                        as-child
                        variant="outline"
                        class="justify-between rounded-full border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/20"
                    >
                        <a
                            :href="albumAccessEntryUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span>{{
                                t('onboarding.photos.open_code_entry')
                            }}</span>
                            <ArrowRight class="size-4" />
                        </a>
                    </Button>

                    <Button
                        v-if="dashboardUrl"
                        as-child
                        variant="ghost"
                        class="rounded-full text-brand-muted hover:text-brand-ink"
                    >
                        <Link :href="dashboardUrl">
                            {{ t('onboarding.photos.go_dashboard') }}
                        </Link>
                    </Button>

                    <Button
                        v-else-if="readyUrl"
                        as-child
                        variant="ghost"
                        class="rounded-full text-brand-muted hover:text-brand-ink"
                    >
                        <Link :href="readyUrl">{{
                            t('onboarding.photos.go_dashboard')
                        }}</Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
