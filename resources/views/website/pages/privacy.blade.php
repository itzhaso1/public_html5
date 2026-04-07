@extends('website.layouts.common.website')

@section('pageTitle')
{{ $pageTitle }}
@endsection

@section('content')
<section class="max-w-4xl mx-auto px-4 py-8" dir="rtl">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 sm:p-7">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-4">{{ $pageTitle }}</h1>

        <ul class="list-disc pr-5 space-y-2 text-gray-800 leading-7">
            <li>يتم جمع بيانات مثل الاسم والبريد الإلكتروني ومعلومات الدفع.</li>
            <li>تستخدم البيانات فقط لإتمام الطلب.</li>
            <li>لا يتم مشاركة البيانات إلا مع بوابات الدفع عند الحاجة.</li>
            <li>يتم الحفاظ على أمان المعلومات.</li>
        </ul>

        <p class="text-sm text-gray-700 mt-5 mb-1">
            اسم الموقع: متجر الممالك
        </p>
        <p class="text-sm text-gray-700">
            البريد الإلكتروني: king2game.com@gmail.com
        </p>
    </div>
</section>
@endsection