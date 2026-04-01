<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, QrCode } from 'lucide-vue-next';
import { computed } from 'vue';
import QrPrintPackBuilder from '@/components/events/QrPrintPackBuilder.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    name: string;
};

type EventLinks = {
    dashboard: string;
    printPack: string;
    album: string;
    wall: string;
    invitation: string;
    albumAccessCode: string;
    albumQrDataUrl: string;
    wallQrDataUrl: string;
    invitationQrDataUrl: string;
};

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
}>();

const { t } = useTranslations();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: props.currentEvent.name,
        href: props.eventLinks.dashboard,
    },
    {
        title: t('event_home.print_pack.title'),
        href: props.eventLinks.printPack,
    },
]);

const printPackTargets = computed(() => [
    {
        key: 'album' as const,
        url: props.eventLinks.album,
        qrDataUrl: props.eventLinks.albumQrDataUrl,
    },
    {
        key: 'wall' as const,
        url: props.eventLinks.wall,
        qrDataUrl: props.eventLinks.wallQrDataUrl,
    },
    {
        key: 'invitation' as const,
        url: props.eventLinks.invitation,
        qrDataUrl: props.eventLinks.invitationQrDataUrl,
    },
]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${currentEvent.name} · ${t('event_home.print_pack.title')}`" />

        <div class="min-h-screen bg-[linear-gradient(180deg,#f7f1e7_0%,#f4ede2_22%,#f2eee8_100%)]">
            <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-5 py-2 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-[0.72rem] font-semibold uppercase tracking-[0.26em] text-zinc-500">
                            {{ t('event_home.print_pack.page_kicker') }}
                        </p>
                        <div class="mt-3 flex items-start gap-3">
                            <div class="rounded-[1rem] bg-[#171411] p-3 text-white">
                                <QrCode class="size-5" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-semibold tracking-tight text-[#171411]">
                                    {{ t('event_home.print_pack.title') }}
                                </h1>
                                <p class="mt-3 text-sm leading-6 text-zinc-600 sm:text-base">
                                    {{ t('event_home.print_pack.page_description') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Button as-child variant="outline" class="rounded-full bg-white/80 px-5">
                        <Link :href="eventLinks.dashboard">
                            <ArrowLeft class="mr-2 size-4" />
                            {{ t('event_home.print_pack.back_to_workspace') }}
                        </Link>
                    </Button>
                </div>

                <div class="mt-6">
                    <QrPrintPackBuilder
                        :event-name="currentEvent.name"
                        :album-access-code="eventLinks.albumAccessCode"
                        :targets="printPackTargets"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
