<script setup lang="ts">
import { ExternalLink, Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import { removeGoogleFontStylesheet, syncGoogleFontStylesheet } from '@/lib/google-fonts';
import { readQrPrintDraft, writeQrPrintDraft } from '@/lib/qr-print-draft';
import { qrTemplateDefinitions, resolveQrTemplateDefinition } from '@/lib/qr-print-templates';

const props = defineProps<{
    eventId: number;
    eventName: string;
    albumQrDataUrl: string;
    previewUrl: string;
}>();

const { t } = useTranslations();
const configureOpen = ref(false);
const activeTemplateId = ref<string>(qrTemplateDefinitions[0].id);

const subtitleText = ref<string>('SHARE THE');
const titleText = ref<string>('LOVE');
const sloganText = ref<string>('sharing is caring');
const messageText = ref<string>('Scan the QR code and share your memories by uploading photos, videos or wishes to the newly wed.');
const eventTitleText = ref<string>(props.eventName);

const activeTemplate = computed(() => resolveQrTemplateDefinition(activeTemplateId.value));

const previewUrlFor = (print = false): string => {
    if (typeof window === 'undefined') {
        return props.previewUrl;
    }

    const url = new URL(props.previewUrl, window.location.origin);

    if (print) {
        url.searchParams.set('print', '1');
    }

    return url.toString();
};

onMounted(() => {
    const draft = readQrPrintDraft(props.eventId);

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
});

watch(
    () => [
        activeTemplateId.value,
        subtitleText.value,
        titleText.value,
        sloganText.value,
        messageText.value,
        eventTitleText.value,
    ],
    () => {
        writeQrPrintDraft(props.eventId, {
            templateId: activeTemplate.value.id,
            subtitle: subtitleText.value,
            title: titleText.value,
            slogan: sloganText.value,
            message: messageText.value,
            eventTitle: eventTitleText.value,
        });
    },
    { immediate: true },
);

watch(
    () => activeTemplate.value.fonts.stylesheetHref,
    (href) => {
        syncGoogleFontStylesheet(href, 'qr-template-google');
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    removeGoogleFontStylesheet('qr-template-google');
});

const openPreview = (): void => {
    window.open(previewUrlFor(), '_blank', 'noopener,noreferrer');
};

const printPoster = (): void => {
    window.open(previewUrlFor(true), '_blank', 'noopener,noreferrer');
};
</script>

<template>
    <div class="flex h-full min-h-0 flex-col gap-3 overflow-hidden py-1">
        <div class="flex shrink-0 items-start gap-3">
            <div class="min-w-0 flex-1 overflow-x-auto pb-1">
                <div class="flex min-w-max items-center gap-2 pr-2">
                <button
                    v-for="theme in qrTemplateDefinitions"
                    :key="theme.id"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition"
                    :class="activeTemplateId === theme.id ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-300 bg-white/78 text-neutral-700 hover:border-neutral-500'"
                    @click="activeTemplateId = theme.id"
                >
                    {{ theme.label }}
                </button>
                </div>
            </div>

            <div class="inline-flex shrink-0 items-center gap-1 rounded-full border border-neutral-300 bg-white/82 p-1">
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.print_pdf')" @click="printPoster">
                    <Printer class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.open_preview')" @click="openPreview">
                    <ExternalLink class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.configure')" @click="configureOpen = true">
                    <SlidersHorizontal class="size-4" />
                </Button>
            </div>
        </div>

        <div class="flex min-h-0 flex-1 items-center justify-center overflow-hidden">
            <component
                :is="activeTemplate.component"
                class="block h-full max-h-full w-auto max-w-full"
                :subtitle="subtitleText"
                :title="titleText"
                :slogan="sloganText"
                :message="messageText"
                :event-title="eventTitleText"
                :fonts="activeTemplate.fonts"
                :qr-data-url="albumQrDataUrl"
                :preview-alt="t('event_home.print_pack.preview_alt')"
            />
        </div>

        <Sheet v-model:open="configureOpen">
            <SheetContent side="right" class="w-full overflow-y-auto border-l border-neutral-200 bg-[#fcfaf7] sm:max-w-md">
                <SheetHeader class="space-y-2 border-b border-neutral-200 px-6 py-5">
                    <SheetTitle class="text-left text-xl font-semibold text-neutral-950">
                        {{ t('event_home.print_pack.configure') }}
                    </SheetTitle>
                    <SheetDescription class="text-left text-sm leading-6 text-neutral-600">
                        {{ t('event_home.print_pack.configure_description') }}
                    </SheetDescription>
                </SheetHeader>

                <div class="space-y-5 px-6 py-5">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-900">
                            {{ t('event_home.print_pack.copy_fields.subtitle') }}
                        </label>
                        <Input v-model="subtitleText" class="rounded-2xl" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-900">
                            {{ t('event_home.print_pack.copy_fields.title') }}
                        </label>
                        <Input v-model="titleText" class="rounded-2xl" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-900">
                            {{ t('event_home.print_pack.copy_fields.slogan') }}
                        </label>
                        <Input v-model="sloganText" class="rounded-2xl" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-900">
                            {{ t('event_home.print_pack.copy_fields.message') }}
                        </label>
                        <Textarea v-model="messageText" rows="5" class="min-h-28 rounded-[1.5rem]" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-neutral-900">
                            {{ t('event_home.print_pack.copy_fields.event_title') }}
                        </label>
                        <Input v-model="eventTitleText" class="rounded-2xl" />
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    </div>
</template>
