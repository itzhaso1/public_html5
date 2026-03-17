<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Notifications\AdminUserMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $adminMessages = collect();
        $notificationsReady = true;

        try {
            $adminMessages = $user->notifications()
                ->where('type', AdminUserMessageNotification::class)
                ->latest()
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            $adminMessages = collect();
            $notificationsReady = false;
        }

        return view('website.customer.profile', [
            'pageTitle' => 'الملف الشخصي',
            'user' => $user,
            'adminMessages' => $adminMessages,
            'notificationsReady' => $notificationsReady,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'min:8', 'max:32'],
        ]);

        $user->update([
            'email' => $data['email'],
            'phone' => !empty($data['phone']) ? preg_replace('/\D+/', '', (string) $data['phone']) : null,
        ]);

        return back()->with('success', 'تم تحديث البريد الإلكتروني بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'كلمة المرور الحالية غير صحيحة.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'تم تحديث كلمة المرور بنجاح.');
    }
}

