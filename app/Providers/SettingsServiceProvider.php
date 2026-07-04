<?php

namespace App\Providers;

use App\Support\SettingsRepository;
use Illuminate\Support\ServiceProvider;
use Throwable;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class);
    }

    public function boot(): void
    {
        // Apply admin-managed overrides on top of the .env config so the UI
        // actually configures the app. Guarded so a broken/empty store can
        // never take down mail or storage — we only override filled values.
        try {
            $settings = $this->app->make(SettingsRepository::class);
        } catch (Throwable) {
            return;
        }

        $this->applyMailOverrides($settings);
        $this->applyStorageOverrides($settings);
    }

    private function applyMailOverrides(SettingsRepository $settings): void
    {
        $host = $settings->get('mail.host');

        if (! is_string($host) || $host === '') {
            return; // not configured in the UI — keep .env
        }

        $overrides = [
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) ($settings->get('mail.port') ?: 587),
            'mail.mailers.smtp.username' => $settings->get('mail.username'),
            'mail.mailers.smtp.password' => $settings->get('mail.password'),
            'mail.from.address' => $settings->get('mail.from_address') ?: config('mail.from.address'),
            'mail.from.name' => $settings->get('mail.from_name') ?: config('mail.from.name'),
        ];

        $encryption = $settings->get('mail.encryption');
        if (is_string($encryption) && $encryption !== '' && $encryption !== 'none') {
            $overrides['mail.mailers.smtp.encryption'] = $encryption;
            $overrides['mail.mailers.smtp.scheme'] = $encryption === 'ssl' ? 'smtps' : 'smtp';
        }

        config(array_filter($overrides, fn ($value): bool => $value !== null));
    }

    private function applyStorageOverrides(SettingsRepository $settings): void
    {
        $key = $settings->get('storage.access_key');
        $bucket = $settings->get('storage.bucket');

        if (! is_string($key) || $key === '' || ! is_string($bucket) || $bucket === '') {
            return; // not configured in the UI — keep .env
        }

        config([
            'filesystems.disks.s3.key' => $key,
            'filesystems.disks.s3.secret' => $settings->get('storage.secret') ?: config('filesystems.disks.s3.secret'),
            'filesystems.disks.s3.region' => $settings->get('storage.region') ?: config('filesystems.disks.s3.region'),
            'filesystems.disks.s3.bucket' => $bucket,
            'filesystems.disks.s3.endpoint' => $settings->get('storage.endpoint') ?: config('filesystems.disks.s3.endpoint'),
        ]);
    }
}
