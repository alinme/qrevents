<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import {
    removeGoogleFontStylesheet,
    syncGoogleFontStylesheet,
} from '@/lib/google-fonts';
import { readQrPrintDraft } from '@/lib/qr-print-draft';
import {
    qrTemplateDefinitions,
    resolveQrTemplateDefinition,
} from '@/lib/qr-print-templates';

type EventPayload = {
    id: number;
    name: string;
    type?: string | null;
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

const defaultMessageKey = (): string => {
    switch (props.currentEvent.type) {
        case 'wedding':
        case 'engagement':
            return 'event_home.print_pack.defaults.message_wedding';
        case 'baptism':
            return 'event_home.print_pack.defaults.message_baptism';
        case 'birthday':
            return 'event_home.print_pack.defaults.message_birthday';
        default:
            return 'event_home.print_pack.defaults.message_generic';
    }
};

const subtitleText = ref(t('event_home.print_pack.defaults.subtitle'));
const titleText = ref(t('event_home.print_pack.defaults.title'));
const sloganText = ref(t('event_home.print_pack.defaults.slogan'));
const messageText = ref(t(defaultMessageKey()));
const eventTitleText = ref(props.currentEvent.name);

const activeTemplate = computed(() =>
    resolveQrTemplateDefinition(activeTemplateId.value),
);

const previewFrameClass = computed(() => {
    return activeTemplate.value.printOrientation === 'landscape'
        ? 'w-full max-w-[min(96rem,calc((100svh-2rem)*1.4142))] max-h-[calc(100svh-2rem)] aspect-[1.4142/1]'
        : 'h-[calc(100svh-2rem)] max-h-[1120px] max-w-full aspect-[1/1.4142]';
});

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
    <div class="min-h-screen overflow-hidden bg-brand-canvas">
        <Head
            :title="`${currentEvent.name} · ${t('event_home.print_pack.preview_title')}`"
        />

        <div
            class="fixed top-4 right-4 z-20 flex items-center gap-2 print:hidden"
        >
            <Button
                as="a"
                :href="eventLinks.printPack"
                variant="outline"
                size="icon-sm"
                class="rounded-full bg-white/88"
            >
                <SlidersHorizontal class="size-4" />
            </Button>
            <Button
                type="button"
                size="icon-sm"
                class="rounded-full"
                @click="printPreview"
            >
                <Printer class="size-4" />
            </Button>
        </div>

        <div
            class="flex min-h-screen items-center justify-center p-4 sm:p-8 print:p-0"
        >
            <div
                :class="previewFrameClass"
                class="relative shrink-0 print:h-screen print:max-h-none print:w-full print:max-w-none"
            >
                <component
                    :is="activeTemplate.component"
                    class="!h-full max-h-full !w-full max-w-full print:!h-screen print:!w-full print:rounded-none print:shadow-none"
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
    </div>
</template>
