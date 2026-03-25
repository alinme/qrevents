<script setup lang="ts">
import { CalendarDays, MapPin, Phone } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    invitationTemplateMap,
    type InvitationTemplateId,
} from '@/lib/invitation-templates';

const props = withDefaults(defineProps<{
    template: InvitationTemplateId;
    eventName: string;
    guestLabel?: string | null;
    headline: string;
    message: string;
    closing: string;
    contactPhone?: string | null;
    dateLabel?: string | null;
    venueAddress?: string | null;
    logoUrl?: string | null;
    mode?: 'preview' | 'live';
}>(), {
    guestLabel: null,
    contactPhone: null,
    dateLabel: null,
    venueAddress: null,
    logoUrl: null,
    mode: 'live',
});

const invitationArtwork = computed(() => invitationTemplateMap[props.template]);

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
</script>

<template>
    <div
        :class="[
            'relative overflow-hidden rounded-[30px] border p-3 shadow-sm sm:p-4',
            invitationTemplateVisuals.surfaceClass,
            isPreviewMode ? 'shadow-sm' : 'shadow-xl',
        ]"
        :style="invitationPaperStyle"
    >
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-black/10 via-white/30 to-black/10 opacity-70" />
            <div class="absolute -right-20 top-12 h-48 w-48 rounded-full bg-white/20 blur-3xl" />
            <div class="absolute bottom-0 left-0 h-40 w-40 rounded-full bg-black/5 blur-2xl" />
        </div>

        <div class="relative aspect-[210/297] overflow-hidden rounded-[24px] border border-current/10 bg-white/10">
            <div class="flex h-full flex-col px-[10.5%] py-[11%] text-center">
                <div class="flex items-center justify-center gap-3">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        alt=""
                        class="h-10 w-10 rounded-[16px] border border-current/10 object-contain p-2 shadow-sm"
                    >
                    <div class="space-y-1">
                        <p :class="['text-[0.68rem] font-semibold uppercase tracking-[0.34em]', mutedTextClass]">
                            {{ eventName }}
                        </p>
                        <p v-if="guestLabel" :class="['text-sm', mutedTextClass]">
                            {{ guestLabel }}
                        </p>
                    </div>
                </div>

                <div class="my-auto space-y-5">
                    <h2
                        :class="[
                            'mx-auto max-w-[12ch] font-semibold tracking-tight',
                            template !== 'midnight' ? 'font-serif' : '',
                            isPreviewMode ? 'text-[1.7rem] sm:text-[2rem]' : 'text-[2rem] sm:text-[3rem]',
                        ]"
                    >
                        {{ headline }}
                    </h2>
                    <p
                        :class="[
                            'mx-auto leading-7',
                            mutedTextClass,
                            isPreviewMode ? 'max-w-[24ch] text-sm sm:text-[0.95rem]' : 'max-w-[26ch] text-[0.95rem] sm:text-lg',
                        ]"
                    >
                        {{ message }}
                    </p>
                </div>

                <div class="space-y-4 border-t border-current/10 pt-5">
                    <div
                        v-if="dateLabel || venueAddress || contactPhone"
                        :class="['flex flex-col items-center gap-3 text-sm', mutedTextClass]"
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
                        :class="[
                            'mx-auto max-w-[24ch] leading-6',
                            mutedTextClass,
                            isPreviewMode ? 'text-sm' : 'text-sm',
                        ]"
                    >
                        {{ closing }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
