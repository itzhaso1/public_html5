<?php
 
namespace App\Http\Controllers\Website;
 
use App\Http\Controllers\Controller;
use App\Models\{Category,Slider, Section, Product, Setting};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
 
class WebsiteController extends Controller
{
    public function __invoke()
    {
        $locale = app()->getLocale();

        $sliders = Cache::remember("home.sliders.$locale", 60 * 5, function () {
            return Slider::with(['translations', 'media'])->latest()->get();
        });

        // `$categories` is shared via View Composer for website.*
        $categories = Cache::get("website.categories.menu.$locale")
            ?? Category::with(['translations', 'media', 'children.translations'])
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->get();

        $featuredCategories = Cache::remember("home.featured_categories.$locale", 60 * 5, function () {
            return Category::query()
                ->whereNull('parent_id')
                ->where('status', 'active')
                ->with(['translations', 'media'])
                ->get();
        });

        $categoryCount = $categories->count();
        $slidesPerView = $categoryCount < 10 ? $categoryCount : 10;
 
        return view('website.pages.home', ['pageTitle' => trans('site/site.home_page_title'),
            'categories' => $categories,
            'sliders' => $sliders,
            'featuredCategories' => $featuredCategories,
            'categoryCount' => $categoryCount,
            'slidesPerView' => $slidesPerView,
        ]);
    }

    public function freefireAccounts()
    {
        $locale = app()->getLocale();
        $data = $this->resolveAccountsData($locale);

        return view('website.pages.freefire_accounts', array_merge([
            'pageTitle' => 'حسابات فري فاير',
        ], $data));
    }
 
    public function show(Product $product)
    {
        // Hide unapproved public submissions from direct links.
        if (($product->publish_source ?? null) === 'public' && (string) ($product->status ?? 'draft') !== 'published') {
            abort(404);
        }

        // ============================================================
        // ✅ التعديل الجديد: توجيه منتجات الشحن لصفحة خاصة
        // ============================================================
        if (!empty($product->service_type)) {
            // إذا كان المنتج شحناً، نستخدم تصميماً مبسطاً
            // سنحتاج لإنشاء هذا الملف في الخطوة التالية
            return view('website.diamonds.show_charge', compact('product'));
        }
        // ============================================================
 
        $product->load(['translations', 'media', 'videos']);
        $mainImage = $product->getMediaUrl('product', $product, null, 'media', 'product');
        $galleryImages = $product->getMultipleMediaUrls('product/gallery', $product, 'media', 'gallery');
        $productVideo = $product->videos->first();
        return view('website.pages.products_show', compact('product', 'mainImage', 'galleryImages', 'productVideo'));
    }

    private function resolveAccountsData(string $locale): array
    {
        $sections = Cache::remember("home.sections.$locale", 60 * 5, function () {
            return Section::with([
                'translations',
                'products' => function ($query) {
                    $query->websiteVisible()
                        ->where('status', 'published')
                        ->whereNull('service_type')
                        ->orderByDesc('price')
                        ->orderByDesc('id');
                },
                'products.translations',
                'products.media',
                'products.codeThumbnail',
                'categories.translations',
            ])
                ->orderBy('order')
                ->get()
                ->filter(fn($section) => ($section->products?->count() ?? 0) > 0)
                ->values();
        });

        $sectionProductIds = $sections->pluck('products')->flatten()->pluck('id')->unique()->values();

        $featuredProductIds = collect();
        try {
            if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'home_featured_product_ids')) {
                $appSettings = Cache::get('app_settings') ?: Setting::query()->latest('id')->first();
                $rawIds = $appSettings?->home_featured_product_ids ?? [];
                if (is_string($rawIds)) {
                    $decoded = json_decode($rawIds, true);
                    $rawIds = is_array($decoded) ? $decoded : [];
                }
                $featuredProductIds = collect((array) $rawIds)
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();
            }
        } catch (\Throwable $e) {
            $featuredProductIds = collect();
        }

        $featuredProducts = collect();
        if ($featuredProductIds->isNotEmpty()) {
            $order = array_flip($featuredProductIds->all());
            $featuredProducts = Product::with(['translations', 'media'])
                ->websiteVisible()
                ->where('status', 'published')
                ->whereNull('service_type')
                ->whereIn('id', $featuredProductIds->all())
                ->get()
                ->sortBy(fn($p) => $order[(int) $p->id] ?? PHP_INT_MAX)
                ->values();
        }

        $excludedProductIds = $sectionProductIds
            ->merge($featuredProducts->pluck('id'))
            ->unique()
            ->values();

        $products = Cache::remember("home.products.$locale", 60 * 5, function () use ($excludedProductIds) {
            $query = Product::with(['translations', 'media'])
                ->websiteVisible()
                ->where('status', 'published')
                ->whereNull('service_type')
                ->orderByDesc('price')
                ->orderByDesc('id');

            if ($excludedProductIds->isNotEmpty()) {
                $query->whereNotIn('id', $excludedProductIds->all());
            }

            return $query->get();
        });

        return [
            'sections' => $sections,
            'featuredProducts' => $featuredProducts,
            'products' => $products,
        ];
    }
}
