<script setup lang="ts">
import { ImageUp, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: File | null;
    previewUrl?: string | null;
    accept?: string;
    hint?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: File | null];
}>();

const input = ref<HTMLInputElement | null>(null);
const localPreview = ref<string | null>(null);

const preview = computed(() => localPreview.value ?? props.previewUrl ?? null);
const fileName = computed(() => props.modelValue?.name ?? null);

const pick = (): void => input.value?.click();

const onChange = (event: Event): void => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    emit('update:modelValue', file);
    localPreview.value = file ? URL.createObjectURL(file) : null;
};

const clear = (): void => {
    emit('update:modelValue', null);
    localPreview.value = null;
    if (input.value) {
        input.value.value = '';
    }
};
</script>

<template>
    <div class="flex items-center gap-4">
        <div
            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-brand-border/70 bg-brand-canvas/50"
        >
            <img
                v-if="preview"
                :src="preview"
                alt="Preview"
                class="size-full object-contain"
            />
            <ImageUp v-else class="size-5 text-brand-muted/60" />
        </div>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    @click="pick"
                >
                    {{ preview ? 'Replace' : 'Upload' }}
                </Button>
                <Button
                    v-if="modelValue"
                    type="button"
                    size="sm"
                    variant="ghost"
                    class="text-brand-muted"
                    @click="clear"
                >
                    <X class="size-3.5" /> Remove
                </Button>
            </div>
            <p v-if="fileName" class="dashboard-meta mt-1 truncate">
                {{ fileName }}
            </p>
            <p v-else-if="hint" class="dashboard-meta mt-1">{{ hint }}</p>
        </div>

        <input
            ref="input"
            type="file"
            :accept="accept ?? 'image/*'"
            class="hidden"
            @change="onChange"
        />
    </div>
</template>
