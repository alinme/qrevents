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
import { Label } from '@/components/ui/label';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import SettingsFileUpload from '@/components/settings/SettingsFileUpload.vue';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };
type SeoValue = { title: string; description: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    values: Record<string, SeoValue>;
    locales: string[];
    social: Record<string, string>;
    shareImageUrl: string | null;
}>();

const localeLabels: Record<string, string> = {
    en: 'English',
    ro: 'Română',
    el: 'Ελληνικά',
};

const socialFields = [
    { key: 'facebook', label: 'Facebook', icon: Facebook },
    { key: 'instagram', label: 'Instagram', icon: Instagram },
    { key: 'linkedin', label: 'LinkedIn', icon: Linkedin },
    { key: 'twitter', label: 'X / Twitter', icon: Twitter },
    { key: 'youtube', label: 'YouTube', icon: Youtube },
    { key: 'tiktok', label: 'TikTok', icon: null },
];

const form = useForm({
    values: Object.fromEntries(
        props.locales.map((locale) => [
            locale,
            {
                title: props.values[locale]?.title ?? '',
                description: props.values[locale]?.description ?? '',
            },
        ]),
    ) as Record<string, SeoValue>,
    social: {
        facebook: props.social.facebook ?? '',
        instagram: props.social.instagram ?? '',
        linkedin: props.social.linkedin ?? '',
        twitter: props.social.twitter ?? '',
        youtube: props.social.youtube ?? '',
        tiktok: props.social.tiktok ?? '',
    } as Record<string, string>,
    share_image: null as File | null,
});

const submit = (): void => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post('/admin/settings/seo', { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <SettingsShell
        :tabs="settingsTabs"
        :active-tab="activeTab"
        title="Localization & SEO"
        description="Default search-engine metadata, translated per language."
    >
        <form @submit.prevent="submit">
            <SettingsSection
                title="Default SEO metadata"
                description="Used as the fallback title and description on public pages. Switch languages with the tabs."
            >
                <Tabs :default-value="locales[0]">
                    <TabsList>
                        <TabsTrigger
                            v-for="locale in locales"
                            :key="locale"
                            :value="locale"
                        >
                            {{ localeLabels[locale] ?? locale.toUpperCase() }}
                        </TabsTrigger>
                    </TabsList>
                    <TabsContent
                        v-for="locale in locales"
                        :key="locale"
                        :value="locale"
                        class="space-y-5 pt-5"
                    >
                        <div class="space-y-1.5">
                            <Label :for="`seo-title-${locale}`">
                                SEO title
                            </Label>
                            <Input
                                :id="`seo-title-${locale}`"
                                v-model="form.values[locale].title"
                                maxlength="180"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <Label :for="`seo-desc-${locale}`">
                                SEO description
                            </Label>
                            <Textarea
                                :id="`seo-desc-${locale}`"
                                v-model="form.values[locale].description"
                                rows="3"
                                maxlength="320"
                            />
                        </div>
                    </TabsContent>
                </Tabs>

                <div class="space-y-1.5 border-t border-brand-border/70 pt-5">
                    <Label>Share image (Open Graph)</Label>
                    <SettingsFileUpload
                        v-model="form.share_image"
                        :preview-url="shareImageUrl"
                        hint="Shown when a page is shared on social. 1200×630 recommended."
                    />
                </div>
            </SettingsSection>

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
                        <Label
                            :for="`social-${field.key}`"
                            class="flex items-center gap-1.5"
                        >
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
