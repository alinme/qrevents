<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventGuest;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearFakeEventAlbum extends Command
{
    protected $signature = 'events:clear-fake-album
        {event : Event id or share token}
        {batchId? : Specific fake batch id to delete}
        {--all : Delete all fake batches for the event}
        {--dry-run : Preview without deleting anything}';

    protected $description = 'Delete fake album guests, assets, likes, and comments for one generated batch or an entire event.';

    public function handle(): int
    {
        $event = $this->resolveEvent((string) $this->argument('event'));
        $deleteAll = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');
        $batchId = trim((string) ($this->argument('batchId') ?? ''));

        if (! $deleteAll && $batchId === '') {
            $knownBatches = $this->detectFakeBatches($event);

            if ($knownBatches->count() === 1) {
                $batchId = (string) $knownBatches->first();
            } else {
                $this->error('Provide a batch id or use --all.');

                if ($knownBatches->isNotEmpty()) {
                    $this->line('Detected fake batches: '.$knownBatches->implode(', '));
                } else {
                    $this->line('No fake batches detected for this event.');
                }

                return self::FAILURE;
            }
        }

        $assetQuery = $this->fakeAssetQuery($event, $deleteAll ? null : $batchId);
        $guestQuery = $this->fakeGuestQuery($event, $deleteAll ? null : $batchId);

        $assets = $assetQuery->get([
            'id',
            'disk',
            'path',
            'thumbnail_path',
            'preview_path',
            'watermarked_thumbnail_path',
            'watermarked_preview_path',
            'watermarked_download_path',
            'video_thumbnail_path',
            'watermarked_video_thumbnail_path',
            'video_preview_path',
            'watermarked_video_preview_path',
            'watermarked_video_download_path',
            'size_bytes',
        ]);
        $guests = $guestQuery->get(['id', 'guest_token']);
        $assetCount = $assets->count();
        $guestCount = $guests->count();
        $storageBytes = (int) ($assets->sum('size_bytes') ?? 0);

        if ($assetCount === 0 && $guestCount === 0) {
            $scope = $deleteAll ? 'all fake batches' : "batch {$batchId}";
            $this->warn("Nothing to delete for {$scope} on event {$event->id}.");

            return self::SUCCESS;
        }

        $scopeLabel = $deleteAll ? 'all fake batches' : "batch {$batchId}";
        $this->info("Event: {$event->id} {$event->name}");
        $this->info("Scope: {$scopeLabel}");
        $this->info("Fake guests: {$guestCount}");
        $this->info("Fake assets: {$assetCount}");
        $this->info("Storage bytes: {$storageBytes}");

        if ($dryRun) {
            $this->comment('Dry run only. No fake data deleted.');

            return self::SUCCESS;
        }

        $pathsByDisk = $assets
            ->groupBy(fn (EventAsset $asset): string => $asset->disk)
            ->map(function (Collection $diskAssets): array {
                return $diskAssets
                    ->flatMap(fn (EventAsset $asset): array => array_values(array_filter([
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
                    ], fn (mixed $path): bool => is_string($path) && trim($path) !== '')))
                    ->unique()
                    ->values()
                    ->all();
            });

        DB::transaction(function () use ($event, $assetQuery, $guestQuery): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);

            $assetQuery->delete();
            $guestQuery->delete();

            $lockedEvent->forceFill([
                'upload_count' => (int) EventAsset::query()->where('event_id', $lockedEvent->id)->count(),
                'storage_used_bytes' => (int) (EventAsset::query()->where('event_id', $lockedEvent->id)->sum('size_bytes') ?? 0),
            ])->save();
        });

        $deletedFiles = 0;

        foreach ($pathsByDisk as $disk => $paths) {
            $existingPaths = array_values(array_filter(
                $paths,
                fn (string $path): bool => Storage::disk($disk)->exists($path),
            ));

            if ($existingPaths === []) {
                continue;
            }

            $deletedFiles += Storage::disk($disk)->delete($existingPaths) ? count($existingPaths) : 0;
        }

        $this->info("Deleted {$guestCount} fake guests.");
        $this->info("Deleted {$assetCount} fake assets.");
        $this->info("Deleted {$deletedFiles} stored files.");

        return self::SUCCESS;
    }

    private function resolveEvent(string $eventReference): Event
    {
        return Event::query()
            ->where('id', is_numeric($eventReference) ? (int) $eventReference : -1)
            ->orWhere('share_token', $eventReference)
            ->firstOrFail();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<EventAsset>
     */
    private function fakeAssetQuery(Event $event, ?string $batchId): \Illuminate\Database\Eloquent\Builder
    {
        $basePrefix = "events/{$event->id}/fake-batches/";
        $query = EventAsset::query()
            ->where('event_id', $event->id)
            ->where('path', 'like', $basePrefix.'%');

        if ($batchId !== null && $batchId !== '') {
            $query->where('path', 'like', $basePrefix.$batchId.'/%');
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<EventGuest>
     */
    private function fakeGuestQuery(Event $event, ?string $batchId): \Illuminate\Database\Eloquent\Builder
    {
        $query = EventGuest::query()
            ->where('event_id', $event->id)
            ->where('guest_token', 'like', 'fake:%');

        if ($batchId !== null && $batchId !== '') {
            $query->where('guest_token', 'like', "fake:{$batchId}:%");
        }

        return $query;
    }

    /**
     * @return Collection<int, string>
     */
    private function detectFakeBatches(Event $event): Collection
    {
        $prefix = "events/{$event->id}/fake-batches/";

        $assetBatches = EventAsset::query()
            ->where('event_id', $event->id)
            ->where('path', 'like', $prefix.'%')
            ->pluck('path')
            ->map(function (string $path) use ($prefix): ?string {
                if (! str_starts_with($path, $prefix)) {
                    return null;
                }

                $relative = substr($path, strlen($prefix));
                $segments = explode('/', (string) $relative);

                return $segments[0] ?? null;
            });

        $guestBatches = EventGuest::query()
            ->where('event_id', $event->id)
            ->where('guest_token', 'like', 'fake:%')
            ->pluck('guest_token')
            ->map(function (string $guestToken): ?string {
                $segments = explode(':', $guestToken, 3);

                return $segments[1] ?? null;
            });

        return $assetBatches
            ->concat($guestBatches)
            ->filter(fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(fn (string $value): string => trim($value))
            ->unique()
            ->values();
    }
}
