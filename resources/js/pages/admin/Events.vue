<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { badgeClass, formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type { Tone } from '@/types/dashboard';

type AdminEventRow = {
    id: number;
    name: string;
    ownerName: string;
    ownerEmail: string;
    planName: string;
    planPriceLabel: string;
    statusLabel: string;
    statusTone: Tone;
    billingLabel: string;
    billingTone: Tone;
    isPaid: boolean;
    assetCount: number;
    createdAt: string | null;
    links: {
        settings: string;
    };
};

const props = defineProps<{
    events: AdminEventRow[];
    pagination: {
        currentPage: number;
        lastPage: number;
        total: number;
        prevPageUrl: string | null;
        nextPageUrl: string | null;
    };
    filters: {
        search: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Events',
        href: '/admin/events',
    },
];

const search = ref(props.filters.search);

const submitSearch = (): void => {
    router.get(
        '/admin/events',
        search.value.trim() === '' ? {} : { search: search.value.trim() },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head title="Admin · Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section class="dashboard-panel">
                    <div
                        class="dashboard-panel-divider flex flex-col gap-4 pb-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p class="dashboard-eyebrow">Admin</p>
                            <h1 class="dashboard-title mt-2">Events</h1>
                            <p class="dashboard-body mt-2">
                                {{ pagination.total }} event(s) across the
                                platform.
                            </p>
                        </div>

                        <form
                            class="flex w-full max-w-sm items-center gap-2"
                            @submit.prevent="submitSearch"
                        >
                            <Input
                                v-model="search"
                                type="search"
                                placeholder="Search by event or owner"
                                aria-label="Search events"
                            />
                            <Button
                                type="submit"
                                size="sm"
                                class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            >
                                <Search class="size-4" />
                                Search
                            </Button>
                        </form>
                    </div>

                    <div
                        v-if="events.length === 0"
                        class="dashboard-body py-10"
                    >
                        No events match this search.
                    </div>

                    <div v-else class="overflow-x-auto pt-2">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr
                                    class="border-b border-brand-border/70 text-[0.72rem] font-semibold tracking-wide text-brand-muted uppercase"
                                >
                                    <th class="py-3 pr-4">Event</th>
                                    <th class="py-3 pr-4">Owner</th>
                                    <th class="py-3 pr-4">Plan</th>
                                    <th class="py-3 pr-4">Billing</th>
                                    <th class="py-3 pr-4">Uploads</th>
                                    <th class="py-3 pr-4">Created</th>
                                    <th class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border/70">
                                <tr v-for="event in events" :key="event.id">
                                    <td class="py-3 pr-4">
                                        <p class="font-semibold text-brand-ink">
                                            {{ event.name }}
                                        </p>
                                        <span
                                            class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[0.7rem] font-semibold"
                                            :class="
                                                badgeClass(event.statusTone)
                                            "
                                        >
                                            {{ event.statusLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ event.ownerEmail }}
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ event.planName }}
                                        <p class="dashboard-meta">
                                            {{ event.planPriceLabel }}
                                        </p>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-[0.7rem] font-semibold"
                                            :class="
                                                badgeClass(event.billingTone)
                                            "
                                        >
                                            {{ event.billingLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ event.assetCount }}
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ formatDateTime(event.createdAt) }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="event.links.settings">
                                                Billing settings
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="pagination.lastPage > 1"
                        class="mt-4 flex items-center justify-between border-t border-brand-border/70 pt-4"
                    >
                        <p class="dashboard-meta">
                            Page {{ pagination.currentPage }} of
                            {{ pagination.lastPage }}
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
                                    Previous
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
                                    Next
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
