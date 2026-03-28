<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
</script>

<template>
    <Head title="Business onboarding" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl space-y-6 p-4 md:p-6">
            <section class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Business setup
                </p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#171411]">
                    Finish your business profile
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">
                    Add the company and brand details you want to reuse across multiple events. You only do this once.
                </p>
            </section>

            <form class="rounded-[1.75rem] border border-black/5 bg-white p-5 shadow-sm md:p-6" @submit.prevent="submit">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="company_name">Company name</Label>
                        <Input id="company_name" v-model="form.company_name" />
                        <InputError :message="form.errors.company_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="brand_name">Public brand name</Label>
                        <Input id="brand_name" v-model="form.brand_name" />
                        <InputError :message="form.errors.brand_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="billing_email">Billing email</Label>
                        <Input id="billing_email" v-model="form.billing_email" type="email" />
                        <InputError :message="form.errors.billing_email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" v-model="form.phone" />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <div class="space-y-2">
                        <Label for="website">Website</Label>
                        <Input id="website" v-model="form.website" />
                        <InputError :message="form.errors.website" />
                    </div>

                    <div class="space-y-2">
                        <Label for="logo_file">Logo</Label>
                        <Input id="logo_file" type="file" accept="image/*" @input="form.logo_file = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                        <InputError :message="form.errors.logo_file" />
                    </div>

                    <div class="space-y-2">
                        <Label for="primary_color">Primary color</Label>
                        <Input id="primary_color" v-model="form.primary_color" type="color" class="h-11" />
                        <InputError :message="form.errors.primary_color" />
                    </div>

                    <div class="space-y-2">
                        <Label for="accent_color">Accent color</Label>
                        <Input id="accent_color" v-model="form.accent_color" type="color" class="h-11" />
                        <InputError :message="form.errors.accent_color" />
                    </div>
                </div>

                <div v-if="profile.logoUrl" class="mt-5">
                    <img :src="profile.logoUrl" alt="Current logo" class="h-20 w-20 rounded-2xl object-cover" />
                </div>

                <div class="mt-6 flex justify-end">
                    <Button type="submit" class="bg-[#171411] text-white hover:bg-[#2b2621]" :disabled="form.processing">
                        Save business profile
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
