@extends('website.layouts.common.website')

@push('css')
<style>
    .home-featured-accounts-swiper {
        padding: 0 6px 42px;
    }

    .home-featured-accounts-swiper .swiper-slide {
        display: flex;
        justify-content: center;
        height: auto;
    }

    .home-featured-accounts-swiper .swiper-slide > .product {
        width: 100%;
        max-width: 360px;
        margin-inline: auto;
    }

    .home-featured-accounts-swiper .swiper-pagination {
        position: relative !important;
        bottom: 0 !important;
        margin-top: 14px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        text-align: center;
        pointer-events: auto;
    }

    .home-featured-accounts-swiper .home-featured-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        opacity: 1;
        margin: 0 5px !important;
        background: #d1d5db;
        border-radius: 999px;
        transform: scale(1);
        transition: width 0.3s ease, transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .home-featured-accounts-swiper .home-featured-dot.is-active {
        width: 30px;
        background: linear-gradient(90deg, #facc15 0%, #f59e0b 100%);
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    }
</style>
@endpush

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
@php
    $fallbackImage = asset('img/قريبا.jpg');
@endphp

@include('website.diamonds.partials.header', [
    'title' => 'حسابات فري فاير',
    'subtitle' => 'تصفح الحسابات المميزة وباقي الحسابات في مكان واحد.',
    'active' => 'accounts',
])

<div class="mt-2 px-4">
    @include('website.partials.currency_picker')
</div>

<!-- الحسابات المميزة -->
@if(($featuredProducts ?? collect())->isNotEmpty())
<div class="px-4 py-6">
    <h2 class="text-center font-bold text-xl mb-4 border-b border-gray-300 pb-2 text-yellow-500">
        الحسابات المميزة
    </h2>

    <div class="swiper home-featured-accounts-swiper">
        <div class="swiper-wrapper">
            @foreach($featuredProducts as $product)
                @php
                    $imageUrl = $product->getMediaUrl('product', $product, null, 'media', 'product');
                    $productImage = $imageUrl ?: $fallbackImage;
                    $isSold = (bool) ($product->featured ?? false);
                    $discountPercent = null;
                    $dealEndsAt = $product->deal_ends_at ?? null;

                    if (!empty($product->price_before_discount) && $product->price_before_discount > 0) {
                        $discountPercent = round((($product->price_before_discount - $product->price) / $product->price_before_discount) * 100);
                    }

                    $hasCountdown = false;
                    try {
                        $hasCountdown = (! $isSold) && ($discountPercent > 0) && $dealEndsAt && $dealEndsAt->isFuture();
                    } catch (\Throwable $e) {
                        $hasCountdown = false;
                    }
                @endphp

                <div class="swiper-slide">
                    <div class="relative bg-white p-3 rounded-lg shadow text-center product flex flex-col h-full"
                         data-status="{{ $isSold ? 'مباع' : '' }}">
                        @if($isSold)
                            <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow">
                                مباع
                            </div>
                        @endif

                        @if(!$isSold && !empty($discountPercent) && $discountPercent > 0)
                            <div class="absolute top-2 right-2 bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded shadow">
                                خصم {{ $discountPercent }}%
                            </div>
                        @endif

                        @if($hasCountdown)
                            <div class="deal-countdown-wrap absolute top-9 left-1/2 -translate-x-1/2 bg-black/85 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow">
                                ⏳ ينتهي خلال:
                                <span class="deal-countdown font-mono" data-ends="{{ $dealEndsAt->toIso8601String() }}">--:--:--</span>
                            </div>
                        @endif

                        <img src="{{ $productImage }}"
                             class="product-img mx-auto rounded-md object-cover w-full h-auto"
                             alt="{{ $product->name ?? 'Product' }}"
                             width="600" height="300"
                             loading="lazy"
                             decoding="async">

                        <h2 class="font-bold mt-2">{{ $product->name }}</h2>

                        @if(!empty($product->price_before_discount))
                            <p class="font-semibold mt-1 product-price text-red-600"
                               data-base-price="{{ $product->price }}"
                               data-base-old="{{ $product->price_before_discount }}">
                                <span class="current-price">ر.س {{ $product->price }}</span>
                                <span class="old-price text-gray-500 text-sm line-through">
                                    {{ $product->price_before_discount }}
                                </span>
                            </p>
                        @else
                            <p class="font-semibold mt-1 product-price" data-base-price="{{ $product->price }}">
                                <span class="current-price">ر.س {{ $product->price }}</span>
                            </p>
                        @endif

                        @if($isSold)
                            <button class="mt-auto w-full bg-gray-400 text-white py-1 rounded text-sm cursor-not-allowed">
                                مباع
                            </button>
                        @else
                            <a href="{{ route('website.product.show', $product->id) }}"
                               class="mt-auto block w-full bg-black text-white py-1 rounded text-sm text-center">
                                عرض تفاصيل
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>
@endif

<!-- الأقسام والمنتجات -->
@foreach($sections as $section)
    <div class="px-4 py-6">
        <h2 class="text-center font-bold text-xl mb-4">{{ $section->name }}</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(($section->products ?? collect()) as $product)
                @php
                    $imageUrl = $product->getMediaUrl('product', $product, null, 'media', 'product');
                    $thumb = ($product->service_type ?? null) === 'codes' ? ($product->codeThumbnail?->image_path ?? null) : null;
                    $thumbUrl = $thumb ? Storage::disk('public')->url($thumb) : null;
                    $productImage = $imageUrl ?: ($thumbUrl ?: $fallbackImage);
                    $isSold = (bool) ($product->featured ?? false);
                    $discountPercent = null;
                    $dealEndsAt = $product->deal_ends_at ?? null;

                    if (!empty($product->price_before_discount) && $product->price_before_discount > 0) {
                        $discountPercent = round((($product->price_before_discount - $product->price) / $product->price_before_discount) * 100);
                    }

                    $hasCountdown = false;
                    try {
                        $hasCountdown = (! $isSold) && ($discountPercent > 0) && $dealEndsAt && $dealEndsAt->isFuture();
                    } catch (\Throwable $e) {
                        $hasCountdown = false;
                    }
                @endphp

                <div class="relative bg-white p-3 rounded-lg shadow text-center overflow-hidden flex flex-col h-full">
                    @if($isSold)
                        <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow">
                            مباع
                        </div>
                    @endif

                    @if(!$isSold && !empty($discountPercent) && $discountPercent > 0)
                        <div class="absolute top-2 right-2 bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded shadow">
                            خصم {{ $discountPercent }}%
                        </div>
                    @endif

                    @if($hasCountdown)
                        <div class="deal-countdown-wrap absolute top-9 left-1/2 -translate-x-1/2 bg-black/85 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow">
                            ⏳ ينتهي خلال:
                            <span class="deal-countdown font-mono" data-ends="{{ $dealEndsAt->toIso8601String() }}">--:--:--</span>
                        </div>
                    @endif

                    <img src="{{ $productImage }}"
                         class="product-img mx-auto rounded-md object-cover w-full h-auto"
                         alt="{{ $product->name ?? 'Product' }}"
                         width="600" height="300"
                         loading="lazy"
                         decoding="async">

                    <h3 class="font-bold mt-2">{{ $product->name }}</h3>

                    <p class="text-gray-500 text-sm">
                        {{ $product->short_description ?? 'لا يوجد وصف لهذا المنتج' }}
                    </p>

                    @if(!empty($product->price_before_discount))
                        <p class="font-semibold mt-1 product-price text-red-600"
                           data-base-price="{{ $product->price }}"
                           data-base-old="{{ $product->price_before_discount }}">
                            <span class="current-price">ر.س {{ $product->price }}</span>
                            <span class="old-price text-gray-500 text-sm line-through">
                                {{ $product->price_before_discount }}
                            </span>
                        </p>
                    @else
                        <p class="font-semibold mt-1 product-price" data-base-price="{{ $product->price }}">
                            <span class="current-price">ر.س {{ $product->price }}</span>
                        </p>
                    @endif

                    @if($isSold)
                        <button class="mt-auto w-full bg-gray-400 text-white py-1 rounded text-sm cursor-not-allowed">
                            مباع
                        </button>
                    @else
                        <a href="{{ route('website.product.show', $product->id) }}"
                           class="mt-auto block w-full bg-black text-white py-1 rounded text-sm text-center hover:bg-gray-800 transition">
                            عرض تفاصيل
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<!-- حسابات متجر الممالك -->
<div class="px-4 py-6">
    <h2 class="text-center font-bold text-xl mb-4 border-b border-gray-300 pb-2 text-yellow-500">
        حسابات متجر الممالك
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" data-sort-by-base-price>
       @foreach($products->sortByDesc(fn($p) => (float) ($p->price ?? 0)) as $product)
            @php
                $imageUrl = $product->getMediaUrl('product', $product, null, 'media', 'product');
                $productImage = $imageUrl ?: $fallbackImage;
                $isSold = (bool) ($product->featured ?? false);
                $discountPercent = null;
                $dealEndsAt = $product->deal_ends_at ?? null;

                if (!empty($product->price_before_discount) && $product->price_before_discount > 0) {
                    $discountPercent = round((($product->price_before_discount - $product->price) / $product->price_before_discount) * 100);
                }

                $hasCountdown = false;
                try {
                    $hasCountdown = (! $isSold) && ($discountPercent > 0) && $dealEndsAt && $dealEndsAt->isFuture();
                } catch (\Throwable $e) {
                    $hasCountdown = false;
                }
            @endphp

            <div class="relative bg-white p-3 rounded-lg shadow text-center product flex flex-col h-full"
                 data-status="{{ $isSold ? 'مباع' : '' }}">

                @if($isSold)
                    <div class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded shadow">
                        مباع
                    </div>
                @endif

                @if(!$isSold && !empty($discountPercent) && $discountPercent > 0)
                    <div class="absolute top-2 right-2 bg-yellow-400 text-black text-xs font-bold px-2 py-1 rounded shadow">
                        خصم {{ $discountPercent }}%
                    </div>
                @endif

                @if($hasCountdown)
                    <div class="deal-countdown-wrap absolute top-9 left-1/2 -translate-x-1/2 bg-black/85 text-white text-[11px] font-bold px-2.5 py-1 rounded-full shadow">
                        ⏳ ينتهي خلال:
                        <span class="deal-countdown font-mono" data-ends="{{ $dealEndsAt->toIso8601String() }}">--:--:--</span>
                    </div>
                @endif

                <img src="{{ $productImage }}"
                     class="product-img mx-auto rounded-md object-cover"
                     alt="{{ $product->name }}"
                     width="600" height="300"
                     loading="lazy"
                     decoding="async">

                <h2 class="font-bold mt-2">{{ $product->name }}</h2>

                <p class="text-gray-500 text-sm">
                    {{ $product->short_description ?? 'لا يوجد وصف متاح' }}
                </p>

                @if(!empty($product->price_before_discount))
                    <p class="font-semibold mt-1 product-price text-red-600"
                       data-base-price="{{ $product->price }}"
                       data-base-old="{{ $product->price_before_discount }}">
                        <span class="current-price">ر.س {{ $product->price }}</span>
                        <span class="old-price text-gray-500 text-sm line-through">
                            {{ $product->price_before_discount }}
                        </span>
                    </p>
                @else
                    <p class="font-semibold mt-1 product-price" data-base-price="{{ $product->price }}">
                        <span class="current-price">ر.س {{ $product->price }}</span>
                    </p>
                @endif

                @if($isSold)
                    <button class="mt-auto w-full bg-gray-400 text-white py-1 rounded text-sm cursor-not-allowed">
                        مباع
                    </button>
                @else
                    <a href="{{ route('website.product.show', $product->id) }}"
                       class="mt-auto block w-full bg-black text-white py-1 rounded text-sm text-center">
                        عرض تفاصيل
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const productsContainer = document.querySelector('[data-sort-by-base-price]');
    if (!productsContainer) return;

    const products = Array.from(productsContainer.children || []);
    if (products.length < 2) return;

    const getPrice = (el) => {
        const priceEl = el && el.querySelector ? el.querySelector('.product-price[data-base-price]') : null;
        if (!priceEl) return 0;
        const raw = String(priceEl.getAttribute('data-base-price') || '').trim();
        const n = parseFloat(raw);
        return isNaN(n) ? 0 : n;
    };

    products.sort((a, b) => getPrice(b) - getPrice(a));
    products.forEach(p => productsContainer.appendChild(p));
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const els = Array.from(document.querySelectorAll('.deal-countdown[data-ends]'));
    if (!els.length) return;

    const pad2 = (n) => String(Math.max(0, n)).padStart(2, '0');
    const format = (sec) => {
        sec = Math.max(0, Math.floor(sec));
        const d = Math.floor(sec / 86400);
        sec = sec % 86400;
        const h = Math.floor(sec / 3600);
        sec = sec % 3600;
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        if (d > 0) return `${d}ي ${pad2(h)}:${pad2(m)}:${pad2(s)}`;
        return `${pad2(h)}:${pad2(m)}:${pad2(s)}`;
    };

    const tick = () => {
        const now = Date.now();
        for (const el of els) {
            const ends = Date.parse(el.getAttribute('data-ends') || '');
            if (!ends || Number.isNaN(ends)) continue;
            const diffSec = Math.floor((ends - now) / 1000);
            if (diffSec <= 0) {
                const wrap = el.closest('.deal-countdown-wrap');
                if (wrap) wrap.remove();
                continue;
            }
            el.textContent = format(diffSec);
        }
    };

    tick();
    setInterval(tick, 1000);
});
</script>
@endpush
