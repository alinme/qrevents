<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { LoaderCircle, QrCode, Sparkles } from 'lucide-vue-next';
import { onMounted } from 'vue';

const props = defineProps<{
    eventName: string;
    nextUrl: string;
}>();

onMounted(() => {
    window.setTimeout(() => {
        router.visit(props.nextUrl);
    }, 1800);
});
</script>

<template>
    <Head title="Creating your album" />

    <div class="min-h-screen bg-brand-canvas px-4 py-10 text-brand-ink md:px-6 md:py-14">
        <div class="mx-auto flex min-h-[calc(100vh-5rem)] w-full max-w-4xl items-center">
            <section class="w-full overflow-hidden rounded-[2rem] border border-brand-border/70 bg-brand-panel shadow-sm">
                <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="space-y-6 px-6 py-8 sm:px-8 sm:py-10">
                        <span class="dashboard-chip">Album setup</span>

                        <div class="space-y-3">
                            <h1 class="dashboard-title text-3xl sm:text-[2.15rem]">
                                Creating your album
                            </h1>
                            <p class="max-w-2xl text-base leading-7 text-brand-muted">
                                We&apos;re preparing
                                <span class="font-semibold text-brand-ink">{{ eventName }}</span>,
                                generating the sharing links, and getting the wall
                                ready for the first photos.
                            </p>
                        </div>

                        <div class="flex items-center gap-3 rounded-full border border-brand-border/70 bg-brand-inverse px-4 py-3 text-sm text-brand-muted sm:w-fit">
                            <LoaderCircle class="size-4 animate-spin text-brand-accent" />
                            This usually takes just a moment.
                        </div>
                    </div>

                    <div class="border-t border-brand-border/70 bg-brand-inverse/75 px-6 py-8 sm:px-8 lg:border-t-0 lg:border-l">
                        <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                            <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-panel px-4 py-4">
                                <QrCode class="size-5 text-brand-accent" />
                                <p class="mt-3 text-sm font-semibold text-brand-ink">
                                    QR sharing
                                </p>
                                <p class="mt-1 text-sm leading-6 text-brand-muted">
                                    A scan-ready code is being generated for guests.
                                </p>
                            </div>

                            <div class="rounded-[1.4rem] border border-brand-border/70 bg-brand-panel px-4 py-4">
                                <Sparkles class="size-5 text-brand-accent" />
                                <p class="mt-3 text-sm font-semibold text-brand-ink">
                                    Wall preview
                                </p>
                                <p class="mt-1 text-sm leading-6 text-brand-muted">
                                    The public wall and album links are being connected.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
