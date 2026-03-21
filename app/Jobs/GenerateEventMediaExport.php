<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class GenerateEventMediaExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $eventId,
        public readonly string $token,
    ) {}

    public function handle(): void
    {
        $this->liftExecutionLimits();

        if (! class_exists(ZipArchive::class)) {
            $this->markFailed('ZIP export is not available on this server.');

            return;
        }

        $event = Event::query()->find($this->eventId);
        if (! $event instanceof Event || $event->media_export_token !== $this->token) {
            return;
        }

        $event->forceFill([
            'media_export_status' => 'processing',
            'media_export_started_at' => now(),
            'media_export_failed_at' => null,
            'media_export_error' => null,
        ])->save();

        $tempDirectory = storage_path('app/temp/event-exports/'.Str::uuid()->toString());
        if (! is_dir($tempDirectory) && ! @mkdir($tempDirectory, 0755, true) && ! is_dir($tempDirectory)) {
            $this->markFailed('Export could not be prepared.');

            return;
        }

        $zipPath = $tempDirectory.'/media-export.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @rmdir($tempDirectory);
            $this->markFailed('Export could not be prepared.');

            return;
        }

        $temporaryFiles = [];
        $exportedEntries = 0;

        try {
            $usedEntryNames = [];
            $assetIndex = 0;

            foreach ($this->exportAssetsQuery()->cursor() as $asset) {
                $assetIndex++;
                $entryName = $this->mediaExportEntryName($asset, $assetIndex, $usedEntryNames);

                if ($asset->kind === 'text') {
                    $contents = $this->textAssetExportContents($asset);
                    if ($contents === null) {
                        continue;
                    }

                    $zip->addFromString($entryName, $contents);
                    $exportedEntries++;

                    continue;
                }

                $disk = Storage::disk($asset->disk);

                try {
                    $stream = $disk->readStream($asset->path);
                } catch (\Throwable) {
                    $stream = false;
                }

                if (! is_resource($stream)) {
                    continue;
                }

                $localCopyPath = $tempDirectory.'/'.Str::uuid()->toString().'-'.basename($entryName);
                $localCopy = fopen($localCopyPath, 'wb');
                if (! is_resource($localCopy)) {
                    fclose($stream);

                    continue;
                }

                stream_copy_to_stream($stream, $localCopy);
                fclose($stream);
                fclose($localCopy);

                $temporaryFiles[] = $localCopyPath;
                $zip->addFile($localCopyPath, $entryName);
                $exportedEntries++;
            }

            $zip->close();

            foreach ($temporaryFiles as $temporaryFile) {
                @unlink($temporaryFile);
            }

            if ($exportedEntries === 0) {
                @unlink($zipPath);
                @rmdir($tempDirectory);
                $this->markFailed('No approved media was available to export.');

                return;
            }

            $freshEvent = Event::query()->find($this->eventId);
            if (! $freshEvent instanceof Event || $freshEvent->media_export_token !== $this->token) {
                @unlink($zipPath);
                @rmdir($tempDirectory);

                return;
            }

            $exportDisk = (string) config('events.upload_disk', 'public');
            $exportPath = sprintf(
                'events/%d/exports/%s-album-export-%s.zip',
                $freshEvent->id,
                Str::slug($freshEvent->name) !== '' ? Str::slug($freshEvent->name) : 'event',
                now()->format('Ymd-His'),
            );

            $stream = fopen($zipPath, 'rb');
            if (! is_resource($stream)) {
                @unlink($zipPath);
                @rmdir($tempDirectory);
                $this->markFailed('Export file could not be finalized.');

                return;
            }

            $stored = Storage::disk($exportDisk)->put(
                $exportPath,
                $stream,
                ['visibility' => 'private'],
            );
            fclose($stream);
            @unlink($zipPath);
            @rmdir($tempDirectory);

            if (! $stored) {
                $this->markFailed('Export file could not be stored.');

                return;
            }

            if (
                is_string($freshEvent->media_export_disk)
                && $freshEvent->media_export_disk !== ''
                && is_string($freshEvent->media_export_path)
                && $freshEvent->media_export_path !== ''
            ) {
                Storage::disk($freshEvent->media_export_disk)->delete($freshEvent->media_export_path);
            }

            $freshEvent->forceFill([
                'media_export_status' => 'ready',
                'media_export_disk' => $exportDisk,
                'media_export_path' => $exportPath,
                'media_export_completed_at' => now(),
                'media_export_failed_at' => null,
                'media_export_error' => null,
            ])->save();
        } catch (\Throwable) {
            foreach ($temporaryFiles as $temporaryFile) {
                @unlink($temporaryFile);
            }
            @unlink($zipPath);
            @rmdir($tempDirectory);

            $this->markFailed('Export generation failed.');
        }
    }

    public function failed(\Throwable $exception): void
    {
        $message = $exception->getMessage();
        if (! is_string($message) || trim($message) === '') {
            $message = 'Export generation failed.';
        }

        $this->markFailed($message);
    }

    private function exportAssetsQuery()
    {
        return EventAsset::query()
            ->where('event_id', $this->eventId)
            ->where('moderation_status', 'approved')
            ->orderBy('id');
    }

    /**
     * @param  array<string, true>  $usedEntryNames
     */
    private function mediaExportEntryName(EventAsset $asset, int $position, array &$usedEntryNames): string
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $guestSlug = Str::slug((string) ($metadata['guest_name'] ?? 'guest'));
        if ($guestSlug === '') {
            $guestSlug = 'guest';
        }

        $timestamp = $asset->created_at?->format('Ymd-His') ?? now()->format('Ymd-His');
        $baseName = sprintf('%04d-%s-%s', $position, $guestSlug, $timestamp);

        if ($asset->kind === 'text') {
            return $this->uniqueExportEntryName("text-posts/{$baseName}.txt", $usedEntryNames);
        }

        $originalExtension = Str::lower((string) pathinfo($asset->original_filename ?: $asset->path, PATHINFO_EXTENSION));
        if ($originalExtension === '') {
            $originalExtension = $asset->kind === 'video' ? 'mp4' : 'jpg';
        }

        $folder = $asset->kind === 'video' ? 'videos' : 'photos';

        return $this->uniqueExportEntryName(
            "{$folder}/{$baseName}.{$originalExtension}",
            $usedEntryNames,
        );
    }

    /**
     * @param  array<string, true>  $usedEntryNames
     */
    private function uniqueExportEntryName(string $candidate, array &$usedEntryNames): string
    {
        if (! isset($usedEntryNames[$candidate])) {
            $usedEntryNames[$candidate] = true;

            return $candidate;
        }

        $extension = pathinfo($candidate, PATHINFO_EXTENSION);
        $directory = pathinfo($candidate, PATHINFO_DIRNAME);
        $filename = pathinfo($candidate, PATHINFO_FILENAME);
        $suffix = 2;

        do {
            $alternate = $directory === '.'
                ? "{$filename}-{$suffix}".($extension !== '' ? ".{$extension}" : '')
                : "{$directory}/{$filename}-{$suffix}".($extension !== '' ? ".{$extension}" : '');
            $suffix++;
        } while (isset($usedEntryNames[$alternate]));

        $usedEntryNames[$alternate] = true;

        return $alternate;
    }

    private function textAssetExportContents(EventAsset $asset): ?string
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $text = is_string($metadata['text'] ?? null)
            ? trim((string) $metadata['text'])
            : '';

        if ($text === '') {
            return null;
        }

        $guestName = is_string($metadata['guest_name'] ?? null) && trim((string) $metadata['guest_name']) !== ''
            ? trim((string) $metadata['guest_name'])
            : 'Guest';
        $createdAt = $asset->created_at?->toDateTimeString() ?? now()->toDateTimeString();
        $message = is_string($metadata['message'] ?? null)
            ? trim((string) $metadata['message'])
            : '';

        $lines = [
            "Guest: {$guestName}",
            "Created at: {$createdAt}",
        ];

        if ($message !== '') {
            $lines[] = "Message: {$message}";
        }

        $lines[] = '';
        $lines[] = $text;
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    private function markFailed(string $message): void
    {
        $event = Event::query()->find($this->eventId);
        if (! $event instanceof Event || $event->media_export_token !== $this->token) {
            return;
        }

        $event->forceFill([
            'media_export_status' => 'failed',
            'media_export_failed_at' => now(),
            'media_export_error' => $message,
        ])->save();
    }

    private function liftExecutionLimits(): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '-1');

        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
    }
}
