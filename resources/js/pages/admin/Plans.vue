<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Plans',
        href: '/admin/plans',
    },
];

const editingPlanId = ref<number | null>(null);

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
    isEditing.value ? 'Edit plan' : 'Create plan',
);

const resetForm = (): void => {
    editingPlanId.value = null;
    form.reset();
    form.clearErrors();
};

const editPlan = (plan: AdminPlanRow): void => {
    editingPlanId.value = plan.id;
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

    if (editingPlan) {
        form.patch(editingPlan.links.update, {
            preserveScroll: true,
            onSuccess: resetForm,
        });

        return;
    }

    form.post(props.planStoreUrl, {
        preserveScroll: true,
        onSuccess: resetForm,
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
        `${plan.uploadLimit} uploads`,
        `${plan.uploadWindowDays}-day window`,
        `${plan.retentionDays}-day retention`,
        `${plan.eventCount} event(s)`,
    ].join(' · ');
</script>

<template>
    <Head title="Admin · Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-6xl">
                <div
                    class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(360px,1fr)]"
                >
                    <section class="dashboard-panel">
                        <div class="dashboard-panel-divider pb-4">
                            <p class="dashboard-eyebrow">Admin</p>
                            <h1 class="dashboard-title mt-2">Plans</h1>
                            <p class="dashboard-body mt-2">
                                Packages offered at checkout. The default plan
                                is preselected for new events.
                            </p>
                        </div>

                        <div
                            v-if="plans.length === 0"
                            class="dashboard-body py-10"
                        >
                            No plans yet. Create the first one.
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
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }}
                                            </span>
                                            <span
                                                v-if="plan.isDefault"
                                                class="dashboard-chip bg-sky-100 text-sky-800"
                                            >
                                                Default
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
                                            Edit
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="toggleActive(plan)"
                                        >
                                            {{
                                                plan.isActive
                                                    ? 'Deactivate'
                                                    : 'Activate'
                                            }}
                                        </Button>
                                        <Button
                                            v-if="!plan.isDefault"
                                            size="sm"
                                            variant="outline"
                                            @click="makeDefault(plan)"
                                        >
                                            Make default
                                        </Button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="dashboard-panel">
                        <div
                            class="dashboard-panel-divider flex items-center justify-between pb-4"
                        >
                            <h2 class="dashboard-section-title">
                                {{ formTitle }}
                            </h2>
                            <Button
                                v-if="isEditing"
                                size="sm"
                                variant="ghost"
                                @click="resetForm"
                            >
                                New plan instead
                            </Button>
                        </div>

                        <form class="space-y-4 pt-4" @submit.prevent="submit">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label for="plan-name">Name</Label>
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
                                    <Label for="plan-slug">Slug</Label>
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
                                <Label for="plan-description"
                                    >Description</Label
                                >
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
                                    <Label for="plan-currency">Currency</Label>
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
                                    <Label for="plan-price"
                                        >Price (cents)</Label
                                    >
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
                                    <Label for="plan-storage"
                                        >Storage (GB)</Label
                                    >
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
                                    <Label for="plan-uploads"
                                        >Upload limit</Label
                                    >
                                    <Input
                                        id="plan-uploads"
                                        v-model.number="form.upload_limit"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-window"
                                        >Upload window (days)</Label
                                    >
                                    <Input
                                        id="plan-window"
                                        v-model.number="form.upload_window_days"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-retention"
                                        >Retention (days)</Label
                                    >
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
                                    <Label for="plan-grace">Grace (days)</Label>
                                    <Input
                                        id="plan-grace"
                                        v-model.number="form.grace_days"
                                        type="number"
                                        min="0"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-video-duration"
                                        >Video max (sec)</Label
                                    >
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
                                    <Label for="plan-tier"
                                        >Customization tier</Label
                                    >
                                    <select
                                        id="plan-tier"
                                        v-model="form.customization_tier"
                                        class="h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                    >
                                        <option value="basic">Basic</option>
                                        <option value="better">Better</option>
                                        <option value="advanced">
                                            Advanced
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
                                    <Label for="plan-photo-size"
                                        >Photo max (MB)</Label
                                    >
                                    <Input
                                        id="plan-photo-size"
                                        v-model.number="form.photo_max_size_mb"
                                        type="number"
                                        min="1"
                                        required
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="plan-video-size"
                                        >Video max (MB)</Label
                                    >
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
                                    Full album downloads
                                    <Switch
                                        v-model="form.download_all_enabled"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    Moderation tools
                                    <Switch
                                        v-model="form.moderation_tools_enabled"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    Remove app branding
                                    <Switch
                                        v-model="form.remove_app_branding"
                                    />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    Active
                                    <Switch v-model="form.is_active" />
                                </label>
                                <label
                                    class="flex items-center justify-between gap-3 text-sm text-brand-ink"
                                >
                                    Default plan for its currency
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
                                {{ isEditing ? 'Save changes' : 'Create plan' }}
                            </Button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
