<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventAsset;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EnforceEventLifecycle extends Command
{
    protected $signature = 'events:enforce-lifecycle {--dry-run : Preview actions without writing changes}';

    protected $description = 'Enforce event payment/retention lifecycle, lock status, and timed deletions.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $checked = 0;
        $updated = 0;
        $deleted = 0;
        $deletedAssets = 0;

        Event::query()
            ->orderBy('id')
            ->chunkById(100, function ($events) use (
                &$checked,
                &$updated,
                &$deleted,
                &$deletedAssets,
                $dryRun
            ): void {
                foreach ($events as $event) {
                    $checked++;
                    $now = now($event->timezone ?: config('events.default_timezone', 'UTC'));
                    $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;
                    $retentionEnded = $event->retention_ends_at !== null && $now->gt($event->retention_ends_at);
                    $unpaidGraceEnded = ! $event->is_paid && $paymentDueAt !== null && $now->gt($paymentDueAt);

                    if ($retentionEnded || $unpaidGraceEnded) {
                        $deleted++;
                        if (! $dryRun) {
                            $deletedAssets += $this->purgeEventData($event);
                        }
                        continue;
                    }

                    $nextStatus = $this->resolveRuntimeStatus($event, $now, $paymentDueAt);
                    $updates = [];

                    if ($event->status !== $nextStatus) {
                        $updates['status'] = $nextStatus;
                    }

                    if (! $event->is_paid && $event->payment_due_at === null && $event->grace_ends_at !== null) {
                        $updates['payment_due_at'] = $event->grace_ends_at;
                    }

                    if ($updates !== []) {
                        $updated++;
                        if (! $dryRun) {
                            $event->forceFill($updates)->save();
                        }
                    }
                }
            });

        $this->info("Checked {$checked} events.");
        $this->info("Updated {$updated} events.");
        $this->info("Deleted {$deleted} events.");
        if (! $dryRun) {
            $this->info("Deleted {$deletedAssets} assets from storage.");
        }

        return self::SUCCESS;
    }

    private function resolveRuntimeStatus(Event $event, CarbonInterface $now, ?CarbonInterface $paymentDueAt): string
    {
        if ($event->upload_window_starts_at === null || $event->upload_window_ends_at === null) {
            return Event::STATUS_DRAFT;
        }

        if ($now->lt($event->upload_window_starts_at)) {
            return Event::STATUS_SCHEDULED;
        }

        if ($now->betweenIncluded($event->upload_window_starts_at, $event->upload_window_ends_at)) {
            return Event::STATUS_LIVE;
        }

        if (! $event->is_paid && $paymentDueAt !== null && $now->gt($paymentDueAt)) {
            return Event::STATUS_LOCKED;
        }

        if ($event->is_paid && $event->retention_ends_at !== null && $now->gt($event->retention_ends_at)) {
            return Event::STATUS_EXPIRED;
        }

        return Event::STATUS_GRACE;
    }

    private function purgeEventData(Event $event): int
    {
        $assets = EventAsset::query()
            ->where('event_id', $event->id)
            ->get(['id', 'disk', 'path']);

        foreach ($assets as $asset) {
            Storage::disk($asset->disk)->delete($asset->path);
        }

        $deletedAssets = $assets->count();
        $event->forceDelete();

        return $deletedAssets;
    }
}
