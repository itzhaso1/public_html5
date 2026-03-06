@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10" dir="rtl">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-black text-gray-900">المزادات</h1>
        @auth
            <a href="{{ route('auctions.my') }}" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-bold text-white hover:bg-black">
                مزاداتي
            </a>
        @endauth
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($auctions as $auction)
            @php
                $cover = $auction->images->first();
            @endphp
            <a href="{{ route('auctions.show', $auction) }}" class="rounded-2xl bg-white border border-gray-200 shadow-sm hover:shadow-xl transition overflow-hidden">
                <img
                    src="{{ $cover ? asset('storage/'.$cover->path) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1200&auto=format&fit=crop' }}"
                    alt="{{ $auction->title }}"
                    class="h-48 w-full object-cover"
                >
                <div class="p-4">
                    <h2 class="font-black text-gray-900">{{ $auction->title }}</h2>
                    <p class="text-xs text-gray-500 mt-1">{{ $auction->game_name }}</p>
                    <div class="mt-3 text-sm font-bold text-blue-700">السعر الحالي: {{ number_format((float) $auction->current_price, 2) }}</div>
                    <div class="mt-1 text-xs text-gray-500">المشاركون: {{ $auction->participants_count }}/{{ $auction->min_participants }}</div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-600">
                لا توجد مزادات متاحة حالياً.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $auctions->links() }}</div>
</section>
@endsection
