import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface TranslationTree {
    [key: string]: string | TranslationTree;
}

type TranslationPageProps = {
    locale?: {
        current?: string;
        available?: string[];
    };
    translations?: TranslationTree;
};

const resolveTranslation = (
    source: TranslationTree | undefined,
    key: string,
): string | null => {
    if (!source) {
        return null;
    }

    const segments = key.split('.');
    let current: string | TranslationTree | undefined = source;

    for (const segment of segments) {
        if (
            current === undefined ||
            current === null ||
            typeof current === 'string' ||
            !(segment in current)
        ) {
            return null;
        }

        current = current[segment];
    }

    return typeof current === 'string' ? current : null;
};

const interpolateTranslation = (
    template: string,
    replacements: Record<string, string | number>,
): string =>
    // Match Laravel's translator: replace each known placeholder directly,
    // longest name first, so ":max" still applies inside ":maxs".
    Object.keys(replacements)
        .sort((a, b) => b.length - a.length)
        .reduce(
            (result, name) =>
                result.split(`:${name}`).join(String(replacements[name])),
            template,
        );

export function useTranslations() {
    const page = usePage<TranslationPageProps>();
    const locale = computed(() => page.props.locale?.current ?? 'en');
    const translations = computed(() => page.props.translations ?? {});

    const t = (
        key: string,
        replacements: Record<string, string | number> = {},
    ): string => {
        const translation = resolveTranslation(translations.value, key) ?? key;

        if (Object.keys(replacements).length === 0) {
            return translation;
        }

        return interpolateTranslation(translation, replacements);
    };

    return {
        locale,
        translations,
        t,
    };
}
