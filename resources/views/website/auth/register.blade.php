@extends('website.layouts.common.website')
@push('css')
<style>
    .auth-bg {
        background: radial-gradient(1200px 500px at 80% 10%, rgba(250, 204, 21, 0.18), transparent 60%),
                    radial-gradient(900px 450px at 10% 30%, rgba(59, 130, 246, 0.10), transparent 55%),
                    linear-gradient(180deg, #0b0b0b, #111827);
    }
</style>
@endpush

@section('pageTitle')
{{$pageTitle}}
@endsection

@section('content')
<section class="auth-bg min-h-[calc(100vh-3.5rem)] flex items-center py-10" dir="rtl">
    <div class="max-w-6xl mx-auto px-4 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">
            <div class="hidden lg:flex flex-col justify-between rounded-3xl border border-white/10 bg-white/5 backdrop-blur p-8 text-white">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-bold">
                        <span>حساب واحد</span>
                        <span class="opacity-60">•</span>
                        <span>مشترياتك محفوظة</span>
                    </div>
                    <h2 class="mt-4 text-3xl font-extrabold leading-tight">
                        أنشئ حسابك لمتابعة الشحن واستلام الأكواد
                    </h2>
                    <p class="mt-2 text-sm text-white/80 leading-relaxed">
                        بعد التسجيل ستتمكن من متابعة حالة طلباتك ورؤية الأكواد المستلمة داخل “مشترياتي”.
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-3 text-sm">
                    <div class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="text-green-300 font-bold">✓</span> متابعة الطلبات
                    </div>
                    <div class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="text-yellow-300 font-bold">✓</span> تسليم الأكواد بعد الموافقة
                    </div>
                    <div class="flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 p-4">
                        <span class="text-blue-300 font-bold">✓</span> تعديل البريد وكلمة المرور
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                        <img src="{{ $logo ?: ($fallbackLogo ?? asset('dashboard/assets/media/logos/logo-default.svg')) }}"
                             onerror="this.onerror=null;this.src='{{ $fallbackLogo ?? asset('dashboard/assets/media/logos/logo-default.svg') }}';"
                             alt="logo" class="h-10 w-auto">
                    </a>
                </div>

                <h1 class="mt-4 text-center text-2xl font-extrabold text-gray-900">
                    {{ trans('site/site.register_new_account') }}
                </h1>
                <p class="mt-1 text-center text-sm text-gray-600">
                    أدخل بياناتك لإنشاء حساب جديد
                </p>

                @if($errors->any())
                    <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('auth.register.submit') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="active">

                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-800 mb-1">{{ trans('site/site.name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-800 mb-1">{{ trans('site/site.email') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold text-gray-800 mb-1">{{ trans('site/site.phone') }}</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-gray-800 mb-1">{{ trans('site/site.password') }}</label>
                        <input type="password" id="password" name="password" required
                               class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60">
                        <div class="text-xs text-gray-500 mt-1">6 أحرف على الأقل.</div>
                    </div>

                    <button class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-yellow-400 hover:text-black transition">
                        إنشاء حساب
                    </button>

                    <div class="text-center text-sm text-gray-600">
                        لديك حساب بالفعل؟
                        <a href="{{ route('auth.login') }}" class="font-extrabold text-blue-700 hover:underline">
                            تسجيل الدخول
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')

@endpush
