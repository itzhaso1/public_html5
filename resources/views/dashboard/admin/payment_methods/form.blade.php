@extends('dashboard.layouts.master')

@section('pageTitle')
    {{ $pageTitle ?? 'طريقة دفع' }}
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')

    @php
        /** @var \App\Models\PaymentMethod $method */
        $isEdit = (bool) ($method?->exists ?? false);
        $details = (array) ($method?->details ?? []);
    @endphp

    <div id="kt_content_container" class="container-xxl">
        <div class="card card-xxl-stretch mb-8 shadow-sm border-0">
            <div class="card-header border-0">
                <div class="card-title m-0">
                    <h3 class="fw-bolder m-0">{{ $pageTitle ?? ($isEdit ? 'تعديل طريقة دفع' : 'إضافة طريقة دفع') }}</h3>
                </div>
            </div>

            <div class="card-body py-4">
                <form method="POST"
                      action="{{ $isEdit ? route('admin.payment_methods.update', $method) : route('admin.payment_methods.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="row g-6">
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">Key (معرف الطريقة)</label>
                            <input type="text"
                                   name="key"
                                   value="{{ old('key', $method->key) }}"
                                   class="form-control @error('key') is-invalid @enderror"
                                   placeholder="مثال: sa_bank / jo_click / binance_trc20"
                                   {{ $isEdit ? 'readonly' : '' }}>
                            @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">يفضل استخدام حروف صغيرة و underscore فقط.</div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">العنوان</label>
                            <input type="text"
                                   name="title"
                                   value="{{ old('title', $method->title) }}"
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="مثال: السعودية - تحويل بنكي">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-lg-3">
                            <label class="form-label fw-bolder">الترتيب</label>
                            <input type="number"
                                   min="0"
                                   name="sort_order"
                                   value="{{ old('sort_order', (int) ($method->sort_order ?? 0)) }}"
                                   class="form-control @error('sort_order') is-invalid @enderror">
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-lg-3 d-flex align-items-end">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1"
                                       {{ old('enabled', (bool) ($method->enabled ?? true)) ? 'checked' : '' }}>
                                <span class="form-check-label fw-bolder">مفعلة</span>
                            </label>
                        </div>

                        <div class="col-12 col-lg-6 d-flex align-items-end">
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="allowed_for_charge" value="1"
                                       {{ old('allowed_for_charge', (bool) ($method->allowed_for_charge ?? true)) ? 'checked' : '' }}>
                                <span class="form-check-label fw-bolder">تظهر في الشحن (Charge)</span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-8">

                    <div class="row g-6">
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">اسم البنك</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $details['bank_name'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">اسم الحساب</label>
                            <input type="text" name="account_name" value="{{ old('account_name', $details['account_name'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">رقم الحساب</label>
                            <input type="text" name="account_number" value="{{ old('account_number', $details['account_number'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">IBAN</label>
                            <input type="text" name="iban" value="{{ old('iban', $details['iban'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">Click ID (اختياري)</label>
                            <input type="text" name="click_id" value="{{ old('click_id', $details['click_id'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">Network (اختياري)</label>
                            <input type="text" name="network" value="{{ old('network', $details['network'] ?? '') }}" class="form-control" placeholder="TRC20">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">Address (اختياري)</label>
                            <input type="text" name="address" value="{{ old('address', $details['address'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label fw-bolder">Link (اختياري)</label>
                            <input type="url" name="link" value="{{ old('link', $details['link'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bolder">ملاحظة (اختياري)</label>
                            <textarea name="note" rows="3" class="form-control">{{ old('note', $details['note'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-8">
                        <a href="{{ route('admin.payment_methods.index') }}" class="btn btn-light">رجوع</a>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'حفظ التعديل' : 'إضافة' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

