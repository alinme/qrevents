<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    profile: {
        companyName: string;
        brandName: string;
        billingEmail: string;
        phone: string;
        website: string;
        primaryColor: string;
        accentColor: string;
        logoUrl: string | null;
    };
    submitUrl: string;
    cancelUrl: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Business', href: '/dashboard/business' },
    { title: 'Set up profile', href: '#' },
];

const form = useForm({
    company_name: props.profile.companyName,
    brand_name: props.profile.brandName,
    billing_email: props.profile.billingEmail,
    phone: props.profile.phone,
    website: props.profile.website,
    primary_color: props.profile.primaryColor,
    accent_color: props.profile.accentColor,
    logo_file: null as File | null,
});

const onLogoChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    form.logo_file = target.files?.[0] ?? null;
};

const submit = (): void => {
    form.post(props.submitUrl, { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Business · Set up profile" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-3xl">
                <section class="dashboard-panel">
                    <p class="dashboard-eyebrow">Business</p>
                    <h1 class="dashboard-title mt-2">Set up your business profile</h1>
                    <p class="dashboard-body mt-2">
                        Tell us about your company. This branding is used across
                        your events and on invoices.
                    </p>

                    <form class="mt-6 flex flex-col gap-5" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <label class="flex flex-col gap-1">
                                <span class="dashboard-eyebrow">Company name</span>
                                <Input v-model="form.company_name" type="text" />
                                <InputError :message="form.errors.company_name" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="dashboard-eyebrow">Brand name</span>
                                <Input v-model="form.brand_name" type="text" />
                                <InputError :message="form.errors.brand_name" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="dashboard-eyebrow">Billing email</span>
                                <Input v-model="form.billing_email" type="email" />
                                <InputError :message="form.errors.billing_email" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="dashboard-eyebrow">Phone</span>
                                <Input v-model="form.phone" type="text" />
                                <InputError :message="form.errors.phone" />
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="dashboard-eyebrow">Website</span>
                                <Input v-model="form.website" type="text" placeholder="https://" />
                                <InputError :message="form.errors.website" />
                            </label>
                            <div class="flex gap-4">
                                <label class="flex flex-col gap-1">
                                    <span class="dashboard-eyebrow">Primary color</span>
                                    <input
                                        v-model="form.primary_color"
                                        type="color"
                                        class="h-9 w-16 rounded-md border border-brand-border"
                                    />
                                </label>
                                <label class="flex flex-col gap-1">
                                    <span class="dashboard-eyebrow">Accent color</span>
                                    <input
                                        v-model="form.accent_color"
                                        type="color"
                                        class="h-9 w-16 rounded-md border border-brand-border"
                                    />
                                </label>
                            </div>
                        </div>

                        <label class="flex flex-col gap-1">
                            <span class="dashboard-eyebrow">Logo</span>
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="profile.logoUrl"
                                    :src="profile.logoUrl"
                                    alt="Current logo"
                                    class="h-12 w-12 rounded-md object-contain"
                                />
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="text-sm"
                                    @change="onLogoChange"
                                />
                            </div>
                            <InputError :message="form.errors.logo_file" />
                        </label>

                        <div class="flex items-center gap-3 border-t border-brand-border/70 pt-4">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                            >
                                Save profile
                            </Button>
                            <a
                                href="/dashboard"
                                class="text-sm font-semibold text-brand-muted hover:text-brand-ink"
                            >
                                Cancel
                            </a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
