<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f5;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px;max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                    @include('emails.partials.header', [
                        'title' => $headerTitle ?? config('app.name', 'Kululu'),
                        'subtitle' => $headerSubtitle ?? null,
                    ])

                    <tr>
                        <td style="padding:28px 32px;">
                            @yield('body')
                        </td>
                    </tr>

                    @include('emails.partials.footer', [
                        'footerText' => $footerText ?? null,
                    ])
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
