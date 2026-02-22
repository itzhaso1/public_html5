<?php

namespace App\Services\Wallet;

use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getPointPrices(): array
    {
        return Cache::remember('wallet.point_prices', 60 * 10, function () {
            $s = Setting::query()->latest('id')->first();
            $sar = (float) ($s?->point_price_sar ?? 3.75);
            $usd = (float) ($s?->point_price_usd ?? 1.0);

            return [
                'sar' => $sar > 0 ? $sar : 3.75,
                'usd' => $usd > 0 ? $usd : 1.0,
            ];
        });
    }

    public function clearPointPricesCache(): void
    {
        Cache::forget('wallet.point_prices');
    }

    public function credit(User $user, int $points, string $type, ?object $reference = null, array $meta = []): WalletTransaction
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be > 0');
        }

        return $this->applyDelta($user, $points, $type, $reference, $meta);
    }

    public function debit(User $user, int $points, string $type, ?object $reference = null, array $meta = []): WalletTransaction
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be > 0');
        }

        return $this->applyDelta($user, -$points, $type, $reference, $meta);
    }

    private function applyDelta(User $user, int $delta, string $type, ?object $reference, array $meta): WalletTransaction
    {
        return DB::transaction(function () use ($user, $delta, $type, $reference, $meta) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = (int) ($lockedUser->wallet_points_balance ?? 0);

            $after = $before + $delta;
            if ($after < 0) {
                throw new \RuntimeException('Insufficient points balance.');
            }

            $lockedUser->wallet_points_balance = $after;
            $lockedUser->save();

            $tx = WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'points_delta' => $delta,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'meta' => $meta ?: null,
            ]);

            // keep in-memory user in sync for current request
            $user->wallet_points_balance = $after;

            return $tx;
        });
    }
}

