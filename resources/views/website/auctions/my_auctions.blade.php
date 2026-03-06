@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10" dir="rtl">
    <h1 class="text-2xl font-black text-gray-900 mb-6">مزاداتي</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($auctions as $auction)
            @php $cover = $auction->images->first(); @endphp
            <a href="{{ route('auctions.show', $auction) }}" class="rounded-2xl bg-white border border-gray-200 shadow-sm hover:shadow-xl transition overflow-hidden">
                <img
                    src="{{ $cover ? asset('storage/'.$cover->path) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop' }}"
                    alt="{{ $auction->title }}"
                    class="h-44 w-full object-cover"
                >
                <div class="p-4">
                    <h2 class="font-black text-gray-900 line-clamp-1">{{ $auction->title }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ $auction->game_name }}</p>
                    <div class="mt-2 text-sm font-black text-blue-700">{{ number_format((float) $auction->current_price, 2) }}</div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-600">
                لا توجد مزادات مرتبطة بحسابك حتى الآن.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $auctions->links() }}</div>
</section>
@endsection
