<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import { removeGoogleFontStylesheet, syncGoogleFontStylesheet } from '@/lib/google-fonts';
import { readQrPrintDraft } from '@/lib/qr-print-draft';
import { qrTemplateDefinitions, resolveQrTemplateDefinition } from '@/lib/qr-print-templates';

type EventPayload = {
    id: number;
    name: string;
};

type EventLinks = {
    printPack: string;
    albumQrDataUrl: string;
};

const props = defineProps<{
    currentEvent: EventPayload;
    eventLinks: EventLinks;
}>();

const { t } = useTranslations();

const activeTemplateId = ref<string>(qrTemplateDefinitions[0].id);
const subtitleText = ref('SHARE THE');
const titleText = ref('LOVE');
const sloganText = ref('sharing is caring');
const messageText = ref('Scan the QR code and share your memories by uploading photos, videos or wishes to the newly wed.');
const eventTitleText = ref(props.currentEvent.name);

const activeTemplate = computed(() => resolveQrTemplateDefinition(activeTemplateId.value));

watch(
    () => activeTemplate.value.fonts.stylesheetHref,
    (href) => {
        syncGoogleFontStylesheet(href, 'qr-template-preview-google');
    },
    { immediate: true },
);

onMounted(() => {
    const draft = readQrPrintDraft(props.currentEvent.id);

    if (draft?.templateId) {
        activeTemplateId.value = draft.templateId;
    }

    if (draft?.subtitle !== undefined) {
        subtitleText.value = draft.subtitle;
    }

    if (draft?.title !== undefined) {
        titleText.value = draft.title;
    }

    if (draft?.slogan !== undefined) {
        sloganText.value = draft.slogan;
    }

    if (draft?.message !== undefined) {
        messageText.value = draft.message;
    }

    if (draft?.eventTitle !== undefined) {
        eventTitleText.value = draft.eventTitle;
    }

    if (new URLSearchParams(window.location.search).get('print') === '1') {
        window.setTimeout(() => {
            window.print();
        }, 250);
    }
});

onBeforeUnmount(() => {
    removeGoogleFontStylesheet('qr-template-preview-google');
});

const printPreview = (): void => {
    window.print();
};
</script>

<template>
    <div class="min-h-screen overflow-hidden bg-[linear-gradient(180deg,#f7f1e7_0%,#f4ede2_22%,#f2eee8_100%)]">
        <Head :title="`${currentEvent.name} · ${t('event_home.print_pack.preview_title')}`" />

        <div class="fixed right-4 top-4 z-20 flex items-center gap-2 print:hidden">
            <Button as="a" :href="eventLinks.printPack" variant="outline" size="icon-sm" class="rounded-full bg-white/88">
                <SlidersHorizontal class="size-4" />
            </Button>
            <Button type="button" size="icon-sm" class="rounded-full" @click="printPreview">
                <Printer class="size-4" />
            </Button>
        </div>

        <div class="flex min-h-screen items-center justify-center p-4 sm:p-8 print:p-0">
            <component
                :is="activeTemplate.component"
                class="h-[calc(100vh-2rem)] max-h-[1120px] w-auto max-w-full print:h-screen print:max-h-none print:w-full print:rounded-none print:shadow-none"
                :subtitle="subtitleText"
                :title="titleText"
                :slogan="sloganText"
                :message="messageText"
                :event-title="eventTitleText"
                :fonts="activeTemplate.fonts"
                :qr-data-url="eventLinks.albumQrDataUrl"
                :preview-alt="t('event_home.print_pack.preview_alt')"
            />
        </div>
    </div>
</template>
