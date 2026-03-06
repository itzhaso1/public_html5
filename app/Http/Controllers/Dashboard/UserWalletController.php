<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTopupRequest;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserWalletController extends Controller
{
    public function show(User $user)
    {
        $transactions = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(50);

        $topups = WalletTopupRequest::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20, ['*'], 'topups_page');

        return view('dashboard.admin.users.wallet', [
            'pageTitle' => 'سجل نقاط المستخدم',
            'user' => $user,
            'transactions' => $transactions,
            'topups' => $topups,
        ]);
    }

    public function adjust(Request $request, User $user, WalletService $wallet)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:set,delta'],
            'value' => ['required', 'integer', 'min:-1000000000', 'max:1000000000'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $mode = (string) $data['mode'];
        $value = (int) $data['value'];
        $note = trim((string) ($data['note'] ?? ''));

        DB::transaction(function () use ($wallet, $user, $mode, $value, $note) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = (int) ($locked->wallet_points_balance ?? 0);

            $delta = $mode === 'set' ? ($value - $before) : $value;
            if ($delta === 0) {
                return;
            }

            $meta = [
                'admin_id' => (int) auth('admin')->id(),
                'mode' => $mode,
                'note' => $note !== '' ? $note : null,
            ];

            if ($delta > 0) {
                $wallet->credit($locked, (int) $delta, 'admin_adjust', (object) ['id' => $locked->id], $meta);
            } else {
                $wallet->debit($locked, (int) abs($delta), 'admin_adjust', (object) ['id' => $locked->id], $meta);
            }
        });

        return back()->with('success', 'تم تعديل نقاط المستخدم ✅');
    }
}

