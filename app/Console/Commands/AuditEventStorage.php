<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventGuest;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AuditEventStorage extends Command
{
    protected $signature = 'events:audit-storage
        {event? : Event id or share token. Omit to audit all events}
        {--delete-orphans : Delete orphaned files from storage}
        {--dry-run : Show what would be deleted without deleting anything}';

    protected $description = 'Compare app-managed storage files against database paths and optionally delete orphaned files.';

    public function handle(): int
    {
        $eventReference = trim((string) ($this->argument('event') ?? ''));
        $deleteOrphans = (bool) $this->option('delete-orphans');
        $dryRun = (bool) $this->option('dry-run');

        if ($deleteOrphans && $dryRun) {
            $this->error('Use either --delete-orphans or --dry-run, not both.');

            return self::FAILURE;
        }

        $event = $eventReference !== '' ? $this->resolveEvent($eventReference) : null;
        $scopeLabel = $event !== null
            ? "event {$event->id} {$event->name}"
            : 'all events';

        $dbPathsByDisk = $this->databasePathsByDisk($event);
        $storagePathsByDisk = $this->storagePathsByDisk($event, $dbPathsByDisk->keys());

        $missingByDisk = collect();
        $orphanByDisk = collect();

        foreach ($storagePathsByDisk->keys()->merge($dbPathsByDisk->keys())->unique() as $disk) {
            $dbPaths = collect($dbPathsByDisk->get($disk, []));
            $storagePaths = collect($storagePathsByDisk->get($disk, []));

            $missingByDisk->put($disk, $dbPaths->diff($storagePaths)->values());
            $orphanByDisk->put($disk, $storagePaths->diff($dbPaths)->values());
        }

        $totalDbPaths = $dbPathsByDisk->sum(fn (Collection $paths): int => $paths->count());
        $totalStoragePaths = $storagePathsByDisk->sum(fn (Collection $paths): int => $paths->count());
        $totalMissing = $missingByDisk->sum(fn (Collection $paths): int => $paths->count());
        $totalOrphans = $orphanByDisk->sum(fn (Collection $paths): int => $paths->count());

        $this->info("Scope: {$scopeLabel}");
        $this->line("DB-managed paths: {$totalDbPaths}");
        $this->line("Storage files: {$totalStoragePaths}");
        $this->line("Missing files: {$totalMissing}");
        $this->line("Orphan files: {$totalOrphans}");

        foreach ($storagePathsByDisk->keys()->merge($dbPathsByDisk->keys())->unique() as $disk) {
            $this->newLine();
            $this->info("Disk: {$disk}");
            $this->line('  DB paths: '.collect($dbPathsByDisk->get($disk, []))->count());
            $this->line('  Storage files: '.collect($storagePathsByDisk->get($disk, []))->count());
            $this->line('  Missing: '.collect($missingByDisk->get($disk, []))->count());
            $this->line('  Orphans: '.collect($orphanByDisk->get($disk, []))->count());

            $this->printSampleList('Missing sample', collect($missingByDisk->get($disk, [])));
            $this->printSampleList('Orphan sample', collect($orphanByDisk->get($disk, [])));
        }

        if (! $deleteOrphans && ! $dryRun) {
            return self::SUCCESS;
        }

        if ($totalOrphans === 0) {
            $this->comment($dryRun
                ? 'Dry run: no orphan files would be deleted.'
                : 'No orphan files to delete.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->comment("Dry run: {$totalOrphans} orphan files would be deleted.");

            return self::SUCCESS;
        }

        $deletedCount = 0;

        foreach ($orphanByDisk as $disk => $paths) {
            /** @var Collection<int, string> $paths */
            if ($paths->isEmpty()) {
                continue;
            }

            $deleted = Storage::disk((string) $disk)->delete($paths->all());
            if ($deleted) {
                $deletedCount += $paths->count();
            }
        }

        $this->newLine();
        $this->info("Deleted orphan files: {$deletedCount}");

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
     * @return Collection<string, Collection<int, string>>
     */
    private function databasePathsByDisk(?Event $event): Collection
    {
        $pathsByDisk = collect();

        $assetQuery = EventAsset::query();
        if ($event !== null) {
            $assetQuery->where('event_id', $event->id);
        }

        $assetQuery->get()->each(function (EventAsset $asset) use ($pathsByDisk): void {
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->thumbnail_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->preview_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_thumbnail_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_preview_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_download_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->video_thumbnail_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_video_thumbnail_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->video_preview_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_video_preview_path);
            $this->pushPath($pathsByDisk, (string) $asset->disk, $asset->watermarked_video_download_path);
        });

        $guestQuery = EventGuest::query();
        if ($event !== null) {
            $guestQuery->where('event_id', $event->id);
        }

        $guestQuery->get()->each(function (EventGuest $guest) use ($pathsByDisk): void {
            if ($guest->avatar_disk === null) {
                return;
            }

            $this->pushPath($pathsByDisk, (string) $guest->avatar_disk, $guest->avatar_path);
        });

        $eventQuery = Event::query();
        if ($event !== null) {
            $eventQuery->whereKey($event->id);
        }

        $eventQuery->get()->each(function (Event $eventModel) use ($pathsByDisk): void {
            $branding = is_array($eventModel->branding) ? $eventModel->branding : [];

            $this->pushPath(
                $pathsByDisk,
                (string) ($branding['logo_disk'] ?? config('events.upload_disk', 'public')),
                $branding['logo_path'] ?? null,
            );
            $this->pushPath(
                $pathsByDisk,
                (string) ($branding['album_background_disk'] ?? config('events.upload_disk', 'public')),
                $branding['album_background_path'] ?? null,
            );
            $this->pushPath(
                $pathsByDisk,
                (string) ($branding['welcome_screen_background_disk'] ?? config('events.upload_disk', 'public')),
                $branding['welcome_screen_background_path'] ?? null,
            );
        });

        return $pathsByDisk
            ->map(fn (Collection $paths): Collection => $paths->unique()->values())
            ->sortKeys();
    }

    /**
     * @param  Collection<int, string>  $candidateDisks
     * @return Collection<string, Collection<int, string>>
     */
    private function storagePathsByDisk(?Event $event, Collection $candidateDisks): Collection
    {
        $pathsByDisk = collect();
        $prefix = $event !== null ? "events/{$event->id}" : 'events';

        foreach ($candidateDisks->filter()->unique()->values() as $disk) {
            /** @var string $disk */
            $paths = collect(Storage::disk($disk)->allFiles($prefix))
                ->filter(fn (mixed $path): bool => is_string($path) && trim((string) $path) !== '')
                ->values();

            $pathsByDisk->put($disk, $paths);
        }

        return $pathsByDisk->sortKeys();
    }

    /**
     * @param  Collection<string, Collection<int, string>>  $pathsByDisk
     */
    private function pushPath(Collection $pathsByDisk, string $disk, mixed $path): void
    {
        if (! is_string($path) || trim($path) === '') {
            return;
        }

        /** @var Collection<int, string> $paths */
        $paths = $pathsByDisk->get($disk, collect());
        $paths->push(trim($path));
        $pathsByDisk->put($disk, $paths);
    }

    /**
     * @param  Collection<int, string>  $items
     */
    private function printSampleList(string $label, Collection $items): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $this->line("  {$label}:");

        foreach ($items->take(5) as $item) {
            $this->line("    - {$item}");
        }
    }
}
