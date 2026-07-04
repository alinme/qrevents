<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExtendEventRequest;
use App\Models\AdminAuditLog;
use App\Models\Event;
use App\Support\EventDataPurger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventModerationController extends Controller
{
    public function suspend(Request $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);

        $previousStatus = (string) $event->status;
        $event->forceFill(['status' => Event::STATUS_LOCKED])->save();

        AdminAuditLog::record(
            $request->user(),
            'event.suspended',
            $event,
            $event->name,
            ['from_status' => $previousStatus],
            $request->ip(),
        );

        return back()->with('success', "\"{$event->name}\" is suspended (locked).");
    }

    public function extend(ExtendEventRequest $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);

        $days = (int) $request->validated()['days'];

        $updates = [];
        foreach (['retention_ends_at', 'grace_ends_at', 'payment_due_at'] as $field) {
            if ($event->{$field} !== null) {
                $updates[$field] = $event->{$field}->copy()->addDays($days);
            }
        }

        if ($updates === []) {
            $updates['grace_ends_at'] = now()->addDays($days);
        }

        // Give access back immediately if the event was locked/expired.
        if (in_array($event->status, [Event::STATUS_LOCKED, Event::STATUS_EXPIRED], true)) {
            $updates['status'] = Event::STATUS_GRACE;
        }

        $event->forceFill($updates)->save();

        AdminAuditLog::record(
            $request->user(),
            'event.extended',
            $event,
            $event->name,
            ['days' => $days],
            $request->ip(),
        );

        return back()->with('success', "\"{$event->name}\" extended by {$days} day(s).");
    }

    public function destroy(Request $request, Event $event, EventDataPurger $purger): RedirectResponse
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);

        $name = $event->name;
        $result = $purger->purgeEventForDeletion($event);

        AdminAuditLog::record(
            $request->user(),
            'event.deleted',
            null,
            $name,
            [
                'deleted_event_id' => $event->id,
                'deleted_assets' => $result['deletedAssetCount'],
                'reclaimed_bytes' => $result['reclaimedStorageBytes'],
            ],
            $request->ip(),
        );

        return redirect()->route('admin.events')->with('success', "\"{$name}\" and its media were deleted.");
    }
}
