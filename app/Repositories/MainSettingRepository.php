<?php

namespace App\Repositories;

use App\Http\Requests\MainSettingRequest;
use App\Models\{Setting};
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

        return view('dashboard.admin.settings.index', [
            'title' => 'General Main Settings',
            'setting' => $setting,
            'logo' => $logo,
            'favicon' => $favicon,
            'homeQuickChargeImg' => $homeQuickChargeImg,
            'homeQuickCodesImg' => $homeQuickCodesImg,
            'homeQuickCashExchangeImg' => $homeQuickCashExchangeImg,
            'homeQuickMoneyExchangeImg' => $homeQuickMoneyExchangeImg,
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
            try {
                $hasMoneyToggle = Schema::hasColumn('settings', 'money_exchange_enabled');
                $hasChargeToggle = Schema::hasColumn('settings', 'charge_enabled');
                $hasCodesToggle = Schema::hasColumn('settings', 'codes_enabled');
            } catch (\Throwable $e) {
                $hasMoneyToggle = false;
                $hasChargeToggle = false;
                $hasCodesToggle = false;
            }

            $setting = Setting::firstOrNew([]);
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