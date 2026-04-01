<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventDataPurger
{
    /**
     * @return array{deletedAssetCount: int, reclaimedStorageBytes: int, clearedExport: bool, deletedFileCount: int}
     */
    public function purgeEventMedia(Event $event): array
    {
        return DB::transaction(function () use ($event): array {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $assets = EventAsset::query()
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->get();

            $deletedAssetCount = $assets->count();
            $reclaimedStorageBytes = (int) $assets->sum('size_bytes');
            $deletedFileCount = 0;

            foreach ($assets as $asset) {
                $deletedFileCount += $this->deleteEventAssetFiles($asset);
                $asset->delete();
            }

            $clearedExport = $this->eventHasStoredExportArchive($lockedEvent);
            if ($clearedExport) {
                $deletedFileCount += 1;
            }

            $this->resetEventExportState($lockedEvent, deleteStoredFile: $clearedExport);

            return [
                'deletedAssetCount' => $deletedAssetCount,
                'reclaimedStorageBytes' => $reclaimedStorageBytes,
                'clearedExport' => $clearedExport,
                'deletedFileCount' => $deletedFileCount,
            ];
        });
    }

    /**
     * @return array{deletedAssetCount: int, reclaimedStorageBytes: int, clearedExport: bool, deletedFileCount: int}
     */
    public function purgeEventForDeletion(Event $event): array
    {
        return DB::transaction(function () use ($event): array {
            $lockedEvent = Event::query()->withTrashed()->lockForUpdate()->findOrFail($event->id);
            $result = $this->purgeEventMedia($lockedEvent);
            $lockedEvent->forceDelete();

            return $result;
        });
    }

    /**
     * @return array{assetCount: int, reclaimableStorageBytes: int, storedFileCount: int, hasStoredExportArchive: bool}
     */
    public function inspectEventForDeletion(Event $event): array
    {
        $assets = EventAsset::query()
            ->where('event_id', $event->id)
            ->get();

        $storedFileCount = $assets
            ->map(fn (EventAsset $asset): int => count($this->assetFilePaths($asset)))
            ->sum();
        $hasStoredExportArchive = $this->eventHasStoredExportArchive($event);

        if ($hasStoredExportArchive) {
            $storedFileCount += 1;
        }

        return [
            'assetCount' => $assets->count(),
            'reclaimableStorageBytes' => (int) $assets->sum('size_bytes'),
            'storedFileCount' => $storedFileCount,
            'hasStoredExportArchive' => $hasStoredExportArchive,
        ];
    }

    public function clearEventExportArchive(Event $event): bool
    {
        return DB::transaction(function () use ($event): bool {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $hadArchive = $this->eventHasStoredExportArchive($lockedEvent);

            $this->resetEventExportState($lockedEvent, deleteStoredFile: $hadArchive);

            return $hadArchive;
        });
    }

    public function eventHasStoredExportArchive(Event $event): bool
    {
        return is_string($event->media_export_disk)
            && $event->media_export_disk !== ''
            && is_string($event->media_export_path)
            && $event->media_export_path !== '';
    }

    public function resetEventExportState(Event $event, bool $deleteStoredFile = false): void
    {
        if ($deleteStoredFile && $this->eventHasStoredExportArchive($event)) {
            Storage::disk((string) $event->media_export_disk)->delete((string) $event->media_export_path);
        }

        $event->forceFill([
            'media_export_status' => null,
            'media_export_token' => null,
            'media_export_disk' => null,
            'media_export_path' => null,
            'media_export_requested_at' => null,
            'media_export_started_at' => null,
            'media_export_completed_at' => null,
            'media_export_failed_at' => null,
            'media_export_error' => null,
        ])->save();
    }

    public function deleteEventAssetFiles(EventAsset $asset): int
    {
        $paths = $this->assetFilePaths($asset);
        Storage::disk($asset->disk)->delete($paths);

        return count($paths);
    }

    /**
     * @return list<string>
     */
    public function assetFilePaths(EventAsset $asset): array
    {
        return array_values(array_filter([
            $asset->path,
            $asset->thumbnail_path,
            $asset->preview_path,
            $asset->watermarked_thumbnail_path,
            $asset->watermarked_preview_path,
            $asset->watermarked_download_path,
            $asset->video_thumbnail_path,
            $asset->watermarked_video_thumbnail_path,
            $asset->video_preview_path,
            $asset->watermarked_video_preview_path,
            $asset->watermarked_video_download_path,
        ], fn (?string $path): bool => is_string($path) && $path !== ''));
    }
}
