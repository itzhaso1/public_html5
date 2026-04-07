@extends('website.layouts.common.website')

@section('pageTitle')
الشروط والأحكام
@endsection

@section('content')
<section class="py-10 px-4 sm:px-6" dir="rtl">
    <div class="max-w-5xl mx-auto">
        <div class="rounded-3xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 sm:px-8 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900">الشروط والأحكام</h1>
                <p class="text-sm text-gray-500 mt-1">متجر الممالك</p>
            </div>

            <div class="px-6 sm:px-8 py-6 sm:py-8">
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">جميع المنتجات المعروضة هي منتجات رقمية.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">يمنع إعادة بيع المنتجات أو مشاركتها.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">يحق للموقع تعديل الأسعار والخدمات.</p>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 inline-block h-2.5 w-2.5 rounded-full bg-yellow-500"></span>
                        <p class="text-gray-800 leading-8">المستخدم مسؤول عن استخدام الموقع بشكل قانوني.</p>
                    </li>
                </ul>

                <div class="mt-6 border-t border-gray-200 pt-4 text-sm text-gray-700">
                    <p>اسم الموقع: <span class="font-semibold">متجر الممالك</span></p>
                    <p class="mt-1">البريد الإلكتروني: <span class="font-semibold">king2game.com@gmail.com</span></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
