<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    Camera,
    CheckSquare,
    CreditCard,
    Download,
    FolderKanban,
    Search,
    Settings,
    Square,
    X,
} from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    badgeClass,
    formatBytes,
    formatDateOnly,
    formatDateTime,
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
        createEvent: string;
        topUpWallet: string;
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
        label: 'Setup',
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
        label: 'Wallet',
        value: `${props.businessOverview.walletCredits} credits`,
        detail: `Stored in ${props.businessOverview.walletCurrency}.`,
        icon: CreditCard,
    },
]);

const primaryActions = computed(() => props.quickActions.slice(0, 4));

const actionButtonClass = (tone: QuickAction['tone']): string => {
    if (tone === 'dark') {
        return 'bg-[#171411] text-white hover:bg-[#2b2621]';
    }

    return 'border border-black/10 bg-white text-[#171411] hover:bg-[#faf7f1]';
};

const latestWalletEntry = computed(() => props.walletActivity[0] ?? null);

const walletActivityLabel = (item: BusinessWalletActivity): string => {
    if (item.kind === 'top_up') {
        return `+${item.credits} credits added`;
    }

    if (item.kind === 'bonus') {
        return `+${item.credits} bonus credits`;
    }

    if (item.kind === 'event_debit') {
        return `-${item.credits} credits used`;
    }

    return `${item.credits} credits updated`;
};

const page = usePage();

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

const selectionLabel = computed(() => {
    if (allFilteredSelected.value) {
        return `All ${props.filters.ownedEventCount} filtered workspaces selected`;
    }

    if (selectedEventIds.value.length === 0) {
        return `All ${props.filters.ownedEventCount} filtered workspaces (default)`;
    }

    return `${selectedEventIds.value.length} selected across pages`;
});

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
        <div class="min-h-full bg-[#faf7f2]">
            <div class="mx-auto flex max-w-7xl flex-col gap-5 p-4 md:p-6">
                <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="flex flex-col gap-4 border-b border-black/5 pb-5 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                Business
                            </p>
                            <h1 class="mt-2 text-xl font-semibold tracking-tight text-[#171411] sm:text-2xl">
                                Create and run client events
                            </h1>
                            <p class="mt-2 text-sm leading-6 text-zinc-600">
                                Wallet, billing, exports, and live workspaces in one calm home.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
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

                    <div class="grid gap-5 pt-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                        <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2 xl:grid-cols-5">
                            <div
                                v-for="card in businessHealthCards"
                                :key="card.label"
                                class="border-l border-black/8 pl-4 first:border-l-0 first:pl-0 sm:first:border-l sm:first:pl-4 xl:first:border-l-0 xl:first:pl-0"
                            >
                                <dt class="flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                    <component :is="card.icon" class="size-3.5 text-zinc-400" />
                                    {{ card.label }}
                                </dt>
                                <dd class="mt-2 text-lg font-semibold tracking-tight text-[#171411]">
                                    {{ card.value }}
                                </dd>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">
                                    {{ card.detail }}
                                </p>
                            </div>
                        </dl>

                        <div class="border-t border-black/5 pt-4 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                Wallet
                            </p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#171411]">
                                {{ businessOverview.walletCredits }} credits
                            </p>
                            <p class="mt-1 text-sm leading-6 text-zinc-600">
                                One credit equals one euro in wallet value.
                            </p>

                            <div class="mt-4 space-y-2 text-sm text-zinc-600">
                                <p v-if="latestWalletEntry" class="text-[#171411]">
                                    <span class="font-semibold">{{ walletActivityLabel(latestWalletEntry) }}</span>
                                    <span class="text-zinc-500">
                                        · {{ formatDateTime(latestWalletEntry.createdAt) }}
                                    </span>
                                </p>
                                <p v-else>
                                    No credit activity yet.
                                </p>
                                <p>
                                    {{ formatBytes(businessOverview.totalUsedStorageBytes) }} used ·
                                    {{ formatBytes(businessOverview.totalFreeStorageBytes) }} free
                                </p>
                            </div>

                        </div>
                    </div>
                </section>

                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(320px,0.85fr)]">
                    <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                        <div class="flex flex-col gap-3 border-b border-black/5 pb-4 md:flex-row md:items-end md:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                    Event portfolio
                                </h2>
                                <p class="mt-1 text-sm text-zinc-600">
                                    Create events, filter the portfolio, and jump straight into the right workspace.
                                </p>
                            </div>
                            <p class="text-sm text-zinc-500">
                                {{ filters.ownedEventCount }} of {{ filters.ownedEventTotalCount }} shown
                            </p>
                        </div>

                        <div class="space-y-4 pt-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <form class="flex w-full flex-col gap-3 md:max-w-xl md:flex-row" @submit.prevent="applyFilters">
                                    <div class="relative flex-1">
                                        <Search class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-zinc-400" />
                                        <Input
                                            v-model="search"
                                            type="search"
                                            placeholder="Search events, plans, billing, or status"
                                            class="h-11 rounded-full border-black/10 bg-white pr-4 pl-10"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button type="submit" class="bg-[#171411] text-white hover:bg-[#2b2621]">
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
                                            ? 'border-[#171411] bg-[#171411] text-white'
                                            : 'border-black/10 bg-white text-zinc-700 hover:border-black/20 hover:bg-[#faf7f1]'
                                    "
                                >
                                    <span>{{ option.label }}</span>
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs"
                                        :class="
                                            option.value === filters.status
                                                ? 'bg-white/12 text-white'
                                                : 'bg-[#fbfaf7] text-zinc-500'
                                        "
                                    >
                                        {{ option.count }}
                                    </span>
                                </Link>
                            </div>

                            <div class="flex flex-col gap-3 border-t border-black/5 pt-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="text-sm text-zinc-600">
                                        Batch actions target
                                        <span class="font-semibold text-[#171411]">{{ selectionLabel }}</span>
                                    </p>
                                    <p v-if="allFilteredSelected" class="mt-1 text-xs text-zinc-500">
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
                                        v-if="allFilteredSelected || selectedEventIds.length > 0"
                                        type="button"
                                        variant="outline"
                                        @click="clearSelection"
                                    >
                                        <X class="size-4" />
                                        Clear selection
                                    </Button>
                                    <Button
                                        v-if="filters.ownedEventCount > 0"
                                        type="button"
                                        class="bg-[#171411] text-white hover:bg-[#2b2621]"
                                        @click="startBulkExports"
                                    >
                                        <Download class="size-4" />
                                        Start exports
                                    </Button>
                                    <Button
                                        v-if="filters.ownedEventCount > 0"
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

                        <div v-if="ownedEvents.length === 0" class="py-12 text-center">
                            <div class="mx-auto max-w-md space-y-2">
                                <h3 class="text-lg font-semibold text-[#171411]">
                                    {{ filters.hasActiveFilters ? 'No workspaces match these filters' : 'No owned events yet' }}
                                </h3>
                                <p class="text-sm leading-6 text-zinc-600">
                                    {{
                                        filters.hasActiveFilters
                                            ? 'Try a broader search or switch back to all workspaces.'
                                            : 'Your first business event will appear here with quick routes into workspace, media, billing, and export.'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="divide-y divide-black/5 pt-3">
                            <article
                                v-for="event in ownedEvents"
                                :key="event.id"
                                class="py-4"
                            >
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <label class="mr-2 inline-flex items-center gap-2 text-sm font-medium text-[#171411]" :class="allFilteredSelected ? 'opacity-70' : ''">
                                                <Checkbox
                                                    :checked="allFilteredSelected || selectedEventIds.includes(event.id)"
                                                    :disabled="allFilteredSelected"
                                                    @update:checked="toggleEventSelection(event.id)"
                                                />
                                                Select
                                            </label>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.statusTone)">
                                                {{ event.statusLabel }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.billingTone)">
                                                {{ event.billingLabel }}
                                            </span>
                                        </div>

                                        <div class="mt-3">
                                            <h3 class="text-base font-semibold text-[#171411]">
                                                {{ event.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-zinc-600">
                                                {{ event.plan }} · {{ formatDateOnly(event.eventDate) }} · {{ event.timezone }}
                                            </p>
                                            <p class="mt-1 text-sm text-zinc-500">
                                                {{ event.guestCount }} guests · {{ event.assetCount }} uploads · {{ event.processingCount }} pending review
                                                <span v-if="event.mediaExportStatus === 'ready'"> · Export ready</span>
                                                <span v-if="event.lastUploadAt"> · Last upload {{ formatDateTime(event.lastUploadAt) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2 xl:max-w-[320px] xl:justify-end">
                                        <Button as-child size="sm" class="bg-[#171411] text-white hover:bg-[#2b2621]">
                                            <Link :href="event.primaryAction.url">
                                                {{ event.primaryAction.label }}
                                            </Link>
                                        </Button>
                                        <Button as-child size="sm" variant="outline">
                                            <Link :href="event.links.media">
                                                <Camera class="size-4" />
                                                Media
                                            </Link>
                                        </Button>
                                        <Button v-if="!event.isPaid" as-child size="sm" variant="outline">
                                            <Link :href="event.links.billing">
                                                <CreditCard class="size-4" />
                                                Billing
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="event.canManage && event.mediaExportStatus === 'ready'"
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="event.links.mediaExportDownload">
                                                <Download class="size-4" />
                                                Export
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div
                            v-if="ownedEventsPagination.lastPage > 1"
                            class="mt-4 flex flex-col gap-3 border-t border-black/5 pt-4 md:flex-row md:items-center md:justify-between"
                        >
                            <p class="text-sm text-zinc-600">
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
                                <span class="text-sm font-medium text-zinc-600">
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
                    </section>

                    <div class="space-y-5">
                        <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                            <div class="flex items-start justify-between gap-3 border-b border-black/5 pb-4">
                                <div>
                                    <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                        Needs attention
                                    </h2>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        The workspaces that need action first.
                                    </p>
                                </div>
                                <span class="inline-flex rounded-full bg-[#fbfaf7] px-2.5 py-1 text-xs font-semibold text-zinc-600">
                                    {{ businessAttentionSummary.visibleCount }}
                                </span>
                            </div>

                            <div v-if="businessAttentionEvents.length === 0" class="py-8 text-sm leading-6 text-zinc-600">
                                Nothing urgent right now.
                            </div>

                            <div v-else class="divide-y divide-black/5 pt-2">
                                <article
                                    v-for="event in businessAttentionEvents"
                                    :key="event.id"
                                    class="py-4"
                                >
                                    <div class="flex flex-col gap-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.statusTone)">
                                                {{ event.statusLabel }}
                                            </span>
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[0.68rem] font-semibold" :class="badgeClass(event.billingTone)">
                                                {{ event.billingLabel }}
                                            </span>
                                            <span class="inline-flex rounded-full bg-[#171411] px-2.5 py-1 text-[0.68rem] font-semibold text-white">
                                                {{ event.attentionLabel }}
                                            </span>
                                        </div>

                                        <div>
                                            <h3 class="text-base font-semibold text-[#171411]">
                                                {{ event.name }}
                                            </h3>
                                            <p class="mt-1 text-sm text-zinc-600">
                                                {{ event.plan }} · {{ event.attentionDetail }}
                                            </p>
                                            <p class="mt-1 text-xs text-zinc-500">
                                                {{
                                                    event.paymentDueAt
                                                        ? `Due ${formatDateOnly(event.paymentDueAt)}`
                                                        : 'No due date set'
                                                }}
                                                · {{ event.assetCount }} uploads
                                                · {{ formatBytes(event.storageUsedBytes) }} of {{ formatBytes(event.storageLimitBytes) }}
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.dashboard">
                                                    Open workspace
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.media">
                                                    <Camera class="size-4" />
                                                    Media
                                                </Link>
                                            </Button>
                                            <Button as-child size="sm" variant="outline">
                                                <Link :href="event.links.settings">
                                                    <Settings class="size-4" />
                                                    Settings
                                                </Link>
                                            </Button>
                                            <Button v-if="event.billingTone !== 'emerald'" as-child size="sm" variant="outline">
                                                <Link :href="event.links.billing">
                                                    <CreditCard class="size-4" />
                                                    Billing
                                                </Link>
                                            </Button>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                            <div class="flex flex-col gap-2 border-b border-black/5 pb-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <h2 class="text-base font-semibold text-[#171411] sm:text-lg">
                                        Wallet activity
                                    </h2>
                                    <p class="mt-1 text-sm text-zinc-600">
                                        Recent credit movement for the business account.
                                    </p>
                                </div>
                                <Button as-child size="sm" variant="outline">
                                    <Link :href="businessActionLinks.topUpWallet">
                                        Top up credits
                                    </Link>
                                </Button>
                            </div>

                            <div v-if="walletActivity.length === 0" class="py-8 text-sm leading-6 text-zinc-600">
                                No credit activity yet.
                            </div>

                            <div v-else class="divide-y divide-black/5 pt-2">
                                <article
                                    v-for="item in walletActivity"
                                    :key="item.id"
                                    class="flex flex-col gap-2 py-3"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-[#171411]">
                                                {{ walletActivityLabel(item) }}
                                            </p>
                                            <p class="mt-1 text-sm text-zinc-600">
                                                {{ item.description }}
                                                <span v-if="item.eventName"> · {{ item.eventName }}</span>
                                            </p>
                                        </div>
                                        <p class="shrink-0 text-xs text-zinc-500">
                                            {{ formatDateTime(item.createdAt) }}
                                        </p>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
