<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentRequest;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WalletPointsOrderController extends Controller
{
    public function index()
    {
        $requests = ManualPaymentRequest::query()
            ->where('payment_method', 'wallet_points')
            ->with(['product', 'user', 'diamondCode'])
            ->latest()
            ->paginate(30);

        return view('dashboard.admin.wallet_points_orders.index', [
            'pageTitle' => 'طلبات الشحن بالنقاط',
            'requests' => $requests,
        ]);
    }

    public function show(ManualPaymentRequest $manualPaymentRequest)
    {
        abort_if(($manualPaymentRequest->payment_method ?? null) !== 'wallet_points', 404);

        $manualPaymentRequest->load(['product', 'user', 'diamondCode']);

        return view('dashboard.admin.wallet_points_orders.show', [
            'pageTitle' => 'تفاصيل طلب النقاط',
            'mpr' => $manualPaymentRequest,
        ]);
    }

    public function receipt(ManualPaymentRequest $manualPaymentRequest)
    {
        abort_if(($manualPaymentRequest->payment_method ?? null) !== 'wallet_points', 404);
        abort_if(! $manualPaymentRequest->receipt_path, 404);
        abort_if(! Storage::disk('public')->exists($manualPaymentRequest->receipt_path), 404);

        return Storage::disk('public')->response($manualPaymentRequest->receipt_path);
    }

    public function refreshTransaction(Request $request, ManualPaymentRequest $manualPaymentRequest, WalletService $wallet)
    {
        abort_if(($manualPaymentRequest->payment_method ?? null) !== 'wallet_points', 404);

        $manualPaymentRequest->loadMissing(['product', 'user']);
        if (($manualPaymentRequest->product?->service_type ?? null) !== 'gems') {
            return back()->withErrors(['error' => 'هذا الطلب ليس شحن جواهر.']);
        }
        if (empty($manualPaymentRequest->shop2topup_trx_id)) {
            return back()->withErrors(['error' => 'رقم العملية غير موجود.']);
        }

        $service = new Shop2TopUpService();
        $trx = $service->getTransaction((string) $manualPaymentRequest->shop2topup_trx_id);
        if (($trx['success'] ?? false) !== true) {
            $msg = (string) ($trx['msg'] ?? 'تعذر جلب الحالة');
            return back()->withErrors(['error' => 'Shop2TopUp: ' . $msg]);
        }

        $status = (string) ($trx['status'] ?? '');
        $msg = (string) ($trx['msg'] ?? '');

        DB::transaction(function () use ($manualPaymentRequest, $trx, $status, $msg, $wallet) {
            /** @var ManualPaymentRequest $mpr */
            $mpr = ManualPaymentRequest::query()->whereKey($manualPaymentRequest->id)->lockForUpdate()->firstOrFail();

            $mpr->shop2topup_status = $status !== '' ? $status : $mpr->shop2topup_status;
            $mpr->shop2topup_order_id = $trx['order_id'] ?? $mpr->shop2topup_order_id;
            $mpr->shop2topup_secure_id = $trx['secure_id'] ?? $mpr->shop2topup_secure_id;
            $mpr->shop2topup_delivery_at = !empty($trx['delivery_at']) ? $trx['delivery_at'] : $mpr->shop2topup_delivery_at;
            $mpr->shop2topup_response = $trx;

            $hay = strtoupper(trim($status . ' ' . $msg));
            $isDelivered = $hay !== '' && (
                str_contains($hay, 'DELIVER') ||
                str_contains($hay, 'SUCCESS') ||
                str_contains($hay, 'COMPLET') ||
                str_contains($hay, 'DONE')
            );
            $isFailed = $hay !== '' && (
                str_contains($hay, 'REFUND_REGION') ||
                str_contains($hay, 'REFUND') ||
                str_contains($hay, 'FAILED') ||
                str_contains($hay, 'REJECT') ||
                str_contains($hay, 'CANCEL') ||
                str_contains($hay, 'ERROR')
            );

            if ($isDelivered) {
                $mpr->status = 'approved';
                $mpr->approved_at = $mpr->approved_at ?: now();
            } elseif ($isFailed) {
                $mpr->status = 'rejected';
                $mpr->admin_note = trim(($mpr->admin_note ? ($mpr->admin_note . "\n") : '') . 'AUTO: تم رفض/استرجاع من المزود (' . ($msg ?: $status ?: 'رفض') . ')');

                $points = (int) ($mpr->points_spent ?? 0);
                if ($points > 0 && empty($mpr->points_refunded_at) && $mpr->user) {
                    $wallet->credit($mpr->user, $points, 'refund_credit', $mpr, [
                        'reason' => 'vendor_reject',
                        'message' => $msg ?: $status,
                    ]);
                    $mpr->points_refunded_at = now();
                }
            }

            $mpr->save();
        });

        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
            Cache::forget("diamonds.charge.$locale");
        }

        return back()->with('success', 'تم تحديث حالة المزود ✅');
    }

    public function destroy(Request $request, ManualPaymentRequest $manualPaymentRequest, WalletService $wallet)
    {
        abort_if(($manualPaymentRequest->payment_method ?? null) !== 'wallet_points', 404);

        $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        // Refund points (best-effort) before delete, if not already refunded.
        try {
            DB::transaction(function () use ($manualPaymentRequest, $wallet) {
                /** @var ManualPaymentRequest $mpr */
                $mpr = ManualPaymentRequest::query()->whereKey($manualPaymentRequest->id)->lockForUpdate()->firstOrFail();
                $points = (int) ($mpr->points_spent ?? 0);
                if ($points > 0 && empty($mpr->points_refunded_at) && $mpr->user) {
                    $wallet->credit($mpr->user, $points, 'refund_credit', $mpr, [
                        'reason' => 'admin_delete',
                    ]);
                    $mpr->points_refunded_at = now();
                    $mpr->save();
                }
            });
        } catch (\Throwable $e) {
            report($e);
        }

        $manualPaymentRequest->delete();

        return redirect()
            ->route('admin.wallet_points_orders.index')
            ->with('success', 'تم حذف الطلب ✅');
    }
}

