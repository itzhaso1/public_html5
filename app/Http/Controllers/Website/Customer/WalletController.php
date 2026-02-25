<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Models\WalletTopupRequest;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $wallet)
    {
        $user = $request->user();

        $transactions = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $topups = WalletTopupRequest::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'topups_page');

        $prices = $wallet->getPointPrices();

        return view('website.customer.wallet', [
            'pageTitle' => 'محفظتي (نقاط)',
            'user' => $user,
            'transactions' => $transactions,
            'topups' => $topups,
            'pointPrices' => $prices,
        ]);
    }

    public function createTopup(Request $request, WalletService $wallet)
    {
        $user = $request->user();
        $prices = $wallet->getPointPrices();

        return view('website.customer.wallet_topup', [
            'pageTitle' => 'إيداع نقاط',
            'user' => $user,
            'pointPrices' => $prices,
        ]);
    }

    public function storeTopup(Request $request, WalletService $wallet)
    {
        $methods = (array) config('bank.methods', []);
        $enabledKeys = [];
        foreach ($methods as $k => $m) {
            if (!is_array($m)) continue;
            if (!($m['enabled'] ?? false)) continue;
            if ($k === 'binance_trc20') {
                $addr = trim((string) ($m['address'] ?? ''));
                $link = trim((string) ($m['link'] ?? ''));
                if ($addr === '' && $link === '') continue;
            }
            $enabledKeys[] = (string) $k;
        }

        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $enabledKeys ?: ['sa_bank'])],
            'receipt' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $user = $request->user();
        $prices = $wallet->getPointPrices();
        $points = (int) $data['points'];
        $method = (string) ($data['payment_method'] ?? '');

        // Prevent double submissions (multi-tap / slow network).
        $lockKey = 'wallet.topup.submit.' . $user->id . '.' . $points . '.' . sha1($method);
        $lock = Cache::lock($lockKey, 20);
        if (! $lock->get()) {
            return back()->withErrors(['error' => 'طلب الإيداع قيد الإرسال حالياً… انتظر قليلًا ثم أعد المحاولة.'])->withInput();
        }

        try {
            // Block duplicate pending requests created moments ago.
            $dup = WalletTopupRequest::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->where('points', $points)
                ->where('payment_method', $method)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->exists();
            if ($dup) {
                return back()->withErrors(['error' => 'لديك طلب إيداع مشابه قيد المراجعة بالفعل. انتظر قليلًا ثم راجع “محفظتي”.'])->withInput();
            }

            $amountSar = round($points * (float) $prices['sar'], 2);
            $amountUsd = round($points * (float) $prices['usd'], 2);

            $path = null;
            try {
                $path = $request->file('receipt')->store('uploads/wallet_topups', 'public');
            } catch (\Throwable $e) {
                report($e);
                return back()->withErrors(['error' => 'تعذر رفع الإيصال الآن. حاول مرة أخرى.'])->withInput();
            }

            WalletTopupRequest::create([
                'user_id' => $user->id,
                'points' => $points,
                'point_price_sar' => (float) $prices['sar'],
                'point_price_usd' => (float) $prices['usd'],
                'amount_sar' => $amountSar,
                'amount_usd' => $amountUsd,
                'payment_method' => $method,
                'receipt_path' => $path,
                'status' => 'pending',
            ]);
        } finally {
            optional($lock)->release();
        }

        return redirect()
            ->route('customer.wallet.index')
            ->with('success', 'تم إرسال طلب إيداع النقاط بنجاح ✅ سيتم مراجعته من الإدارة.');
    }

    public function receipt(Request $request, WalletTopupRequest $walletTopupRequest)
    {
        abort_if($walletTopupRequest->user_id !== $request->user()->id, 403);
        abort_if(! $walletTopupRequest->receipt_path, 404);
        abort_if(! Storage::disk('public')->exists($walletTopupRequest->receipt_path), 404);

        return Storage::disk('public')->response($walletTopupRequest->receipt_path);
    }
}

