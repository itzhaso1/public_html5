@extends('website.layouts.common.website')

@section('pageTitle')
{{ $product?->name ?? 'الدايموند' }}
@endsection

@section('content')
@php
    $fallbackImage = asset('img/قريبا.jpg');
    $imageUrl = $product?->getMediaUrl('product', $product, null, 'media', 'product');
    $productImage = $imageUrl ?: $fallbackImage;
    $isCodes = ($product?->service_type ?? null) === 'codes';
    $title = $product?->name ?? ($isCodes ? 'كود' : 'شحن جواهر');
    $availableCodesCount = $isCodes
        ? \App\Models\DiamondCode::query()
            ->where('product_id', $product->id)
            ->where('status', 'available')
            ->count()
        : null;
    $pendingRequestsCount = $isCodes
        ? \App\Models\ManualPaymentRequest::query()
            ->where('product_id', $product->id)
            ->where('status', 'pending')
            ->count()
        : null;
    $effectiveAvailable = $isCodes ? max(0, ((int) $availableCodesCount) - ((int) $pendingRequestsCount)) : null;
    $isOutOfStock = $isCodes && ((int) $effectiveAvailable) === 0;
    $basePrice = (float) ($product?->price ?? 0);
@endphp

@include('website.diamonds.partials.header', [
    'title' => $isCodes ? 'أكواد ملابس' : 'شحن الجواهر',
    'subtitle' => 'تفاصيل الباقة قبل الشراء.',
    'active' => $isCodes ? 'codes' : 'charge',
])

<section class="max-w-4xl mx-auto px-4 pb-12" dir="rtl">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="aspect-[4/3] bg-gray-50">
                <img src="{{ $productImage }}"
                     alt="{{ $title }}"
                     class="w-full h-full object-cover"
                     loading="lazy"
                     decoding="async">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-snug">
                {{ $title }}
            </h2>

            @if($isOutOfStock)
                <div class="mt-3 inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-bold text-red-700">
                    نفذت الكمية
                </div>
            @endif

            @if(!empty($product?->short_description))
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                    {{ $product->short_description }}
                </p>
            @endif

            <div class="mt-5 rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">السعر</div>
                <div class="mt-1 text-3xl font-extrabold text-green-600 product-price"
                     data-base-price="{{ (float) $basePrice }}">
                    <span class="current-price">ر.س {{ number_format((float) $basePrice, 2) }}</span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2 rounded-xl border border-gray-100 p-3">
                    <span class="text-green-600 font-bold">✓</span> تنفيذ سريع
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-gray-100 p-3">
                    <span class="text-blue-600 font-bold">✓</span> دعم عربي
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-gray-100 p-3">
                    <span class="text-yellow-600 font-bold">✓</span> آمن
                </div>
            </div>

            @if(!empty($product?->description))
                <div class="mt-5">
                    <div class="text-sm font-bold text-gray-900 mb-2">الوصف</div>
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ trim(strip_tags($product->description)) }}
                    </div>
                </div>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-800 hover:bg-gray-50 transition">
                    العودة للمتجر
                </a>
                @if(! $isOutOfStock)
                    <a href="{{ route('website.diamonds.manual_payment.create', $product) }}"
                       class="inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                        الدفع اليدوي (تحويل بنكي)
                    </a>
                @else
                    <a href="{{ route('website.diamonds.codes') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-gray-100 px-5 py-3 text-sm font-extrabold text-gray-700 cursor-not-allowed">
                        نفذت الكمية
                    </a>
                @endif
                <a href="https://chat.whatsapp.com/LiEKm0hQPlB9yeToyetcbh"
                   target="_blank"
                   class="inline-flex items-center justify-center rounded-xl bg-[#25D366] px-5 py-3 text-sm font-extrabold text-white hover:brightness-95 transition">
                    تواصل واتساب لإتمام الطلب
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

