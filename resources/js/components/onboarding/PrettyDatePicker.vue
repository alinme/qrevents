<script setup lang="ts">
import {
    CalendarDate,
    getLocalTimeZone,
    parseDate,
    today,
} from '@internationalized/date';
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import type { DateValue } from 'reka-ui';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps<{
    id?: string;
    modelValue: string;
    placeholder?: string;
}>();

const { t } = useTranslations();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const open = ref(false);
const calendarValue = ref<DateValue | undefined>();

const displayValue = computed(() => {
    if (props.modelValue.trim() === '') {
        return props.placeholder ?? t('onboarding.datepicker.placeholder');
    }

    const parsedDate = new Date(`${props.modelValue}T00:00:00`);

    return new Intl.DateTimeFormat('en-GB', {
        weekday: 'short',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(parsedDate);
});

watch(
    () => props.modelValue,
    (value) => {
        calendarValue.value =
            value.trim() !== '' ? parseDate(value) : undefined;
    },
    { immediate: true },
);

const updateValue = (value: DateValue | undefined): void => {
    calendarValue.value = value;

    if (value === undefined) {
        emit('update:modelValue', '');

        return;
    }

    const nextDate = `${value.year}-${String(value.month).padStart(2, '0')}-${String(value.day).padStart(2, '0')}`;
    emit('update:modelValue', nextDate);
    open.value = false;
};

const pickToday = (): void => {
    const value = today(getLocalTimeZone());
    updateValue(new CalendarDate(value.year, value.month, value.day));
};

const clearValue = (): void => {
    calendarValue.value = undefined;
    emit('update:modelValue', '');
    open.value = false;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <button
                :id="props.id"
                type="button"
                class="group flex h-12 w-full items-center justify-between rounded-[18px] border border-promo-line bg-white px-4 text-left transition-colors duration-200 hover:border-promo-primary/50 focus-visible:border-promo-primary focus-visible:outline-none"
            >
                <span class="inline-flex min-w-0 items-center gap-3">
                    <span
                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-promo-surface text-promo-primary"
                    >
                        <CalendarDays class="size-4.5" />
                    </span>
                    <span class="min-w-0">
                        <span
                            class="block text-[0.68rem] font-semibold tracking-[0.18em] text-promo-primary uppercase"
                        >
                            {{ t('onboarding.datepicker.label') }}
                        </span>
                        <span
                            class="block truncate text-sm font-medium"
                            :class="
                                props.modelValue.trim() !== ''
                                    ? 'text-promo-ink'
                                    : 'text-promo-muted'
                            "
                        >
                            {{ displayValue }}
                        </span>
                    </span>
                </span>

                <span
                    class="text-xs font-semibold tracking-[0.18em] text-promo-muted uppercase"
                >
                    {{ t('onboarding.datepicker.open') }}
                </span>
            </button>
        </PopoverTrigger>

        <PopoverContent
            align="start"
            :side-offset="10"
            class="w-[22rem] rounded-[28px] border-promo-line bg-white p-0 shadow-[0_24px_70px_rgba(232,79,154,0.14)]"
        >
            <div
                class="border-b border-promo-line bg-promo-surface/55 px-5 py-4"
            >
                <p
                    class="text-[0.68rem] font-semibold tracking-[0.2em] text-promo-primary uppercase"
                >
                    {{ t('onboarding.datepicker.heading') }}
                </p>
                <p class="mt-2 text-sm text-promo-muted">
                    {{ t('onboarding.datepicker.description') }}
                </p>
            </div>

            <div class="p-4">
                <Calendar
                    :model-value="calendarValue"
                    :default-placeholder="
                        calendarValue ?? today(getLocalTimeZone())
                    "
                    :weekday-format="'short'"
                    layout="month-and-year"
                    initial-focus
                    class="rounded-[24px] border border-promo-line bg-promo-surface/35 p-4 [&_[data-slot=calendar-cell-trigger]]:size-10 [&_[data-slot=calendar-cell-trigger]]:rounded-full [&_[data-slot=calendar-cell-trigger]]:text-sm [&_[data-slot=calendar-cell-trigger]]:font-medium [&_[data-slot=calendar-cell-trigger]]:text-promo-ink [&_[data-slot=calendar-cell-trigger][data-selected]]:bg-promo-primary [&_[data-slot=calendar-cell-trigger][data-selected]]:text-white [&_[data-slot=calendar-cell-trigger][data-today]:not([data-selected])]:bg-white [&_[data-slot=calendar-cell-trigger][data-today]:not([data-selected])]:text-promo-primary [&_[data-slot=calendar-grid]]:w-full [&_[data-slot=calendar-head-cell]]:text-[0.68rem] [&_[data-slot=calendar-head-cell]]:font-semibold [&_[data-slot=calendar-head-cell]]:tracking-[0.18em] [&_[data-slot=calendar-head-cell]]:text-promo-muted [&_[data-slot=calendar-head-cell]]:uppercase [&_[data-slot=calendar-header]]:relative [&_[data-slot=calendar-header]]:mb-5 [&_[data-slot=calendar-heading]]:text-base [&_[data-slot=calendar-heading]]:font-semibold [&_[data-slot=calendar-heading]]:text-promo-ink [&_[data-slot=calendar-next-button]]:size-9 [&_[data-slot=calendar-next-button]]:rounded-full [&_[data-slot=calendar-next-button]]:border-promo-line [&_[data-slot=calendar-next-button]]:bg-white [&_[data-slot=calendar-next-button]]:opacity-100 [&_[data-slot=calendar-prev-button]]:size-9 [&_[data-slot=calendar-prev-button]]:rounded-full [&_[data-slot=calendar-prev-button]]:border-promo-line [&_[data-slot=calendar-prev-button]]:bg-white [&_[data-slot=calendar-prev-button]]:opacity-100"
                    @update:model-value="updateValue"
                >
                    <template #calendar-prev-icon>
                        <ChevronLeft class="size-4 text-promo-ink" />
                    </template>
                    <template #calendar-next-icon>
                        <ChevronRight class="size-4 text-promo-ink" />
                    </template>
                </Calendar>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        class="rounded-full px-4 text-promo-muted"
                        @click="clearValue"
                    >
                        {{ t('onboarding.datepicker.clear') }}
                    </Button>

                    <Button
                        type="button"
                        class="rounded-full bg-promo-primary px-4 text-white hover:bg-promo-primary-strong"
                        @click="pickToday"
                    >
                        {{ t('onboarding.datepicker.today') }}
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
