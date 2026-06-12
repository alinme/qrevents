<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        badge?: string;
        align?: 'start' | 'center';
    }>(),
    {
        description: undefined,
        badge: undefined,
        align: 'center',
    },
);
</script>

<template>
    <div
        class="grid gap-3 md:grid-cols-[minmax(0,1fr)_320px] md:gap-4"
        :class="align === 'center' ? 'md:items-center' : 'md:items-start'"
    >
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold">{{ title }}</h3>
                <Badge v-if="badge" variant="secondary">
                    <Sparkles />
                    {{ badge }}
                </Badge>
            </div>
            <p v-if="description" class="mt-0.5 text-sm text-muted-foreground">
                {{ description }}
            </p>
            <slot name="meta" />
        </div>
        <div class="min-w-0">
            <slot />
        </div>
    </div>
</template>
