<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\MerchantRequest;
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
        MerchantRequest::create([
            'user_id' => $user->id,
            'name' => trim((string) $data['name']),
            'phone' => $phone !== '' ? $phone : trim((string) $data['phone']),
            'note' => !empty($data['note']) ? trim((string) $data['note']) : null,
            'status' => 'pending',
        ]);

        return redirect()->route('website.diamonds.charge')->with('success', 'تم إرسال طلب التاجر بنجاح ✅ سيتم مراجعته من الإدارة.');
    }
}

