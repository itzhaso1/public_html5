<?php

namespace App\Repositories;

use App\Http\Requests\MainSettingRequest;
use App\Models\{Product, Setting};
use App\Services\Contracts\MainSettingInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Session};
use App\Models\Concerns\UploadMedia2;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\DataTables\Dashboard\History\HistoryDataTable;

class MainSettingRepository implements MainSettingInterface
{
    use UploadMedia2;
    public function __construct(protected HistoryDataTable $historyDataTable)
    {
        $this->historyDataTable = $historyDataTable;
    }

    public function index() {
        $setting = Setting::with(['media'])->orderBy('created_at', 'DESC')->first();
        $logo = $setting?->getMediaUrl('setting', $setting, null, 'media', 'logo') ?? asset('assets/default/default.jpg');
        $favicon = $setting?->getMediaUrl('setting', $setting, null, 'media', 'favicon') ?? asset('assets/default/default.jpg');

        $homeQuickChargeImg = $setting?->getMediaUrl('setting', $setting, null, 'media', 'home_quick_charge') ?? null;
        $homeQuickCodesImg = $setting?->getMediaUrl('setting', $setting, null, 'media', 'home_quick_codes') ?? null;
        $homeQuickCashExchangeImg = $setting?->getMediaUrl('setting', $setting, null, 'media', 'home_quick_cash_exchange') ?? null;
        $homeQuickMoneyExchangeImg = $setting?->getMediaUrl('setting', $setting, null, 'media', 'home_quick_money_exchange') ?? null;

        $homeFeaturedProducts = collect();
        $selectedHomeFeaturedProductIds = [];
        try {
            if (Schema::hasColumn('settings', 'home_featured_product_ids')) {
                $raw = $setting?->home_featured_product_ids ?? [];
                if (is_string($raw)) {
                    $decoded = json_decode($raw, true);
                    $raw = is_array($decoded) ? $decoded : [];
                }
                $selectedHomeFeaturedProductIds = collect((array) $raw)
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                $homeFeaturedProducts = Product::query()
                    ->select(['id', 'price', 'status'])
                    ->where('status', 'published')
                    ->accountsOnly()
                    ->with(['translations'])
                    ->orderByDesc('id')
                    ->limit(500)
                    ->get();
            }
        } catch (\Throwable $e) {
            $homeFeaturedProducts = collect();
            $selectedHomeFeaturedProductIds = [];
        }

        return view('dashboard.admin.settings.index', [
            'title' => 'General Main Settings',
            'setting' => $setting,
            'logo' => $logo,
            'favicon' => $favicon,
            'homeQuickChargeImg' => $homeQuickChargeImg,
            'homeQuickCodesImg' => $homeQuickCodesImg,
            'homeQuickCashExchangeImg' => $homeQuickCashExchangeImg,
            'homeQuickMoneyExchangeImg' => $homeQuickMoneyExchangeImg,
            'homeFeaturedProducts' => $homeFeaturedProducts,
            'selectedHomeFeaturedProductIds' => $selectedHomeFeaturedProductIds,
            'accountImageSettings' => $setting,
        ]);
    }

    public function save(MainSettingRequest $request) {
        try {
            $hasHomeQuick = false;
            try {
                $hasHomeQuick = Schema::hasColumn('settings', 'home_quick_charge_title');
            } catch (\Throwable $e) {
                $hasHomeQuick = false;
            }
            $hasCashToggle = false;
            try {
                $hasCashToggle = Schema::hasColumn('settings', 'cash_exchange_enabled');
            } catch (\Throwable $e) {
                $hasCashToggle = false;
            }
            $hasMoneyToggle = false;
            $hasChargeToggle = false;
            $hasCodesToggle = false;
            $hasPublishMinGallery = false;
            $hasHomeFeaturedProducts = false;
            $hasAccountBlurControls = false;
            $hasTopAreaControls = false;
            try {
                $hasMoneyToggle = Schema::hasColumn('settings', 'money_exchange_enabled');
                $hasChargeToggle = Schema::hasColumn('settings', 'charge_enabled');
                $hasCodesToggle = Schema::hasColumn('settings', 'codes_enabled');
                $hasPublishMinGallery = Schema::hasColumn('settings', 'public_publish_min_gallery_images');
                $hasHomeFeaturedProducts = Schema::hasColumn('settings', 'home_featured_product_ids');
                $hasAccountBlurControls =
                    Schema::hasColumn('settings', 'account_name_blur_enabled')
                    && Schema::hasColumn('settings', 'account_name_blur_x_offset_from_right')
                    && Schema::hasColumn('settings', 'account_name_blur_y')
                    && Schema::hasColumn('settings', 'account_name_blur_width')
                    && Schema::hasColumn('settings', 'account_name_blur_height')
                    && Schema::hasColumn('settings', 'account_name_blur_strength')
                    && Schema::hasColumn('settings', 'account_center_blur_enabled')
                    && Schema::hasColumn('settings', 'account_center_blur_x')
                    && Schema::hasColumn('settings', 'account_center_blur_y')
                    && Schema::hasColumn('settings', 'account_center_blur_width')
                    && Schema::hasColumn('settings', 'account_center_blur_height')
                    && Schema::hasColumn('settings', 'account_center_blur_strength');
                $hasTopAreaControls =
                    Schema::hasColumn('settings', 'account_top_area_mode')
                    && Schema::hasColumn('settings', 'account_top_area_size_px')
                    && Schema::hasColumn('settings', 'account_top_area_width_px')
                    && Schema::hasColumn('settings', 'account_top_area_x_from_right_px')
                    && Schema::hasColumn('settings', 'account_top_area_blur_strength');
            } catch (\Throwable $e) {
                $hasMoneyToggle = false;
                $hasChargeToggle = false;
                $hasCodesToggle = false;
                $hasPublishMinGallery = false;
                $hasHomeFeaturedProducts = false;
                $hasAccountBlurControls = false;
                $hasTopAreaControls = false;
            }

            // Always update the latest settings row (singleton behavior).
            // Using firstOrNew([]) may update an older row while the app reads the latest.
            $setting = Setting::query()->latest('id')->first() ?? new Setting();
            $fields = [
                'email',
                'name',
                'description',
                'phone',
                'address',
                'currency',
                'loyalty_points',
                'delivery_fees',
                'version',
            ];

            if ($hasHomeQuick) {
                $fields = array_merge($fields, [
                    'home_quick_charge_title',
                    'home_quick_codes_title',
                    'home_quick_cash_exchange_title',
                    'home_quick_money_exchange_title',
                ]);
            }
            if ($hasCashToggle) {
                $fields[] = 'cash_exchange_enabled';
            }
            if ($hasMoneyToggle) {
                $fields[] = 'money_exchange_enabled';
            }
            if ($hasChargeToggle) {
                $fields[] = 'charge_enabled';
            }
            if ($hasCodesToggle) {
                $fields[] = 'codes_enabled';
            }
            if ($hasPublishMinGallery) {
                $fields[] = 'public_publish_min_gallery_images';
            }
            try {
                if (Schema::hasColumn('settings', 'merchant_usd_rate')) {
                    $fields[] = 'merchant_usd_rate';
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                if (Schema::hasColumn('settings', 'merchant_charge_discount_percent')) {
                    $fields[] = 'merchant_charge_discount_percent';
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                if (Schema::hasColumn('settings', 'point_price_sar')) {
                    $fields[] = 'point_price_sar';
                }
                if (Schema::hasColumn('settings', 'point_price_usd')) {
                    $fields[] = 'point_price_usd';
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $setting->fill($request->only($fields));
            if ($hasCashToggle) {
                // checkbox => set false when unchecked
                $setting->cash_exchange_enabled = $request->boolean('cash_exchange_enabled');
            }
            if ($hasMoneyToggle) {
                $setting->money_exchange_enabled = $request->boolean('money_exchange_enabled');
            }
            if ($hasChargeToggle) {
                $setting->charge_enabled = $request->boolean('charge_enabled');
            }
            if ($hasCodesToggle) {
                $setting->codes_enabled = $request->boolean('codes_enabled');
            }
            if ($hasPublishMinGallery) {
                $n = (int) $request->input('public_publish_min_gallery_images', 12);
                if ($n < 1) $n = 1;
                if ($n > 40) $n = 40;
                $setting->public_publish_min_gallery_images = $n;
            }
            if ($hasHomeFeaturedProducts) {
                $ids = collect((array) $request->input('home_featured_product_ids', []))
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->take(100)
                    ->values()
                    ->all();
                $setting->home_featured_product_ids = $ids;
            }

            if ($hasAccountBlurControls) {
                $setting->account_name_blur_enabled = $request->boolean('account_name_blur_enabled');
                $mode = strtolower(trim((string) $request->input('account_name_blur_mode', 'fixed')));
                if (!in_array($mode, ['fixed', 'adaptive'], true)) {
                    $mode = 'fixed';
                }
                $setting->account_name_blur_mode = $mode;
                $setting->account_name_blur_x_offset_from_right = max(0, (int) $request->input('account_name_blur_x_offset_from_right', 420));
                $setting->account_name_blur_y = max(0, (int) $request->input('account_name_blur_y', 40));
                $setting->account_name_blur_width = max(1, (int) $request->input('account_name_blur_width', 350));
                $setting->account_name_blur_height = max(1, (int) $request->input('account_name_blur_height', 100));
                $setting->account_name_blur_strength = max(1, min(100, (int) $request->input('account_name_blur_strength', 35)));

                $setting->account_center_blur_enabled = $request->boolean('account_center_blur_enabled');
                // If x/y are 0, image processor will auto-center this blur box.
                $setting->account_center_blur_x = max(0, (int) $request->input('account_center_blur_x', 0));
                $setting->account_center_blur_y = max(0, (int) $request->input('account_center_blur_y', 0));
                $setting->account_center_blur_width = max(1, (int) $request->input('account_center_blur_width', 120));
                $setting->account_center_blur_height = max(1, (int) $request->input('account_center_blur_height', 60));
                $setting->account_center_blur_strength = max(1, min(100, (int) $request->input('account_center_blur_strength', 35)));
            }

            if ($hasTopAreaControls) {
                $topMode = strtolower(trim((string) $request->input('account_top_area_mode', 'blur')));
                if (!in_array($topMode, ['blur', 'crop', 'none'], true)) {
                    $topMode = 'blur';
                }
                $setting->account_top_area_mode = $topMode;
                $setting->account_top_area_size_px = max(0, (int) $request->input('account_top_area_size_px', 35));
                $setting->account_top_area_width_px = max(0, (int) $request->input('account_top_area_width_px', 0));
                $setting->account_top_area_x_from_right_px = max(0, (int) $request->input('account_top_area_x_from_right_px', 0));
                $setting->account_top_area_blur_strength = max(1, min(100, (int) $request->input('account_top_area_blur_strength', 35)));
            }
            $setting->save();
            if ($request->hasFile('logo'))
                $setting->updateSingleMedia('setting', $request->file('logo'), $setting, null, 'media', true, false, 'logo');
            if ($request->hasFile('favicon'))
                $setting->updateSingleMedia('setting', $request->file('favicon'), $setting, null, 'media', true, false, 'favicon');

            if ($hasHomeQuick) {
                if ($request->hasFile('home_quick_charge_image')) {
                    $setting->updateSingleMedia('setting', $request->file('home_quick_charge_image'), $setting, null, 'media', true, false, 'home_quick_charge');
                }
                if ($request->hasFile('home_quick_codes_image')) {
                    $setting->updateSingleMedia('setting', $request->file('home_quick_codes_image'), $setting, null, 'media', true, false, 'home_quick_codes');
                }
                if ($request->hasFile('home_quick_cash_exchange_image')) {
                    $setting->updateSingleMedia('setting', $request->file('home_quick_cash_exchange_image'), $setting, null, 'media', true, false, 'home_quick_cash_exchange');
                }
                if ($request->hasFile('home_quick_money_exchange_image')) {
                    $setting->updateSingleMedia('setting', $request->file('home_quick_money_exchange_image'), $setting, null, 'media', true, false, 'home_quick_money_exchange');
                }
            }

            Cache::forget('app_settings');
            Cache::forget('wallet.point_prices');
            $locales = array_keys(config('laravellocalization.supportedLocales', []));
            if (empty($locales)) {
                $locales = ['ar', 'en'];
            }
            foreach ($locales as $locale) {
                Cache::forget("home.products.$locale");
            }

            $msg = 'تم تحديث الإعدادات بنجاح.';
            if (! $hasHomeQuick) {
                $msg .= ' (لتفعيل تعديل كروت الصفحة الرئيسية شغّل: php artisan migrate --force)';
            }
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    public function history(HistoryDataTable $historyDataTable)
    {
        return $historyDataTable->render('dashboard.admin.settings.history', ['pageTitle' => 'History']);
    }
}