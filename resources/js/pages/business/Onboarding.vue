<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BadgeCheck, BriefcaseBusiness, Globe2, Palette, ReceiptText } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    {
        title: 'Business onboarding',
        href: '#',
    },
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

const submit = (): void => {
    form.post(props.submitUrl, {
        forceFormData: true,
    });
};

const fieldHintClass = 'text-xs leading-5 text-zinc-500';
const previewBrandName = computed(() =>
    form.brand_name.trim() || form.company_name.trim() || 'Your business brand',
);
const brandPreviewStyle = computed(() => ({
    backgroundColor: `${form.primary_color}14`,
    borderColor: `${form.primary_color}2b`,
    color: form.primary_color,
}));
</script>

<template>
    <Head title="Business onboarding" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto grid max-w-6xl gap-5 p-4 md:p-6 xl:grid-cols-[minmax(0,1fr)_320px] xl:items-start">
            <form class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6" @submit.prevent="submit">
                <div class="border-b border-black/5 pb-5">
                    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                        Business setup
                    </p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#171411]">
                        Finish your business profile
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">
                        Add the company and brand details you want to reuse across future events, billing, and business pages.
                    </p>
                </div>

                <div class="grid gap-6 py-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="company_name">Company name</Label>
                        <Input id="company_name" v-model="form.company_name" placeholder="Studio Events SRL" />
                        <p :class="fieldHintClass">
                            Use the legal company name for contracts, invoices, and bookkeeping.
                        </p>
                        <InputError :message="form.errors.company_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="brand_name">Public brand name</Label>
                        <Input id="brand_name" v-model="form.brand_name" placeholder="Studio Events" />
                        <p :class="fieldHintClass">
                            This is the name clients will see across your business dashboard and event spaces.
                        </p>
                        <InputError :message="form.errors.brand_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="billing_email">Billing email</Label>
                        <Input id="billing_email" v-model="form.billing_email" type="email" placeholder="billing@studio-events.com" />
                        <p :class="fieldHintClass">
                            We will use this email for wallet receipts and billing updates.
                        </p>
                        <InputError :message="form.errors.billing_email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" v-model="form.phone" placeholder="+40 721 000 111" />
                        <p :class="fieldHintClass">
                            Optional, but helpful when clients or collaborators need a quick contact point.
                        </p>
                        <InputError :message="form.errors.phone" />
                    </div>

                    <div class="space-y-2">
                        <Label for="website">Website</Label>
                        <Input id="website" v-model="form.website" placeholder="studio-events.com" />
                        <p :class="fieldHintClass">
                            Example: <span class="font-medium text-zinc-700">studio-events.com</span> or <span class="font-medium text-zinc-700">https://studio-events.com</span>.
                            We will add <span class="font-medium text-zinc-700">https://</span> for you if you skip it.
                        </p>
                        <InputError :message="form.errors.website" />
                    </div>

                    <div class="space-y-2">
                        <Label for="logo_file">Logo</Label>
                        <Input id="logo_file" type="file" accept="image/*" @input="form.logo_file = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                        <p :class="fieldHintClass">
                            Use a square or transparent logo if you have one. You can always change it later.
                        </p>
                        <InputError :message="form.errors.logo_file" />
                    </div>

                    <div class="space-y-2">
                        <Label for="primary_color">Primary color</Label>
                        <Input id="primary_color" v-model="form.primary_color" type="color" class="h-11" />
                        <p :class="fieldHintClass">
                            Main brand tone for business pages and reusable accents.
                        </p>
                        <InputError :message="form.errors.primary_color" />
                    </div>

                    <div class="space-y-2">
                        <Label for="accent_color">Accent color</Label>
                        <Input id="accent_color" v-model="form.accent_color" type="color" class="h-11" />
                        <p :class="fieldHintClass">
                            Secondary highlight color for buttons, details, and support accents.
                        </p>
                        <InputError :message="form.errors.accent_color" />
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-black/5 pt-5">
                    <Link
                        as="button"
                        :href="props.cancelUrl"
                        method="post"
                        class="inline-flex items-center justify-center rounded-full border border-black/10 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-black/20 hover:bg-zinc-50"
                    >
                        Cancel
                    </Link>
                    <Button type="submit" class="bg-[#171411] text-white hover:bg-[#2b2621]" :disabled="form.processing">
                        Save business profile
                    </Button>
                </div>
            </form>

            <aside class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6 xl:sticky xl:top-6">
                <div class="space-y-5">
                    <section class="border-b border-black/5 pb-5">
                        <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                            Preview
                        </p>
                        <div class="mt-4 rounded-[24px] border px-4 py-4" :style="brandPreviewStyle">
                            <div class="flex items-start gap-3">
                                <img
                                    v-if="profile.logoUrl"
                                    :src="profile.logoUrl"
                                    alt="Current logo"
                                    class="size-12 rounded-2xl object-cover ring-1 ring-black/5"
                                />
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold" :style="{ color: form.primary_color }">
                                        {{ previewBrandName }}
                                    </p>
                                    <p class="mt-1 text-sm leading-6 text-zinc-600">
                                        This brand will follow your business dashboard and future event workspaces.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-2">
                                <span class="inline-flex h-4 w-4 rounded-full ring-1 ring-black/5" :style="{ backgroundColor: form.primary_color }" />
                                <span class="inline-flex h-4 w-4 rounded-full ring-1 ring-black/5" :style="{ backgroundColor: form.accent_color }" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <div>
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                                Reused across
                            </p>
                        </div>

                        <div class="space-y-3 text-sm text-zinc-600">
                            <p class="flex items-start gap-3">
                                <BriefcaseBusiness class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Business dashboard and multi-event workspace branding.
                            </p>
                            <p class="flex items-start gap-3">
                                <ReceiptText class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Billing email and company details for wallet receipts.
                            </p>
                            <p class="flex items-start gap-3">
                                <Globe2 class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Public-facing brand details you may reuse across events.
                            </p>
                            <p class="flex items-start gap-3">
                                <Palette class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                Primary and accent colors for your business surfaces.
                            </p>
                        </div>

                        <div class="border-t border-black/5 pt-4 text-sm leading-6 text-zinc-600">
                            <p class="flex items-start gap-3">
                                <BadgeCheck class="mt-0.5 size-4 shrink-0 text-zinc-400" />
                                You only do this once. You can still update it later from your business settings.
                            </p>
                        </div>
                    </section>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
