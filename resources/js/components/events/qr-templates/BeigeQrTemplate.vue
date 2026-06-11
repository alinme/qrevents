<script setup lang="ts">
import { computed } from 'vue';
import {
    qrTemplateGeometry,
    qrZoneHeightFraction,
} from '@/lib/qr-print-geometry';
import type { QrTemplateProps } from './types';

const props = defineProps<QrTemplateProps>();

const geometry = qrTemplateGeometry.beige;

const fontVariables = computed<Record<string, string>>(() => ({
    '--qr-heading-font': props.fonts.headingFamily,
    '--qr-body-font': props.fonts.bodyFamily,
}));

const zoneStyles = {
    header: { top: `${geometry.headerTop * 100}%` },
    message: { top: `${geometry.messageTop * 100}%` },
    qr: {
        left: `${geometry.qr.left * 100}%`,
        top: `${geometry.qr.top * 100}%`,
        width: `${geometry.qr.width * 100}%`,
        height: `${qrZoneHeightFraction(geometry) * 100}%`,
    },
    footer: { top: `${geometry.footerTop * 100}%` },
};
</script>

<template>
    <article
        :style="fontVariables"
        class="qr-template qr-template-beige relative mx-auto aspect-[1410/2000] h-full max-h-full w-auto max-w-full overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(53,36,24,0.16)]"
    >
        <div class="qr-template__art absolute inset-0" />

        <header
            class="absolute right-[8%] left-[8%] text-center text-[#2f211a]"
            :style="zoneStyles.header"
        >
            <p class="qr-template__subtitle">{{ subtitle }}</p>
            <h2 class="qr-template__title">{{ title }}</h2>
            <p class="qr-template__slogan">{{ slogan }}</p>
        </header>

        <p
            class="qr-template__message absolute right-[14%] left-[14%] text-center"
            :style="zoneStyles.message"
        >
            {{ message }}
        </p>

        <div
            class="absolute flex items-center justify-center"
            :style="zoneStyles.qr"
        >
            <div class="qr-template__qr-frame h-[88%] w-[88%]">
                <img
                    :src="qrDataUrl"
                    :alt="previewAlt"
                    class="block h-full w-full object-contain"
                />
            </div>
        </div>

        <footer
            class="qr-template__footer absolute right-[18%] left-[18%] border-t border-[#50382c]/15 pt-[1.6cqh] text-center"
            :style="zoneStyles.footer"
        >
            <p class="qr-template__event-title">{{ eventTitle }}</p>
        </footer>
    </article>
</template>

<style scoped>
.qr-template {
    container-type: size;
    --qr-unit: min(1cqw, 1cqh);
}

.qr-template__art {
    background:
        center / cover no-repeat url('/qr-bg-themes/beige-base.png'),
        #f7efe6;
}

.qr-template__subtitle {
    font-family: var(--qr-body-font);
    font-size: clamp(0.5rem, calc(var(--qr-unit) * 1.55), 0.92rem);
    font-weight: 800;
    letter-spacing: clamp(0.12em, calc(var(--qr-unit) * 0.12), 0.3em);
    text-transform: uppercase;
    color: rgb(81 57 45 / 0.75);
}

.qr-template__title {
    margin-top: 0.6cqh;
    font-family: var(--qr-heading-font);
    font-size: clamp(1.9rem, calc(var(--qr-unit) * 8.4), 5.2rem);
    font-weight: 600;
    line-height: 0.88;
    letter-spacing: -0.04em;
}

.qr-template__slogan {
    margin-top: 0.8cqh;
    font-family: var(--qr-body-font);
    font-size: clamp(0.78rem, calc(var(--qr-unit) * 2.45), 1.28rem);
    line-height: 1.45;
    color: rgb(75 52 41 / 0.8);
}

.qr-template__qr-frame {
    border-radius: 1.2rem;
    background: #ffffff;
    padding: clamp(0.35rem, calc(var(--qr-unit) * 0.9), 0.8rem);
}

.qr-template__message {
    white-space: pre-line;
    font-family: var(--qr-body-font);
    font-size: clamp(0.72rem, calc(var(--qr-unit) * 2.02), 1.02rem);
    line-height: 1.62;
    color: rgb(56 38 29 / 0.84);
    text-wrap: pretty;
}

.qr-template__footer {
    font-family: var(--qr-body-font);
}

.qr-template__event-title {
    font-size: clamp(0.85rem, calc(var(--qr-unit) * 2.5), 1.35rem);
    font-weight: 700;
    letter-spacing: 0.01em;
    color: #2f211a;
}
</style>
