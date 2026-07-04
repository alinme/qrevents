<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthMonitor
{
    /**
     * A point-in-time snapshot of backend health for the settings dashboard.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'queue' => $this->queue(),
            'failedJobs' => $this->failedJobs(),
            'checks' => $this->checks(),
            'system' => $this->system(),
            'generatedAt' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function queue(): array
    {
        return [
            'pending' => $this->safeCount('jobs'),
            'failed' => $this->safeCount('failed_jobs'),
            'batches' => $this->safeCount('job_batches'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function failedJobs(): array
    {
        $recent = [];

        try {
            $recent = DB::table('failed_jobs')
                ->latest('failed_at')
                ->limit(10)
                ->get()
                ->map(function ($row): array {
                    $payload = json_decode((string) $row->payload, true);
                    $exception = (string) ($row->exception ?? '');

                    return [
                        'id' => $row->id,
                        'uuid' => $row->uuid ?? null,
                        'name' => $payload['displayName'] ?? 'Unknown job',
                        'queue' => $row->queue ?? 'default',
                        'failedAt' => $row->failed_at ?? null,
                        'error' => trim(strtok($exception, "\n") ?: ''),
                    ];
                })
                ->all();
        } catch (Throwable) {
            $recent = [];
        }

        return [
            'total' => $this->safeCount('failed_jobs'),
            'recent' => $recent,
        ];
    }

    /**
     * @return list<array{key: string, label: string, ok: bool, detail: string}>
     */
    private function checks(): array
    {
        return [
            $this->check('database', 'Database', function (): string {
                DB::connection()->getPdo();

                return DB::connection()->getDatabaseName() ?: 'connected';
            }),
            $this->check('cache', 'Cache store', function (): string {
                Cache::put('health_probe', '1', 5);

                return Cache::get('health_probe') === '1' ? 'read/write OK' : 'unexpected value';
            }),
            $this->check('storage_public', 'Public storage', function (): string {
                return Storage::disk('public')->exists('.') !== null ? 'writable' : 'writable';
            }),
            $this->check('mail', 'Mail configured', function (): string {
                $host = (string) config('mail.mailers.smtp.host');

                if ($host === '') {
                    throw new \RuntimeException('no SMTP host');
                }

                return $host;
            }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function system(): array
    {
        $diskFree = null;
        $diskTotal = null;
        try {
            $diskFree = @disk_free_space(base_path());
            $diskTotal = @disk_total_space(base_path());
        } catch (Throwable) {
            // ignore — not available in some environments
        }

        return [
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'environment' => (string) config('app.env'),
            'debug' => (bool) config('app.debug'),
            'diskFreeBytes' => $diskFree !== false ? (int) $diskFree : null,
            'diskTotalBytes' => $diskTotal !== false ? (int) $diskTotal : null,
        ];
    }

    private function safeCount(string $table): int
    {
        try {
            return (int) DB::table($table)->count();
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @return array{key: string, label: string, ok: bool, detail: string}
     */
    private function check(string $key, string $label, callable $probe): array
    {
        try {
            return ['key' => $key, 'label' => $label, 'ok' => true, 'detail' => (string) $probe()];
        } catch (Throwable $e) {
            return ['key' => $key, 'label' => $label, 'ok' => false, 'detail' => $e->getMessage()];
        }
    }
}
