<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DiamondCode;
use App\Models\ManualPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\ChargeCompletedNotification;
use App\Support\Email\EmailNotifier;
use App\Support\Shop2TopUp\Shop2TopUpBundle;
use App\Support\WhatsApp\WhatsAppNumber;
use App\Support\WhatsApp\WasenderNotifier;

class ManualPaymentController extends Controller
{
    private function forgetDiamondsCaches(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
            Cache::forget("diamonds.charge.$locale");
        }
    }

    private function isDeliveredStatus(?string $status): bool
    {
        $s = strtoupper(trim((string) $status));
        if ($s === '') {
            return false;
        }
        return str_contains($s, 'DELIVER')
            || str_contains($s, 'SUCCESS')
            || str_contains($s, 'COMPLET')
            || str_contains($s, 'DONE');
    }

    private function normalizeShop2TopUpOfferGroupFromName(?string $name): string
    {
        $name = (string) $name;
        if (stripos($name, 'eu') !== false) {
            return 'EU';
        }
        if (stripos($name, 'global') !== false) {
            return 'GLOBAL';
        }
        return 'DEFAULT';
    }

    private function normalizeShop2TopUpOfferGroupFromRegion(?string $region): string
    {
        $region = strtoupper(trim((string) $region));
        if ($region === 'EU') {
            return 'EU';
        }
        // Shop2TopUp examples include RU; treat everything else as DEFAULT unless vendor specifies GLOBAL.
        if ($region === 'GLOBAL') {
            return 'GLOBAL';
        }
        return 'DEFAULT';
    }

    private function extractDiamondAmountKey(?string $name): ?int
    {
        $name = (string) $name;

        // Prefer patterns like "100 💎" or "💎 100"
        if (preg_match('/(\d+)\s*💎/u', $name, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/💎\s*(\d+)/u', $name, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public function index()
    {
        $requests = ManualPaymentRequest::query()
            ->where(function ($q) {
                $q->whereNull('payment_method')
                    ->orWhere('payment_method', '!=', 'wallet_points');
            })
            ->with(['product', 'user'])
            ->latest()
            ->paginate(30);

        return view('dashboard.admin.manual_payments.index', [
            'pageTitle' => 'طلبات الدفع اليدوي',
            'requests' => $requests,
        ]);
    }

    public function show(ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            return redirect()
                ->route('admin.wallet_points_orders.show', $manualPaymentRequest)
                ->withErrors(['error' => 'هذا الطلب مدفوع بالنقاط وتم نقله لقسم طلبات النقاط.']);
        }

        $manualPaymentRequest->load(['product', 'user']);

        return view('dashboard.admin.manual_payments.show', [
            'pageTitle' => 'تفاصيل طلب الدفع اليدوي',
            'mpr' => $manualPaymentRequest,
            'receiptUrl' => $manualPaymentRequest->receipt_path
                ? route('admin.manual_payments.receipt', $manualPaymentRequest)
                : null,
            'receiptIsPdf' => $manualPaymentRequest->receipt_path
                ? str_ends_with(strtolower($manualPaymentRequest->receipt_path), '.pdf')
                : false,
        ]);
    }

    public function receipt(ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            abort(404);
        }
        abort_if(! $manualPaymentRequest->receipt_path, 404);
        abort_if(! Storage::disk('public')->exists($manualPaymentRequest->receipt_path), 404);

        return Storage::disk('public')->response($manualPaymentRequest->receipt_path);
    }

    public function checkTransaction(Request $request, ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            return redirect()
                ->route('admin.wallet_points_orders.show', $manualPaymentRequest)
                ->withErrors(['error' => 'هذا الطلب مدفوع بالنقاط—استخدم تحديث الحالة من قسم طلبات النقاط.']);
        }
        $request->validate([
            'trx_id' => ['required', 'string', 'max:100'],
        ]);

        $manualPaymentRequest->loadMissing(['user', 'product']);
        $oldStatus = $manualPaymentRequest->shop2topup_status;

        $trxIdInput = (string) $request->input('trx_id');
        $trxIds = Shop2TopUpBundle::parseTrxIds($trxIdInput);
        if (empty($trxIds)) {
            return back()->withErrors(['error' => 'Shop2TopUp: TRXID_MISSING']);
        }

        $service = new Shop2TopUpService();
        $transactions = [];
        foreach ($trxIds as $tId) {
            $res = $service->getTransaction((string) $tId);
            if (! ($res['success'] ?? false)) {
                $msg = $res['msg'] ?? 'فشل التحقق من العملية';
                return back()->withErrors(['error' => 'Shop2TopUp: ' . $msg . ' (trx_id=' . $tId . ')']);
            }
            $res['_trx_id'] = (string) $tId;
            $transactions[] = $res;
        }

        $isBundle = count($trxIds) > 1;
        $summary = Shop2TopUpBundle::summarizeTransactions($transactions);

        $manualPaymentRequest->update([
            'shop2topup_trx_id' => Shop2TopUpBundle::encodeTrxIds($trxIds),
            'shop2topup_status' => $isBundle
                ? ($summary['is_delivered'] ? 'DELIVERED' : ($summary['is_failed'] ? 'FAILED' : 'PROCESSING'))
                : ($transactions[0]['status'] ?? null),
            'shop2topup_order_id' => $isBundle ? null : ($transactions[0]['order_id'] ?? null),
            'shop2topup_secure_id' => $isBundle ? null : ($transactions[0]['secure_id'] ?? null),
            'shop2topup_delivery_at' => !empty($transactions[0]['delivery_at']) ? $transactions[0]['delivery_at'] : null,
            'shop2topup_response' => $isBundle
                ? ['bundle' => true, 'transactions' => $transactions, 'summary' => $summary]
                : $transactions[0],
        ]);

        $manualPaymentRequest->refresh();
        $newStatus = $manualPaymentRequest->shop2topup_status;
        if (! $this->isDeliveredStatus($oldStatus) && $this->isDeliveredStatus($newStatus) && $manualPaymentRequest->user) {
            // Send customer notification on completion (best-effort)
            try {
                $manualPaymentRequest->user->notify(new ChargeCompletedNotification($manualPaymentRequest));
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return back()->with('success', 'تم تحديث حالة العملية من Shop2TopUp ✅');
    }

    public function approve(Request $request, ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            return redirect()
                ->route('admin.wallet_points_orders.show', $manualPaymentRequest)
                ->withErrors(['error' => 'طلبات النقاط لا يتم قبولها من هنا.']);
        }
        if ((string) ($manualPaymentRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $oldStatus = (string) ($manualPaymentRequest->status ?? '');
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $manualPaymentRequest->load(['product']);

        // If this request is for a "gems" product, perform Shop2TopUp topup before approving.
        if (($manualPaymentRequest->product?->service_type ?? null) === 'gems') {
            $product = $manualPaymentRequest->product;
            $playerId = trim((string) $manualPaymentRequest->player_id);
            $offerIds = Shop2TopUpBundle::offerIdsForProduct($product);

            if ($playerId === '' || mb_strlen($playerId) < 3) {
                return back()->withErrors(['error' => 'Player ID غير صحيح.']);
            }
            if (empty($offerIds)) {
                return back()->withErrors(['error' => 'هذا المنتج غير مربوط بعرض Shop2TopUp (itemId). قم بالمزامنة أو ضبط itemId أولاً.']);
            }

            $service = new Shop2TopUpService();

            // Reserve a trx id with a DB lock to prevent duplicate topups (double-click / concurrent requests).
            $reservedTrxIds = [];
            $alreadyHadTrx = false;

            DB::transaction(function () use ($manualPaymentRequest, &$reservedTrxIds, &$alreadyHadTrx, $request, $offerIds) {
                /** @var ManualPaymentRequest $mpr */
                $mpr = ManualPaymentRequest::query()
                    ->whereKey($manualPaymentRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!empty($mpr->shop2topup_trx_id)) {
                    $reservedTrxIds = Shop2TopUpBundle::parseTrxIds((string) $mpr->shop2topup_trx_id);
                    $alreadyHadTrx = true;
                    return;
                }

                $reservedTrxIds = [];
                foreach ($offerIds as $_) {
                    $reservedTrxIds[] = (string) Str::uuid();
                }
                $mpr->update([
                    'shop2topup_trx_id' => Shop2TopUpBundle::encodeTrxIds($reservedTrxIds),
                    'shop2topup_status' => 'SUBMITTING',
                    'admin_note' => $request->input('admin_note'),
                ]);
            });

            // If already has trx, do not resend topup.
            if ($alreadyHadTrx) {
                try {
                    $transactions = [];
                    foreach ($reservedTrxIds as $tId) {
                        $trx = $service->getTransaction((string) $tId);
                        if (is_array($trx)) {
                            $trx['_trx_id'] = (string) $tId;
                            $transactions[] = $trx;
                        }
                    }

                    if (!empty($transactions)) {
                        $summary = Shop2TopUpBundle::summarizeTransactions($transactions);
                        $statusLabel = $summary['is_delivered']
                            ? 'DELIVERED'
                            : ($summary['is_failed'] ? ($summary['is_partial'] ? 'PARTIAL' : 'FAILED') : 'PROCESSING');

                        $manualPaymentRequest->update([
                            'shop2topup_status' => $statusLabel,
                            'shop2topup_order_id' => $summary['total'] === 1 ? ($transactions[0]['order_id'] ?? $manualPaymentRequest->shop2topup_order_id) : $manualPaymentRequest->shop2topup_order_id,
                            'shop2topup_secure_id' => $summary['total'] === 1 ? ($transactions[0]['secure_id'] ?? $manualPaymentRequest->shop2topup_secure_id) : $manualPaymentRequest->shop2topup_secure_id,
                            'shop2topup_delivery_at' => !empty($transactions[0]['delivery_at']) ? $transactions[0]['delivery_at'] : $manualPaymentRequest->shop2topup_delivery_at,
                            'shop2topup_response' => $summary['total'] > 1
                                ? ['bundle' => true, 'transactions' => $transactions, 'summary' => $summary]
                                : $transactions[0],
                        ]);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            } else {
                // Ensure the player name is checked (API requires it)
                $check = $service->checkPlayer($playerId);
                if (!($check['success'] ?? false)) {
                    $msg = $check['msg'] ?? 'NOT_READY';
                    return back()->withErrors(['error' => 'Shop2TopUp: لا يمكن التحقق من اللاعب الآن: ' . $msg . '. حاول بعد دقيقة.']);
                }

                $offerIdsToUse = $offerIds;

                // Try to avoid REFUND_REGION by picking a region-matching offer if possible (single offer only).
                if (count($offerIds) === 1) {
                    $offerId = (int) $offerIds[0];
                    $playerGroup = $this->normalizeShop2TopUpOfferGroupFromRegion($check['region'] ?? null);
                    $productGroup = $this->normalizeShop2TopUpOfferGroupFromName($product?->name ?? null);
                    // Global offers are treated as universal (do not block by region).
                    if ($productGroup !== 'GLOBAL' && $playerGroup !== $productGroup) {
                        $amountKey = $this->extractDiamondAmountKey($product?->name ?? null);
                        if ($amountKey) {
                            $alt = \App\Models\Product::query()
                                ->where('service_type', 'gems')
                                ->whereNotNull('itemID')
                                ->where('itemID', '!=', '')
                                ->whereHas('translations', function ($q) use ($amountKey, $playerGroup) {
                                    $q->where('name', 'like', '%' . $amountKey . '%');
                                    if ($playerGroup === 'EU') {
                                        $q->where('name', 'like', '%EU%');
                                    } elseif ($playerGroup === 'GLOBAL') {
                                        $q->where('name', 'like', '%Global%');
                                    } else {
                                        $q->where('name', 'not like', '%EU%')->where('name', 'not like', '%Global%');
                                    }
                                })
                                ->orderBy('id', 'desc')
                                ->first();

                            if ($alt && (int) ($alt->itemID ?? 0) > 0) {
                                $offerId = (int) $alt->itemID;
                            }
                        }
                    }
                    $offerIdsToUse = [$offerId];
                }

                $topups = [];
                $providerTrxIds = [];
                $submittedOkCountSoFar = 0;

                foreach ($offerIdsToUse as $idx => $oid) {
                    $submitTrx = (string) ($reservedTrxIds[$idx] ?? Str::uuid());
                    $topup = $service->topup($playerId, (int) $oid, $submitTrx);
                    $topups[] = [
                        'offer_id' => (int) $oid,
                        'trx_id' => $submitTrx,
                        'response' => $topup,
                    ];

                    if (!($topup['success'] ?? false)) {
                        $msg = (string) ($topup['msg'] ?? 'TOPUP_FAILED');
                        if ($msg === 'REFUND_REGION') {
                            return back()->withErrors(['error' => 'Shop2TopUp: REFUND_REGION — الباقة لا تناسب منطقة اللاعب. جرّب باقة EU أو Global أو الافتراضية حسب المنطقة.']);
                        }

                        try {
                            $manualPaymentRequest->update([
                                'shop2topup_status' => $submittedOkCountSoFar > 0 ? 'PARTIAL_FAILED' : 'FAILED_SUBMIT',
                                'shop2topup_response' => [
                                    'bundle' => count($offerIdsToUse) > 1,
                                    'topups' => $topups,
                                ],
                                'shop2topup_trx_id' => Shop2TopUpBundle::encodeTrxIds($providerTrxIds),
                            ]);
                        } catch (\Throwable $e) {
                            // ignore
                        }

                        return back()->withErrors(['error' => 'Shop2TopUp: فشل الشحن: ' . $msg]);
                    }

                    $providerTrx = (string) (($topup['trxID'] ?? '') ?: $submitTrx);
                    $providerTrxIds[] = $providerTrx;
                    $submittedOkCountSoFar++;
                }

                // Save trx details on the request for tracking via /transaction
                try {
                    $manualPaymentRequest->update([
                        'shop2topup_trx_id' => Shop2TopUpBundle::encodeTrxIds($providerTrxIds),
                        'shop2topup_status' => 'SUBMITTED',
                        'shop2topup_response' => [
                            'bundle' => count($offerIdsToUse) > 1,
                            'topups' => $topups,
                        ],
                    ]);
                } catch (\Throwable $e) {
                    report($e);
                    return back()->withErrors([
                        'error' => 'تم إرسال الشحن للمزود ✅ لكن تعذر حفظ بيانات العملية محلياً. نفّذ: php artisan migrate --force ثم أعد المحاولة (لن نعيد الإرسال).'
                    ]);
                }

                // Try to fetch transaction status immediately (best-effort)
                try {
                    $transactions = [];
                    foreach ($providerTrxIds as $pt) {
                        $trx = $service->getTransaction((string) $pt);
                        if (is_array($trx)) {
                            $trx['_trx_id'] = (string) $pt;
                            $transactions[] = $trx;
                        }
                    }

                    if (!empty($transactions)) {
                        $summary = Shop2TopUpBundle::summarizeTransactions($transactions);
                        $statusLabel = $summary['is_delivered']
                            ? 'DELIVERED'
                            : ($summary['is_failed'] ? ($summary['is_partial'] ? 'PARTIAL' : 'FAILED') : 'PROCESSING');

                        $oldShopStatus = (string) ($manualPaymentRequest->shop2topup_status ?? '');
                        $manualPaymentRequest->update([
                            'shop2topup_status' => $statusLabel,
                            'shop2topup_order_id' => $summary['total'] === 1 ? ($transactions[0]['order_id'] ?? $manualPaymentRequest->shop2topup_order_id) : $manualPaymentRequest->shop2topup_order_id,
                            'shop2topup_secure_id' => $summary['total'] === 1 ? ($transactions[0]['secure_id'] ?? $manualPaymentRequest->shop2topup_secure_id) : $manualPaymentRequest->shop2topup_secure_id,
                            'shop2topup_delivery_at' => !empty($transactions[0]['delivery_at']) ? $transactions[0]['delivery_at'] : $manualPaymentRequest->shop2topup_delivery_at,
                            'shop2topup_response' => $summary['total'] > 1
                                ? ['bundle' => true, 'transactions' => $transactions, 'summary' => $summary, 'topups' => $topups]
                                : $transactions[0],
                        ]);

                        $manualPaymentRequest->loadMissing(['user', 'product']);
                        $manualPaymentRequest->refresh();
                        $newShopStatus = (string) ($manualPaymentRequest->shop2topup_status ?? '');
                        if (! $this->isDeliveredStatus($oldShopStatus) && $this->isDeliveredStatus($newShopStatus) && $manualPaymentRequest->user) {
                            try {
                                $manualPaymentRequest->user->notify(new ChargeCompletedNotification($manualPaymentRequest));
                            } catch (\Throwable $e) {
                                // ignore
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                // Clear gems page cache (optional)
                foreach (['ar', 'en'] as $locale) {
                    Cache::forget("diamonds.charge.$locale");
                }
            }
        }

        // If this request is for a "codes" product, allocate and deliver a code.
        if (($manualPaymentRequest->product?->service_type ?? null) === 'codes') {
            if (! $manualPaymentRequest->user_id) {
                return back()->withErrors(['error' => 'لا يمكن تسليم الكود بدون مستخدم (تأكد أن العميل مسجّل دخول).']);
            }

            $available = DiamondCode::query()
                ->where('product_id', $manualPaymentRequest->product_id)
                ->where('status', 'available')
                ->orderBy('id')
                ->first();

            if (! $available) {
                return back()->withErrors(['error' => 'لا يوجد أكواد متاحة لهذا المنتج. أضف أكواد من الداشبورد أولاً.']);
            }

            $available->update([
                'status' => 'delivered',
                'user_id' => $manualPaymentRequest->user_id,
                'manual_payment_request_id' => $manualPaymentRequest->id,
                'delivered_at' => now(),
            ]);

            // Codes page is cached; clear it so out-of-stock products disappear immediately.
            foreach (['ar', 'en'] as $locale) {
                Cache::forget("diamonds.codes.$locale");
            }
        }

        $manualPaymentRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_note' => $request->input('admin_note'),
        ]);

        if ($oldStatus !== 'approved') {
            $this->notifyCustomerDecision($manualPaymentRequest, true);
        }

        return redirect()
            ->route('admin.manual_payments.show', $manualPaymentRequest)
            ->with('success', 'تمت الموافقة على الطلب.');
    }

    public function reject(Request $request, ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            return redirect()
                ->route('admin.wallet_points_orders.show', $manualPaymentRequest)
                ->withErrors(['error' => 'طلبات النقاط لا يتم رفضها من هنا.']);
        }
        if ((string) ($manualPaymentRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $oldStatus = (string) ($manualPaymentRequest->status ?? '');
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $manualPaymentRequest->load(['product']);

        $manualPaymentRequest->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
        ]);

        // Refund points if this request was paid via wallet points.
        try {
            $this->refundWalletPointsIfNeeded($manualPaymentRequest);
        } catch (\Throwable $e) {
            // best-effort; do not block admin flow
            report($e);
        }

        if ($oldStatus !== 'rejected') {
            $this->notifyCustomerDecision($manualPaymentRequest, false);
        }

        if (($manualPaymentRequest->product?->service_type ?? null) === 'codes') {
            foreach (['ar', 'en'] as $locale) {
                Cache::forget("diamonds.codes.$locale");
            }
        }

        return redirect()
            ->route('admin.manual_payments.show', $manualPaymentRequest)
            ->with('success', 'تم رفض الطلب.');
    }

    private function refundWalletPointsIfNeeded(ManualPaymentRequest $manualPaymentRequest): void
    {
        $pm = (string) ($manualPaymentRequest->payment_method ?? '');
        $points = (int) ($manualPaymentRequest->points_spent ?? 0);

        if ($pm !== 'wallet_points' || $points <= 0) {
            return;
        }

        DB::transaction(function () use ($manualPaymentRequest, $points) {
            /** @var ManualPaymentRequest $mpr */
            $mpr = ManualPaymentRequest::query()
                ->whereKey($manualPaymentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!empty($mpr->points_refunded_at)) {
                return;
            }

            $user = $mpr->user()->first();
            if (! $user) {
                return;
            }

            $wallet = app(WalletService::class);
            $wallet->credit($user, $points, 'refund_credit', $mpr, [
                'manual_payment_request_id' => $mpr->id,
                'product_id' => $mpr->product_id,
            ]);

            $mpr->update([
                'points_refunded_at' => now(),
            ]);
        });
    }

    private function notifyCustomerDecision(ManualPaymentRequest $mpr, bool $approved): void
    {
        try { $mpr->loadMissing(['user', 'product', 'user.profile']); } catch (\Throwable $e) {}

        $app = (string) config('app.name', 'المتجر');
        $status = $approved ? 'تم قبول طلبك ✅' : 'تم رفض طلبك ❌';
        $note = trim((string) ($mpr->admin_note ?? ''));
        $noteLine = $note !== '' ? ("\nملاحظة: " . mb_substr($note, 0, 180)) : '';
        $link = null;
        try { $link = route('website.diamonds.manual_payment.thanks', ['reference' => $mpr->reference]); } catch (\Throwable $e) {}

        $text = trim(
            "{$app}\n" .
            "{$status}\n" .
            "رقم الطلب: {$mpr->reference}\n" .
            ($mpr->product?->name ? ("المنتج: " . $mpr->product->name . "\n") : '') .
            ($link ? "تفاصيل الطلب: {$link}\n" : '') .
            $noteLine
        );

        // WhatsApp (optional)
        if ((bool) config('services.wasender.enabled', false) && (bool) config('services.wasender.notify_customers', true)) {
            $to = WhatsAppNumber::normalize($mpr->contact_phone ?? '');
            if ($to === '') $to = WhatsAppNumber::normalize($mpr->user?->phone ?? '');
            if ($to === '') $to = WhatsAppNumber::normalize($mpr->user?->profile?->phone ?? '');
            if ($to !== '') {
                WasenderNotifier::sendAfterCommit($to, $text);
            }
        }

        // Email (optional additional channel)
        if ((bool) config('services.email_notify.enabled', false) && (bool) config('services.email_notify.notify_customers', true)) {
            $email = trim((string) ($mpr->contact_email ?? ''));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = trim((string) ($mpr->user?->email ?? ''));
            }
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $subject = $approved ? 'تم قبول طلبك ✅' : 'تم رفض طلبك ❌';
                EmailNotifier::sendAfterCommit($email, $subject, $text);
            }
        }
    }

    public function destroy(Request $request, ManualPaymentRequest $manualPaymentRequest)
    {
        if (($manualPaymentRequest->payment_method ?? null) === 'wallet_points') {
            return redirect()
                ->route('admin.wallet_points_orders.show', $manualPaymentRequest)
                ->withErrors(['error' => 'حذف طلبات النقاط يتم من قسم طلبات النقاط.']);
        }
        $data = $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        // If deleting a pending points-paid request, refund points first.
        try {
            if ((string) ($manualPaymentRequest->status ?? '') === 'pending') {
                $this->refundWalletPointsIfNeeded($manualPaymentRequest);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $receipt = $manualPaymentRequest->receipt_path;
        if ($receipt) {
            try {
                Storage::disk('public')->delete($receipt);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $manualPaymentRequest->delete();
        $this->forgetDiamondsCaches();

        return redirect()
            ->route('admin.manual_payments.index')
            ->with('success', 'تم حذف الطلب ✅');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'confirm' => ['required', 'in:DELETE'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['ids'] ?? [])));
        if (empty($ids)) {
            return back()->withErrors(['error' => 'لم يتم تحديد طلبات للحذف.']);
        }

        $requests = ManualPaymentRequest::query()
            ->whereIn('id', $ids)
            ->where(function ($q) {
                $q->whereNull('payment_method')
                    ->orWhere('payment_method', '!=', 'wallet_points');
            })
            ->get(['id', 'receipt_path', 'status', 'payment_method', 'points_spent', 'points_refunded_at', 'user_id', 'product_id']);

        foreach ($requests as $r) {
            // Refund points first (best-effort) before deleting.
            try {
                if ((string) ($r->status ?? '') === 'pending') {
                    $this->refundWalletPointsIfNeeded($r);
                }
            } catch (\Throwable $e) {
                report($e);
            }
            if (!empty($r->receipt_path)) {
                try {
                    Storage::disk('public')->delete($r->receipt_path);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        ManualPaymentRequest::query()
            ->whereIn('id', $ids)
            ->where(function ($q) {
                $q->whereNull('payment_method')
                    ->orWhere('payment_method', '!=', 'wallet_points');
            })
            ->delete();
        $this->forgetDiamondsCaches();

        return back()->with('success', 'تم حذف الطلبات المحددة ✅');
    }

    public function deleteAll(Request $request)
    {
        $data = $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        ManualPaymentRequest::query()
            ->where(function ($q) {
                $q->whereNull('payment_method')
                    ->orWhere('payment_method', '!=', 'wallet_points');
            })
            ->select(['id', 'receipt_path', 'status', 'payment_method', 'points_spent', 'points_refunded_at', 'user_id', 'product_id'])
            ->orderBy('id')
            ->chunkById(200, function ($chunk) {
                $ids = [];
                foreach ($chunk as $r) {
                    try {
                        if ((string) ($r->status ?? '') === 'pending') {
                            $this->refundWalletPointsIfNeeded($r);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                    $ids[] = $r->id;
                    if (!empty($r->receipt_path)) {
                        try {
                            Storage::disk('public')->delete($r->receipt_path);
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }
                }
                if (!empty($ids)) {
                    ManualPaymentRequest::query()->whereIn('id', $ids)->delete();
                }
            });

        $this->forgetDiamondsCaches();

        return back()->with('success', 'تم حذف جميع طلبات الدفع اليدوي ✅');
    }
}

