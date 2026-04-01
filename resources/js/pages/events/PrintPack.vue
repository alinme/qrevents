<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import QrPrintPackBuilder from '@/components/events/QrPrintPackBuilder.vue';
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
    albumAccessCode: string;
    albumQrDataUrl: string;
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

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${currentEvent.name} · ${t('event_home.print_pack.title')}`" />

        <div class="min-h-screen bg-[linear-gradient(180deg,#f7f1e7_0%,#f4ede2_22%,#f2eee8_100%)]">
            <div class="mx-auto max-w-[1500px] px-4 py-5 sm:px-6 lg:px-8">
                <div class="max-w-3xl py-2">
                    <h1 class="text-3xl font-semibold tracking-tight text-[#171411]">
                        {{ t('event_home.print_pack.title') }}
                    </h1>
                    <p class="mt-3 text-sm leading-6 text-zinc-600 sm:text-base">
                        {{ t('event_home.print_pack.page_description') }}
                    </p>
                </div>

                <div class="mt-6">
                    <QrPrintPackBuilder
                        :event-name="currentEvent.name"
                        :album-url="eventLinks.album"
                        :album-access-code="eventLinks.albumAccessCode"
                        :album-qr-data-url="eventLinks.albumQrDataUrl"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
