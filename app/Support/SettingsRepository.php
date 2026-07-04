<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingsRepository
{
    private const CACHE_KEY = 'site_settings.map';

    /**
     * @var array<string, array{value: string|null, is_encrypted: bool, group: string}>|null
     */
    private ?array $map = null;

    /**
     * Full in-memory map of stored rows (cached across requests).
     *
     * @return array<string, array{value: string|null, is_encrypted: bool, group: string}>
     */
    private function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        // During early migration the table may not exist yet.
        if (! $this->tableExists()) {
            return $this->map = [];
        }

        return $this->map = Cache::rememberForever(self::CACHE_KEY, function (): array {
            return SiteSetting::query()
                ->get(['key', 'value', 'is_encrypted', 'group'])
                ->keyBy('key')
                ->map(fn (SiteSetting $setting): array => [
                    'value' => $setting->value,
                    'is_encrypted' => (bool) $setting->is_encrypted,
                    'group' => (string) $setting->group,
                ])
                ->all();
        });
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->map());
    }

    /**
     * Get a decoded (and decrypted) setting value, or the default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->map()[$key] ?? null;

        if ($row === null || $row['value'] === null) {
            return $default;
        }

        $raw = $row['value'];

        if ($row['is_encrypted']) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (Throwable) {
                return $default;
            }
        }

        $decoded = json_decode($raw, true);

        return $decoded === null && json_last_error() !== JSON_ERROR_NONE ? $default : $decoded;
    }

    /**
     * Non-empty check for a (possibly encrypted) secret without exposing it.
     */
    public function isFilled(string $key): bool
    {
        $value = $this->get($key);

        return $value !== null && $value !== '';
    }

    /**
     * All decoded values for a group.
     *
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        $values = [];

        foreach ($this->map() as $key => $row) {
            if ($row['group'] === $group) {
                $values[$key] = $this->get($key);
            }
        }

        return $values;
    }

    public function set(string $key, mixed $value, string $group, bool $encrypted = false): void
    {
        $encoded = json_encode($value);
        $stored = $encrypted ? Crypt::encryptString((string) $encoded) : $encoded;

        SiteSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'group' => $group,
                'is_encrypted' => $encrypted,
            ],
        );

        $this->forget();
    }

    /**
     * Persist many values in a group. Keys listed in $encryptedKeys are encrypted;
     * an encrypted value that comes in as null/'' is treated as "keep existing".
     *
     * @param  array<string, mixed>  $values
     * @param  list<string>  $encryptedKeys
     */
    public function setMany(string $group, array $values, array $encryptedKeys = []): void
    {
        foreach ($values as $key => $value) {
            $encrypted = in_array($key, $encryptedKeys, true);

            if ($encrypted && ($value === null || $value === '')) {
                continue; // blank secret => keep the existing one
            }

            $this->set($key, $value, $group, $encrypted);
        }
    }

    public function forget(): void
    {
        $this->map = null;
        Cache::forget(self::CACHE_KEY);
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('site_settings');
        } catch (Throwable) {
            return false;
        }
    }
}
