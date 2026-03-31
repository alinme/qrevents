<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUpdateEventGuestInvitationsRequest;
use App\Http\Requests\CreateEventCheckoutSessionRequest;
use App\Http\Requests\ImportEventGuestPartiesRequest;
use App\Http\Requests\ResolveAlbumCodeRequest;
use App\Http\Requests\RespondEventGuestInvitationRequest;
use App\Http\Requests\UpdateEventBillingRequest;
use App\Http\Requests\UpdateEventInvitationSettingsRequest;
use App\Http\Requests\UpdatePublicGuestListCheckInRequest;
use App\Http\Requests\UpsertEventGuestPartyRequest;
use App\Http\Requests\UpsertEventTableRequest;
use App\Jobs\GenerateEventAssetImageVariants;
use App\Jobs\GenerateEventAssetVideoThumbnails;
use App\Jobs\GenerateEventMediaExport;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventAssetComment;
use App\Models\EventAssetCommentLike;
use App\Models\EventAssetLike;
use App\Models\EventCollaborator;
use App\Models\EventGuest;
use App\Models\EventGuestParty;
use App\Models\EventGuestPartyInvitationActivity;
use App\Models\EventGuestPartyInvitationView;
use App\Models\EventTable;
use App\Models\Plan;
use App\Models\TextPostTheme;
use App\Models\User;
use App\Notifications\EventCollaboratorInviteNotification;
use App\Notifications\EventGuestInvitationResponseNotification;
use App\Support\EventBillingManager;
use App\Support\EventGuestPartyImportParser;
use App\Support\EventLifecycleWindows;
use App\Support\FrontendLocalization;
use App\Support\IsgdShortUrlManager;
use App\Support\StripeCheckoutGateway;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Imagick;
use ImagickException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    private const PUBLIC_ALBUM_STACKS_PER_PAGE = 18;

    private const PUBLIC_ALBUM_RAW_FETCH_CHUNK = 120;

    private const ADMIN_MEDIA_FULL_SCAN_LIMIT = 5000;

    public function albumAccess(): Response
    {
        return Inertia::render('public/AlbumAccess', [
            'submitUrl' => route('events.album.access.resolve'),
            'homeUrl' => route('home'),
            'segmentCount' => 4,
            'entryShortcutUrl' => 'https://is.gd/evsmrt',
            'defaultTarget' => 'album',
        ]);
    }

    public function resolveAlbumAccess(ResolveAlbumCodeRequest $request): RedirectResponse
    {
        $event = $this->resolvePublicAlbumEvent($request->normalizedCode());

        if ($event === null) {
            throw ValidationException::withMessages([
                'code' => 'We could not find an album for that code. Check the letters and numbers, then try again.',
            ]);
        }

        return redirect()->route($request->target() === 'wall' ? 'events.wall' : 'events.album', $event->publicAlbumCode());
    }

    public function show(Request $request, Event $event): Response
    {
        $this->assertCanViewEvent($request, $event);

        return Inertia::render('events/Home', [
            ...$this->eventProps($request, $event),
            ...$this->dashboardProps($event),
            'showDashboardModal' => $request->session()->pull('show_dashboard_modal', false),
        ]);
    }

    public function media(Request $request, Event $event): Response
    {
        $this->assertCanViewEvent($request, $event);
        $membership = $this->activeCollaboratorMembership($request, $event);
        $requestedAssetId = $request->integer('asset');
        $initialActiveAssetId = $requestedAssetId > 0 && EventAsset::query()
            ->where('event_id', $event->id)
            ->whereKey($requestedAssetId)
            ->exists()
            ? $requestedAssetId
            : null;

        return Inertia::render('events/Media', [
            ...$this->eventProps($request, $event),
            ...$this->mediaProps($event),
            'initialActiveAssetId' => $initialActiveAssetId,
            'canManageMedia' => $request->user()->canAccessAdmin()
                || $request->user()->id === $event->user_id
                || ($membership !== null && $membership->role === 'manager'),
        ]);
    }

    public function guests(Request $request, Event $event): Response
    {
        $this->assertCanManageEvent($request, $event);

        return Inertia::render('events/Guests', [
            ...$this->eventProps($request, $event),
            ...$this->guestPartyProps($event),
        ]);
    }

    public function guestReport(Request $request, Event $event): Response
    {
        $this->assertCanManageEvent($request, $event);

        return Inertia::render('events/GuestReport', [
            ...$this->eventProps($request, $event),
            ...$this->guestPartyProps($event),
        ]);
    }

    public function storeGuestParty(UpsertEventGuestPartyRequest $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        $payload = $request->payload();
        $payload['invitation_delivery_channel'] ??= 'public_link';
        $eventTable = $this->resolveEventTableForGuestParty($event, $payload['event_table_id'] ?? null);
        $this->assertTableHasRoomForGuestParty($eventTable, $payload);

        $event->guestParties()->create([
            ...$payload,
            'event_table_id' => $eventTable?->id,
            'table_name' => $eventTable?->name,
            'invitation_token' => (string) Str::lower((string) Str::uuid()),
        ]);

        return back()->with('success', 'Guest party added.');
    }

    public function updateGuestParty(
        UpsertEventGuestPartyRequest $request,
        Event $event,
        EventGuestParty $guestParty,
    ): RedirectResponse {
        $this->assertCanManageEvent($request, $event);
        abort_unless($guestParty->event_id === $event->id, 404);

        $payload = $request->payload();
        $eventTable = $this->resolveEventTableForGuestParty($event, $payload['event_table_id'] ?? null);
        $this->assertTableHasRoomForGuestParty($eventTable, $payload, $guestParty);

        $guestParty->update([
            ...$payload,
            'event_table_id' => $eventTable?->id,
            'table_name' => $eventTable?->name,
        ]);

        return back()->with('success', 'Guest party updated.');
    }

    public function storeEventTable(UpsertEventTableRequest $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        $event->tables()->create([
            ...$request->payload(),
            'sort_order' => (int) $event->tables()->count() + 1,
        ]);

        return back()->with('success', 'Table added.');
    }

    public function updateEventTable(
        UpsertEventTableRequest $request,
        Event $event,
        EventTable $eventTable,
    ): RedirectResponse {
        $this->assertCanManageEvent($request, $event);
        abort_unless($eventTable->event_id === $event->id, 404);

        $payload = $request->payload();
        if ($this->eventTableOccupiedSeats($eventTable) > $payload['seats_count']) {
            throw ValidationException::withMessages([
                'seats_count' => 'This table already has more people assigned than that.',
            ]);
        }

        $eventTable->update($payload);

        $eventTable->guestParties()->update([
            'table_name' => $eventTable->name,
        ]);

        return back()->with('success', 'Table updated.');
    }

    public function destroyEventTable(Request $request, Event $event, EventTable $eventTable): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);
        abort_unless($eventTable->event_id === $event->id, 404);

        if ($eventTable->guestParties()->exists()) {
            return back()->with('error', 'Move the assigned invitees before deleting this table.');
        }

        $eventTable->delete();

        return back()->with('success', 'Table deleted.');
    }

    public function publicGuestList(Request $request, string $shareToken): Response
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        app()->setLocale(FrontendLocalization::resolveEventLocale(
            $request,
            $this->resolvedEventBranding($event),
        ));

        return Inertia::render('public/GuestList', $this->publicGuestListProps($event));
    }

    public function updatePublicGuestListAttendance(
        UpdatePublicGuestListCheckInRequest $request,
        string $shareToken,
        EventGuestParty $guestParty,
    ): RedirectResponse {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        abort_unless($guestParty->event_id === $event->id, 404);

        $payload = $request->payload();
        if (($payload['actual_attendance_status'] ?? null) === 'present') {
            $confirmedAttendeesCount = max(1, (int) ($payload['confirmed_attendees_count']
                ?? $guestParty->confirmed_attendees_count
                ?? $guestParty->invited_attendees_count
                ?? 1));
            $payload['confirmed_attendees_count'] = $confirmedAttendeesCount;
            $payload['actual_attendees_count'] = $confirmedAttendeesCount;
            $payload['attendance_status'] = 'accepted';
        }

        $eventTable = $this->resolveEventTableForGuestParty($event, $payload['event_table_id'] ?? null);
        $this->assertTableHasRoomForGuestParty($eventTable, [
            'invited_attendees_count' => $guestParty->invited_attendees_count,
            'confirmed_attendees_count' => $payload['confirmed_attendees_count'] ?? $guestParty->confirmed_attendees_count,
            'actual_attendance_status' => $payload['actual_attendance_status'] ?? $guestParty->actual_attendance_status,
            'actual_attendees_count' => $payload['actual_attendees_count'] ?? $guestParty->actual_attendees_count,
        ], $guestParty);

        $guestParty->update([
            ...$payload,
            'event_table_id' => $eventTable?->id,
            'table_name' => $eventTable?->name,
        ]);

        return back()->with('success', 'Guest list updated.');
    }

    public function destroyGuestParty(Request $request, Event $event, EventGuestParty $guestParty): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);
        abort_unless($guestParty->event_id === $event->id, 404);

        $guestParty->delete();

        return back()->with('success', 'Guest party removed.');
    }

    public function importGuestParties(
        ImportEventGuestPartiesRequest $request,
        Event $event,
        EventGuestPartyImportParser $parser,
    ): RedirectResponse {
        $this->assertCanManageEvent($request, $event);

        $rows = $parser->parse($request->importContents());
        if ($rows === []) {
            return back()->with('error', 'No guests could be parsed from that import.');
        }

        $existingKeys = $event->guestParties()
            ->get(['name', 'phone'])
            ->map(fn (EventGuestParty $party): string => $this->guestPartyDuplicateKey($party->name, $party->phone))
            ->all();

        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $duplicateKey = $this->guestPartyDuplicateKey($row['name'], $row['phone']);
            if (in_array($duplicateKey, $existingKeys, true)) {
                $skipped++;

                continue;
            }

            $event->guestParties()->create([
                ...$row,
                'attendance_status' => 'pending',
                'invitation_status' => 'draft',
                'invitation_delivery_channel' => 'public_link',
                'invitation_token' => (string) Str::lower((string) Str::uuid()),
            ]);

            $existingKeys[] = $duplicateKey;
            $created++;
        }

        $message = "{$created} guest ".($created === 1 ? 'party' : 'parties').' imported.';
        if ($skipped > 0) {
            $message .= " {$skipped} duplicate ".($skipped === 1 ? 'entry was' : 'entries were').' skipped.';
        }

        return back()->with($created > 0 ? 'success' : 'info', $message);
    }

    public function exportGuestLedger(Request $request, Event $event): StreamedResponse
    {
        $this->assertCanManageEvent($request, $event);

        $filename = Str::of($event->name)
            ->slug('-')
            ->prepend('guest-ledger-')
            ->append('-'.now()->format('Y-m-d').'.csv')
            ->value();

        $guestParties = $event->guestParties()
            ->with('invitationActivities')
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($guestParties): void {
            $handle = fopen('php://output', 'wb');

            if (! is_resource($handle)) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Family / Name',
                'Phone',
                'Invited attendees',
                'Confirmed attendees',
                'Actual attendance status',
                'Actual attendees',
                'Actual attendance recorded at',
                'Attendance status',
                'Invitation status',
                'Invitation delivery channel',
                'Invitation delivered at',
                'Invitation opens',
                'First opened at',
                'Last opened at',
                'Responded at',
                'Guest names',
                'Meal preference',
                'Notes',
                'Response notes',
                'Gift type',
                'Gift currency',
                'Gift amount',
            ]);

            foreach ($guestParties as $guestParty) {
                fputcsv($handle, [
                    $guestParty->name,
                    $guestParty->phone,
                    $guestParty->invited_attendees_count,
                    $guestParty->confirmed_attendees_count,
                    $guestParty->actual_attendance_status,
                    $guestParty->actual_attendees_count,
                    $guestParty->actual_attendance_recorded_at?->toDateTimeString(),
                    $guestParty->attendance_status,
                    $guestParty->invitation_status,
                    $guestParty->invitation_delivery_channel,
                    $guestParty->invitation_delivered_at?->toDateTimeString(),
                    $guestParty->invitation_open_count,
                    $guestParty->invitation_first_opened_at?->toDateTimeString(),
                    $guestParty->invitation_last_opened_at?->toDateTimeString(),
                    $guestParty->responded_at?->toDateTimeString(),
                    $guestParty->guest_names,
                    $guestParty->meal_preference,
                    $guestParty->notes,
                    $guestParty->response_notes,
                    $guestParty->gift_type,
                    $guestParty->gift_currency,
                    $guestParty->gift_amount,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function bulkUpdateGuestInvitations(
        BulkUpdateEventGuestInvitationsRequest $request,
        Event $event,
    ): RedirectResponse {
        $this->assertCanManageEvent($request, $event);

        $guestParties = $event->guestParties()
            ->whereIn('id', $request->guestPartyIds())
            ->get();

        if ($guestParties->isEmpty()) {
            return back()->with('error', 'No matching guest parties were found for that invitation action.');
        }

        $updatedCount = 0;
        $deliveredAt = now();

        foreach ($guestParties as $guestParty) {
            $status = $this->invitationStatusForDeliveryAction($guestParty, $request->actionName());
            $guestParty->forceFill([
                'invitation_status' => $status,
                'invitation_delivery_channel' => $request->deliveryChannel(),
                'invitation_delivered_at' => $request->actionName() === 'mark_reminded_online'
                    ? ($guestParty->invitation_delivered_at ?? $deliveredAt)
                    : $deliveredAt,
            ])->save();
            $this->recordInvitationActivity(
                $guestParty,
                match ($request->actionName()) {
                    'mark_delivered_in_person' => 'delivered_in_person',
                    'mark_reminded_online' => 'reminded',
                    default => 'sent_online',
                },
                $request->deliveryChannel(),
                [
                    'invitationStatus' => $status,
                ],
                $request,
            );

            $updatedCount++;
        }

        $message = match ($request->actionName()) {
            'mark_delivered_in_person' => "{$updatedCount} invitation ".($updatedCount === 1 ? 'was' : 'were').' marked as delivered in person.',
            'mark_reminded_online' => "{$updatedCount} reminder ".($updatedCount === 1 ? 'was' : 'were').' saved.',
            default => "{$updatedCount} invitation ".($updatedCount === 1 ? 'was' : 'were').' marked as sent online.',
        };

        return back()->with('success', $message);
    }

    public function updateInvitationSettings(
        UpdateEventInvitationSettingsRequest $request,
        Event $event,
    ): RedirectResponse {
        $this->assertCanManageEvent($request, $event);

        $event->forceFill([
            'public_invitation_token' => $this->ensurePublicInvitationToken($event),
            'invitation_settings' => $request->settingsPayload(),
        ])->save();

        return back()->with('success', 'Invitation settings saved.');
    }

    public function guestInvitation(Request $request, string $token): Response
    {
        $guestParty = EventGuestParty::query()
            ->with(['event.user:id,name'])
            ->where('invitation_token', $token)
            ->firstOrFail();
        $event = $guestParty->event;

        $this->recordInvitationOpen($request, $event, $guestParty, 'party');

        return Inertia::render('invitations/EventGuestInvite', $this->invitationPageProps(
            $request,
            $event,
            $guestParty->fresh(),
            false,
        ));
    }

    public function respondToGuestInvitation(
        RespondEventGuestInvitationRequest $request,
        string $token,
    ): RedirectResponse {
        $guestParty = EventGuestParty::query()
            ->with('event')
            ->where('invitation_token', $token)
            ->firstOrFail();
        $previousAttendanceStatus = (string) $guestParty->attendance_status;

        $payload = $request->responsePayload();
        if ($payload['attendance_status'] === 'accepted' && (int) $payload['confirmed_attendees_count'] <= 0) {
            $payload['confirmed_attendees_count'] = $guestParty->invited_attendees_count;
        }
        $payload['confirmed_attendees_count'] = min(
            (int) $guestParty->invited_attendees_count,
            max(0, (int) $payload['confirmed_attendees_count']),
        );

        $guestParty->forceFill($payload)->save();
        $this->recordInvitationActivity(
            $guestParty,
            'responded',
            $guestParty->invitation_delivery_channel,
            [
                'attendanceStatus' => $guestParty->attendance_status,
                'confirmedAttendeesCount' => $guestParty->confirmed_attendees_count,
            ],
            $request,
        );
        $this->notifyEventOwnerAboutInvitationResponse($guestParty->fresh(['event.user']), $previousAttendanceStatus);

        return to_route('events.guests.invitation.show', [
            'token' => $token,
            'submitted' => 1,
            'lang' => $request->query('lang'),
        ])->with('success', 'RSVP received.');
    }

    public function publicInvitation(Request $request, string $token): Response
    {
        $event = Event::query()
            ->with('user:id,name')
            ->where('public_invitation_token', $token)
            ->firstOrFail();

        $settings = $this->eventInvitationSettings($event);
        abort_unless(
            $settings['publicRsvpEnabled'] || $this->canPreviewInvitationAsManager($request, $event),
            404,
        );

        if ($settings['publicRsvpEnabled']) {
            $this->recordInvitationOpen($request, $event, null, 'public');
        }

        return Inertia::render('invitations/EventGuestInvite', $this->invitationPageProps(
            $request,
            $event,
            null,
            true,
        ));
    }

    public function respondToPublicInvitation(
        RespondEventGuestInvitationRequest $request,
        string $token,
    ): RedirectResponse {
        $event = Event::query()
            ->where('public_invitation_token', $token)
            ->firstOrFail();

        abort_unless($this->eventInvitationSettings($event)['publicRsvpEnabled'], 404);

        $partyData = $request->publicPartyPayload();
        $guestParty = $this->matchPublicInvitationGuestParty($event, $partyData['name'], $partyData['phone']);

        if ($guestParty === null) {
            $guestParty = $event->guestParties()->create([
                ...$partyData,
                'invitation_status' => 'draft',
                'invitation_delivery_channel' => 'public_link',
                'invitation_token' => (string) Str::lower((string) Str::uuid()),
            ]);
        } else {
            $guestParty->forceFill([
                'name' => $partyData['name'],
                'phone' => $partyData['phone'],
                'invited_attendees_count' => $partyData['invited_attendees_count'],
                'invitation_delivery_channel' => $guestParty->invitation_delivery_channel ?: 'public_link',
            ])->save();
        }
        $previousAttendanceStatus = (string) $guestParty->attendance_status;

        $payload = $request->responsePayload();
        if ($payload['attendance_status'] === 'accepted' && (int) $payload['confirmed_attendees_count'] <= 0) {
            $payload['confirmed_attendees_count'] = $partyData['invited_attendees_count'];
        }
        $payload['confirmed_attendees_count'] = min(
            max(1, (int) $partyData['invited_attendees_count']),
            max(0, (int) $payload['confirmed_attendees_count']),
        );

        $guestParty->forceFill($payload)->save();
        $this->recordInvitationActivity(
            $guestParty,
            'responded',
            $guestParty->invitation_delivery_channel,
            [
                'attendanceStatus' => $guestParty->attendance_status,
                'confirmedAttendeesCount' => $guestParty->confirmed_attendees_count,
                'publicInvite' => true,
            ],
            $request,
        );
        $this->notifyEventOwnerAboutInvitationResponse($guestParty->fresh(['event.user']), $previousAttendanceStatus);

        return to_route('events.guests.public-invitation.show', [
            'token' => $token,
            'submitted' => 1,
            'lang' => $request->query('lang'),
        ])->with('success', 'RSVP received.');
    }

    public function startMediaExport(Request $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        if (! $this->eventCanDownloadAll($event)) {
            return back()->with('error', 'Download all is available on Plus and Pro after payment.');
        }

        $hasApprovedAssets = EventAsset::query()
            ->where('event_id', $event->id)
            ->where('moderation_status', 'approved')
            ->exists();

        if (! $hasApprovedAssets) {
            return back()->with('error', 'No approved media is ready to export yet.');
        }

        $event->refresh();
        if (in_array($event->media_export_status, ['pending', 'processing'], true)) {
            return back()->with('success', 'Album export is already running.');
        }

        $this->invalidateEventMediaExport($event, deleteStoredFile: true);
        $token = Str::uuid()->toString();

        $event->forceFill([
            'media_export_status' => 'pending',
            'media_export_token' => $token,
            'media_export_requested_at' => now(),
            'media_export_started_at' => null,
            'media_export_completed_at' => null,
            'media_export_failed_at' => null,
            'media_export_error' => null,
        ])->save();

        GenerateEventMediaExport::dispatch($event->id, $token);

        return back()->with('success', 'Album export started.');
    }

    public function downloadMediaExport(Request $request, Event $event): StreamedResponse|RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        if (
            $event->media_export_status !== 'ready'
            || ! is_string($event->media_export_disk)
            || $event->media_export_disk === ''
            || ! is_string($event->media_export_path)
            || $event->media_export_path === ''
        ) {
            return back()->with('error', 'Album export is not ready yet.');
        }

        if (
            $this->shouldCheckStoragePathExists($event->media_export_disk)
            && ! Storage::disk($event->media_export_disk)->exists($event->media_export_path)
        ) {
            $this->invalidateEventMediaExport($event, deleteStoredFile: false);

            return back()->with('error', 'The export file is no longer available. Please generate it again.');
        }

        return $this->downloadStorageAsset(
            $event->media_export_disk,
            $event->media_export_path,
            $this->eventExportArchiveFilename($event),
        );
    }

    public function destroyAsset(Request $request, Event $event, EventAsset $asset): JsonResponse|RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);
        abort_unless($asset->event_id === $event->id, 404);

        $this->deleteAssetFromEvent($event, $asset);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
            ]);
        }

        return back()->with('success', 'Media deleted.');
    }

    public function bulkDestroyAssets(Request $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        $validated = $request->validate([
            'asset_ids' => ['required', 'array', 'min:1', 'max:100'],
            'asset_ids.*' => ['required', 'integer'],
        ]);

        $assetIds = collect($validated['asset_ids'])
            ->map(static fn (mixed $value): int => (int) $value)
            ->unique()
            ->values();

        EventAsset::query()
            ->where('event_id', $event->id)
            ->whereIn('id', $assetIds)
            ->get()
            ->each(fn (EventAsset $asset): bool => $this->deleteAssetFromEvent($event, $asset) || true);

        return back()->with('success', 'Selected media deleted.');
    }

    public function bulkUpdateAssetModeration(Request $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        if (! $this->eventAllowsModerationTools($event)) {
            return back()->with('error', 'Moderation tools are available on Pro.');
        }

        $validated = $request->validate([
            'asset_ids' => ['required', 'array', 'min:1', 'max:100'],
            'asset_ids.*' => ['required', 'integer'],
            'moderation_status' => ['required', 'string', Rule::in(['approved', 'rejected', 'processing'])],
        ]);

        $assetIds = collect($validated['asset_ids'])
            ->map(static fn (mixed $value): int => (int) $value)
            ->unique()
            ->values();

        EventAsset::query()
            ->where('event_id', $event->id)
            ->whereIn('id', $assetIds)
            ->update([
                'moderation_status' => $validated['moderation_status'],
                'reviewed_at' => $validated['moderation_status'] === 'processing' ? null : now(),
            ]);

        $this->invalidateEventMediaExport($event, deleteStoredFile: true);

        return back()->with('success', 'Selected media updated.');
    }

    public function updateAssetModeration(Request $request, Event $event, EventAsset $asset): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);
        abort_unless($asset->event_id === $event->id, 404);

        if (! $this->eventAllowsModerationTools($event)) {
            return back()->with('error', 'Moderation tools are available on Pro.');
        }

        $validated = $request->validate([
            'moderation_status' => ['required', 'string', Rule::in(['approved', 'rejected', 'processing'])],
        ]);

        $asset->update([
            'moderation_status' => $validated['moderation_status'],
            'reviewed_at' => $validated['moderation_status'] === 'processing' ? null : now(),
        ]);

        $this->invalidateEventMediaExport($event, deleteStoredFile: true);

        return back()->with('success', 'Moderation updated.');
    }

    public function updateAssetWallVisibility(Request $request, Event $event, EventAsset $asset): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);
        abort_unless($asset->event_id === $event->id, 404);

        $validated = $request->validate([
            'wall_visibility' => ['required', 'string', Rule::in(['approved', 'rejected', 'pending'])],
        ]);

        $this->syncAssetWallVisibility($asset, $validated['wall_visibility']);

        return back()->with('success', 'Photo wall visibility updated.');
    }

    public function settings(Request $request, Event $event): Response
    {
        $this->assertCanViewEvent($request, $event);

        return Inertia::render('events/Settings', [
            ...$this->eventProps($request, $event),
            'eventTypes' => $this->eventTypeOptions(),
            'eventDateMax' => now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            'textPostThemes' => $this->textPostThemesPayload(),
        ]);
    }

    public function updateSettings(Request $request, Event $event): RedirectResponse
    {
        $this->assertCanManageEvent($request, $event);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in($this->eventTypeValues())],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'event_date' => [
                'nullable',
                'date',
                'before_or_equal:'.now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            ],
            'timezone' => ['required', 'timezone:all'],
            'album_public' => ['required', 'boolean'],
            'moderation_enabled' => ['required', 'boolean'],
            'auto_moderation_enabled' => ['required', 'boolean'],
            'album_permission' => ['sometimes', 'string', Rule::in(['view_upload', 'view_only', 'upload_only'])],
            'allowed_media_types' => ['sometimes', 'array', 'min:1'],
            'allowed_media_types.*' => ['string', Rule::in(['photo', 'video', 'text'])],
            'display_language' => ['sometimes', 'string', Rule::in(['automatic', 'ro', 'en', 'el'])],
            'hide_side_images' => ['sometimes', 'boolean'],
            'hide_qr_code' => ['sometimes', 'boolean'],
            'hide_caption' => ['sometimes', 'boolean'],
            'caption_theme' => ['sometimes', 'string', Rule::in(['dark', 'light'])],
            'disable_guest_download' => ['sometimes', 'boolean'],
            'welcome_screen_enabled' => ['sometimes', 'boolean'],
            'welcome_screen_subtitle' => ['nullable', 'string', 'max:220'],
            'welcome_screen_button_text' => ['nullable', 'string', 'max:40'],
            'welcome_screen_font' => ['nullable', 'string', Rule::in($this->welcomeScreenFontValues())],
            'welcome_screen_animated' => ['sometimes', 'boolean'],
            'welcome_screen_collect_name' => ['sometimes', 'boolean'],
            'welcome_screen_collect_email' => ['sometimes', 'boolean'],
            'welcome_screen_collect_phone' => ['sometimes', 'boolean'],
            'welcome_screen_fields' => ['sometimes', 'array', 'max:12'],
            'welcome_screen_fields.*.id' => ['required', 'string', 'max:80'],
            'welcome_screen_fields.*.type' => ['required', 'string', Rule::in($this->welcomeScreenFieldTypeValues())],
            'welcome_screen_fields.*.label' => ['required', 'string', 'min:1', 'max:80'],
            'welcome_screen_fields.*.help_text' => ['nullable', 'string', 'max:160'],
            'welcome_screen_fields.*.attach_to' => ['nullable', 'string', Rule::in($this->welcomeScreenAttachToValues())],
            'welcome_screen_fields.*.required' => ['required', 'boolean'],
            'welcome_screen_fields.*.enabled' => ['required', 'boolean'],
            'wedding_details' => ['sometimes', 'array'],
            'wedding_details.partner_one_name' => ['nullable', 'string', 'max:80'],
            'wedding_details.partner_two_name' => ['nullable', 'string', 'max:80'],
            'wedding_details.family_name' => ['nullable', 'string', 'max:80'],
            'wedding_details.show_family_name' => ['sometimes', 'boolean'],
            'wedding_details.bride_parents' => ['nullable', 'string', 'max:160'],
            'wedding_details.groom_parents' => ['nullable', 'string', 'max:160'],
            'wedding_details.godparents' => ['nullable', 'string', 'max:160'],
            'welcome_screen_background_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:6144'],
            'remove_welcome_screen_background' => ['sometimes', 'boolean'],
            'album_background_enabled' => ['sometimes', 'boolean'],
            'album_background_mode' => ['sometimes', 'string', Rule::in(['rotate', 'solid', 'preset', 'image'])],
            'album_background_color' => ['nullable', 'regex:/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'album_background_preset_theme_id' => ['nullable', 'integer'],
            'album_background_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:6144'],
            'remove_album_background' => ['sometimes', 'boolean'],
            'text_posts_backgrounds_enabled' => ['sometimes', 'boolean'],
            'text_posts_background_palette' => ['sometimes', 'array', 'max:8'],
            'text_posts_background_palette.*' => ['string', 'regex:/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'moderation_filters' => ['sometimes', 'array', 'max:5'],
            'moderation_filters.*' => ['string', Rule::in(['adult', 'nudity', 'violence', 'suggestive', 'hate'])],
            'logo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'remove_logo' => ['sometimes', 'boolean'],
            'branding' => ['nullable', 'array'],
            'branding.primary_color' => ['nullable', 'regex:/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'branding.accent_color' => ['nullable', 'regex:/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'branding.welcome_message' => ['nullable', 'string', 'max:160'],
        ], [
            'event_date.before_or_equal' => 'Event date is too far in the future.',
        ]);

        $timezone = $validated['timezone'];
        $eventDate = $validated['event_date'] ?? null;
        $windows = $this->buildEventWindows($event, $eventDate, $timezone);
        $moderationEnabled = $this->eventAllowsModerationTools($event)
            ? (bool) $validated['moderation_enabled']
            : false;
        $autoModerationEnabled = $moderationEnabled && (bool) $validated['auto_moderation_enabled'];
        $removeLogo = (bool) ($validated['remove_logo'] ?? false);
        /** @var UploadedFile|null $logoFile */
        $logoFile = $request->file('logo_file');
        $removeAlbumBackground = (bool) ($validated['remove_album_background'] ?? false);
        /** @var UploadedFile|null $albumBackgroundFile */
        $albumBackgroundFile = $request->file('album_background_file');
        $removeWelcomeScreenBackground = (bool) ($validated['remove_welcome_screen_background'] ?? false);
        /** @var UploadedFile|null $welcomeScreenBackgroundFile */
        $welcomeScreenBackgroundFile = $request->file('welcome_screen_background_file');

        /** @var array<string, mixed> $brandingInput */
        $brandingInput = $validated['branding'] ?? [];
        $currentBranding = is_array($event->branding) ? $event->branding : [];
        $allowsBetterCustomization = $this->eventAllowsBetterCustomization($event);
        $allowsAdvancedCustomization = $this->eventAllowsAdvancedCustomization($event);

        if (! $allowsBetterCustomization) {
            $removeLogo = false;
            $logoFile = null;
        }

        if (! $allowsAdvancedCustomization) {
            $removeAlbumBackground = false;
            $albumBackgroundFile = null;
            $removeWelcomeScreenBackground = false;
            $welcomeScreenBackgroundFile = null;
        }

        $allowedMediaTypesSource = $validated['allowed_media_types'] ?? ($currentBranding['allowed_media_types'] ?? $this->defaultAllowedMediaTypes());
        if (! is_array($allowedMediaTypesSource)) {
            $allowedMediaTypesSource = $this->defaultAllowedMediaTypes();
        }
        $allowedMediaTypes = array_values(array_unique(array_filter(
            $allowedMediaTypesSource,
            fn ($value): bool => in_array($value, ['photo', 'video', 'text'], true),
        )));
        if ($allowedMediaTypes === []) {
            $allowedMediaTypes = $this->defaultAllowedMediaTypes();
        }
        $albumPermission = (string) ($validated['album_permission']
            ?? ($currentBranding['album_permission'] ?? ((bool) $validated['album_public'] ? 'view_upload' : 'upload_only')));
        if (! in_array($albumPermission, ['view_upload', 'view_only', 'upload_only'], true)) {
            $albumPermission = (bool) $validated['album_public'] ? 'view_upload' : 'upload_only';
        }
        $logoPath = is_string($currentBranding['logo_path'] ?? null) ? $currentBranding['logo_path'] : null;
        $logoDisk = is_string($currentBranding['logo_disk'] ?? null) ? $currentBranding['logo_disk'] : null;
        $albumBackgroundPath = is_string($currentBranding['album_background_path'] ?? null) ? $currentBranding['album_background_path'] : null;
        $albumBackgroundDisk = is_string($currentBranding['album_background_disk'] ?? null) ? $currentBranding['album_background_disk'] : null;
        $welcomeScreenBackgroundPath = is_string($currentBranding['welcome_screen_background_path'] ?? null) ? $currentBranding['welcome_screen_background_path'] : null;
        $welcomeScreenBackgroundDisk = is_string($currentBranding['welcome_screen_background_disk'] ?? null) ? $currentBranding['welcome_screen_background_disk'] : null;

        if ($removeLogo || $logoFile !== null) {
            if ($logoPath !== null && $logoDisk !== null) {
                Storage::disk($logoDisk)->delete($logoPath);
            }

            $logoPath = null;
            $logoDisk = null;
        }

        if ($logoFile !== null) {
            $logoDisk = (string) config('events.upload_disk', 'public');
            $extension = $logoFile->getClientOriginalExtension();
            $filename = 'logo-'.Str::uuid()->toString().($extension !== '' ? ".{$extension}" : '');
            $storedPath = $this->writeUploadedFileToStorage(
                $logoDisk,
                "events/{$event->id}/branding",
                $logoFile,
                $filename,
            );

            if ($storedPath === false) {
                throw ValidationException::withMessages([
                    'logo_file' => 'Logo upload failed. Please try again.',
                ]);
            }

            $logoPath = $storedPath;
        }

        if ($removeAlbumBackground || $albumBackgroundFile !== null) {
            if ($albumBackgroundPath !== null && $albumBackgroundDisk !== null) {
                Storage::disk($albumBackgroundDisk)->delete($albumBackgroundPath);
            }

            $albumBackgroundPath = null;
            $albumBackgroundDisk = null;
        }

        if ($albumBackgroundFile !== null) {
            $albumBackgroundDisk = (string) config('events.upload_disk', 'public');
            $extension = $albumBackgroundFile->getClientOriginalExtension();
            $filename = 'album-background-'.Str::uuid()->toString().($extension !== '' ? ".{$extension}" : '');
            $storedPath = $this->writeUploadedFileToStorage(
                $albumBackgroundDisk,
                "events/{$event->id}/branding",
                $albumBackgroundFile,
                $filename,
            );

            if ($storedPath === false) {
                throw ValidationException::withMessages([
                    'album_background_file' => 'Album background upload failed. Please try again.',
                ]);
            }

            $albumBackgroundPath = $storedPath;
        }

        if ($removeWelcomeScreenBackground || $welcomeScreenBackgroundFile !== null) {
            if ($welcomeScreenBackgroundPath !== null && $welcomeScreenBackgroundDisk !== null) {
                Storage::disk($welcomeScreenBackgroundDisk)->delete($welcomeScreenBackgroundPath);
            }

            $welcomeScreenBackgroundPath = null;
            $welcomeScreenBackgroundDisk = null;
        }

        if ($welcomeScreenBackgroundFile !== null) {
            $welcomeScreenBackgroundDisk = (string) config('events.upload_disk', 'public');
            $extension = $welcomeScreenBackgroundFile->getClientOriginalExtension();
            $filename = 'welcome-screen-background-'.Str::uuid()->toString().($extension !== '' ? ".{$extension}" : '');
            $storedPath = $this->writeUploadedFileToStorage(
                $welcomeScreenBackgroundDisk,
                "events/{$event->id}/branding",
                $welcomeScreenBackgroundFile,
                $filename,
            );

            if ($storedPath === false) {
                throw ValidationException::withMessages([
                    'welcome_screen_background_file' => 'Welcome screen background upload failed. Please try again.',
                ]);
            }

            $welcomeScreenBackgroundPath = $storedPath;
        }

        $albumBackgroundMode = (string) ($validated['album_background_mode'] ?? ($currentBranding['album_background_mode'] ?? 'rotate'));
        if (! in_array($albumBackgroundMode, ['rotate', 'solid', 'preset', 'image'], true)) {
            $albumBackgroundMode = 'rotate';
        }
        $albumBackgroundPresetThemeId = $this->normalizeTextPostThemeId(
            $validated['album_background_preset_theme_id'] ?? ($currentBranding['album_background_preset_theme_id'] ?? null),
        );
        if ($albumBackgroundMode === 'preset' && $albumBackgroundPresetThemeId === null) {
            $albumBackgroundMode = 'rotate';
        }
        if ($albumBackgroundMode === 'image' && ! $allowsAdvancedCustomization) {
            $albumBackgroundMode = $albumBackgroundPresetThemeId !== null ? 'preset' : 'rotate';
        }
        if ($albumBackgroundMode === 'image' && $albumBackgroundPath === null) {
            $albumBackgroundMode = $albumBackgroundPresetThemeId !== null ? 'preset' : 'rotate';
        }

        $textPostBackgroundPaletteSource = $validated['text_posts_background_palette']
            ?? ($currentBranding['text_posts_background_palette'] ?? ['#1D4ED8', '#0F766E', '#EA580C']);
        if (! is_array($textPostBackgroundPaletteSource)) {
            $textPostBackgroundPaletteSource = ['#1D4ED8', '#0F766E', '#EA580C'];
        }
        $textPostBackgroundPalette = array_values(array_unique(array_filter(array_map(
            static fn ($value): ?string => is_string($value) && preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value) === 1
                ? strtoupper($value)
                : null,
            $textPostBackgroundPaletteSource,
        ))));
        if ($textPostBackgroundPalette === []) {
            $textPostBackgroundPalette = ['#1D4ED8', '#0F766E', '#EA580C'];
        }

        $moderationFiltersSource = $validated['moderation_filters']
            ?? ($currentBranding['moderation_filters'] ?? ['adult', 'nudity', 'violence', 'suggestive']);
        if (! is_array($moderationFiltersSource)) {
            $moderationFiltersSource = ['adult', 'nudity', 'violence', 'suggestive'];
        }
        $moderationFilters = array_values(array_unique(array_filter(
            $moderationFiltersSource,
            static fn ($value): bool => is_string($value) && in_array($value, ['adult', 'nudity', 'violence', 'suggestive', 'hate'], true),
        )));
        if ($moderationFilters === []) {
            $moderationFilters = ['adult', 'nudity', 'violence', 'suggestive'];
        }

        $welcomeScreenFieldsSource = $validated['welcome_screen_fields']
            ?? ($currentBranding['welcome_screen_fields'] ?? $this->defaultWelcomeScreenFields());
        if (! is_array($welcomeScreenFieldsSource)) {
            $welcomeScreenFieldsSource = $this->defaultWelcomeScreenFields();
        }
        $welcomeScreenFields = $this->normalizeWelcomeScreenFields($welcomeScreenFieldsSource);
        $weddingDetailsSource = $validated['wedding_details']
            ?? ($currentBranding['wedding_details'] ?? []);
        $weddingDetails = $this->normalizeWeddingDetails($weddingDetailsSource);
        $collectName = collect($welcomeScreenFields)->contains(
            fn (array $field): bool => $field['enabled'] === true && $field['id'] === 'name',
        );
        $collectEmail = collect($welcomeScreenFields)->contains(
            fn (array $field): bool => $field['enabled'] === true && $field['type'] === 'email',
        );
        $collectPhone = collect($welcomeScreenFields)->contains(
            fn (array $field): bool => $field['enabled'] === true && $field['type'] === 'phone',
        );

        $branding = array_filter([
            'primary_color' => $allowsAdvancedCustomization ? ($brandingInput['primary_color'] ?? null) : null,
            'accent_color' => $allowsAdvancedCustomization ? ($brandingInput['accent_color'] ?? null) : null,
            'welcome_message' => $brandingInput['welcome_message'] ?? null,
            'display_language' => $validated['display_language'] ?? ($currentBranding['display_language'] ?? 'automatic'),
            'hide_side_images' => (bool) ($validated['hide_side_images'] ?? ($currentBranding['hide_side_images'] ?? false)),
            'hide_qr_code' => (bool) ($validated['hide_qr_code'] ?? ($currentBranding['hide_qr_code'] ?? false)),
            'hide_caption' => (bool) ($validated['hide_caption'] ?? ($currentBranding['hide_caption'] ?? false)),
            'caption_theme' => $validated['caption_theme'] ?? ($currentBranding['caption_theme'] ?? 'dark'),
            'disable_guest_download' => (bool) ($validated['disable_guest_download'] ?? ($currentBranding['disable_guest_download'] ?? false)),
            'welcome_screen_enabled' => (bool) ($validated['welcome_screen_enabled'] ?? ($currentBranding['welcome_screen_enabled'] ?? false)),
            'welcome_screen_subtitle' => $validated['welcome_screen_subtitle'] ?? ($currentBranding['welcome_screen_subtitle'] ?? null),
            'welcome_screen_button_text' => $validated['welcome_screen_button_text'] ?? ($currentBranding['welcome_screen_button_text'] ?? 'Continue'),
            'welcome_screen_font' => $validated['welcome_screen_font'] ?? ($currentBranding['welcome_screen_font'] ?? 'montserrat'),
            'welcome_screen_animated' => (bool) ($validated['welcome_screen_animated'] ?? ($currentBranding['welcome_screen_animated'] ?? false)),
            'welcome_screen_collect_name' => (bool) ($validated['welcome_screen_collect_name'] ?? $collectName),
            'welcome_screen_collect_email' => (bool) ($validated['welcome_screen_collect_email'] ?? $collectEmail),
            'welcome_screen_collect_phone' => (bool) ($validated['welcome_screen_collect_phone'] ?? $collectPhone),
            'welcome_screen_fields' => $welcomeScreenFields,
            'wedding_details' => $weddingDetails,
            'album_background_enabled' => (bool) ($validated['album_background_enabled'] ?? ($currentBranding['album_background_enabled'] ?? false)),
            'album_background_mode' => $albumBackgroundMode,
            'album_background_color' => $validated['album_background_color'] ?? ($currentBranding['album_background_color'] ?? '#0F172A'),
            'album_background_preset_theme_id' => $albumBackgroundPresetThemeId,
            'text_posts_backgrounds_enabled' => (bool) ($validated['text_posts_backgrounds_enabled'] ?? ($currentBranding['text_posts_backgrounds_enabled'] ?? false)),
            'text_posts_background_palette' => $textPostBackgroundPalette,
            'moderation_filters' => $moderationFilters,
            'album_permission' => $albumPermission,
            'allowed_media_types' => $allowedMediaTypes,
            'allowed_media_types_version' => 2,
        ], fn ($value): bool => $value !== null && $value !== '');

        if ($allowsBetterCustomization && $logoPath !== null && $logoDisk !== null) {
            $branding['logo_path'] = $logoPath;
            $branding['logo_disk'] = $logoDisk;
        }
        if ($allowsAdvancedCustomization && $albumBackgroundPath !== null && $albumBackgroundDisk !== null) {
            $branding['album_background_path'] = $albumBackgroundPath;
            $branding['album_background_disk'] = $albumBackgroundDisk;
        }
        if ($allowsAdvancedCustomization && $welcomeScreenBackgroundPath !== null && $welcomeScreenBackgroundDisk !== null) {
            $branding['welcome_screen_background_path'] = $welcomeScreenBackgroundPath;
            $branding['welcome_screen_background_disk'] = $welcomeScreenBackgroundDisk;
        }

        $event->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'event_date' => $eventDate,
            'timezone' => $timezone,
            'status' => $windows['status'],
            'upload_window_starts_at' => $windows['upload_window_starts_at'],
            'upload_window_ends_at' => $windows['upload_window_ends_at'],
            'grace_ends_at' => $windows['grace_ends_at'],
            'hard_lock_at' => $windows['hard_lock_at'],
            'payment_due_at' => $event->is_paid ? null : $windows['upload_window_starts_at'],
            'album_public' => $albumPermission !== 'upload_only',
            'moderation_enabled' => $moderationEnabled,
            'auto_moderation_enabled' => $autoModerationEnabled,
            'branding' => $branding === [] ? null : $branding,
        ]);

        return back()->with('success', 'Event settings saved.');
    }

    public function updateBilling(UpdateEventBillingRequest $request, Event $event): RedirectResponse
    {
        $plan = Plan::query()
            ->whereKey((int) $request->integer('plan_id'))
            ->where('is_active', true)
            ->firstOrFail();

        $timezone = $event->timezone ?: config('events.default_timezone', 'UTC');
        $isPaid = (bool) $request->boolean('is_paid');
        $billingNote = $request->filled('billing_note')
            ? trim((string) $request->string('billing_note'))
            : null;
        $paymentDueAt = $request->filled('payment_due_at')
            ? CarbonImmutable::parse((string) $request->string('payment_due_at'), $timezone)->endOfDay()
            : ($event->payment_due_at?->toImmutable() ?? $event->grace_ends_at?->toImmutable());
        $paidAt = $isPaid
            ? ($request->filled('paid_at')
                ? CarbonImmutable::parse((string) $request->string('paid_at'), $timezone)
                : ($event->paid_at?->toImmutable() ?? now($timezone)->toImmutable()))
            : null;

        app(EventBillingManager::class)->applyAdminUpdate(
            $event,
            $plan,
            $isPaid,
            $paymentDueAt,
            $paidAt,
            $billingNote,
        );

        return back()->with('success', 'Billing updated.');
    }

    public function createBillingCheckout(
        CreateEventCheckoutSessionRequest $request,
        Event $event,
        StripeCheckoutGateway $stripeCheckoutGateway,
    ): RedirectResponse|HttpResponse {
        $event->loadMissing(['user:id,email', 'plan']);

        if ($event->is_paid) {
            return back()->with('info', 'This event is already paid.');
        }

        if (! $event->plan instanceof Plan || ! $event->plan->is_active) {
            return back()->with('error', 'This event does not have an active billing plan yet.');
        }

        if ((int) $event->plan->price_cents <= 0) {
            return back()->with('info', 'This plan does not require an online payment.');
        }

        if (! $stripeCheckoutGateway->isConfigured()) {
            return back()->with('error', 'Online payments are not configured yet.');
        }

        $successUrl = route('events.settings', [
            'event' => $event,
            'tab' => 'billing',
            'stripe_checkout' => 'success',
        ]).'&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('events.settings', [
            'event' => $event,
            'tab' => 'billing',
            'stripe_checkout' => 'cancelled',
        ]);

        $session = $stripeCheckoutGateway->createCheckoutSession([
            'mode' => 'payment',
            'client_reference_id' => (string) $event->id,
            'customer_email' => (string) ($event->user?->email ?? $request->user()->email),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'payment_method_types' => ['card'],
            'metadata' => [
                'event_id' => (string) $event->id,
                'plan_id' => (string) $event->plan->id,
                'owner_id' => (string) $event->user_id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'event_id' => (string) $event->id,
                    'plan_id' => (string) $event->plan->id,
                    'owner_id' => (string) $event->user_id,
                ],
            ],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower((string) $event->plan->currency),
                    'unit_amount' => (int) $event->plan->price_cents,
                    'product_data' => [
                        'name' => "{$event->plan->name} - {$event->name}",
                        'description' => 'EventSmart event hosting and storage plan',
                    ],
                ],
            ]],
        ]);

        return Inertia::location($session['url']);
    }

    public function storeCollaborator(Request $request, Event $event): RedirectResponse
    {
        $this->assertOwnership($request, $event);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', Rule::in(['manager', 'viewer'])],
        ]);

        $normalizedEmail = Str::lower(trim((string) $validated['email']));
        $event->loadMissing('user');
        $ownerEmail = Str::lower((string) ($event->user?->email ?? ''));
        if ($normalizedEmail === '' || $normalizedEmail === $ownerEmail) {
            throw ValidationException::withMessages([
                'email' => 'Event owner cannot be invited as collaborator.',
            ]);
        }

        $existingCollaborator = EventCollaborator::query()
            ->where('event_id', $event->id)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();
        if (
            $existingCollaborator !== null &&
            $this->normalizeCollaboratorStatus($existingCollaborator->status) === 'active'
        ) {
            $existingCollaborator->update([
                'role' => $validated['role'],
            ]);

            return back()->with('success', 'Collaborator role updated.');
        }

        $existingUser = User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        $now = now();
        $expiresAt = $this->inviteExpiresAt($now);

        $collaborator = EventCollaborator::query()->updateOrCreate(
            [
                'event_id' => $event->id,
                'email' => $normalizedEmail,
            ],
            [
                'user_id' => $existingUser?->id,
                'role' => $validated['role'],
                'status' => 'invited',
                'invited_by_user_id' => $request->user()->id,
                'invited_at' => $now,
                'accepted_at' => null,
            ],
        );

        $acceptUrl = URL::temporarySignedRoute(
            'events.collaborators.accept',
            $expiresAt,
            ['collaborator' => $collaborator->id],
        );
        Notification::route('mail', $normalizedEmail)->notify(
            new EventCollaboratorInviteNotification(
                eventName: $event->name,
                inviterName: (string) $request->user()->name,
                acceptUrl: $acceptUrl,
                expiresAt: $expiresAt,
            ),
        );

        return back()->with(
            'success',
            'Invite sent. Collaborator remains invited until accepting the email link.',
        );
    }

    public function acceptCollaboratorInvite(Request $request, EventCollaborator $collaborator): Response|RedirectResponse
    {
        $event = $collaborator->event()->firstOrFail();
        if ($request->user() === null) {
            $normalizedInviteEmail = Str::lower((string) $collaborator->email);
            $hasAccount = User::query()
                ->whereRaw('LOWER(email) = ?', [$normalizedInviteEmail])
                ->exists();
            $expiresAt = $this->inviteExpiresAt(now());

            return Inertia::render('invitations/AcceptCollaborator', [
                'eventName' => $event->name,
                'email' => $collaborator->email,
                'hasAccount' => $hasAccount,
                'links' => [
                    'register' => URL::temporarySignedRoute(
                        'events.collaborators.complete-register',
                        $expiresAt,
                        ['collaborator' => $collaborator->id],
                    ),
                    'login' => URL::temporarySignedRoute(
                        'events.collaborators.complete-login',
                        $expiresAt,
                        ['collaborator' => $collaborator->id],
                    ),
                ],
            ]);
        }

        return $this->activateCollaboratorInvite($request->user(), $collaborator);
    }

    public function completeCollaboratorInviteRegistration(
        Request $request,
        EventCollaborator $collaborator,
    ): RedirectResponse {
        if ($request->user() !== null) {
            return $this->activateCollaboratorInvite($request->user(), $collaborator);
        }

        $normalizedInviteEmail = Str::lower((string) $collaborator->email);
        $existingUser = User::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedInviteEmail])
            ->first();
        if ($existingUser !== null) {
            throw ValidationException::withMessages([
                'email' => 'This invited email already has an account. Use password login below.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $collaborator->email,
            'password' => $validated['password'],
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return $this->activateCollaboratorInvite($user, $collaborator);
    }

    public function completeCollaboratorInviteLogin(
        Request $request,
        EventCollaborator $collaborator,
    ): RedirectResponse {
        if ($request->user() !== null) {
            return $this->activateCollaboratorInvite($request->user(), $collaborator);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [Str::lower((string) $collaborator->email)])
            ->first();
        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'No account found for this invited email.',
            ]);
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $request->string('password')->toString()], true)) {
            throw ValidationException::withMessages([
                'password' => 'Invalid password for this invited account.',
            ]);
        }

        $request->session()->regenerate();

        return $this->activateCollaboratorInvite($user, $collaborator);
    }

    public function album(Request $request, string $shareToken): Response
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: true);
        abort_if($event === null, 404);
        $isUploadOpen = $this->isUploadWindowOpen($event);
        $isPaymentLocked = $this->isPaymentLocked($event);
        $isPreEventTestMode = $this->isPreEventTestUploadMode($event);
        $canViewAlbumMedia = $this->publicAlbumAllowsViewingMedia($event);
        $canUploadToAlbum = $this->publicAlbumAllowsUploads($event) && ($isUploadOpen || $isPreEventTestMode);
        $allowedMediaTypes = $this->allowedMediaTypes($event);
        $guestLookups = $this->eventGuestLookups($event);
        $branding = $this->resolvedEventBranding($event);
        app()->setLocale(FrontendLocalization::resolveEventLocale($request, $branding));
        $captionTheme = (string) ($branding['caption_theme'] ?? 'dark');
        if (! in_array($captionTheme, ['dark', 'light'], true)) {
            $captionTheme = 'dark';
        }
        $albumUrl = $this->publicAlbumUrl($event);
        $assetPage = $canViewAlbumMedia
            ? $this->publicAlbumAssetPagePayload(
                $event,
                $guestLookups,
                null,
                self::PUBLIC_ALBUM_STACKS_PER_PAGE,
            )
            : [
                'assets' => [],
                'nextCursor' => null,
                'hasMore' => false,
            ];

        return Inertia::render('public/Album', [
            'shareToken' => $event->share_token,
            'albumAccessCode' => $event->publicAlbumCode(),
            'eventName' => $event->name,
            'eventDate' => $event->event_date?->toDateString(),
            'uploadWindowStartsAt' => $event->upload_window_starts_at?->toIso8601String(),
            'uploadWindowEndsAt' => $event->upload_window_ends_at?->toIso8601String(),
            'isUploadOpen' => $isUploadOpen,
            'isPaymentLocked' => $isPaymentLocked,
            'isUploadAllowed' => $canUploadToAlbum,
            'isPreEventTestMode' => $isPreEventTestMode,
            'canViewGallery' => $canViewAlbumMedia,
            'preEventTestUploadLimit' => $this->preEventTestUploadLimit(),
            'preEventTestUploadsRemaining' => $this->preEventTestUploadsRemaining($event),
            'guestProfileUrl' => route('events.album.guest-profile.show', $event->share_token),
            'guestProfileUpsertUrl' => route('events.album.guest-profile.upsert', $event->share_token),
            'uploadUrl' => route('events.album.upload', $event->share_token),
            'textPostUrl' => route('events.album.text-post', $event->share_token),
            'textPostThemes' => $this->textPostThemesPayload(),
            'allowTextPosts' => in_array('text', $allowedMediaTypes, true),
            'allowedMediaTypes' => $allowedMediaTypes,
            'canGuestDownload' => $this->publicGuestDownloadsEnabled($event),
            'showPoweredBy' => ! $this->eventRemovesAppBranding($event),
            'links' => [
                'album' => $albumUrl,
                'wall' => route('events.wall', $event->share_token),
                'albumEntry' => route('events.album.access.show'),
                'albumEntryShortcut' => 'https://is.gd/evsmrt',
            ],
            'assetFeedUrl' => route('events.album.assets', $event->share_token),
            'albumQrDataUrl' => $this->createQrCodeDataUrl($albumUrl),
            'appearance' => [
                'primaryColor' => $branding['primary_color'] ?? null,
                'accentColor' => $branding['accent_color'] ?? null,
                'logoUrl' => $this->brandingLogoUrl($branding),
                'hideSideImages' => (bool) ($branding['hide_side_images'] ?? false),
                'hideQrCode' => (bool) ($branding['hide_qr_code'] ?? false),
                'hideCaption' => (bool) ($branding['hide_caption'] ?? false),
                'captionTheme' => $captionTheme,
                'albumBackgroundEnabled' => (bool) ($branding['album_background_enabled'] ?? false),
                'albumBackgroundMode' => $this->albumBackgroundMode($branding),
                'albumBackgroundColor' => (string) ($branding['album_background_color'] ?? '#0F172A'),
                'albumBackgroundImageUrl' => $this->effectiveAlbumBackgroundUrl($event, $branding),
            ],
            'welcomeScreen' => [
                'enabled' => (bool) ($branding['welcome_screen_enabled'] ?? false),
                'title' => (string) ($branding['welcome_message'] ?? ''),
                'subtitle' => (string) ($branding['welcome_screen_subtitle'] ?? ''),
                'buttonText' => (string) ($branding['welcome_screen_button_text'] ?? 'Continue'),
                'font' => (string) ($branding['welcome_screen_font'] ?? 'montserrat'),
                'animated' => (bool) ($branding['welcome_screen_animated'] ?? false),
                'logoUrl' => $this->brandingLogoUrl($branding),
                'collectName' => (bool) ($branding['welcome_screen_collect_name'] ?? true),
                'collectEmail' => (bool) ($branding['welcome_screen_collect_email'] ?? false),
                'collectPhone' => (bool) ($branding['welcome_screen_collect_phone'] ?? false),
                'fields' => $this->normalizeWelcomeScreenFields($branding['welcome_screen_fields'] ?? []),
                'backgroundUrl' => $this->brandingWelcomeScreenBackgroundUrl(
                    $branding,
                ),
            ],
            'limits' => [
                'storageLimitBytes' => $event->storage_limit_bytes,
                'storageUsedBytes' => $event->storage_used_bytes,
                'storageRemainingBytes' => max(0, $event->storage_limit_bytes - $event->storage_used_bytes),
                'uploadLimit' => $event->upload_limit,
                'uploadCount' => $event->upload_count,
                'uploadRemaining' => max(0, $event->upload_limit - $event->upload_count),
                'photoMaxSizeBytes' => $event->photo_max_size_bytes,
                'videoMaxSizeBytes' => $event->video_max_size_bytes,
                'videoMinDurationSeconds' => 10,
                'videoMaxDurationSeconds' => $event->video_max_duration_seconds,
            ],
            'assets' => $assetPage['assets'],
            'assetsNextCursor' => $assetPage['nextCursor'],
            'assetsHasMore' => $assetPage['hasMore'],
        ]);
    }

    public function albumAssets(Request $request, string $shareToken): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->with([
                'guests' => fn ($query) => $query->latest('updated_at')->limit(500),
            ])
            ->firstOrFail();

        if (! $this->publicAlbumAllowsViewingMedia($event)) {
            return response()->json([
                'assets' => [],
                'nextCursor' => null,
                'hasMore' => false,
            ]);
        }

        $guestLookups = $this->eventGuestLookups($event);
        $beforeCursor = $request->integer('before_cursor');
        $assetPage = $this->publicAlbumAssetPagePayload(
            $event,
            $guestLookups,
            $beforeCursor > 0 ? $beforeCursor : null,
            self::PUBLIC_ALBUM_STACKS_PER_PAGE,
        );

        return response()->json($assetPage);
    }

    public function guestProfile(Request $request, string $shareToken): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        $validated = $request->validate([
            'guest_token' => ['required', 'string', 'max:120'],
        ]);

        $guest = EventGuest::query()
            ->where('event_id', $event->id)
            ->where('guest_token', trim((string) $validated['guest_token']))
            ->first();
        if ($guest === null) {
            return response()->json([
                'guest' => null,
                'likedAssetIds' => [],
            ]);
        }

        return response()->json([
            'guest' => $this->serializeEventGuest($guest),
            'likedAssetIds' => $guest->likes()
                ->whereHas('asset', fn ($query) => $query->where('event_id', $event->id))
                ->pluck('event_asset_id')
                ->map(static fn (mixed $value): int => (int) $value)
                ->values()
                ->all(),
        ]);
    }

    public function upsertGuestProfile(Request $request, string $shareToken): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        $validated = $request->validate([
            'guest_token' => ['required', 'string', 'max:120'],
            'guest_name' => ['required', 'string', 'min:2', 'max:80'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'guest_fields' => ['nullable', 'array', 'max:20'],
            'guest_fields.*' => ['nullable', 'string', 'max:255'],
            'guest_intent' => ['nullable', 'string', Rule::in($this->guestIntentValues())],
            'avatar_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        /** @var UploadedFile|null $avatarFile */
        $avatarFile = $request->file('avatar_file');
        $guest = $this->upsertEventGuest(
            $event,
            [
                'guest_token' => trim((string) $validated['guest_token']),
                'name' => trim((string) $validated['guest_name']),
                'email' => isset($validated['guest_email']) ? trim((string) $validated['guest_email']) : null,
                'phone' => isset($validated['guest_phone']) ? trim((string) $validated['guest_phone']) : null,
                'guest_fields' => is_array($validated['guest_fields'] ?? null) ? $validated['guest_fields'] : [],
                'last_intent' => isset($validated['guest_intent']) ? (string) $validated['guest_intent'] : null,
            ],
            $avatarFile,
            (bool) ($validated['remove_avatar'] ?? false),
        );

        return response()->json([
            'guest' => $this->serializeEventGuest($guest),
            'likedAssetIds' => $guest->likes()->pluck('event_asset_id')->map(static fn (mixed $value): int => (int) $value)->values()->all(),
        ]);
    }

    public function upload(Request $request, string $shareToken): RedirectResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['required', 'file'],
            'guest_name' => ['required', 'string', 'min:2', 'max:80'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:500'],
            'guest_token' => ['nullable', 'string', 'max:120'],
            'guest_fields' => ['nullable', 'array', 'max:20'],
            'guest_fields.*' => ['nullable', 'string', 'max:255'],
            'guest_intent' => ['nullable', 'string', Rule::in($this->guestIntentValues())],
        ]);
        $guestName = trim((string) $validated['guest_name']);
        $guestEmail = isset($validated['guest_email']) ? trim((string) $validated['guest_email']) : null;
        $guestPhone = isset($validated['guest_phone']) ? trim((string) $validated['guest_phone']) : null;
        $message = isset($validated['message']) ? trim((string) $validated['message']) : null;
        $guestToken = isset($validated['guest_token']) ? trim((string) $validated['guest_token']) : null;
        $guestFields = is_array($validated['guest_fields'] ?? null) ? $validated['guest_fields'] : [];
        $guestIntent = isset($validated['guest_intent']) ? (string) $validated['guest_intent'] : null;
        if ($guestName === '') {
            throw ValidationException::withMessages([
                'guest_name' => 'Please enter your name before uploading.',
            ]);
        }

        /** @var array<int, UploadedFile> $files */
        $files = $validated['files'];

        DB::transaction(function () use ($event, $files, $guestName, $guestEmail, $guestPhone, $message, $guestToken, $guestFields, $guestIntent): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $this->ensureUploadsAllowed($lockedEvent);
            $isPreEventTestMode = $this->isPreEventTestUploadMode($lockedEvent);
            $preEventTestUploadsRemaining = $isPreEventTestMode
                ? $this->preEventTestUploadsRemaining($lockedEvent)
                : null;
            $guestFieldAttachments = $this->resolveGuestFieldAttachments(
                $lockedEvent,
                $guestFields,
            );

            $storageUsedBytes = (int) $lockedEvent->storage_used_bytes;
            $uploadCount = (int) $lockedEvent->upload_count;
            $disk = (string) config('events.upload_disk', 'public');
            $allowedMediaTypes = $this->allowedMediaTypes($lockedEvent);
            $uploadBatchId = Str::uuid()->toString();
            if ($guestToken !== null && $guestToken !== '') {
                $this->upsertEventGuest($lockedEvent, [
                    'guest_token' => $guestToken,
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'phone' => $guestPhone,
                    'guest_fields' => $guestFields,
                    'last_intent' => $guestIntent,
                ]);
            }

            foreach ($files as $index => $file) {
                if ($preEventTestUploadsRemaining !== null && $preEventTestUploadsRemaining <= 0) {
                    throw ValidationException::withMessages([
                        'files' => "Pre-event testing limit reached ({$this->preEventTestUploadLimit()} uploads).",
                    ]);
                }

                $kind = $this->resolveAssetKind($file);
                if (! in_array($kind, $allowedMediaTypes, true)) {
                    throw ValidationException::withMessages([
                        'files' => "This event doesn't allow {$kind} uploads.",
                    ]);
                }

                $sizeBytes = (int) $file->getSize();
                $optimizedPhoto = null;
                if ($kind === 'photo') {
                    $this->ensurePhotoUploadWithinProcessingLimit($lockedEvent, $sizeBytes);
                    $optimizedPhoto = $this->optimizedPhotoUpload($file);
                    $sizeBytes = $optimizedPhoto['sizeBytes'];
                } else {
                    $this->ensureFileWithinKindLimit($lockedEvent, $kind, $sizeBytes);
                }

                $this->ensureFileWithinKindLimit($lockedEvent, $kind, $sizeBytes);

                if ($uploadCount + 1 > $lockedEvent->upload_limit) {
                    throw ValidationException::withMessages([
                        'files' => 'Upload limit reached for this event.',
                    ]);
                }

                if ($storageUsedBytes + $sizeBytes > $lockedEvent->storage_limit_bytes) {
                    throw ValidationException::withMessages([
                        'files' => 'Storage limit reached for this event.',
                    ]);
                }

                $displayFilename = $this->generatedAssetFilename(
                    $file,
                    $kind,
                    $guestName,
                    $index,
                    $optimizedPhoto['extension'] ?? null,
                );
                $filename = $this->generatedAssetStorageFilename($displayFilename);
                $path = $optimizedPhoto !== null
                    ? "events/{$lockedEvent->id}/uploads/{$filename}"
                    : $this->writeUploadedFileToStorage(
                        $disk,
                        "events/{$lockedEvent->id}/uploads",
                        $file,
                        $filename,
                    );
                if ($optimizedPhoto !== null) {
                    $stored = $this->writeContentsToStorage(
                        $disk,
                        $path,
                        $optimizedPhoto['contents'],
                        ['visibility' => 'private'],
                    );
                    if (! $stored) {
                        $path = false;
                    }
                }
                if ($path === false) {
                    throw ValidationException::withMessages([
                        'files' => 'Upload failed. Please try again.',
                    ]);
                }

                $moderation = $this->moderationDecision(
                    $lockedEvent,
                    $kind,
                    $displayFilename,
                    $message,
                    null,
                );

                $asset = $lockedEvent->assets()->create([
                    'kind' => $kind,
                    'disk' => $disk,
                    'path' => $path,
                    'original_filename' => $displayFilename,
                    'mime_type' => $optimizedPhoto['mimeType'] ?? ($file->getMimeType() ?? 'application/octet-stream'),
                    'size_bytes' => $sizeBytes,
                    'width' => $optimizedPhoto['width'] ?? null,
                    'height' => $optimizedPhoto['height'] ?? null,
                    'metadata' => array_filter([
                        'guest_name' => $guestName,
                        'guest_email' => $guestEmail,
                        'guest_phone' => $guestPhone,
                        'message' => $message,
                        'guest_token' => $guestToken,
                        'guest_fields' => $guestFields,
                        'guest_intent' => $guestIntent,
                        'upload_batch_id' => $uploadBatchId,
                        'upload_batch_index' => $index + 1,
                        'wall_visibility' => $this->initialWallVisibility($moderation['status']),
                        ...$guestFieldAttachments,
                        ...$moderation['metadata'],
                    ], fn ($value): bool => $value !== null && $value !== ''),
                    'moderation_status' => $moderation['status'],
                    'moderation_score' => $moderation['score'],
                    'is_watermarked' => false,
                    'reviewed_at' => $moderation['reviewedAt'],
                ]);

                if ($kind === 'photo') {
                    GenerateEventAssetImageVariants::dispatch($asset->id)->afterCommit();
                } elseif ($kind === 'video') {
                    GenerateEventAssetVideoThumbnails::dispatch($asset->id)->afterCommit();
                }

                $storageUsedBytes += $sizeBytes;
                $uploadCount++;
                if ($preEventTestUploadsRemaining !== null) {
                    $preEventTestUploadsRemaining--;
                }
            }

            $lockedEvent->update([
                'storage_used_bytes' => $storageUsedBytes,
                'upload_count' => $uploadCount,
            ]);

            $this->invalidateEventMediaExport($lockedEvent, deleteStoredFile: true);
        });

        return back()->with('success', 'Files uploaded.');
    }

    public function postText(Request $request, string $shareToken): RedirectResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
            'text_post_theme_id' => ['required', 'integer', Rule::exists('text_post_themes', 'id')->where('is_active', true)],
            'guest_name' => ['required', 'string', 'min:2', 'max:80'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'guest_token' => ['nullable', 'string', 'max:120'],
            'guest_fields' => ['nullable', 'array', 'max:20'],
            'guest_fields.*' => ['nullable', 'string', 'max:255'],
            'guest_intent' => ['nullable', 'string', Rule::in($this->guestIntentValues())],
        ]);
        $text = trim((string) $validated['text']);
        $textPostThemeId = (int) $validated['text_post_theme_id'];
        $guestName = trim((string) $validated['guest_name']);
        $guestEmail = isset($validated['guest_email']) ? trim((string) $validated['guest_email']) : null;
        $guestPhone = isset($validated['guest_phone']) ? trim((string) $validated['guest_phone']) : null;
        $guestToken = isset($validated['guest_token']) ? trim((string) $validated['guest_token']) : null;
        $guestFields = is_array($validated['guest_fields'] ?? null) ? $validated['guest_fields'] : [];
        $guestIntent = isset($validated['guest_intent']) ? (string) $validated['guest_intent'] : null;
        if ($text === '') {
            throw ValidationException::withMessages([
                'text' => 'Text post cannot be empty.',
            ]);
        }
        if ($guestName === '') {
            throw ValidationException::withMessages([
                'guest_name' => 'Please enter your name before posting.',
            ]);
        }

        DB::transaction(function () use ($event, $text, $textPostThemeId, $guestName, $guestEmail, $guestPhone, $guestToken, $guestFields, $guestIntent): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $this->ensureUploadsAllowed($lockedEvent);
            if (! in_array('text', $this->allowedMediaTypes($lockedEvent), true)) {
                throw ValidationException::withMessages([
                    'text' => 'Text posts are disabled for this event.',
                ]);
            }
            $theme = TextPostTheme::query()
                ->active()
                ->find($textPostThemeId);
            if ($theme === null) {
                throw ValidationException::withMessages([
                    'text' => 'Please choose a background theme.',
                ]);
            }
            $guestFieldAttachments = $this->resolveGuestFieldAttachments(
                $lockedEvent,
                $guestFields,
            );

            $isPreEventTestMode = $this->isPreEventTestUploadMode($lockedEvent);
            $preEventTestUploadsRemaining = $isPreEventTestMode
                ? $this->preEventTestUploadsRemaining($lockedEvent)
                : null;
            if ($preEventTestUploadsRemaining !== null && $preEventTestUploadsRemaining <= 0) {
                throw ValidationException::withMessages([
                    'text' => "Pre-event testing limit reached ({$this->preEventTestUploadLimit()} uploads).",
                ]);
            }

            $storageUsedBytes = (int) $lockedEvent->storage_used_bytes;
            $uploadCount = (int) $lockedEvent->upload_count;
            $sizeBytes = strlen($text);
            if ($guestToken !== null && $guestToken !== '') {
                $this->upsertEventGuest($lockedEvent, [
                    'guest_token' => $guestToken,
                    'name' => $guestName,
                    'email' => $guestEmail,
                    'phone' => $guestPhone,
                    'guest_fields' => $guestFields,
                    'last_intent' => $guestIntent,
                ]);
            }

            if ($uploadCount + 1 > $lockedEvent->upload_limit) {
                throw ValidationException::withMessages([
                    'text' => 'Upload limit reached for this event.',
                ]);
            }

            if ($storageUsedBytes + $sizeBytes > $lockedEvent->storage_limit_bytes) {
                throw ValidationException::withMessages([
                    'text' => 'Storage limit reached for this event.',
                ]);
            }

            $disk = (string) config('events.upload_disk', 'public');
            $filename = 'post-'.Str::uuid()->toString().'.txt';
            $path = "events/{$lockedEvent->id}/text-posts/{$filename}";
            $stored = $this->writeContentsToStorage($disk, $path, $text, ['visibility' => 'private']);
            if (! $stored) {
                throw ValidationException::withMessages([
                    'text' => 'Text post upload failed. Please try again.',
                ]);
            }

            $moderation = $this->moderationDecision(
                $lockedEvent,
                'text',
                $theme->name,
                null,
                $text,
            );

            $lockedEvent->assets()->create([
                'kind' => 'text',
                'disk' => $disk,
                'path' => $path,
                'original_filename' => null,
                'mime_type' => 'text/plain',
                'size_bytes' => $sizeBytes,
                'metadata' => array_filter([
                    'text' => $text,
                    'text_theme_id' => $theme->id,
                    'text_theme_slug' => $theme->slug,
                    'text_background_image_path' => $theme->image_path,
                    'text_background_color' => $theme->background_color,
                    'text_color' => $theme->text_color,
                    'guest_name' => $guestName,
                    'guest_email' => $guestEmail,
                    'guest_phone' => $guestPhone,
                    'guest_token' => $guestToken,
                    'guest_fields' => $guestFields,
                    'guest_intent' => $guestIntent,
                    'wall_visibility' => $this->initialWallVisibility($moderation['status']),
                    ...$guestFieldAttachments,
                    ...$moderation['metadata'],
                ], fn ($value): bool => $value !== null && $value !== ''),
                'moderation_status' => $moderation['status'],
                'moderation_score' => $moderation['score'],
                'is_watermarked' => false,
                'reviewed_at' => $moderation['reviewedAt'],
            ]);

            $lockedEvent->update([
                'storage_used_bytes' => $storageUsedBytes + $sizeBytes,
                'upload_count' => $uploadCount + 1,
            ]);

            $this->invalidateEventMediaExport($lockedEvent, deleteStoredFile: true);
        });

        return back()->with('success', 'Text post published.');
    }

    public function toggleAssetLike(Request $request, string $shareToken, EventAsset $asset): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);

        $validated = $request->validate([
            'guest_token' => ['required', 'string', 'max:120'],
        ]);

        $guest = EventGuest::query()
            ->where('event_id', $event->id)
            ->where('guest_token', trim((string) $validated['guest_token']))
            ->first();
        if ($guest === null) {
            throw ValidationException::withMessages([
                'guest_token' => 'Complete guest onboarding before liking uploads.',
            ]);
        }

        $liked = DB::transaction(function () use ($asset, $guest): bool {
            $existingLike = EventAssetLike::query()
                ->where('event_asset_id', $asset->id)
                ->where('event_guest_id', $guest->id)
                ->lockForUpdate()
                ->first();

            if ($existingLike !== null) {
                $existingLike->delete();

                return false;
            }

            EventAssetLike::query()->create([
                'event_asset_id' => $asset->id,
                'event_guest_id' => $guest->id,
            ]);

            return true;
        });

        $guest->forceFill([
            'last_seen_at' => now(),
        ])->save();

        return response()->json([
            'liked' => $liked,
            'likeCount' => EventAssetLike::query()
                ->where('event_asset_id', $asset->id)
                ->count(),
        ]);
    }

    public function assetComments(Request $request, string $shareToken, EventAsset $asset): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);

        $validated = $request->validate([
            'guest_token' => ['nullable', 'string', 'max:120'],
        ]);
        $currentGuest = $this->eventGuestForToken(
            $event,
            isset($validated['guest_token']) ? trim((string) $validated['guest_token']) : null,
        );

        $comments = EventAssetComment::query()
            ->where('event_asset_id', $asset->id)
            ->with('guest')
            ->withCount('likes')
            ->oldest('id')
            ->get()
            ->map(fn (EventAssetComment $comment): array => $this->serializeEventAssetComment($event, $asset, $comment, $currentGuest?->id))
            ->values()
            ->all();

        return response()->json([
            'comments' => $comments,
            'commentCount' => count($comments),
        ]);
    }

    public function storeAssetComment(Request $request, string $shareToken, EventAsset $asset): JsonResponse
    {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);

        $validated = $request->validate([
            'guest_token' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $guest = $this->eventGuestForToken(
            $event,
            trim((string) $validated['guest_token']),
        );
        if ($guest === null) {
            throw ValidationException::withMessages([
                'guest_token' => 'Complete guest onboarding before commenting.',
            ]);
        }

        $body = trim((string) $validated['body']);
        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => 'Comment cannot be empty.',
            ]);
        }

        $comment = EventAssetComment::query()->create([
            'event_asset_id' => $asset->id,
            'event_guest_id' => $guest->id,
            'body' => $body,
        ]);

        $guest->forceFill([
            'last_seen_at' => now(),
        ])->save();

        $comment->load('guest')->loadCount('likes');

        return response()->json([
            'comment' => $this->serializeEventAssetComment($event, $asset, $comment, $guest->id),
            'commentCount' => EventAssetComment::query()
                ->where('event_asset_id', $asset->id)
                ->count(),
        ]);
    }

    public function toggleAssetCommentLike(
        Request $request,
        string $shareToken,
        EventAsset $asset,
        EventAssetComment $comment,
    ): JsonResponse {
        $event = Event::query()
            ->where('share_token', $shareToken)
            ->firstOrFail();

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($comment->event_asset_id === $asset->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);

        $validated = $request->validate([
            'guest_token' => ['required', 'string', 'max:120'],
        ]);

        $guest = $this->eventGuestForToken(
            $event,
            trim((string) $validated['guest_token']),
        );
        if ($guest === null) {
            throw ValidationException::withMessages([
                'guest_token' => 'Complete guest onboarding before liking comments.',
            ]);
        }

        $liked = DB::transaction(function () use ($comment, $guest): bool {
            $existingLike = EventAssetCommentLike::query()
                ->where('event_asset_comment_id', $comment->id)
                ->where('event_guest_id', $guest->id)
                ->lockForUpdate()
                ->first();

            if ($existingLike !== null) {
                $existingLike->delete();

                return false;
            }

            EventAssetCommentLike::query()->create([
                'event_asset_comment_id' => $comment->id,
                'event_guest_id' => $guest->id,
            ]);

            return true;
        });

        $guest->forceFill([
            'last_seen_at' => now(),
        ])->save();

        return response()->json([
            'liked' => $liked,
            'likeCount' => EventAssetCommentLike::query()
                ->where('event_asset_comment_id', $comment->id)
                ->count(),
        ]);
    }

    public function downloadPublicAsset(string $shareToken, EventAsset $asset): StreamedResponse|RedirectResponse
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: false);
        abort_if($event === null, 404);

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);
        abort_unless($this->publicGuestDownloadsEnabled($event), 403);
        abort_unless(! $this->videoVariantsPending($asset, true), 409);

        $downloadPath = $this->assetPublicDownloadPath($asset);
        $filename = $this->publicDownloadFilename($asset, $downloadPath);

        return $this->downloadStorageAsset($asset->disk, $downloadPath, $filename);
    }

    public function publicAssetPreview(string $shareToken, EventAsset $asset): StreamedResponse|RedirectResponse
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: false);
        abort_if($event === null, 404);

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);
        abort_unless(! $this->videoVariantsPending($asset, true), 409);

        $previewPath = $this->assetPublicPreviewPath($asset);
        abort_unless($previewPath !== null, 404);

        return $this->streamStorageAsset($asset->disk, $previewPath);
    }

    public function publicAssetThumbnail(string $shareToken, EventAsset $asset): StreamedResponse|RedirectResponse
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: false);
        abort_if($event === null, 404);

        abort_unless($asset->event_id === $event->id, 404);
        abort_unless($this->publicAlbumAllowsViewingMedia($event), 403);
        abort_unless($asset->moderation_status === 'approved', 404);
        abort_unless(! $this->videoVariantsPending($asset, true), 409);

        $thumbnailPath = $this->assetPublicThumbnailPath($asset);
        abort_unless($thumbnailPath !== null, 404);

        return $this->streamStorageAsset($asset->disk, $thumbnailPath);
    }

    public function deletePublicAsset(Request $request, string $shareToken, EventAsset $asset): RedirectResponse
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: false);
        abort_if($event === null, 404);

        abort_unless($asset->event_id === $event->id, 404);

        $validated = $request->validate([
            'guest_name' => ['nullable', 'string', 'min:2', 'max:80'],
            'guest_token' => ['required', 'string', 'max:120'],
        ]);

        $guestToken = trim((string) $validated['guest_token']);

        abort_unless($this->canGuestDeleteAsset($asset, $guestToken), 403);

        $this->deleteAssetFromEvent($event, $asset);

        return back()->with('success', 'Upload deleted.');
    }

    public function wall(Request $request, string $shareToken): Response
    {
        $event = $this->resolvePublicAlbumEvent($shareToken, withGuests: false, extraRelations: [
            'assets' => fn ($query) => $query
                ->whereIn('kind', ['photo', 'video', 'text'])
                ->where('moderation_status', 'approved')
                ->withCount(['likes', 'comments'])
                ->with([
                    'comments' => fn ($commentQuery) => $commentQuery
                        ->latest('id')
                        ->limit(6)
                        ->with('guest')
                        ->withCount('likes'),
                ])
                ->latest('id')
                ->limit(480),
        ]);
        abort_if($event === null, 404);
        $branding = $this->resolvedEventBranding($event);
        app()->setLocale(FrontendLocalization::resolveEventLocale($request, $branding));
        $captionTheme = (string) ($branding['caption_theme'] ?? 'dark');
        if (! in_array($captionTheme, ['dark', 'light'], true)) {
            $captionTheme = 'dark';
        }
        $albumUrl = $this->publicAlbumUrl($event);

        return Inertia::render('public/Wall', [
            'eventName' => $event->name,
            'status' => $event->status,
            'albumUrl' => $albumUrl,
            'albumAccessCode' => $event->publicAlbumCode(),
            'albumEntryShortcutUrl' => 'https://is.gd/evsmrt',
            'albumQrDataUrl' => $this->createQrCodeDataUrl($albumUrl),
            'showPoweredBy' => ! $this->eventRemovesAppBranding($event),
            'branding' => [
                'primaryColor' => $branding['primary_color'] ?? null,
                'accentColor' => $branding['accent_color'] ?? null,
                'logoUrl' => $this->brandingLogoUrl($branding),
                'welcomeMessage' => $branding['welcome_message'] ?? null,
                'hideSideImages' => (bool) ($branding['hide_side_images'] ?? false),
                'hideQrCode' => (bool) ($branding['hide_qr_code'] ?? false),
                'hideCaption' => (bool) ($branding['hide_caption'] ?? false),
                'captionTheme' => $captionTheme,
                'albumBackgroundEnabled' => (bool) ($branding['album_background_enabled'] ?? false),
                'albumBackgroundMode' => $this->albumBackgroundMode($branding),
                'albumBackgroundColor' => (string) ($branding['album_background_color'] ?? '#0F172A'),
                'albumBackgroundImageUrl' => $this->effectiveAlbumBackgroundUrl($event, $branding),
            ],
            'assets' => $event->assets
                ->filter(fn (EventAsset $asset): bool => $this->assetVisibleOnWall($asset))
                ->take(240)
                ->values()
                ->map(fn (EventAsset $asset): array => $this->wallAssetProps($event, $asset))
                ->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extraRelations
     */
    private function resolvePublicAlbumEvent(string $code, bool $withGuests = false, array $extraRelations = []): ?Event
    {
        $normalizedCode = Str::upper(trim($code));

        $relations = $extraRelations;

        if ($withGuests) {
            $relations['guests'] = fn ($query) => $query->latest('updated_at')->limit(500);
        }

        $query = Event::query();

        if ($relations !== []) {
            $query->with($relations);
        }

        return $query
            ->where('album_access_code', $normalizedCode)
            ->orWhere('share_token', $code)
            ->first();
    }

    private function publicAlbumUrl(Event $event): string
    {
        return route('events.album', $event->publicAlbumCode());
    }

    /**
     * @return array<string, mixed>
     */
    private function eventProps(Request $request, Event $event): array
    {
        $event->loadMissing(['user:id,email', 'collaborators', 'plan']);
        $albumUrl = $this->publicAlbumUrl($event);
        $publicShortLinks = app(IsgdShortUrlManager::class)->forEvent($event);
        $showEventOverviewLink = $this->shouldShowEventOverviewLink($request);
        $branding = $this->resolvedEventBranding($event);
        $eventOverviewUrl = $this->eventOverviewUrl($request);
        $planFeatures = $this->planFeaturePayload($event);
        $canManageBilling = $request->user()->canAccessAdmin();
        $canCheckoutBilling = ! $canManageBilling
            && $request->user()->id === $event->user_id
            && ! $event->is_paid
            && $event->plan instanceof Plan
            && $event->plan->is_active
            && (int) $event->plan->price_cents > 0
            && trim((string) config('services.stripe.secret')) !== '';
        $todayStart = now($event->timezone)->startOfDay()->utc();
        $todayEnd = now($event->timezone)->endOfDay()->utc();
        $moderationSummary = [
            'processingCount' => EventAsset::query()
                ->where('event_id', $event->id)
                ->where('moderation_status', 'processing')
                ->count(),
            'autoRejectedCount' => EventAsset::query()
                ->where('event_id', $event->id)
                ->where('moderation_status', 'rejected')
                ->where('metadata->moderation->pipeline', 'automatic')
                ->count(),
            'approvedTodayCount' => EventAsset::query()
                ->where('event_id', $event->id)
                ->where('moderation_status', 'approved')
                ->whereBetween('reviewed_at', [$todayStart, $todayEnd])
                ->count(),
        ];

        $captionTheme = (string) ($branding['caption_theme'] ?? 'dark');
        if (! in_array($captionTheme, ['dark', 'light'], true)) {
            $captionTheme = 'dark';
        }
        [$billingStatusCode, $billingStatusLabel, $billingStatusHint, $billingStatusTone] = $this->billingStatusMeta($event);
        $storageQuota = $this->storageQuotaMeta(
            (int) $event->storage_limit_bytes,
            (int) $event->storage_used_bytes,
        );
        $albumPermission = $branding['album_permission'] ?? null;
        if (! is_string($albumPermission) || ! in_array($albumPermission, ['view_upload', 'view_only', 'upload_only'], true)) {
            $albumPermission = $event->album_public ? 'view_upload' : 'upload_only';
        }

        $ownerEmail = (string) ($event->user?->email ?? '');
        $collaborators = [
            [
                'id' => "owner-{$event->id}",
                'email' => $ownerEmail,
                'role' => 'owner',
                'status' => 'active',
            ],
            ...$event->collaborators
                ->sortByDesc('id')
                ->map(fn (EventCollaborator $collaborator): array => [
                    'id' => $collaborator->id,
                    'email' => $collaborator->email,
                    'role' => $collaborator->role,
                    'status' => $this->normalizeCollaboratorStatus($collaborator->status),
                ])
                ->all(),
        ];

        return [
            'currentEvent' => [
                'id' => $event->id,
                'name' => $event->name,
                'type' => $event->type,
                'status' => $event->status,
                'plan' => $event->plan?->name ?? 'Free',
                'planId' => $event->plan_id,
                'planFeatures' => $planFeatures,
                'currency' => $event->currency,
                'isPaid' => $event->is_paid,
                'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                'paidAt' => $event->paid_at?->toIso8601String(),
                'retentionEndsAt' => $event->retention_ends_at?->toIso8601String(),
                'storageLimitBytes' => $event->storage_limit_bytes,
                'storageUsedBytes' => $event->storage_used_bytes,
                'uploadLimit' => $event->upload_limit,
                'uploadCount' => $event->upload_count,
                'eventDate' => $event->event_date?->toDateString(),
                'timezone' => $event->timezone,
                'graceEndsAt' => $event->grace_ends_at?->toIso8601String(),
                'uploadWindowStartsAt' => $event->upload_window_starts_at?->toIso8601String(),
                'uploadWindowEndsAt' => $event->upload_window_ends_at?->toIso8601String(),
                'albumPublic' => $event->album_public,
                'allowedMediaTypes' => $this->allowedMediaTypes($event),
                'moderationEnabled' => $event->moderation_enabled,
                'autoModerationEnabled' => $event->auto_moderation_enabled,
                'billing' => [
                    'planId' => $event->plan_id,
                    'planName' => $event->plan?->name ?? 'Custom plan',
                    'planPriceLabel' => $this->planPriceLabel($event->plan),
                    'planFeatures' => $planFeatures,
                    'isPaid' => $event->is_paid,
                    'paymentDueAt' => $event->payment_due_at?->toIso8601String(),
                    'paidAt' => $event->paid_at?->toIso8601String(),
                    'graceEndsAt' => $event->grace_ends_at?->toIso8601String(),
                    'retentionEndsAt' => $event->retention_ends_at?->toIso8601String(),
                    'note' => $canManageBilling ? $event->billing_note : null,
                    'statusCode' => $billingStatusCode,
                    'statusLabel' => $billingStatusLabel,
                    'statusHint' => $billingStatusHint,
                    'statusTone' => $billingStatusTone,
                    'isLocked' => $this->isPaymentLocked($event),
                    'canManage' => $canManageBilling,
                    'canCheckout' => $canCheckoutBilling,
                    'checkoutLabel' => $canCheckoutBilling ? "Pay {$this->planPriceLabel($event->plan)}" : null,
                    'checkoutHint' => $canCheckoutBilling
                        ? 'Complete payment online to unlock the paid retention window for this event.'
                        : null,
                    'storage' => $storageQuota,
                ],
                'moderationSummary' => $moderationSummary,
                'branding' => [
                    'primaryColor' => $branding['primary_color'] ?? null,
                    'accentColor' => $branding['accent_color'] ?? null,
                    'logoUrl' => $this->brandingLogoUrl($branding),
                    'welcomeMessage' => $branding['welcome_message'] ?? null,
                ],
                'mediaExport' => [
                    'status' => is_string($event->media_export_status) && $event->media_export_status !== ''
                        ? $event->media_export_status
                        : 'idle',
                    'requestedAt' => $event->media_export_requested_at?->toIso8601String(),
                    'startedAt' => $event->media_export_started_at?->toIso8601String(),
                    'completedAt' => $event->media_export_completed_at?->toIso8601String(),
                    'failedAt' => $event->media_export_failed_at?->toIso8601String(),
                    'error' => $event->media_export_error,
                ],
                'collaborators' => $collaborators,
                'settings' => [
                    'displayLanguage' => $branding['display_language'] ?? 'automatic',
                    'hideSideImages' => (bool) ($branding['hide_side_images'] ?? false),
                    'hideQrCode' => (bool) ($branding['hide_qr_code'] ?? false),
                    'hideCaption' => (bool) ($branding['hide_caption'] ?? false),
                    'captionTheme' => $captionTheme,
                    'disableGuestDownload' => (bool) ($branding['disable_guest_download'] ?? false),
                    'welcomeScreenEnabled' => (bool) ($branding['welcome_screen_enabled'] ?? false),
                    'welcomeScreenTitle' => (string) ($branding['welcome_message'] ?? ''),
                    'welcomeScreenSubtitle' => (string) ($branding['welcome_screen_subtitle'] ?? ''),
                    'welcomeScreenButtonText' => (string) ($branding['welcome_screen_button_text'] ?? 'Continue'),
                    'welcomeScreenFont' => (string) ($branding['welcome_screen_font'] ?? 'montserrat'),
                    'welcomeScreenAnimated' => (bool) ($branding['welcome_screen_animated'] ?? false),
                    'welcomeScreenBackgroundUrl' => $this->brandingWelcomeScreenBackgroundUrl($branding),
                    'welcomeScreenCollectName' => (bool) ($branding['welcome_screen_collect_name'] ?? true),
                    'welcomeScreenCollectEmail' => (bool) ($branding['welcome_screen_collect_email'] ?? false),
                    'welcomeScreenCollectPhone' => (bool) ($branding['welcome_screen_collect_phone'] ?? false),
                    'welcomeScreenFields' => $this->normalizeWelcomeScreenFields($branding['welcome_screen_fields'] ?? []),
                    'weddingDetails' => $this->weddingDetailsPayload($branding['wedding_details'] ?? []),
                    'albumBackgroundEnabled' => (bool) ($branding['album_background_enabled'] ?? false),
                    'albumBackgroundMode' => $this->albumBackgroundMode($branding),
                    'albumBackgroundColor' => (string) ($branding['album_background_color'] ?? '#0F172A'),
                    'albumBackgroundPresetThemeId' => $this->albumBackgroundPresetThemeId($branding),
                    'albumBackgroundImageUrl' => $this->effectiveAlbumBackgroundUrl($event, $branding),
                    'textPostsBackgroundsEnabled' => (bool) ($branding['text_posts_backgrounds_enabled'] ?? false),
                    'textPostsBackgroundPalette' => $this->textPostsBackgroundPalette($branding),
                    'albumPermission' => $albumPermission,
                    'allowedMediaTypes' => $this->allowedMediaTypes($event),
                    'moderationFilters' => $this->moderationFilters($branding),
                ],
            ],
            'eventLinks' => [
                'accountDashboard' => $eventOverviewUrl,
                'dashboard' => route('events.show', $event),
                'guests' => route('events.guests', $event),
                'guestReport' => route('events.guests.report', $event),
                'media' => route('events.media', $event),
                'mediaExportStart' => route('events.exports.media.start', $event),
                'mediaExportDownload' => route('events.exports.media.download', $event),
                'settings' => route('events.settings', $event),
                'settingsUpdate' => route('events.settings.update', $event),
                'guestPartiesStore' => route('events.guests.store', $event),
                'guestPartiesImport' => route('events.guests.import', $event),
                'guestInvitationsBulkUpdate' => route('events.guests.invitations.bulk-update', $event),
                'invitationSettingsUpdate' => route('events.guests.invitation-settings.update', $event),
                'tablesStore' => route('events.tables.store', $event),
                'publicGuestList' => route('events.guests.public-list.show', $event->share_token),
                'billingUpdate' => route('events.billing.update', $event),
                'billingCheckout' => route('events.billing.checkout', $event),
                'businessActivate' => route('dashboard.business.activate'),
                'collaboratorsStore' => route('events.collaborators.store', $event),
                'album' => $albumUrl,
                'albumShortUrl' => $publicShortLinks['albumShortUrl'],
                'albumAccessCode' => $event->publicAlbumCode(),
                'albumEntry' => route('events.album.access.show'),
                'albumEntryShortcut' => 'https://is.gd/evsmrt',
                'wall' => route('events.wall', $event->publicAlbumCode()),
                'wallShortUrl' => $publicShortLinks['wallShortUrl'],
                'albumQrDataUrl' => $this->createQrCodeDataUrl($albumUrl),
                'wallQrDataUrl' => $this->createQrCodeDataUrl(route('events.wall', $event->publicAlbumCode())),
            ],
            'availableBillingPlans' => $this->billingPlanOptions(),
            'eventNavigation' => array_values(array_filter([
                ['title' => 'Workspace', 'href' => route('events.show', $event)],
                ['title' => 'Media', 'href' => route('events.media', $event)],
                ['title' => 'Guests', 'href' => route('events.guests', $event)],
                ['title' => 'Settings', 'href' => route('events.settings', $event)],
            ])),
            'backNavigation' => $eventOverviewUrl !== null
                ? ['title' => 'Events', 'href' => $eventOverviewUrl]
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardProps(Event $event): array
    {
        $assetBaseQuery = EventAsset::query()->where('event_id', $event->id);

        return [
            'dashboardStats' => [
                'guestCount' => EventGuest::query()
                    ->where('event_id', $event->id)
                    ->count(),
                'photoCount' => (clone $assetBaseQuery)
                    ->where('kind', 'photo')
                    ->count(),
                'videoCount' => (clone $assetBaseQuery)
                    ->where('kind', 'video')
                    ->count(),
                'textCount' => (clone $assetBaseQuery)
                    ->where('kind', 'text')
                    ->count(),
                'approvedCount' => (clone $assetBaseQuery)
                    ->where('moderation_status', 'approved')
                    ->count(),
                'rejectedCount' => (clone $assetBaseQuery)
                    ->where('moderation_status', 'rejected')
                    ->count(),
                'storageRemainingBytes' => max(0, $event->storage_limit_bytes - $event->storage_used_bytes),
                'uploadRemaining' => max(0, $event->upload_limit - $event->upload_count),
                'lastUploadAt' => (clone $assetBaseQuery)
                    ->latest('id')
                    ->value('created_at'),
            ],
            'dashboardRecentUploads' => EventAsset::query()
                ->where('event_id', $event->id)
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(function (EventAsset $asset): array {
                    $metadata = is_array($asset->metadata) ? $asset->metadata : [];

                    return [
                        'id' => $asset->id,
                        'kind' => $asset->kind,
                        'guestName' => is_string($metadata['guest_name'] ?? null) && trim((string) $metadata['guest_name']) !== ''
                            ? trim((string) $metadata['guest_name'])
                            : 'Guest',
                        'message' => is_string($metadata['message'] ?? null) ? $metadata['message'] : null,
                        'text' => is_string($metadata['text'] ?? null) ? $metadata['text'] : null,
                        'moderationStatus' => $asset->moderation_status,
                        'createdAt' => $asset->created_at?->toIso8601String(),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function guestPartyProps(Event $event): array
    {
        $publicInvitationToken = $this->ensurePublicInvitationToken($event);
        $invitationSettings = $this->eventInvitationSettings($event);
        $branding = $this->resolvedEventBranding($event);
        $guestParties = $event->guestParties()
            ->with(['invitationActivities', 'table'])
            ->orderBy('name')
            ->get();
        $eventTables = $event->tables()
            ->with('guestParties')
            ->get();
        $stats = $this->guestPartyStatsPayload($event, $guestParties);
        $report = $this->guestPartyReportPayload($guestParties);

        return [
            'eventInvitationSettings' => $invitationSettings,
            'publicInvitationUrl' => route('events.guests.public-invitation.show', $publicInvitationToken),
            'publicGuestListUrl' => route('events.guests.public-list.show', $event->share_token),
            'guestLedgerExportUrl' => route('events.guests.export', $event),
            'eventTables' => $eventTables
                ->map(fn (EventTable $eventTable): array => [
                    'id' => $eventTable->id,
                    'name' => $eventTable->name,
                    'seatsCount' => $eventTable->seats_count,
                    'occupiedSeats' => $this->eventTableOccupiedSeats($eventTable),
                    'remainingSeats' => max(0, $eventTable->seats_count - $this->eventTableOccupiedSeats($eventTable)),
                    'isFull' => $this->eventTableOccupiedSeats($eventTable) >= $eventTable->seats_count,
                    'updateUrl' => route('events.tables.update', [$event, $eventTable]),
                    'deleteUrl' => route('events.tables.destroy', [$event, $eventTable]),
                ])
                ->values()
                ->all(),
            'invitationPreview' => [
                'eventDetails' => [
                    'dateLabel' => $this->invitationPrimaryDateLabel($event),
                    'venueAddress' => $this->invitationVenueAddress($event),
                    'weddingDetails' => $this->weddingDetailsPayload($branding['wedding_details'] ?? []),
                ],
                'branding' => [
                    'logoUrl' => $this->brandingLogoUrl($branding),
                ],
            ],
            'guestPartyStats' => $stats,
            'guestReport' => $report,
            'guestParties' => $guestParties
                ->map(fn (EventGuestParty $party): array => [
                    'id' => $party->id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'eventTableId' => $party->event_table_id,
                    'tableName' => $party->table_name,
                    'invitedAttendeesCount' => $party->invited_attendees_count,
                    'confirmedAttendeesCount' => $party->confirmed_attendees_count,
                    'attendanceStatus' => $party->attendance_status,
                    'actualAttendeesCount' => $party->actual_attendees_count,
                    'actualAttendanceStatus' => $party->actual_attendance_status,
                    'actualAttendanceRecordedAt' => $party->actual_attendance_recorded_at?->toIso8601String(),
                    'notes' => $party->notes,
                    'invitationStatus' => $party->invitation_status,
                    'invitationDeliveryChannel' => $party->invitation_delivery_channel,
                    'invitationDeliveredAt' => $party->invitation_delivered_at?->toIso8601String(),
                    'invitationOpenCount' => $party->invitation_open_count,
                    'invitationFirstOpenedAt' => $party->invitation_first_opened_at?->toIso8601String(),
                    'invitationLastOpenedAt' => $party->invitation_last_opened_at?->toIso8601String(),
                    'invitationLastOpenedIp' => $party->invitation_last_opened_ip,
                    'respondedAt' => $party->responded_at?->toIso8601String(),
                    'reminderCount' => $party->invitationActivities->where('activity_type', 'reminded')->count(),
                    'lastReminderAt' => $party->invitationActivities
                        ->first(fn (EventGuestPartyInvitationActivity $activity): bool => $activity->activity_type === 'reminded')
                        ?->created_at
                        ?->toIso8601String(),
                    'invitationHistory' => $party->invitationActivities
                        ->take(8)
                        ->map(fn (EventGuestPartyInvitationActivity $activity): array => [
                            'type' => $activity->activity_type,
                            'deliveryChannel' => $activity->delivery_channel,
                            'createdAt' => $activity->created_at?->toIso8601String(),
                            'meta' => is_array($activity->meta) ? $activity->meta : [],
                        ])
                        ->values()
                        ->all(),
                    'giftType' => $party->gift_type,
                    'giftCurrency' => $party->gift_currency,
                    'giftAmount' => $party->gift_amount !== null ? (string) $party->gift_amount : null,
                    'guestNames' => $party->guest_names,
                    'mealPreference' => $party->meal_preference,
                    'responseNotes' => $party->response_notes,
                    'inviteUrl' => route('events.guests.invitation.show', $this->ensureGuestPartyInvitationToken($party)),
                    'publicCheckInUrl' => route('events.guests.public-list.update', [$event->share_token, $party]),
                    'updateUrl' => route('events.guests.update', [$event, $party]),
                    'deleteUrl' => route('events.guests.destroy', [$event, $party]),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, EventGuestParty>  $guestParties
     * @return array{
     *   partyCount: int,
     *   invitedAttendeesCount: int,
     *   confirmedAttendeesCount: int,
     *   actualAttendeesCount: int,
     *   acceptedPartyCount: int,
     *   pendingPartyCount: int,
     *   declinedPartyCount: int,
     *   presentPartyCount: int,
     *   absentPartyCount: int,
     *   moneyGiftTotal: float,
     *   moneyGiftCurrency: string
     * }
     */
    private function guestPartyStatsPayload(Event $event, Collection $guestParties): array
    {
        $moneyGiftTotal = $guestParties
            ->filter(fn (EventGuestParty $party): bool => $party->gift_type === 'money' && $party->gift_amount !== null)
            ->reduce(fn (float $carry, EventGuestParty $party): float => $carry + (float) $party->gift_amount, 0.0);

        return [
            'partyCount' => $guestParties->count(),
            'invitedAttendeesCount' => $guestParties->sum('invited_attendees_count'),
            'confirmedAttendeesCount' => $guestParties->sum(
                fn (EventGuestParty $party): int => (int) ($party->confirmed_attendees_count ?? 0),
            ),
            'actualAttendeesCount' => $guestParties->sum(
                fn (EventGuestParty $party): int => (int) ($party->actual_attendees_count ?? 0),
            ),
            'acceptedPartyCount' => $guestParties->where('attendance_status', 'accepted')->count(),
            'pendingPartyCount' => $guestParties->where('attendance_status', 'pending')->count(),
            'declinedPartyCount' => $guestParties->where('attendance_status', 'declined')->count(),
            'presentPartyCount' => $guestParties->where('actual_attendance_status', 'present')->count(),
            'absentPartyCount' => $guestParties->where('actual_attendance_status', 'absent')->count(),
            'moneyGiftTotal' => round($moneyGiftTotal, 2),
            'moneyGiftCurrency' => $event->currency ?: 'EUR',
        ];
    }

    /**
     * @param  Collection<int, EventGuestParty>  $guestParties
     * @return array<string, mixed>
     */
    private function guestPartyReportPayload(Collection $guestParties): array
    {
        $deliveredStatuses = ['delivered_in_person', 'sent', 'opened', 'responded'];
        $sentStatuses = ['sent', 'opened', 'responded'];
        $openedParties = $guestParties->filter(
            fn (EventGuestParty $party): bool => $party->invitation_open_count > 0 || in_array($party->invitation_status, ['opened', 'responded'], true),
        );
        $respondedParties = $guestParties->filter(
            fn (EventGuestParty $party): bool => $party->responded_at !== null || $party->invitation_status === 'responded',
        );
        $giftRecordedParties = $guestParties->filter(
            fn (EventGuestParty $party): bool => $party->gift_type !== null,
        );
        $acceptedParties = $guestParties->where('attendance_status', 'accepted');

        return [
            'deliveredPartyCount' => $guestParties->whereIn('invitation_status', $deliveredStatuses)->count(),
            'sentOnlinePartyCount' => $guestParties->filter(
                fn (EventGuestParty $party): bool => in_array($party->invitation_status, $sentStatuses, true)
                    || in_array($party->invitation_delivery_channel, ['whatsapp', 'facebook', 'public_link', 'other'], true),
            )->count(),
            'openedPartyCount' => $openedParties->count(),
            'respondedPartyCount' => $respondedParties->count(),
            'giftRecordedPartyCount' => $giftRecordedParties->count(),
            'presentPartyCount' => $guestParties->where('actual_attendance_status', 'present')->count(),
            'absentPartyCount' => $guestParties->where('actual_attendance_status', 'absent')->count(),
            'responseRate' => $guestParties->count() > 0
                ? round(($respondedParties->count() / $guestParties->count()) * 100, 1)
                : 0.0,
            'attendanceFillRate' => $guestParties->sum('invited_attendees_count') > 0
                ? round(($guestParties->sum(fn (EventGuestParty $party): int => (int) ($party->confirmed_attendees_count ?? 0)) / $guestParties->sum('invited_attendees_count')) * 100, 1)
                : 0.0,
            'actualAttendanceFillRate' => $guestParties->sum('invited_attendees_count') > 0
                ? round(($guestParties->sum(fn (EventGuestParty $party): int => (int) ($party->actual_attendees_count ?? 0)) / $guestParties->sum('invited_attendees_count')) * 100, 1)
                : 0.0,
            'averageMoneyGiftPerAcceptedParty' => $acceptedParties->count() > 0
                ? round(
                    $acceptedParties
                        ->filter(fn (EventGuestParty $party): bool => $party->gift_type === 'money' && $party->gift_amount !== null)
                        ->sum(fn (EventGuestParty $party): float => (float) $party->gift_amount) / $acceptedParties->count(),
                    2,
                )
                : 0.0,
            'moneyGiftTotals' => $this->guestMoneyGiftTotals($guestParties),
            'recentResponses' => $respondedParties
                ->sortByDesc(fn (EventGuestParty $party): int => $party->responded_at?->getTimestamp() ?? 0)
                ->take(8)
                ->values()
                ->map(fn (EventGuestParty $party): array => [
                    'name' => $party->name,
                    'attendanceStatus' => $party->attendance_status,
                    'confirmedAttendeesCount' => $party->confirmed_attendees_count,
                    'actualAttendanceStatus' => $party->actual_attendance_status,
                    'actualAttendeesCount' => $party->actual_attendees_count,
                    'respondedAt' => $party->responded_at?->toIso8601String(),
                    'mealPreference' => $party->meal_preference,
                    'responseNotes' => $party->response_notes,
                ])
                ->all(),
            'recentInvitationOpens' => $openedParties
                ->sortByDesc(fn (EventGuestParty $party): int => $party->invitation_last_opened_at?->getTimestamp() ?? 0)
                ->take(8)
                ->values()
                ->map(fn (EventGuestParty $party): array => [
                    'name' => $party->name,
                    'invitationOpenCount' => $party->invitation_open_count,
                    'invitationLastOpenedAt' => $party->invitation_last_opened_at?->toIso8601String(),
                    'invitationLastOpenedIp' => $party->invitation_last_opened_ip,
                    'invitationDeliveryChannel' => $party->invitation_delivery_channel,
                ])
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, EventGuestParty>  $guestParties
     * @return array<int, array{currency: string, amount: float}>
     */
    private function guestMoneyGiftTotals(Collection $guestParties): array
    {
        return $guestParties
            ->filter(fn (EventGuestParty $party): bool => $party->gift_type === 'money' && $party->gift_amount !== null && $party->gift_currency !== null)
            ->groupBy(fn (EventGuestParty $party): string => (string) $party->gift_currency)
            ->map(fn (Collection $parties, string $currency): array => [
                'currency' => $currency,
                'amount' => round($parties->sum(fn (EventGuestParty $party): float => (float) $party->gift_amount), 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{
     *   template: string,
     *   headline: string,
     *   message: string,
     *   closing: string,
     *   contactPhone: string|null,
     *   publicRsvpEnabled: bool
     * }
     */
    private function eventInvitationSettings(Event $event): array
    {
        $settings = is_array($event->invitation_settings) ? $event->invitation_settings : [];
        $template = is_string($settings['template'] ?? null) ? $settings['template'] : 'classic';

        if (! in_array($template, ['classic', 'floral', 'midnight', 'canva_cream', 'canva_brown', 'canva_watercolor'], true)) {
            $template = 'classic';
        }

        return [
            'template' => $template,
            'headline' => is_string($settings['headline'] ?? null) && trim((string) $settings['headline']) !== ''
                ? trim((string) $settings['headline'])
                : $event->name,
            'message' => is_string($settings['message'] ?? null) && trim((string) $settings['message']) !== ''
                ? trim((string) $settings['message'])
                : 'We would love to celebrate together. Please let us know if you can join us and how many will attend.',
            'closing' => is_string($settings['closing'] ?? null) && trim((string) $settings['closing']) !== ''
                ? trim((string) $settings['closing'])
                : 'Please answer when you can so we can plan every seat with care.',
            'contactPhone' => is_string($settings['contact_phone'] ?? null) && trim((string) $settings['contact_phone']) !== ''
                ? trim((string) $settings['contact_phone'])
                : null,
            'publicRsvpEnabled' => (bool) ($settings['public_rsvp_enabled'] ?? true),
        ];
    }

    private function ensurePublicInvitationToken(Event $event): string
    {
        $token = is_string($event->public_invitation_token ?? null) ? trim((string) $event->public_invitation_token) : '';

        if ($token !== '') {
            return $token;
        }

        $token = Str::lower((string) Str::uuid());
        $event->forceFill([
            'public_invitation_token' => $token,
        ])->save();

        return $token;
    }

    private function ensureGuestPartyInvitationToken(EventGuestParty $guestParty): string
    {
        $token = is_string($guestParty->invitation_token ?? null) ? trim((string) $guestParty->invitation_token) : '';

        if ($token !== '') {
            return $token;
        }

        $token = Str::lower((string) Str::uuid());
        $guestParty->forceFill([
            'invitation_token' => $token,
        ])->save();

        return $token;
    }

    private function matchPublicInvitationGuestParty(Event $event, string $name, ?string $phone): ?EventGuestParty
    {
        if ($phone !== null && trim($phone) !== '') {
            $party = EventGuestParty::query()
                ->where('event_id', $event->id)
                ->where('phone', $phone)
                ->first();

            if ($party instanceof EventGuestParty) {
                return $party;
            }
        }

        return EventGuestParty::query()
            ->where('event_id', $event->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();
    }

    private function recordInvitationOpen(
        Request $request,
        Event $event,
        ?EventGuestParty $guestParty,
        string $invitationKind,
    ): void {
        $openedAt = now();

        EventGuestPartyInvitationView::query()->create([
            'event_id' => $event->id,
            'event_guest_party_id' => $guestParty?->id,
            'invitation_kind' => $invitationKind,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'opened_at' => $openedAt,
        ]);

        if (! $guestParty instanceof EventGuestParty) {
            return;
        }

        $guestParty->forceFill([
            'invitation_open_count' => (int) $guestParty->invitation_open_count + 1,
            'invitation_first_opened_at' => $guestParty->invitation_first_opened_at ?? $openedAt,
            'invitation_last_opened_at' => $openedAt,
            'invitation_last_opened_ip' => $request->ip(),
            'invitation_last_opened_user_agent' => $request->userAgent(),
            'invitation_status' => $guestParty->invitation_status === 'responded' ? 'responded' : 'opened',
        ])->save();

        $this->recordInvitationActivity(
            $guestParty,
            'opened',
            $guestParty->invitation_delivery_channel,
            [
                'invitationKind' => $invitationKind,
            ],
            $request,
            $openedAt,
        );
    }

    private function invitationStatusForDeliveryAction(
        EventGuestParty $guestParty,
        string $action,
    ): string {
        if ($guestParty->invitation_status === 'responded') {
            return 'responded';
        }

        if ($guestParty->invitation_status === 'opened') {
            return 'opened';
        }

        return match ($action) {
            'mark_delivered_in_person' => 'delivered_in_person',
            'mark_sent_online', 'mark_reminded_online' => 'sent',
            default => $guestParty->invitation_status,
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordInvitationActivity(
        EventGuestParty $guestParty,
        string $activityType,
        ?string $deliveryChannel = null,
        array $meta = [],
        ?Request $request = null,
        ?CarbonInterface $recordedAt = null,
    ): void {
        $timestamp = $recordedAt ?? now();

        EventGuestPartyInvitationActivity::query()->create([
            'event_id' => $guestParty->event_id,
            'event_guest_party_id' => $guestParty->id,
            'actor_user_id' => $request?->user()?->id,
            'activity_type' => $activityType,
            'delivery_channel' => $deliveryChannel,
            'meta' => $meta === [] ? null : $meta,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }

    private function notifyEventOwnerAboutInvitationResponse(
        EventGuestParty $guestParty,
        string $previousAttendanceStatus,
    ): void {
        $event = $guestParty->event;

        if (! $event instanceof Event) {
            return;
        }

        $event->loadMissing('user');
        $owner = $event->user;

        if (! $owner instanceof User || blank($owner->email)) {
            return;
        }

        $changeType = match ($guestParty->attendance_status) {
            'accepted' => $previousAttendanceStatus === 'accepted' ? 'updated' : 'accepted',
            'declined' => $previousAttendanceStatus === 'declined' ? 'updated' : 'declined',
            default => 'updated',
        };

        $acceptedPartyCount = (int) $event->guestParties()->where('attendance_status', 'accepted')->count();
        $declinedPartyCount = (int) $event->guestParties()->where('attendance_status', 'declined')->count();
        $pendingPartyCount = (int) $event->guestParties()->where('attendance_status', 'pending')->count();
        $confirmedAttendeeTotal = (int) $event->guestParties()
            ->where('attendance_status', 'accepted')
            ->sum('confirmed_attendees_count');

        $owner->notify(new EventGuestInvitationResponseNotification([
            'eventName' => (string) $event->name,
            'guestPartyName' => (string) $guestParty->name,
            'attendanceStatus' => (string) $guestParty->attendance_status,
            'changeType' => $changeType,
            'confirmedAttendeesCount' => $guestParty->confirmed_attendees_count === null
                ? null
                : (int) $guestParty->confirmed_attendees_count,
            'pendingPartyCount' => $pendingPartyCount,
            'acceptedPartyCount' => $acceptedPartyCount,
            'declinedPartyCount' => $declinedPartyCount,
            'confirmedAttendeeTotal' => $confirmedAttendeeTotal,
            'mealPreference' => $guestParty->meal_preference,
            'guestNames' => $guestParty->guest_names,
            'responseNotes' => $guestParty->response_notes,
            'guestListUrl' => route('events.guests', $event),
        ]));
    }

    private function resolveEventTableForGuestParty(Event $event, mixed $eventTableId): ?EventTable
    {
        if (! is_int($eventTableId) || $eventTableId <= 0) {
            return null;
        }

        $eventTable = $event->tables()->whereKey($eventTableId)->first();
        if (! $eventTable instanceof EventTable) {
            throw ValidationException::withMessages([
                'event_table_id' => 'Choose a valid table for this event.',
            ]);
        }

        return $eventTable;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertTableHasRoomForGuestParty(
        ?EventTable $eventTable,
        array $payload,
        ?EventGuestParty $existingGuestParty = null,
    ): void {
        if (! $eventTable instanceof EventTable) {
            return;
        }

        $requestedSeats = max(1, (int) ($payload['confirmed_attendees_count']
            ?? $payload['invited_attendees_count']
            ?? 1));

        if (($payload['actual_attendance_status'] ?? null) === 'present') {
            $requestedSeats = max(1, (int) ($payload['actual_attendees_count']
                ?? $payload['confirmed_attendees_count']
                ?? $payload['invited_attendees_count']
                ?? 1));
        }

        if (($payload['actual_attendance_status'] ?? null) === 'absent') {
            $requestedSeats = 0;
        }

        $occupiedSeats = $this->eventTableOccupiedSeats($eventTable, $existingGuestParty);

        if (($occupiedSeats + $requestedSeats) > $eventTable->seats_count) {
            throw ValidationException::withMessages([
                'event_table_id' => 'This table is already full.',
            ]);
        }
    }

    private function eventTableOccupiedSeats(
        EventTable $eventTable,
        ?EventGuestParty $excludingGuestParty = null,
    ): int {
        return (int) $eventTable->guestParties()
            ->when(
                $excludingGuestParty instanceof EventGuestParty,
                fn ($query) => $query->whereKeyNot($excludingGuestParty->id),
            )
            ->get()
            ->sum(function (EventGuestParty $guestParty): int {
                if ($guestParty->actual_attendance_status === 'present') {
                    return max(
                        1,
                        (int) ($guestParty->actual_attendees_count
                            ?? $guestParty->confirmed_attendees_count
                            ?? $guestParty->invited_attendees_count
                            ?? 1),
                    );
                }

                if ($guestParty->actual_attendance_status === 'absent') {
                    return 0;
                }

                return max(
                    1,
                    (int) ($guestParty->confirmed_attendees_count ?? $guestParty->invited_attendees_count ?? 1),
                );
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function publicGuestListProps(Event $event): array
    {
        $guestParties = $event->guestParties()
            ->orderBy('name')
            ->get();
        $eventTables = $event->tables()
            ->with('guestParties')
            ->get();

        return [
            'currentEvent' => [
                'id' => $event->id,
                'name' => $event->name,
            ],
            'guestList' => [
                'searchPlaceholder' => 'Search invitee, phone, or table',
                'publicUrl' => route('events.guests.public-list.show', $event->share_token),
            ],
            'eventTables' => $eventTables
                ->map(fn (EventTable $eventTable): array => [
                    'id' => $eventTable->id,
                    'name' => $eventTable->name,
                    'remainingSeats' => max(0, $eventTable->seats_count - $this->eventTableOccupiedSeats($eventTable)),
                    'isFull' => $this->eventTableOccupiedSeats($eventTable) >= $eventTable->seats_count,
                ])
                ->values()
                ->all(),
            'guestParties' => $guestParties
                ->map(fn (EventGuestParty $party): array => [
                    'id' => $party->id,
                    'name' => $party->name,
                    'phone' => $party->phone,
                    'eventTableId' => $party->event_table_id,
                    'tableName' => $party->table_name,
                    'invitedAttendeesCount' => $party->invited_attendees_count,
                    'confirmedAttendeesCount' => $party->confirmed_attendees_count,
                    'actualAttendanceStatus' => $party->actual_attendance_status,
                    'actualAttendeesCount' => $party->actual_attendees_count,
                    'notes' => $party->notes,
                    'updateUrl' => route('events.guests.public-list.update', [$event->share_token, $party]),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function invitationPageProps(
        Request $request,
        Event $event,
        ?EventGuestParty $guestParty,
        bool $isPublicInvite,
    ): array {
        $branding = $this->resolvedEventBranding($event);
        app()->setLocale(FrontendLocalization::resolveEventLocale($request, $branding));
        $settings = $this->eventInvitationSettings($event);
        $token = $isPublicInvite
            ? $this->ensurePublicInvitationToken($event)
            : $this->ensureGuestPartyInvitationToken($guestParty);

        return [
            'eventName' => $event->name,
            'eventType' => $event->type,
            'submitted' => $request->boolean('submitted'),
            'isPublicInvite' => $isPublicInvite,
            'guestParty' => $guestParty instanceof EventGuestParty
                ? [
                    'name' => $guestParty->name,
                    'phone' => $guestParty->phone,
                    'invitedAttendeesCount' => $guestParty->invited_attendees_count,
                    'confirmedAttendeesCount' => $guestParty->confirmed_attendees_count,
                    'attendanceStatus' => $guestParty->attendance_status,
                    'guestNames' => $guestParty->guest_names,
                    'mealPreference' => $guestParty->meal_preference,
                    'responseNotes' => $guestParty->response_notes,
                ]
                : null,
            'invitation' => [
                'template' => $settings['template'],
                'headline' => $settings['headline'],
                'message' => $settings['message'],
                'closing' => $settings['closing'],
                'contactPhone' => $settings['contactPhone'],
            ],
            'eventDetails' => [
                'dateLabel' => $this->invitationPrimaryDateLabel($event),
                'venueAddress' => $this->invitationVenueAddress($event),
                'weddingDetails' => $this->weddingDetailsPayload($branding['wedding_details'] ?? []),
                'timezone' => $event->timezone,
                'moments' => $this->invitationMoments($event),
            ],
            'links' => [
                'respond' => $isPublicInvite
                    ? route('events.guests.public-invitation.respond', $token)
                    : route('events.guests.invitation.respond', $token),
                'album' => $this->publicAlbumUrl($event),
            ],
            'branding' => [
                'primaryColor' => (string) ($branding['primary_color'] ?? '#0F172A'),
                'accentColor' => (string) ($branding['accent_color'] ?? '#D97706'),
                'logoUrl' => $this->brandingLogoUrl($branding),
                'albumBackgroundMode' => $this->albumBackgroundMode($branding),
                'albumBackgroundColor' => (string) ($branding['album_background_color'] ?? '#0F172A'),
                'albumBackgroundImageUrl' => $this->effectiveAlbumBackgroundUrl($event, $branding),
            ],
            'appName' => config('app.name'),
            'showPoweredBy' => ! $this->eventRemovesAppBranding($event),
        ];
    }

    private function invitationPrimaryDateLabel(Event $event): string
    {
        $date = $event->event_date;

        if ($date instanceof CarbonInterface) {
            return $date->setTimezone($event->timezone)->isoFormat('dddd, D MMMM YYYY');
        }

        return 'Date to be confirmed';
    }

    private function invitationVenueAddress(Event $event): ?string
    {
        $venueAddress = is_string($event->venue_address ?? null) ? trim((string) $event->venue_address) : '';
        $subEvents = collect(is_array($event->sub_events) ? $event->sub_events : [])
            ->filter(fn (mixed $subEvent): bool => is_array($subEvent));

        if ($event->type === 'wedding' && $subEvents->isNotEmpty()) {
            $receptionAddress = $subEvents
                ->first(function (array $subEvent): bool {
                    $key = Str::lower((string) ($subEvent['key'] ?? ''));
                    $label = Str::lower((string) ($subEvent['label'] ?? ''));

                    return in_array($key, ['reception', 'party'], true)
                        || str_contains($label, 'reception')
                        || $label === 'party';
                });

            if (
                is_array($receptionAddress)
                && is_string($receptionAddress['address'] ?? null)
                && trim((string) $receptionAddress['address']) !== ''
            ) {
                return trim((string) $receptionAddress['address']);
            }
        }

        if ($venueAddress !== '') {
            return $venueAddress;
        }

        return $subEvents
            ->map(fn (mixed $subEvent): ?string => is_array($subEvent) && is_string($subEvent['address'] ?? null)
                ? trim((string) $subEvent['address'])
                : null)
            ->filter(fn (?string $address): bool => is_string($address) && $address !== '')
            ->first();
    }

    /**
     * @return array<int, array{label: string, date: string, time: string, address: string, mapsUrl: string|null}>
     */
    private function invitationMoments(Event $event): array
    {
        $timezone = $event->timezone;
        $subEvents = is_array($event->sub_events) ? $event->sub_events : [];

        if ($subEvents !== []) {
            return collect($subEvents)
                ->filter(fn (mixed $subEvent): bool => is_array($subEvent))
                ->map(function (array $subEvent) use ($timezone): array {
                    $date = is_string($subEvent['date'] ?? null) ? $subEvent['date'] : null;
                    $startTime = is_string($subEvent['start_time'] ?? null) ? $subEvent['start_time'] : null;
                    $normalizedAddress = is_string($subEvent['address'] ?? null) && trim((string) $subEvent['address']) !== ''
                        ? trim((string) $subEvent['address'])
                        : null;
                    $address = $normalizedAddress ?? 'Address not needed';

                    $dateLabel = $date !== null
                        ? CarbonImmutable::parse($date, $timezone)->isoFormat('ddd, D MMM')
                        : 'Date pending';

                    return [
                        'label' => is_string($subEvent['label'] ?? null) ? (string) $subEvent['label'] : 'Event moment',
                        'date' => $dateLabel,
                        'time' => $startTime !== null && trim($startTime) !== '' ? $startTime : 'Time pending',
                        'address' => $address,
                        'mapsUrl' => $normalizedAddress !== null ? $this->mapsSearchUrl($normalizedAddress) : null,
                    ];
                })
                ->values()
                ->all();
        }

        return [[
            'label' => 'Main event',
            'date' => $this->invitationPrimaryDateLabel($event),
            'time' => 'Time pending',
            'address' => $this->invitationVenueAddress($event) ?? 'Address pending',
            'mapsUrl' => $this->invitationVenueAddress($event) !== null
                ? $this->mapsSearchUrl((string) $this->invitationVenueAddress($event))
                : null,
        ]];
    }

    private function mapsSearchUrl(string $address): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($address);
    }

    /**
     * @return array<string, mixed>
     */
    private function mediaProps(Event $event): array
    {
        $event->load([
            'assets' => fn ($query) => $query
                ->withCount(['likes', 'comments'])
                ->latest('id')
                ->limit(self::ADMIN_MEDIA_FULL_SCAN_LIMIT),
        ]);

        $assets = $event->assets->map(function (EventAsset $asset) use ($event): array {
            $metadata = is_array($asset->metadata) ? $asset->metadata : [];
            $guestName = is_string($metadata['guest_name'] ?? null) && trim((string) $metadata['guest_name']) !== ''
                ? trim((string) $metadata['guest_name'])
                : 'Guest';
            $moderationMetadata = is_array($metadata['moderation'] ?? null)
                ? $metadata['moderation']
                : [];

            return [
                'id' => $asset->id,
                'kind' => $asset->kind,
                'thumbnailUrl' => $this->assetThumbnailUrl($asset),
                'previewUrl' => $this->assetPreviewUrl($asset),
                'videoProcessing' => $this->videoVariantsPending($asset),
                'originalFilename' => $asset->original_filename,
                'mimeType' => $asset->mime_type,
                'sizeBytes' => $asset->size_bytes,
                'moderationStatus' => $asset->moderation_status,
                'moderationScore' => $asset->moderation_score,
                'moderationPipeline' => is_string($moderationMetadata['pipeline'] ?? null) ? $moderationMetadata['pipeline'] : null,
                'moderationMatches' => collect($moderationMetadata['matches'] ?? [])
                    ->filter(fn (mixed $match): bool => is_array($match) && is_string($match['category'] ?? null) && is_string($match['keyword'] ?? null))
                    ->map(fn (array $match): array => [
                        'category' => (string) $match['category'],
                        'keyword' => (string) $match['keyword'],
                    ])
                    ->values()
                    ->all(),
                'guestKey' => $this->mediaGuestKeyFromMetadata($metadata),
                'guestName' => $guestName,
                'guestEmail' => is_string($metadata['guest_email'] ?? null) ? $metadata['guest_email'] : null,
                'guestPhone' => is_string($metadata['guest_phone'] ?? null) ? $metadata['guest_phone'] : null,
                'message' => is_string($metadata['message'] ?? null) ? $metadata['message'] : null,
                'text' => is_string($metadata['text'] ?? null) ? $metadata['text'] : null,
                ...$this->textPostThemeAssetProps($metadata),
                'wallVisibility' => $this->assetWallVisibility($asset),
                'createdAt' => $asset->created_at?->toIso8601String(),
                'reviewedAt' => $asset->reviewed_at?->toIso8601String(),
                'commentCount' => (int) ($asset->comments_count ?? 0),
                'deleteUrl' => route('events.assets.destroy', [$event, $asset]),
                'moderationUpdateUrl' => route('events.assets.moderation.update', [$event, $asset]),
                'wallVisibilityUpdateUrl' => route('events.assets.wall-visibility.update', [$event, $asset]),
            ];
        })->values();

        $attendees = $assets
            ->groupBy(fn (array $asset): string => (string) $asset['guestKey'])
            ->map(function ($group, string $key): array {
                /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $group */
                $first = $group->first();
                $latestCreatedAt = $group
                    ->pluck('createdAt')
                    ->filter()
                    ->sortDesc()
                    ->first();

                return [
                    'key' => $key,
                    'guestName' => (string) ($first['guestName'] ?? 'Guest'),
                    'guestEmail' => $first['guestEmail'] ?? null,
                    'guestPhone' => $first['guestPhone'] ?? null,
                    'photoCount' => $group->where('kind', 'photo')->count(),
                    'videoCount' => $group->where('kind', 'video')->count(),
                    'textCount' => $group->where('kind', 'text')->count(),
                    'uploadCount' => $group->count(),
                    'latestCreatedAt' => $latestCreatedAt,
                    'assetIds' => $group->pluck('id')->all(),
                ];
            })
            ->sortByDesc(fn (array $attendee): string => (string) ($attendee['latestCreatedAt'] ?? ''))
            ->values()
            ->all();

        return [
            'mediaAssets' => $assets->all(),
            'mediaAttendees' => $attendees,
            'mediaBulkDeleteUrl' => route('events.assets.bulk-destroy', $event),
            'mediaBulkModerationUrl' => route('events.assets.bulk-moderation', $event),
        ];
    }

    /**
     * @param  array{byToken: \Illuminate\Support\Collection<string, EventGuest>, byName: \Illuminate\Support\Collection<string, EventGuest>}  $guestLookups
     * @return array{assets: array<int, array<string, mixed>>, nextCursor: int|null, hasMore: bool}
     */
    private function publicAlbumAssetPagePayload(
        Event $event,
        array $guestLookups,
        ?int $beforeCursor,
        int $stackLimit,
    ): array {
        $assets = collect();
        $selectedGroupKeys = [];
        $cursor = $beforeCursor;
        $chunkWasFull = false;

        do {
            $chunk = EventAsset::query()
                ->where('event_id', $event->id)
                ->where('moderation_status', 'approved')
                ->when($cursor !== null, fn ($query) => $query->where('id', '<', $cursor))
                ->withCount(['likes', 'comments'])
                ->latest('id')
                ->limit(self::PUBLIC_ALBUM_RAW_FETCH_CHUNK)
                ->get();

            $chunkWasFull = $chunk->count() === self::PUBLIC_ALBUM_RAW_FETCH_CHUNK;
            if ($chunk->isEmpty()) {
                break;
            }

            $assets = $assets->concat($chunk);
            $selectedGroupKeys = $assets
                ->map(fn (EventAsset $asset): string => $this->albumGalleryGroupKey($asset))
                ->unique()
                ->take($stackLimit)
                ->values()
                ->all();

            $cursor = $chunk->last()?->id;
        } while (count($selectedGroupKeys) < $stackLimit && $chunkWasFull && $cursor !== null);

        $groupKeyLookup = [];
        foreach ($assets as $asset) {
            $groupKeyLookup[$asset->id] = $this->albumGalleryGroupKey($asset);
        }

        $selectedKeySet = array_fill_keys($selectedGroupKeys, true);
        $pageAssets = $assets
            ->filter(fn (EventAsset $asset): bool => isset($selectedKeySet[$groupKeyLookup[$asset->id] ?? '']))
            ->values();

        $hasMore = $assets->count() > $pageAssets->count() || $chunkWasFull;
        $nextCursor = $hasMore ? $pageAssets->last()?->id : null;

        return [
            'assets' => $pageAssets
                ->map(fn (EventAsset $asset): array => $this->publicAlbumAssetProps($event, $asset, $guestLookups))
                ->all(),
            'nextCursor' => $nextCursor,
            'hasMore' => $hasMore && $nextCursor !== null,
        ];
    }

    private function assertOwnership(Request $request, Event $event): void
    {
        abort_unless($request->user()->id === $event->user_id, 403);
    }

    private function assertCanViewEvent(Request $request, Event $event): void
    {
        if ($request->user()->canAccessAdmin()) {
            return;
        }

        if ($request->user()->id === $event->user_id) {
            return;
        }

        abort_unless($this->activeCollaboratorMembership($request, $event) !== null, 403);
    }

    private function assertCanManageEvent(Request $request, Event $event): void
    {
        if ($request->user()->canAccessAdmin()) {
            return;
        }

        if ($request->user()->id === $event->user_id) {
            return;
        }

        $membership = $this->activeCollaboratorMembership($request, $event);
        abort_unless($membership !== null && $membership->role === 'manager', 403);
    }

    private function activeCollaboratorMembership(Request $request, Event $event): ?EventCollaborator
    {
        return EventCollaborator::query()
            ->where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'accepted'])
            ->first();
    }

    private function canPreviewInvitationAsManager(Request $request, Event $event): bool
    {
        if ($request->user() === null) {
            return false;
        }

        if ($request->user()->canAccessAdmin()) {
            return true;
        }

        if ($request->user()->id === $event->user_id) {
            return true;
        }

        $membership = $this->activeCollaboratorMembership($request, $event);

        return $membership !== null && $membership->role === 'manager';
    }

    private function shouldShowEventOverviewLink(Request $request): bool
    {
        if ($request->user()->canAccessAdmin() || $request->user()->canAccessBusinessDashboard()) {
            return true;
        }

        $ownedEventIds = $request->user()
            ->events()
            ->latest('id')
            ->limit(2)
            ->pluck('events.id');

        if ($ownedEventIds->count() > 1) {
            return true;
        }

        $collaboratorEventIds = EventCollaborator::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'accepted'])
            ->whereNotIn('event_id', $ownedEventIds->all())
            ->latest('id')
            ->limit(2)
            ->pluck('event_id');

        return $ownedEventIds->count() + $collaboratorEventIds->count() > 1;
    }

    private function eventOverviewUrl(Request $request): ?string
    {
        if (! $this->shouldShowEventOverviewLink($request)) {
            return null;
        }

        if ($request->user()->canAccessBusinessDashboard()) {
            return route('dashboard.business.events.index');
        }

        return route('dashboard');
    }

    private function guestPartyDuplicateKey(string $name, ?string $phone): string
    {
        return Str::lower(trim($name)).'|'.preg_replace('/\D+/', '', (string) $phone);
    }

    private function isUploadWindowOpen(Event $event): bool
    {
        if ($event->upload_window_starts_at === null || $event->upload_window_ends_at === null) {
            return false;
        }

        $now = now($event->timezone);

        return $now->betweenIncluded($event->upload_window_starts_at, $event->upload_window_ends_at);
    }

    private function isPaymentLocked(Event $event): bool
    {
        if ($event->is_paid) {
            return false;
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;
        if ($paymentDueAt === null) {
            return false;
        }

        return now($event->timezone)->gt($paymentDueAt);
    }

    /**
     * @return array<string, mixed>
     */
    private function planFeaturePayload(Event $event): array
    {
        return [
            'customizationTier' => $this->eventCustomizationTier($event),
            'allowsBetterCustomization' => $this->eventAllowsBetterCustomization($event),
            'allowsAdvancedCustomization' => $this->eventAllowsAdvancedCustomization($event),
            'allowsDownloadAll' => $this->eventCanDownloadAll($event),
            'allowsModerationTools' => $this->eventAllowsModerationTools($event),
            'removesAppBranding' => $this->eventRemovesAppBranding($event),
            'uploadWindowDays' => max(1, (int) $event->upload_window_days),
        ];
    }

    private function eventCustomizationTier(Event $event): string
    {
        $tier = trim((string) $event->customization_tier);

        return in_array($tier, ['basic', 'better', 'advanced'], true)
            ? $tier
            : 'basic';
    }

    private function eventAllowsBetterCustomization(Event $event): bool
    {
        return in_array($this->eventCustomizationTier($event), ['better', 'advanced'], true);
    }

    private function eventAllowsAdvancedCustomization(Event $event): bool
    {
        return $this->eventCustomizationTier($event) === 'advanced';
    }

    private function eventAllowsModerationTools(Event $event): bool
    {
        return (bool) $event->moderation_tools_enabled;
    }

    private function eventCanDownloadAll(Event $event): bool
    {
        return (bool) $event->download_all_enabled && (bool) $event->is_paid;
    }

    private function eventRemovesAppBranding(Event $event): bool
    {
        return (bool) $event->remove_app_branding;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedEventBranding(Event $event): array
    {
        $branding = is_array($event->branding) ? $event->branding : [];

        if (! $this->eventAllowsBetterCustomization($event)) {
            unset(
                $branding['logo_path'],
                $branding['logo_disk'],
                $branding['logo_url'],
            );
        }

        if (! $this->eventAllowsAdvancedCustomization($event)) {
            unset(
                $branding['primary_color'],
                $branding['accent_color'],
                $branding['album_background_path'],
                $branding['album_background_disk'],
                $branding['welcome_screen_background_path'],
                $branding['welcome_screen_background_disk'],
            );

            if (($branding['album_background_mode'] ?? null) === 'image') {
                $branding['album_background_mode'] = $this->albumBackgroundPresetThemeId($branding) !== null
                    ? 'preset'
                    : 'rotate';
            }
        }

        if (! $this->eventAllowsModerationTools($event)) {
            unset($branding['moderation_filters']);
        }

        return $branding;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function billingStatusMeta(Event $event): array
    {
        if ($event->is_paid) {
            return [
                'paid',
                'Paid',
                $event->retention_ends_at !== null
                    ? 'Access stays available until the retention window ends.'
                    : 'Payment is confirmed for this event.',
                'emerald',
            ];
        }

        if ($this->isPaymentLocked($event)) {
            return [
                'locked',
                'Locked until payment',
                'Guests cannot upload until payment is completed.',
                'rose',
            ];
        }

        $paymentDueAt = $event->payment_due_at ?? $event->grace_ends_at;
        if ($paymentDueAt !== null) {
            return [
                'pending',
                'Payment pending',
                'Payment is still outstanding before the event locks.',
                'amber',
            ];
        }

        return [
            'pending',
            'Billing pending',
            'A billing plan still needs to be confirmed for this event.',
            'amber',
        ];
    }

    /**
     * @return array{
     *   limitBytes: int,
     *   usedBytes: int,
     *   freeBytes: int,
     *   usagePercent: int,
     *   isNearLimit: bool,
     *   isOverLimit: bool
     * }
     */
    private function storageQuotaMeta(int $limitBytes, int $usedBytes): array
    {
        $safeLimit = max(0, $limitBytes);
        $safeUsed = max(0, $usedBytes);
        $freeBytes = max(0, $safeLimit - $safeUsed);
        $rawPercent = $safeLimit > 0 ? (int) round(($safeUsed / $safeLimit) * 100) : 0;

        return [
            'limitBytes' => $safeLimit,
            'usedBytes' => $safeUsed,
            'freeBytes' => $freeBytes,
            'usagePercent' => max(0, $rawPercent),
            'isNearLimit' => $safeLimit > 0 && $safeUsed >= (int) floor($safeLimit * 0.8),
            'isOverLimit' => $safeLimit > 0 && $safeUsed > $safeLimit,
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, priceLabel: string, description: string, limitsLabel: string}>
     */
    private function billingPlanOptions(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('currency')
            ->orderBy('price_cents')
            ->get()
            ->map(fn (Plan $plan): array => [
                'id' => $plan->id,
                'name' => $plan->name,
                'priceLabel' => $this->planPriceLabel($plan),
                'description' => (string) $plan->description,
                'limitsLabel' => "{$plan->upload_limit} uploads · ".round($plan->storage_limit_bytes / 1073741824).' GB storage',
            ])
            ->values()
            ->all();
    }

    private function planPriceLabel(?Plan $plan): string
    {
        if (! $plan instanceof Plan) {
            return 'Custom pricing';
        }

        $amount = number_format($plan->price_cents / 100, 2, '.', '');

        return "{$amount} {$plan->currency}";
    }

    private function publicGuestDownloadsEnabled(Event $event): bool
    {
        if (! $this->eventCanDownloadAll($event)) {
            return false;
        }

        $branding = $this->resolvedEventBranding($event);

        return ! (bool) ($branding['disable_guest_download'] ?? false);
    }

    private function publicAlbumPermission(Event $event): string
    {
        $branding = is_array($event->branding) ? $event->branding : [];
        $permission = $branding['album_permission']
            ?? ((bool) ($branding['album_public'] ?? true) ? 'view_upload' : 'upload_only');

        return is_string($permission) && in_array($permission, ['view_upload', 'view_only', 'upload_only'], true)
            ? $permission
            : 'view_upload';
    }

    private function publicAlbumAllowsViewingMedia(Event $event): bool
    {
        return in_array($this->publicAlbumPermission($event), ['view_upload', 'view_only'], true);
    }

    private function publicAlbumAllowsUploads(Event $event): bool
    {
        return in_array($this->publicAlbumPermission($event), ['view_upload', 'upload_only'], true);
    }

    private function ensureUploadsAllowed(Event $event): void
    {
        if (! $this->publicAlbumAllowsUploads($event)) {
            throw ValidationException::withMessages([
                'files' => 'Guests cannot upload to this event right now.',
            ]);
        }

        if ($this->isPaymentLocked($event)) {
            throw ValidationException::withMessages([
                'files' => 'This event is locked until payment is completed.',
            ]);
        }

        if (! $this->isUploadWindowOpen($event)) {
            if ($this->isPreEventTestUploadMode($event)) {
                return;
            }

            throw ValidationException::withMessages([
                'files' => 'Uploads are disabled for this event at the moment.',
            ]);
        }
    }

    private function resolveAssetKind(UploadedFile $file): string
    {
        $mime = $file->getMimeType() ?? '';
        $extension = Str::lower($file->getClientOriginalExtension());

        if (str_starts_with($mime, 'image/')) {
            return 'photo';
        }
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'avif', 'svg'], true)) {
            return 'photo';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }
        if (in_array($extension, ['mp4', 'mov', 'm4v', 'webm'], true)) {
            return 'video';
        }

        throw ValidationException::withMessages([
            'files' => "Unsupported file type: {$mime}.",
        ]);
    }

    private function ensureFileWithinKindLimit(Event $event, string $kind, int $sizeBytes): void
    {
        $maxSizeBytes = $kind === 'photo'
            ? (int) $event->photo_max_size_bytes
            : (int) $event->video_max_size_bytes;

        if ($sizeBytes > $maxSizeBytes) {
            throw ValidationException::withMessages([
                'files' => $kind === 'photo'
                    ? 'Photo file is too large for this event.'
                    : 'Video file is too large for this event.',
            ]);
        }
    }

    private function ensurePhotoUploadWithinProcessingLimit(Event $event, int $rawSizeBytes): void
    {
        $processingLimit = max(
            (int) $event->photo_max_size_bytes,
            (int) config('events.image_variants.upload_processing_max_bytes', 157286400),
        );

        if ($rawSizeBytes > $processingLimit) {
            throw ValidationException::withMessages([
                'files' => 'Photo file is too large to process for this event.',
            ]);
        }
    }

    /**
     * @return array{contents: string, sizeBytes: int, mimeType: string, extension: string, width: int, height: int}
     */
    private function optimizedPhotoUpload(UploadedFile $file): array
    {
        $contents = $file->get();
        if (! is_string($contents) || $contents === '') {
            throw ValidationException::withMessages([
                'files' => 'Photo upload could not be processed.',
            ]);
        }

        try {
            $image = new Imagick;
            $image->readImageBlob($contents);
            $image = $image->coalesceImages();
            $image->setIteratorIndex(0);
            $image->autoOrient();
            $image->stripImage();

            $maxPixels = max(1280, (int) config('events.image_variants.upload_max_pixels', 2560));
            $width = max(1, (int) $image->getImageWidth());
            $height = max(1, (int) $image->getImageHeight());
            $scale = min($maxPixels / $width, $maxPixels / $height, 1);
            if ($scale < 1) {
                $targetWidth = max(1, (int) round($width * $scale));
                $targetHeight = max(1, (int) round($height * $scale));
                $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);
            }

            $format = $this->normalizedImageUploadFormat();
            $targetFormat = $format === 'jpg' ? 'jpeg' : $format;

            if ($targetFormat === 'jpeg') {
                $image->setImageBackgroundColor('white');
                $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }

            $image->setImageFormat($targetFormat);
            $image->setImageCompressionQuality(
                max(40, min(95, (int) config('events.image_variants.upload_quality', 84))),
            );

            $blob = $image->getImagesBlob();
            $optimizedWidth = max(1, (int) $image->getImageWidth());
            $optimizedHeight = max(1, (int) $image->getImageHeight());
            $image->clear();
            $image->destroy();

            if (! is_string($blob) || $blob === '') {
                throw ValidationException::withMessages([
                    'files' => 'Photo upload could not be processed.',
                ]);
            }

            return [
                'contents' => $blob,
                'sizeBytes' => strlen($blob),
                'mimeType' => $this->imageUploadMimeType($format),
                'extension' => $format,
                'width' => $optimizedWidth,
                'height' => $optimizedHeight,
            ];
        } catch (ImagickException) {
            return [
                'contents' => $contents,
                'sizeBytes' => strlen($contents),
                'mimeType' => $file->getMimeType() ?? 'application/octet-stream',
                'extension' => $this->normalizedUploadExtension($file),
                'width' => 0,
                'height' => 0,
            ];
        }
    }

    private function generatedAssetFilename(
        UploadedFile $file,
        string $kind,
        string $guestName,
        int $index,
        ?string $preferredExtension = null,
    ): string {
        $extension = $preferredExtension ?: $this->normalizedUploadExtension($file);
        $guestSlug = Str::slug($guestName);

        if ($guestSlug === '') {
            $guestSlug = 'guest';
        }

        $kindLabel = $kind === 'photo'
            ? 'photo'
            : ($kind === 'video' ? 'video' : 'upload');

        return sprintf(
            '%s-%s-%s-%02d%s',
            $guestSlug,
            $kindLabel,
            now()->format('Ymd-His'),
            $index + 1,
            $extension !== '' ? ".{$extension}" : '',
        );
    }

    private function generatedAssetStorageFilename(string $displayFilename): string
    {
        $baseName = pathinfo($displayFilename, PATHINFO_FILENAME);
        $extension = pathinfo($displayFilename, PATHINFO_EXTENSION);

        return sprintf(
            '%s-%s%s',
            $baseName,
            Str::lower(Str::random(6)),
            $extension !== '' ? ".{$extension}" : '',
        );
    }

    private function normalizedUploadExtension(UploadedFile $file): string
    {
        $extension = Str::lower(
            $file->getClientOriginalExtension()
            ?: ($file->extension() ?? '')
        );

        if ($extension !== '') {
            return $extension;
        }

        $mime = $file->getMimeType() ?? '';

        if (str_starts_with($mime, 'image/')) {
            return 'jpg';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'mp4';
        }

        return '';
    }

    private function normalizedImageUploadFormat(): string
    {
        $format = Str::lower((string) config('events.image_variants.upload_format', 'jpg'));

        return in_array($format, ['jpg', 'jpeg', 'webp', 'png'], true)
            ? ($format === 'jpeg' ? 'jpg' : $format)
            : 'jpg';
    }

    private function imageUploadMimeType(string $extension): string
    {
        return match (Str::lower($extension)) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            default => 'image/jpeg',
        };
    }

    private function createQrCodeDataUrl(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(320),
            new SvgImageBackEnd,
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function eventExportArchiveFilename(Event $event): string
    {
        $slug = Str::slug($event->name);
        if ($slug === '') {
            $slug = 'event';
        }

        return "{$slug}-album.zip";
    }

    private function assetOriginalUrl(EventAsset $asset): ?string
    {
        return $this->storageUrl($asset->disk, $asset->path);
    }

    private function assetPreviewUrl(EventAsset $asset): ?string
    {
        if ($asset->kind === 'video') {
            $path = $asset->video_preview_path;

            if ($path === null || $path === '') {
                return null;
            }

            return $this->storageUrl($asset->disk, $path);
        }

        $path = $asset->preview_path ?: $asset->path;

        return $this->storageUrl($asset->disk, $path);
    }

    private function assetThumbnailUrl(EventAsset $asset): ?string
    {
        if ($asset->kind === 'video') {
            $path = $asset->video_thumbnail_path;

            if ($path === null || $path === '') {
                return null;
            }

            return $this->storageUrl($asset->disk, $path);
        }

        $path = $asset->thumbnail_path ?: $asset->preview_path ?: $asset->path;

        return $this->storageUrl($asset->disk, $path);
    }

    private function assetPublicPreviewUrl(EventAsset $asset): ?string
    {
        $path = $this->assetPublicPreviewPath($asset);

        return $path === null ? null : $this->storageUrl($asset->disk, $path);
    }

    private function assetPublicThumbnailUrl(EventAsset $asset): ?string
    {
        $path = $this->assetPublicThumbnailPath($asset);

        return $path === null ? null : $this->storageUrl($asset->disk, $path);
    }

    private function assetPublicDownloadPath(EventAsset $asset): string
    {
        if ($asset->kind === 'video') {
            return $asset->video_preview_path
                ?: $asset->path;
        }

        return $asset->watermarked_download_path
            ?: $asset->watermarked_preview_path
            ?: $asset->preview_path
            ?: $asset->path;
    }

    private function publicDownloadFilename(EventAsset $asset, string $downloadPath): string
    {
        $filename = $asset->original_filename ?: basename($asset->path);
        $servedExtension = pathinfo($downloadPath, PATHINFO_EXTENSION);
        if (! is_string($servedExtension) || trim($servedExtension) === '') {
            return $filename;
        }

        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        if (! is_string($baseName) || trim($baseName) === '') {
            return basename($downloadPath);
        }

        return $baseName.'.'.Str::lower($servedExtension);
    }

    private function videoVariantsPending(EventAsset $asset, bool $public = false): bool
    {
        if ($asset->kind !== 'video') {
            return false;
        }

        return ! is_string($asset->video_thumbnail_path)
            || trim((string) $asset->video_thumbnail_path) === ''
            || ! is_string($asset->video_preview_path)
            || trim((string) $asset->video_preview_path) === '';
    }

    /**
     * @return array<int, array{id: int, slug: string, name: string, imageUrl: string, backgroundColor: string|null, textColor: string}>
     */
    private function textPostThemesPayload(): array
    {
        return TextPostTheme::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (TextPostTheme $theme): array {
                return [
                    'id' => $theme->id,
                    'slug' => $theme->slug,
                    'name' => $theme->name,
                    'imageUrl' => asset($theme->image_path),
                    'backgroundColor' => $theme->background_color,
                    'textColor' => $theme->text_color ?: '#FFFFFF',
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{textThemeId: int|null, textThemeSlug: string|null, textThemeImageUrl: string|null, textThemeBackgroundColor: string|null, textThemeTextColor: string|null}
     */
    private function textPostThemeAssetProps(array $metadata): array
    {
        $imagePath = is_string($metadata['text_background_image_path'] ?? null)
            ? trim((string) $metadata['text_background_image_path'])
            : null;

        return [
            'textThemeId' => is_numeric($metadata['text_theme_id'] ?? null) ? (int) $metadata['text_theme_id'] : null,
            'textThemeSlug' => is_string($metadata['text_theme_slug'] ?? null) ? $metadata['text_theme_slug'] : null,
            'textThemeImageUrl' => $imagePath !== null && $imagePath !== '' ? asset($imagePath) : null,
            'textThemeBackgroundColor' => is_string($metadata['text_background_color'] ?? null) ? $metadata['text_background_color'] : null,
            'textThemeTextColor' => is_string($metadata['text_color'] ?? null) ? $metadata['text_color'] : null,
        ];
    }

    /**
     * @param  array{byToken: \Illuminate\Support\Collection<string, EventGuest>, byName: \Illuminate\Support\Collection<string, EventGuest>}  $guestLookups
     * @return array<string, mixed>
     */
    private function publicAlbumAssetProps(Event $event, EventAsset $asset, array $guestLookups): array
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $guest = $this->guestForAsset($asset, $guestLookups);

        return [
            'id' => $asset->id,
            'kind' => $asset->kind,
            'moderationStatus' => $asset->moderation_status,
            'width' => $asset->width,
            'height' => $asset->height,
            'thumbnailUrl' => route('events.album.asset-thumbnail', [$event->publicAlbumCode(), $asset]),
            'previewUrl' => route('events.album.asset-preview', [$event->publicAlbumCode(), $asset]),
            'videoProcessing' => $this->videoVariantsPending($asset, true),
            'downloadUrl' => route('events.album.asset-download', [$event->share_token, $asset]),
            'deleteUrl' => route('events.album.asset-delete', [$event->share_token, $asset]),
            'likeToggleUrl' => route('events.album.asset-like.toggle', [$event->share_token, $asset]),
            'sizeBytes' => $this->assetPublicSizeBytes($asset),
            'text' => is_string($metadata['text'] ?? null) ? $metadata['text'] : null,
            ...$this->textPostThemeAssetProps($metadata),
            'guestName' => is_string($metadata['guest_name'] ?? null) ? $metadata['guest_name'] : null,
            'guestAvatarUrl' => $guest !== null ? $this->eventGuestAvatarUrl($guest) : null,
            'message' => is_string($metadata['message'] ?? null) ? $metadata['message'] : null,
            'originalFilename' => $asset->original_filename,
            'mimeType' => $asset->mime_type,
            'createdAt' => $asset->created_at?->toIso8601String(),
            'uploadBatchId' => is_string($metadata['upload_batch_id'] ?? null) ? $metadata['upload_batch_id'] : null,
            'uploadBatchIndex' => is_numeric($metadata['upload_batch_index'] ?? null) ? (int) $metadata['upload_batch_index'] : null,
            'galleryGroupKey' => $this->albumGalleryGroupKey($asset),
            'captionTitle' => is_string($metadata['caption_title'] ?? null) ? $metadata['caption_title'] : null,
            'captionSubtitle' => is_string($metadata['caption_subtitle'] ?? null) ? $metadata['caption_subtitle'] : null,
            'likeCount' => (int) ($asset->likes_count ?? 0),
            'commentCount' => (int) ($asset->comments_count ?? 0),
            'commentsUrl' => route('events.album.asset-comments.index', [$event->share_token, $asset]),
            'commentStoreUrl' => route('events.album.asset-comments.store', [$event->share_token, $asset]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function wallAssetProps(Event $event, EventAsset $asset): array
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];

        return [
            'id' => $asset->id,
            'kind' => $asset->kind,
            'width' => $asset->width,
            'height' => $asset->height,
            'thumbnailUrl' => route('events.album.asset-thumbnail', [$event->publicAlbumCode(), $asset]),
            'previewUrl' => route('events.album.asset-preview', [$event->publicAlbumCode(), $asset]),
            'videoProcessing' => $this->videoVariantsPending($asset, true),
            'text' => is_string($metadata['text'] ?? null) ? $metadata['text'] : null,
            ...$this->textPostThemeAssetProps($metadata),
            'guestName' => is_string($metadata['guest_name'] ?? null) ? $metadata['guest_name'] : null,
            'captionTitle' => is_string($metadata['caption_title'] ?? null) ? $metadata['caption_title'] : null,
            'captionSubtitle' => is_string($metadata['caption_subtitle'] ?? null) ? $metadata['caption_subtitle'] : null,
            'createdAt' => $asset->created_at?->toIso8601String(),
            'durationSeconds' => $asset->duration_seconds,
            'likeCount' => (int) ($asset->likes_count ?? 0),
            'commentCount' => (int) ($asset->comments_count ?? 0),
            'recentComments' => $asset->relationLoaded('comments')
                ? $asset->comments
                    ->sortBy('id')
                    ->take(4)
                    ->values()
                    ->map(fn (EventAssetComment $comment): array => $this->serializeWallAssetComment($comment))
                    ->all()
                : [],
        ];
    }

    private function assetPublicSizeBytes(EventAsset $asset): int
    {
        if ($asset->kind !== 'video' || ! is_string($asset->video_preview_path) || trim($asset->video_preview_path) === '') {
            return (int) $asset->size_bytes;
        }

        try {
            return (int) Storage::disk($asset->disk)->size($asset->video_preview_path);
        } catch (Throwable) {
            return (int) $asset->size_bytes;
        }
    }

    /**
     * @return array{byToken: \Illuminate\Support\Collection<string, EventGuest>, byName: \Illuminate\Support\Collection<string, EventGuest>}
     */
    private function eventGuestLookups(Event $event): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, EventGuest> $guests */
        $guests = $event->guests;

        return [
            'byToken' => $guests
                ->filter(fn (EventGuest $guest): bool => trim((string) $guest->guest_token) !== '')
                ->keyBy(fn (EventGuest $guest): string => trim((string) $guest->guest_token)),
            'byName' => $guests
                ->filter(fn (EventGuest $guest): bool => $this->normalizedGuestNameString($guest->name) !== null)
                ->keyBy(fn (EventGuest $guest): string => (string) $this->normalizedGuestNameString($guest->name)),
        ];
    }

    /**
     * @param  array{byToken: \Illuminate\Support\Collection<string, EventGuest>, byName: \Illuminate\Support\Collection<string, EventGuest>}  $guestLookups
     */
    private function guestForAsset(EventAsset $asset, array $guestLookups): ?EventGuest
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $guestToken = is_string($metadata['guest_token'] ?? null)
            ? trim((string) $metadata['guest_token'])
            : '';
        if ($guestToken !== '') {
            $guestByToken = $guestLookups['byToken']->get($guestToken);
            if ($guestByToken instanceof EventGuest) {
                return $guestByToken;
            }
        }

        $normalizedGuestName = $this->normalizedAssetGuestName($metadata);
        if ($normalizedGuestName === null) {
            return null;
        }

        $guestByName = $guestLookups['byName']->get($normalizedGuestName);

        return $guestByName instanceof EventGuest ? $guestByName : null;
    }

    private function albumGalleryGroupKey(EventAsset $asset): string
    {
        if ($asset->kind === 'text') {
            return "text:{$asset->id}";
        }

        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $uploadBatchId = is_string($metadata['upload_batch_id'] ?? null)
            ? trim((string) $metadata['upload_batch_id'])
            : '';
        if ($uploadBatchId !== '') {
            return "batch:{$uploadBatchId}";
        }

        $guestToken = is_string($metadata['guest_token'] ?? null)
            ? trim((string) $metadata['guest_token'])
            : '';
        if ($guestToken !== '') {
            return 'guest:'.sha1($guestToken);
        }

        $guestName = $this->normalizedAssetGuestName($metadata);
        if ($guestName !== null) {
            return 'guest:'.sha1($guestName);
        }

        return "single:{$asset->id}";
    }

    private function normalizedAssetGuestName(array $metadata): ?string
    {
        return $this->normalizedGuestNameString($metadata['guest_name'] ?? null);
    }

    private function normalizedGuestNameString(mixed $guestName): ?string
    {
        if (! is_string($guestName)) {
            return null;
        }

        $normalizedGuestName = trim($guestName);
        if ($normalizedGuestName === '') {
            return null;
        }

        return Str::of($normalizedGuestName)
            ->squish()
            ->lower()
            ->toString();
    }

    private function eventGuestAvatarUrl(EventGuest $guest): ?string
    {
        if ($guest->avatar_disk === null || $guest->avatar_path === null) {
            return null;
        }

        return $this->storageUrl($guest->avatar_disk, $guest->avatar_path);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEventGuest(EventGuest $guest): array
    {
        return [
            'id' => $guest->id,
            'guestToken' => $guest->guest_token,
            'name' => $guest->name,
            'email' => $guest->email,
            'phone' => $guest->phone,
            'avatarUrl' => $this->eventGuestAvatarUrl($guest),
            'guestFields' => is_array($guest->guest_fields) ? $guest->guest_fields : [],
            'lastIntent' => $guest->last_intent,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeEventAssetComment(
        Event $event,
        EventAsset $asset,
        EventAssetComment $comment,
        ?int $currentGuestId = null,
    ): array {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'guestName' => $comment->guest?->name ?? 'Guest',
            'guestAvatarUrl' => $comment->guest !== null ? $this->eventGuestAvatarUrl($comment->guest) : null,
            'createdAt' => $comment->created_at?->toIso8601String(),
            'likeCount' => (int) ($comment->likes_count ?? 0),
            'liked' => $currentGuestId !== null
                ? EventAssetCommentLike::query()
                    ->where('event_asset_comment_id', $comment->id)
                    ->where('event_guest_id', $currentGuestId)
                    ->exists()
                : false,
            'likeToggleUrl' => route('events.album.asset-comment-like.toggle', [$event->share_token, $asset, $comment]),
        ];
    }

    /**
     * @return array{id: int, body: string, guestName: string, createdAt: string|null, likeCount: int}
     */
    private function serializeWallAssetComment(EventAssetComment $comment): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'guestName' => $comment->guest?->name ?? 'Guest',
            'createdAt' => $comment->created_at?->toIso8601String(),
            'likeCount' => (int) ($comment->likes_count ?? 0),
        ];
    }

    private function eventGuestForToken(Event $event, ?string $guestToken): ?EventGuest
    {
        $normalizedToken = trim((string) $guestToken);
        if ($normalizedToken === '') {
            return null;
        }

        return EventGuest::query()
            ->where('event_id', $event->id)
            ->where('guest_token', $normalizedToken)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertEventGuest(
        Event $event,
        array $attributes,
        ?UploadedFile $avatarFile = null,
        bool $removeAvatar = false,
    ): EventGuest {
        $guestToken = trim((string) ($attributes['guest_token'] ?? ''));
        if ($guestToken === '') {
            throw ValidationException::withMessages([
                'guest_token' => 'Guest token is required.',
            ]);
        }

        $guest = EventGuest::query()->firstOrNew([
            'event_id' => $event->id,
            'guest_token' => $guestToken,
        ]);

        $guest->fill([
            'name' => trim((string) ($attributes['name'] ?? 'Guest')),
            'email' => $this->nullableTrimmedString($attributes['email'] ?? null),
            'phone' => $this->nullableTrimmedString($attributes['phone'] ?? null),
            'guest_fields' => $this->normalizedGuestFields($attributes['guest_fields'] ?? []),
            'last_intent' => $this->nullableTrimmedString($attributes['last_intent'] ?? null),
            'last_seen_at' => now(),
        ]);
        $guest->save();

        if ($removeAvatar || $avatarFile !== null) {
            $this->deleteEventGuestAvatar($guest);
            $guest->forceFill([
                'avatar_disk' => null,
                'avatar_path' => null,
            ]);
        }

        if ($avatarFile !== null) {
            $disk = (string) config('events.upload_disk', 'public');
            $extension = $avatarFile->getClientOriginalExtension();
            $filename = 'avatar-'.Str::uuid()->toString().($extension !== '' ? ".{$extension}" : '');
            $path = $this->writeUploadedFileToStorage(
                $disk,
                "events/{$event->id}/guests/{$guest->id}",
                $avatarFile,
                $filename,
            );
            if ($path === false) {
                throw ValidationException::withMessages([
                    'avatar_file' => 'Avatar upload failed. Please try again.',
                ]);
            }

            $guest->forceFill([
                'avatar_disk' => $disk,
                'avatar_path' => $path,
            ]);
        }

        if ($guest->isDirty()) {
            $guest->save();
        }

        return $guest->refresh();
    }

    /**
     * @return array<string, string>
     */
    private function normalizedGuestFields(mixed $guestFields): array
    {
        if (! is_array($guestFields)) {
            return [];
        }

        return collect($guestFields)
            ->mapWithKeys(function (mixed $value, mixed $key): array {
                if (! is_string($key)) {
                    return [];
                }

                $normalizedValue = is_string($value) ? trim($value) : '';
                if ($normalizedValue === '') {
                    return [];
                }

                return [$key => $normalizedValue];
            })
            ->all();
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalizedValue = trim($value);

        return $normalizedValue === '' ? null : $normalizedValue;
    }

    private function deleteEventGuestAvatar(EventGuest $guest): void
    {
        if ($guest->avatar_disk === null || $guest->avatar_path === null) {
            return;
        }

        Storage::disk($guest->avatar_disk)->delete($guest->avatar_path);
    }

    private function canGuestDeleteAsset(EventAsset $asset, string $guestToken): bool
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $assetGuestToken = is_string($metadata['guest_token'] ?? null)
            ? trim((string) $metadata['guest_token'])
            : '';

        return $assetGuestToken !== '' && hash_equals($assetGuestToken, $guestToken);
    }

    private function deleteAssetFromEvent(Event $event, EventAsset $asset): void
    {
        DB::transaction(function () use ($event, $asset): void {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
            $lockedAsset = EventAsset::query()
                ->whereKey($asset->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->firstOrFail();

            Storage::disk($lockedAsset->disk)->delete(array_values(array_filter([
                $lockedAsset->path,
                $lockedAsset->thumbnail_path,
                $lockedAsset->preview_path,
                $lockedAsset->watermarked_thumbnail_path,
                $lockedAsset->watermarked_preview_path,
                $lockedAsset->watermarked_download_path,
                $lockedAsset->video_thumbnail_path,
                $lockedAsset->watermarked_video_thumbnail_path,
                $lockedAsset->video_preview_path,
                $lockedAsset->watermarked_video_preview_path,
                $lockedAsset->watermarked_video_download_path,
            ])));

            $lockedAsset->delete();

            $lockedEvent->update([
                'storage_used_bytes' => max(0, (int) $lockedEvent->storage_used_bytes - (int) $lockedAsset->size_bytes),
                'upload_count' => max(0, (int) $lockedEvent->upload_count - 1),
            ]);

            $this->invalidateEventMediaExport($lockedEvent, deleteStoredFile: true);
        });
    }

    private function invalidateEventMediaExport(Event $event, bool $deleteStoredFile = true): void
    {
        if (
            $deleteStoredFile
            && is_string($event->media_export_disk)
            && $event->media_export_disk !== ''
            && is_string($event->media_export_path)
            && $event->media_export_path !== ''
        ) {
            Storage::disk($event->media_export_disk)->delete($event->media_export_path);
        }

        $event->forceFill([
            'media_export_status' => null,
            'media_export_token' => null,
            'media_export_disk' => null,
            'media_export_path' => null,
            'media_export_requested_at' => null,
            'media_export_started_at' => null,
            'media_export_completed_at' => null,
            'media_export_failed_at' => null,
            'media_export_error' => null,
        ])->save();
    }

    private function preEventTestUploadLimit(): int
    {
        return max(0, (int) config('events.pre_event_test_upload_limit', 10));
    }

    private function preEventTestUploadsRemaining(Event $event): int
    {
        return max(0, $this->preEventTestUploadLimit() - (int) $event->upload_count);
    }

    private function isPreEventTestUploadMode(Event $event): bool
    {
        if ($this->preEventTestUploadLimit() === 0) {
            return false;
        }

        if ($event->upload_window_starts_at === null) {
            return false;
        }

        $now = now($event->timezone);
        if (! $now->lt($event->upload_window_starts_at)) {
            return false;
        }

        return $this->preEventTestUploadsRemaining($event) > 0;
    }

    /**
     * @return array<int, string>
     */
    private function allowedMediaTypes(Event $event): array
    {
        $branding = is_array($event->branding) ? $event->branding : [];
        $allowed = $branding['allowed_media_types'] ?? $this->defaultAllowedMediaTypes();
        if (! is_array($allowed)) {
            return $this->defaultAllowedMediaTypes();
        }

        $normalized = array_values(array_unique(array_filter(
            $allowed,
            fn ($value): bool => is_string($value) && in_array($value, ['photo', 'video', 'text'], true),
        )));

        if ($normalized === []) {
            return $this->defaultAllowedMediaTypes();
        }

        $version = (int) ($branding['allowed_media_types_version'] ?? 1);
        if ($version < 2 && $normalized === ['photo', 'video']) {
            return $this->defaultAllowedMediaTypes();
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function defaultAllowedMediaTypes(): array
    {
        return ['photo', 'video', 'text'];
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function mediaGuestKeyFromMetadata(array $metadata): string
    {
        $guestToken = is_string($metadata['guest_token'] ?? null)
            ? trim((string) $metadata['guest_token'])
            : '';
        if ($guestToken !== '') {
            return 'token:'.$guestToken;
        }

        $guestEmail = is_string($metadata['guest_email'] ?? null)
            ? Str::lower(trim((string) $metadata['guest_email']))
            : '';
        if ($guestEmail !== '') {
            return 'email:'.$guestEmail;
        }

        $guestPhone = is_string($metadata['guest_phone'] ?? null)
            ? preg_replace('/\D+/', '', (string) $metadata['guest_phone'])
            : '';
        if (is_string($guestPhone) && $guestPhone !== '') {
            return 'phone:'.$guestPhone;
        }

        $guestName = is_string($metadata['guest_name'] ?? null)
            ? Str::lower(trim((string) $metadata['guest_name']))
            : '';
        if ($guestName !== '') {
            return 'name:'.$guestName;
        }

        return 'guest:unknown';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function eventTypeOptions(): array
    {
        return [
            ['value' => 'wedding', 'label' => 'Wedding'],
            ['value' => 'party', 'label' => 'Party'],
            ['value' => 'birthday', 'label' => 'Birthday'],
            ['value' => 'engagement', 'label' => 'Engagement'],
            ['value' => 'baptism', 'label' => 'Baptism'],
            ['value' => 'other', 'label' => 'Other'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function eventTypeValues(): array
    {
        return array_map(
            fn (array $option): string => (string) $option['value'],
            $this->eventTypeOptions(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function guestIntentValues(): array
    {
        return [
            'upload_media',
            'video_testimonial',
            'text_wish',
            'browse_gallery',
        ];
    }

    /**
     * @return array{
     *   upload_window_starts_at: CarbonImmutable|null,
     *   upload_window_ends_at: CarbonImmutable|null,
     *   grace_ends_at: CarbonImmutable|null,
     *   hard_lock_at: CarbonImmutable|null,
     *   status: string
     * }
     */
    private function buildEventWindows(Event $event, ?string $eventDate, string $timezone): array
    {
        return EventLifecycleWindows::build(
            $eventDate,
            $timezone,
            max(1, (int) $event->upload_window_days),
            max(0, (int) ($event->plan?->grace_days ?? 0)),
        );
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function brandingLogoUrl(array $branding): ?string
    {
        $logoPath = $branding['logo_path'] ?? null;
        $logoDisk = $branding['logo_disk'] ?? null;

        if (is_string($logoPath) && is_string($logoDisk)) {
            return $this->storageUrl($logoDisk, $logoPath);
        }

        $legacyLogoUrl = $branding['logo_url'] ?? null;

        return is_string($legacyLogoUrl) ? $legacyLogoUrl : null;
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function brandingAlbumBackgroundUrl(array $branding): ?string
    {
        $path = $branding['album_background_path'] ?? null;
        $disk = $branding['album_background_disk'] ?? null;

        if (is_string($path) && is_string($disk)) {
            return $this->storageUrl($disk, $path);
        }

        $presetTheme = $this->textPostThemeById($this->albumBackgroundPresetThemeId($branding));
        if ($presetTheme !== null) {
            return asset($presetTheme->image_path);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function effectiveAlbumBackgroundUrl(Event $event, array $branding): string
    {
        return $this->brandingAlbumBackgroundUrl($branding) ?? $this->defaultAlbumBackgroundUrl($event);
    }

    private function defaultAlbumBackgroundUrl(Event $event): string
    {
        $backgrounds = [
            'alvin-bg-md.jpg',
            'beatriz-bg-md.jpg',
            'drew-bg-md.jpg',
            'jeremy-bg-md.jpg',
            'nathan-bg-md.jpg',
            'sandy-bg-md.jpg',
        ];

        $seed = $event->album_access_code ?: $event->share_token ?: (string) $event->getKey();
        $hash = (int) sprintf('%u', crc32((string) $seed));
        $background = $backgrounds[$hash % count($backgrounds)] ?? $backgrounds[0];

        return asset("images/album/{$background}");
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function albumBackgroundMode(array $branding): string
    {
        $mode = $branding['album_background_mode'] ?? 'rotate';
        if (! is_string($mode) || ! in_array($mode, ['rotate', 'solid', 'preset', 'image'], true)) {
            return 'rotate';
        }

        if (in_array($mode, ['preset', 'image'], true) && $this->brandingAlbumBackgroundUrl($branding) === null) {
            return 'rotate';
        }

        return $mode;
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function albumBackgroundPresetThemeId(array $branding): ?int
    {
        return $this->normalizeTextPostThemeId($branding['album_background_preset_theme_id'] ?? null);
    }

    private function normalizeTextPostThemeId(mixed $value): ?int
    {
        $themeId = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (! is_int($themeId)) {
            return null;
        }

        return $this->textPostThemeById($themeId)?->id;
    }

    private function textPostThemeById(?int $themeId): ?TextPostTheme
    {
        if ($themeId === null) {
            return null;
        }

        return TextPostTheme::query()
            ->active()
            ->whereKey($themeId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function brandingWelcomeScreenBackgroundUrl(array $branding): ?string
    {
        $path = $branding['welcome_screen_background_path'] ?? null;
        $disk = $branding['welcome_screen_background_disk'] ?? null;

        if (is_string($path) && is_string($disk)) {
            return $this->storageUrl($disk, $path);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $branding
     * @return array<int, string>
     */
    private function textPostsBackgroundPalette(array $branding): array
    {
        $palette = $branding['text_posts_background_palette'] ?? ['#1D4ED8', '#0F766E', '#EA580C'];
        if (! is_array($palette)) {
            return ['#1D4ED8', '#0F766E', '#EA580C'];
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static fn ($value): ?string => is_string($value) && preg_match('/^#(?:[A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value) === 1
                ? strtoupper($value)
                : null,
            $palette,
        ))));

        return $normalized === [] ? ['#1D4ED8', '#0F766E', '#EA580C'] : $normalized;
    }

    /**
     * @param  array<string, mixed>  $branding
     * @return array<int, string>
     */
    private function moderationFilters(array $branding): array
    {
        $filters = $branding['moderation_filters'] ?? ['adult', 'nudity', 'violence', 'suggestive'];
        if (! is_array($filters)) {
            return ['adult', 'nudity', 'violence', 'suggestive'];
        }

        $normalized = array_values(array_unique(array_filter(
            $filters,
            static fn ($value): bool => is_string($value) && in_array($value, ['adult', 'nudity', 'violence', 'suggestive', 'hate'], true),
        )));

        return $normalized === [] ? ['adult', 'nudity', 'violence', 'suggestive'] : $normalized;
    }

    /**
     * @return array{status: string, score: int|null, reviewedAt: CarbonInterface|null, metadata: array<string, mixed>}
     */
    private function moderationDecision(
        Event $event,
        string $kind,
        string $originalFilename,
        ?string $message,
        ?string $text,
    ): array {
        if (! $event->moderation_enabled) {
            return [
                'status' => 'approved',
                'score' => null,
                'reviewedAt' => now(),
                'metadata' => [
                    'moderation' => [
                        'pipeline' => 'disabled',
                        'decision' => 'approved',
                    ],
                ],
            ];
        }

        $branding = is_array($event->branding) ? $event->branding : [];
        $filters = $this->moderationFilters($branding);

        if (! $event->auto_moderation_enabled) {
            return [
                'status' => 'processing',
                'score' => null,
                'reviewedAt' => null,
                'metadata' => [
                    'moderation' => [
                        'pipeline' => 'manual',
                        'decision' => 'processing',
                        'filters' => $filters,
                    ],
                ],
            ];
        }

        $analysis = $this->analyzeModerationSignals(
            $filters,
            $kind,
            $originalFilename,
            $message,
            $text,
        );
        $status = $analysis['blocked'] ? 'rejected' : 'approved';

        return [
            'status' => $status,
            'score' => $analysis['score'],
            'reviewedAt' => now(),
            'metadata' => [
                'moderation' => [
                    'pipeline' => 'automatic',
                    'decision' => $status,
                    'filters' => $filters,
                    'matches' => $analysis['matches'],
                    'signals' => $analysis['signals'],
                ],
            ],
        ];
    }

    private function initialWallVisibility(string $moderationStatus): string
    {
        if ($moderationStatus === 'rejected') {
            return 'rejected';
        }

        return 'pending';
    }

    private function assetWallVisibility(EventAsset $asset): string
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $visibility = $metadata['wall_visibility'] ?? null;

        if (is_string($visibility) && in_array($visibility, ['approved', 'rejected', 'pending'], true)) {
            return $visibility;
        }

        return $asset->moderation_status === 'approved' ? 'approved' : 'pending';
    }

    private function assetVisibleOnWall(EventAsset $asset): bool
    {
        return $asset->moderation_status === 'approved'
            && $this->assetWallVisibility($asset) === 'approved';
    }

    private function syncAssetWallVisibility(EventAsset $asset, string $wallVisibility): void
    {
        $metadata = is_array($asset->metadata) ? $asset->metadata : [];
        $metadata['wall_visibility'] = $wallVisibility;

        $asset->forceFill([
            'metadata' => $metadata,
        ])->save();
    }

    /**
     * @param  array<int, string>  $filters
     * @return array{blocked: bool, score: int, matches: array<int, array{category: string, keyword: string}>, signals: array<int, string>}
     */
    private function analyzeModerationSignals(
        array $filters,
        string $kind,
        string $originalFilename,
        ?string $message,
        ?string $text,
    ): array {
        $signals = array_values(array_filter([
            $kind,
            Str::of($originalFilename)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value(),
            is_string($message) ? Str::of($message)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value() : null,
            is_string($text) ? Str::of($text)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->trim()->value() : null,
        ], static fn (?string $value): bool => is_string($value) && $value !== ''));

        $keywordsByCategory = [
            'adult' => ['porn', 'xxx', 'sex', 'hardcore', 'explicit'],
            'nudity' => ['nude', 'naked', 'topless', 'nsfw', 'boobs'],
            'violence' => ['blood', 'gore', 'kill', 'knife', 'gun', 'fight'],
            'suggestive' => ['sexy', 'lingerie', 'bikini', 'seductive', 'intimate'],
            'hate' => ['nazi', 'racist', 'slur', 'white power', 'hate speech'],
        ];
        $weights = [
            'adult' => 96,
            'nudity' => 92,
            'violence' => 88,
            'suggestive' => 74,
            'hate' => 98,
        ];

        $matches = [];
        foreach ($filters as $filter) {
            foreach ($keywordsByCategory[$filter] ?? [] as $keyword) {
                foreach ($signals as $signal) {
                    if (! Str::contains($signal, $keyword)) {
                        continue;
                    }

                    $matches[] = [
                        'category' => $filter,
                        'keyword' => $keyword,
                    ];
                    break;
                }
            }
        }

        $score = collect($matches)
            ->map(fn (array $match): int => $weights[$match['category']] ?? 70)
            ->max() ?? 0;

        if (count($matches) > 1) {
            $score = min(100, $score + min(8, count($matches) * 2));
        }

        return [
            'blocked' => $matches !== [],
            'score' => $score,
            'matches' => $matches,
            'signals' => $signals,
        ];
    }

    private function storageUrl(string $disk, string $path): ?string
    {
        if (trim($path) === '') {
            return null;
        }

        try {
            if ($this->shouldCheckStoragePathExists($disk) && ! $this->storagePathExists($disk, $path)) {
                return null;
            }

            if ($disk === 'local') {
                return Storage::disk('local')->temporaryUrl(
                    $path,
                    now()->addMinutes((int) config('events.upload_temporary_url_ttl_minutes', 30)),
                );
            }

            if ((bool) config('events.upload_temporary_urls', false)) {
                return Storage::disk($disk)->temporaryUrl(
                    $path,
                    now()->addMinutes((int) config('events.upload_temporary_url_ttl_minutes', 30)),
                );
            }

            return Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    private function shouldCheckStoragePathExists(string $disk): bool
    {
        return in_array($disk, ['local', 'public'], true);
    }

    private function downloadStorageAsset(string $disk, string $path, string $filename): StreamedResponse|RedirectResponse
    {
        if ($this->shouldCheckStoragePathExists($disk)) {
            return Storage::disk($disk)->download($path, $filename);
        }

        try {
            $url = Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes((int) config('events.upload_temporary_url_ttl_minutes', 30)),
                [
                    'ResponseContentDisposition' => 'attachment; filename="'.$filename.'"',
                ],
            );

            return redirect()->away($url);
        } catch (\Throwable) {
            return Storage::disk($disk)->download($path, $filename);
        }
    }

    private function streamStorageAsset(string $disk, string $path): StreamedResponse|RedirectResponse
    {
        if ($this->shouldCheckStoragePathExists($disk)) {
            return Storage::disk($disk)->response($path);
        }

        try {
            return redirect()->away(Storage::disk($disk)->temporaryUrl(
                $path,
                now()->addMinutes((int) config('events.upload_temporary_url_ttl_minutes', 30)),
            ));
        } catch (\Throwable) {
            return Storage::disk($disk)->response($path);
        }
    }

    private function assetPublicPreviewPath(EventAsset $asset): ?string
    {
        if ($asset->kind === 'video') {
            $path = $asset->video_preview_path;

            return is_string($path) && trim($path) !== '' ? $path : null;
        }

        $path = $asset->watermarked_preview_path ?: $asset->preview_path ?: $asset->path;

        return is_string($path) && trim($path) !== '' ? $path : null;
    }

    private function assetPublicThumbnailPath(EventAsset $asset): ?string
    {
        if ($asset->kind === 'video') {
            $path = $asset->video_thumbnail_path;

            return is_string($path) && trim($path) !== '' ? $path : null;
        }

        $path = $asset->watermarked_thumbnail_path
            ?: $asset->thumbnail_path
            ?: $asset->watermarked_preview_path
            ?: $asset->preview_path
            ?: $asset->path;

        return is_string($path) && trim($path) !== '' ? $path : null;
    }

    private function normalizeCollaboratorStatus(string $status): string
    {
        return in_array($status, ['active', 'accepted'], true)
            ? 'active'
            : 'invited';
    }

    private function writeUploadedFileToStorage(
        string $disk,
        string $directory,
        UploadedFile $file,
        string $filename,
    ): string|false {
        try {
            $path = Storage::disk($disk)->putFileAs($directory, $file, $filename, ['visibility' => 'private']);
        } catch (\Throwable $exception) {
            Log::warning('Event storage file upload failed.', [
                'disk' => $disk,
                'directory' => $directory,
                'filename' => $filename,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! is_string($path) || trim($path) === '') {
            return false;
        }

        if (! $this->storagePathExists($disk, $path)) {
            Log::warning('Event storage file upload did not persist.', [
                'disk' => $disk,
                'path' => $path,
            ]);
            Storage::disk($disk)->delete($path);

            return false;
        }

        return $path;
    }

    private function writeContentsToStorage(
        string $disk,
        string $path,
        string $contents,
        array $options = [],
    ): bool {
        try {
            $stored = Storage::disk($disk)->put($path, $contents, $options);
        } catch (\Throwable $exception) {
            Log::warning('Event storage write failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $stored) {
            return false;
        }

        if (! $this->storagePathExists($disk, $path)) {
            Log::warning('Event storage write did not persist.', [
                'disk' => $disk,
                'path' => $path,
            ]);
            Storage::disk($disk)->delete($path);

            return false;
        }

        return true;
    }

    private function storagePathExists(string $disk, ?string $path): bool
    {
        if (! is_string($path) || trim($path) === '') {
            return false;
        }

        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Throwable $exception) {
            Log::warning('Event storage existence check failed.', [
                'disk' => $disk,
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function activateCollaboratorInvite(User $user, EventCollaborator $collaborator): RedirectResponse
    {
        $event = $collaborator->event()->firstOrFail();
        $normalizedInviteEmail = Str::lower((string) $collaborator->email);
        $normalizedUserEmail = Str::lower((string) $user->email);
        if ($normalizedInviteEmail !== $normalizedUserEmail) {
            return to_route('dashboard')->with(
                'error',
                "This invitation was sent to {$collaborator->email}. Please use that account.",
            );
        }

        if ($this->normalizeCollaboratorStatus($collaborator->status) !== 'active') {
            $collaborator->update([
                'user_id' => $user->id,
                'status' => 'active',
                'accepted_at' => now(),
            ]);
        }

        return to_route('events.show', $event)->with(
            'success',
            'Invitation accepted. You are now active on this event.',
        );
    }

    private function inviteExpiresAt(CarbonInterface $now): CarbonInterface
    {
        return $now->copy()->addDays(
            (int) config('events.collaborator_invite_expires_days', 7),
        );
    }

    /**
     * @return array<int, string>
     */
    private function welcomeScreenFieldTypeValues(): array
    {
        return ['text', 'email', 'phone', 'number'];
    }

    /**
     * @return array<int, string>
     */
    private function welcomeScreenAttachToValues(): array
    {
        return ['caption_title', 'caption_subtitle', 'file_name'];
    }

    /**
     * @return array<int, array{
     *   id: string,
     *   type: string,
     *   label: string,
     *   help_text: string,
     *   attach_to: string|null,
     *   required: bool,
     *   enabled: bool
     * }>
     */
    private function defaultWelcomeScreenFields(): array
    {
        return [
            [
                'id' => 'name',
                'type' => 'text',
                'label' => 'Name',
                'help_text' => 'Write your name',
                'attach_to' => null,
                'required' => true,
                'enabled' => true,
            ],
        ];
    }

    /**
     * @return array<int, array{
     *   id: string,
     *   type: string,
     *   label: string,
     *   help_text: string,
     *   attach_to: string|null,
     *   required: bool,
     *   enabled: bool
     * }>
     */
    private function normalizeWelcomeScreenFields(mixed $fields): array
    {
        if (! is_array($fields)) {
            return $this->defaultWelcomeScreenFields();
        }

        $allowedTypes = $this->welcomeScreenFieldTypeValues();
        $allowedAttachTo = $this->welcomeScreenAttachToValues();
        $normalized = [];
        $usedIds = [];

        foreach ($fields as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $type = is_string($field['type'] ?? null) && in_array($field['type'], $allowedTypes, true)
                ? $field['type']
                : 'text';
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                $label = 'Field';
            }
            $helpText = trim((string) ($field['help_text'] ?? ''));
            $attachTo = is_string($field['attach_to'] ?? null) && in_array($field['attach_to'], $allowedAttachTo, true)
                ? $field['attach_to']
                : null;
            $required = (bool) ($field['required'] ?? false);
            $enabled = (bool) ($field['enabled'] ?? true);

            $rawId = is_string($field['id'] ?? null) ? trim($field['id']) : '';
            $id = Str::slug($rawId !== '' ? $rawId : "{$label}-{$index}", '_');
            if ($id === '') {
                $id = "field_{$index}";
            }
            if (in_array($id, $usedIds, true)) {
                $id = "{$id}_".Str::random(4);
            }
            $usedIds[] = $id;

            if ($id === 'name') {
                $type = 'text';
                $required = true;
                $enabled = true;
            }

            $normalized[] = [
                'id' => $id,
                'type' => $type,
                'label' => Str::limit($label, 80, ''),
                'help_text' => Str::limit($helpText, 160, ''),
                'attach_to' => $attachTo,
                'required' => $required,
                'enabled' => $enabled,
            ];
        }

        if ($normalized === []) {
            return $this->defaultWelcomeScreenFields();
        }

        $hasName = collect($normalized)->contains(
            fn (array $field): bool => $field['id'] === 'name',
        );
        if (! $hasName) {
            array_unshift($normalized, $this->defaultWelcomeScreenFields()[0]);
        }

        return array_slice($normalized, 0, 12);
    }

    /**
     * @return array{
     *   partner_one_name: string,
     *   partner_two_name: string,
     *   family_name: string,
     *   show_family_name: bool,
     *   bride_parents: string,
     *   groom_parents: string,
     *   godparents: string
     * }
     */
    private function normalizeWeddingDetails(mixed $details): array
    {
        $source = is_array($details) ? $details : [];

        return [
            'partner_one_name' => Str::limit(trim((string) ($source['partner_one_name'] ?? $source['partnerOneName'] ?? '')), 80, ''),
            'partner_two_name' => Str::limit(trim((string) ($source['partner_two_name'] ?? $source['partnerTwoName'] ?? '')), 80, ''),
            'family_name' => Str::limit(trim((string) ($source['family_name'] ?? $source['familyName'] ?? '')), 80, ''),
            'show_family_name' => (bool) ($source['show_family_name'] ?? $source['showFamilyName'] ?? false),
            'bride_parents' => Str::limit(trim((string) ($source['bride_parents'] ?? $source['brideParents'] ?? '')), 160, ''),
            'groom_parents' => Str::limit(trim((string) ($source['groom_parents'] ?? $source['groomParents'] ?? '')), 160, ''),
            'godparents' => Str::limit(trim((string) ($source['godparents'] ?? '')), 160, ''),
        ];
    }

    /**
     * @return array{
     *   partnerOneName: string,
     *   partnerTwoName: string,
     *   familyName: string,
     *   showFamilyName: bool,
     *   brideParents: string,
     *   groomParents: string,
     *   godparents: string
     * }
     */
    private function weddingDetailsPayload(mixed $details): array
    {
        $normalized = $this->normalizeWeddingDetails($details);

        return [
            'partnerOneName' => $normalized['partner_one_name'],
            'partnerTwoName' => $normalized['partner_two_name'],
            'familyName' => $normalized['family_name'],
            'showFamilyName' => $normalized['show_family_name'],
            'brideParents' => $normalized['bride_parents'],
            'groomParents' => $normalized['groom_parents'],
            'godparents' => $normalized['godparents'],
        ];
    }

    /**
     * @param  array<string, mixed>  $guestFields
     * @return array<string, string>
     */
    private function resolveGuestFieldAttachments(Event $event, array $guestFields): array
    {
        if ($guestFields === []) {
            return [];
        }

        $branding = is_array($event->branding) ? $event->branding : [];
        $fields = $this->normalizeWelcomeScreenFields(
            $branding['welcome_screen_fields'] ?? [],
        );
        $fieldsById = collect($fields)->keyBy(
            fn (array $field): string => (string) $field['id'],
        );
        $attachments = [];

        foreach ($guestFields as $fieldId => $value) {
            if (! is_string($fieldId) || ! is_string($value)) {
                continue;
            }
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }

            $field = $fieldsById->get($fieldId);
            if (! is_array($field)) {
                continue;
            }

            $attachTo = $field['attach_to'] ?? null;
            if (! is_string($attachTo) || $attachTo === '') {
                continue;
            }
            if (! in_array($attachTo, $this->welcomeScreenAttachToValues(), true)) {
                continue;
            }
            if (array_key_exists($attachTo, $attachments)) {
                continue;
            }

            $attachments[$attachTo] = Str::limit($trimmed, 255, '');
        }

        return $attachments;
    }

    /**
     * @return array<int, string>
     */
    private function welcomeScreenFontValues(): array
    {
        return [
            'montserrat',
            'poppins',
            'playfair_display',
            'dm_sans',
        ];
    }
}
