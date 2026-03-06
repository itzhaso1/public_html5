<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\DiamondCode;
use App\Models\ManualPaymentRequest;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use Illuminate\Http\JsonResponse;
use App\Support\WhatsApp\WhatsAppNumber;
use Illuminate\Validation\Rule;

class ManualPaymentController extends Controller
{
    private const WA_DIAL_BY_COUNTRY = [
        'SA' => '966', // Saudi Arabia
        'JO' => '962', // Jordan
        'AE' => '971', // UAE
        'KW' => '965', // Kuwait
        'QA' => '974', // Qatar
        'BH' => '973', // Bahrain
        'OM' => '968', // Oman
        'IQ' => '964', // Iraq
        'LB' => '961', // Lebanon
        'PS' => '970', // Palestine
        'YE' => '967', // Yemen
        'SY' => '963', // Syria
        'EG' => '20',  // Egypt
        'SD' => '249', // Sudan
        'LY' => '218', // Libya
        'TN' => '216', // Tunisia
        'DZ' => '213', // Algeria
        'MA' => '212', // Morocco
        'MR' => '222', // Mauritania
        'SO' => '252', // Somalia
        'DJ' => '253', // Djibouti
        'KM' => '269', // Comoros
    ];

    private function dialForCountry(?string $country): string
    {
        $c = strtoupper(trim((string) $country));
        return self::WA_DIAL_BY_COUNTRY[$c] ?? '966';
    }

    private function isEnabledForProduct(Product $product): bool
    {
        $isCodes = ($product->service_type ?? null) === 'codes';
        $col = $isCodes ? 'codes_enabled' : 'charge_enabled';

        try {
            if (!Schema::hasTable('settings') || !Schema::hasColumn('settings', $col)) {
                return true;
            }
        } catch (\Throwable $e) {
            return true;
        }

        try {
            $s = Cache::get('app_settings') ?: Setting::query()->latest()->first();
            return (bool) ($s?->{$col} ?? true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    private function getEnabledPaymentMethods(): array
    {
        // Prefer DB-defined methods if table exists & has rows.
        try {
            if (Schema::hasTable('payment_methods')) {
                $rows = PaymentMethod::query()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                if ($rows->count() > 0) {
                    $enabled = [];
                    foreach ($rows as $pm) {
                        if (!($pm->enabled ?? false)) continue;
                        $key = (string) ($pm->key ?? '');
                        if ($key === '') continue;

                        $details = (array) ($pm->details ?? []);
                        $enabled[$key] = array_merge([
                            'enabled' => true,
                            'title' => (string) ($pm->title ?? $key),
                        ], $details);
                    }

                    return $enabled;
                }
            }
        } catch (\Throwable $e) {
            // fallback to config
        }

        // Fallback: env/config based methods
        $methods = (array) config('bank.methods', []);
        $enabled = [];

        foreach ($methods as $key => $m) {
            if (!is_array($m)) continue;
            if (!($m['enabled'] ?? false)) continue;

            // Binance method needs at least address or link configured
            if ($key === 'binance_trc20') {
                $addr = trim((string) ($m['address'] ?? ''));
                $link = trim((string) ($m['link'] ?? ''));
                if ($addr === '' && $link === '') {
                    continue;
                }
            }

            $enabled[$key] = $m;
        }

        return $enabled;
    }

    private function getAllowedChargeMethodKeys(): array
    {
        try {
            if (Schema::hasTable('payment_methods')) {
                $rows = PaymentMethod::query()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                if ($rows->count() > 0) {
                    return $rows
                        ->where('enabled', true)
                        ->where('allowed_for_charge', true)
                        ->pluck('key')
                        ->values()
                        ->all();
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return (array) config('bank.charge_method_keys', []);
    }


    private function forgetCodesPageCache(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
        }
    }

    private function ensureCodesAvailabilityOrRedirect(Product $product)
    {
        $available = DiamondCode::query()
            ->where('product_id', $product->id)
            ->where('status', 'available')
            ->count();

        $pending = ManualPaymentRequest::query()
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->count();

        if (($available - $pending) <= 0) {
            return redirect()
                ->route('website.diamonds.codes')
                ->withErrors(['error' => 'نفذت الكمية لهذا المنتج حالياً.']);
        }

        return null;
    }

    public function create(Product $product)
    {
        abort_unless(config('bank.enabled'), 404);

        if (! $this->isEnabledForProduct($product)) {
            $msg = (($product->service_type ?? null) === 'codes') ? 'قسم الأكواد غير متاح حالياً.' : 'قسم الشحن غير متاح حالياً.';
            return redirect()->route('home')->withErrors(['error' => $msg]);
        }

        $isCodes = ($product->service_type ?? null) === 'codes';
        if ($isCodes) {
            $redirect = $this->ensureCodesAvailabilityOrRedirect($product);
            if ($redirect) return $redirect;
        }

        return view('website.diamonds.manual_payment', [
            'product' => $product,
            'pageTitle' => 'الدفع اليدوي',
            'paymentMethods' => $this->getEnabledPaymentMethods(),
            'allowedChargeMethodKeys' => $this->getAllowedChargeMethodKeys(),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        abort_unless(config('bank.enabled'), 404);

        if (! $this->isEnabledForProduct($product)) {
            $msg = (($product->service_type ?? null) === 'codes') ? 'قسم الأكواد غير متاح حالياً.' : 'قسم الشحن غير متاح حالياً.';
            return back()->withErrors(['error' => $msg])->withInput();
        }

        $isCodes = ($product->service_type ?? null) === 'codes';
        if ($isCodes) {
            $redirect = $this->ensureCodesAvailabilityOrRedirect($product);
            if ($redirect) return $redirect;
        }

        $paymentMethods = $this->getEnabledPaymentMethods();
        $allowedKeys = array_keys($paymentMethods);
        $isGems = ! $isCodes;
        if ($isGems) {
            $allowedKeys = array_values(array_intersect($allowedKeys, $this->getAllowedChargeMethodKeys()));
        }

        $rules = [
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $allowedKeys)],
        ];

        // For gems top-up we need the player's ID. For codes we don't.
        if (! $isCodes) {
            $rules['player_id'] = ['required', 'string', 'max:64'];
        }

        $existingPhone = WhatsAppNumber::normalize($request->user()?->phone ?? '')
            ?: WhatsAppNumber::normalize($request->user()?->profile?->phone ?? '');
        // If no phone saved on account, require it so WhatsApp confirmation can be sent.
        $rules['contact_phone'] = ['nullable', 'string', 'max:64'];
        $rules['contact_phone_country'] = ['nullable', Rule::in(array_keys(self::WA_DIAL_BY_COUNTRY))];
        $rules['contact_phone_local'] = ['nullable', 'string', 'max:32'];

        $data = $request->validate($rules);

        $contactPhone = WhatsAppNumber::normalize($data['contact_phone'] ?? '');
        if ($contactPhone === '') {
            $dial = $this->dialForCountry($data['contact_phone_country'] ?? null);
            $local = WhatsAppNumber::normalize($data['contact_phone_local'] ?? '');
            $local = ltrim($local, '0');
            $contactPhone = $dial . $local;
        }
        $contactPhone = WhatsAppNumber::normalize($contactPhone);

        if ($existingPhone === '' && $contactPhone === '') {
            return back()->withErrors(['contact_phone' => 'رقم الواتساب مطلوب لاستلام إشعار الطلب.'])->withInput();
        }
        if ($contactPhone !== '' && $existingPhone === '' && $request->user()) {
            try {
                $request->user()->update(['phone' => $contactPhone]);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        try {
            Storage::disk('public')->makeDirectory('manual-payments');

            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                if (! $file->isValid()) {
                    return back()->withErrors(['receipt' => 'فشل رفع الإيصال، حاول مرة أخرى.'])->withInput();
                }

                $receiptPath = Storage::disk('public')->putFile('manual-payments', $file);
                if (! $receiptPath) {
                    return back()->withErrors(['receipt' => 'تعذر حفظ الإيصال على السيرفر.'])->withInput();
                }
            }
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['receipt' => 'حدث خطأ أثناء رفع الإيصال.'])->withInput();
        }

        $reservedDiamondCodeId = null;
        if ($isCodes && (bool) ($product->is_lucky_draw_codes ?? false)) {
            // Reserve a lucky code (weighted random) to avoid duplicates on concurrent requests.
            try {
                $reservedDiamondCodeId = DB::transaction(function () use ($product) {
                    // Exclude already reserved codes by other pending requests.
                    $reservedIds = ManualPaymentRequest::query()
                        ->where('product_id', $product->id)
                        ->where('status', 'pending')
                        ->whereNotNull('reserved_diamond_code_id')
                        ->pluck('reserved_diamond_code_id')
                        ->all();

                    $candidates = \App\Models\DiamondCode::query()
                        ->where('product_id', $product->id)
                        ->where('status', 'available')
                        ->when(!empty($reservedIds), fn($q) => $q->whereNotIn('id', $reservedIds))
                        ->select(['id', 'luck_weight'])
                        ->lockForUpdate()
                        ->get();

                    if ($candidates->isEmpty()) {
                        return null;
                    }

                    $total = 0;
                    $items = [];
                    foreach ($candidates as $c) {
                        $w = (int) ($c->luck_weight ?? 1);
                        if ($w <= 0) $w = 1;
                        $total += $w;
                        $items[] = ['id' => (int) $c->id, 'w' => $w];
                    }

                    if ($total <= 0) {
                        return (int) ($items[0]['id'] ?? 0) ?: null;
                    }

                    $r = random_int(1, $total);
                    $acc = 0;
                    foreach ($items as $it) {
                        $acc += $it['w'];
                        if ($r <= $acc) {
                            return (int) $it['id'];
                        }
                    }

                    return (int) ($items[count($items) - 1]['id'] ?? 0) ?: null;
                });
            } catch (\Throwable $e) {
                report($e);
                $reservedDiamondCodeId = null;
            }

            if (! $reservedDiamondCodeId) {
                return back()->withErrors(['error' => 'لا يوجد أكواد متاحة للقرعة حالياً.'])->withInput();
            }
        }

        $mpr = ManualPaymentRequest::create([
            'reference' => (string) Str::uuid(),
            'product_id' => $product->id,
            'reserved_diamond_code_id' => $reservedDiamondCodeId,
            'user_id' => Auth::id(),
            // `player_id` is required for gems and not required for codes.
            'player_id' => $data['player_id'] ?? '-',
            'contact_phone' => $contactPhone !== '' ? $contactPhone : null,
            'contact_email' => null,
            'amount' => (float) $product->price,
            'currency' => 'SAR',
            'payment_method' => $data['payment_method'],
            'receipt_path' => $receiptPath ?? null,
            'status' => 'pending',
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
        ]);

        if ($isCodes) {
            // After creating a pending request, the product may become effectively out-of-stock.
            $this->forgetCodesPageCache();
        }

        return redirect()->route('website.diamonds.manual_payment.thanks', ['reference' => $mpr->reference]);
    }

    public function checkPlayerName(Request $request): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'string', 'min:3', 'max:64'],
        ]);

        $playerId = trim((string) $data['player_id']);
        $cacheKey = 'shop2topup.player.' . sha1($playerId);

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ($cached['success'] ?? false) === true) {
            return response()->json(array_merge(['cached' => true], $cached));
        }

        $service = new Shop2TopUpService();
        $res = $service->checkPlayer($playerId);

        // Cache only successful lookups to reduce API calls and avoid freezes.
        if (($res['success'] ?? false) === true && !empty($res['player_name'])) {
            Cache::put($cacheKey, $res, now()->addHours(12));
        }

        return response()->json($res);
    }

    public function thanks(string $reference)
    {
        $mpr = ManualPaymentRequest::query()
            ->where('reference', $reference)
            ->with(['product'])
            ->firstOrFail();

        return view('website.diamonds.manual_payment_thanks', [
            'mpr' => $mpr,
            'pageTitle' => 'تم استلام طلبك',
        ]);
    }
}

