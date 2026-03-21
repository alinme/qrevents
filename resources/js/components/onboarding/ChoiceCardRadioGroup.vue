<script setup lang="ts">
import { computed } from 'vue';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

type ChoiceCardOption = {
    value: string;
    label: string;
    description: string;
    imageUrl: string;
    eyebrow?: string;
};

const props = defineProps<{
    modelValue: string;
    options: ChoiceCardOption[];
    columnsClass?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const selectedValue = computed({
    get: () => props.modelValue,
    set: (value: string | undefined) => {
        if (typeof value === 'string') {
            emit('update:modelValue', value);
        }
    },
});

const isSelected = (value: string): boolean => selectedValue.value === value;
</script>

<template>
    <RadioGroup
        v-model="selectedValue"
        :class="columnsClass ?? 'grid gap-5 xl:grid-cols-2'"
    >
        <label
            v-for="option in options"
            :key="option.value"
            class="group block cursor-pointer"
        >
            <RadioGroupItem
                :value="option.value"
                class="sr-only"
            />

            <div
                class="relative overflow-hidden rounded-[28px] border transition duration-200 hover:-translate-y-0.5"
                :class="
                    isSelected(option.value)
                        ? 'border-promo-primary shadow-[0_20px_50px_rgba(232,79,154,0.16)]'
                        : 'border-neutral-300 shadow-[0_12px_34px_rgba(17,24,39,0.06)]'
                "
            >
                <div
                    class="absolute inset-0 bg-cover bg-center transition duration-300"
                    :class="isSelected(option.value) ? 'grayscale-0 saturate-110' : 'grayscale contrast-110'"
                    :style="{ backgroundImage: `url(${option.imageUrl})` }"
                />
                <div
                    class="absolute inset-0 transition duration-300"
                    :class="
                        isSelected(option.value)
                            ? 'bg-linear-to-t from-[rgba(61,19,45,0.88)] via-[rgba(61,19,45,0.26)] to-[rgba(255,255,255,0.04)]'
                            : 'bg-linear-to-t from-[rgba(17,17,17,0.82)] via-[rgba(17,17,17,0.38)] to-[rgba(255,255,255,0.08)]'
                    "
                />

                <div
                    class="absolute inset-x-5 top-5 flex items-center justify-between"
                >
                    <span
                        class="rounded-full px-3 py-1 text-[0.68rem] font-semibold uppercase tracking-[0.2em] backdrop-blur-sm transition duration-300"
                        :class="
                            isSelected(option.value)
                                ? 'bg-white/18 text-white'
                                : 'bg-white/88 text-neutral-900'
                        "
                    >
                        {{ option.eyebrow ?? 'Event type' }}
                    </span>

                    <span
                        class="size-3 rounded-full border transition duration-300"
                        :class="
                            isSelected(option.value)
                                ? 'border-promo-primary bg-promo-primary shadow-[0_0_0_4px_rgba(255,255,255,0.22)]'
                                : 'border-white/95 bg-white/85'
                        "
                    />
                </div>

                <div class="relative flex min-h-64 flex-col justify-end p-6 text-white">
                    <div>
                        <h3 class="text-2xl font-extrabold tracking-[-0.04em]">
                            {{ option.label }}
                        </h3>
                        <p
                            class="mt-3 max-w-sm text-sm leading-6 transition duration-300"
                            :class="isSelected(option.value) ? 'text-white/86' : 'text-white/80'"
                        >
                            {{ option.description }}
                        </p>
                    </div>
                </div>
            </div>
        </label>
    </RadioGroup>
</template>
