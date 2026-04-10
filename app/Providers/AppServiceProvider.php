<?php

namespace App\Providers;

use App\Models\CashExchangeRequest;
use App\Models\Category;
use App\Models\ManualPaymentRequest;
use App\Models\MoneyExchangeRequest;
use App\Models\MoneyExchangeSetting;
use App\Models\Order;
use App\Models\Setting;
use App\Models\WalletTopupRequest;
use App\Observers\NewDashboardRequestWhatsAppObserver;
use App\Services\Currency\ExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            Schema::defaultStringLength(191);
        }

        // Ensure these variables always exist in views, even if DB is unavailable
        // or settings table is empty.
        View::share([
            'settings' => null,
            'logo' => null,
            'favicon' => null,
            'cashExchangeEnabled' => true,
        ]);

        try {
            if (Schema::hasTable('settings')) {
                $settings = Cache::remember('app_settings', 60 * 60, function () {
                    return Setting::with('media')->latest('id')->first();
                });

                if ($settings) {
                    $assetUrl = (string) config('app.asset_url', '');
                    $assetUrl = trim($assetUrl);
                    $assetPath = $assetUrl !== '' ? (string) (parse_url($assetUrl, PHP_URL_PATH) ?? '') : '';
                    $assetPath = rtrim($assetPath, '/');
                    $hasPublicBase = $assetPath === '/public';
                    $prefix = $hasPublicBase ? '' : 'public/';

                    $fallbackLogo = asset($prefix . 'dashboard/assets/media/logos/logo-default.svg');
                    $logo = $settings->getMediaUrl('setting', $settings, null, 'media', 'logo') ?: $fallbackLogo;
                    $favicon = $settings->getMediaUrl('setting', $settings, null, 'media', 'favicon') ?: $fallbackLogo;

                    View::share([
                        'settings' => $settings,
                        'logo' => $logo,
                        'favicon' => $favicon,
                        'fallbackLogo' => $fallbackLogo,
                        'cashExchangeEnabled' => (bool) ($settings->cash_exchange_enabled ?? true),
                        'chargeEnabled' => (bool) ($settings->charge_enabled ?? true),
                        'codesEnabled' => (bool) ($settings->codes_enabled ?? true),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // If DB is misconfigured/unavailable, don't crash the whole app at boot.
        }

        // Share cached menu categories for website views
        View::composer('website.*', function ($view) {
            $fx = app(ExchangeRateService::class)->sarRates();
            $ratesByCountry = [
                'SA' => 1.0,
                'JO' => (float) ($fx['JOD'] ?? 0.1885),
                'US' => (float) ($fx['USD'] ?? 0.2666),
            ];

            // Merchant USD override: affects USD display only.
            try {
                if (auth()->check() && (bool) (auth()->user()?->is_merchant ?? false)) {
                    if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'merchant_usd_rate')) {
                        $s = Cache::get('app_settings') ?: Setting::query()->latest('id')->first();
                        $m = (float) ($s?->merchant_usd_rate ?? 0);
                        if ($m > 0) {
                            $ratesByCountry['US'] = $m;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $locale = app()->getLocale();
                $categories = Cache::remember("website.categories.menu.$locale", 60 * 10, function () {
                    return Category::with(['translations', 'media', 'children.translations'])
                        ->whereNull('parent_id')
                        ->where('status', 'active')
                        ->get();
                });
            } catch (\Throwable $e) {
                $categories = collect();
            }

            $moneyExchangeEnabled = false;
            try {
                if (Schema::hasTable('money_exchange_settings')) {
                    $moneyExchangeEnabled = Cache::remember('money_exchange.enabled', 60 * 5, function () {
                        /** @var MoneyExchangeSetting|null $s */
                        $s = MoneyExchangeSetting::query()->latest('id')->first();
                        if (! $s || ! $s->enabled) return false;
                        if (empty($s->sar_per_usdt) || empty($s->usdt_to_sar_rate)) return false;
                        return true;
                    });
                }
            } catch (\Throwable $e) {
                $moneyExchangeEnabled = false;
            }

            // Optional manual toggle from main settings (same UX as cash exchange).
            try {
                if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'money_exchange_enabled')) {
                    $appSettings = Cache::get('app_settings');
                    if (!$appSettings) {
                        $appSettings = Setting::query()->latest()->first();
                    }
                    $toggle = (bool) ($appSettings?->money_exchange_enabled ?? true);
                    $moneyExchangeEnabled = (bool) $moneyExchangeEnabled && $toggle;
                }
            } catch (\Throwable $e) {
                // ignore
            }

            $view->with([
                'categories' => $categories,
                'currencyRatesByCountry' => $ratesByCountry,
                'currencyRatesMeta' => [
                    'base' => 'SAR',
                    'date' => $fx['date'] ?? null,
                    'source' => $fx['source'] ?? null,
                ],
                'moneyExchangeEnabled' => (bool) $moneyExchangeEnabled,
            ]);
        });

        // WhatsApp notifications for new dashboard requests (best-effort)
        try {
            $observer = NewDashboardRequestWhatsAppObserver::class;
            ManualPaymentRequest::observe($observer);
            CashExchangeRequest::observe($observer);
            MoneyExchangeRequest::observe($observer);
            Order::observe($observer);
            WalletTopupRequest::observe($observer);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
