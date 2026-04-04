@extends('dashboard.layouts.master')

@section('pageTitle')
    الاعدادات العامه
@endsection

@section('content')
    @include('dashboard.layouts.common._partial.messages')
    <div id="kt_content_container" class="container-xxl">
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
                    <div class="container p-4 mt-2 bg-white rounded shadow">
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
                    <div class="container p-4 mt-4 bg-white rounded shadow">
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
                        </div>
                    </div>

                    <!-- Public publish form settings -->
                    <div class="container p-4 mt-4 bg-white rounded shadow">
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
                    <div class="container p-4 mt-4 bg-white rounded shadow">
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
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات التغبيش من لوحة التحكم شغّل: <code>php artisan migrate --force</code>
                            </div>
                        @endif
                    </div>

                    <!-- Home featured accounts -->
                    <div class="container p-4 mt-4 bg-white rounded shadow">
                        <h4 class="mb-3 fw-bolder">الحسابات المميزة في الصفحة الرئيسية (سلايدر)</h4>
                        @if(\Illuminate\Support\Facades\Schema::hasColumn('settings', 'home_featured_product_ids'))
                            @php
                                $selectedFeatured = old('home_featured_product_ids', $selectedHomeFeaturedProductIds ?? []);
                                if (!is_array($selectedFeatured)) $selectedFeatured = [];
                                $selectedFeatured = array_map('intval', $selectedFeatured);
                            @endphp
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="input-group-text text-dark mb-2">اختر الحسابات المميزة</label>
                                    <select id="home_featured_product_ids"
                                            name="home_featured_product_ids[]"
                                            class="form-select"
                                            multiple
                                            size="10">
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

                    <!-- Merchant pricing -->
                    <div class="container p-4 mt-4 bg-white rounded shadow">
                        <h4 class="mb-3 fw-bolder">أسعار التجار (قسم شحن الجواهر)</h4>
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
                    <div class="container p-4 mt-4 bg-white rounded shadow">
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
                    <div class="form-row">
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

        if (window.jQuery && $.fn && $.fn.select2) {
            const featuredSelect = $('#home_featured_product_ids');
            if (featuredSelect.length) {
                featuredSelect.select2({
                    placeholder: 'اختر الحسابات المميزة',
                    width: '100%',
                    dir: 'rtl',
                    closeOnSelect: false
                });
            }
        }

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
        const previewImage = document.getElementById('blurPreviewImage');
        const previewStage = document.getElementById('blurPreviewStage');
        const ovTop = document.getElementById('ovTop');
        const ovName = document.getElementById('ovName');
        const ovCenter = document.getElementById('ovCenter');
        let naturalW = 0, naturalH = 0;

        function n(id, def = 0) {
            const el = document.getElementById(id);
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
            const cX = cXRaw <= 0 ? Math.max(0, (naturalW - cW) / 2) : cXRaw;
            const cY = cYRaw <= 0 ? Math.max(0, (naturalH - cH) / 2) : cYRaw;
            show(ovCenter, centerEnabled);
            if (ovCenter) {
                ovCenter.style.left = (offX + px(cX)) + 'px';
                ovCenter.style.top = (offY + px(cY)) + 'px';
                ovCenter.style.width = px(Math.min(cW, Math.max(1, naturalW - cX))) + 'px';
                ovCenter.style.height = px(Math.min(cH, Math.max(1, naturalH - cY))) + 'px';
            }
        }

        if (previewInput && previewImage) {
            previewInput.addEventListener('change', function () {
                const f = this.files && this.files[0] ? this.files[0] : null;
                if (!f) return;
                const url = URL.createObjectURL(f);
                previewImage.onload = function () {
                    naturalW = this.naturalWidth || this.width;
                    naturalH = this.naturalHeight || this.height;
                    this.style.display = 'block';
                    renderPreviewOverlays();
                };
                previewImage.src = url;
            });
        }

        [
            'account_top_area_mode','account_top_area_size_px','account_top_area_width_px','account_top_area_x_from_right_px','account_top_area_blur_strength',
            'account_name_blur_enabled','account_name_blur_mode','account_name_blur_width','account_name_blur_height','account_name_blur_x_offset_from_right','account_name_blur_y','account_name_blur_strength',
            'account_name_blur_x_offset_from_right_ratio','account_name_blur_y_ratio','account_name_blur_width_ratio','account_name_blur_height_ratio',
            'account_center_blur_enabled','account_center_blur_width','account_center_blur_height','account_center_blur_x','account_center_blur_y','account_center_blur_strength'
        ].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', renderPreviewOverlays);
            el.addEventListener('change', renderPreviewOverlays);
        });
        window.addEventListener('resize', renderPreviewOverlays);
    </script>
@endpush
