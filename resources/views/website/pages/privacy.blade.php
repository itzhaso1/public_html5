@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="py-10 px-4 sm:px-6" dir="rtl">
    <div class="max-w-5xl mx-auto">
        <div class="rounded-3xl border border-slate-200 bg-white shadow-[0_20px_55px_rgba(2,6,23,0.08)] overflow-hidden">
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-6 sm:px-8 py-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white">{{ $pageTitle }}</h1>
                <p class="text-slate-300 text-sm mt-2">متجر الممالك</p>
            </div>

            <div class="px-6 sm:px-8 py-6 sm:py-8">
                <ul class="space-y-3 text-slate-700 leading-8 text-[15px] sm:text-base">
                    <li class="flex items-start gap-2"><span class="text-amber-500 font-bold">•</span><span>يتم جمع بيانات مثل الاسم والبريد الإلكتروني ومعلومات الدفع.</span></li>
                    <li class="flex items-start gap-2"><span class="text-amber-500 font-bold">•</span><span>تستخدم البيانات فقط لإتمام الطلب.</span></li>
                    <li class="flex items-start gap-2"><span class="text-amber-500 font-bold">•</span><span>لا يتم مشاركة البيانات إلا مع بوابات الدفع عند الحاجة.</span></li>
                    <li class="flex items-start gap-2"><span class="text-amber-500 font-bold">•</span><span>يتم الحفاظ على أمان المعلومات.</span></li>
                </ul>

                <div class="mt-6 border-t border-slate-200 pt-4 text-sm text-slate-600">
                    <p>اسم الموقع: <span class="font-bold text-slate-800">متجر الممالك</span></p>
                    <p class="mt-1">البريد الإلكتروني: <span class="font-bold text-slate-800">king2game.com@gmail.com</span></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection