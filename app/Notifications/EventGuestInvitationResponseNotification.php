<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class EventGuestInvitationResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{
     *   eventName: string,
     *   guestPartyName: string,
     *   attendanceStatus: string,
     *   changeType: string,
     *   confirmedAttendeesCount: int|null,
     *   pendingPartyCount: int,
     *   acceptedPartyCount: int,
     *   declinedPartyCount: int,
     *   confirmedAttendeeTotal: int,
     *   mealPreference: string|null,
     *   guestNames: string|null,
     *   responseNotes: string|null,
     *   guestListUrl: string
     * }  $summary
     */
    public function __construct(
        private readonly array $summary,
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
        $subject = match ($this->summary['changeType']) {
            'accepted' => "{$this->summary['guestPartyName']} accepted your invitation",
            'declined' => "{$this->summary['guestPartyName']} declined your invitation",
            default => "{$this->summary['guestPartyName']} updated their RSVP",
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.event-guest-rsvp', [
                'subject' => $subject,
                'headerTitle' => config('app.name', 'EventSmart'),
                'headerSubtitle' => 'Guest RSVP update',
                'footerText' => 'You are receiving this update because you own the event and guest invitations are active.',
                'summary' => $this->summary,
                'headline' => $this->headline(),
                'attendanceLine' => $this->attendanceLine(),
                'pendingLine' => $this->pendingLine(),
                'totalsLine' => $this->totalsLine(),
                'mealPreferenceLabel' => $this->mealPreferenceLabel(),
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->summary;
    }

    private function headline(): string
    {
        return match ($this->summary['changeType']) {
            'accepted' => "Yes! {$this->summary['guestPartyName']} just accepted your invitation.",
            'declined' => "{$this->summary['guestPartyName']} just let you know they cannot make it.",
            default => "{$this->summary['guestPartyName']} just updated their RSVP details.",
        };
    }

    private function attendanceLine(): string
    {
        if ($this->summary['attendanceStatus'] !== 'accepted') {
            return 'Their reply has been saved to your guest list and ledger.';
        }

        $confirmedAttendeesCount = max(1, (int) ($this->summary['confirmedAttendeesCount'] ?? 0));
        $attendeeLabel = Str::plural('attendee', $confirmedAttendeesCount);

        return "They confirmed {$confirmedAttendeesCount} {$attendeeLabel} for this event.";
    }

    private function pendingLine(): string
    {
        if ($this->summary['pendingPartyCount'] === 0) {
            return 'Everyone on your guest list has answered now.';
        }

        $pendingLabel = Str::plural('invitation', $this->summary['pendingPartyCount']);

        return "Now {$this->summary['pendingPartyCount']} {$pendingLabel} are still waiting for an answer.";
    }

    private function totalsLine(): string
    {
        $confirmedAttendeeLabel = Str::plural('confirmed attendee', $this->summary['confirmedAttendeeTotal']);

        return "{$this->summary['acceptedPartyCount']} accepted, {$this->summary['declinedPartyCount']} declined, {$this->summary['pendingPartyCount']} pending, {$this->summary['confirmedAttendeeTotal']} {$confirmedAttendeeLabel} total.";
    }

    private function mealPreferenceLabel(): ?string
    {
        return match ($this->summary['mealPreference']) {
            'vegetarian' => 'Vegetarian',
            'vegan' => 'Vegan',
            'halal' => 'Halal',
            'other' => 'Other',
            'standard' => 'Standard',
            default => null,
        };
    }
}
