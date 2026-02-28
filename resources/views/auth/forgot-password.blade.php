@extends('website.layouts.common.website')

@section('pageTitle')
نسيت كلمة السر
@endsection

@section('content')
<section class="max-w-md mx-auto px-4 py-10" dir="rtl">
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-extrabold text-gray-900 text-center">نسيت كلمة السر؟</h1>
        <p class="mt-2 text-sm text-gray-600 text-center">
            اكتب بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور.
        </p>

        @if (session('status'))
            <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                       required autofocus autocomplete="email">
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                إرسال رابط إعادة التعيين
            </button>

            <div class="text-center text-sm text-gray-600">
                <a href="{{ route('auth.login') }}" class="font-extrabold text-blue-700 hover:underline">
                    الرجوع لتسجيل الدخول
                </a>
            </div>
        </form>
    </div>
</section>
@endsection

