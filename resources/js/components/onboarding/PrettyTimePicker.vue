<script setup lang="ts">
import { Clock3 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';

const props = withDefaults(defineProps<{
    id?: string;
    modelValue: string;
    placeholder?: string;
}>(), {
    placeholder: 'Pick a time',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const open = ref(false);
const quickTimes = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
const hours = Array.from({ length: 24 }, (_, index) => String(index).padStart(2, '0'));
const minutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

const selectedHour = computed(() => props.modelValue.split(':')[0] ?? '');
const selectedMinute = computed(() => props.modelValue.split(':')[1] ?? '');

const displayValue = computed(() => {
    if (props.modelValue.trim() === '') {
        return props.placeholder;
    }

    const [hourValue, minuteValue] = props.modelValue.split(':');
    const hour = Number(hourValue);
    const suffix = hour >= 12 ? 'PM' : 'AM';
    const normalizedHour = hour % 12 === 0 ? 12 : hour % 12;

    return `${normalizedHour}:${minuteValue} ${suffix}`;
});

const commitTime = (hour: string, minute: string): void => {
    emit('update:modelValue', `${hour}:${minute}`);
};

const selectHour = (hour: string): void => {
    commitTime(hour, selectedMinute.value !== '' ? selectedMinute.value : '00');
};

const selectMinute = (minute: string): void => {
    commitTime(selectedHour.value !== '' ? selectedHour.value : '12', minute);
};

const applyQuickTime = (value: string): void => {
    emit('update:modelValue', value);
    open.value = false;
};

const clearValue = (): void => {
    emit('update:modelValue', '');
    open.value = false;
};

const done = (): void => {
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
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-promo-surface text-promo-primary">
                        <Clock3 class="size-4.5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                            Time
                        </span>
                        <span
                            class="block truncate text-sm font-medium"
                            :class="props.modelValue.trim() !== '' ? 'text-promo-ink' : 'text-promo-muted'"
                        >
                            {{ displayValue }}
                        </span>
                    </span>
                </span>

                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-promo-muted">
                    Open
                </span>
            </button>
        </PopoverTrigger>

        <PopoverContent
            align="start"
            :side-offset="10"
            class="w-[22rem] rounded-[28px] border-promo-line bg-white p-0 shadow-[0_24px_70px_rgba(232,79,154,0.14)]"
        >
            <div class="border-b border-promo-line bg-promo-surface/55 px-5 py-4">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-promo-primary">
                    Pick a time
                </p>
                <p class="mt-2 text-sm text-promo-muted">
                    Choose the approximate start time for this moment.
                </p>
            </div>

            <div class="space-y-4 p-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="timeOption in quickTimes"
                        :key="timeOption"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-sm font-medium transition-colors duration-200"
                        :class="
                            props.modelValue === timeOption
                                ? 'border-promo-primary bg-promo-primary text-white'
                                : 'border-promo-line bg-promo-surface/40 text-promo-muted hover:bg-white'
                        "
                        @click="applyQuickTime(timeOption)"
                    >
                        {{ timeOption }}
                    </button>
                </div>

                <div class="grid grid-cols-[1fr_auto_1fr] items-start gap-3">
                    <div class="rounded-[22px] border border-promo-line bg-promo-surface/35 p-3">
                        <p class="mb-3 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                            Hour
                        </p>
                        <ScrollArea class="h-56">
                            <div class="space-y-1 pr-2">
                                <button
                                    v-for="hour in hours"
                                    :key="hour"
                                    type="button"
                                    class="flex h-10 w-full items-center justify-center rounded-[16px] text-sm font-medium transition-colors duration-200"
                                    :class="
                                        selectedHour === hour
                                            ? 'bg-promo-primary text-white'
                                            : 'text-promo-ink hover:bg-white'
                                    "
                                    @click="selectHour(hour)"
                                >
                                    {{ hour }}
                                </button>
                            </div>
                        </ScrollArea>
                    </div>

                    <div class="pt-16 text-2xl font-black text-promo-primary">
                        :
                    </div>

                    <div class="rounded-[22px] border border-promo-line bg-promo-surface/35 p-3">
                        <p class="mb-3 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-promo-primary">
                            Minute
                        </p>
                        <ScrollArea class="h-56">
                            <div class="space-y-1 pr-2">
                                <button
                                    v-for="minute in minutes"
                                    :key="minute"
                                    type="button"
                                    class="flex h-10 w-full items-center justify-center rounded-[16px] text-sm font-medium transition-colors duration-200"
                                    :class="
                                        selectedMinute === minute
                                            ? 'bg-promo-primary text-white'
                                            : 'text-promo-ink hover:bg-white'
                                    "
                                    @click="selectMinute(minute)"
                                >
                                    {{ minute }}
                                </button>
                            </div>
                        </ScrollArea>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <Button
                        type="button"
                        variant="ghost"
                        class="rounded-full px-4 text-promo-muted"
                        @click="clearValue"
                    >
                        Clear
                    </Button>

                    <Button
                        type="button"
                        class="rounded-full bg-promo-primary px-4 text-white hover:bg-promo-primary-strong"
                        @click="done"
                    >
                        Done
                    </Button>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
