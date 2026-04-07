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
    <article :style="fontVariables" class="qr-template qr-template-pink-landscape relative mx-auto aspect-[1.4142/1] h-full max-h-full w-full max-w-full overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(76,41,52,0.15)]">
        <div class="qr-template__art absolute inset-0" />
        <div class="qr-template__wash absolute inset-0" />

        <div class="relative z-10 grid h-full grid-cols-[minmax(0,1fr)_19rem] gap-8 px-10 py-8 text-left text-[#38232d] sm:px-14 sm:py-10">
            <div class="flex h-full flex-col justify-between">
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

                <div class="space-y-6">
                    <p class="qr-template__message">
                        {{ message }}
                    </p>

                    <footer class="qr-template__footer w-full max-w-[34rem] border-t border-[#7a5260]/16 pt-4">
                        <p class="text-[1.7rem] font-semibold tracking-[0.03em]">
                            {{ eventTitle }}
                        </p>
                    </footer>
                </div>
            </div>

            <div class="flex h-full items-center justify-center">
                <div class="qr-template__qr-frame w-full">
                    <img :src="qrDataUrl" :alt="previewAlt" class="block h-auto w-full">
                </div>
            </div>
        </div>
    </article>
</template>

<style scoped>
.qr-template__art {
    background:
        center / cover no-repeat url('/qr-bg-themes/pink-landscape-base.png'),
        #f7e8ed;
}

.qr-template__wash {
    background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.3), transparent 42%),
        linear-gradient(180deg, rgba(255, 247, 250, 0.42), rgba(255, 251, 252, 0.64));
}

.qr-template__subtitle {
    font-family: var(--qr-body-font);
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: rgb(111 70 83 / 0.78);
}

.qr-template__title {
    margin-top: 0.5rem;
    font-family: var(--qr-heading-font);
    font-size: clamp(5rem, 8vw, 6.8rem);
    font-weight: 600;
    line-height: 0.84;
    letter-spacing: -0.05em;
}

.qr-template__slogan {
    margin-top: 0.65rem;
    font-family: var(--qr-body-font);
    font-size: clamp(1.2rem, 2.4vw, 1.6rem);
    line-height: 1.45;
    color: rgb(98 63 75 / 0.82);
}

.qr-template__qr-frame {
    border-radius: 1.7rem;
    background: rgb(255 255 255 / 0.95);
    padding: 0.75rem;
    box-shadow: 0 24px 52px rgba(84, 46, 58, 0.12);
}

.qr-template__message {
    max-width: 42rem;
    white-space: pre-line;
    font-family: var(--qr-body-font);
    font-size: clamp(1.05rem, 1.8vw, 1.35rem);
    line-height: 1.78;
    color: rgb(59 37 46 / 0.84);
    text-wrap: pretty;
}
</style>
