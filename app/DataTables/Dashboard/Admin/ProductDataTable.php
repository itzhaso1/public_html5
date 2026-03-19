<?php
 
namespace App\DataTables\Dashboard\Admin;
 
use App\DataTables\Base\BaseDataTable;
use App\Models\Product;
use App\Support\Shop2TopUp\Shop2TopUpBundle;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Utilities\Request as DataTableRequest;
 
class ProductDataTable extends BaseDataTable {
    public function __construct(DataTableRequest $request)
    {
        parent::__construct(new Product);
        $this->request = $request;
    }
 
    public function dataTable($query): EloquentDataTable {
        $table = (new EloquentDataTable($query))
            ->addColumn('select', function (Product $product) {
                return '<input type="checkbox" class="form-check-input js-product-select" value="' . (int) $product->id . '" aria-label="Select ' . (int) $product->id . '">';
            })
            ->addColumn('action', function (Product $product) {
                return view('dashboard.admin.products.btn.actions', compact('product'));
            })
            ->editColumn('product', function (Product $product) {
                $url = '';
                try {
                    $url = (string) $product->getMediaUrl('product', $product, null, 'media', 'product');
                } catch (\Throwable $e) {
                    $url = '';
                }

                if ($url === '') {
                    try { $url = (string) $product->getImageUrl(); } catch (\Throwable $e) {}
                }
                if ($url === '') {
                    $url = asset('img/قريبا.jpg');
                }

                $alt = e((string) ($product?->name ?? ''));

                return '<img src="' . e($url) . '" class="img-fluid" alt="' . $alt . '" style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 5px;"/>';
            })
            ->addColumn('category', function (Product $product) {
                return $product?->category?->name;
            })
            ->addColumn('brand', function (Product $product) {
                return $product?->brand?->name;
            })
            ->addColumn('tags', function (Product $product) {
                if ($product->tags->isEmpty()) return '<span class="text-muted">لا يوجد</span>';
                $badges = '';
                foreach ($product->tags as $tag) {
                    $badges .= '<span class="badge bg-info text-dark me-1">' . $tag->name . '</span>';
                }
                return $badges;
            })
            ->addColumn('itemID', function (Product $product) {
                if (($product->service_type ?? null) !== 'gems') {
                    return '<span class="text-muted">—</span>';
                }
                $raw = trim((string) ($product->itemID ?? ''));
                if ($raw === '') {
                    return '<span class="badge bg-light-danger text-danger">غير مربوط</span>';
                }

                $ids = [];
                $isBundle = false;
                $pretty = $raw;
                try {
                    $ids = Shop2TopUpBundle::parseOfferIds($raw);
                    $isBundle = count($ids) > 1;
                    $pretty = $isBundle ? implode(' + ', $ids) : (string) ($ids[0] ?? $raw);
                } catch (\Throwable $e) {
                    $ids = [];
                    $isBundle = false;
                    $pretty = $raw;
                }

                $html = '<div class="font-monospace small" style="white-space:nowrap">'
                    . e($raw)
                    . '</div>';

                if ($isBundle) {
                    $html .= '<div class="text-muted small" style="white-space:nowrap">'
                        . 'bundle: ' . e($pretty)
                        . '</div>';
                }

                return $html;
            })
            ->editColumn('created_at', function (Product $product) {
                return $this->formatBadge($this->formatDate($product->created_at));
            })
            ->editColumn('updated_at', function (Product $product) {
                return $this->formatBadge($this->formatDate($product->updated_at));
            })
            ->rawColumns(['select','category','tags','types','action', 'created_at', 'updated_at', 'product', 'itemID']);

        // Custom, reliable search for product lists (name is translatable).
        // Defensive guards: some deployments may miss optional columns.
        $hasSlug = false;
        $hasSku = false;
        $hasItemId = false;
        $hasTranslationName = false;
        try {
            if (Schema::hasTable('products')) {
                $hasSlug = Schema::hasColumn('products', 'slug');
                $hasSku = Schema::hasColumn('products', 'sku');
                $hasItemId = Schema::hasColumn('products', 'itemID');
            }
            if (Schema::hasTable('product_translations')) {
                $hasTranslationName = Schema::hasColumn('product_translations', 'name')
                    && Schema::hasColumn('product_translations', 'product_id');
            }
        } catch (\Throwable $e) {
            $hasSlug = false;
            $hasSku = false;
            $hasItemId = false;
            $hasTranslationName = false;
        }

        // Force stable ordering to avoid ordering by translatable columns.
        $table->order(function (QueryBuilder $query) {
            $query->orderByDesc('products.id');
        });

        // Override global search to avoid "products.name" SQL errors (name is translatable).
        $table->filter(function (QueryBuilder $query) use ($hasSlug, $hasSku, $hasItemId, $hasTranslationName) {
            $search = trim((string) data_get(request()->input('search'), 'value', ''));
            if ($search === '') return;

            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';

            $query->where(function (QueryBuilder $q) use ($search, $like, $hasSlug, $hasSku, $hasItemId) {
                if (ctype_digit($search)) {
                    $q->orWhere('products.id', (int) $search);
                }

                if ($hasSlug) {
                    $q->orWhere('products.slug', 'like', $like);
                }
                if ($hasSku) {
                    $q->orWhere('products.sku', 'like', $like);
                }
                if ($hasItemId) {
                    $q->orWhere('products.itemID', 'like', $like);
                }

                if ($hasTranslationName) {
                    $q->orWhereExists(function ($sq) use ($like) {
                        $sq->select(DB::raw(1))
                            ->from('product_translations as pt')
                            ->whereColumn('pt.product_id', 'products.id')
                            ->where('pt.name', 'like', $like);
                    });
                }
            });
        }, true);

        return $table;
    }
 
    public function query(): QueryBuilder
    {
        $query = Product::with(['media','category', 'brand', 'tags'])->orderByDesc('id');

        // Important: DataTables loads data via AJAX; don't rely on ad-hoc query params.
        // Instead, infer the group from the current route name.
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

        if ($group === 'accounts') {
            // المنتجات العادية (حسابات): ليست شحن وليست أكواد
            $query->whereNull('service_type');
        } elseif ($group === 'charge') {
            // باقات الشحن (جواهر)
            $query->where('service_type', 'gems');
        } elseif ($group === 'codes') {
            // منتجات الأكواد
            $query->where('service_type', 'codes');
        }

        return $query;
    }

    protected function getParameters(): array
    {
        $params = parent::getParameters();

        // Stable default order (avoid ordering by translatable "name" column)
        $params['order'] = [[1, 'desc']];

        // Bigger default page size for products lists (requested).
        $routeName = request()->route()?->getName();
        if (in_array($routeName, ['admin.products.index', 'admin.products.accounts', 'admin.products.charge', 'admin.products.codes'], true)) {
            $params['pageLength'] = 50;
            $params['lengthMenu'] = [[10, 25, 50, 100, 200, -1], [10, 25, 50, 100, 200, 'الكل']];
        }

        return $params;
    }
 
    public function getColumns(): array
    {
        return [
            ['name' => 'select', 'data' => 'select', 'title' => '<input type="checkbox" class="form-check-input" id="products-select-all" aria-label="Select all">', 'orderable' => false, 'searchable' => false],
            ['name' => 'id', 'data' => 'id', 'title' => '#', 'orderable' => true, 'searchable' => false],
            ['name' => 'name', 'data' => 'name', 'title' => trans('dashboard/admin.product.name'), 'orderable' => false, 'searchable' => false],
            ['name' => 'product', 'data' => 'product', 'title' => 'الصوره', 'orderable' => false, 'searchable' => false],
            ['name' => 'category', 'data' => 'category', 'title' => 'التصنيف', 'orderable' => false, 'searchable' => false],
            ['name' => 'brand', 'data' => 'brand', 'title' => 'الماركه', 'orderable' => false, 'searchable' => false],
            ['name' => 'tags', 'data' => 'tags', 'title' => 'الوسوم', 'orderable' => false, 'searchable' => false],
            ['name' => 'price', 'data' => 'price', 'title' => trans('dashboard/admin.product.price')],
            ['name' => 'itemID', 'data' => 'itemID', 'title' => 'Shop2TopUp itemID', 'orderable' => false, 'searchable' => false],
            ['name' => 'created_at', 'data' => 'created_at', 'title' => trans('dashboard/general.created_at'), 'orderable' => false, 'searchable' => false],
            ['name' => 'updated_at', 'data' => 'updated_at', 'title' => trans('dashboard/general.updated_at'), 'orderable' => false, 'searchable' => false],
            ['name' => 'action', 'data' => 'action', 'title' => trans('dashboard/general.actions'), 'orderable' => false, 'searchable' => false],
        ];
    }
}