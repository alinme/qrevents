<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventGuestLedgerReminderLog;
use App\Notifications\EventGuestLedgerExportReminderNotification;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendGuestLedgerExportReminders extends Command
{
    protected $signature = 'events:send-guest-ledger-export-reminders {--force : Send reminders even if a reminder for the current retention snapshot already exists}';

    protected $description = 'Send reminder emails before guest ledger retention expires.';

    public function handle(): int
    {
        $thresholds = $this->reminderThresholds();
        $checked = 0;
        $sent = 0;

        Event::query()
            ->where('is_paid', true)
            ->whereNotNull('retention_ends_at')
            ->orderBy('id')
            ->chunkById(100, function ($events) use (&$checked, &$sent, $thresholds): void {
                foreach ($events as $event) {
                    $checked++;
                    $sent += $this->sendEventReminders($event, $thresholds);
                }
            });

        $this->info("Checked {$checked} events.");
        $this->info("Sent {$sent} reminder emails.");

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function reminderThresholds(): array
    {
        /** @var array<int, int> $thresholds */
        $thresholds = config('events.guest_ledger_export_reminder.days_before_expiry', [30, 7, 1]);

        return collect($thresholds)
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int>  $thresholds
     */
    private function sendEventReminders(Event $event, array $thresholds): int
    {
        $event->loadMissing('user:id,name,email', 'guestParties');

        $user = $event->user;
        $retentionEndsAt = $event->retention_ends_at?->toImmutable();
        if ($user === null || $retentionEndsAt === null) {
            return 0;
        }

        $now = now($event->timezone ?: config('events.default_timezone', 'UTC'))->toImmutable();
        $sentCount = 0;
        $summary = $this->buildSummary($event, $now, $retentionEndsAt);

        foreach ($thresholds as $daysBeforeExpiry) {
            if (! $this->isWithinReminderWindow($now, $retentionEndsAt, $daysBeforeExpiry)) {
                continue;
            }

            $reminderKey = $this->reminderKey($daysBeforeExpiry);

            if (! $this->option('force') && $this->reminderAlreadySent($event, $reminderKey, $retentionEndsAt)) {
                continue;
            }

            $user->notify(new EventGuestLedgerExportReminderNotification(
                summary: $summary,
                daysBeforeExpiry: $daysBeforeExpiry,
                reminderKey: $reminderKey,
            ));

            EventGuestLedgerReminderLog::query()->create([
                'event_id' => $event->id,
                'reminder_key' => $reminderKey,
                'retention_ends_at' => $retentionEndsAt->toDateTimeString(),
                'sent_at' => $now->toDateTimeString(),
            ]);

            $sentCount++;
        }

        return $sentCount;
    }

    private function reminderAlreadySent(Event $event, string $reminderKey, CarbonImmutable $retentionEndsAt): bool
    {
        return EventGuestLedgerReminderLog::query()
            ->where('event_id', $event->id)
            ->where('reminder_key', $reminderKey)
            ->where('retention_ends_at', $retentionEndsAt->toDateTimeString())
            ->exists();
    }

    private function isWithinReminderWindow(CarbonImmutable $now, CarbonImmutable $retentionEndsAt, int $daysBeforeExpiry): bool
    {
        $windowStart = $retentionEndsAt->subDays($daysBeforeExpiry)->startOfDay();
        $windowEnd = $daysBeforeExpiry > 1
            ? $retentionEndsAt->subDays($daysBeforeExpiry - 1)->endOfDay()
            : $retentionEndsAt->endOfDay();

        return $now->betweenIncluded($windowStart, $windowEnd);
    }

    private function reminderKey(int $daysBeforeExpiry): string
    {
        return match ($daysBeforeExpiry) {
            1 => '1_day',
            7 => '7_days',
            30 => '30_days',
            default => $daysBeforeExpiry.'_days',
        };
    }

    /**
     * @return array{
     *   eventName: string,
     *   retentionEndsAt: string,
     *   daysRemaining: int,
     *   partyCount: int,
     *   invitedAttendeesCount: int,
     *   confirmedAttendeesCount: int,
     *   pendingPartyCount: int,
     *   guestReportUrl: string,
     *   guestLedgerExportUrl: string
     * }
     */
    private function buildSummary(Event $event, CarbonImmutable $now, CarbonImmutable $retentionEndsAt): array
    {
        $guestParties = $event->guestParties;
        $guestReportUrl = route('events.guests.report', $event);

        return [
            'eventName' => $event->name,
            'retentionEndsAt' => $retentionEndsAt->toDayDateTimeString(),
            'daysRemaining' => max(0, $now->startOfDay()->diffInDays($retentionEndsAt->startOfDay(), false)),
            'partyCount' => $guestParties->count(),
            'invitedAttendeesCount' => $guestParties->sum('invited_attendees_count'),
            'confirmedAttendeesCount' => $guestParties->sum(fn ($party): int => (int) ($party->confirmed_attendees_count ?? 0)),
            'pendingPartyCount' => $guestParties->where('attendance_status', 'pending')->count(),
            'guestReportUrl' => $guestReportUrl,
            'guestLedgerExportUrl' => route('events.guests.export', $event),
        ];
    }
}
