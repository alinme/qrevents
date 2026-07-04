<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
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
    status: string;
    isSuspended: boolean;
    assetCount: number;
    createdAt: string | null;
    links: {
        settings: string;
        suspend: string;
        extend: string;
        destroy: string;
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
        status: string;
    };
    statusOptions: { value: string; label: string }[];
}>();

const { t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('admin.shared.admin'),
        href: '/admin',
    },
    {
        title: t('admin.events.title'),
        href: '/admin/events',
    },
];

const search = ref(props.filters.search);
const deleteTarget = ref<AdminEventRow | null>(null);

const query = (overrides: Record<string, string>): void => {
    const params: Record<string, string> = {};
    const nextSearch =
        overrides.search !== undefined ? overrides.search : search.value.trim();
    const nextStatus =
        overrides.status !== undefined ? overrides.status : props.filters.status;

    if (nextSearch !== '') {
        params.search = nextSearch;
    }
    if (nextStatus !== '') {
        params.status = nextStatus;
    }

    router.get('/admin/events', params, { preserveState: true, replace: true });
};

const submitSearch = (): void => query({ search: search.value.trim() });
const filterByStatus = (status: string): void =>
    query({ status: props.filters.status === status ? '' : status });

const suspendEvent = (event: AdminEventRow): void => {
    router.post(event.links.suspend, {}, { preserveScroll: true });
};

const extendEvent = (event: AdminEventRow): void => {
    router.patch(event.links.extend, { days: 30 }, { preserveScroll: true });
};

const confirmDelete = (): void => {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(deleteTarget.value.links.destroy, {
        preserveScroll: true,
        onFinish: () => (deleteTarget.value = null),
    });
};
</script>

<template>
    <Head :title="t('admin.events.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <section>
                    <div
                        class="dashboard-panel-divider flex flex-col gap-4 pb-4 lg:flex-row lg:items-end lg:justify-between"
                    >
                        <div>
                            <p class="dashboard-eyebrow">
                                {{ t('admin.shared.admin') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('admin.events.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{
                                    t('admin.events.total', {
                                        count: pagination.total,
                                    })
                                }}
                            </p>
                        </div>

                        <form
                            class="flex w-full max-w-sm items-center gap-2"
                            @submit.prevent="submitSearch"
                        >
                            <Input
                                v-model="search"
                                type="search"
                                :placeholder="
                                    t('admin.events.search.placeholder')
                                "
                                :aria-label="t('admin.events.search.aria')"
                            />
                            <Button
                                type="submit"
                                size="sm"
                                class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            >
                                <Search class="size-4" />
                                {{ t('admin.events.search.submit') }}
                            </Button>
                        </form>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-4">
                        <button
                            v-for="option in statusOptions"
                            :key="option.value"
                            type="button"
                            class="inline-flex rounded-full border px-3 py-1 text-[0.72rem] font-semibold transition"
                            :class="
                                filters.status === option.value
                                    ? 'border-brand-ink bg-brand-ink text-brand-inverse'
                                    : 'border-brand-border/70 text-brand-muted hover:border-brand-ink'
                            "
                            @click="filterByStatus(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <div
                        v-if="events.length === 0"
                        class="dashboard-body py-10"
                    >
                        {{ t('admin.events.empty') }}
                    </div>

                    <div v-else class="overflow-x-auto pt-2">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr
                                    class="border-b border-brand-border/70 text-[0.72rem] font-semibold tracking-wide text-brand-muted uppercase"
                                >
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.event') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.owner') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.plan') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.billing') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.uploads') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.events.table.created') }}
                                    </th>
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
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="size-8"
                                                    :aria-label="
                                                        t('admin.events.actions.menu')
                                                    "
                                                >
                                                    <MoreHorizontal
                                                        class="size-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="
                                                            event.links.settings
                                                        "
                                                    >
                                                        {{ t('admin.events.actions.open') }}
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="!event.isSuspended"
                                                    @click="suspendEvent(event)"
                                                >
                                                    {{ t('admin.events.actions.suspend') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    @click="extendEvent(event)"
                                                >
                                                    {{ t('admin.events.actions.extend') }}
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    class="text-rose-600 focus:text-rose-600"
                                                    @click="deleteTarget = event"
                                                >
                                                    {{ t('admin.events.actions.delete') }}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
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
                            {{
                                t('admin.events.pagination.page', {
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
                                    {{ t('admin.events.pagination.previous') }}
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
                                    {{ t('admin.events.pagination.next') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <Dialog
            :open="deleteTarget !== null"
            @update:open="(value) => { if (!value) deleteTarget = null }"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ t('admin.events.delete_dialog.title') }}</DialogTitle>
                    <DialogDescription>
                        {{
                            t('admin.events.delete_dialog.body', {
                                name: deleteTarget?.name ?? '',
                            })
                        }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="deleteTarget = null">
                        {{ t('admin.events.actions.cancel') }}
                    </Button>
                    <Button
                        class="bg-rose-600 text-white hover:bg-rose-700"
                        @click="confirmDelete"
                    >
                        {{ t('admin.events.delete_dialog.confirm') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
