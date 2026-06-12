<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { QrCode } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Button } from '@/components/ui/button';

const { t } = useTranslations();

const props = defineProps<{
    submitUrl: string;
    homeUrl: string;
    segmentCount: number;
    entryShortcutUrl: string;
    defaultTarget: 'album' | 'wall';
}>();

const segmentRefs = ref<Array<HTMLInputElement | null>>([]);
const segments = ref<string[]>(
    Array.from({ length: props.segmentCount }, () => ''),
);
const lastSubmittedCode = ref<string>('');
const form = useForm({
    code: '',
    target: props.defaultTarget,
});

const isComplete = computed(() =>
    segments.value.every((segment) => segment.length === 1),
);

const normalizeCode = (value: string): string => {
    const albumMatch = value.match(/\/a\/([A-Za-z0-9]+)/i);
    const source = albumMatch?.[1] ?? value;

    return source
        .replace(/[^A-Za-z0-9]/g, '')
        .toUpperCase()
        .slice(0, props.segmentCount);
};

const focusSegment = (index: number): void => {
    nextTick(() => {
        segmentRefs.value[index]?.focus();
        segmentRefs.value[index]?.select();
    });
};

const fillSegmentsFromCode = (value: string): void => {
    const normalized = normalizeCode(value);

    for (let index = 0; index < props.segmentCount; index += 1) {
        segments.value[index] = normalized[index] ?? '';
    }
};

const syncFormCode = (): void => {
    form.code = segments.value.join('');
};

const submit = (): void => {
    if (
        !isComplete.value ||
        form.processing ||
        form.code === lastSubmittedCode.value
    ) {
        return;
    }

    lastSubmittedCode.value = form.code;

    form.post(props.submitUrl, {
        preserveScroll: true,
        preserveState: true,
        onError: () => {
            lastSubmittedCode.value = '';
            const firstIncompleteIndex = segments.value.findIndex(
                (segment) => segment.length < 1,
            );
            focusSegment(
                firstIncompleteIndex === -1 ? 0 : firstIncompleteIndex,
            );
        },
    });
};

const handleInput = (index: number, event: Event): void => {
    const target = event.target as HTMLInputElement;
    const value = normalizeCode(target.value).slice(0, 1);

    segments.value[index] = value;

    if (value.length === 1 && index < props.segmentCount - 1) {
        focusSegment(index + 1);
    }
};

const handleKeydown = (index: number, event: KeyboardEvent): void => {
    if (
        event.key === 'Backspace' &&
        segments.value[index] === '' &&
        index > 0
    ) {
        focusSegment(index - 1);

        return;
    }

    if (event.key === 'ArrowLeft' && index > 0) {
        event.preventDefault();
        focusSegment(index - 1);

        return;
    }

    if (event.key === 'ArrowRight' && index < props.segmentCount - 1) {
        event.preventDefault();
        focusSegment(index + 1);
    }
};

const handlePaste = (event: ClipboardEvent): void => {
    const pastedCode = normalizeCode(
        event.clipboardData?.getData('text') ?? '',
    );

    if (pastedCode === '') {
        return;
    }

    event.preventDefault();
    fillSegmentsFromCode(pastedCode);

    const firstIncompleteIndex = segments.value.findIndex(
        (segment) => segment.length < 1,
    );
    focusSegment(
        firstIncompleteIndex === -1
            ? props.segmentCount - 1
            : firstIncompleteIndex,
    );
};

const setTarget = (target: 'album' | 'wall'): void => {
    form.target = target;
    lastSubmittedCode.value = '';

    if (isComplete.value) {
        submit();
    }
};

watch(
    segments,
    () => {
        syncFormCode();
        form.clearErrors('code');

        if (isComplete.value) {
            submit();
        }
    },
    { deep: true },
);
</script>

<template>
    <Head :title="t('public.album_access.head_title')" />

    <main
        class="min-h-svh bg-[linear-gradient(180deg,oklch(0.989_0.01_338)_0%,oklch(0.982_0.012_338)_58%,oklch(0.989_0.006_28)_100%)] px-4 py-6 text-promo-ink sm:px-6"
    >
        <div
            class="mx-auto flex min-h-[calc(100svh-3rem)] max-w-3xl flex-col items-center justify-center"
        >
            <div class="w-full max-w-xl text-center">
                <Link
                    :href="homeUrl"
                    class="mx-auto inline-flex items-center gap-3"
                >
                    <div class="text-left">
                        <img
                            src="/logo.png?v=20260329-6"
                            alt="EventSmart"
                            width="154"
                            height="45"
                            class="h-9 w-auto object-contain"
                        />
                        <p
                            class="text-lg font-extrabold tracking-[-0.04em] text-promo-ink"
                        >
                            {{ t('public.album_access.brand_line') }}
                        </p>
                    </div>
                </Link>

                <div class="mt-8 space-y-3">
                    <div
                        class="mx-auto inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-[0.75rem] font-semibold tracking-[0.16em] text-promo-primary uppercase shadow-[0_10px_24px_rgba(232,79,154,0.08)]"
                    >
                        <QrCode class="size-4" />
                        {{ t('public.album_access.badge') }}
                    </div>
                    <h1
                        class="text-3xl font-extrabold tracking-[-0.05em] text-promo-ink sm:text-4xl"
                    >
                        {{ t('public.album_access.title') }}
                    </h1>
                    <p
                        class="mx-auto max-w-lg text-sm leading-7 text-promo-muted sm:text-base"
                    >
                        {{ t('public.album_access.description') }}
                    </p>
                </div>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <input type="hidden" name="code" :value="form.code" />
                    <input type="hidden" name="target" :value="form.target" />

                    <div
                        class="mx-auto grid max-w-sm grid-cols-2 gap-2 rounded-full bg-white p-1 shadow-[0_10px_24px_rgba(232,79,154,0.08)]"
                    >
                        <button
                            type="button"
                            class="rounded-full px-4 py-3 text-sm font-semibold transition"
                            :class="
                                form.target === 'album'
                                    ? 'bg-promo-primary text-white'
                                    : 'text-promo-muted hover:text-promo-ink'
                            "
                            @click="setTarget('album')"
                        >
                            {{ t('public.album_access.target_album') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full px-4 py-3 text-sm font-semibold transition"
                            :class="
                                form.target === 'wall'
                                    ? 'bg-promo-primary text-white'
                                    : 'text-promo-muted hover:text-promo-ink'
                            "
                            @click="setTarget('wall')"
                        >
                            {{ t('public.album_access.target_wall') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <label
                            v-for="(_, index) in segments"
                            :key="`segment-${index}`"
                            class="space-y-2"
                        >
                            <span
                                class="block text-[0.72rem] font-semibold tracking-[0.16em] text-promo-muted uppercase"
                            >
                                {{
                                    t('public.album_access.segment_label', {
                                        number: String(index + 1),
                                    })
                                }}
                            </span>
                            <input
                                :ref="
                                    (element) => {
                                        segmentRefs[index] =
                                            element as HTMLInputElement | null;
                                    }
                                "
                                :value="segments[index]"
                                :autocomplete="
                                    index === 0 ? 'one-time-code' : 'off'
                                "
                                :autofocus="index === 0"
                                autocapitalize="characters"
                                inputmode="text"
                                maxlength="1"
                                spellcheck="false"
                                class="h-20 w-full rounded-[1.35rem] border border-promo-line bg-white px-0 text-center text-4xl font-semibold tracking-[0.08em] text-promo-ink uppercase shadow-[0_14px_30px_rgba(232,79,154,0.06)] transition outline-none focus:border-promo-primary focus:ring-4 focus:ring-promo-primary/10 sm:h-24 sm:text-5xl"
                                @input="handleInput(index, $event)"
                                @keydown="handleKeydown(index, $event)"
                                @paste="handlePaste"
                            />
                        </label>
                    </div>

                    <InputError
                        :message="form.errors.code"
                        class="text-center"
                    />

                    <div class="space-y-3">
                        <Button
                            type="submit"
                            class="h-12 w-full rounded-full bg-promo-primary text-white hover:bg-promo-primary-strong sm:h-14"
                            :disabled="form.processing || !isComplete"
                        >
                            {{
                                form.processing
                                    ? t('public.album_access.submitting')
                                    : t('public.album_access.submit')
                            }}
                        </Button>

                        <p class="text-sm text-promo-muted">
                            {{
                                t('public.album_access.no_qr', {
                                    url: entryShortcutUrl,
                                })
                            }}
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>
