<?php

namespace App\Http\Controllers\Dashboard\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\Auction\AuctionLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function index(AuctionLifecycleService $lifecycleService): View
    {
        $auctions = Auction::query()
            ->with('winner')
            ->withCount(['approvedSubscriptions', 'bids'])
            ->latest('id')
            ->paginate(20);

        $auctions->getCollection()->transform(
            fn (Auction $auction) => $lifecycleService->syncStatus($auction)
        );

        return view('dashboard.admin.auctions.index', [
            'PageTitle' => 'إدارة المزادات',
            'auctions' => $auctions,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.auctions.create', [
            'PageTitle' => 'إضافة مزاد',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAuction($request);

        $auction = Auction::query()->create([
            ...$validated,
            'slug' => $this->generateSlug($validated['title']),
            'admin_id' => auth('admin')->id(),
            'current_price' => $validated['starting_price'],
        ]);

        $this->storeImages($request, $auction);

        return redirect()
            ->route('admin.auctions.index')
            ->with('success', 'تم إنشاء المزاد بنجاح.');
    }

    public function show(Auction $auction, AuctionLifecycleService $lifecycleService): View
    {
        $auction = $lifecycleService->syncStatus($auction->load([
            'images',
            'winner',
            'subscriptions.user',
            'subscriptions.paymentMethod',
            'bids.user',
        ])->loadCount(['approvedSubscriptions', 'bids']));

        return view('dashboard.admin.auctions.show', [
            'PageTitle' => 'تفاصيل المزاد',
            'auction' => $auction,
            'participants' => $auction->subscriptions()->with('user')->where('status', 'approved')->latest()->get(),
            'lastBids' => $auction->bids()->with('user')->latest()->limit(20)->get(),
        ]);
    }

    public function edit(Auction $auction): View
    {
        $auction->load('images');

        return view('dashboard.admin.auctions.edit', [
            'PageTitle' => 'تعديل المزاد',
            'auction' => $auction,
        ]);
    }

    public function update(Request $request, Auction $auction): RedirectResponse
    {
        $validated = $this->validateAuction($request);

        $auction->update([
            ...$validated,
            'slug' => $this->generateSlug($validated['title'], $auction->id),
        ]);

        $this->storeImages($request, $auction);

        if ($request->filled('delete_image_ids')) {
            $auction->images()
                ->whereIn('id', $request->input('delete_image_ids', []))
                ->delete();
        }

        return redirect()
            ->route('admin.auctions.edit', $auction)
            ->with('success', 'تم تحديث المزاد بنجاح.');
    }

    public function toggleStatus(Request $request, Auction $auction, AuctionLifecycleService $lifecycleService): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:start,pause,end,cancel'],
        ]);

        $action = $request->string('action')->toString();

        if ($action === 'start') {
            $lifecycleService->startAuction($auction);
        } elseif ($action === 'pause') {
            $lifecycleService->pauseAuction($auction);
        } elseif ($action === 'end') {
            $lifecycleService->endAuction($auction);
        } elseif ($action === 'cancel') {
            $auction->update(['status' => 'cancelled', 'ended_at' => now()]);
        }

        return back()->with('success', 'تم تحديث حالة المزاد.');
    }

    private function validateAuction(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'game_name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'starting_price' => ['required', 'numeric', 'min:1'],
            'subscription_fee' => ['required', 'numeric', 'min:0'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,scheduled,active,paused'],
            'is_visible' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
        ]);

        $validated['is_visible'] = $request->boolean('is_visible');

        return $validated;
    }

    private function storeImages(Request $request, Auction $auction): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $nextSort = (int) $auction->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $image) {
            $path = $image->store('auctions', 'public');
            $auction->images()->create([
                'path' => $path,
                'sort_order' => $nextSort++,
            ]);
        }
    }

    private function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'auction';
        $slug = $baseSlug;
        $suffix = 1;

        while (
            Auction::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
