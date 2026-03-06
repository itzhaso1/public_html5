<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DiamondCode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DiamondCodeController extends Controller
{
    private function forgetCodesPageCache(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.codes.$locale");
            // Codes products may appear inside home sections; refresh thumbnails immediately.
            Cache::forget("home.sections.$locale");
        }
    }

    public function index()
    {
        $status = request()->query('status', 'available');

        $codes = DiamondCode::query()
            ->with(['product', 'user'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(50);

        return view('dashboard.admin.diamond_codes.index', [
            'pageTitle' => 'أكواد ملابس',
            'codes' => $codes,
            'status' => $status,
        ]);
    }

    public function create()
    {
        $products = Product::query()
            ->where('service_type', 'codes')
            ->with('translations')
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard.admin.diamond_codes.create', [
            'pageTitle' => 'إضافة كود',
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_mode' => ['required', Rule::in(['existing', 'new'])],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where(fn ($q) => $q->where('service_type', 'codes'))],
            'product_name' => ['nullable', 'string', 'max:255'],
            'product_price' => ['nullable', 'numeric', 'min:0'],
            'is_lucky_draw_codes' => ['nullable', 'boolean'],
            // One code (single) OR multiple codes (one per line)
            'code' => ['nullable', 'string', 'max:500'],
            'codes' => ['nullable', 'string', 'max:20000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'luck_weight' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'luck_label' => ['nullable', 'string', 'max:100'],
        ]);

        // Resolve / create product
        if (($data['product_mode'] ?? null) === 'new') {
            $name = trim((string) ($data['product_name'] ?? ''));
            if ($name === '') {
                return back()->withErrors(['product_name' => 'اسم المنتج مطلوب.'])->withInput();
            }

            $categoryId = \DB::table('categories')->value('id');
            $typeId = \DB::table('types')->value('id');
            if (! $categoryId) {
                return back()->withErrors(['product_name' => 'لا يوجد أقسام (Categories) في النظام. أضف قسم أولاً.'])->withInput();
            }

            $baseSlug = Str::slug($name) ?: ('codes-'.time());
            $slug = $baseSlug;
            $i = 1;
            while (Product::query()->where('slug', $slug)->exists()) {
                $i++;
                $slug = $baseSlug.'-'.$i;
            }

            $product = Product::create([
                'slug' => $slug,
                'type' => 'simple',
                'category_id' => $categoryId,
                'type_id' => $typeId ?: null,
                'service_type' => 'codes',
                'is_lucky_draw_codes' => $request->boolean('is_lucky_draw_codes'),
                'price' => $data['product_price'] ?? 0,
                'price_before_discount' => null,
                'stock' => 9999,
                'sku' => 'CODES-'.time(),
                'featured' => false,
                'status' => 'published',
                'published_at' => now(),
            ]);

            \DB::table('product_translations')->updateOrInsert(
                ['product_id' => $product->id, 'locale' => 'ar'],
                ['name' => $name, 'description' => $name]
            );
            \DB::table('product_translations')->updateOrInsert(
                ['product_id' => $product->id, 'locale' => 'en'],
                ['name' => $name, 'description' => $name]
            );

            // Ensure website codes page updates immediately.
            $this->forgetCodesPageCache();
        } else {
            if (empty($data['product_id'])) {
                return back()->withErrors(['product_id' => 'اختر المنتج أو أنشئ منتج جديد.'])->withInput();
            }

            $product = Product::findOrFail($data['product_id']);
        }

        $singleCode = trim((string) ($data['code'] ?? ''));
        $bulkCodesText = trim((string) ($data['codes'] ?? ''));
        $luckWeight = (int) ($data['luck_weight'] ?? 1);
        if ($luckWeight <= 0) $luckWeight = 1;
        $luckLabel = trim((string) ($data['luck_label'] ?? ''));
        $luckLabel = $luckLabel !== '' ? $luckLabel : null;

        if ($singleCode === '' && $bulkCodesText === '') {
            return back()->withErrors(['code' => 'ضع كود واحد أو مجموعة أكواد (كل كود بسطر).'])->withInput();
        }

        Storage::disk('public')->makeDirectory('diamond-codes');

        // Bulk mode
        if ($bulkCodesText !== '') {
            $lines = preg_split("/\\r\\n|\\r|\\n/", $bulkCodesText) ?: [];
            $codes = [];
            foreach ($lines as $line) {
                $c = trim($line);
                if ($c !== '') {
                    $codes[] = $c;
                }
            }

            $codes = array_values(array_unique($codes));
            if (count($codes) === 0) {
                return back()->withErrors(['codes' => 'لا يوجد أكواد صالحة داخل النص.'])->withInput();
            }

            $existing = DiamondCode::query()
                ->whereIn('code', $codes)
                ->pluck('code')
                ->all();

            if (! empty($existing)) {
                return back()->withErrors(['codes' => 'بعض الأكواد موجودة مسبقًا: '.implode(', ', array_slice($existing, 0, 5)).(count($existing) > 5 ? '...' : '')])->withInput();
            }

            $images = $request->file('images', []);

            foreach ($codes as $idx => $codeValue) {
                $imagePath = null;
                if (isset($images[$idx]) && $images[$idx] && $images[$idx]->isValid()) {
                    $imagePath = Storage::disk('public')->putFile('diamond-codes', $images[$idx]);
                }

                DiamondCode::create([
                    'product_id' => $product->id,
                    'code' => $codeValue,
                    'image_path' => $imagePath,
                    'luck_weight' => $luckWeight,
                    'luck_label' => $luckLabel,
                    'status' => 'available',
                ]);
            }

            $this->forgetCodesPageCache();
            return redirect()->route('admin.diamond_codes.index')->with('success', 'تمت إضافة '.count($codes).' كود بنجاح.');
        }

        // Single mode
        if (DiamondCode::query()->where('code', $singleCode)->exists()) {
            return back()->withErrors(['code' => 'هذا الكود موجود مسبقًا.'])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = Storage::disk('public')->putFile('diamond-codes', $request->file('image'));
        }

        DiamondCode::create([
            'product_id' => $product->id,
            'code' => $singleCode,
            'image_path' => $imagePath,
            'luck_weight' => $luckWeight,
            'luck_label' => $luckLabel,
            'status' => 'available',
        ]);

        $this->forgetCodesPageCache();
        return redirect()->route('admin.diamond_codes.index')->with('success', 'تم إضافة الكود بنجاح.');
    }

    public function image(DiamondCode $diamondCode)
    {
        abort_if(! $diamondCode->image_path, 404);
        abort_if(! Storage::disk('public')->exists($diamondCode->image_path), 404);

        return Storage::disk('public')->response($diamondCode->image_path);
    }

    public function destroy(DiamondCode $diamondCode)
    {
        if ($diamondCode->status === 'delivered') {
            return back()->withErrors(['error' => 'لا يمكن حذف كود تم تسليمه.']);
        }

        if ($diamondCode->image_path) {
            Storage::disk('public')->delete($diamondCode->image_path);
        }

        $diamondCode->delete();

        return back()->with('success', 'تم حذف الكود.');
    }

    public function updateProduct(Request $request, Product $product)
    {
        abort_unless(($product->service_type ?? null) === 'codes', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $name = trim($data['name']);
        if ($name === '') {
            return back()->withErrors(['error' => 'اسم المنتج مطلوب.'])->withInput();
        }

        $product->update([
            'price' => (float) $data['price'],
        ]);

        DB::table('product_translations')->updateOrInsert(
            ['product_id' => $product->id, 'locale' => 'ar'],
            ['name' => $name, 'description' => $name]
        );
        DB::table('product_translations')->updateOrInsert(
            ['product_id' => $product->id, 'locale' => 'en'],
            ['name' => $name, 'description' => $name]
        );

        $this->forgetCodesPageCache();

        return back()->with('success', 'تم تحديث المنتج بنجاح.');
    }

    public function destroyProduct(Product $product)
    {
        abort_unless(($product->service_type ?? null) === 'codes', 404);

        $hasManualPayments = DB::table('manual_payment_requests')
            ->where('product_id', $product->id)
            ->exists();

        if ($hasManualPayments) {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا المنتج لوجود طلبات دفع مرتبطة به.']);
        }

        $hasDeliveredCodes = DiamondCode::query()
            ->where('product_id', $product->id)
            ->where('status', 'delivered')
            ->exists();

        if ($hasDeliveredCodes) {
            return back()->withErrors(['error' => 'لا يمكن حذف هذا المنتج لأن هناك أكواد تم تسليمها بالفعل.']);
        }

        // Remove any stored images for available codes before deleting (to avoid orphaned files)
        $paths = DiamondCode::query()
            ->where('product_id', $product->id)
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->all();

        foreach ($paths as $p) {
            Storage::disk('public')->delete($p);
        }

        // Deleting the product will cascade delete available diamond codes (FK cascade)
        $product->deleteExistingMedia('product', $product, null, 'media', true, 'product');
        $product->delete();

        $this->forgetCodesPageCache();

        return redirect()->route('admin.diamond_codes.create')->with('success', 'تم حذف المنتج بنجاح.');
    }
}

