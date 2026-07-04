<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
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
import SettingsSection from '@/components/settings/SettingsSection.vue';
import SettingsShell from '@/components/settings/SettingsShell.vue';

type SettingsTab = { key: string; title: string; href: string };
type SeoValue = { title: string; description: string };

const props = defineProps<{
    settingsTabs: SettingsTab[];
    activeTab: string;
    values: Record<string, SeoValue>;
    locales: string[];
}>();

const localeLabels: Record<string, string> = {
    en: 'English',
    ro: 'Română',
    el: 'Ελληνικά',
};

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
});

const submit = (): void => {
    form.put('/admin/settings/seo', { preserveScroll: true });
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
