@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'تم استلام طلبك' }}
@endsection

@section('content')
@php
    $product = $mpr->product;
    $isCodes = ($product?->service_type ?? null) === 'codes';
    $methods = (array) config('bank.methods', []);
    $methodKey = $mpr->payment_method ?? null;
    $method = $methodKey && isset($methods[$methodKey]) ? $methods[$methodKey] : null;

    $status = (string) ($mpr->status ?? 'pending');
    $statusLabel = match ($status) {
        'approved' => 'مقبول',
        'rejected' => 'مرفوض',
        default => 'قيد المراجعة',
    };
    $statusClass = match ($status) {
        'approved' => 'text-green-700',
        'rejected' => 'text-red-700',
        default => 'text-yellow-700',
    };
    $subtitle = match ($status) {
        'approved' => 'تمت الموافقة على طلبك وسيتم التنفيذ/التسليم حسب نوع الخدمة.',
        'rejected' => 'تم رفض طلبك. إذا كان لديك استفسار تواصل مع الدعم.',
        default => 'طلبك قيد المراجعة وسيتم تنفيذ الشحن بعد التأكيد.',
    };

    $reservedCode = null;
    if ($isCodes && !empty($mpr->reserved_diamond_code_id)) {
        try {
            $reservedCode = \App\Models\DiamondCode::query()
                ->whereKey($mpr->reserved_diamond_code_id)
                ->first();
        } catch (\Throwable $e) {
            $reservedCode = null;
        }
    }
@endphp

@include('website.diamonds.partials.header', [
    'title' => 'تم استلام طلبك',
    'subtitle' => $subtitle,
    'active' => $isCodes ? 'codes' : 'charge',
])

<section class="max-w-3xl mx-auto px-4 pb-12" dir="rtl">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="text-center">
            <div class="text-4xl">✅</div>
            <h2 class="mt-2 text-2xl font-extrabold text-gray-900">تم استلام طلب الدفع اليدوي</h2>
            <p class="mt-1 text-sm text-gray-600">احتفظ برقم الطلب للمتابعة.</p>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">رقم الطلب</div>
                <div class="mt-1 font-extrabold text-gray-900 select-all">{{ $mpr->reference }}</div>
            </div>
            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">الحالة</div>
                <div class="mt-1 font-extrabold {{ $statusClass }}">{{ $statusLabel }}</div>
            </div>
            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">الباقة</div>
                <div class="mt-1 font-bold text-gray-900">{{ $product?->name }}</div>
            </div>
            <div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">Player ID</div>
                <div class="mt-1 font-bold text-gray-900 select-all">{{ $mpr->player_id }}</div>
            </div>
        </div>

        @if($reservedCode && ($product?->is_lucky_draw_codes ?? false))
            <div class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-4">
                <div class="text-sm font-extrabold text-gray-900">نتيجة القرعة</div>
                <div class="text-xs text-gray-700 mt-1">
                    تم حجز كود لك من القرعة. سيتم تسليمه لك بعد الموافقة.
                </div>
                @if(!empty($reservedCode->image_path))
                    <div class="mt-3">
                        <img src="{{ asset('storage/' . ltrim($reservedCode->image_path, '/')) }}"
                             alt="reserved"
                             class="w-full max-w-xs mx-auto rounded-xl border border-yellow-200"
                             onerror="this.onerror=null;this.src='{{ asset('img/قريبا.jpg') }}';">
                    </div>
                @endif
            </div>
        @endif

        <div class="mt-6">
            <div class="text-sm font-extrabold text-gray-900 mb-2">طريقة الدفع</div>
            @if($method)
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-sm text-gray-700">
                    <div class="font-extrabold text-gray-900">{{ $method['title'] ?? $methodKey }}</div>

                    <div class="mt-3 divide-y divide-gray-100">
                        @if($methodKey === 'sa_bank')
                            @if(!empty($method['bank_name']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">البنك</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-semibold select-all">{{ $method['bank_name'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['bank_name'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['account_name']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">اسم الحساب</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-semibold select-all">{{ $method['account_name'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['account_name'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['account_number']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">رقم الحساب</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-[13px] select-all">{{ $method['account_number'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['account_number'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['iban']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">IBAN</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-[13px] select-all">{{ $method['iban'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['iban'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                        @elseif($methodKey === 'jo_click')
                            @if(!empty($method['bank_name']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">البنك</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-semibold select-all">{{ $method['bank_name'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['bank_name'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['account_name']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">الاسم</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-semibold select-all">{{ $method['account_name'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['account_name'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['click_id']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">Click ID</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-[13px] select-all">{{ $method['click_id'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['click_id'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                        @elseif($methodKey === 'binance_trc20')
                            <div class="py-2 flex items-center justify-between gap-3">
                                <span class="text-xs text-gray-500">Network</span>
                                <span class="flex items-center gap-2">
                                    <span class="font-semibold select-all">{{ $method['network'] ?? 'TRC20' }}</span>
                                    <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['network'] ?? 'TRC20' }}" aria-label="Copy">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                    </button>
                                </span>
                            </div>
                            @if(!empty($method['address']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">Address</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-mono font-semibold text-[13px] select-all">{{ $method['address'] }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['address'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                            @if(!empty($method['link']))
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">Link</span>
                                    <span class="flex items-center gap-2">
                                        <a class="text-blue-600 underline" href="{{ $method['link'] }}" target="_blank">فتح الرابط</a>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $method['link'] }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="pt-3 text-xs text-gray-500">بعد التحويل سيتم تنفيذ الطلب بعد التأكيد.</div>
                </div>
            @else
                <div class="rounded-2xl border border-gray-200 bg-white p-4 text-sm text-gray-600">تم استلام الطلب. سيتم التنفيذ بعد التأكيد.</div>
            @endif
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="{{ $isCodes ? route('website.diamonds.codes') : route('website.diamonds.charge') }}"
               class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-800 hover:bg-gray-50 transition">
                الرجوع لقسم الدايموند
            </a>
            <a href="{{ route('home') }}"
               class="flex-1 inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                الرئيسية
            </a>
        </div>
    </div>
</section>
@endsection

