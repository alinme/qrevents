<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

test('temp prune command reports stale event export directories and owned system temp files in dry run mode', function () {
    $exportDirectory = storage_path('app/temp/event-exports/'.Str::uuid()->toString());
    File::ensureDirectoryExists($exportDirectory);
    File::put($exportDirectory.'/archive-part.txt', 'fixture');
    touch($exportDirectory, now()->subHours(24)->timestamp);
    touch($exportDirectory.'/archive-part.txt', now()->subHours(24)->timestamp);

    $systemTempFile = tempnam(sys_get_temp_dir(), 'qr-watermark-');
    File::put($systemTempFile, 'fixture');
    touch($systemTempFile, now()->subHours(24)->timestamp);

    $this->artisan('events:prune-temp-files --dry-run --hours=12')
        ->expectsOutput('Prune cutoff: files older than 12 hour(s).')
        ->expectsOutput('Dry run complete. No files were deleted.')
        ->assertExitCode(0);

    expect(File::isDirectory($exportDirectory))->toBeTrue()
        ->and(File::exists($systemTempFile))->toBeTrue();

    File::delete($systemTempFile);
    File::deleteDirectory($exportDirectory);
});

test('temp prune command deletes stale event export directories and keeps fresh work', function () {
    CarbonImmutable::setTestNow('2026-04-01 12:00:00');

    $staleDirectory = storage_path('app/temp/event-exports/'.Str::uuid()->toString());
    $freshDirectory = storage_path('app/temp/event-exports/'.Str::uuid()->toString());
    File::ensureDirectoryExists($staleDirectory);
    File::ensureDirectoryExists($freshDirectory);
    File::put($staleDirectory.'/archive-part.txt', 'fixture');
    File::put($freshDirectory.'/archive-part.txt', 'fixture');
    touch($staleDirectory, now()->subHours(30)->timestamp);
    touch($staleDirectory.'/archive-part.txt', now()->subHours(30)->timestamp);
    touch($freshDirectory, now()->subHours(1)->timestamp);
    touch($freshDirectory.'/archive-part.txt', now()->subHours(1)->timestamp);

    $staleTempFile = tempnam(sys_get_temp_dir(), 'qr-video-input-');
    $freshTempFile = tempnam(sys_get_temp_dir(), 'qr-video-input-');
    File::put($staleTempFile, 'fixture');
    File::put($freshTempFile, 'fixture');
    touch($staleTempFile, now()->subHours(30)->timestamp);
    touch($freshTempFile, now()->subHours(1)->timestamp);

    $this->artisan('events:prune-temp-files --hours=12')
        ->expectsOutput('Temporary file pruning complete.')
        ->assertExitCode(0);

    expect(File::isDirectory($staleDirectory))->toBeFalse()
        ->and(File::exists($staleTempFile))->toBeFalse()
        ->and(File::isDirectory($freshDirectory))->toBeTrue()
        ->and(File::exists($freshTempFile))->toBeTrue();

    File::delete($freshTempFile);
    File::deleteDirectory($freshDirectory);

    CarbonImmutable::setTestNow();
});
