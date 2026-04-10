@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'إيداع نقاط' }}
@endsection

@section('content')
@php
    $ppSar = (float) ($pointPrices['sar'] ?? 3.75);
    $ppUsd = (float) ($pointPrices['usd'] ?? 1.0);
    $methods = (array) config('bank.methods', []);
    $enabledMethods = collect($methods)->filter(fn($m) => is_array($m) && ($m['enabled'] ?? false));
    $enabledMethods = $enabledMethods->filter(function ($m, $key) {
        if ($key !== 'binance_trc20') return true;
        $addr = trim((string) ($m['address'] ?? ''));
        $link = trim((string) ($m['link'] ?? ''));
        return $addr !== '' || $link !== '';
    });
    $methodKeys = $enabledMethods->keys()->values()->all();
    $selectedMethod = old('payment_method') ?: ($methodKeys[0] ?? null);
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

        <form id="walletTopupForm" method="POST" action="{{ route('customer.wallet.topup.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
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

            @if(count($methodKeys))
                <div>
                    <label class="block text-sm font-extrabold mb-2">طريقة الدفع</label>
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

                <div class="space-y-3">
                    @foreach($methodKeys as $key)
                        @php $m = $enabledMethods->get($key, []); @endphp
                        <div class="payment-card payment-{{ $key }} {{ $selectedMethod === $key ? '' : 'hidden' }} rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="font-extrabold text-gray-900">{{ $m['title'] ?? $key }}</div>
                                <span class="payment-badge text-[11px] font-extrabold text-blue-700 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-full">
                                    محدد
                                </span>
                            </div>

                            <div class="mt-3 divide-y divide-gray-100 text-sm text-gray-700">
                                @if($key === 'sa_bank')
                                    @if(!empty($m['bank_name']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">البنك</span>
                                            <span class="font-semibold select-all">{{ $m['bank_name'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['account_name']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">اسم الحساب</span>
                                            <span class="font-semibold select-all">{{ $m['account_name'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['account_number']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">رقم الحساب</span>
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['account_number'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['iban']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">IBAN</span>
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['iban'] }}</span>
                                        </div>
                                    @endif
                                @elseif($key === 'jo_click')
                                    @if(!empty($m['bank_name']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">البنك</span>
                                            <span class="font-semibold select-all">{{ $m['bank_name'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['account_name']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">الاسم</span>
                                            <span class="font-semibold select-all">{{ $m['account_name'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['click_id']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">Click ID</span>
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['click_id'] }}</span>
                                        </div>
                                    @endif
                                @elseif($key === 'binance_trc20')
                                    <div class="py-2 flex items-center justify-between gap-3">
                                        <span class="text-xs text-gray-500">Network</span>
                                        <span class="font-semibold select-all">{{ $m['network'] ?? 'TRC20' }}</span>
                                    </div>
                                    @if(!empty($m['address']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">Address</span>
                                            <span class="font-mono font-semibold text-[13px] select-all">{{ $m['address'] }}</span>
                                        </div>
                                    @endif
                                    @if(!empty($m['link']))
                                        <div class="py-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-gray-500">Link</span>
                                            <a class="text-blue-600 underline" href="{{ $m['link'] }}" target="_blank">فتح الرابط</a>
                                        </div>
                                    @endif
                                @endif

                                @if(!empty($m['note']))
                                    <div class="pt-3 text-xs text-gray-500">{{ $m['note'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div>
                <label class="block text-sm font-extrabold mb-2">إيصال التحويل (صورة أو PDF)</label>
                <input id="receiptInput" type="file" name="receipt" accept="image/*,.pdf"
                       class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm"
                       required>
                <div class="text-xs text-gray-500 mt-1">الحد الأقصى 10MB. (نصيحة: الصورة الصغيرة ترفع أسرع)</div>
            </div>

            <button id="topupSubmitBtn" type="submit"
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
<script>
  (function () {
    const form = document.getElementById('walletTopupForm');
    const btn = document.getElementById('topupSubmitBtn');
    const receiptInput = document.getElementById('receiptInput');
    if (!form || !btn || !receiptInput) return;

    const MAX_IMG_DIM = 1600;
    const JPEG_QUALITY = 0.75;
    let submitting = false;

    async function downscaleToJpeg(file) {
      if (!file || !file.type || !file.type.startsWith('image/')) return file;
      const isHeic = file.type === 'image/heic' || (file.name || '').toLowerCase().endsWith('.heic');
      if (!isHeic && (file.size || 0) < 900 * 1024) return file;

      const img = await new Promise((resolve, reject) => {
        const i = new Image();
        i.onload = () => resolve(i);
        i.onerror = reject;
        i.src = URL.createObjectURL(file);
      });

      const w = img.naturalWidth || img.width;
      const h = img.naturalHeight || img.height;
      const scale = Math.min(1, MAX_IMG_DIM / Math.max(w, h));
      const tw = Math.max(1, Math.round(w * scale));
      const th = Math.max(1, Math.round(h * scale));

      const canvas = document.createElement('canvas');
      canvas.width = tw;
      canvas.height = th;
      const ctx = canvas.getContext('2d', { alpha: false });
      ctx.drawImage(img, 0, 0, tw, th);

      const blob = await new Promise((resolve) => {
        canvas.toBlob((b) => resolve(b), 'image/jpeg', JPEG_QUALITY);
      });

      try { URL.revokeObjectURL(img.src); } catch (e) {}
      if (!blob) return file;
      const base = (file.name || 'receipt').replace(/\.(heic|png|webp|jpeg|jpg)$/i, '');
      return new File([blob], base + '.jpg', { type: 'image/jpeg' });
    }

    function setBusy(state, label) {
      btn.disabled = state;
      btn.classList.toggle('opacity-60', state);
      btn.classList.toggle('cursor-not-allowed', state);
      btn.textContent = state ? (label || 'جاري الإرسال...') : 'إرسال طلب الإيداع';
    }

    form.addEventListener('submit', async (e) => {
      if (submitting) return;
      submitting = true;

      // If the browser is navigating normally, still prevent multi-click.
      setBusy(true, 'جاري تجهيز الإيصال ثم الرفع...');

      try {
        const f = receiptInput.files && receiptInput.files[0] ? receiptInput.files[0] : null;
        if (f && f.type && f.type.startsWith('image/')) {
          e.preventDefault();

          let compressed = f;
          try { compressed = await downscaleToJpeg(f); } catch (err) { compressed = f; }

          if (compressed !== f) {
            const dt = new DataTransfer();
            dt.items.add(compressed);
            receiptInput.files = dt.files;
          }

          setBusy(true, 'جاري رفع الإيصال... لا تغلق الصفحة');
          form.submit();
          return;
        }

        setBusy(true, 'جاري رفع الإيصال... لا تغلق الصفحة');
      } catch (err) {
        submitting = false;
        setBusy(false);
      }
    });
  })();
</script>
@if(count($methodKeys ?? []))
<script>
  (function () {
    const radios = document.querySelectorAll('input[name="payment_method"]');
    if (!radios.length) return;
    const toggle = (key) => {
      document.querySelectorAll('.payment-card').forEach(el => {
        el.classList.add('hidden');
        const badge = el.querySelector('.payment-badge');
        if (badge) badge.classList.add('hidden');
      });
      const target = document.querySelector('.payment-' + key);
      if (target) {
        target.classList.remove('hidden');
        const badge = target.querySelector('.payment-badge');
        if (badge) badge.classList.remove('hidden');
      }
    };
    radios.forEach(r => r.addEventListener('change', () => toggle(r.value)));
    const checked = document.querySelector('input[name="payment_method"]:checked');
    if (checked) toggle(checked.value);
  })();
</script>
@endif
@endpush

