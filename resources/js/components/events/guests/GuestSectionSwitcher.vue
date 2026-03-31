<script setup lang="ts">
import type { Component } from 'vue';

type GuestSection = 'invitees' | 'invitation' | 'ledger' | 'guest_list';

defineProps<{
    modelValue: GuestSection;
    sections: Array<{
        key: GuestSection;
        icon: Component;
        label: string;
        description: string;
        value: string;
        detail: string;
    }>;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: GuestSection];
}>();
</script>

<template>
    <div class="grid gap-3 lg:grid-cols-4">
        <button
            v-for="section in sections"
            :key="section.key"
            type="button"
            :data-test="`guest-section-${section.key}`"
            :class="[
                'rounded-3xl border px-4 py-4 text-left transition',
                modelValue === section.key
                    ? 'border-neutral-950 bg-neutral-950 text-white shadow-sm'
                    : 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300 hover:shadow-sm',
            ]"
            @click="emit('update:modelValue', section.key)"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-3">
                    <div class="inline-flex size-10 items-center justify-center rounded-2xl border border-current/10 bg-current/5">
                        <component :is="section.icon" class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold">
                            {{ section.label }}
                        </p>
                        <p
                            class="mt-1 text-sm"
                            :class="modelValue === section.key ? 'text-white/72' : 'text-neutral-600'"
                        >
                            {{ section.description }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-lg font-semibold tracking-tight">
                        {{ section.value }}
                    </p>
                    <p
                        class="mt-1 text-xs"
                        :class="modelValue === section.key ? 'text-white/70' : 'text-neutral-500'"
                    >
                        {{ section.detail }}
                    </p>
                </div>
            </div>
        </button>
    </div>
</template>
