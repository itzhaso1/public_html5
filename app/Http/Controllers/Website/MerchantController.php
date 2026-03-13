<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\MerchantRequest;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ((bool) ($user->is_merchant ?? false)) {
            return redirect()->route('website.diamonds.charge')->with('success', 'أنت تاجر بالفعل ✅');
        }

        $existing = MerchantRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        return view('website.merchant.apply', [
            'pageTitle' => 'طلب تاجر',
            'existingRequest' => $existing,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('auth.login');
        }

        if ((bool) ($user->is_merchant ?? false)) {
            return redirect()->route('website.diamonds.charge')->with('success', 'أنت تاجر بالفعل ✅');
        }

        $pending = MerchantRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($pending) {
            return back()->withErrors(['error' => 'لديك طلب تاجر قيد المراجعة بالفعل.'])->withInput();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'min:8', 'max:32'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $phone = WhatsAppNumber::normalize($data['phone'] ?? '');
        $created = MerchantRequest::create([
            'user_id' => $user->id,
            'name' => trim((string) $data['name']),
            'phone' => $phone !== '' ? $phone : trim((string) $data['phone']),
            'note' => !empty($data['note']) ? trim((string) $data['note']) : null,
            'status' => 'pending',
        ]);

        // Email admin on new merchant request (optional).
        if ((bool) config('services.email_notify.enabled', false) && (bool) config('services.email_notify.notify_admin', true)) {
            $emails = EmailNotifier::adminRecipients();
            if (!empty($emails)) {
                $name = trim((string) ($user->name ?? $created->name ?? ''));
                $email = trim((string) ($user->email ?? ''));
                $subject = 'طلب جديد: التقديم كتاجر';
                $text = trim(
                    "تم استلام طلب تاجر جديد\n" .
                    ($name !== '' ? "الاسم: {$name}\n" : '') .
                    "User ID: {$user->id}\n" .
                    "الهاتف: {$created->phone}\n" .
                    (filter_var($email, FILTER_VALIDATE_EMAIL) ? "Email: {$email}\n" : '') .
                    (!empty($created->note) ? ("ملاحظة: " . $created->note . "\n") : '')
                );
                EmailNotifier::sendAfterCommit($emails, $subject, $text);
            }
        }

        return redirect()->route('website.diamonds.charge')->with('success', 'تم إرسال طلب التاجر بنجاح ✅ سيتم مراجعته من الإدارة.');
    }
}

