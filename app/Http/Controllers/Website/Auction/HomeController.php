<?php

namespace App\Http\Controllers\Website\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\Auction\AuctionLifecycleService;

class HomeController extends Controller
{
    public function __invoke(AuctionLifecycleService $lifecycleService)
    {
        $auctions = Auction::query()
            ->where('is_visible', true)
            ->whereIn('status', ['scheduled', 'active', 'paused'])
            ->with(['images', 'winner'])
            ->withCount('approvedSubscriptions')
            ->latest('id')
            ->take(6)
            ->get()
            ->map(function (Auction $auction) use ($lifecycleService) {
                return $lifecycleService->syncStatus($auction);
            });

        return view('website.pages.home', [
            'pageTitle' => 'منصة مزاد حسابات الألعاب',
            'auctions' => $auctions,
        ]);
    }
}
