<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Support\Email\EmailNotifier;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function isBackofficeUrl(string $url): bool
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return false;
        }

        // Matches /admin/* or localized /ar/admin/* (same for manager)
        return (bool) preg_match('#/(?:[a-z]{2}/)?(?:admin|manager)(?:/|$)#i', $path);
    }

    public function showLoginForm()
    {
        return view('website.auth.login', [
            'pageTitle' => trans('site/site.login_page_title'),
            'redirectTo' => url()->previous(),
            'breadcrumbs' => [
                ['title' => __('site/site.login_page_title')],
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $intended = (string) ($request->session()->get('url.intended') ?? '');
            $redirectTo = trim((string) ($request->input('redirect_to') ?? ''));

            // Never send website user login flow to backoffice dashboard.
            if ($intended !== '' && $this->isBackofficeUrl($intended)) {
                $request->session()->forget('url.intended');
                $intended = '';
            }

            // If intended is the generic customer dashboard, prefer sending user back to the page they came from.
            $customerDash = route('customer.dashboard');
            $shouldPreferBack = $intended !== '' && $customerDash !== '' && rtrim($intended, '/') === rtrim($customerDash, '/');

            if ($redirectTo !== '' && $shouldPreferBack) {
                // Basic safety: avoid redirecting back to auth pages.
                if (
                    ! str_contains($redirectTo, '/login')
                    && ! str_contains($redirectTo, '/register')
                    && ! $this->isBackofficeUrl($redirectTo)
                ) {
                    $request->session()->forget('url.intended');
                    return redirect()->to($redirectTo);
                }
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }

    public function showRegisterForm()
    {
        return view('website.auth.register', [
            'pageTitle' => trans('site/site.register_page_title'),
            'breadcrumbs' => [
                ['title' => __('site/site.register_page_title')],
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'status'   => 'nullable',
        ]);
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        // Best-effort welcome email via the configured API mailer.
        try {
            EmailNotifier::sendAfterCommit(
                (string) $user->email,
                'مرحباً بك في King2Game',
                "أهلاً {$user->name}،\nتم إنشاء حسابك بنجاح. نتمنى لك تجربة ممتعة معنا."
            );
        } catch (\Throwable $e) {
            report($e);
        }

        Auth::login($user);
        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('home');
    }
}