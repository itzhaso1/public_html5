@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-[#0f1738] via-[#102a61] to-[#0b0f22] text-white" dir="rtl">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top_right,_#60a5fa,_transparent_45%)]"></div>
    <div class="relative max-w-7xl mx-auto px-4 py-16 sm:py-20">
        <div class="max-w-3xl">
            <span class="inline-flex rounded-full border border-blue-300/40 bg-blue-400/10 px-3 py-1 text-xs font-bold">
                منصة احترافية لمزاد حسابات الألعاب
            </span>
            <h1 class="mt-4 text-3xl sm:text-5xl font-black leading-tight">
                مزاد حسابات Free Fire و PUBG Mobile
                <span class="text-yellow-300">بأمان كامل</span>
            </h1>
            <p class="mt-4 text-white/85 text-sm sm:text-base leading-7">
                شارك في المزادات بعد الاشتراك اليدوي، وتابع الأسعار لحظياً، واستفد من نظام ضمان عادل:
                غير الفائز يسترجع الاشتراك، والفائز يُحتسب اشتراكه ضمن السعر النهائي.
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('auctions.index') }}" class="rounded-xl bg-yellow-400 px-5 py-3 text-sm font-black text-black hover:bg-yellow-300">
                    تصفح المزادات
                </a>
                <a href="{{ route('subscriptions.index') }}" class="rounded-xl border border-white/30 px-5 py-3 text-sm font-bold hover:bg-white/10">
                    إدارة الاشتراكات
                </a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-10" dir="rtl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-gray-900">أحدث المزادات</h2>
        <a href="{{ route('auctions.index') }}" class="text-sm font-bold text-blue-700 hover:underline">عرض الكل</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($auctions as $auction)
            @php
                $cover = $auction->images->first();
                $statusMap = [
                    'active' => ['نشط', 'bg-green-100 text-green-700'],
                    'scheduled' => ['مجدول', 'bg-blue-100 text-blue-700'],
                    'paused' => ['موقوف', 'bg-yellow-100 text-yellow-800'],
                    'ended' => ['منتهي', 'bg-gray-200 text-gray-700'],
                ];
                [$label, $badgeClass] = $statusMap[$auction->status] ?? ['غير معروف', 'bg-gray-100 text-gray-700'];
            @endphp

            <a href="{{ route('auctions.show', $auction) }}" class="group rounded-2xl bg-white border border-gray-200 shadow-sm hover:shadow-xl transition overflow-hidden">
                <div class="relative">
                    <img
                        src="{{ $cover ? asset('storage/'.$cover->path) : 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?q=80&w=1200&auto=format&fit=crop' }}"
                        alt="{{ $auction->title }}"
                        class="h-48 w-full object-cover group-hover:scale-[1.03] transition duration-500"
                    >
                    <span class="absolute top-3 right-3 rounded-full px-2.5 py-1 text-xs font-black {{ $badgeClass }}">
                        {{ $label }}
                    </span>
                </div>
                <div class="p-4">
                    <h3 class="font-black text-gray-900 line-clamp-1">{{ $auction->title }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $auction->game_name }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-gray-500">السعر الحالي</div>
                            <div class="font-black text-blue-700">{{ number_format((float) $auction->current_price, 2) }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-2">
                            <div class="text-gray-500">المشاركون</div>
                            <div class="font-black text-gray-900">{{ $auction->participants_count }}/{{ $auction->min_participants }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        ينتهي: {{ optional($auction->ends_at)->format('Y-m-d H:i') }}
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-600">
                لا توجد مزادات متاحة حالياً.
            </div>
        @endforelse
    </div>
</section>
@endsection