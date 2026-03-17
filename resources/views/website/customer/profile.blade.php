@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'الملف الشخصي' }}
@endsection

@section('content')
<section class="max-w-4xl mx-auto px-4 py-8" dir="rtl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">الملف الشخصي</h1>
            <p class="text-sm text-gray-600 mt-1">تحديث البريد الإلكتروني وكلمة المرور.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="#admin-messages"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                رسائل الإدارة
            </a>
            <a href="{{ route('customer.purchases') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                مشترياتي
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">تحديث البريد الإلكتروني</h2>

            <form class="mt-4 space-y-3" method="POST" action="{{ route('customer.profile.update') }}">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                           required>
                    @error('email')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">رقم واتساب (لإشعارات الطلبات)</label>
                    <input type="tel" name="phone" value="{{ old('phone', preg_replace('/\\D+/', '', (string) ($user->phone ?? $user->profile?->phone ?? ''))) }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                           placeholder="مثال: 9665XXXXXXXX">
                    <div class="text-xs text-gray-500 mt-1">اكتب الرقم الدولي بدون + وبدون مسافات.</div>
                    @error('phone')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                    حفظ البريد
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h2 class="font-extrabold text-gray-900">تغيير كلمة المرور</h2>

            <form class="mt-4 space-y-3" method="POST" action="{{ route('customer.profile.password') }}">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">كلمة المرور الحالية</label>
                    <input type="password" name="current_password"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                           required>
                    @error('current_password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">كلمة المرور الجديدة</label>
                    <input type="password" name="password"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                           required>
                    @error('password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-1">تأكيد كلمة المرور</label>
                    <input type="password" name="password_confirmation"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                           required>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                    تحديث كلمة المرور
                </button>
            </form>
        </div>
    </div>

    <div id="admin-messages" class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="text-sm font-extrabold text-gray-900">رسائل الإدارة</div>
        <div class="mt-3 space-y-3">
            @forelse(($adminMessages ?? collect()) as $note)
                @php
                    $payload = (array) ($note->data ?? []);
                @endphp
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
                    <div class="font-extrabold text-gray-900">{{ $payload['title'] ?? 'رسالة' }}</div>
                    <div class="text-sm text-gray-700 mt-1 whitespace-pre-line">{{ $payload['message'] ?? '' }}</div>
                    <div class="text-[11px] text-gray-500 mt-2">{{ $note->created_at?->format('Y-m-d H:i') }}</div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-3 text-sm text-gray-600">
                    لا توجد رسائل إدارة حالياً.
                    @if(($notificationsReady ?? true) === false)
                        <div class="mt-1 text-xs text-red-600">تنبيه تقني: نظام الإشعارات غير جاهز في البيئة الحالية.</div>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-extrabold text-gray-900">اختر بلدك</h2>
                <p class="text-sm text-gray-600 mt-1">لتحديد العملة تلقائيًا (السعودية/الأردن/دولار لباقي الدول). يتم حفظ الاختيار على هذا الجهاز.</p>
            </div>
            <a href="#" data-open-country-picker
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                فتح نافذة الاختيار
            </a>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm cursor-pointer">
                <div class="font-bold text-gray-900">السعودية</div>
                <div class="text-xs text-gray-500 mt-1">العملة: ر.س</div>
                <input class="mt-2 accent-yellow-500" type="radio" name="country_pref" value="SA">
            </label>
            <label class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm cursor-pointer">
                <div class="font-bold text-gray-900">الأردن</div>
                <div class="text-xs text-gray-500 mt-1">العملة: د.أ</div>
                <input class="mt-2 accent-yellow-500" type="radio" name="country_pref" value="JO">
            </label>
            <label class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm cursor-pointer">
                <div class="font-bold text-gray-900">دول أخرى</div>
                <div class="text-xs text-gray-500 mt-1">العملة: $</div>
                <input class="mt-2 accent-yellow-500" type="radio" name="country_pref" value="US">
            </label>
        </div>

        <button id="saveCountryPrefBtn" type="button"
                class="mt-4 w-full sm:w-auto rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
            حفظ الاختيار
        </button>
    </div>
</section>
@endsection

@push('js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const CACHE_KEY = "user_country_code";
    const readCached = () => {
      try { return (JSON.parse(localStorage.getItem(CACHE_KEY) || "null") || {}).code || null; } catch (e) { return null; }
    };
    const writeCached = (code) => {
      try { localStorage.setItem(CACHE_KEY, JSON.stringify({ code, ts: Date.now() })); } catch (e) {}
    };

    const current = (readCached() || 'US').toUpperCase();
    const radio = document.querySelector(`input[name="country_pref"][value="${current}"]`)
      || document.querySelector(`input[name="country_pref"][value="US"]`);
    if (radio) radio.checked = true;

    const btn = document.getElementById('saveCountryPrefBtn');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const checked = document.querySelector('input[name="country_pref"]:checked');
      const code = checked ? String(checked.value || 'US').toUpperCase() : 'US';
      writeCached(code);
      window.location.reload();
    });
  });
</script>
@endpush

