<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Email\EmailNotifier;
use App\Support\WhatsApp\WasenderNotifier;
use App\Support\WhatsApp\WhatsAppNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicProductRequestController extends Controller
{
    private const REASONS = [
        'البروفايل بدون نص المتجر',
        'الصور غير واضحة',
        'صور المعرض ناقصة أو مكررة',
        'الوصف غير مطابق/غير كامل',
        'السعر غير مناسب',
        'بيانات ناقصة',
        'الحساب مخالف للشروط',
    ];

    public function index(Request $request)
    {
        $filter = (string) $request->query('status', 'pending');
        if (!in_array($filter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $filter = 'pending';
        }

        $q = Product::query()
            ->with(['translations', 'media'])
            ->where('publish_source', 'public')
            ->whereNull('service_type')
            ->latest();

        if ($filter !== 'all') {
            $map = [
                'pending' => 'draft',
                'approved' => 'published',
                'rejected' => 'archived',
            ];
            $q->where('status', $map[$filter]);
        }

        $requests = $q->paginate(40)->withQueryString();

        return view('dashboard.admin.public_products.index', [
            'pageTitle' => 'طلبات نشر الحسابات (مراجعة قبل النشر)',
            'requests' => $requests,
            'status' => $filter,
        ]);
    }

    public function show(Product $product)
    {
        $this->ensurePublic($product);
        $product->load(['translations', 'media']);

        $mainImage = $product->getMediaUrl('product', $product, null, 'media', 'product');
        $galleryImages = $product->getMultipleMediaUrls('product/gallery', $product, 'media', 'gallery');

        return view('dashboard.admin.public_products.show', [
            'pageTitle' => 'مراجعة طلب نشر الحساب',
            'product' => $product,
            'mainImage' => $mainImage,
            'galleryImages' => $galleryImages,
            'reasons' => self::REASONS,
        ]);
    }

    public function approve(Request $request, Product $product)
    {
        $this->ensurePublic($product);

        if ((string) ($product->status ?? '') !== 'draft') {
            return back()->withErrors(['error' => 'لا يمكن الموافقة لأن الطلب ليس قيد المراجعة.']);
        }

        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $product->update([
            'status' => 'published',
            'published_at' => now(),
            'review_note' => $data['review_note'] ?? null,
            'review_reject_reasons' => null,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'rejected_at' => null,
        ]);

        $this->flushWebsiteProductCaches();
        $this->notifyPublisher($product, true);

        return redirect()
            ->route('admin.public_products.index', ['status' => 'approved'])
            ->with('success', 'تمت الموافقة وتم نشر الحساب ✅');
    }

    public function reject(Request $request, Product $product)
    {
        $this->ensurePublic($product);

        if ((string) ($product->status ?? '') !== 'draft') {
            return back()->withErrors(['error' => 'لا يمكن الرفض لأن الطلب ليس قيد المراجعة.']);
        }

        $data = $request->validate([
            'review_reject_reasons' => ['required', 'array', 'min:1'],
            'review_reject_reasons.*' => ['required', 'string', 'in:' . implode(',', self::REASONS)],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $product->update([
            'status' => 'archived',
            'published_at' => null,
            'review_reject_reasons' => array_values(array_unique($data['review_reject_reasons'] ?? [])),
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'rejected_at' => now(),
        ]);

        $this->flushWebsiteProductCaches();
        $this->notifyPublisher($product, false);

        return redirect()
            ->route('admin.public_products.index', ['status' => 'rejected'])
            ->with('success', 'تم رفض الطلب ❌');
    }

    private function ensurePublic(Product $product): void
    {
        if ((string) ($product->publish_source ?? '') !== 'public') {
            abort(404);
        }
    }

    private function flushWebsiteProductCaches(): void
    {
        $locales = array_keys((array) config('laravellocalization.supportedLocales', []));
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

    private function notifyPublisher(Product $product, bool $approved): void
    {
        $app = (string) config('app.name', 'المتجر');
        $name = (string) ($product->name ?? '');
        $trackUrl = route('public.products.track', ['slug' => $product->slug]);
        $publishUrl = route('public.products.create');
        $note = trim((string) ($product->review_note ?? ''));
        $noteLine = $note !== '' ? ("\nملاحظة الإدارة: " . mb_substr($note, 0, 250)) : '';

        $subject = $approved ? 'تم قبول طلب نشر حسابك ✅' : 'تم رفض طلب نشر حسابك ❌';

        if ($approved) {
            $productUrl = route('website.product.show', $product);
            $text = trim(
                "{$app}\n" .
                "تم قبول طلب نشر حسابك ✅\n" .
                ($name !== '' ? "اسم الحساب: {$name}\n" : '') .
                "رابط الإعلان: {$productUrl}\n" .
                "متابعة الطلب: {$trackUrl}" .
                $noteLine
            );
        } else {
            $reasons = (array) ($product->review_reject_reasons ?? []);
            $reasonsLines = '';
            foreach ($reasons as $r) {
                $r = trim((string) $r);
                if ($r === '') continue;
                $reasonsLines .= "- {$r}\n";
            }
            $reasonsBlock = trim($reasonsLines) !== '' ? ("\nالأسباب:\n" . trim($reasonsLines)) : '';

            $text = trim(
                "{$app}\n" .
                "تم رفض طلب نشر حسابك ❌\n" .
                ($name !== '' ? "اسم الحساب: {$name}\n" : '') .
                $reasonsBlock .
                $noteLine . "\n" .
                "أعد نشره بعد تعديل الشروط الصحيحة من هنا: {$publishUrl}\n" .
                "متابعة الطلب: {$trackUrl}"
            );
        }

        // WhatsApp (optional)
        if ((bool) config('services.wasender.enabled', false) && (bool) config('services.wasender.notify_customers', true)) {
            $to = WhatsAppNumber::normalize((string) ($product->client_number ?? ''));
            if ($to !== '') {
                WasenderNotifier::sendAfterCommit($to, $text);
            }
        }

        // Email (optional additional channel)
        if ((bool) config('services.email_notify.enabled', false) && (bool) config('services.email_notify.notify_customers', true)) {
            $email = trim((string) ($product->client_email ?? ''));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                EmailNotifier::sendAfterCommit($email, $subject, $text);
            }
        }
    }

    public function destroy(Request $request, Product $product)
    {
        $this->ensurePublic($product);

        $request->validate([
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $this->deletePublicProductRequest($product);

        return redirect()
            ->route('admin.public_products.index', ['status' => 'all'])
            ->with('success', 'تم حذف الطلب ✅');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1'],
            'confirm' => ['required', 'in:DELETE'],
        ]);

        $ids = array_values(array_unique(array_map('intval', (array) ($data['ids'] ?? []))));
        if (count($ids) === 0) {
            return back()->withErrors(['error' => 'اختر عنصر واحد على الأقل.']);
        }

        $q = Product::query()
            ->whereIn('id', $ids)
            ->where('publish_source', 'public')
            ->whereNull('service_type');

        $found = $q->get();
        if ($found->isEmpty()) {
            return back()->withErrors(['error' => 'لم يتم العثور على الطلبات المحددة.']);
        }

        $deleted = 0;

        DB::beginTransaction();
        try {
            foreach ($found as $product) {
                try {
                    $this->deletePublicProductRequest($product, false);
                    $deleted++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->flushWebsiteProductCaches();

        return redirect()
            ->route('admin.public_products.index', ['status' => 'all'])
            ->with('success', "تم حذف {$deleted} طلب(ات) ✅");
    }

    private function deletePublicProductRequest(Product $product, bool $flushCaches = true): void
    {
        $this->ensurePublic($product);

        try { $product->loadMissing(['media', 'translations']); } catch (\Throwable $e) {}

        // Remove uploaded files + media records (best-effort)
        try {
            if (method_exists($product, 'media')) {
                $items = $product->media()->get();
                foreach ($items as $m) {
                    $collection = (string) ($m->collection_name ?? '');
                    $file = (string) ($m->file_name ?? '');
                    $disk = (string) ($m->disk ?? 'direct_public');
                    if ($file === '') {
                        $m->delete();
                        continue;
                    }

                    // Determine base folder by collection name used by upload trait.
                    $baseFolder = null;
                    if ($collection === 'product' || $collection === 'default') {
                        $baseFolder = 'product';
                    } elseif ($collection === 'gallery') {
                        $baseFolder = 'product/gallery';
                    }

                    if ($baseFolder) {
                        $base = "uploads/{$baseFolder}";
                        $useStorage = $disk === 'storage_public';
                        try {
                            // UploadMedia2::deleteFile()
                            $product->deleteFile($base, $file, $useStorage);
                        } catch (\Throwable $e) {}
                    }

                    $m->delete();
                }
            }
        } catch (\Throwable $e) {
            // continue deletion
        }

        // Clean pivots just in case
        try { $product->sections()->detach(); } catch (\Throwable $e) {}
        try { $product->tags()->detach(); } catch (\Throwable $e) {}

        $product->delete();

        if ($flushCaches) {
            $this->flushWebsiteProductCaches();
        }
    }
}

