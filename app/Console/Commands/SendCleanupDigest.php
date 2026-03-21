<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyCleanupDigestNotification;
use App\Support\CleanupDigestReport;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SendCleanupDigest extends Command
{
    protected $signature = 'events:send-cleanup-digest
        {--force : Send even when there is no pending cleanup review or recent review activity}';

    protected $description = 'Send the daily cleanup digest to configured super-admin recipients.';

    public function __construct(
        private readonly CleanupDigestReport $cleanupDigestReport,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $report = $this->cleanupDigestReport->build();
        $emails = $this->superAdminEmails();

        if ($emails->isEmpty()) {
            $this->warn('No super admin email recipients are configured.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $report['shouldSend']) {
            $this->info('No cleanup activity to report.');

            return self::SUCCESS;
        }

        $users = User::query()
            ->whereIn('email', $emails->all())
            ->get()
            ->keyBy(fn (User $user): string => mb_strtolower(trim((string) $user->email)));

        $notification = new DailyCleanupDigestNotification($report);

        foreach ($users as $user) {
            $user->notify($notification);
        }

        $onDemandEmails = $emails
            ->reject(fn (string $email): bool => $users->has($email))
            ->values();

        foreach ($onDemandEmails as $email) {
            Notification::route('mail', $email)->notify(new DailyCleanupDigestNotification($report));
        }

        $this->info('Cleanup digest sent.');
        $this->line("Tracked events: {$report['trackedCount']}");
        $this->line("Needs review: {$report['counts']['review']}");
        $this->line("Recent review actions: {$report['recentReviewActionCount']}");
        $this->line('Recipients: '.($users->count() + $onDemandEmails->count()));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function superAdminEmails(): Collection
    {
        /** @var array<int, string> $emails */
        $emails = config('app.super_admin_emails', []);

        return collect($emails)
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->filter()
            ->values();
    }
}
