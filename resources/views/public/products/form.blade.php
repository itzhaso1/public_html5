@extends('public.layouts.master')

@section('pageTitle')
    {{ $pageTitle }}
@endsection

@section('content')

@php
    $isEdit = isset($product);
@endphp
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">

<div class="w-full flex justify-center py-2">
    <div class="w-[85%] max-w-[300px] bg-red-50 border border-red-200 rounded-2xl p-3 text-center shadow-sm">
        <div class="text-red-600 text-lg mb-1">⚠️</div>
        <div class="text-[11px] leading-relaxed text-gray-800">
            يرجى تنفيذ الشروط <span class="font-bold text-red-600 underline">بالتفصيل</span>،
            <br>
            أو <span class="font-bold text-red-700">سوف يتم رفض حسابك</span>.
        </div>
    </div>
</div>

<div class="bg-gray-100" dir="rtl">

    <div class="bg-white px-3 sm:px-4 pt-2 sm:pt-3 pb-24 space-y-3 sm:space-y-4 max-w-md mx-auto w-full">


        @if(session('success'))
            <div class="rounded-2xl bg-green-100 text-green-800 px-4 py-3 text-center font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-red-100 text-red-800 px-4 py-3 text-center font-semibold">
                {{ session('error') }}
            </div>
        @endif

        <div class="text-center mb-2">

            <h1 class="text-2xl font-bold tracking-tight text-gray-800">
                {{ $isEdit ? 'تحديث المنتج' : 'إضافة منتج' }}
            </h1>
        </div>

        <form
            id="productForm"
            action="{{ route('public.products.store', request()->query()) }}"
            method="POST"
            enctype="multipart/form-data"
           class="space-y-6"

        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- شريط التقدم -->
            <div class="space-y-2">
                <div id="stepIndicator" class="text-center text-xs text-gray-500">الخطوة 1 من 7</div>
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div id="stepProgress" class="h-2 bg-indigo-600 transition-all" style="width: 14%;"></div>
                </div>
            </div>

            {{-- ================== اللغة العربية ================== --}}
            @foreach (config('translatable.locales') as $locale)
                @if($locale === 'ar')

                    <!-- STEP 1 -->
                    <div class="step" data-step="1">
                        <label class="text-sm text-gray-600">(AR) اسم الحساب</label>
                        <input
                            type="text"
                            name="{{ $locale }}[name]"
                            maxlength="20"
                            minlength="3"
                            placeholder="مثال: حساب فير 8 لليوم او حساب كلاش محروق"
                            value="{{ old($locale.'.name', $product?->translateOrNew($locale)->name ?? '') }}"
                            oninput="updateCounter(this, 'nameCounter')"
                            class="mt-2 w-full rounded-2xl border border-gray-300 bg-gray-50
                                   px-4 py-5 text-lg
                                   placeholder:text-gray-400
                                   focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                        <div id="nameCounter" class="text-xs text-gray-400 mt-1">0 / 20</div>
                        @error($locale.'.name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- STEP 2 -->
                    <div class="step hidden" data-step="2">
                        <label class="text-sm text-gray-600">(AR) الوصف المختصر</label>
                        <textarea
                            name="{{ $locale }}[short_description]"
                            rows="2"
                            maxlength="37"
                            oninput="updateCounter(this, 'shortDescCounter')"
                            class="mt-2 w-full rounded-2xl border border-gray-300 bg-gray-50
                                   px-4 py-5 text-lg
                                   placeholder:text-gray-400
                                   focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >{{ old(
                            $locale.'.short_description',
                            $product?->translateOrNew($locale)->short_description
                            ?? 'لفل الحساب: () | عدد السكنات: ()'
                        ) }}</textarea>
                        <div id="shortDescCounter" class="text-xs text-gray-400 mt-1">
                            {{ strlen(old(
                                $locale.'.short_description',
                                $product?->translateOrNew($locale)->short_description
                                ?? 'لفل الحساب: () | عدد السكنات: ()'
                            )) }} / 35
                        </div>
                        @error($locale.'.short_description')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- STEP 3 -->
                    <div class="step hidden" data-step="3">
                        <label class="text-sm text-gray-600">(AR) الوصف الكامل</label>
                        <textarea
                            name="{{ $locale }}[description]"
                            rows="6"
                            class="mt-2 w-full rounded-2xl border border-gray-300 bg-gray-50
                                   px-4 py-5 text-lg
                                   focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >{{ old(
                            $locale.'.description',
                            $product?->translateOrNew($locale)->description
                            ?? "فير باس:
لفل الحساب: ( )
عدد السكنات: ( )
عدد الرقصات: ( )
تسجيل دخول: ( )
عدد الأسلحة ماكس 🔫: ( من أصل )"
                        ) }}</textarea>
                        @error($locale.'.description')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                @endif
            @endforeach

            <!-- STEP 4 -->
            <div class="step hidden" data-step="4">
                <div>
                    <label class="text-sm text-gray-600">رقم الواتساب</label>

                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <select id="clientDial" name="client_dial"
                                class="w-full rounded-2xl border border-gray-300 bg-gray-50 px-4 py-5 text-base focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="962">🇯🇴 الأردن (+962)</option>
                            <option value="966">🇸🇦 السعودية (+966)</option>
                            <option value="971">🇦🇪 الإمارات (+971)</option>
                            <option value="965">🇰🇼 الكويت (+965)</option>
                            <option value="974">🇶🇦 قطر (+974)</option>
                            <option value="973">🇧🇭 البحرين (+973)</option>
                            <option value="968">🇴🇲 عُمان (+968)</option>
                            <option value="20">🇪🇬 مصر (+20)</option>
                            <option value="964">🇮🇶 العراق (+964)</option>
                            <option value="961">🇱🇧 لبنان (+961)</option>
                            <option value="970">🇵🇸 فلسطين (+970)</option>
                            <option value="967">🇾🇪 اليمن (+967)</option>
                            <option value="963">🇸🇾 سوريا (+963)</option>
                            <option value="212">🇲🇦 المغرب (+212)</option>
                            <option value="216">🇹🇳 تونس (+216)</option>
                            <option value="213">🇩🇿 الجزائر (+213)</option>
                            <option value="218">🇱🇾 ليبيا (+218)</option>
                            <option value="249">🇸🇩 السودان (+249)</option>
                        </select>

                        <input
                            id="clientLocal"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel"
                            placeholder="اكتب رقمك بدون كود الدولة"
                            value=""
                            class="sm:col-span-2 w-full rounded-2xl border border-gray-300 bg-gray-50
                                   px-4 py-5 text-lg
                                   placeholder:text-gray-400
                                   focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>

                    <input type="hidden" id="clientNumberFull" name="client_number"
                           value="{{ old('client_number', $product->client_number ?? '') }}">

                    <div class="mt-2 text-xs text-gray-500">
                        سيتم إرسال إشعار واتساب عند <b>قبول</b> أو <b>رفض</b> طلبك.
                    </div>
                    @error('client_number')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm text-gray-600">السعر بريال</label>
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        value="{{ old('price', $product->price ?? '') }}"
                        class="mt-2 w-full rounded-2xl border border-gray-300 bg-gray-50
                               px-4 py-5 text-lg
                               focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <div class="mt-3 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-gray-700 font-semibold">العمولة</span>
                            <span id="commissionFee" class="font-extrabold text-indigo-700">—</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-3">
                            <span class="text-gray-700 font-semibold">السعر بعد العمولة</span>
                            <span id="commissionFinal" class="font-extrabold text-green-700">—</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">
                            اكتب سعر الحساب الأساسي (بدون عمولة)، وسيتم إضافة العمولة تلقائياً عند الإرسال.
                        </div>
                    </div>
                    @error('price')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- النوع ثابت --}}
            <input type="hidden" name="type_id" value="{{ old('type_id', $product?->type_id ?? 2) }}">
            <input type="hidden" name="slug" value="{{ old('slug', $product?->slug ?? Str::random(32)) }}">
            <input type="hidden" name="category_id" value="{{ old('category_id', $product?->category_id ?? $data['categories']->first()->id) }}">
            <input type="hidden" name="stock" value="{{ old('stock', $product?->stock ?? 1) }}">

            <!-- STEP 5 (الصورة الرئيسية) -->
            <div class="step hidden" data-step="5">
                <div class="space-y-2">
                    <label class="text-sm text-gray-600">
                        صورة <span class="text-indigo-600 font-semibold">الملف الشخصي</span>
                    </label>

                    <div class="rounded-xl bg-red-50 border border-red-300 p-4 text-center space-y-2">
                        <div class="text-sm font-bold text-red-700">⚠️ تنبيه</div>
                        <div class="text-sm text-red-600">يرجى نسخ النص التالي ووضعه في الملف الشخصي للحساب</div>

                        <div onclick="copyStoreOnly()" class="cursor-pointer select-none rounded-lg bg-white border p-3">
                            <div class="font-bold tracking-widest">مــتــجـر الــمــمالـــك</div>
                            <div class="text-green-600 font-semibold">WHATSAPP+962ᅠ0777ᅠ515ﾠ306</div>
                            <div class="text-xs text-gray-500 mt-2">اضغط هنا لنسخ النص</div>
                        </div>
                    </div>

                    <label for="product_image" class="flex items-center justify-center gap-2 w-full py-4 rounded-2xl border-2 border-dashed border-indigo-300 bg-indigo-50 text-indigo-700 font-semibold text-base cursor-pointer active:scale-[0.98] transition">
                        📷 اختر صورة
                    </label>

                    <input id="product_image" type="file" name="product" accept="image/*" class="hidden" onchange="previewMainImage(this)">

                    <div id="imagePreviewBox" class="hidden mt-3 relative">
                        <img id="imagePreview" class="w-full h-48 object-cover rounded-xl border" alt="معاينة الصورة">
                        <button type="button" onclick="removeMainImage()"
                                class="absolute top-2 right-2 bg-red-500 text-white w-7 h-7 rounded-full flex items-center justify-center shadow-lg border border-white">
                            ✕
                        </button>
                    </div>

                    <p id="product_image_name" class="text-xs text-gray-500">لم يتم اختيار ملف</p>

                    @error('product')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- STEP 6 (المعرض) -->
            <div class="step hidden" data-step="6">
                <div class="space-y-2">
                    <label class="text-sm text-gray-600">
                        صور استعراض الحساب
                        <span class="text-red-600 font-semibold">
                            (يُمنع وضع صورة البروفايل مرة أخرى)
                        </span>
                    </label>

                    <label for="gallery_images"
                           class="flex items-center justify-center gap-2
                                  w-full py-4 rounded-2xl
                                  border-2 border-dashed border-emerald-300
                                  bg-emerald-50 text-emerald-700
                                  font-semibold text-base
                                  cursor-pointer
                                  active:scale-[0.98] transition">
                        🖼️ اختر صور
                    </label>

                    <input id="gallery_images" type="file" name="gallery[]" accept="image/*" multiple
                           class="hidden" onchange="previewGalleryImages(this)">

                    <p id="gallery_images_name" class="text-xs text-gray-500">لم يتم اختيار أي ملفات</p>

                    <div id="galleryPreview" class="grid grid-cols-3 gap-2 mt-3"></div>

                    @error('gallery')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @error('gallery.*')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <p class="mt-2 text-base font-bold text-red-700">
                    🚫 ممنوع تمامًا رفع صور مُمنتجة أو مُركّبة  
                    ✔️ يُقبل فقط تصوير الشاشة الأصلي بدون تعديل
                </p>
            </div>

            <!-- STEP 7 (المراجعة) -->
            <div class="step hidden" data-step="7">
                <div class="rounded-2xl bg-gray-50 border border-gray-200 p-4 space-y-2 text-sm">
                    <div>اسم الحساب: <span id="reviewName" class="font-semibold">—</span></div>
                    <div>الوصف المختصر: <span id="reviewShort" class="font-semibold">—</span></div>
                    <div>السعر: <span id="reviewPrice" class="font-semibold">—</span></div>
                    <div>رقم الهاتف: <span id="reviewPhone" class="font-semibold">—</span></div>
                    <div>الصورة الرئيسية: <span id="reviewMain" class="font-semibold">—</span></div>
                    <div>صور المعرض: <span id="reviewGallery" class="font-semibold">0</span></div>
                </div>
                <p class="text-xs text-gray-500 text-center mt-2">راجع البيانات ثم اضغط نشر الحساب</p>
            </div>

            <!-- أزرار التنقل -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 space-y-2">
                <div class="flex gap-2">
                    <button type="button" id="wizardPrevBtn" onclick="prevStep()"
                            class="w-1/2 bg-gray-200 text-gray-800 py-4 rounded-2xl font-bold text-lg">
                        السابق
                    </button>
                    <button type="button" id="wizardNextBtn" onclick="nextStep()"
                            class="w-1/2 bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg">
                        التالي
                    </button>
                </div>

                <button type="submit" id="finalSubmit"
                        class="hidden w-full bg-indigo-600 text-white py-5 rounded-2xl font-bold text-xl shadow-lg active:scale-[0.98] transition">
                    نشر الحساب
                </button>
            </div>
        </form>
    </div>
</div>

<!-- عداد التحميل -->
<div id="uploadBox" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white w-11/12 max-w-md rounded-2xl p-6 space-y-4 text-center">
        <h2 class="text-lg font-bold text-gray-800">جاري رفع الملف...</h2>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div id="progressBar" class="bg-indigo-600 h-4 w-0 transition-all"></div>
        </div>
        <div id="progressPercent" class="text-sm font-semibold text-gray-700">0%</div>
        <div id="progressInfo" class="text-xs text-gray-500">0 MB / 0 MB</div>
        <div id="progressTime" class="text-xs text-gray-500">الوقت المتبقي: --</div>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 7;
let isProcessingImages = false;
const MAX_IMG_DIM = 1600;
const JPEG_QUALITY = 0.72;

async function downscaleToJpeg(file, opts = {}) {
  const maxDim = opts.maxDim || MAX_IMG_DIM;
  const quality = (typeof opts.quality === 'number') ? opts.quality : JPEG_QUALITY;

  if (!file || !file.type || !file.type.startsWith('image/')) return file;

  // Compress only if the file is large (keeps fast devices fast)
  const isHeic = file.type === 'image/heic' || (file.name || '').toLowerCase().endsWith('.heic');
  if (!isHeic && (file.size || 0) < 900 * 1024) {
    return file;
  }

  const img = await new Promise((resolve, reject) => {
    const i = new Image();
    i.onload = () => resolve(i);
    i.onerror = reject;
    i.src = URL.createObjectURL(file);
  });

  const w = img.naturalWidth || img.width;
  const h = img.naturalHeight || img.height;
  const scale = Math.min(1, maxDim / Math.max(w, h));
  const tw = Math.max(1, Math.round(w * scale));
  const th = Math.max(1, Math.round(h * scale));

  const canvas = document.createElement('canvas');
  canvas.width = tw;
  canvas.height = th;
  const ctx = canvas.getContext('2d', { alpha: false });
  ctx.drawImage(img, 0, 0, tw, th);

  const blob = await new Promise((resolve) => {
    canvas.toBlob((b) => resolve(b), 'image/jpeg', quality);
  });

  try { URL.revokeObjectURL(img.src); } catch (e) {}

  if (!blob) return file;
  const base = (file.name || 'image').replace(/\.(heic|png|webp|jpeg|jpg)$/i, '');
  return new File([blob], base + '.jpg', { type: 'image/jpeg' });
}

function setWizardBusy(state, label = 'التالي') {
    isProcessingImages = state;
    const nextBtn = document.getElementById('wizardNextBtn');
    const submitBtn = document.getElementById('finalSubmit');

    if (nextBtn) {
        nextBtn.disabled = state;
        nextBtn.classList.toggle('opacity-50', state);
        nextBtn.classList.toggle('cursor-not-allowed', state);
        nextBtn.textContent = state ? 'جاري تجهيز الصور...' : label;
    }

    if (submitBtn) {
        submitBtn.disabled = state;
        submitBtn.classList.toggle('opacity-50', state);
        submitBtn.classList.toggle('cursor-not-allowed', state);
    }
}

function updateProgress(step) {
    const percent = Math.round((step / totalSteps) * 100);
    const bar = document.getElementById('stepProgress');
    if (bar) bar.style.width = percent + '%';
}

function showStep(step) {
    document.querySelectorAll('.step').forEach(el => el.classList.add('hidden'));
    const active = document.querySelector(`.step[data-step="${step}"]`);
    if (active) active.classList.remove('hidden');

    document.getElementById('wizardPrevBtn').classList.toggle('hidden', step === 1);
    document.getElementById('wizardNextBtn').classList.toggle('hidden', step === totalSteps);
    document.getElementById('finalSubmit').classList.toggle('hidden', step !== totalSteps);

    document.getElementById('stepIndicator').textContent = `الخطوة ${step} من ${totalSteps}`;
    updateProgress(step);

    if (step === totalSteps) {
        updateReview();
    }
}

function validateStep(step) {
    if (step === 1) {
        const name = document.querySelector('input[name="ar[name]"]');
        if (!name || name.value.trim().length < 3) {
            alert('اسم الحساب مطلوب (3 أحرف على الأقل)');
            return false;
        }
    }
    if (step === 2) {
        const shortDesc = document.querySelector('textarea[name="ar[short_description]"]');
        if (!shortDesc || shortDesc.value.trim().length < 5) {
            alert('الوصف المختصر مطلوب');
            return false;
        }
    }
    if (step === 4) {
        const dial = document.getElementById('clientDial');
        const local = document.getElementById('clientLocal');
        const full = document.getElementById('clientNumberFull');
        if (dial && local && full) {
            syncClientNumber();
            const v = (full.value || '').trim();
            if (v.length < 9) {
                alert('رقم الواتساب مطلوب');
                return false;
            }
        }
    }
    if (step === 5) {
        const mainImage = document.querySelector('input[name="product"]');
        if (!mainImage || mainImage.files.length === 0) {
            alert('يجب رفع صورة البروفايل');
            return false;
        }
    }
    if (step === 6) {
        const gallery = document.querySelector('input[name="gallery[]"]');
        if (!gallery || gallery.files.length < 12) {
            alert('يجب رفع 12 صورة على الأقل');
            return false;
        }
    }
    return true;
}

function syncClientNumber() {
    const dial = document.getElementById('clientDial');
    const local = document.getElementById('clientLocal');
    const full = document.getElementById('clientNumberFull');
    if (!dial || !local || !full) return;

    const dialDigits = (dial.value || '').replace(/\D+/g, '');
    let localDigits = (local.value || '').replace(/\D+/g, '');
    // remove leading zeros users usually type
    localDigits = localDigits.replace(/^0+/, '');

    // If user pasted full international number into local field, keep it as-is.
    if (dialDigits && localDigits.startsWith(dialDigits) && localDigits.length >= dialDigits.length + 6) {
        full.value = localDigits;
        return;
    }

    full.value = (dialDigits + localDigits).replace(/\D+/g, '');
}

function nextStep() {
    if (isProcessingImages) {
        alert('انتظر حتى يتم تجهيز الصور');
        return;
    }
    if (!validateStep(currentStep)) return;
    if (currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

function updateReview() {
    const name = document.querySelector('input[name="ar[name]"]')?.value?.trim() || '—';
    const shortDesc = document.querySelector('textarea[name="ar[short_description]"]')?.value?.trim() || '—';
    const priceRaw = document.querySelector('input[name="price"]')?.value?.trim() || '';
    syncClientNumber();
    const phone = document.getElementById('clientNumberFull')?.value?.trim() || '—';
    const mainImage = document.querySelector('input[name="product"]')?.files?.[0]?.name || 'غير مرفوعة';
    const galleryCount = document.querySelector('input[name="gallery[]"]')?.files?.length || 0;

    const toNum = (v) => {
        const n = parseFloat(String(v || '').replace(/[^\d.]/g, ''));
        return isNaN(n) ? 0 : n;
    };
    const calcCommission = (base) => {
        const p = toNum(base);
        if (p <= 0) return 0;
        if (p <= 500) return 50;
        if (p <= 1000) return 75;
        if (p <= 1500) return 75;
        if (p <= 2000) return 100;
        if (p <= 3000) return 175;
        if (p <= 4000) return 250;
        return 270;
    };
    const basePrice = toNum(priceRaw);
    const fee = calcCommission(basePrice);
    const finalPrice = basePrice > 0 ? (basePrice + fee) : 0;

    document.getElementById('reviewName').textContent = name;
    document.getElementById('reviewShort').textContent = shortDesc;
    document.getElementById('reviewPrice').textContent =
        basePrice > 0
            ? `${finalPrice} ريال (شامل عمولة ${fee})`
            : '—';
    document.getElementById('reviewPhone').textContent = phone;
    document.getElementById('reviewMain').textContent = mainImage;
    document.getElementById('reviewGallery').textContent = galleryCount;
}

document.addEventListener('DOMContentLoaded', () => {
    showStep(currentStep);
    // Keep hidden full phone in sync.
    try {
        document.getElementById('clientDial')?.addEventListener('change', syncClientNumber);
        document.getElementById('clientLocal')?.addEventListener('input', syncClientNumber);

        // Pre-fill local phone if full already exists (old input).
        const full = document.getElementById('clientNumberFull');
        const dial = document.getElementById('clientDial');
        const local = document.getElementById('clientLocal');
        if (full && dial && local) {
            const fullDigits = (full.value || '').replace(/\D+/g, '');
            if (fullDigits.length >= 8) {
                const dials = Array.from(dial.options).map(o => (o.value || '').replace(/\D+/g, ''))
                    .filter(Boolean)
                    .sort((a, b) => b.length - a.length);
                const match = dials.find(d => fullDigits.startsWith(d));
                if (match) {
                    dial.value = match;
                    local.value = fullDigits.slice(match.length);
                } else {
                    // fallback: show last 9 digits
                    local.value = fullDigits.slice(-9);
                }
            }
            syncClientNumber();
        }
    } catch (e) {}
});
</script>

<script>
  (function () {
    const input = document.querySelector('input[name="price"]');
    const feeEl = document.getElementById('commissionFee');
    const finalEl = document.getElementById('commissionFinal');
    if (!input || !feeEl || !finalEl) return;

    const toNum = (v) => {
      const n = parseFloat(String(v || '').replace(/[^\d.]/g, ''));
      return isNaN(n) ? 0 : n;
    };
    const calcCommission = (base) => {
      const p = toNum(base);
      if (p <= 0) return 0;
      if (p <= 500) return 50;
      if (p <= 1000) return 75;
      if (p <= 1500) return 75;
      if (p <= 2000) return 100;
      if (p <= 3000) return 175;
      if (p <= 4000) return 250;
      return 270;
    };
    const fmt = (n) => {
      try { return (Math.round(n * 100) / 100).toString().replace(/\.00$/, ''); } catch (e) { return String(n); }
    };
    const render = () => {
      const base = toNum(input.value);
      if (!base || base <= 0) {
        feeEl.textContent = '—';
        finalEl.textContent = '—';
        return;
      }
      const fee = calcCommission(base);
      const finalPrice = base + fee;
      feeEl.textContent = `+${fmt(fee)} ريال`;
      finalEl.textContent = `${fmt(finalPrice)} ريال`;
    };

    input.addEventListener('input', render);
    render();
  })();
</script>

<script>
document.getElementById('productForm').addEventListener('submit', function (e) {
    const form = this;
    try { syncClientNumber(); } catch (e) {}
    const uploadBox = document.getElementById('uploadBox');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const progressInfo = document.getElementById('progressInfo');
    const progressTime = document.getElementById('progressTime');

    const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent || '');

    // Basic guard: prevent double submit
    const submitBtn = document.getElementById('finalSubmit');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        submitBtn.textContent = 'جاري الإرسال...';
    }

    // Show overlay early so user doesn't think it's frozen
    if (uploadBox) uploadBox.classList.remove('hidden');

    // If SweetAlert2 didn't load yet, fallback to alert later
    const showSuccess = () => {
        if (window.Swal && Swal.fire) {
            Swal.fire({
                icon: 'success',
                title: 'تم تحميل الحساب',
                text: 'تم رفع المنتج بنجاح',
                confirmButtonText: 'تمام'
            }).then(() => window.location.reload());
        } else {
            alert('تم رفع المنتج بنجاح');
            window.location.reload();
        }
    };
    const showError = (msg) => {
        const text = msg || 'حدث خطأ أثناء رفع المنتج';
        if (window.Swal && Swal.fire) {
            Swal.fire({ icon: 'error', title: 'خطأ', text });
        } else {
            alert(text);
        }
        if (uploadBox) uploadBox.classList.add('hidden');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.textContent = 'نشر الحساب';
        }
    };

    // iOS Safari is more reliable with native form submit for large multi-file uploads.
    if (isIOS) {
        // Let the browser handle upload; keep a simple “uploading” overlay.
        if (progressPercent) progressPercent.innerText = 'جاري الرفع...';
        if (progressInfo) progressInfo.innerText = 'قد يستغرق وقتًا حسب حجم الصور — لا تغلق الصفحة';
        if (progressTime) progressTime.innerText = '';
        if (progressBar) progressBar.style.width = '35%';
        return; // do NOT preventDefault => native submit continues
    }

    e.preventDefault();

    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();

    const startTime = new Date().getTime();

    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
    xhr.timeout = 12 * 60 * 1000; // 12 minutes

    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressPercent.innerText = percent + '%';

            const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
            const totalMB = (e.total / (1024 * 1024)).toFixed(2);
            progressInfo.innerText = `${loadedMB} MB / ${totalMB} MB`;

            const elapsedTime = (new Date().getTime() - startTime) / 1000;
            const speed = e.loaded / elapsedTime;
            const remainingTime = (e.total - e.loaded) / speed;

            progressTime.innerText = `الوقت المتبقي: ${Math.ceil(remainingTime)} ثانية`;
        }
    };

    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
            showSuccess();
        } else {
            console.error(xhr.responseText);
            showError('حدث خطأ أثناء رفع المنتج');
        }
    };

    xhr.onerror = function () {
        showError('فشل الاتصال أثناء الرفع');
    };

    xhr.ontimeout = function () {
        showError('انتهت مهلة الرفع. حاول مرة أخرى أو قلّل حجم الصور.');
    };

    xhr.send(formData);
});
</script>

<script>
function updateCounter(input, counterId) {
    const counter = document.getElementById(counterId);
    counter.innerText = input.value.length + ' / ' + input.maxLength;
}
</script>

<script>
async function previewMainImage(input) {
  setWizardBusy(true);

  let file = input.files[0];
  const previewBox = document.getElementById('imagePreviewBox');
  const previewImg = document.getElementById('imagePreview');
  const fileName = document.getElementById('product_image_name');

  if (!file) {
    setWizardBusy(false);
    return;
  }

  if (file.type === 'image/heic' || file.name.toLowerCase().endsWith('.heic')) {
    const convertedBlob = await heic2any({
      blob: file,
      toType: 'image/jpeg',
      quality: 0.75
    });

    file = new File([convertedBlob], file.name.replace('.heic', '.jpg'), { type: 'image/jpeg' });
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
  }

  try {
    file = await downscaleToJpeg(file);
    const dt2 = new DataTransfer();
    dt2.items.add(file);
    input.files = dt2.files;
  } catch (e) {}

  fileName.innerText = file.name;
  previewImg.src = URL.createObjectURL(file);
  previewBox.classList.remove('hidden');

  setWizardBusy(false);
}

function removeMainImage() {
    const input = document.getElementById('product_image');
    const previewBox = document.getElementById('imagePreviewBox');
    const fileName = document.getElementById('product_image_name');
    
    input.value = "";
    previewBox.classList.add('hidden');
    fileName.textContent = "لم يتم اختيار ملف";
}

function copyStoreOnly() {
    const text = `مــتــجـر الــمــمالـــك\nWHATSAPP+962ᅠ0777ᅠ515ﾠ306`;
    navigator.clipboard.writeText(text).then(() => {
        alert('تم نسخ النص ✔️');
    }).catch(() => {
        alert('فشل النسخ');
    });
}
</script>

<script>
let galleryFiles = [];

async function previewGalleryImages(input) {
    setWizardBusy(true);

    const preview = document.getElementById('galleryPreview');
    const nameLabel = document.getElementById('gallery_images_name');

    let files = Array.from(input.files);
    galleryFiles = [];
    preview.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        let file = files[i];

        if (nameLabel) {
            nameLabel.textContent = `جاري تجهيز الصور... (${i + 1} / ${files.length})`;
            nameLabel.classList.remove('text-red-600', 'text-green-600');
            nameLabel.classList.add('text-gray-500');
        }

        try {
            if (file.type === 'image/heic' || file.name.toLowerCase().endsWith('.heic')) {
                const blob = await heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.8
                });

                file = new File([blob], file.name.replace('.heic', '.jpg'), {
                    type: 'image/jpeg'
                });
            }
        } catch (e) {}

        try {
            file = await downscaleToJpeg(file);
        } catch (e) {}

        galleryFiles.push(file);
    }

    renderGallery();
    setWizardBusy(false);
}

function renderGallery() {
    const preview = document.getElementById('galleryPreview');
    const nameLabel = document.getElementById('gallery_images_name');
    const nextBtn = document.getElementById('wizardNextBtn');

    preview.innerHTML = '';

    const dt = new DataTransfer();
    galleryFiles.forEach(f => dt.items.add(f));
    document.getElementById('gallery_images').files = dt.files;

    if (galleryFiles.length < 12) {
        nameLabel.textContent = `⚠️ يجب اختيار 12 صورة على الأقل (المختار: ${galleryFiles.length})`;
        nameLabel.classList.add('text-red-600');
        nameLabel.classList.remove('text-green-600');

        if (nextBtn) {
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    } else {
        nameLabel.textContent = `${galleryFiles.length} صور مختارة`;
        nameLabel.classList.remove('text-red-600');
        nameLabel.classList.add('text-green-600');

        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    galleryFiles.forEach((file, index) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = `w-full aspect-[6/4] object-cover bg-gray-50 rounded-lg border`;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.innerHTML = '✖';
        removeBtn.className = `
            absolute -top-2 -right-2
            bg-red-600 text-white text-xs
            w-6 h-6 rounded-full
            flex items-center justify-center
            shadow
        `;

        removeBtn.onclick = () => removeGalleryImage(index);

        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        preview.appendChild(wrapper);
    });
}

function removeGalleryImage(index) {
    galleryFiles.splice(index, 1);
    renderGallery();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection