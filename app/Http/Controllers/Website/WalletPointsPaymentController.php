<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\DiamondCode;
use App\Models\ManualPaymentRequest;
use App\Models\Product;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
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

        $playerId = null;
        $playerCheck = null;
        if ($isGems) {
            $playerId = trim((string) ($data['player_id'] ?? ''));
            $cacheKey = 'shop2topup.player.' . sha1($playerId);

            $check = Cache::get($cacheKey);
            if (!is_array($check) || ($check['success'] ?? false) !== true) {
                $service = new Shop2TopUpService();
                $check = $service->checkPlayer($playerId);
                if (($check['success'] ?? false) === true && !empty($check['player_name'])) {
                    Cache::put($cacheKey, $check, now()->addHours(12));
                }
            }

            if (($check['success'] ?? false) !== true || empty($check['player_name'])) {
                $raw = (string) ($check['msg'] ?? 'فشل التحقق');
                $friendly = in_array($raw, ['NOT_READY', 'DUPLICATE_TASK'], true)
                    ? 'التحقق قيد المعالجة، انتظر قليلًا ثم أعد المحاولة.'
                    : $raw;

                return back()
                    ->withErrors(['error' => 'تعذر التحقق من اسم اللاعب: ' . $friendly])
                    ->withInput();
            }
            $playerCheck = $check;
        }

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

        if ($isCodes) {
            // Instant delivery for codes paid with points.
            try {
                DB::transaction(function () use ($wallet, $user, $product, $points, $data, $request) {
                    $wallet->debit($user, $points, 'purchase_debit', $product, [
                        'product_id' => $product->id,
                        'service_type' => (string) ($product->service_type ?? ''),
                    ]);

                    $mpr = ManualPaymentRequest::create([
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
                        'status' => 'approved',
                        'approved_at' => now(),
                        'ip' => $request->ip(),
                        'user_agent' => (string) $request->userAgent(),
                    ]);

                    $code = DiamondCode::query()
                        ->where('product_id', $product->id)
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->first();

                    if (!$code) {
                        throw new \RuntimeException('نفذت الكمية حالياً. جرّب لاحقاً.');
                    }

                    $code->update([
                        'status' => 'delivered',
                        'user_id' => $user->id,
                        'manual_payment_request_id' => $mpr->id,
                        'delivered_at' => now(),
                    ]);
                });
            } catch (\RuntimeException $e) {
                return back()->withErrors(['error' => $e->getMessage()])->withInput();
            }
        } else {
            // Gems: submit to Shop2TopUp immediately (no admin approval), refund points on failure.
            $mprId = null;
            $trxId = null;

            try {
                DB::transaction(function () use ($wallet, $user, $product, $points, $data, $request, &$mprId, &$trxId) {
                    $wallet->debit($user, $points, 'purchase_debit', $product, [
                        'product_id' => $product->id,
                        'service_type' => (string) ($product->service_type ?? ''),
                    ]);

                    $trxId = (string) Str::uuid();
                    $mpr = ManualPaymentRequest::create([
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
                        'shop2topup_trx_id' => $trxId,
                        'shop2topup_status' => 'SUBMITTING',
                        'ip' => $request->ip(),
                        'user_agent' => (string) $request->userAgent(),
                    ]);
                    $mprId = $mpr->id;
                });
            } catch (\RuntimeException $e) {
                return back()->withErrors(['error' => $e->getMessage()])->withInput();
            }

            $service = new Shop2TopUpService();
            $player = trim((string) ($data['player_id'] ?? ''));
            $offerId = (int) ($product->itemID ?? 0);
            if ($offerId <= 0) {
                // refund points + mark rejected
                $this->refundAndRejectMpr($wallet, $user, $points, $mprId, 'هذا المنتج غير مربوط بعرض Shop2TopUp (itemId).');
                return back()->withErrors(['error' => 'هذا المنتج غير مربوط بعرض Shop2TopUp (itemId).'])->withInput();
            }

            $top = $service->topup($player, $offerId, (string) $trxId);
            if (($top['success'] ?? false) !== true) {
                $msg = (string) ($top['msg'] ?? 'فشل إرسال الطلب');
                $this->refundAndRejectMpr($wallet, $user, $points, $mprId, $msg);
                return back()->withErrors(['error' => $msg])->withInput();
            }

            // Update transaction status (best-effort)
            try {
                $trx = $service->getTransaction((string) $trxId);
                $status = (string) ($trx['status'] ?? '');
                $msg = (string) ($trx['msg'] ?? '');

                ManualPaymentRequest::query()->whereKey($mprId)->update([
                    'shop2topup_status' => $status !== '' ? $status : 'SUBMITTED',
                    'shop2topup_order_id' => $trx['order_id'] ?? null,
                    'shop2topup_secure_id' => $trx['secure_id'] ?? null,
                    'shop2topup_delivery_at' => !empty($trx['delivery_at']) ? $trx['delivery_at'] : null,
                    'shop2topup_response' => $trx,
                ]);

                if ($this->isDeliveredStatus($status)) {
                    ManualPaymentRequest::query()->whereKey($mprId)->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                } elseif ($this->isRefundOrRejectedStatus($status, $msg)) {
                    $reason = $msg !== '' ? $msg : ($status !== '' ? $status : 'رفض المزود');
                    $this->refundAndRejectMpr($wallet, $user, $points, $mprId, $reason);
                    return back()->withErrors(['error' => 'تم رفض الطلب من المزود: ' . $reason . ' — اشحن مرة أخرى.'])->withInput();
                } else {
                    // still processing => keep pending
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Clear cached codes list so out-of-stock products can disappear fast.
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
        }

        return redirect()
            ->route('customer.purchases')
            ->with('success', $isGems ? 'تم إرسال طلب الشحن للمزود ✅ الحالة: قيد المعالجة.' : 'تم تنفيذ طلبك والدفع بالنقاط ✅');
    }

    private function isDeliveredStatus(?string $status): bool
    {
        $s = strtoupper(trim((string) $status));
        if ($s === '') return false;
        return str_contains($s, 'DELIVER') || str_contains($s, 'SUCCESS') || str_contains($s, 'COMPLET');
    }

    private function isRefundOrRejectedStatus(?string $status, ?string $msg): bool
    {
        $s = strtoupper(trim((string) $status));
        $m = strtoupper(trim((string) $msg));
        $hay = $s . ' ' . $m;
        if ($hay === '') return false;

        // Common failure indicators from vendor
        return str_contains($hay, 'REFUND_REGION')
            || str_contains($hay, 'REFUND')
            || str_contains($hay, 'FAILED')
            || str_contains($hay, 'REJECT')
            || str_contains($hay, 'CANCEL')
            || str_contains($hay, 'ERROR');
    }

    private function refundAndRejectMpr(WalletService $wallet, $user, int $points, ?int $mprId, string $message): void
    {
        if (!$mprId) {
            // Best-effort refund only; debit may have failed before mpr creation.
            try { $wallet->credit($user, $points, 'refund_credit', null, ['message' => $message]); } catch (\Throwable $e) {}
            return;
        }

        try {
            DB::transaction(function () use ($wallet, $user, $points, $mprId, $message) {
                $mpr = ManualPaymentRequest::query()->whereKey($mprId)->lockForUpdate()->first();
                if (!$mpr) return;

                if (empty($mpr->points_refunded_at) && (int) ($mpr->points_spent ?? 0) > 0) {
                    $wallet->credit($user, $points, 'refund_credit', $mpr, [
                        'reason' => 'points_purchase_failed',
                        'message' => $message,
                    ]);
                    $mpr->points_refunded_at = now();
                }

                $mpr->status = 'rejected';
                $mpr->admin_note = trim(($mpr->admin_note ? ($mpr->admin_note . "\n") : '') . 'AUTO: ' . $message);
                $mpr->save();
            });
        } catch (\Throwable $e) {
            // ignore
        }
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

