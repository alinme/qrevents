<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    CheckSquare,
    CreditCard,
    Download,
    FolderKanban,
    MoreHorizontal,
    Search,
    Settings,
    Square,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import type { Component } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    badgeClass,
    formatBytes,
    formatDateOnly,
    formatDateTime,
    walletActivityLabel,
} from '@/lib/dashboard';
import type {
    BreadcrumbItem,
    BusinessDashboardFilters,
    BusinessAttentionEvent,
    BusinessOverview,
    BusinessWalletActivity,
    DashboardEvent,
    DashboardLinks,
    PaginationMeta,
    QuickAction,
    Summary,
} from '@/types';

const props = defineProps<{
    summary: Summary;
    businessOverview: BusinessOverview;
    walletActivity: BusinessWalletActivity[];
    businessAttentionEvents: BusinessAttentionEvent[];
    businessAttentionSummary: {
        visibleCount: number;
        totalCount: number;
    };
    filters: BusinessDashboardFilters;
    quickActions: QuickAction[];
    dashboardLinks: DashboardLinks;
    businessActionLinks: {
        startExports: string;
        billingQueueDownload: string;
        walletHistory: string;
    };
    ownedEvents: DashboardEvent[];
    ownedEventsPagination: PaginationMeta;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: props.dashboardLinks.overview,
    },
    {
        title: 'Business',
        href: props.dashboardLinks.business ?? props.dashboardLinks.overview,
    },
];

const businessHealthCards = computed(() => [
    {
        label: 'Events',
        value: props.filters.ownedEventTotalCount,
        detail: 'Everything in your owned-event portfolio.',
        icon: FolderKanban,
    },
    {
        label: 'Live',
        value: props.businessOverview.liveEventCount,
        detail: 'Events currently open for guest uploads.',
        icon: ArrowRight,
    },
    {
        label: 'To setup',
        value: props.summary.pendingSetupCount,
        detail: 'Events still going through onboarding.',
        icon: Settings,
    },
    {
        label: 'Attention',
        value:
            props.businessOverview.overdueEventCount > 0
                ? `${props.businessOverview.overdueEventCount} overdue`
                : `${props.businessOverview.unpaidEventCount} unpaid`,
        detail: 'Events that still need billing follow-up.',
        icon: CreditCard,
    },
    {
        label: 'Exports',
        value: props.businessOverview.readyExportCount,
        detail: 'Workspaces with a media export ready to download.',
        icon: Download,
    },
]);

const primaryActions = computed(() => props.quickActions);

type RowActionLink = {
    label: string;
    url: string;
    icon: Component;
};

const actionButtonClass = (tone: QuickAction['tone']): string => {
    if (tone === 'dark') {
        return 'bg-brand-ink text-brand-inverse hover:bg-brand-accent';
    }

    return 'border border-brand-border bg-brand-panel text-brand-ink hover:bg-brand-highlight/25';
};

const latestWalletEntry = computed(() => props.walletActivity[0] ?? null);

const page = usePage();
const walletCheckoutStatus = computed(() => {
    const query = page.url.split('?')[1] ?? '';
    const params = new URLSearchParams(query);
    const status = params.get('wallet_checkout');

    return status === 'success' || status === 'cancelled' ? status : null;
});

const parseSelectedEventIdsFromUrl = (url: string): number[] => {
    const query = url.split('?')[1] ?? '';
    const params = new URLSearchParams(query);

    return Array.from(
        new Set(
            Array.from(params.entries())
                .filter(
                    ([key]) =>
                        key === 'event_ids[]' || key.startsWith('event_ids['),
                )
                .map(([, value]) => value)
                .map((value) => Number.parseInt(value, 10))
                .filter((value) => Number.isInteger(value) && value > 0),
        ),
    );
};

const parseAllFilteredSelectionFromUrl = (url: string): boolean => {
    const query = url.split('?')[1] ?? '';
    const params = new URLSearchParams(query);

    return params.get('selection_scope') === 'all_filtered';
};

const search = ref(props.filters.search);
const selectedEventIds = ref<number[]>(parseSelectedEventIdsFromUrl(page.url));
const allFilteredSelected = ref(
    props.filters.selectionScope === 'all_filtered',
);

const businessBaseUrl = computed(
    () => props.dashboardLinks.business ?? props.dashboardLinks.overview,
);

const buildBusinessUrl = (overrides?: {
    search?: string;
    status?: BusinessDashboardFilters['status'];
    page?: number | null;
}): string => {
    const params = new URLSearchParams();
    const nextSearch = (overrides?.search ?? props.filters.search).trim();
    const nextStatus = overrides?.status ?? props.filters.status;
    const nextPage = overrides?.page ?? null;

    if (nextSearch !== '') {
        params.set('search', nextSearch);
    }

    if (nextStatus !== 'all') {
        params.set('status', nextStatus);
    }

    if (nextPage !== null && nextPage > 1) {
        params.set('page', String(nextPage));
    }

    const query = params.toString();

    return query === ''
        ? businessBaseUrl.value
        : `${businessBaseUrl.value}?${query}`;
};

const buildBusinessActionUrl = (baseUrl: string): string => {
    const params = new URLSearchParams();

    if (props.filters.search.trim() !== '') {
        params.set('search', props.filters.search.trim());
    }

    if (props.filters.status !== 'all') {
        params.set('status', props.filters.status);
    }

    if (allFilteredSelected.value) {
        params.set('selection_scope', 'all_filtered');
    } else {
        selectedEventIds.value.forEach((id) => {
            params.append('event_ids[]', String(id));
        });
    }

    const query = params.toString();

    return query === '' ? baseUrl : `${baseUrl}?${query}`;
};

const syncSelectionInBrowserUrl = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    const url = new URL(window.location.href);

    url.searchParams.delete('search');
    url.searchParams.delete('status');
    url.searchParams.delete('page');
    url.searchParams.delete('selection_scope');
    Array.from(url.searchParams.keys()).forEach((key) => {
        if (key === 'event_ids[]' || key.startsWith('event_ids[')) {
            url.searchParams.delete(key);
        }
    });

    if (props.filters.search.trim() !== '') {
        url.searchParams.set('search', props.filters.search.trim());
    }

    if (props.filters.status !== 'all') {
        url.searchParams.set('status', props.filters.status);
    }

    if (props.ownedEventsPagination.currentPage > 1) {
        url.searchParams.set(
            'page',
            String(props.ownedEventsPagination.currentPage),
        );
    }

    if (allFilteredSelected.value) {
        url.searchParams.set('selection_scope', 'all_filtered');
    } else {
        selectedEventIds.value.forEach((id) => {
            url.searchParams.append('event_ids[]', String(id));
        });
    }

    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
};

const applyFilters = (): void => {
    selectedEventIds.value = [];
    allFilteredSelected.value = false;

    router.get(
        businessBaseUrl.value,
        {
            search:
                search.value.trim() !== '' ? search.value.trim() : undefined,
            status:
                props.filters.status !== 'all'
                    ? props.filters.status
                    : undefined,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = (): void => {
    search.value = '';
    selectedEventIds.value = [];
    allFilteredSelected.value = false;

    router.get(
        businessBaseUrl.value,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
};

const startBulkExports = (): void => {
    if (!hasSelection.value) {
        return;
    }

    router.post(
        props.businessActionLinks.startExports,
        {
            search:
                props.filters.search.trim() !== ''
                    ? props.filters.search.trim()
                    : undefined,
            status:
                props.filters.status !== 'all'
                    ? props.filters.status
                    : undefined,
            event_ids:
                !allFilteredSelected.value && selectedEventIds.value.length > 0
                    ? selectedEventIds.value
                    : undefined,
            selection_scope: allFilteredSelected.value
                ? 'all_filtered'
                : undefined,
        },
        {
            preserveScroll: true,
        },
    );
};

const visibleEventIds = computed(() =>
    props.ownedEvents.map((event) => event.id),
);

const allVisibleSelected = computed(
    () =>
        visibleEventIds.value.length > 0 &&
        visibleEventIds.value.every((id) =>
            selectedEventIds.value.includes(id),
        ),
);

const hasSelection = computed(
    () => allFilteredSelected.value || selectedEventIds.value.length > 0,
);

const selectionLabel = computed(() => {
    if (allFilteredSelected.value) {
        return `All ${props.filters.ownedEventCount} filtered workspaces selected`;
    }

    return `${selectedEventIds.value.length} selected across pages`;
});

const eventSecondaryAction = (event: DashboardEvent): RowActionLink => {
    if (!event.isPaid) {
        return {
            label: 'Billing',
            url: event.links.billing,
            icon: CreditCard,
        };
    }

    if (event.canManage && event.mediaExportStatus === 'ready') {
        return {
            label: 'Export',
            url: event.links.mediaExportDownload,
            icon: Download,
        };
    }

    return {
        label: 'Media',
        url: event.links.media,
        icon: Camera,
    };
};

const eventOverflowActions = (event: DashboardEvent): RowActionLink[] => {
    const secondaryAction = eventSecondaryAction(event);
    const actions: RowActionLink[] = [
        {
            label: 'Media',
            url: event.links.media,
            icon: Camera,
        },
        {
            label: 'Settings',
            url: event.links.settings,
            icon: Settings,
        },
    ];

    if (!event.isPaid) {
        actions.splice(1, 0, {
            label: 'Billing',
            url: event.links.billing,
            icon: CreditCard,
        });
    }

    if (event.canManage && event.mediaExportStatus === 'ready') {
        actions.splice(actions.length - 1, 0, {
            label: 'Export',
            url: event.links.mediaExportDownload,
            icon: Download,
        });
    }

    return actions.filter((action) => action.url !== secondaryAction.url);
};

const attentionSecondaryAction = (
    event: BusinessAttentionEvent,
): RowActionLink => {
    if (event.billingTone !== 'emerald') {
        return {
            label: 'Billing',
            url: event.links.billing,
            icon: CreditCard,
        };
    }

    return {
        label: 'Media',
        url: event.links.media,
        icon: Camera,
    };
};

const attentionOverflowActions = (
    event: BusinessAttentionEvent,
): RowActionLink[] => {
    const secondaryAction = attentionSecondaryAction(event);
    const actions: RowActionLink[] = [
        {
            label: 'Media',
            url: event.links.media,
            icon: Camera,
        },
        {
            label: 'Settings',
            url: event.links.settings,
            icon: Settings,
        },
    ];

    if (event.billingTone !== 'emerald') {
        actions.splice(1, 0, {
            label: 'Billing',
            url: event.links.billing,
            icon: CreditCard,
        });
    }

    return actions.filter((action) => action.url !== secondaryAction.url);
};

const toggleEventSelection = (eventId: number): void => {
    if (allFilteredSelected.value) {
        return;
    }

    if (selectedEventIds.value.includes(eventId)) {
        selectedEventIds.value = selectedEventIds.value.filter(
            (id) => id !== eventId,
        );

        return;
    }

    selectedEventIds.value = [...selectedEventIds.value, eventId];
};

const toggleVisibleSelection = (): void => {
    if (allFilteredSelected.value) {
        return;
    }

    if (allVisibleSelected.value) {
        selectedEventIds.value = selectedEventIds.value.filter(
            (id) => !visibleEventIds.value.includes(id),
        );

        return;
    }

    const nextIds = new Set(selectedEventIds.value);
    visibleEventIds.value.forEach((id) => nextIds.add(id));
    selectedEventIds.value = Array.from(nextIds);
};

const selectAllFiltered = (): void => {
    selectedEventIds.value = [];
    allFilteredSelected.value = true;
};

const clearSelection = (): void => {
    selectedEventIds.value = [];
    allFilteredSelected.value = false;
};

watch(
    () => page.url,
    () => {
        selectedEventIds.value = parseSelectedEventIdsFromUrl(page.url);
        allFilteredSelected.value = parseAllFilteredSelectionFromUrl(page.url);
    },
    { immediate: true },
);

watch([selectedEventIds, allFilteredSelected], () => {
    syncSelectionInBrowserUrl();
});
</script>

<template>
    <Head title="Business Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell flex max-w-7xl flex-col gap-5">
                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-4 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="dashboard-eyebrow">
                                Business
                            </p>
                            <h1 class="dashboard-title mt-2">
                                Create and run client events
                            </h1>
                            <p class="dashboard-body mt-2">
                                Wallet, billing, exports, and live workspaces in one calm home.
                            </p>
                        </div>

                        <div v-if="primaryActions.length > 0" class="flex flex-wrap gap-2">
                            <Button
                                v-for="action in primaryActions"
                                :key="action.label"
                                as-child
                                :variant="action.tone === 'dark' ? 'default' : 'outline'"
                                :class="actionButtonClass(action.tone)"
                            >
                                <Link :href="action.url">
                                    {{ action.label }}
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <div
                        v-if="walletCheckoutStatus"
                        class="mt-5 rounded-[20px] border px-4 py-4 text-sm"
                        :class="
                            walletCheckoutStatus === 'success'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                                : 'border-amber-200 bg-amber-50 text-amber-900'
                        "
                    >
                        <p class="font-semibold">
                            {{
                                walletCheckoutStatus === 'success'
                                    ? 'Top-up checkout completed.'
                                    : 'Top-up checkout was cancelled.'
                            }}
                        </p>
                        <p class="mt-1 leading-6">
                            {{
                                walletCheckoutStatus === 'success'
                                    ? 'If Stripe has already confirmed the payment, your credits should appear here. Refresh once if they do not show up right away.'
                                    : 'Your wallet balance did not change. You can try again whenever you are ready.'
                            }}
                        </p>
                    </div>

                    <div class="grid gap-5 pt-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-5">
                            <div
                                v-for="card in businessHealthCards"
                                :key="card.label"
                                class="dashboard-divider-left"
                            >
                                <dt class="flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-brand-muted">
                                    <component :is="card.icon" class="size-3.5 text-brand-muted/70" />
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

                        <div class="border-t border-brand-border/70 pt-4 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-brand-muted">
                                Wallet
                            </p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-brand-ink">
                                {{ businessOverview.walletCredits }} credits
                            </p>
                            <p class="dashboard-body mt-1">
                                One credit equals one euro in wallet value.
                            </p>

                            <div class="mt-4 space-y-2 text-sm text-brand-muted">
                                <p v-if="latestWalletEntry" class="text-brand-ink">
                                    <span class="font-semibold">{{ walletActivityLabel(latestWalletEntry) }}</span>
                                    <span class="text-brand-muted">
                                        · {{ formatDateTime(latestWalletEntry.createdAt) }}
                                    </span>
                                </p>
                                <p v-else>
                                    No credit activity yet.
                                </p>
                                <p>
                                    {{ businessOverview.walletCredits }} credits on hand ·
                                    {{ businessOverview.walletCurrency }}
                                </p>
                                <p>
                                    {{ formatBytes(businessOverview.totalUsedStorageBytes) }} used ·
                                    {{ formatBytes(businessOverview.totalFreeStorageBytes) }} free
                                </p>
                            </div>

                        </div>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex items-start justify-between gap-3 pb-4">
                        <div>
                            <p class="dashboard-eyebrow">
                                Attention
                            </p>
                            <h2 class="dashboard-section-title mt-2">
                                What needs you first
                            </h2>
                            <p class="dashboard-body mt-1">
                                Billing follow-up, overdue workspaces, and the next places to open.
                            </p>
                        </div>
                        <span class="dashboard-chip">
                            {{ businessAttentionSummary.visibleCount }}
                        </span>
                    </div>

                    <div v-if="businessAttentionEvents.length === 0" class="dashboard-body py-8">
                        Nothing urgent right now.
                    </div>

                    <div v-else class="divide-y divide-brand-border/70 pt-2">
                        <article
                            v-for="event in businessAttentionEvents"
                            :key="event.id"
                            class="py-4"
                        >
                            <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.statusTone)">
                                            {{ event.statusLabel }}
                                        </span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.billingTone)">
                                            {{ event.billingLabel }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-brand-ink px-2.5 py-1 text-[0.68rem] font-semibold text-brand-inverse">
                                            {{ event.attentionLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-2.5">
                                        <h3 class="text-[0.97rem] font-semibold text-brand-ink">
                                            {{ event.name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-brand-muted">
                                            {{ event.plan }} · {{ event.attentionDetail }}
                                        </p>
                                        <p class="mt-1 text-sm text-brand-muted">
                                            {{
                                                event.paymentDueAt
                                                    ? `Due ${formatDateOnly(event.paymentDueAt)}`
                                                    : 'No due date set'
                                            }}
                                            · {{ event.assetCount }} uploads
                                            · {{ formatBytes(event.storageUsedBytes) }} of {{ formatBytes(event.storageLimitBytes) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 xl:max-w-[300px] xl:justify-end">
                                    <Button as-child size="sm" class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                                        <Link :href="event.links.dashboard">
                                            Open workspace
                                        </Link>
                                    </Button>
                                    <Button as-child size="sm" variant="outline">
                                        <Link :href="attentionSecondaryAction(event).url">
                                            <component :is="attentionSecondaryAction(event).icon" class="size-4" />
                                            {{ attentionSecondaryAction(event).label }}
                                        </Link>
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <button
                                                type="button"
                                                class="inline-flex size-9 items-center justify-center rounded-full border border-brand-border bg-brand-inverse text-brand-muted transition hover:border-brand-accent/30 hover:bg-brand-highlight/20 hover:text-brand-ink"
                                                aria-label="More attention actions"
                                            >
                                                <MoreHorizontal class="size-4" />
                                            </button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-44">
                                            <DropdownMenuItem
                                                v-for="action in attentionOverflowActions(event)"
                                                :key="`${event.id}-${action.label}`"
                                                as-child
                                            >
                                                <Link :href="action.url">
                                                    <component :is="action.icon" class="size-4" />
                                                    {{ action.label }}
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex flex-col gap-3 pb-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h2 class="dashboard-section-title">
                                Event portfolio
                            </h2>
                            <p class="dashboard-body mt-1">
                                Find the right workspace fast, then jump into the next action.
                            </p>
                        </div>
                        <p class="text-sm text-brand-muted">
                            {{ filters.ownedEventCount }} of {{ filters.ownedEventTotalCount }} shown
                        </p>
                    </div>

                    <div class="space-y-4 pt-5">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <form class="flex w-full flex-col gap-3 md:max-w-xl md:flex-row" @submit.prevent="applyFilters">
                                <div class="relative flex-1">
                                    <Search class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-brand-muted/70" />
                                    <Input
                                        v-model="search"
                                        type="search"
                                        placeholder="Search events, plans, billing, or status"
                                        class="h-11 rounded-full border-brand-border bg-brand-inverse pr-4 pl-10"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <Button type="submit" class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                                        Apply
                                    </Button>
                                    <Button
                                        v-if="filters.hasActiveFilters"
                                        type="button"
                                        variant="outline"
                                        @click="resetFilters"
                                    >
                                        <X class="size-4" />
                                        Clear
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="option in filters.statusOptions"
                                :key="option.value"
                                :href="buildBusinessUrl({ status: option.value, page: null })"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition"
                                :class="
                                    option.value === filters.status
                                        ? 'border-brand-ink bg-brand-ink text-brand-inverse'
                                        : 'border-brand-border bg-brand-inverse text-brand-muted hover:border-brand-accent/30 hover:bg-brand-highlight/20 hover:text-brand-ink'
                                "
                            >
                                <span>{{ option.label }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="
                                        option.value === filters.status
                                            ? 'bg-white/12 text-brand-inverse'
                                            : 'bg-brand-panel-strong/35 text-brand-muted'
                                    "
                                >
                                    {{ option.count }}
                                </span>
                            </Link>
                        </div>

                        <div
                            v-if="hasSelection"
                            class="flex flex-col gap-3 border-t border-brand-border/70 pt-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div>
                                <p class="text-sm font-medium text-brand-muted">
                                    <span class="font-semibold text-brand-ink">{{ selectionLabel }}</span>
                                </p>
                                <p v-if="allFilteredSelected" class="dashboard-meta mt-1">
                                    Clear selection to go back to page-by-page picks.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-if="filters.ownedEventCount > 0 && !allFilteredSelected"
                                    type="button"
                                    variant="outline"
                                    @click="selectAllFiltered"
                                >
                                    <CheckSquare class="size-4" />
                                    Select all filtered
                                </Button>
                                <Button
                                    v-if="visibleEventIds.length > 0 && !allFilteredSelected"
                                    type="button"
                                    variant="outline"
                                    @click="toggleVisibleSelection"
                                >
                                    <component :is="allVisibleSelected ? CheckSquare : Square" class="size-4" />
                                    {{ allVisibleSelected ? 'Clear page' : 'Select page' }}
                                </Button>
                                <Button
                                    v-if="hasSelection"
                                    type="button"
                                    variant="outline"
                                    @click="clearSelection"
                                >
                                    <X class="size-4" />
                                    Clear selection
                                </Button>
                                <Button
                                    v-if="hasSelection"
                                    type="button"
                                    class="bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                                    @click="startBulkExports"
                                >
                                    <Download class="size-4" />
                                    Start exports
                                </Button>
                                <Button
                                    v-if="hasSelection"
                                    as-child
                                    variant="outline"
                                >
                                    <a :href="buildBusinessActionUrl(businessActionLinks.billingQueueDownload)">
                                        <CreditCard class="size-4" />
                                        Billing CSV
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[32rem] overflow-y-auto overscroll-contain pt-1 pr-1 sm:max-h-[36rem]">
                        <div v-if="ownedEvents.length === 0" class="py-12 text-center">
                            <div class="mx-auto max-w-md space-y-2">
                                <h3 class="text-lg font-semibold text-brand-ink">
                                    {{ filters.hasActiveFilters ? 'No workspaces match these filters' : 'No owned events yet' }}
                                </h3>
                                <p class="dashboard-body">
                                    {{
                                        filters.hasActiveFilters
                                            ? 'Try a broader search or switch back to all workspaces.'
                                            : 'Your first business event will appear here with quick routes into workspace, media, billing, and export.'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="divide-y divide-brand-border/70 pt-3">
                            <article
                                v-for="event in ownedEvents"
                                :key="event.id"
                                class="py-3.5"
                            >
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <label class="mr-1 inline-flex items-center" :class="allFilteredSelected ? 'opacity-70' : ''">
                                                <Checkbox
                                                    :checked="allFilteredSelected || selectedEventIds.includes(event.id)"
                                                    :disabled="allFilteredSelected"
                                                    @update:checked="toggleEventSelection(event.id)"
                                                />
                                                <span class="sr-only">Select {{ event.name }}</span>
                                            </label>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.statusTone)">
                                                {{ event.statusLabel }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.billingTone)">
                                                {{ event.billingLabel }}
                                            </span>
                                            <span class="text-xs text-brand-muted">
                                                {{ event.plan }}
                                            </span>
                                        </div>

                                        <div class="mt-2.5">
                                            <h3 class="text-[0.97rem] font-semibold text-brand-ink">
                                                {{ event.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-brand-muted">
                                                {{ formatDateOnly(event.eventDate) }} · {{ event.timezone }}
                                            </p>
                                            <p class="mt-1 text-sm text-brand-muted">
                                                {{ event.guestCount }} guests · {{ event.assetCount }} uploads · {{ event.processingCount }} pending
                                                <span v-if="event.mediaExportStatus === 'ready'"> · Export ready</span>
                                                <span v-if="event.lastUploadAt"> · Last upload {{ formatDateTime(event.lastUploadAt) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2 xl:max-w-[300px] xl:justify-end">
                                        <Button as-child size="sm" class="bg-brand-ink text-brand-inverse hover:bg-brand-accent">
                                            <Link :href="event.primaryAction.url">
                                                {{ event.primaryAction.label }}
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="eventSecondaryAction(event).url">
                                                <component :is="eventSecondaryAction(event).icon" class="size-4" />
                                                {{ eventSecondaryAction(event).label }}
                                            </Link>
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <button
                                                    type="button"
                                                    class="inline-flex size-9 items-center justify-center rounded-full border border-brand-border bg-brand-inverse text-brand-muted transition hover:border-brand-accent/30 hover:bg-brand-highlight/20 hover:text-brand-ink"
                                                    aria-label="More workspace actions"
                                                >
                                                    <MoreHorizontal class="size-4" />
                                                </button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-44">
                                                <DropdownMenuItem
                                                    v-for="action in eventOverflowActions(event)"
                                                    :key="`${event.id}-${action.label}`"
                                                    as-child
                                                >
                                                    <Link :href="action.url">
                                                        <component :is="action.icon" class="size-4" />
                                                        {{ action.label }}
                                                    </Link>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div
                            v-if="ownedEventsPagination.lastPage > 1"
                            class="mt-4 flex flex-col gap-3 border-t border-brand-border/70 pt-4 md:flex-row md:items-center md:justify-between"
                        >
                            <p class="text-sm text-brand-muted">
                                Showing {{ ownedEventsPagination.from ?? 0 }} to {{ ownedEventsPagination.to ?? 0 }} of {{ ownedEventsPagination.total }} workspaces
                            </p>

                            <div class="flex flex-wrap items-center gap-2">
                                <Button v-if="ownedEventsPagination.prevPageUrl" as-child variant="outline">
                                    <Link :href="ownedEventsPagination.prevPageUrl">
                                        Previous
                                    </Link>
                                </Button>
                                <Button v-else variant="outline" disabled>
                                    Previous
                                </Button>
                                <span class="text-sm font-medium text-brand-muted">
                                    Page {{ ownedEventsPagination.currentPage }} of {{ ownedEventsPagination.lastPage }}
                                </span>
                                <Button v-if="ownedEventsPagination.nextPageUrl" as-child variant="outline">
                                    <Link :href="ownedEventsPagination.nextPageUrl">
                                        Next
                                    </Link>
                                </Button>
                                <Button v-else variant="outline" disabled>
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <div class="dashboard-panel-divider flex items-start justify-between gap-3 pb-4">
                        <div>
                            <p class="dashboard-eyebrow">
                                Wallet
                            </p>
                            <h2 class="dashboard-section-title mt-2">
                                Recent activity
                            </h2>
                            <p class="dashboard-body mt-1">
                                The latest credit changes for this business account.
                            </p>
                        </div>
                        <Button as-child size="sm" variant="outline">
                            <Link :href="businessActionLinks.walletHistory">
                                View history
                            </Link>
                        </Button>
                    </div>

                    <div v-if="walletActivity.length === 0" class="dashboard-body py-8">
                        No credit activity yet.
                    </div>

                    <div v-else class="max-h-[18rem] overflow-y-auto overscroll-contain pt-2 pr-1 sm:max-h-[20rem]">
                        <div class="divide-y divide-brand-border/70">
                            <article
                                v-for="item in walletActivity"
                                :key="item.id"
                                class="py-3"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-brand-ink">
                                            {{ walletActivityLabel(item) }}
                                        </p>
                                        <p class="mt-1 text-sm text-brand-muted">
                                            {{ item.description }}
                                            <span v-if="item.eventName">
                                                ·
                                                <Link
                                                    v-if="item.eventUrl"
                                                    :href="item.eventUrl"
                                                    class="font-medium text-brand-ink hover:text-brand-accent"
                                                >
                                                    {{ item.eventName }}
                                                </Link>
                                                <span v-else>{{ item.eventName }}</span>
                                            </span>
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-xs text-brand-muted">
                                        {{ formatDateTime(item.createdAt) }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
