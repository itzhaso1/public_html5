@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'تعديل المستخدم' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-xl-8">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3">{{ $pageTitle ?? 'تعديل المستخدم' }}</span>
                    <span class="text-muted mt-1 fw-bold fs-7">تعديل بيانات المستخدم أو حذف رقم الهاتف.</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('admin.user.index') }}" class="btn btn-sm btn-light">رجوع</a>
                </div>
            </div>

            <div class="card-body py-3">
                <form method="POST" action="{{ route('admin.user.update', $user) }}" class="row g-4">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label class="form-label fw-bold">الاسم</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">رقم الهاتف (users.phone)</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+9665XXXXXXXX">
                        <div class="form-text">اتركه فارغاً لحذف الرقم.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">رقم الهاتف (profile.phone)</label>
                        <input type="text" name="profile_phone" class="form-control"
                               value="{{ old('profile_phone', $user->profile?->phone) }}"
                               placeholder="+9665XXXXXXXX">
                        <div class="form-text">اتركه فارغاً لحذف الرقم من الملف الشخصي.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">رصيد النقاط</label>
                        <input type="number" min="0" step="1" name="wallet_points_balance" class="form-control"
                               value="{{ old('wallet_points_balance', (int) ($user->wallet_points_balance ?? 0)) }}">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_merchant"
                                   name="is_merchant" value="1"
                                   {{ old('is_merchant', (bool) ($user->is_merchant ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_merchant">حساب تاجر</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.user.index') }}" class="btn btn-light ms-2">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

