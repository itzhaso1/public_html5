@extends('website.layouts.common.website')

@section('pageTitle')
إعادة تعيين كلمة السر
@endsection

@section('content')
<section class="max-w-md mx-auto px-4 py-10" dir="rtl">
    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm p-6 sm:p-8">
        <h1 class="text-2xl font-extrabold text-gray-900 text-center">إعادة تعيين كلمة السر</h1>
        <p class="mt-2 text-sm text-gray-600 text-center">اختر كلمة سر جديدة لحسابك.</p>

        @if($errors->any())
            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                       required autocomplete="email">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">كلمة السر الجديدة</label>
                <input type="password" name="password"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                       required autocomplete="new-password">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-800 mb-1">تأكيد كلمة السر</label>
                <input type="password" name="password_confirmation"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-yellow-400/60"
                       required autocomplete="new-password">
            </div>

            <button type="submit"
                    class="w-full rounded-xl bg-black px-5 py-3 text-sm font-extrabold text-white hover:bg-gray-800 transition">
                حفظ كلمة السر الجديدة
            </button>
        </form>
    </div>
</section>
@endsection

