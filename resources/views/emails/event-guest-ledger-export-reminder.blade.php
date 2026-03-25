@extends('emails.layouts.standard')

@section('body')
    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        Your guest ledger for <strong>{{ $summary['eventName'] }}</strong> is getting close to its retention end.
    </p>

    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        You have <strong>{{ $summary['daysRemaining'] }} days</strong> left before the archive window closes. We recommend exporting the report now so the family list, RSVP notes, and gift ledger stay with you forever.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 18px 0;border:1px solid #e5e7eb;border-radius:10px;background:#fafafa;">
        <tr>
            <td style="padding:18px 20px;">
                <p style="margin:0 0 10px 0;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#6b7280;">
                    Export snapshot
                </p>

                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Retention ends:</strong> {{ $summary['retentionEndsAt'] }}
                </p>

                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Guest parties:</strong> {{ $summary['partyCount'] }}
                </p>

                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Invited seats:</strong> {{ $summary['invitedAttendeesCount'] }}
                </p>

                <p style="margin:0 0 8px 0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Confirmed seats:</strong> {{ $summary['confirmedAttendeesCount'] }}
                </p>

                <p style="margin:0;font-size:14px;line-height:1.6;color:#111827;">
                    <strong>Still pending:</strong> {{ $summary['pendingPartyCount'] }}
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 18px 0;">
        <tr>
            <td style="padding-right:10px;">
                <a
                    href="{!! $summary['guestReportUrl'] !!}"
                    style="display:inline-block;padding:11px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;"
                >
                    {{ $actionLabel }}
                </a>
            </td>
            <td>
                <a
                    href="{!! $summary['guestLedgerExportUrl'] !!}"
                    style="display:inline-block;padding:11px 18px;background:#ffffff;color:#111827;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;border:1px solid #d1d5db;"
                >
                    Download CSV
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
        This reminder is based on the current retention window and will not repeat for the same retention date.
    </p>
@endsection
