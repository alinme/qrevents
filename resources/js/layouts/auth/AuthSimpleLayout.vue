<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Camera, MonitorPlay, QrCode, Sparkles } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        contentWidth?: 'default' | 'wide';
        headingEyebrow?: string;
    }>(),
    {
        contentWidth: 'default',
        headingEyebrow: 'Account access',
    },
);
</script>

<template>
    <div class="min-h-svh bg-promo-bg text-promo-ink">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 -z-10 overflow-hidden"
        >
            <div class="mx-auto max-w-7xl">
                <div class="relative h-[30rem]">
                    <div
                        class="absolute top-[-8rem] left-[-7rem] h-[21rem] w-[21rem] rounded-full bg-promo-purple/70 blur-3xl"
                    />
                    <div
                        class="absolute top-[3rem] right-[-5rem] h-[24rem] w-[24rem] rounded-full bg-promo-surface-strong/80 blur-3xl"
                    />
                </div>
            </div>
        </div>

        <div
            class="mx-auto flex min-h-svh w-full max-w-6xl flex-col gap-8 px-6 py-6 lg:gap-10 lg:px-8 lg:py-8"
        >
            <Link
                :href="home()"
                class="inline-flex items-center gap-3 self-start"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-[18px] bg-linear-to-br from-promo-primary to-promo-primary-strong text-white shadow-[0_12px_28px_rgba(255,56,92,0.22)]"
                >
                    <AppLogoIcon class="size-8 fill-current" />
                </div>
                <div>
                    <div
                        class="text-[0.68rem] font-semibold tracking-[0.24em] text-promo-primary uppercase"
                    >
                        EventSmart
                    </div>
                    <div
                        class="text-xl font-extrabold tracking-[-0.04em] text-promo-ink"
                    >
                        Friendly event sharing
                    </div>
                </div>
            </Link>

            <div
                class="grid flex-1 gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,26rem)] lg:items-start"
            >
                <section class="order-2 max-w-lg space-y-5 lg:order-1 lg:pt-6">
                    <slot name="aside">
                        <div class="space-y-3">
                            <p
                                class="inline-flex items-center gap-2 rounded-full bg-white px-3.5 py-1.5 text-[0.65rem] font-semibold tracking-[0.22em] text-promo-primary uppercase shadow-[0_10px_24px_rgba(0,0,0,0.04)]"
                            >
                                <Sparkles class="size-3.5" />
                                Event access
                            </p>
                            <h2
                                class="max-w-md text-3xl leading-tight font-extrabold tracking-[-0.05em] text-promo-ink md:text-[2.5rem]"
                            >
                                Run your event album from one calm place.
                            </h2>
                            <p
                                class="max-w-md text-sm leading-7 text-promo-muted md:text-[0.95rem]"
                            >
                                Sign in to manage albums, share your QR code,
                                review uploads, and collect every memory without
                                extra apps or messy setup.
                            </p>
                        </div>

                        <div class="relative hidden max-w-md pb-8 lg:block">
                            <div
                                class="overflow-hidden rounded-[1.6rem] shadow-[rgba(0,0,0,0.04)_0px_6px_16px,rgba(0,0,0,0.12)_0px_20px_40px]"
                            >
                                <img
                                    src="/images/album/jeremy-bg-md.jpg"
                                    alt="Guests sharing event photos in a digital album"
                                    class="aspect-[16/11] w-full object-cover"
                                    loading="lazy"
                                />
                            </div>

                            <div
                                class="absolute -bottom-2 -left-4 inline-flex items-center gap-3 rounded-[1.1rem] bg-white px-4 py-3 shadow-card"
                            >
                                <span
                                    class="flex size-9 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                                >
                                    <QrCode class="size-4.5" />
                                </span>
                                <span>
                                    <span
                                        class="block text-sm font-semibold text-promo-ink"
                                    >
                                        Guests join instantly
                                    </span>
                                    <span
                                        class="block text-xs text-promo-muted"
                                    >
                                        Scan and upload in seconds
                                    </span>
                                </span>
                            </div>

                            <div
                                class="absolute -top-3 -right-3 inline-flex items-center gap-3 rounded-[1.1rem] bg-white px-4 py-3 shadow-card"
                            >
                                <span
                                    class="flex size-9 items-center justify-center rounded-full bg-promo-primary/12 text-promo-primary"
                                >
                                    <MonitorPlay class="size-4.5" />
                                </span>
                                <span
                                    class="block text-sm font-semibold text-promo-ink"
                                >
                                    Live slideshow ready
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-promo-muted"
                        >
                            <div class="inline-flex items-center gap-2">
                                <MonitorPlay
                                    class="size-4 text-promo-primary"
                                />
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

                <section
                    class="order-1 flex items-start justify-center lg:order-2 lg:justify-end"
                >
                    <div
                        class="w-full"
                        :class="
                            props.contentWidth === 'wide'
                                ? 'max-w-3xl'
                                : 'max-w-md'
                        "
                    >
                        <div class="mb-5 space-y-2 text-center lg:text-left">
                            <p
                                class="text-xs font-semibold tracking-[0.22em] text-promo-primary uppercase"
                            >
                                {{ props.headingEyebrow }}
                            </p>
                            <h1
                                class="text-3xl font-extrabold tracking-[-0.05em] text-promo-ink md:text-[2.25rem]"
                            >
                                {{ title }}
                            </h1>
                            <p class="text-sm leading-7 text-promo-muted">
                                {{ description }}
                            </p>
                        </div>

                        <div
                            class="rounded-[28px] border border-promo-line bg-white p-6 shadow-card lg:p-7"
                        >
                            <slot />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
