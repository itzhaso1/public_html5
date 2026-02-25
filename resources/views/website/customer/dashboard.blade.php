@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'حسابي' }}
@endsection

@section('content')
@php
    $data = $data ?? [];
@endphp

<section class="max-w-5xl mx-auto px-4 py-8" dir="rtl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">حسابي</h1>
            <p class="text-sm text-gray-600 mt-1">مرحباً {{ $user?->name ?? '' }} — تابع مشترياتك وبياناتك.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('customer.purchases') }}"
               class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2 text-sm font-bold text-white hover:bg-gray-800 transition">
                مشترياتي
            </a>
            <a href="{{ route('customer.profile') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                الملف الشخصي
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">إجمالي الطلبات</div>
            <div class="mt-1 text-2xl font-extrabold text-gray-900">{{ (int)($data['total'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">قيد الانتظار</div>
            <div class="mt-1 text-2xl font-extrabold text-yellow-700">{{ (int)($data['pending'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <div class="text-xs text-gray-500">مكتمل</div>
            <div class="mt-1 text-2xl font-extrabold text-green-700">{{ (int)($data['completed'] ?? 0) }}</div>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="text-sm font-extrabold text-gray-900">روابط سريعة</div>
        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $chargeEnabled = (bool) ($chargeEnabled ?? ($settings?->charge_enabled ?? true));
                $codesEnabled = (bool) ($codesEnabled ?? ($settings?->codes_enabled ?? true));
            @endphp

            <a href="{{ $chargeEnabled ? route('website.diamonds.charge') : 'javascript:void(0)' }}"
               class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition {{ $chargeEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
                <div class="font-extrabold text-gray-900">شحن الجواهر</div>
                <div class="text-xs text-gray-500 mt-1">اختر الباقة وادفع.</div>
                @unless($chargeEnabled)
                    <div class="mt-2 text-xs font-extrabold text-yellow-700">غير متاح حالياً</div>
                @endunless
            </a>
            <a href="{{ $codesEnabled ? route('website.diamonds.codes') : 'javascript:void(0)' }}"
               class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition {{ $codesEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
                <div class="font-extrabold text-gray-900">أكواد ملابس</div>
                <div class="text-xs text-gray-500 mt-1">شراء أكواد جاهزة للتسليم.</div>
                @unless($codesEnabled)
                    <div class="mt-2 text-xs font-extrabold text-blue-700">غير متاح حالياً</div>
                @endunless
            </a>
            @php
                $cashEnabled = (bool) ($cashExchangeEnabled ?? ($settings?->cash_exchange_enabled ?? true));
                $moneyEnabled = (bool) ($moneyExchangeEnabled ?? false);
            @endphp

            <a href="{{ $cashEnabled ? route('website.cash_exchange.index') : 'javascript:void(0)' }}"
               class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition {{ $cashEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
                <div class="font-extrabold text-gray-900">استبدل رصيدك كاش</div>
                <div class="text-xs text-gray-500 mt-1">ارسل كود البطاقة واستلم كاش.</div>
                @unless($cashEnabled)
                    <div class="mt-2 text-xs font-extrabold text-emerald-700">غير متاح حالياً</div>
                @endunless
            </a>
            <a href="{{ $moneyEnabled ? route('website.money_exchange.index') : 'javascript:void(0)' }}"
               class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition {{ $moneyEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
                <div class="font-extrabold text-gray-900">تحويل الأموال / تبادل العملات</div>
                <div class="text-xs text-gray-500 mt-1">تحويل SAR ↔ USDT حسب الصرف.</div>
                @unless($moneyEnabled)
                    <div class="mt-2 text-xs font-extrabold text-purple-700">غير متاح حالياً</div>
                @endunless
            </a>

            <a href="{{ route('customer.wallet.index') }}"
               class="rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition">
                <div class="font-extrabold text-gray-900">محفظتي (نقاط)</div>
                <div class="text-xs text-gray-500 mt-1">عرض الرصيد + إيداع نقاط.</div>
                <div class="mt-2 text-xs font-extrabold text-gray-800">
                    الرصيد: {{ number_format((int)($user?->wallet_points_balance ?? 0)) }} نقطة
                </div>
            </a>
        </div>
    </div>
</section>
@endsection

