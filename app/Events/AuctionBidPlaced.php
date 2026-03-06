<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Auction $auction,
        public Bid $bid
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('auctions.'.$this->auction->id)];
    }

    public function broadcastAs(): string
    {
        return 'auction.bid.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'current_price' => (float) $this->auction->current_price,
            'bid' => [
                'id' => $this->bid->id,
                'amount' => (float) $this->bid->amount,
                'bidder' => $this->bid->user?->name,
                'created_at' => $this->bid->created_at?->toIso8601String(),
            ],
        ];
    }
}
