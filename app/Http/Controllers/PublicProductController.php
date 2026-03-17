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
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WhatsAppNumber;

class PublicProductController extends Controller
{
    protected ProductInterface $productInterface;

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
        return view('public.products.form', [
            'pageTitle' => 'نشر منتج',
            'formAction' => route('public.products.store', request()->query()),
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
        return view('public.products.form', [
            'pageTitle' => 'نشر منتج (للإدارة)',
            'formAction' => route('public.products.store_admin', request()->query()),
            'namePrefix' => 'ج',
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
            $guidedOrder = [
                'weapons_gallery',
                'shotgun',
                'hair',
                'face',
                'tops',
                'pants',
                'emotes',
                'login_emotes',
                'banners',
                'fire_pass',
                'extra_1',
                'extra_2',
            ];
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
            if (count($galleryFiles) < 12) {
                throw ValidationException::withMessages([
                    'gallery' => 'يجب رفع 12 صورة على الأقل في الخطوة 6.',
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
                return response()->json([
                    'ok' => true,
                    'message' => 'تم إرسال طلبك للمراجعة وسيتم نشر الحساب بعد موافقة الإدارة.',
                    'track_url' => route('public.products.track', ['slug' => $slug]),
                ], 201);
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

        } catch (\Throwable $e) {
            report($e);
            if ($this->expectsAjaxJson($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'حدث خطأ غير متوقع أثناء رفع الطلب. حاول مرة أخرى.',
                ], 500);
            }

            return redirect()
                ->route('home')
                ->with('error', 'حدث خطأ غير متوقع');
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
                EmailNotifier::sendAfterCommit($admins, 'طلب جديد: نشر حساب', $adminText);
            }
        }

        // Customer acknowledgement
        if ((bool) config('services.email_notify.notify_customers', true) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $customerText = trim(
                "تم استلام طلبك ✅\n" .
                "سنراجعه ونرسل لك النتيجة (قبول/رفض) قريباً.\n" .
                "متابعة الطلب: {$track}\n"
            );
            EmailNotifier::sendAfterCommit($email, 'تم استلام طلب نشر الحساب ✅', $customerText);
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

        if (count($files) >= 12) {
            return $files;
        }

        // Extra fallback: some hosts/browsers may submit nested file names in a non-standard shape.
        // Collect any uploaded files that look like gallery files and exclude main product/video.
        $fallback = [];
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
            if (str_contains($p, 'gallery')) {
                $fallback[] = $file;
            }
        }

        if (!empty($fallback)) {
            return $this->uniqueUploadedFiles($fallback);
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
