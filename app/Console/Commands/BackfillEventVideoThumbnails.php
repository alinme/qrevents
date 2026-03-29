<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEventAssetVideoThumbnails;
use App\Models\Event;
use App\Models\EventAsset;
use Illuminate\Console\Command;

class BackfillEventVideoThumbnails extends Command
{
    protected $signature = 'events:backfill-video-thumbnails
        {event? : Event id or share token. Omit to process all events}
        {--sync : Process inline instead of dispatching to the queue}
        {--force : Rebuild even if thumbnail paths already exist}';

    protected $description = 'Generate missing video poster thumbnails and preview variants for existing event videos.';

    public function handle(): int
    {
        $eventReference = trim((string) ($this->argument('event') ?? ''));
        $sync = (bool) $this->option('sync');
        $force = (bool) $this->option('force');

        $query = EventAsset::query()
            ->where('kind', 'video')
            ->where('mime_type', 'like', 'video/%');

        if ($eventReference !== '') {
            $event = Event::query()
                ->where('id', is_numeric($eventReference) ? (int) $eventReference : -1)
                ->orWhere('share_token', $eventReference)
                ->firstOrFail();

            $query->where('event_id', $event->id);

            $this->info("Event: {$event->id} {$event->name}");
        } else {
            $this->info('Event: all');
        }

        if (! $force) {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('video_thumbnail_path')
                    ->orWhereNull('video_preview_path');
            });
        }

        $assetIds = $query
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($assetIds === []) {
            $this->warn('No video assets need backfill.');

            return self::SUCCESS;
        }

        $this->info('Assets: '.count($assetIds));
        $this->info($sync ? 'Mode: sync' : 'Mode: queued');

        foreach ($assetIds as $assetId) {
            if ($sync) {
                (new GenerateEventAssetVideoThumbnails((int) $assetId))->handle();
            } else {
                GenerateEventAssetVideoThumbnails::dispatch((int) $assetId);
            }
        }

        $this->info($sync
            ? 'Video thumbnail backfill completed.'
            : 'Video thumbnail backfill dispatched to the queue.');

        return self::SUCCESS;
    }
}
