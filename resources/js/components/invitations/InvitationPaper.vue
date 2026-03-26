<script setup lang="ts">
import { CalendarDays, MapPin, Phone } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import {
    type InvitationTemplateDetailsConfig,
    invitationTemplateMap,
    type InvitationTemplateTextBlockConfig,
    type InvitationTemplateId,
} from '@/lib/invitation-templates';

const props = withDefaults(defineProps<{
    template: InvitationTemplateId;
    eventName: string;
    logoUrl?: string | null;
    guestLabel?: string | null;
    headline: string;
    message: string;
    closing: string;
    detailLines?: string[];
    contactPhone?: string | null;
    dateLabel?: string | null;
    venueAddress?: string | null;
    mode?: 'preview' | 'live';
}>(), {
    contactPhone: null,
    dateLabel: null,
    venueAddress: null,
    logoUrl: null,
    guestLabel: null,
    detailLines: () => [],
    mode: 'live',
});

const invitationArtwork = computed(() => invitationTemplateMap[props.template]);
const paperContainer = ref<HTMLElement | null>(null);
const paperWidth = ref<number | null>(null);
let resizeObserver: ResizeObserver | null = null;

const invitationTemplateVisuals = computed(() => {
    const selectedTemplate = invitationTemplateMap[props.template];

    return {
        classic: {
            surfaceClass: 'border-stone-200 bg-[linear-gradient(180deg,rgba(255,252,247,0.98),rgba(255,255,255,0.92))] text-neutral-950',
            mutedClass: 'text-neutral-600',
        },
        floral: {
            surfaceClass: 'border-rose-200 bg-[linear-gradient(180deg,rgba(255,247,249,0.98),rgba(255,255,255,0.92))] text-neutral-950',
            mutedClass: 'text-neutral-600',
        },
        midnight: {
            surfaceClass: 'border-white/15 bg-[linear-gradient(180deg,rgba(15,23,42,0.98),rgba(15,23,42,0.9))] text-white',
            mutedClass: 'text-white/72',
        },
        canva_cream: {
            surfaceClass: 'border-stone-200 bg-[#f9f7f2] text-neutral-950',
            mutedClass: 'text-neutral-600',
            label: selectedTemplate.label,
        },
        canva_brown: {
            surfaceClass: 'border-[#ead7ca] bg-[#fcf7f3] text-neutral-950',
            mutedClass: 'text-[#8f6556]',
            label: selectedTemplate.label,
        },
        canva_watercolor: {
            surfaceClass: 'border-[#dfe6d8] bg-[#f7faf5] text-neutral-950',
            mutedClass: 'text-[#7a8676]',
            label: selectedTemplate.label,
        },
    }[props.template];
});

const invitationPaperStyle = computed(() => {
    if (!invitationArtwork.value.baseUrl) {
        return undefined;
    }

    return {
        backgroundImage: `linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.18)), url(${invitationArtwork.value.baseUrl})`,
        backgroundPosition: 'center',
        backgroundSize: 'cover',
    };
});

const isPreviewMode = computed(() => props.mode === 'preview');
const mutedTextClass = computed(() => invitationTemplateVisuals.value.mutedClass);
const templateLayout = computed(() => invitationArtwork.value.layout);
const paperScale = computed(() => {
    if (paperWidth.value === null) {
        return 1;
    }

    const baselineWidth = isPreviewMode.value ? 260 : 620;

    return Math.min(1, Math.max(0.58, paperWidth.value / baselineWidth));
});

const resolveFontFamily = (fontFamily?: string): string | undefined => {
    if (! fontFamily) {
        return undefined;
    }

    const normalizedFontFamily = fontFamily.trim().toLowerCase();

    if (normalizedFontFamily === 'serif') {
        return 'var(--font-serif)';
    }

    if (normalizedFontFamily === 'sans') {
        return 'var(--font-sans)';
    }

    if (normalizedFontFamily === 'cinzel') {
        return '"Cinzel", var(--font-serif)';
    }

    if (normalizedFontFamily === 'montserrat') {
        return '"Montserrat", var(--font-sans)';
    }

    if (normalizedFontFamily === 'great vibes') {
        return '"Great Vibes", var(--font-serif)';
    }

    if (normalizedFontFamily === 'cormorant garamond') {
        return '"Cormorant Garamond", var(--font-serif)';
    }

    return `"${fontFamily}", var(--font-sans)`;
};

const resolveFontSize = (block: InvitationTemplateTextBlockConfig): string | undefined => {
    if (isPreviewMode.value) {
        return block.fontSizePreview ?? block.fontSizeLive;
    }

    return block.fontSizeLive ?? block.fontSizePreview;
};

const scaleCssSize = (value?: string): string | undefined => {
    if (! value) {
        return undefined;
    }

    const match = value.trim().match(/^(-?\d*\.?\d+)(rem|px)$/);
    if (! match) {
        return value;
    }

    const numericValue = Number.parseFloat(match[1]);
    const unit = match[2];

    return `${(numericValue * paperScale.value).toFixed(4).replace(/\.?0+$/, '')}${unit}`;
};

const blockStyle = (block: InvitationTemplateTextBlockConfig): Record<string, string> => {
    const style: Record<string, string> = {
        top: block.top,
        left: block.left,
        width: block.width,
        textAlign: block.align,
    };

    const fontSize = resolveFontSize(block);
    if (fontSize) {
        style.fontSize = scaleCssSize(fontSize) ?? fontSize;
    }

    if (block.lineHeight) {
        style.lineHeight = block.lineHeight;
    }

    if (block.letterSpacing) {
        style.letterSpacing = block.letterSpacing;
    }

    if (block.fontWeight) {
        style.fontWeight = block.fontWeight;
    }

    if (block.color) {
        style.color = block.color;
    }

    const fontFamily = resolveFontFamily(block.fontFamily);
    if (fontFamily) {
        style.fontFamily = fontFamily;
    }

    if (block.uppercase) {
        style.textTransform = 'uppercase';
    }

    return style;
};

const footerStyle = computed<Record<string, string>>(() => ({
    top: templateLayout.value.footer.top,
    left: templateLayout.value.footer.left,
    width: templateLayout.value.footer.width,
    textAlign: templateLayout.value.footer.align,
    gap: templateLayout.value.footer.gap,
}));

const detailsBlock = computed<InvitationTemplateDetailsConfig | null>(() => {
    return templateLayout.value.details ?? null;
});

const detailsStyle = computed<Record<string, string> | null>(() => {
    if (detailsBlock.value === null) {
        return null;
    }

    return {
        ...blockStyle(detailsBlock.value),
        gap: detailsBlock.value.gap,
    };
});

const footerMetaStyle = computed<Record<string, string>>(() => {
    const footer = templateLayout.value.footer;

    return {
        gap: footer.metaGap,
        fontSize: scaleCssSize(footer.fontSize) ?? footer.fontSize,
        lineHeight: footer.lineHeight,
        ...(footer.fontFamily ? { fontFamily: resolveFontFamily(footer.fontFamily) } : {}),
        ...(footer.color ? { color: footer.color } : {}),
    };
});

const footerClosingStyle = computed<Record<string, string>>(() => {
    const closing = templateLayout.value.footer.closing;
    const style: Record<string, string> = {
        maxWidth: closing.maxWidth,
    };

    const fontSize = isPreviewMode.value
        ? (closing.fontSizePreview ?? closing.fontSizeLive)
        : (closing.fontSizeLive ?? closing.fontSizePreview);

    if (fontSize) {
        style.fontSize = scaleCssSize(fontSize) ?? fontSize;
    }

    if (closing.lineHeight) {
        style.lineHeight = closing.lineHeight;
    }

    if (closing.fontWeight) {
        style.fontWeight = closing.fontWeight;
    }

    if (closing.color) {
        style.color = closing.color;
    }

    return style;
});

const logoStyle = computed<Record<string, string>>(() => {
    const dimension = isPreviewMode.value ? 44 : 72;
    const scaledDimension = Math.max(isPreviewMode.value ? 36 : 56, Math.round(dimension * paperScale.value));

    return {
        top: isPreviewMode.value ? '3.4%' : '3.2%',
        left: '50%',
        width: `${scaledDimension}px`,
        height: `${scaledDimension}px`,
        transform: 'translateX(-50%)',
    };
});

const updatePaperWidth = (): void => {
    paperWidth.value = paperContainer.value?.clientWidth ?? null;
};

onMounted(() => {
    updatePaperWidth();

    if (typeof ResizeObserver === 'undefined') {
        return;
    }

    resizeObserver = new ResizeObserver(() => {
        updatePaperWidth();
    });

    if (paperContainer.value) {
        resizeObserver.observe(paperContainer.value);
    }
});

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
});
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden rounded-[30px] border p-3 shadow-sm sm:p-4',
            invitationTemplateVisuals.surfaceClass,
            isPreviewMode ? 'shadow-sm' : 'shadow-xl print:border-0 print:p-0 print:shadow-none',
        ]"
        :style="invitationPaperStyle"
    >
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-black/10 via-white/30 to-black/10 opacity-70" />
            <div class="absolute -right-20 top-12 h-48 w-48 rounded-full bg-white/20 blur-3xl" />
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-black/5 blur-2xl" />
        </div>

        <div
            ref="paperContainer"
            class="relative overflow-hidden border border-current/10 bg-white/10"
            :style="{
                aspectRatio: templateLayout.paper.aspectRatio,
                borderRadius: templateLayout.paper.innerRadius,
            }"
        >
            <div class="absolute inset-0" :style="{ backgroundColor: `rgba(255,255,255,${templateLayout.paper.overlayOpacity})` }" />

            <div
                v-if="logoUrl"
                class="absolute z-[2] overflow-hidden rounded-full border border-white/70 bg-white/80 shadow-lg backdrop-blur-sm"
                :style="logoStyle"
            >
                <img
                    :src="logoUrl"
                    alt="Invitation logo"
                    class="h-full w-full object-cover"
                >
            </div>

            <div v-if="templateLayout.header.enabled" class="absolute inset-0">
                <p
                    :class="['absolute', mutedTextClass]"
                    :style="blockStyle(templateLayout.header.eventName)"
                >
                    {{ eventName }}
                </p>
                <p
                    v-if="guestLabel"
                    :class="['absolute', mutedTextClass]"
                    :style="blockStyle(templateLayout.header.guestLabel)"
                >
                    {{ guestLabel }}
                </p>
            </div>

            <div class="absolute inset-0">
                <h2
                    class="absolute"
                    :style="blockStyle(templateLayout.headline)"
                >
                    {{ headline }}
                </h2>
                <p
                    :class="['absolute', mutedTextClass]"
                    :style="blockStyle(templateLayout.message)"
                >
                    {{ message }}
                </p>
            </div>

            <div
                v-if="detailLines.length > 0 && detailsStyle"
                class="absolute flex flex-col items-center"
                :class="mutedTextClass"
                :style="detailsStyle"
            >
                <p
                    v-for="(detailLine, index) in detailLines"
                    :key="`${detailLine}-${index}`"
                    class="text-center"
                >
                    {{ detailLine }}
                </p>
            </div>

            <div class="absolute flex flex-col items-center" :style="footerStyle">
                <div
                    v-if="dateLabel || venueAddress || contactPhone"
                    class="flex flex-col items-center"
                    :class="mutedTextClass"
                    :style="footerMetaStyle"
                >
                    <span v-if="dateLabel" class="inline-flex items-center gap-2 text-center">
                        <CalendarDays class="size-4" />
                        {{ dateLabel }}
                    </span>
                    <span v-if="venueAddress" class="inline-flex items-center gap-2 text-center">
                        <MapPin class="size-4" />
                        {{ venueAddress }}
                    </span>
                    <span v-if="contactPhone" class="inline-flex items-center gap-2 text-center">
                        <Phone class="size-4" />
                        {{ contactPhone }}
                    </span>
                </div>
                <p
                    :class="['mx-auto', mutedTextClass]"
                    :style="footerClosingStyle"
                >
                    {{ closing }}
                </p>
            </div>

            <div class="sr-only">
                <p>
                        {{ eventName }}
                </p>
                <p v-if="guestLabel">
                        {{ guestLabel }}
                </p>
                <p>
                        {{ message }}
                </p>
                <p
                    v-for="(detailLine, index) in detailLines"
                    :key="`sr-${detailLine}-${index}`"
                >
                    {{ detailLine }}
                </p>
                <p v-if="dateLabel">
                    {{ dateLabel }}
                </p>
                <p v-if="venueAddress">
                    {{ venueAddress }}
                </p>
                <p v-if="contactPhone">
                    {{ contactPhone }}
                </p>
                <p>
                        {{ closing }}
                </p>
            </div>
        </div>
    </div>
</template>
