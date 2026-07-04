<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateS3Storage extends Command
{
    protected $signature = 'storage:migrate-s3
        {--from=s3_legacy : Source disk}
        {--to=s3 : Destination disk}
        {--dry-run : List what would be copied without writing}';

    protected $description = 'Copy every object from the legacy S3 bucket to the current one (resumable).';

    public function handle(): int
    {
        $source = Storage::disk((string) $this->option('from'));
        $dest = Storage::disk((string) $this->option('to'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Listing objects in the source bucket…');
        $files = $source->allFiles();
        $total = count($files);
        $this->info("Found {$total} objects.".($dryRun ? ' (dry run)' : ''));

        $copied = 0;
        $skipped = 0;
        $errors = 0;
        $bytes = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($files as $path) {
            try {
                if ($dest->exists($path)) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $stream = $source->readStream($path);
                    $dest->writeStream($path, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $bytes += (int) $source->size($path);
                }

                $copied++;
            } catch (Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("{$path}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info(sprintf(
            'Done. Copied %d, skipped %d (already present), errors %d, transferred %.1f MB.',
            $copied,
            $skipped,
            $errors,
            $bytes / 1_048_576,
        ));

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
