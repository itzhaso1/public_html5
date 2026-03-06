<?php

namespace App\Http\Controllers\Website\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\Auction\AuctionLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function index(AuctionLifecycleService $lifecycleService): View
    {
        $auctions = Auction::query()
            ->where('is_visible', true)
            ->with('images')
            ->withCount('approvedSubscriptions')
            ->latest('id')
            ->paginate(12);

        $auctions->getCollection()->transform(
            fn (Auction $auction) => $lifecycleService->syncStatus($auction)
        );

        return view('website.auctions.index', [
            'pageTitle' => 'المزادات',
            'auctions' => $auctions,
        ]);
    }

    public function show(Auction $auction, AuctionLifecycleService $lifecycleService): View
    {
        abort_unless($auction->is_visible, 404);

        $auction = $lifecycleService->syncStatus($auction->load([
            'images',
            'winner',
            'bids.user',
        ])->loadCount('approvedSubscriptions'));

        $lastBids = $auction->bids()->with('user')->latest()->limit(10)->get();

        $userSubscriptionStatus = null;
        if (auth()->check()) {
            $userSubscriptionStatus = $auction->subscriptions()
                ->where('user_id', auth()->id())
                ->latest('id')
                ->value('status');
        }

        return view('website.auctions.show', [
            'pageTitle' => $auction->title,
            'auction' => $auction,
            'lastBids' => $lastBids,
            'userSubscriptionStatus' => $userSubscriptionStatus,
        ]);
    }

    public function myAuctions(): View
    {
        $user = auth()->user();

        $auctions = Auction::query()
            ->whereHas('subscriptions', fn ($query) => $query->where('user_id', $user->id))
            ->orWhereHas('bids', fn ($query) => $query->where('user_id', $user->id))
            ->with('images')
            ->withCount('approvedSubscriptions')
            ->latest('id')
            ->paginate(12);

        return view('website.auctions.my_auctions', [
            'pageTitle' => 'مزاداتي',
            'auctions' => $auctions,
        ]);
    }

    public function placeBid(Request $request, Auction $auction, AuctionLifecycleService $lifecycleService): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $lifecycleService->placeBid(
            $auction,
            $request->user(),
            (float) $validated['amount'],
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'تم تسجيل مزايدتك بنجاح.');
    }

    public function stream(Auction $auction, AuctionLifecycleService $lifecycleService): JsonResponse
    {
        abort_unless($auction->is_visible, 404);

        $auction = $lifecycleService->syncStatus($auction->loadCount('approvedSubscriptions'));
        $lastBids = $auction->bids()->with('user')->latest()->limit(10)->get()->map(function ($bid) {
            return [
                'amount' => (float) $bid->amount,
                'bidder' => $bid->user?->name ?? 'مستخدم',
                'created_at' => optional($bid->created_at)->diffForHumans(),
            ];
        })->values();

        return response()->json([
            'status' => $auction->status,
            'current_price' => (float) $auction->current_price,
            'participants_count' => $auction->participants_count,
            'can_receive_bids' => $auction->canReceiveBids(),
            'last_bids' => $lastBids,
            'ends_at' => optional($auction->ends_at)->toIso8601String(),
        ]);
    }
}
