<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import {
    parseVisitPath,
    shouldShowDefaultSuccessToast,
} from '@/lib/flash-toasts';

type SharedFlash = {
    success?: string | null;
    error?: string | null;
    info?: string | null;
};

const page = usePage();
const flash = computed<SharedFlash>(() => {
    const flashProps = page.props.flash;

    if (flashProps && typeof flashProps === 'object') {
        return flashProps as SharedFlash;
    }

    return {};
});

watch(
    flash,
    (value) => {
        const success = value.success?.trim() ?? '';
        if (success !== '') {
            toast.success(success);
        }

        const error = value.error?.trim() ?? '';
        if (error !== '') {
            toast.error(error);
        }

        const info = value.info?.trim() ?? '';
        if (info !== '') {
            toast.info(info);
        }
    },
    { immediate: true, deep: true },
);

let removeSuccessListener: (() => void) | null = null;
let removeErrorListener: (() => void) | null = null;
let removeStartListener: (() => void) | null = null;
let lastVisitMethod = 'get';
let lastVisitPath = '/';

onMounted(() => {
    removeStartListener = router.on('start', (event) => {
        lastVisitMethod = String(event.detail.visit.method).toLowerCase();
        lastVisitPath = parseVisitPath(event.detail.visit.url, window.location.origin);
    });

    removeSuccessListener = router.on('success', (event) => {
        if (!shouldShowDefaultSuccessToast(lastVisitMethod, lastVisitPath)) {
            return;
        }

        const pageProps = event.detail.page.props as {
            flash?: SharedFlash;
        };
        const hasFlashMessage = Boolean(
            pageProps.flash?.success ||
                pageProps.flash?.error ||
                pageProps.flash?.info,
        );
        if (!hasFlashMessage) {
            toast.success('Saved successfully.');
        }
    });

    removeErrorListener = router.on('error', (event) => {
        if (Object.keys(event.detail.errors).length > 0) {
            toast.error('Please check the form and try again.');
        }
    });
});

onBeforeUnmount(() => {
    removeStartListener?.();
    removeSuccessListener?.();
    removeErrorListener?.();
});
</script>

<template>
    <div class="hidden" aria-hidden="true" />
</template>
