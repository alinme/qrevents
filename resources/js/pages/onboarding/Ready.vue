<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CircleCheckBig, QrCode, Sparkles } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    eventName: string;
    dashboardUrl: string;
}>();
</script>

<template>
    <Head title="You're all set" />

    <div class="min-h-screen bg-brand-canvas px-4 py-10 text-brand-ink md:px-6 md:py-14">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-4xl items-center">
            <section class="w-full overflow-hidden rounded-[2rem] border border-brand-border/70 bg-brand-panel shadow-sm">
                <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-6 px-6 py-8 sm:px-8 sm:py-10">
                        <span class="dashboard-chip">Ready to go</span>

                        <div class="space-y-3">
                            <div class="inline-flex size-12 items-center justify-center rounded-full border border-brand-border/70 bg-brand-inverse text-brand-accent">
                                <CircleCheckBig class="size-6" />
                            </div>

                            <h1 class="dashboard-title text-3xl sm:text-[2.15rem]">
                                You&apos;re all set up
                            </h1>

                            <p class="text-base leading-7 text-brand-muted">
                                <span class="font-semibold text-brand-ink">{{ eventName }}</span>
                                is ready. Your dashboard is next, with the album
                                links, QR sharing tools, and slideshow access in one
                                place.
                            </p>
                        </div>

                        <Button as-child class="rounded-full bg-brand-accent px-5 text-white hover:bg-brand-accent/90">
                            <Link :href="dashboardUrl">
                                Go to your dashboard
                                <ArrowRight class="size-4" />
                            </Link>
                        </Button>
                    </div>

                    <div class="border-t border-brand-border/70 bg-brand-inverse/75 px-6 py-8 sm:px-8 lg:border-t-0 lg:border-l">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-panel px-4 py-4">
                                <QrCode class="size-5 text-brand-accent" />
                                <p class="mt-3 text-sm font-semibold text-brand-ink">
                                    QR sharing
                                </p>
                                <p class="mt-1 text-sm leading-6 text-brand-muted">
                                    Send guests straight into the album with the
                                    shareable QR flow.
                                </p>
                            </div>

                            <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-panel px-4 py-4">
                                <Sparkles class="size-5 text-brand-accent" />
                                <p class="mt-3 text-sm font-semibold text-brand-ink">
                                    Live wall
                                </p>
                                <p class="mt-1 text-sm leading-6 text-brand-muted">
                                    Review uploads, open the slideshow, and keep the
                                    wall active from the dashboard.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
