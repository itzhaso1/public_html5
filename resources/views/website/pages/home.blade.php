@extends('website.layouts.common.website')

@push('css')
<style>
    .home-featured-products-swiper .featured-product-card {
        display: grid;
        grid-template-rows: auto auto minmax(3.2rem, 3.2rem) minmax(2.75rem, 2.75rem) auto;
        row-gap: 0.35rem;
    }

    .home-featured-products-swiper {
        padding: 0 6px 42px;
    }

    .home-featured-products-swiper .swiper-slide {
        display: flex;
        justify-content: center;
        height: auto;
    }

    .home-featured-products-swiper .swiper-slide > .product {
        width: 100%;
        max-width: 300px;
        margin-inline: auto;
        display: flex;
        flex-direction: column;
    }

    .home-featured-products-swiper .swiper-pagination {
        position: relative !important;
        bottom: 0 !important;
        margin-top: 14px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        pointer-events: auto;
    }

    .home-featured-products-swiper .home-featured-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        opacity: 1;
        margin: 0 5px !important;
        background: #d1d5db;
        border-radius: 999px;
        transition: width 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
    }

    .home-featured-products-swiper .home-featured-dot.is-active {
        width: 30px;
        background: linear-gradient(90deg, #facc15 0%, #f59e0b 100%);
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    }

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

    @media (max-width: 639.98px) {
        .home-featured-products-swiper .swiper-slide > .product {
            height: 352px;
            min-height: 352px;
            max-height: 352px;
        }

        .home-featured-products-swiper .product-img {
            height: 128px !important;
        }

        .home-featured-products-swiper .featured-product-title {
            height: 3.2rem;
            line-height: 1.6rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .home-featured-products-swiper .featured-product-price {
            height: 2.75rem;
            min-height: 2.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            flex-wrap: wrap;
            overflow: hidden;
        }

        .home-featured-products-swiper .featured-product-card .featured-product-cta {
            margin-top: 0 !important;
            padding-top: 0 !important;
            align-self: end;
        }
    }
</style>
@endpush

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
@php
    $fallbackImage = asset('img/قريبا.jpg');
    $featuredAllMobileColumns = in_array((int) ($settings?->home_featured_all_mobile_columns ?? 1), [1, 2], true)
        ? (int) ($settings?->home_featured_all_mobile_columns ?? 1)
        : 1;
    $featuredAllAutoplaySeconds = in_array((int) ($settings?->home_featured_all_autoplay_seconds ?? 3), [2, 3], true)
        ? (int) ($settings?->home_featured_all_autoplay_seconds ?? 3)
        : 3;
    $chargeTitle = $settings?->home_quick_charge_title ?: 'شحن جواهر';
    $codesTitle = $settings?->home_quick_codes_title ?: 'أكواد ملابس';
    $cashTitle = $settings?->home_quick_cash_exchange_title ?: 'استبدل رصيدك كاش';
    $moneyTitle = $settings?->home_quick_money_exchange_title ?: 'تحويل الأموال';
    $freefireTitle = $settings?->home_quick_freefire_title ?: 'حسابات فري فاير';
    $freefirePosition = strtolower((string) ($settings?->home_quick_freefire_position ?? 'end'));
    $freefirePosition = in_array($freefirePosition, ['start', 'end'], true) ? $freefirePosition : 'end';

    $defaultQuickImg = $fallbackImage;
    $chargeImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_charge') ?: $defaultQuickImg;
    $codesImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_codes') ?: $defaultQuickImg;
    $cashImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_cash_exchange') ?: null;
    $moneyImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_money_exchange') ?: null;
    $freefireImg = $settings?->getMediaUrl('setting', $settings, null, 'media', 'home_quick_freefire') ?: null;

    $chargeEnabled = (bool) ($chargeEnabled ?? ($settings?->charge_enabled ?? true));
    $codesEnabled = (bool) ($codesEnabled ?? ($settings?->codes_enabled ?? true));
    $cashEnabled = (bool) ($cashExchangeEnabled ?? ($settings?->cash_exchange_enabled ?? true));
    $moneyEnabled = (bool) ($moneyExchangeEnabled ?? false);

    $quickSections = [
        [
            'key' => 'charge',
            'title' => $chargeTitle,
            'enabled' => $chargeEnabled,
            'route' => $chargeEnabled ? route('website.diamonds.charge') : 'javascript:void(0)',
            'cardClass' => 'border-yellow-200 bg-gradient-to-l from-yellow-50 to-white',
            'badgeClass' => 'bg-yellow-400/15 text-yellow-700',
            'overlayClass' => 'bg-yellow-700',
            'iconClass' => 'bi bi-gem',
            'desc' => 'ادخل للشحن واختر الباقة المناسبة',
            'img' => $chargeImg,
            'emoji' => null,
            'emojiWrap' => 'bg-yellow-500/10 text-yellow-700',
        ],
        [
            'key' => 'codes',
            'title' => $codesTitle,
            'enabled' => $codesEnabled,
            'route' => $codesEnabled ? route('website.diamonds.codes') : 'javascript:void(0)',
            'cardClass' => 'border-blue-200 bg-gradient-to-l from-blue-50 to-white',
            'badgeClass' => 'bg-blue-500/10 text-blue-700',
            'overlayClass' => 'bg-blue-700',
            'iconClass' => 'bi bi-upc-scan',
            'desc' => 'ادخل لشراء/استخدام أكواد الجواهر',
            'img' => $codesImg,
            'emoji' => null,
            'emojiWrap' => 'bg-blue-500/10 text-blue-700',
        ],
        [
            'key' => 'cash',
            'title' => $cashTitle,
            'enabled' => $cashEnabled,
            'route' => $cashEnabled ? route('website.cash_exchange.index') : 'javascript:void(0)',
            'cardClass' => 'border-emerald-200 bg-gradient-to-l from-emerald-50 to-white',
            'badgeClass' => 'bg-emerald-500/10 text-emerald-700',
            'overlayClass' => 'bg-emerald-700',
            'iconClass' => 'bi bi-cash-coin',
            'desc' => 'اختر فئة الرصيد وادخل كود البطاقة لاستلام كاش',
            'img' => $cashImg,
            'emoji' => '💵',
            'emojiWrap' => 'bg-emerald-500/10 text-emerald-700',
        ],
        [
            'key' => 'money',
            'title' => $moneyTitle,
            'enabled' => $moneyEnabled,
            'route' => $moneyEnabled ? route('website.money_exchange.index') : 'javascript:void(0)',
            'cardClass' => 'border-purple-200 bg-gradient-to-l from-purple-50 to-white',
            'badgeClass' => 'bg-purple-500/10 text-purple-700',
            'overlayClass' => 'bg-purple-700',
            'iconClass' => 'bi bi-currency-exchange',
            'desc' => 'تحويل SAR ↔ USDT حسب سعر الصرف',
            'img' => $moneyImg,
            'emoji' => '💱',
            'emojiWrap' => 'bg-purple-500/10 text-purple-700',
        ],
        [
            'key' => 'freefire',
            'title' => $freefireTitle,
            'enabled' => true,
            'route' => route('website.freefire_accounts'),
            'cardClass' => 'border-amber-300 bg-gradient-to-l from-amber-50 to-white',
            'badgeClass' => 'bg-amber-500/10 text-amber-700',
            'overlayClass' => 'bg-amber-700',
            'iconClass' => 'bi bi-controller',
            'desc' => 'تصفح جميع الحسابات المميزة وباقي الحسابات داخل قسم مستقل',
            'img' => $freefireImg,
            'emoji' => '🎮',
            'emojiWrap' => 'bg-amber-500/10 text-amber-700',
        ],
    ];

    $freefireIndex = collect($quickSections)->search(fn($item) => ($item['key'] ?? '') === 'freefire');
    if ($freefireIndex !== false) {
        $freefireCard = $quickSections[$freefireIndex];
        unset($quickSections[$freefireIndex]);
        $quickSections = array_values($quickSections);
        if ($freefirePosition === 'start') {
            array_unshift($quickSections, $freefireCard);
        } else {
            $quickSections[] = $freefireCard;
        }
    }
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

<!-- المنتجات المميزة (كل الأنواع) -->
@if(($featuredAllProducts ?? collect())->isNotEmpty())
<div class="px-4 py-6">
    <h2 class="text-center font-bold text-xl mb-4 border-b border-gray-300 pb-2 text-yellow-500">
        المنتجات المميزة
    </h2>

    <div class="swiper home-featured-products-swiper"
         data-mobile-columns="{{ $featuredAllMobileColumns }}"
         data-autoplay-seconds="{{ $featuredAllAutoplaySeconds }}">
        <div class="swiper-wrapper">
            @foreach($featuredAllProducts as $product)
                @php
                    $imageUrl = $product->getMediaUrl('product', $product, null, 'media', 'product');
                    $thumb = ($product->service_type ?? null) === 'codes' ? ($product->codeThumbnail?->image_path ?? null) : null;
                    $thumbUrl = $thumb ? asset('storage/' . ltrim($thumb, '/')) : null;
                    $productImage = $imageUrl ?: ($thumbUrl ?: $fallbackImage);
                    $isSold = (bool) ($product->featured ?? false);
                    $discountPercent = null;
                    $dealEndsAt = $product->deal_ends_at ?? null;
                    $dealEndsAtIso = $dealEndsAt ? \Illuminate\Support\Carbon::parse($dealEndsAt)->toIso8601String() : null;
                    if (!empty($product->price_before_discount) && $product->price_before_discount > 0) {
                        $discountPercent = round((($product->price_before_discount - $product->price) / $product->price_before_discount) * 100);
                    }
                    $typeLabel = match((string) ($product->service_type ?? '')) {
                        'gems' => 'شحن',
                        'codes' => 'أكواد',
                        default => 'حساب',
                    };
                @endphp

                <div class="swiper-slide">
                    <div class="featured-product-card relative bg-white p-2.5 rounded-lg shadow text-center product h-full"
                         style="box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                        @if($discountPercent && $discountPercent > 0)
                            <span class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full shadow">
                                -{{ $discountPercent }}%
                            </span>
                        @endif
                        @if($isSold)
                            <span class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">
                                مباع
                            </span>
                        @else
                            <span class="absolute top-2 right-2 bg-emerald-600 text-white text-xs px-2 py-1 rounded-full">
                                متوفر
                            </span>
                        @endif
                        <span class="absolute top-11 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                            {{ $typeLabel }}
                        </span>

                        @if($dealEndsAtIso)
                            <div class="deal-countdown-wrap absolute top-2 left-20 z-10">
                                <span class="deal-countdown text-[11px] sm:text-xs bg-black/70 text-white px-2 py-1 rounded-full"
                                      data-ends="{{ $dealEndsAtIso }}">
                                    --
                                </span>
                            </div>
                        @endif

                        <img src="{{ $productImage }}"
                             class="product-img mx-auto rounded-md object-cover w-full h-40 sm:h-44"
                             alt="{{ $product->name ?? 'Product' }}"
                             loading="lazy"
                             decoding="async"
                             onerror="this.onerror=null;this.src='{{ $fallbackImage }}';">
                        <h2 class="font-bold mt-2 featured-product-title">{{ $product->name }}</h2>

                        @if(!empty($product->price_before_discount))
                            <p class="font-semibold mt-1 product-price featured-product-price text-red-600"
                               data-base-price="{{ $product->price }}"
                               data-base-old="{{ $product->price_before_discount }}">
                                <span class="current-price">ر.س {{ $product->price }}</span>
                                <span class="old-price line-through text-gray-400 text-sm ml-1">
                                    {{ $product->price_before_discount }}
                                </span>
                            </p>
                        @else
                            <p class="font-semibold mt-1 product-price featured-product-price" data-base-price="{{ $product->price }}">
                                <span class="current-price">ر.س {{ $product->price }}</span>
                            </p>
                        @endif

                        <div class="featured-product-cta mt-auto pt-3">
                            <a href="{{ route('website.product.show', $product->id) }}"
                               class="block bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600 transition font-medium text-center">
                                عرض التفاصيل
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
    </div>
</div>
@endif

<!-- أقسام سريعة -->
<div class="px-4 mt-4 mb-10 sm:mb-12">
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
        @foreach($quickSections as $sectionCard)
            <a href="{{ $sectionCard['route'] }}"
               class="group relative overflow-hidden rounded-2xl border shadow-sm transition hover:shadow-md active:scale-[0.99] {{ $sectionCard['cardClass'] }} {{ $sectionCard['enabled'] ? '' : 'opacity-60 cursor-not-allowed pointer-events-none' }}">
                <div class="p-2.5 sm:p-4">
                    <div class="flex items-center justify-center">
                        @if(!empty($sectionCard['img']))
                            <img src="{{ $sectionCard['img'] }}"
                                 alt="{{ $sectionCard['title'] }}"
                                 class="w-full h-24 sm:h-32 object-cover rounded-xl"
                                 loading="lazy"
                                 decoding="async">
                        @else
                            <div class="w-full h-24 sm:h-32 rounded-xl flex items-center justify-center text-4xl font-extrabold {{ $sectionCard['emojiWrap'] }}">
                                {{ $sectionCard['emoji'] ?: '🎮' }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-2 text-center">
                        <span class="inline-block text-xs text-gray-500">القسم</span>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900 mt-0.5">{{ $sectionCard['title'] }}</h3>
                    </div>

                    <p class="text-[11px] sm:text-sm text-gray-600 mt-2 text-center leading-relaxed">
                        {{ $sectionCard['desc'] }}
                    </p>

                    <div class="mt-3 flex justify-center">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $sectionCard['badgeClass'] }}">
                            <i class="{{ $sectionCard['iconClass'] }}"></i>
                            دخول القسم
                        </span>
                    </div>
                </div>

                @unless($sectionCard['enabled'])
                    <div class="absolute inset-0 bg-white/70 flex items-center justify-center">
                        <span class="rounded-full {{ $sectionCard['overlayClass'] }} text-white text-xs font-extrabold px-3 py-1.5 shadow">
                            غير متاح حالياً
                        </span>
                    </div>
                @endunless
            </a>
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
(function () {
    const initHomeFeaturedProductsSwiper = () => {
        const homeFeaturedEl = document.querySelector('.home-featured-products-swiper');
        if (!homeFeaturedEl || !window.Swiper || homeFeaturedEl.swiper) return;

        const slidesCount = homeFeaturedEl.querySelectorAll('.swiper-slide').length;
        const mobileColumns = Number(homeFeaturedEl.dataset.mobileColumns || 1) === 2 ? 2 : 1;
        const autoplaySecondsRaw = Number(homeFeaturedEl.dataset.autoplaySeconds || 3);
        const autoplaySeconds = autoplaySecondsRaw === 2 ? 2 : 3;
        new Swiper(homeFeaturedEl, {
            slidesPerView: mobileColumns,
            spaceBetween: 10,
            grabCursor: true,
            centeredSlides: false,
            watchOverflow: true,
            allowTouchMove: true,
            simulateTouch: true,
            loop: slidesCount > 1,
            grid: { rows: 1, fill: 'row' },
            autoplay: slidesCount > 1 ? {
                delay: autoplaySeconds * 1000,
                disableOnInteraction: false,
            } : false,
            pagination: {
                el: '.home-featured-products-swiper .swiper-pagination',
                clickable: true,
                bulletClass: 'home-featured-dot',
                bulletActiveClass: 'is-active',
                renderBullet: function (index, className) {
                    return `<span class="${className}" aria-label="slide ${index + 1}"></span>`;
                },
            },
            breakpoints: {
                480: { slidesPerView: mobileColumns, spaceBetween: 12, grid: { rows: 1, fill: 'row' } },
                640: { slidesPerView: 2, spaceBetween: 14, grid: { rows: 1, fill: 'row' } },
                1024: { slidesPerView: 3, spaceBetween: 16, grid: { rows: 1, fill: 'row' } },
            },
        });
    };

    const boot = () => {
        if (window.Swiper) {
            initHomeFeaturedProductsSwiper();
            return;
        }

        window.__swiperQueue = window.__swiperQueue || [];
        window.__swiperQueue.push(initHomeFeaturedProductsSwiper);

        let tries = 0;
        const timer = setInterval(() => {
            tries += 1;
            if (window.Swiper) {
                clearInterval(timer);
                initHomeFeaturedProductsSwiper();
            }
            if (tries >= 40) {
                clearInterval(timer);
            }
        }, 150);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
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