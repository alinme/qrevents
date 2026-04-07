<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeft,
    BriefcaseBusiness,
    Camera,
    CreditCard,
    FolderKanban,
    LayoutGrid,
    Package,
    QrCode,
    ScrollText,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import SidebarLocaleSwitcher from '@/components/SidebarLocaleSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type EventNavItem = {
    title: string;
    href: string;
};

type CurrentEvent = {
    name: string;
};

type PageProps = {
    currentEvent?: CurrentEvent;
    eventNavigation?: EventNavItem[];
    accountNavigation?: EventNavItem[];
    businessNavigation?: EventNavItem[];
    adminNavigation?: EventNavItem[];
    backNavigation?: EventNavItem;
    sidebarLabel?: string;
};

const page = usePage<PageProps>();
const { t } = useTranslations();

const eventNavigation = computed(() => page.props.eventNavigation ?? []);
const adminNavigation = computed(() => page.props.adminNavigation ?? []);
const businessNavigation = computed(() => page.props.businessNavigation ?? []);

const matchesTranslatedTitle = (title: string, english: string, translated: string): boolean => {
    return title === english || title === translated;
};

const translatedNavTitle = (title: string): string => {
    const knownLabels: Record<string, string> = {
        Dashboard: t('app.nav.dashboard'),
        Events: t('app.nav.events'),
        Workspace: t('app.nav.workspace'),
        'QR Studio': t('app.nav.print_pack'),
        'Invite Studio': t('app.nav.invite_studio'),
        Media: t('app.nav.media'),
        Guests: t('app.nav.guests'),
        Settings: t('app.nav.settings'),
        Overview: t('app.nav.overview'),
        Users: t('app.nav.users'),
        Plans: t('app.nav.plans'),
        Billing: t('app.nav.billing'),
        Cleanup: t('app.nav.cleanup'),
        Business: t('app.nav.business'),
        'Owned Events': t('app.nav.owned_events'),
        'Shared Events': t('app.nav.shared_events'),
        'Recent Activity': t('app.nav.recent_activity'),
        Admin: t('app.nav.admin'),
        Account: t('app.nav.account'),
    };

    return knownLabels[title] ?? title;
};

const footerBackItem = computed<NavItem | null>(() => {
    const eventBackItem = eventNavigation.value.find(
        (item) =>
            matchesTranslatedTitle(item.title, 'Dashboard', t('app.nav.dashboard'))
            || matchesTranslatedTitle(item.title, 'Events', t('app.nav.events')),
    );

    if (eventBackItem) {
        return {
            title: translatedNavTitle(eventBackItem.title),
            href: eventBackItem.href,
            icon: ArrowLeft,
        };
    }

    if (page.props.backNavigation) {
        return {
            title: translatedNavTitle(page.props.backNavigation.title),
            href: page.props.backNavigation.href,
            icon: ArrowLeft,
        };
    }

    return null;
});

const mainNavItems = computed<NavItem[]>(() => {
    if (eventNavigation.value.length > 0) {
        return eventNavigation.value
            .filter((item) => !matchesTranslatedTitle(item.title, 'Dashboard', t('app.nav.dashboard')))
            .map((item) => ({
                title: translatedNavTitle(item.title),
                href: item.href,
                icon:
                    matchesTranslatedTitle(item.title, 'Workspace', t('app.nav.workspace'))
                        ? LayoutGrid
                        : matchesTranslatedTitle(item.title, 'Guests', t('app.nav.guests'))
                          ? Users
                        : matchesTranslatedTitle(item.title, 'Media', t('app.nav.media'))
                          ? Camera
                          : matchesTranslatedTitle(item.title, 'Settings', t('app.nav.settings'))
                            ? Settings
                            : matchesTranslatedTitle(item.title, 'Invite Studio', t('app.nav.invite_studio'))
                              ? ScrollText
                            : matchesTranslatedTitle(item.title, 'QR Studio', t('app.nav.print_pack'))
                              ? QrCode
                            : LayoutGrid,
            }));
    }

    if (adminNavigation.value.length > 0) {
        return adminNavigation.value.map((item) => ({
            title: translatedNavTitle(item.title),
            href: item.href,
            icon:
                item.title === 'Overview'
                    ? LayoutGrid
                    : item.title === 'Users'
                      ? Users
                      : item.title === 'Events'
                        ? Camera
                        : item.title === 'Plans'
                          ? Package
                          : item.title === 'Billing'
                            ? CreditCard
                            : item.title === 'Cleanup'
                              ? Activity
                              : Shield,
        }));
    }

    if (businessNavigation.value.length > 0) {
        return businessNavigation.value.map((item) => ({
            title: translatedNavTitle(item.title),
            href: item.href,
            icon:
                item.title === 'Business'
                    ? BriefcaseBusiness
                    : item.title === 'Billing'
                        ? CreditCard
                        : item.title === 'Events'
                            ? FolderKanban
                            : LayoutGrid,
        }));
    }

    const accountNavigation = page.props.accountNavigation ?? [];
    if (accountNavigation.length > 0) {
        return accountNavigation.map((item) => ({
            title: translatedNavTitle(item.title),
            href: item.href,
            icon:
                item.title === 'Overview'
                    ? LayoutGrid
                    : item.title === 'Business'
                      ? BriefcaseBusiness
                      : item.title === 'Billing'
                        ? CreditCard
                          : item.title === 'Events'
                            ? FolderKanban
                      : item.title === 'Owned Events'
                        ? Camera
                        : item.title === 'Admin'
                          ? Shield
                          : item.title === 'Shared Events'
                            ? Users
                            : item.title === 'Recent Activity'
                              ? Activity
                              : LayoutGrid,
        }));
    }

    return [
        {
            title: t('app.nav.dashboard'),
            href: dashboard().url,
            icon: LayoutGrid,
        },
    ];
});

const eventTitle = computed(
    () => translatedNavTitle(page.props.sidebarLabel ?? page.props.currentEvent?.name ?? 'Account'),
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="!overflow-visible">
            <NavMain :items="mainNavItems" :label="eventTitle" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu v-if="footerBackItem">
                <SidebarMenuItem>
                    <SidebarMenuButton
                        as-child
                        :is-active="false"
                        :tooltip="footerBackItem.title"
                    >
                        <Link :href="footerBackItem.href">
                            <component :is="footerBackItem.icon" />
                            <span>{{ footerBackItem.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <SidebarLocaleSwitcher />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
