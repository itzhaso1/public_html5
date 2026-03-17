@component('mail::message')
<div dir="rtl" style="text-align: right; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Arial, sans-serif; color: #0f172a;">
    <div style="text-align: center; margin-bottom: 22px;">
        <img src="https://king2game.com/logo.png" alt="King2Game" style="max-width: 180px; width: 100%; height: auto;">
    </div>

    <h1 style="margin: 0 0 12px; font-size: 26px; line-height: 1.35; font-weight: 800; color: #0b1220;">
        أهلاً بك 👋
    </h1>

    <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.9; color: #334155;">
        مرحباً بك في King2Game، نحن سعداء بانضمامك إلينا
    </p>
</div>

@component('mail::button', ['url' => 'https://king2game.com', 'color' => 'primary'])
ابدأ الآن
@endcomponent

<div dir="rtl" style="text-align: right; margin-top: 12px; font-size: 12px; line-height: 1.8; color: #64748b;">
    King2Game
</div>
@endcomponent
