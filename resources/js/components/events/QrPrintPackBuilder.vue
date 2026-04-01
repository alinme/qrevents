<script setup lang="ts">
import { Copy, Download, FileOutput, QrCode, Printer } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';

type PrintPackTarget = 'album' | 'wall' | 'invitation';
type PrintPackPreset = 'welcome_sign' | 'table_card' | 'invitation_insert' | 'wall_sign' | 'small_card';
type PaperSize = 'A4' | 'A5' | '5x7' | 'card';

const props = defineProps<{
    eventName: string;
    albumAccessCode: string;
    branding: {
        primaryColor: string | null;
        accentColor: string | null;
        logoUrl: string | null;
    };
    targets: Array<{
        key: PrintPackTarget;
        url: string;
        qrDataUrl: string;
    }>;
}>();

const { t } = useTranslations();
const selectedTarget = ref<PrintPackTarget>(props.targets[0]?.key ?? 'album');
const selectedPreset = ref<PrintPackPreset>('welcome_sign');
const selectedPaperSize = ref<PaperSize>('A4');

const presetMeta = computed<Record<PrintPackPreset, {
    title: string;
    body: string;
    defaultTarget: PrintPackTarget;
    defaultPaperSize: PaperSize;
    instruction: string;
}>>(() => ({
    welcome_sign: {
        title: t('event_home.print_pack.presets.welcome_sign.title'),
        body: t('event_home.print_pack.presets.welcome_sign.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'A4',
        instruction: t('event_home.print_pack.instructions.album'),
    },
    table_card: {
        title: t('event_home.print_pack.presets.table_card.title'),
        body: t('event_home.print_pack.presets.table_card.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'card',
        instruction: t('event_home.print_pack.instructions.album'),
    },
    invitation_insert: {
        title: t('event_home.print_pack.presets.invitation_insert.title'),
        body: t('event_home.print_pack.presets.invitation_insert.body'),
        defaultTarget: 'invitation',
        defaultPaperSize: '5x7',
        instruction: t('event_home.print_pack.instructions.invitation'),
    },
    wall_sign: {
        title: t('event_home.print_pack.presets.wall_sign.title'),
        body: t('event_home.print_pack.presets.wall_sign.body'),
        defaultTarget: 'wall',
        defaultPaperSize: 'A4',
        instruction: t('event_home.print_pack.instructions.wall'),
    },
    small_card: {
        title: t('event_home.print_pack.presets.small_card.title'),
        body: t('event_home.print_pack.presets.small_card.body'),
        defaultTarget: 'album',
        defaultPaperSize: 'card',
        instruction: t('event_home.print_pack.instructions.album'),
    },
}));

const presetKeys: PrintPackPreset[] = ['welcome_sign', 'table_card', 'invitation_insert', 'wall_sign', 'small_card'];
const paperSizes: PaperSize[] = ['A4', 'A5', '5x7', 'card'];

const sizeMeta: Record<PaperSize, { width: string; aspectRatio: string }> = {
    A4: { width: '100%', aspectRatio: '210 / 297' },
    A5: { width: '100%', aspectRatio: '148 / 210' },
    '5x7': { width: '100%', aspectRatio: '5 / 7' },
    card: { width: '100%', aspectRatio: '5 / 3.2' },
};

const selectedTargetMeta = computed(() => props.targets.find((target) => target.key === selectedTarget.value) ?? props.targets[0]);
const activePresetMeta = computed(() => presetMeta.value[selectedPreset.value]);

const previewStyle = computed(() => ({
    '--print-primary': props.branding.primaryColor ?? '#171411',
    '--print-accent': props.branding.accentColor ?? '#d97706',
    aspectRatio: sizeMeta[selectedPaperSize.value].aspectRatio,
}));

const openSelectedTarget = (): void => {
    window.open(selectedTargetMeta.value?.url, '_blank', 'noopener,noreferrer');
};

const copySelectedTarget = async (): Promise<void> => {
    if (!selectedTargetMeta.value || typeof navigator === 'undefined' || !navigator.clipboard) {
        return;
    }

    await navigator.clipboard.writeText(selectedTargetMeta.value.url);
};

const svgMarkupFromDataUrl = async (dataUrl: string): Promise<string> => {
    if (dataUrl.startsWith('data:image/svg+xml;base64,')) {
        return atob(dataUrl.replace('data:image/svg+xml;base64,', ''));
    }

    if (dataUrl.startsWith('data:image/svg+xml,')) {
        return decodeURIComponent(dataUrl.replace('data:image/svg+xml,', ''));
    }

    const response = await fetch(dataUrl);

    return response.text();
};

const downloadRaster = async (format: 'png' | 'jpeg'): Promise<void> => {
    if (!selectedTargetMeta.value) {
        return;
    }

    const svgMarkup = await svgMarkupFromDataUrl(selectedTargetMeta.value.qrDataUrl);
    const blob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const image = new Image();

    await new Promise<void>((resolve, reject) => {
        image.onload = () => resolve();
        image.onerror = () => reject(new Error('Unable to render QR image.'));
        image.src = url;
    });

    const canvas = document.createElement('canvas');
    canvas.width = 1800;
    canvas.height = 1800;
    const context = canvas.getContext('2d');
    if (context === null) {
        URL.revokeObjectURL(url);
        return;
    }

    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);
    URL.revokeObjectURL(url);

    const outputUrl = canvas.toDataURL(format === 'png' ? 'image/png' : 'image/jpeg', 0.92);
    const link = document.createElement('a');
    link.href = outputUrl;
    link.download = `eventsmart-${selectedPreset.value}-${selectedTarget.value}.${format === 'png' ? 'png' : 'jpg'}`;
    link.click();
};

const printPack = async (): Promise<void> => {
    if (!selectedTargetMeta.value) {
        return;
    }

    const printWindow = window.open('', '_blank', 'width=1100,height=900');
    if (!printWindow) {
        return;
    }

    const html = `
        <html>
            <head>
                <title>${props.eventName} Print Pack</title>
                <style>
                    @page { margin: 0; size: auto; }
                    html, body { margin: 0; padding: 0; font-family: Georgia, serif; background: #f7f4ef; color: #171411; }
                    .sheet { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px; }
                    .card { width: min(720px, 100%); background: linear-gradient(180deg, #fffdf9, #ffffff); border-radius: 28px; padding: 28px; border: 1px solid rgba(23,20,17,0.08); box-shadow: 0 30px 70px rgba(23,20,17,0.12); }
                    .eyebrow { font-size: 12px; letter-spacing: 0.24em; text-transform: uppercase; color: #6b645c; }
                    .title { margin: 16px 0 0; font-size: 36px; line-height: 1.1; }
                    .body { margin: 12px 0 0; font-size: 16px; line-height: 1.6; color: #59524a; }
                    .qr-wrap { margin: 28px auto 0; width: fit-content; background: white; padding: 16px; border-radius: 24px; border: 1px solid rgba(23,20,17,0.08); }
                    .qr { display: block; width: 280px; height: 280px; }
                    .footer { margin-top: 24px; display: grid; gap: 8px; text-align: center; color: #59524a; }
                    .code { letter-spacing: 0.24em; font-weight: 700; text-transform: uppercase; }
                    .hint { margin-top: 18px; text-align: center; font-size: 13px; color: #7a7268; }
                </style>
            </head>
            <body>
                <div class="sheet">
                    <div class="card">
                        <div class="eyebrow">${activePresetMeta.value.title}</div>
                        <h1 class="title">${props.eventName}</h1>
                        <p class="body">${activePresetMeta.value.instruction}</p>
                        <div class="qr-wrap">
                            <img id="print-qr" class="qr" src="${selectedTargetMeta.value.qrDataUrl}" alt="QR code" />
                        </div>
                        <p class="hint">Scan with your phone camera</p>
                        <div class="footer">
                            <div class="code">${props.albumAccessCode}</div>
                            <div>${selectedTargetMeta.value.url}</div>
                        </div>
                    </div>
                </div>
                <script>
                    const image = document.getElementById('print-qr');
                    const runPrint = () => {
                        window.focus();
                        window.print();
                    };

                    if (image && image.complete) {
                        setTimeout(runPrint, 180);
                    } else if (image) {
                        image.addEventListener('load', () => setTimeout(runPrint, 180), { once: true });
                        image.addEventListener('error', () => setTimeout(runPrint, 180), { once: true });
                    } else {
                        setTimeout(runPrint, 180);
                    }
                <\/script>
            </body>
        </html>
    `;

    printWindow.document.open();
    printWindow.document.write(html);
    printWindow.document.close();
};

const applyPreset = (preset: PrintPackPreset): void => {
    selectedPreset.value = preset;
    selectedTarget.value = presetMeta.value[preset].defaultTarget;
    selectedPaperSize.value = presetMeta.value[preset].defaultPaperSize;
};
</script>

<template>
    <section class="overflow-hidden rounded-[1.9rem] border border-black/5 bg-[linear-gradient(180deg,#fffdf9,#fbf7f0)] shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-black/5 px-5 py-5 md:px-6">
            <div>
                <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                    {{ t('event_home.print_pack.title') }}
                </h2>
                <p class="mt-1 text-sm text-zinc-600">
                    {{ t('event_home.print_pack.description') }}
                </p>
            </div>
            <div class="rounded-full bg-[#171411]/5 p-2 text-[#171411]">
                <QrCode class="size-4" />
            </div>
        </div>

        <div class="grid gap-0 lg:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)]">
            <div class="border-b border-black/5 px-5 py-5 lg:border-b-0 lg:border-r md:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        {{ t('event_home.print_pack.presets_title') }}
                    </p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                        <button
                            v-for="preset in presetKeys"
                            :key="preset"
                            type="button"
                            :class="[
                                'rounded-[1.2rem] px-4 py-4 text-left transition',
                                selectedPreset === preset
                                    ? 'bg-[#171411] text-white shadow-sm'
                                    : 'bg-white/70 text-[#171411] ring-1 ring-black/8 hover:bg-white hover:ring-black/15',
                            ]"
                            @click="applyPreset(preset)"
                        >
                            <p class="font-semibold">{{ presetMeta[preset].title }}</p>
                            <p class="mt-2 text-sm" :class="selectedPreset === preset ? 'text-white/80' : 'text-zinc-600'">
                                {{ presetMeta[preset].body }}
                            </p>
                        </button>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="border-t border-black/5 pt-4">
                        <p class="text-sm font-semibold text-[#171411]">
                            {{ t('event_home.print_pack.target_title') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="target in targets"
                                :key="target.key"
                                type="button"
                                :class="[
                                    'rounded-full px-4 py-2 text-sm font-medium transition',
                                    selectedTarget === target.key
                                        ? 'bg-[#171411] text-white'
                                        : 'bg-white/85 text-[#171411] ring-1 ring-black/10 hover:bg-white hover:ring-black/20',
                                ]"
                                @click="selectedTarget = target.key"
                            >
                                {{ t(`event_home.print_pack.targets.${target.key}`) }}
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-black/5 pt-4">
                        <p class="text-sm font-semibold text-[#171411]">
                            {{ t('event_home.print_pack.size_title') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="paperSize in paperSizes"
                                :key="paperSize"
                                type="button"
                                :class="[
                                    'rounded-full px-4 py-2 text-sm font-medium transition',
                                    selectedPaperSize === paperSize
                                        ? 'bg-[#171411] text-white'
                                        : 'bg-white/85 text-[#171411] ring-1 ring-black/10 hover:bg-white hover:ring-black/20',
                                ]"
                                @click="selectedPaperSize = paperSize"
                            >
                                {{ paperSize }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-t border-black/5 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        {{ t('event_home.print_pack.quick_actions_title') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                    <Button class="rounded-full px-5" @click="printPack">
                        <Printer class="mr-2 size-4" />
                        {{ t('event_home.print_pack.actions.print_pdf') }}
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="downloadRaster('png')">
                        <FileOutput class="mr-2 size-4" />
                        {{ t('event_home.print_pack.actions.download_png') }}
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="downloadRaster('jpeg')">
                        <FileOutput class="mr-2 size-4" />
                        {{ t('event_home.print_pack.actions.download_jpg') }}
                    </Button>
                    <Button as-child variant="outline" class="rounded-full px-5">
                        <a :href="selectedTargetMeta?.qrDataUrl" :download="`eventsmart-${selectedTarget}-qr.svg`">
                            <Download class="mr-2 size-4" />
                            {{ t('event_home.print_pack.actions.download_svg') }}
                        </a>
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="openSelectedTarget">
                        <QrCode class="mr-2 size-4" />
                        {{ t('event_home.print_pack.actions.open_target') }}
                    </Button>
                    <Button variant="outline" class="rounded-full px-5" @click="copySelectedTarget">
                        <Copy class="mr-2 size-4" />
                        {{ t('event_home.print_pack.actions.copy_target') }}
                    </Button>
                    </div>
                </div>
            </div>

            <div class="bg-[radial-gradient(circle_at_top,rgba(217,119,6,0.12),transparent_35%),linear-gradient(180deg,#f8f2e8,#f5efe4)] px-5 py-5 md:px-6">
                <div class="mb-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        {{ t('event_home.print_pack.preview_title') }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-600">
                        {{ activePresetMeta.body }}
                    </p>
                </div>
                <div
                    class="mx-auto max-w-[28rem] overflow-hidden rounded-[2rem] border border-black/10 bg-[linear-gradient(180deg,#fffefb,#ffffff)] p-4 shadow-[0_24px_60px_rgba(23,20,17,0.12)]"
                    :style="previewStyle"
                >
                    <div class="flex h-full flex-col justify-between rounded-[1.5rem] bg-transparent p-2">
                        <div>
                            <p class="text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                {{ activePresetMeta.title }}
                            </p>
                            <h3 class="mt-4 text-2xl font-semibold tracking-tight text-[#171411]">
                                {{ eventName }}
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-zinc-600">
                                {{ activePresetMeta.instruction }}
                            </p>
                        </div>

                        <div class="my-6 mx-auto w-fit rounded-[1.6rem] bg-white p-4 shadow-[0_12px_32px_rgba(23,20,17,0.08)]">
                            <img
                                v-if="selectedTargetMeta"
                                :src="selectedTargetMeta.qrDataUrl"
                                :alt="t('event_home.print_pack.preview_alt')"
                                class="mx-auto aspect-square w-full max-w-[16rem]"
                            />
                        </div>

                        <div class="space-y-2 text-center">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-zinc-500">
                                {{ t('event_home.album.code_label', { code: albumAccessCode }) }}
                            </p>
                            <p class="text-sm text-zinc-600">
                                {{ selectedTargetMeta?.url }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
