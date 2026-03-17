<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Avoid 500s when migrations are not applied on production.
            if (!Schema::hasTable('password_reset_tokens')) {
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'ميزة إعادة تعيين كلمة المرور غير مفعّلة حالياً (جدول password_reset_tokens غير موجود). شغّل: php artisan migrate --force']);
            }
        } catch (\Throwable $e) {
            // If DB is unavailable/misconfigured, show friendly message.
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'حدث خطأ في قاعدة البيانات. حاول لاحقاً أو تواصل مع الإدارة.']);
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'تعذر إرسال رابط إعادة التعيين حالياً. تأكد من إعدادات SendGrid ثم أعد المحاولة.']);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
