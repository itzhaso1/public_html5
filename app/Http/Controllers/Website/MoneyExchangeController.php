<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\MoneyExchangeRequest;
use App\Models\MoneyExchangeSetting;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\WhatsApp\WhatsAppNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class MoneyExchangeController extends Controller
{
    private function isEnabled(): bool
    {
        try {
            if (!Schema::hasTable('settings') || !Schema::hasColumn('settings', 'money_exchange_enabled')) {
                return true;
            }
        } catch (\Throwable $e) {
            return true;
        }

        try {
            $settings = Cache::get('app_settings');
            if (!$settings) {
                $settings = Setting::query()->latest()->first();
            }
            return (bool) ($settings?->money_exchange_enabled ?? true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function getSettings(): ?MoneyExchangeSetting
    {
        /** @var MoneyExchangeSetting|null $s */
        $s = MoneyExchangeSetting::query()->latest('id')->first();
        if (! $s || ! $s->enabled || empty($s->sar_per_usdt) || empty($s->usdt_to_sar_rate)) {
            return null;
        }
        return $s;
    }

    public function index()
    {
        if (! $this->isEnabled()) {
            return redirect()->route('home')->with('error', 'خدمة تحويل الأموال غير متاحة حالياً.');
        }

        $settings = $this->getSettings();

        return view('website.money_exchange.index', [
            'pageTitle' => 'تحويل الأموال / تبادل العملات',
            'settings' => $settings,
        ]);
    }

    public function store(Request $request)
    {
        if (! $this->isEnabled()) {
            return back()->withErrors(['error' => 'الخدمة غير متاحة حالياً.'])->withInput();
        }

        $settings = $this->getSettings();
        if (! $settings) {
            return back()->withErrors(['error' => 'الخدمة غير متاحة حالياً.'])->withInput();
        }

        $data = $request->validate([
            'direction' => ['required', 'in:sar_to_usdt,usdt_to_sar'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'contact_phone' => ['nullable', 'string', 'min:8', 'max:32'],

            // SAR -> USDT destination
            'destination_type' => ['nullable', 'in:trc20,binance_id,email'],
            'destination_value' => ['nullable', 'string', 'max:190'],

            // USDT -> SAR bank details
            'bank_name' => ['nullable', 'string', 'max:190'],
            'account_name' => ['nullable', 'string', 'max:190'],
            'account_number' => ['nullable', 'string', 'max:190'],
            'iban' => ['nullable', 'string', 'max:190'],
        ]);

        $existingPhone = WhatsAppNumber::normalize($request->user()?->phone ?? '')
            ?: WhatsAppNumber::normalize($request->user()?->profile?->phone ?? '');
        $contactPhone = WhatsAppNumber::normalize($data['contact_phone'] ?? '');
        if ($existingPhone === '' && $contactPhone === '') {
            return back()->withErrors(['contact_phone' => 'رقم الواتساب مطلوب لاستلام إشعار الطلب.'])->withInput();
        }
        if ($existingPhone === '' && $contactPhone !== '' && $request->user()) {
            try {
                $request->user()->update(['phone' => $contactPhone]);
            } catch (\Throwable $e) {}
        }

        $direction = (string) $data['direction'];
        $amount = (float) $data['amount'];

        if ($direction === 'sar_to_usdt') {
            if ($settings->min_sar > 0 && $amount < (float) $settings->min_sar) {
                return back()->withErrors(['amount' => 'الحد الأدنى للتحويل هو ' . $settings->min_sar . ' SAR'])->withInput();
            }
            if ($settings->max_sar > 0 && $amount > (float) $settings->max_sar) {
                return back()->withErrors(['amount' => 'الحد الأعلى للتحويل هو ' . $settings->max_sar . ' SAR'])->withInput();
            }

            $destType = $data['destination_type'] ?? null;
            $destValue = trim((string) ($data['destination_value'] ?? ''));
            if (! $destType || $destValue === '') {
                return back()->withErrors(['destination_value' => 'ضع عنوان TRC20 أو Binance ID أو البريد.'])->withInput();
            }

            $amountTo = $amount / (float) $settings->sar_per_usdt;
            $amountTo = round($amountTo, 4);
        } else {
            if ($settings->min_usdt > 0 && $amount < (float) $settings->min_usdt) {
                return back()->withErrors(['amount' => 'الحد الأدنى للتحويل هو ' . $settings->min_usdt . ' USDT'])->withInput();
            }
            if ($settings->max_usdt > 0 && $amount > (float) $settings->max_usdt) {
                return back()->withErrors(['amount' => 'الحد الأعلى للتحويل هو ' . $settings->max_usdt . ' USDT'])->withInput();
            }

            $bankName = trim((string) ($data['bank_name'] ?? ''));
            $accountName = trim((string) ($data['account_name'] ?? ''));
            $accountNumber = trim((string) ($data['account_number'] ?? ''));
            $iban = trim((string) ($data['iban'] ?? ''));

            if ($bankName === '' || $accountName === '') {
                return back()->withErrors(['bank_name' => 'ضع اسم البنك واسم صاحب الحساب.'])->withInput();
            }
            if ($accountNumber === '' && $iban === '') {
                return back()->withErrors(['iban' => 'ضع رقم الحساب أو IBAN.'])->withInput();
            }

            $amountTo = $amount * (float) $settings->usdt_to_sar_rate;
            $amountTo = round($amountTo, 2);
        }

        $ref = strtoupper(Str::random(10));
        while (MoneyExchangeRequest::query()->where('reference', $ref)->exists()) {
            $ref = strtoupper(Str::random(10));
        }

        MoneyExchangeRequest::create([
            'reference' => $ref,
            'user_id' => $request->user()->id,
            'direction' => $direction,
            'amount_from' => $amount,
            'amount_to' => $amountTo,
            'sar_per_usdt' => (float) $settings->sar_per_usdt,
            'profit_percent' => (float) $settings->profit_percent,
            'usdt_to_sar_rate' => (float) $settings->usdt_to_sar_rate,
            'destination_type' => $direction === 'sar_to_usdt' ? ($data['destination_type'] ?? null) : null,
            'destination_value' => $direction === 'sar_to_usdt' ? trim((string) ($data['destination_value'] ?? '')) : null,
            'bank_name' => $direction === 'usdt_to_sar' ? trim((string) ($data['bank_name'] ?? '')) : null,
            'account_name' => $direction === 'usdt_to_sar' ? trim((string) ($data['account_name'] ?? '')) : null,
            'account_number' => $direction === 'usdt_to_sar' ? (trim((string) ($data['account_number'] ?? '')) ?: null) : null,
            'iban' => $direction === 'usdt_to_sar' ? (trim((string) ($data['iban'] ?? '')) ?: null) : null,
            'status' => 'pending',
        ]);

        return redirect()->route('website.money_exchange.thanks', ['reference' => $ref]);
    }

    public function thanks(string $reference)
    {
        $req = MoneyExchangeRequest::query()
            ->where('reference', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('website.money_exchange.thanks', [
            'pageTitle' => 'تم استلام طلبك',
            'req' => $req,
            'settings' => MoneyExchangeSetting::query()->latest('id')->first(),
        ]);
    }

    public function list()
    {
        $requests = MoneyExchangeRequest::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('website.money_exchange.list', [
            'pageTitle' => 'طلباتي - تحويل الأموال',
            'requests' => $requests,
        ]);
    }

    public function show(string $reference)
    {
        $req = MoneyExchangeRequest::query()
            ->where('reference', $reference)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('website.money_exchange.show', [
            'pageTitle' => 'تفاصيل طلب التحويل',
            'req' => $req,
        ]);
    }
}

