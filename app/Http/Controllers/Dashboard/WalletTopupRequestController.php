<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTopupRequest;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WalletTopupRequestController extends Controller
{
    public function index()
    {
        $requests = WalletTopupRequest::query()
            ->with(['user'])
            ->latest()
            ->paginate(30);

        return view('dashboard.admin.wallet_topups.index', [
            'pageTitle' => 'طلبات إيداع النقاط',
            'requests' => $requests,
        ]);
    }

    public function receipt(WalletTopupRequest $walletTopupRequest)
    {
        abort_if(! $walletTopupRequest->receipt_path, 404);
        abort_if(! Storage::disk('public')->exists($walletTopupRequest->receipt_path), 404);

        return Storage::disk('public')->response($walletTopupRequest->receipt_path);
    }

    public function approve(Request $request, WalletTopupRequest $walletTopupRequest, WalletService $wallet)
    {
        if ((string) ($walletTopupRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($walletTopupRequest, $wallet, $data) {
            $topup = WalletTopupRequest::query()
                ->whereKey($walletTopupRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((string) ($topup->status ?? '') !== 'pending') {
                return;
            }

            $user = User::query()->whereKey($topup->user_id)->firstOrFail();

            $topup->update([
                'status' => 'approved',
                'admin_note' => $data['admin_note'] ?? null,
                'reviewed_by' => (int) auth('admin')->id(),
                'reviewed_at' => now(),
            ]);

            $wallet->credit($user, (int) $topup->points, 'deposit_credit', $topup, [
                'topup_id' => $topup->id,
                'amount_sar' => (float) $topup->amount_sar,
                'amount_usd' => (float) $topup->amount_usd,
            ]);
        });

        return back()->with('success', 'تمت الموافقة وإضافة النقاط للمستخدم ✅');
    }

    public function reject(Request $request, WalletTopupRequest $walletTopupRequest)
    {
        if ((string) ($walletTopupRequest->status ?? '') !== 'pending') {
            return back()->withErrors(['error' => 'لا يمكن تنفيذ هذا الإجراء لأن الطلب ليس قيد المراجعة.']);
        }

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $walletTopupRequest->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => (int) auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'تم رفض طلب الإيداع.');
    }
}

