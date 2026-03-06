<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuctionBidPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Auction $auction,
        private readonly Bid $bid
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'bid_amount' => (float) $this->bid->amount,
            'bidder_name' => $this->bid->user?->name,
            'message' => 'تم تقديم مزايدة جديدة في المزاد: '.$this->auction->title,
        ];
    }

}
