<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem } from '@/types';

const { t } = useTranslations();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('account_settings.appearance.page_title'),
        href: '/settings/profile',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="t('account_settings.appearance.page_title')" />

        <h1 class="sr-only">{{ t('account_settings.appearance.page_title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="t('account_settings.appearance.title')"
                    :description="t('account_settings.appearance.description')"
                />
                <p class="text-sm text-muted-foreground">
                    {{ t('account_settings.appearance.body') }}
                </p>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
