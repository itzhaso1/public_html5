<?php

namespace App\Http\Controllers\Dashboard\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSubscription;
use App\Services\Auction\AuctionLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $subscriptions = AuctionSubscription::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->with(['auction', 'user', 'paymentMethod'])
            ->latest('id')
            ->paginate(20);

        return view('dashboard.admin.auctions.subscriptions.index', [
            'PageTitle' => 'طلبات اشتراك المزادات',
            'subscriptions' => $subscriptions,
            'status' => $status,
        ]);
    }

    public function show(AuctionSubscription $subscription): View
    {
        $subscription->load(['auction', 'user', 'paymentMethod', 'reviewedBy']);

        return view('dashboard.admin.auctions.subscriptions.show', [
            'PageTitle' => 'تفاصيل طلب الاشتراك',
            'subscription' => $subscription,
        ]);
    }

    public function approve(AuctionSubscription $subscription, Request $request, AuctionLifecycleService $lifecycleService): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        if ($subscription->status !== 'pending') {
            return back()->withErrors(['admin_notes' => 'لا يمكن اعتماد طلب غير معلق.']);
        }

        $alreadyApproved = AuctionSubscription::query()
            ->where('auction_id', $subscription->auction_id)
            ->where('user_id', $subscription->user_id)
            ->whereIn('status', ['approved', 'applied_to_winner'])
            ->exists();

        if ($alreadyApproved) {
            return back()->withErrors(['admin_notes' => 'المستخدم مشترك بالفعل في هذا المزاد.']);
        }

        $subscription->update([
            'status' => 'approved',
            'reviewed_by_admin_id' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $lifecycleService->syncStatus($subscription->auction);

        return back()->with('success', 'تمت الموافقة على الاشتراك وتفعيله للمستخدم.');
    }

    public function reject(AuctionSubscription $subscription, Request $request): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['required', 'string'],
        ]);

        if ($subscription->status !== 'pending') {
            return back()->withErrors(['admin_notes' => 'لا يمكن رفض طلب غير معلق.']);
        }

        $subscription->update([
            'status' => 'rejected',
            'reviewed_by_admin_id' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('success', 'تم رفض طلب الاشتراك.');
    }
}
