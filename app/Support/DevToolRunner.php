<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Runs a FIXED allowlist of maintenance operations. There is no free-form
 * command input anywhere — each key maps to a hardcoded operation, so the
 * web endpoint can never execute arbitrary shell/artisan commands.
 */
class DevToolRunner
{
    /**
     * @return array<string, array{label: string, description: string, danger: bool}>
     */
    public function operations(): array
    {
        return [
            'caches_clear' => [
                'label' => 'Clear all caches',
                'description' => 'config, route, view, application & event caches',
                'danger' => false,
            ],
            'caches_rebuild' => [
                'label' => 'Rebuild caches',
                'description' => 'config:cache · route:cache · view:cache',
                'danger' => false,
            ],
            'queue_restart' => [
                'label' => 'Restart queue workers',
                'description' => 'gracefully restart background workers',
                'danger' => false,
            ],
            'optimize' => [
                'label' => 'Optimize',
                'description' => 'cache config, routes & views for production',
                'danger' => false,
            ],
            'migrate' => [
                'label' => 'Run migrations',
                'description' => 'php artisan migrate --force — applies schema changes',
                'danger' => true,
            ],
            'build' => [
                'label' => 'Rebuild assets',
                'description' => 'npm run build — recompiles the frontend (~30-60s)',
                'danger' => true,
            ],
        ];
    }

    public function allows(string $operation): bool
    {
        return array_key_exists($operation, $this->operations());
    }

    /**
     * @return array{ok: bool, output: string}
     */
    public function run(string $operation): array
    {
        try {
            return match ($operation) {
                'caches_clear' => $this->artisan('optimize:clear'),
                'caches_rebuild' => $this->artisanChain(['config:cache', 'route:cache', 'view:cache']),
                'queue_restart' => $this->artisan('queue:restart'),
                'optimize' => $this->artisan('optimize'),
                'migrate' => $this->artisan('migrate', ['--force' => true]),
                'build' => $this->build(),
                default => ['ok' => false, 'output' => 'Unknown operation.'],
            };
        } catch (Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array{ok: bool, output: string}
     */
    private function artisan(string $command, array $args = []): array
    {
        Artisan::call($command, $args);

        return ['ok' => true, 'output' => trim(Artisan::output()) ?: "{$command} completed."];
    }

    /**
     * @param  list<string>  $commands
     * @return array{ok: bool, output: string}
     */
    private function artisanChain(array $commands): array
    {
        $output = [];
        foreach ($commands as $command) {
            Artisan::call($command);
            $output[] = "$ php artisan {$command}\n".trim(Artisan::output());
        }

        return ['ok' => true, 'output' => trim(implode("\n\n", $output))];
    }

    /**
     * @return array{ok: bool, output: string}
     */
    private function build(): array
    {
        // Node/npm live under nvm, which is not on the non-interactive PATH.
        $result = Process::path(base_path())
            ->timeout(300)
            ->run('bash -lc "export NVM_DIR=\$HOME/.nvm; [ -s \$NVM_DIR/nvm.sh ] && . \$NVM_DIR/nvm.sh; npm run build 2>&1"');

        $output = trim($result->output().$result->errorOutput());

        return [
            'ok' => $result->successful(),
            'output' => $output !== '' ? $output : 'Asset build finished.',
        ];
    }
}
