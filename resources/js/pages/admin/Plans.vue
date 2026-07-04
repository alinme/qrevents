<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import { useTranslations } from '@/composables/useTranslations';
import type { BreadcrumbItem } from '@/types';

type AdminPlanRow = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    currency: string;
    priceCents: number;
    priceLabel: string;
    storageLimitGb: number;
    storageLimitLabel: string;
    uploadLimit: number;
    retentionDays: number;
    graceDays: number;
    uploadWindowDays: number;
    customizationTier: string;
    videoMaxDurationSeconds: number;
    photoMaxSizeMb: number;
    videoMaxSizeMb: number;
    downloadAllEnabled: boolean;
    moderationToolsEnabled: boolean;
    removeAppBranding: boolean;
    isActive: boolean;
    isDefault: boolean;
    eventCount: number;
    links: {
        update: string;
    };
};

const props = defineProps<{
    plans: AdminPlanRow[];
    planStoreUrl: string;
}>();

const { t } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('admin.shared.admin'),
        href: '/admin',
    },
    {
        title: t('admin.plans.title'),
        href: '/admin/plans',
    },
];

const editingPlanId = ref<number | null>(null);
const formOpen = ref(false);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    currency: 'EUR',
    price_cents: 0,
    storage_limit_gb: 5,
    upload_limit: 500,
    retention_days: 90,
    grace_days: 7,
    upload_window_days: 30,
    customization_tier: 'basic',
    video_max_duration_seconds: 45,
    photo_max_size_mb: 25,
    video_max_size_mb: 500,
    download_all_enabled: false,
    moderation_tools_enabled: false,
    remove_app_branding: false,
    is_active: true,
    is_default: false,
});

const isEditing = computed(() => editingPlanId.value !== null);

const formTitle = computed(() =>
    isEditing.value
        ? t('admin.plans.form.edit_title')
        : t('admin.plans.form.create_title'),
);

const resetForm = (): void => {
    editingPlanId.value = null;
    form.reset();
    form.clearErrors();
};

const openCreate = (): void => {
    resetForm();
    formOpen.value = true;
};

const editPlan = (plan: AdminPlanRow): void => {
    editingPlanId.value = plan.id;
    formOpen.value = true;
    form.clearErrors();
    form.name = plan.name;
    form.slug = plan.slug;
    form.description = plan.description ?? '';
    form.currency = plan.currency;
    form.price_cents = plan.priceCents;
    form.storage_limit_gb = plan.storageLimitGb;
    form.upload_limit = plan.uploadLimit;
    form.retention_days = plan.retentionDays;
    form.grace_days = plan.graceDays;
    form.upload_window_days = plan.uploadWindowDays;
    form.customization_tier = plan.customizationTier;
    form.video_max_duration_seconds = plan.videoMaxDurationSeconds;
    form.photo_max_size_mb = plan.photoMaxSizeMb;
    form.video_max_size_mb = plan.videoMaxSizeMb;
    form.download_all_enabled = plan.downloadAllEnabled;
    form.moderation_tools_enabled = plan.moderationToolsEnabled;
    form.remove_app_branding = plan.removeAppBranding;
    form.is_active = plan.isActive;
    form.is_default = plan.isDefault;
};

const submit = (): void => {
    const editingPlan = props.plans.find(
        (plan) => plan.id === editingPlanId.value,
    );

    const onSuccess = (): void => {
        resetForm();
        formOpen.value = false;
    };

    if (editingPlan) {
        form.patch(editingPlan.links.update, {
            preserveScroll: true,
            onSuccess,
        });

        return;
    }

    form.post(props.planStoreUrl, {
        preserveScroll: true,
        onSuccess,
    });
};

const planPayload = (plan: AdminPlanRow): Record<string, unknown> => ({
    name: plan.name,
    slug: plan.slug,
    description: plan.description ?? '',
    currency: plan.currency,
    price_cents: plan.priceCents,
    storage_limit_gb: plan.storageLimitGb,
    upload_limit: plan.uploadLimit,
    retention_days: plan.retentionDays,
    grace_days: plan.graceDays,
    upload_window_days: plan.uploadWindowDays,
    customization_tier: plan.customizationTier,
    video_max_duration_seconds: plan.videoMaxDurationSeconds,
    photo_max_size_mb: plan.photoMaxSizeMb,
    video_max_size_mb: plan.videoMaxSizeMb,
    download_all_enabled: plan.downloadAllEnabled,
    moderation_tools_enabled: plan.moderationToolsEnabled,
    remove_app_branding: plan.removeAppBranding,
    is_active: plan.isActive,
    is_default: plan.isDefault,
});

const toggleActive = (plan: AdminPlanRow): void => {
    const nextActive = !plan.isActive;

    router.patch(
        plan.links.update,
        {
            ...planPayload(plan),
            is_active: nextActive,
            // A default plan must stay active, so deactivating clears the flag.
            is_default: nextActive ? plan.isDefault : false,
        },
        { preserveScroll: true },
    );
};

const makeDefault = (plan: AdminPlanRow): void => {
    router.patch(
        plan.links.update,
        {
            ...planPayload(plan),
            is_active: true,
            is_default: true,
        },
        { preserveScroll: true },
    );
};

const planMeta = (plan: AdminPlanRow): string =>
    [
        plan.storageLimitLabel,
        t('admin.plans.meta.uploads', { count: plan.uploadLimit }),
        t('admin.plans.meta.upload_window', { days: plan.uploadWindowDays }),
        t('admin.plans.meta.retention', { days: plan.retentionDays }),
        t('admin.plans.meta.events', { count: plan.eventCount }),
    ].join(' · ');
</script>

<template>
    <Head :title="t('admin.plans.head_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-4xl">
                <section class="dashboard-panel">
                    <div
                        class="dashboard-panel-divider flex flex-col gap-4 pb-4 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <p class="dashboard-eyebrow">
                                {{ t('admin.shared.admin') }}
                            </p>
                            <h1 class="dashboard-title mt-2">
                                {{ t('admin.plans.title') }}
                            </h1>
                            <p class="dashboard-body mt-2">
                                {{ t('admin.plans.description') }}
                            </p>
                        </div>
                        <Button
                            size="sm"
                            class="shrink-0 rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />
                            {{ t('admin.plans.form.create_title') }}
                        </Button>
                    </div>

                        <div
                            v-if="plans.length === 0"
                            class="dashboard-body py-10"
                        >
                            {{ t('admin.plans.empty') }}
                        </div>

                        <div v-else class="divide-y divide-brand-border/70">
                            <article
                                v-for="plan in plans"
                                :key="plan.id"
                                class="py-4"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <h2
                                                class="text-sm font-semibold text-brand-ink sm:text-base"
                                            >
                                                {{ plan.name }}
                                            </h2>
                                            <span
                                                class="dashboard-chip"
                                                :class="{
                                                    'bg-emerald-100 text-emerald-800':
                                                        plan.isActive,
                                                    'bg-zinc-100 text-zinc-700':
                                                        !plan.isActive,
                                                }"
                                            >
                                                {{
                                                    plan.isActive
                                                        ? t(
                                                              'admin.plans.status.active',
                                                          )
                                                        : t(
                                                              'admin.plans.status.inactive',
                                                          )
                                                }}
                                            </span>
                                            <span
                                                v-if="plan.isDefault"
                                                class="dashboard-chip bg-sky-100 text-sky-800"
                                            >
                                                {{
                                                    t(
                                                        'admin.plans.status.default',
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <p
                                            class="mt-1 text-sm font-semibold text-brand-ink"
                                        >
                                            {{ plan.priceLabel }}
                                        </p>
                                        <p class="dashboard-meta mt-1">
                                            {{ planMeta(plan) }}
                                        </p>
                                        <p
                                            v-if="plan.description"
                                            class="mt-1 text-sm text-brand-muted"
                                        >
                                            {{ plan.description }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex shrink-0 flex-wrap gap-2 sm:justify-end"
                                    >
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="editPlan(plan)"
                                        >
                                            {{ t('admin.plans.actions.edit') }}
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="toggleActive(plan)"
                                        >
                                            {{
                                                plan.isActive
                                                    ? t(
                                                          'admin.plans.actions.deactivate',
                                                      )
                                                    : t(
                                                          'admin.plans.actions.activate',
                                                      )
                                            }}
                                        </Button>
                                        <Button
                                            v-if="!plan.isDefault"
                                            size="sm"
                                            variant="outline"
                                            @click="makeDefault(plan)"
                                        >
                                            {{
                                                t(
                                                    'admin.plans.actions.make_default',
                                                )
                                            }}
                                        </Button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>

                    <Sheet v-model:open="formOpen">
                        <SheetContent
                            side="right"
                            class="w-full overflow-y-auto sm:max-w-xl"
                        >
                            <SheetHeader>
                                <SheetTitle>{{ formTitle }}</SheetTitle>
                                <SheetDescription>
                                    {{ t('admin.plans.description') }}
                                </SheetDescription>
                            </SheetHeader>

                            <form class="mt-6 space-y-4" @submit.prevent="submit">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label for="plan-name">{{
                                        t('admin.plans.form.name')
                                    }}</Label>
                                    <Input
                                        id="plan-name"
                                        v-model="form.name"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-slug">{{
                                        t('admin.plans.form.slug')
                                    }}</Label>
                                    <Input id="plan-slug" v-model="form.slug" />
                                    <p
                                        v-if="form.errors.slug"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.slug }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="plan-description">{{
                                    t('admin.plans.form.description')
                                }}</Label>
                                <Input
                                    id="plan-description"
                                    v-model="form.description"
                                />
                                <p
                                    v-if="form.errors.description"
                                    class="text-sm text-rose-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <Label for="plan-currency">{{
                                        t('admin.plans.form.currency')
                                    }}</Label>
                                    <Input
                                        id="plan-currency"
                                        v-model="form.currency"
                                        maxlength="3"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.currency"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.currency }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-price">{{
                                        t('admin.plans.form.price_cents')
                                    }}</Label>
                                    <Input
                                        id="plan-price"
                                        v-model.number="form.price_cents"
                                        type="number"
                                        min="0"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.price_cents"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.price_cents }}
                                    </p>
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-storage">{{
                                        t('admin.plans.form.storage_gb')
                                    }}</Label>
                                    <Input
                                        id="plan-storage"
                                        v-model.number="form.storage_limit_gb"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.storage_limit_gb"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.storage_limit_gb }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <Label for="plan-uploads">{{
                                        t('admin.plans.form.upload_limit')
                                    }}</Label>
                                    <Input
                                        id="plan-uploads"
                                        v-model.number="form.upload_limit"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-window">{{
                                        t('admin.plans.form.upload_window_days')
                                    }}</Label>
                                    <Input
                                        id="plan-window"
                                        v-model.number="form.upload_window_days"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-retention">{{
                                        t('admin.plans.form.retention_days')
                                    }}</Label>
                                    <Input
                                        id="plan-retention"
                                        v-model.number="form.retention_days"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <Label for="plan-grace">{{
                                        t('admin.plans.form.grace_days')
                                    }}</Label>
                                    <Input
                                        id="plan-grace"
                                        v-model.number="form.grace_days"
                                        type="number"
                                        min="0"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-video-duration">{{
                                        t('admin.plans.form.video_max_seconds')
                                    }}</Label>
                                    <Input
                                        id="plan-video-duration"
                                        v-model.number="
                                            form.video_max_duration_seconds
                                        "
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-tier">{{
                                        t('admin.plans.form.customization_tier')
                                    }}</Label>
                                    <select
                                        id="plan-tier"
                                        v-model="form.customization_tier"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                    >
                                        <option value="basic">
                                            {{
                                                t('admin.plans.form.tier.basic')
                                            }}
                                        </option>
                                        <option value="better">
                                            {{
                                                t(
                                                    'admin.plans.form.tier.better',
                                                )
                                            }}
                                        </option>
                                        <option value="advanced">
                                            {{
                                                t(
                                                    'admin.plans.form.tier.advanced',
                                                )
                                            }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.customization_tier"
                                        class="text-sm text-rose-600"
                                    >
                                        {{ form.errors.customization_tier }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label for="plan-photo-size">{{
                                        t('admin.plans.form.photo_max_mb')
                                    }}</Label>
                                    <Input
                                        id="plan-photo-size"
                                        v-model.number="form.photo_max_size_mb"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-video-size">{{
                                        t('admin.plans.form.video_max_mb')
                                    }}</Label>
                                    <Input
                                        id="plan-video-size"
                                        v-model.number="form.video_max_size_mb"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                            </div>

                            <div
                                class="space-y-3 border-t border-brand-border/70 pt-4"
                            >
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    {{
                                        t(
                                            'admin.plans.form.toggles.download_all',
                                        )
                                    }}
                                    <Switch
                                        v-model="form.download_all_enabled"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    {{
                                        t(
                                            'admin.plans.form.toggles.moderation_tools',
                                        )
                                    }}
                                    <Switch
                                        v-model="form.moderation_tools_enabled"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    {{
                                        t(
                                            'admin.plans.form.toggles.remove_branding',
                                        )
                                    }}
                                    <Switch
                                        v-model="form.remove_app_branding"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    {{ t('admin.plans.form.toggles.active') }}
                                    <Switch v-model="form.is_active" />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    {{ t('admin.plans.form.toggles.default') }}
                                    <Switch v-model="form.is_default" />
                                </label>
                                <p
                                    v-if="form.errors.is_active"
                                    class="text-sm text-rose-600"
                                >
                                    {{ form.errors.is_active }}
                                </p>
                            </div>

                            <Button
                                type="submit"
                                class="w-full rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                                :disabled="form.processing"
                            >
                                {{
                                    isEditing
                                        ? t('admin.plans.form.save')
                                        : t('admin.plans.form.create_title')
                                }}
                            </Button>
                            </form>
                        </SheetContent>
                    </Sheet>
            </div>
        </div>
    </AppLayout>
</template>
