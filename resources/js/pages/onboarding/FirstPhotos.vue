<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Download, ExternalLink, Images, QrCode, ScanLine } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    eventName: string;
    albumUrl: string;
    wallUrl: string;
    qrCodeDataUrl: string;
    readyUrl: string;
}>();
</script>

<template>
    <Head title="Add your first photos" />

    <div class="min-h-screen bg-brand-canvas px-4 py-10 text-brand-ink md:px-6 md:py-14">
        <div class="mx-auto w-full max-w-5xl">
            <section class="overflow-hidden rounded-[2rem] border border-brand-border/70 bg-brand-panel shadow-sm">
                <div class="border-b border-brand-border/70 px-6 py-7 sm:px-8 sm:py-8">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-3xl space-y-3">
                            <span class="dashboard-chip">Album setup</span>
                            <h1 class="dashboard-title text-3xl sm:text-[2.15rem]">
                                Let&apos;s add your first photos
                            </h1>
                            <p class="text-base leading-7 text-brand-muted">
                                Scan the QR code, open the album once, and you&apos;ll be
                                ready to start collecting uploads for
                                <span class="font-semibold text-brand-ink">{{ eventName }}</span>.
                            </p>
                        </div>

                        <div class="rounded-full border border-brand-border/70 bg-brand-inverse px-4 py-2 text-sm font-medium text-brand-muted">
                            Guests can join in seconds
                        </div>
                    </div>
                </div>

                <div class="grid gap-0 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                    <div class="border-b border-brand-border/70 bg-brand-inverse/75 px-6 py-8 sm:px-8 lg:border-r lg:border-b-0">
                        <div class="mx-auto flex max-w-[22rem] flex-col items-center text-center">
                            <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-brand-border/70 bg-brand-panel px-3 py-1.5 text-sm font-medium text-brand-muted">
                                <QrCode class="size-4 text-brand-accent" />
                                Scan to open the album
                            </div>

                            <img
                                :src="qrCodeDataUrl"
                                alt="Album QR code"
                                class="w-full max-w-[320px]"
                            />

                            <p class="mt-5 text-sm leading-6 text-brand-muted">
                                Point a phone camera at the code, follow the upload
                                steps, and the photos will start appearing on the
                                wall shortly after.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-8 sm:px-8">
                        <div class="space-y-6">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-inverse px-4 py-4">
                                    <ScanLine class="size-5 text-brand-accent" />
                                    <p class="mt-3 text-sm font-semibold text-brand-ink">
                                        Scan
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-brand-muted">
                                        Open the album from the QR code once.
                                    </p>
                                </div>

                                <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-inverse px-4 py-4">
                                    <Images class="size-5 text-brand-accent" />
                                    <p class="mt-3 text-sm font-semibold text-brand-ink">
                                        Upload
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-brand-muted">
                                        Add a few photos so the wall comes to life.
                                    </p>
                                </div>

                                <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-inverse px-4 py-4">
                                    <ExternalLink class="size-5 text-brand-accent" />
                                    <p class="mt-3 text-sm font-semibold text-brand-ink">
                                        Share
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-brand-muted">
                                        Keep the album and wall links close by.
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-[1.5rem] border border-brand-border/70 bg-brand-inverse px-5 py-5">
                                <p class="dashboard-eyebrow">Quick links</p>
                                <p class="mt-2 text-sm leading-6 text-brand-muted">
                                    Everything below opens in a new tab, so you can
                                    quickly test the flow before moving on.
                                </p>

                                <div class="mt-5 flex flex-col gap-3">
                                    <Button as-child class="justify-between rounded-full bg-brand-accent text-white hover:bg-brand-accent/90">
                                        <a
                                            :href="albumUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <ExternalLink class="size-4" />
                                                Open digital album
                                            </span>
                                            <ArrowRight class="size-4" />
                                        </a>
                                    </Button>

                                    <Button as-child variant="outline" class="justify-between rounded-full border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/20">
                                        <a
                                            :href="wallUrl"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <Images class="size-4" />
                                                Open photo wall
                                            </span>
                                            <ArrowRight class="size-4" />
                                        </a>
                                    </Button>

                                    <Button as-child variant="outline" class="justify-between rounded-full border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/20">
                                        <a
                                            :href="qrCodeDataUrl"
                                            download="digital-album-qr.svg"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <Download class="size-4" />
                                                Download QR code
                                            </span>
                                            <ArrowRight class="size-4" />
                                        </a>
                                    </Button>
                                </div>
                            </div>

                            <Button as-child variant="ghost" class="h-auto justify-start px-0 text-sm font-medium text-brand-muted hover:text-brand-ink">
                                <Link :href="readyUrl">I&apos;ll do this later</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
