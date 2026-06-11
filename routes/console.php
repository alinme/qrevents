<?php

use App\Console\Commands\EnforceEventLifecycle;
use App\Console\Commands\PruneTemporaryEventFiles;
use App\Console\Commands\SendCleanupDigest;
use App\Console\Commands\SyncExchangeRates;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(EnforceEventLifecycle::class)->hourly()->withoutOverlapping();
Schedule::command(PruneTemporaryEventFiles::class)->hourly()->withoutOverlapping();
Schedule::command(SyncExchangeRates::class)->hourly()->withoutOverlapping();
Schedule::command(SendCleanupDigest::class)
    ->dailyAt(sprintf(
        '%02d:%02d',
        (int) config('events.cleanup_digest.hour', 8),
        (int) config('events.cleanup_digest.minute', 5),
    ))
    ->timezone(config('events.default_timezone', 'UTC'))
    ->withoutOverlapping();
