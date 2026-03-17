<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px;background:#111827;color:#ffffff;font-size:16px;font-weight:700;">
                            {{ config('app.name', 'King2Game') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;color:#111827;">{{ $subject ?? 'إشعار جديد' }}</h1>
                            <div style="font-size:14px;line-height:1.8;color:#374151;white-space:pre-line;">{{ $content ?? '' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                            {{ config('app.name', 'King2Game') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

