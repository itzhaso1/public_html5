<!DOCTYPE html>
@if (app()->getLocale() == 'ar')
    <html direction="rtl" dir="rtl" style="direction: rtl">
@else
    <html direction="ltr" dir="ltr" style="direction: ltr">
@endif
<!--begin::Head-->

<head>
    <base href="">
    <title>{{ ($settings ?? null)?->name }} | @yield('pageTitle')</title>
    <meta name="description" content="{{ ($settings ?? null)?->description }}" />
    <meta name="keywords" content="{{ ($settings ?? null)?->description }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ ($settings ?? null)?->description }}" />
    <meta property="og:url" content="{{ ($settings ?? null)?->name }}" />
    <meta property="og:site_name" content="{{ ($settings ?? null)?->name }}" />
    <link rel="canonical" href="#" />
    <link rel="shortcut icon" href="{{ $favicon ?? asset('dashboard/assets/media/logos/logo-demo13-compact.svg') }}" />
    <!--begin::Fonts-->
    <link href="https://fonts.googleapis.com/css?family=Cairo:300,400&amp;subset=arabic,latin-ext" rel="stylesheet">
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    @if (app()->getLocale() == 'ar')
        <link href="{{ asset('dashboard/assets/plugins/custom/prismjs/prismjs.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('dashboard/assets/plugins/global/plugins.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('dashboard/assets/css/style.bundle.rtl.css') }}" rel="stylesheet" type="text/css" />
    @else
        <link href="{{ asset('dashboard/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('dashboard/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @endif
    <style>
        html,
        body,
        a,
        i,
        p,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        .btn,
        .alert {
            font-family: 'Cairo', sans-serif;
        }
    </style>
    @yield('css')
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="bg-body">
    <!--begin::Main-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Authentication - Sign-in -->
        <div class="d-flex flex-column flex-column-fluid p-10 pb-lg-20">

