<script setup lang="ts">
import { computed } from 'vue';
import {
    qrTemplateGeometry,
    qrZoneHeightFraction,
} from '@/lib/qr-print-geometry';
import type { QrTemplateProps } from './types';

const props = defineProps<QrTemplateProps>();

const geometry = qrTemplateGeometry.pink;

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
        class="qr-template qr-template-pink relative mx-auto aspect-[1414/2000] h-full max-h-full w-auto max-w-full overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(76,41,52,0.15)]"
    >
        <div class="qr-template__art absolute inset-0" />

        <header
            class="absolute right-[8%] left-[8%] text-center text-[#38232d]"
            :style="zoneStyles.header"
        >
            <p class="qr-template__subtitle">{{ subtitle }}</p>
            <h2 class="qr-template__title">{{ title }}</h2>
            <p class="qr-template__slogan">{{ slogan }}</p>
        </header>

        <p
            class="qr-template__message absolute right-[16%] left-[16%] text-center"
            :style="zoneStyles.message"
        >
            {{ message }}
        </p>

        <div class="absolute" :style="zoneStyles.qr">
            <img
                :src="qrDataUrl"
                :alt="previewAlt"
                class="block h-full w-full object-contain"
            />
        </div>

        <footer
            class="qr-template__footer absolute right-[22%] left-[22%] border-t border-[#7a5260]/16 pt-[1.6cqh] text-center"
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
        center / cover no-repeat url('/qr-bg-themes/pink-base.png'),
        #f7e8ed;
}

.qr-template__subtitle {
    font-family: var(--qr-body-font);
    font-size: clamp(0.5rem, calc(var(--qr-unit) * 1.5), 0.9rem);
    font-weight: 800;
    letter-spacing: clamp(0.1em, calc(var(--qr-unit) * 0.11), 0.28em);
    text-transform: uppercase;
    color: rgb(111 70 83 / 0.78);
}

.qr-template__title {
    margin-top: 0.6cqh;
    font-family: var(--qr-heading-font);
    font-size: clamp(1.8rem, calc(var(--qr-unit) * 8), 5rem);
    font-weight: 600;
    line-height: 0.86;
    letter-spacing: -0.05em;
}

.qr-template__slogan {
    margin-top: 0.8cqh;
    font-family: var(--qr-body-font);
    font-size: clamp(0.74rem, calc(var(--qr-unit) * 2.3), 1.2rem);
    line-height: 1.4;
    color: rgb(98 63 75 / 0.82);
}

.qr-template__message {
    white-space: pre-line;
    font-family: var(--qr-body-font);
    font-size: clamp(0.7rem, calc(var(--qr-unit) * 1.95), 1rem);
    line-height: 1.62;
    color: rgb(59 37 46 / 0.84);
    text-wrap: pretty;
}

.qr-template__footer {
    font-family: var(--qr-body-font);
}

.qr-template__event-title {
    font-size: clamp(0.85rem, calc(var(--qr-unit) * 2.55), 1.4rem);
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #38232d;
}
</style>
