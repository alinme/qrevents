<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BadgeCheck,
    KeyRound,
    ShieldCheck,
    Trash2,
    UserRoundCog,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import { badgeClass, formatDateTime } from '@/lib/dashboard';
import type { BreadcrumbItem } from '@/types';
import type { Tone } from '@/types/dashboard';

type Profile = {
    id: number;
    name: string;
    email: string;
    accountType: string;
    accountTypeLabel: string;
    accountTypeTone: Tone;
    isVerified: boolean;
    verifiedAt: string | null;
    twoFactorEnabled: boolean;
    hasGoogle: boolean;
    eventCount: number;
    businessWalletCredits: number;
    createdAt: string | null;
};

type EventRow = {
    id: number;
    name: string;
    status: string;
    planName: string;
    isPaid: boolean;
    assetCount: number;
    createdAt: string | null;
    links: { settings: string };
};

type AuditRow = {
    id: number;
    action: string;
    adminName: string;
    targetLabel: string | null;
    meta: Record<string, unknown> | null;
    createdAt: string | null;
};

const props = defineProps<{
    profile: Profile;
    events: EventRow[];
    auditEntries: AuditRow[];
    accountTypes: { value: string; label: string }[];
    canImpersonate: boolean;
    canDelete: boolean;
    canChangeAccountType: boolean;
    links: {
        updateAccountType: string;
        verify: string;
        impersonate: string;
        destroy: string;
    };
}>();

const { t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    { title: t('admin.shared.admin'), href: '/admin' },
    { title: t('admin.users.title'), href: '/admin/users' },
    { title: props.profile.name, href: '#' },
];

const selectedType = ref(props.profile.accountType);
const savingType = ref(false);
const confirmingDelete = ref(false);

const saveAccountType = (): void => {
    if (selectedType.value === props.profile.accountType) {
        return;
    }

    router.patch(
        props.links.updateAccountType,
        { account_type: selectedType.value },
        {
            preserveScroll: true,
            onStart: () => (savingType.value = true),
            onFinish: () => (savingType.value = false),
        },
    );
};

const verifyUser = (): void => {
    router.post(props.links.verify, {}, { preserveScroll: true });
};

const impersonateUser = (): void => {
    router.post(props.links.impersonate, {});
};

const deleteUser = (): void => {
    router.delete(props.links.destroy);
};
</script>

<template>
    <Head :title="`${profile.name} · ${t('admin.users.title')}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-5xl">
                <section class="dashboard-panel">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <p class="dashboard-eyebrow">
                                {{ t('admin.users.detail.eyebrow') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ profile.name }}
                            </h1>
                            <p class="dashboard-body mt-1">{{ profile.email }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[0.72rem] font-semibold"
                                    :class="badgeClass(profile.accountTypeTone)"
                                >
                                    {{ profile.accountTypeLabel }}
                                </span>
                                <span
                                    v-if="profile.isVerified"
                                    class="inline-flex items-center gap-1 text-[0.72rem] font-semibold text-emerald-600"
                                >
                                    <ShieldCheck class="size-3.5" />
                                    {{ t('admin.users.verified_yes') }}
                                </span>
                                <span
                                    v-if="profile.twoFactorEnabled"
                                    class="inline-flex items-center gap-1 text-[0.72rem] font-semibold text-brand-muted"
                                >
                                    <KeyRound class="size-3.5" /> 2FA
                                </span>
                            </div>
                        </div>

                        <Button
                            v-if="canImpersonate"
                            size="sm"
                            class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            @click="impersonateUser"
                        >
                            <UserRoundCog class="size-4" />
                            {{ t('admin.users.actions.impersonate') }}
                        </Button>
                    </div>

                    <dl
                        class="mt-6 grid gap-x-6 gap-y-4 border-t border-brand-border/70 pt-4 sm:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <dt class="dashboard-eyebrow">
                                {{ t('admin.users.detail.events') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold text-brand-ink">
                                {{ profile.eventCount }}
                            </dd>
                        </div>
                        <div>
                            <dt class="dashboard-eyebrow">
                                {{ t('admin.users.detail.wallet') }}
                            </dt>
                            <dd class="mt-1 text-lg font-semibold text-brand-ink">
                                {{ profile.businessWalletCredits }}
                            </dd>
                        </div>
                        <div>
                            <dt class="dashboard-eyebrow">
                                {{ t('admin.users.detail.google') }}
                            </dt>
                            <dd class="mt-1 text-sm text-brand-muted">
                                {{ profile.hasGoogle ? 'Yes' : 'No' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="dashboard-eyebrow">
                                {{ t('admin.users.detail.joined') }}
                            </dt>
                            <dd class="mt-1 text-sm text-brand-muted">
                                {{ formatDateTime(profile.createdAt) }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="dashboard-panel">
                    <h2 class="dashboard-section-title">
                        {{ t('admin.users.detail.controls') }}
                    </h2>

                    <div class="mt-4 flex flex-col gap-4">
                        <div
                            class="flex flex-col gap-2 sm:flex-row sm:items-end sm:gap-3"
                        >
                            <label class="flex-1">
                                <span class="dashboard-eyebrow">
                                    {{ t('admin.users.detail.account_type') }}
                                </span>
                                <select
                                    v-model="selectedType"
                                    :disabled="!canChangeAccountType"
                                    class="mt-1 h-9 w-full rounded-md border border-brand-border bg-transparent px-3 text-sm disabled:opacity-50"
                                >
                                    <option
                                        v-for="option in accountTypes"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <Button
                                size="sm"
                                variant="outline"
                                :disabled="
                                    !canChangeAccountType ||
                                    savingType ||
                                    selectedType === profile.accountType
                                "
                                @click="saveAccountType"
                            >
                                {{ t('admin.users.actions.save_type') }}
                            </Button>
                        </div>
                        <p
                            v-if="!canChangeAccountType"
                            class="dashboard-meta"
                        >
                            {{ t('admin.users.detail.self_note') }}
                        </p>

                        <div
                            class="flex flex-wrap gap-2 border-t border-brand-border/70 pt-4"
                        >
                            <Button
                                v-if="!profile.isVerified"
                                size="sm"
                                variant="outline"
                                @click="verifyUser"
                            >
                                <BadgeCheck class="size-4" />
                                {{ t('admin.users.actions.verify') }}
                            </Button>

                            <template v-if="canDelete">
                                <Button
                                    v-if="!confirmingDelete"
                                    size="sm"
                                    variant="outline"
                                    class="border-rose-300 text-rose-600 hover:bg-rose-50"
                                    @click="confirmingDelete = true"
                                >
                                    <Trash2 class="size-4" />
                                    {{ t('admin.users.actions.delete') }}
                                </Button>
                                <div
                                    v-else
                                    class="flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2"
                                >
                                    <span class="text-xs font-medium text-rose-700">
                                        {{ t('admin.users.actions.delete_confirm') }}
                                    </span>
                                    <Button
                                        size="sm"
                                        class="bg-rose-600 text-white hover:bg-rose-700"
                                        @click="deleteUser"
                                    >
                                        {{ t('admin.users.actions.delete_yes') }}
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        @click="confirmingDelete = false"
                                    >
                                        {{ t('admin.users.actions.cancel') }}
                                    </Button>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <h2 class="dashboard-section-title">
                        {{ t('admin.users.detail.events_title') }}
                    </h2>
                    <div
                        v-if="events.length === 0"
                        class="dashboard-body py-6"
                    >
                        {{ t('admin.users.detail.events_empty') }}
                    </div>
                    <div v-else class="divide-y divide-brand-border/70">
                        <div
                            v-for="event in events"
                            :key="event.id"
                            class="flex items-center justify-between gap-3 py-3"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-brand-ink">
                                    {{ event.name }}
                                </p>
                                <p class="dashboard-meta">
                                    {{ event.status }} · {{ event.planName }} ·
                                    {{ event.assetCount }} uploads
                                </p>
                            </div>
                            <Button as-child size="sm" variant="outline">
                                <Link :href="event.links.settings">
                                    {{ t('admin.users.detail.open_event') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>

                <section class="dashboard-panel">
                    <h2 class="dashboard-section-title">
                        {{ t('admin.users.detail.audit_title') }}
                    </h2>
                    <div
                        v-if="auditEntries.length === 0"
                        class="dashboard-body py-6"
                    >
                        {{ t('admin.users.detail.audit_empty') }}
                    </div>
                    <ul v-else class="mt-2 divide-y divide-brand-border/70">
                        <li
                            v-for="entry in auditEntries"
                            :key="entry.id"
                            class="flex items-center justify-between gap-3 py-2 text-sm"
                        >
                            <span class="text-brand-ink">
                                <span class="font-semibold">{{ entry.adminName }}</span>
                                — {{ entry.action }}
                            </span>
                            <span class="dashboard-meta shrink-0">
                                {{ formatDateTime(entry.createdAt) }}
                            </span>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
