<?php

namespace App\Repositories;

use App\Http\Requests\MainSettingRequest;
use App\Models\{Product, Setting, SettingWatermark};
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
        $homeQuickFreefireImg = $setting?->getMediaUrl('setting', $setting, null, 'media', 'home_quick_freefire') ?? null;

        $homeFeaturedProducts = collect();
        $selectedHomeFeaturedProductIds = [];
        $homeFeaturedAllProducts = collect();
        $selectedHomeFeaturedAllProductIds = [];
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
            if (Schema::hasColumn('settings', 'home_featured_product_ids_all')) {
                $rawAll = $setting?->home_featured_product_ids_all ?? [];
                if (is_string($rawAll)) {
                    $decodedAll = json_decode($rawAll, true);
                    $rawAll = is_array($decodedAll) ? $decodedAll : [];
                }
                $selectedHomeFeaturedAllProductIds = collect((array) $rawAll)
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                $homeFeaturedAllProducts = Product::query()
                    ->select(['id', 'price', 'status', 'service_type'])
                    ->where('status', 'published')
                    ->websiteVisible()
                    ->with(['translations'])
                    ->orderByDesc('id')
                    ->limit(700)
                    ->get();
            }
        } catch (\Throwable $e) {
            $homeFeaturedProducts = collect();
            $selectedHomeFeaturedProductIds = [];
            $homeFeaturedAllProducts = collect();
            $selectedHomeFeaturedAllProductIds = [];
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
            'homeQuickFreefireImg' => $homeQuickFreefireImg,
            'homeFeaturedProducts' => $homeFeaturedProducts,
            'selectedHomeFeaturedProductIds' => $selectedHomeFeaturedProductIds,
            'homeFeaturedAllProducts' => $homeFeaturedAllProducts,
            'selectedHomeFeaturedAllProductIds' => $selectedHomeFeaturedAllProductIds,
            'homeFeaturedAllMobileColumns' => (int) ($setting?->home_featured_all_mobile_columns ?? 1),
            'homeFeaturedAllAutoplaySeconds' => (int) ($setting?->home_featured_all_autoplay_seconds ?? 3),
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
            $hasFreefirePosition = false;
            $hasPublishMinGallery = false;
            $hasHomeFeaturedProducts = false;
            $hasHomeFeaturedProductsAll = false;
            $hasHomeFeaturedAllSliderControls = false;
            $hasAccountBlurControls = false;
            $hasCenterBlurRightOffset = false;
            $hasTopAreaControls = false;
            $hasWatermarkEnabled = false;
            $hasWatermarkMultiEnabled = false;
            $hasWatermarkXOffset = false;
            $hasWatermarkYOffset = false;
            $hasWatermarkScale = false;
            $hasWatermarkSecondEnabled = false;
            $hasWatermarkSecondXOffset = false;
            $hasWatermarkSecondYOffset = false;
            try {
                $hasMoneyToggle = Schema::hasColumn('settings', 'money_exchange_enabled');
                $hasChargeToggle = Schema::hasColumn('settings', 'charge_enabled');
                $hasCodesToggle = Schema::hasColumn('settings', 'codes_enabled');
                $hasFreefirePosition = Schema::hasColumn('settings', 'home_quick_freefire_position');
                $hasPublishMinGallery = Schema::hasColumn('settings', 'public_publish_min_gallery_images');
                $hasHomeFeaturedProducts = Schema::hasColumn('settings', 'home_featured_product_ids');
                $hasHomeFeaturedProductsAll = Schema::hasColumn('settings', 'home_featured_product_ids_all');
                $hasHomeFeaturedAllSliderControls =
                    Schema::hasColumn('settings', 'home_featured_all_mobile_columns')
                    && Schema::hasColumn('settings', 'home_featured_all_autoplay_seconds');
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
                $hasCenterBlurRightOffset = Schema::hasColumn('settings', 'account_center_blur_x_from_right');
                $hasTopAreaControls =
                    Schema::hasColumn('settings', 'account_top_area_mode')
                    && Schema::hasColumn('settings', 'account_top_area_size_px')
                    && Schema::hasColumn('settings', 'account_top_area_width_px')
                    && Schema::hasColumn('settings', 'account_top_area_x_from_right_px')
                    && Schema::hasColumn('settings', 'account_top_area_blur_strength');
                $hasWatermarkEnabled = Schema::hasColumn('settings', 'watermark_enabled');
                $hasWatermarkMultiEnabled = Schema::hasColumn('settings', 'watermark_multi_enabled');
                $hasWatermarkXOffset = Schema::hasColumn('settings', 'watermark_x_offset');
                $hasWatermarkYOffset = Schema::hasColumn('settings', 'watermark_y_offset');
                $hasWatermarkScale = Schema::hasColumn('settings', 'watermark_scale_percent');
                $hasWatermarkSecondEnabled = Schema::hasColumn('settings', 'watermark_second_enabled');
                $hasWatermarkSecondXOffset = Schema::hasColumn('settings', 'watermark_second_x_offset');
                $hasWatermarkSecondYOffset = Schema::hasColumn('settings', 'watermark_second_y_offset');
            } catch (\Throwable $e) {
                $hasMoneyToggle = false;
                $hasChargeToggle = false;
                $hasCodesToggle = false;
                $hasFreefirePosition = false;
                $hasPublishMinGallery = false;
                $hasHomeFeaturedProducts = false;
                $hasHomeFeaturedProductsAll = false;
                $hasHomeFeaturedAllSliderControls = false;
                $hasAccountBlurControls = false;
                $hasCenterBlurRightOffset = false;
                $hasTopAreaControls = false;
                $hasWatermarkEnabled = false;
                $hasWatermarkMultiEnabled = false;
                $hasWatermarkXOffset = false;
                $hasWatermarkYOffset = false;
                $hasWatermarkScale = false;
                $hasWatermarkSecondEnabled = false;
                $hasWatermarkSecondXOffset = false;
                $hasWatermarkSecondYOffset = false;
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
                    'home_quick_freefire_title',
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
            if ($hasFreefirePosition) {
                $fields[] = 'home_quick_freefire_position';
            }
            if ($hasPublishMinGallery) {
                $fields[] = 'public_publish_min_gallery_images';
            }
            if ($hasHomeFeaturedAllSliderControls) {
                $fields[] = 'home_featured_all_mobile_columns';
                $fields[] = 'home_featured_all_autoplay_seconds';
            }
            try {
                if (Schema::hasColumn('settings', 'merchant_usd_rate')) {
                    $fields[] = 'merchant_usd_rate';
                }
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                if (Schema::hasColumn('settings', 'custom_usd_to_sar_rate')) {
                    $fields[] = 'custom_usd_to_sar_rate';
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
            try {
                if (Schema::hasColumn('settings', 'custom_usd_to_sar_rate')) {
                    $usdToSar = (float) $request->input('custom_usd_to_sar_rate', 0);
                    $setting->custom_usd_to_sar_rate = $usdToSar > 0 ? round($usdToSar, 6) : null;
                }
            } catch (\Throwable $e) {
                // ignore
            }
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
            if ($hasHomeFeaturedProductsAll) {
                $idsAll = collect((array) $request->input('home_featured_product_ids_all', []))
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->take(150)
                    ->values()
                    ->all();
                $setting->home_featured_product_ids_all = $idsAll;
            }
            if ($hasFreefirePosition) {
                $freefirePosition = strtolower(trim((string) $request->input('home_quick_freefire_position', 'end')));
                $setting->home_quick_freefire_position = in_array($freefirePosition, ['start', 'end'], true) ? $freefirePosition : 'end';
            }
            if ($hasHomeFeaturedAllSliderControls) {
                $mobileColumns = (int) $request->input('home_featured_all_mobile_columns', 1);
                $setting->home_featured_all_mobile_columns = in_array($mobileColumns, [1, 2], true) ? $mobileColumns : 1;

                $autoplaySeconds = (int) $request->input('home_featured_all_autoplay_seconds', 3);
                $setting->home_featured_all_autoplay_seconds = in_array($autoplaySeconds, [2, 3], true) ? $autoplaySeconds : 3;
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
                if ($hasCenterBlurRightOffset) {
                    $setting->account_center_blur_x_from_right = max(0, (int) $request->input('account_center_blur_x_from_right', 0));
                }
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

            if ($hasWatermarkEnabled || $hasWatermarkXOffset || $hasWatermarkYOffset || $hasWatermarkScale || $hasWatermarkSecondEnabled || $hasWatermarkSecondXOffset || $hasWatermarkSecondYOffset) {
                if ($hasWatermarkEnabled) {
                $setting->watermark_enabled = $request->boolean('watermark_enabled', true);
                }
                if ($hasWatermarkMultiEnabled) {
                    $setting->watermark_multi_enabled = $request->boolean('watermark_multi_enabled', false);
                }
                if ($hasWatermarkXOffset) {
                    $setting->watermark_x_offset = max(0, (int) $request->input('watermark_x_offset', 20));
                }
                if ($hasWatermarkYOffset) {
                    $setting->watermark_y_offset = (int) $request->input('watermark_y_offset', 0);
                }
                if ($hasWatermarkScale) {
                    $setting->watermark_scale_percent = max(1, min(100, (int) $request->input('watermark_scale_percent', 20)));
                }
                if ($hasWatermarkSecondEnabled) {
                    $setting->watermark_second_enabled = $request->boolean('watermark_second_enabled', true);
                }
                if ($hasWatermarkSecondXOffset) {
                    $setting->watermark_second_x_offset = (int) $request->input('watermark_second_x_offset', 40);
                }
                if ($hasWatermarkSecondYOffset) {
                    $setting->watermark_second_y_offset = (int) $request->input('watermark_second_y_offset', 0);
                }
            }
            $setting->save();
            if ($request->hasFile('logo'))
                $setting->updateSingleMedia('setting', $request->file('logo'), $setting, null, 'media', true, false, 'logo');
            if ($request->hasFile('favicon'))
                $setting->updateSingleMedia('setting', $request->file('favicon'), $setting, null, 'media', true, false, 'favicon');
            if ($hasWatermarkMultiEnabled && $request->boolean('watermark_multi_enabled', false)) {
                $this->syncDynamicWatermarks($request, $setting);
            }

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
                if ($request->hasFile('home_quick_freefire_image')) {
                    $setting->updateSingleMedia('setting', $request->file('home_quick_freefire_image'), $setting, null, 'media', true, false, 'home_quick_freefire');
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

    private function syncDynamicWatermarks(MainSettingRequest $request, Setting $setting): void
    {
        if (!Schema::hasTable('setting_watermarks')) {
            return;
        }

        $items = $request->input('wm', []);
        if (!is_array($items)) {
            $items = [];
        }
        $keptIds = [];

        foreach ($items as $idx => $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = isset($row['id']) ? (int) $row['id'] : 0;
            $enabled = isset($row['enabled']) && (string) $row['enabled'] === '1';
            $x = (int) ($row['x_offset'] ?? 0);
            $y = (int) ($row['y_offset'] ?? 0);
            $scale = max(1, min(100, (int) ($row['scale_percent'] ?? 20)));
            $sortOrder = isset($row['sort_order']) ? (int) $row['sort_order'] : ((int) $idx + 1);

            $wm = null;
            if ($id > 0) {
                $wm = $setting->watermarks()->where('id', $id)->first();
            }
            if (! $wm) {
                $wm = $setting->watermarks()->create([
                    'enabled' => true,
                    'x_offset' => 0,
                    'y_offset' => 0,
                    'scale_percent' => 20,
                    'sort_order' => $sortOrder,
                ]);
            }

            $wm->enabled = $enabled;
            $wm->x_offset = $x;
            $wm->y_offset = $y;
            $wm->scale_percent = $scale;
            $wm->sort_order = $sortOrder;
            $wm->save();

            $fileKey = "wm.$idx.image";
            if ($request->hasFile($fileKey)) {
                $wm->updateSingleMedia(
                    'setting/watermarks',
                    $request->file($fileKey),
                    $wm,
                    null,
                    'media',
                    true,
                    false,
                    'watermark_image'
                );
            }

            $keptIds[] = (int) $wm->id;
        }

        if (!empty($keptIds)) {
            $toDelete = $setting->watermarks()->whereNotIn('id', $keptIds)->get();
        } else {
            $toDelete = $setting->watermarks()->get();
        }

        foreach ($toDelete as $wm) {
            $wm->deleteExistingMedia('setting/watermarks', $wm, null, 'media', true, 'watermark_image');
            $wm->delete();
        }
    }
}