<?php

namespace App\Console\Commands;

use App\Support\CleanupDigestReport;
use Illuminate\Console\Command;

class EventCleanupReport extends Command
{
    protected $signature = 'events:cleanup-report
        {--state= : Filter cleanup state (review, approved, protected, completed, cooldown, not_due)}
        {--detailed : Show matching events in a table}';

    protected $description = 'Report cleanup candidates, review state, and reclaimable storage.';

    public function __construct(
        private readonly CleanupDigestReport $cleanupDigestReport,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $stateFilter = trim((string) $this->option('state'));
        $validStates = ['review', 'approved', 'protected', 'completed', 'cooldown', 'not_due'];

        if ($stateFilter !== '' && ! in_array($stateFilter, $validStates, true)) {
            $this->error('Invalid --state filter. Use one of: '.implode(', ', $validStates));

            return self::FAILURE;
        }

        $report = $this->cleanupDigestReport->build();
        $rows = collect($report['rows']);
        $filteredRows = $stateFilter === ''
            ? $rows
            : $rows->filter(fn (array $row): bool => $row['cleanup_state_code'] === $stateFilter)->values();
        $counts = $report['counts'];

        $this->info('Cleanup report');
        $this->line('Tracked events: '.$report['trackedCount']);
        $this->line('Needs review: '.$counts['review']);
        $this->line('Approved: '.$counts['approved']);
        $this->line('Protected: '.$counts['protected']);
        $this->line('Completed: '.$counts['completed']);
        $this->line('Cooldown: '.$counts['cooldown']);
        $this->line('Not due: '.$counts['not_due']);
        $this->line('Reclaimable storage: '.$this->humanBytes((int) $report['reclaimableBytes']));

        if ($stateFilter !== '') {
            $this->line("Filter: {$stateFilter}");
        }

        if ($this->option('detailed') && $filteredRows->isNotEmpty()) {
            $this->newLine();
            $this->table(
                ['ID', 'Event', 'Owner', 'Queue', 'Cleanup', 'Assets', 'Storage', 'Candidate After'],
                $filteredRows->map(fn (array $row): array => [
                    $row['id'],
                    $row['name'],
                    $row['owner_email'],
                    $row['queue_label'],
                    $row['cleanup_state_label'],
                    $row['asset_count'],
                    $row['storage_used_label'],
                    $row['candidate_at'] ?? 'n/a',
                ])->all(),
            );
        } elseif ($this->option('detailed')) {
            $this->warn('No events matched the current filter.');
        }

        return self::SUCCESS;
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf(
            '%s %s',
            $value >= 10 || $power === 0 ? number_format($value, 0) : number_format($value, 1),
            $units[$power],
        );
    }
}
