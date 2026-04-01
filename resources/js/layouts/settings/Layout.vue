<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useTranslations } from '@/composables/useTranslations';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const { t } = useTranslations();

const sidebarNavItems: NavItem[] = [
    {
        title: t('account_settings.layout.profile'),
        href: editProfile(),
    },
    {
        title: t('account_settings.layout.password'),
        href: editPassword(),
    },
    {
        title: t('account_settings.layout.two_factor'),
        href: show(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="dashboard-page">
        <div class="dashboard-shell max-w-5xl">
            <div class="space-y-2">
                <p class="dashboard-eyebrow">
                    {{ t('account_settings.layout.eyebrow') }}
                </p>
                <Heading
                    :title="t('account_settings.layout.title')"
                    :description="t('account_settings.layout.description')"
                />
            </div>

            <nav
                class="flex flex-wrap gap-2 border-b border-brand-border/70 pb-4"
                :aria-label="t('account_settings.layout.title')"
            >
                <Button
                    v-for="item in sidebarNavItems"
                    :key="toUrl(item.href)"
                    variant="outline"
                    :class="[
                        'rounded-full border-brand-border bg-brand-inverse text-brand-ink hover:bg-brand-highlight/20',
                        {
                            'border-brand-ink bg-brand-highlight/20': isCurrentOrParentUrl(item.href),
                        },
                    ]"
                    as-child
                >
                    <Link :href="item.href">
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.title }}
                    </Link>
                </Button>
            </nav>

            <Separator class="hidden" />

            <section class="max-w-3xl space-y-8 pt-2">
                <slot />
            </section>
        </div>
    </div>
</template>
