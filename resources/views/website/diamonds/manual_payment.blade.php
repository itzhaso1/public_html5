@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'الدفع اليدوي' }}
@endsection

@section('content')
@php
    $isCodes = ($product?->service_type ?? null) === 'codes';
    $methods = $paymentMethods ?? config('bank.methods', []);
    $enabledMethods = collect($methods)->filter(fn($m) => is_array($m) && ($m['enabled'] ?? false));
    $allowedCharge = collect($allowedChargeMethodKeys ?? config('bank.charge_method_keys', []))->values()->all();
    $methodKeys = $isCodes ? $enabledMethods->keys()->all() : array_values(array_intersect($enabledMethods->keys()->all(), $allowedCharge));
    $selectedMethod = old('payment_method') ?: ($methodKeys[0] ?? null);
    $userPhone = preg_replace('/\D+/', '', (string) (auth()->user()?->phone ?? auth()->user()?->profile?->phone ?? ''));
    $phoneRequired = $userPhone === '';
    $basePrice = (float) ($product?->price ?? 0);
@endphp

@include('website.diamonds.partials.header', [
    'title' => $isCodes ? 'أكواد ملابس' : 'شحن الجواهر',
    'subtitle' => 'اخترت الدفع اليدوي: حوّل المبلغ ثم ارفع إيصال التحويل.',
    'active' => $isCodes ? 'codes' : 'charge',
])

<section class="max-w-4xl mx-auto px-4 pb-12" dir="rtl">
    <div class="mt-4">
        @include('website.partials.currency_picker')
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h2 class="text-xl font-extrabold text-gray-900">تفاصيل الباقة</h2>
            <div class="mt-3 rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-sm text-gray-700 font-bold">{{ $product->name }}</div>
                <div class="mt-1 text-xs text-gray-500">السعر</div>
                <div class="mt-1 text-3xl font-extrabold text-green-600 product-price"
                     data-base-price="{{ (float) $basePrice }}">
                    <span class="current-price">ر.س {{ number_format((float) $basePrice, 2) }}</span>
                </div>
            </div>

            <div class="mt-5">
                <div class="text-sm font-extrabold text-gray-900">بيانات التحويل البنكي</div>
                <div class="mt-3 space-y-3 text-sm text-gray-700">
                    @foreach($methodKeys as $key)
                        @php $m = $enabledMethods->get($key, []); @endphp
                        <div class="payment-card payment-{{ $key }} {{ $selectedMethod === $key ? '' : 'hidden' }} rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-extrabold text-gray-900">{{ $m['title'] ?? $key }}</div>
                                <span class="payment-badge text-[11px] font-extrabold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">
                                    محدد
                                </span>
                            </div>

                            <div class="mt-3 divide-y divide-gray-100">
                            @if($key === 'sa_bank')
                                @if(!empty($m['bank_name']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">البنك</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-semibold select-all">{{ $m['bank_name'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['bank_name'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['account_name']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">اسم الحساب</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-semibold select-all">{{ $m['account_name'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['account_name'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['account_number']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">رقم الحساب</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['account_number'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['account_number'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['iban']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">IBAN</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['iban'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['iban'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                            @elseif($key === 'jo_click')
                                @if(!empty($m['bank_name']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">البنك</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-semibold select-all">{{ $m['bank_name'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['bank_name'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['account_name']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">الاسم</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-semibold select-all">{{ $m['account_name'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['account_name'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['click_id']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">Click ID</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['click_id'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['click_id'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                            @elseif($key === 'binance_trc20')
                                <div class="py-2 flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">Network</span>
                                    <span class="flex items-center gap-2">
                                        <span class="font-semibold select-all">{{ $m['network'] ?? 'TRC20' }}</span>
                                        <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['network'] ?? 'TRC20' }}" aria-label="Copy">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                        </button>
                                    </span>
                                </div>
                                @if(!empty($m['address']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">Address</span>
                                        <span class="flex items-center gap-2">
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['address'] }}</span>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['address'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                                @if(!empty($m['link']))
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">Link</span>
                                        <span class="flex items-center gap-2">
                                            <a class="text-blue-600 underline" href="{{ $m['link'] }}" target="_blank">فتح الرابط</a>
                                            <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $m['link'] }}" aria-label="Copy">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                            </button>
                                        </span>
                                    </div>
                                @endif
                            @else
                                @php
                                    $labels = [
                                        'bank_name' => 'البنك',
                                        'account_name' => 'اسم الحساب',
                                        'account_number' => 'رقم الحساب',
                                        'iban' => 'IBAN',
                                        'click_id' => 'Click ID',
                                        'network' => 'Network',
                                        'address' => 'Address',
                                        'link' => 'Link',
                                    ];
                                @endphp
                                @foreach($labels as $field => $label)
                                    @php $val = $m[$field] ?? null; @endphp
                                    @if(!empty($val))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">{{ $label }}</span>
                                            <span class="flex items-center gap-2">
                                                @if($field === 'link')
                                                    <a class="text-blue-600 underline" href="{{ $val }}" target="_blank">فتح الرابط</a>
                                                    <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $val }}" aria-label="Copy">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                                    </button>
                                                @else
                                                    <span class="{{ in_array($field, ['account_number','iban','address','click_id'], true) ? 'font-mono' : 'font-semibold' }} font-semibold text-[13px] select-all">{{ $val }}</span>
                                                    <button type="button" class="copy-trigger text-blue-600 hover:text-blue-800" data-copy-text="{{ $val }}" aria-label="Copy">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4C2.9 1 2 1.9 2 3v14h2V3h12V1zm3 4H8C6.9 5 6 5.9 6 7v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                                                    </button>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            @if(!empty($m['note']))
                                <div class="pt-3 text-xs text-gray-500">{{ $m['note'] }}</div>
                            @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <h2 class="text-xl font-extrabold text-gray-900">إرسال طلب الدفع اليدوي</h2>
            <p class="mt-1 text-sm text-gray-600">
                @if($isCodes)
                    ارفع إيصال التحويل فقط، وسيتم تسليم الكود بعد الموافقة.
                @else
                    أدخل الـ ID وارفع إيصال التحويل.
                @endif
            </p>

            <form class="mt-4 space-y-4" method="POST" enctype="multipart/form-data"
                  action="{{ route('website.diamonds.manual_payment.store', $product) }}">
                @csrf

                @if(count($methodKeys))
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">طريقة الدفع</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            @foreach($methodKeys as $key)
                                @php $m = $enabledMethods->get($key, []); @endphp
                                @php
                                    $methodUi = [
                                        'sa_bank' => ['emoji' => '🇸🇦', 'label' => 'تحويل بنكي سعودي'],
                                        'jo_click' => ['emoji' => '🇯🇴', 'label' => 'تحويل أردني'],
                                        'binance_trc20' => ['emoji' => '💰', 'label' => 'Binance USDT (TRC20)'],
                                    ];
                                    $ui = $methodUi[$key] ?? null;
                                @endphp
                                <label class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 transition">
                                    <input type="radio" name="payment_method" value="{{ $key }}"
                                           class="accent-yellow-500"
                                           {{ $selectedMethod === $key ? 'checked' : '' }}>
                                    <span class="text-base">{{ $ui['emoji'] ?? '💳' }}</span>
                                    <span class="font-extrabold">{{ $ui['label'] ?? ($m['title'] ?? $key) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                @endif

                @unless($isCodes)
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-1">Player ID / ID الحساب</label>
                        <div class="flex gap-2">
                            <input id="playerIdInput" type="text" name="player_id" value="{{ old('player_id') }}"
                                   class="flex-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                                   placeholder="مثال: 123456789" required>
                            <button id="checkPlayerBtn" type="button"
                                    class="whitespace-nowrap rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-extrabold hover:bg-gray-50 transition">
                                تحقق من الاسم
                            </button>
                        </div>
                        <div id="playerCheckResult" class="mt-2 text-xs"></div>
                        @error('player_id')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                @endunless

                @php
                    $phoneFull = preg_replace('/\D+/', '', (string) old('contact_phone', $userPhone));
                    $waCountries = [
                        'SA' => ['dial' => '966', 'label' => '🇸🇦 السعودية (+966)'],
                        'JO' => ['dial' => '962', 'label' => '🇯🇴 الأردن (+962)'],
                        'AE' => ['dial' => '971', 'label' => '🇦🇪 الإمارات (+971)'],
                        'KW' => ['dial' => '965', 'label' => '🇰🇼 الكويت (+965)'],
                        'QA' => ['dial' => '974', 'label' => '🇶🇦 قطر (+974)'],
                        'BH' => ['dial' => '973', 'label' => '🇧🇭 البحرين (+973)'],
                        'OM' => ['dial' => '968', 'label' => '🇴🇲 عُمان (+968)'],
                        'IQ' => ['dial' => '964', 'label' => '🇮🇶 العراق (+964)'],
                        'LB' => ['dial' => '961', 'label' => '🇱🇧 لبنان (+961)'],
                        'PS' => ['dial' => '970', 'label' => '🇵🇸 فلسطين (+970)'],
                        'YE' => ['dial' => '967', 'label' => '🇾🇪 اليمن (+967)'],
                        'SY' => ['dial' => '963', 'label' => '🇸🇾 سوريا (+963)'],
                        'EG' => ['dial' => '20',  'label' => '🇪🇬 مصر (+20)'],
                        'SD' => ['dial' => '249', 'label' => '🇸🇩 السودان (+249)'],
                        'LY' => ['dial' => '218', 'label' => '🇱🇾 ليبيا (+218)'],
                        'TN' => ['dial' => '216', 'label' => '🇹🇳 تونس (+216)'],
                        'DZ' => ['dial' => '213', 'label' => '🇩🇿 الجزائر (+213)'],
                        'MA' => ['dial' => '212', 'label' => '🇲🇦 المغرب (+212)'],
                        'MR' => ['dial' => '222', 'label' => '🇲🇷 موريتانيا (+222)'],
                        'SO' => ['dial' => '252', 'label' => '🇸🇴 الصومال (+252)'],
                        'DJ' => ['dial' => '253', 'label' => '🇩🇯 جيبوتي (+253)'],
                        'KM' => ['dial' => '269', 'label' => '🇰🇲 جزر القمر (+269)'],
                    ];

                    $defaultCountry = 'SA';
                    $defaultLocal = $phoneFull;

                    $dials = [];
                    foreach ($waCountries as $cc => $info) { $dials[$cc] = (string) ($info['dial'] ?? ''); }
                    uasort($dials, fn($a, $b) => strlen($b) <=> strlen($a)); // match longer first

                    foreach ($dials as $cc => $dial) {
                        if ($dial !== '' && str_starts_with($phoneFull, $dial)) {
                            $defaultCountry = $cc;
                            $defaultLocal = substr($phoneFull, strlen($dial));
                            break;
                        }
                    }

                    $defaultLocal = ltrim((string) $defaultLocal, '0');
                @endphp
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">رقم واتساب لاستلام إشعار الطلب</label>
                    <input type="hidden" name="contact_phone" id="waFullPhone" value="{{ $phoneFull }}">
                    <div class="grid grid-cols-1 sm:grid-cols-[11rem,1fr] gap-2">
                        <select name="contact_phone_country" id="waCountry"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm bg-white outline-none focus:ring-2 focus:ring-yellow-400/60">
                            @foreach($waCountries as $cc => $info)
                                <option value="{{ $cc }}" {{ ($defaultCountry === $cc) ? 'selected' : '' }}>
                                    {{ $info['label'] ?? ($cc . ' +' . ($info['dial'] ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="tel" name="contact_phone_local" id="waLocal"
                               value="{{ old('contact_phone_local', $defaultLocal) }}"
                               class="min-w-0 rounded-xl border border-gray-200 px-4 py-2 text-sm font-mono outline-none focus:ring-2 focus:ring-yellow-400/60 text-left"
                               dir="ltr"
                               placeholder="مثال: 5XXXXXXXX"
                               inputmode="numeric" autocomplete="tel"
                               {{ $phoneRequired ? 'required' : '' }}>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">سيتم إضافة كود الدولة تلقائيًا. اكتب الرقم بدون 0 في البداية.</div>
                    @error('contact_phone')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    @error('contact_phone_local')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">إيصال التحويل</label>
                    <input id="receiptInput" type="file" name="receipt"
                           class="hidden"
                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                           required>
                    <div class="space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="button" id="pickReceiptImage"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-extrabold text-gray-800 hover:bg-gray-50 transition">
                                اختيار صورة الإيصال
                                <span class="text-xs font-mono text-gray-500">JPG/PNG/WEBP</span>
                            </button>
                            <button type="button" id="pickReceiptPdf"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-extrabold text-gray-800 hover:bg-gray-50 transition">
                                اختيار PDF
                                <span class="text-xs font-mono text-gray-500">PDF</span>
                            </button>
                        </div>
                        <div id="receiptName" class="text-xs text-gray-600 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
                            لم يتم اختيار ملف
                        </div>
                        <div class="text-xs text-gray-500">الحد الأقصى: 5MB.</div>
                    </div>
                    @error('receipt')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-yellow-400 hover:text-black transition">
                    إرسال الطلب
                </button>
            </form>

            @if(config('bank.whatsapp'))
                <a class="mt-3 w-full inline-flex items-center justify-center rounded-xl bg-[#25D366] px-5 py-3 text-sm font-extrabold text-white hover:brightness-95 transition"
                   target="_blank"
                   href="https://wa.me/{{ preg_replace('/\\D+/', '', config('bank.whatsapp')) }}">
                    إرسال الإيصال عبر واتساب (اختياري)
                </a>
            @endif
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
  (function () {
    const country = document.getElementById('waCountry');
    const local = document.getElementById('waLocal');
    const full = document.getElementById('waFullPhone');
    if (!country || !local || !full) return;

    const dialByCountry = {
      SA: '966', JO: '962', AE: '971', KW: '965', QA: '974', BH: '973', OM: '968',
      IQ: '964', LB: '961', PS: '970', YE: '967', SY: '963', EG: '20', SD: '249',
      LY: '218', TN: '216', DZ: '213', MA: '212', MR: '222', SO: '252', DJ: '253', KM: '269'
    };
    const digitsOnly = (v) => String(v || '').replace(/\D+/g, '');
    const build = () => {
      const c = String(country.value || 'SA').toUpperCase();
      const dial = dialByCountry[c] || '966';
      let l = digitsOnly(local.value);
      l = l.replace(/^0+/, ''); // remove leading zeros
      full.value = dial + l;
    };
    country.addEventListener('change', build);
    local.addEventListener('input', build);
    build();
  })();
</script>
<script>
  (function () {
    const input = document.getElementById('receiptInput');
    const nameEl = document.getElementById('receiptName');
    const btnImg = document.getElementById('pickReceiptImage');
    const btnPdf = document.getElementById('pickReceiptPdf');
    if (!input || !nameEl || !btnImg || !btnPdf) return;

    const setName = () => {
      const f = input.files && input.files[0] ? input.files[0] : null;
      nameEl.textContent = f ? (f.name || 'تم اختيار ملف') : 'لم يتم اختيار ملف';
    };

    btnImg.addEventListener('click', () => {
      input.accept = 'image/*';
      input.click();
    });
    btnPdf.addEventListener('click', () => {
      input.accept = 'application/pdf';
      input.click();
    });
    input.addEventListener('change', setName);
    setName();
  })();
</script>
@unless($isCodes)
<script>
  (function () {
    const btn = document.getElementById('checkPlayerBtn');
    const input = document.getElementById('playerIdInput');
    const out = document.getElementById('playerCheckResult');
    if (!btn || !input || !out) return;

    const csrf = @json(csrf_token());
    const url = @json(route('website.diamonds.check_player'));

    const setMsg = (html, cls) => {
      out.className = 'mt-2 text-xs ' + (cls || '');
      out.innerHTML = html;
    };

    btn.addEventListener('click', async () => {
      const playerId = (input.value || '').trim();
      if (playerId.length < 3) {
        setMsg('ضع Player ID صحيح.', 'text-red-600');
        return;
      }

      btn.disabled = true;
      setMsg('جارِ التحقق...', 'text-gray-500');

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ player_id: playerId })
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          setMsg('حدث خطأ أثناء التحقق. حاول لاحقاً.', 'text-red-600');
          return;
        }

        if (data.success === true) {
          const name = data.player_name ? String(data.player_name) : '—';
          const region = data.region ? String(data.region) : '—';
          const cached = data.cached ? ' (cached)' : '';
          setMsg(`✅ الاسم: <b>${name}</b> — Region: <b>${region}</b>${cached}`, 'text-green-700');
        } else {
          const raw = data.msg ? String(data.msg) : 'فشل التحقق';
          const friendly = (raw === 'NOT_READY' || raw === 'DUPLICATE_TASK')
            ? 'الطلب قيد المعالجة، انتظر قليلًا ثم أعد المحاولة.'
            : raw;
          setMsg(`❌ ${friendly}`, 'text-red-600');
        }
      } catch (e) {
        setMsg('فشل الاتصال. حاول لاحقاً.', 'text-red-600');
      } finally {
        btn.disabled = false;
      }
    });
  })();
</script>
@endunless
<script>
  (function () {
    const radios = document.querySelectorAll('input[name="payment_method"]');
    if (!radios.length) return;
    const toggle = (key) => {
      document.querySelectorAll('.payment-card').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('ring-2', 'ring-blue-400/60', 'border-blue-200', 'bg-blue-50/30');
        const badge = el.querySelector('.payment-badge');
        if (badge) badge.classList.add('hidden');
      });
      const target = document.querySelector('.payment-' + key);
      if (target) {
        target.classList.remove('hidden');
        target.classList.add('ring-2', 'ring-blue-400/60', 'border-blue-200', 'bg-blue-50/30');
        const badge = target.querySelector('.payment-badge');
        if (badge) badge.classList.remove('hidden');
      }
    };
    radios.forEach(r => r.addEventListener('change', () => toggle(r.value)));
    const checked = document.querySelector('input[name="payment_method"]:checked');
    if (checked) toggle(checked.value);
  })();
</script>
@endpush

