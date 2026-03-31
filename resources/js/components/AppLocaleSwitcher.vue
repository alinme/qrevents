<script setup lang="ts">
import { Languages } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
} from '@/components/ui/dropdown-menu';
import { useTranslations } from '@/composables/useTranslations';

const { locale, t } = useTranslations();

const localeOptions = computed(() => [
    {
        code: 'en',
        nativeLabel: t('app.language.locales.english.native'),
        label: t('app.language.locales.english.label'),
        flag: '🇬🇧',
    },
    {
        code: 'ro',
        nativeLabel: t('app.language.locales.romanian.native'),
        label: t('app.language.locales.romanian.label'),
        flag: '🇷🇴',
    },
    {
        code: 'el',
        nativeLabel: t('app.language.locales.greek.native'),
        label: t('app.language.locales.greek.label'),
        flag: '🇬🇷',
    },
]);

const switchLocale = (nextLocale: string): void => {
    if (
        nextLocale === locale.value
        || typeof document === 'undefined'
        || typeof window === 'undefined'
    ) {
        return;
    }

    const maxAge = 60 * 60 * 24 * 365;
    document.cookie = `site_locale=${nextLocale}; path=/; max-age=${maxAge}; SameSite=Lax`;
    window.location.reload();
};
</script>

<template>
    <DropdownMenuGroup>
        <DropdownMenuLabel class="px-2 pb-1.5 pt-0 text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
            {{ t('app.language.label') }}
        </DropdownMenuLabel>
        <DropdownMenuItem
            v-for="option in localeOptions"
            :key="option.code"
            class="cursor-pointer"
            @click="switchLocale(option.code)"
        >
            <span class="mr-2 text-base leading-none">{{ option.flag }}</span>
            <div class="flex min-w-0 flex-1 items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="truncate font-medium">
                        {{ option.nativeLabel }}
                    </div>
                    <div class="truncate text-xs text-muted-foreground">
                        {{ option.label }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Languages class="size-4 text-muted-foreground" />
                    <span
                        class="size-2.5 rounded-full"
                        :class="
                            locale === option.code
                                ? 'bg-primary'
                                : 'bg-border'
                        "
                    />
                </div>
            </div>
        </DropdownMenuItem>
    </DropdownMenuGroup>
</template>
