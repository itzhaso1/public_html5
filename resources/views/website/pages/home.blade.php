@extends('website.layouts.common.website')

@push('css')
<style>
    .reviewsSwiper {
        padding-bottom: 40px;
    }

    .reviewsSwiper .swiper-slide {
        transition: all 0.4s ease;
    }

    .reviewsSwiper .swiper-slide-active {
        transform: scale(1.03);
        background: #fffef7;
        border-color: #facc15;
    }

    .reviewsSwiper .swiper-pagination {
        position: relative !important;
        bottom: 0 !important;
        margin-top: 1rem;
        margin-bottom: -10px;
        text-align: center;
    }

    .reviewsSwiper .swiper-pagination-bullet {
        background: #d1d5db;
        opacity: 1;
        width: 8px;
        height: 8px;
        margin: 0 4px !important;
        transition: all 0.3s ease;
    }

    .reviewsSwiper .swiper-pagination-bullet-active {
        background: #facc15;
        width: 12px;
        height: 12px;
    }

    /* تثبيت أبعاد السلايدر لتقليل CLS */
    .home-hero-swiper {
        aspect-ratio: 2 / 1;
        min-height: 300px;
    }

    .home-hero-swiper .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0.5rem;
        display: block;
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

<!-- السلايدر -->
<div class="my-4 px-2">
    <div class="swiper-container home-hero-swiper">
        <div class="swiper-wrapper">
            @foreach($sliders as $slider)
                @php
                    $imageUrl = $slider->getMediaUrl('slider', $slider, null, 'media', 'slider');
                    $sliderImage = $imageUrl ?: $fallbackImage;
                @endphp
                @if($imageUrl)
                    <div class="swiper-slide flex justify-center">
                        <img src="{{ $sliderImage }}"
                             alt="{{ $slider->name ?? 'slider' }}"
                             width="1200" height="600"
                             loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                             fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                             decoding="async">
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- اختيار العملة -->
<div class="mt-2">
    @include('website.partials.currency_picker')
</div>

<!-- أقسام سريعة -->
<div class="px-4 mt-4">
    <div class="grid grid-cols-2 gap-3 sm:gap-4">

        @php
            $chargeTitle = $settings?->home_quick_charge_title ?: 'شحن جواهر';
            $codesTitle = $settings?->home_quick_codes_title ?: 'أكواد ملابس';
            $cashTitle = $settings?->home_quick_cash_exchange_title ?: 'استبدل رصيدك كاش';
            $moneyTitle = $settings?->home_quick_money_exchange_title ?: 'تحويل الأموال';

            $defaultQuickImg = asset('public/uploads/oki/old.png');
            $chargeImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_charge') ?: $defaultQuickImg;
            $codesImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_codes') ?: $defaultQuickImg;
            $cashImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_cash_exchange') ?: null;
            $moneyImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_money_exchange') ?: null;

            $chargeEnabled = (bool) ($chargeEnabled ?? ($settings?->charge_enabled ?? true));
            $codesEnabled = (bool) ($codesEnabled ?? ($settings?->codes_enabled ?? true));
            $cashEnabled = (bool) ($cashExchangeEnabled ?? ($settings?->cash_exchange_enabled ?? true));
            $moneyEnabled = (bool) ($moneyExchangeEnabled ?? false);
        @endphp

        <!-- شحن جواهر -->
        <a href="{{ $chargeEnabled ? route('website.diamonds.charge') : 'javascript:void(0)' }}"
           class="group relative overflow-hidden rounded-2xl border border-yellow-200 bg-gradient-to-l from-yellow-50 to-white shadow-sm transition hover:shadow-md active:scale-[0.99] {{ $chargeEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
            <div class="p-2.5 sm:p-4">
                <div class="flex items-center justify-center">
                    {{-- غيّر الصورة كما تريد --}}
                    <img src="{{ $chargeImg }}"
                         alt="{{ $chargeTitle }}"
                         class="w-full h-24 sm:h-32 object-cover rounded-xl"
                         loading="lazy" decoding="async">
                </div>

                <div class="mt-2 text-center">
                    <span class="inline-block text-xs text-gray-500">القسم</span>
                    <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mt-0.5">{{ $chargeTitle }}</h3>
                </div>

                <p class="text-[11px] sm:text-sm text-gray-600 mt-2 text-center leading-relaxed">
                    ادخل للشحن واختر الباقة المناسبة
                </p>

                <div class="mt-3 flex justify-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-yellow-400/15 px-3 py-1 text-xs font-bold text-yellow-700">
                        <i class="bi bi-gem"></i>
                        دخول القسم
                    </span>
                </div>
            </div>

            @unless($chargeEnabled)
                <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                    <span class="rounded-full bg-yellow-700 text-white text-xs font-extrabold px-3 py-1.5 shadow">
                        غير متاح حالياً
                    </span>
                </div>
            @endunless
        </a>

        <!-- أكواد جواهر -->
        <a href="{{ $codesEnabled ? route('website.diamonds.codes') : 'javascript:void(0)' }}"
           class="group relative overflow-hidden rounded-2xl border border-blue-200 bg-gradient-to-l from-blue-50 to-white shadow-sm transition hover:shadow-md active:scale-[0.99] {{ $codesEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
            <div class="p-2.5 sm:p-4">
                <div class="flex items-center justify-center">
                    {{-- غيّر الصورة كما تريد --}}
                    <img src="{{ $codesImg }}"
                         alt="{{ $codesTitle }}"
                         class="w-full h-24 sm:h-32 object-cover rounded-xl"
                         loading="lazy" decoding="async">
                </div>

                <div class="mt-2 text-center">
                    <span class="inline-block text-xs text-gray-500">القسم</span>
                    <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mt-0.5">{{ $codesTitle }}</h3>
                </div>

                <p class="text-[11px] sm:text-sm text-gray-600 mt-2 text-center leading-relaxed">
                    ادخل لشراء/استخدام أكواد الجواهر
                </p>

                <div class="mt-3 flex justify-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-3 py-1 text-xs font-bold text-blue-700">
                        <i class="bi bi-upc-scan"></i>
                        دخول القسم
                    </span>
                </div>
            </div>

            @unless($codesEnabled)
                <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                    <span class="rounded-full bg-blue-700 text-white text-xs font-extrabold px-3 py-1.5 shadow">
                        غير متاح حالياً
                    </span>
                </div>
            @endunless
        </a>

        <!-- استبدل رصيدك كاش -->
        <a href="{{ $cashEnabled ? route('website.cash_exchange.index') : 'javascript:void(0)' }}"
           class="group relative overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-l from-emerald-50 to-white shadow-sm transition hover:shadow-md active:scale-[0.99] {{ $cashEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
            <div class="p-2.5 sm:p-4">
                <div class="flex items-center justify-center">
                    @if($cashImg)
                        <img src="{{ $cashImg }}" alt="{{ $cashTitle }}"
                             class="w-full h-24 sm:h-32 object-cover rounded-xl"
                             loading="lazy" decoding="async">
                    @else
                        <div class="w-full h-24 sm:h-32 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-700 text-4xl font-extrabold">
                            💵
                        </div>
                    @endif
                </div>

                <div class="mt-2 text-center">
                    <span class="inline-block text-xs text-gray-500">القسم</span>
                    <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mt-0.5">{{ $cashTitle }}</h3>
                </div>

                <p class="text-[11px] sm:text-sm text-gray-600 mt-2 text-center leading-relaxed">
                    اختر فئة الرصيد وادخل كود البطاقة لاستلام كاش
                </p>

                <div class="mt-3 flex justify-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-bold text-emerald-700">
                        <i class="bi bi-cash-coin"></i>
                        دخول القسم
                    </span>
                </div>
            </div>

            @unless($cashEnabled)
                <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                    <span class="rounded-full bg-emerald-700 text-white text-xs font-extrabold px-3 py-1.5 shadow">
                        غير متاح حالياً
                    </span>
                </div>
            @endunless
        </a>

        <!-- تحويل الأموال / تبادل العملات -->
        <a href="{{ $moneyEnabled ? route('website.money_exchange.index') : 'javascript:void(0)' }}"
           class="group relative overflow-hidden rounded-2xl border border-purple-200 bg-gradient-to-l from-purple-50 to-white shadow-sm transition hover:shadow-md active:scale-[0.99] {{ $moneyEnabled ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
            <div class="p-2.5 sm:p-4">
                <div class="flex items-center justify-center">
                    @if($moneyImg)
                        <img src="{{ $moneyImg }}" alt="{{ $moneyTitle }}"
                             class="w-full h-24 sm:h-32 object-cover rounded-xl"
                             loading="lazy" decoding="async">
                    @else
                        <div class="w-full h-24 sm:h-32 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-700 text-4xl font-extrabold">
                            💱
                        </div>
                    @endif
                </div>

                <div class="mt-2 text-center">
                    <span class="inline-block text-xs text-gray-500">القسم</span>
                    <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mt-0.5">{{ $moneyTitle }}</h3>
                </div>

                <p class="text-[11px] sm:text-sm text-gray-600 mt-2 text-center leading-relaxed">
                    تحويل SAR ↔ USDT حسب سعر الصرف
                </p>

                <div class="mt-3 flex justify-center">
                    <span class="inline-flex items-center gap-2 rounded-full bg-purple-500/10 px-3 py-1 text-xs font-bold text-purple-700">
                        <i class="bi bi-currency-exchange"></i>
                        دخول القسم
                    </span>
                </div>
            </div>

            @unless($moneyEnabled)
                <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                    <span class="rounded-full bg-purple-700 text-white text-xs font-extrabold px-3 py-1.5 shadow">
                        غير متاح حالياً
                    </span>
                </div>
            @endunless
        </a>

    </div>
</div>



<!-- الأقسام والمنتجات -->
@foreach($sections as $section)
    <div class="px-4 py-6">
        <h2 class="text-center font-bold text-xl mb-4">{{ $section->name }}</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(($section->products ?? collect())->sortByDesc('price') as $product)
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

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
       @foreach($products->sortByDesc('price') as $product)
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

<!-- آراء العملاء -->
<div class="px-4 py-10 bg-gradient-to-l from-yellow-50 to-white border-t border-gray-200">
    <h2 class="text-center font-bold text-xl mb-6 text-gray-800">
        آراء <span class="text-yellow-500">عملائنا</span>
    </h2>

    <div class="swiper reviewsSwiper max-w-6xl mx-auto">
        <div class="swiper-wrapper">
            @php
                $reviews = [
                    ['name' => 'تركي القحطاني 🇸🇦', 'text' => 'متجر الممالك ثقة 🔥 جربته أكثر من مرة وما خيب ظني أبد 💪'],
                    ['name' => 'سارة المطيري 🇸🇦', 'text' => 'تنفيذ سريع جدًا وخدمة محترمة 🖤 أفضل متجر فري فاير بلا منازع!'],
                    ['name' => 'عبدالله الشهري 🇸🇦', 'text' => 'يا عيال المتجر ذا فخم 🔥 سرعة بالتنفيذ وثقة ما بعدها ثقة 💚'],
                    ['name' => 'ريم العتيبي 🇸🇦', 'text' => 'الممالك فخم فخم فخم 👑 كل طلب يوصلني بثواني حرفيًا!'],
                    ['name' => 'فهد الحربي 🇸🇦', 'text' => 'اطلق ممالك فالعالم 💚 ما في تأخير ولا مشاكل، متجر محترم جدًا.'],
                    ['name' => 'نواف الدوسري 🇸🇦', 'text' => 'تجربة خرافية 😍 أسعار ممتازة وتنفيذ لحظي، شكراً لكم!'],
                    ['name' => 'منيرة القحطاني 🇸🇦', 'text' => 'المتجر الوحيد اللي أتعامل معه 💛 تعامل راقي وسرعة تنفيذ 🔥'],
                    ['name' => 'عبدالرحمن الزهراني 🇸🇦', 'text' => 'متجر الممالك يستحق خمس نجوم ⭐⭐⭐⭐⭐ ثقة وأمان وسرعة.'],
                    ['name' => 'مشعل العنزي 🇸🇦', 'text' => 'جربت أكثر من مرة وكل مرة نفس الجودة 💚 المتجر الأفضل بلا منازع.'],
                    ['name' => 'لطيفة الشمري 🇸🇦', 'text' => 'والله ما أتوقع فيه متجر ينافسهم 🔥 سرعة ودقة بالتعامل.'],
                ];
            @endphp

            @foreach($reviews as $review)
                <div class="swiper-slide bg-white rounded-2xl shadow-sm p-4 border border-gray-100 transition transform hover:scale-[1.02]">
                    <h3 class="font-semibold text-gray-800 mb-1 text-sm sm:text-base">{{ $review['name'] }}</h3>
                    <p class="text-gray-600 text-xs sm:text-sm leading-relaxed">{{ $review['text'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="swiper-pagination mt-4"></div>
    </div>
</div>

<!-- المزايا -->
<div class="px-4 py-8 text-center bg-white border-t border-gray-100">
    <div class="flex flex-wrap justify-center gap-8">
        <div class="flex items-center gap-2">
            <i class="bi bi-shield-check text-green-500 text-xl"></i>
            <span class="text-gray-700 font-semibold text-sm">ضمان استرجاع الأموال</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="bi bi-truck text-blue-500 text-xl"></i>
            <span class="text-gray-700 font-semibold text-sm">تنفيذ فوري وآمن</span>
        </div>
        <div class="flex items-center gap-2">
            <i class="bi bi-lock text-yellow-500 text-xl"></i>
            <span class="text-gray-700 font-semibold text-sm">دفع مشفر وآمن</span>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const stars = document.querySelectorAll(".star");
    stars.forEach((star, index) => {
        star.addEventListener("click", () => {
            stars.forEach((s, i) => {
                s.classList.toggle("text-yellow-400", i <= index);
                s.classList.toggle("text-gray-400", i > index);
                if (s.previousElementSibling) {
                    s.previousElementSibling.checked = true;
                }
            });
        });
    });
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