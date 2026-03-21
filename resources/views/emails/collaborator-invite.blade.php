@extends('emails.layouts.standard')

@section('body')
    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        Hi,
    </p>

    <p style="margin:0 0 14px 0;font-size:14px;line-height:1.7;color:#111827;">
        <strong>{{ $inviterName }}</strong> invited you to collaborate on
        <strong>{{ $eventName }}</strong> in {{ config('app.name', 'Kululu') }}.
    </p>

    <p style="margin:0 0 18px 0;font-size:14px;line-height:1.7;color:#111827;">
        Your access remains <strong>Invited</strong> until you accept this invitation.
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 18px 0;">
        <tr>
            <td>
                <a
                    href="{!! $acceptUrl !!}"
                    style="display:inline-block;padding:11px 18px;background:#111827;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;"
                >
                    Accept Invitation
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px 0;font-size:12px;line-height:1.6;color:#6b7280;">
        If the button does not work, copy this URL:
    </p>
    <p style="margin:0 0 18px 0;font-size:12px;line-height:1.6;color:#374151;word-break:break-all;">
        {!! $acceptUrl !!}
    </p>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
        This link expires on {{ $expiresAt }}.
    </p>
@endsection
