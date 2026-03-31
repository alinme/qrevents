<script setup lang="ts">
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLocaleSwitcher from '@/components/AppLocaleSwitcher.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useTranslations } from '@/composables/useTranslations';

const { locale, t } = useTranslations();
const { isMobile, state } = useSidebar();

const localeOptions = computed(() => [
    { code: 'en', nativeLabel: t('app.language.locales.english.native'), flag: '🇬🇧' },
    { code: 'ro', nativeLabel: t('app.language.locales.romanian.native'), flag: '🇷🇴' },
    { code: 'el', nativeLabel: t('app.language.locales.greek.native'), flag: '🇬🇷' },
]);

const activeLocale = computed(
    () => localeOptions.value.find((option) => option.code === locale.value) ?? localeOptions.value[0],
);
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        size="default"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        :tooltip="t('app.language.label')"
                    >
                        <Languages />
                        <span>{{ t('app.language.label') }}</span>
                        <span class="ml-auto text-xs text-sidebar-foreground/65 group-data-[collapsible=icon]:hidden">
                            {{ activeLocale.flag }} {{ activeLocale.nativeLabel }}
                        </span>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-64 rounded-lg"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'top'
                    "
                    align="end"
                    :side-offset="8"
                >
                    <AppLocaleSwitcher />
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
