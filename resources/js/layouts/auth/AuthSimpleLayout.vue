<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Camera, MonitorPlay, QrCode, Sparkles } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const props = withDefaults(defineProps<{
    title?: string;
    description?: string;
    contentWidth?: 'default' | 'wide';
    headingEyebrow?: string;
}>(), {
    contentWidth: 'default',
    headingEyebrow: 'Account access',
});

const previewTiles = [
    {
        title: 'Guests join instantly',
        body: 'Share one link or QR code so anyone can upload in seconds.',
        icon: QrCode,
    },
    {
        title: 'Moments stay organized',
        body: 'Albums, approvals, walls, and downloads live in one simple dashboard.',
        icon: Camera,
    },
];
</script>

<template>
    <div class="min-h-svh bg-promo-bg text-promo-ink">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden">
            <div class="mx-auto max-w-7xl">
                <div class="relative h-[30rem]">
                    <div class="absolute left-[-7rem] top-[-8rem] h-[21rem] w-[21rem] rounded-full bg-promo-purple/70 blur-3xl" />
                    <div class="absolute right-[-5rem] top-[3rem] h-[24rem] w-[24rem] rounded-full bg-promo-surface-strong/80 blur-3xl" />
                </div>
            </div>
        </div>

        <div class="mx-auto flex min-h-svh w-full max-w-6xl flex-col gap-8 px-6 py-6 lg:gap-10 lg:px-8 lg:py-8">
            <Link :href="home()" class="inline-flex items-center gap-3 self-start">
                <div class="flex size-12 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-primary to-promo-primary-strong text-white shadow-[0_12px_28px_rgba(232,79,154,0.22)]">
                    <AppLogoIcon class="size-8 fill-current" />
                </div>
                <div>
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-promo-primary">
                        EventSmart
                    </div>
                    <div class="text-xl font-extrabold tracking-[-0.04em] text-promo-ink">
                        Friendly event sharing
                    </div>
                </div>
            </Link>

            <div class="grid flex-1 gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,26rem)] lg:items-start">
                <section class="order-2 max-w-lg space-y-5 lg:order-1 lg:pt-6">
                    <slot name="aside">
                        <div class="space-y-3">
                            <p class="inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-promo-primary shadow-[0_10px_24px_rgba(232,79,154,0.08)]">
                                <Sparkles class="size-3.5" />
                                Event access
                            </p>
                            <h2 class="max-w-md text-3xl font-extrabold leading-tight tracking-[-0.05em] text-promo-ink md:text-[2.5rem]">
                                Run your event album from one calm place.
                            </h2>
                            <p class="max-w-md text-sm leading-7 text-promo-muted md:text-[0.95rem]">
                                Sign in to manage albums, share your QR code, review uploads, and collect every memory without extra apps or messy setup.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <article
                                v-for="tile in previewTiles"
                                :key="tile.title"
                                class="rounded-[22px] border border-promo-line bg-white p-4 shadow-[0_14px_30px_rgba(120,86,255,0.08)]"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex size-10 items-center justify-center rounded-[16px] bg-promo-surface text-promo-primary">
                                        <component :is="tile.icon" class="size-4.5" />
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-promo-ink">
                                            {{ tile.title }}
                                        </h3>
                                        <p class="mt-1.5 text-sm leading-6 text-promo-muted">
                                            {{ tile.body }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-promo-muted">
                            <div class="inline-flex items-center gap-2">
                                <MonitorPlay class="size-4 text-promo-primary" />
                                Live walls
                            </div>
                            <div class="inline-flex items-center gap-2">
                                <QrCode class="size-4 text-promo-primary" />
                                QR sharing
                            </div>
                            <div class="inline-flex items-center gap-2">
                                <Camera class="size-4 text-promo-primary" />
                                Easy downloads
                            </div>
                        </div>
                    </slot>
                </section>

                <section class="order-1 flex items-start justify-center lg:order-2 lg:justify-end">
                    <div class="w-full" :class="props.contentWidth === 'wide' ? 'max-w-3xl' : 'max-w-md'">
                        <div class="mb-5 space-y-2 text-center lg:text-left">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-promo-primary">
                                {{ props.headingEyebrow }}
                            </p>
                            <h1 class="text-3xl font-extrabold tracking-[-0.05em] text-promo-ink md:text-[2.25rem]">
                                {{ title }}
                            </h1>
                            <p class="text-sm leading-7 text-promo-muted">
                                {{ description }}
                            </p>
                        </div>

                        <div class="rounded-[28px] border border-promo-line bg-white p-6 shadow-[0_24px_60px_rgba(232,79,154,0.12)] lg:p-7">
                            <slot />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
