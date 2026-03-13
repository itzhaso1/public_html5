@extends('dashboard.layouts.login')

@section('css')
<style>
    /* 🌈 خلفية متدرجة أنيقة */
    body {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        font-family: 'Tajawal', sans-serif;
        direction: rtl;
    }

    /* 🪶 صندوق تسجيل الدخول */
    .login-box {
        background: #fff;
        border-radius: 16px;
        padding: 40px 35px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .login-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    /* 🧭 العنوان */
    .login-title {
        font-weight: 800;
        color: #2a5298;
        letter-spacing: 0.5px;
    }

    /* ✨ الحقول */
    .form-control {
        border-radius: 12px;
        border: 1px solid #d1d5db;
        padding: 12px;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #2a5298;
        box-shadow: 0 0 0 0.25rem rgba(42, 82, 152, 0.25);
    }

    /* 🔘 زر الدخول */
    .btn-primary {
        background: linear-gradient(90deg, #2a5298, #1e3c72);
        border: none;
        border-radius: 12px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: scale(1.03);
        background: linear-gradient(90deg, #1e3c72, #2a5298);
    }

    /* 🌙 رابط نسيت كلمة المرور */
    .link-primary {
        color: #2a5298 !important;
        transition: color 0.2s ease;
    }

    .link-primary:hover {
        color: #1e3c72 !important;
        text-decoration: underline;
    }

    /* 📱 تحسين العرض على الجوال */
    @media (max-width: 768px) {
        .login-box {
            padding: 30px 25px;
        }
        .login-title {
            font-size: 1.4rem;
        }
        .form-control {
            font-size: 14px;
        }
    }
</style>
@endsection

@section('pageTitle')
    تسجيل دخول مدير فرع
@endsection

@section('content')
<!--begin::Content-->
<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
    <!--begin::Logo-->
    <a href="#" class="mb-10 text-center">
        <img alt="{{ $settings?->name }}" src="{{ $logo ?? asset('public/dashboard/assets/media/logos/logo-demo13-compact.svg') }}" class="h-80px" />
    </a>
    <!--end::Logo-->

    <!--begin::Wrapper-->
    <div class="login-box w-lg-500px mx-auto text-center animate__animated animate__fadeInUp">
        <!--begin::Form-->
        @include('dashboard.layouts.common._partial.messages')
        <form class="form w-100" novalidate method="POST" id="kt_sign_in_form" action="{{ route('manager.post.login') }}">
            @csrf

            <h2 class="login-title mb-8">تسجيل دخول مدير فرع</h2>

            <!-- Email -->
            <div class="fv-row mb-4 text-start">
                <label class="form-label fs-6 fw-bold text-dark mb-1">البريد الإلكتروني</label>
                <input class="form-control form-control-lg" type="email" name="email" placeholder="example@email.com" autocomplete="off" />
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="fv-row mb-5 text-start">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-bold text-dark fs-6 mb-0">كلمة المرور</label>
                    <a href="#" class="link-primary small">نسيت كلمة المرور؟</a>
                </div>
                <input class="form-control form-control-lg" type="password" name="password" placeholder="••••••••" autocomplete="off" />
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-3">
                <span class="indicator-label">تسجيل الدخول</span>
                <span class="indicator-progress">الرجاء الانتظار...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Wrapper-->
</div>
<!--end::Content-->
@endsection

@section('js')
<script>
    $(document).ready(function() {
        @if(session('toastr'))
            var toastrOptions = session('toastr');
            toastr[toastrOptions.type](toastrOptions.message);
        @endif
    });
</script>
@endsection
