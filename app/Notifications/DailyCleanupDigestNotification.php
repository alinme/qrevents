<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyCleanupDigestNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{
     *   generatedAt: string,
     *   since: string,
     *   trackedCount: int,
     *   reclaimableBytes: int,
     *   counts: array{
     *     review: int,
     *     approved: int,
     *     protected: int,
     *     completed: int,
     *     cooldown: int,
     *     not_due: int
     *   },
     *   newCandidateCount: int,
     *   recentReviewActionCount: int,
     *   newCandidateEvents: list<array<string, mixed>>,
     *   pendingReviewEvents: list<array<string, mixed>>,
     *   recentReviewEvents: list<array<string, mixed>>
     * }  $report
     */
    public function __construct(
        private readonly array $report,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $counts = $this->report['counts'];

        $mail = (new MailMessage)
            ->subject("Daily cleanup digest: {$counts['review']} awaiting review")
            ->greeting('Daily cleanup digest')
            ->line("Tracked events: {$this->report['trackedCount']}")
            ->line("Needs review: {$counts['review']} | Approved: {$counts['approved']} | Protected: {$counts['protected']} | Completed: {$counts['completed']}")
            ->line("New cleanup candidates in the last 24 hours: {$this->report['newCandidateCount']}")
            ->line("Recent review actions in the last 24 hours: {$this->report['recentReviewActionCount']}")
            ->line("Reclaimable storage still at risk: {$this->humanBytes((int) $this->report['reclaimableBytes'])}");

        if ($this->report['pendingReviewEvents'] !== []) {
            $mail->line('Oldest events still waiting for cleanup review:');

            foreach ($this->report['pendingReviewEvents'] as $event) {
                $mail->line("- {$event['name']} ({$event['owner_email']}) | {$event['storage_used_label']} | due {$event['candidate_at_label']}");
            }
        }

        if ($this->report['recentReviewEvents'] !== []) {
            $mail->line('Recent review actions:');

            foreach ($this->report['recentReviewEvents'] as $event) {
                $reviewedAt = $event['reviewed_at_label'] ?? 'recently';
                $mail->line("- {$event['name']} marked {$event['cleanup_state_label']} at {$reviewedAt}");
            }
        }

        return $mail
            ->action('Open cleanup queue', route('admin.cleanup'))
            ->line('You are receiving this because your email is configured as a super admin.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'trackedCount' => $this->report['trackedCount'],
            'newCandidateCount' => $this->report['newCandidateCount'],
            'recentReviewActionCount' => $this->report['recentReviewActionCount'],
            'counts' => $this->report['counts'],
        ];
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
