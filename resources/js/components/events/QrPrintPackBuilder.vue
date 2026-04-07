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

type QrPosterTheme = {
    id: string;
    label: string;
    backgroundPath: string;
    paperColor: string;
    overlay: string;
    orientation: 'portrait' | 'landscape';
};

const props = defineProps<{
    eventName: string;
    albumUrl: string;
    albumAccessCode: string;
    albumQrDataUrl: string;
}>();

const { t } = useTranslations();

const qrPosterThemes: QrPosterTheme[] = [
    {
        id: 'beige',
        label: 'Beige',
        backgroundPath: '/qr-bg-themes/beige-base.png',
        paperColor: '#f7efe6',
        overlay: 'linear-gradient(180deg, rgba(255, 249, 243, 0.52), rgba(255, 252, 249, 0.68))',
        orientation: 'portrait',
    },
    {
        id: 'pink',
        label: 'Pink',
        backgroundPath: '/qr-bg-themes/pink-base.png',
        paperColor: '#f7e8ed',
        overlay: 'linear-gradient(180deg, rgba(255, 247, 250, 0.48), rgba(255, 251, 252, 0.70))',
        orientation: 'portrait',
    },
    {
        id: 'pink_landscape',
        label: 'Pink Landscape',
        backgroundPath: '/qr-bg-themes/pink-landscape-base.png',
        paperColor: '#f7e8ed',
        overlay: 'linear-gradient(180deg, rgba(255, 247, 250, 0.42), rgba(255, 251, 252, 0.64))',
        orientation: 'landscape',
    },
];

const configureOpen = ref(false);
const activeThemeId = ref<string>(qrPosterThemes[0].id);

const subtitleText = ref<string>('SHARE THE');
const titleText = ref<string>('LOVE');
const sloganText = ref<string>('sharing is caring');
const messageText = ref<string>('Scan the QR code and share your memories by uploading photos, videos or wishes to the newly wed.');
const eventTitleText = ref<string>(props.eventName);

const activeTheme = computed<QrPosterTheme>(() => {
    return qrPosterThemes.find((theme) => theme.id === activeThemeId.value) ?? qrPosterThemes[0];
});

const isLandscape = computed(() => activeTheme.value.orientation === 'landscape');

const posterStyle = computed<Record<string, string>>(() => ({
    backgroundColor: activeTheme.value.paperColor,
    backgroundImage: `url(${activeTheme.value.backgroundPath})`,
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
    backgroundSize: 'cover',
}));

const overlayStyle = computed<Record<string, string>>(() => ({
    background: activeTheme.value.overlay,
}));

const qrFrameStyle = computed<Record<string, string>>(() => ({
    backgroundColor: 'rgba(255,255,255,0.94)',
    padding: isLandscape.value ? '0.7rem' : '0.65rem',
    borderRadius: isLandscape.value ? '1.4rem' : '1.6rem',
    boxShadow: '0 24px 52px rgba(60, 38, 30, 0.12)',
}));

const printPoster = (): void => {
    const printWindow = window.open('', '_blank', 'noopener,noreferrer,width=1240,height=920');
    if (!printWindow) {
        return;
    }

    const printOrientation = isLandscape.value ? 'landscape' : 'portrait';
    const layoutClass = isLandscape.value ? 'poster poster-landscape' : 'poster poster-portrait';

    const markup = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>${titleText.value}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 ${printOrientation};
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        html, body {
            margin: 0;
            height: 100%;
            font-family: "Manrope", sans-serif;
            background: ${activeTheme.value.paperColor};
        }

        .poster {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            color: #2f211a;
            background-color: ${activeTheme.value.paperColor};
            background-image: url('${activeTheme.value.backgroundPath}');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .poster::before {
            content: "";
            position: absolute;
            inset: 0;
            background: ${activeTheme.value.overlay};
        }

        .poster-inner {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: ${isLandscape.value ? '42px 54px' : '58px 54px 52px'};
            text-align: center;
        }

        .poster-landscape .poster-inner {
            padding: 38px 58px 42px;
        }

        .subtitle {
            font-size: ${isLandscape.value ? '18px' : '19px'};
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: rgba(66, 44, 35, 0.76);
        }

        .title {
            margin: 12px 0 0;
            font-family: "Cormorant Garamond", serif;
            font-size: ${isLandscape.value ? '110px' : '122px'};
            font-weight: 600;
            line-height: 0.88;
            letter-spacing: -0.04em;
        }

        .slogan {
            margin: 10px 0 0;
            font-size: ${isLandscape.value ? '22px' : '24px'};
            line-height: 1.4;
            color: rgba(55, 35, 27, 0.80);
        }

        .middle {
            display: flex;
            width: 100%;
            flex-direction: column;
            align-items: center;
            gap: ${isLandscape.value ? '24px' : '30px'};
        }

        .qr-frame {
            width: ${isLandscape.value ? '292px' : '318px'};
            padding: 12px;
            border-radius: 24px;
            background: rgba(255,255,255,0.94);
            box-shadow: 0 24px 52px rgba(60, 38, 30, 0.12);
        }

        .qr-frame img {
            display: block;
            width: 100%;
            height: auto;
        }

        .message {
            max-width: ${isLandscape.value ? '760px' : '620px'};
            font-size: ${isLandscape.value ? '21px' : '22px'};
            line-height: 1.7;
            color: rgba(45, 30, 22, 0.84);
            text-wrap: pretty;
        }

        .footer {
            width: min(100%, 640px);
            padding-top: 18px;
            border-top: 1px solid rgba(73, 49, 39, 0.18);
            font-size: ${isLandscape.value ? '25px' : '26px'};
            font-weight: 700;
            letter-spacing: 0.01em;
        }
    </style>
</head>
<body>
    <article class="${layoutClass}">
        <div class="poster-inner">
            <div>
                <div class="subtitle">${subtitleText.value}</div>
                <div class="title">${titleText.value}</div>
                <div class="slogan">${sloganText.value}</div>
            </div>

            <div class="middle">
                <div class="qr-frame">
                    <img src="${props.albumQrDataUrl}" alt="${t('event_home.print_pack.preview_alt')}" />
                </div>
                <div class="message">${messageText.value.replace(/\n/g, '<br />')}</div>
            </div>

            <div class="footer">${eventTitleText.value}</div>
        </div>
    </article>
</body>
</html>`;

    printWindow.onload = () => {
        printWindow.focus();
        window.setTimeout(() => {
            printWindow.print();
        }, 180);
    };

    printWindow.document.open();
    printWindow.document.write(markup);
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
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    v-for="theme in qrPosterThemes"
                    :key="theme.id"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition"
                    :class="activeThemeId === theme.id ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-neutral-300 bg-white/78 text-neutral-700 hover:border-neutral-500'"
                    @click="activeThemeId = theme.id"
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
            <article
                class="relative mx-auto h-full w-full overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(53,36,24,0.16)]"
                :class="isLandscape ? 'max-w-[1100px] aspect-[1.4142/1]' : 'max-w-[760px] aspect-[1/1.4142]'"
                :style="posterStyle"
            >
                <div class="absolute inset-0" :style="overlayStyle" />

                <div
                    class="relative z-10 flex h-full flex-col items-center justify-between text-center text-[#2f211a]"
                    :class="isLandscape ? 'px-10 py-8 sm:px-14 sm:py-10' : 'px-8 py-10 sm:px-12 sm:py-12'"
                >
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-[0.32em] text-[#51392d]/75 sm:text-base">
                            {{ subtitleText }}
                        </p>
                        <h2
                            class="mt-2 font-semibold leading-[0.88] tracking-[-0.04em]"
                            :class="isLandscape ? 'text-6xl sm:text-[6.6rem]' : 'text-[4.6rem] sm:text-[7.5rem]'"
                            style="font-family: 'Cormorant Garamond', var(--font-serif);"
                        >
                            {{ titleText }}
                        </h2>
                        <p class="mt-2 text-lg text-[#4b3429]/80 sm:text-2xl">
                            {{ sloganText }}
                        </p>
                    </div>

                    <div class="flex w-full flex-col items-center gap-5 sm:gap-7">
                        <div
                            class="w-full max-w-[18rem] sm:max-w-[20rem]"
                            :class="isLandscape ? 'md:max-w-[18rem]' : 'md:max-w-[20rem]'"
                            :style="qrFrameStyle"
                        >
                            <img
                                :src="albumQrDataUrl"
                                :alt="t('event_home.print_pack.preview_alt')"
                                class="block h-auto w-full"
                            >
                        </div>

                        <p
                            class="max-w-[38rem] whitespace-pre-line text-base leading-7 text-[#38261d]/84 sm:text-lg sm:leading-8"
                            :class="isLandscape ? 'max-w-[46rem]' : ''"
                        >
                            {{ messageText }}
                        </p>
                    </div>

                    <div class="w-full max-w-[40rem] border-t border-[#50382c]/15 pt-4 sm:pt-5">
                        <p class="text-xl font-bold tracking-[0.01em] sm:text-[1.75rem]">
                            {{ eventTitleText }}
                        </p>
                    </div>
                </div>
            </article>
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
