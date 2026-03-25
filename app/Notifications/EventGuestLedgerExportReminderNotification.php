<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventGuestLedgerExportReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{
     *   eventName: string,
     *   retentionEndsAt: string,
     *   daysRemaining: int,
     *   partyCount: int,
     *   invitedAttendeesCount: int,
     *   confirmedAttendeesCount: int,
     *   pendingPartyCount: int,
     *   guestReportUrl: string,
     *   guestLedgerExportUrl: string
     * }  $summary
     */
    public function __construct(
        private readonly array $summary,
        private readonly int $daysBeforeExpiry,
        private readonly string $reminderKey,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->daysBeforeExpiry) {
            1 => "Final reminder: export {$this->summary['eventName']} data today",
            7 => "One week left to export {$this->summary['eventName']} data",
            30 => "A month left to export {$this->summary['eventName']} data",
            default => "Export reminder for {$this->summary['eventName']}",
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.event-guest-ledger-export-reminder', [
                'subject' => $subject,
                'headerTitle' => config('app.name', 'EventSmart'),
                'headerSubtitle' => 'Guest ledger export reminder',
                'footerText' => 'You are receiving this reminder because the event ledger is still available and should be exported before retention ends.',
                'summary' => $this->summary,
                'daysBeforeExpiry' => $this->daysBeforeExpiry,
                'reminderKey' => $this->reminderKey,
                'actionLabel' => $this->daysBeforeExpiry === 1 ? 'Export now' : 'Open report',
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            ...$this->summary,
            'daysBeforeExpiry' => $this->daysBeforeExpiry,
            'reminderKey' => $this->reminderKey,
        ];
    }
}
