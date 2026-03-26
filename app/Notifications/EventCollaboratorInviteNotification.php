<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCollaboratorInviteNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $eventName,
        private readonly string $inviterName,
        private readonly string $acceptUrl,
        private readonly CarbonInterface $expiresAt,
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
        $subject = "You're invited to collaborate on {$this->eventName}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.collaborator-invite', [
                'subject' => $subject,
                'headerTitle' => config('app.name', 'EventSmart'),
                'headerSubtitle' => 'Event collaborator invitation',
                'footerText' => 'If you were not expecting this invitation, you can ignore this email.',
                'eventName' => $this->eventName,
                'inviterName' => $this->inviterName,
                'acceptUrl' => $this->acceptUrl,
                'expiresAt' => $this->expiresAt->toDayDateTimeString(),
            ]);
    }
}
