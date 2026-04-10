<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Models\CashExchangeRequest;
use App\Models\ManualPaymentRequest;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use App\Support\Shop2TopUp\Shop2TopUpBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        if (!in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = 'all';
        }

        $base = ManualPaymentRequest::query()
            ->where('user_id', auth()->id())
            ->with(['product', 'diamondCode'])
            ->latest();

        if ($status !== 'all') {
            $base->where('status', $status);
        }

        $manualRequests = $base->paginate(20)->withQueryString();

        // counts for tabs
        $counts = ManualPaymentRequest::query()
            ->where('user_id', auth()->id())
            ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending_count")
            ->selectRaw("sum(case when status = 'approved' then 1 else 0 end) as approved_count")
            ->selectRaw("sum(case when status = 'rejected' then 1 else 0 end) as rejected_count")
            ->first();

        $cashRequests = CashExchangeRequest::query()
            ->where('user_id', auth()->id())
            ->with(['offer'])
            ->latest()
            ->get();

        return view('website.customer.purchases', [
            'pageTitle' => 'مشترياتي',
            'requests' => $manualRequests,
            'cashRequests' => $cashRequests,
            'status' => $status,
            'counts' => $counts,
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
        $trxIds = Shop2TopUpBundle::parseTrxIds((string) ($manualPaymentRequest->shop2topup_trx_id ?? ''));
        if (empty($trxIds)) {
            return back()->withErrors(['error' => 'رقم العملية غير موجود.']);
        }

        $service = new Shop2TopUpService();
        $transactions = [];
        foreach ($trxIds as $tId) {
            $trx = $service->getTransaction((string) $tId);
            if (($trx['success'] ?? false) !== true) {
                $msg = (string) ($trx['msg'] ?? 'تعذر جلب الحالة');
                return back()->withErrors(['error' => 'تعذر تحديث الحالة: ' . $msg . ' (trx_id=' . $tId . ')']);
            }
            $trx['_trx_id'] = (string) $tId;
            $transactions[] = $trx;
        }

        $summary = Shop2TopUpBundle::summarizeTransactions($transactions);
        $statusLabel = $summary['is_delivered']
            ? 'DELIVERED'
            : ($summary['is_failed'] ? ($summary['is_partial'] ? 'PARTIAL' : 'FAILED') : 'PROCESSING');

        DB::transaction(function () use ($manualPaymentRequest, $transactions, $summary, $statusLabel, $wallet) {
            /** @var ManualPaymentRequest $mpr */
            $mpr = ManualPaymentRequest::query()->whereKey($manualPaymentRequest->id)->lockForUpdate()->firstOrFail();
            $mpr->shop2topup_status = $statusLabel;
            $mpr->shop2topup_order_id = $summary['total'] === 1 ? ($transactions[0]['order_id'] ?? $mpr->shop2topup_order_id) : $mpr->shop2topup_order_id;
            $mpr->shop2topup_secure_id = $summary['total'] === 1 ? ($transactions[0]['secure_id'] ?? $mpr->shop2topup_secure_id) : $mpr->shop2topup_secure_id;
            $mpr->shop2topup_delivery_at = !empty($transactions[0]['delivery_at']) ? $transactions[0]['delivery_at'] : $mpr->shop2topup_delivery_at;
            $mpr->shop2topup_response = $summary['total'] > 1
                ? ['bundle' => true, 'transactions' => $transactions, 'summary' => $summary]
                : $transactions[0];

            if ($summary['is_delivered']) {
                $mpr->status = 'approved';
                $mpr->approved_at = $mpr->approved_at ?: now();
            } elseif ($summary['is_failed']) {
                $mpr->status = 'rejected';
                $mpr->admin_note = trim(($mpr->admin_note ? ($mpr->admin_note . "\n") : '') . 'AUTO: تم رفض/استرجاع من المزود (bundle)');

                $points = (int) ($mpr->points_spent ?? 0);
                $allFailed = $summary['total'] > 0 && $summary['failed'] === $summary['total'] && $summary['delivered'] === 0;
                if ($allFailed && $points > 0 && empty($mpr->points_refunded_at) && $mpr->user) {
                    $wallet->credit($mpr->user, $points, 'refund_credit', $mpr, [
                        'reason' => 'vendor_reject',
                        'message' => 'bundle_failed',
                    ]);
                    $mpr->points_refunded_at = now();
                }
            }

            $mpr->save();
        });

        return back()->with('success', 'تم تحديث حالة الطلب ✅');
    }
}

