<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Camera,
    FolderKanban,
    Search,
    Settings,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { badgeClass, formatBytes, formatDateOnly, formatDateTime } from '@/lib/dashboard';
import type {
    BreadcrumbItem,
    BusinessDashboardFilters,
    BusinessOverview,
    DashboardEvent,
    DashboardLinks,
    PaginationMeta,
    Summary,
} from '@/types';

const props = defineProps<{
    summary: Summary;
    businessOverview: BusinessOverview;
    filters: BusinessDashboardFilters;
    dashboardLinks: DashboardLinks;
    ownedEvents: DashboardEvent[];
    ownedEventsPagination: PaginationMeta;
}>();

const { locale, t } = useTranslations();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: t('app.nav.dashboard'),
        href: props.dashboardLinks.overview,
    },
    {
        title: t('app.nav.business'),
        href: props.dashboardLinks.business ?? props.dashboardLinks.overview,
    },
    {
        title: t('app.nav.events'),
        href: props.dashboardLinks.ownedEvents,
    },
]);

const search = ref(props.filters.search);

const formatNumber = (value: number): string =>
    new Intl.NumberFormat(
        locale.value === 'ro'
            ? 'ro-RO'
            : locale.value === 'el'
              ? 'el-GR'
              : 'en-GB',
    ).format(value);

const formatLocalizedDateOnly = (value: string | null): string =>
    formatDateOnly(value, {
        locale: locale.value,
        emptyLabel: t('event_home.fallback.not_set'),
    });

const formatLocalizedDateTime = (value: string | null): string =>
    formatDateTime(value, {
        locale: locale.value,
        emptyLabel: t('dashboard.business.events.event.no_activity'),
    });

const summaryCards = computed(() => [
    {
        label: t('dashboard.business.events.metrics.events.label'),
        value: formatNumber(props.filters.ownedEventTotalCount),
        detail: t('dashboard.business.events.metrics.events.detail'),
    },
    {
        label: t('dashboard.business.events.metrics.live.label'),
        value: formatNumber(props.businessOverview.liveEventCount),
        detail: t('dashboard.business.events.metrics.live.detail'),
    },
    {
        label: t('dashboard.business.events.metrics.setup.label'),
        value: formatNumber(props.summary.pendingSetupCount),
        detail: t('dashboard.business.events.metrics.setup.detail'),
    },
    {
        label: t('dashboard.business.events.metrics.attention.label'),
        value: formatNumber(props.filters.attentionTotalCount),
        detail: t('dashboard.business.events.metrics.attention.detail'),
    },
]);

const applyFilters = (overrides?: {
    status?: BusinessDashboardFilters['status'];
    page?: number;
}): void => {
    router.get(
        props.dashboardLinks.ownedEvents,
        {
            search: search.value.trim() !== '' ? search.value.trim() : undefined,
            status:
                (overrides?.status ?? props.filters.status) !== 'all'
                    ? (overrides?.status ?? props.filters.status)
                    : undefined,
            page: overrides?.page && overrides.page > 1 ? overrides.page : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head :title="t('dashboard.business.events.page_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-brand-canvas">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-4 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="dashboard-eyebrow">
                                {{ t('dashboard.business.hero.kicker') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('dashboard.business.events.hero.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{ t('dashboard.business.events.hero.description') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button as-child variant="outline" class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20">
                                <Link :href="props.dashboardLinks.business ?? props.dashboardLinks.overview">
                                    {{ t('dashboard.business.events.actions.back') }}
                                </Link>
                            </Button>
                            <Button as-child class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                                <Link :href="props.dashboardLinks.createBusiness ?? '/dashboard/business/events/create'">
                                    {{ t('dashboard.business.events.actions.create') }}
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div class="pt-5">
                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div
                                v-for="card in summaryCards"
                                :key="card.label"
                                class="dashboard-divider-left"
                            >
                                <dt class="dashboard-eyebrow">
                                    {{ card.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-brand-ink">
                                    {{ card.value }}
                                </dd>
                                <p class="dashboard-meta mt-1">
                                    {{ card.detail }}
                                </p>
                            </div>
                        </dl>
                    </div>
                </section>

                <section class="overflow-hidden rounded-[1.75rem] border border-brand-border/70 bg-brand-panel shadow-sm">
                    <div class="border-b border-brand-border/70 px-5 py-5 md:px-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-1 items-center gap-3 rounded-full border border-brand-border bg-brand-inverse px-4 py-2.5">
                                <Search class="size-4 text-brand-muted" />
                                <Input
                                    v-model="search"
                                    class="h-auto border-0 bg-transparent px-0 py-0 text-sm text-brand-ink shadow-none focus-visible:ring-0"
                                    :placeholder="t('dashboard.business.events.search_placeholder')"
                                    @keydown.enter.prevent="applyFilters()"
                                />
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-for="option in props.filters.statusOptions"
                                    :key="option.value"
                                    type="button"
                                    variant="outline"
                                    class="rounded-full border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20"
                                    :class="option.value === props.filters.status ? 'border-brand-ink bg-brand-highlight/25' : ''"
                                    @click="applyFilters({ status: option.value, page: 1 })"
                                >
                                    {{ option.label }} ({{ formatNumber(option.count) }})
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-if="props.ownedEvents.length === 0" class="px-6 py-14 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div class="rounded-full bg-brand-highlight/25 p-4 text-brand-ink">
                                <FolderKanban class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-brand-ink">
                                    {{ t('dashboard.business.events.empty.title') }}
                                </h2>
                                <p class="dashboard-body">
                                    {{ t('dashboard.business.events.empty.description') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-brand-border/70">
                        <article
                            v-for="event in props.ownedEvents"
                            :key="event.id"
                            class="px-5 py-5 md:px-6"
                        >
                            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(event.roleTone)">
                                            {{ event.roleLabel }}
                                        </span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(event.statusTone)">
                                            {{ event.statusLabel }}
                                        </span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(event.billingTone)">
                                            {{ event.billingLabel }}
                                        </span>
                                    </div>

                                    <div class="space-y-1">
                                        <h2 class="text-lg font-semibold tracking-tight text-brand-ink sm:text-xl">
                                            {{ event.name }}
                                        </h2>
                                        <p class="dashboard-body">
                                            {{ event.plan }} · {{ formatLocalizedDateOnly(event.eventDate) }} · {{ event.timezone }}
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-brand-muted">
                                        <span>{{ t('dashboard.business.events.event.guests', { count: formatNumber(event.guestCount) }) }}</span>
                                        <span>{{ t('dashboard.business.events.event.uploads', { count: formatNumber(event.assetCount) }) }}</span>
                                        <span>{{ t('dashboard.business.events.event.pending', { count: formatNumber(event.processingCount) }) }}</span>
                                        <span>{{ formatBytes(event.storageUsedBytes) }} / {{ formatBytes(event.storageLimitBytes) }}</span>
                                        <span>{{ formatLocalizedDateTime(event.lastUploadAt) }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                    <Button as-child variant="outline" class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20">
                                        <Link :href="event.links.dashboard">
                                            <ArrowUpRight class="size-4" />
                                            {{ t('app.nav.workspace') }}
                                        </Link>
                                    </Button>
                                    <Button as-child variant="outline" class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20">
                                        <Link :href="event.links.media">
                                            <Camera class="size-4" />
                                            {{ t('app.nav.media') }}
                                        </Link>
                                    </Button>
                                    <Button as-child variant="outline" class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20">
                                        <Link :href="event.links.billing">
                                            {{ t('app.nav.billing') }}
                                        </Link>
                                    </Button>
                                    <Button as-child variant="outline" class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20">
                                        <Link :href="event.links.settings">
                                            <Settings class="size-4" />
                                            {{ t('app.nav.settings') }}
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-if="props.ownedEventsPagination.lastPage > 1"
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-brand-border/70 px-5 py-4 md:px-6"
                    >
                        <p class="dashboard-body">
                            {{ t('dashboard.business.pagination.showing_compact', {
                                from: formatNumber(props.ownedEventsPagination.from ?? 0),
                                to: formatNumber(props.ownedEventsPagination.to ?? 0),
                                total: formatNumber(props.ownedEventsPagination.total),
                            }) }}
                        </p>

                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="link in props.ownedEventsPagination.links"
                                :key="`${link.label}-${link.url}`"
                                type="button"
                                variant="outline"
                                class="border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20"
                                :disabled="link.url === null"
                                :class="link.active ? 'border-brand-ink bg-brand-highlight/25' : ''"
                                @click="link.url && router.visit(link.url, { preserveState: true, preserveScroll: true })"
                            >
                                <span v-html="link.label" />
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
