<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Models\CashExchangeRequest;
use App\Models\ManualPaymentRequest;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {
        $manualRequests = ManualPaymentRequest::query()
            ->where('user_id', auth()->id())
            ->with(['product', 'diamondCode'])
            ->latest()
            ->paginate(20);

        $cashRequests = CashExchangeRequest::query()
            ->where('user_id', auth()->id())
            ->with(['offer'])
            ->latest()
            ->get();

        return view('website.customer.purchases', [
            'pageTitle' => 'مشترياتي',
            'requests' => $manualRequests,
            'cashRequests' => $cashRequests,
        ]);
    }

    public function refreshShop2Topup(Request $request, ManualPaymentRequest $manualPaymentRequest, WalletService $wallet)
    {
        abort_if($manualPaymentRequest->user_id !== (int) auth()->id(), 403);

        $manualPaymentRequest->loadMissing(['product', 'user']);
        if (($manualPaymentRequest->product?->service_type ?? null) !== 'gems') {
            return back()->withErrors(['error' => 'هذا الطلب لا يدعم تحديث حالة المزود.']);
        }
        if (($manualPaymentRequest->payment_method ?? null) !== 'wallet_points') {
            return back()->withErrors(['error' => 'هذا الطلب ليس مدفوعاً بالنقاط.']);
        }
        if (empty($manualPaymentRequest->shop2topup_trx_id)) {
            return back()->withErrors(['error' => 'رقم العملية غير موجود.']);
        }

        $service = new Shop2TopUpService();
        $trx = $service->getTransaction((string) $manualPaymentRequest->shop2topup_trx_id);
        if (($trx['success'] ?? false) !== true) {
            $msg = (string) ($trx['msg'] ?? 'تعذر جلب الحالة');
            return back()->withErrors(['error' => 'تعذر تحديث الحالة: ' . $msg]);
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

        return back()->with('success', 'تم تحديث حالة الطلب ✅');
    }
}

