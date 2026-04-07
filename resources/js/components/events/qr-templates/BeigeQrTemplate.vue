<script setup lang="ts">
import { computed } from 'vue';
import type { QrTemplateProps } from './types';

const props = defineProps<QrTemplateProps>();

const fontVariables = computed<Record<string, string>>(() => ({
    '--qr-heading-font': props.fonts.headingFamily,
    '--qr-body-font': props.fonts.bodyFamily,
}));
</script>

<template>
    <article :style="fontVariables" class="qr-template qr-template-beige relative mx-auto aspect-[1/1.4142] h-full max-h-full w-auto max-w-full overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(53,36,24,0.16)]">
        <div class="qr-template__art absolute inset-0" />
        <div class="qr-template__wash absolute inset-0" />

        <div class="relative z-10 flex h-full flex-col items-center justify-between px-[8%] py-[7.5%] text-center text-[#2f211a]">
            <header>
                <p class="qr-template__subtitle">
                    {{ subtitle }}
                </p>
                <h2 class="qr-template__title">
                    {{ title }}
                </h2>
                <p class="qr-template__slogan">
                    {{ slogan }}
                </p>
            </header>

            <div class="flex w-full flex-col items-center gap-[3.6cqh]">
                <div class="qr-template__qr-frame w-full max-w-[44cqw]">
                    <img :src="qrDataUrl" :alt="previewAlt" class="block h-auto w-full">
                </div>

                <p class="qr-template__message">
                    {{ message }}
                </p>
            </div>

            <footer class="qr-template__footer w-full max-w-[76cqw] border-t border-[#50382c]/15 pt-[2.2cqh]">
                <p class="qr-template__event-title">
                    {{ eventTitle }}
                </p>
            </footer>
        </div>
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

.qr-template__wash {
    background:
        radial-gradient(circle at top, rgba(255, 255, 255, 0.34), transparent 42%),
        linear-gradient(180deg, rgba(255, 249, 243, 0.52), rgba(255, 252, 249, 0.68));
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
    border-radius: 1.6rem;
    background: rgb(255 255 255 / 0.94);
    padding: clamp(0.35rem, calc(var(--qr-unit) * 0.72), 0.68rem);
    box-shadow: 0 24px 52px rgba(60, 38, 30, 0.12);
}

.qr-template__message {
    max-width: 66cqw;
    white-space: pre-line;
    font-family: var(--qr-body-font);
    font-size: clamp(0.72rem, calc(var(--qr-unit) * 2.02), 1.02rem);
    line-height: 1.62;
    color: rgb(56 38 29 / 0.84);
    text-wrap: pretty;
}

.qr-template__event-title {
    font-size: clamp(0.88rem, calc(var(--qr-unit) * 2.45), 1.5rem);
    font-weight: 700;
    letter-spacing: 0.01em;
}
</style>
