@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle ?? 'طلب تاجر' }}
@endsection

@section('content')
<section class="max-w-4xl mx-auto px-4 py-8" dir="rtl">
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm p-6 sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">تقديم طلب لتصبح تاجر</h1>
                <p class="mt-1 text-sm text-gray-600">التجار يحصلون على سعر أفضل (خصوصاً عند اختيار الدولار).</p>
            </div>
            <a href="{{ route('website.diamonds.charge') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-bold hover:bg-gray-50 transition">
                رجوع لقسم الشحن
            </a>
        </div>

        @if($errors->any())
            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(!empty($existingRequest))
            <div class="mt-4 rounded-2xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-900">
                لديك طلب قيد المراجعة بالفعل. سيتم إشعارك عند الموافقة.
            </div>
        @endif

        <form method="POST" action="{{ route('website.merchant.apply.store') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">الاسم</label>
                <input type="text" name="name"
                       value="{{ old('name', auth()->user()?->name) }}"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                       required>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">رقم واتساب</label>
                <input type="tel" name="phone"
                       value="{{ old('phone', preg_replace('/\D+/', '', (string) (auth()->user()?->phone ?? auth()->user()?->profile?->phone ?? ''))) }}"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-mono outline-none focus:ring-2 focus:ring-yellow-400/60"
                       placeholder="مثال: 9665XXXXXXXX"
                       required>
                <div class="mt-1 text-xs text-gray-500">اكتب الرقم الدولي بدون + وبدون مسافات.</div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">ملاحظة (اختياري)</label>
                <textarea name="note" rows="4"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                          placeholder="مثال: أنا تاجر وأحتاج سعر أفضل لأن عندي طلبات يومية...">{{ old('note') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition"
                    {{ !empty($existingRequest) ? 'disabled' : '' }}>
                إرسال الطلب
            </button>
        </form>
    </div>
</section>
@endsection

