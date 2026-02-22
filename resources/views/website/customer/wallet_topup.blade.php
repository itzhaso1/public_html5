@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'إيداع نقاط' }}
@endsection

@section('content')
@php
    $ppSar = (float) ($pointPrices['sar'] ?? 3.75);
    $ppUsd = (float) ($pointPrices['usd'] ?? 1.0);
@endphp

<section class="max-w-3xl mx-auto px-4 py-8" dir="rtl">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">إيداع نقاط</h1>
            <p class="text-sm text-gray-600 mt-1">حدد عدد النقاط ثم ارفع إيصال التحويل.</p>
        </div>
        <a href="{{ route('customer.wallet.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
            رجوع للمحفظة
        </a>
    </div>

    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
            <div class="font-extrabold text-gray-900 mb-1">سعر النقطة</div>
            <div>بالريال: <span class="font-extrabold text-green-700">ر.س {{ number_format($ppSar, 2) }}</span></div>
            <div>بالدولار: <span class="font-extrabold text-blue-700">$ {{ number_format($ppUsd, 2) }}</span></div>
        </div>

        <form method="POST" action="{{ route('customer.wallet.topup.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-extrabold mb-2">عدد النقاط</label>
                <input id="pointsInput" type="number" name="points" min="1" step="1"
                       value="{{ old('points', 100) }}"
                       class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/20"
                       required>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">الإجمالي (SAR)</div>
                    <div class="mt-1 text-xl font-extrabold text-green-700" id="totalSar">ر.س 0.00</div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="text-xs text-gray-500">الإجمالي (USD)</div>
                    <div class="mt-1 text-xl font-extrabold text-blue-700" id="totalUsd">$ 0.00</div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-extrabold mb-2">إيصال التحويل (صورة أو PDF)</label>
                <input type="file" name="receipt" accept="image/*,.pdf"
                       class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"
                       required>
                <div class="text-xs text-gray-500 mt-1">الحد الأقصى 10MB.</div>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                إرسال طلب الإيداع
            </button>
        </form>
    </div>
</section>
@endsection

@push('js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const pointsInput = document.getElementById('pointsInput');
    const totalSar = document.getElementById('totalSar');
    const totalUsd = document.getElementById('totalUsd');
    const ppSar = {{ json_encode((float) $ppSar) }};
    const ppUsd = {{ json_encode((float) $ppUsd) }};

    const fmt = (n) => (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
    const update = () => {
      const p = Math.max(0, parseInt(pointsInput.value || '0', 10) || 0);
      totalSar.textContent = 'ر.س ' + fmt(p * ppSar);
      totalUsd.textContent = '$ ' + fmt(p * ppUsd);
    };

    pointsInput.addEventListener('input', update);
    update();
  });
</script>
@endpush

