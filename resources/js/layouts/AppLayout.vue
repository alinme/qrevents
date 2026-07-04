<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { UserRoundCog } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

type Impersonation = {
    active: boolean;
    userName: string | null;
    userEmail: string | null;
    adminName: string | null;
    stopUrl: string;
};

const page = usePage();
const impersonation = computed(
    () => (page.props.impersonation as Impersonation | null) ?? null,
);

const stopImpersonating = (): void => {
    if (!impersonation.value) {
        return;
    }

    router.post(impersonation.value.stopUrl, {}, { preserveScroll: true });
};
</script>

<template>
    <div
        v-if="impersonation?.active"
        class="sticky top-0 z-50 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 bg-amber-500 px-4 py-2 text-center text-sm font-medium text-amber-950"
    >
        <span class="inline-flex items-center gap-2">
            <UserRoundCog class="size-4" />
            Viewing as
            <strong>{{ impersonation.userEmail ?? impersonation.userName }}</strong>
        </span>
        <button
            type="button"
            class="rounded-full bg-amber-950 px-3 py-1 text-xs font-semibold text-amber-50 transition hover:bg-amber-900"
            @click="stopImpersonating"
        >
            Return to admin
        </button>
    </div>

    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
