<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import SettingsFileUpload from '@/components/settings/SettingsFileUpload.vue';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    values: {
        site_name: string;
        company_name: string;
        support_phone: string;
        public_email: string;
        business_address: string;
    };
    logoUrl: string | null;
    faviconUrl: string | null;
}>();

const form = useForm({
    site_name: props.values.site_name,
    company_name: props.values.company_name,
    support_phone: props.values.support_phone,
    public_email: props.values.public_email,
    business_address: props.values.business_address,
    logo: null as File | null,
    favicon: null as File | null,
});

const submit = (): void => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post('/admin/settings/general', { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="General"
        description="Your platform identity and public contact details."
    >
        <form @submit.prevent="submit">
            <SettingsSection
                title="Identity"
                description="How your platform is named and branded across the app."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="site_name">Website name</Label>
                        <Input id="site_name" v-model="form.site_name" />
                        <InputError :message="form.errors.site_name" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="company_name">Company name</Label>
                        <Input id="company_name" v-model="form.company_name" />
                        <InputError :message="form.errors.company_name" />
                    </div>
                </div>
                <div class="space-y-1.5">
                    <Label>Logo</Label>
                    <SettingsFileUpload
                        v-model="form.logo"
                        :preview-url="logoUrl"
                        hint="PNG or SVG, up to 4 MB"
                    />
                    <InputError :message="form.errors.logo" />
                </div>
                <div class="space-y-1.5">
                    <Label>Favicon</Label>
                    <SettingsFileUpload
                        v-model="form.favicon"
                        :preview-url="faviconUrl"
                        hint="Square PNG or ICO, up to 1 MB"
                    />
                    <InputError :message="form.errors.favicon" />
                </div>
            </SettingsSection>

            <SettingsSection
                title="Contact information"
                description="Shown to guests and hosts across the public site and emails."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="support_phone">Support phone</Label>
                        <Input id="support_phone" v-model="form.support_phone" />
                        <InputError :message="form.errors.support_phone" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="public_email">Public email</Label>
                        <Input
                            id="public_email"
                            v-model="form.public_email"
                            type="email"
                        />
                        <InputError :message="form.errors.public_email" />
                    </div>
                </div>
                <div class="space-y-1.5">
                    <Label for="business_address">Business address</Label>
                    <Textarea
                        id="business_address"
                        v-model="form.business_address"
                        rows="3"
                    />
                    <InputError :message="form.errors.business_address" />
                </div>
            </SettingsSection>

            <div
                class="flex justify-end border-t border-brand-border/70 pt-6"
            >
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-full bg-brand-ink text-brand-inverse hover:bg-brand-accent"
                >
                    Save changes
                </Button>
            </div>
        </form>
    </SettingsShell>
</template>
