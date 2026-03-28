<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Password',
        href: editPassword(),
    },
    {
        title: 'Two-factor auth',
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
                    Account
                </p>
                <Heading
                    title="Settings"
                    description="Manage your profile and account settings"
                />
            </div>

            <nav
                class="flex flex-wrap gap-2 border-b border-brand-border/70 pb-4"
                aria-label="Settings"
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
