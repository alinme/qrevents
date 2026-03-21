<?php

namespace App\Support;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CleanupDigestReport
{
    /**
     * @return array{
     *   generatedAt: string,
     *   since: string,
     *   trackedCount: int,
     *   shouldSend: bool,
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
     *   recentReviewEvents: list<array<string, mixed>>,
     *   rows: list<array<string, mixed>>
     * }
     */
    public function build(?CarbonInterface $referenceTime = null): array
    {
        $generatedAt = $referenceTime instanceof CarbonImmutable
            ? $referenceTime
            : CarbonImmutable::instance($referenceTime ?? now());
        $since = $generatedAt->subDay();

        /** @var Collection<int, array<string, mixed>> $rows */
        $rows = Event::query()
            ->with('user:id,name,email')
            ->withCount('assets')
            ->where(function ($query): void {
                $query
                    ->whereIn('status', [Event::STATUS_LOCKED, Event::STATUS_EXPIRED])
                    ->orWhereNotNull('cleanup_review_state');
            })
            ->latest('cleanup_reviewed_at')
            ->latest('id')
            ->get()
            ->map(fn (Event $event): array => $this->cleanupRow($event, $generatedAt))
            ->values();

        $countedStates = $rows->countBy('cleanup_state_code');

        $counts = [
            'review' => (int) ($countedStates['review'] ?? 0),
            'approved' => (int) ($countedStates['approved'] ?? 0),
            'protected' => (int) ($countedStates['protected'] ?? 0),
            'completed' => (int) ($countedStates['completed'] ?? 0),
            'cooldown' => (int) ($countedStates['cooldown'] ?? 0),
            'not_due' => (int) ($countedStates['not_due'] ?? 0),
        ];

        /** @var Collection<int, array<string, mixed>> $pendingReviewEvents */
        $pendingReviewEvents = $rows
            ->filter(fn (array $row): bool => $row['cleanup_state_code'] === 'review')
            ->sortBy(fn (array $row): int => (int) ($row['candidate_at_timestamp'] ?? PHP_INT_MAX))
            ->values();

        /** @var Collection<int, array<string, mixed>> $newCandidateEvents */
        $newCandidateEvents = $pendingReviewEvents
            ->filter(function (array $row) use ($since, $generatedAt): bool {
                $candidateTimestamp = $row['candidate_at_timestamp'] ?? null;

                if (! is_int($candidateTimestamp)) {
                    return false;
                }

                return $candidateTimestamp >= $since->getTimestamp()
                    && $candidateTimestamp <= $generatedAt->getTimestamp();
            })
            ->values();

        /** @var Collection<int, array<string, mixed>> $recentReviewEvents */
        $recentReviewEvents = $rows
            ->filter(function (array $row) use ($since, $generatedAt): bool {
                if (! in_array($row['cleanup_state_code'], ['approved', 'protected', 'completed'], true)) {
                    return false;
                }

                $reviewedTimestamp = $row['reviewed_at_timestamp'] ?? null;

                if (! is_int($reviewedTimestamp)) {
                    return false;
                }

                return $reviewedTimestamp >= $since->getTimestamp()
                    && $reviewedTimestamp <= $generatedAt->getTimestamp();
            })
            ->values();

        $reclaimableBytes = (int) $rows
            ->filter(fn (array $row): bool => in_array($row['cleanup_state_code'], ['review', 'approved', 'protected'], true))
            ->sum('storage_used_bytes');

        return [
            'generatedAt' => $generatedAt->toIso8601String(),
            'since' => $since->toIso8601String(),
            'trackedCount' => $rows->count(),
            'shouldSend' => $counts['review'] > 0 || $recentReviewEvents->isNotEmpty(),
            'reclaimableBytes' => $reclaimableBytes,
            'counts' => $counts,
            'newCandidateCount' => $newCandidateEvents->count(),
            'recentReviewActionCount' => $recentReviewEvents->count(),
            'newCandidateEvents' => $newCandidateEvents->take(5)->values()->all(),
            'pendingReviewEvents' => $pendingReviewEvents->take(5)->values()->all(),
            'recentReviewEvents' => $recentReviewEvents->take(5)->values()->all(),
            'rows' => $rows->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cleanupRow(Event $event, CarbonImmutable $referenceTime): array
    {
        $storageUsedBytes = max(0, (int) $event->storage_used_bytes);
        $hasExportArchive = is_string($event->media_export_disk)
            && $event->media_export_disk !== ''
            && is_string($event->media_export_path)
            && $event->media_export_path !== '';
        [$queueLabel] = $this->queueMeta($event);
        [$cleanupStateCode, $cleanupStateLabel, $candidateAt] = $this->cleanupState($event, $referenceTime, $storageUsedBytes, $hasExportArchive);
        $reviewedAt = $event->cleanup_reviewed_at?->toImmutable();

        return [
            'id' => $event->id,
            'name' => $event->name,
            'owner_name' => $event->user?->name ?? 'Unknown owner',
            'owner_email' => $event->user?->email ?? 'unknown',
            'queue_label' => $queueLabel,
            'cleanup_state_code' => $cleanupStateCode,
            'cleanup_state_label' => $cleanupStateLabel,
            'asset_count' => (int) ($event->assets_count ?? 0),
            'storage_used_bytes' => $storageUsedBytes,
            'storage_used_label' => $this->humanBytes($storageUsedBytes),
            'candidate_at' => $candidateAt?->toIso8601String(),
            'candidate_at_label' => $candidateAt?->toDayDateTimeString(),
            'candidate_at_timestamp' => $candidateAt?->getTimestamp(),
            'reviewed_at' => $reviewedAt?->toIso8601String(),
            'reviewed_at_label' => $reviewedAt?->toDayDateTimeString(),
            'reviewed_at_timestamp' => $reviewedAt?->getTimestamp(),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function queueMeta(Event $event): array
    {
        if ($event->status === Event::STATUS_LOCKED) {
            return ['Locked', 'rose'];
        }

        if ($event->status === Event::STATUS_EXPIRED) {
            return ['Expired', 'zinc'];
        }

        if ($event->is_paid) {
            return ['Paid', 'emerald'];
        }

        if ($event->payment_due_at?->isPast()) {
            return ['Overdue', 'rose'];
        }

        return ['Awaiting payment', 'amber'];
    }

    /**
     * @return array{0: string, 1: string, 2: CarbonImmutable|null}
     */
    private function cleanupState(Event $event, CarbonImmutable $referenceTime, int $storageUsedBytes, bool $hasExportArchive): array
    {
        $referenceAt = match ($event->status) {
            Event::STATUS_LOCKED => $event->payment_due_at ?? $event->grace_ends_at ?? $event->hard_lock_at,
            Event::STATUS_EXPIRED => $event->retention_ends_at,
            default => null,
        };

        $thresholdDays = $event->status === Event::STATUS_EXPIRED
            ? (int) config('events.cleanup_policy.expired_candidate_after_days', 7)
            : (int) config('events.cleanup_policy.locked_candidate_after_days', 14);
        $candidateAt = $referenceAt?->toImmutable()->addDays(max(0, $thresholdDays));
        $needsCleanup = $storageUsedBytes > 0 || $hasExportArchive;
        $isEligibleEvent = in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true);
        $comparisonTime = $referenceTime->setTimezone($event->timezone ?: config('events.default_timezone', 'UTC'));
        $isDue = $candidateAt !== null && $comparisonTime->gte($candidateAt);

        if ($event->cleanup_review_state === 'completed') {
            return ['completed', 'Completed', $candidateAt];
        }

        if ($event->cleanup_review_state === 'protected') {
            return ['protected', 'Protected', $candidateAt];
        }

        if (! $isEligibleEvent || ! $needsCleanup) {
            return ['not_due', 'Not due', $candidateAt];
        }

        if (! $isDue) {
            return ['cooldown', 'Cooldown', $candidateAt];
        }

        if ($event->cleanup_review_state === 'approved') {
            return ['approved', 'Approved', $candidateAt];
        }

        return ['review', 'Needs review', $candidateAt];
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
