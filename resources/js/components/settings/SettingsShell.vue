<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type SettingsTab = {
    key: string;
    title: string;
    href: string;
};

const props = defineProps<{
    tabs: SettingsTab[];
    activeTab: string;
    title: string;
    description?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Settings', href: '/admin/settings' },
    { title: props.title, href: '#' },
];
</script>

<template>
    <Head :title="`Settings · ${title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dashboard-page">
            <div class="dashboard-shell max-w-4xl">
                <div>
                    <p class="dashboard-eyebrow">Site settings</p>
                    <h1 class="dashboard-title mt-2">{{ title }}</h1>
                    <p v-if="description" class="dashboard-body mt-2">
                        {{ description }}
                    </p>
                </div>

                <nav
                    class="scrollbar-none -mx-1 mt-6 flex gap-1 overflow-x-auto border-b border-brand-border/70 pb-px"
                    aria-label="Settings sections"
                >
                    <Link
                        v-for="tab in tabs"
                        :key="tab.key"
                        :href="tab.href"
                        class="relative shrink-0 rounded-t-lg px-4 py-2.5 text-sm font-medium whitespace-nowrap transition"
                        :class="
                            tab.key === activeTab
                                ? 'text-brand-ink'
                                : 'text-brand-muted hover:text-brand-ink'
                        "
                    >
                        {{ tab.title }}
                        <span
                            v-if="tab.key === activeTab"
                            class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-brand-ink"
                        />
                    </Link>
                </nav>

                <div class="mt-8">
                    <slot />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
