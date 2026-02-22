@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'الدفع بالنقاط' }}
@endsection

@section('content')
@php
    $product = $product ?? null;
    $isCodes = ($product?->service_type ?? null) === 'codes';
    $isGems = ($product?->service_type ?? null) === 'gems';
    $pointsPrice = (int) ($pointsPrice ?? ($product?->points_price ?? 0));
    $balance = (int) (($user?->wallet_points_balance ?? auth()->user()?->wallet_points_balance) ?? 0);
@endphp

@include('website.diamonds.partials.header', [
    'title' => 'الدفع بالنقاط',
    'subtitle' => 'سيتم إنشاء طلب جديد وسيخصم رصيدك من النقاط.',
    'active' => $isCodes ? 'codes' : 'charge',
])

<section class="max-w-3xl mx-auto px-4 pb-10" dir="rtl">
    @if(session('success'))
        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">المنتج</div>
                <div class="mt-1 text-lg font-extrabold text-gray-900">{{ $product?->name ?? '-' }}</div>
                <div class="mt-2 text-xs text-gray-600">
                    السعر بالنقاط: <span class="font-extrabold text-black">{{ number_format($pointsPrice) }}</span> نقطة
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 min-w-[240px]">
                <div class="text-xs text-gray-500">رصيدك</div>
                <div class="mt-1 text-2xl font-extrabold text-gray-900">{{ number_format($balance) }}</div>
                <div class="text-xs text-gray-500">نقطة</div>
            </div>
        </div>

        <form id="pointsPaymentForm" method="POST" action="{{ route('website.diamonds.points_payment.store', $product) }}" class="mt-5 space-y-4">
            @csrf

            @if($isGems)
                <div>
                    <label class="block text-sm font-extrabold mb-2">Player ID</label>
                    <div class="flex gap-2">
                        <input id="playerIdInput" type="text" name="player_id" value="{{ old('player_id') }}"
                               class="flex-1 w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/50"
                               placeholder="اكتب Player ID" required>
                        <button id="checkPlayerBtn" type="button"
                                class="whitespace-nowrap rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-extrabold hover:bg-gray-50 transition">
                            تحقق من الاسم
                        </button>
                    </div>
                    <div id="playerCheckResult" class="mt-2 text-xs"></div>
                    <div class="text-xs text-gray-500 mt-1">سيتم تنفيذ الشحن بعد المراجعة.</div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-extrabold mb-2">واتساب للتواصل (اختياري)</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="+9665XXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-extrabold mb-2">إيميل (اختياري)</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-black/10"
                           placeholder="name@example.com">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
                سيتم خصم <span class="font-extrabold">{{ number_format($pointsPrice) }}</span> نقطة من رصيدك عند الإرسال.
                @if($isCodes)
                    <div class="mt-1 text-xs text-gray-600">الكود سيتم تسليمه بعد موافقة الإدارة (نفس نظام الدفع اليدوي).</div>
                @endif
            </div>

            <button type="submit"
                    id="pointsSubmitBtn"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                تأكيد الدفع بالنقاط
            </button>
        </form>
    </div>
</section>
@endsection

@push('js')
@if($isGems)
<script>
  (function () {
    const form = document.getElementById('pointsPaymentForm');
    const btn = document.getElementById('checkPlayerBtn');
    const input = document.getElementById('playerIdInput');
    const out = document.getElementById('playerCheckResult');
    const submitBtn = document.getElementById('pointsSubmitBtn');
    if (!form || !btn || !input || !out || !submitBtn) return;

    const csrf = @json(csrf_token());
    const url = @json(route('website.diamonds.check_player'));

    let verifiedPlayerId = '';
    let isChecking = false;

    const setMsg = (html, cls) => {
      out.className = 'mt-2 text-xs ' + (cls || '');
      out.innerHTML = html;
    };

    const check = async () => {
      const playerId = (input.value || '').trim();
      if (playerId.length < 3) {
        setMsg('ضع Player ID صحيح.', 'text-red-600');
        verifiedPlayerId = '';
        return false;
      }

      if (verifiedPlayerId === playerId) {
        return true;
      }

      if (isChecking) return false;
      isChecking = true;
      btn.disabled = true;
      submitBtn.disabled = true;
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
          verifiedPlayerId = '';
          return false;
        }

        if (data.success === true) {
          const name = data.player_name ? String(data.player_name) : '—';
          const region = data.region ? String(data.region) : '—';
          const cached = data.cached ? ' (cached)' : '';
          setMsg(`✅ الاسم: <b>${name}</b> — Region: <b>${region}</b>${cached}`, 'text-green-700');
          verifiedPlayerId = playerId;
          return true;
        }

        const raw = data.msg ? String(data.msg) : 'فشل التحقق';
        const friendly = (raw === 'NOT_READY' || raw === 'DUPLICATE_TASK')
          ? 'الطلب قيد المعالجة، انتظر قليلًا ثم أعد المحاولة.'
          : raw;
        setMsg(`❌ ${friendly}`, 'text-red-600');
        verifiedPlayerId = '';
        return false;
      } catch (e) {
        setMsg('فشل الاتصال. حاول لاحقاً.', 'text-red-600');
        verifiedPlayerId = '';
        return false;
      } finally {
        isChecking = false;
        btn.disabled = false;
        submitBtn.disabled = false;
      }
    };

    btn.addEventListener('click', check);

    form.addEventListener('submit', async (e) => {
      const playerId = (input.value || '').trim();
      if (playerId.length >= 3 && verifiedPlayerId === playerId) return;

      e.preventDefault();
      const ok = await check();
      if (ok) {
        // avoid double-check: verifiedPlayerId is set
        form.submit();
      }
    });
  })();
</script>
@endif
@endpush

