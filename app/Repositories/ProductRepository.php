<?php

namespace App\Repositories;

use App\Models\{Product, Category, Type, Brand, Tag, Section};
use App\Models\SettingWatermark;
use App\Services\Contracts\ProductInterface;
use Illuminate\Http\Request;
use App\DataTables\Dashboard\Admin\ProductDataTable;
use App\Models\Concerns\UploadVideoTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ProductRepository implements ProductInterface
{
    use UploadVideoTrait;

    /* =========================
     * Index
     * ========================= */
    public function index(ProductDataTable $productDataTable)
    {
        $route = request()->route();
        $routeName = $route?->getName();
        $group = null;
        if ($routeName === 'admin.products.accounts') {
            $group = 'accounts';
        } elseif ($routeName === 'admin.products.charge') {
            $group = 'charge';
        } elseif ($routeName === 'admin.products.codes') {
            $group = 'codes';
        } else {
            $group = request()->get('group');
        }
        $pageTitle = trans('dashboard/admin.product.products');

        $countQuery = Product::query();
        if ($group === 'accounts') {
            $pageTitle = 'قائمة الحسابات';
            $countQuery->accountsOnly();
        } elseif ($group === 'charge') {
            $pageTitle = 'قائمة باقات الشحن';
            $countQuery->where('service_type', 'gems');
        } elseif ($group === 'codes') {
            $pageTitle = 'قائمة منتجات الأكواد';
            $countQuery->where('service_type', 'codes');
        }

        return $productDataTable->render('dashboard.admin.products.index', [
            'pageTitle' => $pageTitle,
            'group' => $group,
            'productsCount' => $countQuery->count(),
        ]);
    }

    /* =========================
     * Create
     * ========================= */
    public function create()
    {
        $defaultCategoryId = Category::query()->where('status', 'active')->value('id') ?? Category::query()->value('id');
        $defaultTypeId = Type::query()->value('id');
        $categories = Category::query()->latest()->get();
        $sections = Section::query()->orderBy('order')->with('translations')->get();

        return view('dashboard.admin.products.form', [
            'pageTitle' => 'إضافة منتج',
            'product' => null,
            'defaultCategoryId' => $defaultCategoryId,
            'defaultTypeId' => $defaultTypeId,
            'categories' => $categories,
            'sections' => $sections,
        ]);
    }

    /* =========================
     * Store
     * ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'product' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data = $this->extractData($request);
        $product = Product::create($data);

        // الوسوم
        $product->tags()->sync($request->input('tags', []));

        // أقسام الصفحة الرئيسية (Sections)
        $product->sections()->sync($request->input('section_ids', []));

        // الصورة الرئيسية
        if ($request->hasFile('product')) {
            try {
                $media = $product->uploadSingleMedia(
                    'product',
                    $request->file('product'),
                    $product,
                    null,
                    'media',
                    true,
                    false,
                    null,
                    false,
                    (int) config('account_image.top_area.size_px', 35),
                    true
                );
            } catch (\Throwable $e) {
                report($e);
                throw ValidationException::withMessages([
                    'product' => 'تعذر معالجة الصورة الرئيسية. ارفع صورة أوضح بصيغة JPG/PNG/WEBP.',
                ]);
            }

            if ($media) {
                $imagePath = public_path("uploads/product/{$media}");
                $this->addWatermark($imagePath);
            }
        }

        // صور المعرض
        if ($request->hasFile('gallery')) {
            try {
                $uploadedGallery = $product->uploadMultipleMedia(
                    'product/gallery',
                    $request->file('gallery'),
                    $product,
                    'media',
                    false,
                    true,
                    'gallery',
                    false,
                    (int) config('account_image.top_area.size_px', 35)
                );
            } catch (\Throwable $e) {
                report($e);
                throw ValidationException::withMessages([
                    'gallery' => 'تعذر معالجة صور المعرض. تأكد أن الصور واضحة وبصيغة مدعومة.',
                ]);
            }

            if (empty($uploadedGallery)) {
                // Avoid leaving an orphan product when all gallery files fail processing.
                try { $product->delete(); } catch (\Throwable $e) {}
                throw ValidationException::withMessages([
                    'gallery' => 'تعذر رفع الصور الفرعية. تأكد أن الملفات صور صالحة (JPG/PNG/WEBP).',
                ]);
            }
        }

        // الفيديو
        if ($request->hasFile('video')) {
            $product->uploadVideo($request->file('video'));
        }

        $this->flushWebsiteProductCaches();

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    /* =========================
     * Update (الدالة الوحيدة الصحيحة)
     * ========================= */
    public function update(Request $request, Product $product)
{
    $request->validate([
        'product' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        'gallery' => ['nullable', 'array'],
        'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
    ]);

    $data = $this->extractData($request);
    $product->update($data);

    // الوسوم
    $product->tags()->sync($request->input('tags', []));

    // أقسام الصفحة الرئيسية (Sections)
    $product->sections()->sync($request->input('section_ids', []));

    // تحديث الصورة الرئيسية
    if ($request->hasFile('product')) {
        $media = $product->updateSingleMedia(
            'product',
            $request->file('product'),
            $product,
            null,
            'media',
            true,
            false,
            null,
            false,
            (int) config('account_image.top_area.size_px', 35),
            true
        );

        if ($media) {
            $imagePath = public_path("uploads/product/{$media}");
            $this->addWatermark($imagePath);
        }
    }
$galleryFiles = $request->file('gallery');

if (is_array($galleryFiles)) {

    // نتحقق أن فيه ملفات مرفوعة فعليًا
    $validFiles = array_filter($galleryFiles, function ($file) {
        return $file && $file->isValid();
    });

    if (count($validFiles) > 0) {

        // 🔥 حذف كل الصور القديمة
        $product->deleteExistingMedia(
            'gallery',
            $product,
            null,
            'media',
            true,
            'gallery'
        );

        // 🔥 رفع الصور الجديدة فقط
        $product->uploadMultipleMedia(
            'product/gallery',
            $validFiles,
            $product,
            'media',
            false,
            true,
            'gallery',
            false,
            (int) config('account_image.top_area.size_px', 35)
        );
    }
}



    // ✅ حل مشكلة الفيديو (حذف القديم + رفع الجديد)
    // ✅ تحديث الفيديو (حذف أي فيديو قديم مهما كان اسمه)
if ($request->hasFile('video')) {

    foreach ($product->media as $media) {
        if (str_starts_with($media->mime_type, 'video')) {
            $media->delete();
        }
    }

    // رفع الفيديو الجديد
    $product->uploadVideo($request->file('video'));
}

    $this->flushWebsiteProductCaches();


    return redirect()->route('admin.products.index')
        ->with('success', 'تم تحديث المنتج بنجاح');
}

    /* =========================
     * Edit
     * ========================= */
    public function edit(Product $product)
    {
        $product->load(['tags', 'media', 'sections']);

        $defaultCategoryId = Category::query()->where('status', 'active')->value('id') ?? Category::query()->value('id');
        $defaultTypeId = Type::query()->value('id');
        $categories = Category::query()->latest()->get();
        $sections = Section::query()->orderBy('order')->with('translations')->get();

        return view('dashboard.admin.products.form', [
            'pageTitle' => 'تعديل منتج',
            'product'   => $product,
            'defaultCategoryId' => $defaultCategoryId,
            'defaultTypeId' => $defaultTypeId,
            'categories' => $categories,
            'sections' => $sections,
        ]);
    }

    /* =========================
     * Destroy
     * ========================= */
    public function destroy(Product $product)
    {
        $product->deleteExistingMedia('product', $product, null, 'media', true, 'product');
        $product->delete();

        $this->flushWebsiteProductCaches();

        return redirect()->route('admin.products.index')
            ->with('success', 'تم الحذف بنجاح!');
    }

    private function flushWebsiteProductCaches(): void
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        if (empty($locales)) {
            $locales = (array) config('translatable.locales', []);
        }
        if (empty($locales)) {
            $locales = ['ar', 'en'];
        }

        foreach ($locales as $locale) {
            Cache::forget("home.products.$locale");
            Cache::forget("home.sections.$locale");
        }
    }

    /* =========================
     * Extract Data
     * ========================= */
    private function extractData(Request $request)
    {
        $data = $request->only([
            'category_id',
            'brand_id',
            'type_id',
            'price_before_discount',
            'deal_ends_at',
            'price',
            'points_price',
            'stock',
            'sku',
            'status',
            'featured',
            'slug',
            'client_number',
            'client_email',
            'publish_source',
            'review_note',
            'review_reject_reasons',
            'reviewed_by',
            'reviewed_at',
            'rejected_at',
        ]);

        // Backward-compatible deploy: avoid inserting columns that may not exist yet.
        try {
            if (! Schema::hasColumn('products', 'client_email')) {
                unset($data['client_email']);
            }
        } catch (\Throwable $e) {
            unset($data['client_email']);
        }

        if (empty($data['category_id'])) {
            $data['category_id'] = Category::query()->where('status', 'active')->value('id') ?? Category::query()->value('id');
        }

        if (empty($data['type_id'])) {
            $data['type_id'] = Type::query()->value('id');
        }

        if (empty($data['stock'])) {
            $data['stock'] = 9999;
        }

        if (empty($data['status'])) {
            $data['status'] = 'published';
        }

        $data['featured'] = $request->has('featured');

        foreach (config('translatable.locales') as $locale) {
            $data[$locale] = $request->input($locale);
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->input('ar.name', 'product-' . uniqid()));
        } elseif (Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . uniqid();
        }

        return $data;
    }

    /* =========================
     * Watermark
     * ========================= */
    private function addWatermark($imagePath)
    {
        try {
            if (!file_exists($imagePath)) {
                return;
            }

            $settings = \App\Models\Setting::query()->latest('id')->first();
            $wmEnabled = (bool) ($settings?->watermark_enabled ?? true);
            if (! $wmEnabled) {
                return;
            }
            $multiEnabled = (bool) ($settings?->watermark_multi_enabled ?? false);

            $info = getimagesize($imagePath);
            $mime = $info['mime'];

            $image = $mime === 'image/png'
                ? imagecreatefrompng($imagePath)
                : imagecreatefromjpeg($imagePath);

            imagesavealpha($image, true);
            imagealphablending($image, true);

            $imageWidth  = imagesx($image);
            $imageHeight = imagesy($image);
            $placedCount = 0;

            // New multi-watermark system (dashboard-managed, unlimited items).
            $multiWatermarks = collect();
            if ($multiEnabled && $settings?->id) {
                $multiWatermarks = SettingWatermark::query()
                    ->where('setting_id', (int) $settings->id)
                    ->where('enabled', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();
            }

            if ($multiWatermarks->isNotEmpty()) {
                foreach ($multiWatermarks as $wm) {
                    $wmPath = (string) $wm->getMediaUrl('setting/watermarks', $wm, null, 'media', 'watermark_image');
                    if ($wmPath === '' || !is_file($wmPath)) {
                        continue;
                    }

                    $logo = @imagecreatefrompng($wmPath);
                    if (! $logo) {
                        continue;
                    }
                    imagesavealpha($logo, true);
                    imagealphablending($logo, true);

                    $logoWidth = imagesx($logo);
                    $logoHeight = imagesy($logo);
                    if ($logoWidth <= 0 || $logoHeight <= 0) {
                        imagedestroy($logo);
                        continue;
                    }

                    $scalePercent = max(1, min(100, (int) $wm->scale_percent));
                    $newLogoWidth = max(1, (int) floor($imageWidth * ($scalePercent / 100)));
                    $scale = $newLogoWidth / $logoWidth;
                    $newLogoHeight = max(1, (int) floor($logoHeight * $scale));

                    $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
                    imagesavealpha($resizedLogo, true);
                    imagefill($resizedLogo, 0, 0, imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127));
                    imagecopyresampled(
                        $resizedLogo,
                        $logo,
                        0,
                        0,
                        0,
                        0,
                        $newLogoWidth,
                        $newLogoHeight,
                        $logoWidth,
                        $logoHeight
                    );

                    $xOffset = (int) ($wm->x_offset ?? 20);
                    $yOffset = (int) ($wm->y_offset ?? 0);
                    $x = max(0, min($imageWidth - $newLogoWidth, $xOffset));
                    $y = max(0, min($imageHeight - $newLogoHeight, $yOffset));

                    imagecopy($image, $resizedLogo, $x, $y, 0, 0, $newLogoWidth, $newLogoHeight);
                    $placedCount++;

                    imagedestroy($logo);
                    imagedestroy($resizedLogo);
                }
            }

            // Backward-compatible fallback to the old two-logo behavior
            // when no multi-watermark item is configured yet.
            if ($placedCount === 0) {
                $logoPath = public_path('watermark/logo.png');
                if (is_file($logoPath)) {
                    $xOffset = max(0, (int) ($settings?->watermark_x_offset ?? 20));
                    $yOffset = max(0, (int) ($settings?->watermark_y_offset ?? 0));
                    $scalePercent = max(5, min(90, (int) ($settings?->watermark_scale_percent ?? 20)));
                    $secondEnabled = (bool) ($settings?->watermark_second_enabled ?? true);
                    $secondXOffset = (int) ($settings?->watermark_second_x_offset ?? 40);
                    $secondYOffset = (int) ($settings?->watermark_second_y_offset ?? 0);

                    $logo = imagecreatefrompng($logoPath);
                    imagesavealpha($logo, true);
                    imagealphablending($logo, true);

                    $logoWidth   = imagesx($logo);
                    $logoHeight  = imagesy($logo);
                    $newLogoWidth  = intval($imageWidth * ($scalePercent / 100));
                    $scale         = $newLogoWidth / max(1, $logoWidth);
                    $newLogoHeight = intval($logoHeight * $scale);

                    $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
                    imagesavealpha($resizedLogo, true);
                    imagefill($resizedLogo, 0, 0, imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127));

                    imagecopyresampled(
                        $resizedLogo,
                        $logo,
                        0,
                        0,
                        0,
                        0,
                        $newLogoWidth,
                        $newLogoHeight,
                        $logoWidth,
                        $logoHeight
                    );

                    $y = max(0, min($imageHeight - $newLogoHeight, $yOffset));
                    $x1 = max(0, $imageWidth - $newLogoWidth - $xOffset);
                    imagecopy($image, $resizedLogo, $x1, $y, 0, 0, $newLogoWidth, $newLogoHeight);

                    if ($secondEnabled) {
                        $x2Base = (int) floor(($imageWidth - $newLogoWidth) / 2);
                        $x2 = max(0, min($imageWidth - $newLogoWidth, $x2Base + $secondXOffset));
                        $y2 = max(0, min($imageHeight - $newLogoHeight, $y + $secondYOffset));
                        imagecopy($image, $resizedLogo, $x2, $y2, 0, 0, $newLogoWidth, $newLogoHeight);
                    }

                    imagedestroy($logo);
                    imagedestroy($resizedLogo);
                }
            }

            $mime === 'image/png'
                ? imagepng($image, $imagePath, 9)
                : imagejpeg($image, $imagePath, 90);

            imagedestroy($image);

        } catch (\Exception $e) {
            // تجاهل الخطأ
        }
    }

}
