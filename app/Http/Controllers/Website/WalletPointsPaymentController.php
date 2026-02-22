<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\DiamondCode;
use App\Models\ManualPaymentRequest;
use App\Models\Product;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletPointsPaymentController extends Controller
{
    public function create(Request $request, Product $product, WalletService $wallet)
    {
        $this->ensureProductSupportsPoints($product);

        $user = $request->user();

        return view('website.diamonds.points_payment', [
            'pageTitle' => 'الدفع بالنقاط',
            'product' => $product,
            'user' => $user,
            'pointsPrice' => (int) $product->points_price,
            'pointPrices' => $wallet->getPointPrices(),
        ]);
    }

    public function store(Request $request, Product $product, WalletService $wallet)
    {
        $this->ensureProductSupportsPoints($product);

        $isCodes = ($product->service_type ?? null) === 'codes';
        $isGems = ($product->service_type ?? null) === 'gems';

        $rules = [
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ];
        if ($isGems) {
            $rules['player_id'] = ['required', 'string', 'min:3', 'max:50'];
        }

        $data = $request->validate($rules);

        if ($isCodes) {
            // Prevent overselling codes stock (available > pending).
            $available = DiamondCode::query()
                ->where('product_id', $product->id)
                ->where('status', 'available')
                ->count();

            $pending = ManualPaymentRequest::query()
                ->where('product_id', $product->id)
                ->where('status', 'pending')
                ->count();

            if ($available <= $pending) {
                return back()->withErrors(['error' => 'نفذت الكمية حالياً. جرّب لاحقاً.']);
            }
        }

        $user = $request->user();
        $points = (int) $product->points_price;

        try {
            DB::transaction(function () use ($wallet, $user, $product, $points, $data, $request) {
                // Deduct points first (locked in WalletService).
                $wallet->debit($user, $points, 'purchase_debit', $product, [
                    'product_id' => $product->id,
                    'service_type' => (string) ($product->service_type ?? ''),
                ]);

                ManualPaymentRequest::create([
                    'reference' => (string) Str::uuid(),
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'player_id' => $data['player_id'] ?? null,
                    'contact_phone' => $data['contact_phone'] ?? null,
                    'contact_email' => $data['contact_email'] ?? null,
                    'amount' => (float) ($product->price ?? 0),
                    'currency' => 'SAR',
                    'payment_method' => 'wallet_points',
                    'points_spent' => $points,
                    'receipt_path' => null,
                    'status' => 'pending',
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // Clear cached codes list so out-of-stock products can disappear fast.
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
        }

        return redirect()
            ->route('customer.purchases')
            ->with('success', 'تم إنشاء طلبك والدفع بالنقاط ✅ سيتم تنفيذ الطلب بعد المراجعة.');
    }

    private function ensureProductSupportsPoints(Product $product): void
    {
        $serviceType = (string) ($product->service_type ?? '');
        if (! in_array($serviceType, ['gems', 'codes'], true)) {
            abort(404);
        }

        $pp = (int) ($product->points_price ?? 0);
        if ($pp <= 0) {
            abort(404);
        }
    }
}

