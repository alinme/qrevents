<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';

type AuditRow = {
    id: number;
    action: string;
    adminName: string;
    adminEmail: string | null;
    targetLabel: string | null;
    meta: Record<string, unknown> | null;
    ipAddress: string | null;
    createdAt: string | null;
};

const props = defineProps<{
    entries: AuditRow[];
    pagination: {
        currentPage: number;
        lastPage: number;
        total: number;
        prevPageUrl: string | null;
        nextPageUrl: string | null;
    };
    filters: { action: string };
    actionOptions: string[];
}>();

const { t } = useTranslations();

const filterByAction = (event: Event): void => {
    const value = (event.target as HTMLSelectElement).value;
    router.get('/admin/audit', value === '' ? {} : { action: value }, {
        preserveState: true,
        replace: true,
    });
};

void props;

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('admin.shared.admin'), href: '/admin' },
    { title: t('admin.audit.title'), href: '/admin/audit' },
];

const summarizeMeta = (meta: Record<string, unknown> | null): string => {
    if (!meta) {
        return '';
    }

    return Object.entries(meta)
        .map(([key, value]) => `${key}: ${String(value)}`)
        .join(', ');
};
</script>

<template>
    <Head :title="t('admin.audit.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section class="dashboard-panel">
                    <div
                        class="dashboard-panel-divider flex flex-col gap-4 pb-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p class="dashboard-eyebrow">
                                {{ t('admin.shared.admin') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('admin.audit.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{ t('admin.audit.description') }}
                            </p>
                        </div>

                        <label class="w-full max-w-xs">
                            <span class="dashboard-eyebrow">
                                {{ t('admin.audit.filter.label') }}
                            </span>
                            <select
                                :value="filters.action"
                                class="mt-1 h-9 w-full rounded-md border border-brand-border bg-transparent px-3 text-sm"
                                @change="filterByAction"
                            >
                                <option value="">
                                    {{ t('admin.audit.filter.all') }}
                                </option>
                                <option
                                    v-for="option in actionOptions"
                                    :key="option"
                                    :value="option"
                                >
                                    {{ option }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <div
                        v-if="entries.length === 0"
                        class="dashboard-body py-10"
                    >
                        {{ t('admin.audit.empty') }}
                    </div>

                    <ul v-else class="divide-y divide-brand-border/70">
                        <li
                            v-for="entry in entries"
                            :key="entry.id"
                            class="flex flex-col gap-1.5 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-md bg-brand-canvas px-2 py-0.5 font-mono text-[0.75rem] font-medium text-brand-ink"
                                    >
                                        {{ entry.action }}
                                    </span>
                                    <span
                                        v-if="entry.targetLabel"
                                        class="truncate text-sm font-medium text-brand-ink"
                                    >
                                        {{ entry.targetLabel }}
                                    </span>
                                </div>
                                <p class="dashboard-meta mt-1">
                                    {{ entry.adminName }}
                                    <template v-if="summarizeMeta(entry.meta)">
                                        · {{ summarizeMeta(entry.meta) }}
                                    </template>
                                </p>
                            </div>
                            <p
                                class="dashboard-meta shrink-0 whitespace-nowrap sm:text-right"
                            >
                                {{ formatDateTime(entry.createdAt) }}
                                <span v-if="entry.ipAddress" class="opacity-70">
                                    · {{ entry.ipAddress }}
                                </span>
                            </p>
                        </li>
                    </ul>

                    <div
                        v-if="pagination.lastPage > 1"
                        class="mt-4 flex items-center justify-between border-t border-brand-border/70 pt-4"
                    >
                        <p class="dashboard-meta">
                            {{
                                t('admin.audit.pagination.page', {
                                    current: pagination.currentPage,
                                    last: pagination.lastPage,
                                })
                            }}
                        </p>
                        <div class="flex gap-2">
                            <Button
                                as-child
                                size="sm"
                                variant="outline"
                                :class="{
                                    'pointer-events-none opacity-40':
                                        !pagination.prevPageUrl,
                                }"
                            >
                                <Link :href="pagination.prevPageUrl ?? '#'">
                                    {{ t('admin.audit.pagination.previous') }}
                                </Link>
                            </Button>
                            <Button
                                as-child
                                size="sm"
                                variant="outline"
                                :class="{
                                    'pointer-events-none opacity-40':
                                        !pagination.nextPageUrl,
                                }"
                            >
                                <Link :href="pagination.nextPageUrl ?? '#'">
                                    {{ t('admin.audit.pagination.next') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
