@extends('website.layouts.common.website')

@section('pageTitle')
سياسة التسليم
@endsection

@section('content')
<section class="py-10 px-4 sm:px-6" dir="rtl">
    <div class="max-w-5xl mx-auto">
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 sm:px-8 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">سياسة التسليم</h1>
                <p class="text-sm text-gray-500 mt-1">متجر الممالك</p>
            </div>

            <div class="px-6 sm:px-8 py-6 sm:py-8">
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">يتم تسليم المنتج فور الدفع.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">التسليم عبر البريد الإلكتروني أو رابط داخل الموقع.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">في حال عدم الاستلام، يتم التواصل مع الدعم.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">للتواصل: king2game.com@gmail.com</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection
