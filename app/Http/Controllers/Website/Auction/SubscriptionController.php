<?php

namespace App\Http\Controllers\Website\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionSubscription;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = AuctionSubscription::query()
            ->where('user_id', auth()->id())
            ->with(['auction', 'paymentMethod'])
            ->latest('id')
            ->paginate(12);

        return view('website.auctions.subscriptions.index', [
            'pageTitle' => 'اشتراكاتي',
            'subscriptions' => $subscriptions,
        ]);
    }

    public function create(Auction $auction): View
    {
        abort_unless($auction->is_visible, 404);

        $paymentMethods = PaymentMethod::query()
            ->where('is_active', true)
            ->latest('id')
            ->get();

        return view('website.auctions.subscriptions.create', [
            'pageTitle' => 'الاشتراك في المزاد',
            'auction' => $auction,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request, Auction $auction): RedirectResponse
    {
        abort_unless($auction->is_visible, 404);

        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'receipt' => ['required', 'image', 'max:4096'],
        ]);

        $existingActive = AuctionSubscription::query()
            ->where('auction_id', $auction->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved', 'applied_to_winner'])
            ->exists();

        if ($existingActive) {
            return back()->withErrors([
                'receipt' => 'لديك طلب اشتراك قائم بالفعل في هذا المزاد.',
            ]);
        }

        $path = $request->file('receipt')->store('auction-subscriptions', 'public');

        AuctionSubscription::query()->create([
            'auction_id' => $auction->id,
            'user_id' => $request->user()->id,
            'payment_method_id' => $request->integer('payment_method_id'),
            'amount' => $auction->subscription_fee,
            'receipt_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('auctions.show', $auction)
            ->with('success', 'تم إرسال طلب الاشتراك بنجاح، بانتظار مراجعة الإدارة.');
    }
}
