<?php

namespace App\Services\Auction;

use App\Events\AuctionBidPlaced;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Notifications\AuctionBidPlacedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AuctionLifecycleService
{
    public function syncStatus(Auction $auction): Auction
    {
        $auction->loadCount('approvedSubscriptions');

        if (in_array($auction->status, ['ended', 'cancelled'], true)) {
            return $auction;
        }

        $now = now();

        if ($auction->ends_at <= $now) {
            return $this->endAuction($auction);
        }

        if ($auction->status === 'scheduled'
            && $auction->starts_at <= $now
            && $auction->approved_subscriptions_count >= $auction->min_participants
        ) {
            $auction->update([
                'status' => 'active',
                'started_at' => $auction->started_at ?? $now,
            ]);
        }

        return $auction->fresh(['winner'])->loadCount('approvedSubscriptions');
    }

    public function startAuction(Auction $auction): Auction
    {
        $auction->loadCount('approvedSubscriptions');
        if ($auction->approved_subscriptions_count < $auction->min_participants) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تشغيل المزاد قبل اكتمال الحد الأدنى من المشاركين.',
            ]);
        }

        if ($auction->starts_at > now()) {
            throw ValidationException::withMessages([
                'status' => 'لا يمكن تشغيل المزاد قبل وقت البداية المحدد.',
            ]);
        }

        $auction->update([
            'status' => 'active',
            'started_at' => $auction->started_at ?? now(),
        ]);

        return $auction->fresh()->loadCount('approvedSubscriptions');
    }

    public function pauseAuction(Auction $auction): Auction
    {
        $auction->update(['status' => 'paused']);
        return $auction->fresh()->loadCount('approvedSubscriptions');
    }

    public function endAuction(Auction $auction): Auction
    {
        return DB::transaction(function () use ($auction) {
            /** @var Auction $lockedAuction */
            $lockedAuction = Auction::query()->whereKey($auction->id)->lockForUpdate()->firstOrFail();

            if ($lockedAuction->status === 'ended') {
                return $lockedAuction->fresh(['winner'])->loadCount('approvedSubscriptions');
            }

            $highestBid = Bid::query()
                ->where('auction_id', $lockedAuction->id)
                ->latest('amount')
                ->latest('id')
                ->first();

            $winnerId = $highestBid?->user_id;
            $finalPrice = $highestBid?->amount ?? $lockedAuction->current_price ?? $lockedAuction->starting_price;

            $lockedAuction->update([
                'status' => 'ended',
                'winner_user_id' => $winnerId,
                'final_price' => $finalPrice,
                'current_price' => $finalPrice,
                'ended_at' => now(),
            ]);

            $lockedAuction->subscriptions()
                ->where('status', 'approved')
                ->where('user_id', '!=', $winnerId)
                ->update([
                    'status' => 'refunded',
                    'reviewed_at' => now(),
                ]);

            if ($winnerId) {
                $lockedAuction->subscriptions()
                    ->where('status', 'approved')
                    ->where('user_id', $winnerId)
                    ->update([
                        'status' => 'applied_to_winner',
                        'reviewed_at' => now(),
                    ]);
            }

            return $lockedAuction->fresh(['winner'])->loadCount('approvedSubscriptions');
        });
    }

    public function placeBid(Auction $auction, User $user, float $amount, ?string $ip = null, ?string $userAgent = null): Bid
    {
        return DB::transaction(function () use ($auction, $user, $amount, $ip, $userAgent) {
            /** @var Auction $lockedAuction */
            $lockedAuction = Auction::query()
                ->whereKey($auction->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAuction->loadCount('approvedSubscriptions');

            if ($lockedAuction->ends_at <= now()) {
                throw ValidationException::withMessages([
                    'amount' => 'المزاد انتهى بالفعل.',
                ]);
            }

            if (
                $lockedAuction->status === 'scheduled'
                && $lockedAuction->starts_at <= now()
                && $lockedAuction->approved_subscriptions_count >= $lockedAuction->min_participants
            ) {
                $lockedAuction->update([
                    'status' => 'active',
                    'started_at' => $lockedAuction->started_at ?? now(),
                ]);
                $lockedAuction = $lockedAuction->fresh()->loadCount('approvedSubscriptions');
            }

            if (! $lockedAuction->canReceiveBids()) {
                throw ValidationException::withMessages([
                    'amount' => 'المزاد غير متاح للمزايدة حالياً.',
                ]);
            }

            $hasApprovedSubscription = $lockedAuction->subscriptions()
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'applied_to_winner'])
                ->exists();

            if (! $hasApprovedSubscription) {
                throw ValidationException::withMessages([
                    'amount' => 'لا يمكنك المزايدة قبل تفعيل الاشتراك.',
                ]);
            }

            $latestBidAmount = (float) ($lockedAuction->bids()->max('amount') ?? $lockedAuction->current_price ?? $lockedAuction->starting_price);
            $minRequired = $latestBidAmount + (float) $lockedAuction->bid_increment;

            if ($amount < $minRequired) {
                throw ValidationException::withMessages([
                    'amount' => 'الحد الأدنى للمزايدة التالية هو '.number_format($minRequired, 2),
                ]);
            }

            $bid = Bid::query()->create([
                'auction_id' => $lockedAuction->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'ip_address' => $ip,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1024) : null,
            ]);

            $lockedAuction->update(['current_price' => $bid->amount]);

            $this->notifyParticipants($lockedAuction, $bid, $user);
            event(new AuctionBidPlaced($lockedAuction->fresh(), $bid->load('user')));

            return $bid->load('user', 'auction');
        });
    }

    private function notifyParticipants(Auction $auction, Bid $bid, User $bidder): void
    {
        $participantIds = $auction->subscriptions()
            ->whereIn('status', ['approved', 'applied_to_winner'])
            ->where('user_id', '!=', $bidder->id)
            ->pluck('user_id');

        if ($participantIds->isEmpty()) {
            return;
        }

        $users = User::query()->whereIn('id', $participantIds)->get();
        Notification::send($users, new AuctionBidPlacedNotification($auction, $bid->loadMissing('user')));
    }
}
