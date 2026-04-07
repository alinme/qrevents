<script setup lang="ts">
import { Copy, Eye, ExternalLink, Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
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
    albumUrl: string;
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

const openAlbum = (): void => {
    window.open(props.albumUrl, '_blank', 'noopener,noreferrer');
};

const copyAlbumLink = async (): Promise<void> => {
    try {
        await navigator.clipboard.writeText(props.albumUrl);
        toast.success(t('event_home.print_pack.copy_success'));
    } catch {
        toast.error(t('guests.messages.copy_label_failed', { label: 'link' }));
    }
};
</script>

<template>
    <div class="flex min-h-[calc(100vh-9rem)] flex-col gap-6 py-2">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex flex-wrap items-center gap-2">
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

            <div class="ml-auto inline-flex items-center gap-1 rounded-full border border-neutral-300 bg-white/82 p-1">
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('guests.actions.open_preview')" @click="openPreview">
                    <Eye class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.print_pdf')" @click="printPoster">
                    <Printer class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.copy_target')" @click="void copyAlbumLink()">
                    <Copy class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.open_target')" @click="openAlbum">
                    <ExternalLink class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.configure')" @click="configureOpen = true">
                    <SlidersHorizontal class="size-4" />
                </Button>
            </div>
        </div>

        <div class="flex flex-1 items-center justify-center rounded-[2rem] border border-dashed border-neutral-300/80 bg-white/55 px-6 py-10 text-center">
            <div class="max-w-2xl space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-neutral-500">
                    {{ activeTemplate.label }}
                </p>
                <h2 class="text-3xl font-semibold tracking-tight text-neutral-950 sm:text-4xl">
                    {{ titleText }}
                </h2>
                <p class="text-sm leading-7 text-neutral-600 sm:text-base">
                    {{ t('event_home.print_pack.page_description') }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                    <Button type="button" class="rounded-full" @click="openPreview">
                        <Eye class="size-4" />
                        {{ t('guests.actions.open_preview') }}
                    </Button>
                    <Button type="button" variant="outline" class="rounded-full" @click="configureOpen = true">
                        <SlidersHorizontal class="size-4" />
                        {{ t('event_home.print_pack.configure') }}
                    </Button>
                </div>
            </div>
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
