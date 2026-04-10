@extends('dashboard.layouts.master')

@section('pageTitle')
    الاعدادات العامه
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')
    <div id="kt_content_container" class="container-xxl settings-page">
        <div class="mb-5 card card-xxl-stretch mb-xl-8">
            <!--begin::Header-->
            <div class="pt-5 border-0 card-header">
                <h3 class="card-title align-items-start flex-column">
                    <span class="mb-1 card-label fw-bolder fs-3">الاعدادات العامه</span>
                    <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover">
                        <a href="{{ route('admin.mainSettings.histories') }}" target="_blank" class="btn btn-sm btn-light btn-active-primary">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                            <span class="svg-icon svg-icon-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1" transform="rotate(-90 11.364 20.364)" fill="black" />
                                    <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            History
                        </a>
                    </div>
                </h3>
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="py-3 card-body">
                <!-- Start Content -->
                <form id="mainSettings" action="{{ route('admin.mainSettings.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <!-- Start General Settings -->
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">الاسم</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $setting?->name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">رقم الهاتف</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ old('phone', $setting?->phone) }}">
                        </div>
                    </div>
                    <div class="mt-2 form-group row">
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">البريد الالكترونى</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $setting?->email) }}">
                        </div>
                    </div>
                    <div class="mt-2 form-group row">
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">العنوان</label>
                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $setting?->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">الوصف</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $setting?->description) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-2 form-group row">
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">العمله</label>
                            <input type="text" class="form-control" id="currency" name="currency"
                                value="{{ old('currency', $setting?->currency) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="input-group-text text-dark">الاصدار</label>
                            <input class="form-control" id="version" name="version" value="{{old('version',$setting?->version)}}">
                            {{-- <small class="text-danger">كل عمله = 10 نقطه</small> --}}
                        </div>


                    </div>

                    <!-- Start Logo & Favicon & Banner -->
                    <div class="settings-card">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="p-3 mb-3 text-center border rounded">
                                    <label for="logo" class="form-label fw-bold">الشعار (Logo)</label>
                                    <input class="form-control" type="file" name="logo" id="logoInput"
                                        accept="image/*" >
                                    <img id="logoPreview" src="{{$logo}}" class="img-fluid" style="max-height: 60px;" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 mb-3 text-center border rounded">
                                    <label for="favicon" class="form-label fw-bold">favicon</label>
                                    <input class="form-control" type="file" name="favicon" id="faviconInput" accept="image/*">
                                    <img id="faviconPreview" src="{{$favicon}}" class="img-fluid" style="max-height: 60px;"/>
                                </div>
                            </div>
                        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="imageModalLabel">عرض الصورة</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="text-center modal-body">
                                        <img id="popupImage" src="" class="rounded img-fluid"
                                            style="max-width: 100%; max-height: 80vh;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Home Quick Sections (Homepage cards) -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">كروت الأقسام في الصفحة الرئيسية (تعديل الاسم والصورة)</h4>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="fw-bold mb-2">كرت الشحن</div>
                                    <label class="input-group-text text-dark">الاسم</label>
                                    <input type="text" class="form-control" name="home_quick_charge_title"
                                           value="{{ old('home_quick_charge_title', $setting?->home_quick_charge_title) }}"
                                           placeholder="شحن جواهر">
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'charge_enabled'))
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   id="charge_enabled" name="charge_enabled" value="1"
                                                   {{ old('charge_enabled', (bool)($setting?->charge_enabled ?? true)) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="charge_enabled">الخدمة متاحة حالياً</label>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                            <label class="form-label fw-bold">الصورة</label>
                                        <input class="form-control" type="file" name="home_quick_charge_image" accept="image/*">
                                        @if(!empty($homeQuickChargeImg))
                                            <img src="{{ $homeQuickChargeImg }}" class="img-fluid mt-2" style="max-height: 80px;">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="fw-bold mb-2">كرت الأكواد</div>
                                    <label class="input-group-text text-dark">الاسم</label>
                                    <input type="text" class="form-control" name="home_quick_codes_title"
                                           value="{{ old('home_quick_codes_title', $setting?->home_quick_codes_title) }}"
                                           placeholder="أكواد ملابس">
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'codes_enabled'))
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   id="codes_enabled" name="codes_enabled" value="1"
                                                   {{ old('codes_enabled', (bool)($setting?->codes_enabled ?? true)) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="codes_enabled">الخدمة متاحة حالياً</label>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <label class="form-label fw-bold">الصورة</label>
                                        <input class="form-control" type="file" name="home_quick_codes_image" accept="image/*">
                                        @if(!empty($homeQuickCodesImg))
                                            <img src="{{ $homeQuickCodesImg }}" class="img-fluid mt-2" style="max-height: 80px;">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="fw-bold mb-2">كرت استبدال الرصيد كاش</div>
                                    <label class="input-group-text text-dark">الاسم</label>
                                    <input type="text" class="form-control" name="home_quick_cash_exchange_title"
                                           value="{{ old('home_quick_cash_exchange_title', $setting?->home_quick_cash_exchange_title) }}"
                                           placeholder="استبدل رصيدك كاش">
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'cash_exchange_enabled'))
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   id="cash_exchange_enabled" name="cash_exchange_enabled" value="1"
                                                   {{ old('cash_exchange_enabled', (bool)($setting?->cash_exchange_enabled ?? true)) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="cash_exchange_enabled">الخدمة متاحة حالياً</label>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <label class="form-label fw-bold">الصورة</label>
                                        <input class="form-control" type="file" name="home_quick_cash_exchange_image" accept="image/*">
                                        @if(!empty($homeQuickCashExchangeImg))
                                            <img src="{{ $homeQuickCashExchangeImg }}" class="img-fluid mt-2" style="max-height: 80px;">
                                        @endif
                                        <div class="form-text">إذا لم ترفع صورة سيظهر رمز افتراضي.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="fw-bold mb-2">كرت تحويل الأموال</div>
                                    <label class="input-group-text text-dark">الاسم</label>
                                    <input type="text" class="form-control" name="home_quick_money_exchange_title"
                                           value="{{ old('home_quick_money_exchange_title', $setting?->home_quick_money_exchange_title) }}"
                                           placeholder="تحويل الأموال">
                                    @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'money_exchange_enabled'))
                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   id="money_exchange_enabled" name="money_exchange_enabled" value="1"
                                                   {{ old('money_exchange_enabled', (bool)($setting?->money_exchange_enabled ?? true)) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="money_exchange_enabled">الخدمة متاحة حالياً</label>
                                        </div>
                                    @endif
                                    <div class="mt-2">
                                        <label class="form-label fw-bold">الصورة</label>
                                        <input class="form-control" type="file" name="home_quick_money_exchange_image" accept="image/*">
                                        @if(!empty($homeQuickMoneyExchangeImg))
                                            <img src="{{ $homeQuickMoneyExchangeImg }}" class="img-fluid mt-2" style="max-height: 80px;">
                                        @endif
                                        <div class="form-text">إذا لم ترفع صورة سيظهر رمز افتراضي.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <div class="fw-bold mb-2">كرت حسابات فري فاير</div>
                                    <label class="input-group-text text-dark">الاسم</label>
                                    <input type="text" class="form-control" name="home_quick_freefire_title"
                                           value="{{ old('home_quick_freefire_title', $setting?->home_quick_freefire_title) }}"
                                           placeholder="حسابات فري فاير">
                                    <div class="mt-2">
                                        <label class="input-group-text text-dark">مكان الظهور ضمن الأقسام</label>
                                        @php
                                            $freefirePos = old('home_quick_freefire_position', (string) ($setting?->home_quick_freefire_position ?? 'end'));
                                        @endphp
                                        <select class="form-select" name="home_quick_freefire_position">
                                            <option value="start" {{ $freefirePos === 'start' ? 'selected' : '' }}>في البداية</option>
                                            <option value="end" {{ $freefirePos === 'end' ? 'selected' : '' }}>في النهاية</option>
                                        </select>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label fw-bold">الصورة</label>
                                        <input class="form-control" type="file" name="home_quick_freefire_image" accept="image/*">
                                        @if(!empty($homeQuickFreefireImg))
                                            <img src="{{ $homeQuickFreefireImg }}" class="img-fluid mt-2" style="max-height: 80px;">
                                        @endif
                                        <div class="form-text">إذا لم ترفع صورة سيظهر رمز افتراضي.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Public publish form settings -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">إعدادات صفحة نشر الحساب (publish-product)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'public_publish_min_gallery_images'))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark">الحد الأدنى لعدد صور المعرض</label>
                                    <input type="number"
                                           min="1"
                                           max="40"
                                           step="1"
                                           class="form-control"
                                           name="public_publish_min_gallery_images"
                                           value="{{ old('public_publish_min_gallery_images', (int) ($setting?->public_publish_min_gallery_images ?? 12)) }}">
                                    <div class="form-text">
                                        هذا العدد سيُطبق على الوضعين (الرفع المرتب + المتقدم) في الخطوة 6.
                                        إذا زاد العدد عن 12 ستظهر خانات إضافية بأسماء تلقائية.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات نشر الحساب شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Account image blur controls -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">تحكم تغبيش صور الحسابات</h4>
                        <div class="text-muted mb-3">
                            هذه الإعدادات تتحكم في التغبيش أثناء رفع صور الحسابات (الصورة الرئيسية + صور المعرض).
                        </div>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'account_name_blur_width'))
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">تفعيل تغبيش اسم الحساب</label>
                                    <select name="account_name_blur_enabled" class="form-select">
                                        <option value="1" {{ old('account_name_blur_enabled', (bool)($setting?->account_name_blur_enabled ?? true)) ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ !old('account_name_blur_enabled', (bool)($setting?->account_name_blur_enabled ?? true)) ? 'selected' : '' }}>معطل</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">وضع تغبيش الاسم</label>
                                    @php $nameModeOld = old('account_name_blur_mode', (string) ($setting?->account_name_blur_mode ?? 'fixed')); @endphp
                                    <select name="account_name_blur_mode" id="account_name_blur_mode" class="form-select">
                                        <option value="fixed" {{ $nameModeOld === 'fixed' ? 'selected' : '' }}>ثابت (Fixed)</option>
                                        <option value="adaptive" {{ $nameModeOld === 'adaptive' ? 'selected' : '' }}>نسبي (Adaptive)</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">عرض تغبيش اسم الحساب</label>
                                    <input type="number" min="1" step="1" class="form-control" name="account_name_blur_width"
                                           value="{{ old('account_name_blur_width', (int) ($setting?->account_name_blur_width ?? 350)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">ارتفاع تغبيش اسم الحساب</label>
                                    <input type="number" min="1" step="1" class="form-control" name="account_name_blur_height"
                                           value="{{ old('account_name_blur_height', (int) ($setting?->account_name_blur_height ?? 100)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">قوة تغبيش اسم الحساب</label>
                                    <input type="number" min="1" max="100" step="1" class="form-control" name="account_name_blur_strength"
                                           value="{{ old('account_name_blur_strength', (int) ($setting?->account_name_blur_strength ?? 35)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">إزاحة X (من اليمين)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_name_blur_x_offset_from_right"
                                           value="{{ old('account_name_blur_x_offset_from_right', (int) ($setting?->account_name_blur_x_offset_from_right ?? 420)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">موضع Y</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_name_blur_y"
                                           value="{{ old('account_name_blur_y', (int) ($setting?->account_name_blur_y ?? 40)) }}">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">تفعيل مربع تغبيش المنتصف</label>
                                    <select name="account_center_blur_enabled" class="form-select">
                                        <option value="1" {{ old('account_center_blur_enabled', (bool)($setting?->account_center_blur_enabled ?? false)) ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ !old('account_center_blur_enabled', (bool)($setting?->account_center_blur_enabled ?? false)) ? 'selected' : '' }}>معطل</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">عرض مربع المنتصف</label>
                                    <input type="number" min="1" step="1" class="form-control" name="account_center_blur_width"
                                           value="{{ old('account_center_blur_width', (int) ($setting?->account_center_blur_width ?? 140)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">ارتفاع مربع المنتصف</label>
                                    <input type="number" min="1" step="1" class="form-control" name="account_center_blur_height"
                                           value="{{ old('account_center_blur_height', (int) ($setting?->account_center_blur_height ?? 90)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">قوة تغبيش المنتصف</label>
                                    <input type="number" min="1" max="100" step="1" class="form-control" name="account_center_blur_strength"
                                           value="{{ old('account_center_blur_strength', (int) ($setting?->account_center_blur_strength ?? 35)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">إحداثي X (0 = وسط)</label>
                                    <input type="number" step="1" class="form-control" name="account_center_blur_x"
                                           value="{{ old('account_center_blur_x', (int) ($setting?->account_center_blur_x ?? 0)) }}">
                                    <div class="form-text">إذا وضعت 0 سيتم توسيط المربع تلقائياً.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">إزاحة X من اليمين (للصور الفرعية)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_center_blur_x_from_right" id="account_center_blur_x_from_right"
                                           value="{{ old('account_center_blur_x_from_right', (int) ($setting?->account_center_blur_x_from_right ?? 0)) }}">
                                    <div class="form-text">إذا أكبر من 0 سيتم تجاهل X وتثبيت المربع من يمين الصورة.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">إحداثي Y (0 = وسط)</label>
                                    <input type="number" step="1" class="form-control" name="account_center_blur_y"
                                           value="{{ old('account_center_blur_y', (int) ($setting?->account_center_blur_y ?? 0)) }}">
                                    <div class="form-text">إذا وضعت 0 سيتم توسيط المربع تلقائياً.</div>
                                </div>
                            </div>
                            <div class="form-text mt-3">
                                ملاحظة: يمكنك تكبير/تصغير العرض والارتفاع كما تريد من هنا مباشرة بدون تعديل كود.
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">إعدادات تغبيش الجزء العلوي</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">نمط الجزء العلوي</label>
                                    <select name="account_top_area_mode" id="account_top_area_mode" class="form-select">
                                        @php $topModeOld = old('account_top_area_mode', (string) ($setting?->account_top_area_mode ?? 'blur')); @endphp
                                        <option value="blur" {{ $topModeOld === 'blur' ? 'selected' : '' }}>Blur</option>
                                        <option value="crop" {{ $topModeOld === 'crop' ? 'selected' : '' }}>Crop</option>
                                        <option value="none" {{ $topModeOld === 'none' ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">ارتفاع الجزء العلوي (PX)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_top_area_size_px" id="account_top_area_size_px"
                                           value="{{ old('account_top_area_size_px', (int) ($setting?->account_top_area_size_px ?? 35)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">عرض الجزء العلوي (PX)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_top_area_width_px" id="account_top_area_width_px"
                                           value="{{ old('account_top_area_width_px', (int) ($setting?->account_top_area_width_px ?? 0)) }}">
                                    <div class="form-text">0 = عرض كامل الصورة.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">إزاحة X من اليمين (PX)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="account_top_area_x_from_right_px" id="account_top_area_x_from_right_px"
                                           value="{{ old('account_top_area_x_from_right_px', (int) ($setting?->account_top_area_x_from_right_px ?? 0)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">قوة تغبيش الجزء العلوي</label>
                                    <input type="number" min="1" max="100" step="1" class="form-control" name="account_top_area_blur_strength" id="account_top_area_blur_strength"
                                           value="{{ old('account_top_area_blur_strength', (int) ($setting?->account_top_area_blur_strength ?? 35)) }}">
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">معاينة مباشرة (Preview)</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark mb-2">ارفع صورة للمعاينة فقط</label>
                                    <input type="file" id="blurPreviewInput" class="form-control" accept="image/*">
                                    <div class="form-text">هذه المعاينة لا تحفظ في السيرفر، فقط تساعدك تضبط الإحداثيات.</div>
                                </div>
                            </div>
                            <div class="mt-3 p-2 border rounded bg-light" style="max-width: 720px;">
                                <div id="blurPreviewStage" style="position:relative; width:100%; aspect-ratio:16/9; background:#f1f3f5; overflow:hidden; border-radius:8px;">
                                    <img id="blurPreviewImage" src="" alt="preview" style="width:100%; height:100%; object-fit:contain; display:none;">
                                    <div id="ovTop" style="position:absolute; border:2px dashed #ef4444; background:rgba(239,68,68,0.18); display:none;"></div>
                                    <div id="ovName" style="position:absolute; border:2px dashed #3b82f6; background:rgba(59,130,246,0.18); display:none;"></div>
                                    <div id="ovCenter" style="position:absolute; border:2px dashed #10b981; background:rgba(16,185,129,0.18); display:none;"></div>
                                </div>
                                <div class="mt-2 text-xs text-muted">
                                    الأحمر = الجزء العلوي، الأزرق = تغبيش الاسم، الأخضر = مربع المنتصف.
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">إعدادات لوجو العلامة المائية (للصورة الرئيسية)</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">تفعيل اللوجو المائي</label>
                                    <select name="watermark_enabled" class="form-select">
                                        <option value="1" {{ old('watermark_enabled', (bool)($setting?->watermark_enabled ?? true)) ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ !old('watermark_enabled', (bool)($setting?->watermark_enabled ?? true)) ? 'selected' : '' }}>معطل</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">حجم اللوجو (%)</label>
                                    <input type="number" min="1" max="100" step="1" class="form-control" name="watermark_scale_percent"
                                           value="{{ old('watermark_scale_percent', (int) ($setting?->watermark_scale_percent ?? 20)) }}">
                                    <div class="form-text">نسبة من عرض الصورة.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">موضع Y (PX من الأعلى)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="watermark_y_offset"
                                           value="{{ old('watermark_y_offset', (int) ($setting?->watermark_y_offset ?? 0)) }}">
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">اللوجو الأول: X من اليمين (PX)</label>
                                    <input type="number" min="0" step="1" class="form-control" name="watermark_x_offset"
                                           value="{{ old('watermark_x_offset', (int) ($setting?->watermark_x_offset ?? 20)) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">تفعيل اللوجو الثاني</label>
                                    <select name="watermark_second_enabled" class="form-select">
                                        <option value="1" {{ old('watermark_second_enabled', (bool)($setting?->watermark_second_enabled ?? true)) ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ !old('watermark_second_enabled', (bool)($setting?->watermark_second_enabled ?? true)) ? 'selected' : '' }}>معطل</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">اللوجو الثاني: إزاحة X (PX)</label>
                                    <input type="number" step="1" class="form-control" name="watermark_second_x_offset"
                                           value="{{ old('watermark_second_x_offset', (int) ($setting?->watermark_second_x_offset ?? 40)) }}">
                                    <div class="form-text">قيمة موجبة = يمين، سالبة = يسار.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="input-group-text text-dark">اللوجو الثاني: إزاحة Y (PX)</label>
                                    <input type="number" step="1" class="form-control" name="watermark_second_y_offset"
                                           value="{{ old('watermark_second_y_offset', (int) ($setting?->watermark_second_y_offset ?? 0)) }}">
                                    <div class="form-text">قيمة موجبة = تحت، سالبة = فوق.</div>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                هذه الإعدادات تتحكم فقط باللوجو المضاف تلقائياً بعد معالجة الصورة الرئيسية.
                            </div>
                            @php
                                $multiEnabledOld = old('watermark_multi_enabled', (bool)($setting?->watermark_multi_enabled ?? false));
                            @endphp
                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <label class="input-group-text text-dark">تفعيل اللوجوهات المتعددة</label>
                                    <select name="watermark_multi_enabled" id="watermark_multi_enabled" class="form-select">
                                        <option value="1" {{ $multiEnabledOld ? 'selected' : '' }}>مفعل</option>
                                        <option value="0" {{ !$multiEnabledOld ? 'selected' : '' }}>معطل (افتراضي)</option>
                                    </select>
                                    <div class="form-text">عند التعطيل سيتم استخدام نظام اللوجو الأساسي فقط مثل قبل.</div>
                                </div>
                            </div>
                            @php
                                $watermarkItemsOld = old('wm', null);
                                if (!is_array($watermarkItemsOld)) {
                                    $watermarkItemsOld = collect($setting?->watermarks ?? [])
                                        ->map(function ($wm) {
                                            return [
                                                'id' => $wm->id,
                                                'enabled' => (int) ($wm->enabled ?? 1),
                                                'x_offset' => (int) ($wm->x_offset ?? 20),
                                                'y_offset' => (int) ($wm->y_offset ?? 0),
                                                'scale_percent' => (int) ($wm->scale_percent ?? 20),
                                                'sort_order' => (int) ($wm->sort_order ?? 0),
                                            ];
                                        })
                                        ->values()
                                        ->all();
                                }
                            @endphp
                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">لوجوهات متعددة (غير محدود)</h6>
                            <div class="form-text mb-3">
                                يمكنك إضافة أي عدد من اللوجوهات مع تحكم مستقل بالمكان والحجم لكل لوجو.
                            </div>
                            <div id="wmMultiWrap" style="{{ $multiEnabledOld ? '' : 'display:none;' }}">
                            <div id="wmList">
                                @foreach($watermarkItemsOld as $i => $wmRow)
                                    <div class="row g-3 align-items-end border rounded p-3 mb-2 wm-item" data-index="{{ $i }}">
                                        <input type="hidden" name="wm[{{ $i }}][id]" value="{{ (int)($wmRow['id'] ?? 0) }}">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label fw-bold">صورة اللوجو</label>
                                            <input type="file" class="form-control" name="wm[{{ $i }}][image]" accept="image/*">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold">تفعيل</label>
                                            <select class="form-select" name="wm[{{ $i }}][enabled]">
                                                <option value="1" {{ (int)($wmRow['enabled'] ?? 1) === 1 ? 'selected' : '' }}>مفعل</option>
                                                <option value="0" {{ (int)($wmRow['enabled'] ?? 1) === 0 ? 'selected' : '' }}>معطل</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold">X</label>
                                            <input type="number" class="form-control" name="wm[{{ $i }}][x_offset]" value="{{ (int)($wmRow['x_offset'] ?? 20) }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold">Y</label>
                                            <input type="number" class="form-control" name="wm[{{ $i }}][y_offset]" value="{{ (int)($wmRow['y_offset'] ?? 0) }}">
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label fw-bold">الحجم %</label>
                                            <input type="number" min="1" max="100" class="form-control" name="wm[{{ $i }}][scale_percent]" value="{{ (int)($wmRow['scale_percent'] ?? 20) }}">
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <label class="form-label fw-bold">ترتيب</label>
                                            <input type="number" class="form-control" name="wm[{{ $i }}][sort_order]" value="{{ (int)($wmRow['sort_order'] ?? 0) }}">
                                        </div>
                                        <div class="col-12 col-md-12 text-end">
                                            <button type="button" class="btn btn-sm btn-light-danger wm-remove-btn">حذف</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-primary" id="wmAddBtn">+ إضافة لوجو جديد</button>
                            </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات التغبيش من لوحة التحكم شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Home featured accounts -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">الحسابات المميزة في الصفحة الرئيسية (سلايدر)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'home_featured_product_ids'))
                            @php
                                $selectedFeatured = old('home_featured_product_ids', $selectedHomeFeaturedProductIds ?? []);
                                if (!is_array($selectedFeatured)) $selectedFeatured = [];
                                $selectedFeatured = array_map('intval', $selectedFeatured);
                            @endphp
                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <label class="input-group-text text-dark mb-2">بحث سريع</label>
                                    <input type="text"
                                           id="featuredSearchInput"
                                           class="form-control"
                                           placeholder="ابحث بالاسم أو رقم الحساب...">
                                    <div class="form-text mt-2">
                                        اضغط على البطاقة لاختيارها أو إلغاء اختيارها.
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-light-primary btn-sm" id="featuredSelectAllBtn">تحديد الكل (الظاهر)</button>
                                        <button type="button" class="btn btn-light-danger btn-sm" id="featuredClearAllBtn">إلغاء الكل</button>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="fw-bold">المحدد الآن</div>
                                            <span class="badge bg-primary" id="featuredSelectedCount">0</span>
                                        </div>
                                        <div id="featuredSelectedBadges" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-8">
                                    <label class="input-group-text text-dark mb-2">اختر الحسابات المميزة</label>
                                    <select id="home_featured_product_ids"
                                            name="home_featured_product_ids[]"
                                            class="d-none"
                                            multiple>
                                        @foreach(($homeFeaturedProducts ?? collect()) as $p)
                                            @php
                                                $pid = (int) $p->id;
                                                $pname = trim((string) ($p->name ?? "حساب #{$pid}"));
                                                $priceText = is_numeric($p->price ?? null) ? number_format((float) $p->price, 2) : '-';
                                            @endphp
                                            <option value="{{ $pid }}" {{ in_array($pid, $selectedFeatured, true) ? 'selected' : '' }}>
                                                #{{ $pid }} — {{ $pname }} — {{ $priceText }} ر.س
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="featuredCardsGrid" class="row g-2">
                                        @forelse(($homeFeaturedProducts ?? collect()) as $p)
                                            @php
                                                $pid = (int) $p->id;
                                                $pname = trim((string) ($p->name ?? "حساب #{$pid}"));
                                                $priceText = is_numeric($p->price ?? null) ? number_format((float) $p->price, 2) : '-';
                                                $isSelected = in_array($pid, $selectedFeatured, true);
                                            @endphp
                                            <div class="col-12 col-md-6 featured-card-wrap" data-featured-wrap>
                                                <button type="button"
                                                        class="featured-card {{ $isSelected ? 'is-selected' : '' }}"
                                                        data-featured-id="{{ $pid }}"
                                                        data-featured-name="{{ e($pname) }}"
                                                        data-featured-price="{{ $priceText }}"
                                                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                                        <div class="text-start">
                                                            <div class="featured-title">#{{ $pid }} — {{ $pname }}</div>
                                                            <div class="featured-subtitle">{{ $priceText }} ر.س</div>
                                                        </div>
                                                        <span class="featured-check">{{ $isSelected ? '✓' : '+' }}</span>
                                                    </div>
                                                </button>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-light text-center mb-0">
                                                    لا توجد حسابات منشورة متاحة للاختيار حالياً.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                    <div class="form-text mt-2">
                                        الحسابات المختارة هنا ستظهر في سلايدر "الحسابات المميزة" في الصفحة الرئيسية.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل هذا الخيار شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Home featured products (all types) -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">المنتجات المميزة أعلى الأقسام (جميع الأنواع)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'home_featured_product_ids_all'))
                            @php
                                $selectedFeaturedAll = old('home_featured_product_ids_all', $selectedHomeFeaturedAllProductIds ?? []);
                                if (!is_array($selectedFeaturedAll)) $selectedFeaturedAll = [];
                                $selectedFeaturedAll = array_map('intval', $selectedFeaturedAll);
                            @endphp
                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <label class="input-group-text text-dark mb-2">بحث سريع</label>
                                    <input type="text"
                                           id="featuredAllSearchInput"
                                           class="form-control"
                                           placeholder="ابحث بالاسم أو رقم المنتج...">
                                    <div class="form-text mt-2">
                                        اضغط على البطاقة لاختيارها أو إلغاء اختيارها.
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-light-primary btn-sm" id="featuredAllSelectAllBtn">تحديد الكل (الظاهر)</button>
                                        <button type="button" class="btn btn-light-danger btn-sm" id="featuredAllClearAllBtn">إلغاء الكل</button>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="fw-bold">المحدد الآن</div>
                                            <span class="badge bg-primary" id="featuredAllSelectedCount">0</span>
                                        </div>
                                        <div id="featuredAllSelectedBadges" class="d-flex flex-wrap gap-2"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-8">
                                    <label class="input-group-text text-dark mb-2">اختر المنتجات المميزة (كل الأنواع)</label>
                                    <select id="home_featured_product_ids_all"
                                            name="home_featured_product_ids_all[]"
                                            class="d-none"
                                            multiple>
                                        @foreach(($homeFeaturedAllProducts ?? collect()) as $p)
                                            @php
                                                $pid = (int) $p->id;
                                                $pname = trim((string) ($p->name ?? "منتج #{$pid}"));
                                                $priceText = is_numeric($p->price ?? null) ? number_format((float) $p->price, 2) : '-';
                                                $typeLabel = match((string) ($p->service_type ?? '')) {
                                                    'gems' => 'شحن',
                                                    'codes' => 'أكواد',
                                                    default => 'حسابات',
                                                };
                                            @endphp
                                            <option value="{{ $pid }}" {{ in_array($pid, $selectedFeaturedAll, true) ? 'selected' : '' }}>
                                                #{{ $pid }} — {{ $pname }} — {{ $typeLabel }} — {{ $priceText }} ر.س
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="featuredAllCardsGrid" class="row g-2">
                                        @forelse(($homeFeaturedAllProducts ?? collect()) as $p)
                                            @php
                                                $pid = (int) $p->id;
                                                $pname = trim((string) ($p->name ?? "منتج #{$pid}"));
                                                $priceText = is_numeric($p->price ?? null) ? number_format((float) $p->price, 2) : '-';
                                                $typeLabel = match((string) ($p->service_type ?? '')) {
                                                    'gems' => 'شحن',
                                                    'codes' => 'أكواد',
                                                    default => 'حسابات',
                                                };
                                                $isSelected = in_array($pid, $selectedFeaturedAll, true);
                                            @endphp
                                            <div class="col-12 col-md-6 featured-all-card-wrap" data-featured-all-wrap>
                                                <button type="button"
                                                        class="featured-card {{ $isSelected ? 'is-selected' : '' }}"
                                                        data-featured-all-id="{{ $pid }}"
                                                        data-featured-all-name="{{ e($pname) }}"
                                                        data-featured-all-price="{{ $priceText }}"
                                                        data-featured-all-type="{{ $typeLabel }}"
                                                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}">
                                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                                        <div class="text-start">
                                                            <div class="featured-title">#{{ $pid }} — {{ $pname }}</div>
                                                            <div class="featured-subtitle">{{ $typeLabel }} — {{ $priceText }} ر.س</div>
                                                        </div>
                                                        <span class="featured-check">{{ $isSelected ? '✓' : '+' }}</span>
                                                    </div>
                                                </button>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="alert alert-light text-center mb-0">
                                                    لا توجد منتجات منشورة متاحة للاختيار حالياً.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                    <div class="form-text mt-2">
                                        المنتجات المختارة هنا ستظهر أعلى الأقسام في الصفحة الرئيسية.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل هذا الخيار شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Merchant pricing -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">أسعار الصرف (حسابات الريس + عرض الدولار)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'custom_usd_to_sar_rate'))
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark">سعر الصرف اليدوي (1 USD = كم SAR)</label>
                                    <input type="number" step="0.0001" min="0" class="form-control"
                                           name="custom_usd_to_sar_rate"
                                           value="{{ old('custom_usd_to_sar_rate', $setting?->custom_usd_to_sar_rate) }}"
                                           placeholder="مثال: 3.9000">
                                    <div class="form-text">
                                        إذا تركته فارغًا أو 0 سيستمر النظام بأخذ السعر التلقائي. إذا وضعت قيمة (مثال 3.9)،
                                        سيتم اعتمادها في كل صفحات عرض الأسعار بالدولار (الرئيسية + تفاصيل المنتج + باقي الصفحات).
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'merchant_usd_rate'))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark">سعر الدولار للتاجر (تحويل SAR → USD)</label>
                                    <input type="number" step="0.000001" min="0" class="form-control"
                                           name="merchant_usd_rate"
                                           value="{{ old('merchant_usd_rate', $setting?->merchant_usd_rate) }}"
                                           placeholder="مثال: 0.240000">
                                    <div class="form-text">
                                        هذا الرقم هو معدل التحويل المستخدم لعرض الأسعار بالدولار داخل قسم شحن الجواهر للتجار فقط (بدون تغيير سعر الريال).
                                        مثال تقريبي: السعر العادي \(1 SAR ≈ 0.26 USD\). لجعل الدولار أرخص للتاجر ضع قيمة أقل (مثل 0.24).
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات التجار شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Wallet / Points pricing -->
                    <div class="settings-card">
                        <h4 class="mb-3 fw-bolder">نظام النقاط (المحفظة)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'point_price_sar'))
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark">سعر النقطة بالريال (SAR)</label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           name="point_price_sar"
                                           value="{{ old('point_price_sar', $setting?->point_price_sar ?? 3.75) }}"
                                           placeholder="مثال: 3.75">
                                    <div class="form-text">هذا السعر يستخدم لحساب قيمة الإيداع بالنقاط (عدد النقاط × سعر النقطة).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="input-group-text text-dark">سعر النقطة بالدولار (USD)</label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                           name="point_price_usd"
                                           value="{{ old('point_price_usd', $setting?->point_price_usd ?? 1.00) }}"
                                           placeholder="مثال: 1.00">
                                    <div class="form-text">للإظهار للمستخدم عند اختيار الدولار (ليس شرطاً أن يطابق سعر الصرف).</div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات النقاط شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- End Name & alert message -->
                    <hr>
                    <div class="form-row settings-sticky-submit">
                        <div class="text-center col-md-12">
                            <button type="submit" class="btn btn-success btn-lg">تحديث</button>
                        </div>
                    </div>
                    <!-- End Submit Form -->
                </form>
                <!-- End Content -->
            </div>
            <!--begin::Body-->
        </div>
    </div>
@endsection

@push('css')
<style>
    .settings-card {
        padding: 1rem;
        margin-top: 1rem;
        background: #fff;
        border-radius: .75rem;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    }
    @media (min-width: 768px) {
        .settings-card {
            padding: 1.5rem;
            margin-top: 1.25rem;
        }
    }
    .settings-sticky-submit {
        position: sticky;
        bottom: .75rem;
        z-index: 5;
        background: rgba(255, 255, 255, .95);
        padding: .75rem;
        border-radius: .75rem;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    }
    .featured-card {
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: .75rem;
        padding: .75rem;
        background: #fff;
        text-align: inherit;
        transition: all .15s ease;
    }
    .featured-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 .25rem .75rem rgba(13, 110, 253, .12);
        transform: translateY(-1px);
    }
    .featured-card.is-selected {
        border-color: #198754;
        background: #f1fff6;
    }
    .featured-title {
        font-weight: 700;
        font-size: .93rem;
        line-height: 1.35;
    }
    .featured-subtitle {
        margin-top: .2rem;
        color: #6c757d;
        font-size: .85rem;
    }
    .featured-check {
        min-width: 28px;
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid #dee2e6;
        font-weight: 700;
        color: #6c757d;
        background: #f8f9fa;
    }
    .featured-card.is-selected .featured-check {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }
    .featured-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        background: #eef5ff;
        color: #0b5ed7;
        border: 1px solid #cfe2ff;
        padding: .25rem .6rem;
        font-size: .8rem;
        line-height: 1.2;
    }
    .featured-chip button {
        border: 0;
        background: transparent;
        color: inherit;
        font-weight: 700;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
</style>
@endpush

@push('js')
    <script>
        function previewImage(inputId, previewId) {
            let input = document.getElementById(inputId);
            let preview = document.getElementById(previewId);
            if (!input || !preview) return;

            input.addEventListener("change", function() {
                let file = input.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.src = "";
                    preview.style.display = "none";
                }
            });
        }
        function openImageModal(src, title) {
            if (src) {
                let popupImage = document.getElementById("popupImage");
                let modalTitle = document.getElementById("imageModalLabel");
                popupImage.src = src;
                modalTitle.innerText = title;
                let imageModal = new bootstrap.Modal(document.getElementById("imageModal"));
                imageModal.show();
            }
        }
        previewImage("logoInput", "logoPreview");
        previewImage("faviconInput", "faviconPreview");

        (function initFeaturedAccountsPicker() {
            const hiddenSelect = document.getElementById('home_featured_product_ids');
            const searchInput = document.getElementById('featuredSearchInput');
        const selectAllBtn = document.getElementById('featuredSelectAllBtn');
        const clearAllBtn = document.getElementById('featuredClearAllBtn');
            const cards = Array.from(document.querySelectorAll('[data-featured-id]'));
            const wraps = Array.from(document.querySelectorAll('[data-featured-wrap]'));
            const badgesWrap = document.getElementById('featuredSelectedBadges');
            const countEl = document.getElementById('featuredSelectedCount');
            if (!hiddenSelect || cards.length === 0) return;

            const optionById = (id) => {
                return Array.from(hiddenSelect.options).find((o) => String(o.value) === String(id)) || null;
            };
            const cardById = (id) => cards.find((c) => String(c.dataset.featuredId) === String(id)) || null;

            const setCardVisual = (card, selected) => {
                if (!card) return;
                card.classList.toggle('is-selected', !!selected);
                card.setAttribute('aria-pressed', selected ? 'true' : 'false');
                const check = card.querySelector('.featured-check');
                if (check) check.textContent = selected ? '✓' : '+';
            };

            const renderSelected = () => {
                if (!badgesWrap || !countEl) return;
                const selectedOpts = Array.from(hiddenSelect.options).filter((o) => o.selected);
                countEl.textContent = String(selectedOpts.length);
                badgesWrap.innerHTML = '';
                selectedOpts.forEach((opt) => {
                    const id = String(opt.value);
                    const card = cardById(id);
                    const name = card?.dataset.featuredName || opt.textContent || ('#' + id);
                    const chip = document.createElement('span');
                    chip.className = 'featured-chip';
                    chip.innerHTML = `<span>${name}</span><button type="button" data-remove-featured="${id}">×</button>`;
                    badgesWrap.appendChild(chip);
                });
            };

            cards.forEach((card) => {
                const id = card.dataset.featuredId;
                const option = optionById(id);
                const selected = !!option?.selected;
                setCardVisual(card, selected);
                card.addEventListener('click', function () {
                    const opt = optionById(id);
                    if (!opt) return;
                    opt.selected = !opt.selected;
                    setCardVisual(card, opt.selected);
                    renderSelected();
                });
            });

            if (badgesWrap) {
                badgesWrap.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove-featured]');
                    if (!btn) return;
                    const id = btn.getAttribute('data-remove-featured');
                    const opt = optionById(id);
                    if (!opt) return;
                    opt.selected = false;
                    setCardVisual(cardById(id), false);
                    renderSelected();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const q = String(this.value || '').trim().toLowerCase();
                    wraps.forEach((wrap) => {
                        const card = wrap.querySelector('[data-featured-id]');
                        if (!card) return;
                        const id = String(card.dataset.featuredId || '');
                        const name = String(card.dataset.featuredName || '').toLowerCase();
                        const text = `${id} ${name}`;
                        wrap.style.display = q === '' || text.includes(q) ? '' : 'none';
                    });
                });
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    wraps.forEach((wrap) => {
                        if (wrap.style.display === 'none') return;
                        const card = wrap.querySelector('[data-featured-id]');
                        if (!card) return;
                        const opt = optionById(card.dataset.featuredId);
                        if (!opt) return;
                        opt.selected = true;
                        setCardVisual(card, true);
                    });
                    renderSelected();
                });
            }
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    cards.forEach((card) => {
                        const opt = optionById(card.dataset.featuredId);
                        if (!opt) return;
                        opt.selected = false;
                        setCardVisual(card, false);
                    });
                    renderSelected();
                });
            }

            renderSelected();
        })();

        (function initFeaturedAllProductsPicker() {
            const hiddenSelect = document.getElementById('home_featured_product_ids_all');
            const searchInput = document.getElementById('featuredAllSearchInput');
            const selectAllBtn = document.getElementById('featuredAllSelectAllBtn');
            const clearAllBtn = document.getElementById('featuredAllClearAllBtn');
            const cards = Array.from(document.querySelectorAll('[data-featured-all-id]'));
            const wraps = Array.from(document.querySelectorAll('[data-featured-all-wrap]'));
            const badgesWrap = document.getElementById('featuredAllSelectedBadges');
            const countEl = document.getElementById('featuredAllSelectedCount');
            if (!hiddenSelect || cards.length === 0) return;

            const optionById = (id) => {
                return Array.from(hiddenSelect.options).find((o) => String(o.value) === String(id)) || null;
            };
            const cardById = (id) => cards.find((c) => String(c.dataset.featuredAllId) === String(id)) || null;

            const setCardVisual = (card, selected) => {
                if (!card) return;
                card.classList.toggle('is-selected', !!selected);
                card.setAttribute('aria-pressed', selected ? 'true' : 'false');
                const check = card.querySelector('.featured-check');
                if (check) check.textContent = selected ? '✓' : '+';
            };

            const renderSelected = () => {
                if (!badgesWrap || !countEl) return;
                const selectedOpts = Array.from(hiddenSelect.options).filter((o) => o.selected);
                countEl.textContent = String(selectedOpts.length);
                badgesWrap.innerHTML = '';
                selectedOpts.forEach((opt) => {
                    const id = String(opt.value);
                    const card = cardById(id);
                    const name = card?.dataset.featuredAllName || opt.textContent || ('#' + id);
                    const type = card?.dataset.featuredAllType || '';
                    const chip = document.createElement('span');
                    chip.className = 'featured-chip';
                    chip.innerHTML = `<span>${name}${type ? (' — ' + type) : ''}</span><button type="button" data-remove-featured-all="${id}">×</button>`;
                    badgesWrap.appendChild(chip);
                });
            };

            cards.forEach((card) => {
                const id = card.dataset.featuredAllId;
                const option = optionById(id);
                const selected = !!option?.selected;
                setCardVisual(card, selected);
                card.addEventListener('click', function () {
                    const opt = optionById(id);
                    if (!opt) return;
                    opt.selected = !opt.selected;
                    setCardVisual(card, opt.selected);
                    renderSelected();
                });
            });

            if (badgesWrap) {
                badgesWrap.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove-featured-all]');
                    if (!btn) return;
                    const id = btn.getAttribute('data-remove-featured-all');
                    const opt = optionById(id);
                    if (!opt) return;
                    opt.selected = false;
                    setCardVisual(cardById(id), false);
                    renderSelected();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const q = String(this.value || '').trim().toLowerCase();
                    wraps.forEach((wrap) => {
                        const card = wrap.querySelector('[data-featured-all-id]');
                        if (!card) return;
                        const id = String(card.dataset.featuredAllId || '');
                        const name = String(card.dataset.featuredAllName || '').toLowerCase();
                        const type = String(card.dataset.featuredAllType || '').toLowerCase();
                        const text = `${id} ${name} ${type}`;
                        wrap.style.display = q === '' || text.includes(q) ? '' : 'none';
                    });
                });
            }

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    wraps.forEach((wrap) => {
                        if (wrap.style.display === 'none') return;
                        const card = wrap.querySelector('[data-featured-all-id]');
                        if (!card) return;
                        const opt = optionById(card.dataset.featuredAllId);
                        if (!opt) return;
                        opt.selected = true;
                        setCardVisual(card, true);
                    });
                    renderSelected();
                });
            }
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    cards.forEach((card) => {
                        const opt = optionById(card.dataset.featuredAllId);
                        if (!opt) return;
                        opt.selected = false;
                        setCardVisual(card, false);
                    });
                    renderSelected();
                });
            }

            renderSelected();
        })();

        function updateLoyaltyPointsDisplay(value) {
            document.getElementById('loyalty_points_display').textContent = value;
            document.getElementById('loyalty_points').value = value;
        }

        const audioInput = document.getElementById('audioInput');
        if (audioInput) {
            audioInput.addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (file) {
                    const audioContainer = document.getElementById('audioContainer');
                    const audio = document.createElement('audio');
                    audio.setAttribute('controls', true);
                    audio.style.width = '100%';
                    audio.src = URL.createObjectURL(file);

                    audioContainer.innerHTML = '';
                    audioContainer.appendChild(audio);
                    audioContainer.style.display = 'block';
                }
            });
        }

        // ===== Live preview for blur boxes =====
        const previewInput = document.getElementById('blurPreviewInput');
        const previewImgEl = document.getElementById('blurPreviewImage');
        const previewStage = document.getElementById('blurPreviewStage');
        const ovTop = document.getElementById('ovTop');
        const ovName = document.getElementById('ovName');
        const ovCenter = document.getElementById('ovCenter');
        let naturalW = 0, naturalH = 0;

        function fieldEl(idOrName) {
            return document.getElementById(idOrName) || document.querySelector(`[name="${idOrName}"]`);
        }
        function n(id, def = 0) {
            const el = fieldEl(id);
            if (!el) return def;
            const x = parseFloat(String(el.value || '').trim());
            return Number.isFinite(x) ? x : def;
        }
        function show(el, yes) { if (el) el.style.display = yes ? 'block' : 'none'; }

        function renderPreviewOverlays() {
            if (!naturalW || !naturalH || !previewStage) return;
            const stageW = previewStage.clientWidth || 1;
            const stageH = previewStage.clientHeight || 1;
            const scale = Math.min(stageW / naturalW, stageH / naturalH);
            const drawW = naturalW * scale;
            const drawH = naturalH * scale;
            const offX = (stageW - drawW) / 2;
            const offY = (stageH - drawH) / 2;
            const px = (v) => (v * scale);

            // Top area
            const topMode = (document.getElementById('account_top_area_mode')?.value || 'blur').toLowerCase();
            const topH = Math.max(0, n('account_top_area_size_px', 35));
            const topWRaw = Math.max(0, n('account_top_area_width_px', 0));
            const topW = topWRaw > 0 ? Math.min(naturalW, topWRaw) : naturalW;
            const topXFromRight = Math.max(0, n('account_top_area_x_from_right_px', 0));
            const topX = Math.max(0, naturalW - topXFromRight - topW);
            show(ovTop, topMode !== 'none' && topH > 0);
            if (ovTop) {
                ovTop.style.left = (offX + px(topX)) + 'px';
                ovTop.style.top = (offY + 0) + 'px';
                ovTop.style.width = px(topW) + 'px';
                ovTop.style.height = px(Math.min(topH, naturalH)) + 'px';
            }

            // Name blur
            const nameEnabled = Number(n('account_name_blur_enabled', 1)) === 1;
            const nameMode = (document.getElementById('account_name_blur_mode')?.value || 'fixed').toLowerCase();
            const nameW = Math.max(1, n('account_name_blur_width', 350));
            const nameH = Math.max(1, n('account_name_blur_height', 100));
            let nameX = 0;
            let nameY = 0;
            if (nameMode === 'adaptive') {
                const offsetRatio = Math.max(0, Math.min(1, n('account_name_blur_x_offset_from_right_ratio', 0.39)));
                const yRatio = Math.max(0, Math.min(1, n('account_name_blur_y_ratio', 0.037)));
                const wRatio = Math.max(0.01, Math.min(1, n('account_name_blur_width_ratio', 0.325)));
                const hRatio = Math.max(0.01, Math.min(1, n('account_name_blur_height_ratio', 0.093)));
                const offsetPx = naturalW * offsetRatio;
                nameX = Math.max(0, naturalW - offsetPx);
                nameY = Math.max(0, naturalH * yRatio);
                // في adaptive، العرض/الارتفاع من النسب
                const adaptiveW = Math.max(1, naturalW * wRatio);
                const adaptiveH = Math.max(1, naturalH * hRatio);
                show(ovName, nameEnabled);
                if (ovName) {
                    ovName.style.left = (offX + px(nameX)) + 'px';
                    ovName.style.top = (offY + px(nameY)) + 'px';
                    ovName.style.width = px(Math.min(adaptiveW, Math.max(1, naturalW - nameX))) + 'px';
                    ovName.style.height = px(Math.min(adaptiveH, Math.max(1, naturalH - nameY))) + 'px';
                }
            } else {
                const nameXFromRight = Math.max(0, n('account_name_blur_x_offset_from_right', 420));
                nameY = Math.max(0, n('account_name_blur_y', 40));
                nameX = Math.max(0, naturalW - nameXFromRight);
                show(ovName, nameEnabled);
                if (ovName) {
                    ovName.style.left = (offX + px(nameX)) + 'px';
                    ovName.style.top = (offY + px(nameY)) + 'px';
                    ovName.style.width = px(Math.min(nameW, Math.max(1, naturalW - nameX))) + 'px';
                    ovName.style.height = px(Math.min(nameH, Math.max(1, naturalH - nameY))) + 'px';
                }
            }

            // Center blur
            const centerEnabled = Number(n('account_center_blur_enabled', 0)) === 1;
            const cW = Math.max(1, n('account_center_blur_width', 120));
            const cH = Math.max(1, n('account_center_blur_height', 120));
            const cXRaw = n('account_center_blur_x', 0);
            const cYRaw = n('account_center_blur_y', 0);
            const cXFromRight = Math.max(0, n('account_center_blur_x_from_right', 0));
            const cX = cXFromRight > 0
                ? Math.max(0, naturalW - cXFromRight - cW)
                : (cXRaw <= 0 ? Math.max(0, (naturalW - cW) / 2) : cXRaw);
            const cY = cYRaw <= 0 ? Math.max(0, (naturalH - cH) / 2) : cYRaw;
            show(ovCenter, centerEnabled);
            if (ovCenter) {
                ovCenter.style.left = (offX + px(cX)) + 'px';
                ovCenter.style.top = (offY + px(cY)) + 'px';
                ovCenter.style.width = px(Math.min(cW, Math.max(1, naturalW - cX))) + 'px';
                ovCenter.style.height = px(Math.min(cH, Math.max(1, naturalH - cY))) + 'px';
            }
        }

        if (previewInput && previewImgEl) {
            previewInput.addEventListener('change', function () {
                const f = this.files && this.files[0] ? this.files[0] : null;
                if (!f) return;
                const url = URL.createObjectURL(f);
                previewImgEl.onload = function () {
                    naturalW = this.naturalWidth || this.width;
                    naturalH = this.naturalHeight || this.height;
                    this.style.display = 'block';
                    renderPreviewOverlays();
                };
                previewImgEl.src = url;
            });
        }

        [
            'account_top_area_mode','account_top_area_size_px','account_top_area_width_px','account_top_area_x_from_right_px','account_top_area_blur_strength',
            'account_name_blur_enabled','account_name_blur_mode','account_name_blur_width','account_name_blur_height','account_name_blur_x_offset_from_right','account_name_blur_y','account_name_blur_strength',
            'account_name_blur_x_offset_from_right_ratio','account_name_blur_y_ratio','account_name_blur_width_ratio','account_name_blur_height_ratio',
            'account_center_blur_enabled','account_center_blur_width','account_center_blur_height','account_center_blur_x','account_center_blur_x_from_right','account_center_blur_y','account_center_blur_strength',
            'watermark_enabled','watermark_x_offset','watermark_y_offset','watermark_scale_percent','watermark_second_enabled','watermark_second_x_offset','watermark_second_y_offset'
        ].forEach((id) => {
            const el = fieldEl(id);
            if (!el) return;
            el.addEventListener('input', renderPreviewOverlays);
            el.addEventListener('change', renderPreviewOverlays);
        });
        window.addEventListener('resize', renderPreviewOverlays);

        // ===== Dynamic multi-watermark UI =====
        (function initWatermarkItemsUI() {
            const list = document.getElementById('wmList');
            const addBtn = document.getElementById('wmAddBtn');
            const multiToggle = document.getElementById('watermark_multi_enabled');
            const multiWrap = document.getElementById('wmMultiWrap');
            if (!list || !addBtn) return;

            function refreshMultiVisibility() {
                if (!multiToggle || !multiWrap) return;
                multiWrap.style.display = String(multiToggle.value) === '1' ? '' : 'none';
            }
            if (multiToggle) {
                multiToggle.addEventListener('change', refreshMultiVisibility);
                refreshMultiVisibility();
            }

            const nextIndex = () => {
                const items = list.querySelectorAll('.wm-item');
                let max = -1;
                items.forEach((el) => {
                    const idx = Number(el.getAttribute('data-index') || 0);
                    if (Number.isFinite(idx) && idx > max) max = idx;
                });
                return max + 1;
            };

            const rowHtml = (i) => `
                <div class="row g-3 align-items-end border rounded p-3 mb-2 wm-item" data-index="${i}">
                    <input type="hidden" name="wm[${i}][id]" value="0">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold">صورة اللوجو</label>
                        <input type="file" class="form-control" name="wm[${i}][image]" accept="image/*">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold">تفعيل</label>
                        <select class="form-select" name="wm[${i}][enabled]">
                            <option value="1" selected>مفعل</option>
                            <option value="0">معطل</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold">X</label>
                        <input type="number" class="form-control" name="wm[${i}][x_offset]" value="20">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold">Y</label>
                        <input type="number" class="form-control" name="wm[${i}][y_offset]" value="0">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold">الحجم %</label>
                        <input type="number" min="1" max="100" class="form-control" name="wm[${i}][scale_percent]" value="20">
                    </div>
                    <div class="col-6 col-md-1">
                        <label class="form-label fw-bold">ترتيب</label>
                        <input type="number" class="form-control" name="wm[${i}][sort_order]" value="${i}">
                    </div>
                    <div class="col-12 col-md-12 text-end">
                        <button type="button" class="btn btn-sm btn-light-danger wm-remove-btn">حذف</button>
                    </div>
                </div>
            `;

            addBtn.addEventListener('click', function () {
                const i = nextIndex();
                list.insertAdjacentHTML('beforeend', rowHtml(i));
            });

            list.addEventListener('click', function (e) {
                const btn = e.target.closest('.wm-remove-btn');
                if (!btn) return;
                const row = btn.closest('.wm-item');
                if (row) row.remove();
            });
        })();
    </script>
@endpush
