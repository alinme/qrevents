<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PruneTemporaryEventFiles extends Command
{
    protected $signature = 'events:prune-temp-files
        {--dry-run : Preview deletions without removing files}
        {--hours= : Minimum age in hours before a temporary file is pruned}';

    protected $description = 'Prune stale temporary event export and processing files.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $hours = max(1, (int) ($this->option('hours') ?: config('events.maintenance.temp_file_prune_after_hours', 12)));
        $cutoff = CarbonImmutable::now()->subHours($hours);

        $targets = [
            ...$this->collectExportDirectories($cutoff),
            ...$this->collectSystemTempFiles($cutoff),
        ];

        $directoryCount = 0;
        $fileCount = 0;
        $reclaimedBytes = 0;

        foreach ($targets as $target) {
            $reclaimedBytes += $target['bytes'];

            if ($target['type'] === 'directory') {
                $directoryCount++;
            } else {
                $fileCount++;
            }

            if ($dryRun) {
                continue;
            }

            if ($target['type'] === 'directory') {
                File::deleteDirectory($target['path']);
            } else {
                File::delete($target['path']);
            }
        }

        $this->info(sprintf('Prune cutoff: files older than %d hour(s).', $hours));
        $this->info("Matched {$directoryCount} directories and {$fileCount} files.");
        $this->info(sprintf('Reclaimable storage: %s.', $this->humanBytes($reclaimedBytes)));

        if ($dryRun) {
            $this->info('Dry run complete. No files were deleted.');
        } else {
            $this->info('Temporary file pruning complete.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{path: string, type: 'directory', bytes: int}>
     */
    private function collectExportDirectories(CarbonImmutable $cutoff): array
    {
        $basePath = storage_path('app/temp/event-exports');
        if (! File::isDirectory($basePath)) {
            return [];
        }

        return collect(File::directories($basePath))
            ->filter(fn (string $path): bool => CarbonImmutable::createFromTimestamp(File::lastModified($path))->lte($cutoff))
            ->map(fn (string $path): array => [
                'path' => $path,
                'type' => 'directory',
                'bytes' => $this->directorySize($path),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{path: string, type: 'file', bytes: int}>
     */
    private function collectSystemTempFiles(CarbonImmutable $cutoff): array
    {
        $tempPath = sys_get_temp_dir();
        $prefixes = ['qr-video-input-', 'qr-video-poster-', 'qr-video-variant-', 'qr-watermark-'];

        return collect(File::files($tempPath))
            ->filter(function (\SplFileInfo $file) use ($prefixes, $cutoff): bool {
                $filename = $file->getFilename();
                $matchesPrefix = collect($prefixes)->contains(
                    fn (string $prefix): bool => str_starts_with($filename, $prefix),
                );

                return $matchesPrefix
                    && CarbonImmutable::createFromTimestamp($file->getMTime())->lte($cutoff);
            })
            ->map(fn (\SplFileInfo $file): array => [
                'path' => $file->getPathname(),
                'type' => 'file',
                'bytes' => (int) $file->getSize(),
            ])
            ->values()
            ->all();
    }

    private function directorySize(string $path): int
    {
        return collect(File::allFiles($path))
            ->sum(fn (\SplFileInfo $file): int => (int) $file->getSize());
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf('%s %s', $value >= 10 || $power === 0 ? number_format($value, 0) : number_format($value, 1), $units[$power]);
    }
}
