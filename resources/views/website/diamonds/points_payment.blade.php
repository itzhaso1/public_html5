@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'الدفع بالنقاط' }}
@endsection

@section('content')
@php
    $product = $product ?? null;
    $isCodes = ($product?->service_type ?? null) === 'codes';
    $isGems = ($product?->service_type ?? null) === 'gems';
    $pointsPrice = (int) ($pointsPrice ?? ($product?->points_price ?? 0));
    $balance = (int) (($user?->wallet_points_balance ?? auth()->user()?->wallet_points_balance) ?? 0);
@endphp

@include('website.diamonds.partials.header', [
    'title' => 'الدفع بالنقاط',
    'subtitle' => 'سيتم إنشاء طلب جديد وسيخصم رصيدك من النقاط.',
    'active' => $isCodes ? 'codes' : 'charge',
])

<section class="max-w-3xl mx-auto px-4 pb-10" dir="rtl">
    @if(session('success'))
        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">المنتج</div>
                <div class="mt-1 text-lg font-extrabold text-gray-900">{{ $product?->name ?? '-' }}</div>
                <div class="mt-2 text-xs text-gray-600">
                    السعر بالنقاط: <span class="font-extrabold text-black">{{ number_format($pointsPrice) }}</span> نقطة
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 min-w-[240px]">
                <div class="text-xs text-gray-500">رصيدك</div>
                <div class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($balance) }}</div>
                <div class="text-xs text-gray-500">نقطة</div>
            </div>
        </div>

        <form method="POST" action="{{ route('website.diamonds.points_payment.store', $product) }}" class="mt-5 space-y-4">
            @csrf

            @if($isGems)
                <div>
                    <label class="block text-sm font-extrabold mb-2">Player ID</label>
                    <input type="text" name="player_id" value="{{ old('player_id') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/50"
                           placeholder="اكتب Player ID" required>
                    <div class="text-xs text-gray-500 mt-1">سيتم تنفيذ الشحن بعد المراجعة.</div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-extrabold mb-2">واتساب للتواصل (اختياري)</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="+9665XXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-2">إيميل (اختياري)</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="name@example.com">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                سيتم خصم <span class="font-extrabold">{{ number_format($pointsPrice) }}</span> نقطة من رصيدك عند الإرسال.
                @if($isCodes)
                    <div class="mt-1 text-xs text-gray-600">الكود سيتم تسليمه بعد موافقة الإدارة (نفس نظام الدفع اليدوي).</div>
                @endif
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                تأكيد الدفع بالنقاط
            </button>
        </form>
    </div>
</section>
@endsection

