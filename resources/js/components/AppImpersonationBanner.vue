<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { LogOut, UserRoundCog } from 'lucide-vue-next';
import { computed } from 'vue';

type Impersonation = {
    active: boolean;
    userName: string | null;
    userEmail: string | null;
    adminName: string | null;
    stopUrl: string;
};

const page = usePage();
const impersonation = computed(
    () => (page?.props?.impersonation as Impersonation | null) ?? null,
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
        class="fixed right-4 bottom-4 z-[100] flex max-w-[calc(100vw-2rem)] items-center gap-3 rounded-full border border-amber-600/40 bg-amber-500 py-2 pr-2 pl-4 text-sm font-medium text-amber-950 shadow-[0_10px_30px_rgba(0,0,0,0.18)]"
    >
        <span class="inline-flex min-w-0 items-center gap-2">
            <UserRoundCog class="size-4 shrink-0" />
            <span class="truncate">
                Viewing as
                <strong>{{
                    impersonation.userEmail ?? impersonation.userName
                }}</strong>
            </span>
        </span>
        <button
            type="button"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-amber-950 px-3 py-1.5 text-xs font-semibold text-amber-50 transition hover:bg-amber-900"
            @click="stopImpersonating"
        >
            <LogOut class="size-3.5" />
            Exit
        </button>
    </div>
</template>
