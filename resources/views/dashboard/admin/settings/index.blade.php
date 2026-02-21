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
                                    <img src="{{$logo}}" class="img-fluid" style="max-height: 60px;" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 mb-3 text-center border rounded">
                                    <label for="favicon" class="form-label fw-bold">favicon</label>
                                    <input class="form-control" type="file" name="favicon" id="logoInput" accept="image/*">
                                    <img src="{{$favicon}}" class="img-fluid" style="max-height: 60px;"/>
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
                                        هذا الرقم هو معدل التحويل المستخدم لعرض الأسعار بالدولار داخل قسم شحن الجواهر للتجار فقط.
                                        اتركه فارغاً لاستخدام السعر الافتراضي.
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                لتفعيل إعدادات التجار شغّل: <code>php artisan migrate --force</code>
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

        function updateLoyaltyPointsDisplay(value) {
            document.getElementById('loyalty_points_display').textContent = value;
            document.getElementById('loyalty_points').value = value;
        }

        document.getElementById('audioInput').addEventListener('change', function (event) {
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
    </script>
@endpush
