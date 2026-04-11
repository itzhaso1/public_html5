<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

use App\Services\Contracts\ProductInterface;
use App\Models\Category;
use App\Models\Type;
use App\Models\Tag;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use Illuminate\Support\Facades\Cache;

class PublicProductController extends Controller
{
    protected ProductInterface $productInterface;

    private function publishMinGalleryImages(): int
    {
        $default = 12;
        try {
            $value = null;
            if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'public_publish_min_gallery_images')) {
                $s = \Illuminate\Support\Facades\Cache::get('app_settings') ?: \App\Models\Setting::query()->latest('id')->first();
                $value = (int) ($s?->public_publish_min_gallery_images ?? $default);
            }
            $n = (int) ($value ?? $default);
            if ($n < 1) $n = 1;
            if ($n > 40) $n = 40;
            return $n;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * @return array<int,array{key:string,title:string,hint:string}>
     */
    private function guidedSlotDefinitions(int $minRequired): array
    {
        $base = [
            ['key' => 'weapons_gallery', 'title' => '1) معرض أسلحة', 'hint' => 'صورة واضحة للأسلحة/الاسكنات'],
            ['key' => 'shotgun', 'title' => '2) الشوت قان', 'hint' => 'صورة الشوت قان أو أفضل سلاح عندك'],
            ['key' => 'hair', 'title' => '3) الشعر', 'hint' => 'صورة الشعر/الهيت'],
            ['key' => 'face', 'title' => '4) الوجه', 'hint' => 'صورة الوجه/الماسك'],
            ['key' => 'tops', 'title' => '5) الصدريات / تيشيرتات', 'hint' => 'أفضل صدرية/تيشيرت'],
            ['key' => 'pants', 'title' => '6) السراويل', 'hint' => 'أفضل بنطلون/سروال'],
            ['key' => 'emotes', 'title' => '7) الرقصات', 'hint' => 'أشهر الرقصات'],
            ['key' => 'login_emotes', 'title' => '8) رقصات تسجيل دخول', 'hint' => 'رقصات الدخول/اللوبي'],
            ['key' => 'banners', 'title' => '9) البنرات', 'hint' => 'بنرات/بادجات الحساب'],
            ['key' => 'fire_pass', 'title' => '10) الفير باسات', 'hint' => 'صورة الفير باس/الباس'],
            ['key' => 'extra_1', 'title' => '11) صورة إضافية 1', 'hint' => 'أي شيء قوي بالحساب'],
            ['key' => 'extra_2', 'title' => '12) صورة إضافية 2', 'hint' => 'أي شيء قوي بالحساب'],
        ];

        $minRequired = max(1, min(40, (int) $minRequired));
        if ($minRequired <= count($base)) {
            return array_slice($base, 0, $minRequired);
        }

        $slots = $base;
        for ($idx = count($base) + 1; $idx <= $minRequired; $idx++) {
            $extraIndex = $idx - 10;
            $slots[] = [
                'key' => 'extra_' . $extraIndex,
                'title' => $idx . ') صورة إضافية ' . $extraIndex,
                'hint' => 'صورة إضافية حسب ما تراه مناسباً',
            ];
        }
        return $slots;
    }

    private function publishCommissionFor(float $basePrice): float
    {
        $p = (float) $basePrice;
        if ($p <= 0) return 0.0;

        // Pricing tiers (SAR):
        // <= 500 => +50
        // <= 1000 => +75
        // > 1500 => +100
        // > 2000 => +175
        // > 3000 => +250
        // > 4000 => +270
        if ($p <= 500) return 50.0;
        if ($p <= 1000) return 75.0;
        if ($p <= 1500) return 75.0;
        if ($p <= 2000) return 100.0;
        if ($p <= 3000) return 175.0;
        if ($p <= 4000) return 250.0;
        return 270.0;
    }

    public function __construct(ProductInterface $productInterface)
    {
        $this->productInterface = $productInterface;
    }

    /**
     * عرض فورم نشر المنتج مباشرة
     */
    public function create()
    {
        $minGallery = $this->publishMinGalleryImages();
        $guidedSlots = $this->guidedSlotDefinitions($minGallery);
        $publishProfileGuideImage = null;
        try {
            $s = Cache::get('app_settings') ?: Setting::query()->latest('id')->first();
            if ($s) {
                $publishProfileGuideImage = (bool) ($s?->public_publish_profile_guide_enabled ?? false)
                    ? ($s->getMediaUrl('setting', $s, null, 'media', 'public_publish_profile_guide') ?: null)
                    : null;
            }
        } catch (\Throwable $e) {
            $publishProfileGuideImage = null;
        }
        return view('public.products.form', [
            'pageTitle' => 'نشر منتج',
            'product' => null,
            'formAction' => route('public.products.store', request()->query()),
            'minGalleryCount' => $minGallery,
            'guidedSlots' => $guidedSlots,
            'guidedGalleryKeys' => array_values(array_map(fn($s) => (string) $s['key'], $guidedSlots)),
            'publishProfileGuideImage' => $publishProfileGuideImage,
            'data' => [
                'categories' => Category::all(),
                'types' => Type::all(),
                'tags' => Tag::all(),
            ],
        ]);
    }

    /**
     * نفس صفحة publish-product لكن مخصصة للإدارة (تضيف حرف "ج" تلقائياً لاسم الحساب).
     */
    public function createAdmin()
    {
        $minGallery = $this->publishMinGalleryImages();
        $guidedSlots = $this->guidedSlotDefinitions($minGallery);
        $publishProfileGuideImage = null;
        try {
            $s = Cache::get('app_settings') ?: Setting::query()->latest('id')->first();
            if ($s) {
                $publishProfileGuideImage = (bool) ($s?->public_publish_profile_guide_enabled ?? false)
                    ? ($s->getMediaUrl('setting', $s, null, 'media', 'public_publish_profile_guide') ?: null)
                    : null;
            }
        } catch (\Throwable $e) {
            $publishProfileGuideImage = null;
        }
        return view('public.products.form', [
            'pageTitle' => 'نشر منتج (للإدارة)',
            'product' => null,
            'formAction' => route('public.products.store_admin', request()->query()),
            'namePrefix' => 'ج',
            'minGalleryCount' => $minGallery,
            'guidedSlots' => $guidedSlots,
            'guidedGalleryKeys' => array_values(array_map(fn($s) => (string) $s['key'], $guidedSlots)),
            'publishProfileGuideImage' => $publishProfileGuideImage,
            'data' => [
                'categories' => Category::all(),
                'types' => Type::all(),
                'tags' => Tag::all(),
            ],
        ]);
    }

    /**
     * حفظ المنتج
     */
    public function store(Request $request)
    {
        return $this->storeInternal($request, null);
    }

    public function storeAdmin(Request $request)
    {
        return $this->storeInternal($request, 'ج');
    }

    private function expectsAjaxJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || strtolower((string) $request->header('X-Requested-With')) === 'xmlhttprequest';
    }

    private function storeInternal(Request $request, ?string $namePrefix)
    {
        try {
            $emailRules = ['required', 'string', 'email', 'max:255'];
            if (!empty($namePrefix)) {
                // Admin helper page can keep email optional.
                $emailRules = ['nullable', 'string', 'email', 'max:255'];
            }

            $request->validate([
                'client_email' => $emailRules,
            ], [
                'client_email.required' => 'البريد الإلكتروني مطلوب لإرسال إشعارات حالة حسابك.',
                'client_email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            ]);

            // Gallery mode: guided vs advanced (single multi-upload).
            // We accept both without breaking old clients.
            $galleryMode = strtolower(trim((string) $request->input('gallery_mode', 'guided')));
            if (!in_array($galleryMode, ['guided', 'advanced'], true)) {
                $galleryMode = 'guided';
            }

            // Normalize customer WhatsApp number (digits only, fixes common formats).
            $normalizedPhone = WhatsAppNumber::normalize((string) $request->input('client_number', ''));
            if ($normalizedPhone !== '') {
                $request->merge(['client_number' => $normalizedPhone]);
            }

            $clientEmail = trim((string) $request->input('client_email', ''));
            if ($clientEmail !== '') {
                try {
                    if (Schema::hasColumn('products', 'client_email')) {
                        $request->merge(['client_email' => $clientEmail]);
                    }
                } catch (\Throwable $e) {
                    // ignore (migrations not yet applied)
                }
            }

            if ($namePrefix) {
                $this->applyNamePrefix($request, $namePrefix, 'ar');
            }

            // Build gallery files for guided mode (ordered by sections).
            // We do this explicitly when gallery_mode=guided to avoid stale hidden advanced input affecting validation.
            $minGallery = $this->publishMinGalleryImages();
            $guidedOrder = array_values(array_map(
                fn($s) => (string) ($s['key'] ?? ''),
                $this->guidedSlotDefinitions($minGallery)
            ));
            if ($galleryMode === 'guided') {
                $files = $this->collectGuidedGalleryFiles($request, $guidedOrder);
                $request->files->set('gallery', $files);
            } elseif (! $request->hasFile('gallery')) {
                // Backward compatibility: if old clients miss gallery_mode but send guided files, still accept.
                $files = $this->collectGuidedGalleryFiles($request, $guidedOrder);
                if (!empty($files)) {
                    $request->files->set('gallery', $files);
                }
            }

            // Validate gallery minimum (security & UX parity with frontend).
            $galleryFiles = $request->file('gallery');
            if ($galleryFiles instanceof \Illuminate\Http\UploadedFile) {
                $galleryFiles = [$galleryFiles];
            }
            if (!is_array($galleryFiles) || count($galleryFiles) === 0) {
                throw ValidationException::withMessages([
                    'gallery' => 'يجب رفع صور المعرض في الخطوة 6.',
                ]);
            }
            if (count($galleryFiles) < $minGallery) {
                throw ValidationException::withMessages([
                    'gallery' => 'يجب رفع ' . $minGallery . ' صورة على الأقل في الخطوة 6.',
                ]);
            }

            // Auto-add commission for public publish price.
            $basePrice = (float) $request->input('price', 0);
            if ($basePrice > 0) {
                $commission = $this->publishCommissionFor($basePrice);
                $finalPrice = round($basePrice + $commission, 2);

                $note = trim((string) $request->input('review_note', ''));
                $meta = "Public publish pricing:\n- base: {$basePrice} SAR\n- commission: {$commission} SAR\n- final: {$finalPrice} SAR";
                $request->merge([
                    // Persist final price in DB.
                    'price' => $finalPrice,
                    // Add a note for admin review (doesn't affect frontend).
                    'review_note' => $note !== '' ? ($note . "\n\n" . $meta) : $meta,
                ]);
            }

            // Always create as "pending review" before publishing.
            $slug = Str::random(32);
            $payload = [
                'status' => 'draft',
                // Ensure we control the tracking slug; never trust client-provided slug.
                'slug' => $slug,
                'published_at' => null,
            ];
            try {
                if (Schema::hasColumn('products', 'publish_source')) {
                    $payload['publish_source'] = 'public';
                }
            } catch (\Throwable $e) {
                // ignore (migrations not yet applied)
            }
            $request->merge($payload);

            $this->productInterface->store($request);
            $this->notifyOnNewPublishRequest($request, $slug);

            if ($this->expectsAjaxJson($request)) {
                $trackUrl = route('public.products.track', ['slug' => $slug]);
                return response()->json([
                    'ok' => true,
                    'message' => 'تم إرسال طلبك للمراجعة وسيتم نشر الحساب بعد موافقة الإدارة.',
                    'track_url' => $trackUrl,
                ], 201)->header('X-Track-Url', $trackUrl);
            }

            return redirect()
                ->route('public.products.track', ['slug' => $slug])
                ->with('success', 'تم إرسال طلبك للمراجعة وسيتم نشر الحساب بعد موافقة الإدارة.');

        } catch (ValidationException $e) {
            if ($this->expectsAjaxJson($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'يرجى تصحيح البيانات المرفوعة ثم المحاولة مرة أخرى.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\RuntimeException $e) {
            $msg = trim((string) $e->getMessage());
            if ($msg === '') {
                $msg = 'تعذر رفع بعض الصور، يرجى إعادة المحاولة.';
            }
            if ($this->expectsAjaxJson($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => $msg,
                    'errors' => ['gallery' => [$msg]],
                ], 422);
            }
            return redirect()
                ->back()
                ->withErrors(['gallery' => $msg])
                ->withInput();

        } catch (\Throwable $e) {
            report($e);
            if ($this->expectsAjaxJson($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'حدث خطأ غير متوقع أثناء رفع الطلب. حاول مرة أخرى.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'حدث خطأ غير متوقع أثناء الإرسال. حاول مرة أخرى.'])
                ->withInput();
        }
    }

    private function applyNamePrefix(Request $request, string $prefix, string $locale = 'ar'): void
    {
        $prefix = trim((string) $prefix);
        if ($prefix === '') return;

        $payload = (array) $request->input($locale, []);
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') return;

        if (!str_starts_with($name, $prefix) && !str_starts_with($name, $prefix . ' ')) {
            $payload['name'] = $prefix . ' ' . $name;
            $request->merge([$locale => $payload]);
        }
    }

    private function notifyOnNewPublishRequest(Request $request, string $slug): void
    {
        if (! (bool) config('services.email_notify.enabled', false)) {
            return;
        }

        $name = trim((string) $request->input('ar.name', ''));
        $price = (string) $request->input('price', '');
        $phone = trim((string) $request->input('client_number', ''));
        $email = trim((string) $request->input('client_email', ''));
        $track = route('public.products.track', ['slug' => $slug]);

        // Admin notification
        if ((bool) config('services.email_notify.notify_admin', true)) {
            $admins = EmailNotifier::adminRecipients();
            if (!empty($admins)) {
                $adminText = trim(
                    "طلب جديد: نشر حساب من صفحة publish-product\n" .
                    ($name !== '' ? "الاسم: {$name}\n" : '') .
                    ($price !== '' ? "السعر: {$price}\n" : '') .
                    ($phone !== '' ? "الهاتف: {$phone}\n" : '') .
                    (filter_var($email, FILTER_VALIDATE_EMAIL) ? "Email: {$email}\n" : '') .
                    "متابعة الطلب: {$track}\n"
                );
                try {
                    EmailNotifier::sendAfterCommit($admins, 'طلب جديد: نشر حساب', $adminText);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        // Customer acknowledgement
        if ((bool) config('services.email_notify.notify_customers', true) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $customerText = trim(
                "تم استلام طلبك ✅\n" .
                "سنراجعه ونرسل لك النتيجة (قبول/رفض) قريباً.\n" .
                "متابعة الطلب: {$track}\n"
            );
            try {
                EmailNotifier::sendAfterCommit($email, 'تم استلام طلب نشر الحساب ✅', $customerText);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Robust extractor for guided gallery files.
     * Some hosts/framework combinations expose nested file arrays differently.
     */
    private function collectGuidedGalleryFiles(Request $request, array $order): array
    {
        $files = [];
        $allFiles = $request->allFiles();

        foreach ($order as $key) {
            $file = $request->file("gallery_guided.$key");
            if (!$file) {
                $file = data_get($allFiles, "gallery_guided.$key");
            }

            if ($file instanceof UploadedFile) {
                $files[] = $file;
            }
        }

        if (count($files) >= count($order)) {
            return $files;
        }

        // Strong fallback: JS submit appends guided slots again as gallery[] in order.
        // Some hosts/browsers fail to expose nested gallery_guided[...] reliably.
        $directGallery = $request->file('gallery');
        if ($directGallery instanceof UploadedFile) {
            $directGallery = [$directGallery];
        }
        if (is_array($directGallery) && !empty($directGallery)) {
            $directGallery = array_values(array_filter($directGallery, fn($f) => $f instanceof UploadedFile));
            if (count($directGallery) >= count($order)) {
                return $directGallery;
            }
        }

        // Extra fallback: some hosts/browsers may submit nested file names in a non-standard shape.
        // Collect files with preference for guided fields and exclude main product/video.
        $fallbackGuided = [];
        $fallbackGallery = [];
        foreach ($this->flattenUploadedFiles($allFiles) as $path => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $p = strtolower((string) $path);
            if ($p === 'product' || str_starts_with($p, 'product.')) {
                continue;
            }
            if ($p === 'video' || str_starts_with($p, 'video.')) {
                continue;
            }
            if (str_contains($p, 'gallery_guided')) {
                $fallbackGuided[] = $file;
                continue;
            }
            if ($p === 'gallery' || str_starts_with($p, 'gallery.')) {
                $fallbackGallery[] = $file;
            }
        }

        if (count($fallbackGuided) >= count($order)) {
            return $fallbackGuided;
        }

        if (count($fallbackGallery) >= count($order)) {
            return $fallbackGallery;
        }

        if (!empty($directGallery)) {
            return $directGallery;
        }

        if (!empty($fallbackGuided)) {
            return $fallbackGuided;
        }

        if (!empty($fallbackGallery)) {
            return $fallbackGallery;
        }

        return $files;
    }

    private function flattenUploadedFiles(array $files, string $prefix = ''): array
    {
        $out = [];
        foreach ($files as $key => $value) {
            $path = $prefix === '' ? (string) $key : ($prefix . '.' . $key);
            if (is_array($value)) {
                $out += $this->flattenUploadedFiles($value, $path);
                continue;
            }
            if ($value instanceof UploadedFile) {
                $out[$path] = $value;
            }
        }
        return $out;
    }

    private function uniqueUploadedFiles(array $files): array
    {
        $seen = [];
        $out = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $sig = implode('|', [
                (string) $file->getPathname(),
                $file->getClientOriginalName(),
                (string) $file->getSize(),
            ]);
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $out[] = $file;
        }
        return $out;
    }

    /**
     * Track a public publish request by slug (no login required).
     */
    public function track(string $slug)
    {
        $q = Product::query()
            ->with(['translations', 'media'])
            ->where('slug', $slug);
        try {
            if (Schema::hasColumn('products', 'publish_source')) {
                $q->where('publish_source', 'public');
            }
        } catch (\Throwable $e) {
            // ignore (migrations not yet applied)
        }
        $product = $q->firstOrFail();

        $status = (string) ($product->status ?? 'draft');
        $statusLabel = match ($status) {
            'published' => 'تمت الموافقة (منشور)',
            'archived' => 'مرفوض',
            default => 'قيد المراجعة',
        };

        return view('public.products.track', [
            'pageTitle' => 'متابعة طلب نشر الحساب',
            'product' => $product,
            'statusLabel' => $statusLabel,
        ]);
    }
}
