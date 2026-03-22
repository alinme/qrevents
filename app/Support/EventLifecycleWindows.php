<?php

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonImmutable;

class EventLifecycleWindows
{
    /**
     * @return array{
     *   upload_window_starts_at: CarbonImmutable|null,
     *   upload_window_ends_at: CarbonImmutable|null,
     *   grace_ends_at: CarbonImmutable|null,
     *   hard_lock_at: CarbonImmutable|null,
     *   status: string
     * }
     */
    public static function build(?string $eventDate, string $timezone, int $uploadWindowDays, int $graceDays): array
    {
        if ($eventDate === null) {
            return [
                'upload_window_starts_at' => null,
                'upload_window_ends_at' => null,
                'grace_ends_at' => null,
                'hard_lock_at' => null,
                'status' => Event::STATUS_DRAFT,
            ];
        }

        $activeDays = max(1, $uploadWindowDays);
        $graceWindowDays = max(0, $graceDays);
        $eventDay = CarbonImmutable::parse($eventDate, $timezone)->startOfDay();
        $uploadWindowStartsAt = $eventDay;
        $uploadWindowEndsAt = $eventDay->addDays($activeDays - 1)->endOfDay();
        $graceEndsAt = $uploadWindowEndsAt->addDays($graceWindowDays)->endOfDay();
        $now = now($timezone)->toImmutable();

        $status = Event::STATUS_SCHEDULED;
        if ($now->betweenIncluded($uploadWindowStartsAt, $uploadWindowEndsAt)) {
            $status = Event::STATUS_LIVE;
        } elseif ($now->gt($uploadWindowEndsAt) && $now->lte($graceEndsAt)) {
            $status = Event::STATUS_GRACE;
        } elseif ($now->gt($graceEndsAt)) {
            $status = Event::STATUS_LOCKED;
        }

        return [
            'upload_window_starts_at' => $uploadWindowStartsAt,
            'upload_window_ends_at' => $uploadWindowEndsAt,
            'grace_ends_at' => $graceEndsAt,
            'hard_lock_at' => $graceEndsAt,
            'status' => $status,
        ];
    }
}
