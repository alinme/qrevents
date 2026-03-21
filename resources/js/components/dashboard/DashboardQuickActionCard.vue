<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
import type { QuickAction } from '@/types';

defineProps<{
    action: QuickAction;
}>();
</script>

<template>
    <Link
        :href="action.url"
        class="group rounded-2xl border border-white/10 bg-white/8 p-4 text-left transition hover:bg-white/14"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <div class="text-sm font-semibold">
                    {{ action.label }}
                </div>
                <div class="text-sm text-white/68">
                    {{ action.description }}
                </div>
            </div>
            <span
                class="inline-flex size-9 items-center justify-center rounded-full transition group-hover:translate-x-0.5"
                :class="
                    action.tone === 'dark'
                        ? 'bg-white text-[#171411]'
                        : 'bg-white/14 text-white'
                "
            >
                <ArrowRight class="size-4" />
            </span>
        </div>
    </Link>
</template>
