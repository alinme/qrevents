<script setup lang="ts">
import { Copy, ExternalLink, Printer, SlidersHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import { qrTemplateDefinitions, resolveQrTemplateDefinition } from '@/lib/qr-print-templates';

const props = defineProps<{
    eventName: string;
    albumUrl: string;
    albumAccessCode: string;
    albumQrDataUrl: string;
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

const templateContent = computed(() => ({
    subtitle: subtitleText.value,
    title: titleText.value,
    slogan: sloganText.value,
    message: messageText.value,
    eventTitle: eventTitleText.value,
}));

const printPoster = (): void => {
    const printWindow = window.open('', '_blank', 'noopener,noreferrer,width=1240,height=920');
    if (!printWindow) {
        return;
    }

    printWindow.onload = () => {
        printWindow.focus();
        window.setTimeout(() => {
            printWindow.print();
        }, 180);
    };

    printWindow.document.open();
    printWindow.document.write(
        activeTemplate.value.renderPrintHtml(
            templateContent.value,
            props.albumQrDataUrl,
            t('event_home.print_pack.preview_alt'),
        ),
    );
    printWindow.document.close();
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
    <div class="flex min-h-[calc(100vh-9rem)] flex-col gap-4">
        <div class="flex flex-wrap items-center gap-2 print:hidden">
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

            <Button variant="outline" class="ml-auto rounded-full" @click="configureOpen = true">
                <SlidersHorizontal class="size-4" />
                {{ t('event_home.print_pack.configure') }}
            </Button>

            <div class="inline-flex items-center gap-1 rounded-full border border-neutral-300 bg-white/82 p-1">
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.copy_target')" @click="void copyAlbumLink()">
                    <Copy class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.open_target')" @click="openAlbum">
                    <ExternalLink class="size-4" />
                </Button>
                <Button type="button" variant="ghost" size="icon-sm" class="rounded-full" :title="t('event_home.print_pack.actions.print_pdf')" @click="printPoster">
                    <Printer class="size-4" />
                </Button>
            </div>
        </div>

        <div class="flex-1">
            <component
                :is="activeTemplate.component"
                :subtitle="subtitleText"
                :title="titleText"
                :slogan="sloganText"
                :message="messageText"
                :event-title="eventTitleText"
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
