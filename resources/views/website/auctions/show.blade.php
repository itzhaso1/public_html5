@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-7xl mx-auto px-4 py-8" dir="rtl">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
                @php $mainImage = $auction->images->first(); @endphp
                <img
                    id="auction-main-image"
                    src="{{ $mainImage ? asset('storage/'.$mainImage->path) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop' }}"
                    alt="{{ $auction->title }}"
                    class="h-[360px] w-full object-cover"
                >
                @if($auction->images->count() > 1)
                    <div class="grid grid-cols-4 gap-2 p-3 bg-gray-50">
                        @foreach($auction->images as $image)
                            <button
                                type="button"
                                class="rounded-lg overflow-hidden border border-gray-200 hover:border-blue-500"
                                onclick="document.getElementById('auction-main-image').src='{{ asset('storage/'.$image->path) }}'"
                            >
                                <img src="{{ asset('storage/'.$image->path) }}" alt="image" class="h-20 w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <h1 class="text-2xl font-black text-gray-900">{{ $auction->title }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $auction->game_name }}</p>
                <div class="mt-4 text-sm leading-7 text-gray-700 whitespace-pre-line">{{ $auction->description }}</div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <div class="text-xs text-gray-500">السعر الحالي</div>
                <div id="current-price" class="text-3xl font-black text-blue-700">{{ number_format((float) $auction->current_price, 2) }}</div>

                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg bg-gray-50 p-2">
                        <div class="text-gray-500">السعر الابتدائي</div>
                        <div class="font-black text-gray-900">{{ number_format((float) $auction->starting_price, 2) }}</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-2">
                        <div class="text-gray-500">المشاركون</div>
                        <div id="participants-count" class="font-black text-gray-900">{{ $auction->participants_count }}/{{ $auction->min_participants }}</div>
                    </div>
                </div>

                <div class="mt-3 rounded-lg bg-gray-900 text-white p-3 text-sm">
                    <div class="text-gray-300">الوقت المتبقي</div>
                    <div id="countdown" data-ends="{{ optional($auction->ends_at)->toIso8601String() }}" class="font-black tracking-wider mt-1">--:--:--</div>
                </div>

                <div class="mt-3 rounded-lg bg-blue-50 border border-blue-100 p-3 text-xs text-blue-800">
                    حالة المزاد: <span id="auction-status" class="font-black">{{ $auction->status }}</span>
                </div>
            </div>

            @auth
                @if(in_array($userSubscriptionStatus, ['approved', 'applied_to_winner']))
                    <div class="rounded-2xl bg-white border border-gray-200 p-5">
                        <form method="POST" action="{{ route('auctions.bid', $auction) }}" class="space-y-3">
                            @csrf
                            <label class="text-sm font-bold text-gray-800">قيمة المزايدة</label>
                            <input
                                type="number"
                                name="amount"
                                step="0.01"
                                min="{{ (float) $auction->current_price + (float) $auction->bid_increment }}"
                                placeholder="مثال: {{ number_format((float) $auction->current_price + (float) $auction->bid_increment, 2) }}"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2.5"
                                required
                            >
                            <button type="submit" class="w-full rounded-xl bg-blue-700 px-4 py-3 font-black text-white hover:bg-blue-800">
                                رفع السعر الآن
                            </button>
                        </form>
                    </div>
                @elseif($userSubscriptionStatus === 'pending')
                    <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        طلب اشتراكك قيد المراجعة. سيتم تفعيل المزايدة بعد موافقة الإدارة.
                    </div>
                @else
                    <a href="{{ route('auctions.subscribe.create', $auction) }}" class="block rounded-2xl bg-yellow-400 p-4 text-center font-black text-black hover:bg-yellow-300">
                        اشترك في المزاد أولاً
                    </a>
                @endif
            @else
                <a href="{{ route('auth.login') }}" class="block rounded-2xl bg-gray-900 p-4 text-center font-black text-white hover:bg-black">
                    سجّل الدخول للمشاركة بالمزايدة
                </a>
            @endauth
        </div>
    </div>

    <div class="mt-6 rounded-2xl bg-white border border-gray-200 p-5">
        <h2 class="text-lg font-black text-gray-900 mb-3">آخر المزايدات</h2>
        <div id="last-bids-list" class="space-y-2">
            @forelse($lastBids as $bid)
                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 text-sm">
                    <div class="font-bold text-gray-700">{{ $bid->user?->name ?? 'مستخدم' }}</div>
                    <div class="font-black text-blue-700">{{ number_format((float) $bid->amount, 2) }}</div>
                    <div class="text-xs text-gray-500">{{ optional($bid->created_at)->diffForHumans() }}</div>
                </div>
            @empty
                <div class="text-sm text-gray-500">لا توجد مزايدات حتى الآن.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const countdownEl = document.getElementById('countdown');
    const currentPriceEl = document.getElementById('current-price');
    const participantsEl = document.getElementById('participants-count');
    const statusEl = document.getElementById('auction-status');
    const bidsList = document.getElementById('last-bids-list');

    const formatRemaining = (seconds) => {
        if (seconds <= 0) return 'انتهى';
        const d = Math.floor(seconds / 86400);
        const h = Math.floor((seconds % 86400) / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        if (d > 0) return `${d}ي ${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    };

    const tick = () => {
        if (!countdownEl) return;
        const endsAt = Date.parse(countdownEl.dataset.ends || '');
        if (!endsAt) return;
        const diff = Math.floor((endsAt - Date.now()) / 1000);
        countdownEl.textContent = formatRemaining(diff);
    };

    const refreshData = async () => {
        try {
            const response = await fetch("{{ route('auctions.stream', $auction) }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) return;
            const data = await response.json();
            if (currentPriceEl) currentPriceEl.textContent = Number(data.current_price).toFixed(2);
            if (participantsEl) participantsEl.textContent = `${data.participants_count}/{{ $auction->min_participants }}`;
            if (statusEl) statusEl.textContent = data.status;
            if (data.ends_at && countdownEl) countdownEl.dataset.ends = data.ends_at;
            if (Array.isArray(data.last_bids) && bidsList) {
                bidsList.innerHTML = data.last_bids.map((bid) => (
                    `<div class="flex items-center justify-between rounded-lg bg-gray-50 p-3 text-sm">
                        <div class="font-bold text-gray-700">${bid.bidder}</div>
                        <div class="font-black text-blue-700">${Number(bid.amount).toFixed(2)}</div>
                        <div class="text-xs text-gray-500">${bid.created_at}</div>
                    </div>`
                )).join('') || '<div class="text-sm text-gray-500">لا توجد مزايدات حتى الآن.</div>';
            }
        } catch (e) {}
    };

    tick();
    refreshData();
    setInterval(tick, 1000);
    setInterval(refreshData, 5000);
});
</script>
@endpush
