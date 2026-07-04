<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { badgeClass, formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type { Tone } from '@/types/dashboard';

type AdminUserRow = {
    id: number;
    name: string;
    email: string;
    accountType: string;
    accountTypeLabel: string;
    accountTypeTone: Tone;
    eventCount: number;
    isVerified: boolean;
    twoFactorEnabled: boolean;
    createdAt: string | null;
    links: {
        show: string;
    };
};

const props = defineProps<{
    users: AdminUserRow[];
    pagination: {
        currentPage: number;
        lastPage: number;
        total: number;
        prevPageUrl: string | null;
        nextPageUrl: string | null;
    };
    filters: {
        search: string;
        type: string;
    };
    accountTypes: { value: string; label: string }[];
}>();

const { t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('admin.shared.admin'), href: '/admin' },
    { title: t('admin.users.title'), href: '/admin/users' },
];

const search = ref(props.filters.search);

const query = (overrides: Record<string, string>): void => {
    const params: Record<string, string> = {};
    const nextSearch =
        overrides.search !== undefined ? overrides.search : search.value.trim();
    const nextType =
        overrides.type !== undefined ? overrides.type : props.filters.type;

    if (nextSearch !== '') {
        params.search = nextSearch;
    }
    if (nextType !== '') {
        params.type = nextType;
    }

    router.get('/admin/users', params, {
        preserveState: true,
        replace: true,
    });
};

const submitSearch = (): void => query({ search: search.value.trim() });
const filterByType = (type: string): void =>
    query({ type: props.filters.type === type ? '' : type });
</script>

<template>
    <Head :title="t('admin.users.head_title')" />

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
                                {{ t('admin.users.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{
                                    t('admin.users.total', {
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
                                :placeholder="t('admin.users.search.placeholder')"
                                :aria-label="t('admin.users.search.aria')"
                            />
                            <Button
                                type="submit"
                                size="sm"
                                class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            >
                                <Search class="size-4" />
                                {{ t('admin.users.search.submit') }}
                            </Button>
                        </form>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-4">
                        <button
                            v-for="option in accountTypes"
                            :key="option.value"
                            type="button"
                            class="inline-flex rounded-full border px-3 py-1 text-[0.72rem] font-semibold transition"
                            :class="
                                filters.type === option.value
                                    ? 'border-brand-ink bg-brand-ink text-brand-inverse'
                                    : 'border-brand-border/70 text-brand-muted hover:border-brand-ink'
                            "
                            @click="filterByType(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <div
                        v-if="users.length === 0"
                        class="dashboard-body py-10"
                    >
                        {{ t('admin.users.empty') }}
                    </div>

                    <div v-else class="overflow-x-auto pt-2">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr
                                    class="border-b border-brand-border/70 text-[0.72rem] font-semibold tracking-wide text-brand-muted uppercase"
                                >
                                    <th class="py-3 pr-4">
                                        {{ t('admin.users.table.user') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.users.table.type') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.users.table.events') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.users.table.verified') }}
                                    </th>
                                    <th class="py-3 pr-4">
                                        {{ t('admin.users.table.joined') }}
                                    </th>
                                    <th class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-border/70">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="py-3 pr-4">
                                        <p class="font-semibold text-brand-ink">
                                            {{ user.name }}
                                        </p>
                                        <p class="dashboard-meta">
                                            {{ user.email }}
                                        </p>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span
                                            class="inline-flex rounded-full px-2 py-0.5 text-[0.7rem] font-semibold"
                                            :class="
                                                badgeClass(user.accountTypeTone)
                                            "
                                        >
                                            {{ user.accountTypeLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ user.eventCount }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span
                                            v-if="user.isVerified"
                                            class="inline-flex items-center gap-1 text-[0.72rem] font-semibold text-emerald-600"
                                        >
                                            <ShieldCheck class="size-3.5" />
                                            {{ t('admin.users.verified_yes') }}
                                        </span>
                                        <span
                                            v-else
                                            class="dashboard-meta"
                                        >
                                            {{ t('admin.users.verified_no') }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-brand-muted">
                                        {{ formatDateTime(user.createdAt) }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="user.links.show">
                                                {{ t('admin.users.manage') }}
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
                            {{
                                t('admin.users.pagination.page', {
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
                                    {{ t('admin.users.pagination.previous') }}
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
                                    {{ t('admin.users.pagination.next') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
