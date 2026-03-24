<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FrontendLocalization
{
    public const SITE_LOCALE_COOKIE = 'site_locale';

    private const SUPPORTED_LOCALES = ['en', 'ro', 'el'];

    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $translations = [];

    /**
     * @return list<string>
     */
    public static function supportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    public static function normalize(?string $locale): string
    {
        $fallbackLocale = config('app.fallback_locale', 'en');

        if (is_string($locale) && in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        return in_array($fallbackLocale, self::SUPPORTED_LOCALES, true)
            ? $fallbackLocale
            : 'en';
    }

    public static function resolveAutomaticLocale(Request $request): string
    {
        return self::normalize($request->getPreferredLanguage(self::SUPPORTED_LOCALES));
    }

    public static function resolveSiteLocale(Request $request): string
    {
        $cookieLocale = $request->cookie(self::SITE_LOCALE_COOKIE);

        if (is_string($cookieLocale) && in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
            return $cookieLocale;
        }

        return self::resolveAutomaticLocale($request);
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    public static function resolveEventLocale(Request $request, array $branding): string
    {
        $queryLocale = $request->query('lang');

        if (is_string($queryLocale) && in_array($queryLocale, self::SUPPORTED_LOCALES, true)) {
            return self::normalize($queryLocale);
        }

        $configuredLocale = $branding['display_language'] ?? 'automatic';

        if (! is_string($configuredLocale) || $configuredLocale === '' || $configuredLocale === 'automatic') {
            return self::resolveAutomaticLocale($request);
        }

        return self::normalize($configuredLocale);
    }

    /**
     * @return array<string, mixed>
     */
    public static function translationsFor(string $locale): array
    {
        $normalizedLocale = self::normalize($locale);

        if (! array_key_exists($normalizedLocale, self::$translations)) {
            $baseTranslations = self::loadFlatTranslations('en');
            $localizedTranslations = $normalizedLocale === 'en'
                ? $baseTranslations
                : array_replace($baseTranslations, self::loadFlatTranslations($normalizedLocale));

            self::$translations[$normalizedLocale] = self::nestTranslations($localizedTranslations);
        }

        return self::$translations[$normalizedLocale];
    }

    /**
     * @return array<string, string>
     */
    private static function loadFlatTranslations(string $locale): array
    {
        $path = lang_path("{$locale}.json");

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn (mixed $value, mixed $key): bool => is_string($key) && is_string($value))
            ->all();
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, mixed>
     */
    private static function nestTranslations(array $translations): array
    {
        $nested = [];

        foreach ($translations as $key => $value) {
            data_set($nested, $key, $value);
        }

        return $nested;
    }
}
