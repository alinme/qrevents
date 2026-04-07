<script setup lang="ts">
import type { QrTemplateProps } from './types';

defineProps<QrTemplateProps>();
</script>

<template>
    <article class="qr-template qr-template-beige relative mx-auto aspect-[1/1.4142] h-full max-h-[calc(100vh-15rem)] w-full max-w-[760px] overflow-hidden rounded-[2rem] shadow-[0_34px_80px_rgba(53,36,24,0.16)]">
        <div class="qr-template__art absolute inset-0" />
        <div class="qr-template__wash absolute inset-0" />

        <div class="relative z-10 flex h-full flex-col items-center justify-between px-8 py-10 text-center text-[#2f211a] sm:px-12 sm:py-12">
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

            <div class="flex w-full flex-col items-center gap-5 sm:gap-7">
                <div class="qr-template__qr-frame w-full max-w-[18rem] sm:max-w-[20rem]">
                    <img :src="qrDataUrl" :alt="previewAlt" class="block h-auto w-full">
                </div>

                <p class="qr-template__message">
                    {{ message }}
                </p>
            </div>

            <footer class="qr-template__footer w-full max-w-[40rem] border-t border-[#50382c]/15 pt-4 sm:pt-5">
                <p class="text-xl font-bold tracking-[0.01em] sm:text-[1.75rem]">
                    {{ eventTitle }}
                </p>
            </footer>
        </div>
    </article>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap');

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
    font-family: 'Manrope', sans-serif;
    font-size: 0.95rem;
    font-weight: 800;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: rgb(81 57 45 / 0.75);
}

.qr-template__title {
    margin-top: 0.5rem;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(4.6rem, 15vw, 7.5rem);
    font-weight: 600;
    line-height: 0.88;
    letter-spacing: -0.04em;
}

.qr-template__slogan {
    margin-top: 0.5rem;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(1.1rem, 3vw, 1.6rem);
    line-height: 1.45;
    color: rgb(75 52 41 / 0.8);
}

.qr-template__qr-frame {
    border-radius: 1.6rem;
    background: rgb(255 255 255 / 0.94);
    padding: 0.7rem;
    box-shadow: 0 24px 52px rgba(60, 38, 30, 0.12);
}

.qr-template__message {
    max-width: 38rem;
    white-space: pre-line;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(1rem, 2.7vw, 1.2rem);
    line-height: 1.75;
    color: rgb(56 38 29 / 0.84);
    text-wrap: pretty;
}
</style>
