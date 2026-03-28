<script setup lang="ts">
import type { HTMLAttributes, Component } from 'vue';
import { cn } from '@/lib/utils';

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
                'rounded-2xl border border-brand-border/70 bg-brand-panel p-4',
                props.class,
            )
        "
    >
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-2">
                <p class="text-sm font-medium text-brand-muted">
                    {{ label }}
                </p>
                <p class="text-3xl font-semibold text-brand-ink">
                    {{ value }}
                </p>
            </div>
            <div
                v-if="icon"
                class="rounded-2xl bg-brand-inverse p-3 text-brand-ink shadow-sm"
            >
                <component :is="icon" class="size-5" />
            </div>
        </div>
        <p v-if="detail" class="dashboard-body mt-4">
            {{ detail }}
        </p>
    </div>
</template>
