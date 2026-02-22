<?php
 
namespace App\Http\Controllers\Dashboard;
 
use App\DataTables\Dashboard\Admin\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Services\Contracts\ProductInterface;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Services\Services\ERP\ERPService;
use Illuminate\Support\Str; // مكتبة للنصوص
use Illuminate\Support\Facades\Cache;
use App\Services\Integrations\Shop2TopUp\Shop2TopUpService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
 
class ProductController extends Controller
{
    protected ERPService $erpService;
 
    public function __construct(ERPService $erpService, protected ProductDataTable $productDataTable, protected ProductInterface $productInterface)
    {
        $this->productInterface = $productInterface;
        $this->productDataTable = $productDataTable;
        $this->erpService = $erpService;
    }
 
    // --- الدوال الأساسية ---
    public function index(ProductDataTable $productDataTable) { return $this->productInterface->index($this->productDataTable); }
    public function accounts(ProductDataTable $productDataTable) { return $this->productInterface->index($this->productDataTable); }
    public function charge(ProductDataTable $productDataTable) { return $this->productInterface->index($this->productDataTable); }
    public function codes(ProductDataTable $productDataTable) { return $this->productInterface->index($this->productDataTable); }
    public function create() { return $this->productInterface->create(); }
    public function store(Request $request) { return $this->productInterface->store($request); }
    public function edit(Product $product) { return $this->productInterface->edit($product); }
    public function update(Request $request, Product $product) { return $this->productInterface->update($request, $product); }
    public function destroy(Product $product) { return $this->productInterface->destroy($product); }
    
    public function import(Request $request) {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        Excel::import(new ProductsImport, $request->file('file'));
        return response()->json(['message' => 'تم حفظ المنتج بنجاح ✅']);
    }
 
    public function testConnection() {
        $result = $this->erpService->testConnection();
        return $result['success'] ? back()->with('success', $result['message']) : back()->with('error', $result['message']);
    }
 
    public function exportProductsToERP() {
        $products = Product::with(['translations', 'category.translations', 'type.translations'])->get();
        $formattedProducts = $this->erpService::formatProductsForERP($products);
        $result = $this->erpService->sendProducts($formattedProducts);
        return $result['success'] ? back()->with('success', 'تم الإرسال بنجاح') : back()->with('error', 'فشل الإرسال');
    }
 
    public function show($id) {
        return redirect()->route('admin.products.index');
    }
 
    // ==========================================
    // ✅ (الجديد) قسم إضافة منتجات الشحن
    // ==========================================
    public function createChargeProduct()
    {
        return view('dashboard.admin.products.create_charge');
    }
 
    public function storeChargeProduct(Request $request)
    {
        // التحقق: نطلب أن تكون القيمة إما gems أو codes
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'points_price' => 'nullable|integer|min:0',
            // NOTE: this field is actually the service type (gems/codes)
            'type_id' => 'required|in:gems,codes',
        ]);
 
        try {
            // نأخذ أول قسم ونوع موجودين لتجنب الأخطاء
            $categoryId = \DB::table('categories')->value('id');
            $typeId = \DB::table('types')->value('id');

            if (! $categoryId) {
                return redirect()->back()->withErrors(['error' => 'لا يوجد تصنيف (Category) في النظام. أضف تصنيف واحد على الأقل ثم أعد المحاولة.']);
            }

            $baseSlug = Str::slug($request->name) ?: ('charge-'.time());
            $slug = $baseSlug.'-'.Str::lower(Str::random(6)).'-'.time();

            // SKU should be unique enough (even on fast repeated submits)
            $sku = 'CHG-'.Str::lower(Str::random(6)).'-'.time();

            $id = \DB::table('products')->insertGetId([
                'slug'         => $slug,
                'type'         => 'simple',
                'category_id'  => $categoryId,
                'type_id'      => $typeId ?: null,
                'service_type' => $request->type_id, // gems/codes
                'price'        => $request->price,
                'points_price' => $request->filled('points_price') ? (int) $request->points_price : null,
                'stock'        => 9999,
                'sku'          => $sku,
                'status'       => 'published',
                'published_at' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
 
            // حفظ الاسم في الترجمة
            try {
                \DB::table('product_translations')->insert([
                    ['product_id' => $id, 'locale' => 'ar', 'name' => $request->name, 'description' => $request->name],
                    ['product_id' => $id, 'locale' => 'en', 'name' => $request->name, 'description' => $request->name],
                ]);
            } catch (\Exception $e) {}

            // مسح كاش صفحات الشحن/الأكواد حتى تظهر الباقات مباشرة
            $locales = array_keys(config('laravellocalization.supportedLocales', []));
            if (empty($locales)) {
                $locales = ['ar', 'en'];
            }

            foreach ($locales as $locale) {
                if ($request->type_id === 'gems') {
                    Cache::forget("diamonds.charge.$locale");
                }
                if ($request->type_id === 'codes') {
                    Cache::forget("diamonds.codes.$locale");
                }
            }
 
            return redirect()->back()->with('success', 'تم إضافة الباقة بنجاح ✅ وستظهر مباشرة في القسم.');
 
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function bulkDeleteByGroup(Request $request, string $group)
    {
        $group = strtolower(trim($group));
        if (!in_array($group, ['accounts', 'charge', 'codes'], true)) {
            abort(404);
        }

        // Safety: only delete products that are not tied to orders/carts/manual payments.
        $query = Product::query()->with('media');

        if ($group === 'accounts') {
            $query->whereNull('service_type');
        } elseif ($group === 'charge') {
            $query->where('service_type', 'gems');
        } else { // codes
            $query->where('service_type', 'codes');
        }

        // Never delete products that have manual payment requests (would cascade delete requests).
        $query->whereDoesntHave('manualPaymentRequests');

        // Avoid deleting products currently in carts or referenced in orders (conservative).
        // (Order items are nullOnDelete, but we keep them for safety.)
        $query->whereNotIn('id', function ($q) {
            $q->select('product_id')->from('carts')->whereNotNull('product_id');
        });
        $query->whereNotIn('id', function ($q) {
            $q->select('product_id')->from('order_items')->whereNotNull('product_id');
        });

        if ($group === 'codes') {
            // Avoid deleting any codes products that already delivered codes.
            $query->whereNotIn('id', function ($q) {
                $q->select('product_id')->from('diamond_codes')->where('status', 'delivered');
            });
        }

        $toDeleteCount = (clone $query)->count();
        if ($toDeleteCount === 0) {
            return back()->with('success', 'لا يوجد منتجات يمكن حذفها ضمن هذا القسم (كلها مرتبطة بطلبات/سلة/مبيعات).');
        }

        $deleted = 0;

        // Delete in chunks to keep memory low.
        $query->orderBy('id')->chunkById(50, function ($products) use (&$deleted) {
            foreach ($products as $product) {
                try {
                    // Delete media files/records (main + gallery)
                    if (method_exists($product, 'deleteExistingMedia')) {
                        $product->deleteExistingMedia('product', $product, null, 'media', true, 'product');
                        $product->deleteExistingMedia('gallery', $product, null, 'media', true, 'gallery');
                    }
                    $product->delete();
                    $deleted++;
                } catch (\Throwable $e) {
                    // Skip failures; continue.
                    report($e);
                }
            }
        });

        // Clear relevant caches
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("home.products.$locale");
            Cache::forget("home.sections.$locale");
            Cache::forget("diamonds.charge.$locale");
            Cache::forget("diamonds.codes.$locale");
        }

        return back()->with('success', "تم حذف {$deleted} منتج بنجاح ✅");
    }

    /**
     * Sync Shop2TopUp offers into gems products (service_type=gems).
     * Stores vendor offer id in products.itemID.
     */
    public function syncChargeOffers(Request $request)
    {
        $service = new Shop2TopUpService();
        $result = $service->getOffers();

        if (! ($result['success'] ?? false)) {
            $msg = $result['msg'] ?? 'فشل جلب العروض من المزود';
            return redirect()->back()->withErrors(['error' => 'Shop2TopUp: ' . $msg]);
        }

        $offers = (array) ($result['offers'] ?? []);
        if (count($offers) === 0) {
            return redirect()->back()->withErrors(['error' => 'Shop2TopUp: لا توجد عروض في الرد']);
        }

        // Filter: Global offers only (name contains "Global")
        if ($request->boolean('only_global')) {
            $offers = array_values(array_filter($offers, function ($offer) {
                $name = (string) ($offer['name'] ?? '');
                return stripos($name, 'global') !== false;
            }));
            if (count($offers) === 0) {
                return redirect()->back()->withErrors(['error' => 'Shop2TopUp: لا توجد عروض Global في الرد']);
            }
        }

        $categoryId = DB::table('categories')->value('id');
        $typeId = DB::table('types')->value('id');
        if (! $categoryId) {
            return redirect()->back()->withErrors(['error' => 'لا يوجد تصنيف (Category) في النظام. أضف تصنيف واحد على الأقل ثم أعد المحاولة.']);
        }

        $created = 0;
        $updated = 0;

        foreach ($offers as $offer) {
            $itemId = (int) ($offer['itemId'] ?? 0);
            $name = trim((string) ($offer['name'] ?? ''));
            $price = (string) ($offer['price'] ?? '');

            if ($itemId <= 0 || $name === '' || $price === '') {
                continue;
            }

            $slug = 's2tu-gems-' . $itemId;
            $sku = 'S2TU-GEMS-' . $itemId;

            /** @var \App\Models\Product|null $product */
            $product = Product::query()
                ->where('service_type', 'gems')
                ->where('itemID', (string) $itemId)
                ->first();

            $payload = [
                'slug' => $slug,
                'type' => 'simple',
                'category_id' => $categoryId,
                'type_id' => $typeId ?: null,
                'service_type' => 'gems',
                'price' => (float) $price,
                'stock' => 9999,
                'sku' => $sku,
                'status' => 'published',
                'published_at' => now(),
                'itemID' => (string) $itemId,
            ];

            if ($product) {
                $product->update($payload);
                $updated++;
            } else {
                $product = Product::create($payload);
                $created++;
            }

            // Translations
            foreach (['ar', 'en'] as $locale) {
                DB::table('product_translations')->updateOrInsert(
                    ['product_id' => $product->id, 'locale' => $locale],
                    ['name' => $name, 'description' => $name]
                );
            }
        }

        // clear gems page cache
        $locales = array_keys(config('laravellocalization.supportedLocales', []));
        if (empty($locales)) $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            Cache::forget("diamonds.charge.$locale");
        }

        return redirect()->back()->with('success', "تمت المزامنة ✅ (جديد: $created ، تحديث: $updated)");
    }

    public function syncChargeOffersGlobal(Request $request)
    {
        $request->merge(['only_global' => true]);
        return $this->syncChargeOffers($request);
    }

    public function deleteNonGlobalChargeOffers()
    {
        // Delete only synced gems offers (itemID set) that are NOT Global and safe to delete.
        $query = Product::query()
            ->where('service_type', 'gems')
            ->whereNotNull('itemID')
            ->where('itemID', '!=', '')
            ->whereDoesntHave('manualPaymentRequests')
            ->whereNotIn('id', function ($q) {
                $q->select('product_id')->from('carts')->whereNotNull('product_id');
            })
            ->whereNotIn('id', function ($q) {
                $q->select('product_id')->from('order_items')->whereNotNull('product_id');
            })
            ->whereHas('translations', function ($q) {
                $q->where('name', 'not like', '%Global%');
            });

        $count = (clone $query)->count();
        if ($count === 0) {
            return back()->with('success', 'لا يوجد باقات شحن غير Global قابلة للحذف.');
        }

        $deleted = 0;
        $query->orderBy('id')->chunkById(50, function ($products) use (&$deleted) {
            foreach ($products as $product) {
                try {
                    if (method_exists($product, 'deleteExistingMedia')) {
                        $product->deleteExistingMedia('product', $product, null, 'media', true, 'product');
                        $product->deleteExistingMedia('gallery', $product, null, 'media', true, 'gallery');
                    }
                    $product->delete();
                    $deleted++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        foreach (['ar', 'en'] as $locale) {
            Cache::forget("diamonds.charge.$locale");
        }

        return back()->with('success', "تم حذف {$deleted} باقة غير Global ✅");
    }

    public function chargeWalletBalance()
    {
        $service = new Shop2TopUpService();
        $result = $service->getBalance();

        if (! ($result['success'] ?? false)) {
            $msg = $result['msg'] ?? 'فشل جلب الرصيد';
            return redirect()->back()->withErrors(['error' => 'Shop2TopUp: ' . $msg]);
        }

        $balance = $result['balance'] ?? null;
        if ($balance === null || $balance === '') {
            return redirect()->back()->withErrors(['error' => 'Shop2TopUp: لم يتم إرجاع الرصيد']);
        }

        return redirect()->back()->with('success', 'رصيد المحفظة: $' . $balance);
    }
    
}