<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes, Component } from 'vue';

const props = defineProps<{
    label: string;
    value: string | number;
    detail?: string;
    icon?: Component;
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        :class="
            cn(
                'rounded-2xl border border-black/6 bg-[#fbfaf7] p-4',
                props.class,
            )
        "
    >
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-500">
                    {{ label }}
                </p>
                <p class="text-3xl font-semibold text-[#171411]">
                    {{ value }}
                </p>
            </div>
            <div
                v-if="icon"
                class="rounded-2xl bg-white p-3 text-[#171411] shadow-sm"
            >
                <component :is="icon" class="size-5" />
            </div>
        </div>
        <p v-if="detail" class="mt-4 text-sm leading-6 text-zinc-600">
            {{ detail }}
        </p>
    </div>
</template>
