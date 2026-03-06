@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'انت وحظك' }}
@endsection

@section('content')
@php
    /** @var \App\Models\Product $product */
    $price = (float) ($product?->price ?? 0);
    $hasStock = ((int) ($product?->available_codes_count ?? 0)) > ((int) ($product?->pending_manual_requests_count ?? 0));
@endphp

@include('website.diamonds.partials.header', [
    'title' => 'انت وحظك - أكواد ملابس فري فاير',
    'subtitle' => 'سعر ثابت، وقرعة على الأكواد. حظك يحدد أي كود تربح.',
    'active' => 'codes',
])

<section class="max-w-5xl mx-auto px-4 pb-12" dir="rtl">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-xl font-extrabold text-gray-900">كيف تشتغل؟</h2>
            <ul class="mt-3 space-y-2 text-sm text-gray-700">
                <li>- تدفع <b>سعر ثابت</b> للقرعة.</li>
                <li>- النظام يختار لك كود بشكل عشوائي (مع احتمالات حسب إعدادات الإدارة).</li>
                <li>- بعد تأكيد الدفع يتم تسليم الكود لك داخل حسابك.</li>
            </ul>

            <div class="mt-5 rounded-2xl bg-gray-50 border border-gray-100 p-4">
                <div class="text-xs text-gray-500">السعر</div>
                <div class="mt-1 text-3xl font-extrabold text-green-600">ر.س {{ number_format($price, 2) }}</div>
                <div class="mt-2 text-xs text-gray-500">* السعر ثابت لكل قرعة.</div>
            </div>

            <div class="mt-5">
                <a href="{{ route('website.diamonds.manual_payment.create', $product) }}"
                   class="w-full inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-yellow-400 hover:text-black transition">
                    سوي قرعة الآن
                </a>
                <div class="mt-2 text-xs text-gray-500 text-center">ستلزمك عملية دفع يدوي (رفع إيصال) ثم يتم التسليم بعد الموافقة.</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-xl font-extrabold text-gray-900">عينات من الأكواد</h2>
            <p class="mt-1 text-sm text-gray-600">صور توضيحية للأكواد الموجودة داخل القرعة.</p>

            <div class="mt-4 grid grid-cols-3 sm:grid-cols-4 gap-3">
                @forelse($samples as $s)
                    <div class="rounded-xl border border-gray-100 bg-gray-50 overflow-hidden">
                        <img src="{{ asset('storage/' . ltrim($s->image_path, '/')) }}"
                             alt="code"
                             class="w-full h-24 object-cover"
                             loading="lazy" decoding="async"
                             onerror="this.onerror=null;this.src='{{ asset('img/قريبا.jpg') }}';">
                        @if(!empty($s->luck_label))
                            <div class="px-2 py-1 text-[11px] text-gray-600 font-bold text-center">{{ $s->luck_label }}</div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-3 sm:col-span-4 text-center text-sm text-gray-500 py-6">
                        لا توجد صور مرفوعة للأكواد بعد.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

