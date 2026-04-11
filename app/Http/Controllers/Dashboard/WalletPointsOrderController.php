<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentRequest;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use App\Support\Shop2TopUp\Shop2TopUpBundle;
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
                return back()->withErrors(['error' => 'Shop2TopUp: ' . $msg . ' (trx_id=' . $tId . ')']);
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

