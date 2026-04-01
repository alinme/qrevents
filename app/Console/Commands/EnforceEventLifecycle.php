<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Support\EventDataPurger;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;

class EnforceEventLifecycle extends Command
{
    protected $signature = 'events:enforce-lifecycle {--dry-run : Preview actions without writing changes}';

    protected $description = 'Enforce event payment/retention lifecycle, lock status, and timed deletions.';

    public function handle(EventDataPurger $eventDataPurger): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $checked = 0;
        $updated = 0;
        $deleted = 0;
        $deletedAssets = 0;
        $deletedFiles = 0;
        $reclaimableStorageBytes = 0;

        Event::query()
            ->orderBy('id')
            ->chunkById(100, function ($events) use (
                &$checked,
                &$updated,
                &$deleted,
                &$deletedAssets,
                &$deletedFiles,
                &$reclaimableStorageBytes,
                $eventDataPurger,
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
                        $inspection = $eventDataPurger->inspectEventForDeletion($event);
                        $reclaimableStorageBytes += $inspection['reclaimableStorageBytes'];
                        $deletedAssets += $inspection['assetCount'];
                        $deletedFiles += $inspection['storedFileCount'];

                        if (! $dryRun) {
                            $result = $eventDataPurger->purgeEventForDeletion($event);
                            $deletedAssets = $deletedAssets - $inspection['assetCount'] + $result['deletedAssetCount'];
                            $deletedFiles = $deletedFiles - $inspection['storedFileCount'] + $result['deletedFileCount'];
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
        $this->info("Affected {$deletedAssets} assets.");
        $this->info("Affected {$deletedFiles} stored files.");
        $this->info("Reclaimable storage: {$this->humanBytes($reclaimableStorageBytes)}.");
        if ($dryRun) {
            $this->info('Dry run complete. No events were deleted.');
        } else {
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
