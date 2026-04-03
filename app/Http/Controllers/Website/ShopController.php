<?php
 
namespace App\Http\Controllers\Website;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
 
class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['translations', 'media'])
            ->websiteVisible();
 
        // ====================================================
        // ✅ إخفاء منتجات الشحن الجديدة من المتجر الرئيسي
        // ====================================================
        $products->accountsOnly();
        // ====================================================
 
        if ($request->filled('category_id')) {
            $categoryId = $request->category_id;
            $baseProductsCount = Product::query()->where('category_id', $categoryId)->count();
            if ($baseProductsCount <= 3) {
                $subCategoryIds = Category::where('parent_id', $categoryId)->pluck('id')->toArray();
                $products->whereIn('category_id', array_merge([$categoryId], $subCategoryIds));
            } else {
                $products->where('category_id', $categoryId);
            }

            $subcategories = Cache::remember("shop.subcategories.$categoryId", 60 * 10, function () use ($categoryId) {
                return Category::query()
                    ->where('parent_id', $categoryId)
                    ->with(['translations', 'media'])
                    ->get();
            });
        } else {
            $subcategories = Cache::remember('shop.subcategories.all', 60 * 10, function () {
                return Category::query()
                    ->with(['translations', 'media'])
                    ->get();
            });
        }

        if ($request->filled('brand_id')) {
            $products->where('brand_id', $request->brand_id);
        }

        if ($request->filled('min_price')) {
            $products->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $products->where('price', '<=', $request->max_price);
        }

        $products = $products->latest()->paginate(12);

        $categories = Cache::remember('shop.categories', 60 * 30, function () {
            return Category::query()->with('translations')->get();
        });

        $brands = Cache::remember('shop.brands', 60 * 30, function () {
            return Brand::query()->get();
        });

        $pageTitle = trans('site/site.shop');
        // return products;
        return view('website.pages.shop', compact('products', 'categories', 'brands', 'pageTitle', 'subcategories'))->with([
            'breadcrumbs' => [
                ['title' => trans('site/site.shop')],
            ]
        ]);
    }
 
    public function show($id)
    {
        $product = Product::with(['translations', 'media', 'category', 'brand', 'type', 'tags', 'sections'])->findOrFail($id);
        
        $relatedProducts = Product::with(['translations', 'media'])
        ->where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->where('status', 'published')
        // ✅ إخفاء منتجات الشحن من المقترحات أيضاً
        ->accountsOnly()
        ->take(10)
        ->get();
 
        return view('website.pages.products_show', compact('product', 'relatedProducts'))->with(
                [
                    'breadcrumbs' => [
                        ['title' => trans('site/site.shop'), 'url' => route('shop.index')],
                        ['title' => $product->name]
                ]
            ]
        );
    }
 
    public function unlockClientNumber(Request $request, $productId)
    {
        try {
            // ✅ التحقق من المدخلات (بدون تقييد بنوع string في البداية)
            $request->validate([
                'client_password' => 'required',
            ]);
 
            // 🔒 قراءة كلمة السر من .env مع قيمة افتراضية للتجربة
            $secret = env('CLIENT_NUMBER_PASSWORD', '217121');
 
            // 🔎 التأكد من وجود المنتج
            $product = Product::find($productId);
            if (!$product) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '🚫 المنتج غير موجود'
                    ], 404);
                }
                return back()->withErrors(['error' => '🚫 المنتج غير موجود']);
            }
 
            // ❌ تحقق من كلمة السر (مقارنة صارمة للنصوص لتجنب مشاكل الأرقام)
            $inputPassword = trim((string)$request->input('client_password'));
            $envSecret = trim((string)$secret);
 
            if ($inputPassword !== $envSecret) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ كلمة السر غير صحيحة'
                    ], 401);
                }
                return back()->with('client_unlock_error', '❌ كلمة السر غير صحيحة');
            }
 
            // ✅ خزّن حالة الفتح في الجلسة
            session()->put('unlocked_client_' . $product->id, true);
 
            // 🚀 رد ناجح عبر AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'client_number' => $product->client_number ?? '—'
                ]);
            }
 
            // 🔁 في حال مو AJAX (عادي)
            return back()->with('client_unlock_success', '✅ تم فتح رقم العميل بنجاح');
 
        } catch (\Throwable $e) {
            // 💥 التقاط أي خطأ غير متوقع
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ حدث خطأ أثناء معالجة الطلب',
                    'error' => $e->getMessage(),
                ], 500);
            }
 
            return back()->withErrors(['error' => '⚠️ حدث خطأ: ' . $e->getMessage()]);
        }
    }
}