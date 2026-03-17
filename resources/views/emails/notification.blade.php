<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? 'King2Game' }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2ff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Tahoma,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef2ff;padding:28px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #dbe1ff;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="padding:18px 22px;background:linear-gradient(120deg,#0b1220,#172554);text-align:center;">
                            <img src="https://king2game.com/logo.png" alt="King2Game" style="max-width:170px;width:100%;height:auto;display:inline-block;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 24px 18px;text-align:right;" dir="rtl">
                            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.45;font-weight:800;color:#0f172a;">{{ $subject ?? 'إشعار جديد' }}</h1>
                            <div style="font-size:15px;line-height:1.9;color:#334155;white-space:pre-line;">{{ $content ?? '' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;border-top:1px solid #e2e8f0;text-align:right;font-size:12px;color:#64748b;" dir="rtl">
                            King2Game
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

