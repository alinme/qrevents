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
    <article :style="fontVariables" class="qr-template qr-template-pink relative mx-auto aspect-[1/1.4142] h-full max-h-[calc(100vh-15rem)] w-full max-w-[760px] overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(76,41,52,0.15)]">
        <div class="qr-template__art absolute inset-0" />
        <div class="qr-template__wash absolute inset-0" />

        <div class="relative z-10 grid h-full grid-rows-[auto_minmax(0,1fr)_auto] px-8 py-9 text-center text-[#38232d] sm:px-12 sm:py-11">
            <header class="self-start">
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

            <div class="flex flex-col items-center justify-center gap-5 sm:gap-6">
                <p class="qr-template__message order-2">
                    {{ message }}
                </p>

                <div class="qr-template__qr-frame order-1 w-full max-w-[17rem] sm:max-w-[18.5rem]">
                    <img :src="qrDataUrl" :alt="previewAlt" class="block h-auto w-full">
                </div>
            </div>

            <footer class="qr-template__footer mx-auto w-full max-w-[34rem] self-end border-t border-[#7a5260]/16 pt-4">
                <p class="text-xl font-semibold tracking-[0.03em] sm:text-[1.7rem]">
                    {{ eventTitle }}
                </p>
            </footer>
        </div>
    </article>
</template>

<style scoped>
.qr-template__art {
    background:
        center / cover no-repeat url('/qr-bg-themes/pink-base.png'),
        #f7e8ed;
}

.qr-template__wash {
    background:
        radial-gradient(circle at top, rgba(255, 255, 255, 0.32), transparent 45%),
        linear-gradient(180deg, rgba(255, 247, 250, 0.48), rgba(255, 251, 252, 0.70));
}

.qr-template__subtitle {
    font-family: var(--qr-body-font);
    font-size: 0.92rem;
    font-weight: 800;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgb(111 70 83 / 0.78);
}

.qr-template__title {
    margin-top: 0.45rem;
    font-family: var(--qr-heading-font);
    font-size: clamp(4.3rem, 14vw, 7rem);
    font-weight: 600;
    line-height: 0.86;
    letter-spacing: -0.05em;
}

.qr-template__slogan {
    margin-top: 0.4rem;
    font-family: var(--qr-body-font);
    font-size: clamp(1.05rem, 2.7vw, 1.45rem);
    line-height: 1.4;
    color: rgb(98 63 75 / 0.82);
}

.qr-template__qr-frame {
    border-radius: 1.7rem;
    background: rgb(255 255 255 / 0.95);
    padding: 0.65rem;
    box-shadow: 0 24px 52px rgba(84, 46, 58, 0.12);
}

.qr-template__message {
    max-width: 34rem;
    white-space: pre-line;
    font-family: var(--qr-body-font);
    font-size: clamp(1rem, 2.5vw, 1.15rem);
    line-height: 1.75;
    color: rgb(59 37 46 / 0.84);
    text-wrap: pretty;
}
</style>
