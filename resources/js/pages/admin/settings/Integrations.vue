<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    Facebook,
    Instagram,
    Linkedin,
    Twitter,
    Youtube,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    social: Record<string, string>;
    storage: {
        access_key: string;
        region: string;
        bucket: string;
        endpoint: string;
    };
    storageSecretSet: boolean;
}>();

const socialFields = [
    { key: 'facebook', label: 'Facebook', icon: Facebook },
    { key: 'instagram', label: 'Instagram', icon: Instagram },
    { key: 'linkedin', label: 'LinkedIn', icon: Linkedin },
    { key: 'twitter', label: 'X / Twitter', icon: Twitter },
    { key: 'youtube', label: 'YouTube', icon: Youtube },
    { key: 'tiktok', label: 'TikTok', icon: null },
];

const form = useForm({
    social: {
        facebook: props.social.facebook ?? '',
        instagram: props.social.instagram ?? '',
        linkedin: props.social.linkedin ?? '',
        twitter: props.social.twitter ?? '',
        youtube: props.social.youtube ?? '',
        tiktok: props.social.tiktok ?? '',
    } as Record<string, string>,
    storage: {
        access_key: props.storage.access_key,
        secret: '',
        region: props.storage.region,
        bucket: props.storage.bucket,
        endpoint: props.storage.endpoint,
    },
});

const submit = (): void => {
    form.put('/admin/settings/integrations', { preserveScroll: true });
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="Integrations & Storage"
        description="Social profiles and third-party cloud storage."
    >
        <form @submit.prevent="submit">
            <SettingsSection
                title="Social links"
                description="Public profile URLs shown in the footer and share cards."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div
                        v-for="field in socialFields"
                        :key="field.key"
                        class="space-y-1.5"
                    >
                        <Label :for="`social-${field.key}`" class="flex items-center gap-1.5">
                            <component
                                :is="field.icon"
                                v-if="field.icon"
                                class="size-3.5 text-brand-muted/70"
                            />
                            {{ field.label }}
                        </Label>
                        <Input
                            :id="`social-${field.key}`"
                            v-model="form.social[field.key]"
                            type="url"
                            placeholder="https://"
                        />
                        <InputError :message="form.errors[`social.${field.key}` as never]" />
                    </div>
                </div>
            </SettingsSection>

            <SettingsSection
                title="Cloud storage — Scaleway"
                description="S3-compatible object storage for guest media. The secret key is stored encrypted."
            >
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="storage-access">Access key</Label>
                        <Input id="storage-access" v-model="form.storage.access_key" />
                        <InputError :message="form.errors['storage.access_key' as never]" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="storage-secret">Secret key</Label>
                        <Input
                            id="storage-secret"
                            v-model="form.storage.secret"
                            type="password"
                            :placeholder="storageSecretSet ? '•••••••••• saved — leave blank to keep' : 'Enter secret key'"
                        />
                        <InputError :message="form.errors['storage.secret' as never]" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="storage-region">Region</Label>
                        <Input id="storage-region" v-model="form.storage.region" placeholder="nl-ams" />
                        <InputError :message="form.errors['storage.region' as never]" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="storage-bucket">Bucket name</Label>
                        <Input id="storage-bucket" v-model="form.storage.bucket" />
                        <InputError :message="form.errors['storage.bucket' as never]" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <Label for="storage-endpoint">Endpoint</Label>
                        <Input
                            id="storage-endpoint"
                            v-model="form.storage.endpoint"
                            type="url"
                            placeholder="https://s3.nl-ams.scw.cloud"
                        />
                        <InputError :message="form.errors['storage.endpoint' as never]" />
                    </div>
                </div>
            </SettingsSection>

            <div class="flex justify-end border-t border-brand-border/70 pt-6">
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
