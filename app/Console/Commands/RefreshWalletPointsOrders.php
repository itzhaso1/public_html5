<?php

namespace App\Console\Commands;

use App\Models\ManualPaymentRequest;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use App\Support\Shop2TopUp\Shop2TopUpBundle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefreshWalletPointsOrders extends Command
{
    protected $signature = 'wallet:refresh-points-orders {--limit=50 : Max pending orders per run}';

    protected $description = 'Refresh Shop2TopUp status for pending wallet points gem orders and auto-refund on failures.';

    public function handle(Shop2TopUpService $shop2TopUp, WalletService $wallet): int
    {
        $lock = Cache::lock('wallet.refresh_points_orders', 60);
        if (! $lock->get()) {
            $this->info('Skipped: already running.');
            Log::info('wallet:refresh-points-orders skipped (lock busy)');
            return self::SUCCESS;
        }

        try {
            $startedAt = microtime(true);
            $limit = max(1, (int) $this->option('limit'));

            $q = ManualPaymentRequest::query()
                ->where('payment_method', 'wallet_points')
                ->where('status', 'pending')
                ->whereNotNull('shop2topup_trx_id')
                ->whereHas('product', function ($p) {
                    $p->where('service_type', 'gems');
                })
                ->with(['user', 'product'])
                ->orderBy('id');

            $total = (clone $q)->count();
            $this->info("Pending wallet points gem orders: {$total}");
            Log::info('wallet:refresh-points-orders started', ['pending_total' => $total, 'limit' => $limit]);

            $processed = 0;
            $approved = 0;
            $rejected = 0;
            $failedFetch = 0;

            $q->limit($limit)->chunkById(25, function ($chunk) use (
                $shop2TopUp,
                $wallet,
                &$processed,
                &$approved,
                &$rejected,
                &$failedFetch
            ) {
                foreach ($chunk as $mpr) {
                    $processed++;

                    $trxIds = Shop2TopUpBundle::parseTrxIds((string) ($mpr->shop2topup_trx_id ?? ''));
                    if (empty($trxIds)) {
                        continue;
                    }

                    $transactions = [];
                    $fetchOk = true;
                    foreach ($trxIds as $tId) {
                        $trx = $shop2TopUp->getTransaction((string) $tId);
                        if (($trx['success'] ?? false) !== true) {
                            $fetchOk = false;
                            break;
                        }
                        $trx['_trx_id'] = (string) $tId;
                        $transactions[] = $trx;
                    }
                    if (!$fetchOk || empty($transactions)) {
                        $failedFetch++;
                        continue;
                    }

                    $summary = Shop2TopUpBundle::summarizeTransactions($transactions);
                    $statusLabel = $summary['is_delivered']
                        ? 'DELIVERED'
                        : ($summary['is_failed'] ? ($summary['is_partial'] ? 'PARTIAL' : 'FAILED') : 'PROCESSING');

                    DB::transaction(function () use ($mpr, $transactions, $summary, $statusLabel, $wallet, &$approved, &$rejected) {
                        /** @var ManualPaymentRequest $locked */
                        $locked = ManualPaymentRequest::query()->whereKey($mpr->id)->lockForUpdate()->with(['user', 'product'])->first();
                        if (! $locked) return;

                        // Ensure still eligible
                        if (($locked->payment_method ?? null) !== 'wallet_points') return;
                        if (($locked->status ?? null) !== 'pending') return;
                        if (($locked->product?->service_type ?? null) !== 'gems') return;

                        $locked->shop2topup_status = $statusLabel;
                        $locked->shop2topup_order_id = $summary['total'] === 1 ? ($transactions[0]['order_id'] ?? $locked->shop2topup_order_id) : $locked->shop2topup_order_id;
                        $locked->shop2topup_secure_id = $summary['total'] === 1 ? ($transactions[0]['secure_id'] ?? $locked->shop2topup_secure_id) : $locked->shop2topup_secure_id;
                        $locked->shop2topup_delivery_at = !empty($transactions[0]['delivery_at']) ? $transactions[0]['delivery_at'] : $locked->shop2topup_delivery_at;
                        $locked->shop2topup_response = $summary['total'] > 1
                            ? ['bundle' => true, 'transactions' => $transactions, 'summary' => $summary]
                            : $transactions[0];

                        if ($summary['is_delivered']) {
                            $locked->status = 'approved';
                            $locked->approved_at = $locked->approved_at ?: now();
                            $approved++;
                        } elseif ($summary['is_failed']) {
                            $locked->status = 'rejected';
                            $locked->admin_note = trim(($locked->admin_note ? ($locked->admin_note . "\n") : '') . 'AUTO: تم رفض/استرجاع من المزود (bundle)');

                            $points = (int) ($locked->points_spent ?? 0);
                            $allFailed = $summary['total'] > 0 && $summary['failed'] === $summary['total'] && $summary['delivered'] === 0;
                            if ($allFailed && $points > 0 && empty($locked->points_refunded_at) && $locked->user) {
                                $wallet->credit($locked->user, $points, 'refund_credit', $locked, [
                                    'reason' => 'vendor_reject',
                                    'message' => 'bundle_failed',
                                ]);
                                $locked->points_refunded_at = now();
                            }
                            $rejected++;
                        } else {
                            // still processing; keep pending
                        }

                        $locked->save();
                    });
                }
            });

            $this->info("Processed: {$processed}, Approved: {$approved}, Rejected+Refunded: {$rejected}, FailedFetch: {$failedFetch}");
            Log::info('wallet:refresh-points-orders finished', [
                'processed' => $processed,
                'approved' => $approved,
                'rejected_refunded' => $rejected,
                'failed_fetch' => $failedFetch,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);
            return self::SUCCESS;
        } finally {
            optional($lock)->release();
        }
    }
}

