<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { PencilLine, Plus, ShieldCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Summary = {
    totalPlans: number;
    activePlanCount: number;
    defaultPlanCount: number;
    totalEvents: number;
    totalAllocatedStorageBytes: number;
};

type AdminLinks = {
    overview: string;
    users: string;
    events: string;
    plans: string;
    plansStore: string;
    billing: string;
    cleanup: string;
    dashboard: string;
};

type PlanRow = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    currency: string;
    priceCents: number;
    priceLabel: string;
    storageLimitBytes: number;
    storageLimitLabel: string;
    uploadLimit: number;
    retentionDays: number;
    graceDays: number;
    uploadWindowDays: number;
    customizationTier: 'basic' | 'better' | 'advanced';
    downloadAllEnabled: boolean;
    moderationToolsEnabled: boolean;
    removeAppBranding: boolean;
    videoMaxDurationSeconds: number;
    photoMaxSizeBytes: number;
    videoMaxSizeBytes: number;
    isActive: boolean;
    isDefault: boolean;
    eventCount: number;
    createdAt: string | null;
    updatedAt: string | null;
    links: {
        update: string;
    };
};

const props = defineProps<{
    summary: Summary;
    adminLinks: AdminLinks;
    plans: PlanRow[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: props.adminLinks.overview,
    },
    {
        title: 'Plans',
        href: props.adminLinks.plans,
    },
];

const formatBytes = (bytes: number): string => {
    if (bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / 1024 ** exponent;

    return `${value.toFixed(value >= 10 || exponent === 0 ? 0 : 1)} ${units[exponent]}`;
};

const formatDateTime = (value: string | null): string => {
    if (!value) {
        return 'No timestamp';
    }

    return new Intl.DateTimeFormat('en-GB', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const toMegabytes = (bytes: number): number => Math.max(1, Math.round(bytes / 1048576));
const toGigabytes = (bytes: number): number => Math.max(1, Math.round(bytes / 1073741824));

const search = ref('');
const editDialogOpen = ref(false);
const selectedPlan = ref<PlanRow | null>(null);

const createForm = useForm({
    name: '',
    slug: '',
    description: '',
    currency: 'EUR',
    price_cents: 2000,
    storage_limit_gb: 10,
    upload_limit: 300,
    retention_days: 30,
    grace_days: 7,
    upload_window_days: 30,
    customization_tier: 'basic' as PlanRow['customizationTier'],
    video_max_duration_seconds: 30,
    photo_max_size_mb: 25,
    video_max_size_mb: 500,
    download_all_enabled: false,
    moderation_tools_enabled: false,
    remove_app_branding: false,
    is_active: true,
    is_default: false,
});

const editForm = useForm({
    name: '',
    slug: '',
    description: '',
    currency: 'EUR',
    price_cents: 2000,
    storage_limit_gb: 10,
    upload_limit: 300,
    retention_days: 30,
    grace_days: 7,
    upload_window_days: 30,
    customization_tier: 'basic' as PlanRow['customizationTier'],
    video_max_duration_seconds: 30,
    photo_max_size_mb: 25,
    video_max_size_mb: 500,
    download_all_enabled: false,
    moderation_tools_enabled: false,
    remove_app_branding: false,
    is_active: true,
    is_default: false,
});

const filteredPlans = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (term === '') {
        return props.plans;
    }

    return props.plans.filter((plan) =>
        [plan.name, plan.slug, plan.currency, plan.description ?? ''].some((value) =>
            value.toLowerCase().includes(term),
        ),
    );
});

const resetCreateForm = (): void => {
    createForm.reset();
    createForm.currency = 'EUR';
    createForm.price_cents = 2000;
    createForm.storage_limit_gb = 10;
    createForm.upload_limit = 300;
    createForm.retention_days = 30;
    createForm.grace_days = 7;
    createForm.upload_window_days = 30;
    createForm.customization_tier = 'basic';
    createForm.video_max_duration_seconds = 30;
    createForm.photo_max_size_mb = 25;
    createForm.video_max_size_mb = 500;
    createForm.download_all_enabled = false;
    createForm.moderation_tools_enabled = false;
    createForm.remove_app_branding = false;
    createForm.is_active = true;
    createForm.is_default = false;
    createForm.clearErrors();
};

const openEditDialog = (plan: PlanRow): void => {
    selectedPlan.value = plan;
    editForm.name = plan.name;
    editForm.slug = plan.slug;
    editForm.description = plan.description ?? '';
    editForm.currency = plan.currency;
    editForm.price_cents = plan.priceCents;
    editForm.storage_limit_gb = toGigabytes(plan.storageLimitBytes);
    editForm.upload_limit = plan.uploadLimit;
    editForm.retention_days = plan.retentionDays;
    editForm.grace_days = plan.graceDays;
    editForm.upload_window_days = plan.uploadWindowDays;
    editForm.customization_tier = plan.customizationTier;
    editForm.video_max_duration_seconds = plan.videoMaxDurationSeconds;
    editForm.photo_max_size_mb = toMegabytes(plan.photoMaxSizeBytes);
    editForm.video_max_size_mb = toMegabytes(plan.videoMaxSizeBytes);
    editForm.download_all_enabled = plan.downloadAllEnabled;
    editForm.moderation_tools_enabled = plan.moderationToolsEnabled;
    editForm.remove_app_branding = plan.removeAppBranding;
    editForm.is_active = plan.isActive;
    editForm.is_default = plan.isDefault;
    editForm.clearErrors();
    editDialogOpen.value = true;
};

const closeEditDialog = (): void => {
    editDialogOpen.value = false;
    selectedPlan.value = null;
    editForm.reset();
    editForm.clearErrors();
};

const submitCreateForm = (): void => {
    createForm.post(props.adminLinks.plansStore, {
        preserveScroll: true,
        onSuccess: () => {
            resetCreateForm();
        },
    });
};

const submitEditForm = (): void => {
    if (!selectedPlan.value) {
        return;
    }

    editForm.patch(selectedPlan.value.links.update, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditDialog();
        },
    });
};
</script>

<template>
    <Head title="Admin Plans" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_32%),radial-gradient(circle_at_85%_10%,_rgba(251,191,36,0.14),_transparent_22%)]">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 p-4 md:p-6">
                <section class="overflow-hidden rounded-[2rem] border border-black/5 bg-white shadow-sm">
                    <div class="border-b border-black/5 bg-[linear-gradient(135deg,#171411_0%,#2d251f_46%,#5f533f_100%)] px-6 py-8 text-white md:px-8">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div class="space-y-2">
                                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-white/12 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-white/80">
                                    Super admin
                                </div>
                                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Packages and pricing</h1>
                                <p class="max-w-2xl text-sm leading-6 text-white/72 md:text-base">
                                    Manage the live packages your customers can land on, including storage, upload limits, retention, and default plan behavior.
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link :href="adminLinks.overview" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Overview
                                </Link>
                                <Link :href="adminLinks.billing" class="rounded-full border border-white/14 bg-white/8 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/14">
                                    Billing
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-5 xl:p-6">
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total packages</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalPlans }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Active packages</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.activePlanCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Default packages</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.defaultPlanCount }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Total events</p>
                            <p class="mt-2 text-3xl font-semibold text-[#171411]">{{ summary.totalEvents }}</p>
                        </div>
                        <div class="rounded-2xl border border-black/6 bg-[#fbfaf7] p-4">
                            <p class="text-sm font-medium text-zinc-500">Provisioned storage</p>
                            <p class="mt-2 text-2xl font-semibold text-[#171411]">{{ formatBytes(summary.totalAllocatedStorageBytes) }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="rounded-full bg-[#f4e6d5] p-3 text-[#8b5f3d]">
                            <Plus class="size-5" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Create package</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Add a new live plan</h2>
                        </div>
                    </div>

                    <form class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" @submit.prevent="submitCreateForm">
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Name</span>
                            <Input v-model="createForm.name" placeholder="Business 49 EUR" />
                            <p v-if="createForm.errors.name" class="text-sm text-rose-600">{{ createForm.errors.name }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Slug</span>
                            <Input v-model="createForm.slug" placeholder="business-49-eur" />
                            <p v-if="createForm.errors.slug" class="text-sm text-rose-600">{{ createForm.errors.slug }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Currency</span>
                            <Input v-model="createForm.currency" maxlength="3" placeholder="EUR" />
                            <p v-if="createForm.errors.currency" class="text-sm text-rose-600">{{ createForm.errors.currency }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Price cents</span>
                            <Input v-model="createForm.price_cents" type="number" min="0" />
                            <p v-if="createForm.errors.price_cents" class="text-sm text-rose-600">{{ createForm.errors.price_cents }}</p>
                        </label>
                        <label class="space-y-2 xl:col-span-2">
                            <span class="text-sm font-medium text-[#171411]">Description</span>
                            <textarea
                                v-model="createForm.description"
                                rows="3"
                                class="min-h-[110px] w-full rounded-2xl border border-black/10 bg-[#fbfaf7] px-4 py-3 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                                placeholder="Ideal for planners with higher guest upload volume."
                            />
                            <p v-if="createForm.errors.description" class="text-sm text-rose-600">{{ createForm.errors.description }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Storage GB</span>
                            <Input v-model="createForm.storage_limit_gb" type="number" min="1" />
                            <p v-if="createForm.errors.storage_limit_gb" class="text-sm text-rose-600">{{ createForm.errors.storage_limit_gb }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Upload limit</span>
                            <Input v-model="createForm.upload_limit" type="number" min="1" />
                            <p v-if="createForm.errors.upload_limit" class="text-sm text-rose-600">{{ createForm.errors.upload_limit }}</p>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Retention days</span>
                            <Input v-model="createForm.retention_days" type="number" min="1" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Grace days</span>
                            <Input v-model="createForm.grace_days" type="number" min="0" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Active window days</span>
                            <Input v-model="createForm.upload_window_days" type="number" min="1" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Customization tier</span>
                            <select
                                v-model="createForm.customization_tier"
                                class="h-10 rounded-2xl border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                            >
                                <option value="basic">Basic</option>
                                <option value="better">Better</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Video seconds</span>
                            <Input v-model="createForm.video_max_duration_seconds" type="number" min="1" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Photo max MB</span>
                            <Input v-model="createForm.photo_max_size_mb" type="number" min="1" />
                        </label>
                        <label class="space-y-2">
                            <span class="text-sm font-medium text-[#171411]">Video max MB</span>
                            <Input v-model="createForm.video_max_size_mb" type="number" min="1" />
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3">
                            <input v-model="createForm.is_active" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Active package</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3">
                            <input v-model="createForm.download_all_enabled" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Download-all enabled</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3">
                            <input v-model="createForm.moderation_tools_enabled" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Moderation tools enabled</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3">
                            <input v-model="createForm.remove_app_branding" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Hide EventSmart branding</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3">
                            <input v-model="createForm.is_default" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Default for this currency</span>
                        </label>

                        <div class="xl:col-span-4 flex justify-end">
                            <Button type="submit" class="rounded-full bg-[#171411] px-6 text-white hover:bg-black" :disabled="createForm.processing">
                                Create package
                            </Button>
                        </div>
                    </form>
                </section>

                <section class="rounded-[2rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-zinc-500">Live catalog</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-[#171411]">Edit existing packages</h2>
                        </div>
                        <Input v-model="search" type="search" placeholder="Search by name, slug, currency, or description" class="md:max-w-sm" />
                    </div>

                    <div v-if="filteredPlans.length === 0" class="py-12 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div class="rounded-full bg-[#fbfaf7] p-4 text-[#171411]">
                                <ShieldCheck class="size-7" />
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-xl font-semibold text-[#171411]">No matching packages</h2>
                                <p class="text-sm leading-6 text-zinc-600">Try a different search term.</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid gap-4">
                        <article v-for="plan in filteredPlans" :key="plan.id" class="rounded-[1.5rem] border border-black/6 bg-[#fcfbf8] p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-2xl font-semibold tracking-tight text-[#171411]">{{ plan.name }}</h3>
                                        <span v-if="plan.isDefault" class="inline-flex rounded-full bg-[#171411] px-2.5 py-1 text-xs font-semibold text-white">Default</span>
                                        <span v-if="plan.isActive" class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Active</span>
                                        <span v-else class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">Inactive</span>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-lg font-semibold text-[#171411]">{{ plan.priceLabel }}</p>
                                        <p class="text-sm text-zinc-600">{{ plan.slug }} · {{ plan.currency }}</p>
                                        <p class="text-sm leading-6 text-zinc-600">{{ plan.description || 'No description set yet.' }}</p>
                                    </div>
                                </div>

                                <Button type="button" variant="outline" class="rounded-full border-black/10 bg-white text-[#171411] hover:bg-[#f4ede5]" @click="openEditDialog(plan)">
                                    <PencilLine class="mr-2 size-4" />
                                    Edit package
                                </Button>
                            </div>

                            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Storage</p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411]">{{ plan.storageLimitLabel }}</p>
                                </div>
                                <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Uploads</p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411]">{{ plan.uploadLimit }}</p>
                                </div>
                                <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Retention</p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411]">{{ plan.retentionDays }} days</p>
                                </div>
                                <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Active window</p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411]">{{ plan.uploadWindowDays }} days</p>
                                </div>
                                <div class="rounded-2xl border border-black/6 bg-white px-4 py-3">
                                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-400">Events using it</p>
                                    <p class="mt-1 text-sm font-semibold text-[#171411]">{{ plan.eventCount }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-4 text-xs uppercase tracking-[0.18em] text-zinc-400">
                                <span>{{ plan.customizationTier }} customization</span>
                                <span>{{ plan.downloadAllEnabled ? 'download-all on' : 'download-all off' }}</span>
                                <span>{{ plan.moderationToolsEnabled ? 'moderation on' : 'moderation off' }}</span>
                                <span>{{ plan.removeAppBranding ? 'white-label' : 'with branding' }}</span>
                                <span>{{ plan.videoMaxDurationSeconds }} sec video</span>
                                <span>{{ toMegabytes(plan.photoMaxSizeBytes) }} MB photo max</span>
                                <span>{{ toMegabytes(plan.videoMaxSizeBytes) }} MB video max</span>
                                <span>Updated {{ formatDateTime(plan.updatedAt) }}</span>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
        </div>

        <Dialog v-model:open="editDialogOpen">
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Edit package</DialogTitle>
                    <DialogDescription>
                        Adjust pricing and limits without touching existing event media directly.
                    </DialogDescription>
                </DialogHeader>

                <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submitEditForm">
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Name</span>
                        <Input v-model="editForm.name" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Slug</span>
                        <Input v-model="editForm.slug" />
                    </label>
                    <label class="space-y-2 md:col-span-2">
                        <span class="text-sm font-medium text-[#171411]">Description</span>
                        <textarea
                            v-model="editForm.description"
                            rows="3"
                            class="min-h-[110px] w-full rounded-2xl border border-black/10 bg-[#fbfaf7] px-4 py-3 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                        />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Currency</span>
                        <Input v-model="editForm.currency" maxlength="3" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Price cents</span>
                        <Input v-model="editForm.price_cents" type="number" min="0" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Storage GB</span>
                        <Input v-model="editForm.storage_limit_gb" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Upload limit</span>
                        <Input v-model="editForm.upload_limit" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Retention days</span>
                        <Input v-model="editForm.retention_days" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Grace days</span>
                        <Input v-model="editForm.grace_days" type="number" min="0" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Active window days</span>
                        <Input v-model="editForm.upload_window_days" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Customization tier</span>
                        <select
                            v-model="editForm.customization_tier"
                            class="h-10 rounded-2xl border border-black/10 bg-[#fbfaf7] px-4 text-sm text-[#171411] outline-none transition focus:border-[#c79a5b] focus:ring-2 focus:ring-[#e8c892]"
                        >
                            <option value="basic">Basic</option>
                            <option value="better">Better</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Video seconds</span>
                        <Input v-model="editForm.video_max_duration_seconds" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Photo max MB</span>
                        <Input v-model="editForm.photo_max_size_mb" type="number" min="1" />
                    </label>
                    <label class="space-y-2">
                        <span class="text-sm font-medium text-[#171411]">Video max MB</span>
                        <Input v-model="editForm.video_max_size_mb" type="number" min="1" />
                    </label>
                    <div class="space-y-3 rounded-2xl border border-black/8 bg-[#fbfaf7] px-4 py-3 md:col-span-2">
                        <label class="flex items-center gap-3">
                            <input v-model="editForm.is_active" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Active package</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="editForm.download_all_enabled" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Download-all enabled</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="editForm.moderation_tools_enabled" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Moderation tools enabled</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="editForm.remove_app_branding" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Hide EventSmart branding</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input v-model="editForm.is_default" type="checkbox" class="size-4 rounded border-black/20 text-[#8b5f3d] focus:ring-[#c79a5b]" >
                            <span class="text-sm font-medium text-[#171411]">Default for this currency</span>
                        </label>
                    </div>

                    <p
                        v-if="Object.keys(editForm.errors).length > 0"
                        class="text-sm text-rose-600 md:col-span-2"
                    >
                        {{ Object.values(editForm.errors)[0] }}
                    </p>

                    <DialogFooter class="gap-2 md:col-span-2 sm:justify-end">
                        <Button type="button" variant="outline" class="rounded-full border-black/10 bg-white text-[#171411] hover:bg-[#f4ede5]" @click="closeEditDialog">
                            Cancel
                        </Button>
                        <Button type="submit" class="rounded-full bg-[#171411] px-6 text-white hover:bg-black" :disabled="editForm.processing">
                            Save package
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
