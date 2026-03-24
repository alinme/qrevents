@extends('emails.layouts.standard')

@section('body')
    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        {{ $headline }}
    </p>

    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        {{ $attendanceLine }}
    </p>

    <p style="margin:0 0 18px 0;font-size:14px;line-height:1.7;color:#111827;">
        {{ $pendingLine }}
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 18px 0;">
        <tr>
            <td>
                <a
                    href="{!! $summary['guestListUrl'] !!}"
                    style="display:inline-block;padding:11px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;"
                >
                    Open guest list
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 18px 0;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">
                    RSVP snapshot
                </p>

                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Family / Name:</strong> {{ $summary['guestPartyName'] }}
                </p>

                @if (($summary['confirmedAttendeesCount'] ?? null) !== null)
                    <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                        <strong>Attendees:</strong> {{ $summary['confirmedAttendeesCount'] }}
                    </p>
                @endif

                @if ($mealPreferenceLabel)
                    <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                        <strong>Meal preference:</strong> {{ $mealPreferenceLabel }}
                    </p>
                @endif

                @if (filled($summary['guestNames']))
                    <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                        <strong>Guest names:</strong> {{ $summary['guestNames'] }}
                    </p>
                @endif

                @if (filled($summary['responseNotes']))
                    <p style="margin:0;font-size:14px;line-height:1.6;color:#111827;">
                        <strong>Note:</strong> {{ $summary['responseNotes'] }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
        {{ $totalsLine }}
    </p>
@endsection
