<script setup lang="ts">
import { computed } from 'vue';
import { buildInvitationFooterItems } from '@/components/invitations/invitation-sheet-content';
import type { InvitationSheetProps } from '@/components/invitations/types';
import { useTranslations } from '@/composables/useTranslations';

const props = withDefaults(defineProps<InvitationSheetProps>(), {
    guestLabel: null,
    logoUrl: null,
    detailLines: () => [],
    dateLabel: null,
    venueAddress: null,
    contactPhone: null,
});

const { t } = useTranslations();

const footerItems = computed(() => buildInvitationFooterItems(t, props));
</script>

<template>
    <article
        class="invitation-sheet invitation-sheet-brown relative isolate aspect-[1/1.4142] h-full w-auto max-w-full overflow-hidden rounded-[2rem] border border-black/8 shadow-[0_30px_90px_rgba(49,27,18,0.16)]"
    >
        <div class="invitation-sheet__art absolute inset-0" />
        <div class="invitation-sheet__wash absolute inset-0" />

        <div class="relative z-10 flex h-full flex-col px-[9%] py-[8%] text-[#3f2b20]">
            <header class="flex items-start justify-between gap-[2cqw]">
                <span
                    v-if="guestLabel"
                    class="invitation-sheet__chip inline-flex items-center rounded-full px-[2.2cqw] py-[0.95cqw]"
                >
                    {{ guestLabel }}
                </span>

                <img
                    v-if="logoUrl"
                    :src="logoUrl"
                    alt=""
                    class="h-[8cqw] max-w-[17cqw] object-contain opacity-80"
                >
            </header>

            <div class="flex flex-1 flex-col justify-center">
                <div class="w-full max-w-[68cqw] space-y-[2.4cqw]">
                    <p class="invitation-sheet__lead">
                        {{ leadIn }}
                    </p>

                    <h1 class="invitation-sheet__title">
                        {{ title }}
                    </h1>

                    <div v-if="detailLines.length > 0" class="space-y-[0.7cqw]">
                        <p
                            v-for="detailLine in detailLines"
                            :key="detailLine"
                            class="invitation-sheet__detail"
                        >
                            {{ detailLine }}
                        </p>
                    </div>

                    <p v-if="message" class="invitation-sheet__message whitespace-pre-line">
                        {{ message }}
                    </p>

                    <p v-if="closing" class="invitation-sheet__closing whitespace-pre-line">
                        {{ closing }}
                    </p>
                </div>
            </div>

            <footer
                v-if="footerItems.length > 0"
                class="grid w-full max-w-[74cqw] gap-[2.2cqw] border-t border-[#8c6248]/20 pt-[2.8cqw]"
            >
                <div
                    v-for="item in footerItems"
                    :key="item.key"
                    class="space-y-[0.7cqw]"
                >
                    <p class="invitation-sheet__footer-label">
                        {{ item.label }}
                    </p>
                    <p class="invitation-sheet__footer-value whitespace-pre-line">
                        {{ item.value }}
                    </p>
                </div>
            </footer>
        </div>
    </article>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&display=swap');

.invitation-sheet {
    container-type: size;
    --invitation-unit: min(1cqw, 1cqh);
}

.invitation-sheet__art {
    background:
        center / cover no-repeat url('/invitation-templates/canva/brown/base.png'),
        #f7efe8;
    filter: saturate(0.98);
}

.invitation-sheet__wash {
    background:
        radial-gradient(circle at top left, rgba(255, 255, 255, 0.32), transparent 46%),
        linear-gradient(180deg, rgba(244, 236, 228, 0.74), rgba(250, 245, 240, 0.86));
}

.invitation-sheet__chip {
    background: rgba(255, 247, 241, 0.7);
    color: #5b3d2c;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.5rem, calc(var(--invitation-unit) * 1.24), 0.84rem);
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.invitation-sheet__lead {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.58rem, calc(var(--invitation-unit) * 1.55), 0.96rem);
    font-weight: 600;
    letter-spacing: clamp(0.08em, calc(var(--invitation-unit) * 0.08), 0.16em);
    text-transform: uppercase;
}

.invitation-sheet__title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.48rem, calc(var(--invitation-unit) * 6.2), 4.4rem);
    font-weight: 600;
    letter-spacing: 0.01em;
    line-height: 0.92;
    text-wrap: balance;
}

.invitation-sheet__detail {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.56rem, calc(var(--invitation-unit) * 1.38), 0.9rem);
    line-height: 1.42;
    opacity: 0.82;
}

.invitation-sheet__message {
    max-width: 56cqw;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.62rem, calc(var(--invitation-unit) * 1.62), 1rem);
    line-height: 1.56;
    text-wrap: pretty;
}

.invitation-sheet__closing {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(0.82rem, calc(var(--invitation-unit) * 2.02), 1.34rem);
    font-style: italic;
    line-height: 1.32;
    color: color-mix(in srgb, #8c6248 88%, #3f2b20 12%);
}

.invitation-sheet__footer-label {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.46rem, calc(var(--invitation-unit) * 1.08), 0.74rem);
    font-weight: 700;
    letter-spacing: clamp(0.08em, calc(var(--invitation-unit) * 0.08), 0.14em);
    text-transform: uppercase;
    color: color-mix(in srgb, #8c6248 88%, #3f2b20 12%);
}

.invitation-sheet__footer-value {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.6rem, calc(var(--invitation-unit) * 1.48), 0.9rem);
    line-height: 1.42;
}
</style>
