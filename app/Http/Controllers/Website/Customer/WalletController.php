<?php

namespace App\Http\Controllers\Website\Customer;

use App\Http\Controllers\Controller;
use App\Models\WalletTopupRequest;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
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
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'receipt' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $user = $request->user();
        $prices = $wallet->getPointPrices();
        $points = (int) $data['points'];

        $amountSar = round($points * (float) $prices['sar'], 2);
        $amountUsd = round($points * (float) $prices['usd'], 2);

        $path = $request->file('receipt')->store('uploads/wallet_topups', 'public');

        WalletTopupRequest::create([
            'user_id' => $user->id,
            'points' => $points,
            'point_price_sar' => (float) $prices['sar'],
            'point_price_usd' => (float) $prices['usd'],
            'amount_sar' => $amountSar,
            'amount_usd' => $amountUsd,
            'receipt_path' => $path,
            'status' => 'pending',
        ]);

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

