<script setup lang="ts">
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';
import { resolveInvitationSheetTheme } from '@/lib/invitation-sheet-themes';

const props = withDefaults(defineProps<{
    template?: string | null;
    guestLabel?: string | null;
    logoUrl?: string | null;
    leadIn: string;
    title: string;
    message: string;
    closing: string;
    detailLines?: string[];
    dateLabel?: string | null;
    venueAddress?: string | null;
    contactPhone?: string | null;
}>(), {
    template: 'canva_cream',
    guestLabel: null,
    logoUrl: null,
    detailLines: () => [],
    dateLabel: null,
    venueAddress: null,
    contactPhone: null,
});

const { t } = useTranslations();

const theme = computed(() => resolveInvitationSheetTheme(props.template));

const footerItems = computed(() => {
    return [
        props.dateLabel
            ? {
                key: 'date',
                label: t('invitations.event_date'),
                value: props.dateLabel,
            }
            : null,
        props.venueAddress
            ? {
                key: 'venue',
                label: t('invitations.venue'),
                value: props.venueAddress,
            }
            : null,
        props.contactPhone
            ? {
                key: 'contact',
                label: t('guests.invitation.contact_phone'),
                value: props.contactPhone,
            }
            : null,
    ].filter((item): item is { key: string; label: string; value: string } => item !== null);
});

const sheetStyle = computed<Record<string, string>>(() => ({
    '--invitation-bg-image': `url(${theme.value.backgroundUrl})`,
    '--invitation-overlay': theme.value.overlay,
    '--invitation-paper': theme.value.paperTint,
    '--invitation-ink': theme.value.inkColor,
    '--invitation-accent': theme.value.accentColor,
    '--invitation-chip-bg': theme.value.chipBackground,
    '--invitation-chip-ink': theme.value.chipColor,
}));
</script>

<template>
    <article
        class="invitation-sheet relative isolate aspect-[1/1.4142] h-full w-auto max-w-full overflow-hidden rounded-[2rem] border border-black/8 shadow-[0_28px_80px_rgba(40,24,12,0.14)]"
        :style="sheetStyle"
    >
        <div class="invitation-sheet__art absolute inset-0" />
        <div class="invitation-sheet__wash absolute inset-0" />

        <div class="relative z-10 flex h-full flex-col px-[8.6%] py-[8.2%] text-center text-[var(--invitation-ink)]">
            <header class="flex items-start justify-between gap-[2.4cqw]">
                <div class="min-h-[4cqw]">
                    <span
                        v-if="guestLabel"
                        class="invitation-sheet__chip inline-flex items-center rounded-full px-[2.2cqw] py-[0.95cqw]"
                    >
                        {{ guestLabel }}
                    </span>
                </div>

                <img
                    v-if="logoUrl"
                    :src="logoUrl"
                    alt=""
                    class="h-[8.5cqw] max-w-[18cqw] object-contain opacity-80"
                >
            </header>

            <div class="flex flex-1 flex-col justify-center">
                <div class="mx-auto w-full max-w-[73cqw] space-y-[2.8cqw]">
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
                class="mx-auto grid w-full max-w-[82cqw] gap-[2.6cqw] border-t border-[color:color-mix(in_srgb,var(--invitation-accent)_22%,transparent)] pt-[3.2cqw]"
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
    background-image: var(--invitation-bg-image);
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    filter: saturate(0.98);
}

.invitation-sheet__wash {
    background:
        radial-gradient(circle at top, rgba(255, 255, 255, 0.35), transparent 42%),
        var(--invitation-overlay);
}

.invitation-sheet__chip {
    background: var(--invitation-chip-bg);
    color: var(--invitation-chip-ink);
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.5rem, calc(var(--invitation-unit) * 1.3), 0.88rem);
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.invitation-sheet__lead {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.62rem, calc(var(--invitation-unit) * 1.7), 1.04rem);
    font-weight: 600;
    letter-spacing: clamp(0.08em, calc(var(--invitation-unit) * 0.08), 0.18em);
    text-transform: uppercase;
}

.invitation-sheet__title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.55rem, calc(var(--invitation-unit) * 6.7), 4.8rem);
    font-weight: 600;
    letter-spacing: 0.02em;
    line-height: 0.9;
    text-wrap: balance;
}

.invitation-sheet__detail {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.58rem, calc(var(--invitation-unit) * 1.5), 0.96rem);
    line-height: 1.45;
    opacity: 0.82;
}

.invitation-sheet__message {
    margin: 0 auto;
    max-width: 62cqw;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.64rem, calc(var(--invitation-unit) * 1.74), 1.08rem);
    line-height: 1.58;
    text-wrap: pretty;
}

.invitation-sheet__closing {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(0.8rem, calc(var(--invitation-unit) * 2.08), 1.42rem);
    font-style: italic;
    line-height: 1.35;
    color: color-mix(in srgb, var(--invitation-accent) 88%, var(--invitation-ink) 12%);
}

.invitation-sheet__footer-label {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.48rem, calc(var(--invitation-unit) * 1.16), 0.78rem);
    font-weight: 700;
    letter-spacing: clamp(0.08em, calc(var(--invitation-unit) * 0.08), 0.14em);
    text-transform: uppercase;
    color: color-mix(in srgb, var(--invitation-accent) 88%, var(--invitation-ink) 12%);
}

.invitation-sheet__footer-value {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(0.62rem, calc(var(--invitation-unit) * 1.58), 0.94rem);
    line-height: 1.45;
}
</style>
