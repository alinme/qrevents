<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

withDefaults(
    defineProps<{
        eyebrow: string;
        badge?: string;
    }>(),
    {
        badge: undefined,
    },
);
</script>

<template>
    <section class="space-y-5">
        <div class="flex items-center gap-2">
            <p
                class="text-xs font-semibold tracking-[0.14em] text-muted-foreground uppercase"
            >
                {{ eyebrow }}
            </p>
            <Badge v-if="badge" variant="secondary">
                <Sparkles />
                {{ badge }}
            </Badge>
        </div>
        <slot />
    </section>
</template>
