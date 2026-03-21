<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEventAssetImageVariants;
use App\Models\Event;
use App\Models\EventAsset;
use Illuminate\Console\Command;

class BackfillEventImageVariants extends Command
{
    protected $signature = 'events:backfill-image-variants
        {event? : Event id or share token. Omit to process all events}
        {--sync : Process inline instead of dispatching to the queue}
        {--force : Rebuild even if variant paths already exist}';

    protected $description = 'Generate missing image thumbnail, preview, and watermarked variants for existing event photos.';

    public function handle(): int
    {
        $eventReference = trim((string) ($this->argument('event') ?? ''));
        $sync = (bool) $this->option('sync');
        $force = (bool) $this->option('force');

        $query = EventAsset::query()
            ->where('kind', 'photo')
            ->where('mime_type', 'like', 'image/%');

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
                    ->whereNull('thumbnail_path')
                    ->orWhereNull('preview_path')
                    ->orWhereNull('watermarked_thumbnail_path')
                    ->orWhereNull('watermarked_preview_path')
                    ->orWhereNull('watermarked_download_path');
            });
        }

        $assetIds = $query
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($assetIds === []) {
            $this->warn('No image assets need backfill.');

            return self::SUCCESS;
        }

        $this->info('Assets: '.count($assetIds));
        $this->info($sync ? 'Mode: sync' : 'Mode: queued');

        foreach ($assetIds as $assetId) {
            if ($sync) {
                (new GenerateEventAssetImageVariants((int) $assetId))->handle();
            } else {
                GenerateEventAssetImageVariants::dispatch((int) $assetId);
            }
        }

        $this->info($sync
            ? 'Image variant backfill completed.'
            : 'Image variant backfill dispatched to the queue.');

        return self::SUCCESS;
    }
}
