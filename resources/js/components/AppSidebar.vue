<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeft,
    BriefcaseBusiness,
    Camera,
    CreditCard,
    CircleDollarSign,
    FolderPlus,
    LayoutGrid,
    Package,
    Settings,
    Shield,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
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
    adminNavigation?: EventNavItem[];
    backNavigation?: EventNavItem;
    sidebarLabel?: string;
};

const page = usePage<PageProps>();

const eventNavigation = computed(() => page.props.eventNavigation ?? []);
const adminNavigation = computed(() => page.props.adminNavigation ?? []);

const footerBackItem = computed<NavItem | null>(() => {
    const eventBackItem = eventNavigation.value.find(
        (item) => item.title === 'Dashboard' || item.title === 'Events',
    );

    if (eventBackItem) {
        return {
            title: eventBackItem.title,
            href: eventBackItem.href,
            icon: ArrowLeft,
        };
    }

    if (page.props.backNavigation) {
        return {
            title: page.props.backNavigation.title,
            href: page.props.backNavigation.href,
            icon: ArrowLeft,
        };
    }

    return null;
});

const mainNavItems = computed<NavItem[]>(() => {
    if (eventNavigation.value.length > 0) {
        return eventNavigation.value
            .filter((item) => item.title !== 'Dashboard')
            .map((item) => ({
                title: item.title,
                href: item.href,
                icon:
                    item.title === 'Workspace'
                        ? LayoutGrid
                        : item.title === 'Guests'
                          ? Users
                        : item.title === 'Media'
                          ? Camera
                          : item.title === 'Settings'
                            ? Settings
                            : LayoutGrid,
            }));
    }

    if (adminNavigation.value.length > 0) {
        return adminNavigation.value.map((item) => ({
            title: item.title,
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

    const accountNavigation = page.props.accountNavigation ?? [];
    if (accountNavigation.length > 0) {
        return accountNavigation.map((item) => ({
            title: item.title,
            href: item.href,
            icon:
                item.title === 'Overview'
                    ? LayoutGrid
                    : item.title === 'Business'
                      ? BriefcaseBusiness
                      : item.title === 'Create'
                        ? FolderPlus
                        : item.title === 'Wallet'
                          ? CircleDollarSign
                          : item.title === 'Portfolio'
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
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },
    ];
});

const eventTitle = computed(
    () => page.props.sidebarLabel ?? page.props.currentEvent?.name ?? 'Account',
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
