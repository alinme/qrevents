<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Download, ExternalLink, Images } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    eventName: string;
    albumUrl: string;
    wallUrl: string;
    qrCodeDataUrl: string;
    readyUrl?: string | null;
    businessMode?: boolean;
    dashboardUrl?: string | null;
}>();
</script>

<template>
    <Head title="Add your first photos" />

    <div class="min-h-screen bg-brand-canvas px-4 py-10 text-brand-ink md:px-6 md:py-14">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-2xl items-center justify-center">
            <div class="w-full text-center">
                <span class="dashboard-chip">Album setup</span>

                <h1 class="mt-5 text-3xl font-semibold tracking-tight text-brand-ink sm:text-[2.3rem]">
                    Let&apos;s add your first photos
                </h1>

                <p class="mx-auto mt-3 max-w-xl text-base leading-7 text-brand-muted">
                    Scan the QR code for
                    <span class="font-semibold text-brand-ink">{{ eventName }}</span>
                    and start uploading.
                </p>

                <div class="mt-8 flex justify-center">
                    <img
                        :src="qrCodeDataUrl"
                        alt="Album QR code"
                        class="w-full max-w-[320px]"
                    />
                </div>

                <div class="mx-auto mt-8 flex max-w-md flex-col gap-3">
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

                    <Button
                        v-if="dashboardUrl"
                        as-child
                        variant="ghost"
                        class="rounded-full text-brand-muted hover:text-brand-ink"
                    >
                        <Link :href="dashboardUrl">
                            {{ businessMode ? 'Go to business dashboard' : 'Go to dashboard' }}
                        </Link>
                    </Button>

                    <Button
                        v-else-if="readyUrl"
                        as-child
                        variant="ghost"
                        class="rounded-full text-brand-muted hover:text-brand-ink"
                    >
                        <Link :href="readyUrl">Go to dashboard</Link>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
