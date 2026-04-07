<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import QrPrintPackBuilder from '@/components/events/QrPrintPackBuilder.vue';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type EventPayload = {
    id: number;
    name: string;
};

type EventLinks = {
    dashboard: string;
    printPack: string;
    printPackPreview: string;
    album: string;
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
            <div class="mx-auto max-w-[1560px] px-4 py-4 sm:px-6 lg:px-8">
                <QrPrintPackBuilder
                    :event-id="currentEvent.id"
                    :event-name="currentEvent.name"
                    :album-url="eventLinks.album"
                    :preview-url="eventLinks.printPackPreview"
                />
            </div>
        </div>
    </AppLayout>
</template>
